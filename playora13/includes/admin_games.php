<?php
// Shared helpers for the admin pages: games.php, add_game.php, edit_game.php.
declare(strict_types=1);

function admin_genre_labels(): array
{
    return [
        'action' => 'Экшн', 'combat' => 'Шутер / бои', 'horror' => 'Хоррор', 'adventure' => 'Приключения',
        'rpg' => 'RPG', 'multiplayer' => 'Мультиплеер', 'sandbox' => 'Песочница',
        'simulation' => 'Симулятор', 'strategy' => 'Стратегия',
    ];
}

function admin_modes(): array
{
    return ['Single-player', 'Single-player / Online', 'Single-player / Co-op', 'Online', 'Online multiplayer', 'Online co-op'];
}

function admin_themes(): array
{
    return ['cyber', 'elden', 'minecraft', 'genshin', 'valorant', 'gta', 'witcher', 'phasmo', 'dota', 'apex', 'roblox', 'ffxiv',
        'neon', 'ember', 'ocean', 'forest', 'violet', 'crimson', 'gold'];
}

function admin_slugify(string $text): string
{
    $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $text), '-'));
    return $slug;
}

function admin_defaults(): array
{
    return [
        'title' => '', 'slug' => '', 'genre' => 'action', 'release_year' => (int) date('Y'),
        'publisher' => '', 'platforms' => 'PC', 'game_mode' => 'Single-player', 'rating' => '4.0', 'price' => '0',
        'steam_app_id' => '', 'theme' => 'neon', 'code' => '', 'is_featured' => 0,
        'description' => '', 'description_ru' => '', 'description_en' => '', 'cover_image' => '',
    ];
}

/** Reads and validates the game form. Returns [data, errors]. */
function admin_collect_game(PDO $pdo, array $post, ?int $editingId): array
{
    $errors = [];
    $d = admin_defaults();
    foreach (['title', 'slug', 'publisher', 'platforms', 'game_mode', 'theme', 'code', 'description', 'description_ru', 'description_en', 'cover_image'] as $key) {
        $d[$key] = trim((string) ($post[$key] ?? ''));
    }
    $d['genre'] = (string) ($post['genre'] ?? '');
    $d['release_year'] = (int) ($post['release_year'] ?? 0);
    $d['rating'] = str_replace(',', '.', trim((string) ($post['rating'] ?? '0')));
    $d['price'] = str_replace(',', '.', trim((string) ($post['price'] ?? '0')));
    $d['steam_app_id'] = trim((string) ($post['steam_app_id'] ?? ''));
    $d['is_featured'] = isset($post['is_featured']) ? 1 : 0;

    if ($d['title'] === '' || mb_strlen($d['title']) > 160) {
        $errors[] = 'Название обязательно (до 160 символов).';
    }
    if ($d['slug'] === '') {
        $d['slug'] = admin_slugify($d['title']);
    }
    $d['slug'] = strtolower($d['slug']);
    if (!preg_match('/^[a-z0-9-]{2,120}$/', $d['slug'])) {
        $errors[] = 'Адрес (slug): только латиница, цифры и дефис, 2–120 символов. Введите его вручную, если название не на латинице.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM games WHERE slug = :slug AND id <> :id LIMIT 1');
        $stmt->execute(['slug' => $d['slug'], 'id' => $editingId ?? 0]);
        if ($stmt->fetch()) {
            $errors[] = 'Игра с таким адресом (slug) уже существует.';
        }
    }
    if (!in_array($d['genre'], game_genres(), true)) {
        $errors[] = 'Выберите жанр.';
    }
    if ($d['release_year'] < 1970 || $d['release_year'] > (int) date('Y') + 3) {
        $errors[] = 'Укажите корректный год выпуска.';
    }
    if ($d['publisher'] === '' || mb_strlen($d['publisher']) > 160) {
        $errors[] = 'Издатель обязателен (до 160 символов).';
    }
    if ($d['platforms'] === '' || mb_strlen($d['platforms']) > 180) {
        $errors[] = 'Платформы: до 180 символов.';
    }
    if (!in_array($d['game_mode'], admin_modes(), true)) {
        $errors[] = 'Выберите режим игры.';
    }
    if (!is_numeric($d['rating']) || (float) $d['rating'] < 0 || (float) $d['rating'] > 5) {
        $errors[] = 'Рейтинг должен быть числом от 0 до 5.';
    }
    if (!is_numeric($d['price']) || (float) $d['price'] < 0) {
        $errors[] = 'Цена должна быть числом 0 или больше (в тенге).';
    }
    if ($d['steam_app_id'] !== '' && !ctype_digit($d['steam_app_id'])) {
        $errors[] = 'Steam App ID — только цифры (или оставьте пустым).';
    }
    if (!in_array($d['theme'], admin_themes(), true)) {
        $d['theme'] = 'neon';
    }
    if ($d['code'] === '') {
        $d['code'] = strtoupper(substr((string) preg_replace('/[^A-Za-z0-9]/', '', $d['title']), 0, 4)) ?: 'PLAY';
    }
    $d['code'] = mb_substr($d['code'], 0, 12);
    if ($d['description'] === '') {
        $errors[] = 'Описание (казахский) обязательно.';
    }
    if ($d['cover_image'] !== '' && !preg_match('#^https?://#i', $d['cover_image']) && strpos($d['cover_image'], UPLOAD_URL . '/') !== 0) {
        $errors[] = 'Ссылка на обложку должна начинаться с http:// или https://.';
    }
    return [$d, $errors];
}

