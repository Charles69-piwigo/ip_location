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

$period_map = array('week' => '7 DAY', 'month' => '30 DAY', 'quarter' => '90 DAY');
$period_key = isset($plugin_conf['visitors_period']) ? $plugin_conf['visitors_period'] : 'week';
if (!array_key_exists($period_key, $period_map)) $period_key = 'week';
$interval = $period_map[$period_key];

// Visites humaines : accès album (index.php?/category/NNN) ou photo (index.php?/NNN/category/MMM)
// avec passage par la page d'accueil (url sans ?/) dans les 30 min avant ou après.
$result = pwg_query("
SELECT l1.country_code, MIN(l1.country) AS country, COUNT(*) AS visit_count
  FROM {$prefixeTable}ip_location_log l1
 WHERE l1.visit_date >= NOW() - INTERVAL {$interval}
   AND l1.is_bot        = 0
   AND l1.is_blocked    = 0
   AND l1.country_code != ''
   AND l1.url REGEXP '(index|picture)[.]php[?]/(category/[0-9]+|[0-9]+/category/)'
   AND EXISTS (
         SELECT 1
           FROM {$prefixeTable}ip_location_log l2
          WHERE l2.ip = l1.ip
            AND l2.url NOT LIKE '%?/%'
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

echo json_encode(array('rows' => $rows, 'period' => $period_key));
