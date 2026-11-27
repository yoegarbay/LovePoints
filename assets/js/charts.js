/**=======================================================
 * charts.js
 * -------------------------------------------------------
 * Propósito : Dibujar la gráfica de puntos netos por persona
 *             y periodo (mes / trimestre / año) con Chart.js.
 * Sprint    : 5
 * Depende de: Chart.js (cdnjs) y app.js (state). Los datos
 *             los calcula el BE (stats.php); aquí solo se dibujan.
 * Se carga  : solo en app.php, después de app.js.
 * =======================================================*/

/** Colores de cada persona (paleta 2: principal y secundario). */
const COLORES_GRAFICA = ['#7254D1', '#F2A7C3'];

/** @type {Chart|null} Gráfica actual (se destruye antes de dibujar otra). */
let graficaGasto = null;

/**
 * Dibuja la gráfica de barras con la respuesta de stats.php.
 * @param {{periodos: Array<string>, series: Array<{nombre: string, valores: Array<number>}>}} datos
 */
function dibujarGrafica(datos) {
    const lienzo = document.querySelector('#grafica-gasto');
    const vacia = document.querySelector('#grafica-vacia');
    if (!lienzo) return;

    const periodos = datos.periodos || [];
    const series = datos.series || [];
    const hayDatos = periodos.length > 0 && series.length > 0;

    // Sin datos: mensaje en lugar de la gráfica
    lienzo.parentElement.classList.toggle('oculto', !hayDatos);
    vacia.classList.toggle('oculto', hayDatos);
    if (!hayDatos) return;

    // Si Chart.js no ha cargado (sin internet), Freya avisa
    if (typeof Chart === 'undefined') {
        state.decirFreya('No he podido cargar las gráficas. Revisa la conexión a internet.');
        return;
    }

    if (graficaGasto) graficaGasto.destroy();

    graficaGasto = new Chart(lienzo, {
        type: 'bar',
        data: {
            labels: periodos,
            datasets: series.map((serie, i) => ({
                label: serie.nombre,
                data: serie.valores,
                backgroundColor: COLORES_GRAFICA[i % COLORES_GRAFICA.length],
                borderRadius: 6
            }))
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // usa el alto de .grafica (CSS)
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ctx.dataset.label + ': ' + formatearPuntos(ctx.parsed.y)
                    }
                }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}
