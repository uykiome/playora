-- Playora database. Import this file in phpMyAdmin.
CREATE DATABASE IF NOT EXISTS playora_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE playora_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS games (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    title VARCHAR(160) NOT NULL,
    genre VARCHAR(40) NOT NULL,
    release_year SMALLINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    publisher VARCHAR(160) NOT NULL,
    platforms VARCHAR(180) NOT NULL DEFAULT 'PC',
    game_mode VARCHAR(80) NOT NULL DEFAULT 'Single-player',
    rating DECIMAL(2,1) NOT NULL DEFAULT 0.0,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    steam_app_id INT UNSIGNED NULL,
    theme VARCHAR(40) NOT NULL DEFAULT 'cyber',
    code VARCHAR(12) NOT NULL DEFAULT 'PLAY',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_games_genre (genre),
    INDEX idx_games_year (release_year),
    INDEX idx_games_rating (rating)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS favorites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    game_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_game (user_id, game_id),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_game FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO games (slug,title,genre,release_year,description,publisher,platforms,game_mode,rating,price,steam_app_id,theme,code,is_featured) VALUES
('cyberpunk-2077','Cyberpunk 2077','rpg',2020,'Түнгі қалада өз жолыңды таңда. Технология, билік және адам тағдыры тоғысқан болашақты зертте.','CD PROJEKT RED','PC, PlayStation, Xbox','Single-player',4.8,16990,1091500,'cyber','2077',1),
('elden-ring','Elden Ring','action',2022,'Құпияға толы кең әлемді зерттеп, қиын сынақтардан өтіп, Аралық жерлердің сырын аш.','Bandai Namco Entertainment','PC, PlayStation, Xbox','Single-player',4.9,19990,1245620,'elden','ER',1),
('minecraft','Minecraft','sandbox',2011,'Блоктардан өз әлеміңді құрып, үңгірлерді зертте және достарыңмен бірге ойна.','Mojang Studios','PC, Console, Mobile','Single-player / Online',4.8,9990,NULL,'minecraft','MC',1),
('genshin-impact','Genshin Impact','rpg',2020,'Тейват әлемін зерттеп, ерекше кейіпкерлермен бірге оның құпияларын аш.','HoYoverse','PC, PlayStation, Mobile','Online',4.7,0,NULL,'genshin','GI',1),
('valorant','VALORANT','combat',2020,'Командаңмен тактика құрып, ерекше қабілеттерді пайдалан және раунд сайын жеңіске ұмтыл.','Riot Games','PC','Online multiplayer',4.5,0,NULL,'valorant','V.',0),
('grand-theft-auto-v','Grand Theft Auto V','action',2013,'Лос-Сантос қаласындағы оқиғаларға араласып, ашық әлемде өз бағытыңды таңда.','Rockstar Games','PC, PlayStation, Xbox','Single-player / Online',4.7,12990,271590,'gta','GTA',0),
('the-witcher-3','The Witcher 3: Wild Hunt','adventure',2015,'Геральтпен бірге құбыжықтар, күрделі шешімдер және есте қаларлық оқиғаларға толы әлемді арала.','CD PROJEKT RED','PC, PlayStation, Xbox, Switch','Single-player',4.9,4990,292030,'witcher','W3',0),
('phasmophobia','Phasmophobia','horror',2020,'Достарыңмен бірге паранормалды құбылыстарды зерттеп, жұмбақ орындардан дәлел жина.','Kinetic Games','PC','Online co-op',4.6,6990,739630,'phasmo','PH',0),
('dota-2','Dota 2','strategy',2013,'Командаңмен стратегия құрып, әртүрлі қабілеттері бар кейіпкерлер арқылы жеңіске жет.','Valve','PC','Online multiplayer',4.6,0,570,'dota','D2',0),
('apex-legends','Apex Legends','combat',2019,'Бірегей қабілеттері бар кейіпкерлермен команда құр және қарқынды матчтарға қатыс.','Electronic Arts','PC, PlayStation, Xbox','Online multiplayer',4.5,0,1172470,'apex','APEX',0),
('roblox','Roblox','multiplayer',2006,'Мыңдаған пайдаланушы жасаған әлемдерді аш, өз тәжірибеңді жаса және достарыңмен ойна.','Roblox Corporation','PC, Console, Mobile','Online',4.6,0,NULL,'roblox','R',0),
('final-fantasy-xiv','Final Fantasy XIV','rpg',2013,'Кең фэнтези әлемінде тапсырмалар орындап, кейіпкеріңді дамыт және басқа ойыншылармен сапарға шық.','Square Enix','PC, PlayStation','Online multiplayer',4.8,0,39210,'ffxiv','FFXIV',0)
ON DUPLICATE KEY UPDATE title=VALUES(title), genre=VALUES(genre), release_year=VALUES(release_year), description=VALUES(description), publisher=VALUES(publisher), platforms=VALUES(platforms), game_mode=VALUES(game_mode), rating=VALUES(rating), price=VALUES(price), steam_app_id=VALUES(steam_app_id), theme=VALUES(theme), code=VALUES(code), is_featured=VALUES(is_featured);
