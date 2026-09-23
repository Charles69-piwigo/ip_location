<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

// ── Actions POST ──────────────────────────────────────────────────────────────

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'purge_cache') {
        pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_cache');
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=cache_purged');
    } elseif ($_POST['action'] === 'purge_before_date') {
        $date = trim($_POST['before_date'] ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_log
  WHERE visit_date < \'' . pwg_db_real_escape_string($date) . '\'');
            redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=purged');
        }
    } elseif ($_POST['action'] === 'save_visitors_config') {
        $visitors_enabled = isset($_POST['visitors_enabled']) ? '1' : '0';
        $visitors_period  = in_array($_POST['visitors_period'] ?? '', ['week', 'fortnight', 'month', 'quarter'])
                            ? $_POST['visitors_period'] : 'week';
        $conf_cur = ip_location_get_conf();
        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'visitors_enabled' => $visitors_enabled,
            'visitors_period'  => $visitors_period,
        ])));
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved');
    } elseif ($_POST['action'] === 'save_htaccess_config') {
        $htaccess_enabled = isset($_POST['htaccess_enabled']) ? '1' : '0';
        $whitelist = trim($_POST['whitelist_ips'] ?? '');
        $conf_cur = ip_location_get_conf();
        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'htaccess_enabled' => $htaccess_enabled,
            'whitelist'        => $whitelist,
        ])));
        $htaccess_result = ip_location_write_htaccess($htaccess_enabled);
        $msg = ($htaccess_result === true) ? 'config_saved' : ($htaccess_result === 'missing' ? 'htaccess_missing' : 'htaccess_error');
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=' . $msg);
    } elseif ($_POST['action'] === 'save_url_config') {
        $keywords = trim($_POST['blocked_url_keywords'] ?? '');
        $conf_cur = ip_location_get_conf();
        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'blocked_url_keywords' => $keywords,
        ])));
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved');
    } elseif ($_POST['action'] === 'save_download_config') {
        $download_filter_enabled    = isset($_POST['download_filter_enabled']) ? '1' : '0';
        $download_allowed_countries = strtoupper(trim($_POST['download_allowed_countries'] ?? ''));
        $download_geo_fail_mode     = ($_POST['download_geo_fail_mode'] ?? '') === 'open' ? 'open' : 'closed';
        $conf_cur = ip_location_get_conf();
        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'download_filter_enabled'    => $download_filter_enabled,
            'download_allowed_countries' => $download_allowed_countries,
            'download_geo_fail_mode'     => $download_geo_fail_mode,
        ])));
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved');
    } elseif ($_POST['action'] === 'save_bot_block_config') {
        $conf_cur = ip_location_get_conf();
        $bot_block_mode = ($_POST['bot_block_mode'] ?? '') === 'is_bot' ? 'is_bot' : 'score';
        $bot_block_score_threshold = max(10, min(90, (int)($_POST['bot_block_score_threshold'] ?? 70)));
        // L'interrupteur ne peut être activé que si le blocage .htaccess global l'est déjà —
        // sinon une ligne ajoutée au blocklist ne bloquerait jamais rien réellement
        // (cf. ip_location_auto_block_bots()).
        $bot_block_enabled = (isset($_POST['bot_block_enabled']) && $conf_cur['htaccess_enabled'] === '1') ? '1' : '0';
        $new_conf = array_merge($conf_cur, [
            'bot_block_enabled'         => $bot_block_enabled,
            'bot_block_mode'            => $bot_block_mode,
            'bot_block_score_threshold' => $bot_block_score_threshold,
        ]);
        conf_update_param('ip_location', serialize($new_conf));
        // Libère aussitôt les IP auto-bloquées qui ne correspondent plus au nouveau
        // mode/seuil, sans attendre leur expiration TTL (cf. ip_location_reconcile_auto_blocks()).
        ip_location_reconcile_auto_blocks($new_conf);
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved');
    } elseif ($_POST['action'] === 'save_config') {
        $blocked = strtoupper(trim($_POST['blocked_countries'] ?? ''));
        $blocking_enabled = isset($_POST['blocking_enabled']) ? '1' : '0';
        $max_records = max(0, (int)($_POST['max_records'] ?? 10000));
        $conf_cur = ip_location_get_conf();
        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'blocked_countries' => $blocked,
            'blocking_enabled'  => $blocking_enabled,
            'max_records'       => $max_records,
        ])));
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved');
    } elseif ($_POST['action'] === 'block_ip') {
        $ip      = trim($_POST['ip'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $city    = trim($_POST['city'] ?? '');
        if ($ip) {
            $whitelist_raw = ip_location_get_conf()['whitelist'] ?? '';
            $whitelist = array_filter(array_map('trim', explode("\n", $whitelist_raw)));
            if (in_array($ip, $whitelist)) {
                $page['errors'][] = sprintf(l10n('IP %s est dans la liste blanche.'), $ip);
            } else {
                // ON DUPLICATE KEY : promeut aussi une éventuelle entrée auto existante en
                // blocage manuel permanent (origin='manuel', expires_at=NULL) — un clic
                // explicite de l'admin doit toujours l'emporter sur une expiration auto.
                pwg_query('
INSERT INTO ' . $prefixeTable . 'ip_location_blocklist (ip, country, city, blocked_at, origin, expires_at)
  VALUES (
    \'' . pwg_db_real_escape_string($ip) . '\',
    \'' . pwg_db_real_escape_string($country) . '\',
    \'' . pwg_db_real_escape_string($city) . '\',
    NOW(), \'manuel\', NULL
  )
  ON DUPLICATE KEY UPDATE blocked_at = NOW(), origin = \'manuel\', expires_at = NULL');
                // Si l'IP ajoutée est une plage /16, les entrées 'auto' déjà couvertes
                // par cette plage sont désormais redondantes (la plage manuelle les
                // bloque déjà) : on les retire pour qu'elles cessent d'apparaître dans
                // la liste "IP bloquées automatiquement". ip_location_get_bot_candidates()
                // les exclura aussi durablement tant que la plage reste active.
                $range_prefix = ip_location_manual_range_prefix($ip);
                if ($range_prefix !== null) {
                    pwg_query('
DELETE FROM ' . $prefixeTable . 'ip_location_blocklist
 WHERE origin = \'auto\' AND ip LIKE \'' . pwg_db_real_escape_string($range_prefix) . '%\'');
                }
                ip_location_write_htaccess();
                redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=ip_blocked&ip=' . urlencode($ip));
            }
        }
    } elseif ($_POST['action'] === 'unblock_ip') {
        $ip = trim($_POST['ip'] ?? '');
        if ($ip) {
            // Marque l'IP 'exempt' plutôt que de simplement supprimer la ligne : bot_score/
            // is_bot sont recalculés depuis zéro à chaque passage de
            // ip_location_classify_recent() (déclenché par ce rechargement de page même),
            // donc un simple retrait serait aussitôt annulé par les mêmes vieilles preuves
            // toujours dans la fenêtre de 7 jours. ip_location_get_bot_candidates() ignore
            // les visites antérieures à cette date pour cette IP ; seule une NOUVELLE
            // visite suspecte peut la refaire qualifier (et promouvoir cette ligne en
            // 'auto', cf. ip_location_auto_block_bots()).
            pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_blocklist
   SET origin = \'exempt\', blocked_at = NOW(), expires_at = NULL
 WHERE ip = \'' . pwg_db_real_escape_string($ip) . '\'');
            ip_location_write_htaccess();
            redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=ip_unblocked&ip=' . urlencode($ip));
        }
    } elseif ($_POST['action'] === 'save_stats_preset') {
        $slot = (int)($_POST['slot'] ?? -1);
        $conf_cur = ip_location_get_conf();

        if ($slot < 0 || $slot > IP_LOCATION_STATS_PRESET_SLOTS - 1) {
            redirect(get_root_url() . 'admin.php?page=plugin-ip_location&tab=stats&msg=preset_invalid');
        }

        // Piwigo (common.inc.php) applique addslashes() à tout $_GET/$_POST — stripslashes()
        // obligatoire avant json_decode(), sinon les guillemets échappés cassent le parsing.
        $name = mb_substr(strip_tags(trim(stripslashes($_POST['name'] ?? ''))), 0, 60);
        $allowed_keywords = array_filter(array_map('trim', explode("\n", $conf_cur['blocked_url_keywords'] ?? '')));
        $raw_series = json_decode(stripslashes($_POST['series_json'] ?? ''), true);
        $series = ip_location_validate_stats_series($raw_series, $allowed_keywords);

        if ($name === '' || empty($series)) {
            redirect(get_root_url() . 'admin.php?page=plugin-ip_location&tab=stats&msg=preset_invalid');
        }

        $presets = ip_location_normalize_stats_presets($conf_cur['stats_presets'] ?? null);
        $presets[$slot] = ['name' => $name, 'series' => $series];

        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'stats_presets' => $presets,
        ])));
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&tab=stats&msg=preset_saved');
    } elseif ($_POST['action'] === 'delete_stats_preset') {
        $slot = (int)($_POST['slot'] ?? -1);
        if ($slot >= 0 && $slot < IP_LOCATION_STATS_PRESET_SLOTS) {
            $conf_cur = ip_location_get_conf();
            $presets = ip_location_normalize_stats_presets($conf_cur['stats_presets'] ?? null);
            $presets[$slot] = null;
            conf_update_param('ip_location', serialize(array_merge($conf_cur, [
                'stats_presets' => $presets,
            ])));
        }
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&tab=stats&msg=preset_deleted');
    }
}

