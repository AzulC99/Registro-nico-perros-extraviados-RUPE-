<?php
/**
 * RUPE CDMX — Recuperar contraseña (reCAPTCHA v3)
 * app/views/auth/forgot.php
 */
declare(strict_types=1);
$siteKey = defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '';
$csrf = function_exists('csrf_token') ? csrf_token() : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar contraseña — RUPE CDMX</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --guinda: #6B1D2F;
      --guinda-dark: #4A1521;
      --dorado: #E8A33D;
      --bg: #F4F6F8;
    }
    body {
      background: var(--bg);
      min-height: 100vh;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }
    .btn-guinda { background: var(--guinda); color: #fff; border: none; }
    .btn-guinda:hover { background: var(--guinda-dark); color: #fff; }
    .text-guinda { color: var(--guinda); }
    .card-auth {
      border: none;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(107, 29, 47, 0.12);
    }
    .brand-bar {
      background: var(--guinda);
      color: #fff;
      border-radius: 16px 16px 0 0;
      padding: 1.25rem 1.5rem;
    }
    .form-control.is-valid { border-color: #198754; }
    .form-control.is-invalid { border-color: #dc3545; }
  
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
  <script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8') ?>"></script>
  <?php endif; ?>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card card-auth">
          <div class="brand-bar d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center bg-white rounded-2 px-2 py-1">
              <img src="/assets/img/logo-unadm.png" alt="UnADM" style="height:26px;width:auto;object-fit:contain;"
                   onerror="this.style.display='none'">
            </span>
            <div>
              <div class="fw-bold">RUPE CDMX</div>
              <div class="small opacity-75">Recuperar contraseña</div>
            </div>
          </div>
          <div class="card-body p-4 p-md-5">
            <p class="text-secondary small mb-3">
              Ingresa el correo de tu cuenta. Si existe y está activa, te enviaremos un enlace para restablecer la contraseña.
            </p>

            <div class="alert alert-info py-2 small mb-3" id="forgotExpiryInfo">
              <i class="bi bi-hourglass-split me-1"></i>
              Tras enviarlo podrás abrir el enlace de inmediato desde esta pantalla o desde tu correo.
            </div>

            <div class="alert alert-warning py-2 small mb-3 d-none" id="forgotCountdownBox">
              <i class="bi bi-clock me-1"></i>
              Tiempo restante del enlace: <strong id="forgotCountdown">30</strong> min
            </div>

            <div id="alertBox" class="alert d-none py-2 small" role="alert"></div>

            <form id="formForgot" novalidate>
              <input type="hidden" name="_csrf" id="forgotCsrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

              <div class="mb-3">
                <label class="form-label fw-semibold" for="correo">Correo registrado *</label>
                <div class="input-group input-group-lg">
                  <input type="email" class="form-control" id="correo" name="correo"
                         required maxlength="80" autocomplete="email"
                         placeholder="correo@ejemplo.com">
                  <span class="input-group-text d-none text-success" id="okForgotCorreo">
                    <i class="bi bi-check-circle-fill"></i>
                  </span>
                </div>
                <div class="form-text">Correo válido (sin espacios ni acentos).</div>
                <div class="invalid-feedback">Correo electrónico no válido.</div>
              </div>

              <button type="submit" id="btnForgot" class="btn btn-guinda btn-lg w-100 mb-3">
                <span class="btn-text"><i class="bi bi-envelope me-1"></i> Enviar instrucciones</span>
                <span class="btn-loading d-none">
                  <span class="spinner-border spinner-border-sm me-1"></span> Enviando…
                </span>
              </button>
            </form>

            <p class="small text-center mb-0">
              <a href="/" class="text-guinda text-decoration-none">
                <i class="bi bi-arrow-left"></i> Volver al inicio
              </a>
            </p>
            <p class="text-center text-muted mt-3 mb-0" style="font-size:11px;">
              Protegido con reCAPTCHA v3 de Google
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="/assets/js/recaptcha.js?v=20260926rc1"></script>
  <script>
  window.__CSRF__ = <?= json_encode($csrf, JSON_UNESCAPED_UNICODE) ?>;
  window.__RECAPTCHA_SITE_KEY__ = <?= json_encode($siteKey, JSON_UNESCAPED_UNICODE) ?>;
  </script>
  <script>
  (function () {
    var form = document.getElementById('formForgot');
    var alertBox = document.getElementById('alertBox');
    var btn = document.getElementById('btnForgot');
    var siteKey = window.__RECAPTCHA_SITE_KEY__ || '';

    function showAlert(msg, type) {
      if (!alertBox) return;
      alertBox.className = 'alert alert-' + (type || 'info') + ' py-2 small';
      alertBox.textContent = msg;
      alertBox.classList.remove('d-none');
    }

    function setLoading(on) {
      if (!btn) return;
      btn.disabled = !!on;
      var t = btn.querySelector('.btn-text');
      var l = btn.querySelector('.btn-loading');
      if (t) t.classList.toggle('d-none', !!on);
      if (l) l.classList.toggle('d-none', !on);
    }

    function validCorreo(v) {
      v = String(v || '').trim();
      if (!v || /\s/.test(v)) return false;
      if (/[ÁÉÍÓÚÜáéíóúüÑñ]/.test(v)) return false;
      return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) && v.length <= 80;
    }

    function rupeValidarForgot(force) {
      var el = document.getElementById('correo');
      var ok = document.getElementById('okForgotCorreo');
      if (!el) return false;
      var good = validCorreo(el.value);
      el.classList.toggle('is-valid', good);
      el.classList.toggle('is-invalid', !good && (force || String(el.value || '').trim() !== ''));
      if (ok) ok.classList.toggle('d-none', !good);
      return good;
    }
    window.rupeValidarForgot = rupeValidarForgot;

    async function getRecaptchaToken(action) {
      if (!siteKey || typeof grecaptcha === 'undefined') return 'dev-bypass';
      try {
        await new Promise(function (resolve) {
          try { grecaptcha.ready(resolve); } catch (_) { resolve(); }
        });
        return await grecaptcha.execute(siteKey, { action: action || 'forgot' });
      } catch (_) {
        return '';
      }
    }

    var correoEl = document.getElementById('correo');
    if (correoEl) {
      correoEl.addEventListener('input', function () { rupeValidarForgot(false); });
      correoEl.addEventListener('blur', function () { rupeValidarForgot(false); });
    }

    if (!form) return;

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      if (alertBox) alertBox.classList.add('d-none');
      form.querySelectorAll('.is-invalid').forEach(function (el) {
        el.classList.remove('is-invalid');
      });

      if (!rupeValidarForgot(true)) {
        showAlert('Ingresa un correo electrónico válido.', 'warning');
        return;
      }

      var correo = String((document.getElementById('correo') || {}).value || '').trim();
      setLoading(true);
      try {
        var recaptcha_token = await getRecaptchaToken('forgot');
        var csrf = window.__CSRF__ || (document.getElementById('forgotCsrf') || {}).value || '';
        var res = await fetch('/api/auth/forgot', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrf
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            correo: correo,
            recaptcha_token: recaptcha_token,
            _csrf: csrf
          })
        });
        var data = await res.json().catch(function () { return {}; });
        if (!res.ok || data.success === false) {
          showAlert((data && data.message) || 'No se pudo enviar el correo. Intenta de nuevo.', 'danger');
          return;
        }
        var payload = (data && data.data) ? data.data : (data || {});
        var token = String(payload.token || '').trim();
        var resetUrl = String(payload.reset_url || '').trim();
        // Siempre preferir ruta relativa con el token fresco
        if (token && token.length >= 32) {
          resetUrl = '/auth/reset?token=' + encodeURIComponent(token);
        } else if (resetUrl && resetUrl.indexOf('http') === 0) {
          try {
            var u = new URL(resetUrl);
            resetUrl = u.pathname + u.search;
          } catch (_) {}
        }
        showAlert((data && data.message) || 'Revisa tu correo. Puedes abrir el enlace de inmediato.', 'success');
        form.reset();
        rupeValidarForgot(false);
        var okSpan = document.getElementById('okForgotCorreo');
        if (okSpan) okSpan.classList.add('d-none');
        if (correoEl) correoEl.classList.remove('is-valid', 'is-invalid');

        var box = document.getElementById('forgotCountdownBox');
        if (box) {
          box.classList.remove('d-none');
          if (resetUrl) {
            box.className = 'alert alert-success py-3 small mb-3';
            box.innerHTML =
              '<div class="fw-semibold mb-2"><i class="bi bi-envelope-check me-1"></i> Enlace listo</div>' +
              '<p class="mb-2 mb-0">Se envió al correo (si la cuenta existe). También puedes abrirlo <strong>ahora mismo</strong>:</p>' +
              '<a class="btn btn-guinda w-100 mt-2" id="btnAbrirEnlaceAhora" href="' + resetUrl.replace(/"/g, '&quot;') + '">' +
              '<i class="bi bi-box-arrow-up-right me-1"></i> Abrir restablecer contraseña</a>' +
              '<div class="text-muted mt-2" style="font-size:11px;word-break:break-all;">Abre el enlace para crear tu nueva contraseña.</div>';
          } else {
            box.className = 'alert alert-info py-2 small mb-3';
            box.innerHTML =
              '<i class="bi bi-info-circle me-1"></i> Si el correo está registrado, revisa tu bandeja (y spam). ' +
              'Revisa tu bandeja de entrada y la carpeta de spam.';
          }
        }

        // Botón + abrir de inmediato el enlace fresco
        setTimeout(function () {
          var b = document.getElementById('btnAbrirEnlaceAhora');
          if (!b || !resetUrl) return;
          b.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            window.location.replace(resetUrl);
          });
          try { b.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (_) {}
          // Guardar token por si la navegación pierde el query
          try { sessionStorage.setItem('rupe_reset_token', token || ''); } catch (_) {}
        }, 30);
      } catch (err) {
        showAlert('Error de red. Verifica tu conexión.', 'danger');
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
</body>
</html>
