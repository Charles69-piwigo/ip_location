<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function plugin_install()
{
}

function plugin_activate($plugin_id, $plugin_version, &$errors)
{
    global $prefixeTable;

    // Migration v1.3 → v1.4 : regrouper les 5 anciennes entrées _config en une seule
    $old_params = [
        'ip_location_blocked_countries',
        'ip_location_whitelist',
        'ip_location_blocking_enabled',
        'ip_location_htaccess_enabled',
        'ip_location_max_records',
    ];
    $has_old = false;
    foreach ($old_params as $param) {
        if (conf_get_param($param, null) !== null) { $has_old = true; break; }
    }
    if ($has_old) {
        $migrated = [
            'blocked_countries' => conf_get_param('ip_location_blocked_countries', ''),
            'whitelist'         => conf_get_param('ip_location_whitelist', ''),
            'blocking_enabled'  => conf_get_param('ip_location_blocking_enabled', '0'),
            'htaccess_enabled'  => conf_get_param('ip_location_htaccess_enabled', '0'),
            'max_records'       => (int)conf_get_param('ip_location_max_records', 10000),
        ];
        conf_update_param('ip_location', serialize($migrated));
        foreach ($old_params as $param) {
            pwg_query("DELETE FROM " . CONFIG_TABLE . " WHERE param = '" . $param . "'");
        }
    }

    pwg_query('
CREATE TABLE IF NOT EXISTS ' . $prefixeTable . 'ip_location_log (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  ip           VARCHAR(45)  NOT NULL,
  country      VARCHAR(64),
  country_code CHAR(2),
  city         VARCHAR(64),
  url          VARCHAR(512),
  user_agent   VARCHAR(512),
  is_bot       TINYINT(1)   NOT NULL DEFAULT 0,
  is_blocked   TINYINT(1)   NOT NULL DEFAULT 0,
  visit_date   DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

    pwg_query('
CREATE TABLE IF NOT EXISTS ' . $prefixeTable . 'ip_location_cache (
  ip           VARCHAR(45)  PRIMARY KEY,
  country      VARCHAR(64),
  country_code CHAR(2),
  city         VARCHAR(64),
  resolved_at  DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

    pwg_query('
CREATE TABLE IF NOT EXISTS ' . $prefixeTable . 'ip_location_blocklist (
  ip           VARCHAR(45)  PRIMARY KEY,
  reason       VARCHAR(255),
  blocked_at   DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

function plugin_deactivate()
{
    // Les tables sont conservées lors d'une simple désactivation
}

function plugin_uninstall()
{
    global $prefixeTable;

    pwg_query('DROP TABLE IF EXISTS ' . $prefixeTable . 'ip_location_log');
    pwg_query('DROP TABLE IF EXISTS ' . $prefixeTable . 'ip_location_cache');
    pwg_query('DROP TABLE IF EXISTS ' . $prefixeTable . 'ip_location_blocklist');
    pwg_query("DELETE FROM " . CONFIG_TABLE . " WHERE param = 'ip_location'");
}
