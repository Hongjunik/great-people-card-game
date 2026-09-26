CREATE TABLE user_card_collection (
    user_id BIGINT UNSIGNED NOT NULL,
    card_id BIGINT UNSIGNED NOT NULL,

    first_acquired_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id, card_id),

    KEY idx_collection_card_id (card_id),

    CONSTRAINT fk_collection_user
        FOREIGN KEY (user_id)
        REFERENCES users (id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_collection_card
        FOREIGN KEY (card_id)
        REFERENCES cards (id)
        ON DELETE RESTRICT
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;