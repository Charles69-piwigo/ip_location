<?php
/*
Plugin Name: ip_location
Version: auto
Description: Log des visites des guests avec géolocalisation IP + traitement htaccess
Plugin URI: https://piwigo.org/ext/extension_view.php?eid=1068
Author: Charles69 
Has Settings: webmaster
*/

// Versions
/*
    version 2.6.8 - 23/09/2026
        le Journal reflète l'état actuel (revient sur le choix "historique figé" de la
        2.6.1) : "Bloqués" = refus réels + tous les accès des IP et robots actuellement
        bloqués, même servis avant le blocage — une ligne "Bots non bloqués" ne peut plus
        porter "Bloquée depuis le …". Seuls les leviers allumés comptent (liste manuelle
        si blocage par IP activé, liste auto non expirée si blocage auto activé, robots
        "Bloqué" si bloc Robots activé) : tous coupés, "Bloqués" ne contient que les refus
        réels. Les règles portant sur la requête (pays, mot-clé) ne reclassent pas
        l'historique. Nouvelle fonction commune ip_location_currently_blocked_sql()
        (Journal, Statistiques, widget Visiteurs), qui remplace
        ip_location_in_blocklist_sql() ; badge "Robot bloqué · nom" dans le Journal

    version 2.6.7 - 23/09/2026
        Journal : le badge "EN LISTE" devient "Bloquée depuis le jj/mm/aaaa" (date de
        l'entrée de blocage, ou de la plage /16 qui couvre l'IP), infobulle "Accès servi
        normalement : il date d'avant le blocage de cette IP" — la chronologie se lit
        directement. Texte de l'Aide sur la catégorie Bloqués mis à jour

    version 2.6.6 - 23/09/2026
        onglet Configuration en deux sous-onglets, Réglages (blocs) et Journal des accès
        (paramètre sub=journal ; tout filtre du Journal ouvre directement ce sous-onglet).
        Journal : compteurs cliquables servant de filtre (Tous, Normal, Bots non bloqués,
        dont robots autorisés — nouveau filtre "robots" —, Bloqués) calculés avec les
        mêmes filtres pays/dates/IP ; filtres regroupés ; répartition par pays repliable
        dont les pastilles filtrent ; badges sous la date (robot autorisé "Googlebot ✓",
        BOT, faux robot, Refusé · motif, EN LISTE, Exempté) ; menu "Actions" par ligne
        (filtrer sur l'IP, bloquer l'IP ou sa plage /16, débloquer) qui revient au
        Journal avec les mêmes filtres et la même page (return_qs) ; pagination fenêtrée
        (1 2 3 … 197 au lieu de tous les numéros). Styles des blocs déplacés dans
        admin.tpl (partagés par les deux sous-onglets) ; les données des blocs Réglages ne
        sont plus calculées quand le Journal est affiché

    version 2.6.5 - 23/09/2026
        robots d'IA (GPTBot, ClaudeBot, CCBot, Bytespider, PerplexityBot, Amazonbot)
        bloqués par défaut — GPTBot observé à ~50 requêtes/minute pendant des heures sur
        un site réel. S'applique aux sites dont la liste n'a jamais été enregistrée ; un
        site qui avait personnalisé l'ancienne liste blanche garde ses robots autorisés,
        complétés des robots d'IA bloqués
        refus répétés (même IP, même motif : robot bloqué, liste d'IP, blocage auto)
        journalisés au plus une fois toutes les ip_location_refusal_log_minutes (10)
        minutes : la requête reste refusée à chaque fois, mais un robot qui insiste ne
        remplit plus le journal jusqu'à évincer tout le reste via max_records

    version 2.6.4 - 23/09/2026
        onglet Configuration : l'étiquette d'état de chaque bloc (Actif / Inactif,
        Affiché / Masqué) suit l'interrupteur en direct, sans attendre l'enregistrement ;
        le bandeau "Mode actuel" (Observateur / Observation + blocage, pastilles des
        leviers) est recalculé en direct à chaque bascule — y compris le passage d'un
        robot en "Bloqué" — avec la mention "modifications non enregistrées" tant que
        l'état affiché diffère de l'état enregistré. La pastille Robots suit son
        interrupteur comme les autres ; elle ne compte parmi les leviers de blocage que
        si au moins un robot est bloqué (infobulle sinon)

    version 2.6.3 - 23/09/2026
        onglet Configuration réécrit en blocs (maquette validée) : bandeau "Mode actuel"
        (Observateur / Observation + blocage, leviers actifs) ; groupe Observation
        (widget Visiteurs, conservation du journal avec suppression des anciens accès et
        vidage du cache géo, liste blanche) ; groupe Leviers de blocage (Robots
        d'indexation éditables, IP manuelles, pays, mots-clés, blocage auto avec sa
        propre liste d'IP, filtre des téléchargements). Un formulaire par bloc
        (nouvelles actions save_ip_block, save_whitelist, save_retention, save_robots ;
        max_records sorti de save_config), pastilles pour les listes, refus sur 7 jours
        par entrée / pays / mot-clé, impact du seuil en direct, avertissement Googlebot
        quand les États-Unis sont bloqués. Libellés "Bloquer / Débloquer" au lieu de
        "Ajouter au / Retirer du .htaccess"

    version 2.6.2 - 23/09/2026
        robots d'indexation (moteur ; l'écran d'édition arrive avec la refonte de l'onglet
        Configuration) : liste par défaut de 20 robots (moteurs de recherche, aperçus de
        partage, robots d'IA), tous autorisés, stockée dans conf['ip_location']['robots'],
        interrupteur robots_enabled.
        - autorisé et authentique : passe tous les leviers (blocklist, pays, mot-clé,
          score forcé à 0, jamais auto-bloqué) ; bloqué : refusé dès l'init, journalisé
          avec le motif "robot"
        - vérification DNS inverse + directe pour Google, Bing, Apple, Yandex, Baidu,
          résultat gardé ip_location_robot_check_days (30) jours dans la nouvelle table
          ip_location_robot_check ; faux robot = visiteur ordinaire + is_bot + score
          ip_location_score_bot_spoof (50)
        - remplace l'ancienne liste blanche $conf['ip_location_bot_allowlist'] ; si un
          site l'avait personnalisée, elle sert de point de départ à la nouvelle liste

    version 2.6.1 - 23/09/2026
        principe "observateur" : chaque levier ne bloque — et n'alimente "Bloqués" — que
        s'il est activé ; tous coupés, le plugin ne fait qu'observer :
        - blocage par IP manuel (interrupteur htaccess_enabled) : pilote désormais aussi
          le contrôle PHP (avant : les IP manuelles restaient bloquées case décochée)
        - blocage auto coupé : ses IP ne bloquent plus (restent listées jusqu'à expiration)
        - nouvel interrupteur keyword_block_enabled (repris à '1' si des mots existaient)
        "Bloqués" = accès réellement refusés : le plugin journalise aussi les refus de la
        blocklist, et chaque refus porte son motif (nouvelle colonne block_reason :
        country / keyword / ip / auto / download). Abandon du marquage rétroactif des
        anciennes lignes d'une IP mise en liste après coup (badge "EN LISTE" à la place)
        blocage auto "à partir du moment" : bot_block_enabled_at mémorisé à l'activation,
        seule l'activité suspecte postérieure peut déclencher un blocage
        colonne block_reason créée aussi au 1er chargement de l'admin (filet de sécurité
        pour une copie de fichiers sans passer par la mise à jour Piwigo)

    version 2.6 - 23/09/2026
        intégration des versions 2.5.1 à 2.5.5
        pour diffusion PEM

    version 2.5.5 - 23/09/2026
        suite à l'analyse du journal et de la blocklist d'un site testeur (10 000 accès,
        10 475 IP auto-bloquées) :
        fix faux positif "rafale multi-URL" sur une navigation humaine rapide (11 photos
        en 27 s en cliquant "suivant") : seuil relevé de 10 à 20 URL distinctes en ~30 s,
        désormais réglable (ip_location_multi_url_threshold) ; les scrapers visés en v2.5
        en faisaient 47 à 88
        requêtes de pré-lecture du navigateur (<link rel="prefetch"> de la photo suivante
        dans le thème, en-têtes Sec-Purpose/Purpose/X-Moz) journalisées avec
        log_type='prefetch' et ignorées par toutes les règles comportementales et par
        les visites qualifiées du widget (une photo pré-chargée mais jamais vue comptait
        comme une page vue)
        règle "UA figé partagé" : ne pose plus is_bot et poids 50 -> 30 — l'UA recyclé
        par le botnet était celui de Chrome 151/Windows, donc tout vrai visiteur avec
        ce navigateur était marqué bot (et auto-bloquable)
        blocage auto appliqué en PHP seulement, jamais écrit dans le .htaccess
        (réservé aux blocages manuels) : nouveau garde ip_location_blocklist_guard()
        sur init, qui couvre tous les scripts PHP (pas seulement index/picture) et
        les plages /16 manuelles — celles-ci n'étaient appliquées que par le
        .htaccess, inopérant sur le site testeur. Le blocage auto ne dépend plus de
        htaccess_enabled (fonctionne aussi sous nginx). .htaccess réécrit une fois
        au 1er chargement de l'admin pour en retirer les anciennes entrées auto
        blocage auto limité aux IP suspectes récemment (ip_location_auto_block_recent_hours,
        défaut 24) : la 1ère activation posait d'un coup toute la fenêtre de 7 jours
        jamais de blocage auto d'une IP privée/réservée (poste du réseau local) : une fois
        bloquée, elle ne pouvait même plus afficher la page de connexion depuis le LAN
        fix "Bloqués" (Journal, Statistiques, widget Visiteurs) : les lignes d'une IP
        couverte par une plage /16 manuelle apparaissaient en "Bots non bloqués"/"Normal"
        (test sur l'IP exacte seulement) — ip_location_in_blocklist_sql() commun, et
        mention "Plage /16 bloquée" dans le Journal à la place des boutons
        supprimé le mode de blocage auto "Détection bot standard (is_bot)" : il revenait
        à un seuil de score ~10, bloquait sur un signal isolé peu fiable (co-visitation)
        et is_bot n'est jamais remis à 0 après correction d'une règle. Blocage auto
        toujours sur le score ; migration au 1er chargement de l'admin : les sites en
        mode is_bot passent au score avec le seuil déjà enregistré, et les entrées auto
        qui ne l'atteignent pas sont libérées aussitôt

    version 2.5.4 - 23/09/2026
        fix "Détail des visites comptabilisées" (Configuration) toujours calculé sur la
        période par défaut (semaine) au lieu de la période réellement enregistrée :
        admin.php lisait $plugin_conf['visitors_period'] avant que $plugin_conf ne soit
        assigné plus loin dans le fichier (variable non définie à cet endroit). Lit
        désormais la config via ip_location_get_conf() directement à l'endroit voulu

    version 2.5.3 - 23/09/2026
        fix blocage manuel d'une plage /16 sans effet sur les IP déjà auto-bloquées du
        même sous-réseau : elles restaient affichées séparément dans "IP bloquées
        automatiquement" et pouvaient même être réinsérées par le passage de
        classification suivant. Ajouter une plage manuelle retire désormais aussitôt
        les entrées 'auto' qu'elle couvre et les exclut durablement du blocage
        automatique tant que la plage reste active (ip_location_manual_range_prefix(),
        ip_location_get_bot_candidates())

    version 2.5.2 - 23/09/2026
        ajouté section repliable "Détail des visites comptabilisées" à la fin du
        bloc Statistiques visiteurs (Configuration) : liste ligne par ligne
        (date/IP/pays/URL) des accès qui composent le chiffre du widget public
        "Visiteurs" sur la période configurée, pour pouvoir l'auditer ; critères
        de qualification extraits dans ip_location_qualifying_visit_where()
        (main.inc.php), partagée avec ajax_visitors.php pour ne jamais diverger
        scindé le tableau de blocage .htaccess (Configuration) en deux : IP
        bloquées manuellement toujours affichées, IP auto-bloquées dans une
        section dépliable/repliable séparée — pour rester lisible sur les sites
        très ciblés où l'auto-blocage produit beaucoup d'entrées

    version 2.5.1 - 22/09/2026
        fix compteur du widget public "Visiteurs" : une IP blocklistée après coup
        (manuellement ou auto-bloquée par le score bot) continuait à alimenter le
        décompte de visites tant que ses anciennes lignes restaient dans la fenêtre
        de période — incohérent avec la définition "Normal"/"Humains" du Journal
        depuis la v2.5, qui exclut déjà l'appartenance à la blocklist

    version 2.5 - 22/09/2026
        ajouté 4ème levier de blocage : score de suspicion bot (bot_score),
        calculé en différé (ip_location_classify_recent()) à partir de plusieurs
        signaux pondérés (UA vide/mot-clé, co-visitation, rafale mono-IP, absence
        de trace JS du diaporama, récidive optionnelle) ; liste blanche de bots
        légitimes (Googlebot, Bingbot, Slackbot...) toujours exemptée
        ajouté blocage automatique optionnel (mode score à seuil réglable ou
        is_bot direct), toujours temporaire (TTL configurable, 14j par défaut),
        IP exacte uniquement (jamais de plage /16), avec garde-fous htaccess
        (nginx, htaccess_enabled requis) et revérification whitelist
        ajouté colonnes bot_score (log) et origin/expires_at (blocklist) ;
        interrupteur + curseur en Configuration ; colonne Score dans le Journal ;
        badges Auto/Manuel + expiration dans la liste de blocage
        ajouté réglages fins ($conf['ip_location_xxx']) surchargeables sans
        redéploiement via local/config/config.inc.php (LocalFilesEditor)
        ajouté déclenchement autonome de la classification/blocage auto via le
        trafic public (au plus 1x/ip_location_classify_interval_hours, défaut
        4h), pour fonctionner même sans visite admin régulière
        ajouté règle de détection "rafale mono-IP" (>=3 accès même IP/même URL
        en 10s), en batch et en scan live (guard téléchargement)
        catégories Normal/Bots non bloqués/Bloqués rendues mutuellement
        exclusives (Tous = somme des 3, sans reste caché) ; "Bots" renommé
        "Bots non bloqués" partout ; compteur "Humains" du bandeau corrigé
        fix "Bloqués" (Journal, Statistiques, badge) ignorait l'appartenance
        à la blocklist .htaccess : une IP bloquée après coup restait affichée
        "Normal"/"Bots non bloqués" sur ses lignes déjà journalisées
        fix affichage badge "Auto" + date d'expiration (même ligne, sans l'heure)
        ajouté réconciliation des blocages auto à l'enregistrement du formulaire
        de blocage bot : relever le seuil (ou changer de mode) retire aussitôt de
        la blocklist les IP 'auto' qui ne correspondent plus, sans attendre le TTL
        (les blocages manuels ne sont jamais touchés)
        fix "Retirer du .htaccess" sans effet en blocage auto : l'IP était
        réinsérée aussitôt (bot_score/is_bot sont recalculés depuis les mêmes
        vieilles visites à chaque classification). Le retrait marque désormais
        l'IP origin='exempt' dans ip_location_blocklist : ses visites antérieures
        au retrait ne comptent plus, seule une nouvelle activité suspecte peut la
        refaire bloquer ; 'exempt' exclu du .htaccess, du 403 PHP de
        ip_location_log_visit(), du filtre "Bloqués" et des stats dynamiques ;
        badge "Exempté" à côté du score dans le Journal
        ajouté règle de détection "rafale multi-URL mono-IP" (>=10 URL distinctes
        même IP en ~30s), déduite de l'analyse du journal réel (score + is_bot)
        ajouté détection des navigateurs/OS manifestement obsolètes (Firefox < 100,
        Chrome < 109 — 109 épargné, dernière version pour Windows 7/8 —, iOS < 14),
        en is_bot immédiat et en signal de score
        ajouté règle générique "UA qui annonce un crawler" (URL "://", contact "@"
        ou "compatible;" hors MSIE/Trident), comptée avec le signal mot-clé ;
        couvre p.ex. GoogleOther. Les bots d'IA (GPTBot, GoogleOther...) ne sont
        volontairement pas dans l'allowlist par défaut
        ajouté règle "UA figé partagé par de nombreuses IP" (>=20 accès sur la
        fenêtre, >=90% d'IP distinctes), en is_bot et en signal de score
        (ip_location_score_shared_ua, défaut 50) — calcul batch uniquement,
        déduite de l'analyse d'un 2ème journal réel (pool de proxies résidentiels,
        7000+ accès sur 6400+ IP distinctes invisibles aux règles précédentes)
        modifié signal "absence de trace JS" : appliqué seulement si le logger JS
        fonctionne sur le site (au moins une ligne log_type='js' en base)
        ajouté mots-clés de scanners auto-identifiés (censys, palo alto, leakix,
        cms-checker, rootevidence, netcraft, tlm-audit-scanner,
        internetmeasurement, okhttp) ; liste unique ip_location_bot_ua_keywords()
        au lieu de 3 copies
        ajouté réglages fins ip_location_score_multi_url_burst,
        ip_location_score_outdated_browser et ip_location_classify_window_days
        (fenêtre glissante de classification, 7 jours par défaut)
        modifié ip_location_bot_allowlist : tableau PHP au lieu d'une chaîne
        (la chaîne "un par ligne" reste acceptée)
        modifié bloc "Statistiques par pays" repliable, replié par défaut
        ajouté PARAMETRES.md (liste des $conf surchargeables), et sa version
        anglaise PARAMETERS.md
        fix migrations de base jamais rejouées lors d'une mise à jour depuis
        l'admin Piwigo (PEM ou zip) tant que le plugin restait actif : le cœur
        Piwigo n'appelle alors QUE update(), jamais activate(), et l'ancien
        format procédural (maintain.inc.php) ne le permettait pas (update() était
        un no-op côté Piwigo). Remplacé par maintain.class.php (classe OO
        ip_location_maintain), dont update() rejoue activate() — jusque-là il
        fallait désactiver/réactiver le plugin à la main pour forcer la migration

    version 2.4 - 16/09/2026
        ajouté onglet Statistiques dynamiques : graphique multi-courbes de
        l'évolution des accès dans le temps, combinable par type/pays/mots-clés
        bloqués/IP, avec axes gauche/droite indépendants et export CSV/PNG/JSON
        ajouté préréglages de séries (3 fixes + 4 personnalisables enregistrés
        en config) et granularité adaptative (jour ≤31j, semaine ≤1an, mois
        au-delà)
        ajouté index idx_bot_blocked_date sur ip_location_log
        vendorisé Chart.js en local (template/js/chart.umd.min.js)
        ajouté période "Tous les logs" (bornée à la date du log le plus ancien)
        ajouté mémorisation de la dernière période utilisée (et des dates en
        cas de période personnalisée)
        ajouté couleur de fond du graphique personnalisable (sélecteur +
        raccourcis) et mémorisée, via un plugin Chart.js custom
        ajouté affichage du nom du préréglage actif sur le graphique
        fix quadrillage du graphique invisible sur fond coloré (couleur de
        grille trop proche des fonds clairs)
        fix case "Filtrer les IP/pays/mots-clés" sans effet visuel (règle CSS
        [hidden] manquante sur les lignes de la liste)
        fix nombre d'accès affiché ne tenant pas compte des courbes masquées
        via la légende
        fix RangeError "Maximum call stack size exceeded" dans Chart.js
        (boucle de redimensionnement) : resizeDelay + resize différé

    version 2.3a - 19/08/2026
        fix utilisation d'un hook incorrect

    version 2.3 - 27/07/2026 (ex 2.5)
        fix sécurité : le blocage pays des téléchargements passe fail-closed par
        défaut (download_geo_fail_mode = 'closed') — un échec de géolocalisation
        bloque désormais le téléchargement au lieu de le laisser passer
        le guard download ignore la cache géo négative (2h) introduite en v2.4 :
        un 'Unknown' figé ne fonde plus jamais une décision de blocage, une
        nouvelle tentative re-résout toujours en direct (resolve_geo() accepte
        un paramètre $use_negative_cache, false pour le guard, true ailleurs)
        aide : mise à jour de la section Filtre pays sur les téléchargements

    version 2.2b - 27/07/2026 (ex 2.4)
        fix perf géo : court-circuit immédiat des IP privées/réservées (réseau
        local) dans resolve_geo(), plus jamais envoyées aux providers
        cache géo à double TTL : 30 jours pour une résolution réussie, 2h pour
        un échec 'Unknown' (évite de re-solliciter les providers en boucle)
        réduit CURLOPT_CONNECTTIMEOUT de 5s à 2s (limite le pire cas provider injoignable)
        aide : note sur l'ajout du réseau local à la whitelist

    version 2.2a - 27/07/2026 (ex 2.3)
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
define('IP_LOCATION_STATS_PRESET_SLOTS', 4);

// Score de suspicion bot (4ème levier de blocage, optionnel) — réglages fins
// volontairement hors de l'UI Configuration tant qu'ils sont en phase d'évaluation.
// Surchargeables sans toucher au code via local/config/config.inc.php (édité par le
// plugin LocalFilesEditor), chargé par Piwigo avant l'exécution des plugins.
global $conf;
$ip_location_score_defaults = [
    'ip_location_score_ua_empty'      => 10,
    'ip_location_score_ua_keyword'    => 15,
    'ip_location_score_covisit'       => 40,
    'ip_location_score_burst'         => 50,
    'ip_location_score_multi_url_burst' => 50,
    'ip_location_multi_url_threshold' => 20, // URL distinctes par IP en ~30 s (10 avant v2.5.5)
    'ip_location_score_shared_ua'     => 30,  // < seuils usuels : doit être confirmé par un 2e signal
    'ip_location_score_outdated_browser' => 20,
    'ip_location_score_no_js'         => 20,
    'ip_location_recurrence'          => 0,  // 0 = désactivé (même convention que max_records)
    'ip_location_auto_block_ttl_days' => 14,
    'ip_location_auto_block_recent_hours' => 24, // seules les IP actives depuis N h sont auto-bloquées
    'ip_location_score_bot_spoof'     => 50, // UA d'un moteur connu depuis une IP qui ne lui appartient pas (v2.6.2)
    'ip_location_robot_check_days'    => 30, // durée de validité d'une vérification DNS de robot
    'ip_location_refusal_log_minutes' => 10, // un refus répété (même IP, même motif) n'est journalisé qu'une fois par période ; 0 = tous
    'ip_location_classify_interval_hours' => 4,
    'ip_location_classify_window_days' => 7,
];
// Ancienne liste blanche de robots (réglage fin jusqu'en v2.6.1) : remplacée par le bloc
// "Robots d'indexation" (conf['ip_location']['robots']). Si un site l'avait personnalisée
// dans local/config/config.inc.php, elle sert une seule fois de point de départ à la
// nouvelle liste (cf. ip_location_get_robots()).
$GLOBALS['ip_location_legacy_bot_allowlist'] = isset($conf['ip_location_bot_allowlist']) ? $conf['ip_location_bot_allowlist'] : null;
foreach ($ip_location_score_defaults as $ip_location_score_key => $ip_location_score_default) {
    if (!isset($conf[$ip_location_score_key])) {
        $conf[$ip_location_score_key] = $ip_location_score_default;
    }
}
unset($ip_location_score_defaults, $ip_location_score_key, $ip_location_score_default);

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
        'download_geo_fail_mode'     => 'closed',
        'stats_presets'              => array_fill(0, IP_LOCATION_STATS_PRESET_SLOTS, null),
        'last_stats_period'          => 'month',
        'last_stats_date_from'       => '',
        'last_stats_date_to'         => '',
        'chart_bg_color'             => '#ffffff',
        'bot_block_enabled'          => '0',
        'bot_block_enabled_at'       => '',  // activation du blocage auto (v2.6.1), cf. ip_location_get_bot_candidates()
        'bot_block_score_threshold'  => 70,
        'keyword_block_enabled'      => '0',
        'robots_enabled'             => '1',   // bloc "Robots d'indexation" (v2.6.2)
        'robots'                     => null,  // null = liste par défaut, cf. ip_location_get_robots()
        'last_classify_at'           => '',
    ];

    if (!empty($conf['ip_location'])) {
        $stored = @unserialize($conf['ip_location']);
        if (is_array($stored)) {
            $cache = array_merge($default, $stored);
            // Interrupteur du blocage par mot-clé (v2.6.1) : avant, une liste non vide
            // suffisait à l'activer — on reprend cet état tant que la clé n'a jamais été
            // enregistrée, pour que la mise à jour ne change rien au comportement.
            if (!array_key_exists('keyword_block_enabled', $stored)) {
                $cache['keyword_block_enabled'] = trim($cache['blocked_url_keywords']) !== '' ? '1' : '0';
            }
            return $cache;
        }
    }
    $cache = $default;
    return $cache;
}

/**
 * Périodes proposées pour le widget public "Visiteurs" (nav + panel flottant) et pour
 * la section d'audit "Détail des visites comptabilisées" (admin.php) — source unique
 * pour que les deux restent synchronisés (même piège que ip_location_bot_ua_keywords()).
 */
