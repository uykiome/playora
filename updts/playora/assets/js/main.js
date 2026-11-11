'use strict';

const translations = {
  kk: { navHome: 'Басты бет', navCatalog: 'Каталог', navCategories: 'Жанрлар', navAbout: 'Жоба туралы', navLogin: 'Кіру', navRegister: 'Тіркелу', navFavorites: 'Таңдаулылар', navLogout: 'Шығу', discoverEyebrow: 'ІЗДЕУ ЖӘНЕ ТАБУ', discoverTitle: 'Қандай ойын іздеп жүрсің?', discoverDescription: 'Атын жаз немесе жанрды таңда — нәтижелер бірден жаңарады.', quickFilters: 'ЖЫЛДАМ СҮЗГІЛЕР', heroEyebrow: 'ТАҢДАЛҒАН ОЙЫНДАР', heroKicker: 'PLAYORA ТАҢДАУЫ', heroDetails: 'Ойынды көру', catalogEyebrow: 'ТОЛЫҚ КІТАПХАНА', sortLabel: 'Сұрыптау:', footerNote: 'Оқу жобасы.', backToTop: 'Жоғарыға ↑', loginTitle: 'Жүйеге кіру', registerTitle: 'Тіркелу', searchPlaceholder: 'Ойын атауын енгізіңіз' },
  ru: { navHome: 'Главная', navCatalog: 'Каталог', navCategories: 'Жанры', navAbout: 'О проекте', navLogin: 'Войти', navRegister: 'Регистрация', navFavorites: 'Избранное', navLogout: 'Выйти', discoverEyebrow: 'ПОИСК И ОТКРЫТИЯ', discoverTitle: 'Какую игру ищешь?', discoverDescription: 'Введи название или выбери жанр — результаты обновятся.', quickFilters: 'БЫСТРЫЕ ФИЛЬТРЫ', heroEyebrow: 'ИЗБРАННЫЕ ИГРЫ', heroKicker: 'ВЫБОР PLAYORA', heroDetails: 'Об игре', catalogEyebrow: 'ВСЯ БИБЛИОТЕКА', sortLabel: 'Сортировка:', footerNote: 'Учебный проект.', backToTop: 'Наверх ↑', loginTitle: 'Войти', registerTitle: 'Регистрация', searchPlaceholder: 'Введите название игры' },
  en: { navHome: 'Home', navCatalog: 'Catalog', navCategories: 'Genres', navAbout: 'About', navLogin: 'Log in', navRegister: 'Sign up', navFavorites: 'Favorites', navLogout: 'Log out', discoverEyebrow: 'SEARCH & DISCOVER', discoverTitle: 'What do you want to play?', discoverDescription: 'Enter a title or choose a genre to explore.', quickFilters: 'QUICK FILTERS', heroEyebrow: 'FEATURED GAMES', heroKicker: 'PLAYORA PICK', heroDetails: 'View game', catalogEyebrow: 'THE FULL LIBRARY', sortLabel: 'Sort:', footerNote: 'Student project.', backToTop: 'Back to top ↑', loginTitle: 'Log in', registerTitle: 'Sign up', searchPlaceholder: 'Enter game name' }
};

function applyLanguage(language) {
  const lang = translations[language] ? language : 'kk';
  document.documentElement.lang = lang;
  try { localStorage.setItem('playoraLanguage', lang); } catch (_) {}
  document.querySelectorAll('#languageSelect').forEach((select) => { select.value = lang; });
  document.querySelectorAll('[data-i18n]').forEach((element) => {
    const key = element.dataset.i18n;
    if (translations[lang][key]) element.textContent = translations[lang][key];
  });
  document.querySelectorAll('[data-i18n-placeholder]').forEach((element) => {
    const value = translations[lang][element.dataset.i18nPlaceholder];
    if (value) element.setAttribute('placeholder', value);
  });
}

const languageSelect = document.querySelector('#languageSelect');
let savedLanguage = 'kk';
try { savedLanguage = localStorage.getItem('playoraLanguage') || 'kk'; } catch (_) {}
if (languageSelect) {
  languageSelect.addEventListener('change', () => applyLanguage(languageSelect.value));
  applyLanguage(savedLanguage);
}

const menuToggle = document.querySelector('.menu-toggle');
const mainNav = document.querySelector('.main-nav');
if (menuToggle && mainNav) {
  menuToggle.addEventListener('click', () => {
    const opened = mainNav.classList.toggle('mobile-open');
    menuToggle.setAttribute('aria-expanded', String(opened));
    menuToggle.textContent = opened ? '×' : '☰';
  });
  mainNav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
    mainNav.classList.remove('mobile-open');
    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.textContent = '☰';
  }));
}

const showPasswordButton = document.querySelector('#showPassword');
const passwordInput = document.querySelector('#loginPassword');
if (showPasswordButton && passwordInput) {
  showPasswordButton.addEventListener('click', () => {
    const visible = passwordInput.type === 'password';
    passwordInput.type = visible ? 'text' : 'password';
    const lang = document.documentElement.lang || 'kk';
    showPasswordButton.textContent = visible ? (lang === 'ru' ? 'Скрыть' : lang === 'en' ? 'Hide' : 'Жасыру') : (lang === 'ru' ? 'Показать' : lang === 'en' ? 'Show' : 'Көрсету');
  });
}

// Forms are submitted to PHP. JavaScript does not cancel form submission.
const registerForm = document.querySelector('#registerForm');
if (registerForm) {
  const password = document.querySelector('#registerPassword');
  const confirmPassword = document.querySelector('#confirmPassword');
  registerForm.addEventListener('submit', (event) => {
    confirmPassword.setCustomValidity('');
    if (password.value !== confirmPassword.value) {
      confirmPassword.setCustomValidity('Құпиясөздер бірдей емес.');
      confirmPassword.reportValidity();
      event.preventDefault();
    }
  });
  confirmPassword.addEventListener('input', () => confirmPassword.setCustomValidity(''));
}
