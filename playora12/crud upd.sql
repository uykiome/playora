CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    game_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_game_review (user_id, game_id),
    INDEX idx_reviews_game (game_id),
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_game FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS game_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 1,
    UNIQUE KEY unique_game_sort (game_id, sort_order),
    CONSTRAINT fk_game_images_game FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO game_images (game_id, image_url, sort_order)
SELECT g.id, CONCAT('https://cdn.akamai.steamstatic.com/steam/apps/', g.steam_app_id, '/header.jpg'), 1 FROM games g WHERE g.steam_app_id IS NOT NULL
UNION ALL
SELECT g.id, CONCAT('https://cdn.akamai.steamstatic.com/steam/apps/', g.steam_app_id, '/capsule_616x353.jpg'), 2 FROM games g WHERE g.steam_app_id IS NOT NULL
UNION ALL
SELECT g.id, CONCAT('https://cdn.akamai.steamstatic.com/steam/apps/', g.steam_app_id, '/library_600x900.jpg'), 3 FROM games g WHERE g.steam_app_id IS NOT NULL
UNION ALL
SELECT g.id, CONCAT('https://cdn.akamai.steamstatic.com/steam/apps/', g.steam_app_id, '/library_hero.jpg'), 4 FROM games g WHERE g.steam_app_id IS NOT NULL;

