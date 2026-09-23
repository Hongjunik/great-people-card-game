CREATE TABLE user_cards (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    user_id BIGINT UNSIGNED NOT NULL,

    card_id BIGINT UNSIGNED NOT NULL,

    acquired_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_user_cards_user_card (user_id, card_id),

    KEY idx_user_cards_card_id (card_id),

    CONSTRAINT fk_user_cards_user
        FOREIGN KEY (user_id)
        REFERENCES users (id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_user_cards_card
        FOREIGN KEY (card_id)
        REFERENCES cards (id)
        ON DELETE RESTRICT
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;