// Classification différée des bots par co-visitation (scan lourd, réservé à cette
// consultation admin — jamais déclenché depuis un chemin public comme ajax_visitors.php).
// Placé après les actions POST (qui redirigent) pour ne pas payer ce coût inutilement
// sur une simple sauvegarde de configuration.
ip_location_classify_recent();

// ── Compteurs globaux ─────────────────────────────────────────────────────────

$r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log');
list($total_all) = pwg_db_fetch_row($r);

$r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log WHERE is_bot = 1');
list($total_bots) = pwg_db_fetch_row($r);

$r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log WHERE is_blocked = 1');
list($total_blocked) = pwg_db_fetch_row($r);

$r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log WHERE is_bot = 0 AND is_blocked = 0');
list($total_normal) = pwg_db_fetch_row($r);

// ── Détail des visites comptabilisées (audit du widget public "Visiteurs") ────
// Rejoue exactement la requête d'agrégat de ajax_visitors.php (même helper partagé,
// même période configurée) mais renvoie le détail ligne par ligne, pour pouvoir
// vérifier concrètement ce qui compose le chiffre affiché par le widget.

// $plugin_conf n'est assigné que plus loin dans ce fichier (config Blocklist/bot) :
// on relit ici via ip_location_get_conf() directement (cache statique, coût nul) pour
// ne pas dépendre de l'ordre des blocs et refléter la période réellement enregistrée.
$visitor_conf        = ip_location_get_conf();
$visitor_periods     = ip_location_visitors_periods();
$visitor_period_key  = isset($visitor_conf['visitors_period']) && array_key_exists($visitor_conf['visitors_period'], $visitor_periods)
    ? $visitor_conf['visitors_period'] : 'week';
