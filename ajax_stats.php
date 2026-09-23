<?php
if (!defined('PHPWG_ROOT_PATH')) {
    define('PHPWG_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . '/');
}
include_once(PHPWG_ROOT_PATH . 'include/common.inc.php');

header('Content-Type: application/json; charset=utf-8');

// Contrairement à ajax_visitors.php (public, agrégats par pays uniquement), cet
// endpoint expose des détails par IP et par pays : réservé aux admins.
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

global $prefixeTable;

$plugin_conf      = ip_location_get_conf();
$allowed_keywords = array_filter(array_map('trim', explode("\n", $plugin_conf['blocked_url_keywords'] ?? '')));

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'bad_request']);
    exit;
}

// ── Période ──────────────────────────────────────────────────────────────

$period           = in_array($input['period'] ?? '', ['week', 'fortnight', 'month', 'quarter', 'all', 'custom'], true) ? $input['period'] : 'month';
$period_days_map  = ['week' => 7, 'fortnight' => 15, 'month' => 30, 'quarter' => 90];
$today            = new DateTime('today');

if ($period === 'all') {
    $date_to = clone $today;
    $result  = pwg_query('SELECT MIN(visit_date) AS min_date FROM ' . $prefixeTable . 'ip_location_log');
    $row     = pwg_db_fetch_assoc($result);
    $date_from = !empty($row['min_date']) ? new DateTime(substr($row['min_date'], 0, 10)) : clone $today;
} elseif ($period === 'custom') {
    $date_from_raw = $input['date_from'] ?? '';
    $date_to_raw   = $input['date_to'] ?? '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from_raw) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to_raw)) {
        http_response_code(400);
        echo json_encode(['error' => 'bad_request']);
        exit;
    }
    try {
        $date_from = new DateTime($date_from_raw);
        $date_to   = new DateTime($date_to_raw);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => 'bad_request']);
        exit;
    }
    if ($date_from > $date_to) {
        [$date_from, $date_to] = [$date_to, $date_from];
    }
    // Garde-fou : plage bornée à 2 ans pour éviter une grille de labels démesurée.
    if ((int)$date_from->diff($date_to)->days > 730) {
        $date_from = (clone $date_to)->modify('-730 days');
    }
} else {
    $days      = $period_days_map[$period];
    $date_to   = clone $today;
    $date_from = (clone $today)->modify('-' . ($days - 1) . ' days');
}

// ── Mémorisation de la dernière période et du dernier fond utilisés ─────
$last_period_conf = ['last_stats_period' => $period];
if ($period === 'custom') {
    $last_period_conf['last_stats_date_from'] = $date_from->format('Y-m-d');
    $last_period_conf['last_stats_date_to']   = $date_to->format('Y-m-d');
}
$bg_color_raw = $input['bg_color'] ?? '';
$last_period_conf['chart_bg_color'] = ($bg_color_raw === 'transparent' || preg_match('/^#[0-9a-fA-F]{6}$/', $bg_color_raw))
    ? $bg_color_raw : ($plugin_conf['chart_bg_color'] ?? '#ffffff');
conf_update_param('ip_location', serialize(array_merge($plugin_conf, $last_period_conf)));

$days_span   = (int)$date_from->diff($date_to)->days + 1;
$granularity = $days_span <= 31 ? 'day' : ($days_span <= 365 ? 'week' : 'month');

// ── Grille de labels (zéro-remplissage, partagée par toutes les séries) ────

$labels        = [];
$bucket_starts = [];

