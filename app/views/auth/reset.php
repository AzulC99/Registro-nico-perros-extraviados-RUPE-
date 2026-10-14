<?php
/**
 * RUPE CDMX — Nueva contraseña (token + reCAPTCHA v3)
 * app/views/auth/reset.php
 */
declare(strict_types=1);
$token = (string) ($token ?? '');
$tokenOk = (bool) ($token_ok ?? false);
$tokenMsg = (string) ($token_msg ?? 'Enlace inválido o incompleto.');
$correoMask = (string) ($correo_mask ?? '');
$nombre = (string) ($nombre ?? '');
$siteKey = defined('RECAPTCHA_SITE_KEY') ? (string) RECAPTCHA_SITE_KEY : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nueva contraseña — RUPE CDMX</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root {
      --guinda: #6B1D2F;
      --guinda-dark: #4A1521;
      --bg: #F4F6F8;
    }
    body { background: var(--bg); min-height: 100vh; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
    .btn-guinda { background: var(--guinda); color: #fff; border: none; }
    .btn-guinda:hover { background: var(--guinda-dark); color: #fff; }
    .btn-guinda:disabled { opacity: .75; }
    .text-guinda { color: var(--guinda); }
    .card-auth { border: none; border-radius: 16px; box-shadow: 0 8px 24px rgba(107,29,47,.12); }
    .brand-bar { background: var(--guinda); color: #fff; border-radius: 16px 16px 0 0; padding: 1.25rem 1.5rem; }
    .rule-ok { color: #198754; }
    .rule-bad { color: #6c757d; }
  
    .grecaptcha-badge {
      visibility: visible !important;
      opacity: 1 !important;
      display: block !important;
      z-index: 2147483647 !important;
      position: fixed !important;
      bottom: 14px !important;
      right: 14px !important;
    }
    .rupe-recaptcha-status { font-size: 11px; color: #6c757d; margin-top: 0.35rem; }
    .rupe-recaptcha-status.ok { color: #198754; }
  </style>
  <?php if ($siteKey !== ''): ?>
  <script src="https://www.google.com/recaptcha/api.js?render=<?= e($siteKey) ?>"></script>
  <?php endif; ?>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card card-auth">
          <div class="brand-bar d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center bg-white rounded-2 px-2 py-1">
              <img src="/assets/img/logo-unadm.png" alt="UnADM" style="height:26px;width:auto;object-fit:contain;" onerror="this.style.display='none'">
            </span>
            <div>
              <div class="fw-bold">RUPE CDMX</div>
              <div class="small opacity-75">Restablecer contraseña</div>
            </div>
          </div>
          <div class="card-body p-4 p-md-5">
            <?php if (!$tokenOk): ?>
              <div class="alert alert-warning small mb-3">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <?= e($tokenMsg !== '' ? $tokenMsg : 'Enlace inválido o incompleto.') ?>
              </div>
              <p class="small text-muted mb-2">Si tienes el enlace del correo, pégalo aquí o solicita uno nuevo:</p>
              <div class="mb-3" id="boxAbrirToken">
                <label class="form-label small fw-semibold" for="tokenManual">Token o enlace completo del correo</label>
                <input type="text" class="form-control mb-2" name="token" id="tokenManual"
                       placeholder="Pega el token o la URL completa del correo"
                       value="<?= e($token) ?>" autocomplete="off" spellcheck="false">
                <button type="button" class="btn btn-outline-secondary w-100 mb-2" id="btnAbrirToken">
                  <i class="bi bi-link-45deg me-1"></i> Abrir con este token
                </button>
                <div class="small text-danger d-none" id="tokenManualErr"></div>
              </div>
              <script>
              (function () {
                function extraerToken(raw) {
                  raw = String(raw || '').trim();
                  if (!raw) return '';
                  var m = raw.match(/[?&]token=([a-fA-F0-9]{32,128})/i);
                  if (m) return m[1];
                  m = raw.match(/([a-fA-F0-9]{64})/);
                  if (m) return m[1];
                  return raw.replace(/\s+/g, '');
                }
                var btn = document.getElementById('btnAbrirToken');
                var inp = document.getElementById('tokenManual');
                var err = document.getElementById('tokenManualErr');
                if (!btn || !inp) return;
                function ir() {
                  var tok = extraerToken(inp.value);
                  if (!tok || tok.length < 32) {
                    if (err) {
                      err.textContent = 'Pega el enlace completo del correo o un token de al menos 32 caracteres.';
                      err.classList.remove('d-none');
                    }
                    inp.classList.add('is-invalid');
                    return;
                  }
                  if (err) err.classList.add('d-none');
                  inp.classList.remove('is-invalid');
                  btn.disabled = true;
                  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Abriendo…';
                  window.location.href = '/auth/reset?token=' + encodeURIComponent(tok);
                }
                btn.addEventListener('click', function (e) {
                  e.preventDefault();
                  e.stopPropagation();
                  ir();
                });
                inp.addEventListener('keydown', function (e) {
                  if (e.key === 'Enter') {
                    e.preventDefault();
                    ir();
                  }
                });
              })();
              </script>
              <a href="/auth/forgot" class="btn btn-guinda w-100 mb-2">
                <i class="bi bi-envelope me-1"></i> Solicitar enlace nuevo
              </a>
              <a href="/" class="btn btn-link w-100 small">Volver al inicio</a>
            <?php else: ?>
              <?php if ($correoMask !== ''): ?>
                <p class="text-secondary small mb-2">
                  Cuenta: <strong><?= e($correoMask) ?></strong>
                  <?php if ($nombre !== ''): ?> · <?= e($nombre) ?><?php endif; ?>
                </p>
              <?php endif; ?>
              <p class="text-secondary small mb-3">
                Elige una contraseña segura: exactamente <strong>8 caracteres</strong>, con
                <strong>mayúscula</strong>, <strong>minúscula</strong>, <strong>número</strong> y un
                <strong>carácter especial</strong> (!@#$…).
              </p>
              <div id="alertBox" class="alert d-none py-2 small" role="alert"></div>
              <form id="formReset" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="token" id="resetToken" value="<?= e($token) ?>">
                <div class="mb-3">
                  <label class="form-label fw-semibold" for="password">Nueva contraseña *</label>
                  <div class="input-group input-group-lg">
                    <input type="password" class="form-control" id="password" name="password"
                           required minlength="8" maxlength="8" autocomplete="new-password"
                           placeholder="Ej. Abcdef1!">
                    <button type="button" class="btn btn-outline-secondary btn-toggle-pass" data-target="password" aria-label="Mostrar u ocultar contraseña" title="Mostrar / ocultar">
                      <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                    <span class="input-group-text d-none text-success" id="okResetPass"><i class="bi bi-check-circle-fill"></i></span>
                  </div>
                  <ul class="list-unstyled small mb-0 mt-2" id="passRules">
                    <li data-rule="len"><i class="bi bi-circle me-1"></i>Exactamente 8 caracteres</li>
                    <li data-rule="upper"><i class="bi bi-circle me-1"></i>Una mayúscula</li>
                    <li data-rule="lower"><i class="bi bi-circle me-1"></i>Una minúscula</li>
                    <li data-rule="num"><i class="bi bi-circle me-1"></i>Un número</li>
                    <li data-rule="spec"><i class="bi bi-circle me-1"></i>Un carácter especial</li>
                  </ul>
                  <div class="invalid-feedback d-block" id="passwordError"></div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold" for="password_confirmation">Confirmar *</label>
                  <div class="input-group input-group-lg">
                    <input type="password" class="form-control" id="password_confirmation"
                           name="password_confirmation" required minlength="8" maxlength="8"
                           autocomplete="new-password" placeholder="Repite la contraseña (8 caracteres)">
                    <button type="button" class="btn btn-outline-secondary btn-toggle-pass" data-target="password_confirmation" aria-label="Mostrar u ocultar contraseña" title="Mostrar / ocultar">
                      <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                    <span class="input-group-text d-none text-success" id="okResetPassConf"><i class="bi bi-check-circle-fill"></i></span>
                  </div>
                  <div class="invalid-feedback d-block" id="passwordConfirmError"></div>
                </div>
                <button type="submit" id="btnReset" class="btn btn-guinda btn-lg w-100 mb-3">
                  <span class="btn-text"><i class="bi bi-check2-circle me-1"></i> Guardar contraseña</span>
                  <span class="btn-loading d-none">
                    <span class="spinner-border spinner-border-sm me-1"></span> Guardando…
                  </span>
                </button>
              </form>
            <?php endif; ?>
            <p class="small text-center mb-0">
              <a href="<?= e(url('/')) ?>" class="text-guinda text-decoration-none">
                <i class="bi bi-arrow-left"></i> Volver al inicio
              </a>
              ·
              <a href="<?= e(url('/auth/forgot')) ?>" class="text-guinda text-decoration-none">Olvidé mi contraseña</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="/assets/js/recaptcha.js?v=20260926rc1"></script>
  <script>
  window.__CSRF__ = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE) ?>;
  window.__RECAPTCHA_SITE_KEY__ = <?= json_encode($siteKey, JSON_UNESCAPED_UNICODE) ?>;
  window.__RESET_TOKEN_OK__ = <?= $tokenOk ? 'true' : 'false' ?>;
  </script>
  <script>
  (function () {
    if (!window.__RESET_TOKEN_OK__) return;

    const form = document.getElementById('formReset');
    if (!form) return;

    function showAlert(msg, type) {
      const box = document.getElementById('alertBox');
      if (!box) return;
      box.className = 'alert alert-' + (type || 'info') + ' py-2 small';
      box.innerHTML = msg;
      box.classList.remove('d-none');
    }

    function setLoading(on) {
      const btn = document.getElementById('btnReset');
      if (!btn) return;
      btn.disabled = !!on;
      const t = btn.querySelector('.btn-text');
      const l = btn.querySelector('.btn-loading');
      if (t) t.classList.toggle('d-none', !!on);
      if (l) l.classList.toggle('d-none', !on);
    }

    async function getRecaptchaToken(action) {
      const key = window.__RECAPTCHA_SITE_KEY__ || '';
      if (!key || typeof grecaptcha === 'undefined') return '';
      try {
        await new Promise(function (resolve) {
          try { grecaptcha.ready(resolve); } catch (_) { resolve(); }
        });
        return await grecaptcha.execute(key, { action: action || 'reset' });
      } catch (_) {
        return '';
      }
    }

    function validarPasswordFuerte(pwd) {
      const p = String(pwd || '');
      const rules = {
        len: p.length === 8,
        upper: /[A-Z]/.test(p),
        lower: /[a-z]/.test(p),
        num: /\d/.test(p),
        spec: /[^A-Za-z0-9]/.test(p)
      };
      const fails = [];
      if (!rules.len) fails.push('Exactamente 8 caracteres');
      if (!rules.upper) fails.push('Una mayúscula');
      if (!rules.lower) fails.push('Una minúscula');
      if (!rules.num) fails.push('Un número');
      if (!rules.spec) fails.push('Un carácter especial');
      return { ok: fails.length === 0, rules: rules, message: fails.join(' · ') };
    }

    function pintarReglas(pwd) {
      const r = validarPasswordFuerte(pwd);
      const ul = document.getElementById('passRules');
      if (ul) {
        ul.querySelectorAll('[data-rule]').forEach(function (li) {
          const k = li.getAttribute('data-rule');
          const ok = !!(r.rules && r.rules[k]);
          const icon = li.querySelector('i');
          if (icon) icon.className = ok ? 'bi bi-check-circle-fill text-success me-1 rule-ok' : 'bi bi-circle me-1 text-muted rule-bad';
          li.classList.toggle('rule-ok', ok);
          li.classList.toggle('rule-bad', !ok);
          li.classList.toggle('text-success', ok);
        });
      }
      const okP = document.getElementById('okResetPass');
      if (okP) okP.classList.toggle('d-none', !r.ok);
      return r;
    }

    function rupeValidarReset() {
      const passEl = document.getElementById('password');
      const confEl = document.getElementById('password_confirmation');
      const okC = document.getElementById('okResetPassConf');
      if (passEl && passEl.value.length > 8) passEl.value = passEl.value.slice(0, 8);
      if (confEl && confEl.value.length > 8) confEl.value = confEl.value.slice(0, 8);
      const r = pintarReglas(passEl ? passEl.value : '');
      if (passEl) {
        passEl.classList.toggle('is-valid', r.ok);
        passEl.classList.toggle('is-invalid', !r.ok && (passEl.value || '').length > 0);
      }
      const match = !!(confEl && passEl && confEl.value.length === 8 && confEl.value === passEl.value && r.ok);
      if (confEl) {
        confEl.classList.toggle('is-valid', match);
        confEl.classList.toggle('is-invalid', !match && (confEl.value || '').length > 0);
      }
      if (okC) okC.classList.toggle('d-none', !match);
      return r.ok && match;
    }
    window.rupeValidarReset = rupeValidarReset;

    const passEl = document.getElementById('password');
    const confEl = document.getElementById('password_confirmation');
    const errP = document.getElementById('passwordError');
    const errC = document.getElementById('passwordConfirmError');

    if (passEl) {
      passEl.addEventListener('input', function () {
        const r = rupeValidarReset();
        if (errP) {
          if (passEl.value.length > 0 && !validarPasswordFuerte(passEl.value).ok) errP.textContent = validarPasswordFuerte(passEl.value).message;
          else errP.textContent = '';
        }
        if (errC && confEl && confEl.value) {
          if (confEl.value !== passEl.value) errC.textContent = 'Las contraseñas no coinciden.';
          else if (!validarPasswordFuerte(passEl.value).ok) errC.textContent = 'La contraseña aún no cumple las reglas.';
          else errC.textContent = '';
        }
      });
    }
    if (confEl) {
      confEl.addEventListener('input', function () {
        rupeValidarReset();
        if (errC) {
          if (confEl.value.length > 0) {
            if (confEl.value !== (passEl && passEl.value)) errC.textContent = 'Las contraseñas no coinciden.';
            else if (!validarPasswordFuerte(passEl ? passEl.value : '').ok) errC.textContent = 'La contraseña aún no cumple las reglas.';
            else errC.textContent = '';
          } else errC.textContent = '';
        }
      });
    }

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      if (errP) errP.textContent = '';
      if (errC) errC.textContent = '';
      form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });

      const password = (passEl && passEl.value) || '';
      const password_confirmation = (confEl && confEl.value) || '';
      const token = (document.getElementById('resetToken') || {}).value || '';

      if (!token || token.length < 32) {
        showAlert('Token inválido. Solicita un enlace nuevo.', 'danger');
        return;
      }

      if (typeof window.rupeValidarReset === 'function' && !window.rupeValidarReset()) {
        const r0 = validarPasswordFuerte(password);
        if (!r0.ok) {
          if (passEl) passEl.classList.add('is-invalid');
          if (errP) errP.textContent = r0.message;
          showAlert('Contraseña débil: ' + r0.message, 'warning');
          return;
        }
        if (password !== password_confirmation) {
          if (confEl) confEl.classList.add('is-invalid');
          if (errC) errC.textContent = 'Las contraseñas no coinciden.';
          showAlert('Las contraseñas no coinciden.', 'warning');
          return;
        }
      }
      const r = pintarReglas(password);
      if (!r.ok) {
        if (passEl) passEl.classList.add('is-invalid');
        if (errP) errP.textContent = r.message;
        showAlert('Contraseña débil: ' + r.message, 'warning');
        return;
      }
      if (password !== password_confirmation) {
        if (confEl) confEl.classList.add('is-invalid');
        if (errC) errC.textContent = 'Las contraseñas no coinciden.';
        showAlert('Las contraseñas no coinciden.', 'warning');
        return;
      }

      setLoading(true);
      try {
        const recaptcha_token = await getRecaptchaToken('reset');
        const res = await fetch('/api/auth/reset', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': window.__CSRF__ || ''
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            token: token,
            password: password,
            password_confirmation: password_confirmation,
            recaptcha_token: recaptcha_token || '',
            _csrf: window.__CSRF__ || ''
          })
        });
        const data = await res.json().catch(function () { return {}; });
        if (!res.ok || data.success === false) {
          if (data.errors && typeof data.errors === 'object') {
            Object.keys(data.errors).forEach(function (field) {
              const msgs = data.errors[field];
              const msg = Array.isArray(msgs) ? msgs[0] : String(msgs);
              if (field === 'password' && errP) errP.textContent = msg;
              if (field === 'password_confirmation' && errC) errC.textContent = msg;
              if (field === 'token') showAlert(msg, 'danger');
              const input = form.querySelector('[name="' + field + '"]');
              if (input) input.classList.add('is-invalid');
            });
          }
          showAlert(data.message || 'No se pudo actualizar la contraseña.', 'danger');
          return;
        }
        showAlert('<i class="bi bi-check-circle me-1"></i> ' + (data.message || 'Contraseña actualizada. Redirigiendo…'), 'success');
        form.reset();
        setTimeout(function () { window.location.href = '/'; }, 1800);
      } catch (err) {
        showAlert('Error de red. Verifica tu conexión e intenta de nuevo.', 'danger');
      } finally {
        setLoading(false);
      }
    });
  })();
  </script>

<script>
(function () {
  var key = window.__RECAPTCHA_SITE_KEY__ || '';
  function setStatus(ok, msg) {
    document.querySelectorAll('.rupe-recaptcha-status').forEach(function (el) {
      el.classList.toggle('ok', !!ok);
      el.innerHTML = (ok ? '✓ ' : '⚠ ') + (msg || '');
    });
  }
  if (!key) { setStatus(false, 'Falta SITE_KEY'); return; }
  var n = 0;
  var timer = setInterval(function () {
    n++;
    if (typeof grecaptcha !== 'undefined' && grecaptcha.execute) {
      clearInterval(timer);
      grecaptcha.ready(function () {
        grecaptcha.execute(key, { action: 'page' }).then(function () {
          setStatus(true, 'reCAPTCHA activo');
          var b = document.querySelector('.grecaptcha-badge');
          if (b) { b.style.visibility = 'visible'; b.style.opacity = '1'; b.style.display = 'block'; }
        }).catch(function () { setStatus(false, 'Error reCAPTCHA (dominio)'); });
      });
    } else if (n > 40) {
      clearInterval(timer);
      setStatus(false, 'No cargó reCAPTCHA');
    }
  }, 200);
})();
</script>

<script>
(function () {
  try {
    var params = new URLSearchParams(window.location.search || '');
    var t = params.get('token') || '';
    if (!t) {
      t = sessionStorage.getItem('rupe_reset_token') || '';
      if (t && t.length >= 32) {
        window.location.replace('/auth/reset?token=' + encodeURIComponent(t));
      }
    }
  } catch (_) {}
})();
</script>

<script>
(function () {
  function rupeTogglePasswordVisibility(btn) {
    if (!btn) return;
    var id = btn.getAttribute('data-target');
    var input = id ? document.getElementById(id) : null;
    if (!input || input.tagName !== 'INPUT') return;
    // Solo cambia type; no altera value ni validación de 8 caracteres
    var show = input.getAttribute('type') === 'password';
    input.setAttribute('type', show ? 'text' : 'password');
    var icon = btn.querySelector('i');
    if (icon) {
      icon.classList.remove('bi-eye', 'bi-eye-slash');
      icon.classList.add(show ? 'bi-eye-slash' : 'bi-eye');
    }
    btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
    btn.setAttribute('title', show ? 'Ocultar' : 'Mostrar');
    try { input.focus({ preventScroll: true }); } catch (e) {
      try { input.focus(); } catch (e2) {}
    }
  }
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('.btn-toggle-pass');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    rupeTogglePasswordVisibility(btn);
  }, true);
  function bindButtons() {
    document.querySelectorAll('.btn-toggle-pass[data-target]').forEach(function (btn) {
      if (btn._rupePassBound) return;
      btn._rupePassBound = true;
      btn.type = 'button';
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        rupeTogglePasswordVisibility(btn);
      });
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindButtons);
  } else {
    bindButtons();
  }
})();
</script>

</body>
</html>