$visitor_interval    = $visitor_periods[$visitor_period_key]['interval'];

$visitor_detail_rows = [];
$result = pwg_query('
SELECT l1.visit_date, l1.ip, l1.country, l1.country_code, l1.url
  FROM ' . $prefixeTable . 'ip_location_log l1
 WHERE l1.visit_date >= NOW() - INTERVAL ' . $visitor_interval . '
   AND ' . ip_location_qualifying_visit_where($prefixeTable, 'l1') . '
 ORDER BY l1.country ASC, l1.visit_date DESC');
while ($row = pwg_db_fetch_assoc($result)) {
    $visitor_detail_rows[] = $row;
}

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

// ── Liste des pays pour filtre ────────────────────────────────────────────────

$countries = [];
$result = pwg_query('
SELECT country, country_code, COUNT(*) AS visits
  FROM ' . $prefixeTable . 'ip_location_log
  WHERE country_code IS NOT NULL AND country_code != \'\'
  GROUP BY country, country_code
  ORDER BY country ASC');
while ($row = pwg_db_fetch_assoc($result)) {
    $countries[] = $row;
}

// ── Journal des visites (pagination 50) ───────────────────────────────────────

$per_page     = 50;
$current_page = isset($_GET['pnum']) ? max(1, (int)$_GET['pnum']) : 1;
$offset       = ($current_page - 1) * $per_page;

$filter = isset($_GET['filter']) && in_array($_GET['filter'], ['normal','bot','blocked']) ? $_GET['filter'] : 'all';
$country_filter = isset($_GET['country']) ? strtoupper(trim($_GET['country'])) : '';
if (!preg_match('/^[A-Z]{0,2}$/', $country_filter)) $country_filter = '';

$date_from = isset($_GET['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_from']) ? $_GET['date_from'] : '';
$date_to   = isset($_GET['date_to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_to'])   ? $_GET['date_to']   : '';
$ip_filter = isset($_GET['ip_filter']) ? preg_replace('/[^0-9a-fA-F.:\/]/', '', trim($_GET['ip_filter'])) : '';

// "Bloqués" doit aussi couvrir les IP actuellement dans la blocklist .htaccess
// (manuelle ou auto) — pas seulement is_blocked=1, qui ne reflète que le blocage
// pays/mot-clé décidé au moment de la visite. Sans ça, une IP bloquée après coup
// (ex. ajoutée manuellement) continue d'apparaître comme "Normal"/"Bots non bloqués"
// sur ses lignes déjà journalisées.
$in_blocklist_sql = 'ip IN (SELECT ip FROM ' . $prefixeTable . 'ip_location_blocklist WHERE origin != \'exempt\')';
$where_parts = [];
if ($filter === 'normal')  $where_parts[] = "is_bot = 0 AND is_blocked = 0 AND NOT ($in_blocklist_sql)";
if ($filter === 'bot')     $where_parts[] = "is_bot = 1 AND is_blocked = 0 AND NOT ($in_blocklist_sql)";
if ($filter === 'blocked') $where_parts[] = "(is_blocked = 1 OR $in_blocklist_sql)";
if ($country_filter !== '') $where_parts[] = "country_code = '" . pwg_db_real_escape_string($country_filter) . "'";
if ($date_from !== '') $where_parts[] = "visit_date >= '" . pwg_db_real_escape_string($date_from) . " 00:00:00'";
if ($date_to   !== '') $where_parts[] = "visit_date <= '" . pwg_db_real_escape_string($date_to)   . " 23:59:59'";
if ($ip_filter !== '')  $where_parts[] = "ip LIKE '" . pwg_db_real_escape_string($ip_filter) . "%'";
$filter_where = empty($where_parts) ? '' : 'WHERE ' . implode(' AND ', $where_parts);

$total_result = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log ' . $filter_where);
list($total_visits) = pwg_db_fetch_row($total_result);
$total_pages = max(1, ceil($total_visits / $per_page));

$logs = [];
$result = pwg_query('
SELECT id, visit_date, ip, country, city, url, user_agent, is_bot, is_blocked, bot_score
  FROM ' . $prefixeTable . 'ip_location_log
  ' . $filter_where . '
  ORDER BY visit_date DESC
  LIMIT ' . $per_page . ' OFFSET ' . $offset);
while ($row = pwg_db_fetch_assoc($result)) {
    $logs[] = $row;
}

// ── Rendu via template Piwigo ─────────────────────────────────────────────────

$tab     = in_array($_GET['tab'] ?? '', ['help', 'stats'], true) ? $_GET['tab'] : 'config';
$tab_tpl = IP_LOCATION_PATH . 'template/' . $tab . '.tpl';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'cache_purged') $page['infos'][] = l10n('Cache de géolocalisation vidé.');
    if ($_GET['msg'] === 'purged')       $page['infos'][] = l10n('Logs supprimés.');
    if ($_GET['msg'] === 'config_saved') $page['infos'][] = l10n('Configuration enregistrée.');
    if ($_GET['msg'] === 'htaccess_error')   $page['errors'][] = l10n('.htaccess non accessible en écriture.');
    if ($_GET['msg'] === 'htaccess_missing') $page['errors'][] = l10n('Fichier .htaccess inexistant : vous devez le créer manuellement à la racine de Piwigo.');
    if ($_GET['msg'] === 'ip_blocked')      $page['infos'][] = sprintf(l10n('IP %s ajoutée au .htaccess.'), $_GET['ip'] ?? '');
    if ($_GET['msg'] === 'ip_unblocked')    $page['infos'][] = sprintf(l10n('IP %s retirée du .htaccess.'), $_GET['ip'] ?? '');
    if ($_GET['msg'] === 'preset_saved')    $page['infos'][] = l10n('Préréglage enregistré.');
    if ($_GET['msg'] === 'preset_deleted')  $page['infos'][] = l10n('Préréglage supprimé.');
    if ($_GET['msg'] === 'preset_invalid')  $page['errors'][] = l10n('Préréglage invalide.');
}

$plugin_conf           = ip_location_get_conf();
$blocked_countries     = $plugin_conf['blocked_countries'];
$blocked_url_keywords  = $plugin_conf['blocked_url_keywords'] ?? '';
$whitelist_ips         = $plugin_conf['whitelist'];
$blocking_enabled      = $plugin_conf['blocking_enabled'] === '1';
$htaccess_enabled      = $plugin_conf['htaccess_enabled'] === '1';
$visitors_enabled      = $plugin_conf['visitors_enabled'] === '1';
$visitors_period       = $plugin_conf['visitors_period'] ?? 'week';
$max_records           = (int)$plugin_conf['max_records'];
$server_is_nginx       = stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false
                      && stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'apache') === false;

