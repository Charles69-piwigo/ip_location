<?php
if (!defined('PHPWG_ROOT_PATH')) {
    define('PHPWG_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . '/');
}
include_once(PHPWG_ROOT_PATH . 'include/common.inc.php');

header('Content-Type: application/json; charset=utf-8');

$plugin_conf = ip_location_get_conf();
if (empty($plugin_conf['visitors_enabled'])) {
    echo json_encode(array('error' => 'disabled'));
    exit;
}

$periods    = ip_location_visitors_periods();
$period_key = isset($plugin_conf['visitors_period']) ? $plugin_conf['visitors_period'] : 'week';
if (!array_key_exists($period_key, $periods)) $period_key = 'week';
$interval    = $periods[$period_key]['interval'];
$period_days = $periods[$period_key]['days'];

// Couverture réelle du log (la plus ancienne entrée peut être plus récente que le début de la période)
$r_oldest   = pwg_query("SELECT MIN(visit_date) AS oldest FROM {$prefixeTable}ip_location_log");
$oldest_row = pwg_db_fetch_assoc($r_oldest);
$coverage_days = $period_days; // couverture complète par défaut
if (!empty($oldest_row['oldest'])) {
    $oldest_ts       = strtotime($oldest_row['oldest']);
    $period_start_ts = time() - ($period_days * 86400);
    if ($oldest_ts > $period_start_ts) {
        $coverage_days = max(1, (int) ceil((time() - $oldest_ts) / 86400));
    }
}

// Visites humaines : toute page photo (picture.php) ou page index avec section (?/...)
// précédée ou suivie d'une page d'entrée (accueil sans ?/, ou entrée directe ?/section)
// dans les 30 min avant ou après. Critères exacts dans ip_location_qualifying_visit_where()
// (main.inc.php), partagée avec la section d'audit "Détail des visites comptabilisées"
// de l'admin (admin.php) pour que les deux calculs ne divergent jamais.
$result = pwg_query("
SELECT l1.country_code, MIN(l1.country) AS country, COUNT(*) AS visit_count
  FROM {$prefixeTable}ip_location_log l1
 WHERE l1.visit_date >= NOW() - INTERVAL {$interval}
   AND " . ip_location_qualifying_visit_where($prefixeTable, 'l1') . "
 GROUP BY l1.country_code
 ORDER BY visit_count DESC
");

$rows = array();
while ($row = pwg_db_fetch_assoc($result)) {
    $rows[] = $row;
}

echo json_encode(array('rows' => $rows, 'period' => $period_key, 'period_days' => $period_days, 'coverage_days' => $coverage_days));
