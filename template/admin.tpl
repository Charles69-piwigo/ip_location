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
  .ipl-listed-badge { color:#8a5a00; font-weight:bold; font-size:0.8em; background:#fbeccb; padding:1px 4px; border-radius:3px; }
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
  .ipl-country-stats,
  .ipl-visitor-detail,
  .ipl-blocklist-auto-details { margin:0 0 1.5em 0; text-align:left; }
  .ipl-country-stats summary,
  .ipl-visitor-detail summary,
  .ipl-blocklist-auto-details summary { cursor:pointer; font-size:1.17em; font-weight:bold; padding:10px 0 4px 10px; text-align:left; }
  .ipl-country-stats .ipl-table,
  .ipl-visitor-detail .ipl-table,
  .ipl-blocklist-auto-details .ipl-table { margin-top:0.5em; }
</style>
{* ── Styles des blocs Configuration et du Journal (v2.6.3 / v2.6.6) ── *}
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

  /* ── Sous-onglets Réglages / Journal (v2.6.6) ── */
  .iplc-subtabs{ display:inline-flex; gap:2px; background:#efe8db; border:1px solid #ddd2ba; border-radius:9px; padding:3px; margin:14px 0 0 10px; }
  .iplc-subtabs a{ font-size:12.8px; font-weight:600; color:#5c5140; text-decoration:none; border-radius:6px; padding:6px 18px; }
  .iplc-subtabs a:hover{ color:#221c11; }
  .iplc-subtabs a.active{ background:#fffdf9; color:#221c11; box-shadow:0 1px 3px rgba(0,0,0,.12); }

  /* ── Journal ── */
  .iplj-tiles{ display:grid; grid-template-columns:repeat(5, minmax(0,1fr)); gap:10px; }
  @media (max-width:900px){ .iplj-tiles{ grid-template-columns:repeat(2, minmax(0,1fr)); } }
  .iplj-tile{ display:flex; flex-direction:column; gap:2px; background:var(--c-surface); border:1px solid var(--c-border); border-radius:10px; padding:9px 13px; text-decoration:none; color:var(--c-text); }
  .iplj-tile:hover{ border-color:var(--c-border-strong); }
  .iplj-tile.active{ border-color:var(--c-accent); background:var(--c-accent-wash); }
  .iplj-tile .n{ font-family:"IBM Plex Mono", Consolas, monospace; font-size:19px; font-weight:500; }
  .iplj-tile .l{ font-size:12px; color:var(--c-text-2); display:flex; align-items:center; gap:6px; }
  .iplj-tile .sw{ width:8px; height:8px; border-radius:50%; background:var(--c-muted); }
  .iplj-tile .sw-normal, .iplj-tile .sw-robots{ background:var(--c-on); }
  .iplj-tile .sw-bot{ background:var(--c-text-2); } .iplj-tile .sw-blocked{ background:var(--c-danger); }
  .iplj-filters{ display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; margin:0; }
  .iplj-reset{ font-size:12.5px; color:var(--c-accent); margin-bottom:6px; }
  .iplj-country{ display:inline-flex; align-items:center; gap:7px; font-size:12.3px; padding:3px 10px; border-radius:20px; background:var(--c-surface-2); border:1px solid var(--c-border); color:var(--c-text); text-decoration:none; }
  .iplj-country:hover, .iplj-country.active{ border-color:var(--c-accent); }
  .iplj-country .meta{ font-family:"IBM Plex Mono", Consolas, monospace; font-size:10.8px; color:var(--c-muted); }
  .iplj-head{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:11px 16px; border-bottom:1px solid var(--c-border); }
  .iplj-count{ font-size:12.3px; color:var(--c-muted); }
  .iplj-pager{ margin-left:auto; display:flex; align-items:center; gap:4px; flex-wrap:wrap; }
  .iplj-pager a{ font-family:"IBM Plex Mono", Consolas, monospace; font-size:12px; min-width:28px; text-align:center; padding:3px 7px; border-radius:6px; border:1px solid var(--c-border); background:var(--c-surface); color:var(--c-text); text-decoration:none; }
  .iplj-pager a.active{ background:var(--c-accent); color:var(--c-accent-ink); border-color:var(--c-accent); }
  .iplj-pager .gap{ color:var(--c-muted); padding:0 3px; }
  .iplj-scroll{ overflow-x:auto; }
  table.iplj-table{ min-width:980px; }
  table.iplj-table td{ white-space:nowrap; }
  table.iplj-table td.when{ white-space:normal; min-width:150px; }
  table.iplj-table td.when .d{ display:block; font-family:"IBM Plex Mono", Consolas, monospace; font-size:12px; }
  table.iplj-table td.url{ max-width:300px; overflow:hidden; text-overflow:ellipsis; font-size:12px; }
  table.iplj-table td.url a{ color:var(--c-text-2); text-decoration:none; border-bottom:1px dotted var(--c-border-strong); }
  table.iplj-table td.url a:hover{ color:var(--c-accent); }
  table.iplj-table td.ua{ max-width:260px; overflow:hidden; text-overflow:ellipsis; font-size:11.8px; color:var(--c-text-2); }
  table.iplj-table tr.cat-bot td{ background:#f7f3ea; }
  table.iplj-table tr.cat-blocked td{ background:#fbece8; }
  .iplj-badge{ display:inline-block; font-size:10.3px; font-weight:600; letter-spacing:.02em; padding:1px 6px; border-radius:4px; margin:3px 4px 0 0; }
  .iplj-badge.bot{ background:var(--c-surface-3); color:var(--c-text-2); }
  .iplj-badge.robot{ background:var(--c-on-wash); color:var(--c-on); }
  .iplj-badge.refused{ background:var(--c-danger-wash); color:var(--c-danger); }
  .iplj-badge.listed{ background:var(--c-accent-wash); color:var(--c-accent); }
  .iplj-badge.exempt{ background:#eee; color:#555; cursor:help; }
  details.iplj-menu{ position:relative; display:inline-block; }
  details.iplj-menu > summary{ list-style:none; display:inline-block; }
  details.iplj-menu > summary::-webkit-details-marker{ display:none; }
  details.iplj-menu .pop{ position:absolute; right:0; top:calc(100% + 4px); z-index:30; background:var(--c-surface); border:1px solid var(--c-border-strong); border-radius:8px; padding:4px; box-shadow:0 8px 24px rgba(0,0,0,.18); display:flex; flex-direction:column; min-width:220px; text-align:left; }
  details.iplj-menu .pop a, details.iplj-menu .pop button{ display:block; width:100%; font-family:inherit; font-size:12.5px; text-align:left; background:none; border:none; padding:6px 10px; border-radius:5px; cursor:pointer; color:var(--c-text); text-decoration:none; white-space:nowrap; }
  details.iplj-menu .pop a:hover, details.iplj-menu .pop button:hover{ background:var(--c-surface-2); color:var(--c-accent); }
  details.iplj-menu .pop form{ margin:0; display:block; }
  details.iplj-menu .pop .note{ font-size:11.8px; color:var(--c-muted); padding:6px 10px; white-space:normal; }
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

{if $TAB eq 'config'}
<div style="text-align:left;"><div class="iplc-subtabs" role="tablist">
  <a href="{$BASE_URL|escape}" class="{if $SUB eq 'settings'}active{/if}" role="tab">{'Réglages'|@translate}</a>
  <a href="{$BASE_URL|escape}&amp;sub=journal" class="{if $SUB eq 'journal'}active{/if}" role="tab">{'Journal des accès'|@translate}</a>
</div></div>
{/if}

{if $TAB neq 'config' or $SUB eq 'settings'}
{$TAB_CONTENT}
{/if}

{if $TAB eq 'config' and $SUB eq 'journal'}
{* ═════════════ Sous-onglet Journal des accès (v2.6.6) ═════════════ *}
<div class="iplc" id="ipl-journal">

  {* Compteurs cliquables = filtre par catégorie (mêmes filtres pays/dates/IP) *}
  <div class="iplj-tiles">
    {foreach from=['all','normal','bot','robots','blocked'] item=cat}
      <a class="iplj-tile{if $FILTER eq $cat} active{/if}" href="{$BASE_URL|escape}{$JOURNAL_QS|escape}{if $cat neq 'all'}&amp;filter={$cat}{/if}">
        <span class="n">{$JOURNAL_COUNTS[$cat]}</span>
        <span class="l"><span class="sw sw-{$cat}"></span>{if $cat eq 'all'}{'Tous les accès'|@translate}{elseif $cat eq 'normal'}{'Normal'|@translate}{elseif $cat eq 'bot'}{'Bots non bloqués'|@translate}{elseif $cat eq 'robots'}{'dont robots autorisés'|@translate}{else}{'Bloqués (refusés)'|@translate}{/if}</span>
      </a>
    {/foreach}
  </div>

  {* Filtres *}
  <section class="iplc-card">
    <div class="iplc-body" style="padding-top:14px;">
      <form method="get" action="admin.php" class="iplj-filters">
        <input type="hidden" name="page" value="plugin-ip_location">
        <input type="hidden" name="sub" value="journal">
        {if $FILTER neq 'all'}<input type="hidden" name="filter" value="{$FILTER|escape}">{/if}
        <label class="field">{'Pays'|@translate}
          <select name="country" onchange="this.form.submit()">
            <option value="">{'Tous les pays'|@translate}</option>
            {foreach from=$COUNTRIES item=c}
              <option value="{$c.country_code|escape}"{if $COUNTRY_FILTER eq $c.country_code} selected{/if}>{$c.country|escape} ({$c.visits})</option>
            {/foreach}
          </select>
        </label>
        <label class="field">{'Du'|@translate}<input type="date" name="date_from" value="{$DATE_FROM|escape}"></label>
        <label class="field">{'Au'|@translate}<input type="date" name="date_to" value="{$DATE_TO|escape}"></label>
        <label class="field">{'IP commençant par'|@translate}<input type="text" name="ip_filter" value="{$IP_FILTER|escape}" placeholder="ex. 34.17." style="width:150px;"></label>
        <label class="field">{'Motif de blocage'|@translate}
          <select name="reason" onchange="this.form.submit()">
            <option value="">{'Tous les motifs'|@translate}</option>
            <option value="country"{if $REASON_FILTER eq 'country'} selected{/if}>{'Refusé · pays'|@translate} ({$REASON_COUNTS.country})</option>
            <option value="keyword"{if $REASON_FILTER eq 'keyword'} selected{/if}>{'Refusé · mot-clé'|@translate} ({$REASON_COUNTS.keyword})</option>
            <option value="ip"{if $REASON_FILTER eq 'ip'} selected{/if}>{'Refusé · liste IP'|@translate} ({$REASON_COUNTS.ip})</option>
            <option value="auto"{if $REASON_FILTER eq 'auto'} selected{/if}>{'Refusé · blocage auto'|@translate} ({$REASON_COUNTS.auto})</option>
            <option value="robot"{if $REASON_FILTER eq 'robot'} selected{/if}>{'Refusé · robot'|@translate} ({$REASON_COUNTS.robot})</option>
            <option value="download"{if $REASON_FILTER eq 'download'} selected{/if}>{'Refusé · téléchargement'|@translate} ({$REASON_COUNTS.download})</option>
            <option value="listed"{if $REASON_FILTER eq 'listed'} selected{/if}>{'Bloquée depuis (servi avant le blocage)'|@translate} ({$REASON_COUNTS.listed})</option>
            <option value="unknown"{if $REASON_FILTER eq 'unknown'} selected{/if}>{'Motif inconnu (avant la 2.6.1)'|@translate} ({$REASON_COUNTS.unknown})</option>
          </select>
        </label>
        <label class="field">{'Score'|@translate}
          <select name="score" onchange="this.form.submit()">
            <option value="">{'Tous les scores'|@translate}</option>
            <option value="zero"{if $SCORE_FILTER eq 'zero'} selected{/if}>{'Score 0'|@translate} ({$SCORE_COUNTS.zero})</option>
            <option value="pos"{if $SCORE_FILTER eq 'pos'} selected{/if}>{'Score > 0'|@translate} ({$SCORE_COUNTS.pos})</option>
            <option value="30"{if $SCORE_FILTER eq '30'} selected{/if}>{'Score ≥ 30'|@translate} ({$SCORE_COUNTS.30})</option>
            <option value="50"{if $SCORE_FILTER eq '50'} selected{/if}>{'Score ≥ 50'|@translate} ({$SCORE_COUNTS.50})</option>
            <option value="thr"{if $SCORE_FILTER eq 'thr'} selected{/if}>{'Score ≥ seuil du blocage auto'|@translate} {$SCORE_THRESHOLD_NOW} ({$SCORE_COUNTS.thr})</option>
          </select>
        </label>
        <button type="submit" class="iplc-btn ghost sm">{'Filtrer'|@translate}</button>
        {if $COUNTRY_FILTER neq '' or $DATE_FROM neq '' or $DATE_TO neq '' or $IP_FILTER neq '' or $REASON_FILTER neq '' or $SCORE_FILTER neq ''}
          <a class="iplj-reset" href="{$BASE_URL|escape}&amp;sub=journal{if $FILTER neq 'all'}&amp;filter={$FILTER}{/if}">{'Réinitialiser'|@translate}</a>
        {/if}
      </form>
      <details class="iplc-fold">
        <summary>{'Répartition par pays'|@translate} ({$STATS|@count} {'pays'|@translate})</summary>
        <div class="fold-body"><div class="iplc-chips">
          {foreach from=$STATS item=s}
            {if $s.country_code neq ''}
            <a class="iplj-country{if $COUNTRY_FILTER eq $s.country_code} active{/if}" href="{$BASE_URL|escape}&amp;sub=journal{if $FILTER neq 'all'}&amp;filter={$FILTER}{/if}{if $COUNTRY_FILTER neq $s.country_code}&amp;country={$s.country_code|escape}{/if}" title="{$s.bots} {'bots'|@translate}">{$s.country|escape} <span class="meta">{$s.visits}</span></a>
            {/if}
          {/foreach}
        </div></div>
      </details>
    </div>
  </section>

  {* Tableau *}
  <section class="iplc-card">
    <div class="iplj-head">
      <span class="iplj-count">{$JOURNAL_COUNTS[$FILTER]} {'accès'|@translate}{if $TOTAL_PAGES > 1} · {'page'|@translate} {$CURRENT_PAGE} / {$TOTAL_PAGES}{/if}</span>
      {if $PAGER|@count > 0}
      <div class="iplj-pager">
        {if $CURRENT_PAGE > 1}<a href="{$BASE_URL|escape}{$JOURNAL_QS|escape}{if $FILTER neq 'all'}&amp;filter={$FILTER}{/if}&amp;pnum={$CURRENT_PAGE-1}">‹</a>{/if}
        {foreach from=$PAGER item=p}
          {if isset($p.gap)}<span class="gap">…</span>
          {else}<a href="{$BASE_URL|escape}{$JOURNAL_QS|escape}{if $FILTER neq 'all'}&amp;filter={$FILTER}{/if}&amp;pnum={$p.num}"{if $p.current} class="active"{/if}>{$p.num}</a>{/if}
        {/foreach}
        {if $CURRENT_PAGE < $TOTAL_PAGES}<a href="{$BASE_URL|escape}{$JOURNAL_QS|escape}{if $FILTER neq 'all'}&amp;filter={$FILTER}{/if}&amp;pnum={$CURRENT_PAGE+1}">›</a>{/if}
      </div>
      {/if}
    </div>
    <div class="iplj-scroll">
      <table class="iplc-t iplj-table">
        <thead><tr><th>{'Date'|@translate}</th><th>{'IP'|@translate}</th><th>{'Pays'|@translate}</th><th>{'Ville'|@translate}</th><th class="num">{'Score'|@translate}</th><th>{'URL'|@translate}</th><th>{'Navigateur / robot'|@translate}</th><th></th></tr></thead>
        <tbody>
        {foreach from=$LOGS item=log}
          <tr class="{if $log.is_blocked or $log.listed_since or $log.robot_blocked}cat-blocked{elseif $log.is_bot}cat-bot{/if}">
            <td class="when">
              <span class="d">{$log.visit_date|escape}</span>
              {if $log.robot_name}<span class="iplj-badge robot" title="{'Robot autorisé'|@translate}">{$log.robot_name|escape} ✓</span>
              {elseif $log.is_bot}<span class="iplj-badge bot">BOT</span>{/if}
              {if $log.is_spoof}<span class="iplj-badge refused" title="{'User-Agent d\'un moteur connu, mais l\'IP ne lui appartient pas (vérification DNS)'|@translate}">{'faux robot'|@translate}</span>{/if}
              {if $log.is_blocked}<span class="iplj-badge refused">{'Refusé'|@translate}{if $log.block_reason_label} · {$log.block_reason_label|escape}{/if}</span>
              {elseif $log.robot_blocked}<span class="iplj-badge refused" title="{'Robot marqué « Bloqué » dans le bloc Robots d\'indexation'|@translate}">{'Robot bloqué'|@translate} · {$log.robot_blocked|escape}</span>{elseif $log.listed_since}<span class="iplj-badge listed" title="{'Accès servi normalement : il date d\'avant le blocage de cette IP'|@translate}">{'Bloquée depuis le'|@translate} {$log.listed_since|escape}</span>{/if}
              {if $log.is_exempt && ($log.is_bot || $log.bot_score > 0)}<span class="iplj-badge exempt" title="{'Retirée manuellement du blocage : le score affiché peut rester au-dessus du seuil sans que l\'IP soit rebloquée, tant qu\'aucune nouvelle activité suspecte n\'apparaît après le retrait.'|@translate}">{'Exempté'|@translate}</span>{/if}
            </td>
            <td class="ip">{$log.ip|escape}</td>
            <td>{$log.country|escape}</td>
            <td class="muted">{$log.city|escape}</td>
            <td class="num mono">{$log.bot_score}</td>
            <td class="url"><a href="{$log.url|escape}" target="_blank" rel="noopener" title="{$log.url|escape}">{$log.url|escape}</a></td>
            <td class="ua" title="{$log.user_agent|escape}">{$log.user_agent|escape}</td>
            <td class="num">
              <details class="iplj-menu">
                <summary class="iplc-btn ghost sm">{'Actions'|@translate} ▾</summary>
                <div class="pop">
                  <a href="{$BASE_URL|escape}&amp;sub=journal&amp;ip_filter={$log.ip|escape:'url'}">{'Filtrer sur cette IP'|@translate}</a>
                  {if $log.in_blocklist}
                    <form method="post" action=""><input type="hidden" name="action" value="unblock_ip"><input type="hidden" name="ip" value="{$log.ip|escape}"><input type="hidden" name="return_qs" value="{$RETURN_QS|escape}"><button type="submit">{'Débloquer'|@translate} {$log.ip|escape}</button></form>
                  {elseif $log.in_range}
                    <span class="note">{'Plage /16 bloquée manuellement : à retirer depuis la liste des IP.'|@translate}</span>
                  {else}
                    <form method="post" action=""><input type="hidden" name="action" value="block_ip"><input type="hidden" name="ip" value="{$log.ip|escape}"><input type="hidden" name="country" value="{$log.country|escape}"><input type="hidden" name="city" value="{$log.city|escape}"><input type="hidden" name="return_qs" value="{$RETURN_QS|escape}"><button type="submit">{'Bloquer l\'IP'|@translate} {$log.ip|escape}</button></form>
                    {if $log.range16}
                    <form method="post" action=""><input type="hidden" name="action" value="block_ip"><input type="hidden" name="ip" value="{$log.range16|escape}"><input type="hidden" name="country" value="{$log.country|escape}"><input type="hidden" name="city" value=""><input type="hidden" name="return_qs" value="{$RETURN_QS|escape}"><button type="submit">{'Bloquer la plage'|@translate} {$log.range16|escape}</button></form>
                    {/if}
                  {/if}
                </div>
              </details>
            </td>
          </tr>
        {foreachelse}
          <tr><td colspan="8" class="muted" style="text-align:center;padding:14px;">{'Aucun accès ne correspond à ces filtres.'|@translate}</td></tr>
        {/foreach}
        </tbody>
      </table>
    </div>
  </section>
</div>
<script>
// Un seul menu "Actions" ouvert à la fois ; clic ailleurs = fermeture
(function(){
  var menus = document.querySelectorAll('.iplj-menu');
  menus.forEach(function(m){
    m.addEventListener('toggle', function(){
      if (m.open) menus.forEach(function(o){ if (o !== m) o.open = false; });
    });
  });
  document.addEventListener('click', function(e){
    menus.forEach(function(m){ if (m.open && !m.contains(e.target)) m.open = false; });
  });
})();
</script>
{/if}

