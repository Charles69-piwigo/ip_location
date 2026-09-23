<div class="ipl-help">

  <h4>{'Présentation'|@translate}</h4>
  <p>{'Le plugin IP Location enregistre les accès des visiteurs anonymes (guests) avec leur géolocalisation, détecte les bots et offre plusieurs mécanismes de blocage.'|@translate}</p>

  <h4>{'Statistiques des visites'|@translate}</h4>
  <p>{'Sont comptabilisées comme une visite chaque accès à un album et/ou une photo, à condition qu\'il y ait une connexion à l\'accueil du site dans un délai de ± une demi-heure.'|@translate}</p>

  <h4>{'Enregistrement des accès'|@translate}</h4>
  <ul>
    <li>{'Seuls les visiteurs non connectés (guests) sont enregistrés.'|@translate}</li>
    <li>{'Géolocalisation via fallback multi-providers'|@translate}</li>
    <li>{'Les pages enregistrées sont la page d\'accueil et les pages photos.'|@translate}</li>
  </ul>

  <h4>{'Détection des bots'|@translate}</h4>
  <ul>
    <li><strong>{'User-Agent vide'|@translate}</strong> {'→ marqué bot.'|@translate}</li>
    <li><strong>{'Mots-clés dans le User-Agent'|@translate}</strong> {'→ bot, crawler, spider, scraper, curl, wget, python, etc.'|@translate}</li>
    <li><strong>{'Accès synchronisés'|@translate}</strong> {'→ si la même URL est visitée par 2 IPs différentes dans les 10 secondes, les deux entrées sont marquées bots (marquage rétroactif).'|@translate}</li>
    <li><strong>{'Rafale mono-IP'|@translate}</strong> {'→ si la même IP visite la même URL au moins 3 fois dans les 10 secondes, les entrées sont marquées bots (marquage rétroactif).'|@translate}</li>
    <li><strong>{'Rafale multi-URL mono-IP'|@translate}</strong> {'→ si la même IP visite au moins 10 URL différentes en moins de 30 secondes, les entrées sont marquées bots (marquage rétroactif) — typique d\'un scraper qui parcourt le catalogue à grande vitesse.'|@translate}</li>
    <li><strong>{'Navigateur obsolète'|@translate}</strong> {'→ un User-Agent annonçant un Firefox, un Chrome ou un iOS vieux de plusieurs années (ex. Firefox 47, sorti en 2016) est marqué bot : un vrai visiteur n\'a normalement aucune raison de tourner sur un navigateur aussi ancien. Chrome 109, dernière version pour Windows 7/8, est épargné.'|@translate}</li>
    <li><strong>{'User-Agent annonçant un crawler'|@translate}</strong> {'→ un User-Agent qui contient une URL, une adresse de contact ou un motif « compatible; » (jamais présents dans un vrai navigateur) est marqué bot, même sans le mot « bot ».'|@translate}</li>
    <li><strong>{'User-Agent figé partagé par de nombreuses IP'|@translate}</strong> {'→ si un même User-Agent est vu au moins 20 fois avec au moins 90% d\'IP distinctes (quasiment jamais deux fois la même IP), les entrées sont marquées bots (marquage rétroactif) : signature typique d\'un pool de proxies résidentiels qui recycle une petite bibliothèque de User-Agents figés.'|@translate}</li>
    <li><strong>{'Robots d\'indexation'|@translate}</strong> {'→ les robots connus (moteurs de recherche, aperçus de partage, robots d\'IA) sont gérés dans le bloc « Robots d\'indexation » : un robot autorisé passe tous les leviers de blocage (son IP est vérifiée par DNS quand le moteur publie une méthode) ; un robot bloqué est refusé. Un faux robot (User-Agent de Googlebot depuis une IP qui n\'est pas à Google) est traité comme un visiteur ordinaire et son score augmente. Un robot absent de la liste est traité par le score.'|@translate}</li>
  </ul>

  <h4>{'Blocage par pays'|@translate}</h4>
  <ul>
    <li>{'Saisir les codes ISO des pays à bloquer (ex : US,CN,EG).'|@translate}</li>
    <li>{'Le blocage se fait en PHP après géolocalisation : Apache charge quand même la page avant de renvoyer un 403.'|@translate}</li>
    <li>{'Les accès bloqués sont quand même enregistrés dans le journal (badge BLOQUÉ).'|@translate}</li>
  </ul>

  <h4>{'Liste blanche d\'IPs'|@translate}</h4>
  <ul>
    <li>{'Saisir une IP par ligne dans le champ "IPs toujours autorisées" de la configuration.'|@translate}</li>
    <li>{'Ces IPs ne seront jamais bloquées par le blocage pays.'|@translate}</li>
    <li>{'Il est également impossible de les ajouter à la blocklist .htaccess : un message d\'erreur s\'affiche si vous tentez de le faire.'|@translate}</li>
    <li>{'Utile pour protéger votre propre IP afin de ne pas vous bloquer accidentellement.'|@translate}</li>
    <li>{'Si vous naviguez souvent depuis votre réseau local (IP privée du type 192.168.x.x), vous pouvez aussi l\'ajouter ici : la visite sort alors immédiatement (aucune géolocalisation, aucune ligne de journal), ce qui garde les statistiques propres.'|@translate}</li>
  </ul>

  <h4>{'Blocage .htaccess (blocklist IP)'|@translate}</h4>
  <ul>
    <li>{'Dans le journal, chaque ligne dispose de deux boutons :'|@translate}
      <ul>
        <li><strong>{'Ajouter IP'|@translate}</strong> {'→ ajoute l\'adresse IP complète (ex : 47.146.49.25).'|@translate}</li>
        <li><strong>{'Ajouter /16'|@translate}</strong> {'→ ajoute la plage complète au format CIDR (ex : 47.146.0.0/16), bloquant ainsi tout le sous-réseau — utile contre des bots qui changent régulièrement d\'IP dans la même plage.'|@translate}</li>
      </ul>
    </li>
    <li>{'Le blocage Apache est plus efficace que le blocage par pays : la requête est rejetée avant que PHP ne soit chargé.'|@translate}</li>
    <li>{'Le plugin applique aussi la blocklist lui-même (IP exactes et plages /16), sur toutes les pages PHP de Piwigo : le blocage reste effectif même si le .htaccess est ignoré par le serveur.'|@translate}</li>
    <li>{'Sous nginx pur (sans Apache en backend), le fichier .htaccess est ignoré et le blocage IP n\'a aucun effet. Les NAS Synology utilisent Apache en backend et sont compatibles.'|@translate}</li>
    <li>{'Décocher "Activer le blocage .htaccess" supprime la section du .htaccess sans vider la liste — utile pour observer sans bloquer.'|@translate}</li>
    <li>{'La section ajoutée dans le .htaccess est balisée'|@translate} <code># BEGIN ip_location</code> / <code># END ip_location</code>{'.'|@translate}</li>
  </ul>

  <h4>{'Filtre pays sur les téléchargements'|@translate}</h4>
  <ul>
    <li>{'S\'applique uniquement au téléchargement des originaux (et non aux miniatures ou pages), pour les invités ayant la permission de télécharger le HD.'|@translate}</li>
    <li>{'Fonctionne en liste blanche : seuls les pays listés sont autorisés à télécharger. Une liste vide désactive le filtre, même s\'il est coché comme activé.'|@translate}</li>
    <li>{'Le blocage se fait très tôt (évènement init), avant que Piwigo n\'enregistre l\'accès dans son historique : un téléchargement bloqué ne laisse donc aucune trace dans l\'historique standard de Piwigo.'|@translate}</li>
    <li>{'Conçu pour contrer les rafales de proxies résidentiels à usage unique (une IP différente à chaque tentative) : le blocage par IP ou par liste noire de pays serait inefficace dans ce cas, d\'où le choix d\'une liste blanche.'|@translate}</li>
    <li>{'Une IP présente dans la liste blanche du plugin n\'est jamais bloquée par ce filtre, quel que soit son pays.'|@translate}</li>
    <li>{'Si la géolocalisation échoue (providers indisponibles ou saturés), le mode "Bloquer" (par défaut) refuse le téléchargement par prudence — c\'est précisément ce qui se produit lors d\'un afflux de robots qui sature les fournisseurs de géolocalisation, il ne faut donc pas laisser passer dans ce cas. Le mode "Autoriser" laisse passer le téléchargement mais l\'enregistre quand même dans le journal, afin de mesurer la fréquence réelle des échecs.'|@translate}</li>
    <li>{'Ce chemin ignore volontairement le cache négatif de 2h utilisé ailleurs pour la performance : un échec de géolocalisation ne reste jamais figé pour une décision de blocage download, une nouvelle tentative re-résout toujours en direct.'|@translate}</li>
    <li>{'En mode "Bloquer", un visiteur légitime dont la géolocalisation échoue transitoirement se verra aussi refuser le téléchargement. Si besoin, ajoutez-le (ou ajoutez-vous) à la liste blanche d\'IPs, qui reste toujours prioritaire.'|@translate}</li>
    <li>{'Ces tentatives de téléchargement apparaissent dans le journal des accès comme des entrées normales.'|@translate}
      <pre style="background:#f6f6f6;padding:8px 10px;font-size:0.82em;overflow-x:auto;">SELECT DATE(visit_date) AS jour,
       SUM(is_blocked = 0) AS passes_fail_open,
       SUM(is_blocked = 1) AS bloques
