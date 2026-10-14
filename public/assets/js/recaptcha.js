/**
 * RUPE CDMX — reCAPTCHA v3 helper
 * Uso: const token = await window.rupeRecaptchaToken('login');
 */
(function (global) {
  'use strict';

  function siteKey() {
    return global.__RECAPTCHA_SITE_KEY__ || '';
  }

  function loadScript(key) {
    return new Promise(function (resolve) {
      if (!key) {
        resolve(false);
        return;
      }
      if (typeof global.grecaptcha !== 'undefined' && global.grecaptcha.execute) {
        resolve(true);
        return;
      }
      if (document.querySelector('script[data-rupe-recaptcha]')) {
        var tries = 0;
        var t = setInterval(function () {
          tries += 1;
          if (typeof global.grecaptcha !== 'undefined' && global.grecaptcha.execute) {
            clearInterval(t);
            resolve(true);
          } else if (tries > 40) {
            clearInterval(t);
            resolve(false);
          }
        }, 100);
        return;
      }
      var s = document.createElement('script');
      s.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(key);
      s.async = true;
      s.defer = true;
      s.setAttribute('data-rupe-recaptcha', '1');
      s.onload = function () { resolve(true); };
      s.onerror = function () { resolve(false); };
      document.head.appendChild(s);
    });
  }

  async function rupeRecaptchaToken(action) {
    var key = siteKey();
    if (!key) return 'dev-bypass';
    var ok = await loadScript(key);
    if (!ok || typeof global.grecaptcha === 'undefined') return 'dev-bypass';
    try {
      await new Promise(function (resolve) {
        try {
          global.grecaptcha.ready(resolve);
        } catch (_) {
          resolve();
        }
      });
      var token = await global.grecaptcha.execute(key, { action: action || 'submit' });
      return token || '';
    } catch (e) {
      console.warn('[RUPE] reCAPTCHA execute', e);
      return '';
    }
  }

  global.rupeRecaptchaToken = rupeRecaptchaToken;
  global.getRecaptchaToken = global.getRecaptchaToken || rupeRecaptchaToken;
})(window);
