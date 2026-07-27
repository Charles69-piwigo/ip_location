<?php
/*
Plugin Name: ip_location
Version: 2.4
Description: Log des visites des guests avec géolocalisation IP + traitement htaccess
Plugin URI: https://piwigo.org/ext/extension_view.php?eid=1068
Author: Charles69 
Has Settings: webmaster
*/

// Versions
/*
    version 2.4 - 27/07/2026
        fix perf géo : court-circuit immédiat des IP privées/réservées (réseau
        local) dans resolve_geo(), plus jamais envoyées aux providers
        cache géo à double TTL : 30 jours pour une résolution réussie, 2h pour
        un échec 'Unknown' (évite de re-solliciter les providers en boucle)
        réduit CURLOPT_CONNECTTIMEOUT de 5s à 2s (limite le pire cas provider injoignable)
        aide : note sur l'ajout du réseau local à la whitelist

    version 2.3 - 27/07/2026
        perf : log_visit() allégé sur le chemin chaud (détection bot par user-agent
        seul, sans scan SQL ; retrait du marquage rétroactif du chemin chaud ;
        purge échantillonnée 1/50 au lieu d'un COUNT(*) à chaque visite)
        ajouté ip_location_classify_recent() : classification bot par co-visitation
        rejouée en lot, déclenchée uniquement à la consultation de l'admin
        ajouté index idx_url_date et idx_visit_date sur ip_location_log

    version 2.2 - 27/07/2026
        ajouté filtre pays (liste blanche) sur les téléchargements d'originaux,
        appliqué sur l'évènement init pour couvrir action.php
        ajouté colonne log_type (distingue les tentatives de téléchargement du reste du journal)

    version 2.1a - 26/05/2026
        corrigé bug avec la fonction Upload

    version 2.1 - 25/05/2026
        comptage visite prise en compte de 'recent' 'hasard' ...
        ainsi que les diaporama
        ajouté selecteurs pour personnalisation par css

    version 2.0 - 24/05/2026
        clarifié notion d'accès vs visites
        filtre sur IP dans le journal des visites
        mise à jour de l'aide
        avertissement qd nb enregistrement inf à la période
        
    version 1.9c - 23/05/2026
        remplacé visiteurs par visites
        ajouté quinzaine
        corrigé taille police
    version 1.9b - 22/05/2026
        box vide Kat
    version 1.9a - 22/05/2026
        affichage des visites dans le menu principal
    version 1.9 - 29/03/2026
        ajouté avertissement nginx
        syntaxe courte IP/16 remplacée par X.Y.0.0/16
        curl_close remplacé par unset
        filtre date
    version 1.8 - 25/03/2026
        ajouté Blocage par URL
        divers UX
    version 1.7a activé langue UK
    version 1.7 ajouté URI
    version 1.6e 13/03/2026
        log commenté - 1ère diffusion
    version 1.6 12/03/2026
        ajouté logs
        ajouté freeipapi.com
        restructuration de la page admin
        curl au lieu de ...
    version 1.5 11/03/2026
        cache géo : expiry 30 jours + limite 5000 entrées
        tableau IPs de .htaccess avec pays/ville, suppression éditeur textarea
        fallback multi-providers géolocalisation (ip-api.com > ipwho.is > geoplugin.net > ipapi.co)
    version 1.4 10/03/2026
        config unifiée en une seule entrée _config
    version 1.3 10/03/2026
        ajouté gestion htaccess + aide
    version 1.2 ajouté filtre et déf bot modifié 10/03/2026
    version 1.1 css
    version 1.0 initial 05/03/2026
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

if (basename(dirname(__FILE__)) != 'ip_location')
{
  add_event_handler('init', 'ip_location_error');
  function ip_location_error()
  {
    global $page;
    $page['errors'][] = 'Désactiver le plugin et renommer le répertoire "ip_location"';
  }
  return;
}



// Plugin constants
define('IP_LOCATION_PATH', PHPWG_PLUGINS_PATH . 'ip_location/');

// Debug — décommenter pour activer les logs =============================
/*
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
$_ipl_log = PHPWG_ROOT_PATH . 'plugins/ip_location/ip_location_debug.log';
if (file_exists($_ipl_log) && filesize($_ipl_log) > 256 * 1024) {
    file_put_contents($_ipl_log, ''); // vider le fichier au-delà de 256 Ko
}
ini_set('error_log', $_ipl_log);
unset($_ipl_log);
*/


//===================== CHARGEMENT DES LANGUES , UK PAR DEFAUT ==================
// Charger d'abord l'anglais comme base
load_language('plugin.lang', IP_LOCATION_PATH, array('language' => 'en_UK', 'no_fallback' => true));
// Puis charger la langue de l'utilisateur (qui écrasera l'anglais si c'est du français)
load_language('plugin.lang', IP_LOCATION_PATH);
//=================================================================================



/**
 * Retourne la configuration du plugin (tableau, avec valeurs par défaut).
 * Lit l'entrée unique 'ip_location' dans _config (valeur sérialisée).
 */