FROM piwigo_ip_location_log
WHERE log_type = 'download'
GROUP BY DATE(visit_date)
ORDER BY jour DESC;</pre>
    </li>
    <li>{'Pistes de repli non implémentées ici, à envisager si les passages "Autoriser" s\'avèrent fréquents : détection de rafale (une même photo demandée par plusieurs IP en quelques secondes), ou retrait de la permission de téléchargement HD au groupe Invités.'|@translate}</li>
    <li>{'En mode "Bloquer" (par défaut), un afflux de robots qui sature les fournisseurs de géolocalisation entraîne aussi le blocage de vrais visiteurs arrivant au même moment — effet secondaire assumé, le blocage prime sur la disponibilité dans ce cas précis. Pistes non implémentées pour l\'atténuer : réordonner la cascade de providers pour ne pas dépendre d\'un seul en premier, ajouter un provider sans quota strict, ou à terme une base de géolocalisation locale (sans réseau, immunisée au flood, mais plus lourde à déployer).'|@translate}</li>
    <li><strong>{'Limite connue'|@translate}</strong> {'→ le filtre ne couvre pas les téléchargements par format alternatif (paramètre "format" de action.php, nécessite l\'option Piwigo "enable_formats"). Sur cette installation, cette option est désactivée, donc sans impact ; à revoir si elle est activée un jour.'|@translate}</li>
  </ul>

  <h4>{'Blocage automatique par score de suspicion bot'|@translate}</h4>
  <ul>
    <li>{'Optionnel et désactivé par défaut. Les IP bloquées par ce mécanisme sont refusées par le plugin lui-même et ne sont jamais écrites dans le .htaccess : ce sont le plus souvent des IP à usage unique, qui gonfleraient inutilement ce fichier (relu par Apache à chaque requête). Fonctionne donc aussi sans blocage .htaccess, y compris sous nginx.'|@translate}</li>
    <li>{'Seules les IP suspectes au cours des dernières 24 heures sont bloquées (réglable via ip_location_auto_block_recent_hours) : activer le mécanisme ne bloque pas rétroactivement toutes les IP des 7 derniers jours.'|@translate}</li>
    <li>{'Chaque accès reçoit un score (colonne « Score » du journal), calculé à partir de plusieurs signaux : User-Agent vide ou suspect, navigateur manifestement obsolète, User-Agent figé partagé par de nombreuses IP, co-visitation, rafale mono-IP, rafale multi-URL mono-IP, absence de trace du logger JavaScript du diaporama malgré plusieurs accès, et récidive (optionnelle, désactivée par défaut). Un bot connu et légitime (Googlebot, Bingbot, Slackbot…) obtient toujours un score de 0 et n\'est jamais bloqué par ce mécanisme.'|@translate}</li>
    <li>{'Le signal « User-Agent figé partagé » ne suffit pas à lui seul (poids 30 par défaut, sans marquage bot) : l\'UA recyclé par un réseau de proxies peut être celui du navigateur le plus répandu du moment, et ce signal frapperait alors tous les vrais visiteurs qui l\'utilisent.'|@translate}</li>
    <li>{'Les requêtes de pré-lecture du navigateur (la photo suivante chargée à l\'avance par le thème) sont journalisées mais ignorées par les règles de rafale et le décompte des visites : elles doublaient le nombre de pages d\'une navigation humaine rapide.'|@translate}</li>
    <li>{'Le blocage se déclenche au-delà d\'un seuil de score (curseur 10-90, à régler en observant la colonne Score du journal). Le marquage bot du journal (is_bot) sert au filtrage et aux statistiques, jamais au blocage : un seul signal isolé, parfois peu fiable comme la co-visitation, suffit à le poser.'|@translate}</li>
    <li>{'Contrairement à un blocage manuel, seule l\'IP exacte est ajoutée — jamais une plage /16 — pour limiter les dégâts d\'un éventuel faux positif.'|@translate}</li>
    <li>{'Une IP du réseau local (192.168.x, 10.x, 172.16 à 172.31.x) n\'est jamais bloquée automatiquement : ce n\'est pas un robot venu d\'Internet, et une fois bloquée elle ne pourrait même plus afficher la page de connexion.'|@translate}</li>
    <li>{'Le blocage automatique est toujours temporaire (14 jours par défaut) : une IP bloquée devient invisible au système de détection, il est donc impossible de vérifier comportementalement qu\'elle "s\'est calmée" pour la débloquer plus tôt. Un blocage ajouté manuellement, lui, reste permanent.'|@translate}</li>
    <li>{'Dans la Configuration, les IP bloquées manuellement sont toujours listées ; les IP auto-bloquées apparaissent dans une section dépliable séparée, avec leur date d\'expiration — pour ne pas noyer la liste manuelle sur les sites très ciblés. Cliquer sur "Ajouter IP" pour une IP déjà auto-bloquée la rend permanente.'|@translate}</li>
    <li>{'Le calcul, le blocage et la purge des entrées expirées se font en différé — pas en temps réel pendant la rafale elle-même — déclenchés soit au chargement de l\'onglet admin, soit au plus une fois toutes les 4 heures via le trafic public (réglable via ip_location_classify_interval_hours dans local/config/config.inc.php), pour que ça fonctionne même sans visite admin régulière.'|@translate}</li>
    <li>{'Les poids de chaque signal et la liste des bots légitimes exemptés sont réglables via local/config/config.inc.php (éditable depuis le plugin LocalFilesEditor), sans toucher au code.'|@translate}</li>
    <li>{'Cliquer sur "Retirer du .htaccess" pour une IP auto-bloquée l\'exempte durablement : ses anciennes visites ne compteront plus jamais pour un nouveau blocage automatique, même si son score affiché dans le journal reste au-dessus du seuil courant (badge « Exempté (score) » sur ces lignes, pour ne pas laisser croire à un bug). Seule une nouvelle activité suspecte, postérieure au retrait, peut refaire bloquer l\'IP.'|@translate}</li>
    <li>{'C\'est aussi ce qui explique qu\'en abaissant le seuil de score, une IP retirée manuellement ne réapparaisse pas dans la liste de blocage alors que d\'autres IP au score comparable y reviennent : contrairement à elles, ses anciennes visites ont été exemptées.'|@translate}</li>
  </ul>

  <h4>{'Vidage automatique'|@translate}</h4>
  <ul>
    <li>{'À chaque accès enregistré, si le nombre d\'entrées dépasse le seuil configuré, les plus anciennes sont supprimées.'|@translate}</li>
    <li>{'Mettre 0 pour désactiver le vidage automatique.'|@translate}</li>
    <li>{'La purge manuelle (section Gestion de l\'historique) permet de supprimer les entrées antérieures à une date donnée.'|@translate}</li>
  </ul>

  <h4>{'Statistiques dynamiques'|@translate}</h4>
  <ul>
    <li>{'Onglet Statistiques : construisez des courbes combinant type d\'accès, pays, mots-clés bloqués et IP, sur la période de votre choix, y compris « Tous les logs ».'|@translate}</li>
    <li>{'Granularité automatique : par jour jusqu\'à 31 jours de période, par semaine jusqu\'à 1 an, par mois au-delà (y compris pour une période personnalisée ou « Tous les logs »).'|@translate}</li>
    <li>{'Chaque série peut être affichée sur l\'axe gauche ou l\'axe droit, pour comparer une courbe de fort volume à une courbe de faible amplitude sans que celle-ci soit écrasée visuellement.'|@translate}</li>
    <li>{'La dernière période utilisée est mémorisée et proposée par défaut à la prochaine visite de l\'onglet.'|@translate}</li>
    <li>{'La couleur de fond du graphique se personnalise via un sélecteur de couleur et quelques raccourcis.'|@translate}</li>
    <li>{'3 préréglages prêts à l\'emploi, plus 4 emplacements personnalisables : sélectionnez un emplacement, construisez vos séries, puis enregistrez.'|@translate}</li>
    <li>{'Le graphique s\'exporte en CSV, image (PNG) ou données brutes (JSON) via le bouton Exporter.'|@translate}</li>
  </ul>

  <h4>{'Filtres du journal'|@translate}</h4>
  <ul>
    <li><strong>Tous</strong> {'→ toutes les entrées (= Normal + Bots non bloqués + Bloqués).'|@translate}</li>
    <li><strong>Normal</strong> {'→ visiteurs humains non bloqués.'|@translate}</li>
    <li><strong>Bots non bloqués</strong> {'→ entrées détectées comme bots, non bloquées (un bot bloqué compte dans « Bloqués », pas ici).'|@translate}</li>
    <li><strong>Bloqués</strong> {'→ accès réellement refusés, avec leur motif (pays, mot-clé, liste IP, blocage auto, téléchargement). Une visite servie avant la mise en liste de son IP garde sa catégorie d\'origine et porte seulement le badge « EN LISTE ». Chaque levier ne bloque que s\'il est activé : tous désactivés, le plugin ne fait qu\'observer.'|@translate}</li>
  </ul>

</div>
