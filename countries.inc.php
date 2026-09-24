<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

/**
 * Pays ISO 3166-1 alpha-2 (v2.7.1) : code => nom anglais, même forme que les noms
 * renvoyés par les fournisseurs de géolocalisation (et enregistrés dans le journal).
 * Utilisé par la liste déroulante du bloc « Blocage par pays ».
 */
function ip_location_iso_countries()
{
    return [
        'AD' => 'Andorra', 'AE' => 'United Arab Emirates', 'AF' => 'Afghanistan',
        'AG' => 'Antigua and Barbuda', 'AI' => 'Anguilla', 'AL' => 'Albania',
        'AM' => 'Armenia', 'AO' => 'Angola', 'AQ' => 'Antarctica', 'AR' => 'Argentina',
        'AS' => 'American Samoa', 'AT' => 'Austria', 'AU' => 'Australia', 'AW' => 'Aruba',
        'AX' => 'Åland Islands', 'AZ' => 'Azerbaijan', 'BA' => 'Bosnia and Herzegovina',
        'BB' => 'Barbados', 'BD' => 'Bangladesh', 'BE' => 'Belgium', 'BF' => 'Burkina Faso',
        'BG' => 'Bulgaria', 'BH' => 'Bahrain', 'BI' => 'Burundi', 'BJ' => 'Benin',
        'BL' => 'Saint Barthélemy', 'BM' => 'Bermuda', 'BN' => 'Brunei', 'BO' => 'Bolivia',
        'BQ' => 'Caribbean Netherlands', 'BR' => 'Brazil', 'BS' => 'Bahamas', 'BT' => 'Bhutan',
        'BV' => 'Bouvet Island', 'BW' => 'Botswana', 'BY' => 'Belarus', 'BZ' => 'Belize',
        'CA' => 'Canada', 'CC' => 'Cocos (Keeling) Islands', 'CD' => 'DR Congo',
        'CF' => 'Central African Republic', 'CG' => 'Congo', 'CH' => 'Switzerland',
        'CI' => 'Côte d\'Ivoire', 'CK' => 'Cook Islands', 'CL' => 'Chile', 'CM' => 'Cameroon',
        'CN' => 'China', 'CO' => 'Colombia', 'CR' => 'Costa Rica', 'CU' => 'Cuba',
        'CV' => 'Cape Verde', 'CW' => 'Curaçao', 'CX' => 'Christmas Island', 'CY' => 'Cyprus',
        'CZ' => 'Czechia', 'DE' => 'Germany', 'DJ' => 'Djibouti', 'DK' => 'Denmark',
        'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'DZ' => 'Algeria', 'EC' => 'Ecuador',
        'EE' => 'Estonia', 'EG' => 'Egypt', 'EH' => 'Western Sahara', 'ER' => 'Eritrea',
        'ES' => 'Spain', 'ET' => 'Ethiopia', 'FI' => 'Finland', 'FJ' => 'Fiji',
        'FK' => 'Falkland Islands', 'FM' => 'Micronesia', 'FO' => 'Faroe Islands',
        'FR' => 'France', 'GA' => 'Gabon', 'GB' => 'United Kingdom', 'GD' => 'Grenada',
        'GE' => 'Georgia', 'GF' => 'French Guiana', 'GG' => 'Guernsey', 'GH' => 'Ghana',
        'GI' => 'Gibraltar', 'GL' => 'Greenland', 'GM' => 'Gambia', 'GN' => 'Guinea',
        'GP' => 'Guadeloupe', 'GQ' => 'Equatorial Guinea', 'GR' => 'Greece',
        'GS' => 'South Georgia and the South Sandwich Islands', 'GT' => 'Guatemala',
        'GU' => 'Guam', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HK' => 'Hong Kong',
        'HM' => 'Heard Island and McDonald Islands', 'HN' => 'Honduras', 'HR' => 'Croatia',
        'HT' => 'Haiti', 'HU' => 'Hungary', 'ID' => 'Indonesia', 'IE' => 'Ireland',
        'IL' => 'Israel', 'IM' => 'Isle of Man', 'IN' => 'India',
        'IO' => 'British Indian Ocean Territory', 'IQ' => 'Iraq', 'IR' => 'Iran',
        'IS' => 'Iceland', 'IT' => 'Italy', 'JE' => 'Jersey', 'JM' => 'Jamaica',
        'JO' => 'Jordan', 'JP' => 'Japan', 'KE' => 'Kenya', 'KG' => 'Kyrgyzstan',
        'KH' => 'Cambodia', 'KI' => 'Kiribati', 'KM' => 'Comoros', 'KN' => 'Saint Kitts and Nevis',
        'KP' => 'North Korea', 'KR' => 'South Korea', 'KW' => 'Kuwait', 'KY' => 'Cayman Islands',
        'KZ' => 'Kazakhstan', 'LA' => 'Laos', 'LB' => 'Lebanon', 'LC' => 'Saint Lucia',
        'LI' => 'Liechtenstein', 'LK' => 'Sri Lanka', 'LR' => 'Liberia', 'LS' => 'Lesotho',
        'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'LV' => 'Latvia', 'LY' => 'Libya',
        'MA' => 'Morocco', 'MC' => 'Monaco', 'MD' => 'Moldova', 'ME' => 'Montenegro',
        'MF' => 'Saint Martin', 'MG' => 'Madagascar', 'MH' => 'Marshall Islands',
        'MK' => 'North Macedonia', 'ML' => 'Mali', 'MM' => 'Myanmar', 'MN' => 'Mongolia',
        'MO' => 'Macao', 'MP' => 'Northern Mariana Islands', 'MQ' => 'Martinique',
        'MR' => 'Mauritania', 'MS' => 'Montserrat', 'MT' => 'Malta', 'MU' => 'Mauritius',
        'MV' => 'Maldives', 'MW' => 'Malawi', 'MX' => 'Mexico', 'MY' => 'Malaysia',
        'MZ' => 'Mozambique', 'NA' => 'Namibia', 'NC' => 'New Caledonia', 'NE' => 'Niger',
        'NF' => 'Norfolk Island', 'NG' => 'Nigeria', 'NI' => 'Nicaragua', 'NL' => 'Netherlands',
        'NO' => 'Norway', 'NP' => 'Nepal', 'NR' => 'Nauru', 'NU' => 'Niue',
        'NZ' => 'New Zealand', 'OM' => 'Oman', 'PA' => 'Panama', 'PE' => 'Peru',
        'PF' => 'French Polynesia', 'PG' => 'Papua New Guinea', 'PH' => 'Philippines',
        'PK' => 'Pakistan', 'PL' => 'Poland', 'PM' => 'Saint Pierre and Miquelon',
        'PN' => 'Pitcairn Islands', 'PR' => 'Puerto Rico', 'PS' => 'Palestine',
        'PT' => 'Portugal', 'PW' => 'Palau', 'PY' => 'Paraguay', 'QA' => 'Qatar',
        'RE' => 'Réunion', 'RO' => 'Romania', 'RS' => 'Serbia', 'RU' => 'Russia',
        'RW' => 'Rwanda', 'SA' => 'Saudi Arabia', 'SB' => 'Solomon Islands', 'SC' => 'Seychelles',
        'SD' => 'Sudan', 'SE' => 'Sweden', 'SG' => 'Singapore', 'SH' => 'Saint Helena',
        'SI' => 'Slovenia', 'SJ' => 'Svalbard and Jan Mayen', 'SK' => 'Slovakia',
        'SL' => 'Sierra Leone', 'SM' => 'San Marino', 'SN' => 'Senegal', 'SO' => 'Somalia',
        'SR' => 'Suriname', 'SS' => 'South Sudan', 'ST' => 'São Tomé and Príncipe',
        'SV' => 'El Salvador', 'SX' => 'Sint Maarten', 'SY' => 'Syria', 'SZ' => 'Eswatini',
        'TC' => 'Turks and Caicos Islands', 'TD' => 'Chad', 'TF' => 'French Southern Territories',
        'TG' => 'Togo', 'TH' => 'Thailand', 'TJ' => 'Tajikistan', 'TK' => 'Tokelau',
        'TL' => 'Timor-Leste', 'TM' => 'Turkmenistan', 'TN' => 'Tunisia', 'TO' => 'Tonga',
        'TR' => 'Türkiye', 'TT' => 'Trinidad and Tobago', 'TV' => 'Tuvalu', 'TW' => 'Taiwan',
        'TZ' => 'Tanzania', 'UA' => 'Ukraine', 'UG' => 'Uganda',
        'UM' => 'U.S. Outlying Islands', 'US' => 'United States', 'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan', 'VA' => 'Vatican City', 'VC' => 'Saint Vincent and the Grenadines',
        'VE' => 'Venezuela', 'VG' => 'British Virgin Islands', 'VI' => 'U.S. Virgin Islands',
        'VN' => 'Vietnam', 'VU' => 'Vanuatu', 'WF' => 'Wallis and Futuna', 'WS' => 'Samoa',
        'XK' => 'Kosovo', 'YE' => 'Yemen', 'YT' => 'Mayotte', 'ZA' => 'South Africa',
        'ZM' => 'Zambia', 'ZW' => 'Zimbabwe',
    ];
}

