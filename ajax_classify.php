<?php
/**
 * Classification des accès récents (ip_location_classify_recent()), lancée par la page
 * d'administration une fois affichée (v3.0.1). Avant, elle tournait pendant le
 * chargement de la page, qui attendait la fin du calcul : plusieurs dizaines de secondes
 * sur un gros journal. Au plus une fois toutes les
 * ip_location_classify_admin_interval_minutes (défaut 10), comme avant.
 */
if (!defined('PHPWG_ROOT_PATH')) {
    define('PHPWG_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . '/');
}
include_once(PHPWG_ROOT_PATH . 'include/common.inc.php');

header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

// Écrit dans le journal (score, blocage automatique) : POST avec jeton, comme les
// formulaires d'admin.php.
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_POST['pwg_token']) || !is_string($_POST['pwg_token'])
    || !hash_equals(get_pwg_token(), $_POST['pwg_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'invalid_token']);
    exit;
}

// Le calcul continue si l'administrateur quitte la page entre-temps
ignore_user_abort(true);
@set_time_limit(300);

$start = microtime(true);
$ran = ip_location_classify_if_due(max(0, (int)$conf['ip_location_classify_admin_interval_minutes']) * 60);
echo json_encode(['ran' => $ran, 'seconds' => round(microtime(true) - $start, 1)]);