function ip_location_get_conf()
{
    global $conf;
    static $cache = null;
    if ($cache !== null) return $cache;

    $default = [
        'blocked_countries'    => '',
        'blocked_url_keywords' => '',
        'whitelist'            => '',
        'blocking_enabled'     => '0',
        'htaccess_enabled'     => '0',
        'max_records'          => 10000,
        'visitors_enabled'     => '0',
        'visitors_period'      => 'week',
        'download_filter_enabled'    => '0',
        'download_allowed_countries' => '',
        'download_geo_fail_mode'     => 'open',
    ];

    if (!empty($conf['ip_location'])) {
        $stored = @unserialize($conf['ip_location']);
        if (is_array($stored)) {
            $cache = array_merge($default, $stored);
            return $cache;
        }
    }
    $cache = $default;
    return $cache;
}

// Hooks de visite
add_event_handler('loc_begin_index',   'ip_location_log_visit');
add_event_handler('loc_begin_picture', 'ip_location_log_visit');

// Filtre pays sur les téléchargements d'originaux — voir §1 du handoff :
// action.php ne déclenche ni loc_begin_index ni loc_begin_picture, donc on
// s'accroche à init (déclenché par common.inc.php, avant que action.php
// n'atteigne son propre pwg_log).
add_event_handler('init', 'ip_location_download_guard');

// Logger les photos vues via PhotoSwipe (navigation JS sans rechargement)
add_event_handler('loc_after_page_header', 'ip_location_inject_pswp_logger');

// ─── Blockmanager : bouton dans la barre de navigation ────────────────────
add_event_handler('blockmanager_register_blocks', 'ip_location_register_visitors_block');
add_event_handler('blockmanager_apply',           'ip_location_apply_visitors_block');

function ip_location_register_visitors_block($menu_ref_arr)
{
    $plugin_conf = ip_location_get_conf();
    if (empty($plugin_conf['visitors_enabled'])) return;

    $menu = &$menu_ref_arr[0];
    if ($menu->get_id() != 'menubar') return;
    $menu->register_block(new RegisteredBlock('mbIplVisitors', 'Visitors', 'IPL'));
}

function ip_location_apply_visitors_block($menu_ref_arr)
{
    global $template, $user;
    $plugin_conf = ip_location_get_conf();
    if (empty($plugin_conf['visitors_enabled'])) return;

    $menu = &$menu_ref_arr[0];
    if ($menu->get_id() != 'menubar') return;
    $block = $menu->get_block('mbIplVisitors');
    if (!$block) return;

    $template->assign('IPL_VIS_LBL', l10n('Visiteurs'));
    $template->set_template_dir(IP_LOCATION_PATH . 'template/');

    $theme = isset($user['theme']) ? $user['theme'] : '';
    if (in_array($theme, ['bootstrap_darkroom', 'bootstrapdefault'])) {
        $block->template = 'visitors_bootstrap.tpl';
    } elseif ($theme === 'smartpocket') {
        $block->template = 'visitors_smartpocket.tpl';
    } else {
        $block->template = 'visitors_default.tpl';
    }
}

// ─── Panel flottant + JS (toutes les pages publiques) ─────────────────────
add_event_handler('loc_after_page_header', 'ip_location_inject_visitors_panel');

