# Paramètres surchargeables ($conf)

Ces réglages fins pilotent le **score de suspicion bot** (4ème levier de
blocage, cf. `ip_location_classify_recent()` et `ip_location_auto_block_bots()`
dans `main.inc.php`). Ils sont volontairement **hors de l'UI Configuration**
tant qu'ils sont en phase d'évaluation, mais restent surchargeables sans
toucher au code du plugin.

## Comment les surcharger

Ajouter dans `local/config/config.inc.php` (fichier chargé par Piwigo avant
l'exécution des plugins — donc prioritaire sur les défauts codés en dur) :

```php
$conf['ip_location_score_burst'] = 30;
$conf['ip_location_bot_allowlist'] = ['Googlebot', 'Bingbot', 'MonBot'];
```

Ce fichier peut être édité depuis l'admin Piwigo via le plugin
**LocalFilesEditor**, sans FTP. Défauts définis dans `main.inc.php` (juste
après `define('IP_LOCATION_STATS_PRESET_SLOTS', ...)`), appliqués uniquement
si la clé n'existe pas déjà dans `$conf` (`if (!isset($conf[$key]))`).

## Liste des paramètres

| Paramètre | Défaut | Rôle |
|---|---|---|
| `ip_location_score_ua_empty` | `10` | Points ajoutés au `bot_score` quand le User-Agent de la visite est vide. |
| `ip_location_score_ua_keyword` | `15` | Points ajoutés quand le User-Agent contient un mot-clé de bot connu (liste dans `ip_location_bot_ua_keywords()` de `main.inc.php` : `bot`, `crawler`, `spider`, `scraper`, `slurp`, `curl`, `wget`, `python`, `go-http`, `java/`, `libwww`, `scrapy`, `zgrab`, `masscan`, ainsi que plusieurs scanners auto-identifiés ajoutés en v2.5 : `censys`, `palo alto`, `leakix`, `cms-checker`, `rootevidence`, `netcraft`, `tlm-audit-scanner`, `internetmeasurement`, `okhttp`), ou quand l'UA annonce lui-même un crawler (v2.5, `ip_location_ua_declares_crawler()` : contient `://` ou `@`, ou `compatible;` hors MSIE/Trident — attrape p.ex. `GoogleOther`). |
| `ip_location_score_covisit` | `40` | Points ajoutés en cas de **co-visitation** : ≥ 2 IP distinctes ayant accédé à la même URL dans une fenêtre d'environ 10 secondes. |
| `ip_location_score_burst` | `50` | Points ajoutés en cas de **rafale mono-IP** : ≥ 3 accès de la même IP sur la même URL dans une fenêtre d'environ 10 secondes. |
| `ip_location_score_multi_url_burst` | `50` | Points ajoutés en cas de **rafale multi-URL mono-IP** : ≥ `ip_location_multi_url_threshold` URL distinctes (défaut 20) accédées par la même IP dans une fenêtre d'environ 30 secondes (v2.5) — typique d'un scraper qui parcourt le catalogue à grande vitesse, à la différence de la rafale mono-IP ci-dessus qui répète une même URL. |
| `ip_location_multi_url_threshold` | `20` | (v2.5.5) Nombre d'URL distinctes par la même IP en ~30 secondes à partir duquel la **rafale multi-URL** est retenue (is_bot et score). Était fixé à 10 : un visiteur qui clique « suivant » dans un album atteint facilement 11-12 photos en 30 s ; les scrapers observés en faisaient 47 à 88. |
| `ip_location_score_outdated_browser` | `20` | Points ajoutés quand le User-Agent annonce un navigateur manifestement obsolète (v2.5, cf. `ip_location_is_outdated_browser()` : Firefox < 100, Chrome < 109 — la 109, dernière version pour Windows 7/8, est épargnée —, ou iOS < 14) — peu probable chez un vrai visiteur, observé en pratique sur des UA figés utilisés par des scrapers. |
| `ip_location_score_shared_ua` | `30` | Points ajoutés quand un User-Agent est vu ≥ 20 fois sur la fenêtre avec ≥ 90 % d'IP distinctes (quasiment jamais deux fois la même IP) — trahit une petite bibliothèque d'UA figés recyclée par un pool de proxies résidentiels, chaque IP ne servant qu'une poignée de requêtes. Signal batch uniquement (agrégation sur plusieurs lignes), absent du chemin chaud. Ajouté en v2.5 après analyse d'un journal réel (7000+ accès, 6400+ IP distinctes, invisibles aux autres règles). Seuils (20, 90 %) non surchargeables pour l'instant, seul le poids l'est. Depuis v2.5.5 : contribue au score seulement (plus de marquage `is_bot`), poids abaissé de 50 à 30 — l'UA figé peut être celui du navigateur le plus répandu du moment, ce signal seul ne doit donc jamais atteindre un seuil de blocage. |
| `ip_location_score_no_js` | `20` | Points ajoutés si l'IP n'a **aucune trace** du logger JS du diaporama PhotoSwipe (`log_type = 'js'`) malgré ≥ 2 accès sur la fenêtre glissante de 7 jours — signe qu'un scraper n'exécute jamais de JavaScript. |
| `ip_location_recurrence` | `0` | Poids de **récidive** : ajoute `poids × (nb d'autres lignes déjà suspectes de la même IP − 1)` au score de chaque ligne suspecte. `0` = désactivé (même convention que `max_records`). |
| `ip_location_auto_block_ttl_days` | `14` | Durée en jours avant expiration d'un blocage **automatique** ajouté à la blocklist (toujours temporaire, jamais permanent, IP exacte uniquement — jamais de plage /16). |
| `ip_location_auto_block_recent_hours` | `24` | (v2.5.5) Seules les IP ayant au moins un accès suspect dans les N dernières heures deviennent candidates au blocage automatique — évite de bloquer d'un coup toute la fenêtre de 7 jours (IP souvent à usage unique, déjà parties). N'affecte pas la libération des entrées existantes lors de l'enregistrement du formulaire. |
| `ip_location_bot_allowlist` | `['Googlebot', 'Bingbot', 'Slackbot', 'Twitterbot', 'facebookexternalhit', 'DuckDuckBot', 'WhatsApp', 'Applebot', 'LinkedInBot', 'TelegramBot']` | Liste blanche de bots légitimes (tableau PHP) : toute visite dont le User-Agent contient une de ces sous-chaînes voit son `bot_score` forcé à 0 et n'est jamais éligible au blocage automatique. Pour désactiver l'allowlist (bloquer aussi ces bots), surcharger avec `array()`. Les bots d'IA (`GPTBot`, `GoogleOther`, `ClaudeBot`, `CCBot`…) n'y figurent volontairement pas : ils sont comptés comme bots et peuvent être bloqués ; pour en tolérer un, ajoutez son nom ici (le `robots.txt` du site reste le moyen poli de les refuser). |
| `ip_location_classify_interval_hours` | `4` | Intervalle minimal (en heures) entre deux déclenchements de la classification/blocage auto via le **trafic public** (`ip_location_log_visit()`), pour que le mécanisme fonctionne même sans visite admin régulière. Sans effet sur le déclenchement depuis l'onglet admin, qui a lieu à chaque chargement. |
| `ip_location_classify_window_days` | `7` | Fenêtre glissante (en jours) sur laquelle portent toute la classification bot (`ip_location_classify_recent()`) et la sélection des IP candidates au blocage (`ip_location_get_bot_candidates()`) : au-delà, une visite n'est plus reconsidérée pour le score, `is_bot` ou l'éligibilité au blocage auto. L'élargir permet de détecter des scrapers "lents" étalés sur plusieurs jours, au prix de requêtes SQL plus lourdes (plus de lignes scannées à chaque passage). |

## Notes

- Ces paramètres ne concernent que le **score bot** (4ème levier). Les autres
  réglages du plugin (pays bloqués, whitelist IP, seuil de score, filtre pays téléchargement, etc.) sont gérés via l'onglet
  **Configuration** de l'admin et stockés sérialisés sous la clé unique
  `ip_location` dans `_config` (voir `ip_location_get_conf()`), pas via `$conf`.
- Le blocage automatique par score ne s'active que si `bot_block_enabled` est
  actif dans la config du plugin (réglages admin). Depuis v2.5.5, il ne dépend
  plus de `htaccess_enabled` : les IP auto-bloquées sont refusées par le plugin
  lui-même (`ip_location_blocklist_guard()`, hook `init`) et ne sont jamais
  écrites dans le .htaccess, réservé aux blocages manuels.
- Les requêtes de pré-lecture du navigateur (`log_type = 'prefetch'`) sont
  ignorées par toutes les règles comportementales (co-visitation, rafales,
  UA partagé, absence de JS).
