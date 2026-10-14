/**
 * RUPE CDMX — Sistema unificado de alertas / toasts (ES6)
 * Sustituye alert() nativo en login, registro, contacto, CRUD y portal.
 */
'use strict';

(function (global) {
  const ICONS = {
    success: 'bi-check-circle-fill',
    danger: 'bi-exclamation-triangle-fill',
    error: 'bi-exclamation-triangle-fill',
    warning: 'bi-exclamation-circle-fill',
    info: 'bi-info-circle-fill',
  };

  const COLORS = {
    success: { bg: '#198754', fg: '#fff' },
    danger: { bg: '#dc3545', fg: '#fff' },
    error: { bg: '#dc3545', fg: '#fff' },
    warning: { bg: '#E8A33D', fg: '#1a1a1a' },
    info: { bg: '#0dcaf0', fg: '#062830' },
  };

  function ensureContainer() {
    let c = document.getElementById('rupeToastContainer');
    if (c) return c;
    c = document.createElement('div');
    c.id = 'rupeToastContainer';
    c.setAttribute('aria-live', 'polite');
    c.setAttribute('aria-atomic', 'true');
    c.style.cssText =
      'position:fixed;top:1rem;right:1rem;z-index:10900;display:flex;flex-direction:column;gap:0.6rem;max-width:min(420px,calc(100vw - 1.5rem));pointer-events:none;';
    document.body.appendChild(c);
    return c;
  }

  /**
   * Toast flotante (reemplazo de alert())
   * @param {string} message
   * @param {'success'|'danger'|'error'|'warning'|'info'} [type='info']
   * @param {number} [duration=4000]
   */
  function toast(message, type = 'info', duration = 4000) {
    const t = String(type || 'info').toLowerCase();
    const key = t === 'error' ? 'error' : t;
    const palette = COLORS[key] || COLORS.info;
    const icon = ICONS[key] || ICONS.info;
    const container = ensureContainer();

    const el = document.createElement('div');
    el.className = 'rupe-toast';
    el.setAttribute('role', 'alert');
    el.style.cssText = [
      'pointer-events:auto',
      'display:flex',
      'align-items:flex-start',
      'gap:0.75rem',
      `background:${palette.bg}`,
      `color:${palette.fg}`,
      'padding:0.85rem 1rem',
      'border-radius:12px',
      'box-shadow:0 10px 28px rgba(0,0,0,.18)',
      'font-size:0.92rem',
      'line-height:1.35',
      'opacity:0',
      'transform:translateX(16px)',
      'transition:opacity .25s ease, transform .25s ease',
      'min-width:240px',
    ].join(';');

    el.innerHTML =
      `<i class="bi ${icon}" style="font-size:1.15rem;flex-shrink:0;margin-top:1px;"></i>` +
      `<div style="flex:1;word-break:break-word;">${escapeHtml(String(message || ''))}</div>` +
      `<button type="button" aria-label="Cerrar" style="background:transparent;border:0;color:inherit;opacity:.85;font-size:1.2rem;line-height:1;cursor:pointer;padding:0 0 0 .25rem;">&times;</button>`;

    const closeBtn = el.querySelector('button');
    const remove = () => {
      el.style.opacity = '0';
      el.style.transform = 'translateX(16px)';
      setTimeout(() => el.remove(), 250);
    };
    closeBtn.addEventListener('click', remove);

    container.appendChild(el);
    requestAnimationFrame(() => {
      el.style.opacity = '1';
      el.style.transform = 'translateX(0)';
    });

    if (duration > 0) {
      setTimeout(remove, duration);
    }
    return el;
  }

  /**
   * Alerta inline en formularios (login / registro / contacto)
   * @param {HTMLElement|string} [target]  elemento o id
   * @param {string} message
   * @param {'success'|'danger'|'warning'|'info'} [type='danger']
   */
  function formAlert(target, message, type = 'danger') {
    let el =
      typeof target === 'string'
        ? document.getElementById(target)
        : target;
    if (!el) {
      toast(message, type);
      return;
    }
    const t = String(type || 'danger').toLowerCase();
    const map = {
      success: 'alert-success',
      danger: 'alert-danger',
      error: 'alert-danger',
      warning: 'alert-warning',
      info: 'alert-info',
    };
    const icon = ICONS[t === 'error' ? 'error' : t] || ICONS.danger;
    el.className = `alert ${map[t] || 'alert-danger'} py-2 small d-flex align-items-start gap-2 mb-3`;
    el.innerHTML = `<i class="bi ${icon} flex-shrink-0"></i><span>${escapeHtml(String(message || ''))}</span>`;
    el.classList.remove('d-none');
    el.setAttribute('role', 'alert');
    try {
      el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } catch (_) {}
  }

  function hideFormAlert(target) {
    const el =
      typeof target === 'string' ? document.getElementById(target) : target;
    if (!el) return;
    el.classList.add('d-none');
    el.textContent = '';
  }

  function escapeHtml(str) {
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // API pública
  global.rupeToast = toast;
  global.rupeFormAlert = formAlert;
  global.rupeHideFormAlert = hideFormAlert;

  // Compatibilidad con código existente
  global.mostrarToast = function (msg, tipo) {
    toast(msg, tipo || 'success');
  };
  global.mostrarToastTutor = function (msg, tipo) {
    toast(msg, tipo || 'success');
  };

  // Reemplazo suave de alert() solo si se desea (no sobreescribimos window.alert por defecto)
  global.rupeAlert = function (msg) {
    toast(msg, 'info', 4500);
  };
})(typeof window !== 'undefined' ? window : globalThis);
