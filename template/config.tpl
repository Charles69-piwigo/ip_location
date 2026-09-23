{* ─────────────────────────────────────────────────────────────────────────────
   Onglet Configuration — présentation en blocs (v2.6.3, maquette validée).
   Un formulaire par bloc (les cases non cochées ne sont pas transmises : un seul
   formulaire global remettrait à zéro les interrupteurs des autres blocs).
   ───────────────────────────────────────────────────────────────────────────── *}
<style>
  .iplc{ --c-bg:#f6f2ea; --c-surface:#fffdf9; --c-surface-2:#efe8db; --c-surface-3:#e6ddcb;
         --c-border:#ddd2ba; --c-border-strong:#c9bb9c; --c-text:#221c11; --c-text-2:#5c5140; --c-muted:#8c7f68;
         --c-accent:#a04e1c; --c-accent-ink:#fff8ef; --c-accent-wash:#f1decb;
         --c-on:#2f7a4f; --c-on-wash:#dcefe2; --c-off:#8c7f68; --c-off-wash:#ebe4d6;
         --c-warn:#9a6a00; --c-danger:#b23a2b; --c-danger-wash:#f5dcd6;
         margin:12px 10px 24px; width:calc(100% - 20px); max-width:1240px; display:flex; flex-direction:column; gap:20px; text-align:left;
         color:var(--c-text); font-size:13.5px; line-height:1.5; }
  .iplc *{ box-sizing:border-box; }
  /* L'admin Piwigo centre titres et paragraphes : on force l'alignement à gauche dans les blocs */
  .iplc h2, .iplc h3, .iplc h4, .iplc p, .iplc label, .iplc summary, .iplc td, .iplc th{ text-align:left; }
  .iplc h2, .iplc h3, .iplc h4{ padding:0; }
  .iplc .mono{ font-family:"IBM Plex Mono", Consolas, monospace; font-variant-numeric:tabular-nums; }

  .iplc-mode{ display:flex; align-items:center; gap:16px; flex-wrap:wrap; background:var(--c-surface); border:1px solid var(--c-border); border-radius:12px; padding:12px 16px; }
  .iplc-mode .eyebrow{ font-size:11px; letter-spacing:.09em; text-transform:uppercase; color:var(--c-muted); font-weight:500; }
  .iplc-mode .name{ font-size:18px; font-weight:600; display:block; }
  .iplc-mode .name.observer{ color:var(--c-on); } .iplc-mode .name.blocking{ color:var(--c-accent); }
  .iplc-mode .desc{ font-size:12.5px; color:var(--c-muted); display:block; }
  .iplc-mode .desc.unsaved{ color:var(--c-warn); font-weight:500; }
  .iplc-mode .desc.unsaved[hidden]{ display:none; }
  .iplc-levers{ display:flex; gap:6px; flex-wrap:wrap; margin-left:auto; }
  .iplc-lever{ display:inline-flex; align-items:center; gap:6px; font-size:12px; padding:3px 10px; border-radius:20px; border:1px solid var(--c-border); background:var(--c-surface-2); color:var(--c-muted); text-decoration:none; }
  .iplc-lever .dot{ width:7px; height:7px; border-radius:50%; background:var(--c-off); }
  .iplc-lever.on{ color:var(--c-text); border-color:var(--c-border-strong); } .iplc-lever.on .dot{ background:var(--c-accent); }

  .iplc-group{ display:flex; align-items:baseline; gap:10px; margin:4px 0 -8px; }
  .iplc-group h3{ font-size:17px; font-weight:600; margin:0; }
  .iplc-group p{ margin:0; font-size:12.5px; color:var(--c-muted); }
  .iplc-grid{ display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:16px; align-items:start; }
  .iplc-grid .wide{ grid-column:1 / -1; }
  @media (max-width:900px){ .iplc-grid{ grid-template-columns:minmax(0,1fr); } }

  .iplc-card{ background:var(--c-surface); border:1px solid var(--c-border); border-radius:12px; display:flex; flex-direction:column; min-width:0; scroll-margin-top:60px; }
  .iplc-card form{ margin:0; }
  .iplc-head{ display:flex; align-items:flex-start; gap:12px; padding:14px 16px 8px; }
  .iplc-title{ display:flex; flex-direction:column; gap:2px; flex:1; min-width:0; }
  .iplc-title h4{ margin:0; font-size:14.5px; font-weight:600; color:var(--c-text); border:none; padding:0; }
  .iplc-title .sub{ font-size:12.3px; color:var(--c-muted); }
  .iplc-pill{ font-size:10.5px; font-weight:600; letter-spacing:.04em; text-transform:uppercase; padding:3px 9px; border-radius:20px; white-space:nowrap; margin-top:2px; }
  .iplc-pill.on{ background:var(--c-on-wash); color:var(--c-on); } .iplc-pill.off{ background:var(--c-off-wash); color:var(--c-off); }
  .iplc-pill.info{ background:var(--c-accent-wash); color:var(--c-accent); }

  .iplc-switch{ position:relative; display:inline-flex; flex:none; margin-top:1px; }
  .iplc-switch input{ position:absolute; opacity:0; width:100%; height:100%; margin:0; cursor:pointer; z-index:1; }
  .iplc-switch .track{ width:40px; height:22px; border-radius:22px; background:var(--c-surface-3); border:1px solid var(--c-border-strong); position:relative; transition:background .15s; }
  .iplc-switch .track::after{ content:""; position:absolute; top:2px; left:2px; width:16px; height:16px; border-radius:50%; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.25); transition:transform .15s; }
  .iplc-switch input:checked + .track{ background:var(--c-accent); border-color:var(--c-accent); }
  .iplc-switch input:checked + .track::after{ transform:translateX(18px); }
  .iplc-switch input:focus-visible + .track{ outline:2px solid var(--c-accent); outline-offset:2px; }

  .iplc-body{ padding:4px 16px 14px; display:flex; flex-direction:column; gap:12px; }
  .iplc-card.is-off .dimmable{ opacity:.55; }
  .iplc-offnote{ font-size:12.3px; color:var(--c-text-2); background:var(--c-off-wash); border-radius:8px; padding:7px 10px; }
  .iplc-card:not(.is-off) .iplc-offnote{ display:none; }
  .iplc-foot{ display:flex; align-items:center; gap:10px; justify-content:flex-end; padding:9px 16px; border-top:1px solid var(--c-border); background:var(--c-surface-2); border-radius:0 0 12px 12px; }
  .iplc-foot .dirty{ font-size:12.3px; color:var(--c-warn); font-weight:500; margin-right:auto; }
  .iplc-foot .dirty[hidden]{ display:none; }

  .iplc label.field{ display:flex; flex-direction:column; gap:4px; font-size:12.5px; color:var(--c-text-2); font-weight:500; }
  .iplc input[type="text"], .iplc input[type="number"], .iplc input[type="date"], .iplc select, .iplc textarea{
    font-family:inherit; font-size:13px; color:var(--c-text); background:var(--c-surface-2); border:1px solid var(--c-border-strong); border-radius:7px; padding:5px 9px; margin:0; }
  .iplc .hint{ font-size:12px; color:var(--c-muted); margin:0; }
  .iplc .warn{ font-size:12.3px; color:var(--c-warn); margin:0; }

  .iplc-btn{ font-family:inherit; font-size:12.5px; font-weight:600; border-radius:7px; cursor:pointer; padding:6px 13px; border:1px solid transparent; white-space:nowrap; }
  .iplc-btn.primary{ background:var(--c-accent); color:var(--c-accent-ink); }
  .iplc-btn.primary:disabled{ opacity:.45; cursor:default; }
  .iplc-btn.ghost{ background:transparent; border-color:var(--c-border-strong); color:var(--c-text); }
  .iplc-btn.ghost:hover{ background:var(--c-surface-3); }
  .iplc-btn.sm{ padding:3px 9px; font-size:11.8px; }

  .iplc-chips{ display:flex; flex-wrap:wrap; gap:6px; }
  .iplc-chip{ display:inline-flex; align-items:center; gap:6px; font-size:12.3px; padding:2px 4px 2px 10px; border-radius:20px; background:var(--c-surface-2); border:1px solid var(--c-border); }
  .iplc-chip .meta{ font-family:"IBM Plex Mono", Consolas, monospace; font-size:10.8px; color:var(--c-muted); }
  .iplc-chip button{ border:none; background:none; color:var(--c-muted); cursor:pointer; font-size:12px; padding:2px 5px; border-radius:10px; }
  .iplc-chip button:hover{ color:var(--c-danger); background:var(--c-surface-3); }
  .iplc-addrow{ display:flex; gap:8px; flex-wrap:wrap; }
  .iplc-addrow input[type="text"]{ flex:1; min-width:140px; }

  .iplc-tiles{ display:flex; gap:10px; flex-wrap:wrap; }
  .iplc-tile{ flex:1; min-width:110px; background:var(--c-surface-2); border-radius:9px; padding:7px 12px; display:flex; flex-direction:column; }
  .iplc-tile .n{ font-family:"IBM Plex Mono", Consolas, monospace; font-size:17px; font-weight:500; }
  .iplc-tile .l{ font-size:11.3px; color:var(--c-muted); }

  .iplc-list{ border:1px solid var(--c-border); border-radius:9px; overflow:hidden; }
  .iplc-scroll{ max-height:300px; overflow:auto; }
  table.iplc-t{ width:100%; border-collapse:collapse; font-size:12.5px; margin:0; }
  table.iplc-t th{ position:sticky; top:0; background:var(--c-surface-2); text-align:left; font-weight:600; font-size:10.8px; letter-spacing:.05em; text-transform:uppercase; color:var(--c-muted); padding:6px 10px; border-bottom:1px solid var(--c-border); }
  table.iplc-t td{ padding:5px 10px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
  table.iplc-t tr:last-child td{ border-bottom:none; }
  table.iplc-t td.num, table.iplc-t th.num{ text-align:right; }
  table.iplc-t .ip{ font-family:"IBM Plex Mono", Consolas, monospace; }
  table.iplc-t .muted{ color:var(--c-muted); }
  table.iplc-t tr.fam td{ background:var(--c-surface-2); font-size:10.8px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:var(--c-muted); }
  table.iplc-t tr.removed td{ opacity:.4; text-decoration:line-through; }
  table.iplc-t td form{ display:inline; margin:0; }
  .iplc-tag{ font-size:10px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--c-accent-wash); color:var(--c-accent); margin-left:6px; }
  .iplc-verif{ font-size:11.3px; font-weight:500; } .iplc-verif.ok{ color:var(--c-on); } .iplc-verif.na{ color:var(--c-muted); }

  .iplc-seg{ display:inline-flex; gap:2px; background:var(--c-surface-2); border:1px solid var(--c-border); border-radius:8px; padding:2px; flex-wrap:wrap; }
  .iplc-seg button{ font-family:inherit; font-size:12px; font-weight:600; color:var(--c-text-2); background:none; border:none; border-radius:6px; padding:4px 10px; cursor:pointer; }
  .iplc-seg button.active{ background:var(--c-surface); color:var(--c-text); box-shadow:0 1px 3px rgba(0,0,0,.12); }
  .iplc-status{ display:inline-flex; border:1px solid var(--c-border-strong); border-radius:6px; overflow:hidden; }
  .iplc-status label{ font-size:11.5px; padding:2px 9px; cursor:pointer; background:var(--c-surface); color:var(--c-text-2); margin:0; }
  .iplc-status label + label{ border-left:1px solid var(--c-border-strong); }
  .iplc-status input{ display:none; }
  .iplc-status input.allow:checked + span{ color:var(--c-on); font-weight:600; }
  .iplc-status label.allow-on{ background:var(--c-on-wash); } .iplc-status label.block-on{ background:var(--c-danger-wash); color:var(--c-danger); font-weight:600; }
  .iplc-remove{ cursor:pointer; color:var(--c-muted); font-size:13px; }
  .iplc-remove input{ display:none; }

  details.iplc-fold > summary{ cursor:pointer; font-size:12.8px; color:var(--c-text-2); font-weight:500; list-style:none; }
  details.iplc-fold > summary::-webkit-details-marker{ display:none; }
  details.iplc-fold > summary::before{ content:"▸ "; font-size:10px; color:var(--c-muted); }
  details.iplc-fold[open] > summary::before{ content:"▾ "; }
  details.iplc-fold > .fold-body{ margin-top:10px; display:flex; flex-direction:column; gap:10px; }
  .iplc-threshold{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
  .iplc-threshold input[type="range"]{ flex:1; min-width:160px; accent-color:var(--c-accent); }
  .iplc-threshold .val{ font-family:"IBM Plex Mono", Consolas, monospace; font-size:19px; min-width:2.2ch; text-align:right; }
  .iplc-radio{ display:flex; gap:14px; flex-wrap:wrap; align-items:center; font-size:12.8px; }
  .iplc-radio label{ display:inline-flex; align-items:center; gap:5px; cursor:pointer; margin:0; }
  [data-row-hidden]{ display:none; }
</style>

<div class="iplc">

  {* ── Bandeau de mode ── *}
  <section class="iplc-mode" id="iplc-mode"
           data-observer-name="{'Observateur'|@translate}"
           data-observer-desc="{'Aucun accès n\'est refusé : tout est journalisé en « Normal » ou « Bots non bloqués ».'|@translate}"
           data-blocking-name="{'Observation + blocage'|@translate}"
           data-blocking-desc="{'leviers de blocage actifs'|@translate}"
           data-unsaved="{'modifications non enregistrées'|@translate}">
    <div>
      <span class="eyebrow">{'Mode actuel'|@translate}</span>
      <span class="name {if $LEVERS_ON == 0}observer{else}blocking{/if}" data-mode-name>{if $LEVERS_ON == 0}{'Observateur'|@translate}{else}{'Observation + blocage'|@translate}{/if}</span>
      <span class="desc" data-mode-desc>{if $LEVERS_ON == 0}{'Aucun accès n\'est refusé : tout est journalisé en « Normal » ou « Bots non bloqués ».'|@translate}{else}{$LEVERS_ON} / 5 {'leviers de blocage actifs'|@translate}{/if}</span>
      <span class="desc unsaved" data-mode-unsaved hidden></span>
    </div>
    <div class="iplc-levers">
      {foreach from=$LEVERS item=l}
        <a class="iplc-lever{if $l.on} on{/if}" href="#ipl-card-{$l.key}" data-lever="{$l.key}"><span class="dot"></span>{$l.label|escape}</a>
      {/foreach}
    </div>
  </section>

  {* ═════════════════ Observation ═════════════════ *}
  <div class="iplc-group"><h3>{'Observation'|@translate}</h3><p>{'toujours actif : le journal enregistre chaque accès invité'|@translate}</p></div>
  <div class="iplc-grid">

    {* Widget Visiteurs *}
    <section class="iplc-card{if !$VISITORS_ENABLED} is-off{/if}" id="ipl-card-visitors">
      <form method="post" action="" data-card-form id="iplc-f-visitors">
        <input type="hidden" name="action" value="save_visitors_config">
        <div class="iplc-head">
          <div class="iplc-title"><h4>{'Widget « Visiteurs »'|@translate}</h4><span class="sub">{'Compteur public de visites qualifiées, par pays'|@translate}</span></div>
          <span class="iplc-pill {if $VISITORS_ENABLED}on{else}off{/if}" data-pill data-on="{'Affiché'|@translate}" data-off="{'Masqué'|@translate}">{if $VISITORS_ENABLED}{'Affiché'|@translate}{else}{'Masqué'|@translate}{/if}</span>
          <label class="iplc-switch" title="{'Afficher le widget'|@translate}"><input type="checkbox" name="visitors_enabled" value="1" data-card-switch{if $VISITORS_ENABLED} checked{/if}><span class="track"></span></label>
        </div>
        <div class="iplc-body">
          <div class="iplc-offnote">{'Widget masqué sur la galerie. Le journal continue d\'enregistrer.'|@translate}</div>
          <div class="dimmable" style="display:flex;flex-direction:column;gap:12px;">
            <label class="field">{'Période affichée'|@translate}
              <select name="visitors_period">
                <option value="week"{if $VISITORS_PERIOD eq 'week'} selected{/if}>{'Semaine (7 jours)'|@translate}</option>
                <option value="fortnight"{if $VISITORS_PERIOD eq 'fortnight'} selected{/if}>{'Quinzaine (15 jours)'|@translate}</option>
                <option value="month"{if $VISITORS_PERIOD eq 'month'} selected{/if}>{'Mois (30 jours)'|@translate}</option>
                <option value="quarter"{if $VISITORS_PERIOD eq 'quarter'} selected{/if}>{'Trimestre (90 jours)'|@translate}</option>
              </select>
            </label>
            <div class="iplc-tiles">
              <div class="iplc-tile"><span class="n">{$VISITOR_DETAIL_ROWS|@count}</span><span class="l">{'visites comptées'|@translate}</span></div>
              <div class="iplc-tile"><span class="n">{$VISITOR_COUNTRIES}</span><span class="l">{'pays'|@translate}</span></div>
            </div>
            <p class="hint">{'Ne compte que les visites d\'album ou de photo précédées d\'un passage sur la page d\'accueil (±30 min).'|@translate}</p>
          </div>
        </div>
      </form>
      <div class="iplc-body" style="padding-top:0;">
        <details class="iplc-fold">
          <summary>{'Détail des visites comptabilisées'|@translate} ({$VISITOR_DETAIL_ROWS|@count})</summary>
          <div class="fold-body">
            {if $VISITOR_DETAIL_ROWS|@count == 0}
              <p class="hint">{'Aucune visite comptabilisée sur la période configurée.'|@translate}</p>
            {else}
              <div class="iplc-list"><div class="iplc-scroll">
                <table class="iplc-t"><thead><tr><th>{'Date'|@translate}</th><th>{'IP'|@translate}</th><th>{'Pays'|@translate}</th><th>{'URL'|@translate}</th></tr></thead><tbody>
                {foreach from=$VISITOR_DETAIL_ROWS item=v}
                  <tr><td class="mono">{$v.visit_date|escape}</td><td class="ip">{$v.ip|escape}</td><td>{$v.country|escape}</td><td style="word-break:break-all;">{$v.url|escape}</td></tr>
                {/foreach}
                </tbody></table>
              </div></div>
            {/if}
          </div>
        </details>
      </div>
      <div class="iplc-foot"><span class="dirty" hidden>{'Modifications non enregistrées'|@translate}</span><button type="submit" class="iplc-btn primary" form="iplc-f-visitors" data-save disabled>{'Enregistrer'|@translate}</button></div>
    </section>

    {* Conservation du journal *}
    <section class="iplc-card" id="ipl-card-retention">
      <form method="post" action="" data-card-form id="iplc-f-retention">
        <input type="hidden" name="action" value="save_retention">
        <div class="iplc-head">
          <div class="iplc-title"><h4>{'Conservation du journal'|@translate}</h4><span class="sub">{$TOTAL_ALL} {'accès enregistrés'|@translate}{if $LOG_FIRST} · {$LOG_FIRST} → {$LOG_LAST}{/if}</span></div>
          <span class="iplc-pill info">{'Journal'|@translate}</span>
        </div>
        <div class="iplc-body">
          <label class="field">{'Nombre maximal d\'accès conservés'|@translate}
            <input type="number" name="max_records" value="{$MAX_RECORDS}" min="0" step="1000" style="max-width:180px;">
          </label>
          <p class="hint">{'Au-delà, les plus anciens sont supprimés automatiquement. 0 = sans limite.'|@translate}</p>
        </div>
      </form>
      <div class="iplc-body" style="padding-top:0;">
        <details class="iplc-fold">
          <summary>{'Supprimer les accès anciens ou vider le cache de géolocalisation'|@translate}</summary>
          <div class="fold-body">
            <form method="post" action="" class="iplc-addrow">
              <input type="hidden" name="action" value="purge_before_date">
              <input type="date" name="before_date" required aria-label="{'Supprimer les logs avant le'|@translate}">
              <button type="submit" class="iplc-btn ghost sm">{'Supprimer les accès antérieurs à cette date'|@translate}</button>
            </form>
            <form method="post" action="" class="iplc-addrow" onsubmit="return confirm('{'Vider tout le cache de géolocalisation ?'|@translate|escape:javascript}');">
              <input type="hidden" name="action" value="purge_cache">
              <button type="submit" class="iplc-btn ghost sm">{'Vider le cache de géolocalisation'|@translate}</button>
              <span class="hint">{'Forcer la résolution géographique de toutes les IPs lors de leur prochaine visite.'|@translate}</span>
            </form>
          </div>
        </details>
      </div>
      <div class="iplc-foot"><span class="dirty" hidden>{'Modifications non enregistrées'|@translate}</span><button type="submit" class="iplc-btn primary" form="iplc-f-retention" data-save disabled>{'Enregistrer'|@translate}</button></div>
    </section>

    {* Liste blanche *}
    <section class="iplc-card wide" id="ipl-card-whitelist">
      <form method="post" action="" data-card-form id="iplc-f-whitelist">
        <input type="hidden" name="action" value="save_whitelist">
        <input type="hidden" name="whitelist_ips" id="iplc-wl-value" value="{$WHITELIST_IPS|escape}">
        <div class="iplc-head">
          <div class="iplc-title"><h4>{'IP jamais bloquées'|@translate}</h4><span class="sub">{'Liste blanche, prioritaire sur tous les leviers ci-dessous — ces IP ne sont pas non plus journalisées'|@translate}</span></div>
        </div>
        <div class="iplc-body">
          <div class="iplc-chips" data-chips="iplc-wl-value" data-sep="newline"></div>
          <div class="iplc-addrow">
            <input type="text" data-chip-input="iplc-wl-value" placeholder="{'Ajouter une IP (ex. 93.12.150.175)'|@translate}">
            <button type="button" class="iplc-btn ghost sm" data-chip-add="iplc-wl-value">{'Ajouter'|@translate}</button>
          </div>
          <p class="hint">{'Les IP du réseau local (192.168.x, 10.x…) ne sont de toute façon jamais bloquées automatiquement.'|@translate}</p>
        </div>
      </form>
      <div class="iplc-foot"><span class="dirty" hidden>{'Modifications non enregistrées'|@translate}</span><button type="submit" class="iplc-btn primary" form="iplc-f-whitelist" data-save disabled>{'Enregistrer'|@translate}</button></div>
    </section>
  </div>

  {* ═════════════════ Leviers de blocage ═════════════════ *}
  <div class="iplc-group"><h3>{'Leviers de blocage'|@translate}</h3><p>{'chacun n\'agit — et n\'alimente « Bloqués » — que s\'il est activé'|@translate}</p></div>
  <div class="iplc-grid">

    {* Robots d'indexation *}
    <section class="iplc-card wide{if !$ROBOTS_ENABLED} is-off{/if}" id="ipl-card-robots">
      <form method="post" action="" data-card-form id="iplc-f-robots">
        <input type="hidden" name="action" value="save_robots">
        <div class="iplc-head">
          <div class="iplc-title"><h4>{'Robots d\'indexation'|@translate}</h4><span class="sub">{'Un robot autorisé passe tous les leviers ci-dessous (pays, mot-clé, IP, score). Un robot bloqué reçoit un refus dès qu\'il se présente.'|@translate}</span></div>
          <span class="iplc-pill {if $ROBOTS_ENABLED}on{else}off{/if}" data-pill data-on="{'Actif'|@translate} · {if $ROBOTS_BLOCKED > 0}{$ROBOTS_BLOCKED} {'bloqué(s)'|@translate}{else}{'aucun blocage'|@translate}{/if}" data-off="{'Inactif'|@translate}">{if $ROBOTS_ENABLED}{'Actif'|@translate} · {if $ROBOTS_BLOCKED > 0}{$ROBOTS_BLOCKED} {'bloqué(s)'|@translate}{else}{'aucun blocage'|@translate}{/if}{else}{'Inactif'|@translate}{/if}</span>
          <label class="iplc-switch" title="{'Appliquer la liste des robots (autorisations et blocages)'|@translate}"><input type="checkbox" name="robots_enabled" value="1" data-card-switch{if $ROBOTS_ENABLED} checked{/if}><span class="track"></span></label>
        </div>
        <div class="iplc-body">
          <div class="iplc-offnote">{'Désactivé : les robots sont traités comme n\'importe quel visiteur (pays, mot-clé, liste IP, score) — ni protection, ni blocage. Les choix de la liste sont conservés pour la réactivation.'|@translate}</div>
          <div class="dimmable" style="display:flex;flex-direction:column;gap:12px;">
            <div class="iplc-tiles">
              <div class="iplc-tile"><span class="n">{$ROBOTS_ALLOWED}</span><span class="l">{'robots autorisés'|@translate}</span></div>
              <div class="iplc-tile"><span class="n">{$ROBOTS_BLOCKED}</span><span class="l">{'robots bloqués'|@translate}</span></div>
              <div class="iplc-tile"><span class="n">{$ROBOTS_SEEN_7D}</span><span class="l">{'passages de robots autorisés sur 7 j'|@translate}</span></div>
              <div class="iplc-tile" title="{'User-Agent d\'un moteur connu, mais l\'IP ne lui appartient pas (vérification DNS)'|@translate}"><span class="n" style="color:var(--c-danger)">{$ROBOTS_SPOOFED}</span><span class="l">{'faux robots démasqués sur 7 j'|@translate}</span></div>
            </div>
            <details class="iplc-fold">
              <summary>{'Liste des robots'|@translate} ({$ROBOT_ROWS|@count} · {$ROBOTS_BLOCKED} {'bloqué(s)'|@translate})</summary>
              <div class="fold-body">
                <div class="iplc-seg" data-robot-families>
                  <button type="button" class="active" data-fam="all">{'Tous'|@translate}</button>
                  <button type="button" data-fam="search">{'Moteurs de recherche'|@translate}</button>
                  <button type="button" data-fam="social">{'Aperçus de partage'|@translate}</button>
                  <button type="button" data-fam="ai">{'Robots d\'IA'|@translate}</button>
                </div>
                <div class="iplc-list"><div class="iplc-scroll" style="max-height:340px;">
                  <table class="iplc-t"><thead><tr><th>{'Robot'|@translate}</th><th>{'Reconnu par'|@translate}</th><th>{'Vérification'|@translate}</th><th class="num">{'Vu 7 j'|@translate}</th><th>{'Dernier passage'|@translate}</th><th>{'Statut'|@translate}</th><th></th></tr></thead>
                  <tbody>
                  {foreach from=$ROBOT_ROWS item=rb key=i}
                    <tr data-fam="{$rb.fam|escape}">
                      <td><b>{$rb.name|escape}</b>
                        <input type="hidden" name="robots[{$i}][name]" value="{$rb.name|escape}">
                        <input type="hidden" name="robots[{$i}][ua]" value="{$rb.ua|escape}">
                        <input type="hidden" name="robots[{$i}][fam]" value="{$rb.fam|escape}">
                        <input type="hidden" name="robots[{$i}][verify]" value="{$rb.verify|escape}"></td>
                      <td class="mono" style="font-size:11.5px;color:var(--c-text-2);">{$rb.ua|escape}</td>
                      <td>{if $rb.verifiable}<span class="iplc-verif ok" title="DNS → *.{$rb.verify|escape}">✓ {'vérifié DNS'|@translate}</span>{else}<span class="iplc-verif na" title="{'Le robot ne publie pas de méthode de vérification : confiance au User-Agent'|@translate}">{'non vérifiable'|@translate}</span>{/if}</td>
                      <td class="num mono">{$rb.seen}</td>
                      <td class="muted">{if $rb.last}{$rb.last|escape}{else}—{/if}</td>
                      <td><span class="iplc-status" data-status>
                        <label class="{if $rb.status neq 'block'}allow-on{/if}"><input type="radio" name="robots[{$i}][status]" value="allow"{if $rb.status neq 'block'} checked{/if}>{'Autorisé'|@translate}</label>
                        <label class="{if $rb.status eq 'block'}block-on{/if}"><input type="radio" name="robots[{$i}][status]" value="block"{if $rb.status eq 'block'} checked{/if}>{'Bloqué'|@translate}</label>
                      </span></td>
                      <td class="num"><label class="iplc-remove" title="{'Retirer ce robot de la liste (il redevient un robot inconnu, traité par le score)'|@translate}"><input type="checkbox" name="robots[{$i}][remove]" value="1" data-remove>✕</label></td>
                    </tr>
                  {/foreach}
                  </tbody></table>
                </div></div>
                <details class="iplc-fold">
                  <summary>{'Ajouter un robot'|@translate}</summary>
                  <div class="fold-body">
                    <div class="iplc-addrow">
                      <input type="text" name="new_robot_name" placeholder="{'Nom (ex. Yandex)'|@translate}" style="flex:0 1 160px;">
                      <input type="text" name="new_robot_ua" placeholder="{'Motif dans le User-Agent (ex. YandexBot)'|@translate}">
                      <select name="new_robot_fam">
                        <option value="search">{'Moteur de recherche'|@translate}</option>
                        <option value="social">{'Aperçu de partage'|@translate}</option>
                        <option value="ai">{'Robot d\'IA'|@translate}</option>
                      </select>
                    </div>
                    <p class="hint">{'Le robot est ajouté (autorisé) à l\'enregistrement. Un robot absent de cette liste reste traité par le score de suspicion.'|@translate}</p>
                  </div>
                </details>
              </div>
            </details>
            <p class="hint">{'« Vérifié DNS » : l\'IP est contrôlée auprès du moteur (DNS inverse puis direct, résultat gardé 30 jours). Un faux Googlebot est traité comme un visiteur ordinaire, et l\'usurpation augmente son score.'|@translate}</p>
          </div>
        </div>
      </form>
      <div class="iplc-foot"><span class="dirty" hidden>{'Modifications non enregistrées'|@translate}</span><button type="submit" class="iplc-btn primary" form="iplc-f-robots" data-save disabled>{'Enregistrer'|@translate}</button></div>
    </section>

    {* Blocage par IP (manuel) *}
    <section class="iplc-card wide{if !$HTACCESS_ENABLED} is-off{/if}" id="ipl-card-manual">
      <form method="post" action="" data-card-form id="iplc-f-manual">
        <input type="hidden" name="action" value="save_ip_block">
        <div class="iplc-head">
          <div class="iplc-title"><h4>{'Blocage par IP'|@translate}</h4><span class="sub">{'IP et plages /16 bloquées à la main — appliqué par Apache (.htaccess) et par le plugin'|@translate}{if $SERVER_IS_NGINX} · <span style="color:var(--c-danger)">&#9888; {'Serveur nginx détecté : le fichier .htaccess est ignoré'|@translate}</span>{/if}</span></div>
          <span class="iplc-pill {if $HTACCESS_ENABLED}on{else}off{/if}" data-pill data-on="{'Actif'|@translate} · {$MANUAL_ROWS|@count} {'entrée(s)'|@translate}" data-off="{'Inactif'|@translate}">{if $HTACCESS_ENABLED}{'Actif'|@translate} · {$MANUAL_ROWS|@count} {'entrée(s)'|@translate}{else}{'Inactif'|@translate}{/if}</span>
          <label class="iplc-switch" title="{'Activer le blocage par IP'|@translate}"><input type="checkbox" name="htaccess_enabled" value="1" data-card-switch{if $HTACCESS_ENABLED} checked{/if}><span class="track"></span></label>
        </div>
        <div class="iplc-body" style="padding-bottom:0;">
          <div class="iplc-offnote">{'Désactivé : la liste est conservée mais aucune de ces IP n\'est bloquée, et la section est retirée du .htaccess.'|@translate}</div>
        </div>
      </form>
      <div class="iplc-body dimmable">
        <form method="post" action="" class="iplc-addrow" onsubmit="var f=this.elements['ip'],v=f.value.trim();if(/^\d+\.\d+$/.test(v))f.value=v+'.0.0/16';">
          <input type="hidden" name="action" value="block_ip">
          <input type="text" name="ip" required placeholder="{'IP (ex. 213.209.159.133) ou plage (ex. 116.179.0.0/16)'|@translate}">
          <button type="submit" class="iplc-btn ghost sm">{'Bloquer'|@translate}</button>
        </form>
        <div class="iplc-list"><div class="iplc-scroll">
          <table class="iplc-t"><thead><tr><th>{'IP / plage'|@translate}</th><th>{'Pays'|@translate}</th><th>{'Ville'|@translate}</th><th>{'Depuis le'|@translate}</th><th class="num">{'Refus 7 j'|@translate}</th><th></th></tr></thead><tbody>
          {foreach from=$MANUAL_ROWS item=bl}
            <tr>
              <td><span class="ip">{$bl.ip|escape}</span>{if $bl.is_range}<span class="iplc-tag">{'plage'|@translate}</span>{/if}</td>
              <td>{$bl.country|escape}</td><td class="muted">{$bl.city|escape}</td>
              <td class="muted">{$bl.blocked_at|escape}</td>
              <td class="num mono">{$bl.refusals}</td>
              <td class="num"><form method="post" action=""><input type="hidden" name="action" value="unblock_ip"><input type="hidden" name="ip" value="{$bl.ip|escape}"><button type="submit" class="iplc-btn ghost sm">{'Débloquer'|@translate}</button></form></td>
            </tr>
          {foreachelse}
            <tr><td colspan="6" class="muted" style="text-align:center;padding:12px;">{'Aucune IP bloquée manuellement.'|@translate}</td></tr>
          {/foreach}
          </tbody></table>
        </div></div>
        <p class="hint">{'Dans le Journal, les boutons « Ajouter IP » / « Ajouter /16 » d\'une ligne ajoutent aussi une entrée ici.'|@translate}</p>
      </div>
      <div class="iplc-foot"><span class="dirty" hidden>{'Modifications non enregistrées'|@translate}</span><button type="submit" class="iplc-btn primary" form="iplc-f-manual" data-save disabled>{'Enregistrer'|@translate}</button></div>
    </section>

    {* Blocage par pays *}
    <section class="iplc-card{if !$BLOCKING_ENABLED} is-off{/if}" id="ipl-card-country">
      <form method="post" action="" data-card-form id="iplc-f-country">
        <input type="hidden" name="action" value="save_config">
        <input type="hidden" name="blocked_countries" id="iplc-cc-value" value="{$BLOCKED_COUNTRIES|escape}">
        <div class="iplc-head">
          <div class="iplc-title"><h4>{'Blocage par pays'|@translate}</h4><span class="sub">{'Refuse les pages aux visiteurs des pays listés'|@translate}</span></div>
          <span class="iplc-pill {if $BLOCKING_ENABLED}on{else}off{/if}" data-pill data-on="{'Actif'|@translate} · {$COUNTRY_ROWS|@count} {'pays'|@translate}" data-off="{'Inactif'|@translate}">{if $BLOCKING_ENABLED}{'Actif'|@translate} · {$COUNTRY_ROWS|@count} {'pays'|@translate}{else}{'Inactif'|@translate}{/if}</span>
          <label class="iplc-switch" title="{'Activer le blocage par pays'|@translate}"><input type="checkbox" name="blocking_enabled" value="1" data-card-switch{if $BLOCKING_ENABLED} checked{/if}><span class="track"></span></label>
        </div>
        <div class="iplc-body">
          <div class="iplc-offnote">{'Désactivé : la liste est conservée, aucun pays n\'est bloqué.'|@translate}</div>
          <div class="dimmable" style="display:flex;flex-direction:column;gap:12px;">
            <div class="iplc-chips" data-chips="iplc-cc-value" data-sep="comma" data-upper>
              {foreach from=$COUNTRY_ROWS item=c}<span class="iplc-chip" data-value="{$c.code|escape}">{$c.name|escape} ({$c.code|escape}) <span class="meta">{$c.refusals} {'refus'|@translate}</span><button type="button" aria-label="{'Retirer'|@translate}">✕</button></span>{/foreach}
            </div>
            <div class="iplc-addrow">
              <input type="text" data-chip-input="iplc-cc-value" maxlength="2" placeholder="{'Code pays (ex. CN)'|@translate}">
              <button type="button" class="iplc-btn ghost sm" data-chip-add="iplc-cc-value">{'Ajouter'|@translate}</button>
            </div>
            {if $GOOGLE_WARNING eq 'robots_off'}
              <p class="warn">&#9888; {'Googlebot sera bloqué : le bloc « Robots d\'indexation » est désactivé, les robots sont traités comme n\'importe quel visiteur des États-Unis.'|@translate}</p>
            {elseif $GOOGLE_WARNING eq 'googlebot_not_allowed'}
              <p class="warn">&#9888; {'Googlebot n\'est pas autorisé dans le bloc « Robots d\'indexation » : bloquer les États-Unis le bloque aussi, et le site disparaît de Google.'|@translate}</p>
            {else}
              <p class="hint">{'Les robots autorisés (bloc « Robots d\'indexation ») passent ce blocage.'|@translate} {'Chiffre de chaque pays : accès refusés sur 7 jours.'|@translate}</p>
            {/if}
          </div>
        </div>
      </form>
      <div class="iplc-foot"><span class="dirty" hidden>{'Modifications non enregistrées'|@translate}</span><button type="submit" class="iplc-btn primary" form="iplc-f-country" data-save disabled>{'Enregistrer'|@translate}</button></div>
    </section>

    {* Blocage par mot-clé *}
    <section class="iplc-card{if !$KEYWORD_BLOCK_ENABLED} is-off{/if}" id="ipl-card-keyword">
      <form method="post" action="" data-card-form id="iplc-f-keyword">
        <input type="hidden" name="action" value="save_url_config">
        <input type="hidden" name="blocked_url_keywords" id="iplc-kw-value" value="{$BLOCKED_URL_KEYWORDS|escape}">
        <div class="iplc-head">
          <div class="iplc-title"><h4>{'Blocage par mot-clé d\'URL'|@translate}</h4><span class="sub">{'Refuse toute URL qui contient l\'un de ces mots'|@translate}</span></div>
          <span class="iplc-pill {if $KEYWORD_BLOCK_ENABLED}on{else}off{/if}" data-pill data-on="{'Actif'|@translate} · {$KEYWORD_ROWS|@count} {'mot(s)'|@translate}" data-off="{'Inactif'|@translate}">{if $KEYWORD_BLOCK_ENABLED}{'Actif'|@translate} · {$KEYWORD_ROWS|@count} {'mot(s)'|@translate}{else}{'Inactif'|@translate}{/if}</span>
          <label class="iplc-switch" title="{'Activer le blocage par mot-clé'|@translate}"><input type="checkbox" name="keyword_block_enabled" value="1" data-card-switch{if $KEYWORD_BLOCK_ENABLED} checked{/if}><span class="track"></span></label>
        </div>
        <div class="iplc-body">
          <div class="iplc-offnote">{'Désactivé : les mots sont conservés, aucune URL n\'est bloquée.'|@translate}</div>
          <div class="dimmable" style="display:flex;flex-direction:column;gap:12px;">
            <div class="iplc-chips" data-chips="iplc-kw-value" data-sep="newline">
              {foreach from=$KEYWORD_ROWS item=k}<span class="iplc-chip" data-value="{$k.word|escape}">{$k.word|escape} <span class="meta">{$k.refusals}</span><button type="button" aria-label="{'Retirer'|@translate}">✕</button></span>{/foreach}
            </div>
            <div class="iplc-addrow">
              <input type="text" data-chip-input="iplc-kw-value" placeholder="{'Mot-clé (ex. wp-login)'|@translate}">
              <button type="button" class="iplc-btn ghost sm" data-chip-add="iplc-kw-value">{'Ajouter'|@translate}</button>
            </div>
            <p class="hint">{'Chiffre à droite de chaque mot : accès refusés sur 7 jours.'|@translate}</p>
          </div>
        </div>
      </form>
      <div class="iplc-foot"><span class="dirty" hidden>{'Modifications non enregistrées'|@translate}</span><button type="submit" class="iplc-btn primary" form="iplc-f-keyword" data-save disabled>{'Enregistrer'|@translate}</button></div>
    </section>

    {* Blocage automatique *}
    <section class="iplc-card wide{if !$BOT_BLOCK_ENABLED} is-off{/if}" id="ipl-card-auto">
      <form method="post" action="" data-card-form id="iplc-f-auto">
        <input type="hidden" name="action" value="save_bot_block_config">
        <div class="iplc-head">
          <div class="iplc-title"><h4>{'Blocage automatique par score'|@translate}</h4><span class="sub">{'Bloque pendant 14 jours chaque adresse jugée suspecte, une par une — jamais toute une plage d\'adresses, et jamais les appareils de votre réseau local.'|@translate}</span></div>
          <span class="iplc-pill {if $BOT_BLOCK_ENABLED}on{else}off{/if}" data-pill data-on="{'Actif'|@translate} · {'seuil'|@translate} {$BOT_BLOCK_SCORE_THRESHOLD}" data-off="{'Inactif'|@translate}">{if $BOT_BLOCK_ENABLED}{'Actif'|@translate} · {'seuil'|@translate} {$BOT_BLOCK_SCORE_THRESHOLD}{else}{'Inactif'|@translate}{/if}</span>
          <label class="iplc-switch" title="{'Activer le blocage automatique'|@translate}"><input type="checkbox" name="bot_block_enabled" value="1" data-card-switch{if $BOT_BLOCK_ENABLED} checked{/if}><span class="track"></span></label>
        </div>
        <div class="iplc-body">
          <div class="iplc-offnote">{'Désactivé : les IP listées ne sont plus bloquées (elles restent listées jusqu\'à leur expiration). À la réactivation, seule l\'activité suspecte postérieure compte.'|@translate}</div>
          <div class="dimmable" style="display:flex;flex-direction:column;gap:12px;">
            <div class="iplc-threshold">
              <label for="iplc-threshold" style="font-size:13px;color:var(--c-text-2);font-weight:500;margin:0;">{'Seuil de score'|@translate}</label>
              <input type="range" id="iplc-threshold" name="bot_block_score_threshold" min="10" max="90" step="5" value="{$BOT_BLOCK_SCORE_THRESHOLD}">
              <span class="val" id="iplc-threshold-val">{$BOT_BLOCK_SCORE_THRESHOLD}</span>
            </div>
            <div class="iplc-tiles">
              <div class="iplc-tile"><span class="n">{$BLOCKLIST_AUTO|@count}</span><span class="l">{'IP bloquées en ce moment'|@translate}</span></div>
              <div class="iplc-tile"><span class="n" id="iplc-would">—</span><span class="l">{'IP des dernières'|@translate} {$RECENT_HOURS} h {'au-dessus du seuil'|@translate}</span></div>
              <div class="iplc-tile"><span class="n">{$EXEMPT_COUNT}</span><span class="l">{'IP débloquées à la main (exemptées)'|@translate}</span></div>
            </div>
            <p class="hint">{'Le score de chaque accès est visible dans la colonne « Score » du Journal — à consulter avant de fixer ce seuil.'|@translate}</p>
          </div>
        </div>
      </form>
      <div class="iplc-body dimmable" style="padding-top:0;">
        <details class="iplc-fold">
          <summary>{'IP bloquées automatiquement'|@translate} ({$BLOCKLIST_AUTO|@count})</summary>
          <div class="fold-body">
            <input type="text" data-filter-table="iplc-auto-table" placeholder="{'Filtrer (IP ou pays)…'|@translate}" style="max-width:320px;">
            <div class="iplc-list"><div class="iplc-scroll">
              <table class="iplc-t" id="iplc-auto-table"><thead><tr><th>{'IP'|@translate}</th><th>{'Pays'|@translate}</th><th>{'Ville'|@translate}</th><th>{'Bloquée le'|@translate}</th><th>{'Expire le'|@translate}</th><th></th></tr></thead><tbody>
              {foreach from=$BLOCKLIST_AUTO item=bl}
                <tr>
                  <td class="ip">{$bl.ip|escape}</td><td>{$bl.country|escape}</td><td class="muted">{$bl.city|escape}</td>
                  <td class="muted">{$bl.blocked_at|escape}</td><td class="muted">{$bl.expires_at|escape}</td>
                  <td class="num"><form method="post" action=""><input type="hidden" name="action" value="unblock_ip"><input type="hidden" name="ip" value="{$bl.ip|escape}"><button type="submit" class="iplc-btn ghost sm">{'Débloquer'|@translate}</button></form></td>
                </tr>
              {foreachelse}
                <tr><td colspan="6" class="muted" style="text-align:center;padding:12px;">{'Aucune IP bloquée automatiquement.'|@translate}</td></tr>
              {/foreach}
              </tbody></table>
            </div></div>
          </div>
        </details>
      </div>
      <div class="iplc-foot"><span class="dirty" hidden>{'Modifications non enregistrées'|@translate}</span><button type="submit" class="iplc-btn primary" form="iplc-f-auto" data-save disabled>{'Enregistrer'|@translate}</button></div>
    </section>

    {* Filtre des téléchargements *}
    <section class="iplc-card wide{if !$DOWNLOAD_FILTER_ENABLED} is-off{/if}" id="ipl-card-download">
      {if $GUEST_ENABLED_HIGH}
      <form method="post" action="" data-card-form id="iplc-f-download">
        <input type="hidden" name="action" value="save_download_config">
        <input type="hidden" name="download_allowed_countries" id="iplc-dl-value" value="{$DOWNLOAD_ALLOWED_COUNTRIES|escape}">
        <div class="iplc-head">
          <div class="iplc-title"><h4>{'Filtre pays sur les téléchargements'|@translate}</h4><span class="sub">{'Seuls les pays listés peuvent télécharger les originaux (liste blanche)'|@translate}</span></div>
          <span class="iplc-pill {if $DOWNLOAD_FILTER_ENABLED}on{else}off{/if}" data-pill data-on="{'Actif'|@translate} · {$DOWNLOAD_ROWS|@count} {'pays autorisés'|@translate}" data-off="{'Inactif'|@translate}">{if $DOWNLOAD_FILTER_ENABLED}{'Actif'|@translate} · {$DOWNLOAD_ROWS|@count} {'pays autorisés'|@translate}{else}{'Inactif'|@translate}{/if}</span>
          <label class="iplc-switch" title="{'Activer le filtre pays sur les téléchargements'|@translate}"><input type="checkbox" name="download_filter_enabled" value="1" data-card-switch{if $DOWNLOAD_FILTER_ENABLED} checked{/if}><span class="track"></span></label>
        </div>
        <div class="iplc-body">
          <div class="iplc-offnote">{'Désactivé : tous les invités autorisés par Piwigo peuvent télécharger les originaux.'|@translate}</div>
          <div class="dimmable" style="display:flex;flex-direction:column;gap:12px;">
            <div class="iplc-chips" data-chips="iplc-dl-value" data-sep="comma" data-upper>
              {foreach from=$DOWNLOAD_ROWS item=c}<span class="iplc-chip" data-value="{$c.code|escape}">{$c.name|escape} ({$c.code|escape})<button type="button" aria-label="{'Retirer'|@translate}">✕</button></span>{/foreach}
            </div>
            <div class="iplc-addrow">
              <input type="text" data-chip-input="iplc-dl-value" maxlength="2" placeholder="{'Code pays autorisé (ex. LU)'|@translate}">
              <button type="button" class="iplc-btn ghost sm" data-chip-add="iplc-dl-value">{'Ajouter'|@translate}</button>
            </div>
            <div class="iplc-radio">
              <span style="color:var(--c-text-2);font-weight:500;">{'Si le pays est inconnu :'|@translate}</span>
              <label><input type="radio" name="download_geo_fail_mode" value="closed"{if $DOWNLOAD_GEO_FAIL_MODE neq 'open'} checked{/if}> {'refuser (recommandé)'|@translate}</label>
              <label><input type="radio" name="download_geo_fail_mode" value="open"{if $DOWNLOAD_GEO_FAIL_MODE eq 'open'} checked{/if}> {'autoriser'|@translate}</label>
            </div>
          </div>
        </div>
      </form>
      <div class="iplc-foot"><span class="dirty" hidden>{'Modifications non enregistrées'|@translate}</span><button type="submit" class="iplc-btn primary" form="iplc-f-download" data-save disabled>{'Enregistrer'|@translate}</button></div>
      {else}
      <div class="iplc-head"><div class="iplc-title"><h4>{'Filtre pays sur les téléchargements'|@translate}</h4><span class="sub">{'Les invités n\'ont pas la permission de télécharger les originaux : filtre sans objet.'|@translate}</span></div></div>
      {/if}
    </section>
  </div>
</div>

<script>
(function(){
  var root = document.querySelector('.iplc');
  if (!root) return;

  // Chaque bloc : son formulaire principal reçoit un id, et le bouton "Enregistrer" du
  // pied de carte s'y rattache (attribut form), actif seulement après une modification.
  var n = 0;
  root.querySelectorAll('.iplc-card').forEach(function(card){
    var form = card.querySelector('form[data-card-form]');
    var save = card.querySelector('[data-save]');
    var dirty = card.querySelector('.iplc-foot .dirty');
    if (!form || !save) return;
    form.id = form.id || ('iplc-form-' + (++n));
    save.setAttribute('form', form.id);
    function markDirty(){ save.disabled = false; if (dirty) dirty.hidden = false; }
    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);
    card._markDirty = markDirty;
    var sw = card.querySelector('[data-card-switch]');
    var pill = card.querySelector('[data-pill]');
    if (sw) sw.addEventListener('change', function(){
      card.classList.toggle('is-off', !sw.checked);
      // Étiquette d'état : suit l'interrupteur tout de suite, avant enregistrement
      if (pill) {
        pill.textContent = sw.checked ? pill.getAttribute('data-on') : pill.getAttribute('data-off');
        pill.classList.toggle('on', sw.checked);
        pill.classList.toggle('off', !sw.checked);
      }
      refreshMode();
    });
  });

  // Bandeau "Mode actuel" : recalculé à partir des interrupteurs de la page. Il reflète
  // l'état enregistré au chargement ; une bascule non enregistrée est signalée.
  var mode = document.getElementById('iplc-mode');
  function leverOn(key){
    var card = document.getElementById('ipl-card-' + key);
    var sw = card && card.querySelector('[data-card-switch]');
    if (!sw || !sw.checked) return false;
    // Robots : un levier de blocage seulement si au moins un robot est "Bloqué"
    if (key === 'robots') return card.querySelectorAll('input[type="radio"][value="block"]:checked').length > 0;
    return true;
  }
  var initialLevers = { };
  if (mode) mode.querySelectorAll('[data-lever]').forEach(function(a){ initialLevers[a.getAttribute('data-lever')] = a.classList.contains('on'); });
  function refreshMode(){
    if (!mode) return;
    var blocking = 0, changed = false;
    mode.querySelectorAll('[data-lever]').forEach(function(a){
      var key = a.getAttribute('data-lever');
      var on = leverOn(key);
      a.classList.toggle('on', on);
      if (on && key !== 'download') blocking++;
      if (on !== initialLevers[key]) changed = true;
    });
    var name = mode.querySelector('[data-mode-name]');
    name.textContent = blocking ? mode.getAttribute('data-blocking-name') : mode.getAttribute('data-observer-name');
    name.classList.toggle('observer', !blocking);
    name.classList.toggle('blocking', !!blocking);
    mode.querySelector('[data-mode-desc]').textContent = blocking
      ? blocking + ' / 5 ' + mode.getAttribute('data-blocking-desc')
      : mode.getAttribute('data-observer-desc');
    var unsaved = mode.querySelector('[data-mode-unsaved]');
    unsaved.hidden = !changed;
    unsaved.textContent = changed ? '⚠ ' + mode.getAttribute('data-unsaved') : '';
  }
  var robotsCardForMode = document.getElementById('ipl-card-robots');
  if (robotsCardForMode) robotsCardForMode.addEventListener('change', refreshMode);

  // Pastilles (liste blanche, pays, mots-clés, pays autorisés) liées à un champ caché
  function chipValues(hidden, sep){
    var raw = hidden.value || '';
    return (sep === 'comma' ? raw.split(',') : raw.split(/\r?\n/)).map(function(s){ return s.trim(); }).filter(Boolean);
  }
  function writeValues(hidden, sep, values){
    hidden.value = values.join(sep === 'comma' ? ',' : '\n');
    var card = hidden.closest('.iplc-card');
    if (card && card._markDirty) card._markDirty();
  }
  root.querySelectorAll('[data-chips]').forEach(function(wrap){
    var hidden = document.getElementById(wrap.getAttribute('data-chips'));
    var sep = wrap.getAttribute('data-sep');
    var upper = wrap.hasAttribute('data-upper');
    function bindChip(chip){
      chip.querySelector('button').addEventListener('click', function(){
        var v = chip.getAttribute('data-value');
        writeValues(hidden, sep, chipValues(hidden, sep).filter(function(x){ return x !== v; }));
        chip.remove();
      });
    }
    // Liste blanche : pastilles construites ici (pas de métadonnée côté serveur)
    if (!wrap.children.length) {
      chipValues(hidden, sep).forEach(function(v){ wrap.appendChild(makeChip(v)); });
    }
    function makeChip(v){
      var s = document.createElement('span');
      s.className = 'iplc-chip'; s.setAttribute('data-value', v);
      s.appendChild(document.createTextNode(v + ' '));
      var b = document.createElement('button'); b.type = 'button'; b.textContent = '✕'; b.setAttribute('aria-label', v);
      s.appendChild(b);
      return s;
    }
    wrap.querySelectorAll('.iplc-chip').forEach(bindChip);
    var input = root.querySelector('[data-chip-input="' + hidden.id + '"]');
    var add = root.querySelector('[data-chip-add="' + hidden.id + '"]');
    function addValue(){
      var v = input.value.trim(); if (upper) v = v.toUpperCase();
      if (!v) return;
      var vals = chipValues(hidden, sep);
      if (vals.indexOf(v) === -1) {
        vals.push(v); writeValues(hidden, sep, vals);
        var chip = makeChip(v); wrap.appendChild(chip); bindChip(chip);
      }
      input.value = '';
    }
    if (add) add.addEventListener('click', addValue);
    if (input) input.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); addValue(); } });
  });

  // Robots : filtre par famille, statut Autorisé/Bloqué, retrait
  var robotsCard = document.getElementById('ipl-card-robots');
  if (robotsCard) {
    robotsCard.querySelectorAll('[data-robot-families] button').forEach(function(btn){
      btn.addEventListener('click', function(){
        robotsCard.querySelectorAll('[data-robot-families] button').forEach(function(b){ b.classList.toggle('active', b === btn); });
        var fam = btn.getAttribute('data-fam');
        robotsCard.querySelectorAll('tbody tr[data-fam]').forEach(function(tr){
          if (fam === 'all' || tr.getAttribute('data-fam') === fam) tr.removeAttribute('data-row-hidden');
          else tr.setAttribute('data-row-hidden', '');
        });
      });
    });
    robotsCard.querySelectorAll('[data-status]').forEach(function(st){
      st.addEventListener('change', function(){
        var labels = st.querySelectorAll('label');
        labels[0].className = labels[0].querySelector('input').checked ? 'allow-on' : '';
        labels[1].className = labels[1].querySelector('input').checked ? 'block-on' : '';
      });
    });
    robotsCard.querySelectorAll('[data-remove]').forEach(function(cb){
      cb.addEventListener('change', function(){ cb.closest('tr').classList.toggle('removed', cb.checked); });
    });
  }

  // Blocage auto : valeur du curseur et IP récentes au-dessus du seuil, en direct
  var recent = {$RECENT_SCORES_JSON};
  var th = document.getElementById('iplc-threshold');
  function refreshThreshold(){
    if (!th) return;
    var t = +th.value;
    document.getElementById('iplc-threshold-val').textContent = t;
    document.getElementById('iplc-would').textContent = recent.filter(function(s){ return s >= t; }).length;
  }
  if (th) { th.addEventListener('input', refreshThreshold); refreshThreshold(); }

  // Filtre texte d'un tableau (IP bloquées automatiquement)
  root.querySelectorAll('[data-filter-table]').forEach(function(inp){
    var table = document.getElementById(inp.getAttribute('data-filter-table'));
    inp.addEventListener('input', function(){
      var q = inp.value.trim().toLowerCase();
      table.querySelectorAll('tbody tr').forEach(function(tr){
        if (!q || tr.textContent.toLowerCase().indexOf(q) !== -1) tr.removeAttribute('data-row-hidden');
        else tr.setAttribute('data-row-hidden', '');
      });
    });
  });
})();
</script>
