
<h3>{'Configuration'|@translate}</h3>

<!-- ── Section 0 : Statistiques visiteurs ────────────────────────────────── -->
<h3>{'Statistiques visiteurs'|@translate}</h3>
<form method="post" action="" class="ipl-config">
  <input type="hidden" name="action" value="save_visitors_config">
  <p>
    <label class="ipl-inline">
      <input type="checkbox" name="visitors_enabled" value="1"{if $VISITORS_ENABLED} checked{/if}>
      <strong style="display:inline;">{'Afficher les visites'|@translate}</strong>
    </label>
    <em style="display:block;margin-left:20px;font-size:0.85em;color:#666;">{'Affiche un bouton "Visiteurs" sur toutes les pages du site, visible par tous.'|@translate}</em>
  </p>
  <p>
    <label><strong>{'Période de calcul'|@translate}</strong>
      <select name="visitors_period" style="margin-left:10px;">
        <option value="week"{if $VISITORS_PERIOD eq 'week'} selected{/if}>{'Semaine (7 jours)'|@translate}</option>
        <option value="fortnight"{if $VISITORS_PERIOD eq 'fortnight'} selected{/if}>{'Quinzaine (15 jours)'|@translate}</option>
        <option value="month"{if $VISITORS_PERIOD eq 'month'} selected{/if}>{'Mois (30 jours)'|@translate}</option>
        <option value="quarter"{if $VISITORS_PERIOD eq 'quarter'} selected{/if}>{'Trimestre (90 jours)'|@translate}</option>
      </select>
    </label>
    <em style="display:block;margin-left:20px;font-size:0.85em;color:#666;">{'Ne compte que les visites d\'album ou de photo précédées d\'un passage sur la page d\'accueil (±30 min).'|@translate}</em>
  </p>
  <button type="submit" class="buttonLike">{'Enregistrer la configuration'|@translate}</button>
</form>

<!-- ── Section 1 : Blocage .htaccess ─────────────────────────────────────── -->
<h3>{'Blocage .htaccess'|@translate}{if $SERVER_IS_NGINX} <span style="font-size:0.75em;font-weight:normal;color:#c0392b;">&#9888; {'Serveur nginx détecté : le fichier .htaccess est ignoré'|@translate}</span>{/if}</h3>
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
<table class="ipl-table" id="ipl-blocklist">
  <thead>
    <tr>
      <th class="ipl-sortable" data-col="0" style="cursor:pointer;">{'IP'|@translate} <span class="ipl-sort-icon">↕</span></th>
      <th class="ipl-sortable" data-col="1" style="cursor:pointer;">{'Date'|@translate} <span class="ipl-sort-icon">↕</span></th>
      <th class="ipl-sortable" data-col="2" style="cursor:pointer;">{'Pays'|@translate} <span class="ipl-sort-icon">↕</span></th>
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
<form method="post" action="" style="margin:0.8em 0 1.5em 20px;"
  onsubmit="var f=this.elements['ip'],v=f.value.trim();if(/^\d+\.\d+$/.test(v))f.value=v+'.0.0/16';">
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

<script>
(function() {
  var table = document.getElementById('ipl-blocklist');
  if (!table) return;
  var sortCol = -1, sortAsc = true;

  table.querySelectorAll('th.ipl-sortable').forEach(function(th) {
    th.addEventListener('click', function() {
      var col = parseInt(th.dataset.col);
      sortAsc = (sortCol === col) ? !sortAsc : true;
      sortCol = col;

      table.querySelectorAll('th.ipl-sortable .ipl-sort-icon').forEach(function(ic) { ic.textContent = '↕'; });
      th.querySelector('.ipl-sort-icon').textContent = sortAsc ? '↑' : '↓';

      var tbody = table.querySelector('tbody');
      var rows = Array.from(tbody.querySelectorAll('tr'));
      rows.sort(function(a, b) {
        var av = (a.cells[col] ? a.cells[col].textContent.trim() : '');
        var bv = (b.cells[col] ? b.cells[col].textContent.trim() : '');
        /* tri numérique pour les IPs (compare octet par octet) */
        if (col === 0) {
          var ap = av.split('.').map(Number), bp = bv.split('.').map(Number);
          for (var i = 0; i < 4; i++) {
            if ((ap[i]||0) !== (bp[i]||0)) return sortAsc ? (ap[i]||0) - (bp[i]||0) : (bp[i]||0) - (ap[i]||0);
          }
          return 0;
        }
        return sortAsc ? av.localeCompare(bv) : bv.localeCompare(av);
      });
      rows.forEach(function(r) { tbody.appendChild(r); });
    });
  });
})();
</script>

<!-- ── Section 2 : Blocage par URL ───────────────────────────────────────── -->
<h3 style="margin-top:2em;">{'Blocage par URL'|@translate}</h3>

<form method="post" action="" class="ipl-config">
  <input type="hidden" name="action" value="save_url_config">
  <p>
    <label><strong>{'Mots-clés bloqués dans l\'URL'|@translate}</strong>
      <em style="font-weight:normal;font-size:0.85em;color:#666;"> &mdash; {'un par ligne — tout accès dont l\'URL contient un de ces mots sera bloqué'|@translate}</em>
    </label>
    <textarea name="blocked_url_keywords" rows="5" style="width:400px;display:block;margin:0.3em 0 0.5em 0;" placeholder="">{$BLOCKED_URL_KEYWORDS|escape}</textarea>
  </p>
  <button type="submit" class="buttonLike">{'Enregistrer la configuration'|@translate}</button>
</form>

<!-- ── Section 3 : Blocage par pays ──────────────────────────────────────── -->
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
