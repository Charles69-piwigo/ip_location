<style>
  .ipl-table { width:auto; border-collapse:collapse; margin:0 0 1em 20px; font-size:0.8rem; }
  .ipl-table th, .ipl-table td { text-align:left; padding:4px 12px 4px 0; white-space:nowrap; }
  .ipl-table td.ipl-date { width:200px; min-width:155px; max-width:200px; }
  .ipl-table thead tr { border-bottom:2px solid #ccc; }
  .ipl-table tbody tr:hover { background:#f5f5f5; }
  .ipl-table td.ipl-url { min-width:500px; max-width:700px; overflow:hidden; text-overflow:ellipsis; }
  .ipl-table td.ipl-ua  { max-width:500px; overflow:hidden; text-overflow:ellipsis; font-size:0.85em; color:#555; }
  #content h3 { text-align:left; padding-left:10px;}
  .ipl-bot-row { background:#fff0f0 }
  .ipl-bot-row:hover { background:#ffe0e0 !important; }
  .ipl-bot-badge { color:#c00; font-weight:bold; font-size:0.8em; }
  .ipl-blocked-badge { color:#800; font-weight:bold; font-size:0.8em; background:#fdd; padding:1px 4px; border-radius:3px; }
  .ipl-exempt-badge { color:#555; font-weight:bold; font-size:0.8em; background:#eee; padding:1px 4px; border-radius:3px; cursor:help; }
  .ipl-btn-block   { font-size:0.8em; padding:2px 7px; background:#c00; color:#fff; border:none; border-radius:3px; cursor:pointer; }
  .ipl-btn-block:hover { background:#900; }
  .ipl-btn-unblock { font-size:0.8em; padding:2px 7px; background:#555; color:#fff; border:none; border-radius:3px; cursor:pointer; }
  .ipl-btn-unblock:hover { background:#333; }
  .ipl-filters { margin:0 0 0.8em 20px; text-align:left; }
  .ipl-filters a { margin-right:8px; padding:3px 10px; border:1px solid #ccc; border-radius:3px; text-decoration:none; color:#333; font-size:0.9em; }
  .ipl-filters a.active { background:#555; color:#fff; border-color:#555; font-weight:bold; }
  .ipl-tabs { margin:0 0 0 10px; border-bottom:2px solid #ccc; text-align:left; }
  .ipl-tabs a { display:inline-block; padding:6px 16px; text-decoration:none; color:#555; border:1px solid transparent; border-bottom:none; border-radius:4px 4px 0 0; margin-bottom:-2px; font-size:0.95em; }
  .ipl-tabs a.active { background:#fff; border-color:#ccc; color:#000; font-weight:bold; border-bottom-color:#fff; }
  .ipl-tabs a:hover:not(.active) { background:#f0f0f0; }
  .ipl-help { margin:0 0 2em 20px; max-width:700px; line-height:1.6; }
  .ipl-help h4 { margin:1.2em 0 0.3em 0; color:#333; border-bottom:1px solid #eee; padding-bottom:2px; }
  .ipl-help ul { margin:0.3em 0 0.5em 1.2em; padding:0; }
  .ipl-help li { margin:0.2em 0; }
  .ipl-help code { background:#f4f4f4; padding:1px 5px; border-radius:3px; font-size:0.9em; }
  .ipl-counters { margin:0 0 1.5em 20px; font-size:0.95em; text-align:left; }
  .ipl-counters span { margin-right:20px; }
  .ipl-config { margin:0 0 2em 20px; text-align:left; }
  .ipl-config * { text-align:left; }
  .ipl-config p { margin:0 0 0.8em 0; }
  .ipl-config label { display:block; }
  .ipl-config label.ipl-inline { display:inline; }
  .ipl-config strong { display:block; }
  .ipl-config input[type="text"], .ipl-config textarea { display:block; margin:0; }
  .ipl-country-stats { margin:0 0 1.5em 0; }
  .ipl-country-stats summary { cursor:pointer; font-size:1.17em; font-weight:bold; padding:10px 0 4px 10px; text-align:left; }
  .ipl-country-stats .ipl-table { margin-top:0.5em; }
</style>
<script>
if (window.location.search.indexOf('msg=') !== -1) {
  var url = window.location.href.replace(/[?&]msg=[^&]*/g, '').replace(/\?&/, '?').replace(/[?&]$/, '');
  history.replaceState(null, '', url);
}
</script>

<div class="titrePage">
  <h2>{'IP Location'|@translate} &mdash; {'Journal des accès'|@translate}</h2>
</div>

<!-- ── Onglets ────────────────────────────────────────────────────────── -->
<div class="ipl-tabs">
  <a href="{$BASE_URL|escape}" {if $TAB eq 'config'}class="active"{/if}>{'Configuration'|@translate}</a>
  <a href="{$BASE_URL|escape}&amp;tab=stats" {if $TAB eq 'stats'}class="active"{/if}>{'Statistiques'|@translate}</a>
  <a href="{$BASE_URL|escape}&amp;tab=help" {if $TAB eq 'help'}class="active"{/if}>{'Aide'|@translate}</a>
</div>

{$TAB_CONTENT}

{if $TAB eq 'config'}
<div class="ipl-counters">
  <span>{'Accès'|@translate} : <strong>{$TOTAL_ALL}</strong></span>
  <span style="color:#c00;">{'Bots détectés'|@translate} : <strong>{$TOTAL_BOTS}</strong></span>
  <span style="color:#800;">{'IPs bloquées'|@translate} : <strong>{$TOTAL_BLOCKED}</strong></span>
  <span>{'Humains'|@translate} : <strong>{$TOTAL_NORMAL}</strong></span>
</div>

<!-- ── Statistiques par pays ─────────────────────────────────────────────── -->
<details class="ipl-country-stats">
  <summary>{'Statistiques par pays'|@translate}</summary>

  <table class="ipl-table">
    <thead>
      <tr>
        <th>{'Pays'|@translate}</th>
        <th>{'Code'|@translate}</th>
        <th>{'Accès'|@translate}</th>
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
</details>

<!-- ── Journal des visites ──────────────────────────────────────────────── -->
<h3 id="ipl-journal">{'Journal des accès'|@translate}</h3>

<div class="ipl-filters">
  {if $COUNTRY_FILTER neq ''}
    {assign var=country_qs value="&amp;country=`$COUNTRY_FILTER`"}
  {else}
    {assign var=country_qs value=''}
  {/if}
  {assign var=date_qs value=''}
  {if $DATE_FROM neq ''}{assign var=date_qs value="`$date_qs`&amp;date_from=`$DATE_FROM`"}{/if}
  {if $DATE_TO   neq ''}{assign var=date_qs value="`$date_qs`&amp;date_to=`$DATE_TO`"}{/if}
  {if $IP_FILTER neq ''}
    {assign var=ip_qs value="&amp;ip_filter=`$IP_FILTER`"}
  {else}
    {assign var=ip_qs value=''}
  {/if}
  <a href="{$BASE_URL|escape}{$country_qs}{$date_qs}{$ip_qs}#ipl-journal"                          {if $FILTER eq 'all'}     class="active"{/if}>{'Tous'|@translate}</a>
  <a href="{$BASE_URL|escape}&amp;filter=normal{$country_qs}{$date_qs}{$ip_qs}#ipl-journal"        {if $FILTER eq 'normal'}  class="active"{/if}>{'Normal'|@translate}</a>
  <a href="{$BASE_URL|escape}&amp;filter=bot{$country_qs}{$date_qs}{$ip_qs}#ipl-journal"           {if $FILTER eq 'bot'}     class="active"{/if}>{'Bots non bloqués'|@translate}</a>
  <a href="{$BASE_URL|escape}&amp;filter=blocked{$country_qs}{$date_qs}{$ip_qs}#ipl-journal"       {if $FILTER eq 'blocked'} class="active"{/if}>{'Bloqués'|@translate}</a>
  &nbsp;|&nbsp;
  <form method="get" action="admin.php" style="display:inline;margin:0;">
    <input type="hidden" name="page" value="plugin-ip_location">
    {if $FILTER neq 'all'}<input type="hidden" name="filter" value="{$FILTER|escape}">{/if}
    <select name="country" onchange="this.form.action='admin.php#ipl-journal';this.form.submit()" style="font-size:0.88em;padding:2px 4px;">
      <option value="">{'Tous les pays'|@translate}</option>
      {foreach from=$COUNTRIES item=c}
      <option value="{$c.country_code|escape}" {if $COUNTRY_FILTER eq $c.country_code}selected{/if}>{$c.country|escape} ({$c.visits})</option>
      {/foreach}
    </select>
    {if $DATE_FROM neq ''}<input type="hidden" name="date_from" value="{$DATE_FROM|escape}">{/if}
    {if $DATE_TO   neq ''}<input type="hidden" name="date_to"   value="{$DATE_TO|escape}">{/if}
    {if $IP_FILTER neq ''}<input type="hidden" name="ip_filter" value="{$IP_FILTER|escape}">{/if}
  </form>
  &nbsp;|&nbsp;
  <form method="get" action="admin.php#ipl-journal" style="display:inline;margin:0;">
    <input type="hidden" name="page" value="plugin-ip_location">
    {if $FILTER neq 'all'}<input type="hidden" name="filter" value="{$FILTER|escape}">{/if}
    {if $COUNTRY_FILTER neq ''}<input type="hidden" name="country" value="{$COUNTRY_FILTER|escape}">{/if}
    <input type="date" name="date_from" value="{$DATE_FROM|escape}" style="font-size:0.88em;padding:2px 4px;">
    <span style="font-size:0.88em;color:#666;">→</span>
    <input type="date" name="date_to"   value="{$DATE_TO|escape}"   style="font-size:0.88em;padding:2px 4px;">
    <button type="submit" style="font-size:0.85em;padding:2px 8px;">{'Filtrer'|@translate}</button>
    {if $DATE_FROM neq '' or $DATE_TO neq ''}
      <a href="{$BASE_URL|escape}{if $FILTER neq 'all'}&amp;filter={$FILTER}{/if}{$country_qs}{$ip_qs}#ipl-journal" style="font-size:0.85em;margin-left:4px;">✕</a>
    {/if}
    {if $IP_FILTER neq ''}<input type="hidden" name="ip_filter" value="{$IP_FILTER|escape}">{/if}
  </form>
  &nbsp;|&nbsp;
  <form method="get" action="admin.php#ipl-journal" style="display:inline;margin:0;">
    <input type="hidden" name="page" value="plugin-ip_location">
    {if $FILTER neq 'all'}<input type="hidden" name="filter" value="{$FILTER|escape}">{/if}
    {if $COUNTRY_FILTER neq ''}<input type="hidden" name="country" value="{$COUNTRY_FILTER|escape}">{/if}
    {if $DATE_FROM neq ''}<input type="hidden" name="date_from" value="{$DATE_FROM|escape}">{/if}
    {if $DATE_TO   neq ''}<input type="hidden" name="date_to"   value="{$DATE_TO|escape}">{/if}
    <span style="font-size:0.88em;color:#666;">IP&nbsp;</span>
    <input type="text" name="ip_filter" value="{$IP_FILTER|escape}" placeholder="" style="font-size:0.88em;padding:2px 4px;width:110px;">
    <button type="submit" style="font-size:0.85em;padding:2px 8px;">{'Filtrer'|@translate}</button>
    {if $IP_FILTER neq ''}
      <a href="{$BASE_URL|escape}{if $FILTER neq 'all'}&amp;filter={$FILTER}{/if}{$country_qs}{$date_qs}#ipl-journal" style="font-size:0.85em;margin-left:4px;">✕</a>
    {/if}
  </form>
</div>

<table class="ipl-table">
  <thead>
    <tr>
      <th>{'htaccess'|@translate}</th>
      <th>{'Date'|@translate}</th>
      <th>{'IP'|@translate}</th>
      <th>{'Pays'|@translate}</th>
      <th>{'Ville'|@translate}</th>
      <th>{'Score'|@translate}</th>
      <th>{'URL'|@translate}</th>
      <th>{'User-Agent'|@translate}</th>
    </tr>
  </thead>
  <tbody>
  {if $LOGS|@count == 0}
    <tr><td colspan="8">{'Aucun accès enregistré.'|@translate}</td></tr>
  {else}
    {foreach from=$LOGS item=log}
    <tr{if $log.is_bot} class="ipl-bot-row"{/if}>
      <td>
        {if $log.in_blocklist}
          <form method="post" action="" style="margin:0;">
            <input type="hidden" name="action" value="unblock_ip">
            <input type="hidden" name="ip" value="{$log.ip|escape}">
            <button type="submit" class="ipl-btn-unblock">{'Retirer du .htaccess'|@translate}</button>
          </form>
        {else}
          <form method="post" action="" style="margin:0;" id="blk_{$log.id}">
            <input type="hidden" name="action" value="block_ip">
            <input type="hidden" name="ip" id="blk_ip_{$log.id}" value="{$log.ip|escape}">
            <input type="hidden" name="country" value="{$log.country|escape}">
            <input type="hidden" name="city" value="{$log.city|escape}">
            <button type="submit" class="ipl-btn-block" style="margin-bottom:2px;">{'Ajouter IP'|@translate}</button>
            <button type="submit" class="ipl-btn-block"
              onclick="var p='{$log.ip|escape}'.split('.');document.getElementById('blk_ip_{$log.id}').value=p[0]+'.'+p[1]+'.0.0/16';">
              {'Ajouter /16'|@translate}
            </button>
          </form>
        {/if}
      </td>
      <td class="ipl-date">{$log.visit_date|escape}{if $log.is_bot} <span class="ipl-bot-badge">BOT</span>{/if}{if $log.is_blocked || $log.in_blocklist} <span class="ipl-blocked-badge">BLOQUÉ</span>{/if}</td>
      <td>{$log.ip|escape}</td>
      <td>{$log.country|escape}</td>
      <td>{$log.city|escape}</td>
      <td>{if $log.is_exempt && ($log.is_bot || $log.bot_score > 0)}<span class="ipl-exempt-badge" title="{'Retirée manuellement du blocage : le score affiché peut rester au-dessus du seuil sans que l\'IP soit rebloquée, tant qu\'aucune nouvelle activité suspecte n\'apparaît après le retrait.'|@translate}">{'Exempté'|@translate}</span> {/if}{$log.bot_score}</td>
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
    <a href="{$BASE_URL|escape}{if $FILTER neq 'all'}&amp;filter={$FILTER}{/if}{$country_qs}{$date_qs}{$ip_qs}&amp;pnum={$pnum}#ipl-journal"{if $pnum == $CURRENT_PAGE} style="font-weight:bold;"{/if}>{$pnum}</a>
  {/section}
</div>
{/if}

<!-- ── Gestion de l'historique ───────────────────────────────────────────── -->
<h3>{'Gestion de l\'historique'|@translate}</h3>

<form method="post" action="" style="margin-left:20px;">
  <input type="hidden" name="action" value="purge_before_date">
  {'Supprimer les logs avant le'|@translate}
  <input type="date" name="before_date" style="display:inline;margin:0 6px;">
  <button type="submit" class="buttonLike">{'Supprimer'|@translate}</button>
</form>

<!-- ── Vidage du cache de géolocalisation ───────────────────────────────── -->
<h3>{'Cache de géolocalisation'|@translate}</h3>

<form method="post" action="" style="margin-left:20px;">
  <input type="hidden" name="action" value="purge_cache">
  <button type="submit" class="buttonLike" onclick="return confirm('Vider tout le cache de géolocalisation ?');">{'Vider le cache'|@translate}</button>
  <span style="margin-left:10px;color:#777;font-size:0.9em;">{'Forcer la résolution géographique de toutes les IPs lors de leur prochaine visite.'|@translate}</span>
</form>

{/if}
