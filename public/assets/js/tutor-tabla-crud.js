/**
 * RUPE CDMX — Tabla CRUD tutor (11 columnas)
 * | Folio | Foto | Mascota | Raza | Alcaldía | Colonia | Calle | Referencia | Fecha | Estatus | Acciones |
 */
'use strict';

(function (global) {
  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function clean(v) {
    if (v === null || v === undefined) return '—';
    var s = String(v).trim();
    if (!s || s === 'null' || s === 'undefined' || s === 'NaN') return '—';
    return s;
  }

  function badgeClass(est) {
    if (est === 'En Búsqueda') return 'bg-danger';
    if (est === 'En Resguardo') return 'bg-warning text-dark';
    if (est === 'Localizado') return 'bg-success';
    return 'bg-secondary';
  }

  function fotoDe(reg) {
    var f = reg.foto || reg.foto_url || '';
    f = String(f).trim();
    if (!f || f === 'null' || f === 'undefined') {
      var esp = String(reg.especie || 'perro').toLowerCase();
      return esp.indexOf('gato') >= 0
        ? '/assets/img/placeholder-gato.svg'
        : '/assets/img/placeholder-perro.svg';
    }
    if (f.charAt(0) !== '/' && f.indexOf('http') !== 0 && f.indexOf('data:') !== 0) {
      f = '/' + f;
    }
    return f;
  }

  function splitDir(reg) {
    var colonia = String((reg && reg.colonia) || '').trim();
    var calle = String((reg && reg.calle) || '').trim();
    var referencia = String((reg && reg.referencia) || '').trim();
    var dir = String((reg && (reg.direccion_ref || '')) || '').trim();
    if ((!calle || !referencia) && dir) {
      var mCalle = dir.match(/Calle:\s*([^|]+)/i);
      var mRef = dir.match(/Ref(?:erencia)?:\s*(.+)$/i);
      if (!calle && mCalle) calle = mCalle[1].trim();
      if (!referencia && mRef) referencia = mRef[1].trim();
      if (!calle && !mCalle && dir.indexOf(',') >= 0) {
        var parts = dir.split(',');
        calle = (parts[0] || '').trim();
        if (!referencia) referencia = parts.slice(1).join(',').trim();
      } else if (!calle && !referencia) {
        referencia = dir;
      }
    }
    return { colonia: colonia, calle: calle, referencia: referencia };
  }

  function normalize(m) {
    if (!m || typeof m !== 'object') return m;
    return {
      id: m.id || m.id_mascota || 0,
      id_mascota: m.id_mascota || m.id || 0,
      folio: m.folio || '',
      nombre: m.nombre || '',
      raza: m.raza || m.raza_nombre || '',
      raza_nombre: m.raza_nombre || m.raza || '',
      id_raza: m.id_raza || 0,
      alcaldia: m.alcaldia || m.alcaldia_nombre || '',
      alcaldia_nombre: m.alcaldia_nombre || m.alcaldia || '',
      id_alcaldia: m.id_alcaldia || 0,
      colonia: m.colonia || '',
      calle: m.calle || '',
      direccion_ref: m.direccion_ref || '',
      referencia: m.referencia || '',
      fecha: m.fecha || m.fecha_extravio || '',
      fecha_extravio: m.fecha_extravio || m.fecha || '',
      estatus: m.estatus || 'En Búsqueda',
      foto: m.foto || m.foto_url || '',
      foto_url: m.foto_url || m.foto || '',
      especie: m.especie || 'perro',
      qr_token: m.qr_token || '',
    };
  }

  function registros() {
    var st = global.state || {};
    if (Array.isArray(st.registros) && st.registros.length) {
      return st.registros.map(normalize);
    }
    if (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas) && global.RUPE_INIT.mascotas.length) {
      return global.RUPE_INIT.mascotas.map(normalize);
    }
    return [];
  }

  function pintarTablaCrud(filtro) {
    var tbody = document.getElementById('tbodyCrud');
    if (!tbody) return;

    var regs = registros();
    var q = String(
      filtro != null && filtro !== ''
        ? filtro
        : ((document.getElementById('inputBuscadorCrud') || {}).value || '')
    )
      .toLowerCase()
      .trim();

    var lista = !q
      ? regs
      : regs.filter(function (r) {
          return (
            String(r.folio || '').toLowerCase().indexOf(q) !== -1 ||
            String(r.nombre || '').toLowerCase().indexOf(q) !== -1
          );
        });

    if (!lista.length) {
      // No borrar filas del servidor si aún no hay datos en memoria
      if (!regs.length && tbody.querySelector('tr[data-id], tr[data-folio]')) {
        return;
      }
      tbody.innerHTML =
        '<tr><td colspan="11" class="text-center text-muted py-4">' +
        (regs.length ? 'Sin resultados para el filtro.' : 'Sin registros. Usa el módulo Registro.') +
        '</td></tr>';
      return;
    }

    var html = '';
    for (var i = 0; i < lista.length; i++) {
      var reg = lista[i] || {};
      var idx = regs.findIndex(function (r) {
        return (r.folio && r.folio === reg.folio) || (r.id && r.id === reg.id);
      });
      if (idx < 0) idx = i;

      var folio = esc(clean(reg.folio));
      var nombre = esc(clean(reg.nombre));
      var raza = esc(clean(reg.raza || reg.raza_nombre));
      var alc = esc(clean(reg.alcaldia || reg.alcaldia_nombre));
      var addr = splitDir(reg);
      var colonia = esc(clean(addr.colonia));
      var calle = esc(clean(addr.calle));
      var referencia = esc(clean(addr.referencia));
      var fecha = esc(clean(reg.fecha || reg.fecha_extravio));
      var est = clean(reg.estatus || 'En Búsqueda');
      var id = esc(reg.id || reg.id_mascota || '');
      var foto = esc(fotoDe(reg));
      var bc = badgeClass(est);

      html +=
        '<tr data-id="' + id + '" data-folio="' + folio + '">' +
        '<td class="ps-4 fw-bold text-danger">#' + folio + '</td>' +
        '<td><img src="' + foto + '" alt="' + nombre + '" width="48" height="48" ' +
        'class="rounded border" style="width:48px;height:48px;object-fit:cover;background:#f0f0f0;display:block;" ' +
        'onerror="this.onerror=null;this.src=\'/assets/img/placeholder-mascota.svg\'"></td>' +
        '<td><strong>' + nombre + '</strong></td>' +
        '<td>' + raza + '</td>' +
        '<td>' + alc + '</td>' +
        '<td class="small">' + colonia + '</td>' +
        '<td class="small">' + calle + '</td>' +
        '<td class="small">' + referencia + '</td>' +
        '<td>' + fecha + '</td>' +
        '<td><span class="badge ' + bc + '">' + esc(est) + '</span></td>' +
        '<td class="text-end pe-4 text-nowrap">' +
        '<button type="button" class="btn btn-sm btn-outline-primary me-1" title="Ver" data-crud="ver" data-index="' + idx + '"><i class="bi bi-eye"></i></button>' +
        '<button type="button" class="btn btn-sm btn-outline-secondary me-1" title="Editar" data-crud="editar" data-index="' + idx + '"><i class="bi bi-pencil"></i></button>' +
        '<button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" data-crud="eliminar" data-index="' + idx + '"><i class="bi bi-trash"></i></button>' +
        '</td></tr>';
    }
    tbody.innerHTML = html;
  }

  async function cargarDesdeApi() {
    try {
      var csrf = (global.RUPE_INIT && global.RUPE_INIT.csrf) || global.__CSRF__ || '';
      var res = await fetch('/api/tutor/mascotas', {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf,
        },
        credentials: 'same-origin',
      });
      var data = await res.json().catch(function () { return {}; });
      var arr = (data.data && data.data.mascotas) || data.mascotas || [];
      if (!Array.isArray(arr) || !arr.length) return false;
      var mapped = arr.map(normalize);
      if (!global.state || typeof global.state !== 'object') global.state = {};
      global.state.registros = mapped;
      if (global.RUPE_INIT) global.RUPE_INIT.mascotas = mapped.slice();
      pintarTablaCrud();
      return true;
    } catch (e) {
      console.warn('[RUPE] cargarDesdeApi CRUD', e);
      return false;
    }
  }

  global.pintarTablaCrud = pintarTablaCrud;
  global.renderizarTablaCrud = pintarTablaCrud;
  global.renderTablaCrudDB = pintarTablaCrud;
  global.rupeRenderCrud = pintarTablaCrud;
  global.RUPECrud = global.RUPECrud || {};
  global.RUPECrud.renderTablaCrud = pintarTablaCrud;

  function boot() {
    try {
      // Sembrar state desde RUPE_INIT si está vacío
      if ((!global.state || !Array.isArray(global.state.registros) || !global.state.registros.length) &&
          global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas) && global.RUPE_INIT.mascotas.length) {
        if (!global.state) global.state = {};
        global.state.registros = global.RUPE_INIT.mascotas.map(normalize);
      }
      pintarTablaCrud();
    } catch (e) {
      console.warn('[RUPE] pintarTablaCrud', e);
    }
  }

  function bootAll() {
    boot();
    // Siempre refrescar desde API para datos reales de BD
    cargarDesdeApi().then(function (ok) {
      if (!ok) boot();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(bootAll, 80);
      setTimeout(boot, 500);
    });
  } else {
    setTimeout(bootAll, 80);
    setTimeout(boot, 500);
  }
  global.addEventListener('load', function () {
    setTimeout(bootAll, 150);
  });
})(typeof window !== 'undefined' ? window : globalThis);
