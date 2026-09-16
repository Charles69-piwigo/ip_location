<style>
{literal}
  .ipl-stats-subtabs{ margin:0 0 1em 20px; text-align:left; }
  .ipl-stats-subtabs button{
    margin-right:8px; padding:3px 14px; border:1px solid #ccc; border-radius:3px;
    background:#f5f5f5; color:#333; font-size:0.9em; cursor:pointer;
  }
  .ipl-stats-subtabs button.active{ background:#555; color:#fff; border-color:#555; font-weight:bold; }

  .ipl-stats-panel{ margin:0 0 1.5em 20px; text-align:left; }
  .ipl-stats-panel h3{ margin-bottom:0.5em; }

  .ipl-stats-field-row{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
  .ipl-stats-custom-range{ display:flex; align-items:center; gap:6px; }
  .ipl-stats-custom-range[hidden]{ display:none; }
  .ipl-stats-granularity{
    font-size:0.82em; background:#eee; color:#555; border-radius:12px; padding:3px 10px; margin-left:auto;
  }

  .ipl-stats-preset-buttons{ display:flex; gap:8px; flex-wrap:wrap; }
  .ipl-stats-preset-btn{
    display:flex; flex-direction:column; align-items:flex-start; gap:2px; min-width:130px;
    background:#f5f5f5; border:1px solid #ccc; border-radius:4px; padding:6px 12px; cursor:pointer; font-family:inherit;
  }
  .ipl-stats-preset-btn:hover{ background:#eee; }
  .ipl-stats-preset-btn.active{ border-color:#555; background:#e6e6e6; }
  .ipl-stats-preset-btn.empty{ border-style:dashed; color:#888; font-style:italic; }
  .ipl-stats-preset-btn .p-name{ font-weight:bold; font-size:0.92em; }
  .ipl-stats-preset-btn.empty .p-name{ font-weight:normal; }
  .ipl-stats-preset-btn .p-meta{ font-size:0.78em; color:#777; }

  .ipl-stats-edit-bar{
    display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-top:12px; padding:8px 10px;
    background:#fff8e6; border:1px solid #e0c060; border-radius:4px;
  }
  .ipl-stats-edit-bar[hidden]{ display:none; }
  .ipl-stats-edit-bar .edit-label{ font-size:0.85em; font-weight:bold; color:#8a6d1a; white-space:nowrap; }
  .ipl-stats-edit-bar input[type="text"]{ flex:1; min-width:140px; padding:3px 6px; }

  .ipl-stats-series-list{ display:flex; flex-direction:column; gap:6px; }
  .ipl-stats-series-row{
    display:flex; align-items:flex-start; gap:8px; flex-wrap:wrap;
    background:#f9f9f9; border:1px solid #ddd; border-radius:4px; padding:8px 10px;
  }
  .ipl-stats-swatch{ width:24px; height:24px; padding:0; border:1px solid #ccc; border-radius:3px; cursor:pointer; }
  .ipl-stats-bg-swatches{ display:flex; gap:6px; }
  .ipl-stats-bg-swatch-btn{ width:24px; height:24px; padding:0; border:1px solid #ccc; border-radius:3px; cursor:pointer; }
  .ipl-stats-series-fields{ display:flex; gap:8px; flex-wrap:wrap; align-items:center; flex:1; min-width:0; }
  .ipl-stats-series-fields input[type="text"]{ width:140px; padding:3px 6px; }
  .ipl-stats-series-fields select{ padding:3px 4px; }

  .ipl-stats-check{ position:relative; }
  .ipl-stats-check summary{
    list-style:none; cursor:pointer; font-size:0.88em; background:#fff; border:1px solid #ccc;
    border-radius:3px; padding:4px 9px; color:#444; white-space:nowrap;
  }
  .ipl-stats-check summary::-webkit-details-marker{ display:none; }
  .ipl-stats-check summary .count{ color:#c00; font-weight:bold; }
  .ipl-stats-check[open] summary{ border-color:#555; }
  .ipl-stats-check-pop{
    position:absolute; top:calc(100% + 3px); left:0; z-index:20; width:220px; max-height:250px; overflow:auto;
    background:#fff; border:1px solid #999; border-radius:4px; padding:6px; box-shadow:0 4px 14px rgba(0,0,0,.2);
  }
  .ipl-stats-check-pop input[type="text"]{ width:100%; margin-bottom:5px; font-size:0.82em; padding:3px 6px; box-sizing:border-box; }
  .ipl-stats-check-row{ display:flex; align-items:center; gap:6px; padding:2px 3px; font-size:0.85em; }
  .ipl-stats-check-row[hidden]{ display:none; }
  .ipl-stats-check-row:hover{ background:#f0f0f0; }
  .ipl-stats-check-row .hits{ margin-left:auto; color:#888; font-size:0.85em; }

  .ipl-stats-axis-toggle{ display:flex; border:1px solid #ccc; border-radius:3px; overflow:hidden; }
  .ipl-stats-axis-toggle button{
    font-size:0.82em; padding:4px 8px; background:#fff; color:#555; border:none; cursor:pointer;
  }
  .ipl-stats-axis-toggle button + button{ border-left:1px solid #ccc; }
  .ipl-stats-axis-toggle button.active{ background:#555; color:#fff; font-weight:bold; }

  .ipl-stats-row-remove{
    margin-left:auto; background:none; border:none; color:#999; cursor:pointer; font-size:15px; padding:3px 5px;
  }
  .ipl-stats-row-remove:hover{ color:#c00; }

  .ipl-stats-generate-row{ display:flex; align-items:center; gap:12px; }
  .ipl-stats-generate-row .hint{ color:#888; font-size:0.85em; }

  #iplStatsPanelChart{ display:flex; flex-direction:column; min-height:60vh; }
  #iplStatsPanelChart[hidden]{ display:none; }
  .ipl-stats-chart-head{ display:flex; align-items:flex-start; justify-content:space-between; gap:14px; flex-wrap:wrap; margin:0 0 0.6em 20px; }
  .ipl-stats-chart-head-left{ display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
  .ipl-stats-preset-tag{
    font-size:0.85em; font-weight:bold; color:#444; background:#eee; border-radius:12px; padding:4px 12px; white-space:nowrap;
  }
  .ipl-stats-preset-tag[hidden]{ display:none; }
  .ipl-stats-stat-tile{ display:flex; flex-direction:column; }
  .ipl-stats-period-label{ font-size:0.8em; font-weight:bold; color:#555; text-transform:uppercase; letter-spacing:.02em; }
  .ipl-stats-stat-value{ font-size:1.5em; font-weight:bold; }
  .ipl-stats-stat-label{ font-size:0.78em; color:#888; }
  .ipl-stats-head-actions{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
  .ipl-stats-legend{ display:flex; gap:6px; flex-wrap:wrap; }
  .ipl-stats-legend-chip{
    display:flex; align-items:center; gap:5px; font-size:0.82em; padding:3px 9px; border-radius:14px;
    background:#f0f0f0; border:1px solid #ddd; cursor:pointer; color:#333;
  }
  .ipl-stats-legend-chip.off{ opacity:.42; }
  .ipl-stats-legend-chip .dot{ width:8px; height:8px; border-radius:50%; flex:none; }
  .ipl-stats-legend-chip .axis-badge{ font-size:0.75em; border:1px solid #bbb; border-radius:3px; padding:0 3px; color:#777; }

  .ipl-stats-export{ position:relative; }
  .ipl-stats-export-dd{
    position:absolute; top:calc(100% + 3px); right:0; z-index:30; min-width:120px;
    background:#fff; border:1px solid #999; border-radius:4px; padding:3px; box-shadow:0 4px 14px rgba(0,0,0,.2);
  }
  .ipl-stats-export-dd[hidden]{ display:none; }
  .ipl-stats-export-dd button{
    display:block; width:100%; text-align:left; font-size:0.85em; padding:5px 9px; background:none; border:none;
    border-radius:3px; cursor:pointer; color:#333;
  }
  .ipl-stats-export-dd button:hover{ background:#f0f0f0; }

  .ipl-stats-chart-wrap{ position:relative; width:100%; flex:1 1 auto; min-height:320px; margin-left:20px; max-width:calc(100% - 30px); }
  .ipl-stats-footnote{ margin:10px 0 0 20px; font-size:0.82em; color:#888; }
  .ipl-stats-chart-empty{ margin:0 0 0 20px; color:#888; }
  .ipl-stats-chart-error{ margin:0 0 0 20px; color:#c00; }
{/literal}
</style>

<div class="titrePage">
  <h2>{'IP Location'|@translate} &mdash; {'Statistiques'|@translate}</h2>
</div>

<div class="ipl-stats-subtabs">
  <button type="button" id="iplSubtabSettings" class="active">{'Réglages'|@translate}</button>
  <button type="button" id="iplSubtabChart">{'Graphique'|@translate}</button>
</div>

<div id="iplPanelSettings">

  <div class="ipl-stats-panel">
    <div class="ipl-stats-field-row">
      <label for="iplPeriodSelect">{'Période'|@translate}</label>
      <select id="iplPeriodSelect">
        <option value="week" {if $LAST_STATS_PERIOD == 'week'}selected{/if}>{'Semaine (7 jours)'|@translate}</option>
        <option value="fortnight" {if $LAST_STATS_PERIOD == 'fortnight'}selected{/if}>{'Quinzaine (15 jours)'|@translate}</option>
        <option value="month" {if $LAST_STATS_PERIOD == 'month'}selected{/if}>{'Mois (30 jours)'|@translate}</option>
        <option value="quarter" {if $LAST_STATS_PERIOD == 'quarter'}selected{/if}>{'Trimestre (90 jours)'|@translate}</option>
        <option value="all" {if $LAST_STATS_PERIOD == 'all'}selected{/if}>{'Tous les logs'|@translate}</option>
        <option value="custom" {if $LAST_STATS_PERIOD == 'custom'}selected{/if}>{'Période à définir'|@translate}</option>
      </select>
      <div class="ipl-stats-custom-range" id="iplCustomRange" {if $LAST_STATS_PERIOD != 'custom'}hidden{/if}>
        <input type="date" id="iplDateFrom">
        <span>&rarr;</span>
        <input type="date" id="iplDateTo">
      </div>
      <span class="ipl-stats-granularity" id="iplGranularityBadge"></span>
    </div>
  </div>

  <div class="ipl-stats-panel">
    <div class="ipl-stats-field-row">
      <label for="iplChartBgColor">{'Couleur de fond du graphique'|@translate}</label>
      <input type="color" id="iplChartBgColor" class="ipl-stats-swatch"
        value="{if $CHART_BG_COLOR == 'transparent'}#ffffff{else}{$CHART_BG_COLOR|escape}{/if}"
        data-transparent="{if $CHART_BG_COLOR == 'transparent'}1{else}0{/if}">
      <div class="ipl-stats-bg-swatches" id="iplChartBgSwatches"></div>
    </div>
  </div>

  <div class="ipl-stats-panel">
    <h3>{'Préréglages'|@translate}</h3>
    <div class="ipl-stats-preset-buttons" id="iplPresetsFixed"></div>
    <h3 style="margin-top:1em;">{'Mes préréglages'|@translate}</h3>
    <div class="ipl-stats-preset-buttons" id="iplPresetsUser"></div>
  </div>

  <div class="ipl-stats-panel">
    <h3>{'Séries'|@translate} <button type="button" id="iplAddSeriesBtn" class="buttonLike" style="font-size:0.8em;">{'+ Ajouter une série'|@translate}</button></h3>
    <div class="ipl-stats-series-list" id="iplSeriesList"></div>
  </div>

  <div class="ipl-stats-panel ipl-stats-generate-row">
    <button type="button" id="iplGenerateBtn" class="buttonLike">{'Générer le graphique'|@translate}</button>
    <span class="hint" id="iplSeriesCountHint"></span>
  </div>

  <div class="ipl-stats-panel ipl-stats-edit-bar" id="iplEditBar" hidden>
    <span class="edit-label" id="iplEditBarLabel"></span>
    <input type="text" id="iplEditBarName" placeholder="{'Nom du préréglage'|@translate}">
    <button type="button" id="iplEditBarSave" class="buttonLike" style="font-size:0.85em;">{'Enregistrer les séries ci-dessus dans ce préréglage'|@translate}</button>
    <button type="button" id="iplEditBarDelete" class="buttonLike" style="font-size:0.85em;" hidden>{'Supprimer ce préréglage'|@translate}</button>
    <a href="#" id="iplEditBarCancel">{'Annuler'|@translate}</a>
  </div>

</div>

<div id="iplStatsPanelChart" hidden>
  <div class="ipl-stats-chart-head">
    <div class="ipl-stats-chart-head-left">
      <span class="ipl-stats-preset-tag" id="iplActivePresetName" hidden></span>
      <div class="ipl-stats-stat-tile">
        <span class="ipl-stats-period-label" id="iplPeriodLabel"></span>
        <span class="ipl-stats-stat-value" id="iplStatTotal">&mdash;</span>
        <span class="ipl-stats-stat-label">{'accès cumulés sur la période, toutes séries actives'|@translate}</span>
      </div>
    </div>
    <div class="ipl-stats-head-actions">
      <div class="ipl-stats-legend" id="iplLegend"></div>
      <div class="ipl-stats-export">
        <button type="button" id="iplExportBtn" class="buttonLike" style="font-size:0.85em;">{'Exporter'|@translate} &#9662;</button>
        <div class="ipl-stats-export-dd" id="iplExportDropdown" hidden>
          <button type="button" data-format="csv">CSV <span style="color:#999;font-size:0.85em;">.csv</span></button>
          <button type="button" data-format="png">{'Image'|@translate} <span style="color:#999;font-size:0.85em;">.png</span></button>
          <button type="button" data-format="json">{'Données'|@translate} <span style="color:#999;font-size:0.85em;">.json</span></button>
        </div>
      </div>
    </div>
  </div>
  <p class="ipl-stats-chart-empty" id="iplChartEmpty">{'Configurez vos séries puis cliquez sur "Générer le graphique".'|@translate}</p>
  <p class="ipl-stats-chart-error" id="iplChartError" hidden></p>
  <div class="ipl-stats-chart-wrap" id="iplChartWrap" hidden><canvas id="iplChart"></canvas></div>
  <p class="ipl-stats-footnote">{'Axe gauche et axe droit indépendants : utile pour comparer une courbe de fort volume (ex. « Tout ») à une courbe de faible amplitude (ex. une IP isolée) sur le même graphique. Granularité automatique : par jour jusqu\'à 31 jours de période, par semaine jusqu\'à 1 an, par mois au-delà (y compris pour une période personnalisée ou « Tous les logs »).'|@translate}</p>
</div>

<form method="post" action="" id="iplPresetForm" style="display:none;">
  <input type="hidden" name="action" id="iplPresetFormAction" value="save_stats_preset">
  <input type="hidden" name="slot" id="iplPresetFormSlot" value="">
  <input type="hidden" name="name" id="iplPresetFormName" value="">
  <input type="hidden" name="series_json" id="iplPresetFormSeriesJson" value="">
</form>

<script>
var IPL_STATS_I18N          = {$STATS_I18N_JSON};
var IPL_STATS_COUNTRIES     = {$COUNTRIES_JSON};
var IPL_STATS_KEYWORDS      = {$BLOCKED_KEYWORDS_JSON};
var IPL_STATS_IPS           = {$KNOWN_IPS_JSON};
var IPL_STATS_USER_PRESETS  = {$STATS_PRESETS_JSON};
var IPL_STATS_AJAX_URL      = {$AJAX_STATS_URL_JSON};
var IPL_LAST_STATS_DATE_FROM = {$LAST_STATS_DATE_FROM_JSON};
var IPL_LAST_STATS_DATE_TO   = {$LAST_STATS_DATE_TO_JSON};
</script>
<script src="{$CHART_JS_URL|escape}"></script>
<script>
{literal}
(function(){

  var I = IPL_STATS_I18N;
  var SERIES_PALETTE = ['#2a78d6','#eb6834','#1baf7a','#eda100','#e87ba4','#008300','#4a3aa7','#e34948'];
  var BG_PALETTE = [
    { value: '#ffffff',     label: I.bg_white },
    { value: '#f2f2f2',     label: I.bg_light_gray },
    { value: '#eaf2fb',     label: I.bg_light_blue },
    { value: 'transparent', label: I.bg_none }
  ];

  Chart.register({
    id: 'iplChartBg',
    beforeDraw: function(chart, args, opts){
      var color = opts.color;
      if (!color || color === 'transparent') return;
      var ctx = chart.ctx;
      ctx.save();
      ctx.globalCompositeOperation = 'destination-over';
      ctx.fillStyle = color;
      ctx.fillRect(0, 0, chart.width, chart.height);
      ctx.restore();
    }
  });

  var TYPE_LABELS = { all: I.type_all, normal: I.type_normal, bot: I.type_bot, blocked: I.type_blocked };

  var FIXED_PRESETS = [
    { name: I.preset_vue_globale, series: [
        { label: I.series_tout, type: 'all', countries: [], keywords: [], ips: [], axis: 'y' }
      ] },
    { name: I.preset_bots_vs_humains, series: [
        { label: I.series_humains, type: 'normal', countries: [], keywords: [], ips: [], axis: 'y' },
        { label: I.series_bots, type: 'bot', countries: [], keywords: [], ips: [], axis: 'y1' }
      ] },
    { name: I.preset_normal_vs_bloques, series: [
        { label: I.series_normal, type: 'normal', countries: [], keywords: [], ips: [], axis: 'y' },
        { label: I.series_bloques, type: 'blocked', countries: [], keywords: [], ips: [], axis: 'y1' }
      ] }
  ];

  var USER_PRESETS = IPL_STATS_USER_PRESETS || [null, null, null, null];
  var selectedSlot = null;
  var rowSeq = 0;
  var chart = null;

  // ── Sous-onglets ─────────────────────────────────────────────────────
  function showSubTab(which){
    var isChart = which === 'chart';
    document.getElementById('iplPanelSettings').hidden = isChart;
    document.getElementById('iplStatsPanelChart').hidden = !isChart;
    document.getElementById('iplSubtabSettings').classList.toggle('active', !isChart);
    document.getElementById('iplSubtabChart').classList.toggle('active', isChart);
    if (isChart && chart) requestAnimationFrame(function(){ chart.resize(); });
  }
  document.getElementById('iplSubtabSettings').addEventListener('click', function(){ showSubTab('settings'); });
  document.getElementById('iplSubtabChart').addEventListener('click', function(){ showSubTab('chart'); });

  // ── Période ──────────────────────────────────────────────────────────
  document.getElementById('iplPeriodSelect').addEventListener('change', function(){
    document.getElementById('iplCustomRange').hidden = this.value !== 'custom';
  });

  function periodPayload(){
    var period = document.getElementById('iplPeriodSelect').value;
    var payload = { period: period };
    if (period === 'custom'){
      payload.date_from = document.getElementById('iplDateFrom').value;
      payload.date_to   = document.getElementById('iplDateTo').value;
    }
    return payload;
  }

  // ── Fond du graphique ────────────────────────────────────────────────
  var bgColorInput = document.getElementById('iplChartBgColor');
  var bgSwatchesEl = document.getElementById('iplChartBgSwatches');

  function currentBgColor(){ return bgColorInput.dataset.transparent === '1' ? 'transparent' : bgColorInput.value; }

  function applyBgColor(){
    if (chart){
      chart.options.plugins.iplChartBg = chart.options.plugins.iplChartBg || {};
      chart.options.plugins.iplChartBg.color = currentBgColor();
      chart.update();
    }
  }

  BG_PALETTE.forEach(function(swatch){
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'ipl-stats-bg-swatch-btn';
    btn.title = swatch.label;
    btn.style.background = swatch.value === 'transparent'
      ? 'repeating-conic-gradient(#ccc 0% 25%, #fff 0% 50%) 50% / 10px 10px'
      : swatch.value;
    btn.addEventListener('click', function(){
      bgColorInput.dataset.transparent = swatch.value === 'transparent' ? '1' : '0';
      if (swatch.value !== 'transparent') bgColorInput.value = swatch.value;
      applyBgColor();
    });
    bgSwatchesEl.appendChild(btn);
  });

  bgColorInput.addEventListener('input', function(){
    bgColorInput.dataset.transparent = '0';
    applyBgColor();
  });

  function formatFR(isoDate){
    var p = isoDate.split('-');
    return p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : isoDate;
  }

  function periodLabelText(){
    var sel = document.getElementById('iplPeriodSelect');
    if (sel.value === 'custom'){
      var from = document.getElementById('iplDateFrom').value;
      var to   = document.getElementById('iplDateTo').value;
      return (from ? formatFR(from) : '') + ' – ' + (to ? formatFR(to) : '');
    }
    return sel.options[sel.selectedIndex].text;
  }

  // ── Listes cochables (pays / mots-clés / IP) ────────────────────────
  function makeCheckPopover(kind, selected){
    var items = kind === 'country' ? IPL_STATS_COUNTRIES : (kind === 'keyword' ? IPL_STATS_KEYWORDS : IPL_STATS_IPS);
    var kindLabel = kind === 'country' ? I.label_countries : (kind === 'keyword' ? I.label_keywords : I.label_ip);
    var placeholder = kind === 'country' ? I.filter_countries : (kind === 'keyword' ? I.filter_keywords : I.filter_ips);

    var wrap = document.createElement('details');
    wrap.className = 'ipl-stats-check';
    var summary = document.createElement('summary');
    wrap.appendChild(summary);

    var pop = document.createElement('div');
    pop.className = 'ipl-stats-check-pop';

    var search = document.createElement('input');
    search.type = 'text';
    search.placeholder = placeholder;
    pop.appendChild(search);

    items.forEach(function(item){
      var value = kind === 'keyword' ? item : (kind === 'country' ? item.code : item.ip);
      var text  = kind === 'keyword' ? item : (kind === 'country' ? (item.name + ' (' + item.code + ')') : item.ip);
      var hits  = kind === 'keyword' ? null : item.hits;

      var row = document.createElement('label');
      row.className = 'ipl-stats-check-row';
      var cb = document.createElement('input');
      cb.type = 'checkbox';
      cb.checked = selected.indexOf(value) !== -1;
      cb.dataset.value = value;
      var txt = document.createElement('span');
      txt.textContent = text;
      row.appendChild(cb);
      row.appendChild(txt);
      if (hits !== null){
        var h = document.createElement('span');
        h.className = 'hits';
        h.textContent = hits;
        row.appendChild(h);
      }
      row.dataset.searchtext = text.toLowerCase();
      cb.addEventListener('change', updateSummary);
      pop.appendChild(row);
    });

    wrap.appendChild(pop);

    search.addEventListener('input', function(){
      var q = search.value.toLowerCase();
      pop.querySelectorAll('.ipl-stats-check-row').forEach(function(r){
        r.hidden = q.length > 0 && r.dataset.searchtext.indexOf(q) === -1;
      });
    });

    function updateSummary(){
      var n = pop.querySelectorAll('input[type="checkbox"]:checked').length;
      summary.innerHTML = kindLabel + (n ? ' &middot; <span class="count">' + n + '</span>' : '');
    }
    updateSummary();

    wrap.getValues = function(){
      return Array.prototype.slice.call(pop.querySelectorAll('input[type="checkbox"]:checked')).map(function(cb){ return cb.dataset.value; });
    };
    return wrap;
  }

  // ── Ligne "série" ────────────────────────────────────────────────────
  function createSeriesRow(cfg, colorHex){
    rowSeq++;
    var row = document.createElement('div');
    row.className = 'ipl-stats-series-row';
    row.dataset.color = colorHex;

    var swatch = document.createElement('input');
    swatch.type = 'color'; swatch.className = 'ipl-stats-swatch'; swatch.value = colorHex;
    swatch.addEventListener('input', function(){ row.dataset.color = swatch.value; });
    row.appendChild(swatch);

    var fields = document.createElement('div');
    fields.className = 'ipl-stats-series-fields';

    var labelInput = document.createElement('input');
    labelInput.type = 'text'; labelInput.value = cfg.label || '';
    labelInput.placeholder = I.series_name_placeholder;
    fields.appendChild(labelInput);

    var typeSelect = document.createElement('select');
    ['all','normal','bot','blocked'].forEach(function(t){
      var opt = document.createElement('option');
      opt.value = t; opt.textContent = TYPE_LABELS[t];
      if (t === cfg.type) opt.selected = true;
      typeSelect.appendChild(opt);
    });
    fields.appendChild(typeSelect);

    var countryPop = makeCheckPopover('country', cfg.countries || []);
    fields.appendChild(countryPop);
    var keywordPop = makeCheckPopover('keyword', cfg.keywords || []);
    fields.appendChild(keywordPop);
    var ipPop = makeCheckPopover('ip', cfg.ips || []);
    fields.appendChild(ipPop);

    var axisToggle = document.createElement('div');
    axisToggle.className = 'ipl-stats-axis-toggle';
    var btnLeft = document.createElement('button'); btnLeft.type = 'button'; btnLeft.textContent = I.axis_left;
    var btnRight = document.createElement('button'); btnRight.type = 'button'; btnRight.textContent = I.axis_right;
    function setAxis(a){
      row.dataset.axis = a;
      btnLeft.classList.toggle('active', a === 'y');
      btnRight.classList.toggle('active', a === 'y1');
    }
    btnLeft.addEventListener('click', function(){ setAxis('y'); });
    btnRight.addEventListener('click', function(){ setAxis('y1'); });
    setAxis(cfg.axis || 'y');
    axisToggle.appendChild(btnLeft); axisToggle.appendChild(btnRight);
    fields.appendChild(axisToggle);

    row.appendChild(fields);

    var remove = document.createElement('button');
    remove.type = 'button'; remove.className = 'ipl-stats-row-remove'; remove.title = I.remove_series;
    remove.textContent = '✕';
    remove.addEventListener('click', function(){ row.remove(); updateSeriesHint(); });
    row.appendChild(remove);

    row.getConfig = function(){
      return {
        label: labelInput.value || TYPE_LABELS[typeSelect.value],
        type: typeSelect.value,
        countries: countryPop.getValues(),
        keywords: keywordPop.getValues(),
        ips: ipPop.getValues(),
        axis: row.dataset.axis,
        color: row.dataset.color
      };
    };
    return row;
  }

  function updateSeriesHint(){
    var n = document.getElementById('iplSeriesList').children.length;
    document.getElementById('iplSeriesCountHint').textContent = n + ' ' + (n > 1 ? I.series_active_many : I.series_active_one);
  }

  function loadSeriesConfigs(configs){
    var list = document.getElementById('iplSeriesList');
    list.innerHTML = '';
    configs.forEach(function(cfg, i){
      var color = cfg.color || SERIES_PALETTE[i % SERIES_PALETTE.length];
      list.appendChild(createSeriesRow(cfg, color));
    });
    updateSeriesHint();
  }

  document.getElementById('iplAddSeriesBtn').addEventListener('click', function(){
    var n = document.getElementById('iplSeriesList').children.length;
    var color = SERIES_PALETTE[n % SERIES_PALETTE.length];
    document.getElementById('iplSeriesList').appendChild(createSeriesRow({ label:'', type:'all', countries:[], keywords:[], ips:[], axis:'y' }, color));
    updateSeriesHint();
  });

  // ── Préréglages fixes ────────────────────────────────────────────────
  function clearActivePresetMark(){
    document.querySelectorAll('.ipl-stats-preset-btn.active').forEach(function(b){ b.classList.remove('active'); });
  }

  function applyFixedPreset(preset, sourceEl){
    selectedSlot = null;
    document.getElementById('iplEditBar').hidden = true;
    loadSeriesConfigs(preset.series);
    clearActivePresetMark();
    sourceEl.classList.add('active');
    renderUserPresets();
    runGenerate();
  }

  function renderFixedPresets(){
    var wrap = document.getElementById('iplPresetsFixed');
    wrap.innerHTML = '';
    FIXED_PRESETS.forEach(function(preset){
      var btn = document.createElement('button');
      btn.type = 'button'; btn.className = 'ipl-stats-preset-btn';
      btn.innerHTML = '<span class="p-name"></span><span class="p-meta"></span>';
      btn.querySelector('.p-name').textContent = preset.name;
      btn.querySelector('.p-meta').textContent = preset.series.length + ' ' + I.slot_series_suffix;
      btn.addEventListener('click', function(){ applyFixedPreset(preset, btn); });
      wrap.appendChild(btn);
    });
  }

  // ── Préréglages configurables ────────────────────────────────────────
  function selectUserSlot(i){
    selectedSlot = i;
    var preset = USER_PRESETS[i];
    clearActivePresetMark();
    loadSeriesConfigs(preset ? preset.series : []);
    document.getElementById('iplEditBarName').value = preset ? preset.name : '';
    document.getElementById('iplEditBarLabel').textContent = I.slot_prefix + ' ' + (i + 1) + ' · ' + (preset ? I.slot_edit : I.slot_new);
    document.getElementById('iplEditBarDelete').hidden = !preset;
    document.getElementById('iplEditBar').hidden = false;
    renderUserPresets();
  }

  function renderUserPresets(){
    var wrap = document.getElementById('iplPresetsUser');
    wrap.innerHTML = '';
    USER_PRESETS.forEach(function(preset, i){
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ipl-stats-preset-btn' + (preset ? '' : ' empty') + (selectedSlot === i ? ' active' : '');
      btn.innerHTML = '<span class="p-name"></span><span class="p-meta"></span>';
      if (preset){
        btn.querySelector('.p-name').textContent = preset.name;
        btn.querySelector('.p-meta').textContent = preset.series.length + ' ' + I.slot_series_suffix;
      } else {
        btn.querySelector('.p-name').textContent = I.slot_empty_name;
        btn.querySelector('.p-meta').textContent = I.slot_empty_meta;
      }
      btn.addEventListener('click', function(){ selectUserSlot(i); });
      wrap.appendChild(btn);
    });
  }

  document.getElementById('iplEditBarSave').addEventListener('click', function(){
    if (selectedSlot === null) return;
    var configs = Array.prototype.map.call(document.getElementById('iplSeriesList').children, function(r){ return r.getConfig(); });
    if (!configs.length){ window.alert(I.save_need_series); return; }
    var name = document.getElementById('iplEditBarName').value.trim() || (I.slot_prefix + ' ' + (selectedSlot + 1));

    document.getElementById('iplPresetFormAction').value = 'save_stats_preset';
    document.getElementById('iplPresetFormSlot').value = String(selectedSlot);
    document.getElementById('iplPresetFormName').value = name;
    document.getElementById('iplPresetFormSeriesJson').value = JSON.stringify(configs);
    document.getElementById('iplPresetForm').submit();
  });

  document.getElementById('iplEditBarDelete').addEventListener('click', function(){
    if (selectedSlot === null) return;
    document.getElementById('iplPresetFormAction').value = 'delete_stats_preset';
    document.getElementById('iplPresetFormSlot').value = String(selectedSlot);
    document.getElementById('iplPresetForm').submit();
  });

  document.getElementById('iplEditBarCancel').addEventListener('click', function(e){
    e.preventDefault();
    selectedSlot = null;
    document.getElementById('iplEditBar').hidden = true;
    renderUserPresets();
  });

  // ── Génération du graphique ──────────────────────────────────────────
  function isDatasetHidden(idx){
    var m = chart.getDatasetMeta(idx);
    return m.hidden !== null ? m.hidden : !!chart.data.datasets[idx].hidden;
  }

  function syncAxisVisibility(){
    var visibleLeft = false, visibleRight = false;
    chart.data.datasets.forEach(function(ds, idx){
      if (isDatasetHidden(idx)) return;
      if (ds.yAxisID === 'y1') visibleRight = true; else visibleLeft = true;
    });
    if (chart.options.scales.y)  chart.options.scales.y.display  = visibleLeft;
    if (chart.options.scales.y1){
      chart.options.scales.y1.display = visibleRight;
      chart.options.scales.y1.grid.drawOnChartArea = visibleRight && !visibleLeft;
    }
  }

  function updateStatTotal(){
    var total = 0;
    chart.data.datasets.forEach(function(ds, i){
      if (chart.getDatasetMeta(i).hidden) return;
      total += ds.data.reduce(function(a,b){ return a+b; }, 0);
    });
    document.getElementById('iplStatTotal').textContent = total.toLocaleString();
  }

  function renderLegend(datasets){
    var wrap = document.getElementById('iplLegend');
    wrap.innerHTML = '';
    datasets.forEach(function(ds, i){
      var chip = document.createElement('button');
      chip.type = 'button'; chip.className = 'ipl-stats-legend-chip';
      chip.innerHTML = '<span class="dot" style="background:' + ds.borderColor + '"></span><span></span><span class="axis-badge"></span>';
      chip.querySelectorAll('span')[1].textContent = ds.label;
      chip.querySelector('.axis-badge').textContent = ds.yAxisID === 'y1' ? I.axis_badge_right : I.axis_badge_left;
      chip.addEventListener('click', function(){
        var meta = chart.getDatasetMeta(i);
        meta.hidden = meta.hidden === null ? !chart.data.datasets[i].hidden : !meta.hidden;
        chip.classList.toggle('off', !!meta.hidden);
        syncAxisVisibility();
        chart.update();
        updateStatTotal();
      });
      wrap.appendChild(chip);
    });
  }

  function updateActivePresetTag(){
    var activeName = document.querySelector('.ipl-stats-preset-btn.active .p-name');
    var tag = document.getElementById('iplActivePresetName');
    if (activeName && activeName.textContent){
      tag.textContent = activeName.textContent;
      tag.hidden = false;
    } else {
      tag.hidden = true;
    }
  }

  function renderChart(payload){
    document.getElementById('iplGranularityBadge').textContent =
      payload.granularity === 'month' ? I.granularity_month :
      (payload.granularity === 'week' ? I.granularity_week : I.granularity_day);
    document.getElementById('iplPeriodLabel').textContent = periodLabelText();
    updateActivePresetTag();

    var usesLeft  = payload.series.some(function(s){ return s.axis !== 'y1'; });
    var usesRight = payload.series.some(function(s){ return s.axis === 'y1'; });

    var datasets = payload.series.map(function(s, i){
      var color = s.color || SERIES_PALETTE[i % SERIES_PALETTE.length];
      return {
        label: s.label, data: s.data, borderColor: color, backgroundColor: color,
        yAxisID: s.axis, borderWidth: 2, pointRadius: 0, pointHoverRadius: 4,
        pointHoverBackgroundColor: color, pointHoverBorderColor: '#fff',
        pointHoverBorderWidth: 2, tension: 0.25, fill: false, spanGaps: true
      };
    });

    var scales = { x: { grid: { color: 'rgba(0,0,0,0.12)' }, ticks: { maxRotation:0, autoSkip:true } } };
    if (usesLeft)  scales.y  = { beginAtZero:true, position:'left',  grid: { color:'rgba(0,0,0,0.12)' } };
    if (usesRight) scales.y1 = { beginAtZero:true, position:'right', grid: { drawOnChartArea: !usesLeft } };

    var data = { labels: payload.labels, datasets: datasets };
    var options = {
      responsive:true, maintainAspectRatio:false, resizeDelay:150,
      interaction: { mode:'index', intersect:false },
      plugins: { legend: { display:false }, iplChartBg: { color: currentBgColor() } },
      scales: scales
    };

    if (chart){ chart.data = data; chart.options = options; chart.update(); }
    else { chart = new Chart(document.getElementById('iplChart').getContext('2d'), { type:'line', data:data, options:options }); }

    renderLegend(datasets);
    updateStatTotal();

    document.getElementById('iplChartEmpty').hidden = true;
    document.getElementById('iplChartError').hidden = true;
    document.getElementById('iplChartWrap').hidden = false;
  }

  function runGenerate(){
    var configs = Array.prototype.map.call(document.getElementById('iplSeriesList').children, function(r){ return r.getConfig(); });
    showSubTab('chart');
    if (!configs.length){
      document.getElementById('iplChartWrap').hidden = true;
      document.getElementById('iplChartError').hidden = true;
      document.getElementById('iplChartEmpty').hidden = false;
      return;
    }

    var payload = periodPayload();
    payload.series = configs;
    payload.bg_color = currentBgColor();

    fetch(IPL_STATS_AJAX_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(function(r){ return r.json(); }).then(function(res){
      if (res.error){
        document.getElementById('iplChartWrap').hidden = true;
        document.getElementById('iplChartEmpty').hidden = true;
        var errEl = document.getElementById('iplChartError');
        errEl.hidden = false;
        errEl.textContent = res.error;
        return;
      }
      renderChart(res);
    }).catch(function(){
      document.getElementById('iplChartWrap').hidden = true;
      document.getElementById('iplChartEmpty').hidden = true;
      var errEl = document.getElementById('iplChartError');
      errEl.hidden = false;
      errEl.textContent = 'Erreur réseau.';
    });
  }

  document.getElementById('iplGenerateBtn').addEventListener('click', runGenerate);
  document.getElementById('iplDateFrom') && document.getElementById('iplDateFrom').addEventListener('change', function(){ if (!document.getElementById('iplStatsPanelChart').hidden) runGenerate(); });
  document.getElementById('iplDateTo')   && document.getElementById('iplDateTo').addEventListener('change', function(){ if (!document.getElementById('iplStatsPanelChart').hidden) runGenerate(); });

  // ── Export CSV / PNG / JSON ──────────────────────────────────────────
  function exportFilename(ext){
    var d = new Date().toISOString().slice(0,10);
    return 'statistiques_ip_location_' + d + '.' + ext;
  }

  function triggerDownload(filename, blob){
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url; a.download = filename;
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    setTimeout(function(){ URL.revokeObjectURL(url); }, 1000);
  }

  function csvEscape(v){
    v = String(v);
    return /[;"\n]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v;
  }

  function exportCSV(){
    if (!chart) return;
    var rows = [['Date'].concat(chart.data.datasets.map(function(ds){ return ds.label; }))];
    chart.data.labels.forEach(function(label, i){
      rows.push([label].concat(chart.data.datasets.map(function(ds){ return ds.data[i]; })));
    });
    var csv = rows.map(function(r){ return r.map(csvEscape).join(';'); }).join('\r\n');
    triggerDownload(exportFilename('csv'), new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' }));
  }

  function exportJSON(){
    if (!chart) return;
    var payload = {
      generated_at: new Date().toISOString(),
      labels: chart.data.labels,
      series: chart.data.datasets.map(function(ds){
        return { label: ds.label, axis: ds.yAxisID === 'y1' ? 'right' : 'left', color: ds.borderColor, data: ds.data };
      })
    };
    triggerDownload(exportFilename('json'), new Blob([JSON.stringify(payload, null, 2)], { type:'application/json' }));
  }

  function roundRectPath(ctx, x, y, w, h, r){
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
  }

  function computeStatLayout(dpr){
    var pad = Math.round(14 * dpr);
    var periodLineH = Math.round(15 * dpr);
    var valueLineH = Math.round(24 * dpr);
    return {
      pad: pad,
      periodFont: 'bold ' + Math.round(11 * dpr) + 'px sans-serif',
      valueFont: Math.round(20 * dpr) + 'px sans-serif',
      labelFont: Math.round(10.5 * dpr) + 'px sans-serif',
      period: (document.getElementById('iplPeriodLabel').textContent || '').trim(),
      value: (document.getElementById('iplStatTotal').textContent || '').trim(),
      label: document.querySelector('.ipl-stats-stat-label').textContent.trim(),
      periodLineH: periodLineH,
      valueLineH: valueLineH,
      height: pad + periodLineH + valueLineH + Math.round(15 * dpr) + Math.round(6 * dpr)
    };
  }

  function drawStatLayout(ctx, layout){
    ctx.textAlign = 'left'; ctx.textBaseline = 'top';
    ctx.font = layout.periodFont; ctx.fillStyle = '#555';
    ctx.fillText(layout.period, layout.pad, layout.pad);
    ctx.font = layout.valueFont; ctx.fillStyle = '#222';
    ctx.fillText(layout.value, layout.pad, layout.pad + layout.periodLineH);
    ctx.font = layout.labelFont; ctx.fillStyle = '#888';
    ctx.fillText(layout.label, layout.pad, layout.pad + layout.periodLineH + layout.valueLineH);
  }

  function computeLegendLayout(measureCtx, canvasWidth, dpr, startY){
    var pad = Math.round(14 * dpr);
    var dotR = Math.round(5 * dpr);
    var gapX = Math.round(16 * dpr);
    var rowH = Math.round(26 * dpr);
    var font = Math.round(12.5 * dpr) + 'px sans-serif';
    var badgeFont = Math.round(9.5 * dpr) + 'px sans-serif';

    var items = chart.data.datasets.map(function(ds, i){
      var meta = chart.getDatasetMeta(i);
      var hidden = meta.hidden !== null ? meta.hidden : !!ds.hidden;
      measureCtx.font = font;
      var textW = measureCtx.measureText(ds.label).width;
      var axisLabel = ds.yAxisID === 'y1' ? I.axis_badge_right : I.axis_badge_left;
      measureCtx.font = badgeFont;
      var badgeW = measureCtx.measureText(axisLabel).width + Math.round(8 * dpr);
      var width = dotR * 2 + Math.round(7 * dpr) + textW + Math.round(7 * dpr) + badgeW;
      return { color: ds.borderColor, text: ds.label, axisLabel: axisLabel, hidden: hidden, textW: textW, badgeW: badgeW, width: width };
    });

    var maxWidth = canvasWidth - pad * 2;
    var rows = [[]];
    var rowWidth = 0;
    items.forEach(function(item){
      if (rowWidth + item.width > maxWidth && rows[rows.length - 1].length > 0){
        rows.push([]);
        rowWidth = 0;
      }
      rows[rows.length - 1].push(item);
      rowWidth += item.width + gapX;
    });

    return { pad: pad, dotR: dotR, gapX: gapX, rowH: rowH, font: font, badgeFont: badgeFont, dpr: dpr, startY: startY,
              rows: rows, height: startY + rows.length * rowH + Math.round(4 * dpr) };
  }

  function drawLegendLayout(ctx, layout){
    layout.rows.forEach(function(row, rowIndex){
      var x = layout.pad;
      var y = layout.startY + rowIndex * layout.rowH + layout.rowH / 2;
      row.forEach(function(item){
        ctx.globalAlpha = item.hidden ? 0.42 : 1;
        ctx.beginPath();
        ctx.fillStyle = item.color;
        ctx.arc(x + layout.dotR, y, layout.dotR, 0, Math.PI * 2);
        ctx.fill();
        x += layout.dotR * 2 + Math.round(7 * layout.dpr);

        ctx.font = layout.font; ctx.fillStyle = '#222';
        ctx.textBaseline = 'middle'; ctx.textAlign = 'left';
        ctx.fillText(item.text, x, y);
        x += item.textW + Math.round(7 * layout.dpr);

        var bh = Math.round(15 * layout.dpr);
        ctx.strokeStyle = '#bbb';
        ctx.lineWidth = Math.max(1, layout.dpr);
        roundRectPath(ctx, x, y - bh / 2, item.badgeW, bh, Math.round(3 * layout.dpr));
        ctx.stroke();
        ctx.font = layout.badgeFont; ctx.fillStyle = '#888';
        ctx.textAlign = 'center';
        ctx.fillText(item.axisLabel, x + item.badgeW / 2, y + 1);
        ctx.textAlign = 'left';

        x += item.badgeW + layout.gapX;
        ctx.globalAlpha = 1;
      });
    });
  }

  function exportPNG(){
    if (!chart) return;
    var srcCanvas = document.getElementById('iplChart');
    var dpr = window.devicePixelRatio || 1;

    var measureCtx = document.createElement('canvas').getContext('2d');
    var statLayout = computeStatLayout(dpr);
    var legendLayout = computeLegendLayout(measureCtx, srcCanvas.width, dpr, statLayout.height);

    var tmp = document.createElement('canvas');
    tmp.width = srcCanvas.width;
    tmp.height = srcCanvas.height + legendLayout.height;
    var ctx = tmp.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, tmp.width, tmp.height);

    drawStatLayout(ctx, statLayout);
    drawLegendLayout(ctx, legendLayout);
    ctx.drawImage(srcCanvas, 0, legendLayout.height);

    tmp.toBlob(function(blob){ triggerDownload(exportFilename('png'), blob); }, 'image/png', 1);
  }

  (function initExportMenu(){
    var btn = document.getElementById('iplExportBtn');
    var dropdown = document.getElementById('iplExportDropdown');
    function setOpen(open){ dropdown.hidden = !open; }
    btn.addEventListener('click', function(e){ e.stopPropagation(); setOpen(dropdown.hidden); });
    dropdown.addEventListener('click', function(e){ e.stopPropagation(); });
    document.addEventListener('click', function(){ setOpen(false); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') setOpen(false); });
    dropdown.querySelectorAll('button[data-format]').forEach(function(b){
      b.addEventListener('click', function(){
        setOpen(false);
        if (b.dataset.format === 'csv') exportCSV();
        else if (b.dataset.format === 'json') exportJSON();
        else if (b.dataset.format === 'png') exportPNG();
      });
    });
  })();

  // ── Initialisation ───────────────────────────────────────────────────
  var today = new Date(), from = new Date(); from.setDate(today.getDate() - 29);
  document.getElementById('iplDateTo').value   = IPL_LAST_STATS_DATE_TO   || today.toISOString().slice(0,10);
  document.getElementById('iplDateFrom').value = IPL_LAST_STATS_DATE_FROM || from.toISOString().slice(0,10);

  renderFixedPresets();
  renderUserPresets();
  loadSeriesConfigs(FIXED_PRESETS[0].series);
  var firstPresetBtn = document.querySelector('#iplPresetsFixed .ipl-stats-preset-btn');
  if (firstPresetBtn) firstPresetBtn.classList.add('active');

})();
{/literal}
</script>
