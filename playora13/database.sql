-- Playora database. Import this file in phpMyAdmin (new installation).
-- For an EXISTING database run migration.sql first, then import this file again (it is safe to re-run).
CREATE DATABASE IF NOT EXISTS playora_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE playora_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    google_id VARCHAR(64) NULL UNIQUE,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS games (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    title VARCHAR(160) NOT NULL,
    genre VARCHAR(40) NOT NULL,
    release_year SMALLINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    description_ru TEXT NULL,
    description_en TEXT NULL,
    publisher VARCHAR(160) NOT NULL,
    platforms VARCHAR(180) NOT NULL DEFAULT 'PC',
    game_mode VARCHAR(80) NOT NULL DEFAULT 'Single-player',
    rating DECIMAL(2,1) NOT NULL DEFAULT 0.0,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    steam_app_id INT UNSIGNED NULL,
    cover_image VARCHAR(500) NULL,
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

INSERT INTO games (slug,title,genre,release_year,description,publisher,platforms,game_mode,rating,price,steam_app_id,theme,code,is_featured,description_ru,description_en) VALUES
('cyberpunk-2077','Cyberpunk 2077','rpg',2020,'Түнгі қалада өз жолыңды таңда. Технология, билік және адам тағдыры тоғысқан болашақты зертте.','CD PROJEKT RED','PC, PlayStation, Xbox','Single-player',4.8,16990,1091500,'cyber','2077',1,NULL,NULL),
('elden-ring','Elden Ring','action',2022,'Құпияға толы кең әлемді зерттеп, қиын сынақтардан өтіп, Аралық жерлердің сырын аш.','Bandai Namco Entertainment','PC, PlayStation, Xbox','Single-player',4.9,19990,1245620,'elden','ER',1,NULL,NULL),
('minecraft','Minecraft','sandbox',2011,'Блоктардан өз әлеміңді құрып, үңгірлерді зертте және достарыңмен бірге ойна.','Mojang Studios','PC, Console, Mobile','Single-player / Online',4.8,9990,NULL,'minecraft','MC',1,NULL,NULL),
('genshin-impact','Genshin Impact','rpg',2020,'Тейват әлемін зерттеп, ерекше кейіпкерлермен бірге оның құпияларын аш.','HoYoverse','PC, PlayStation, Mobile','Online',4.7,0,NULL,'genshin','GI',1,NULL,NULL),
('valorant','VALORANT','combat',2020,'Командаңмен тактика құрып, ерекше қабілеттерді пайдалан және раунд сайын жеңіске ұмтыл.','Riot Games','PC','Online multiplayer',4.5,0,NULL,'valorant','V.',0,NULL,NULL),
('grand-theft-auto-v','Grand Theft Auto V','action',2013,'Лос-Сантос қаласындағы оқиғаларға араласып, ашық әлемде өз бағытыңды таңда.','Rockstar Games','PC, PlayStation, Xbox','Single-player / Online',4.7,12990,271590,'gta','GTA',0,NULL,NULL),
('the-witcher-3','The Witcher 3: Wild Hunt','adventure',2015,'Геральтпен бірге құбыжықтар, күрделі шешімдер және есте қаларлық оқиғаларға толы әлемді арала.','CD PROJEKT RED','PC, PlayStation, Xbox, Switch','Single-player',4.9,4990,292030,'witcher','W3',0,NULL,NULL),
('phasmophobia','Phasmophobia','horror',2020,'Достарыңмен бірге паранормалды құбылыстарды зерттеп, жұмбақ орындардан дәлел жина.','Kinetic Games','PC','Online co-op',4.6,6990,739630,'phasmo','PH',0,NULL,NULL),
('dota-2','Dota 2','strategy',2013,'Командаңмен стратегия құрып, әртүрлі қабілеттері бар кейіпкерлер арқылы жеңіске жет.','Valve','PC','Online multiplayer',4.6,0,570,'dota','D2',0,NULL,NULL),
('apex-legends','Apex Legends','combat',2019,'Бірегей қабілеттері бар кейіпкерлермен команда құр және қарқынды матчтарға қатыс.','Electronic Arts','PC, PlayStation, Xbox','Online multiplayer',4.5,0,1172470,'apex','APEX',0,NULL,NULL),
('roblox','Roblox','multiplayer',2006,'Мыңдаған пайдаланушы жасаған әлемдерді аш, өз тәжірибеңді жаса және достарыңмен ойна.','Roblox Corporation','PC, Console, Mobile','Online',4.6,0,NULL,'roblox','R',0,NULL,NULL),
('final-fantasy-xiv','Final Fantasy XIV','rpg',2013,'Кең фэнтези әлемінде тапсырмалар орындап, кейіпкеріңді дамыт және басқа ойыншылармен сапарға шық.','Square Enix','PC, PlayStation','Online multiplayer',4.8,0,39210,'ffxiv','FFXIV',0,NULL,NULL),
('counter-strike-2','Counter-Strike 2','combat',2023,'Әлемдегі ең танымал тактикалық шутер: командамен раундтарды ұтып, нақты көзді және дәл тактиканы көрсет.','Valve','PC','Online multiplayer',4.6,0,730,'ember','CS2',0,'Самый известный тактический шутер: побеждай в раундах вместе с командой, полагаясь на меткость и стратегию.','The world''s most popular tactical shooter: win rounds with your team through sharp aim and smart strategy.'),
('pubg-battlegrounds','PUBG: BATTLEGROUNDS','combat',2017,'Үлкен картаға түсіп, қару-жарақ жинап, соңғы тірі қалған болу үшін күрес.','KRAFTON','PC, PlayStation, Xbox, Mobile','Online multiplayer',4.2,0,578080,'forest','PUBG',0,'Высадись на огромную карту, собери снаряжение и стань последним выжившим.','Drop onto a huge map, gear up and fight to be the last one standing.'),
('red-dead-redemption-2','Red Dead Redemption 2','adventure',2019,'Жабайы Батыс заманындағы банда тарихы: үлкен ашық әлем, кинематографиялық оқиға және еркін таңдау.','Rockstar Games','PC, PlayStation, Xbox','Single-player / Online',4.9,19990,1174180,'gold','RDR2',1,'История банды на Диком Западе: огромный открытый мир, кинематографичный сюжет и свобода выбора.','A tale of an outlaw gang in the Wild West: a vast open world, a cinematic story and freedom of choice.'),
('baldurs-gate-3','Baldur''s Gate 3','rpg',2023,'Сенің әрбір шешімің оқиғаны өзгертетін, достармен бірге өтуге болатын үлкен рөлдік ойын.','Larian Studios','PC, PlayStation, Mac','Single-player / Co-op',4.9,22990,1086940,'violet','BG3',1,'Масштабная ролевая игра, где каждое твоё решение меняет историю. Можно проходить с друзьями.','A sweeping RPG where every choice reshapes the story. Play solo or with friends.'),
('sekiro-shadows-die-twice','Sekiro: Shadows Die Twice','action',2019,'Ортағасырлық Жапониядағы шиеленісті қылыштасу: дәлдік, төзімділік және жылдамдық.','FromSoftware','PC, PlayStation, Xbox','Single-player',4.7,18990,814380,'crimson','SHDT',0,'Напряжённые поединки на мечах в феодальной Японии: точность, выдержка и скорость.','Tense sword duels in feudal Japan that reward precision, patience and speed.'),
('dark-souls-iii','Dark Souls III','rpg',2016,'Қараңғы, қатал әлемдегі қиын шайқастар мен құпия оқиға. Әр жеңіс еңбекпен келеді.','FromSoftware','PC, PlayStation, Xbox','Single-player',4.7,14990,374320,'ember','DS3',0,'Тёмный и суровый мир, сложные битвы и загадочный сюжет. Каждая победа даётся трудом.','A dark, unforgiving world of punishing battles and cryptic lore. Every victory is earned.'),
('resident-evil-4','Resident Evil 4','horror',2023,'Классикалық хоррордың заманауи қайта жасалуы: ауыл, жиіркенішті жаулар және үздіксіз қауіп.','Capcom','PC, PlayStation, Xbox','Single-player',4.8,18990,2050650,'crimson','RE4',0,'Современный ремейк классики хоррора: глухая деревня, жуткие враги и постоянная опасность.','A modern remake of the survival-horror classic: a remote village, terrifying enemies and constant danger.'),
('resident-evil-village','Resident Evil Village','horror',2021,'Құпия ауылдағы жантүршігерлік оқиға: өмір сүру үшін ресурстарды дұрыс пайдалан.','Capcom','PC, PlayStation, Xbox','Single-player',4.6,12990,1196590,'violet','RE8',0,'Жуткая история в загадочной деревне, где важно грамотно распоряжаться ресурсами.','A chilling story in a mysterious village where every resource counts.'),
('left-4-dead-2','Left 4 Dead 2','action',2009,'Зомби апокалипсисінде төрт адамдық командамен аман қалуға тырыс. Кооперативтің классикасы.','Valve','PC','Online co-op',4.7,2490,550,'forest','L4D2',0,'Выживай в зомби-апокалипсисе в команде из четырёх человек. Классика кооператива.','Survive the zombie apocalypse in a team of four. A co-op classic.'),
('portal-2','Portal 2','adventure',2011,'Порталдар мен физикаға негізделген ақылды жұмбақтар, тамаша юмор және кооператив режимі.','Valve','PC, Mac, Linux','Single-player / Co-op',4.9,2490,620,'ocean','P2',0,'Умные головоломки на порталах и физике, отличный юмор и кооперативный режим.','Clever portal-and-physics puzzles, sharp humor and a co-op mode.'),
('half-life-2','Half-Life 2','action',2004,'Шутерлер тарихын өзгерткен ойын: физика, оқиға және ұмытылмас кейіпкерлер.','Valve','PC, Mac, Linux','Single-player',4.8,2490,220,'neon','HL2',0,'Игра, изменившая историю шутеров: физика, сюжет и запоминающиеся герои.','The game that reshaped shooters: physics, story and unforgettable characters.'),
('fallout-4','Fallout 4','rpg',2015,'Ядролық соғыстан кейінгі әлемде өз тұрағыңды сал, серіктестер тап және пустошьті зертте.','Bethesda Game Studios','PC, PlayStation, Xbox','Single-player',4.4,7990,377160,'gold','F4',0,'Строй поселения, находи спутников и исследуй пустошь после ядерной войны.','Build settlements, find companions and explore the wasteland after nuclear war.'),
('skyrim-special-edition','The Elder Scrolls V: Skyrim SE','rpg',2016,'Айдаһарлар мен ежелгі тайпалар әлеміндегі аңызға айналған ашық әлем рөлдік ойыны.','Bethesda Game Studios','PC, PlayStation, Xbox, Switch','Single-player',4.8,11990,489830,'ocean','TES V',0,'Легендарная ролевая игра с открытым миром, драконами и древними кланами.','A legendary open-world RPG of dragons and ancient clans.'),
('civilization-vi','Sid Meier''s Civilization VI','strategy',2016,'Өз өркениетіңді тас дәуірінен ғарышқа дейін дамытып, тарихты өзгерт.','2K','PC, Mac, Switch, PlayStation, Xbox','Single-player / Online',4.5,19990,289070,'gold','CIV6',0,'Развей свою цивилизацию от каменного века до космической эры и измени ход истории.','Grow your civilization from the Stone Age to the space age and change history.'),
('age-of-empires-ii-de','Age of Empires II: Definitive Edition','strategy',2019,'Ортағасырлық классикалық стратегия: шаруашылық құр, әскер жина және жаулап ал.','Xbox Game Studios','PC, Xbox','Single-player / Online',4.7,6990,813780,'ember','AOE2',0,'Классическая средневековая стратегия: развивай экономику, собирай армию и завоёвывай.','The classic medieval strategy: build an economy, raise an army and conquer.'),
('stellaris','Stellaris','strategy',2016,'Галактикадағы өз империяңды басқарып, жаңа өркениеттермен дипломатия мен соғыс жүргіз.','Paradox Interactive','PC, PlayStation, Xbox','Single-player / Online',4.5,9990,281990,'violet','STEL',0,'Управляй своей галактической империей, веди дипломатию и войны с чужими цивилизациями.','Run your own galactic empire, with diplomacy and war against alien civilizations.'),
('cities-skylines','Cities: Skylines','simulation',2015,'Бос жерден қала салып, көлік қозғалысын, экономиканы және тұрғындар өмірін басқар.','Paradox Interactive','PC, Console, Mac','Single-player',4.6,6990,255710,'ocean','CITY',0,'Построй город с нуля и управляй транспортом, экономикой и жизнью горожан.','Build a city from scratch and manage its traffic, economy and citizens.'),
('the-sims-4','The Sims 4','simulation',2014,'Кейіпкерлеріңді жасап, олардың өмірін, үйін және қарым-қатынасын өзің басқар.','Electronic Arts','PC, Mac, PlayStation, Xbox','Single-player',4.0,0,1222670,'neon','SIMS',0,'Создавай персонажей и управляй их жизнью, домом и отношениями.','Create characters and shape their lives, homes and relationships.'),
('euro-truck-simulator-2','Euro Truck Simulator 2','simulation',2012,'Еуропа жолдарымен жүк тасымалдап, өз көлік компанияңды құр.','SCS Software','PC, Mac, Linux','Single-player',4.8,3990,227300,'forest','ETS2',0,'Перевози грузы по дорогам Европы и построй собственную транспортную компанию.','Haul cargo across Europe''s roads and build your own trucking company.'),
('forza-horizon-5','Forza Horizon 5','simulation',2021,'Мексиканың әсем жолдарында жүздеген көлікпен жарыс және ашық әлемді зертте.','Xbox Game Studios','PC, Xbox','Single-player / Online',4.7,17990,1551360,'ember','FH5',0,'Гоняй на сотнях машин по красочной Мексике и исследуй открытый мир.','Race hundreds of cars across vibrant Mexico and explore an open world.'),
('fall-guys','Fall Guys','multiplayer',2020,'Күлкілі кедергілі жарыстарда 60 ойыншымен бірге тәж үшін күрес.','Epic Games','PC, PlayStation, Xbox, Switch','Online multiplayer',4.3,0,1097150,'neon','FALL',0,'Забавные забеги с препятствиями, где 60 игроков борются за корону.','Wacky obstacle races where 60 players fight for the crown.'),
('among-us','Among Us','multiplayer',2018,'Достармен бірге кемедегі жасырын сатқынды тап немесе өзің сатқын бол.','Innersloth','PC, Console, Mobile','Online multiplayer',4.4,1990,945360,'crimson','AU',0,'Найди предателя среди экипажа или сам стань им. Идеально для компании друзей.','Find the impostor among the crew, or become one. Perfect for friends.'),
('palworld','Palworld','sandbox',2024,'Таңғажайып жаратылыстарды жинап, олармен бірге құрылыс сал, күрес және аман қал.','Pocketpair','PC, Xbox','Single-player / Online',4.4,7990,1623730,'forest','PAL',0,'Лови удивительных существ, стройся и сражайся вместе с ними, выживай в открытом мире.','Collect fantastical creatures, then build, fight and survive alongside them.'),
('helldivers-2','HELLDIVERS 2','combat',2024,'Галактикадағы демократияны қорғап, достармен бірге қарқынды кооперативті шайқастарға қатыс.','Sony Interactive Entertainment','PC, PlayStation','Online co-op',4.5,14990,553850,'gold','HD2',0,'Защищай демократию в галактике и участвуй в напряжённых кооперативных боях с друзьями.','Defend galactic democracy in frantic co-op battles with friends.'),
('hogwarts-legacy','Hogwarts Legacy','rpg',2023,'Сиқырлы әлемде оқушы болып, сабақ алып, құпияларды ашып, өз жолыңды таңда.','Warner Bros. Games','PC, PlayStation, Xbox, Switch','Single-player',4.6,19990,990080,'violet','HL',0,'Стань учеником волшебной школы, изучай заклинания и раскрывай тайны мира магии.','Become a student at a magical school, learn spells and uncover secrets.'),
('god-of-war','God of War','action',2022,'Кратос пен оның ұлы скандинав мифологиясы әлеміндегі қатал сапарға шығады.','PlayStation PC','PC, PlayStation','Single-player',4.9,19990,1593500,'ocean','GOW',1,'Кратос и его сын отправляются в суровое путешествие по миру скандинавской мифологии.','Kratos and his son set out on a harsh journey through the world of Norse mythology.'),
('marvels-spider-man-remastered','Marvel''s Spider-Man Remastered','action',2022,'Нью-Йорк үстінде торлармен қалықтап, қаланы қылмыстан қорға.','PlayStation PC','PC, PlayStation','Single-player',4.8,22990,1817070,'crimson','SPDR',0,'Лети на паутине над Нью-Йорком и защищай город от преступности.','Swing across New York and protect the city from crime.'),
('the-last-of-us-part-i','The Last of Us Part I','adventure',2023,'Пандемиядан кейінгі әлемде екі адамның сапары: эмоцияға толы кинематографиялық оқиға.','Naughty Dog','PC, PlayStation','Single-player',4.6,24990,1888930,'forest','TLOU',0,'Путешествие двух людей по миру после пандемии: эмоциональная кинематографичная история.','Two survivors cross a post-pandemic world in an emotional, cinematic story.'),
('monster-hunter-world','Monster Hunter: World','rpg',2018,'Алып құбыжықтарды аулап, олардың терісінен күшті қару-жарақ жаса.','Capcom','PC, PlayStation, Xbox','Single-player / Online',4.6,9990,582010,'ember','MHW',0,'Охотся на гигантских монстров и создавай мощное снаряжение из их трофеев.','Hunt giant monsters and craft powerful gear from their parts.'),
('lethal-company','Lethal Company','horror',2023,'Достарыңмен бірге қауіпті планеталардан сынық жинап, квотаны орында және тірі қал.','Zeekerss','PC','Online co-op',4.8,2990,1966720,'neon','LC',0,'Вместе с друзьями собирай лом на опасных планетах, выполняй квоту и оставайся в живых.','Scavenge dangerous planets with friends, meet the quota and stay alive.'),
('sea-of-thieves','Sea of Thieves','adventure',2018,'Қарақшы кемесінде достармен теңізге шығып, қазына іздеп, басқа экипаждармен шайқас.','Xbox Game Studios','PC, Xbox, PlayStation','Online multiplayer',4.4,9990,1172620,'ocean','SOT',0,'Выходи в море на пиратском корабле с друзьями, ищи сокровища и сражайся с другими экипажами.','Sail a pirate ship with friends, hunt for treasure and battle rival crews.'),
('it-takes-two','It Takes Two','adventure',2021,'Тек екі адамға арналған кооперативті шытырман оқиға: бірлесіп ойнаудың шынайы қуанышы.','Hazelight Studios','PC, PlayStation, Xbox, Switch','Online co-op',4.8,8990,1426210,'gold','ITT',0,'Приключение, созданное только для двоих: настоящая радость совместной игры.','An adventure made only for two: the pure joy of playing together.'),
('subnautica','Subnautica','adventure',2018,'Бөтен планетаның мұхитында аман қал: су астын зертте, база сал және құпияларды аш.','Unknown Worlds','PC, PlayStation, Xbox, Switch','Single-player',4.8,6990,264710,'ocean','SUB',0,'Выживай в океане чужой планеты: исследуй глубины, строй базу и раскрывай секреты.','Survive in the ocean of an alien planet: explore the depths, build a base and uncover secrets.'),
('terraria','Terraria','sandbox',2011,'2D әлемде қазу, құрылыс, шайқас және зерттеу. Мүмкіндіктер шексіз.','Re-Logic','PC, Console, Mobile','Single-player / Online',4.9,2490,105600,'forest','TERR',0,'Копай, строй, сражайся и исследуй в 2D-мире. Возможностей бесконечно много.','Dig, build, fight and explore in a 2D world with endless possibilities.'),
('stardew-valley','Stardew Valley','simulation',2016,'Ата-бабаңнан қалған фермаңды дамытып, ауыл тұрғындарымен дос бол.','ConcernedApe','PC, Console, Mobile','Single-player / Co-op',4.9,2990,413150,'gold','SDV',0,'Возроди ферму, доставшуюся от дедушки, и подружись с жителями городка.','Revive the farm you inherited and make friends in the village.'),
('hollow-knight','Hollow Knight','adventure',2017,'Жер астындағы құпия патшалықты зерттеп, қиын бастықтармен шайқас. Әдемі қолмен салынған графика.','Team Cherry','PC, Console','Single-player',4.9,3490,367520,'violet','HK',0,'Исследуй подземное королевство, сражайся с сильными боссами. Красивая рисованная графика.','Explore a hidden underground kingdom and face tough bosses, all in hand-drawn art.'),
('hades','Hades','action',2020,'Грек мифологиясының тозағынан қашуға тырыс: жылдам шайқас пен терең оқиға.','Supergiant Games','PC, Console','Single-player',4.9,6990,1145360,'crimson','HADES',0,'Попробуй сбежать из подземного царства греческой мифологии: быстрые бои и глубокий сюжет.','Try to escape the underworld of Greek myth: fast combat and a deep story.'),
('valheim','Valheim','sandbox',2021,'Викингтердің құпия әлемінде аман қал, құрылыс сал және құдайларға жол тап.','Iron Gate Studio','PC, Xbox','Single-player / Online',4.7,4990,892970,'forest','VLHM',0,'Выживай в мифическом мире викингов, строй дома и готовься к встрече с богами.','Survive a mythic Viking world, build your home and face the gods.'),
('dead-by-daylight','Dead by Daylight','horror',2016,'Төртеуі қашады, біреуі аулайды. Асимметриялық хоррордағы шиеленіс пен тактика.','Behaviour Interactive','PC, Console, Mobile','Online multiplayer',4.1,5990,381210,'crimson','DBD',0,'Четверо бегут, один охотится. Напряжение и тактика в асимметричном хорроре.','Four run, one hunts. Tension and tactics in asymmetric horror.'),
('doom-eternal','DOOM Eternal','action',2020,'Тозақтың әскерімен жылдам әрі қатал шайқас. Таза экшн қуаты.','Bethesda Softworks','PC, Console','Single-player / Online',4.7,9990,782330,'ember','DOOM',0,'Стремительные и жестокие бои с силами ада. Чистый заряд экшена.','Fast, brutal combat against the forces of hell. Pure action.'),
('warframe','Warframe','action',2013,'Ғарыштағы ninja-жауынгерлер: жылдам экшн, кооператив және үнемі жаңаратын оқиға.','Digital Extremes','PC, Console, Mobile','Online co-op',4.5,0,230410,'neon','WF',0,'Космические воины-ниндзя: динамичный экшен, кооператив и постоянно обновляемая история.','Space ninja warriors: fast action, co-op and an ever-evolving story.'),
('black-myth-wukong','Black Myth: Wukong','action',2024,'Қытайлық «Батысқа сапар» аңызына негізделген әсем әрі қиын экшн-рөлдік ойын.','Game Science','PC, PlayStation','Single-player',4.7,22990,2358720,'gold','BMW',1,'Красивый и сложный экшн-RPG по мотивам китайской легенды «Путешествие на Запад».','A stunning and challenging action RPG inspired by the Chinese legend Journey to the West.'),
('rust','Rust','sandbox',2018,'Жабайы әлемде аман қал: ресурс жина, база сал және басқа ойыншылармен бәсекелес.','Facepunch Studios','PC, Console','Online multiplayer',4.2,9990,252490,'ember','RUST',0,'Выживай в суровом мире: добывай ресурсы, строй базу и соревнуйся с другими игроками.','Survive a harsh world: gather resources, build a base and outwit other players.'),
('silent-hill-2','Silent Hill 2','horror',2024,'Тұманды қалада жоғалған жандарды іздеген психологиялық хоррордың қайта жасалуы.','Konami','PC, PlayStation','Single-player',4.7,21990,2124490,'ocean','SH2',0,'Ремейк психологического хоррора о поисках в туманном городе.','A remake of the psychological horror about a search through a fog-bound town.'),
('disco-elysium','Disco Elysium','rpg',2019,'Детектив-рөлдік ойын: диалогтар, шешімдер және әр жауаптың салдары.','ZA/UM','PC, Console, Mac','Single-player',4.8,9990,632470,'violet','DISCO',0,'Детективная RPG: диалоги, решения и последствия каждого ответа.','A detective RPG built on dialogue, choices and consequences.'),
('celeste','Celeste','adventure',2018,'Таудың шыңына шығуға тырысқан қыздың жүрекке жеткен тарихы мен қиын платформер.','Maddy Makes Games','PC, Console','Single-player',4.9,3490,504230,'neon','CEL',0,'Трогательная история девушки, покоряющей гору, и сложный платформер.','A heartfelt story of a climber and a tough, precise platformer.'),
('team-fortress-2','Team Fortress 2','combat',2007,'Түрлі кейіпкерлермен командалық шутер: стиль, юмор және тактика.','Valve','PC','Online multiplayer',4.6,0,440,'gold','TF2',0,'Командный шутер с яркими классами героев: стиль, юмор и тактика.','A team shooter with colorful classes: style, humor and tactics.'),
('destiny-2','Destiny 2','combat',2017,'Күннің жүйесін қорғап, достарыңмен рейдтерге қатыс. Шутер мен рөлдік ойынның қоспасы.','Bungie','PC, PlayStation, Xbox','Online co-op',4.3,0,1085660,'violet','DEST',0,'Защищай Солнечную систему и ходи в рейды с друзьями. Сплав шутера и RPG.','Defend the solar system and raid with friends in this shooter-RPG blend.')
ON DUPLICATE KEY UPDATE title=VALUES(title), genre=VALUES(genre), release_year=VALUES(release_year), description=VALUES(description), publisher=VALUES(publisher), platforms=VALUES(platforms), game_mode=VALUES(game_mode), rating=VALUES(rating), price=VALUES(price), steam_app_id=VALUES(steam_app_id), theme=VALUES(theme), code=VALUES(code), is_featured=VALUES(is_featured), description_ru=COALESCE(VALUES(description_ru), description_ru), description_en=COALESCE(VALUES(description_en), description_en);

-- Gallery: landscape Steam artwork for every game that has a steam_app_id.
-- Add your own screenshots in the admin panel (edit_game.php): they are uploaded to assets/uploads/games/.
-- INSERT IGNORE + unique key (game_id, sort_order) make this safe to run more than once.
INSERT IGNORE INTO game_images (game_id, image_url, sort_order)
SELECT g.id, CONCAT('https://cdn.cloudflare.steamstatic.com/steam/apps/', g.steam_app_id, '/header.jpg'), 1 FROM games g WHERE g.steam_app_id IS NOT NULL
UNION ALL
SELECT g.id, CONCAT('https://cdn.cloudflare.steamstatic.com/steam/apps/', g.steam_app_id, '/library_hero.jpg'), 2 FROM games g WHERE g.steam_app_id IS NOT NULL
UNION ALL
SELECT g.id, CONCAT('https://cdn.cloudflare.steamstatic.com/steam/apps/', g.steam_app_id, '/capsule_616x353.jpg'), 3 FROM games g WHERE g.steam_app_id IS NOT NULL;

-- Make a user an administrator (replace the e-mail):
-- UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