if ($granularity === 'day') {
    $cursor = clone $date_from;
    while ($cursor <= $date_to) {
        $labels[]        = $cursor->format('d/m');
        $bucket_starts[] = $cursor->format('Y-m-d');
        $cursor->modify('+1 day');
    }
} elseif ($granularity === 'week') {
    // Aligner le premier bucket sur le lundi de la semaine de $date_from
    // (WEEKDAY() MySQL : 0 = lundi .. 6 = dimanche, même convention que N=1..7 ici -1).
    $cursor  = clone $date_from;
    $weekday = (int)$cursor->format('N');
    $cursor->modify('-' . ($weekday - 1) . ' days');
    while ($cursor <= $date_to) {
        $labels[]        = $cursor->format('d/m');
        $bucket_starts[] = $cursor->format('Y-m-d');
        $cursor->modify('+7 days');
    }
} else {
    // Aligner le premier bucket sur le 1er du mois de $date_from.
    $cursor = new DateTime($date_from->format('Y-m-01'));
    while ($cursor <= $date_to) {
        $labels[]        = $cursor->format('m/Y');
        $bucket_starts[] = $cursor->format('Y-m-d');
        $cursor->modify('+1 month');
    }
}

$range_from = $date_from->format('Y-m-d') . ' 00:00:00';
$range_to   = $date_to->format('Y-m-d') . ' 23:59:59';

// ── Séries ───────────────────────────────────────────────────────────────

$series_in  = ip_location_validate_stats_series($input['series'] ?? [], $allowed_keywords);
$series_out = [];

$bucket_expr = ($granularity === 'day')
    ? 'DATE(visit_date)'
    : (($granularity === 'week')
        ? 'DATE_SUB(DATE(visit_date), INTERVAL WEEKDAY(visit_date) DAY)'
        : "DATE_FORMAT(visit_date, '%Y-%m-01')");

foreach ($series_in as $s) {
    $where = [
        "visit_date >= '" . pwg_db_real_escape_string($range_from) . "'",
        "visit_date <= '" . pwg_db_real_escape_string($range_to) . "'",
    ];

    // Cohérent avec le Journal des accès (admin.php) : "Bloqués" = refus réels OU IP/robot
    // actuellement bloqué, leviers allumés seulement (cf. ip_location_currently_blocked_sql()).
    $cur_blocked = ip_location_currently_blocked_sql($prefixeTable);
    if ($s['type'] === 'normal')  $where[] = "is_bot = 0 AND is_blocked = 0 AND NOT " . $cur_blocked;
    if ($s['type'] === 'bot')     $where[] = "is_bot = 1 AND is_blocked = 0 AND NOT " . $cur_blocked;
    if ($s['type'] === 'blocked') $where[] = "(is_blocked = 1 OR " . $cur_blocked . ")";

    if (!empty($s['countries'])) {
        $escaped = array_map(function ($c) { return "'" . pwg_db_real_escape_string($c) . "'"; }, $s['countries']);
        $where[] = 'country_code IN (' . implode(',', $escaped) . ')';
    }
    if (!empty($s['ips'])) {
        $escaped = array_map(function ($ip) { return "'" . pwg_db_real_escape_string($ip) . "'"; }, $s['ips']);
        $where[] = 'ip IN (' . implode(',', $escaped) . ')';
    }
    if (!empty($s['keywords'])) {
        $kw_parts = array_map(function ($k) { return "url LIKE '%" . pwg_db_real_escape_string($k) . "%'"; }, $s['keywords']);
        $where[]  = '(' . implode(' OR ', $kw_parts) . ')';
    }

    $result = pwg_query('
SELECT ' . $bucket_expr . ' AS bucket, COUNT(*) AS cnt
  FROM ' . $prefixeTable . 'ip_location_log
  WHERE ' . implode(' AND ', $where) . '
  GROUP BY bucket');

    $counts = [];
    while ($row = pwg_db_fetch_assoc($result)) {
        $counts[$row['bucket']] = (int)$row['cnt'];
    }

    $data = [];
    foreach ($bucket_starts as $b) {
        $data[] = $counts[$b] ?? 0;
    }

    $series_out[] = [
        'label' => $s['label'] !== '' ? $s['label'] : $s['type'],
        'axis'  => $s['axis'],
        'color' => $s['color'],
        'data'  => $data,
    ];
}

echo json_encode([
    'labels'      => $labels,
    'granularity' => $granularity,
    'series'      => $series_out,
]);
