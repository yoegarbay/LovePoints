/**=======================================================
 * app.js
 * -------------------------------------------------------
 * Propósito : Estado global de la aplicación (LoveState).
 * Sprint    : 1 (stubs) -> 2 (flujo documentado) -> 3+ (lógica)
 * Depende de: nada por ahora (api.js se añadirá más adelante).
 * Se carga  : en todas las páginas (login, registro, vincular y app),
 *             antes de cualquier script que use `state`.
 * =======================================================*/


/**=======================================================
 * FLUJO DE ESTADO
 * -------------------------------------------------------
 * 1. Al cargar cualquier página: GET check_login.php
 *      - Sin sesión            -> redirigir a login.php
 *                                 (en login y registro no: ya estás ahí)
 *      - Sesión sin pareja     -> redirigir a vincular.php
 *      - Sesión con pareja     -> continuar en app.php
 * 2. state.cargarDatos() rellena el estado:
 *      usuario, pareja, saldos, predefinidos y eventos.
 * 3. Cualquier acción del usuario (filtrar, crear, anular...)
 *      modifica el estado y llama a la función que repinta
 *      la parte afectada de la interfaz.
 *      - Filtros: los aplica el BE (eventos_list.php recibe
 *        usuario, efecto, desde, hasta, nombre y anulado).
 *        El filtro «mes» se convierte en desde/hasta.
 *      - Ordenación (fecha, coste, persona): la hace el FE.
 * 4. Los saldos y estadísticas NUNCA se calculan en el
 *      front: se piden siempre al BE (saldos.php, stats.php).
 * 5. Freya (asistente): todo mensaje al usuario (errores de
 *      validación, avisos, confirmaciones) sale por ella.
 *      Mensajes recientes: sessionStorage. Preferencias (oculta,
 *      posición): localStorage. Nunca datos de eventos o importes.
 *      - En formularios habla sola al enfocar un campo.
 *      - En el resto de la app, solo si el usuario pulsa «?».
 * =======================================================*/


// ===== CLASE LOVESTATE =====

/**
 * Estado global de LovePoints.
 * Guarda los datos que la interfaz necesita y que vienen del BE.
 * Hay una única instancia: `state` (al final del archivo).
 */
class LoveState {

    constructor() {
        /** @type {Object|null} Usuario autenticado (id, nombre, codigo, rol...). */
        this.usuario = null;

        /** @type {Object|null} Datos de la pareja, o null si no tiene. */
        this.pareja = null;

        /**
         * Saldo de cada persona de la pareja (lo calcula el BE).
         * TODO(Sprint 3): confirmar con BE el formato exacto de saldos.php.
         * @type {Array<Object>}
         */
        this.saldos = [];

        /** @type {Array<Object>} Eventos que devuelve eventos_list.php con los filtros activos. */
        this.eventos = [];

        /** @type {Array<Object>} Eventos predefinidos del usuario. */
        this.predefinidos = [];

        /**
         * Filtros activos de la tabla de eventos (se envían a eventos_list.php).
         * persona: se envía como «usuario».
         * efecto: '' | 'resta' | 'suma'   (valores del contrato con el BE)
         * mes: se convierte en desde/hasta antes de enviarlo.
         * anulados: true = mostrar también los anulados.
         */
        this.filtros = { persona: '', efecto: '', desde: '', hasta: '', mes: '', nombre: '', anulados: false };

        /** @type {'mes'|'trimestre'|'año'} Granularidad de las gráficas. */
        this.granularidad = 'mes';

        /**
         * Últimos mensajes de Freya (máximo 5).
         * Se espejan en sessionStorage para que sobrevivan al cambio de página
         * (login -> vincular -> app) y se borran al cerrar la pestaña.
         * Envolver siempre lecturas/escrituras en try/catch.
         * @type {Array<string>}
         */
        this.mensajesFreya = [];
    }

    // ----- Consultas -----

    /**
     * Indica si el usuario tiene pareja vinculada.
     * Se usa para decidir la redirección a vincular.php.
     * @returns {boolean}
     */
    tienePareja() { return this.pareja !== null; }

    // ----- Stubs (se implementan en sprints posteriores) -----

    /** TODO(Sprint 3): cargar usuario, pareja, predefinidos y eventos (check_login, pareja_estado, ...). */
    cargarDatos() { }

    /** TODO(Sprint 4): pedir los saldos al BE (GET saldos.php) y guardarlos en this.saldos. */
    actualizarSaldos() { }

    /** TODO(Sprint 4): pedir al BE los eventos que cumplen this.filtros (GET eventos_list.php). */
    filtrarEventos() { }

    /** TODO(Sprint 5): dibujar las gráficas con Chart.js según this.granularidad (GET stats.php). */
    renderizarGraficas() { }

    /** TODO(Sprint 3): añadir un mensaje a this.mensajesFreya (máx. 5) y mostrarlo en la burbuja. */
    decirFreya() { }
}


// ===== INSTANCIA GLOBAL =====
const state = new LoveState();