$download_filter_enabled    = $plugin_conf['download_filter_enabled'] === '1';
$download_allowed_countries = $plugin_conf['download_allowed_countries'] ?? '';
$download_geo_fail_mode     = $plugin_conf['download_geo_fail_mode'] ?? 'open';
$guest_enabled_high         = ip_location_guest_enabled_high();

$bot_block_enabled         = $plugin_conf['bot_block_enabled'] === '1';
$bot_block_mode            = $plugin_conf['bot_block_mode'] ?? 'score';
$bot_block_score_threshold = (int)($plugin_conf['bot_block_score_threshold'] ?? 70);

// ── Blocklist ip_location_blocklist ───────────────────────────────────────────

$blocklist = [];
$result = pwg_query('SELECT ip, country, city, blocked_at, origin, DATE(expires_at) AS expires_at FROM ' . $prefixeTable . 'ip_location_blocklist WHERE origin != \'exempt\' ORDER BY blocked_at DESC');
while ($row = pwg_db_fetch_assoc($result)) {
    $blocklist[] = $row;
}
// Liste longue sur les sites très ciblés (auto-blocage) : séparée en deux pour la lisibilité
// — manuelles toujours affichées, auto dépliables/repliables (template/config.tpl).
$blocklist_manual = array_values(array_filter($blocklist, function ($b) { return $b['origin'] !== 'auto'; }));
$blocklist_auto    = array_values(array_filter($blocklist, function ($b) { return $b['origin'] === 'auto'; }));
$blocklist_ips = array_column($blocklist, 'ip');

