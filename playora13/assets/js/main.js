'use strict';

const languageSelect = document.querySelector('#languageSelect');
if (languageSelect) {
  languageSelect.addEventListener('change', () => {
    const url = new URL(window.location.href);
    url.searchParams.set('lang', languageSelect.value);
    url.hash = '';
    window.location.href = url.toString();
  });
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
    showPasswordButton.textContent = visible ? showPasswordButton.dataset.hide : showPasswordButton.dataset.show;
  });
}

const registerForm = document.querySelector('#registerForm');
if (registerForm) {
  const password = document.querySelector('#registerPassword');
  const confirmPassword = document.querySelector('#confirmPassword');
  registerForm.addEventListener('submit', (event) => {
    confirmPassword.setCustomValidity('');
    if (password.value !== confirmPassword.value) {
      confirmPassword.setCustomValidity(registerForm.dataset.mismatch || 'Passwords do not match.');
      confirmPassword.reportValidity();
      event.preventDefault();
    }
  });
  confirmPassword.addEventListener('input', () => confirmPassword.setCustomValidity(''));
}

const toast = document.querySelector('#toast');
if (toast && toast.dataset.message) {
  toast.textContent = toast.dataset.message;
  window.setTimeout(() => toast.classList.add('show'), 120);
  window.setTimeout(() => toast.classList.remove('show'), 3800);
}

document.querySelectorAll('form[data-confirm]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  });
});

const gallery = document.querySelector('[data-gallery]');
if (gallery) {
  const section = gallery.closest('.detail-gallery-section');
  const refreshSection = () => {
    if (section && !gallery.querySelector('.gallery-item')) section.hidden = true;
  };

  gallery.querySelectorAll('.gallery-item').forEach((item) => {
    const img = item.querySelector('img');
    if (!img) return;
    const drop = () => { item.remove(); refreshSection(); };
    const hosts = ['https://cdn.cloudflare.steamstatic.com/steam/apps/', 'https://shared.akamai.steamstatic.com/store_item_assets/steam/apps/', 'https://cdn.akamai.steamstatic.com/steam/apps/'];
    let attempt = 0;
    img.addEventListener('error', () => {
      const m = img.src.match(/steam\/apps\/(\d+\/[^?]+)$/);
      if (m && attempt < hosts.length) {
        img.src = hosts[attempt++] + m[1];
        item.dataset.full = img.src;
        return;
      }
      drop();
    });
    if (img.complete && img.naturalWidth === 0 && img.currentSrc) drop();
  });

  let lightbox = null;
  let current = 0;

  const items = () => Array.from(gallery.querySelectorAll('.gallery-item'));
  const show = (index) => {
    const list = items();
    if (!list.length || !lightbox) return;
    current = (index + list.length) % list.length;
    lightbox.querySelector('img').src = list[current].dataset.full;
  };
  const close = () => {
    if (!lightbox) return;
    lightbox.remove();
    lightbox = null;
    document.removeEventListener('keydown', onKey);
    document.body.style.overflow = '';
  };
  const onKey = (event) => {
    if (event.key === 'Escape') close();
    if (event.key === 'ArrowRight') show(current + 1);
    if (event.key === 'ArrowLeft') show(current - 1);
  };
  const open = (index) => {
    lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.setAttribute('role', 'dialog');
    lightbox.setAttribute('aria-modal', 'true');
    lightbox.innerHTML = '<img alt=""><button type="button" class="lb-close"></button><button type="button" class="lb-prev"></button><button type="button" class="lb-next"></button>';
    const closeBtn = lightbox.querySelector('.lb-close');
    const prevBtn = lightbox.querySelector('.lb-prev');
    const nextBtn = lightbox.querySelector('.lb-next');
    closeBtn.textContent = '×';
    prevBtn.textContent = '‹';
    nextBtn.textContent = '›';
    closeBtn.setAttribute('aria-label', gallery.dataset.close || 'Close');
    prevBtn.setAttribute('aria-label', gallery.dataset.prev || 'Previous');
    nextBtn.setAttribute('aria-label', gallery.dataset.next || 'Next');
    closeBtn.addEventListener('click', close);
    prevBtn.addEventListener('click', (event) => { event.stopPropagation(); show(current - 1); });
    nextBtn.addEventListener('click', (event) => { event.stopPropagation(); show(current + 1); });
    lightbox.addEventListener('click', (event) => { if (event.target === lightbox) close(); });
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
    document.addEventListener('keydown', onKey);
    show(index);
  };

  gallery.addEventListener('click', (event) => {
    const item = event.target.closest('.gallery-item');
    if (item) open(items().indexOf(item));
  });
}

// Gallery: Play Market style arrows + try other Steam CDN mirrors before dropping a broken screenshot
(() => {
  const strip = document.querySelector('.gallery-grid');
  if (!strip) return;
  const wrap = strip.closest('.gallery-wrap');
  const step = () => Math.max(240, Math.round(strip.clientWidth * 0.8));
  wrap?.querySelector('.gallery-prev')?.addEventListener('click', () => strip.scrollBy({ left: -step() }));
  wrap?.querySelector('.gallery-next')?.addEventListener('click', () => strip.scrollBy({ left: step() }));
})();
