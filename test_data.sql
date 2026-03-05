-- ─────────────────────────────────────────────────────────────
-- Données de test pour ip_location
-- À exécuter dans phpMyAdmin ou via mysql CLI
-- Remplacer "piwigo_" par le préfixe réel si différent
-- ─────────────────────────────────────────────────────────────

-- Cache IP
INSERT INTO piwigo_ip_location_cache (ip, country, country_code, city, resolved_at) VALUES
  ('82.64.12.1',    'France',         'FR', 'Paris',     NOW()),
  ('78.47.33.2',    'Germany',        'DE', 'Berlin',    NOW()),
  ('212.58.244.3',  'United Kingdom', 'GB', 'London',    NOW()),
  ('185.220.101.4', 'Netherlands',    'NL', 'Amsterdam', NOW()),
  ('8.8.8.5',       'United States',  'US', 'New York',  NOW()),
  ('200.45.67.6',   'Brazil',         'BR', 'São Paulo', NOW())
ON DUPLICATE KEY UPDATE resolved_at = NOW();

-- Journal des visites — entrées récentes et anciennes (pour tester la purge)
INSERT INTO piwigo_ip_location_log (ip, country, country_code, city, url, visit_date) VALUES
  ('82.64.12.1',    'France',         'FR', 'Paris',     'https://monsite.fr/photodev2/', NOW()),
  ('82.64.12.1',    'France',         'FR', 'Paris',     'https://monsite.fr/photodev2/index.php?/category/1', NOW() - INTERVAL 1 HOUR),
  ('78.47.33.2',    'Germany',        'DE', 'Berlin',    'https://monsite.fr/photodev2/index.php?/photo/42', NOW() - INTERVAL 2 HOUR),
  ('78.47.33.2',    'Germany',        'DE', 'Berlin',    'https://monsite.fr/photodev2/', NOW() - INTERVAL 3 HOUR),
  ('212.58.244.3',  'United Kingdom', 'GB', 'London',    'https://monsite.fr/photodev2/index.php?/category/2', NOW() - INTERVAL 5 HOUR),
  ('185.220.101.4', 'Netherlands',    'NL', 'Amsterdam', 'https://monsite.fr/photodev2/index.php?/photo/7',  NOW() - INTERVAL 1 DAY),
  ('8.8.8.5',       'United States',  'US', 'New York',  'https://monsite.fr/photodev2/', NOW() - INTERVAL 2 DAY),
  ('200.45.67.6',   'Brazil',         'BR', 'São Paulo', 'https://monsite.fr/photodev2/index.php?/photo/12', NOW() - INTERVAL 3 DAY),
  ('82.64.12.1',    'France',         'FR', 'Paris',     'https://monsite.fr/photodev2/', NOW() - INTERVAL 10 DAY),
  ('78.47.33.2',    'Germany',        'DE', 'Berlin',    'https://monsite.fr/photodev2/index.php?/category/3', NOW() - INTERVAL 40 DAY),
  ('212.58.244.3',  'United Kingdom', 'GB', 'London',    'https://monsite.fr/photodev2/', NOW() - INTERVAL 60 DAY);

-- ─────────────────────────────────────────────────────────────
-- Pour nettoyer les données de test :
-- DELETE FROM piwigo_ip_location_log;
-- DELETE FROM piwigo_ip_location_cache;
-- ─────────────────────────────────────────────────────────────
