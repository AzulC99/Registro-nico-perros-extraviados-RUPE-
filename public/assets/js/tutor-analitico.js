/**
 * RUPE CDMX — Panel Analítico: filtros, KPIs, gráficos de barras y pastel
 */
'use strict';

(function (global) {
  var charts = {};
  var filtros = { alcaldia: '', estatus: '', especie: '' };

  function toast(msg, type) {
    if (typeof global.rupeToast === 'function') return global.rupeToast(msg, type || 'info');
    if (typeof global.mostrarToastTutor === 'function') return global.mostrarToastTutor(msg, type || 'success');
  }

  function listRegistros() {
    if (global.state && Array.isArray(global.state.registros) && global.state.registros.length) {
      return global.state.registros;
    }
    if (global.RUPE_INIT && Array.isArray(global.RUPE_INIT.mascotas)) {
      return global.RUPE_INIT.mascotas;
    }
    return [];
  }

  function normSexo(v) {
    var s = String(v || '').toLowerCase();
    if (s.indexOf('hembra') >= 0) return 'Hembra';
    if (s.indexOf('macho') >= 0) return 'Macho';
    return 'Otro';
  }

  function normTamano(r) {
    var t = String(r.tamano || '').trim();
    if (!t && r.senas_particulares) {
      var m = String(r.senas_particulares).match(/Tama[ñn]o\s*:\s*([^|]+)/i);
      if (m) t = m[1].trim();
    }
    t = t.charAt(0).toUpperCase() + t.slice(1).toLowerCase();
    if (['Pequeño', 'Pequeno', 'Mediano', 'Grande', 'Gigante'].indexOf(t) < 0) {
      if (/peque/i.test(t)) return 'Pequeño';
      if (/med/i.test(t)) return 'Mediano';
      if (/grand/i.test(t)) return 'Grande';
      if (/gig/i.test(t)) return 'Gigante';
      return 'Sin dato';
    }
    if (t === 'Pequeno') return 'Pequeño';
    return t;
  }

  function normEstatus(v) {
    var e = String(v || 'En Búsqueda');
    if (/localiz/i.test(e)) return 'Localizado';
    if (/resguard/i.test(e)) return 'En Resguardo';
    return 'En Búsqueda';
  }

  function hasChip(r) {
    var c = String(r.microchip || r.chip || '').trim().toLowerCase();
    if (!c || c === 'no' || c.indexOf('no posee') >= 0 || c.indexOf('no registrado') >= 0) {
      // check senas
      var s = String(r.senas_particulares || r.senas || '');
      if (/microchip|chip\s*:/i.test(s) && !/no\s*(posee|cuenta)/i.test(s)) return true;
      return false;
    }
    if (c === 'sí' || c === 'si' || c.indexOf('sí') >= 0 || c.indexOf('si,') >= 0) return true;
    return c.length > 1;
  }

  function filtrar(list) {
    return list.filter(function (r) {
      if (!r) return false;
      if (filtros.alcaldia) {
        var a = String(r.alcaldia || r.alcaldia_nombre || '');
        if (a !== filtros.alcaldia) return false;
      }
      if (filtros.estatus) {
        if (normEstatus(r.estatus) !== filtros.estatus) return false;
      }
      if (filtros.especie) {
        var e = String(r.especie || 'perro').toLowerCase();
        if (e.indexOf(filtros.especie.toLowerCase()) < 0) return false;
      }
      return true;
    });
  }

  function llenarFiltrosAnalitico() {
    var list = listRegistros();
    var selAlc = document.getElementById('filtroAnaliticoAlcaldia');
    var selEst = document.getElementById('filtroAnaliticoEstatus');
    var selEsp = document.getElementById('filtroAnaliticoEspecie');

    if (selAlc) {
      var prev = selAlc.value;
      var set = {};
      list.forEach(function (r) {
        var a = String(r.alcaldia || r.alcaldia_nombre || '').trim();
        if (a) set[a] = true;
      });
      // also from RUPE_INIT alcaldias catalog
      var cat = (global.RUPE_INIT && global.RUPE_INIT.alcaldias) || [];
      cat.forEach(function (a) {
        var n = a.nombre || a;
        if (n) set[String(n)] = true;
      });
      selAlc.innerHTML = '<option value="">Todas las alcaldías</option>';
      Object.keys(set)
        .sort()
        .forEach(function (n) {
          var o = document.createElement('option');
          o.value = n;
          o.textContent = n;
          selAlc.appendChild(o);
        });
      if (prev) {
        try {
          selAlc.value = prev;
        } catch (_) {}
      }
    }

    if (selEst && selEst.options.length < 2) {
      selEst.innerHTML =
        '<option value="">Todos los estatus</option>' +
        '<option value="En Búsqueda">En Búsqueda</option>' +
        '<option value="En Resguardo">En Resguardo</option>' +
        '<option value="Localizado">Localizado</option>';
    }

    if (selEsp && selEsp.options.length < 2) {
      selEsp.innerHTML =
        '<option value="">Todas las especies</option>' +
        '<option value="perro">Perro</option>' +
        '<option value="gato">Gato</option>' +
        '<option value="otro">Otro</option>';
    }
  }
  global.llenarFiltrosAnalitico = llenarFiltrosAnalitico;

  function ensureCharts() {
    if (typeof Chart === 'undefined') return false;
    var opts = { responsive: true, maintainAspectRatio: false };

    function make(id, type, labels, colors) {
      var el = document.getElementById(id);
      if (!el) return null;
      if (charts[id]) {
        try {
          charts[id].destroy();
        } catch (_) {}
      }
      charts[id] = new Chart(el.getContext('2d'), {
        type: type,
        data: {
          labels: labels,
          datasets: [
            {
              data: labels.map(function () {
                return 0;
              }),
              backgroundColor: colors,
              borderWidth: type === 'bar' || type === 'line' ? 1 : 0,
              borderColor: type === 'line' ? '#6B1D2F' : undefined,
              tension: type === 'line' ? 0.3 : undefined,
              fill: type === 'line' ? false : undefined,
            },
          ],
        },
        options: Object.assign({}, opts, {
          plugins: { legend: { display: type !== 'bar' && type !== 'line' } },
          scales:
            type === 'bar' || type === 'line'
              ? { y: { beginAtZero: true, ticks: { precision: 0 } } }
              : undefined,
        }),
      });
      return charts[id];
    }

    if (!charts.chartEstatus) {
      make('chartEstatus', 'doughnut', ['En Búsqueda', 'En Resguardo', 'Localizado'], [
        '#dc3545',
        '#ffc107',
        '#198754',
      ]);
    }
    if (!charts.chartAlcaldias) {
      make('chartAlcaldias', 'bar', ['—'], ['#6B1D2F']);
    }
    if (!charts.chartSexo) {
      make('chartSexo', 'pie', ['Macho', 'Hembra', 'Otro'], ['#0d6efd', '#d63384', '#6c757d']);
    }
    if (!charts.chartChip) {
      make('chartChip', 'doughnut', ['Con chip', 'Sin chip'], ['#6f42c1', '#adb5bd']);
    }
    if (!charts.chartTamanos) {
      make('chartTamanos', 'bar', ['Pequeño', 'Mediano', 'Grande', 'Gigante', 'Sin dato'], [
        '#20c997',
        '#0dcaf0',
        '#fd7e14',
        '#6B1D2F',
        '#ced4da',
      ]);
    }
    if (!charts.chartRazas) {
      make('chartRazas', 'bar', ['—'], ['#E8A33D']);
    }
    if (!charts.chartFechas) {
      make('chartFechas', 'line', ['—'], ['#6B1D2F']);
    }

    // mirror to state.charts for legacy
    if (global.state) {
      global.state.charts = global.state.charts || {};
      global.state.charts.estatus = charts.chartEstatus;
      global.state.charts.alcaldias = charts.chartAlcaldias;
      global.state.charts.sexo = charts.chartSexo;
      global.state.charts.chip = charts.chartChip;
      global.state.charts.tamanos = charts.chartTamanos;
      global.state.charts.razas = charts.chartRazas;
      global.state.charts.fechas = charts.chartFechas;
    }
    return true;
  }

  function setChartData(id, labels, data) {
    var c = charts[id];
    if (!c) return;
    c.data.labels = labels;
    c.data.datasets[0].data = data;
    try {
      c.update();
    } catch (_) {}
  }


  function generarInsightsPanel(stats) {
    var lista = document.getElementById('listaInsights');
    if (!lista) return;
    stats = stats || {};
    var total = stats.total || 0;
    var busqueda = stats.busqueda || 0;
    var resguardo = stats.resguardo || 0;
    var localizado = stats.localizado || 0;
    var totalAvist = stats.totalAvist || 0;
    var sinAvist = stats.sinAvist || 0;
    var conChip = stats.conChip || 0;
    var porAlc = stats.porAlc || {};
    var tamanos = stats.tamanos || {};
    var porRaza = stats.porRaza || {};
    var filtrosTxt = stats.filtrosTxt || '';

    if (!total) {
      lista.innerHTML =
        '<li class="text-muted small mb-0">Sin registros' +
        (filtrosTxt ? ' con los filtros aplicados' : '') +
        '. Registra mascotas para ver insights automáticos.</li>';
      return;
    }

    var tasaLoc = Math.round((localizado / total) * 100);
    var pctChip = Math.round((conChip / total) * 100);

    var topAlc = null;
    Object.keys(porAlc).forEach(function (k) {
      var n = porAlc[k].total || porAlc[k] || 0;
      if (typeof porAlc[k] === 'object') n = porAlc[k].total || 0;
      if (!topAlc || n > topAlc[1]) topAlc = [k, n];
    });

    var topTam = null;
    Object.keys(tamanos).forEach(function (k) {
      if (k === 'Sin dato') return;
      var n = tamanos[k] || 0;
      if (!topTam || n > topTam[1]) topTam = [k, n];
    });

    var topRaza = null;
    Object.keys(porRaza).forEach(function (k) {
      if (k === 'Sin especificar') return;
      var n = porRaza[k] || 0;
      if (!topRaza || n > topRaza[1]) topRaza = [k, n];
    });

    var items = [];
    if (filtrosTxt) {
      items.push(
        '<li class="mb-2 small text-muted"><i class="bi bi-funnel me-2"></i>Filtros activos: <strong>' +
          filtrosTxt.replace(/</g, '&lt;') +
          '</strong></li>'
      );
    }
    items.push(
      '<li class="mb-2 small"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>' +
        tasaLoc +
        '%</strong> de los casos han sido localizados y reunificados (' +
        localizado +
        ' de ' +
        total +
        ').</li>'
    );
    if (busqueda > 0) {
      items.push(
        '<li class="mb-2 small"><i class="bi bi-broadcast text-danger me-2"></i>Hay <strong>' +
          busqueda +
          '</strong> alerta' +
          (busqueda !== 1 ? 's' : '') +
          ' activa' +
          (busqueda !== 1 ? 's' : '') +
          ' en búsqueda.</li>'
      );
    }
    if (sinAvist > 0) {
      items.push(
        '<li class="mb-2 small"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i><strong>' +
          sinAvist +
          '</strong> caso' +
          (sinAvist !== 1 ? 's' : '') +
          ' en búsqueda sin avistamientos reportados.</li>'
      );
    }
    if (totalAvist > 0) {
      items.push(
        '<li class="mb-2 small"><i class="bi bi-geo-alt-fill text-info me-2"></i>La comunidad ha enviado <strong>' +
          totalAvist +
          '</strong> avistamiento' +
          (totalAvist !== 1 ? 's' : '') +
          ' (prom. ' +
          (total ? (totalAvist / total).toFixed(1) : '0') +
          ' por caso).</li>'
      );
    }
    items.push(
      '<li class="mb-2 small"><i class="bi bi-cpu-fill me-2" style="color:#6f42c1;"></i><strong>' +
        pctChip +
        '%</strong> de las mascotas cuentan con microchip o tatuaje (' +
        conChip +
        ' de ' +
        total +
        ').</li>'
    );
    if (topAlc && topAlc[1] > 0) {
      items.push(
        '<li class="mb-2 small"><i class="bi bi-geo-fill text-primary me-2"></i>La alcaldía con más reportes es <strong>' +
          String(topAlc[0]).replace(/</g, '&lt;') +
          '</strong> (' +
          topAlc[1] +
          ' casos).</li>'
      );
    }
    if (topTam && topTam[1] > 0) {
      items.push(
        '<li class="mb-2 small"><i class="bi bi-bounding-box text-success me-2"></i>Tamaño más frecuente: <strong>' +
          String(topTam[0]).replace(/</g, '&lt;') +
          '</strong> (' +
          topTam[1] +
          ' casos).</li>'
      );
    }
    if (topRaza && topRaza[1] > 0) {
      items.push(
        '<li class="mb-2 small"><i class="bi bi-trophy-fill text-warning me-2"></i>Raza más reportada: <strong>' +
          String(topRaza[0]).replace(/</g, '&lt;') +
          '</strong> (' +
          topRaza[1] +
          ').</li>'
      );
    }
    if (resguardo > 0) {
      items.push(
        '<li class="mb-0 small"><i class="bi bi-house-heart-fill text-warning me-2"></i><strong>' +
          resguardo +
          '</strong> mascota' +
          (resguardo !== 1 ? 's' : '') +
          ' en resguardo temporal o albergue.</li>'
      );
    }
    lista.innerHTML = items.join('');
  }
  global.generarInsightsPanel = generarInsightsPanel;


  function actualizarGraficasPanel() {
    var all = listRegistros();
    var list = filtrar(all);
    ensureCharts();

    var total = list.length;
    var busqueda = 0,
      resguardo = 0,
      localizado = 0;
    var machos = 0,
      hembras = 0,
      sexoOtro = 0;
    var conChip = 0,
      sinChip = 0;
    var sinAvist = 0;
    var totalAvist = 0;
    var tamanos = { Pequeño: 0, Mediano: 0, Grande: 0, Gigante: 0, 'Sin dato': 0 };
    var porAlc = {};
    var porRaza = {};
    var porFecha = {};

    list.forEach(function (r) {
      var est = normEstatus(r.estatus);
      if (est === 'En Búsqueda') busqueda++;
      else if (est === 'En Resguardo') resguardo++;
      else localizado++;

      var sx = normSexo(r.sexo);
      if (sx === 'Macho') machos++;
      else if (sx === 'Hembra') hembras++;
      else sexoOtro++;

      if (hasChip(r)) conChip++;
      else sinChip++;

      var av = Array.isArray(r.avistamientos) ? r.avistamientos.length : 0;
      totalAvist += av;
      if (est === 'En Búsqueda' && av === 0) sinAvist++;

      var tam = normTamano(r);
      if (tamanos[tam] === undefined) tamanos['Sin dato']++;
      else tamanos[tam]++;

      var alc = String(r.alcaldia || r.alcaldia_nombre || 'Sin dato').trim() || 'Sin dato';
      if (!porAlc[alc]) porAlc[alc] = { total: 0, busqueda: 0, resguardo: 0, localizado: 0 };
      porAlc[alc].total++;
      if (est === 'En Búsqueda') porAlc[alc].busqueda++;
      else if (est === 'En Resguardo') porAlc[alc].resguardo++;
      else porAlc[alc].localizado++;

      var raza = String(r.raza || r.raza_nombre || 'Sin especificar').trim() || 'Sin especificar';
      porRaza[raza] = (porRaza[raza] || 0) + 1;

      var fecha = String(r.fecha || r.fecha_extravio || '').slice(0, 10);
      if (fecha) porFecha[fecha] = (porFecha[fecha] || 0) + 1;
    });

    // KPIs
    function set(id, val) {
      var el = document.getElementById(id);
      if (el) el.textContent = val;
    }
    set('kpiTotalRegistros', String(total));
    var tasa = total ? Math.round((localizado / total) * 100) : 0;
    set('kpiTasaLocalizacion', tasa + '%');
    set('kpiTasaDetalle', localizado + ' de ' + total + ' casos');
    set('kpiEnResguardo', String(resguardo));
    set('kpiResguardoPct', (total ? Math.round((resguardo / total) * 100) : 0) + '% del total');
    set('kpiAlertasActivas', String(busqueda));
    set('kpiAlertasPct', (total ? Math.round((busqueda / total) * 100) : 0) + '% del total');
    set('kpiTotalAvistamientos', String(totalAvist));
    set(
      'kpiPromAvist',
      'Prom. ' + (total ? (totalAvist / total).toFixed(1) : '0') + ' por caso'
    );
    var chipPct = total ? Math.round((conChip / total) * 100) : 0;
    set('kpiConChip', chipPct + '%');
    set('kpiChipDetalle', conChip + ' de ' + total + ' identificados');
    set('kpiSinAvist', String(sinAvist));

    // Charts
    setChartData('chartEstatus', ['En Búsqueda', 'En Resguardo', 'Localizado'], [
      busqueda,
      resguardo,
      localizado,
    ]);

    var alcOrd = Object.keys(porAlc).sort(function (a, b) {
      return porAlc[b].total - porAlc[a].total;
    });
    setChartData(
      'chartAlcaldias',
      alcOrd.length ? alcOrd : ['Sin datos'],
      alcOrd.length
        ? alcOrd.map(function (k) {
            return porAlc[k].total;
          })
        : [0]
    );

    setChartData('chartSexo', ['Macho', 'Hembra', 'Otro'], [machos, hembras, sexoOtro]);
    setChartData('chartChip', ['Con chip', 'Sin chip'], [conChip, sinChip]);
    setChartData(
      'chartTamanos',
      ['Pequeño', 'Mediano', 'Grande', 'Gigante', 'Sin dato'],
      [
        tamanos['Pequeño'],
        tamanos['Mediano'],
        tamanos['Grande'],
        tamanos['Gigante'],
        tamanos['Sin dato'],
      ]
    );

    var razasOrd = Object.keys(porRaza)
      .sort(function (a, b) {
        return porRaza[b] - porRaza[a];
      })
      .slice(0, 8);
    setChartData(
      'chartRazas',
      razasOrd.length ? razasOrd : ['Sin datos'],
      razasOrd.length
        ? razasOrd.map(function (k) {
            return porRaza[k];
          })
        : [0]
    );

    var fechasOrd = Object.keys(porFecha).sort();
    setChartData(
      'chartFechas',
      fechasOrd.length
        ? fechasOrd.map(function (f) {
            var p = f.split('-');
            return p.length === 3 ? p[2] + '/' + p[1] : f;
          })
        : ['Sin datos'],
      fechasOrd.length
        ? fechasOrd.map(function (f) {
            return porFecha[f];
          })
        : [0]
    );

    // Ranking table
    var tbody = document.getElementById('tbodyRankingAlcaldias');
    if (tbody) {
      tbody.innerHTML = '';
      if (!alcOrd.length) {
        tbody.innerHTML =
          '<tr><td colspan="7" class="text-center text-muted py-3">Sin datos. Registra mascotas para ver el ranking.</td></tr>';
      } else {
        alcOrd.forEach(function (alc, i) {
          var d = porAlc[alc];
          var tasaR = d.total ? Math.round((d.localizado / d.total) * 100) : 0;
          var tr = document.createElement('tr');
          tr.innerHTML =
            '<td class="ps-3">' +
            (i + 1) +
            '</td>' +
            '<td>' +
            alc.replace(/</g, '&lt;') +
            '</td>' +
            '<td class="text-center">' +
            d.total +
            '</td>' +
            '<td class="text-center">' +
            d.busqueda +
            '</td>' +
            '<td class="text-center">' +
            d.resguardo +
            '</td>' +
            '<td class="text-center">' +
            d.localizado +
            '</td>' +
            '<td class="text-end pe-3"><span class="badge ' +
            (tasaR >= 50 ? 'bg-success' : tasaR > 0 ? 'bg-warning text-dark' : 'bg-secondary') +
            '">' +
            tasaR +
            '%</span></td>';
          tbody.appendChild(tr);
        });
      }
    }

    // Insights automáticos (misma data filtrada)
    var filtrosTxt = [filtros.alcaldia, filtros.estatus, filtros.especie].filter(Boolean).join(' · ');
    generarInsightsPanel({
      total: total,
      busqueda: busqueda,
      resguardo: resguardo,
      localizado: localizado,
      totalAvist: totalAvist,
      sinAvist: sinAvist,
      conChip: conChip,
      porAlc: porAlc,
      tamanos: tamanos,
      porRaza: porRaza,
      filtrosTxt: filtrosTxt
    });

  }
  global.actualizarGraficasPanel = actualizarGraficasPanel;
  global.actualizarAnalisisMetricas = function () {
    llenarFiltrosAnalitico();
    actualizarGraficasPanel();
    toast('Panel actualizado con datos reales.', 'success');
  };

  function aplicarFiltrosAnalitico() {
    var a = document.getElementById('filtroAnaliticoAlcaldia');
    var e = document.getElementById('filtroAnaliticoEstatus');
    var s = document.getElementById('filtroAnaliticoEspecie');
    filtros.alcaldia = a ? a.value : '';
    filtros.estatus = e ? e.value : '';
    filtros.especie = s ? s.value : '';
    actualizarGraficasPanel();
  }
  global.aplicarFiltrosAnalitico = aplicarFiltrosAnalitico;

  function bind() {
    llenarFiltrosAnalitico();
    ensureCharts();
    actualizarGraficasPanel();
    ['filtroAnaliticoAlcaldia', 'filtroAnaliticoEstatus', 'filtroAnaliticoEspecie'].forEach(
      function (id) {
        var el = document.getElementById(id);
        if (el && !el._rupeAnBound) {
          el._rupeAnBound = true;
          el.addEventListener('change', aplicarFiltrosAnalitico);
        }
      }
    );
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.nav-modulo[data-modulo="analisis"]');
    if (btn) {
      setTimeout(function () {
        bind();
        actualizarGraficasPanel();
      }, 300);
    }
  });

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind);
  else bind();


  /** Exportar CSV/Excel ordenado con fórmulas de totales */
  function exportarResumenCSV() {
    var all = listRegistros();
    var list = filtrar(all);
    if (!list.length) {
      toast('No hay registros para exportar con los filtros actuales.', 'warning');
      return;
    }

    // Encabezados en orden
    var headers = [
      'Folio',
      'Nombre',
      'Raza',
      'Sexo',
      'Tamano',
      'Color',
      'Fecha_Extravio',
      'Alcaldia',
      'Estatus',
      'Avistamientos',
      'Chip',
      'Localizado_SN',
    ];

    var rows = list.map(function (r) {
      var est = normEstatus(r.estatus);
      var av = Array.isArray(r.avistamientos) ? r.avistamientos.length : 0;
      var chip = hasChip(r) ? 'SI' : 'NO';
      var loc = est === 'Localizado' ? 1 : 0;
      return [
        r.folio || '',
        r.nombre || '',
        r.raza || r.raza_nombre || '',
        normSexo(r.sexo),
        normTamano(r),
        r.color || '',
        String(r.fecha || r.fecha_extravio || '').slice(0, 10),
        r.alcaldia || r.alcaldia_nombre || '',
        est,
        av,
        chip,
        loc,
      ];
    });

    // Fila de fórmulas (Excel interpreta =SUM al abrir CSV/UTF-8)
    var lastRow = rows.length + 1; // header is row 1, data starts row 2
    var formulaRow = [
      'TOTALES',
      '',
      '',
      '',
      '',
      '',
      '',
      '',
      '',
      '=SUM(J2:J' + lastRow + ')',
      '',
      '=SUM(L2:L' + lastRow + ')',
    ];
    var tasaRow = [
      'TASA_LOCALIZACION',
      '',
      '',
      '',
      '',
      '',
      '',
      '',
      '',
      '',
      '',
      '=IF(COUNTA(A2:A' + lastRow + ')=0,0,ROUND(SUM(L2:L' + lastRow + ')/COUNTA(A2:A' + lastRow + ')*100,1))&"%"',
    ];

    function esc(c) {
      var s = String(c == null ? '' : c);
      if (/[",\n;]/.test(s) || s.charAt(0) === '=') {
        // Excel: fórmulas sin comillas; texto con comillas
        if (s.charAt(0) === '=') return s;
        return '"' + s.replace(/"/g, '""') + '"';
      }
      return s;
    }

    var lines = [];
    lines.push(headers.join(','));
    rows.forEach(function (row) {
      lines.push(row.map(esc).join(','));
    });
    lines.push(formulaRow.map(esc).join(','));
    lines.push(tasaRow.map(esc).join(','));

    // Resumen por estatus (segunda zona)
    lines.push('');
    lines.push('RESUMEN_ESTATUS,Cantidad,Formula');
    lines.push('En Busqueda,,"=COUNTIF(I2:I' + lastRow + ',""En Búsqueda"")"');
    lines.push('En Resguardo,,"=COUNTIF(I2:I' + lastRow + ',""En Resguardo"")"');
    lines.push('Localizado,,"=COUNTIF(I2:I' + lastRow + ',""Localizado"")"');

    var csv = '\ufeff' + lines.join('\r\n');
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'RUPE_CDMX_analisis_' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    toast('CSV/Excel exportado (' + list.length + ' filas + fórmulas).', 'success');
  }
  global.exportarResumenCSV = exportarResumenCSV;

  /** PDF solo del módulo análisis (gráficos + tablas + KPIs) */
  function exportarPdfAnalitico() {
    var sec = document.getElementById('modulo-analisis');
    if (!sec) {
      toast('No se encontró el panel analítico.', 'danger');
      return;
    }
    // Activar módulo si está oculto
    if (!sec.classList.contains('activo')) {
      var btn = document.querySelector('.nav-modulo[data-modulo="analisis"]');
      if (btn) btn.click();
    }
    document.body.classList.add('print-solo-analisis');
    toast('Preparando PDF del análisis… Usa «Guardar como PDF» en el diálogo.', 'info');
    setTimeout(function () {
      try {
        window.print();
      } finally {
        setTimeout(function () {
          document.body.classList.remove('print-solo-analisis');
        }, 500);
      }
    }, 350);
  }
  global.exportarPdfAnalitico = exportarPdfAnalitico;

  // Refuerzo del botón Filtrar
  global.aplicarFiltrosAnalitico = function () {
    var a = document.getElementById('filtroAnaliticoAlcaldia');
    var e = document.getElementById('filtroAnaliticoEstatus');
    var s = document.getElementById('filtroAnaliticoEspecie');
    filtros.alcaldia = a ? String(a.value || '') : '';
    filtros.estatus = e ? String(e.value || '') : '';
    filtros.especie = s ? String(s.value || '') : '';
    ensureCharts();
    actualizarGraficasPanel();
    var n = filtrar(listRegistros()).length;
    toast(
      'Filtro aplicado: ' +
        n +
        ' registro(s)' +
        (filtros.alcaldia || filtros.estatus || filtros.especie
          ? ' (' +
            [filtros.alcaldia, filtros.estatus, filtros.especie].filter(Boolean).join(' · ') +
            ')'
          : ' · todos'),
      'success'
    );
  };

  global.addEventListener('load', function () {
    setTimeout(bind, 50);
  });
})(typeof window !== 'undefined' ? window : globalThis);
