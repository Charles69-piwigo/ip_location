# Overridable settings ($conf)

These fine-tuning settings drive the **bot suspicion score** (4th blocking
lever, see `ip_location_classify_recent()` and `ip_location_auto_block_bots()`
in `main.inc.php`). They are deliberately **kept out of the Configuration UI**
while still in an evaluation phase, but remain overridable without touching
the plugin's code.

## How to override them

Add to `local/config/config.inc.php` (loaded by Piwigo before plugins run —
so it takes priority over the hard-coded defaults):

```php
$conf['ip_location_score_burst'] = 30;
$conf['ip_location_bot_allowlist'] = ['Googlebot', 'Bingbot', 'MyBot'];
```

This file can be edited from the Piwigo admin via the **LocalFilesEditor**
plugin, no FTP needed. Defaults are defined in `main.inc.php` (right after
`define('IP_LOCATION_STATS_PRESET_SLOTS', ...)`), applied only if the key
isn't already set in `$conf` (`if (!isset($conf[$key]))`).

## Settings list

| Setting | Default | Role |
|---|---|---|
| `ip_location_score_ua_empty` | `10` | Points added to `bot_score` when the visit's User-Agent is empty. |
| `ip_location_score_ua_keyword` | `15` | Points added when the User-Agent contains a known bot keyword (list in `ip_location_bot_ua_keywords()` in `main.inc.php`: `bot`, `crawler`, `spider`, `scraper`, `slurp`, `curl`, `wget`, `python`, `go-http`, `java/`, `libwww`, `scrapy`, `zgrab`, `masscan`, plus several self-identifying scanners added in v2.5: `censys`, `palo alto`, `leakix`, `cms-checker`, `rootevidence`, `netcraft`, `tlm-audit-scanner`, `internetmeasurement`, `okhttp`), or when the UA declares itself as a crawler (v2.5, `ip_location_ua_declares_crawler()`: contains `://` or `@`, or `compatible;` outside MSIE/Trident — catches e.g. `GoogleOther`). |
| `ip_location_score_covisit` | `40` | Points added on **co-visitation**: ≥ 2 distinct IPs hitting the same URL within roughly a 10-second window. |
| `ip_location_score_burst` | `50` | Points added on **single-IP burst**: ≥ 3 hits from the same IP on the same URL within roughly a 10-second window. |
| `ip_location_score_multi_url_burst` | `50` | Points added on **single-IP multi-URL burst**: ≥ 10 distinct URLs hit by the same IP within roughly a 30-second window (v2.5) — typical of a scraper crawling the catalogue at high speed, unlike the single-IP burst above which repeats the same URL. |
| `ip_location_score_outdated_browser` | `20` | Points added when the User-Agent claims an obviously outdated browser (v2.5, see `ip_location_is_outdated_browser()`: Firefox < 100, Chrome < 109 — 109 is spared, as it's the last version for Windows 7/8 —, or iOS < 14) — unlikely for a genuine visitor, observed in practice on frozen UAs used by scrapers. |
| `ip_location_score_shared_ua` | `50` | Points added when a User-Agent is seen ≥ 20 times in the window with ≥ 90% distinct IPs (almost never the same IP twice) — betrays a small library of frozen UAs recycled by a residential proxy pool, each IP serving only a handful of requests. Batch-only signal (aggregated across several rows), absent from the hot path. Added in v2.5 after analysing a real-world log (7000+ hits, 6400+ distinct IPs, invisible to the other rules). Thresholds (20, 90%) aren't overridable yet, only the weight is. |
| `ip_location_score_no_js` | `20` | Points added if the IP has **no trace** of the PhotoSwipe slideshow's JS logger (`log_type = 'js'`) despite ≥ 2 hits within the rolling window — a sign that a scraper never runs JavaScript. |
| `ip_location_recurrence` | `0` | **Recurrence** weight: adds `weight × (number of other already-suspicious rows from the same IP − 1)` to the score of each suspicious row. `0` = disabled (same convention as `max_records`). |
| `ip_location_auto_block_ttl_days` | `14` | Number of days before an **automatic** block added to the blocklist expires (always temporary, never permanent, exact IP only — never a /16 range). |
| `ip_location_bot_allowlist` | `['Googlebot', 'Bingbot', 'Slackbot', 'Twitterbot', 'facebookexternalhit', 'DuckDuckBot', 'WhatsApp', 'Applebot', 'LinkedInBot', 'TelegramBot']` | Whitelist of legitimate bots (PHP array): any visit whose User-Agent contains one of these substrings has its `bot_score` forced to 0 and is never eligible for automatic blocking, regardless of mode (`score` or `is_bot`). To disable the allowlist (also block these bots), override with `array()`. AI bots (`GPTBot`, `GoogleOther`, `ClaudeBot`, `CCBot`…) are deliberately not in it: they're counted as bots and can be blocked; to tolerate one, add its name here (the site's `robots.txt` remains the polite way to turn them away). |
| `ip_location_classify_interval_hours` | `4` | Minimum interval (in hours) between two triggers of the classification/auto-block pass via **public traffic** (`ip_location_log_visit()`), so the mechanism keeps working even without regular admin visits. Has no effect on the trigger from the admin tab, which runs on every page load. |
| `ip_location_classify_window_days` | `7` | Rolling window (in days) that the whole bot classification (`ip_location_classify_recent()`) and the selection of blocking candidates (`ip_location_get_bot_candidates()`) operate on: beyond it, a visit is no longer reconsidered for the score, `is_bot`, or auto-block eligibility. Widening it lets you catch "slow" scrapers spread over several days, at the cost of heavier SQL queries (more rows scanned on every pass). |

## Notes

- These settings only concern the **bot score** (4th blocking lever). The
  plugin's other settings (blocked countries, IP whitelist, score threshold,
  blocking mode, download country filter, etc.) are managed via the admin's
  **Configuration** tab and stored serialized under the single `ip_location`
  key in `_config` (see `ip_location_get_conf()`), not via `$conf`.
- Automatic blocking by score only activates if both `bot_block_enabled`
  **and** `htaccess_enabled` are on in the plugin's config (admin settings),
  on top of these fine-tuning settings.
