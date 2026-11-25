# Playora — каталог компьютерных игр

PHP + MySQL. Поиск, фильтр по жанрам, избранное, отзывы, вход через почту или Google, админ-панель.

## Запуск (XAMPP / OpenServer)
1. Скопируйте папку проекта в `htdocs` (или `domains`).
2. В phpMyAdmin импортируйте `database.sql`.
   *Если база уже была создана раньше:* сначала выполните `migration.sql`, затем ещё раз импортируйте `database.sql`.
3. Подключение к БД — `config/database.php` (по умолчанию `root` без пароля; на хостинге задайте переменные окружения `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
4. Откройте `http://localhost/playora12/`.

## Структура
```
index.php            главная: каталог, поиск, жанры, сортировка, страницы
game.php             страница игры: описание, галерея скриншотов, отзывы
favorites.php        избранное
login.php            вход (почта / Google)
register.php         регистрация
google_login.php     приём ответа Google Sign-In
logout.php           выход
help.php             помощь: FAQ и контакты
games.php            админ: список, поиск, удаление игр
add_game.php         админ: добавить игру
edit_game.php        админ: изменить игру, обложка и скриншоты
toggle_favorite.php  добавить/убрать из избранного
review_action.php    сохранить/удалить отзыв
config/              database.php, settings.php
includes/            header, footer, functions, lang (kk/ru/en), admin_games, google_button
assets/              css, js, uploads/games (загруженные картинки)
database.sql         схема + 60 игр
migration.sql        обновление старой базы
```

## Контакты на странице «Помощь»
Откройте `config/settings.php` и укажите настоящие данные:
```php
const SUPPORT_EMAIL = 'ваша_почта@gmail.com';
const SUPPORT_PHONE = '+7 700 000 00 00';   // пусто = телефон не показывается
```

## Администратор
1. Зарегистрируйтесь на сайте.
2. В phpMyAdmin выполните:
   `UPDATE users SET role = 'admin' WHERE email = 'ваша_почта@gmail.com';`
3. Войдите заново — в меню появится «Админ-панель» (`games.php`).

Там можно добавлять, изменять и удалять игры, загружать обложку и **скриншоты** (JPG/PNG/WEBP до 5 МБ). Файлы сохраняются в `assets/uploads/games/`.

## Вход через Google
1. https://console.cloud.google.com → APIs & Services → Credentials → Create credentials → **OAuth client ID** (тип *Web application*).
2. *Authorized JavaScript origins*: адрес сайта (например `http://localhost`, `https://ваш-домен.kz`).
3. *Authorized redirect URIs*: `https://ваш-домен.kz/путь/google_login.php` (для локального теста — `http://localhost/playora12/google_login.php`).
4. Скопируйте Client ID и задайте его: в `config/settings.php` замените
   `getenv('PLAYORA_GOOGLE_CLIENT_ID') ?: ''` на `getenv('PLAYORA_GOOGLE_CLIENT_ID') ?: 'ВАШ_CLIENT_ID.apps.googleusercontent.com'`.
Пока Client ID пустой, кнопка «Продолжить с Google» показывает сообщение, что вход скоро появится.
