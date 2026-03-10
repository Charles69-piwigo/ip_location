<?php
/*
Plugin Name: IP Location
Version: 1.2
Description: Log des visites des guests avec géolocalisation IP
Plugin URI: ip_location
Has Settings: webmaster
*/
// Versions
/*
    version 1.2 ajouté filtre et déf bot modifié 10/03/2026
    version 1.1 css 
    version 1.0 initial 05/03/2026
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

define('IP_LOCATION_PATH', PHPWG_PLUGINS_PATH . 'ip_location/');

// Debug  pour activer les log
//error_reporting(E_ALL);
//ini_set('display_errors', 0);
//ini_set('log_errors', 1);
//ini_set('error_log', PHPWG_ROOT_PATH . 'plugins/ip_location/ip_location_debug.log');

// Chargement de la langue
load_language('plugin.lang', IP_LOCATION_PATH . 'language/');

// Hooks de visite
add_event_handler('loc_begin_index',   'ip_location_log_visit');
add_event_handler('loc_begin_picture', 'ip_location_log_visit');

/**
 * Enregistre la visite d'un guest avec géolocalisation
 */
function ip_location_log_visit()
{
    global $user, $prefixeTable;

    // Uniquement les guests (id = 2)
    if (!isset($user['id']) || $user['id'] != 2) {
        return;
    }

    // Récupération de l'IP
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($parts[0]);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $ip_raw = $ip; // IP non échappée pour les comparaisons

    // Liste blanche d'IPs — toujours autorisées
    $whitelist_raw = conf_get_param('ip_location_whitelist', '');
    $whitelist = array_filter(array_map('trim', explode("\n", $whitelist_raw)));
    if (in_array($ip_raw, $whitelist)) {
        return;
    }

    $ip = pwg_db_real_escape_string($ip);

    // Vérification du cache
    $query = '
SELECT country, country_code, city
  FROM ' . $prefixeTable . 'ip_location_cache
  WHERE ip = \'' . $ip . '\';';
    $result = pwg_query($query);

    if (pwg_db_num_rows($result) > 0) {
        $geo = pwg_db_fetch_assoc($result);
    } else {
        // Appel à ip-api.com avec timeout 2 s
        $context = stream_context_create([
            'http' => [
                'timeout' => 2,
            ],
        ]);

        $api_url  = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=country,countryCode,city';
        $response = @file_get_contents($api_url, false, $context);

        $geo = ['country' => 'Unknown', 'country_code' => '', 'city' => 'Unknown'];

        if ($response !== false) {
            $data = json_decode($response, true);
            if (is_array($data) && isset($data['country'])) {
                $geo['country']      = $data['country']     ?? 'Unknown';
                $geo['country_code'] = $data['countryCode'] ?? '';
                $geo['city']         = $data['city']        ?? 'Unknown';
            }
        }

        // Mise en cache
        $query = '
INSERT INTO ' . $prefixeTable . 'ip_location_cache
  (ip, country, country_code, city, resolved_at)
  VALUES (
    \'' . $ip . '\',
    \'' . pwg_db_real_escape_string($geo['country']) . '\',
    \'' . pwg_db_real_escape_string($geo['country_code']) . '\',
    \'' . pwg_db_real_escape_string($geo['city']) . '\',
    NOW()
  )
  ON DUPLICATE KEY UPDATE
    country      = VALUES(country),
    country_code = VALUES(country_code),
    city         = VALUES(city),
    resolved_at  = NOW();';
        pwg_query($query);
    }

    // Construction de l'URL visitée
    $scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $url        = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Détection bot
    $is_bot = ip_location_is_bot($user_agent, $url, $ip, $prefixeTable) ? 1 : 0;

    // Déterminer si la visite sera bloquée (avant l'INSERT pour l'enregistrer)
    $is_blocked = 0;
    if (conf_get_param('ip_location_blocking_enabled', '0') === '1') {
        $blocked_raw = conf_get_param('ip_location_blocked_countries', '');
        $blocked = array_filter(array_map('trim', explode(',', strtoupper($blocked_raw))));
        if (!empty($blocked) && in_array(strtoupper($geo['country_code']), $blocked)) {
            $is_blocked = 1;
        }
    }

    // Insertion dans le log
    $query = '
INSERT INTO ' . $prefixeTable . 'ip_location_log
  (ip, country, country_code, city, url, user_agent, is_bot, is_blocked, visit_date)
  VALUES (
    \'' . $ip . '\',
    \'' . pwg_db_real_escape_string($geo['country']) . '\',
    \'' . pwg_db_real_escape_string($geo['country_code']) . '\',
    \'' . pwg_db_real_escape_string($geo['city']) . '\',
    \'' . pwg_db_real_escape_string($url) . '\',
    \'' . pwg_db_real_escape_string($user_agent) . '\',
    ' . $is_bot . ',
    ' . $is_blocked . ',
    NOW()
  );';
    pwg_query($query);

    // Marquage rétroactif : si >= 2 IPs distinctes ont visité la même URL dans les 10 dernières
    // secondes, toutes ces entrées sont des bots — y compris la première qui avait échappé
    $r = pwg_query('
SELECT COUNT(DISTINCT ip) FROM ' . $prefixeTable . 'ip_location_log
  WHERE url = \'' . pwg_db_real_escape_string($url) . '\'
    AND visit_date >= NOW() - INTERVAL 10 SECOND');
    list($distinct_ips) = pwg_db_fetch_row($r);
    if ($distinct_ips >= 2) {
        pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log
  SET is_bot = 1
  WHERE url = \'' . pwg_db_real_escape_string($url) . '\'
    AND visit_date >= NOW() - INTERVAL 10 SECOND');
    }

    // Vidage automatique : supprimer les plus anciennes entrées si dépassement du seuil
    $max_records = (int)conf_get_param('ip_location_max_records', '10000');
    if ($max_records > 0) {
        $r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log');
        list($count) = pwg_db_fetch_row($r);
        if ($count > $max_records) {
            $to_delete = $count - $max_records;
            pwg_query('
DELETE FROM ' . $prefixeTable . 'ip_location_log
  ORDER BY visit_date ASC
  LIMIT ' . $to_delete);
        }
    }

    if ($is_blocked) {
        header('HTTP/1.0 403 Forbidden');
        exit;
    }
}

function ip_location_is_bot($user_agent, $url, $ip, $prefixeTable)
{
    // UA vide
    if (empty($user_agent)) {
        return true;
    }

    // Mots-clés connus de bots
    $keywords = ['bot', 'crawler', 'spider', 'scraper', 'slurp', 'curl', 'wget',
                 'python', 'go-http', 'java/', 'libwww', 'scrapy', 'zgrab', 'masscan'];
    $ua_lower = strtolower($user_agent);
    foreach ($keywords as $kw) {
        if (strpos($ua_lower, $kw) !== false) {
            return true;
        }
    }

    // Visite en doublon : même URL depuis une IP différente dans les 10 dernières secondes
    $result = pwg_query('
SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log
  WHERE url = \'' . pwg_db_real_escape_string($url) . '\'
    AND ip != \'' . pwg_db_real_escape_string($ip) . '\'
    AND visit_date >= NOW() - INTERVAL 10 SECOND');
    list($count) = pwg_db_fetch_row($result);
    if ($count > 0) {
        return true;
    }

    return false;
}

