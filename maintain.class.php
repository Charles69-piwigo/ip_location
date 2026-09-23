<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

class ip_location_maintain extends PluginMaintain
{
    function install($plugin_version, &$errors = array())
    {
    }

    function activate($plugin_version, &$errors = array())
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
                'max_records'       => (int)conf_get_param('ip_location_max_records', 50000),
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
  log_type     VARCHAR(16)  DEFAULT NULL,
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
  country      VARCHAR(64),
  city         VARCHAR(64),
  blocked_at   DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');

        // Migration v1.4 → v1.5 : remplacer reason par country + city
        $cols = [];
        $r = pwg_query('SHOW COLUMNS FROM ' . $prefixeTable . 'ip_location_blocklist');
        while ($row = pwg_db_fetch_row($r)) $cols[] = $row[0];
        if (in_array('reason', $cols)) {
            pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_blocklist DROP COLUMN reason');
        }
        if (!in_array('country', $cols)) {
            pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_blocklist ADD COLUMN country VARCHAR(64) DEFAULT NULL AFTER ip');
        }
        if (!in_array('city', $cols)) {
            pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_blocklist ADD COLUMN city VARCHAR(64) DEFAULT NULL AFTER country');
        }

        // Migration v2.1a → v2.2 : ajout de la colonne log_type (filtre pays téléchargements)
        $cols = [];
        $r = pwg_query('SHOW COLUMNS FROM ' . $prefixeTable . 'ip_location_log');
        while ($row = pwg_db_fetch_row($r)) $cols[] = $row[0];
        if (!in_array('log_type', $cols)) {
            pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_log
  ADD COLUMN log_type VARCHAR(16) DEFAULT NULL');
        }

        // Migration v2.2 → v2.3 : index pour la passe de classification différée,
        // le panneau visiteurs et la purge (log_visit ne scanne plus la table à chaud)
        $idx = [];
        $r = pwg_query('SHOW INDEX FROM ' . $prefixeTable . 'ip_location_log');
        while ($row = pwg_db_fetch_assoc($r)) $idx[$row['Key_name']] = true;
        if (!isset($idx['idx_url_date'])) {
            pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_log
  ADD INDEX idx_url_date (url(191), visit_date)');
        }
        if (!isset($idx['idx_visit_date'])) {
            pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_log
  ADD INDEX idx_visit_date (visit_date)');
        }

        // Migration v2.3 → v2.3b : index pour les requêtes par série de l'onglet
        // Statistiques dynamiques (chaque série filtre systématiquement is_bot/is_blocked)
        if (!isset($idx['idx_bot_blocked_date'])) {
            pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_log
  ADD INDEX idx_bot_blocked_date (is_bot, is_blocked, visit_date)');
        }

        // Migration v2.4 → v2.5 : score de suspicion bot (4ème levier de blocage)
        $cols = [];
        $r = pwg_query('SHOW COLUMNS FROM ' . $prefixeTable . 'ip_location_log');
        while ($row = pwg_db_fetch_row($r)) $cols[] = $row[0];
        if (!in_array('bot_score', $cols)) {
            pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_log
  ADD COLUMN bot_score SMALLINT UNSIGNED NOT NULL DEFAULT 0');
        }

        // Migration v2.6 → v2.6.1 : motif d'un accès refusé (country, keyword, ip, auto,
        // robot, download) — "Bloqués" = refus réels, journalisés avec leur motif.
        if (!in_array('block_reason', $cols)) {
            pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_log
  ADD COLUMN block_reason VARCHAR(16) DEFAULT NULL');
        }

        // Migration v2.6.1 → v2.6.2 : cache de vérification DNS des robots d'indexation
        pwg_query(ip_location_maintain::robot_check_table_sql($prefixeTable));
    }

    /**
     * CREATE TABLE du cache de vérification des robots (partagé avec le filet de sécurité
     * d'admin.php, pour une copie de fichiers sans passer par la mise à jour Piwigo).
     */
    static function robot_check_table_sql($prefixeTable)
    {
        return '
CREATE TABLE IF NOT EXISTS ' . $prefixeTable . 'ip_location_robot_check (
  ip          VARCHAR(45) PRIMARY KEY,
  robot       VARCHAR(64) NOT NULL,
  verified    TINYINT(1)  NOT NULL DEFAULT 0,
  checked_at  DATETIME    NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';

        $cols = [];
        $r = pwg_query('SHOW COLUMNS FROM ' . $prefixeTable . 'ip_location_blocklist');
        while ($row = pwg_db_fetch_row($r)) $cols[] = $row[0];
        if (!in_array('origin', $cols)) {
            pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_blocklist
  ADD COLUMN origin VARCHAR(8) NOT NULL DEFAULT \'manuel\'');
        }
        if (!in_array('expires_at', $cols)) {
            pwg_query('ALTER TABLE ' . $prefixeTable . 'ip_location_blocklist
  ADD COLUMN expires_at DATETIME DEFAULT NULL');
        }
    }

    function deactivate()
    {
        // Les tables sont conservées lors d'une simple désactivation
    }

    function uninstall()
    {
        global $prefixeTable;

        pwg_query('DROP TABLE IF EXISTS ' . $prefixeTable . 'ip_location_log');
        pwg_query('DROP TABLE IF EXISTS ' . $prefixeTable . 'ip_location_cache');
        pwg_query('DROP TABLE IF EXISTS ' . $prefixeTable . 'ip_location_blocklist');
        pwg_query('DROP TABLE IF EXISTS ' . $prefixeTable . 'ip_location_robot_check');
        pwg_query("DELETE FROM " . CONFIG_TABLE . " WHERE param = 'ip_location'");
    }

    /**
     * Lors d'une mise à jour depuis l'admin Piwigo (PEM ou zip), le cœur n'appelle QUE
     * update() — jamais activate(), même si le plugin reste actif d'une version à
     * l'autre (cf. admin/include/plugins.class.php, perform_action(), case 'update' vs
     * case 'activate' qui fait un simple `break` si déjà actif). Avec l'ancien format
     * procédural (maintain.inc.php), update() était un no-op côté Piwigo (aucun
     * is_callable('plugin_update') n'est même tenté) : les migrations de schéma ne se
     * rejouaient donc jamais après une mise à jour, seulement après un passage explicite
     * désactivé → activé. On rejoue ici activate() : chaque étape de migration y est
     * gardée par un SHOW COLUMNS/SHOW INDEX, donc idempotente et sûre à rejouer même
     * sans changement de schéma réel.
     */
    function update($old_version, $new_version, &$errors = array())
    {
        $this->activate($new_version, $errors);
    }
}
