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

-- Journal des visites
-- Colonnes : ip, country, country_code, city, url, user_agent, is_bot, is_blocked, visit_date
INSERT INTO piwigo_ip_location_log
  (ip, country, country_code, city, url, user_agent, is_bot, is_blocked, visit_date)
VALUES
  -- Visiteurs humains normaux
  ('82.64.12.1',   'France',         'FR', 'Paris',     'https://monsite.fr/photodev2/',                          'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36', 0, 0, NOW()),
  ('82.64.12.1',   'France',         'FR', 'Paris',     'https://monsite.fr/photodev2/index.php?/category/1',     'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36', 0, 0, NOW() - INTERVAL 1 HOUR),
  ('78.47.33.2',   'Germany',        'DE', 'Berlin',    'https://monsite.fr/photodev2/index.php?/photo/42',       'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Safari/605.1.15',          0, 0, NOW() - INTERVAL 2 HOUR),
  ('78.47.33.2',   'Germany',        'DE', 'Berlin',    'https://monsite.fr/photodev2/',                          'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Safari/605.1.15',          0, 0, NOW() - INTERVAL 3 HOUR),
  ('212.58.244.3', 'United Kingdom', 'GB', 'London',    'https://monsite.fr/photodev2/index.php?/category/2',     'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/119.0.0.0 Safari/537.36',            0, 0, NOW() - INTERVAL 5 HOUR),

  -- Bots détectés (is_bot=1)
  ('8.8.8.5',      'United States',  'US', 'New York',  'https://monsite.fr/photodev2/',                          'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',                      1, 0, NOW() - INTERVAL 30 MINUTE),
  ('8.8.8.5',      'United States',  'US', 'New York',  'https://monsite.fr/photodev2/index.php?/photo/7',        'python-requests/2.28.0',                                                                       1, 0, NOW() - INTERVAL 1 DAY),
  ('185.220.101.4','Netherlands',    'NL', 'Amsterdam', 'https://monsite.fr/photodev2/index.php?/category/3',     'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/145.0.0.0 Safari/537.36', 1, 0, NOW() - INTERVAL 2 HOUR),

  -- Visites bloquées (is_blocked=1)
  ('200.45.67.6',  'Brazil',         'BR', 'São Paulo', 'https://monsite.fr/photodev2/',                          'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36', 0, 1, NOW() - INTERVAL 45 MINUTE),
  ('200.45.67.6',  'Brazil',         'BR', 'São Paulo', 'https://monsite.fr/photodev2/index.php?/photo/12',       'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36', 0, 1, NOW() - INTERVAL 3 DAY),

  -- Bots ET bloqués (is_bot=1, is_blocked=1)
  ('8.8.8.5',      'United States',  'US', 'New York',  'https://monsite.fr/photodev2/index.php?/photo/99',       'curl/7.88.1',                                                                                  1, 1, NOW() - INTERVAL 10 MINUTE),

  -- Entrées anciennes (pour tester la purge par ancienneté)
  ('82.64.12.1',   'France',         'FR', 'Paris',     'https://monsite.fr/photodev2/',                          'Mozilla/5.0 (Windows NT 10.0) Chrome/118.0.0.0 Safari/537.36',                                0, 0, NOW() - INTERVAL 10 DAY),
  ('78.47.33.2',   'Germany',        'DE', 'Berlin',    'https://monsite.fr/photodev2/index.php?/category/3',     'Mozilla/5.0 (Windows NT 10.0) Chrome/117.0.0.0 Safari/537.36',                                0, 0, NOW() - INTERVAL 40 DAY),
  ('212.58.244.3', 'United Kingdom', 'GB', 'London',    'https://monsite.fr/photodev2/',                          'Mozilla/5.0 (X11; Linux x86_64) Chrome/116.0.0.0 Safari/537.36',                              0, 0, NOW() - INTERVAL 60 DAY);

-- ─────────────────────────────────────────────────────────────
-- Pour nettoyer les données de test :
-- DELETE FROM piwigo_ip_location_log;
-- DELETE FROM piwigo_ip_location_cache;
-- ─────────────────────────────────────────────────────────────
