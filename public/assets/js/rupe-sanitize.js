/**
 * RUPE CDMX — Sanitizar datos (sin null en el front)
 * ES6
 */
'use strict';

(function (global) {
  /** Cadena segura: nunca null/undefined */
  function rupeStr(v, fallback) {
    if (v === null || v === undefined) return fallback !== undefined ? fallback : '';
    const s = String(v).trim();
    if (s === '' || s.toLowerCase() === 'null' || s.toLowerCase() === 'undefined') {
      return fallback !== undefined ? fallback : '';
    }
    return String(v);
  }

  /** Número o 0 */
  function rupeNum(v, fallback) {
    if (v === null || v === undefined || v === '') {
      return fallback !== undefined ? fallback : 0;
    }
    const n = Number(v);
    return Number.isFinite(n) ? n : (fallback !== undefined ? fallback : 0);
  }

  /** Valor para mostrar en UI (vacío → —) */
  function rupeDisplay(v, emptyMark) {
    const s = rupeStr(v);
    return s === '' ? (emptyMark !== undefined ? emptyMark : '—') : s;
  }

  /** Limpia un objeto/array: null → '' o 0 en ids */
  function rupeClean(data) {
    if (data === null || data === undefined) return '';
    if (Array.isArray(data)) {
      return data.map(function (item) {
        return rupeClean(item);
      });
    }
    if (typeof data === 'object') {
      const out = {};
      Object.keys(data).forEach(function (key) {
        const val = data[key];
        if (val === null || val === undefined) {
          if (key === 'id' || key.indexOf('id_') === 0 || key.slice(-3) === '_id') {
            out[key] = 0;
          } else if (Array.isArray(val)) {
            out[key] = [];
          } else {
            out[key] = '';
          }
        } else if (typeof val === 'object') {
          out[key] = rupeClean(val);
        } else if (typeof val === 'string') {
          const low = val.trim().toLowerCase();
          out[key] = low === 'null' || low === 'undefined' ? '' : val;
        } else {
          out[key] = val;
        }
      });
      return out;
    }
    if (typeof data === 'string') {
      const low = data.trim().toLowerCase();
      return low === 'null' || low === 'undefined' ? '' : data;
    }
    return data;
  }

  /** ¿Valor vacío (null, undefined, '', 'null')? */
  function rupeEmpty(v) {
    if (v === null || v === undefined) return true;
    if (typeof v === 'string') {
      const s = v.trim().toLowerCase();
      return s === '' || s === 'null' || s === 'undefined';
    }
    return false;
  }

  global.rupeStr = rupeStr;
  global.rupeNum = rupeNum;
  global.rupeDisplay = rupeDisplay;
  global.rupeClean = rupeClean;
  global.rupeEmpty = rupeEmpty;
})(typeof window !== 'undefined' ? window : globalThis);
