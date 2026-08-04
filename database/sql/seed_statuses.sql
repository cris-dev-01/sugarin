INSERT INTO statuses (name, `order`, created_at, updated_at)
SELECT 'Rango normal', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM statuses WHERE name = 'Rango normal');

INSERT INTO statuses (name, `order`, created_at, updated_at)
SELECT 'Elevado - fuera de rango normal', 2, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM statuses WHERE name = 'Elevado - fuera de rango normal');

INSERT INTO statuses (name, `order`, created_at, updated_at)
SELECT 'Bajo - fuera de rango normal', 3, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM statuses WHERE name = 'Bajo - fuera de rango normal');
