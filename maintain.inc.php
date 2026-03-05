<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function plugin_install()
{
}

function plugin_activate($plugin_id, $plugin_version, &$errors)
{
    global $prefixeTable;

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
}
