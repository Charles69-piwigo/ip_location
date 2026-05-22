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
        $visitors_period  = in_array($_POST['visitors_period'] ?? '', ['week', 'month', 'quarter'])
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
                pwg_query('
INSERT INTO ' . $prefixeTable . 'ip_location_blocklist (ip, country, city, blocked_at)
  VALUES (
    \'' . pwg_db_real_escape_string($ip) . '\',
    \'' . pwg_db_real_escape_string($country) . '\',
    \'' . pwg_db_real_escape_string($city) . '\',
    NOW()
  )
  ON DUPLICATE KEY UPDATE blocked_at = NOW()');
                ip_location_write_htaccess();
                redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=ip_blocked&ip=' . urlencode($ip));
            }
        }
    } elseif ($_POST['action'] === 'unblock_ip') {
        $ip = trim($_POST['ip'] ?? '');
        if ($ip) {
            pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_blocklist WHERE ip = \'' . pwg_db_real_escape_string($ip) . '\'');
            ip_location_write_htaccess();
            redirect(get_root_url() . 'admin.php?page=plugin-ip_location&msg=ip_unblocked&ip=' . urlencode($ip));
        }
    }
}

// ── Compteurs globaux ─────────────────────────────────────────────────────────

$r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log');
list($total_all) = pwg_db_fetch_row($r);

$r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log WHERE is_bot = 1');
list($total_bots) = pwg_db_fetch_row($r);

$r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log WHERE is_blocked = 1');
list($total_blocked) = pwg_db_fetch_row($r);

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

$where_parts = [];
if ($filter === 'normal')  $where_parts[] = 'is_bot = 0 AND is_blocked = 0';
if ($filter === 'bot')     $where_parts[] = 'is_bot = 1';
if ($filter === 'blocked') $where_parts[] = 'is_blocked = 1';
if ($country_filter !== '') $where_parts[] = "country_code = '" . pwg_db_real_escape_string($country_filter) . "'";
if ($date_from !== '') $where_parts[] = "visit_date >= '" . pwg_db_real_escape_string($date_from) . " 00:00:00'";
if ($date_to   !== '') $where_parts[] = "visit_date <= '" . pwg_db_real_escape_string($date_to)   . " 23:59:59'";
$filter_where = empty($where_parts) ? '' : 'WHERE ' . implode(' AND ', $where_parts);

$total_result = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log ' . $filter_where);
list($total_visits) = pwg_db_fetch_row($total_result);
$total_pages = max(1, ceil($total_visits / $per_page));

$logs = [];
$result = pwg_query('
SELECT id, visit_date, ip, country, city, url, user_agent, is_bot, is_blocked
  FROM ' . $prefixeTable . 'ip_location_log
  ' . $filter_where . '
  ORDER BY visit_date DESC
  LIMIT ' . $per_page . ' OFFSET ' . $offset);
while ($row = pwg_db_fetch_assoc($result)) {
    $logs[] = $row;
}

// ── Rendu via template Piwigo ─────────────────────────────────────────────────

$tab     = isset($_GET['tab']) && $_GET['tab'] === 'help' ? 'help' : 'config';
$tab_tpl = IP_LOCATION_PATH . 'template/' . $tab . '.tpl';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'cache_purged') $page['infos'][] = l10n('Cache de géolocalisation vidé.');
    if ($_GET['msg'] === 'purged')       $page['infos'][] = l10n('Logs supprimés.');
    if ($_GET['msg'] === 'config_saved') $page['infos'][] = l10n('Configuration enregistrée.');
    if ($_GET['msg'] === 'htaccess_error')   $page['errors'][] = l10n('.htaccess non accessible en écriture.');
    if ($_GET['msg'] === 'htaccess_missing') $page['errors'][] = l10n('Fichier .htaccess inexistant : vous devez le créer manuellement à la racine de Piwigo.');
    if ($_GET['msg'] === 'ip_blocked')      $page['infos'][] = sprintf(l10n('IP %s ajoutée au .htaccess.'), $_GET['ip'] ?? '');
    if ($_GET['msg'] === 'ip_unblocked')    $page['infos'][] = sprintf(l10n('IP %s retirée du .htaccess.'), $_GET['ip'] ?? '');
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

// ── Blocklist ip_location_blocklist ───────────────────────────────────────────

$blocklist = [];
$result = pwg_query('SELECT ip, country, city, blocked_at FROM ' . $prefixeTable . 'ip_location_blocklist ORDER BY blocked_at DESC');
while ($row = pwg_db_fetch_assoc($result)) {
    $blocklist[] = $row;
}
$blocklist_ips = array_column($blocklist, 'ip');

// Marquer les entrées du log dont l'IP est en blocklist
foreach ($logs as &$log) {
    $log['in_blocklist'] = in_array($log['ip'], $blocklist_ips);
}
unset($log);

$template->assign([
    'VISITORS_ENABLED'      => $visitors_enabled,
    'VISITORS_PERIOD'       => $visitors_period,
    'BLOCKED_COUNTRIES'     => $blocked_countries,
    'BLOCKED_URL_KEYWORDS'  => $blocked_url_keywords,
    'WHITELIST_IPS'         => $whitelist_ips,
    'BLOCKING_ENABLED'   => $blocking_enabled,
    'HTACCESS_ENABLED'   => $htaccess_enabled,
    'SERVER_IS_NGINX'    => $server_is_nginx,
    'MAX_RECORDS'        => $max_records,
    'BLOCKLIST'          => $blocklist,
    'STATS'              => $stats,
    'LOGS'               => $logs,
    'TOTAL_PAGES'        => $total_pages,
    'CURRENT_PAGE'       => $current_page,
    'BASE_URL'           => get_root_url() . 'admin.php?page=plugin-ip_location',
    'FILTER'             => $filter,
    'COUNTRY_FILTER'     => $country_filter,
    'DATE_FROM'          => $date_from,
    'DATE_TO'            => $date_to,
    'COUNTRIES'          => $countries,
    'TAB'           => $tab,
    'TOTAL_ALL'     => $total_all,
    'TOTAL_BOTS'    => $total_bots,
    'TOTAL_BLOCKED' => $total_blocked,
]);

$template->set_filename('ip_location_tab', $tab_tpl);
$template->assign_var_from_handle('TAB_CONTENT', 'ip_location_tab');

$template->set_filename('ip_location_admin', IP_LOCATION_PATH . 'template/admin.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'ip_location_admin');
