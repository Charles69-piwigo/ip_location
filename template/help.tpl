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
    <li>{'Si la géolocalisation échoue (providers indisponibles), le mode "Autoriser" (par défaut) laisse passer le téléchargement mais l\'enregistre quand même dans le journal, afin de mesurer la fréquence réelle des échecs. Le mode "Bloquer" refuse par prudence dans ce cas.'|@translate}</li>
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
    <li><strong>{'Limite connue'|@translate}</strong> {'→ le filtre ne couvre pas les téléchargements par format alternatif (paramètre "format" de action.php, nécessite l\'option Piwigo "enable_formats"). Sur cette installation, cette option est désactivée, donc sans impact ; à revoir si elle est activée un jour.'|@translate}</li>
  </ul>

  <h4>{'Vidage automatique'|@translate}</h4>
  <ul>
    <li>{'À chaque accès enregistré, si le nombre d\'entrées dépasse le seuil configuré, les plus anciennes sont supprimées.'|@translate}</li>
    <li>{'Mettre 0 pour désactiver le vidage automatique.'|@translate}</li>
    <li>{'La purge manuelle (section Gestion de l\'historique) permet de supprimer les entrées antérieures à une date donnée.'|@translate}</li>
  </ul>

  <h4>{'Filtres du journal'|@translate}</h4>
  <ul>
    <li><strong>Tous</strong> {'→ toutes les entrées.'|@translate}</li>
    <li><strong>Normal</strong> {'→ visiteurs humains non bloqués.'|@translate}</li>
    <li><strong>Bots</strong> {'→ entrées détectées comme bots.'|@translate}</li>
    <li><strong>Bloqués</strong> {'→ entrées bloquées par le blocage pays.'|@translate}</li>
  </ul>

</div>
