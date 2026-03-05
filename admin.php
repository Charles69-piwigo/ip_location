<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

// ── Actions POST ──────────────────────────────────────────────────────────────

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'purge_all') {
        pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_log');
        $page['infos'][] = l10n('Log vidé avec succès.');
    } elseif ($_POST['action'] === 'purge_old') {
        $nb_days = isset($_POST['days']) ? max(1, (int)$_POST['days']) : 30;
        pwg_query('
DELETE FROM ' . $prefixeTable . 'ip_location_log
  WHERE visit_date < DATE_SUB(NOW(), INTERVAL ' . $nb_days . ' DAY)');
        $page['infos'][] = sprintf(l10n('Entrées de plus de %d jours supprimées.'), $nb_days);
    } elseif ($_POST['action'] === 'save_config') {
        $blocked = strtoupper(trim($_POST['blocked_countries'] ?? ''));
        $whitelist = trim($_POST['whitelist_ips'] ?? '');
        $blocking_enabled = isset($_POST['blocking_enabled']) ? '1' : '0';
        conf_update_param('ip_location_blocked_countries', $blocked);
        conf_update_param('ip_location_whitelist', $whitelist);
        conf_update_param('ip_location_blocking_enabled', $blocking_enabled);
        $page['infos'][] = l10n('Configuration enregistrée.');
    }
}

// ── Compteurs globaux ─────────────────────────────────────────────────────────

$r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log');
list($total_all) = pwg_db_fetch_row($r);

$r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log WHERE is_bot = 1');
list($total_bots) = pwg_db_fetch_row($r);

// ── Statistiques par pays ─────────────────────────────────────────────────────

$stats = [];
$result = pwg_query('
SELECT country, country_code, COUNT(*) AS visits,
       SUM(is_bot) AS bots
  FROM ' . $prefixeTable . 'ip_location_log
  GROUP BY country, country_code
  ORDER BY visits DESC');
while ($row = pwg_db_fetch_assoc($result)) {
    $stats[] = $row;
}

// ── Journal des visites (pagination 50) ───────────────────────────────────────

$per_page     = 50;
$current_page = isset($_GET['pnum']) ? max(1, (int)$_GET['pnum']) : 1;
$offset       = ($current_page - 1) * $per_page;

$total_result = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log');
list($total_visits) = pwg_db_fetch_row($total_result);
$total_pages = max(1, ceil($total_visits / $per_page));

$logs = [];
$result = pwg_query('
SELECT id, visit_date, ip, country, city, url, user_agent, is_bot
  FROM ' . $prefixeTable . 'ip_location_log
  ORDER BY visit_date DESC
  LIMIT ' . $per_page . ' OFFSET ' . $offset);
while ($row = pwg_db_fetch_assoc($result)) {
    $logs[] = $row;
}

// ── Rendu via template Piwigo ─────────────────────────────────────────────────

$blocked_countries  = conf_get_param('ip_location_blocked_countries', '');
$whitelist_ips      = conf_get_param('ip_location_whitelist', '');
$blocking_enabled   = conf_get_param('ip_location_blocking_enabled', '0') === '1';

$template->assign([
    'BLOCKED_COUNTRIES'  => $blocked_countries,
    'WHITELIST_IPS'      => $whitelist_ips,
    'BLOCKING_ENABLED'   => $blocking_enabled,
    'STATS'        => $stats,
    'LOGS'         => $logs,
    'TOTAL_PAGES'  => $total_pages,
    'CURRENT_PAGE' => $current_page,
    'BASE_URL'     => get_root_url() . 'admin.php?page=plugin-ip_location',
    'TOTAL_ALL'    => $total_all,
    'TOTAL_BOTS'   => $total_bots,
]);

$template->set_filename('ip_location_admin', IP_LOCATION_PATH . 'template/admin.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'ip_location_admin');