/** Normalises $_FILES['x'] (multiple or single) to a list of single-file arrays. */
function admin_files(string $field): array
{
    if (empty($_FILES[$field])) {
        return [];
    }
    $f = $_FILES[$field];
    if (!is_array($f['name'])) {
        return [$f];
    }
    $list = [];
    foreach ($f['name'] as $i => $name) {
        $list[] = ['name' => $name, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $list;
}

/** Validates and stores one uploaded image. Returns the site-relative path, or null. */
function admin_upload_image(array $file, string $slug, array &$errors): ?string
{
    $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $label = (string) ($file['name'] ?? 'файл');
    if ($err !== UPLOAD_ERR_OK) {
        $errors[] = "Не удалось загрузить «{$label}» (код ошибки {$err}).";
        return null;
    }
    if ((int) $file['size'] > UPLOAD_MAX_BYTES) {
        $errors[] = "«{$label}» больше 5 МБ.";
        return null;
    }
    $info = @getimagesize((string) $file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($types[$info[2]])) {
        $errors[] = "«{$label}»: разрешены только JPG, PNG и WEBP.";
        return null;
    }
    if (!is_dir(UPLOAD_DIR) && !@mkdir(UPLOAD_DIR, 0755, true)) {
        $errors[] = 'Папка assets/uploads/games недоступна для записи.';
        return null;
    }
    $name = $slug . '-' . bin2hex(random_bytes(4)) . '.' . $types[$info[2]];
    if (!move_uploaded_file((string) $file['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        $errors[] = "Не удалось сохранить «{$label}».";
        return null;
    }
    return UPLOAD_URL . '/' . $name;
}

function admin_delete_local_file(?string $path): void
{
    if ($path && strpos($path, UPLOAD_URL . '/') === 0) {
        $file = UPLOAD_DIR . '/' . basename($path);
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

/** Adds screenshots (uploaded files + URLs from the textarea) to a game. */
function admin_add_screenshots(PDO $pdo, int $gameId, string $slug, array &$errors): void
{
    $paths = [];
    foreach (admin_files('screenshots') as $file) {
        $path = admin_upload_image($file, $slug, $errors);
        if ($path !== null) {
            $paths[] = $path;
        }
    }
    foreach (preg_split('/\R+/', (string) ($_POST['screenshot_urls'] ?? '')) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        if (preg_match('#^https?://\S{4,480}$#i', $line)) {
            $paths[] = $line;
        } else {
            $errors[] = "Некорректная ссылка на скриншот: {$line}";
        }
    }
    if (!$paths) {
        return;
    }
    $max = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM game_images WHERE game_id = :id');
    $max->execute(['id' => $gameId]);
    $order = (int) $max->fetchColumn();
    $insert = $pdo->prepare('INSERT INTO game_images (game_id, image_url, sort_order) VALUES (:g, :u, :o)');
    foreach ($paths as $path) {
        if ($order >= 250) {
            $errors[] = 'Достигнут лимит скриншотов для одной игры.';
            break;
        }
        $order++;
        $insert->execute(['g' => $gameId, 'u' => $path, 'o' => $order]);
    }
}

/** Renders the shared add/edit form. */
function admin_render_form(array $g, array $images, array $errors, string $submitLabel, bool $isEdit): void
{
    ?>
    <?php if ($errors): ?>
        <div class="notice notice-error" role="alert"><?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?></div>
    <?php endif; ?>
    <form class="admin-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="admin-grid">
            <label>Название *<input name="title" required maxlength="160" value="<?= e((string) $g['title']) ?>"></label>
            <label>Адрес (slug)<input name="slug" maxlength="120" placeholder="например, elden-ring (можно пусто — создастся из названия)" value="<?= e((string) $g['slug']) ?>"></label>
            <label>Жанр *
                <select name="genre"><?php foreach (admin_genre_labels() as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $g['genre'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?></select>
            </label>
            <label>Год выпуска *<input name="release_year" type="number" min="1970" max="<?= (int) date('Y') + 3 ?>" required value="<?= e((string) $g['release_year']) ?>"></label>
            <label>Издатель / студия *<input name="publisher" required maxlength="160" value="<?= e((string) $g['publisher']) ?>"></label>
            <label>Платформы<input name="platforms" maxlength="180" placeholder="PC, PlayStation, Xbox" value="<?= e((string) $g['platforms']) ?>"></label>
            <label>Режим игры
                <select name="game_mode"><?php foreach (admin_modes() as $mode): ?>
                    <option value="<?= e($mode) ?>" <?= $g['game_mode'] === $mode ? 'selected' : '' ?>><?= e($mode) ?></option>
                <?php endforeach; ?></select>
            </label>
            <label>Рейтинг (0–5)<input name="rating" inputmode="decimal" value="<?= e((string) $g['rating']) ?>"></label>
            <label>Цена, ₸ (0 = бесплатно)<input name="price" inputmode="decimal" value="<?= e((string) $g['price']) ?>"></label>
            <label>Steam App ID (необязательно)<input name="steam_app_id" inputmode="numeric" placeholder="например, 1245620" value="<?= e((string) $g['steam_app_id']) ?>"></label>
            <label>Цвет-заглушка
                <select name="theme"><?php foreach (admin_themes() as $theme): ?>
                    <option value="<?= e($theme) ?>" <?= $g['theme'] === $theme ? 'selected' : '' ?>><?= e($theme) ?></option>
                <?php endforeach; ?></select>
            </label>
            <label>Короткий код на карточке<input name="code" maxlength="12" placeholder="например, ER" value="<?= e((string) $g['code']) ?>"></label>
        </div>

        <label class="admin-check"><input type="checkbox" name="is_featured" value="1" <?= !empty($g['is_featured']) ? 'checked' : '' ?>> Показывать в главном баннере («Избранная игра»)</label>

        <label>Описание (қазақша) *<textarea name="description" rows="3" required><?= e((string) $g['description']) ?></textarea></label>
        <label>Описание (русский)<textarea name="description_ru" rows="3"><?= e((string) $g['description_ru']) ?></textarea></label>
        <label>Описание (English)<textarea name="description_en" rows="3"><?= e((string) $g['description_en']) ?></textarea></label>

        <fieldset class="admin-fieldset">
            <legend>Обложка</legend>
            <?php if (!empty($g['cover_image'])): ?>
                <p><img class="admin-thumb" src="<?= e(image_public_url((string) $g['cover_image'])) ?>" alt="Текущая обложка"></p>
                <label class="admin-check"><input type="checkbox" name="remove_cover" value="1"> Удалить текущую обложку</label>
            <?php endif; ?>
            <label>Загрузить файл (JPG/PNG/WEBP, до 5 МБ)<input type="file" name="cover_file" accept="image/jpeg,image/png,image/webp"></label>
            <label>…или ссылка на картинку<input name="cover_image" placeholder="https://…" value="<?= e((string) $g['cover_image']) ?>"></label>
            <small>Если обложки нет, берётся картинка из Steam по App ID, а если и её нет — цветная заглушка.</small>
        </fieldset>

        <fieldset class="admin-fieldset">
            <legend>Скриншоты (галерея на странице игры)</legend>
            <?php if ($images): ?>
                <div class="admin-shots">
                    <?php foreach ($images as $img): ?>
                        <label class="admin-shot">
                            <img src="<?= e(image_public_url((string) $img['image_url'])) ?>" alt="" loading="lazy" referrerpolicy="no-referrer">
                            <span><input type="checkbox" name="delete_images[]" value="<?= (int) $img['id'] ?>"> удалить</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <label>Добавить файлы (можно выбрать несколько)<input type="file" name="screenshots[]" accept="image/jpeg,image/png,image/webp" multiple></label>
            <label>…или ссылки на скриншоты (по одной в строке)<textarea name="screenshot_urls" rows="3" placeholder="https://…"></textarea></label>
        </fieldset>

        <div class="admin-actions">
            <button class="button" type="submit"><?= e($submitLabel) ?></button>
            <a class="spotlight-save" href="games.php">Отмена</a>
        </div>
    </form>
    <?php
}