/**
 * Noms français des pays (v2.7.1), utilisés quand l'admin est en français : l'extension
 * intl, qui fournirait ces noms, n'est pas toujours installée.
 */
function ip_location_iso_countries_fr()
{
    return [
        'AD' => 'Andorre', 'AE' => 'Émirats arabes unis', 'AF' => 'Afghanistan', 'AG' => 'Antigua-et-Barbuda',
        'AI' => 'Anguilla', 'AL' => 'Albanie', 'AM' => 'Arménie', 'AO' => 'Angola',
        'AQ' => 'Antarctique', 'AR' => 'Argentine', 'AS' => 'Samoa américaines', 'AT' => 'Autriche',
        'AU' => 'Australie', 'AW' => 'Aruba', 'AX' => 'Îles Åland', 'AZ' => 'Azerbaïdjan',
        'BA' => 'Bosnie-Herzégovine', 'BB' => 'Barbade', 'BD' => 'Bangladesh', 'BE' => 'Belgique',
        'BF' => 'Burkina Faso', 'BG' => 'Bulgarie', 'BH' => 'Bahreïn', 'BI' => 'Burundi',
        'BJ' => 'Bénin', 'BL' => 'Saint-Barthélemy', 'BM' => 'Bermudes', 'BN' => 'Brunei',
        'BO' => 'Bolivie', 'BQ' => 'Pays-Bas caribéens', 'BR' => 'Brésil', 'BS' => 'Bahamas',
        'BT' => 'Bhoutan', 'BV' => 'Île Bouvet', 'BW' => 'Botswana', 'BY' => 'Biélorussie',
        'BZ' => 'Belize', 'CA' => 'Canada', 'CC' => 'Îles Cocos', 'CD' => 'Congo (RDC)',
        'CF' => 'République centrafricaine', 'CG' => 'Congo', 'CH' => 'Suisse', 'CI' => 'Côte d\'Ivoire',
        'CK' => 'Îles Cook', 'CL' => 'Chili', 'CM' => 'Cameroun', 'CN' => 'Chine',
        'CO' => 'Colombie', 'CR' => 'Costa Rica', 'CU' => 'Cuba', 'CV' => 'Cap-Vert',
        'CW' => 'Curaçao', 'CX' => 'Île Christmas', 'CY' => 'Chypre', 'CZ' => 'Tchéquie',
        'DE' => 'Allemagne', 'DJ' => 'Djibouti', 'DK' => 'Danemark', 'DM' => 'Dominique',
        'DO' => 'République dominicaine', 'DZ' => 'Algérie', 'EC' => 'Équateur', 'EE' => 'Estonie',
        'EG' => 'Égypte', 'EH' => 'Sahara occidental', 'ER' => 'Érythrée', 'ES' => 'Espagne',
        'ET' => 'Éthiopie', 'FI' => 'Finlande', 'FJ' => 'Fidji', 'FK' => 'Îles Malouines',
        'FM' => 'Micronésie', 'FO' => 'Îles Féroé', 'FR' => 'France', 'GA' => 'Gabon',
        'GB' => 'Royaume-Uni', 'GD' => 'Grenade', 'GE' => 'Géorgie', 'GF' => 'Guyane française',
        'GG' => 'Guernesey', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GL' => 'Groenland',
        'GM' => 'Gambie', 'GN' => 'Guinée', 'GP' => 'Guadeloupe', 'GQ' => 'Guinée équatoriale',
        'GR' => 'Grèce', 'GS' => 'Géorgie du Sud-et-les îles Sandwich du Sud', 'GT' => 'Guatemala', 'GU' => 'Guam',
        'GW' => 'Guinée-Bissau', 'GY' => 'Guyana', 'HK' => 'Hong Kong', 'HM' => 'Îles Heard-et-MacDonald',
        'HN' => 'Honduras', 'HR' => 'Croatie', 'HT' => 'Haïti', 'HU' => 'Hongrie',
        'ID' => 'Indonésie', 'IE' => 'Irlande', 'IL' => 'Israël', 'IM' => 'Île de Man',
        'IN' => 'Inde', 'IO' => 'Territoire britannique de l\'océan Indien', 'IQ' => 'Irak', 'IR' => 'Iran',
        'IS' => 'Islande', 'IT' => 'Italie', 'JE' => 'Jersey', 'JM' => 'Jamaïque',
        'JO' => 'Jordanie', 'JP' => 'Japon', 'KE' => 'Kenya', 'KG' => 'Kirghizistan',
        'KH' => 'Cambodge', 'KI' => 'Kiribati', 'KM' => 'Comores', 'KN' => 'Saint-Christophe-et-Niévès',
        'KP' => 'Corée du Nord', 'KR' => 'Corée du Sud', 'KW' => 'Koweït', 'KY' => 'Îles Caïmans',
        'KZ' => 'Kazakhstan', 'LA' => 'Laos', 'LB' => 'Liban', 'LC' => 'Sainte-Lucie',
        'LI' => 'Liechtenstein', 'LK' => 'Sri Lanka', 'LR' => 'Liberia', 'LS' => 'Lesotho',
        'LT' => 'Lituanie', 'LU' => 'Luxembourg', 'LV' => 'Lettonie', 'LY' => 'Libye',
        'MA' => 'Maroc', 'MC' => 'Monaco', 'MD' => 'Moldavie', 'ME' => 'Monténégro',
        'MF' => 'Saint-Martin', 'MG' => 'Madagascar', 'MH' => 'Îles Marshall', 'MK' => 'Macédoine du Nord',
        'ML' => 'Mali', 'MM' => 'Myanmar', 'MN' => 'Mongolie', 'MO' => 'Macao',
        'MP' => 'Îles Mariannes du Nord', 'MQ' => 'Martinique', 'MR' => 'Mauritanie', 'MS' => 'Montserrat',
        'MT' => 'Malte', 'MU' => 'Maurice', 'MV' => 'Maldives', 'MW' => 'Malawi',
        'MX' => 'Mexique', 'MY' => 'Malaisie', 'MZ' => 'Mozambique', 'NA' => 'Namibie',
        'NC' => 'Nouvelle-Calédonie', 'NE' => 'Niger', 'NF' => 'Île Norfolk', 'NG' => 'Nigeria',
        'NI' => 'Nicaragua', 'NL' => 'Pays-Bas', 'NO' => 'Norvège', 'NP' => 'Népal',
        'NR' => 'Nauru', 'NU' => 'Niue', 'NZ' => 'Nouvelle-Zélande', 'OM' => 'Oman',
        'PA' => 'Panama', 'PE' => 'Pérou', 'PF' => 'Polynésie française', 'PG' => 'Papouasie-Nouvelle-Guinée',
        'PH' => 'Philippines', 'PK' => 'Pakistan', 'PL' => 'Pologne', 'PM' => 'Saint-Pierre-et-Miquelon',
        'PN' => 'Îles Pitcairn', 'PR' => 'Porto Rico', 'PS' => 'Palestine', 'PT' => 'Portugal',
        'PW' => 'Palaos', 'PY' => 'Paraguay', 'QA' => 'Qatar', 'RE' => 'La Réunion',
        'RO' => 'Roumanie', 'RS' => 'Serbie', 'RU' => 'Russie', 'RW' => 'Rwanda',
        'SA' => 'Arabie saoudite', 'SB' => 'Îles Salomon', 'SC' => 'Seychelles', 'SD' => 'Soudan',
        'SE' => 'Suède', 'SG' => 'Singapour', 'SH' => 'Sainte-Hélène', 'SI' => 'Slovénie',
        'SJ' => 'Svalbard et Jan Mayen', 'SK' => 'Slovaquie', 'SL' => 'Sierra Leone', 'SM' => 'Saint-Marin',
        'SN' => 'Sénégal', 'SO' => 'Somalie', 'SR' => 'Suriname', 'SS' => 'Soudan du Sud',
        'ST' => 'Sao Tomé-et-Principe', 'SV' => 'Salvador', 'SX' => 'Saint-Martin (partie néerlandaise)', 'SY' => 'Syrie',
        'SZ' => 'Eswatini', 'TC' => 'Îles Turques-et-Caïques', 'TD' => 'Tchad', 'TF' => 'Terres australes françaises',
        'TG' => 'Togo', 'TH' => 'Thaïlande', 'TJ' => 'Tadjikistan', 'TK' => 'Tokelau',
        'TL' => 'Timor oriental', 'TM' => 'Turkménistan', 'TN' => 'Tunisie', 'TO' => 'Tonga',
        'TR' => 'Turquie', 'TT' => 'Trinité-et-Tobago', 'TV' => 'Tuvalu', 'TW' => 'Taïwan',
        'TZ' => 'Tanzanie', 'UA' => 'Ukraine', 'UG' => 'Ouganda', 'UM' => 'Îles mineures éloignées des États-Unis',
        'US' => 'États-Unis', 'UY' => 'Uruguay', 'UZ' => 'Ouzbékistan', 'VA' => 'Vatican',
        'VC' => 'Saint-Vincent-et-les-Grenadines', 'VE' => 'Venezuela', 'VG' => 'Îles Vierges britanniques', 'VI' => 'Îles Vierges des États-Unis',
        'VN' => 'Viêt Nam', 'VU' => 'Vanuatu', 'WF' => 'Wallis-et-Futuna', 'WS' => 'Samoa',
        'XK' => 'Kosovo', 'YE' => 'Yémen', 'YT' => 'Mayotte', 'ZA' => 'Afrique du Sud',
        'ZM' => 'Zambie', 'ZW' => 'Zimbabwe',
    ];
}

