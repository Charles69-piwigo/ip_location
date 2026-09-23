
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

<!-- Détail des visites comptabilisées par le widget ci-dessus (audit) -->
<details class="ipl-visitor-detail" style="margin:0 0 1.5em 0;">
  <summary>{'Détail des visites comptabilisées'|@translate} ({$VISITOR_DETAIL_ROWS|@count})</summary>
  {if $VISITOR_DETAIL_ROWS|@count == 0}
  <p style="color:#666;">{'Aucune visite comptabilisée sur la période configurée.'|@translate}</p>
  {else}
  <div style="max-height:400px;overflow-y:auto;margin-top:0.5em;">
    <table class="ipl-table">
      <thead>
        <tr>
          <th>{'Date'|@translate}</th>
          <th>{'IP'|@translate}</th>
          <th>{'Pays'|@translate}</th>
          <th>{'URL'|@translate}</th>
        </tr>
      </thead>
      <tbody>
      {foreach from=$VISITOR_DETAIL_ROWS item=v}
        <tr>
          <td class="ipl-date">{$v.visit_date|escape}</td>
          <td>{$v.ip|escape}</td>
          <td>{$v.country|escape}</td>
          <td style="word-break:break-all;">{$v.url|escape}</td>
        </tr>
      {/foreach}
      </tbody>
    </table>
  </div>
  {/if}
</details>

<!-- ── Section 1 : Blocage .htaccess ─────────────────────────────────────── -->
<h3>{'Blocage .htaccess'|@translate}{if $SERVER_IS_NGINX} <span style="font-size:0.75em;font-weight:normal;color:#c0392b;">&#9888; {'Serveur nginx détecté : le fichier .htaccess est ignoré'|@translate}</span>{/if}</h3>
<div style="margin:0 0 1em 20px;text-align:left;">
  <label class="ipl-inline">
    <input type="checkbox" name="htaccess_enabled" value="1" form="form_htaccess_config"{if $HTACCESS_ENABLED} checked{/if}>
    <strong style="display:inline;">{'Activer le blocage .htaccess'|@translate}</strong>
  </label>
  <em style="display:block;margin-left:20px;font-size:0.85em;color:#666;">{'Quand désactivé, les IPs restent dans la liste mais le bloc .htaccess est supprimé.'|@translate}</em>
</div>

<!-- Tableau des IPs bloquées manuellement (toujours affiché) -->
{if $BLOCKLIST_MANUAL|@count == 0}
<p style="margin-left:20px;color:#666;">{'Aucune IP bloquée manuellement.'|@translate}</p>
{else}
<table class="ipl-table" id="ipl-blocklist-manual">
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
  {foreach from=$BLOCKLIST_MANUAL item=bl}
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

<!-- Tableau des IPs bloquées automatiquement (score bot) — dépliable/repliable, pour ne pas
     noyer la liste manuelle sur les sites très ciblés où l'auto-blocage produit beaucoup d'entrées -->
<details class="ipl-blocklist-auto-details" style="margin:0.8em 0 0 20px;">
  <summary>{'IP bloquées automatiquement'|@translate} ({$BLOCKLIST_AUTO|@count})</summary>
  {if $BLOCKLIST_AUTO|@count == 0}
  <p style="color:#666;">{'Aucune IP bloquée automatiquement.'|@translate}</p>
  {else}
  <table class="ipl-table" id="ipl-blocklist-auto" style="margin-top:0.5em;">
    <thead>
      <tr>
        <th class="ipl-sortable" data-col="0" style="cursor:pointer;">{'IP'|@translate} <span class="ipl-sort-icon">↕</span></th>
        <th class="ipl-sortable" data-col="1" style="cursor:pointer;">{'Date'|@translate} <span class="ipl-sort-icon">↕</span></th>
        <th class="ipl-sortable" data-col="2" style="cursor:pointer;">{'Pays'|@translate} <span class="ipl-sort-icon">↕</span></th>
        <th>{'Ville'|@translate}</th>
        <th>{'Expiration'|@translate}</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$BLOCKLIST_AUTO item=bl}
      <tr>
        <td><strong>{$bl.ip|escape}</strong></td>
        <td>{$bl.blocked_at|escape}</td>
        <td>{$bl.country|escape}</td>
        <td>{$bl.city|escape}</td>
        <td>{if $bl.expires_at}{'expire le'|@translate} {$bl.expires_at|escape}{/if}</td>
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
</details>

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
  function attachSort(tableId) {
    var table = document.getElementById(tableId);
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
  }
  attachSort('ipl-blocklist-manual');
  attachSort('ipl-blocklist-auto');
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

<!-- ── Section 4 : Filtre pays sur les téléchargements ───────────────────── -->
<h3 style="margin-top:2em;">{'Filtre pays sur les téléchargements'|@translate}</h3>

