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
    <label><strong>{'IPs toujours autorisées'|@translate}</strong> (une par ligne) :</label><br>
    <textarea name="whitelist_ips" rows="5" style="width:400px;">{$WHITELIST_IPS|escape}</textarea>
  </p>
  <p>
    <label class="ipl-inline">
      <input type="checkbox" name="htaccess_enabled" value="1"{if $HTACCESS_ENABLED} checked{/if}>
      <strong style="display:inline;">{'Activer le blocage .htaccess'|@translate}</strong>
    </label>
    <em style="display:block;margin-left:20px;font-size:0.85em;color:#666;">{'Quand désactivé, les IPs restent dans la liste mais le bloc .htaccess est supprimé.'|@translate}</em>
  </p>
  <p>
    <label><strong>{'Vidage automatique'|@translate}</strong> &mdash; {'supprimer les plus anciennes entrées au-delà de'|@translate}
      <input type="number" name="max_records" value="{$MAX_RECORDS}" min="0" style="width:80px;display:inline;"> {'enregistrements'|@translate}
      <em style="font-size:0.85em;color:#666;">({'0 = désactivé'|@translate})</em>
    </label>
  </p>
  <button type="submit" class="buttonLike">{'Enregistrer la configuration'|@translate}</button>
</form>

<form method="post" action="" class="ipl-config" style="margin-top:1.5em;">
  <input type="hidden" name="action" value="import_ips">
  <p>
    <label><strong>{'Import d\'IPs dans la blocklist'|@translate}</strong> &mdash;
    {'une IP ou préfixe par ligne (ex: 82.97 ou 74.7.23.45 ou 74.7.0.0/24)'|@translate} :</label><br>
    <textarea name="import_ips" rows="6" style="width:400px;" placeholder=""></textarea>
  </p>
  <button type="submit" class="buttonLike">{'Importer'|@translate}</button>
</form>