/**
 * Noms des pays dans la langue de l'admin [code => nom] : français intégré, sinon
 * extension intl si présente, sinon anglais.
 */
function ip_location_country_names()
{
    global $user;
    static $cache = null;
    if ($cache !== null) return $cache;
    $lang = isset($user['language']) ? $user['language'] : 'en_UK';
    $fr   = strpos($lang, 'fr') === 0 ? ip_location_iso_countries_fr() : null;
    $intl = !$fr && strpos($lang, 'en') !== 0 && class_exists('Locale');
    $cache = [];
    foreach (ip_location_iso_countries() as $code => $name) {
        if ($fr) {
            $name = $fr[$code];
        } elseif ($intl) {
            $local = Locale::getDisplayRegion('-' . $code, $lang);
            if ($local !== '' && $local !== $code) $name = $local;
        }
        $cache[$code] = $name;
    }
    return $cache;
}

/**
 * Liste pour une liste déroulante : [['code' => 'FR', 'name' => 'France'], ...], triée par
 * nom sans tenir compte des accents (« Égypte » avec les E, pas après « Zimbabwe »).
 */
function ip_location_country_options()
{
    $out = [];
    foreach (ip_location_country_names() as $code => $name) {
        $key = strtolower(strtr($name, [
            'À' => 'A', 'Â' => 'A', 'Å' => 'A', 'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Î' => 'I',
            'Ï' => 'I', 'Ô' => 'O', 'Ü' => 'U', 'à' => 'a', 'â' => 'a', 'å' => 'a', 'ç' => 'c',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'î' => 'i', 'ï' => 'i', 'ô' => 'o',
            'ö' => 'o', 'ü' => 'u', 'ã' => 'a', 'í' => 'i',
        ]));
        $out[$key . '|' . $code] = ['code' => $code, 'name' => $name];
    }
    ksort($out, SORT_STRING);
    return array_values($out);
}
