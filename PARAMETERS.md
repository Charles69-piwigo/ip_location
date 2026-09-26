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
$conf['ip_location_score_bot_spoof'] = 80;
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
| `ip_location_score_multi_url_burst` | `50` | Points added on **single-IP multi-URL burst**: ≥ `ip_location_multi_url_threshold` distinct URLs (default 20) hit by the same IP within roughly a 30-second window (v2.5) — typical of a scraper crawling the catalogue at high speed, unlike the single-IP burst above which repeats the same URL. |
| `ip_location_multi_url_threshold` | `20` | (v2.5.5) Number of distinct URLs from the same IP within ~30 seconds from which the **multi-URL burst** is retained (is_bot and score). Used to be fixed at 10: a visitor clicking "next" in an album easily reaches 11-12 photos in 30 s; the scrapers observed did 47 to 88. |
| `ip_location_score_outdated_browser` | `20` | Points added when the User-Agent claims an obviously outdated browser (v2.5, see `ip_location_is_outdated_browser()`: Firefox < 100, Chrome < 109 — 109 is spared, as it's the last version for Windows 7/8 —, or iOS < 14) — unlikely for a genuine visitor, observed in practice on frozen UAs used by scrapers. |
| `ip_location_score_shared_ua` | `30` | Points added when a User-Agent is seen ≥ 20 times in the window with ≥ 90% distinct IPs (almost never the same IP twice) — betrays a small library of frozen UAs recycled by a residential proxy pool, each IP serving only a handful of requests. Batch-only signal (aggregated across several rows), absent from the hot path. Added in v2.5 after analysing a real-world log (7000+ hits, 6400+ distinct IPs, invisible to the other rules). Thresholds (20, 90%) aren't overridable yet, only the weight is. Since v2.5.5: contributes to the score only (no more `is_bot` flag), weight lowered from 50 to 30 — the frozen UA may be that of the most widespread browser of the moment, so this signal alone must never reach a blocking threshold. |
| `ip_location_score_no_js` | `20` | Points added if the IP has **no trace** of the PhotoSwipe slideshow's JS logger (`log_type = 'js'`) despite ≥ 2 hits within the rolling window — a sign that a scraper never runs JavaScript. |
| `ip_location_auto_block_ttl_days` | `14` | Number of days before an **automatic** block added to the blocklist expires (always temporary, never permanent, exact IP only — never a /16 range). |
| `ip_location_auto_block_recent_hours` | `24` | (v2.5.5) Only IPs with at least one suspicious hit within the last N hours become candidates for automatic blocking — avoids blocking the whole 7-day window at once (mostly single-use IPs, already gone). Does not affect the release of existing entries when the form is saved. |
| `ip_location_bot_allowlist` | *(obsolete since v2.6.2)* | Former robot whitelist. Replaced by the **Indexing robots** block of the Configuration tab (editable list, Allowed / Blocked status, DNS verification). If this setting exists in `local/config/config.inc.php`, it is used **once** as the starting point of the new list (its robots become "Allowed"), then no longer read once the list is saved from the admin. |
| `ip_location_score_bot_spoof` | `50` | (v2.6.2) Points added — and bot flag — when an IP presents the User-Agent of a verifiable engine (Googlebot, Bingbot, Applebot, Yandex, Baidu) while DNS verification shows it does not belong to it: deliberate spoofing. |
| `ip_location_robot_check_days` | `30` | (v2.6.2) Validity, in days, of the DNS verification result for a robot IP (table `ip_location_robot_check`): a single DNS lookup per IP over that period. |
| `ip_location_refusal_log_minutes` | `10` | (v2.6.5) A repeated refusal — same IP, same reason (blocked robot, IP list, automatic blocking) — is logged only once per N-minute period: the request is still refused every time, but a persistent robot (GPTBot seen at ~50 requests/minute for hours) no longer floods the log. `0` = log every refusal. |
| `ip_location_classify_interval_hours` | `4` | Minimum interval (in hours) between two triggers of the classification/auto-block pass via **public traffic** (`ip_location_log_visit()`), so the mechanism keeps working even without regular admin visits. Has no effect on the trigger from the admin tab (see next row). |
| `ip_location_classify_admin_interval_minutes` | `10` | (v2.7a.4) Minimum interval (in minutes) between two classification/auto-block passes triggered by opening the plugin's admin tab. It used to run on every page load, which caused long waits on heavily crawled sites (log full of Googlebot hits). `0` = on every load (previous behaviour). Saving the "Automatic blocking" block always forces an immediate recalculation. |
| `ip_location_robot_log_limit` | `100` | (v2.7a.5) Number of accesses **logged** per allowed, DNS-verifiable search robot (Googlebot, Bingbot, Applebot, Yandex, Baidu) per day. Beyond that, accesses are no longer written to the log but only counted (`ip_location_robot_count` table, per day / robot / IP / country); the Log counters and the "All" / "Bots" statistics series include them. `0` = log everything (previous behaviour). Allowed robots without DNS verification, fake robots and blocked robots are always logged. |
| `ip_location_classify_window_days` | `7` | Rolling window (in days) that the whole bot classification (`ip_location_classify_recent()`) and the selection of blocking candidates (`ip_location_get_bot_candidates()`) operate on: beyond it, a visit is no longer reconsidered for the score, `is_bot`, or auto-block eligibility. Widening it lets you catch "slow" scrapers spread over several days, at the cost of heavier SQL queries (more rows scanned on every pass). |

## Notes

- These settings only concern the **bot score** (4th blocking lever). The
  plugin's other settings (blocked countries, IP whitelist, score threshold, download country filter, etc.) are managed via the admin's
  **Configuration** tab and stored serialized under the single `ip_location`
  key in `_config` (see `ip_location_get_conf()`), not via `$conf`.
- Automatic blocking by score only activates if `bot_block_enabled` is on in
  the plugin's config (admin settings). Since v2.5.5 it no longer depends on
  `htaccess_enabled`: auto-blocked IPs are refused by the plugin itself
  (`ip_location_blocklist_guard()`, `init` hook) and are never written to
  .htaccess, which is kept for manual blocks.
- Browser prefetch requests (`log_type = 'prefetch'`) are ignored by every
  behavioural rule (co-visitation, bursts, shared UA, missing JS).
