<div class="ipl-help">

  <h4>{'Présentation'|@translate}</h4>
  <p>{'IP Location est d\'abord un observateur : il enregistre chaque accès des visiteurs non connectés, avec leur géolocalisation, et repère les robots. Il peut aussi bloquer, grâce à plusieurs leviers indépendants que vous activez un par un. Tous leviers coupés, il ne bloque rien.'|@translate}</p>
  <p>{'L\'onglet Configuration a deux sous-onglets : Réglages (un bloc par fonction, chacun avec son interrupteur et son bouton Enregistrer) et Journal des accès. En haut des réglages, le bandeau « Mode actuel » indique si le plugin ne fait qu\'observer ou s\'il bloque, et quels leviers sont actifs.'|@translate}</p>
  <h4>{'Ce qui est enregistré'|@translate}</h4>
  <ul>
    <li>{'Seuls les visiteurs non connectés sont enregistrés ; un administrateur connecté n\'apparaît jamais.'|@translate}</li>
    <li>{'Pages de la galerie (accueil, albums, photos), photos vues dans le diaporama, tentatives de téléchargement d\'originaux, et accès refusés par le plugin.'|@translate}</li>
    <li>{'Géolocalisation par plusieurs fournisseurs en cascade, gardée en cache 30 jours. Les IP du réseau local ne sont pas géolocalisées.'|@translate}</li>
    <li>{'Un refus répété (même IP, même motif) n\'est enregistré qu\'une fois toutes les 10 minutes : la requête est refusée à chaque fois, mais un robot qui insiste ne remplit pas le journal.'|@translate}</li>
    <li>{'Les requêtes de pré-lecture du navigateur (la photo suivante chargée à l\'avance par le thème) sont enregistrées mais ignorées par les règles de détection et par le décompte des visites.'|@translate}</li>
  </ul>

  <h4>{'Observation'|@translate}</h4>
  <ul>
    <li><strong>{'Widget « Visiteurs »'|@translate}</strong> {'→ bouton public qui affiche les visites par pays sur la période choisie. Une visite compte si c\'est un accès humain, non bloqué, à un album ou une photo, avec un passage sur l\'accueil à ± 30 minutes. Le détail des visites comptées est consultable dans le bloc.'|@translate}</li>
    <li><strong>{'Conservation du journal'|@translate}</strong> {'→ au-delà du nombre maximal d\'accès, les plus anciens sont supprimés automatiquement (0 = sans limite). Le même bloc permet de supprimer les accès antérieurs à une date et de vider le cache de géolocalisation.'|@translate}</li>
    <li><strong>{'IP jamais bloquées'|@translate}</strong> {'→ liste blanche prioritaire sur tous les leviers : ces IP ne sont jamais bloquées ni enregistrées. Utile pour votre propre IP. Les IP du réseau local ne sont de toute façon jamais bloquées automatiquement.'|@translate}</li>
  </ul>

  <h4>{'Détection des bots et score de suspicion'|@translate}</h4>
  <p>{'Chaque accès peut être marqué « bot » et reçoit un score de suspicion (colonne Score du journal), à partir de ces signaux :'|@translate}</p>
  <ul>
    <li><strong>{'User-Agent vide ou suspect'|@translate}</strong> {'→ mots-clés (bot, crawler, spider, curl, python…), URL ou adresse de contact dans le User-Agent, navigateur manifestement obsolète (Chrome 109, dernière version pour Windows 7/8, est épargné).'|@translate}</li>
    <li><strong>{'Co-visitation'|@translate}</strong> {'→ la même URL visitée par 2 IP différentes dans les 10 secondes.'|@translate}</li>
    <li><strong>{'Rafale mono-IP'|@translate}</strong> {'→ la même IP demande au moins 3 fois la même URL en 10 secondes.'|@translate}</li>
    <li><strong>{'Rafale multi-URL'|@translate}</strong> {'→ la même IP parcourt au moins 20 URL différentes en 30 secondes (un visiteur qui clique « suivant » en voit rarement plus de 12).'|@translate}</li>
    <li><strong>{'User-Agent figé partagé'|@translate}</strong> {'→ un même User-Agent vu au moins 20 fois avec 90 % d\'IP différentes : signature d\'un réseau de proxies. Ce signal n\'augmente que le score, sans marquer « bot », car l\'UA recyclé peut être celui du navigateur le plus répandu.'|@translate}</li>
    <li><strong>{'Faux robot'|@translate}</strong> {'→ User-Agent d\'un moteur connu (Googlebot…) depuis une IP qui ne lui appartient pas, d\'après la vérification DNS.'|@translate}</li>
  </ul>

  <p>{'Le marquage « bot » sert au classement du journal et aux statistiques ; seul le score peut déclencher un blocage automatique. Les poids de chaque signal et les seuils sont réglables dans local/config/config.inc.php (voir PARAMETRES.md).'|@translate}</p>
  <h4>{'Leviers de blocage'|@translate}</h4>
  <p>{'Chaque levier n\'agit — et n\'alimente la catégorie « Bloqués » du journal — que si son interrupteur est allumé. Un accès refusé reçoit une page 403 et apparaît dans le journal avec son motif.'|@translate}</p>
  <ul>
    <li><strong>{'Robots d\'indexation'|@translate}</strong> {'→ liste des robots connus : moteurs de recherche et aperçus de partage autorisés, robots d\'IA (GPTBot, ClaudeBot…) bloqués par défaut. Un robot autorisé passe tous les autres leviers ; son IP est vérifiée par DNS quand le moteur publie une méthode (Google, Bing, Apple, Yandex, Baidu). Un robot bloqué est refusé dès qu\'il se présente. Un robot absent de la liste est traité par le score. Interrupteur coupé : les robots sont traités comme n\'importe quel visiteur.'|@translate}</li>
    <li><strong>{'Blocage par IP'|@translate}</strong> {'→ IP ou plages /16 bloquées à la main, depuis ce bloc ou le menu Actions du journal. Elles sont écrites dans le .htaccess (Apache refuse alors la requête avant PHP) et le plugin les applique aussi lui-même, ce qui les rend efficaces même si le .htaccess est ignoré (nginx). La section du .htaccess est balisée # BEGIN ip_location / # END ip_location.'|@translate}</li>
    <li><strong>{'Blocage par pays'|@translate}</strong> {'→ refuse les pages aux visiteurs des pays listés, après géolocalisation. Attention : bloquer les États-Unis bloquerait Googlebot si le bloc Robots était coupé ; un avertissement s\'affiche alors.'|@translate}</li>
    <li><strong>{'Blocage par mot-clé d\'URL'|@translate}</strong> {'→ refuse toute URL contenant l\'un des mots (sans distinction de majuscules). Un mot court peut toucher des URL légitimes : « tag » bloque aussi les pages de tags, « list » les listes et calendriers.'|@translate}</li>
    <li><strong>{'Blocage automatique par score'|@translate}</strong> {'→ bloque 14 jours chaque IP dont le score atteint le seuil (curseur 10-90, à régler en observant la colonne Score). Jamais de plage, jamais d\'IP du réseau local. Seule l\'activité suspecte des dernières 24 heures, et postérieure à l\'activation, est prise en compte. Ces IP sont refusées par le plugin, jamais écrites dans le .htaccess. Le calcul a lieu à l\'ouverture de l\'admin, ou au plus toutes les 4 heures via le trafic public.'|@translate}</li>
    <li><strong>{'Débloquer une IP bloquée automatiquement'|@translate}</strong> {'→ elle est exemptée : ses anciennes visites ne comptent plus pour un nouveau blocage (badge « Exempté » sur ses lignes). Seule une nouvelle activité suspecte peut la faire rebloquer. « Bloquer l\'IP » sur une IP bloquée automatiquement la rend permanente.'|@translate}</li>
    <li><strong>{'Filtre pays sur les téléchargements'|@translate}</strong> {'→ liste blanche : seuls les pays listés peuvent télécharger les originaux (si les invités en ont la permission). Si le pays est inconnu, le mode recommandé « refuser » bloque par prudence, car un afflux de robots sature justement les fournisseurs de géolocalisation. Limite : les téléchargements par format alternatif (option « enable_formats » de Piwigo) ne sont pas couverts.'|@translate}</li>
  </ul>

  <h4>{'Journal des accès'|@translate}</h4>
  <p>{'Le journal reflète l\'état actuel. Les compteurs en haut servent de filtre :'|@translate}</p>
  <ul>
    <li><strong>{'Normal'|@translate}</strong> {'→ visiteurs humains, servis, dont l\'IP n\'est pas bloquée aujourd\'hui.'|@translate}</li>
    <li><strong>{'Bots non bloqués'|@translate}</strong> {'→ accès marqués « bot », servis, dont l\'IP ou le robot n\'est pas bloqué aujourd\'hui ; « dont robots autorisés » isole les robots de la liste.'|@translate}</li>
    <li><strong>{'Bloqués'|@translate}</strong> {'→ accès refusés, avec leur motif (pays, mot-clé, liste IP, blocage auto, robot, téléchargement), et tous les accès des IP et robots actuellement bloqués — même servis avant le blocage (badge « Bloquée depuis le … » ou « Robot bloqué »). Seuls les leviers allumés comptent ; bloquer un pays ou un mot-clé ne reclasse pas les anciens accès.'|@translate}</li>
  </ul>

  <p>{'Chaque ligne a un menu Actions : filtrer sur cette IP, bloquer l\'IP ou sa plage /16, débloquer. L\'URL s\'ouvre dans un nouvel onglet ; étant connecté, vous voyez la page même si l\'accès d\'origine avait été refusé.'|@translate}</p>
  <h4>{'Statistiques dynamiques'|@translate}</h4>
  <ul>
    <li>{'Courbes combinant type d\'accès, pays, mots-clés bloqués et IP, sur la période de votre choix, y compris « Tous les logs ». Granularité automatique : jour jusqu\'à 31 jours, semaine jusqu\'à 1 an, mois au-delà.'|@translate}</li>
    <li>{'Chaque série peut aller sur l\'axe gauche ou droit, pour comparer une courbe de fort volume à une courbe de faible amplitude.'|@translate}</li>
    <li>{'3 préréglages prêts à l\'emploi et 4 emplacements personnels ; export en CSV, PNG ou JSON.'|@translate}</li>
  </ul>

</div>
