
<h3>{'Configuration'|@translate}</h3>
<!-- ── Section 1 : Blocage .htaccess ─────────────────────────────────────── -->
<h3>{'Blocage .htaccess'|@translate}</h3>
<div style="margin:0 0 1em 20px;text-align:left;">
  <label class="ipl-inline">
    <input type="checkbox" name="htaccess_enabled" value="1" form="form_htaccess_config"{if $HTACCESS_ENABLED} checked{/if}>
    <strong style="display:inline;">{'Activer le blocage .htaccess'|@translate}</strong>
  </label>
  <em style="display:block;margin-left:20px;font-size:0.85em;color:#666;">{'Quand désactivé, les IPs restent dans la liste mais le bloc .htaccess est supprimé.'|@translate}</em>
</div>

<!-- Tableau des IPs bloquées -->
{if $BLOCKLIST|@count == 0}
<p style="margin-left:20px;color:#666;">{'Aucune IP dans le .htaccess.'|@translate}</p>
{else}
<table class="ipl-table">
  <thead>
    <tr>
      <th>{'IP'|@translate}</th>
      <th>{'Date'|@translate}</th>
      <th>{'Pays'|@translate}</th>
      <th>{'Ville'|@translate}</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$BLOCKLIST item=bl}
    <tr>
      <td><strong>{$bl.ip|escape}</strong></td>
      <td>{$bl.blocked_at|escape}</td>
      <td>{$bl.country|escape}</td>
      <td>{$bl.city|escape}</td>
      <td>
        <form method="post" action="" style="margin:0;">
          <input type="hidden" name="action" value="unblock_ip">
          <input type="hidden" name="ip" value="{$bl.ip|escape}">
          <button type="submit" class="ipl-btn-unblock">{'Retirer du .htaccess'|@translate}</button>
        </form>
      </td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/if}

<!-- Ajout manuel -->
<form method="post" action="" style="margin:0.8em 0 1.5em 20px;">
  <input type="hidden" name="action" value="block_ip">
  <label style="display:inline;font-size:0.9em;">{'Ajouter une IP manuellement'|@translate} :</label>
  <input type="text" name="ip" value="" placeholder="ex: 1.2.3.4 ou 45.35.0.0/16"
         style="width:220px;display:inline;margin:0 6px;">
  <button type="submit" class="buttonLike">{'Ajouter au .htaccess'|@translate}</button>
</form>

<!-- Liste blanche -->
<form id="form_htaccess_config" method="post" action="" class="ipl-config">
  <input type="hidden" name="action" value="save_htaccess_config">
  <p style="margin-top:1em;">
    <label><strong>{'IPs toujours autorisées'|@translate}</strong>
      <em style="font-weight:normal;font-size:0.85em;color:#666;"> &mdash; {'une par ligne — ces IPs ne seront jamais bloquées'|@translate}</em>
    </label>
    <textarea name="whitelist_ips" rows="4" style="width:400px;display:block;margin:0.3em 0 0.5em 0;">{$WHITELIST_IPS|escape}</textarea>
  </p>
  <button type="submit" class="buttonLike">{'Enregistrer la configuration'|@translate}</button>
</form>

<!-- ── Section 2 : Blocage par pays ──────────────────────────────────────── -->
<h3 style="margin-top:2em;">{'Blocage par pays'|@translate}</h3>

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
           style="width:400px;" placeholder="">
  </p>
  <p>
    <label><strong>{'Vidage automatique'|@translate}</strong> &mdash; {'supprimer les plus anciennes entrées au-delà de'|@translate}
      <input type="number" name="max_records" value="{$MAX_RECORDS}" min="0" style="width:80px;display:inline;"> {'enregistrements'|@translate}
      <em style="font-size:0.85em;color:#666;">({'0 = désactivé'|@translate})</em>
    </label>
  </p>
  <button type="submit" class="buttonLike">{'Enregistrer la configuration'|@translate}</button>
</form>