// IP retirées manuellement du .htaccess (origin='exempt') : leur bot_score peut rester
// affiché au-dessus du seuil courant sans qu'elles soient bloquées — badge dédié dans le
// journal pour que ça ne ressemble pas à un bug (cf. ip_location_get_bot_candidates()).
$result = pwg_query('SELECT ip FROM ' . $prefixeTable . 'ip_location_blocklist WHERE origin = \'exempt\'');
$exempt_ips = [];
while ($row = pwg_db_fetch_row($result)) {
    $exempt_ips[] = $row[0];
}

// Marquer les entrées du log dont l'IP est en blocklist
foreach ($logs as &$log) {
    $log['in_blocklist'] = in_array($log['ip'], $blocklist_ips);
    $log['is_exempt']    = in_array($log['ip'], $exempt_ips);
}
unset($log);

// ── Onglet Statistiques dynamiques (requêtes/i18n chargées seulement si actif) ─

$known_ips              = [];
$blocked_keywords_list  = [];
$stats_presets_json     = '[]';
$stats_i18n_json        = '{}';
$chart_js_url           = '';
$ajax_stats_url         = '';
$known_ips_json         = '[]';
$countries_json         = '[]';
$blocked_keywords_json  = '[]';
$ajax_stats_url_json    = '""';
$last_stats_period      = 'month';
$last_stats_date_from_json = json_encode('');
$last_stats_date_to_json   = json_encode('');
$chart_bg_color         = '#ffffff';