function ip_location_visitors_periods()
{
    return [
        'week'      => ['interval' => '7 DAY',  'days' => 7],
        'fortnight' => ['interval' => '15 DAY', 'days' => 15],
        'month'     => ['interval' => '30 DAY', 'days' => 30],
        'quarter'   => ['interval' => '90 DAY', 'days' => 90],
    ];
}

/**
 * Fragment SQL (parenthésé) vrai si la ligne de journal vient d'une IP ou d'un robot
 * ACTUELLEMENT bloqué (v2.6.8 : le Journal reflète l'état actuel — une ligne servie avant
 * le blocage passe dans "Bloqués"). Seuls les leviers allumés comptent :
 *   - liste manuelle (IP exacte ou plage "A.B.0.0/16") si le blocage par IP est activé ;
 *   - liste automatique (non expirée) si le blocage automatique est activé ;
 *   - User-Agent d'un robot marqué "Bloqué" si le bloc Robots est activé.
 * Les règles portant sur la requête (pays, mot-clé) ne reclassent pas l'historique : seul
 * un refus réel (is_blocked=1) les fait apparaître. '0' si aucun levier concerné n'est
 * allumé (mode observateur). Source unique pour le Journal, les séries des Statistiques et
 * les visites qualifiées du widget.
 * IPv6 : SUBSTRING_INDEX ne trouve pas de '.', la clé construite ne correspond jamais
 * à une plage — seule la correspondance exacte s'applique (pas de plage IPv6).
 */
