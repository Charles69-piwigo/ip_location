<?php
// Protection du dossier (v2.9.4) : pas de liste de son contenu, renvoi au dossier parent
// (motif habituel des plugins Piwigo).
$url = '../';
header('Request-URI: ' . $url);
header('Content-Location: ' . $url);
header('Location: ' . $url);
exit();
