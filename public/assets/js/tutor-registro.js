/**
 * RUPE CDMX — Registro: Guardar + Folio + QR + Foto + Progreso (ES6)
 * public/assets/js/tutor-registro.js
 */
'use strict';

(function (global) {
  const $ = (id) => document.getElementById(id);

  function toast(msg, type) {
    if (typeof global.rupeToast === 'function') return global.rupeToast(msg, type);
    if (typeof global.mostrarToastTutor === 'function') return global.mostrarToastTutor(msg);
    alert(msg);
  }

  function mapEspecie(v) {
    const s = String(v || '').toLowerCase();
    if (s.indexOf('gato') >= 0) return 'gato';
    if (s.indexOf('otro') >= 0) return 'otro';
    return 'perro';
  }

  function mapSexo(v) {
    const s = String(v || '').toLowerCase();
    if (s.indexOf('hembra') >= 0) return 'hembra';
    if (s.indexOf('macho') >= 0) return 'macho';
    return 'desconocido';
  }

  function resolveRazaId(val) {
    if (val !== undefined && val !== '' && /^\d+$/.test(String(val).trim())) {
      return parseInt(String(val).trim(), 10);
    }
    const list = global.__RUPE_RAZAS__ || (global.RUPE_INIT && global.RUPE_INIT.razas) || [];
    const n = String(val || '').trim().toLowerCase();
    const found = list.find((r) => String(r.nombre).toLowerCase() === n);
    return found ? found.id_raza : '';
  }


  const FOTO_MAX_BYTES = 5 * 1024 * 1024; // 5 MB
  const FOTO_TIPOS = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

  function validarArchivoFoto(file) {
    if (!file) return { ok: false, msg: 'Selecciona una imagen JPG o PNG.' };
    const tipo = String(file.type || '').toLowerCase();
    if (tipo && FOTO_TIPOS.indexOf(tipo) < 0 && !/\.(jpe?g|png|webp)$/i.test(file.name || '')) {
      return { ok: false, msg: 'Solo se permiten imágenes JPG, PNG o WEBP.' };
    }
    if (file.size > FOTO_MAX_BYTES) {
      return { ok: false, msg: 'La imagen no debe superar 5 MB.' };
    }
    if (file.size < 1024) {
      return { ok: false, msg: 'El archivo de imagen es demasiado pequeño o está vacío.' };
    }
    return { ok: true, msg: '' };
  }
  global.validarArchivoFoto = validarArchivoFoto;

  function resolveAlcaldiaId(val) {
    if (val !== undefined && val !== '' && /^\d+$/.test(String(val).trim())) {
      return parseInt(String(val).trim(), 10);
    }
    const list = global.__RUPE_ALCALDIAS__ || (global.RUPE_INIT && global.RUPE_INIT.alcaldias) || [];
    const n = String(val || '').trim().toLowerCase();
    const found = list.find((a) => String(a.nombre).toLowerCase() === n);
    return found ? found.id_alcaldia : '';
  }

  /** Validaciones de caracteres / longitud por campo (ES6) */
  const REGLAS_CARACTERES = {
    regNombre: {
      min: 2, max: 20,
      re: /^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9 .'\-]{2,20}$/,
      msg: 'Nombre de la mascota: 2 a 20 caracteres.',
    },
    regEdad: {
      min: 0, max: 25,
      re: /^(?:[0-9]|1[0-9]|2[0-5])$/,
      msg: 'Edad: número entero de 0 a 25 años.',
    },
    regSenas: {
      min: 5, max: 220,
      re: /^[\s\S]{5,220}$/,
      msg: 'Señas particulares: entre 5 y 220 caracteres.',
    },
    regTutorNombre: {
      min: 2, max: 50,
      re: /^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ .'\-]{2,50}$/,
      msg: 'Nombre del tutor: 2 a 50 letras.',
    },
    regTutorTel: {
      min: 10, max: 10,
      test: (v) => {
        const d = String(v).replace(/\D/g, '');
        return d.length === 10;
      },
      msg: 'Teléfono / WhatsApp: exactamente 10 dígitos.',
    },
    regTutorEmail: {
      min: 6, max: 120,
      re: /^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/,
      msg: 'Correo electrónico no válido.',
    },
    regFecha: {
      re: /^\d{4}-\d{2}-\d{2}$/,
      msg: 'Fecha de extravío inválida.',
    },
    regHora: {
      re: /^\d{2}:\d{2}$/,
      msg: 'Hora aprox. de extravío inválida (HH:MM).',
    },
    regCalle: {
      min: 2, max: 120,
      re: /^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9 .,#\-]{2,120}$/,
      msg: 'Calle: 2–120 caracteres (letras, números, #, guión).',
    },
    regReferencia: {
      max: 120,
      re: /^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9 .,#\-]{0,120}$/,
      msg: 'Referencia: máx. 120 caracteres.',
    },
    regColonia: {
      min: 1, max: 120,
      msg: 'Selecciona una colonia.',
    },
  };

  function marcarCampo(el, valido) {
    if (!el) return;
    el.classList.toggle('is-invalid', !valido);
    el.classList.toggle('is-valid', !!valido);
  }

  function validarCampoCaracteres(id, value) {
    const rule = REGLAS_CARACTERES[id];
    if (!rule) return { ok: true };
    const v = String(value || '').trim();
    if (rule.max && v.length > rule.max) {
      return { ok: false, msg: rule.msg };
    }
    if (rule.min != null && typeof rule.test !== 'function' && v.length < rule.min) {
      return { ok: false, msg: rule.msg };
    }
    if (typeof rule.test === 'function') {
      return rule.test(v) ? { ok: true } : { ok: false, msg: rule.msg };
    }
    if (rule.re && !rule.re.test(v)) {
      return { ok: false, msg: rule.msg };
    }
    return { ok: true };
  }

  function validarFormulario() {
    const requiredIds = [
      'regNombre', 'regRaza', 'regEspecie', 'regEdad', 'regSexo', 'regTamano',
      'regColor', 'regFecha', 'regHora', 'regAlcaldia', 'regColonia', 'regCalle', 'regSenas',
      'regTutorNombre', 'regTutorTel', 'regTutorEmail'
    ];
    let ok = true;
    let firstMsg = '';
    requiredIds.forEach((id) => {
      const el = $(id);
      if (!el) return;
      const val = (el.value || '').trim();
      if (!val) {
        marcarCampo(el, false);
        ok = false;
        if (!firstMsg) firstMsg = 'Completa todos los campos obligatorios del registro.';
        return;
      }
      const r = validarCampoCaracteres(id, val);
      if (!r.ok) {
        marcarCampo(el, false);
        ok = false;
        if (!firstMsg) firstMsg = r.msg;
      } else {
        marcarCampo(el, true);
      }
    });
    // Foto obligatoria
    const fotoInputV = $('regFoto');
    const hasFotoV = !!(global.fotoRegistroCapturada || (fotoInputV && fotoInputV.files && fotoInputV.files[0]));
    if (!hasFotoV) {
      ok = false;
      if (!firstMsg) firstMsg = 'Sube una fotografía de la mascota (JPG o PNG, máx. 5 MB).';
      const errF = document.getElementById('regFotoErr');
      if (errF) { errF.textContent = 'La fotografía es obligatoria (JPG/PNG, máx. 5 MB).'; errF.classList.remove('d-none'); }
      const okF = document.getElementById('regFotoOk');
      if (okF) okF.classList.add('d-none');
    } else {
      const errF = document.getElementById('regFotoErr');
      if (errF) errF.classList.add('d-none');
      const okF = document.getElementById('regFotoOk');
      if (okF) okF.classList.remove('d-none');
    }
    // Privacidad
    const priv = $('regPrivacidad');
    if (priv && !priv.checked) {
      ok = false;
      if (!firstMsg) firstMsg = 'Debes aceptar el uso de datos de contacto para avistamientos.';
    }
    if (!ok) toast(firstMsg || 'Revisa los campos del formulario.', 'warning');
    return ok;
  }

  // Filtrar caracteres en tiempo real (nombre mascota / tutor)
  function bindFiltrosCaracteres() {
    const soloNombre = (el) => {
      if (!el || el._rupeCharBound) return;
      el._rupeCharBound = true;
      el.addEventListener('input', () => {
        const before = el.value;
        // permite letras, números, espacios, acentos, apóstrofe y guión
        const cleaned = before.replace(/[^A-Za-zÁÉÍÓÚÜáéíóúüÑñ0-9 .'\-]/g, '');
        if (cleaned !== before) el.value = cleaned;
        if (el.maxLength > 0 && el.value.length > el.maxLength) {
          el.value = el.value.slice(0, el.maxLength);
        }
        const r = validarCampoCaracteres(el.id, el.value);
        marcarCampo(el, el.value.trim() === '' ? null : r.ok);
        if (el.value.trim() === '') {
          el.classList.remove('is-valid', 'is-invalid');
        }
      });
    };
    soloNombre($('regNombre'));
    ['regCalle', 'regReferencia'].forEach(function (cid) {
      var c = $(cid);
      if (c && !c._rupeCalleBound) {
        c._rupeCalleBound = true;
        c.addEventListener('input', function () { validarCampoLive(cid); });
        c.addEventListener('blur', function () { validarCampoLive(cid); });
      }
    });
    soloNombre($('regTutorNombre'));
    const nomM = $('regNombre');
    if (nomM && !nomM._rupeCharBound) {
      nomM._rupeCharBound = true;
      nomM.addEventListener('input', () => {
        if (nomM.value.length > 20) nomM.value = nomM.value.slice(0, 20);
        validarCampoLive('regNombre');
      });
    }
    const emailEl = $('regTutorEmail');
    if (emailEl && !emailEl._rupeCharBound) {
      emailEl._rupeCharBound = true;
      emailEl.addEventListener('input', () => validarCampoLive('regTutorEmail'));
    }

    const tel = $('regTutorTel');
    if (tel && !tel._rupeCharBound) {
      tel._rupeCharBound = true;
      tel.addEventListener('input', () => {
        // solo dígitos, espacios, +, (), -
        const cleaned = tel.value.replace(/\D/g, '').slice(0, 10);
        if (cleaned !== tel.value) tel.value = cleaned;
        const r = validarCampoCaracteres('regTutorTel', tel.value);
        marcarCampo(tel, tel.value.trim() === '' ? null : r.ok);
        if (tel.value.trim() === '') tel.classList.remove('is-valid', 'is-invalid');
      });
    }

    const senas = $('regSenas');
    if (senas && !senas._rupeCharBound) {
      senas._rupeCharBound = true;
      senas.addEventListener('input', () => {
        if (senas.value.length > 220) senas.value = senas.value.slice(0, 220);
        const r = validarCampoCaracteres('regSenas', senas.value);
        marcarCampo(senas, senas.value.trim() === '' ? null : r.ok);
        if (senas.value.trim() === '') senas.classList.remove('is-valid', 'is-invalid');
      });
    }

    const edad = $('regEdad');
    if (edad && !edad._rupeCharBound) {
      edad._rupeCharBound = true;
      edad.addEventListener('input', () => {
        let n = parseInt(edad.value, 10);
        if (edad.value !== '' && (isNaN(n) || n < 0)) edad.value = '0';
        if (n > 25) edad.value = '25';
        const r = validarCampoCaracteres('regEdad', edad.value);
        marcarCampo(edad, edad.value === '' ? null : r.ok);
        if (edad.value === '') edad.classList.remove('is-valid', 'is-invalid');
      });
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(bindFiltrosCaracteres, 0);
  });

  function validarCampoLive(id) {
    var el = document.getElementById(id);
    if (!el) return true;
    var val = String(el.value || '');
    // Select: solo valor no vacío
    if (el.tagName === 'SELECT') {
      var okSel = val.trim() !== '';
      marcarCampo(el, okSel);
      return okSel;
    }
    if (id === 'regReferencia') {
      var vRef = val.trim();
      if (vRef === '') {
        el.classList.remove('is-valid', 'is-invalid');
        var okR = document.getElementById('regReferenciaOk');
        if (okR) okR.style.display = 'none';
        return true;
      }
      var rRef = validarCampoCaracteres(id, val);
      marcarCampo(el, rRef.ok);
      return rRef.ok;
    }
    if (id === 'regNombre' && val.length > 20) {
      val = val.slice(0, 20);
      el.value = val;
    }
    if (val.trim() === '' && !el.required) {
      el.classList.remove('is-valid', 'is-invalid');
      marcarCampo(el, false);
      el.classList.remove('is-invalid');
      return true;
    }
    if (val.trim() === '' && el.required) {
      marcarCampo(el, false);
      return false;
    }
    var r = validarCampoCaracteres(id, val);
    marcarCampo(el, r.ok);
    return r.ok;
  }
  global.validarCampoLive = validarCampoLive;

  global.validarCampoCaracteres = validarCampoCaracteres;

  function previsualizarFotoRegistro(input) {
    const err = document.getElementById('regFotoErr');
    const ok = document.getElementById('regFotoOk');
    const dz = document.getElementById('regDropzone') || document.querySelector('.rupe-dropzone');
    const preview = document.getElementById('regFotoPreview');
    const file = input && input.files && input.files[0];
    if (!file) {
      if (err) { err.textContent = ''; err.classList.add('d-none'); }
      if (ok) ok.classList.add('d-none');
      return;
    }
    const v = validarArchivoFoto(file);
    if (!v.ok) {
      if (err) { err.textContent = v.msg; err.classList.remove('d-none'); }
      if (ok) ok.classList.add('d-none');
      if (dz) { dz.classList.remove('tiene-archivo'); dz.classList.add('border-danger'); }
      try { input.value = ''; } catch (_) {}
      global.fotoRegistroCapturada = null;
      if (typeof global.rupeToast === 'function') global.rupeToast(v.msg, 'warning');
      return;
    }
    if (err) err.classList.add('d-none');
    if (ok) ok.classList.remove('d-none');
    if (dz) { dz.classList.add('tiene-archivo'); dz.classList.remove('border-danger'); }
    const reader = new FileReader();
    reader.onload = function (e) {
      global.fotoRegistroCapturada = e.target.result;
      if (preview) preview.src = e.target.result;
      const txt = document.getElementById('regDropzoneTexto');
      if (txt) txt.textContent = file.name || 'Foto lista';
    };
    reader.readAsDataURL(file);
  }
  global.previsualizarFotoRegistro = previsualizarFotoRegistro;

  global.bindFiltrosCaracteres = bindFiltrosCaracteres;

  function buildPayload() {
    const razaVal = ($('regRaza') && $('regRaza').value) || '';
    const alcaldiaVal = ($('regAlcaldia') && $('regAlcaldia').value) || '';
    const idRaza = resolveRazaId(razaVal.trim());
    const idAlcaldia = resolveAlcaldiaId(alcaldiaVal.trim());
    if (!idRaza || parseInt(idRaza, 10) <= 0) {
      toast('Selecciona una raza válida del catálogo.', 'warning');
      if ($('regRaza')) $('regRaza').classList.add('is-invalid');
      return null;
    }
    if (!idAlcaldia || parseInt(idAlcaldia, 10) <= 0) {
      toast('Selecciona una alcaldía válida del catálogo.', 'warning');
      if ($('regAlcaldia')) $('regAlcaldia').classList.add('is-invalid');
      return null;
    }
    const collar = ($('regCollar') && $('regCollar').value) || '';
    const chip = ($('regChip') && $('regChip').value) || '';
    const senas = ($('regSenas') && $('regSenas').value) || '';
    const tamano = ($('regTamano') && $('regTamano').value) || '';
    const hora = (($('regHora') && $('regHora').value) || '').trim();
    const senasBase = senas.trim();
    // Guardar hora siempre en señas (columna sin hora_extravio dedicada)
    const extraParts = [];
    if (hora) extraParts.push('Hora extravío: ' + hora);
    if (senasBase) extraParts.push(senasBase);
    const extra = extraParts.join(' | ');

    const tutorNombre = (($('regTutorNombre') && $('regTutorNombre').value) || '').trim();
    const tutorTel = (($('regTutorTel') && $('regTutorTel').value) || '').trim();
    const tutorEmail = (($('regTutorEmail') && $('regTutorEmail').value) || '').trim();

    return {
      nombre: (($('regNombre') && $('regNombre').value) || '').trim(),
      especie: mapEspecie(($('regEspecie') && $('regEspecie').value) || ''),
      sexo: mapSexo(($('regSexo') && $('regSexo').value) || ''),
      id_raza: parseInt(idRaza, 10),
      id_alcaldia: parseInt(idAlcaldia, 10),
      color: (($('regColor') && $('regColor').value) || '').trim(),
      edad_aprox: (($('regEdad') && $('regEdad').value) || '').trim(),
      tamano: tamano.trim(),
      collar: collar.trim(),
      microchip: chip.trim(),
      chip: chip.trim(),
      hora_extravio: hora,
      senas_particulares: extra,
      colonia: (($('regColonia') && $('regColonia').value) || '').trim(),
      calle: (($('regCalle') && $('regCalle').value) || '').trim(),
      referencia: (($('regReferencia') && $('regReferencia').value) || '').trim(),
      direccion_ref: [
        (($('regCalle') && $('regCalle').value) || '').trim(),
        (($('regReferencia') && $('regReferencia').value) || '').trim()
      ].filter(Boolean).join(', '),
      fecha_extravio: (($('regFecha') && $('regFecha').value) || ''),
      estatus: 'En Búsqueda',
      telefono_tutor: tutorTel,
      notas: [
        hora ? 'Hora extravío: ' + hora : '',
        tutorNombre ? 'Tutor form: ' + tutorNombre : '',
        tutorTel ? 'Tel form: ' + tutorTel : '',
        tutorEmail ? 'Email form: ' + tutorEmail : '',
      ].filter(Boolean).join(' | '),
    };
  }

  function qrPublicUrl(mascota) {
    const token = (mascota && (mascota.qr_token || mascota.folio)) || '';
    const base = (global.location && global.location.origin) || '';
    return base + '/mascota/' + encodeURIComponent(token);
  }

  function generarQR(url, folio) {
    const img = $('tutorFichaQR');
    const enlace = $('tutorFichaEnlaceQR');
    if (enlace) enlace.textContent = url;
    const qrImgUrl =
      'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' +
      encodeURIComponent(url);
    if (img) {
      img.src = qrImgUrl;
      img.alt = 'QR ' + (folio || '');
    }
  }

  function pintarFicha(reg) {
    const set = (id, val) => {
      const el = $(id);
      if (el) el.textContent = val || '—';
    };
    if (!reg) return;
    set('tutorFichaFolio', reg.folio ? '#' + reg.folio : '—');
    set('tutorFichaNombre', reg.nombre);
    set('tutorFichaRaza', reg.raza || reg.raza_nombre || '');
    set('tutorFichaEstatus', reg.estatus);
    set('tutorFichaLugar', [reg.alcaldia || reg.alcaldia_nombre || '', reg.colonia || ''].filter(Boolean).join(', '));
    set('tutorFichaFecha', reg.fecha || reg.fecha_extravio || '');
    set('tutorFichaSenas', reg.senas || reg.senas_particulares || '');
    set('tutorFichaColor', reg.color || '');
    set('tutorFichaCollar', reg.collar || '');
    set('tutorFichaMicrochip', reg.microchip || reg.chip || '');
    const foto = reg.foto || reg.foto_url || '';
    const prev = $('tutorFichaFoto');
    if (prev && foto) prev.src = foto.charAt(0) === '/' || foto.indexOf('http') === 0 ? foto : '/' + foto;
    generarQR(qrPublicUrl(reg), reg.folio);
  }

  async function csrf() {
    if (global.__CSRF__) return global.__CSRF__;
    if (global.RUPE_INIT && global.RUPE_INIT.csrf) {
      global.__CSRF__ = global.RUPE_INIT.csrf;
      return global.__CSRF__;
    }
    try {
      const r = await fetch('/api/csrf', { credentials: 'same-origin' });
      const d = await r.json();
      global.__CSRF__ = (d.data && d.data.csrf) || d.csrf || '';
    } catch (_) {}
    return global.__CSRF__ || '';
  }

  // ===== Progreso del registro =====
  function actualizarProgresoRegistro() {
    const formulario = $('formCapturaPerro');
    const barra = $('regProgresoBarra');
    const texto = $('regProgresoTexto');
    if (!formulario || !barra || !texto) return;

    const ids = [
      'regNombre', 'regRaza', 'regEspecie', 'regEdad', 'regSexo', 'regTamano',
      'regColor', 'regFecha', 'regAlcaldia', 'regCollar', 'regChip', 'regColonia',
      'regSenas', 'regTutorNombre', 'regTutorTel', 'regTutorEmail'
    ];
    let total = ids.length + 2; // foto + privacidad
    let done = 0;
    ids.forEach((id) => {
      const el = $(id);
      if (!el) {
        total -= 1;
        return;
      }
      const val = (el.value || '').trim();
      const ok = val !== '';
      el.classList.toggle('is-valid', ok);
      const cont = el.closest('.col-xl-3, .col-xl-6, .col-lg-6, .col-md-6, .col-md-8, .col-12');
      const badge = cont && cont.querySelector('.num-badge');
      if (badge) badge.style.backgroundColor = ok ? '#198754' : '';
      if (ok) done += 1;
    });

    const fotoInput = $('regFoto');
    const hasFoto = !!(
      global.fotoRegistroCapturada ||
      (fotoInput && fotoInput.files && fotoInput.files[0])
    );
    if (hasFoto) done += 1;
    const dz = $('regDropzone');
    if (dz) dz.classList.toggle('tiene-archivo', hasFoto);

    const priv = $('regPrivacidad');
    if (priv) {
      if (priv.checked) done += 1;
    } else {
      total -= 1;
    }

    const pct = total ? Math.round((done / total) * 100) : 0;
    barra.style.width = pct + '%';
    barra.setAttribute('aria-valuenow', String(pct));
    barra.classList.toggle('completo', pct >= 100);
    texto.textContent = done + ' de ' + total + ' campos';
  }
  global.actualizarProgresoRegistro = actualizarProgresoRegistro;

  // ===== Foto: preview + base64 para subir =====
  global.previsualizarFotoRegistro = function (input) {
    const file = input && input.files && input.files[0];
    const errF = document.getElementById('regFotoErr');
    const okF = document.getElementById('regFotoOk');
    if (!file) {
      if (errF) errF.classList.add('d-none');
      if (okF) okF.classList.add('d-none');
      actualizarProgresoRegistro();
      return;
    }
    const v = (typeof validarArchivoFoto === 'function') ? validarArchivoFoto(file) : { ok: true };
    if (!v.ok) {
      toast(v.msg || 'Imagen no válida.', 'warning');
      if (errF) { errF.textContent = v.msg; errF.classList.remove('d-none'); }
      if (okF) okF.classList.add('d-none');
      try { input.value = ''; } catch (_) {}
      global.fotoRegistroCapturada = null;
      actualizarProgresoRegistro();
      return;
    }
    if (errF) errF.classList.add('d-none');
    if (okF) okF.classList.remove('d-none');
    const reader = new FileReader();
    reader.onload = function (e) {
      const dataUrl = e.target.result;
      global.fotoRegistroCapturada = dataUrl;
      try {
        window.fotoRegistroCapturada = dataUrl;
      } catch (_) {}
      const preview = $('regFotoPreview');
      if (preview) {
        preview.src = dataUrl;
        preview.style.display = 'block';
        preview.style.maxHeight = '220px';
        preview.style.objectFit = 'cover';
        preview.style.width = '100%';
        preview.classList.remove('d-none');
      }
      const drop = $('regDropzone');
      if (drop) {
        drop.classList.add('tiene-archivo');
        const icon = drop.querySelector('i');
        if (icon) icon.className = 'bi bi-check-circle-fill d-block mb-1';
        const txt = $('regDropzoneTexto');
        if (txt) txt.textContent = file.name;
      }
      actualizarProgresoRegistro();
      toast('Foto lista para subir.', 'success');
    };
    reader.onerror = function () {
      toast('No se pudo leer la imagen.', 'error');
    };
    reader.readAsDataURL(file);
  };

  global.manejarSoltarFoto = function (event) {
    event.preventDefault();
    event.stopPropagation();
    const dz = event.currentTarget;
    if (dz) dz.classList.remove('dragover');
    const file = event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files[0];
    if (!file) return;
    const input = $('regFoto');
    if (!input) return;
    try {
      const dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
    } catch (_) {}
    global.previsualizarFotoRegistro(input);
  };

  async function procesarRegistroTutor(e) {
    if (e) e.preventDefault();
    if (!validarFormulario()) return;

    const btn =
      document.querySelector('#formCapturaPerro button[type="submit"]') ||
      document.querySelector('button[type="submit"]');
    const prevHtml = btn ? btn.innerHTML : '';
    if (btn) {
      btn.disabled = true;
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1"></span> Guardando y generando folio…';
    }

    const payload = buildPayload();
    if (!payload) {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = prevHtml;
      }
      return;
    }

    try {
      const token = await csrf();
      const fileInput = $('regFoto');
      const file = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
      const fotoB64 =
        global.fotoRegistroCapturada ||
        (typeof window !== 'undefined' && window.fotoRegistroCapturada) ||
        '';

      const fd = new FormData();
      Object.keys(payload).forEach((k) => {
        const v = payload[k];
        if (v !== undefined && v !== null) fd.append(k, String(v));
      });
      fd.set('tamano', String(payload.tamano || ''));
      fd.set('collar', String(payload.collar || ''));
      fd.set('microchip', String(payload.microchip || payload.chip || ''));
      fd.set('color', String(payload.color || ''));
      fd.set('edad_aprox', String(payload.edad_aprox || ''));
      fd.set('sexo', String(payload.sexo || ''));
      fd.set('especie', String(payload.especie || 'perro'));
      fd.set('id_raza', String(payload.id_raza || ''));
      fd.set('id_alcaldia', String(payload.id_alcaldia || ''));
      fd.append('_csrf', token);
      if (file) {
        fd.append('foto', file);
        fd.append('foto_archivo', file);
      }
      if (fotoB64 && String(fotoB64).indexOf('data:image/') === 0) {
        fd.append('foto_base64', fotoB64);
      }

      const res = await fetch('/api/tutor/mascotas', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': token,
        },
        credentials: 'same-origin',
        body: fd,
      });

      const rawText = await res.text();
      let data = {};
      try {
        data = rawText ? JSON.parse(rawText) : {};
      } catch (_) {
        if (res.ok) {
          data = { success: true, message: 'Registro guardado', data: {} };
        } else {
          toast('No se pudo guardar el registro (respuesta inválida del servidor).', 'error');
          return;
        }
      }

      if (!res.ok || data.success === false) {
        let msg = data.message || 'No se pudo guardar el registro (HTTP ' + res.status + ').';
        if (data.errors) {
          try {
            msg = Object.values(data.errors).flat().join(' ');
          } catch (_) {}
        }
        toast(msg, 'error');
        return;
      }

      const payloadData = data.data || {};
      const m = payloadData.mascota || {};
      let folio = payloadData.folio || m.folio || '';
      let qrToken = payloadData.qr_token || m.qr_token || '';
      const idMascota = payloadData.id_mascota || m.id_mascota || m.id || '';

      if (idMascota) {
        try {
          const token2 = await csrf();
          const res2 = await fetch('/api/tutor/mascotas/' + idMascota, {
            headers: {
              Accept: 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-Token': token2,
            },
            credentials: 'same-origin',
          });
          const data2 = await res2.json().catch(() => ({}));
          const m2 = (data2.data && data2.data.mascota) || {};
          if (m2.folio) folio = m2.folio;
          if (m2.qr_token) qrToken = m2.qr_token;
          Object.assign(m, m2);
        } catch (_) {}
      }

      const fotoUrl =
        m.foto_url || m.foto || payloadData.foto_url || global.fotoRegistroCapturada || '';

      const regState = {
        id: idMascota,
        id_mascota: idMascota,
        folio: folio,
        nombre: m.nombre || payload.nombre,
        raza: m.raza || m.raza_nombre || '',
        raza_nombre: m.raza_nombre || m.raza || '',
        id_raza: m.id_raza || payload.id_raza,
        alcaldia: m.alcaldia || m.alcaldia_nombre || '',
        alcaldia_nombre: m.alcaldia_nombre || m.alcaldia || '',
        id_alcaldia: m.id_alcaldia || payload.id_alcaldia,
        especie: m.especie || payload.especie || '',
        sexo: m.sexo || payload.sexo || '',
        color: m.color || payload.color || '',
        edad_aprox: m.edad_aprox || payload.edad_aprox || '',
        edad: m.edad_aprox || payload.edad_aprox || '',
        tamano: m.tamano || payload.tamano || '',
        collar: m.collar || payload.collar || '',
        microchip: m.microchip || m.chip || payload.microchip || '',
        chip: m.microchip || m.chip || payload.microchip || '',
        fecha: m.fecha || m.fecha_extravio || payload.fecha_extravio,
        fecha_extravio: m.fecha_extravio || payload.fecha_extravio,
        colonia: m.colonia || payload.colonia || '',
        estatus: m.estatus || 'En Búsqueda',
        senas: m.senas || m.senas_particulares || payload.senas_particulares || '',
        senas_particulares: m.senas_particulares || m.senas || payload.senas_particulares || '',
        qr_token: qrToken,
        foto: fotoUrl,
        foto_url: fotoUrl,
        latitud: m.latitud || '',
        longitud: m.longitud || '',
      };

      toast('Registro guardado correctamente. Folio: ' + (folio || 'generado'), 'success');

      if (global.state && Array.isArray(global.state.registros)) {
        global.state.registros.unshift(regState);
        global.state.folioSeleccionado = folio;
      }
      if (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas)) {
        global.RUPE_INIT.mascotas.unshift(regState);
      }

      try {
        generarQR(qrPublicUrl({ qr_token: qrToken, folio: folio }), folio);
      } catch (_) {}
      try {
        pintarFicha(regState);
      } catch (_) {}
      try {
        if (typeof global.renderizarFichaTutor === 'function') global.renderizarFichaTutor(regState);
      } catch (_) {}
      try {
        if (typeof global.pintarTablaCrud === 'function') global.pintarTablaCrud();
      } catch (_) {}

      const form = $('formCapturaPerro');
      if (form) form.reset();
      global.fotoRegistroCapturada = '';
      try {
        window.fotoRegistroCapturada = '';
      } catch (_) {}
      const preview = $('regFotoPreview');
      if (preview && global.FOTO_PLACEHOLDER) preview.src = global.FOTO_PLACEHOLDER;
      const dz2 = $('regDropzone');
      if (dz2) {
        dz2.classList.remove('tiene-archivo');
        const txt = $('regDropzoneTexto');
        if (txt) txt.textContent = 'Arrastra o haz clic para subir foto';
      }
      actualizarProgresoRegistro();

      try {
        const btnFicha = document.querySelector('[data-modulo="ficha-pdf"]');
        if (btnFicha) btnFicha.click();
      } catch (_) {}
      setTimeout(function () {
        try {
          if (typeof global.renderizarFichaTutor === 'function') global.renderizarFichaTutor(regState);
          else pintarFicha(regState);
        } catch (_) {}
      }, 200);
    } catch (err) {
      console.error('[RUPE registro]', err);
      const msg = err && err.message ? String(err.message) : '';
      if (msg && /fetch|network|Failed|NetworkError|Load failed/i.test(msg)) {
        toast('Error de red al guardar. Intenta de nuevo.', 'error');
      } else {
        toast(msg || 'No se pudo completar el registro.', 'error');
      }
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = prevHtml || '<i class="bi bi-save me-1"></i> Guardar Gratis y Generar Folio';
      }
    }
  }

  global.procesarRegistroTutor = procesarRegistroTutor;
  global.rupeGenerarQR = generarQR;

  function bindAll() {
    const form = $('formCapturaPerro');
    if (form && !form._rupeRegBound) {
      form._rupeRegBound = true;
      form.addEventListener(
        'submit',
        function (e) {
          e.preventDefault();
          e.stopImmediatePropagation();
          procesarRegistroTutor(e);
        },
        true
      );
      form.addEventListener('input', actualizarProgresoRegistro);
      form.addEventListener('change', actualizarProgresoRegistro);
    }
    const foto = $('regFoto');
    if (foto && !foto._rupeFotoBound) {
      foto._rupeFotoBound = true;
      foto.setAttribute('name', 'foto');
      foto.addEventListener('change', function () {
        global.previsualizarFotoRegistro(foto);
      });
    }
    const dz = $('regDropzone');
    if (dz && !dz._rupeDzBound) {
      dz._rupeDzBound = true;
      dz.addEventListener('dragover', function (e) {
        e.preventDefault();
        dz.classList.add('dragover');
      });
      dz.addEventListener('dragleave', function () {
        dz.classList.remove('dragover');
      });
      dz.addEventListener('drop', global.manejarSoltarFoto);
    }
    actualizarProgresoRegistro();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindAll);
  } else {
    bindAll();
  }
  global.addEventListener('load', bindAll);
})(typeof window !== 'undefined' ? window : globalThis);


  function prefillTutorContactoRegistro() {
    const u = (global.RUPE_INIT && global.RUPE_INIT.user) || global.__RUPE_USER__ || {};
    const nombre = (u.nombre || '').trim();
    const tel = (u.telefono || u.tel || '').trim();
    const correo = (u.correo || u.email || '').trim();
    const n = document.getElementById('regTutorNombre');
    const t = document.getElementById('regTutorTel');
    const e = document.getElementById('regTutorEmail');
    if (n && !String(n.value || '').trim() && nombre) n.value = nombre;
    if (t && !String(t.value || '').trim() && tel) t.value = tel;
    if (e && !String(e.value || '').trim() && correo) e.value = correo;
    // Forzar teléfono desde BD aunque el placeholder esté vacío
    if (t && tel) {
      t.value = tel;
      t.setAttribute('value', tel);
    }
    const aviso = document.getElementById('regTutorAuto');
    if (aviso && (nombre || tel || correo)) {
      aviso.innerHTML = '<i class="bi bi-person-check me-1"></i>Datos de contacto desde tu cuenta (base de datos). Puedes editarlos si este reporte es de otra persona.';
    }
  }
  global.prefillTutorContactoRegistro = prefillTutorContactoRegistro;

  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(prefillTutorContactoRegistro, 0);
    setTimeout(prefillTutorContactoRegistro, 200);
  });

  function bindValidacionVerdeCampos() {
    ['regHora', 'regFecha', 'regColonia', 'regCalle', 'regReferencia', 'regCollar', 'regChip', 'regAlcaldia'].forEach(function (id) {
      const el = document.getElementById(id);
      if (!el || el._rupeVerde) return;
      el._rupeVerde = true;
      const apply = function () {
        const v = (el.value || '').trim();
        if (!v) {
          el.classList.remove('is-valid');
          if (el.required) el.classList.add('is-invalid');
          else el.classList.remove('is-invalid');
          return;
        }
        const r = validarCampoCaracteres(id, v);
        marcarCampo(el, r.ok !== false);
      };
      el.addEventListener('change', apply);
      el.addEventListener('input', apply);
      apply();
    });
  }
  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(bindValidacionVerdeCampos, 50);
  });
  global.bindValidacionVerdeCampos = bindValidacionVerdeCampos;
