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

$period_map      = array('week' => '7 DAY', 'fortnight' => '15 DAY', 'month' => '30 DAY', 'quarter' => '90 DAY');
$period_days_map = array('week' => 7,       'fortnight' => 15,        'month' => 30,        'quarter' => 90);
$period_key = isset($plugin_conf['visitors_period']) ? $plugin_conf['visitors_period'] : 'week';
if (!array_key_exists($period_key, $period_map)) $period_key = 'week';
$interval    = $period_map[$period_key];
$period_days = $period_days_map[$period_key];

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
// dans les 30 min avant ou après.
// Sections couvertes : category, list, recent_pics, most_visited, best_rated, tag, search, favorites, etc.
$result = pwg_query("
SELECT l1.country_code, MIN(l1.country) AS country, COUNT(*) AS visit_count
  FROM {$prefixeTable}ip_location_log l1
 WHERE l1.visit_date >= NOW() - INTERVAL {$interval}
   AND l1.is_bot        = 0
   AND l1.is_blocked    = 0
   AND l1.country_code != ''
   AND (
         l1.url LIKE '%/picture.php%'
      OR l1.url REGEXP '/category/[0-9]+|/list/[0-9]|/recent_pics|/most_visited|/best_rated|/tag/[0-9]|/search/[0-9]|/favorites'
   )
   AND EXISTS (
         SELECT 1
           FROM {$prefixeTable}ip_location_log l2
          WHERE l2.ip  = l1.ip
            AND l2.url != l1.url
            AND l2.visit_date BETWEEN
                DATE_SUB(l1.visit_date, INTERVAL 30 MINUTE)
                AND DATE_ADD(l1.visit_date, INTERVAL 30 MINUTE)
       )
 GROUP BY l1.country_code
 ORDER BY visit_count DESC
");

$rows = array();
while ($row = pwg_db_fetch_assoc($result)) {
    $rows[] = $row;
}

echo json_encode(array('rows' => $rows, 'period' => $period_key, 'period_days' => $period_days, 'coverage_days' => $coverage_days));
