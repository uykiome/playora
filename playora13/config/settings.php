<?php
declare(strict_types=1);

// ---- Контакты поддержки (показываются на странице «Помощь») ----
// TODO: замените на реальные данные перед презентацией.
const SUPPORT_EMAIL = 'playora.help@example.com';
const SUPPORT_PHONE = '';            // например '+7 700 000 00 00' (пусто = не показывать)

const GAMES_PER_PAGE = 8;
const REVIEW_MAX_LENGTH = 1000;

// ---- Вход через Google ----
// Client ID берётся в Google Cloud Console (см. README.md). Пусто = кнопка Google работает как подсказка.
define('GOOGLE_CLIENT_ID', getenv('PLAYORA_GOOGLE_CLIENT_ID') ?: '');

// ---- Загрузка изображений (админ-панель) ----
const UPLOAD_DIR = __DIR__ . '/../assets/uploads/games';
const UPLOAD_URL = 'assets/uploads/games';
const UPLOAD_MAX_BYTES = 5 * 1024 * 1024;