function ip_location_currently_blocked_sql($prefixeTable, $ip_col = 'ip', $ua_col = 'user_agent')
{
    $plugin_conf = ip_location_get_conf();
    $parts = [];
    if ($plugin_conf['htaccess_enabled'] === '1') {
        $parts[] = "{$ip_col} IN (SELECT ip FROM {$prefixeTable}ip_location_blocklist WHERE origin = 'manuel')";
        $parts[] = "CONCAT(SUBSTRING_INDEX({$ip_col}, '.', 2), '.0.0/16') IN"
            . " (SELECT ip FROM {$prefixeTable}ip_location_blocklist WHERE origin = 'manuel')";
    }
    if ($plugin_conf['bot_block_enabled'] === '1') {
        $parts[] = "{$ip_col} IN (SELECT ip FROM {$prefixeTable}ip_location_blocklist"
            . " WHERE origin = 'auto' AND (expires_at IS NULL OR expires_at > NOW()))";
    }
    if ($plugin_conf['robots_enabled'] === '1') {
        foreach (ip_location_get_robots() as $robot) {
            if (($robot['status'] ?? 'allow') === 'block' && $robot['ua'] !== '') {
                $parts[] = "{$ua_col} LIKE '%" . pwg_db_real_escape_string($robot['ua']) . "%'";
            }
        }
    }
    return empty($parts) ? '0' : '(' . implode(' OR ', $parts) . ')';
}

/**
 * Fragment SQL définissant une "visite qualifiée" du widget public Visiteurs : accès
 * présumé humain (non-bot, non-bloqué, IP absente de la blocklist .htaccess) à une page
 * photo ou une section d'album (hors accueil), précédé ou suivi d'une autre URL de la
 * même IP dans les ±30 min. Utilisée par ajax_visitors.php (agrégat par pays) et
 * admin.php (détail ligne par ligne) — source unique pour que les deux calculs ne
 * divergent jamais (même piège que ip_location_bot_ua_keywords()).
 *
 * @param string $prefixeTable
 * @param string $alias  Alias de ip_location_log dans la requête appelante.
 * @return string Fragment à insérer après WHERE (sans le mot-clé WHERE lui-même).
 */
function ip_location_qualifying_visit_where($prefixeTable, $alias = 'l1')
{
    $in_blocklist_sql = ip_location_currently_blocked_sql($prefixeTable, "{$alias}.ip", "{$alias}.user_agent");

    return "
   {$alias}.is_bot        = 0
   AND {$alias}.is_blocked    = 0
   AND ({$alias}.log_type IS NULL OR {$alias}.log_type != 'prefetch')
   AND NOT ({$in_blocklist_sql})
   AND {$alias}.country_code != ''
   AND (
         {$alias}.url LIKE '%/picture.php%'
      OR {$alias}.url REGEXP '/category/[0-9]+|/list/[0-9]|/recent_pics|/most_visited|/best_rated|/tag/[0-9]|/search/[0-9]|/favorites'
   )
   AND EXISTS (
         SELECT 1
           FROM {$prefixeTable}ip_location_log l2
          WHERE l2.ip  = {$alias}.ip
            AND l2.url != {$alias}.url
            AND (l2.log_type IS NULL OR l2.log_type != 'prefetch')
            AND l2.visit_date BETWEEN
                DATE_SUB({$alias}.visit_date, INTERVAL 30 MINUTE)
                AND DATE_ADD({$alias}.visit_date, INTERVAL 30 MINUTE)
       )";
}

// Hooks de visite
add_event_handler('loc_begin_index',   'ip_location_log_visit');
add_event_handler('loc_begin_picture', 'ip_location_log_visit');

// Filtre pays sur les téléchargements d'originaux — voir §1 du handoff :
// action.php ne déclenche ni loc_begin_index ni loc_begin_picture, donc on
// s'accroche à init (déclenché par common.inc.php, avant que action.php
// n'atteigne son propre pwg_log).
add_event_handler('init', 'ip_location_download_guard');

// Blocklist (manuelle + auto) appliquée en PHP dès init : couvre tous les scripts PHP
// (pages, action.php, ws.php...), pas seulement index/picture, et fonctionne même si le
// .htaccess est inopérant (nginx, AllowOverride désactivé...).
add_event_handler('init', 'ip_location_blocklist_guard');

// Logger les photos vues via PhotoSwipe (navigation JS sans rechargement)
// Hook loc_begin_page_tail (pas loc_after_page_header) : à ce stade,
// $template->pparse() a déjà flush le header+corps de page vers le
// navigateur sur tous les points d'entrée publics (index.php, picture.php,
// comments.php, etc.), donc un echo direct ici arrive bien APRÈS le <html>
// déjà envoyé. loc_after_page_header se déclenche trop tôt : le header est
// encore dans le buffer $template->output (pas encore flush), donc un echo
// direct à ce hook part avant le <html> bufferisé — bug remonté par un
// utilisateur sur le forum Piwigo (JS injecté avant <html> dans la source).
add_event_handler('loc_begin_page_tail', 'ip_location_inject_pswp_logger');

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
// Hook loc_begin_page_tail plutôt que loc_after_page_header : voir le
// commentaire sur ip_location_inject_pswp_logger ci-dessus pour l'explication
// du bug (echo direct avant <html> encore bufferisé). Le panel est en
// position:fixed, donc sa position dans le DOM (juste avant le footer
// désormais, au lieu de juste après <body>) n'a aucun impact visuel.
add_event_handler('loc_begin_page_tail', 'ip_location_inject_visitors_panel');

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
 * IP publique valide (ni privée comme 192.168.x / 10.x / 172.16-31.x, ni réservée comme
 * 127.x, ni chaîne invalide — ex. un X-Forwarded-For fantaisiste). Seules ces IP sont
 * géolocalisées et peuvent être bloquées automatiquement.
 */
function ip_location_is_public_ip($ip)
{
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
}

/**
 * Résout la géolocalisation d'une IP : court-circuit immédiat pour une IP privée/réservée
 * (jamais géolocalisable, jamais mise en cache), sinon cache (30 jours pour un succès,
 * 2 h pour un 'Unknown' — voir $use_negative_cache) puis providers en cascade
 * (ip-api.com → freeipapi.com → ipwho.is → geoplugin.net → ipapi.co). Le cache est
 * alimenté dans tous les cas (y compris les échecs) et nettoyé (30 jours + limite 5000).
 *
 * @param string $ip                 IP déjà échappée pour SQL (pwg_db_real_escape_string).
 * @param string $prefixeTable
 * @param bool   $use_negative_cache Si false, ignore les entrées 'Unknown' en cache (mais
 *                                   les écrit quand même) : force une résolution live à
 *                                   chaque appel. Utilisé par ip_location_download_guard()
 *                                   pour qu'un 'Unknown' figé jusqu'à 2h ne fonde jamais
 *                                   une décision de blocage (désarmerait le filtre pendant
 *                                   un flood qui sature justement les providers).
 * @return array ['country' => ..., 'country_code' => ..., 'city' => ...] ('Unknown' si échec).
 */