{if $GUEST_ENABLED_HIGH}
<form method="post" action="" class="ipl-config">
  <input type="hidden" name="action" value="save_download_config">
  <p>
    <label class="ipl-inline">
      <input type="checkbox" name="download_filter_enabled" value="1"{if $DOWNLOAD_FILTER_ENABLED} checked{/if}>
      <strong style="display:inline;">{'Activer le filtre pays sur les téléchargements'|@translate}</strong>
    </label>
    <em style="display:block;margin-left:20px;font-size:0.85em;color:#666;">{'Fonctionne en liste blanche : seuls les téléchargements d\'originaux provenant des pays listés ci-dessous sont autorisés. Liste vide = filtre inactif.'|@translate}</em>
  </p>
  <p>
    <label><strong>{'Pays autorisés'|@translate}</strong> (codes ISO séparés par virgule, ex: FR,BE,CH) :</label><br>
    <input type="text" name="download_allowed_countries" value="{$DOWNLOAD_ALLOWED_COUNTRIES|escape}"
           style="width:400px;" placeholder="">
  </p>
  <p>
    <label><strong>{'En cas d\'échec de géolocalisation'|@translate}</strong>
      <select name="download_geo_fail_mode" style="margin-left:10px;">
        <option value="open"{if $DOWNLOAD_GEO_FAIL_MODE eq 'open'} selected{/if}>{'Autoriser'|@translate}</option>
        <option value="closed"{if $DOWNLOAD_GEO_FAIL_MODE eq 'closed'} selected{/if}>{'Bloquer (recommandé)'|@translate}</option>
      </select>
    </label>
    <em style="display:block;margin-left:20px;font-size:0.85em;color:#666;">{'"Bloquer" (par défaut) refuse le téléchargement si la géolocalisation échoue — c\'est précisément le cas lors d\'un afflux de robots qui sature les fournisseurs de géolocalisation. "Autoriser" laisse passer et journalise (non bloqué) pour mesurer la fréquence réelle des échecs — voir la section Aide.'|@translate}</em>
  </p>
  <button type="submit" class="buttonLike">{'Enregistrer la configuration'|@translate}</button>
</form>
{else}
<p style="margin-left:20px;color:#888;">{'Les invités n\'ont pas la permission de télécharger les originaux : filtre sans objet.'|@translate}</p>
{/if}

<!-- ── Section 5 : Blocage automatique par score de suspicion bot ───────── -->
<h3 style="margin-top:2em;">{'Blocage automatique par score de suspicion bot'|@translate}{if $SERVER_IS_NGINX} <span style="font-size:0.75em;font-weight:normal;color:#c0392b;">&#9888; {'Serveur nginx détecté : le fichier .htaccess est ignoré'|@translate}</span>{/if}</h3>

{if !$HTACCESS_ENABLED}
<p style="margin-left:20px;color:#888;">{'Nécessite d\'abord d\'activer le blocage .htaccess ci-dessus : une IP ajoutée ici ne serait sinon jamais réellement bloquée.'|@translate}</p>
{/if}
<form method="post" action="" class="ipl-config">
  <input type="hidden" name="action" value="save_bot_block_config">
  <p>
    <label class="ipl-inline">
      <input type="checkbox" name="bot_block_enabled" value="1"{if $BOT_BLOCK_ENABLED} checked{/if}{if !$HTACCESS_ENABLED} disabled{/if}>
      <strong style="display:inline;">{'Activer le blocage automatique'|@translate}</strong>
    </label>
    <em style="display:block;margin-left:20px;font-size:0.85em;color:#666;">{'Ajoute automatiquement au .htaccess les IP détectées comme bots, pour une durée limitée (jamais permanent, jamais de plage — seulement l\'IP exacte).'|@translate}</em>
  </p>
  <p style="margin-left:20px;">
    <label class="ipl-inline">
      <input type="radio" name="bot_block_mode" value="score"{if $BOT_BLOCK_MODE eq 'score'} checked{/if}>
      {'Score de suspicion &ge;'|@translate}
      <input type="range" name="bot_block_score_threshold" min="10" max="90" step="5" value="{$BOT_BLOCK_SCORE_THRESHOLD}"
             oninput="this.nextElementSibling.textContent=this.value" style="vertical-align:middle;width:160px;">
      <span>{$BOT_BLOCK_SCORE_THRESHOLD}</span>
    </label>
    <em style="display:block;margin-left:24px;font-size:0.85em;color:#666;">{'Le score de chaque accès est visible dans la colonne « Score » du Journal — à consulter avant de fixer ce seuil.'|@translate}</em>
  </p>
  <p style="margin-left:20px;">
    <label class="ipl-inline">
      <input type="radio" name="bot_block_mode" value="is_bot"{if $BOT_BLOCK_MODE eq 'is_bot'} checked{/if}>
      {'Détection bot standard (is_bot)'|@translate}
    </label>
    <em style="display:block;margin-left:24px;font-size:0.85em;color:#666;">{'Mode simple : bloque dès qu\'un accès est marqué bot dans le Journal, sans passer par le score.'|@translate}</em>
  </p>
  <button type="submit" class="buttonLike">{'Enregistrer la configuration'|@translate}</button>
</form>
