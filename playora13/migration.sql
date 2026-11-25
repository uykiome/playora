-- Migration for an EXISTING Playora database (created from the previous database.sql).
-- 1) Run this file in phpMyAdmin (select playora_db first).  2) Then import the new database.sql once more.
-- If you get "Duplicate column name", that column already exists - just skip that line.
USE playora_db;

ALTER TABLE users MODIFY password_hash VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN google_id VARCHAR(64) NULL UNIQUE AFTER password_hash;
ALTER TABLE users ADD COLUMN role ENUM('user','admin') NOT NULL DEFAULT 'user' AFTER google_id;

ALTER TABLE games ADD COLUMN description_ru TEXT NULL AFTER description;
ALTER TABLE games ADD COLUMN description_en TEXT NULL AFTER description_ru;
ALTER TABLE games ADD COLUMN cover_image VARCHAR(500) NULL AFTER steam_app_id;

-- Old gallery rows used portrait/akamai links; remove them so the new landscape set is created
DELETE FROM game_images WHERE image_url LIKE 'https://cdn.akamai.steamstatic.com/%';