function ip_location_resolve_geo($ip, $prefixeTable, $use_negative_cache = true)
{
    // IP privée / réservée / loopback (ex: réseau local 192.168.x) — non géolocalisable :
    // retour immédiat, aucun appel réseau. filter_var tolère une IP déjà échappée
    // (une IP valide ne contient aucun caractère échappable, donc pas de ré-échappement ici).
    if (!ip_location_is_public_ip($ip)) {
        return ['country' => 'Unknown', 'country_code' => '', 'city' => 'Unknown'];
    }

    // Vérification du cache (30 jours pour une résolution réussie ; 2 h pour un échec
    // 'Unknown', uniquement si $use_negative_cache — sinon on ignore les entrées négatives
    // et on retente une résolution live)
    $query = '
SELECT country, country_code, city
  FROM ' . $prefixeTable . 'ip_location_cache
  WHERE ip = \'' . $ip . '\'
    AND (
          (country <> \'Unknown\' AND resolved_at >= NOW() - INTERVAL 30 DAY)'
    . ($use_negative_cache ? '
       OR (country =  \'Unknown\' AND resolved_at >= NOW() - INTERVAL 2 HOUR)' : '') . '
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
 * IP du visiteur telle que journalisée par ip_location_log_visit() (1ère entrée de
 * X-Forwarded-For si présente, sinon REMOTE_ADDR) — donc celle qui figure dans la
 * blocklist. Source unique pour log_visit() et ip_location_blocklist_guard(), afin que
 * le contrôle de blocage compare bien la même IP que celle enregistrée.
 */
function ip_location_visitor_ip()
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($parts[0]);
    }
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Requête de pré-lecture émise par le navigateur (ex. <link rel="prefetch"> de la photo
 * suivante dans le thème par défaut, header.tpl) et non par une action du visiteur :
 * chaque photo vue génère une 2e requête, qui ne doit compter ni dans les règles de
 * rafale ni comme page vue (la dernière photo pré-chargée n'est souvent jamais vue).
 */
function ip_location_is_prefetch_request()
{
    foreach (['HTTP_SEC_PURPOSE', 'HTTP_PURPOSE', 'HTTP_X_MOZ'] as $header) {
        if (isset($_SERVER[$header]) && stripos($_SERVER[$header], 'prefetch') !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Liste par défaut du bloc "Robots d'indexation" (v2.6.2). Chaque robot : nom, motif
 * cherché dans le User-Agent (insensible à la casse), famille (search / social / ai),
 * domaines de vérification DNS (vide = le robot ne publie pas de méthode : confiance au
 * User-Agent) et statut (allow / block). Moteurs de recherche et aperçus de partage
 * autorisés ; robots d'IA bloqués par défaut (décision v2.6.5 : GPTBot observé à ~50
 * requêtes/minute pendant des heures sur un site réel, sans bénéfice pour le site).
 */
function ip_location_default_robots()
{
    $r = function ($name, $ua, $fam, $verify = '') {
        return ['name' => $name, 'ua' => $ua, 'fam' => $fam, 'verify' => $verify,
                'status' => $fam === 'ai' ? 'block' : 'allow'];
    };
    return [
        $r('Googlebot',    'Googlebot',           'search', 'googlebot.com google.com'),
        $r('Bingbot',      'bingbot',             'search', 'search.msn.com'),
        $r('Applebot',     'Applebot',            'search', 'applebot.apple.com'),
        $r('DuckDuckBot',  'DuckDuckBot',         'search'),
        $r('Qwant',        'Qwantbot',            'search'),
        $r('Yandex',       'YandexBot',           'search', 'yandex.ru yandex.net yandex.com'),
        $r('Baidu',        'Baiduspider',         'search', 'baidu.com baidu.jp'),
        $r('Facebook',     'facebookexternalhit', 'social'),
        $r('X / Twitter',  'Twitterbot',          'social'),
        $r('Slack',        'Slackbot',            'social'),
        $r('WhatsApp',     'WhatsApp',            'social'),
        $r('LinkedIn',     'LinkedInBot',         'social'),
        $r('Telegram',     'TelegramBot',         'social'),
        $r('Discord',      'Discordbot',          'social'),
        $r('OpenAI',       'GPTBot',              'ai'),
        $r('Anthropic',    'ClaudeBot',           'ai'),
        $r('Common Crawl', 'CCBot',               'ai'),
        $r('ByteDance',    'Bytespider',          'ai'),
        $r('Perplexity',   'PerplexityBot',       'ai'),
        $r('Amazon',       'Amazonbot',           'ai'),
    ];
}

/**
 * Liste courante des robots : celle enregistrée dans l'admin, sinon — tant qu'elle n'a
 * jamais été enregistrée — la liste par défaut, ou la reprise de l'ancienne liste
 * blanche $conf['ip_location_bot_allowlist'] si le site l'avait personnalisée : ses
 * robots sont alors listés et autorisés (même un robot d'IA : l'administrateur l'avait
 * explicitement toléré), complétés des robots d'IA par défaut, bloqués ; les autres
 * restent traités par le score comme avant la mise à jour.
 */
function ip_location_get_robots()
{
    static $cache = null;
    if ($cache !== null) return $cache;

    $plugin_conf = ip_location_get_conf();
    if (is_array($plugin_conf['robots'])) {
        return $cache = $plugin_conf['robots'];
    }

    $defaults = ip_location_default_robots();
    $legacy = $GLOBALS['ip_location_legacy_bot_allowlist'] ?? null;
    if ($legacy === null) {
        return $cache = $defaults;
    }
    $patterns = array_filter(array_map('trim', is_array($legacy) ? $legacy : explode("\n", (string)$legacy)));
    $list = [];
    foreach ($patterns as $pattern) {
        $known = null;
        foreach ($defaults as $d) {
            if (strcasecmp($d['ua'], $pattern) === 0 || strcasecmp($d['name'], $pattern) === 0) {
                $known = $d;
                break;
            }
        }
        if ($known) {
            $known['status'] = 'allow';
        }
        $list[] = $known ?: ['name' => $pattern, 'ua' => $pattern, 'fam' => 'search', 'verify' => '', 'status' => 'allow'];
    }
    // Robots d'IA par défaut (bloqués) absents de l'ancienne liste
    $listed = array_map('strtolower', array_column($list, 'ua'));
    foreach ($defaults as $d) {
        if ($d['fam'] === 'ai' && !in_array(strtolower($d['ua']), $listed, true)) {
            $list[] = $d;
        }
    }
    return $cache = $list;
}

/**
 * Robot de la liste dont le motif apparaît dans ce User-Agent (premier trouvé), ou null.
 */
function ip_location_match_robot($user_agent)
{
    if ($user_agent === '') return null;
    foreach (ip_location_get_robots() as $robot) {
        if ($robot['ua'] !== '' && stripos($user_agent, $robot['ua']) !== false) {
            return $robot;
        }
    }
    return null;
}

/**
 * Vérifie qu'une IP appartient bien au robot annoncé : DNS inverse (le nom doit finir par
 * l'un des domaines du robot) puis DNS direct (ce nom doit redonner la même IP) — méthode
 * publiée par Google, Bing, Apple, Yandex, Baidu. Résultat gardé en cache
 * (ip_location_robot_check, ip_location_robot_check_days jours) : une seule résolution
 * par IP. Un robot sans domaine de vérification est cru sur parole (true).
 */
function ip_location_robot_verified($ip_raw, $robot)
{
    global $prefixeTable, $conf;

    $domains = array_filter(preg_split('/\s+/', trim($robot['verify'] ?? '')));
    if (empty($domains)) {
        return true;
    }
    if (!filter_var($ip_raw, FILTER_VALIDATE_IP)) {
        return false;
    }
    $ip_sql = pwg_db_real_escape_string($ip_raw);
    $days = max(1, (int)$conf['ip_location_robot_check_days']);

    $r = pwg_query('SELECT verified FROM ' . $prefixeTable . 'ip_location_robot_check
  WHERE ip = \'' . $ip_sql . '\' AND checked_at >= NOW() - INTERVAL ' . $days . ' DAY LIMIT 1');
    if ($row = pwg_db_fetch_row($r)) {
        return $row[0] === '1' || $row[0] === 1;
    }

    $verified = false;
    $host = @gethostbyaddr($ip_raw);
    if ($host && $host !== $ip_raw) {
        $host_l = strtolower(rtrim($host, '.'));
        foreach ($domains as $dom) {
            $dom = strtolower($dom);
            if ($host_l === $dom || substr($host_l, -strlen($dom) - 1) === '.' . $dom) {
                // DNS direct : le nom doit pointer vers la même IP (sinon DNS inverse falsifié)
                if (filter_var($ip_raw, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ips = @gethostbynamel($host_l) ?: [];
                } else {
                    $ips = array_column(@dns_get_record($host_l, DNS_AAAA) ?: [], 'ipv6');
                    $ips = array_map(function ($a) { return inet_ntop(inet_pton($a)); }, $ips);
                    $ip_raw = inet_ntop(inet_pton($ip_raw));
                }
                $verified = in_array($ip_raw, $ips, true);
                break;
            }
        }
    }

    pwg_query('
REPLACE INTO ' . $prefixeTable . 'ip_location_robot_check (ip, robot, verified, checked_at)
  VALUES (\'' . $ip_sql . '\', \'' . pwg_db_real_escape_string($robot['name']) . '\', ' . ($verified ? 1 : 0) . ', NOW())');
    return $verified;
}

/**
 * Statut "robot" de la requête en cours (calculé une fois par requête) :
 *   null      — bloc Robots coupé, invité inconnu, ou robot hors liste (→ score) ;
 *   'allowed' — robot autorisé et authentique : passe tous les leviers ;
 *   'blocked' — robot marqué "Bloqué" : refusé (sans vérification : un faux GPTBot reste
 *               un robot dont on ne veut pas) ;
 *   'spoof'   — UA d'un robot autorisé vérifiable, mais IP qui ne lui appartient pas :
 *               traité comme un visiteur ordinaire (et signal de score, cf. classify).
 * Retourne ['state' => …, 'robot' => array|null].
 */
function ip_location_request_robot_state($ip_raw, $user_agent)
{
    static $memo = [];
    $key = $ip_raw . '|' . $user_agent;
    if (isset($memo[$key])) return $memo[$key];

    $state = ['state' => null, 'robot' => null];
    $plugin_conf = ip_location_get_conf();
    if ($plugin_conf['robots_enabled'] === '1') {
        $robot = ip_location_match_robot($user_agent);
        if ($robot) {
            $state['robot'] = $robot;
            if (($robot['status'] ?? 'allow') === 'block') {
                $state['state'] = 'blocked';
            } else {
                $state['state'] = ip_location_robot_verified($ip_raw, $robot) ? 'allowed' : 'spoof';
            }
        }
    }
    return $memo[$key] = $state;
}

/**
 * Fragment SQL (parenthésé) vrai pour une ligne de journal émise par un robot autorisé
 * authentique : UA correspondant à un robot "autorisé" de la liste, et IP non démasquée
 * comme usurpatrice (ip_location_robot_check.verified = 0). '0' si le bloc Robots est
 * coupé ou sans robot autorisé. Remplace l'ancienne liste blanche UA (jusqu'en v2.6.1).
 */
function ip_location_allowed_robot_sql($prefixeTable, $ua_col = 'user_agent', $ip_col = 'ip')
{
    $plugin_conf = ip_location_get_conf();
    if ($plugin_conf['robots_enabled'] !== '1') {
        return '0';
    }
    $likes = [];
    foreach (ip_location_get_robots() as $robot) {
        if (($robot['status'] ?? 'allow') === 'allow' && $robot['ua'] !== '') {
            $likes[] = $ua_col . " LIKE '%" . pwg_db_real_escape_string($robot['ua']) . "%'";
        }
    }
    if (empty($likes)) {
        return '0';
    }
    return '((' . implode(' OR ', $likes) . ') AND ' . $ip_col . ' NOT IN (SELECT ip FROM '
        . $prefixeTable . 'ip_location_robot_check WHERE verified = 0))';
}

/**
 * URL complète de la requête en cours, telle que journalisée.
 */
function ip_location_current_url()
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Géolocalisation lue dans le cache uniquement (jamais d'appel réseau), pour journaliser
 * un refus de la blocklist sans solliciter les providers pendant un martèlement. Repli
 * sur le pays/ville mémorisés dans la blocklist si l'IP n'est pas en cache (IP neuve
 * d'une plage /16 bloquée, par exemple).
 */
function ip_location_geo_from_cache($ip_raw, $fallback_country, $fallback_city)
{
    global $prefixeTable;
    $r = pwg_query('SELECT country, country_code, city FROM ' . $prefixeTable . 'ip_location_cache
  WHERE ip = \'' . pwg_db_real_escape_string($ip_raw) . '\' LIMIT 1');
    $row = pwg_db_fetch_assoc($r);
    if ($row && $row['country'] !== 'Unknown') {
        return $row;
    }
    return [
        'country'      => $fallback_country !== null && $fallback_country !== '' ? $fallback_country : 'Unknown',
        'country_code' => '',
        'city'         => $fallback_city !== null && $fallback_city !== '' ? $fallback_city : 'Unknown',
    ];
}

/**
 * Vrai si un refus de même motif a déjà été journalisé pour cette IP depuis moins de
 * $conf['ip_location_refusal_log_minutes'] minutes (défaut 10). Un robot bloqué qui
 * insiste (GPTBot observé à ~50 requêtes/minute pendant des heures) remplirait sinon le
 * journal jusqu'à évincer tout le reste via max_records. La requête reste refusée à
 * chaque fois ; seule sa journalisation est espacée. Requête limitée aux dernières
 * minutes (index idx_visit_date).
 */
function ip_location_refusal_recently_logged($ip_raw, $reason)
{
    global $prefixeTable, $conf;
    $minutes = (int)$conf['ip_location_refusal_log_minutes'];
    if ($minutes <= 0) {
        return false;
    }
    $r = pwg_query('SELECT 1 FROM ' . $prefixeTable . 'ip_location_log
  WHERE visit_date >= NOW() - INTERVAL ' . $minutes . ' MINUTE
    AND ip = \'' . pwg_db_real_escape_string($ip_raw) . '\'
    AND block_reason = \'' . pwg_db_real_escape_string($reason) . '\'
  LIMIT 1');
    return pwg_db_num_rows($r) > 0;
}

/**
 * Vidage automatique au-delà de max_records — échantillonné (1 appel sur 50) pour éviter
 * un COUNT(*) à chaque accès. Le léger dépassement transitoire est sans conséquence.
 */
function ip_location_enforce_max_records($plugin_conf)
{
    global $prefixeTable;
    $max_records = (int)$plugin_conf['max_records'];
    if ($max_records > 0 && mt_rand(1, 50) === 1) {
        $r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log');
        list($count) = pwg_db_fetch_row($r);
        if ($count > $max_records) {
            pwg_query('
DELETE FROM ' . $prefixeTable . 'ip_location_log
  ORDER BY visit_date ASC
  LIMIT ' . ($count - $max_records));
        }
    }
}

/**
 * Blocklist appliquée en PHP, accrochée à init : 403 immédiat pour un invité dont l'IP
 * exacte, ou la plage /16 manuelle qui la couvre, est bloquée — mais seulement pour les
 * leviers allumés (v2.6.1, principe "observateur") :
 *   - entrées 'manuel' (IP et plages) : interrupteur "Blocage par IP" (htaccess_enabled) ;
 *   - entrées 'auto' : interrupteur du blocage automatique (bot_block_enabled).
 * Tous deux coupés : aucune requête, rien n'est bloqué. Seul mécanisme appliquant les
 * entrées 'auto' (jamais écrites dans le .htaccess) ; filet de sécurité pour les manuelles
 * si le .htaccess est inopérant. Chaque refus est journalisé (is_blocked=1, block_reason
 * 'ip' ou 'auto') pour que la catégorie "Bloqués" montre les refus réels.
 */
function ip_location_blocklist_guard()
{
    global $user, $prefixeTable;

    // Invités uniquement (même règle que ip_location_log_visit())
    if (!isset($user['id']) || $user['id'] != 2) {
        return;
    }

    $plugin_conf = ip_location_get_conf();
    $origins = [];
    if ($plugin_conf['htaccess_enabled'] === '1') {
        $origins[] = '\'manuel\'';
    }
    if ($plugin_conf['bot_block_enabled'] === '1') {
        $origins[] = '\'auto\'';
    }
    if (empty($origins) && $plugin_conf['robots_enabled'] !== '1') {
        return;
    }

    $ip_raw = ip_location_visitor_ip();

    // Liste blanche d'IPs — toujours autorisées
    $whitelist = array_filter(array_map('trim', explode("\n", $plugin_conf['whitelist'])));
    if (in_array($ip_raw, $whitelist)) {
        return;
    }

    // Robots d'indexation (v2.6.2) : un robot "Bloqué" est refusé ici quel que soit son
    // IP ; un robot autorisé et authentique passe la blocklist (comme tous les leviers).
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $robot_state = ip_location_request_robot_state($ip_raw, $user_agent);
    if ($robot_state['state'] === 'blocked') {
        if (!ip_location_refusal_recently_logged($ip_raw, 'robot')) {
            ip_location_insert_log([
                'ip'           => $ip_raw,
                'geo'          => ip_location_geo_from_cache($ip_raw, null, null),
                'url'          => ip_location_current_url(),
                'user_agent'   => $user_agent,
                'is_bot'       => 1,
                'is_blocked'   => 1,
                'log_type'     => ip_location_is_prefetch_request() ? 'prefetch' : null,
                'block_reason' => 'robot',
            ]);
            ip_location_enforce_max_records($plugin_conf);
        }
        header('HTTP/1.0 403 Forbidden');
        exit;
    }
    if ($robot_state['state'] === 'allowed' || empty($origins)) {
        return;
    }

    $keys = ['\'' . pwg_db_real_escape_string($ip_raw) . '\''];
    if (preg_match('/^(\d+\.\d+)\.\d+\.\d+$/', $ip_raw, $m)) {
        // Plage /16 au format écrit par le blocage manuel ("A.B.0.0/16")
        $keys[] = '\'' . $m[1] . '.0.0/16\'';
    }

    // origin='exempt' jamais retenu (retrait manuel, cf. ip_location_get_bot_candidates()).
    // Les entrées auto expirées ne bloquent plus rien.
    $r = pwg_query('SELECT origin, country, city FROM ' . $prefixeTable . 'ip_location_blocklist
  WHERE ip IN (' . implode(',', $keys) . ')
    AND origin IN (' . implode(',', $origins) . ')
    AND (expires_at IS NULL OR expires_at > NOW())
  LIMIT 1');
    $hit = pwg_db_fetch_assoc($r);
    if (!$hit) {
        return;
    }

    $reason = $hit['origin'] === 'auto' ? 'auto' : 'ip';
    if (!ip_location_refusal_recently_logged($ip_raw, $reason)) {
        ip_location_insert_log([
            'ip'           => $ip_raw,
            'geo'          => ip_location_geo_from_cache($ip_raw, $hit['country'], $hit['city']),
            'url'          => ip_location_current_url(),
            'user_agent'   => $user_agent,
            'is_bot'       => ip_location_is_bot_ua($user_agent) ? 1 : 0,
            'is_blocked'   => 1,
            'log_type'     => ip_location_is_prefetch_request() ? 'prefetch' : null,
            'block_reason' => $reason,
        ]);
        ip_location_enforce_max_records($plugin_conf);
    }

    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * INSERT d'une ligne de journal (source unique pour log_visit, les refus de la blocklist
 * et les tentatives de téléchargement). $row : ip (brute), geo (country/country_code/city),
 * url, user_agent, is_bot, is_blocked, log_type (null|string), block_reason (null|string).
 */
function ip_location_insert_log($row)
{
    global $prefixeTable;
    $str = function ($v) {
        return $v === null ? 'NULL' : '\'' . pwg_db_real_escape_string($v) . '\'';
    };
    pwg_query('
INSERT INTO ' . $prefixeTable . 'ip_location_log
  (ip, country, country_code, city, url, user_agent, is_bot, is_blocked, log_type, block_reason, visit_date)
  VALUES (
    ' . $str($row['ip']) . ',
    ' . $str($row['geo']['country']) . ',
    ' . $str($row['geo']['country_code']) . ',
    ' . $str($row['geo']['city']) . ',
    ' . $str($row['url']) . ',
    ' . $str($row['user_agent']) . ',
    ' . (int)$row['is_bot'] . ',
    ' . (int)$row['is_blocked'] . ',
    ' . $str($row['log_type'] ?? null) . ',
    ' . $str($row['block_reason'] ?? null) . ',
    NOW()
  )');
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
 * Normalise un tableau de "Mes préréglages" à exactement
 * IP_LOCATION_STATS_PRESET_SLOTS emplacements (null ou ['name'=>..,'series'=>..]).
 */
function ip_location_normalize_stats_presets($presets)
{
    if (!is_array($presets)) $presets = [];
    while (count($presets) < IP_LOCATION_STATS_PRESET_SLOTS) $presets[] = null;
    return array_slice($presets, 0, IP_LOCATION_STATS_PRESET_SLOTS);
}

/**
 * Valide et nettoie un tableau de séries reçu du constructeur de l'onglet
 * Statistiques dynamiques (JSON décodé côté appelant — admin.php pour
 * l'enregistrement d'un préréglage, ajax_stats.php pour le calcul du graphique).
 * Ne fait confiance à aucune valeur reçue : chaque champ est whitelisté.
 * Retourne un tableau nettoyé (jamais plus de 8 séries, jamais d'entrée invalide).
 *
 * @param mixed $raw               Valeur décodée de series_json (doit être un tableau).
 * @param array $allowed_keywords  Mots-clés actuellement configurés (blocked_url_keywords,
 *                                 un par ligne) : seule une appartenance stricte à cette
 *                                 liste est acceptée, ce qui évite de valider du texte libre
 *                                 avant de construire des clauses LIKE.
 * @return array
 */
function ip_location_validate_stats_series($raw, array $allowed_keywords)
{
    if (!is_array($raw)) return [];

    $clean = [];
    foreach ($raw as $item) {
        if (count($clean) >= 8) break;
        if (!is_array($item)) continue;

        $type = in_array($item['type'] ?? '', ['all', 'normal', 'bot', 'blocked'], true) ? $item['type'] : 'all';

        $label = mb_substr(strip_tags(trim((string)($item['label'] ?? ''))), 0, 60);

        $axis = ($item['axis'] ?? '') === 'y1' ? 'y1' : 'y';

        $countries = [];
        if (!empty($item['countries']) && is_array($item['countries'])) {
            foreach (array_slice($item['countries'], 0, 50) as $c) {
                $c = strtoupper(trim((string)$c));
                if (preg_match('/^[A-Z]{2}$/', $c)) $countries[] = $c;
            }
        }

        $keywords = [];
        if (!empty($item['keywords']) && is_array($item['keywords'])) {
            foreach (array_slice($item['keywords'], 0, 50) as $k) {
                if (in_array((string)$k, $allowed_keywords, true)) $keywords[] = (string)$k;
            }
        }

        $ips = [];
        if (!empty($item['ips']) && is_array($item['ips'])) {
            foreach (array_slice($item['ips'], 0, 50) as $ip) {
                $ip = preg_replace('/[^0-9a-fA-F.:\/]/', '', trim((string)$ip));
                if ($ip !== '') $ips[] = $ip;
            }
        }

        $color = (!empty($item['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $item['color'])) ? $item['color'] : '';

        $clean[] = [
            'label'     => $label,
            'type'      => $type,
            'countries' => $countries,
            'keywords'  => $keywords,
            'ips'       => $ips,
            'axis'      => $axis,
            'color'     => $color,
        ];
    }

    return $clean;
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

    // 7. Géo — cache négative ignorée sur ce chemin ($use_negative_cache=false) : un
    // 'Unknown' figé jusqu'à 2h ne doit jamais fonder une décision de blocage download
    // (désarmerait le filtre pendant un flood de scrapers, qui sature justement les
    // providers). Le chemin pages (log_visit) garde le bénéfice perf de la cache négative.
    $geo    = ip_location_resolve_geo($ip, $prefixeTable, false);
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

    // Géo indisponible : appliquer download_geo_fail_mode.
    // 'closed' est le comportement par défaut effectif — tout ce qui n'est pas
    // explicitement 'open' est traité comme fail-closed (bloquer).
    if ($plugin_conf['download_geo_fail_mode'] !== 'open') {
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
    // $ip vient de REMOTE_ADDR (toujours une IP valide, sans caractère échappable).
    ip_location_insert_log([
        'ip'           => $ip,
        'geo'          => $geo,
        'url'          => $url,
        'user_agent'   => $user_agent,
        'is_bot'       => $is_bot,
        'is_blocked'   => $is_blocked,
        'log_type'     => 'download',
        'block_reason' => $is_blocked ? 'download' : null,
    ]);
}

/**
 * Enregistre la visite d'un guest avec géolocalisation.
 *
 * @param string|null $override_url  URL à loguer (null = URL de la requête courante).
 *                                   Utilisé par ajax_log.php pour les vues PhotoSwipe.
 * @param bool        $do_block      Si true, envoie un 403 et exit en cas de blocage.
 *                                   Mettre à false depuis ajax_log.php.
 */
function ip_location_log_visit($override_url = null, $do_block = true, $log_type = null)
{
    global $user, $prefixeTable, $conf;

    // Uniquement les guests (id = 2)
    if (!isset($user['id']) || $user['id'] != 2) {
        return;
    }

    // Récupération de l'IP
    $ip = ip_location_visitor_ip();

    $ip_raw = $ip; // IP non échappée pour les comparaisons

    // Pré-lecture du navigateur (photo suivante) : journalisée mais marquée, pour être
    // exclue des règles de rafale et des visites qualifiées. Les contrôles de blocage
    // ci-dessous s'appliquent normalement.
    if ($log_type === null && ip_location_is_prefetch_request()) {
        $log_type = 'prefetch';
    }

    // Liste blanche d'IPs — toujours autorisées
    $plugin_conf  = ip_location_get_conf();
    $whitelist_raw = $plugin_conf['whitelist'];
    $whitelist = array_filter(array_map('trim', explode("\n", $whitelist_raw)));
    if (in_array($ip_raw, $whitelist)) {
        return;
    }

    $ip = pwg_db_real_escape_string($ip);

    // Blocklist manuelle/auto : déjà appliquée plus tôt par ip_location_blocklist_guard()
    // (hook init), qui couvre aussi les plages /16 — une IP bloquée n'arrive jamais ici.

    // Résolution géo (cache ou providers en cascade)
    $geo = ip_location_resolve_geo($ip, $prefixeTable);

    // Construction de l'URL visitée
    if ($override_url !== null && is_string($override_url)) {
        $url = $override_url;
    } else {
        $url = ip_location_current_url();
    }
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Détection bot par user-agent uniquement (zéro SQL) — la détection par
    // co-visitation (scan de la table) est différée à ip_location_classify_recent()
    $is_bot = ip_location_is_bot_ua($user_agent) ? 1 : 0;

    // Robot de la liste (v2.6.2) : toujours "bot" ; s'il est autorisé et authentique, il
    // passe le blocage par pays et par mot-clé (le garde init lui a déjà épargné la
    // blocklist). Un robot "Bloqué" n'arrive jamais ici (refusé par le garde init).
    $robot_state = ip_location_request_robot_state($ip_raw, $user_agent);
    if ($robot_state['robot']) {
        $is_bot = 1;
    }
    $robot_allowed = $robot_state['state'] === 'allowed';

    // Déterminer si la visite sera bloquée (avant l'INSERT pour l'enregistrer), et pourquoi
    $is_blocked   = 0;
    $block_reason = null;

    // Blocage par pays
    if (!$robot_allowed && $plugin_conf['blocking_enabled'] === '1') {
        $blocked = array_filter(array_map('trim', explode(',', strtoupper($plugin_conf['blocked_countries']))));
        if (!empty($blocked) && in_array(strtoupper($geo['country_code']), $blocked)) {
            $is_blocked   = 1;
            $block_reason = 'country';
        }
    }

    // Blocage par mot-clé dans l'URL — seulement si son interrupteur est allumé (v2.6.1)
    if (!$is_blocked && !$robot_allowed && $plugin_conf['keyword_block_enabled'] === '1' && !empty($plugin_conf['blocked_url_keywords'])) {
        $url_keywords = array_filter(array_map('trim', explode("\n", $plugin_conf['blocked_url_keywords'])));
        $url_lower = strtolower($url);
        foreach ($url_keywords as $kw) {
            if (strpos($url_lower, strtolower($kw)) !== false) {
                $is_blocked   = 1;
                $block_reason = 'keyword';
                break;
            }
        }
    }

    ip_location_insert_log([
        'ip'           => $ip_raw,
        'geo'          => $geo,
        'url'          => $url,
        'user_agent'   => $user_agent,
        'is_bot'       => $is_bot,
        'is_blocked'   => $is_blocked,
        'log_type'     => $log_type,
        'block_reason' => $block_reason,
    ]);

    // Marquage rétroactif par co-visitation : différé en lot à ip_location_classify_recent()
    // (appelée depuis admin.php à chaque chargement, et ci-dessous au plus 1x/jour depuis
    // le trafic public), pour éviter un scan de la table à chaque visite.

    ip_location_enforce_max_records($plugin_conf);

    // Classification bot différée (score + blocage auto), au plus une fois toutes les
    // ip_location_classify_interval_hours (défaut 4h), déclenchée par le trafic public
    // puisqu'aucune visite admin ne le garantit sinon (ip_location_classify_recent()
    // n'était auparavant appelée que depuis admin.php, donc jamais mise à jour sur un
    // site peu administré).
    $last_classify     = $plugin_conf['last_classify_at'] ?? '';
    $classify_interval = (int)$conf['ip_location_classify_interval_hours'] * 3600;
    if ($last_classify === '' || strtotime($last_classify) <= time() - $classify_interval) {
        ip_location_classify_recent();
        conf_update_param('ip_location', serialize(array_merge($plugin_conf, [
            'last_classify_at' => date('Y-m-d H:i:s'),
        ])));
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
        // Blocages manuels uniquement : les entrées 'auto' (nombreuses, temporaires, IP
        // souvent à usage unique) sont appliquées en PHP par ip_location_blocklist_guard()
        // — les écrire ici gonflait le .htaccess (10 000+ lignes relues par Apache à
        // chaque requête, constaté en v2.5.4) sans bénéfice réel.
        $result = pwg_query('SELECT ip FROM ' . $prefixeTable . 'ip_location_blocklist
  WHERE origin = \'manuel\'
  ORDER BY blocked_at ASC');
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
 * Liste des mots-clés User-Agent identifiant un bot, partagée par
 * ip_location_is_bot_ua(), ip_location_is_bot() et le calcul du score de suspicion
 * (ip_location_classify_recent()) — source unique pour éviter que les 3 dérivent.
 * Les entrées censys/palo alto/leakix/cms-checker/rootevidence/netcraft/
 * tlm-audit-scanner/internetmeasurement/okhttp ont été ajoutées suite à une analyse
 * du journal réel (v2.5) : des scanners qui s'auto-identifient dans leur UA mais
 * échappaient jusque-là à la liste.
 */
function ip_location_bot_ua_keywords()
{
    return ['bot', 'crawler', 'spider', 'scraper', 'slurp', 'curl', 'wget',
            'python', 'go-http', 'java/', 'libwww', 'scrapy', 'zgrab', 'masscan',
            'censys', 'palo alto', 'leakix', 'cms-checker', 'rootevidence',
            'netcraft', 'tlm-audit-scanner', 'internetmeasurement', 'okhttp'];
}

/**
 * Détecte un navigateur/OS manifestement obsolète (Firefox/Chrome/iOS plusieurs années
 * derrière la version courante) : un vrai visiteur n'a normalement aucune raison de
 * tourner sur un tel User-Agent en 2026. Ajouté suite à une analyse du journal réel
 * (v2.5) : un seul UA "Firefox/47.0" (sorti en 2016) générait à lui seul plus de 6000
 * accès jamais détectés, et un faux "iPhone iOS 13.2.3" était vu sur 103 IP.
 * Chrome 109 est volontairement épargné : c'est la dernière version pour Windows 7/8,
 * donc celle de vrais visiteurs sur de vieux PC. Un iPhone 5s/6 jamais mis à jour
 * (iOS 12) reste un faux positif possible mais très rare.
 */
function ip_location_is_outdated_browser($user_agent)
{
    if (preg_match('/Firefox\/(\d+)/', $user_agent, $m) && (int)$m[1] < 100) {
        return true;
    }
    if (preg_match('/Chrome\/(\d+)/', $user_agent, $m) && (int)$m[1] < 109) {
        return true;
    }
    if (preg_match('/(?:iPhone|CPU) OS (\d+)_/', $user_agent, $m) && (int)$m[1] < 14) {
        return true;
    }
    return false;
}

/**
 * Détecte un User-Agent qui annonce lui-même un crawler : URL ou adresse de contact
 * ("+http://…", "+info@…") ou motif "(compatible; …)" — un navigateur n'en met jamais
 * (hors vieux Internet Explorer, exclu via msie/trident). Règle générique qui couvre
 * aussi les scanners encore inconnus, ex. "(compatible; GoogleOther)" qui ne contient
 * pas le mot "bot". Doit rester cohérente avec la clause SQL équivalente de
 * ip_location_classify_recent() (signal mot-clé).
 */
function ip_location_ua_declares_crawler($user_agent)
{
    $ua_lower = strtolower($user_agent);
    if (strpos($ua_lower, '://') !== false || strpos($ua_lower, '@') !== false) {
        return true;
    }
    return strpos($ua_lower, 'compatible;') !== false
        && strpos($ua_lower, 'msie') === false
        && strpos($ua_lower, 'trident') === false;
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

    $ua_lower = strtolower($user_agent);
    foreach (ip_location_bot_ua_keywords() as $kw) {
        if (strpos($ua_lower, $kw) !== false) {
            return true;
        }
    }

    return ip_location_ua_declares_crawler($user_agent)
        || ip_location_is_outdated_browser($user_agent);
}

/**
 * Rejoue en lot, sur les 7 derniers jours, la détection de bots par co-visitation
 * (>= 2 IP distinctes sur la même URL dans une fenêtre de ~10 s), ainsi que le calcul
 * du score de suspicion bot (bot_score) et le blocage automatique associé (4ème
 * levier, cf. ip_location_auto_block_bots()). Approximation par buckets fixes de 10 s
 * (au lieu d'une fenêtre glissante) — sans impact sur is_bot, qui reste une info de
 * stats/filtre ne pilotant aucun blocage par lui-même. Idempotent (WHERE is_bot = 0
 * pour la co-visitation ; bot_score recalculé intégralement à chaque passage).
 * Appelée depuis admin.php à chaque chargement de l'onglet, et depuis
 * ip_location_log_visit() au plus une fois par jour (trafic public) pour garantir
 * que le blocage auto fonctionne même sans visite admin régulière.
 */
function ip_location_classify_recent()
{
    global $prefixeTable, $conf;

    // Fenêtre glissante sur laquelle portent toute la classification et le calcul du
    // score (surchargeable via $conf['ip_location_classify_window_days'], défaut 7).
    $window_days = max(1, (int)$conf['ip_location_classify_window_days']);

    // Seuil de la rafale multi-URL (URL distinctes par IP en ~30 s), cf. plus bas.
    $multi_url_threshold = max(2, (int)$conf['ip_location_multi_url_threshold']);

    // Toutes les règles comportementales ci-dessous ignorent les requêtes de pré-lecture
    // (log_type='prefetch', cf. ip_location_is_prefetch_request()) : elles doublent le
    // nombre d'URL d'une navigation humaine et ne traduisent aucune action du visiteur.

    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log t
JOIN (
    SELECT url, FLOOR(UNIX_TIMESTAMP(visit_date)/10) AS bucket
      FROM ' . $prefixeTable . 'ip_location_log
     WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
       AND (log_type IS NULL OR log_type != \'prefetch\')
     GROUP BY url, bucket
    HAVING COUNT(DISTINCT ip) >= 2
) g
  ON t.url = g.url
 AND FLOOR(UNIX_TIMESTAMP(t.visit_date)/10) = g.bucket
SET t.is_bot = 1
WHERE t.is_bot = 0
  AND t.visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY');

    // Rafale mono-IP : >= 3 accès de la même IP sur la même URL dans une fenêtre de ~10 s
    // (ex. martèlement automatisé d'une seule page, contrairement à la co-visitation
    // ci-dessus qui vise les rafales distribuées sur IP différentes).
    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log t
JOIN (
    SELECT ip, url, FLOOR(UNIX_TIMESTAMP(visit_date)/10) AS bucket
      FROM ' . $prefixeTable . 'ip_location_log
     WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
       AND (log_type IS NULL OR log_type != \'prefetch\')
     GROUP BY ip, url, bucket
    HAVING COUNT(*) >= 3
) g
  ON t.ip = g.ip
 AND t.url = g.url
 AND FLOOR(UNIX_TIMESTAMP(t.visit_date)/10) = g.bucket
SET t.is_bot = 1
WHERE t.is_bot = 0
  AND t.visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY');

    // Rafale multi-URL mono-IP : >= $multi_url_threshold URL distinctes (défaut 20) par la
    // même IP en ~30 s (ex. un scraper qui parcourt le catalogue à grande vitesse),
    // contrairement à la rafale mono-IP ci-dessus qui vise la répétition d'une même URL.
    // Ajouté suite à une analyse du journal réel (v2.5) : plusieurs IP faisaient 47 à 88
    // accès à des URL toutes différentes en moins de 25 s, invisibles aux règles
    // précédentes. Seuil initial de 10 relevé à 20 en v2.5.5 : un humain qui clique
    // "suivant" dans un album atteint facilement 11-12 photos en 30 s (faux positif réel).
    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log t
JOIN (
    SELECT ip, FLOOR(UNIX_TIMESTAMP(visit_date)/30) AS bucket
      FROM ' . $prefixeTable . 'ip_location_log
     WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
       AND (log_type IS NULL OR log_type != \'prefetch\')
     GROUP BY ip, bucket
    HAVING COUNT(DISTINCT url) >= ' . $multi_url_threshold . '
) g
  ON t.ip = g.ip
 AND FLOOR(UNIX_TIMESTAMP(t.visit_date)/30) = g.bucket
SET t.is_bot = 1
WHERE t.is_bot = 0
  AND t.visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY');

    // ── Score de suspicion bot (4ème levier de blocage, optionnel) ──────────
    // Recalculé intégralement à chaque passage (remise à 0 puis réaccumulation) plutôt
    // qu'incrémenté : contrairement à is_bot (booléen, idempotent via WHERE is_bot=0),
    // bot_score doit refléter l'état actuel du signal de récidive, qui peut évoluer
    // d'un passage à l'autre à mesure que de nouvelles lignes suspectes s'accumulent.
    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log
   SET bot_score = 0
 WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY');

    // User-Agent vide
    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log
   SET bot_score = bot_score + ' . (int)$conf['ip_location_score_ua_empty'] . '
 WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
   AND (user_agent IS NULL OR user_agent = \'\')');

    // Mot-clé bot dans le User-Agent (même liste que ip_location_is_bot_ua()), ou UA qui
    // annonce lui-même un crawler (même détection que ip_location_ua_declares_crawler())
    $ua_keyword_where = [];
    foreach (ip_location_bot_ua_keywords() as $kw) {
        $ua_keyword_where[] = "user_agent LIKE '%" . pwg_db_real_escape_string($kw) . "%'";
    }
    $ua_keyword_where[] = "user_agent LIKE '%://%'";
    $ua_keyword_where[] = "user_agent LIKE '%@%'";
    $ua_keyword_where[] = "(user_agent LIKE '%compatible;%' AND user_agent NOT LIKE '%msie%' AND user_agent NOT LIKE '%trident%')";
    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log
   SET bot_score = bot_score + ' . (int)$conf['ip_location_score_ua_keyword'] . '
 WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
   AND (' . implode(' OR ', $ua_keyword_where) . ')');

    // Navigateur manifestement obsolète (même détection que ip_location_is_outdated_browser()).
    // Calculé côté PHP sur les UA distincts de la fenêtre plutôt qu'en SQL, pour ne pas
    // dépendre de fonctions d'extraction regex spécifiques à une version de MariaDB.
    $result = pwg_query('
SELECT DISTINCT user_agent
  FROM ' . $prefixeTable . 'ip_location_log
 WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
   AND user_agent IS NOT NULL AND user_agent != \'\'');
    $outdated_uas = [];
    while ($row = pwg_db_fetch_row($result)) {
        if (ip_location_is_outdated_browser($row[0])) {
            $outdated_uas[] = $row[0];
        }
    }
    if (!empty($outdated_uas)) {
        $outdated_where = implode(',', array_map(function ($ua) {
            return '\'' . pwg_db_real_escape_string($ua) . '\'';
        }, $outdated_uas));
        pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log
   SET bot_score = bot_score + ' . (int)$conf['ip_location_score_outdated_browser'] . '
 WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
   AND user_agent IN (' . $outdated_where . ')');
    }

    // Co-visitation (même détection que la mise à jour is_bot ci-dessus)
    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log t
JOIN (
    SELECT url, FLOOR(UNIX_TIMESTAMP(visit_date)/10) AS bucket
      FROM ' . $prefixeTable . 'ip_location_log
     WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
       AND (log_type IS NULL OR log_type != \'prefetch\')
     GROUP BY url, bucket
    HAVING COUNT(DISTINCT ip) >= 2
) g
  ON t.url = g.url
 AND FLOOR(UNIX_TIMESTAMP(t.visit_date)/10) = g.bucket
SET t.bot_score = t.bot_score + ' . (int)$conf['ip_location_score_covisit'] . '
WHERE t.visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY');

    // Rafale mono-IP (même détection que la mise à jour is_bot ci-dessus)
    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log t
JOIN (
    SELECT ip, url, FLOOR(UNIX_TIMESTAMP(visit_date)/10) AS bucket
      FROM ' . $prefixeTable . 'ip_location_log
     WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
       AND (log_type IS NULL OR log_type != \'prefetch\')
     GROUP BY ip, url, bucket
    HAVING COUNT(*) >= 3
) g
  ON t.ip = g.ip
 AND t.url = g.url
 AND FLOOR(UNIX_TIMESTAMP(t.visit_date)/10) = g.bucket
SET t.bot_score = t.bot_score + ' . (int)$conf['ip_location_score_burst'] . '
WHERE t.visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY');

    // Rafale multi-URL mono-IP (même détection que la mise à jour is_bot ci-dessus)
    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log t
JOIN (
    SELECT ip, FLOOR(UNIX_TIMESTAMP(visit_date)/30) AS bucket
      FROM ' . $prefixeTable . 'ip_location_log
     WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
       AND (log_type IS NULL OR log_type != \'prefetch\')
     GROUP BY ip, bucket
    HAVING COUNT(DISTINCT url) >= ' . $multi_url_threshold . '
) g
  ON t.ip = g.ip
 AND FLOOR(UNIX_TIMESTAMP(t.visit_date)/30) = g.bucket
SET t.bot_score = t.bot_score + ' . (int)$conf['ip_location_score_multi_url_burst'] . '
WHERE t.visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY');

    // User-Agent "figé" partagé par un nombre anormalement élevé d'IP distinctes : un vrai
    // navigateur populaire est réutilisé par de nombreux visiteurs différents, mais avec
    // un fort taux de retours (mêmes IP qui reviennent) ; un UA vu >= 20 fois sur la
    // fenêtre avec >= 90% d'IP distinctes (quasiment jamais deux fois la même IP) trahit
    // une petite bibliothèque d'UA figés recyclée par un pool de proxies résidentiels.
    // Calcul batch uniquement (GROUP BY sur plusieurs lignes), donc absent du chemin chaud
    // de ip_location_log_visit().
    // Contribue au score SEULEMENT (plus de mise à jour is_bot depuis v2.5.5) et avec un
    // poids inférieur aux seuils usuels : l'UA figé du pool peut être celui du navigateur
    // le plus répandu du moment (ex. Chrome 151/Windows), donc ce signal seul marquait —
    // et pouvait auto-bloquer — tous les vrais visiteurs utilisant ce navigateur.
    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log t
JOIN (
    SELECT user_agent
      FROM ' . $prefixeTable . 'ip_location_log
     WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
       AND (log_type IS NULL OR log_type != \'prefetch\')
       AND user_agent IS NOT NULL AND user_agent != \'\'
     GROUP BY user_agent
    HAVING COUNT(*) >= 20 AND COUNT(DISTINCT ip) / COUNT(*) >= 0.9
) g ON t.user_agent = g.user_agent
SET t.bot_score = t.bot_score + ' . (int)$conf['ip_location_score_shared_ua'] . '
WHERE t.visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY');

    // Aucune trace du logger JS PhotoSwipe (log_type='js') malgré >= 2 accès sur la
    // fenêtre : un scraper simple n'exécute jamais de JavaScript. Uniquement si le logger
    // fonctionne sur ce site (au moins une ligne 'js' en base) : sinon (thème sans
    // PhotoSwipe, ancienne version du plugin) tous les humains seraient pénalisés.
    $r_js = pwg_query('SELECT 1 FROM ' . $prefixeTable . 'ip_location_log WHERE log_type = \'js\' LIMIT 1');
    if (pwg_db_num_rows($r_js) > 0) {
        pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log t
JOIN (
    SELECT ip
      FROM ' . $prefixeTable . 'ip_location_log
     WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
       AND (log_type IS NULL OR log_type != \'prefetch\')
     GROUP BY ip
    HAVING COUNT(*) >= 2 AND SUM(log_type = \'js\') = 0
) g ON t.ip = g.ip
SET t.bot_score = t.bot_score + ' . (int)$conf['ip_location_score_no_js'] . '
WHERE t.visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY');
    }

    // Récidive : += poids × nombre d'autres lignes déjà suspectes (score > 0) de la
    // même IP sur la fenêtre. Formule linéaire, sans cas particulier pour la 1ère
    // occurrence. 0 = désactivé (même convention que max_records).
    $recurrence_weight = (int)$conf['ip_location_recurrence'];
    if ($recurrence_weight > 0) {
        pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log t
JOIN (
    SELECT ip, COUNT(*) AS suspicious_count
      FROM ' . $prefixeTable . 'ip_location_log
     WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
       AND bot_score > 0
     GROUP BY ip
) g ON t.ip = g.ip
SET t.bot_score = t.bot_score + ' . $recurrence_weight . ' * GREATEST(g.suspicious_count - 1, 0)
WHERE t.visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
  AND t.bot_score > 0');
    }

    // Faux robots (v2.6.2) : UA d'un moteur vérifiable, mais IP démasquée par la
    // vérification DNS (ip_location_robot_check.verified = 0) — usurpation délibérée,
    // signal fort : is_bot + ip_location_score_bot_spoof.
    pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log
   SET is_bot = 1, bot_score = bot_score + ' . (int)$conf['ip_location_score_bot_spoof'] . '
 WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
   AND ip IN (SELECT ip FROM ' . $prefixeTable . 'ip_location_robot_check WHERE verified = 0)');

    // Robots autorisés et authentiques (bloc "Robots d'indexation") : score forcé à 0,
    // jamais éligibles au blocage automatique.
    $allowed_robot_sql = ip_location_allowed_robot_sql($prefixeTable);
    if ($allowed_robot_sql !== '0') {
        pwg_query('
UPDATE ' . $prefixeTable . 'ip_location_log
   SET bot_score = 0
 WHERE visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY
   AND ' . $allowed_robot_sql);
    }

    ip_location_auto_block_bots();
}

/**
 * Extrait le préfixe "A.B." d'une ligne de blocklist au format "A.B.0.0/16" — seul
 * format de plage jamais généré par ce plugin (bouton "Ajouter /16", cf. JS de
 * admin.tpl/config.tpl). Retourne null si $ip n'est pas dans ce format (IP exacte).
 */
function ip_location_manual_range_prefix($ip)
{
    if (preg_match('/^(\d+\.\d+)\.0\.0\/16$/', $ip, $m)) {
        return $m[1] . '.';
    }
    return null;
}

/**
 * IP actuellement éligibles au blocage automatique selon le seuil de score courant
 * d'une config donnée, en excluant la liste blanche de bots
 * légitimes et la whitelist IP du plugin. Fenêtre glissante de 7 jours, comme le
 * reste de la classification. Retourne un tableau de ['ip', 'country', 'city'].
 *
 * Exclut aussi, pour une IP retirée manuellement du .htaccess (origin='exempt' dans
 * ip_location_blocklist, cf. admin.php:unblock_ip), les visites antérieures au retrait :
 * bot_score/is_bot étant recalculés depuis zéro à chaque passage de
 * ip_location_classify_recent() (jamais juste incrémentés), un simple retrait de la
 * blocklist serait sinon annulé dès le classify_recent() suivant — déclenché par ce
 * même rechargement de page — puisque les mêmes vieilles preuves (rafale, absence JS...)
 * restent dans la fenêtre de 7 jours. Seule une NOUVELLE visite suspecte, postérieure
 * au retrait, peut refaire qualifier l'IP.
 */
function ip_location_get_bot_candidates($plugin_conf, $recent_only = false)
{
    global $prefixeTable, $conf;

    // Même fenêtre glissante que ip_location_classify_recent() (surchargeable via
    // $conf['ip_location_classify_window_days'], défaut 7).
    $window_days = max(1, (int)$conf['ip_location_classify_window_days']);

    $where = ['t.visit_date >= NOW() - INTERVAL ' . $window_days . ' DAY'];

    // $recent_only (ajout de nouvelles entrées) : seules les IP ayant une ligne suspecte
    // récente ($conf['ip_location_auto_block_recent_hours'], défaut 24) — bloquer une IP
    // vue une seule fois il y a plusieurs jours ne sert à rien (proxies résidentiels à
    // usage unique) ; sans ce filtre, la 1ère activation posait d'un coup toute la
    // fenêtre de 7 j (8 853 IP constatées en v2.5.4). Pas appliqué par la réconciliation
    // (ip_location_reconcile_auto_blocks()), qui ne doit pas libérer une entrée juste
    // parce que son IP n'est plus revenue.
    if ($recent_only) {
        $recent_hours = max(1, (int)$conf['ip_location_auto_block_recent_hours']);
        $where[] = 't.visit_date >= NOW() - INTERVAL ' . $recent_hours . ' HOUR';
        // "À partir du moment" (v2.6.1) : à l'activation (ou réactivation) du blocage auto,
        // seule l'activité suspecte postérieure compte — l'historique reste l'historique.
        if (!empty($plugin_conf['bot_block_enabled_at'])) {
            $where[] = 't.visit_date >= \'' . pwg_db_real_escape_string($plugin_conf['bot_block_enabled_at']) . '\'';
        }
    }
    // Toujours sur le score (le mode "is_bot direct" a été supprimé en v2.5.5 : il
    // revenait à un seuil de ~10, bloquait sur un signal isolé peu fiable comme la
    // co-visitation, et is_bot n'est jamais remis à 0 après correction d'une règle).
    $where[] = 't.bot_score >= ' . (int)$plugin_conf['bot_block_score_threshold'];

    // Robots autorisés et authentiques : jamais éligibles (déjà exclus via bot_score=0,
    // revérifié ici par sécurité).
    $allowed_robot_sql = ip_location_allowed_robot_sql($prefixeTable, 't.user_agent', 't.ip');
    if ($allowed_robot_sql !== '0') {
        $where[] = 'NOT ' . $allowed_robot_sql;
    }

    // Liste blanche d'IPs du plugin : revérifiée ici (pas seulement au moment du
    // log), au cas où l'IP aurait été whitelistée après avoir généré des lignes
    // suspectes.
    $ip_whitelist = array_filter(array_map('trim', explode("\n", $plugin_conf['whitelist'])));
    foreach ($ip_whitelist as $wip) {
        $where[] = "t.ip != '" . pwg_db_real_escape_string($wip) . "'";
    }

    // Plages manuelles existantes ("A.B.0.0/16", seul format de plage généré par ce
    // plugin) : les IP qu'elles couvrent ne doivent jamais (re)devenir candidates au
    // blocage auto — un blocage manuel plus large prévaut toujours, sinon une entrée
    // 'auto' redondante réapparaîtrait au prochain passage de classification.
    $result_ranges = pwg_query("SELECT ip FROM {$prefixeTable}ip_location_blocklist WHERE origin = 'manuel' AND ip LIKE '%.0.0/16'");
    while ($rr = pwg_db_fetch_row($result_ranges)) {
        $prefix = ip_location_manual_range_prefix($rr[0]);
        if ($prefix !== null) {
            $where[] = "t.ip NOT LIKE '" . pwg_db_real_escape_string($prefix) . "%'";
        }
    }

    // Exemption suite à un retrait manuel (voir docblock) : ignorer les visites
    // antérieures ou égales au retrait pour cette IP.
    $where[] = '(x.blocked_at IS NULL OR t.visit_date > x.blocked_at)';

    $result = pwg_query('
SELECT t.ip, MAX(t.country) AS country, MAX(t.city) AS city
  FROM ' . $prefixeTable . 'ip_location_log t
  LEFT JOIN ' . $prefixeTable . 'ip_location_blocklist x
    ON x.ip = t.ip AND x.origin = \'exempt\'
 WHERE ' . implode(' AND ', $where) . '
 GROUP BY t.ip');

    $candidates = [];
    while ($row = pwg_db_fetch_assoc($result)) {
        // Jamais d'IP privée/réservée (poste du réseau local, cf. ip_location_is_public_ip()) :
        // ce n'est pas un bot venu d'Internet, et une fois bloquée elle ne pourrait même plus
        // afficher la page de connexion depuis le réseau local (garde init sur tous les
        // scripts). Incident réel en v2.5.5 : des tests curl depuis le poste de dev l'ont
        // fait auto-bloquer. Exclues aussi de la réconciliation, qui libère donc une telle
        // entrée déjà posée dès le prochain enregistrement du formulaire.
        if (!ip_location_is_public_ip($row['ip'])) {
            continue;
        }
        $candidates[] = $row;
    }
    return $candidates;
}

/**
 * Blocage automatique par score (4ème levier, optionnel) : ajoute au blocklist les IP
 * qui franchissent le seuil de score configuré, avec une expiration — jamais
 * permanent, jamais de plage /16, seulement
 * l'IP exacte, et seulement si elle s'est montrée suspecte récemment. Purge aussi les
 * entrées auto expirées. Blocage appliqué en PHP (ip_location_blocklist_guard()), pas
 * via le .htaccess.
 * Appelée uniquement depuis ip_location_classify_recent() — via admin.php (à chaque
 * chargement) ou via ip_location_log_visit() (trafic public, au plus 1x/jour).
 */
function ip_location_auto_block_bots()
{
    global $prefixeTable, $conf;

    $plugin_conf = ip_location_get_conf();

    // Purge des entrées auto expirées, indépendamment de l'état de l'interrupteur
    // (une entrée déjà posée doit expirer même si la fonctionnalité a été désactivée depuis)
    pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_blocklist
  WHERE origin = \'auto\' AND expires_at IS NOT NULL AND expires_at <= NOW()');

    // Plus de prérequis .htaccess depuis v2.5.5 : les entrées 'auto' sont appliquées en
    // PHP par ip_location_blocklist_guard() et ne sont jamais écrites dans le .htaccess
    // (donc pas de réécriture du .htaccess ici, ni à l'ajout ni à la purge).
    if ($plugin_conf['bot_block_enabled'] === '1') {
        $candidates = ip_location_get_bot_candidates($plugin_conf, true);

        if (!empty($candidates)) {
            $ttl_days = (int)$conf['ip_location_auto_block_ttl_days'];
            $values = [];
            foreach ($candidates as $c) {
                $values[] = '(\'' . pwg_db_real_escape_string($c['ip']) . '\', \''
                    . pwg_db_real_escape_string($c['country']) . '\', \''
                    . pwg_db_real_escape_string($c['city']) . '\', NOW(), \'auto\', '
                    . 'NOW() + INTERVAL ' . $ttl_days . ' DAY)';
            }
            // Une IP déjà présente avec origin 'manuel' ou 'auto' n'est jamais écrasée —
            // un blocage manuel permanent ne doit jamais se retrouver avec une expiration
            // ajoutée après coup. Seule une IP 'exempt' (retrait manuel, cf.
            // ip_location_get_bot_candidates()) peut être promue en 'auto' : c'est
            // justement le signe qu'une NOUVELLE preuve est apparue après ce retrait.
            pwg_query('
INSERT INTO ' . $prefixeTable . 'ip_location_blocklist
  (ip, country, city, blocked_at, origin, expires_at)
  VALUES ' . implode(',', $values) . '
  ON DUPLICATE KEY UPDATE
    blocked_at = IF(origin = \'exempt\', VALUES(blocked_at), blocked_at),
    country    = IF(origin = \'exempt\', VALUES(country), country),
    city       = IF(origin = \'exempt\', VALUES(city), city),
    expires_at = IF(origin = \'exempt\', VALUES(expires_at), expires_at),
    origin     = IF(origin = \'exempt\', VALUES(origin), origin)');
        }
    }
}

/**
 * Retire de la blocklist les entrées 'auto' qui ne correspondent plus aux réglages
 * donnés (seuil de score, allowlist bots, whitelist IP) — appelée lors de
 * l'enregistrement explicite du formulaire de blocage bot (admin.php) et une fois par
 * la migration v2.5.5 (suppression du mode is_bot), pour que relever le seuil libère
 * aussitôt les IP qui n'y satisfont
 * plus, sans attendre leur expiration TTL. Les blocages 'manuel' ne sont jamais
 * concernés. La purge périodique via ip_location_auto_block_bots() continue elle de
 * ne dépendre que du TTL (y compris si la fonctionnalité est désactivée entre-temps),
 * comportement volontaire à ne pas changer ici.
 * $plugin_conf doit être la config fraîchement enregistrée (pas re-lue via
 * ip_location_get_conf(), dont le cache statique renverrait l'ancienne valeur dans
 * la même requête).
 */
function ip_location_reconcile_auto_blocks($plugin_conf)
{
    global $prefixeTable;

    if ($plugin_conf['bot_block_enabled'] !== '1') {
        return false;
    }

    $candidate_ips = array_column(ip_location_get_bot_candidates($plugin_conf), 'ip');

    $where = "origin = 'auto'";
    if (!empty($candidate_ips)) {
        $keep = array_map(function ($ip) {
            return '\'' . pwg_db_real_escape_string($ip) . '\'';
        }, $candidate_ips);
        $where .= ' AND ip NOT IN (' . implode(',', $keep) . ')';
    }

    $r = pwg_query('SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_blocklist WHERE ' . $where);
    list($count) = pwg_db_fetch_row($r);
    if ($count > 0) {
        pwg_query('DELETE FROM ' . $prefixeTable . 'ip_location_blocklist WHERE ' . $where);
        return true;
    }
    return false;
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
    $ua_lower = strtolower($user_agent);
    foreach (ip_location_bot_ua_keywords() as $kw) {
        if (strpos($ua_lower, $kw) !== false) {
            return true;
        }
    }

    if (ip_location_ua_declares_crawler($user_agent) || ip_location_is_outdated_browser($user_agent)) {
        return true;
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

    // Rafale mono-IP : même IP, même URL, déjà vue >= 2 fois dans les 10 dernières
    // secondes (avec la requête en cours, cela fait >= 3 au total)
    $result = pwg_query('
SELECT COUNT(*) FROM ' . $prefixeTable . 'ip_location_log
  WHERE url = \'' . pwg_db_real_escape_string($url) . '\'
    AND ip = \'' . pwg_db_real_escape_string($ip) . '\'
    AND visit_date >= NOW() - INTERVAL 10 SECOND');
    list($count) = pwg_db_fetch_row($result);
    if ($count >= 2) {
        return true;
    }

    return false;
}

