<div class="ipl-help">

  <h4>{'Présentation'|@translate}</h4>
  <p>{'Le plugin IP Location enregistre les visites des visiteurs anonymes (guests) avec leur géolocalisation, détecte les bots et offre plusieurs mécanismes de blocage.'|@translate}</p>

  <h4>{'Enregistrement des visites'|@translate}</h4>
  <ul>
    <li>{'Seuls les visiteurs non connectés (guests) sont enregistrés.'|@translate}</li>
    <li>{'Géolocalisation via fallback multi-providers'|@translate}</li>
    <li>{'Les pages enregistrées sont la page d\'accueil et les pages photos.'|@translate}</li>
  </ul>

  <h4>{'Détection des bots'|@translate}</h4>
  <ul>
    <li><strong>{'User-Agent vide'|@translate}</strong> {'→ marqué bot.'|@translate}</li>
    <li><strong>{'Mots-clés dans le User-Agent'|@translate}</strong> {'→ bot, crawler, spider, scraper, curl, wget, python, etc.'|@translate}</li>
    <li><strong>{'Visites synchronisées'|@translate}</strong> {'→ si la même URL est visitée par 2 IPs différentes dans les 10 secondes, les deux entrées sont marquées bots (marquage rétroactif).'|@translate}</li>
  </ul>

  <h4>{'Blocage par pays'|@translate}</h4>
  <ul>
    <li>{'Saisir les codes ISO des pays à bloquer (ex : US,CN,EG).'|@translate}</li>
    <li>{'Le blocage se fait en PHP après géolocalisation : Apache charge quand même la page avant de renvoyer un 403.'|@translate}</li>
    <li>{'Les visites bloquées sont quand même enregistrées dans le journal (badge BLOQUÉ).'|@translate}</li>
  </ul>

  <h4>{'Liste blanche d\'IPs'|@translate}</h4>
  <ul>
    <li>{'Saisir une IP par ligne dans le champ "IPs toujours autorisées" de la configuration.'|@translate}</li>
    <li>{'Ces IPs ne seront jamais bloquées par le blocage pays.'|@translate}</li>
    <li>{'Il est également impossible de les ajouter à la blocklist .htaccess : un message d\'erreur s\'affiche si vous tentez de le faire.'|@translate}</li>
    <li>{'Utile pour protéger votre propre IP afin de ne pas vous bloquer accidentellement.'|@translate}</li>
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

  <h4>{'Vidage automatique'|@translate}</h4>
  <ul>
    <li>{'À chaque visite enregistrée, si le nombre d\'entrées dépasse le seuil configuré, les plus anciennes sont supprimées.'|@translate}</li>
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
