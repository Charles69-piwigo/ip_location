<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

/**
 * Paramètres de retour après "Bloquer / Débloquer" depuis le Journal (v2.6.6) : le
 * formulaire transmet sa vue (sous-onglet, filtres, page) dans return_qs ; seuls les
 * paramètres connus du Journal sont repris, valeurs filtrées.
 */
function ip_location_admin_return_qs()
{
    parse_str(stripslashes($_POST['return_qs'] ?? ''), $in);
    $out = '';
    foreach (['sub', 'filter', 'country', 'date_from', 'date_to', 'ip_filter', 'reason', 'pnum'] as $k) {
        if (isset($in[$k]) && is_string($in[$k]) && preg_match('/^[A-Za-z0-9.:\/-]{1,45}$/', $in[$k])) {
            $out .= '&' . $k . '=' . urlencode($in[$k]);
        }
    }
    return $out;
}

// ── Actions POST ──────────────────────────────────────────────────────────────

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'purge_cache') {
        pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_cache');
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=cache_purged#ipl-card-retention');
    } elseif ($_POST['action'] === 'purge_before_date') {
        $date = trim($_POST['before_date'] ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_log
  WHERE visit_date < \'' . pwg_db_real_escape_string($date) . '\'');
            redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=purged#ipl-card-retention');
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
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved#ipl-card-visitors');
    } elseif ($_POST['action'] === 'save_ip_block') {
        // Interrupteur du bloc "Blocage par IP" (clé historique htaccess_enabled) : pilote
        // le .htaccess ET le contrôle PHP des IP bloquées manuellement (v2.6.1).
        $htaccess_enabled = isset($_POST['htaccess_enabled']) ? '1' : '0';
        $conf_cur = ip_location_get_conf();
        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'htaccess_enabled' => $htaccess_enabled,
        ])));
        $htaccess_result = ip_location_write_htaccess($htaccess_enabled);
        $msg = ($htaccess_result === true) ? 'config_saved' : ($htaccess_result === 'missing' ? 'htaccess_missing' : 'htaccess_error');
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=' . $msg . '#ipl-card-manual');
    } elseif ($_POST['action'] === 'save_whitelist') {
        $whitelist = trim(stripslashes($_POST['whitelist_ips'] ?? ''));
        $conf_cur = ip_location_get_conf();
        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'whitelist' => $whitelist,
        ])));
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved#ipl-card-whitelist');
    } elseif ($_POST['action'] === 'save_retention') {
        $max_records = max(0, (int)($_POST['max_records'] ?? 10000));
        $conf_cur = ip_location_get_conf();
        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'max_records' => $max_records,
        ])));
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved#ipl-card-retention');
    } elseif ($_POST['action'] === 'save_robots') {
        // Bloc "Robots d'indexation" (v2.6.2/2.6.3). Tableaux POST robots[i][…] :
        // Piwigo applique addslashes() à tout $_POST (récursivement), d'où stripslashes().
        $allowed_fams = ['search', 'social', 'ai'];
        $robots = [];
        foreach ((array)($_POST['robots'] ?? []) as $row) {
            if (!is_array($row) || !empty($row['remove'])) {
                continue;
            }
            $name = mb_substr(trim(stripslashes($row['name'] ?? '')), 0, 64);
            $ua   = mb_substr(trim(stripslashes($row['ua'] ?? '')), 0, 64);
            if ($name === '' || $ua === '') {
                continue;
            }
            $robots[] = [
                'name'   => $name,
                'ua'     => $ua,
                'fam'    => in_array($row['fam'] ?? '', $allowed_fams, true) ? $row['fam'] : 'search',
                'verify' => preg_replace('/[^a-z0-9. -]/i', '', stripslashes($row['verify'] ?? '')),
                'status' => ($row['status'] ?? '') === 'block' ? 'block' : 'allow',
            ];
        }
        $new_name = mb_substr(trim(stripslashes($_POST['new_robot_name'] ?? '')), 0, 64);
        $new_ua   = mb_substr(trim(stripslashes($_POST['new_robot_ua'] ?? '')), 0, 64);
        if ($new_name !== '' && $new_ua !== '') {
            $robots[] = [
                'name'   => $new_name,
                'ua'     => $new_ua,
                'fam'    => in_array($_POST['new_robot_fam'] ?? '', $allowed_fams, true) ? $_POST['new_robot_fam'] : 'search',
                'verify' => '',
                'status' => 'allow',
            ];
        }
        $conf_cur = ip_location_get_conf();
        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'robots_enabled' => isset($_POST['robots_enabled']) ? '1' : '0',
            'robots'         => $robots,
        ])));
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved#ipl-card-robots');
    } elseif ($_POST['action'] === 'save_url_config') {
        $keywords = trim(stripslashes($_POST['blocked_url_keywords'] ?? ''));
        $conf_cur = ip_location_get_conf();
        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'blocked_url_keywords'  => $keywords,
            'keyword_block_enabled' => isset($_POST['keyword_block_enabled']) ? '1' : '0',
        ])));
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved#ipl-card-keyword');
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
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved#ipl-card-download');
    } elseif ($_POST['action'] === 'save_bot_block_config') {
        $conf_cur = ip_location_get_conf();
        $bot_block_score_threshold = max(10, min(90, (int)($_POST['bot_block_score_threshold'] ?? 70)));
        // Indépendant du blocage .htaccess depuis v2.5.5 : les entrées auto sont appliquées
        // en PHP (cf. ip_location_blocklist_guard()).
        $bot_block_enabled = isset($_POST['bot_block_enabled']) ? '1' : '0';
        $new_conf = array_merge($conf_cur, [
            'bot_block_enabled'         => $bot_block_enabled,
            'bot_block_score_threshold' => $bot_block_score_threshold,
        ]);
        // Passage de coupé à allumé : mémorise l'instant, seule l'activité suspecte
        // postérieure pourra déclencher un blocage (cf. ip_location_get_bot_candidates()).
        if ($bot_block_enabled === '1' && $conf_cur['bot_block_enabled'] !== '1') {
            $new_conf['bot_block_enabled_at'] = date('Y-m-d H:i:s');
        }
        conf_update_param('ip_location', serialize($new_conf));
        // Libère aussitôt les IP auto-bloquées qui ne correspondent plus au nouveau
        // seuil, sans attendre leur expiration TTL (cf. ip_location_reconcile_auto_blocks()).
        ip_location_reconcile_auto_blocks($new_conf);
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved#ipl-card-auto');
    } elseif ($_POST['action'] === 'save_config') {
        // Bloc "Blocage par pays" (max_records a son propre bloc depuis v2.6.3)
        $blocked = strtoupper(preg_replace('/[^A-Za-z,]/', '', $_POST['blocked_countries'] ?? ''));
        $blocking_enabled = isset($_POST['blocking_enabled']) ? '1' : '0';
        $conf_cur = ip_location_get_conf();
        conf_update_param('ip_location', serialize(array_merge($conf_cur, [
            'blocked_countries' => $blocked,
            'blocking_enabled'  => $blocking_enabled,
        ])));
        redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=config_saved#ipl-card-country');
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
                redirect(get_root_url() . 'admin.php?page=plugin-ip_location' . ip_location_admin_return_qs() . '&msg=ip_blocked&ip=' . urlencode($ip));
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
            redirect(get_root_url() . 'admin.php?page=plugin-ip_location' . ip_location_admin_return_qs() . '&msg=ip_unblocked&ip=' . urlencode($ip));
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

// Migrations v2.5.5, une seule fois chacune. Faites ici et non dans
// maintain.class.php::update() : pendant update(), c'est encore l'ancien main.inc.php qui
// est chargé en mémoire (anciennes versions de ip_location_write_htaccess() et
// ip_location_get_bot_candidates()).
$ipl_conf_migr = ip_location_get_conf();
$ipl_conf_changed = false;

// 1. Réécrit le .htaccess pour en retirer les entrées 'auto' qu'y écrivaient les
//    versions précédentes (désormais appliquées en PHP, cf. ip_location_write_htaccess()).
if (empty($ipl_conf_migr['htaccess_manual_only'])) {
    if (ip_location_write_htaccess() !== false) {
        $ipl_conf_migr['htaccess_manual_only'] = '1';
        $ipl_conf_changed = true;
    }
}

// 2. Suppression du mode de blocage auto "is_bot direct" : le blocage auto se fait
//    toujours sur le score, avec le seuil déjà enregistré (le curseur était enregistré
//    même en mode is_bot). Les entrées auto posées par l'ancien mode qui n'atteignent
//    pas ce seuil sont libérées tout de suite, sans attendre leur expiration.
if (array_key_exists('bot_block_mode', $ipl_conf_migr)) {
    $ipl_was_is_bot = $ipl_conf_migr['bot_block_mode'] === 'is_bot';
    unset($ipl_conf_migr['bot_block_mode']);
    $ipl_conf_changed = true;
    if ($ipl_was_is_bot) {
        ip_location_reconcile_auto_blocks($ipl_conf_migr);
    }
}

if ($ipl_conf_changed) {
    conf_update_param('ip_location', serialize($ipl_conf_migr));
}

// 3. Colonne block_reason (v2.6.1) : normalement créée par maintain.class.php::update(),
//    mais filet de sécurité si les fichiers ont été copiés sans passer par la mise à jour
//    Piwigo (FTP, copie de dev) — sans elle, tout INSERT du journal échouerait.
$ipl_cols = [];
$ipl_r = pwg_query('SHOW COLUMNS FROM ' . $prefixeTable . 'ip_location_log');
while ($ipl_row = pwg_db_fetch_row($ipl_r)) $ipl_cols[] = $ipl_row[0];
if (!in_array('block_reason', $ipl_cols)) {
    pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_log ADD COLUMN block_reason VARCHAR(16) DEFAULT NULL');
}
unset($ipl_cols, $ipl_r, $ipl_row);

// 4. Table de vérification DNS des robots (v2.6.2) — même filet de sécurité ; même
//    définition que maintain.class.php::robot_check_table_sql() (non chargée ici).
pwg_query('
CREATE TABLE IF NOT EXISTS ' . $prefixeTable . 'ip_location_robot_check (
  ip          VARCHAR(45) PRIMARY KEY,
  robot       VARCHAR(64) NOT NULL,
  verified    TINYINT(1)  NOT NULL DEFAULT 0,
  checked_at  DATETIME    NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
unset($ipl_conf_migr, $ipl_conf_changed, $ipl_was_is_bot);

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

$filter = isset($_GET['filter']) && in_array($_GET['filter'], ['normal','bot','robots','blocked']) ? $_GET['filter'] : 'all';
$country_filter = isset($_GET['country']) ? strtoupper(trim($_GET['country'])) : '';
if (!preg_match('/^[A-Z]{0,2}$/', $country_filter)) $country_filter = '';

$date_from = isset($_GET['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_from']) ? $_GET['date_from'] : '';
$date_to   = isset($_GET['date_to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_to'])   ? $_GET['date_to']   : '';
$ip_filter = isset($_GET['ip_filter']) ? preg_replace('/[^0-9a-fA-F.:\/]/', '', trim($_GET['ip_filter'])) : '';
// Motif de blocage (v2.6.10) : un motif de refus (block_reason), "listed" (servi avant le
// blocage de son IP / robot, classé Bloqués par l'état actuel) ou "unknown" (refus
// antérieur à la 2.6.1, sans motif enregistré).
$reason_keys   = ['country', 'keyword', 'ip', 'auto', 'robot', 'download', 'listed', 'unknown'];
$reason_filter = isset($_GET['reason']) && in_array($_GET['reason'], $reason_keys, true) ? $_GET['reason'] : '';

// Le Journal reflète l'état actuel (v2.6.8, après un essai "historique figé" en v2.6.1) :
// une ligne servie avant le blocage de son IP / robot passe dans "Bloqués" (badge "Bloquée
// depuis le …"), tant que le levier correspondant est allumé. Les refus réels portent leur
// motif (block_reason).
// Filtres hors catégorie (pays, dates, IP) : partagés par les compteurs cliquables et le
// tableau. "robots" (v2.6.6) = accès servis à un robot autorisé et authentique.
$allowed_robot_sql = ip_location_allowed_robot_sql($prefixeTable);
$base_parts = [];
if ($country_filter !== '') $base_parts[] = "country_code = '" . pwg_db_real_escape_string($country_filter) . "'";
if ($date_from !== '') $base_parts[] = "visit_date >= '" . pwg_db_real_escape_string($date_from) . " 00:00:00'";
if ($date_to   !== '') $base_parts[] = "visit_date <= '" . pwg_db_real_escape_string($date_to)   . " 23:59:59'";
if ($ip_filter !== '')  $base_parts[] = "ip LIKE '" . pwg_db_real_escape_string($ip_filter) . "%'";
// "Bloqués" = refus réels OU IP/robot actuellement bloqué (le Journal reflète l'état
// actuel depuis v2.6.8 ; seuls les leviers allumés comptent, cf. ip_location_currently_blocked_sql()).
$currently_blocked_sql = ip_location_currently_blocked_sql($prefixeTable);

// Filtre "Motif" : conditions par motif, et effectif de chacun (mêmes filtres pays /
// dates / IP) pour l'afficher dans la liste déroulante.
$reason_sql = [
    'listed'  => 'is_blocked = 0 AND ' . $currently_blocked_sql,
    'unknown' => 'is_blocked = 1 AND block_reason IS NULL',
];
foreach (['country', 'keyword', 'ip', 'auto', 'robot', 'download'] as $rk) {
    $reason_sql[$rk] = "is_blocked = 1 AND block_reason = '" . $rk . "'";
}
$reason_cols = [];
foreach ($reason_sql as $rk => $sql) {
    $reason_cols[] = 'SUM(' . $sql . ') AS r_' . $rk;
}
$r = pwg_query('SELECT ' . implode(', ', $reason_cols) . ' FROM ' . $prefixeTable . 'ip_location_log'
    . (empty($base_parts) ? '' : ' WHERE ' . implode(' AND ', $base_parts)));
$reason_counts = [];
$row = pwg_db_fetch_assoc($r) ?: [];
foreach ($reason_keys as $rk) {
    $reason_counts[$rk] = (int)($row['r_' . $rk] ?? 0);
}
if ($reason_filter !== '') {
    $base_parts[] = '(' . $reason_sql[$reason_filter] . ')';
}

$category_sql = [
    'all'     => '1',
    'normal'  => 'is_bot = 0 AND is_blocked = 0 AND NOT ' . $currently_blocked_sql,
    'bot'     => 'is_bot = 1 AND is_blocked = 0 AND NOT ' . $currently_blocked_sql,
    'robots'  => 'is_blocked = 0 AND ' . $allowed_robot_sql . ' AND NOT ' . $currently_blocked_sql,
    'blocked' => '(is_blocked = 1 OR ' . $currently_blocked_sql . ')',
];
$where_parts = $base_parts;
if ($filter !== 'all') $where_parts[] = $category_sql[$filter];
$filter_where = empty($where_parts) ? '' : 'WHERE ' . implode(' AND ', $where_parts);

// Compteurs par catégorie (mêmes filtres hors catégorie), en une requête
$journal_counts = ['all' => 0, 'normal' => 0, 'bot' => 0, 'robots' => 0, 'blocked' => 0];
$count_cols = [];
foreach ($category_sql as $cat => $sql) {
    $count_cols[] = 'SUM(' . $sql . ') AS c_' . $cat;
}
$r = pwg_query('SELECT ' . implode(', ', $count_cols) . ' FROM ' . $prefixeTable . 'ip_location_log'
    . (empty($base_parts) ? '' : ' WHERE ' . implode(' AND ', $base_parts)));
if ($row = pwg_db_fetch_assoc($r)) {
    foreach ($journal_counts as $cat => $v) {
        $journal_counts[$cat] = (int)$row['c_' . $cat];
    }
}
$total_visits = $journal_counts[$filter];
$total_pages  = max(1, ceil($total_visits / $per_page));

$logs = [];
$result = pwg_query('
SELECT id, visit_date, ip, country, city, url, user_agent, is_bot, is_blocked, bot_score, block_reason
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
// Sous-onglets de Configuration (v2.6.6) : Réglages (blocs) / Journal des accès. Tout
// paramètre de filtre du Journal ouvre directement le Journal.
$sub = 'settings';
if ($tab === 'config' && (($_GET['sub'] ?? '') === 'journal'
    || isset($_GET['filter']) || isset($_GET['country']) || isset($_GET['date_from'])
    || isset($_GET['date_to']) || isset($_GET['ip_filter']) || isset($_GET['reason']) || isset($_GET['pnum']))) {
    $sub = 'journal';
}

// Liens du Journal : filtres hors catégorie (pays, dates, IP) à propager, vue complète
// courante (retour après Bloquer / Débloquer) et pagination fenêtrée (la prod dépasse
// 300 pages de 50 : on n'affiche plus tous les numéros).
$journal_qs = '&sub=journal';
if ($country_filter !== '') $journal_qs .= '&country=' . urlencode($country_filter);
if ($date_from !== '')      $journal_qs .= '&date_from=' . urlencode($date_from);
if ($date_to !== '')        $journal_qs .= '&date_to=' . urlencode($date_to);
if ($ip_filter !== '')      $journal_qs .= '&ip_filter=' . urlencode($ip_filter);
if ($reason_filter !== '')  $journal_qs .= '&reason=' . urlencode($reason_filter);
$return_qs = ltrim($journal_qs, '&') . ($filter !== 'all' ? '&filter=' . $filter : '') . '&pnum=' . $current_page;
$pager = [];
if ($total_pages > 1) {
    $window = array_unique(array_filter(array_merge(
        [1, $total_pages], range(max(1, $current_page - 2), min($total_pages, $current_page + 2))
    )));
    sort($window);
    $prev = 0;
    foreach ($window as $p) {
        if ($prev && $p > $prev + 1) {
            $pager[] = ['gap' => true];
        }
        $pager[] = ['num' => $p, 'current' => $p == $current_page];
        $prev = $p;
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'cache_purged') $page['infos'][] = l10n('Cache de géolocalisation vidé.');
    if ($_GET['msg'] === 'purged')       $page['infos'][] = l10n('Logs supprimés.');
    if ($_GET['msg'] === 'config_saved') $page['infos'][] = l10n('Configuration enregistrée.');
    if ($_GET['msg'] === 'htaccess_error')   $page['errors'][] = l10n('.htaccess non accessible en écriture.');
    if ($_GET['msg'] === 'htaccess_missing') $page['errors'][] = l10n('Fichier .htaccess inexistant : vous devez le créer manuellement à la racine de Piwigo.');
    if ($_GET['msg'] === 'ip_blocked')      $page['infos'][] = sprintf(l10n('IP %s bloquée.'), $_GET['ip'] ?? '');
    if ($_GET['msg'] === 'ip_unblocked')    $page['infos'][] = sprintf(l10n('IP %s débloquée.'), $_GET['ip'] ?? '');
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

// Préfixes "A.B." des plages /16 manuelles, pour reconnaître les lignes qu'elles couvrent
// (même règle que ip_location_in_blocklist_sql()).
$blocked_range_prefixes = [];   // préfixe "A.B." => date de blocage de la plage
foreach ($blocklist_manual as $b) {
    $prefix = ip_location_manual_range_prefix($b['ip']);
    if ($prefix !== null) {
        $blocked_range_prefixes[$prefix] = $b['blocked_at'];
    }
}
// Entrées de blocage par IP exacte (badge "Bloquée depuis le …" du Journal)
$blocklist_by_ip = array_column($blocklist, null, 'ip');
$ipl_conf_now = ip_location_get_conf();
$fmt_since = function ($datetime) {
    return $datetime ? date('d/m/Y', strtotime($datetime)) : '';
};

// Marquer les entrées du log dont l'IP est en blocklist (exacte) ou couverte par une
// plage /16 manuelle — pour une ligne couverte par une plage, pas de bouton "Retirer" :
// retirer l'IP isolée ne retirerait pas la plage (retrait depuis la Configuration).
// Motif d'un refus (block_reason, v2.6.1) affiché à côté du badge BLOQUÉ ; NULL pour les
// lignes antérieures à la 2.6.1 (motif inconnu).
$block_reason_labels = [
    'country'  => l10n('pays'),
    'keyword'  => l10n('mot-clé'),
    'ip'       => l10n('liste IP'),
    'auto'     => l10n('blocage auto'),
    'robot'    => l10n('robot'),
    'download' => l10n('téléchargement'),
];
// IP de la page démasquées comme faux robots (vérification DNS), pour ne pas leur
// afficher le badge du robot qu'elles prétendent être.
$page_spoof_ips = [];
if (!empty($logs)) {
    $ips_sql = implode(',', array_map(function ($l) { return '\'' . pwg_db_real_escape_string($l['ip']) . '\''; }, $logs));
    $r = pwg_query('SELECT ip FROM ' . $prefixeTable . 'ip_location_robot_check WHERE verified = 0 AND ip IN (' . $ips_sql . ')');
    while ($row = pwg_db_fetch_row($r)) {
        $page_spoof_ips[$row[0]] = true;
    }
}
$robots_enabled_now = ip_location_get_conf()['robots_enabled'] === '1';
foreach ($logs as &$log) {
    $log['block_reason_label'] = $block_reason_labels[$log['block_reason'] ?? ''] ?? '';
    // Badge "Googlebot ✓" : robot autorisé de la liste, IP non démasquée
    $log['robot_name'] = '';
    if ($robots_enabled_now && ($rb = ip_location_match_robot($log['user_agent'])) && ($rb['status'] ?? 'allow') === 'allow'
        && empty($page_spoof_ips[$log['ip']])) {
        $log['robot_name'] = $rb['name'];
    }
    $log['is_spoof'] = !empty($page_spoof_ips[$log['ip']]);
    // Plage /16 proposée dans le menu Actions (IPv4 uniquement)
    $log['range16'] = preg_match('/^(\d+\.\d+)\.\d+\.\d+$/', $log['ip'], $m) ? $m[1] . '.0.0/16' : '';
    $log['in_blocklist'] = in_array($log['ip'], $blocklist_ips);
    $log['is_exempt']    = in_array($log['ip'], $exempt_ips);
    $log['in_range']     = false;
    $log['listed_since'] = '';
    if ($log['in_blocklist']) {
        // Même règle que ip_location_currently_blocked_sql() : levier de l'entrée allumé
        $entry = $blocklist_by_ip[$log['ip']];
        $active = $entry['origin'] === 'auto'
            ? ($ipl_conf_now['bot_block_enabled'] === '1' && (empty($entry['expires_at']) || $entry['expires_at'] >= date('Y-m-d')))
            : $ipl_conf_now['htaccess_enabled'] === '1';
        if ($active) {
            $log['listed_since'] = $fmt_since($entry['blocked_at']);
        }
    } else {
        foreach ($blocked_range_prefixes as $prefix => $since) {
            if (strpos($log['ip'], $prefix) === 0) {
                $log['in_range'] = true;
                if ($ipl_conf_now['htaccess_enabled'] === '1') {
                    $log['listed_since'] = $fmt_since($since);
                }
                break;
            }
        }
    }
    // Robot marqué "Bloqué" (bloc Robots allumé) : ses accès passent dans "Bloqués"
    $log['robot_blocked'] = '';
    if ($robots_enabled_now && ($rb = ip_location_match_robot($log['user_agent'])) && ($rb['status'] ?? 'allow') === 'block') {
        $log['robot_blocked'] = $rb['name'];
    }
}
unset($log);

// ── Onglet Configuration › Réglages en blocs (v2.6.3) ─────────────────────────
// Données propres aux blocs : noms de pays, refus sur 7 jours par entrée/mot-clé,
// statistiques des robots, impact du seuil du blocage automatique.

$cfg_blocks = [];
if ($tab === 'config' && $sub === 'settings') {
    // Noms de pays connus (journal) pour afficher "United States (US)" dans les pastilles
    $country_names = [];
    $r = pwg_query('SELECT country_code, MIN(country) FROM ' . $prefixeTable . 'ip_location_log
  WHERE country_code != \'\' GROUP BY country_code');
    while ($row = pwg_db_fetch_row($r)) {
        $country_names[$row[0]] = $row[1];
    }
    $country_chips = function ($csv) use ($country_names) {
        $out = [];
        foreach (array_filter(array_map('trim', explode(',', strtoupper($csv)))) as $cc) {
            $out[] = ['code' => $cc, 'name' => $country_names[$cc] ?? $cc];
        }
        return $out;
    };

    // Refus sur 7 jours par motif (colonne block_reason, v2.6.1)
    $refusals_7d = function ($reason, $extra_where = '') use ($prefixeTable) {
        $r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log
  WHERE is_blocked = 1 AND block_reason = \'' . $reason . '\'
    AND visit_date >= NOW() - INTERVAL 7 DAY' . $extra_where);
        list($n) = pwg_db_fetch_row($r);
        return (int)$n;
    };

    // Blocage par IP : refus par entrée (plage /16 : préfixe "A.B.")
    $manual_rows = [];
    foreach ($blocklist_manual as $b) {
        $prefix = ip_location_manual_range_prefix($b['ip']);
        $match = $prefix !== null
            ? ' AND ip LIKE \'' . pwg_db_real_escape_string($prefix) . '%\''
            : ' AND ip = \'' . pwg_db_real_escape_string($b['ip']) . '\'';
        $b['is_range'] = $prefix !== null;
        $b['refusals'] = $refusals_7d('ip', $match);
        $manual_rows[] = $b;
    }

    // Mots-clés : refus par mot
    $keyword_rows = [];
    foreach (array_filter(array_map('trim', explode("\n", $plugin_conf['blocked_url_keywords']))) as $kw) {
        $keyword_rows[] = [
            'word'     => $kw,
            'refusals' => $refusals_7d('keyword', ' AND url LIKE \'%' . pwg_db_real_escape_string($kw) . '%\''),
        ];
    }

    // Pays bloqués : refus par pays
    $country_rows = [];
    foreach ($country_chips($plugin_conf['blocked_countries']) as $c) {
        $c['refusals'] = $refusals_7d('country', ' AND country_code = \'' . pwg_db_real_escape_string($c['code']) . '\'');
        $country_rows[] = $c;
    }

    // Robots : passages sur 7 jours et dernier passage, en une seule requête
    $robots = ip_location_get_robots();
    $robot_rows = [];
    if (!empty($robots)) {
        $cols = [];
        foreach ($robots as $i => $rb) {
            $like = 'user_agent LIKE \'%' . pwg_db_real_escape_string($rb['ua']) . '%\'';
            $cols[] = 'SUM(' . $like . ') AS n' . $i . ', MAX(CASE WHEN ' . $like . ' THEN visit_date END) AS d' . $i;
        }
        $r = pwg_query('SELECT ' . implode(', ', $cols) . ' FROM ' . $prefixeTable . 'ip_location_log
  WHERE visit_date >= NOW() - INTERVAL 7 DAY');
        $robot_stats = pwg_db_fetch_assoc($r) ?: [];
        foreach ($robots as $i => $rb) {
            $rb['seen'] = (int)($robot_stats['n' . $i] ?? 0);
            $rb['last'] = !empty($robot_stats['d' . $i]) ? substr($robot_stats['d' . $i], 0, 10) : '';
            $rb['verifiable'] = trim($rb['verify'] ?? '') !== '';
            $robot_rows[] = $rb;
        }
    }
    $robots_blocked = count(array_filter($robots, function ($rb) { return ($rb['status'] ?? 'allow') === 'block'; }));
    $r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_robot_check
  WHERE verified = 0 AND checked_at >= NOW() - INTERVAL 7 DAY');
    list($robots_spoofed) = pwg_db_fetch_row($r);
    $allowed_robot_sql = ip_location_allowed_robot_sql($prefixeTable);
    $robots_seen_7d = 0;
    if ($allowed_robot_sql !== '0') {
        $r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log
  WHERE visit_date >= NOW() - INTERVAL 7 DAY AND ' . $allowed_robot_sql);
        list($robots_seen_7d) = pwg_db_fetch_row($r);
    }

    // Blocage automatique : score max par IP sur la fenêtre "récente" (curseur en direct)
    $recent_hours = max(1, (int)$conf['ip_location_auto_block_recent_hours']);
    $recent_scores = [];
    $r = pwg_query('SELECT ip, MAX(bot_score) FROM ' . $prefixeTable . 'ip_location_log
  WHERE visit_date >= NOW() - INTERVAL ' . $recent_hours . ' HOUR AND bot_score > 0
  GROUP BY ip');
    while ($row = pwg_db_fetch_row($r)) {
        if (ip_location_is_public_ip($row[0])) {
            $recent_scores[] = (int)$row[1];
        }
    }

    // Conservation : bornes du journal
    $r = pwg_query('SELECT MIN(visit_date), MAX(visit_date) FROM ' . $prefixeTable . 'ip_location_log');
    list($log_first, $log_last) = pwg_db_fetch_row($r);

    // Widget Visiteurs : nombre de pays des visites comptées
    $visitor_countries = count(array_unique(array_column($visitor_detail_rows, 'country')));

    // Bandeau de mode : 'on' = interrupteur du bloc (pastille allumée). Le décompte des
    // leviers de blocage ne retient Robots que si au moins un robot est bloqué (sinon il
    // ne refuse rien), et jamais le filtre des téléchargements (filtre à part).
    $levers = [
        ['key' => 'robots',   'label' => l10n('Robots'),          'on' => $plugin_conf['robots_enabled'] === '1'],
        ['key' => 'manual',   'label' => l10n('Par IP'),          'on' => $plugin_conf['htaccess_enabled'] === '1'],
        ['key' => 'country',  'label' => l10n('Par pays'),        'on' => $plugin_conf['blocking_enabled'] === '1'],
        ['key' => 'keyword',  'label' => l10n('Par mot-clé'),     'on' => $plugin_conf['keyword_block_enabled'] === '1'],
        ['key' => 'auto',     'label' => l10n('Automatique'),     'on' => $plugin_conf['bot_block_enabled'] === '1'],
        ['key' => 'download', 'label' => l10n('Téléchargements'), 'on' => $plugin_conf['download_filter_enabled'] === '1'],
    ];
    $levers_on = count(array_filter($levers, function ($l) use ($robots_blocked) {
        if (!$l['on'] || $l['key'] === 'download') return false;
        return $l['key'] !== 'robots' || $robots_blocked > 0;
    }));

    // Googlebot et le blocage des États-Unis
    $us_blocked = $plugin_conf['blocking_enabled'] === '1'
        && in_array('US', array_map('trim', explode(',', strtoupper($plugin_conf['blocked_countries']))), true);
    $googlebot_allowed = false;
    foreach ($robots as $rb) {
        if (stripos($rb['ua'], 'Googlebot') !== false && ($rb['status'] ?? 'allow') === 'allow') {
            $googlebot_allowed = true;
        }
    }
    $google_warning = '';
    if ($us_blocked && $plugin_conf['robots_enabled'] !== '1') {
        $google_warning = 'robots_off';
    } elseif ($us_blocked && !$googlebot_allowed) {
        $google_warning = 'googlebot_not_allowed';
    }

    $cfg_blocks = [
        'LEVERS'             => $levers,
        'LEVERS_ON'          => $levers_on,
        'MANUAL_ROWS'        => $manual_rows,
        'KEYWORD_ROWS'       => $keyword_rows,
        'COUNTRY_ROWS'       => $country_rows,
        'DOWNLOAD_ROWS'      => $country_chips($plugin_conf['download_allowed_countries']),
        'ROBOT_ROWS'         => $robot_rows,
        'ROBOTS_ENABLED'     => $plugin_conf['robots_enabled'] === '1',
        'ROBOTS_BLOCKED'     => $robots_blocked,
        'ROBOTS_ALLOWED'     => count($robots) - $robots_blocked,
        'ROBOTS_SEEN_7D'     => (int)$robots_seen_7d,
        'ROBOTS_SPOOFED'     => (int)$robots_spoofed,
        'RECENT_SCORES_JSON' => json_encode($recent_scores),
        'RECENT_HOURS'       => $recent_hours,
        'EXEMPT_COUNT'       => count($exempt_ips),
        'LOG_FIRST'          => $log_first ? substr($log_first, 0, 10) : '',
        'LOG_LAST'           => $log_last ? substr($log_last, 0, 10) : '',
        'VISITOR_COUNTRIES'  => $visitor_countries,
        'GOOGLE_WARNING'     => $google_warning,
        'AUTO_TTL_DAYS'      => (int)$conf['ip_location_auto_block_ttl_days'],
    ];
}

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
    'KEYWORD_BLOCK_ENABLED' => $plugin_conf['keyword_block_enabled'] === '1',
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
    'SUB'           => $sub,
    'JOURNAL_COUNTS'=> $journal_counts,
    'REASON_FILTER' => $reason_filter,
    'REASON_COUNTS' => $reason_counts,
    'JOURNAL_QS'    => $journal_qs,
    'RETURN_QS'     => $return_qs,
    'PAGER'         => $pager,
    'TOTAL_ALL'     => $total_all,
    'TOTAL_BOTS'    => $total_bots,
    'TOTAL_BLOCKED' => $total_blocked,
    'TOTAL_NORMAL'  => $total_normal,
]);

if (!empty($cfg_blocks)) {
    $template->assign($cfg_blocks);
}
$template->set_filename('ip_location_tab', $tab_tpl);
$template->assign_var_from_handle('TAB_CONTENT', 'ip_location_tab');

$template->set_filename('ip_location_admin', IP_LOCATION_PATH . 'template/admin.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'ip_location_admin');