if ($tab === 'stats') {
    $result = pwg_query('
SELECT ip, COUNT(*) AS hits
  FROM ' . $prefixeTable . 'ip_location_log
  GROUP BY ip
  ORDER BY hits DESC
  LIMIT 300');
    while ($row = pwg_db_fetch_assoc($result)) {
        $known_ips[] = $row;
    }
    $known_ips_json = json_encode($known_ips, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    $blocked_keywords_list = array_values(array_filter(array_map('trim', explode("\n", $blocked_url_keywords))));
    $blocked_keywords_json = json_encode($blocked_keywords_list, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

    $countries_for_js = array_map(function ($c) {
        return ['code' => $c['country_code'], 'name' => $c['country'], 'hits' => (int)$c['visits']];
    }, $countries);
    $countries_json = json_encode($countries_for_js, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

    $stats_presets = ip_location_normalize_stats_presets($plugin_conf['stats_presets'] ?? null);
    $stats_presets_json = json_encode($stats_presets, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

    $last_stats_period = in_array($plugin_conf['last_stats_period'] ?? '', ['week', 'fortnight', 'month', 'quarter', 'all', 'custom'], true)
                        ? $plugin_conf['last_stats_period'] : 'month';
    $last_stats_date_from_json = json_encode($plugin_conf['last_stats_date_from'] ?? '');
    $last_stats_date_to_json   = json_encode($plugin_conf['last_stats_date_to'] ?? '');

    $chart_bg_color = (!empty($plugin_conf['chart_bg_color']) && ($plugin_conf['chart_bg_color'] === 'transparent' || preg_match('/^#[0-9a-fA-F]{6}$/', $plugin_conf['chart_bg_color'])))
                    ? $plugin_conf['chart_bg_color'] : '#ffffff';

    $stats_i18n = [
        'type_all'            => l10n('Tous'),
        'type_normal'         => l10n('Normal'),
        'type_bot'            => l10n('Bots non bloqués'),
        'type_blocked'        => l10n('Bloqués'),
        'axis_left'           => l10n('Axe gauche'),
        'axis_right'          => l10n('Axe droit'),
        'axis_badge_left'     => l10n('G'),
        'axis_badge_right'    => l10n('D'),
        'filter_countries'    => l10n('Filtrer les pays…'),
        'filter_keywords'     => l10n('Filtrer les mots-clés…'),
        'filter_ips'          => l10n('Filtrer les IP…'),
        'label_countries'     => l10n('Pays'),
        'label_keywords'      => l10n('Mots-clés'),
        'label_ip'            => l10n('IP'),
        'series_name_placeholder' => l10n('Nom de la série'),
        'remove_series'       => l10n('Retirer cette série'),
        'series_active_one'   => l10n('série active'),
        'series_active_many'  => l10n('séries actives'),
        'granularity_day'     => l10n('granularité : jour'),
        'granularity_week'    => l10n('granularité : semaine'),
        'granularity_month'   => l10n('granularité : mois'),
        'slot_empty_name'     => l10n('Emplacement libre'),
        'slot_empty_meta'     => l10n('cliquer pour créer'),
        'slot_series_suffix'  => l10n('série(s)'),
        'slot_prefix'         => l10n('Emplacement'),
        'slot_new'            => l10n('nouveau préréglage'),
        'slot_edit'           => l10n('édition'),
        'save_need_series'    => l10n('Ajoutez au moins une série avant d\'enregistrer ce préréglage.'),
        'preset_vue_globale'       => l10n('Vue globale'),
        'preset_bots_vs_humains'   => l10n('Bots non bloqués vs humains'),
        'preset_normal_vs_bloques' => l10n('Normal vs bloqués'),
        'series_tout'         => l10n('Tout'),
        'series_humains'      => l10n('Humains'),
        'series_bots'         => l10n('Bots non bloqués'),
        'series_normal'       => l10n('Normal'),
        'series_bloques'      => l10n('Bloqués'),
        'bg_white'            => l10n('Blanc'),
        'bg_light_gray'       => l10n('Gris clair'),
        'bg_light_blue'       => l10n('Bleu très pâle'),
        'bg_none'             => l10n('Aucune (transparent)'),
    ];
    $stats_i18n_json = json_encode($stats_i18n, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

    $chart_js_url        = get_root_url() . 'plugins/ip_location/template/js/chart.umd.min.js';
    $ajax_stats_url      = get_root_url() . 'plugins/ip_location/ajax_stats.php';
    $ajax_stats_url_json = json_encode($ajax_stats_url);
}

$template->assign([
    'VISITORS_ENABLED'      => $visitors_enabled,
    'VISITORS_PERIOD'       => $visitors_period,
    'VISITOR_DETAIL_ROWS'   => $visitor_detail_rows,
    'BLOCKED_COUNTRIES'     => $blocked_countries,
    'BLOCKED_URL_KEYWORDS'  => $blocked_url_keywords,
    'WHITELIST_IPS'         => $whitelist_ips,
    'BLOCKING_ENABLED'   => $blocking_enabled,
    'HTACCESS_ENABLED'   => $htaccess_enabled,
    'SERVER_IS_NGINX'    => $server_is_nginx,
    'MAX_RECORDS'        => $max_records,
    'DOWNLOAD_FILTER_ENABLED'    => $download_filter_enabled,
    'DOWNLOAD_ALLOWED_COUNTRIES' => $download_allowed_countries,
    'DOWNLOAD_GEO_FAIL_MODE'     => $download_geo_fail_mode,
    'GUEST_ENABLED_HIGH'         => $guest_enabled_high,
    'BOT_BLOCK_ENABLED'          => $bot_block_enabled,
    'BOT_BLOCK_MODE'             => $bot_block_mode,
    'BOT_BLOCK_SCORE_THRESHOLD'  => $bot_block_score_threshold,
    'BLOCKLIST_MANUAL'   => $blocklist_manual,
    'BLOCKLIST_AUTO'     => $blocklist_auto,
    'STATS'              => $stats,
    'LOGS'               => $logs,
    'TOTAL_PAGES'        => $total_pages,
    'CURRENT_PAGE'       => $current_page,
    'BASE_URL'           => get_root_url() . 'admin.php?page=plugin-ip_location',
    'FILTER'             => $filter,
    'COUNTRY_FILTER'     => $country_filter,
    'DATE_FROM'          => $date_from,
    'DATE_TO'            => $date_to,
    'IP_FILTER'          => $ip_filter,
    'COUNTRIES'          => $countries,
    'KNOWN_IPS_JSON'        => $known_ips_json,
    'COUNTRIES_JSON'        => $countries_json,
    'BLOCKED_KEYWORDS_JSON' => $blocked_keywords_json,
    'STATS_PRESETS_JSON'    => $stats_presets_json,
    'STATS_I18N_JSON'       => $stats_i18n_json,
    'CHART_JS_URL'          => $chart_js_url,
    'AJAX_STATS_URL_JSON'   => $ajax_stats_url_json,
    'LAST_STATS_PERIOD'          => $last_stats_period,
    'LAST_STATS_DATE_FROM_JSON'  => $last_stats_date_from_json,
    'LAST_STATS_DATE_TO_JSON'    => $last_stats_date_to_json,
    'CHART_BG_COLOR'             => $chart_bg_color,
    'TAB'           => $tab,
    'TOTAL_ALL'     => $total_all,
    'TOTAL_BOTS'    => $total_bots,
    'TOTAL_BLOCKED' => $total_blocked,
    'TOTAL_NORMAL'  => $total_normal,
]);

$template->set_filename('ip_location_tab', $tab_tpl);
$template->assign_var_from_handle('TAB_CONTENT', 'ip_location_tab');

$template->set_filename('ip_location_admin', IP_LOCATION_PATH . 'template/admin.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'ip_location_admin');
