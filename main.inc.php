<?php
/*
Plugin Name: IP Location
Version: 1.5
Description: Log des visites des guests avec géolocalisation IP + traitement htaccess
Plugin URI: ip_location
Has Settings: webmaster
*/
// Versions
/*
    version 1.5 11/03/2026
        cache géo : expiry 30 jours + limite 5000 entrées
        tableau IPs de .htaccess avec pays/ville, suppression éditeur textarea
        fallback multi-providers géolocalisation (ip-api.com > ipwho.is > geoplugin.net > ipapi.co)
    version 1.4 10/03/2026
        config unifiée en une seule entrée _config
    version 1.3 10/03/2026
        ajouté gestion htaccess + aide
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

/**
 * Retourne la configuration du plugin (tableau, avec valeurs par défaut).
 * Lit l'entrée unique 'ip_location' dans _config (valeur sérialisée).
 */
function ip_location_get_conf()
{
    global $conf;
    static $cache = null;
    if ($cache !== null) return $cache;

    $default = [
        'blocked_countries' => '',
        'whitelist'         => '',
        'blocking_enabled'  => '0',
        'htaccess_enabled'  => '0',
        'max_records'       => 10000,
    ];

    if (!empty($conf['ip_location'])) {
        $stored = @unserialize($conf['ip_location']);
        if (is_array($stored)) {
            $cache = array_merge($default, $stored);
            return $cache;
        }
    }
    $cache = $default;
    return $cache;
}

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
    $plugin_conf  = ip_location_get_conf();
    $whitelist_raw = $plugin_conf['whitelist'];
    $whitelist = array_filter(array_map('trim', explode("\n", $whitelist_raw)));
    if (in_array($ip_raw, $whitelist)) {
        return;
    }

    $ip = pwg_db_real_escape_string($ip);

    // Blocklist manuelle — blocage immédiat par IP individuelle
    $r = pwg_query('SELECT 1 FROM ' . $prefixeTable . 'ip_location_blocklist WHERE ip = \'' . $ip . '\'');
    if (pwg_db_num_rows($r) > 0) {
        header('HTTP/1.0 403 Forbidden');
        exit;
    }

    // Vérification du cache (entrées valides moins de 30 jours)
    $query = '
SELECT country, country_code, city
  FROM ' . $prefixeTable . 'ip_location_cache
  WHERE ip = \'' . $ip . '\'
    AND resolved_at >= NOW() - INTERVAL 30 DAY;';
    $result = pwg_query($query);

    if (pwg_db_num_rows($result) > 0) {
        $geo = pwg_db_fetch_assoc($result);
    } else {
        // Résolution géo avec fallback multi-providers (timeout 2s chacun)
        $context = stream_context_create([
            'http' => ['timeout' => 2],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $providers = [
            [
                'url'          => 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=country,countryCode,city',
                'country'      => 'country',
                'country_code' => 'countryCode',
                'city'         => 'city',
            ],
            [
                'url'          => 'https://ipwho.is/' . rawurlencode($ip),
                'country'      => 'country',
                'country_code' => 'country_code',
                'city'         => 'city',
                'success'      => 'success',
            ],
            [
                'url'          => 'https://ssl.geoplugin.net/json.gp?ip=' . rawurlencode($ip),
                'country'      => 'geoplugin_countryName',
                'country_code' => 'geoplugin_countryCode',
                'city'         => 'geoplugin_city',
            ],
            [
                'url'          => 'https://ipapi.co/' . rawurlencode($ip) . '/json/',
                'country'      => 'country_name',
                'country_code' => 'country_code',
                'city'         => 'city',
            ],
        ];

        $geo = ['country' => 'Unknown', 'country_code' => '', 'city' => 'Unknown'];

        foreach ($providers as $provider) {
            $response = @file_get_contents($provider['url'], false, $context);
            if ($response === false) continue;

            $data = json_decode($response, true);
            if (!is_array($data)) continue;

            // Vérification champ 'success' (ipwho.is retourne success=false si IP invalide)
            if (isset($provider['success']) && empty($data[$provider['success']])) continue;

            $country      = !empty($data[$provider['country']])      ? $data[$provider['country']]      : '';
            $country_code = !empty($data[$provider['country_code']]) ? $data[$provider['country_code']] : '';
            $city         = !empty($data[$provider['city']])         ? $data[$provider['city']]         : '';

            if (!empty($country) && $country !== 'Unknown') {
                $geo['country']      = $country;
                $geo['country_code'] = $country_code;
                $geo['city']         = !empty($city) ? $city : 'Unknown';
                break; // Provider OK, on arrête
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

        // Nettoyage du cache : supprimer les entrées > 30 jours
        pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_cache
  WHERE resolved_at < NOW() - INTERVAL 30 DAY');

        // Limite de taille : garder les 5000 entrées les plus récentes
        $r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_cache');
        list($cache_count) = pwg_db_fetch_row($r);
        if ($cache_count > 5000) {
            pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_cache
  ORDER BY resolved_at ASC
  LIMIT ' . ($cache_count - 5000));
        }
    }

    // Construction de l'URL visitée
    $scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $url        = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Détection bot
    $is_bot = ip_location_is_bot($user_agent, $url, $ip, $prefixeTable) ? 1 : 0;

    // Déterminer si la visite sera bloquée (avant l'INSERT pour l'enregistrer)
    $is_blocked = 0;
    if ($plugin_conf['blocking_enabled'] === '1') {
        $blocked = array_filter(array_map('trim', explode(',', strtoupper($plugin_conf['blocked_countries']))));
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
    $max_records = (int)$plugin_conf['max_records'];
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

function ip_location_write_htaccess()
{
    global $prefixeTable;

    $htaccess_path = PHPWG_ROOT_PATH . '.htaccess';

    if (file_exists($htaccess_path)) {
        if (!is_writable($htaccess_path)) return false;
        $content = file_get_contents($htaccess_path);
    } else {
        if (!is_writable(PHPWG_ROOT_PATH)) return false;
        $content = '';
    }

    // Supprimer la section existante
    $content = preg_replace('/\n?# BEGIN ip_location\b.*?# END ip_location[^\n]*/s', '', $content);
    $content = rtrim($content);

    if (ip_location_get_conf()['htaccess_enabled'] === '1') {
        $result = pwg_query('SELECT ip FROM ' . $prefixeTable . 'ip_location_blocklist ORDER BY blocked_at ASC');
        $ips = [];
        while ($row = pwg_db_fetch_row($result)) {
            $ips[] = $row[0];
        }
        if (!empty($ips)) {
            $section = "\n\n# BEGIN ip_location\n<RequireAll>\n    Require all granted\n";
            foreach ($ips as $ip) {
                $section .= '    Require not ip ' . $ip . "\n";
            }
            $section .= "</RequireAll>\n# END ip_location";
            $content .= $section;
        }
    }

    file_put_contents($htaccess_path, $content);
    return true;
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

