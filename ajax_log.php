<?php
/**
 * Endpoint AJAX : logge une URL visitée via PhotoSwipe (sans rechargement de page).
 * Appelé par le listener JS afterChange / initialZoomInEnd du wrapper PhotoSwipe.xx
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

// Vérifier que l'URL est interne à cette installation : URL absolue (le wrapper JS la
// fait résoudre par le navigateur), même hôte que cette requête, chemin sous le dossier
// de Piwigo. Avant v2.8c.1, comparaison à get_root_url(), qui vaut ici PHPWG_ROOT_PATH
// (chemin disque, cf. define ci-dessus) : toute URL était refusée en 'external_url', et
// le logger PhotoSwipe n'a jamais rien journalisé depuis la v2.1.
$parts = parse_url($url);
$host  = isset($_SERVER['HTTP_X_FORWARDED_HOST'])
    ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0])
    : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
// Dossier de Piwigo vu du navigateur : 3 niveaux au-dessus de /plugins/ip_location/ajax_log.php
$base  = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/') . '/';
$url_host = isset($parts['host']) ? $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') : '';
if (!is_array($parts)
    || !isset($parts['scheme']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
    || $host === '' || strcasecmp($url_host, $host) !== 0
    || !isset($parts['path']) || strpos($parts['path'], $base) !== 0
) {
    echo json_encode(['ok' => false, 'reason' => 'external_url']);
    exit;
}

// Loguer la visite sans déclencher le blocage HTTP
// (le visiteur est déjà sur le site, il sera bloqué au prochain chargement de page)
// log_type='js' : preuve qu'un vrai navigateur a exécuté du JavaScript, utilisée
// par le score de suspicion bot (signal "aucune trace JS" = probablement un script).
ip_location_log_visit($url, false, 'js');

echo json_encode(['ok' => true]);
