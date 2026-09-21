CREATE TABLE cards (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    card_code VARCHAR(64)
        CHARACTER SET ascii
        COLLATE ascii_bin
        NOT NULL,

    card_name VARCHAR(150) NOT NULL,
    person_name VARCHAR(150) NOT NULL,

    era VARCHAR(100) NULL,
    rarity VARCHAR(50) NOT NULL,
    theme VARCHAR(100) NULL,

    image_path VARCHAR(255) NOT NULL,

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_cards_card_code (card_code),

    INDEX idx_cards_draw (rarity, is_active)
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci