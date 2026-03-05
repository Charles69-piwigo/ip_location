<style>
  .ipl-table { width:auto; border-collapse:collapse; margin:0 0 1em 20px; }
  .ipl-table th, .ipl-table td { text-align:left; padding:4px 12px 4px 0; white-space:nowrap; }
  .ipl-table thead tr { border-bottom:2px solid #ccc; }
  .ipl-table tbody tr:hover { background:#f5f5f5; }
  .ipl-table td.ipl-url { max-width:300px; overflow:hidden; text-overflow:ellipsis; }
  .ipl-table td.ipl-ua  { max-width:250px; overflow:hidden; text-overflow:ellipsis; font-size:0.85em; color:#555; }
  #content h3 { text-align:left; }
  .ipl-bot-row { background:#fff0f0; }
  .ipl-bot-row:hover { background:#ffe0e0 !important; }
  .ipl-bot-badge { color:#c00; font-weight:bold; font-size:0.8em; }
  .ipl-counters { margin:0 0 1.5em 20px; font-size:0.95em; text-align:left; }
  .ipl-counters span { margin-right:20px; }
  .ipl-config { margin:0 0 2em 20px; text-align:left; }
  .ipl-config * { text-align:left; }
  .ipl-config p { margin:0 0 0.8em 0; }
  .ipl-config label { display:block; }
  .ipl-config label.ipl-inline { display:inline; }
  .ipl-config strong { display:block; }
  .ipl-config input[type="text"], .ipl-config textarea { display:block; margin:0; }
</style>

<div class="titrePage">
  <h2>{'IP Location'|@translate} &mdash; {'Journal des visites'|@translate}</h2>
</div>

<!-- ── Configuration ────────────────────────────────────────────────────── -->
<h3>{'Configuration'|@translate}</h3>

<form method="post" action="" class="ipl-config">
  <input type="hidden" name="action" value="save_config">
  <p>
    <label class="ipl-inline">
      <input type="checkbox" name="blocking_enabled" value="1"{if $BLOCKING_ENABLED} checked{/if}>
      <strong style="display:inline;">{'Activer le blocage par pays'|@translate}</strong>
    </label>
  </p>
  <p>
    <label><strong>{'Pays bloqués'|@translate}</strong> (codes ISO séparés par virgule, ex: US,EG,CN) :</label><br>
    <input type="text" name="blocked_countries" value="{$BLOCKED_COUNTRIES|escape}"
           style="width:400px;" placeholder="US,EG,CN">
  </p>
  <p>
    <label><strong>{'IPs toujours autorisées'|@translate}</strong> (une par ligne) :</label><br>
    <textarea name="whitelist_ips" rows="5" style="width:400px;">{$WHITELIST_IPS|escape}</textarea>
  </p>
  <button type="submit" class="buttonLike">{'Enregistrer la configuration'|@translate}</button>
</form>

<div class="ipl-counters">
  <span>{'Visites'|@translate} : <strong>{$TOTAL_ALL}</strong></span>
  <span style="color:#c00;">{'Bots détectés'|@translate} : <strong>{$TOTAL_BOTS}</strong></span>
  <span>{'Humains'|@translate} : <strong>{math equation="a - b" a=$TOTAL_ALL b=$TOTAL_BOTS}</strong></span>
</div>

<!-- ── Statistiques par pays ─────────────────────────────────────────────── -->
<h3>{'Statistiques par pays'|@translate}</h3>

<table class="ipl-table">
  <thead>
    <tr>
      <th>{'Pays'|@translate}</th>
      <th>{'Code'|@translate}</th>
      <th>{'Visites'|@translate}</th>
      <th>{'Bots'|@translate}</th>
    </tr>
  </thead>
  <tbody>
  {if $STATS|@count == 0}
    <tr><td colspan="4">{'Aucune donnée.'|@translate}</td></tr>
  {else}
    {foreach from=$STATS item=s}
    <tr>
      <td>{$s.country|escape}</td>
      <td>{$s.country_code|escape}</td>
      <td>{$s.visits}</td>
      <td>{if $s.bots > 0}<span class="ipl-bot-badge">{$s.bots}</span>{else}0{/if}</td>
    </tr>
    {/foreach}
  {/if}
  </tbody>
</table>

<!-- ── Journal des visites ──────────────────────────────────────────────── -->
<h3>{'Journal des visites'|@translate}</h3>

<table class="ipl-table">
  <thead>
    <tr>
      <th>{'Date'|@translate}</th>
      <th>{'IP'|@translate}</th>
      <th>{'Pays'|@translate}</th>
      <th>{'Ville'|@translate}</th>
      <th>{'URL'|@translate}</th>
      <th>{'User-Agent'|@translate}</th>
    </tr>
  </thead>
  <tbody>
  {if $LOGS|@count == 0}
    <tr><td colspan="6">{'Aucune visite enregistrée.'|@translate}</td></tr>
  {else}
    {foreach from=$LOGS item=log}
    <tr{if $log.is_bot} class="ipl-bot-row"{/if}>
      <td>{$log.visit_date|escape}{if $log.is_bot} <span class="ipl-bot-badge">BOT</span>{/if}</td>
      <td>{$log.ip|escape}</td>
      <td>{$log.country|escape}</td>
      <td>{$log.city|escape}</td>
      <td class="ipl-url">
        <a href="{$log.url|escape}" target="_blank" title="{$log.url|escape}">{$log.url|escape}</a>
      </td>
      <td class="ipl-ua" title="{$log.user_agent|escape}">{$log.user_agent|escape}</td>
    </tr>
    {/foreach}
  {/if}
  </tbody>
</table>

<!-- Pagination -->
{if $TOTAL_PAGES > 1}
<div class="pagination" style="margin:10px 0;">
  {section name=p loop=$TOTAL_PAGES start=1}
    {assign var=pnum value=$smarty.section.p.index}
    <a href="{$BASE_URL|escape}&amp;pnum={$pnum}"{if $pnum == $CURRENT_PAGE} style="font-weight:bold;"{/if}>{$pnum}</a>
  {/section}
</div>
{/if}

<!-- ── Purge ─────────────────────────────────────────────────────────────── -->
<h3>{'Purge'|@translate}</h3>

<form method="post" action="" style="margin-bottom:10px;"
      onsubmit="return confirm('{'Vider tout le log ?'|@translate}');">
  <input type="hidden" name="action" value="purge_all">
  <button type="submit" class="buttonLike">{'Vider tout'|@translate}</button>
</form>

<form method="post" action="">
  <input type="hidden" name="action" value="purge_old">
  {'Purge par ancienneté'|@translate} :
  <input type="number" name="days" value="30" min="1" style="width:60px;">
  {'jours'|@translate}
  <button type="submit" class="buttonLike">{'Supprimer'|@translate}</button>
</form>
