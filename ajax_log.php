<?php
/**
 * Endpoint AJAX : logge une URL visitée via PhotoSwipe (sans rechargement de page).
 * Appelé par le listener JS afterChange / initialZoomInEnd du wrapper PhotoSwipe.
 */
if (!defined('PHPWG_ROOT_PATH')) {
    define('PHPWG_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . '/');
}
include_once(PHPWG_ROOT_PATH . 'include/common.inc.php');

header('Content-Type: application/json; charset=utf-8');

// Plugin doit être actif
if (!function_exists('ip_location_log_visit')) {
    echo json_encode(['ok' => false, 'reason' => 'plugin_inactive']);
    exit;
}

// Uniquement les guests (uid = 2)
global $user;
if (!isset($user['id']) || $user['id'] != 2) {
    echo json_encode(['ok' => false, 'reason' => 'not_guest']);
    exit;
}

// Récupérer et valider l'URL
$url = isset($_GET['url']) ? trim($_GET['url']) : '';
if (empty($url)) {
    echo json_encode(['ok' => false, 'reason' => 'no_url']);
    exit;
}

// Vérifier que l'URL est interne à cette installation
$root = get_root_url();
if (strpos($url, $root) !== 0) {
    echo json_encode(['ok' => false, 'reason' => 'external_url']);
    exit;
}

// Loguer la visite sans déclencher le blocage HTTP
// (le visiteur est déjà sur le site, il sera bloqué au prochain chargement de page)
ip_location_log_visit($url, false);

echo json_encode(['ok' => true]);
