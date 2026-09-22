INSERT INTO cards (
    card_code,
    card_name,
    person_name,
    era,
    rarity,
    theme,
    image_path
)
VALUES
(
    'CARD_TEST_001',
    'A',
    'A',
    '',
    'Common',
    'Test',
    '/assets/cards/CARD_TEST_001.svg'
),
(
    'CARD_TEST_002',
    'B',
    'B',
    '',
    'Common',
    'Test',
    '/assets/cards/CARD_TEST_002.svg'
)
ON DUPLICATE KEY UPDATE
    card_name = VALUES(card_name),
    person_name = VALUES(person_name),
    era = VALUES(era),
    rarity = VALUES(rarity),
    theme = VALUES(theme),
    image_path = VALUES(image_path);