function ip_location_inject_visitors_panel()
{
    // Pages admin uniquement pour les admins — le panel visiteurs est réservé aux pages publiques
    if (defined('IN_ADMIN') && IN_ADMIN) return;

    $plugin_conf = ip_location_get_conf();
    if (empty($plugin_conf['visitors_enabled'])) return;

    $ajax_url      = json_encode(get_root_url() . 'plugins/ip_location/ajax_visitors.php');
    $flag_base_url = json_encode(get_root_url() . 'plugins/ip_location/image/');
    $period_labels = json_encode(array(
        'week'      => l10n('cette semaine'),
        'fortnight' => l10n('cette quinzaine'),
        'month'     => l10n('ce mois'),
        'quarter'   => l10n('ce trimestre'),
    ));
    // Chaînes pour HTML (htmlspecialchars) et pour JS (json_encode séparé)
    $r_visitors = l10n('Visiteurs');
    $r_count    = l10n('Nombre');
    $r_country  = l10n('Pays');
    $r_nodata   = l10n('Aucune donnée.');
    $r_loading  = l10n('Chargement…');
    $r_error    = l10n('Erreur de chargement.');
    $r_total    = l10n('Total');
    $r_warn     = l10n('Couverture incomplète');

    $h = function($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
    $j = function($s) { return json_encode($s); };
?>
<div id="ipl-vis-panel" style="display:none;position:fixed;z-index:9999;background:#fff;color:#222;border:1px solid #bbb;border-radius:0 0 6px 6px;box-shadow:0 6px 20px rgba(0,0,0,.28);width:320px;max-height:430px;overflow:hidden;font-size:.88em;font-family:sans-serif;">
  <div id="ipl-vis-header" style="background:#444;color:#fff;padding:6px 10px;display:flex;justify-content:space-between;align-items:center;white-space:nowrap;gap:8px;">
    <span><?php echo $h($r_visitors); ?> <span id="ipl-vis-period"></span></span>
    <span>
      <a id="ipl-sc" style="color:#fff;font-weight:bold;text-decoration:none;cursor:pointer;font-size:.82em;" onclick="iplVisSort('count')"><?php echo $h($r_count); ?></a>
      <a id="ipl-sk" style="color:#888;text-decoration:none;cursor:pointer;font-size:.82em;margin-left:8px;" onclick="iplVisSort('country')"><?php echo $h($r_country); ?></a>
      <a id="ipl-vis-refresh" style="color:#aaa;text-decoration:none;cursor:pointer;font-size:.9em;margin-left:10px;" onclick="iplVisRefresh()" title="<?php echo $h(l10n('Rafraîchir')); ?>">&#8635;</a>
    </span>
  </div>
  <div id="ipl-vis-body" style="overflow-y:auto;max-height:370px;"><div style="text-align:center;padding:20px;color:#888;"><?php echo $h($r_loading); ?></div></div>
  <div id="ipl-vis-footer" style="padding:5px 10px;border-top:1px solid #eee;font-size:.82em;color:#777;text-align:right;"></div>
</div>
<script>
(function(){
var _v={data:null,period:null,sort:'count',coverage_days:null,period_days:null,
  ajax:<?php echo $ajax_url; ?>,
  flags:<?php echo $flag_base_url; ?>,
  labels:<?php echo $period_labels; ?>,
  nodata:<?php echo $j($r_nodata); ?>,
  error:<?php echo $j($r_error); ?>,
  loading:<?php echo $j($r_loading); ?>,
  total:<?php echo $j($r_total); ?>,
  warn:<?php echo $j($r_warn); ?>
};

window.iplVisToggle=function(anchorEl){
  var p=document.getElementById('ipl-vis-panel');
  if(p.style.display!=='none'){p.style.display='none';return;}
  // Positionner sous l'élément déclencheur (nav item ou lien)
  var anchor=anchorEl||document.getElementById('ipl-vis-nav-item');
  if(anchor){
    var r=anchor.getBoundingClientRect();
    var pw=p.offsetWidth||320,dw=document.documentElement.clientWidth;
    p.style.top=r.bottom+'px';
    // Aligner le bord gauche du panel sur le bord gauche de l'ancre,
    // mais si ça déborde à droite, aligner les bords droits.
    var left=r.left;
    if(left+pw>dw-4)left=r.right-pw;
    p.style.left=Math.max(left,4)+'px';
    p.style.right='auto';
  } else {
    p.style.top='44px';p.style.right='12px';p.style.left='auto';
  }
  p.style.display='block';
  // Revérifier le TTL à chaque ouverture (même si données déjà en mémoire)
  try{
    var c=JSON.parse(sessionStorage.getItem('ipl_vis')||'null');
    if(c&&c.ts&&(Date.now()-c.ts)<600000){
      _v.data=c.rows;_v.period=c.period;_v.coverage_days=c.coverage_days||null;_v.period_days=c.period_days||null;iplVisRender();return;
    } else {
      _v.data=null;
      try{sessionStorage.removeItem('ipl_vis');}catch(e2){}
    }
  }catch(e){}
  iplVisFetch();
};

window.iplVisRefresh=function(){
  _v.data=null;
  try{sessionStorage.removeItem('ipl_vis');}catch(e){}
  iplVisFetch();
};

function iplVisFetch(){
  document.getElementById('ipl-vis-body').innerHTML='<div style="text-align:center;padding:20px;color:#888;">&#9203; '+_v.loading+'</div>';
  var x=new XMLHttpRequest();
  x.open('GET',_v.ajax);
  x.onload=function(){
    if(x.status===200){
      try{
        var d=JSON.parse(x.responseText);
        if(d.rows){
          _v.data=d.rows;_v.period=d.period||'week';_v.coverage_days=d.coverage_days||null;_v.period_days=d.period_days||null;
          try{sessionStorage.setItem('ipl_vis',JSON.stringify({rows:_v.data,period:_v.period,coverage_days:_v.coverage_days,period_days:_v.period_days,ts:Date.now()}));}catch(e){}
          iplVisRender();
        }else{iplVisErr();}
      }catch(e){iplVisErr();}
    }else{iplVisErr();}
  };
  x.onerror=function(){iplVisErr();};
  x.send();
}

function iplVisRender(){
  var rows=(_v.data||[]).slice();
  if(_v.sort==='country')rows.sort(function(a,b){return(a.country||'').localeCompare(b.country||'');});
  else rows.sort(function(a,b){return(b.visit_count|0)-(a.visit_count|0);});
  var lbl=_v.labels[_v.period]||_v.period;
  if(_v.coverage_days&&_v.period_days&&_v.coverage_days<_v.period_days){
    var tip=esc(_v.warn)+' : '+_v.coverage_days+' j / '+_v.period_days+' j';
    document.getElementById('ipl-vis-period').innerHTML=esc(lbl)+' <span title="'+tip+'" style="color:#f90;cursor:help;">⚠</span>';
  }else{
    document.getElementById('ipl-vis-period').textContent=lbl;
  }
  var total=0,html='';
  for(var i=0;i<rows.length;i++){
    var r=rows[i];
    var code=r.country_code||'';
    var flag=code?'<img style="width:16px;height:11px;margin-right:6px;vertical-align:middle;" src="'+_v.flags+code+'.png" alt="">':'<span style="display:inline-block;width:22px;"></span>';
    html+='<div style="display:flex;align-items:center;padding:3px 10px;border-bottom:1px solid #f2f2f2;">'+flag+'<span style="flex:1;">'+esc(r.country||'?')+'</span><span style="font-weight:bold;min-width:32px;text-align:right;">'+(r.visit_count|0)+'</span></div>';
    total+=(r.visit_count|0);
  }
  document.getElementById('ipl-vis-body').innerHTML=html||('<div style="padding:14px;text-align:center;color:#aaa;">'+_v.nodata+'</div>');
  document.getElementById('ipl-vis-footer').textContent=_v.total+' : '+total;
  document.getElementById('ipl-sc').style.color=_v.sort==='count'?'#fff':'#888';
  document.getElementById('ipl-sk').style.color=_v.sort==='country'?'#fff':'#888';
}

function iplVisErr(){document.getElementById('ipl-vis-body').innerHTML='<div style="padding:14px;text-align:center;color:#c00;">'+_v.error+'</div>';}

window.iplVisSort=function(by){_v.sort=by;if(_v.data!==null)iplVisRender();};

function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

document.addEventListener('click',function(e){
  var p=document.getElementById('ipl-vis-panel');
  var n=document.getElementById('ipl-vis-nav-item');
  if(p&&p.style.display!=='none'&&!p.contains(e.target)&&(!n||!n.contains(e.target)))p.style.display='none';
});
})();
</script>
<?php
}

/**
 * Requête HTTP GET avec cURL (préféré) ou file_get_contents en fallback.
 * Timeout 3s. Retourne le body ou false.
 */
function ip_location_http_get($url)
{
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT      => 'ip_location-piwigo/1.0',
        ]);
        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $errmsg   = curl_error($ch);
        unset($ch);
        if ($errno !== 0 || $response === false) {
            //error_log('[ip_location] cURL error on ' . $url . ' : [' . $errno . '] ' . $errmsg);
            return false;
        }
        return $response;
    }

    // Fallback file_get_contents
    $context = stream_context_create([
        'http' => ['timeout' => 3],
        'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    return @file_get_contents($url, false, $context);
}

/**
 * Injecte un wrapper du constructeur PhotoSwipe pour logger les vues en diaporama.
 * Utilise DOMContentLoaded (footer_scripts déjà exécutés à ce moment).
 */
function ip_location_inject_pswp_logger()
{
    // Pages admin uniquement pour les admins — rien à logger ni à injecter
    if (defined('IN_ADMIN') && IN_ADMIN) return;

    $ajax_log_url = json_encode(get_root_url() . 'plugins/ip_location/ajax_log.php');
?>
<script>
(function(){
var _logUrl=<?php echo $ajax_log_url; ?>;
document.addEventListener('DOMContentLoaded',function(){
  if(typeof PhotoSwipe==='undefined') return;
  var _O=PhotoSwipe;
  window.PhotoSwipe=function(el,ui,items,opts){
    var inst=new _O(el,ui,items,opts);
    function doLog(item){
      if(!item||!item.href) return;
      var x=new XMLHttpRequest();
      x.open('GET',_logUrl+'?url='+encodeURIComponent(item.href),true);
      x.send();
    }
    // Première photo affichée
    inst.listen('initialZoomInEnd',function(){ doLog(inst.currItem); });
    // Navigation vers une autre photo
    inst.listen('afterChange',function(){ doLog(inst.currItem); });
    return inst;
  };
});
})();
</script>
<?php
}

/**
 * Résout la géolocalisation d'une IP : court-circuit immédiat pour une IP privée/réservée
 * (jamais géolocalisable, jamais mise en cache), sinon cache (30 jours pour un succès,
 * 2 h pour un 'Unknown') puis providers en cascade (ip-api.com → freeipapi.com →
 * ipwho.is → geoplugin.net → ipapi.co). Le cache est alimenté dans tous les cas (y
 * compris les échecs, pour éviter de re-solliciter les providers en boucle) et
 * nettoyé (30 jours + limite 5000 entrées).
 *
 * @param string $ip           IP déjà échappée pour SQL (pwg_db_real_escape_string).
 * @param string $prefixeTable
 * @return array ['country' => ..., 'country_code' => ..., 'city' => ...] ('Unknown' si échec).
 */
function ip_location_resolve_geo($ip, $prefixeTable)
{
    // IP privée / réservée / loopback (ex: réseau local 192.168.x) — non géolocalisable :
    // retour immédiat, aucun appel réseau. filter_var tolère une IP déjà échappée
    // (une IP valide ne contient aucun caractère échappable, donc pas de ré-échappement ici).
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return ['country' => 'Unknown', 'country_code' => '', 'city' => 'Unknown'];
    }

    // Vérification du cache (30 jours pour une résolution réussie, 2 h pour un échec
    // 'Unknown' — laisse une chance de re-tenter une IP publique temporairement en échec)
    $query = '
SELECT country, country_code, city
  FROM ' . $prefixeTable . 'ip_location_cache
  WHERE ip = \'' . $ip . '\'
    AND (
          (country <> \'Unknown\' AND resolved_at >= NOW() - INTERVAL 30 DAY)
       OR (country =  \'Unknown\' AND resolved_at >= NOW() - INTERVAL 2 HOUR)
        );';
    $result = pwg_query($query);

    if (pwg_db_num_rows($result) > 0) {
        return pwg_db_fetch_assoc($result);
    }

    // Résolution géo avec fallback multi-providers
    $providers = [
        [
            'url'          => 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=country,countryCode,city',
            'country'      => 'country',
            'country_code' => 'countryCode',
            'city'         => 'city',
        ],
        [
            'url'          => 'https://free.freeipapi.com/api/json/' . rawurlencode($ip),
            'country'      => 'countryName',
            'country_code' => 'countryCode',
            'city'         => 'cityName',
        ],
        [
            'url'          => 'https://ipwho.is/' . rawurlencode($ip),
            'country'      => 'country',
            'country_code' => 'country_code',
            'city'         => 'city',
            'success'      => 'success',
        ],
        [
            'url'          => 'https://ssl.geoplugin.net/json.gp?ip=' . rawurlencode($ip),
            'country'      => 'geoplugin_countryName',
            'country_code' => 'geoplugin_countryCode',
            'city'         => 'geoplugin_city',
        ],
        [
            'url'          => 'https://ipapi.co/' . rawurlencode($ip) . '/json/',
            'country'      => 'country_name',
            'country_code' => 'country_code',
            'city'         => 'city',
        ],
    ];

    $geo = ['country' => 'Unknown', 'country_code' => '', 'city' => 'Unknown'];

    foreach ($providers as $provider) {
        $response = ip_location_http_get($provider['url']);
        if ($response === false) {
            continue;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            continue;
        }

        // Vérification champ 'success' (ipwho.is retourne success=false si IP invalide)
        if (isset($provider['success']) && empty($data[$provider['success']])) {
            continue;
        }

        $country      = !empty($data[$provider['country']])      ? $data[$provider['country']]      : '';
        $country_code = !empty($data[$provider['country_code']]) ? $data[$provider['country_code']] : '';
        $city         = !empty($data[$provider['city']])         ? $data[$provider['city']]         : '';

        if (!empty($country) && $country !== 'Unknown') {
            $geo['country']      = $country;
            $geo['country_code'] = $country_code;
            $geo['city']         = !empty($city) ? $city : 'Unknown';
            break; // Provider OK, on arrête
        }
    }

    // Mise en cache — y compris les échecs 'Unknown' (TTL court de 2 h en lecture, cf. plus
    // haut), pour éviter de re-tenter les 5 providers à chaque affichage. Cette écriture
    // n'est atteinte que par des IP publiques (les IP privées sont court-circuitées avant).
    // ON DUPLICATE KEY UPDATE réhydrate automatiquement une entrée 'Unknown' dès qu'une
    // résolution réussie survient (l'ancienne valeur est écrasée par la nouvelle).
    $query = '
INSERT INTO ' . $prefixeTable . 'ip_location_cache
  (ip, country, country_code, city, resolved_at)
  VALUES (
    \'' . $ip . '\',
    \'' . pwg_db_real_escape_string($geo['country']) . '\',
    \'' . pwg_db_real_escape_string($geo['country_code']) . '\',
    \'' . pwg_db_real_escape_string($geo['city']) . '\',
    NOW()
  )
  ON DUPLICATE KEY UPDATE
    country      = VALUES(country),
    country_code = VALUES(country_code),
    city         = VALUES(city),
    resolved_at  = NOW();';
    pwg_query($query);

    // Nettoyage du cache : supprimer les entrées > 30 jours
    pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_cache
  WHERE resolved_at < NOW() - INTERVAL 30 DAY');

    // Limite de taille : garder les 5000 entrées les plus récentes
    $r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_cache');
    list($cache_count) = pwg_db_fetch_row($r);
    if ($cache_count > 5000) {
        pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_cache
  ORDER BY resolved_at ASC
  LIMIT ' . ($cache_count - 5000));
    }

    return $geo;
}

/**
 * IP du client, alignée sur la source utilisée par le cœur Piwigo (pwg_log) :
 * $_SERVER['REMOTE_ADDR'] brut, sans parsing de X-Forwarded-For.
 */
function ip_location_client_ip()
{
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Indique si l'utilisateur invité (id 2) a la permission de télécharger les
 * originaux (enabled_high). Utilisé par admin.php pour l'affichage conditionnel
 * de la section "Filtre pays sur les téléchargements".
 */
function ip_location_guest_enabled_high()
{
    $result = pwg_query('SELECT enabled_high FROM ' . USER_INFOS_TABLE . ' WHERE user_id = 2');
    if ($result && pwg_db_num_rows($result) > 0) {
        list($val) = pwg_db_fetch_row($result);
        return $val === 'true';
    }
    return false;
}

/**
 * Filtre pays (liste blanche) sur les téléchargements d'originaux.
 * Accroché à init pour couvrir action.php, qui ne déclenche ni
 * loc_begin_index ni loc_begin_picture. Bloque avant que action.php
 * n'atteigne son propre pwg_log (donc aucune ligne dans history en cas de blocage).
 * Mécanisme volontairement indépendant de la blocklist PHP et du .htaccess.
 */
function ip_location_download_guard()
{
    global $user, $prefixeTable;

    // 1. Contexte download d'original uniquement
    if (script_basename() !== 'action') {
        return;
    }
    if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['part']) || $_GET['part'] !== 'e') {
        return;
    }

    // 2. Invités uniquement
    if (!isset($user['id']) || $user['id'] != 2) {
        return;
    }

    // 3. Filtre activé + allowlist non vide (sinon : traiter comme filtre inactif)
    $plugin_conf = ip_location_get_conf();
    if ($plugin_conf['download_filter_enabled'] !== '1') {
        return;
    }
    $allowed = array_filter(array_map('trim', explode(',', strtoupper($plugin_conf['download_allowed_countries']))));
    if (empty($allowed)) {
        return;
    }

    // 4. Les invités peuvent-ils télécharger le HD ? Sinon rien à protéger (action.php renverra 401 nativement).
    if (empty($user['enabled_high'])) {
        return;
    }

    // 5. IP alignée sur le cœur (REMOTE_ADDR, sans XFF) pour concorder avec history
    $ip_raw = ip_location_client_ip();

    // 6. Whitelist — toujours autorisée
    $whitelist = array_filter(array_map('trim', explode("\n", $plugin_conf['whitelist'])));
    if (in_array($ip_raw, $whitelist)) {
        return;
    }

    $ip = pwg_db_real_escape_string($ip_raw);

    // 7. Géo
    $geo    = ip_location_resolve_geo($ip, $prefixeTable);
    $geo_ok = ($geo['country'] !== 'Unknown');

    $scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $url        = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $is_bot     = ip_location_is_bot($user_agent, $url, $ip, $prefixeTable) ? 1 : 0;

    // 8. Décision
    if ($geo_ok) {
        if (in_array(strtoupper($geo['country_code']), $allowed)) {
            return; // Autorisé — on laisse action.php suivre son cours (son propre pwg_log alimente history)
        }
        // Pays hors allowlist → blocage
        ip_location_log_download_attempt($ip, $geo, $url, $user_agent, $is_bot, 1, $prefixeTable);
        ip_location_download_denied_response();
    }

    // Géo indisponible : appliquer download_geo_fail_mode
    if ($plugin_conf['download_geo_fail_mode'] === 'closed') {
        ip_location_log_download_attempt($ip, $geo, $url, $user_agent, $is_bot, 1, $prefixeTable);
        ip_location_download_denied_response();
    }

    // fail-open (défaut) : laisser passer, mais journaliser pour mesurer la fréquence réelle
    ip_location_log_download_attempt($ip, $geo, $url, $user_agent, $is_bot, 0, $prefixeTable);
}

/**
 * Page 403 minimale (HTML autonome, sans ressource externe) pour un téléchargement
 * refusé par ip_location_download_guard(). Le statut HTTP reste 403 (pas de simulation
 * de succès) ; seul le corps de réponse est personnalisé. Termine toujours par exit.
 */
function ip_location_download_denied_response()
{
    global $user;

    // Pas de rechargement de langue ici : load_plugins() (common.inc.php) s'exécute avant
    // trigger_notify('init'), donc la langue du plugin (chargée en tête de main.inc.php) est déjà en place.
    $msg = l10n('ipl_download_denied');
    $lang_code = isset($user['language']) ? substr($user['language'], 0, 2) : 'en';

    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');

    echo '<!DOCTYPE html><html lang="' . htmlspecialchars($lang_code) . '"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . htmlspecialchars($msg) . '</title></head>'
       . '<body style="font-family:sans-serif;text-align:center;padding:3em;color:#333">'
       . '<h1 style="font-size:1.4em">' . htmlspecialchars($msg) . '</h1>'
       . '</body></html>';
    exit;
}

/**
 * Journalise une tentative de téléchargement (log_type='download') dans ip_location_log,
 * séparément du journal des visites normales.
 */
function ip_location_log_download_attempt($ip, $geo, $url, $user_agent, $is_bot, $is_blocked, $prefixeTable)
{
    pwg_query('
INSERT INTO ' . $prefixeTable . 'ip_location_log
  (ip, country, country_code, city, url, user_agent, is_bot, is_blocked, log_type, visit_date)
  VALUES (
    \'' . $ip . '\',
    \'' . pwg_db_real_escape_string($geo['country']) . '\',
    \'' . pwg_db_real_escape_string($geo['country_code']) . '\',
    \'' . pwg_db_real_escape_string($geo['city']) . '\',
    \'' . pwg_db_real_escape_string($url) . '\',
    \'' . pwg_db_real_escape_string($user_agent) . '\',
    ' . $is_bot . ',
    ' . $is_blocked . ',
    \'download\',
    NOW()
  );');
}

/**
 * Enregistre la visite d'un guest avec géolocalisation.
 *
 * @param string|null $override_url  URL à loguer (null = URL de la requête courante).
 *                                   Utilisé par ajax_log.php pour les vues PhotoSwipe.
 * @param bool        $do_block      Si true, envoie un 403 et exit en cas de blocage.
 *                                   Mettre à false depuis ajax_log.php.
 */
function ip_location_log_visit($override_url = null, $do_block = true)
{
    global $user, $prefixeTable;

    // Uniquement les guests (id = 2)
    if (!isset($user['id']) || $user['id'] != 2) {
        return;
    }

    // Récupération de l'IP
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($parts[0]);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $ip_raw = $ip; // IP non échappée pour les comparaisons

    // Liste blanche d'IPs — toujours autorisées
    $plugin_conf  = ip_location_get_conf();
    $whitelist_raw = $plugin_conf['whitelist'];
    $whitelist = array_filter(array_map('trim', explode("\n", $whitelist_raw)));
    if (in_array($ip_raw, $whitelist)) {
        return;
    }

    $ip = pwg_db_real_escape_string($ip);

    // Blocklist manuelle — blocage immédiat par IP individuelle
    $r = pwg_query('SELECT 1 FROM ' . $prefixeTable . 'ip_location_blocklist WHERE ip = \'' . $ip . '\'');
    if (pwg_db_num_rows($r) > 0) {
        header('HTTP/1.0 403 Forbidden');
        exit;
    }

    // Résolution géo (cache ou providers en cascade)
    $geo = ip_location_resolve_geo($ip, $prefixeTable);

    // Construction de l'URL visitée
    if ($override_url !== null && is_string($override_url)) {
        $url = $override_url;
    } else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $url    = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Détection bot par user-agent uniquement (zéro SQL) — la détection par
    // co-visitation (scan de la table) est différée à ip_location_classify_recent()
    $is_bot = ip_location_is_bot_ua($user_agent) ? 1 : 0;

    // Déterminer si la visite sera bloquée (avant l'INSERT pour l'enregistrer)
    $is_blocked = 0;

    // Blocage par pays
    if ($plugin_conf['blocking_enabled'] === '1') {
        $blocked = array_filter(array_map('trim', explode(',', strtoupper($plugin_conf['blocked_countries']))));
        if (!empty($blocked) && in_array(strtoupper($geo['country_code']), $blocked)) {
            $is_blocked = 1;
        }
    }

    // Blocage par mot-clé dans l'URL
    if (!$is_blocked && !empty($plugin_conf['blocked_url_keywords'])) {
        $url_keywords = array_filter(array_map('trim', explode("\n", $plugin_conf['blocked_url_keywords'])));
        $url_lower = strtolower($url);
        foreach ($url_keywords as $kw) {
            if (strpos($url_lower, strtolower($kw)) !== false) {
                $is_blocked = 1;
                break;
            }
        }
    }

    // Insertion dans le log
    $query = '
INSERT INTO ' . $prefixeTable . 'ip_location_log
  (ip, country, country_code, city, url, user_agent, is_bot, is_blocked, visit_date)
  VALUES (
    \'' . $ip . '\',
    \'' . pwg_db_real_escape_string($geo['country']) . '\',
    \'' . pwg_db_real_escape_string($geo['country_code']) . '\',
    \'' . pwg_db_real_escape_string($geo['city']) . '\',
    \'' . pwg_db_real_escape_string($url) . '\',
    \'' . pwg_db_real_escape_string($user_agent) . '\',
    ' . $is_bot . ',
    ' . $is_blocked . ',
    NOW()
  );';
    pwg_query($query);

    // Marquage rétroactif par co-visitation : différé en lot à ip_location_classify_recent()
    // (appelée depuis admin.php), pour éviter un scan de la table à chaque visite.

    // Vidage automatique — échantillonné (1 visite sur 50) : évite un COUNT(*) sur
    // ~98% des affichages. Le léger dépassement transitoire du seuil est sans conséquence.
    $max_records = (int)$plugin_conf['max_records'];
    if ($max_records > 0 && mt_rand(1, 50) === 1) {
        $r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log');
        list($count) = pwg_db_fetch_row($r);
        if ($count > $max_records) {
            $to_delete = $count - $max_records;
            pwg_query('
DELETE FROM ' . $prefixeTable . 'ip_location_log
  ORDER BY visit_date ASC
  LIMIT ' . $to_delete);
        }
    }

    if ($is_blocked && $do_block) {
        header('HTTP/1.0 403 Forbidden');
        exit;
    }
}

function ip_location_write_htaccess($htaccess_enabled = null)
{
    global $prefixeTable;

    $htaccess_path = PHPWG_ROOT_PATH . '.htaccess';

    if (file_exists($htaccess_path)) {
        if (!is_writable($htaccess_path)) return false;
        $content = file_get_contents($htaccess_path);
    } else {
        if (!is_writable(PHPWG_ROOT_PATH)) return 'missing';
        $content = '';
    }

    // Supprimer la section existante
    $content = preg_replace('/\n?# BEGIN ip_location\b.*?# END ip_location[^\n]*/s', '', $content);
    $content = rtrim($content);

    if ($htaccess_enabled === null) {
        $htaccess_enabled = ip_location_get_conf()['htaccess_enabled'];
    }
    if ($htaccess_enabled === '1') {
        $result = pwg_query('SELECT ip FROM ' . $prefixeTable . 'ip_location_blocklist ORDER BY blocked_at ASC');
        $ips = [];
        while ($row = pwg_db_fetch_row($result)) {
            $ips[] = $row[0];
        }
        if (!empty($ips)) {
            $section = "\n\n# BEGIN ip_location\n<RequireAll>\n    Require all granted\n";
            foreach ($ips as $ip) {
                $section .= '    Require not ip ' . $ip . "\n";
            }
            $section .= "</RequireAll>\n# END ip_location";
            $content .= $section;
        }
    }

    file_put_contents($htaccess_path, $content);
    return true;
}

/**
 * Détection bot par user-agent uniquement — zéro SQL, utilisée sur le chemin
 * chaud de ip_location_log_visit() (une exécution par affichage de photo).
 */
function ip_location_is_bot_ua($user_agent)
{
    if (empty($user_agent)) {
        return true;
    }

    $keywords = ['bot', 'crawler', 'spider', 'scraper', 'slurp', 'curl', 'wget',
                 'python', 'go-http', 'java/', 'libwww', 'scrapy', 'zgrab', 'masscan'];
    $ua_lower = strtolower($user_agent);
    foreach ($keywords as $kw) {
        if (strpos($ua_lower, $kw) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Rejoue en lot, sur les 7 derniers jours, la détection de bots par co-visitation
 * (>= 2 IP distinctes sur la même URL dans une fenêtre de ~10 s). Approximation par
 * buckets fixes de 10 s (au lieu d'une fenêtre glissante) — sans impact puisque is_bot
 * est une info de stats/filtre, ne pilote aucun blocage. Idempotent (WHERE is_bot = 0).
 * Appelée depuis admin.php uniquement (jamais depuis un chemin public).
 */
function ip_location_classify_recent()
{
    global $prefixeTable;

    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log t
JOIN (
    SELECT url, FLOOR(UNIX_TIMESTAMP(visit_date)/10) AS bucket
      FROM ' . $prefixeTable . 'ip_location_log
     WHERE visit_date >= NOW() - INTERVAL 7 DAY
     GROUP BY url, bucket
    HAVING COUNT(DISTINCT ip) >= 2
) g
  ON t.url = g.url
 AND FLOOR(UNIX_TIMESTAMP(t.visit_date)/10) = g.bucket
SET t.is_bot = 1
WHERE t.is_bot = 0
  AND t.visit_date >= NOW() - INTERVAL 7 DAY');
}

/**
 * Détection bot par UA + co-visitation (scan SQL). Conservée uniquement pour
 * ip_location_download_guard() : les tentatives de téléchargement sont bien plus rares
 * qu'un affichage de photo, donc ce scan n'a pas le même impact perf que dans log_visit()
 * (qui utilise désormais ip_location_is_bot_ua() sur son chemin chaud, cf. v2.3).
 */
function ip_location_is_bot($user_agent, $url, $ip, $prefixeTable)
{
    // UA vide
    if (empty($user_agent)) {
        return true;
    }

    // Mots-clés connus de bots
    $keywords = ['bot', 'crawler', 'spider', 'scraper', 'slurp', 'curl', 'wget',
                 'python', 'go-http', 'java/', 'libwww', 'scrapy', 'zgrab', 'masscan'];
    $ua_lower = strtolower($user_agent);
    foreach ($keywords as $kw) {
        if (strpos($ua_lower, $kw) !== false) {
            return true;
        }
    }

    // Visite en doublon : même URL depuis une IP différente dans les 10 dernières secondes
    $result = pwg_query('
SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log
  WHERE url = \'' . pwg_db_real_escape_string($url) . '\'
    AND ip != \'' . pwg_db_real_escape_string($ip) . '\'
    AND visit_date >= NOW() - INTERVAL 10 SECOND');
    list($count) = pwg_db_fetch_row($result);
    if ($count > 0) {
        return true;
    }

    return false;
}

