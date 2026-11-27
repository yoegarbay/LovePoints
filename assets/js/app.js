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
        try {
            const guardados = sessionStorage.getItem('mensajesFreya');
            if (guardados) this.mensajesFreya = JSON.parse(guardados);
        } catch (e) { }
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

    /**
     * Freya dice un mensaje y lo muestra en la burbuja.
     * TODO(Sprint 5): si Freya está oculta, mostrarlo junto al campo.
     * @param {string} texto Mensaje para el usuario.
     * @param {boolean} [guardar=true] false para ayudas de campos (no se guardan).
     */
    decirFreya(texto, guardar = true) {
        if (guardar) {
            this.mensajesFreya.push(texto);
            if (this.mensajesFreya.length > 5) {
                this.mensajesFreya.shift();
            }
            try {
                sessionStorage.setItem('mensajesFreya', JSON.stringify(this.mensajesFreya));
            } catch (e) { }
        }

        const burbuja = document.querySelector('.freya-burbuja');
        if (!burbuja) return;
        burbuja.textContent = texto;
        burbuja.classList.remove('oculto');
    }
}

// ===== INSTANCIA GLOBAL =====
const state = new LoveState();



// ===== FORMULARIOS (Sprint 3) =====

/**
 * Comprueba si un email tiene formato válido (texto@texto.texto).
 * @param {string} email
 * @returns {boolean}
 */
function esEmailValido(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Marca un campo con error y Freya explica qué pasa.
 * @param {HTMLInputElement} input Campo con el error.
 * @param {string} mensaje Explicación para el usuario.
 */
function marcarError(input, mensaje) {
    input.closest('.campo').classList.add('error');
    input.focus();               // primero el foco (dispara la ayuda)...
    state.decirFreya(mensaje);   // ...y después el error, que la sustituye
}

/**
 * Quita las marcas de error de un formulario.
 * @param {HTMLFormElement} form
 */
function limpiarErrores(form) {
    form.querySelectorAll('.campo.error').forEach(c => c.classList.remove('error'));
}

/**
 * Envía un formulario a un endpoint del BE (POST) y devuelve su respuesta JSON.
 * Si no hay conexión o la respuesta no es JSON, devuelve un error claro.
 * @param {string} endpoint Nombre del archivo, p. ej. 'login.php'.
 * @param {HTMLFormElement} form Formulario con los campos (se usan sus name).
 * @returns {Promise<Object>}
 */
async function enviarApi(endpoint, form) {
    try {
        const respuesta = await fetch('api/' + endpoint, {
            method: 'POST',
            body: new FormData(form)
        });
        return await respuesta.json();
    } catch (e) {
        return { ok: false, error: 'No puedo conectar con el servidor. Inténtalo en un rato.' };
    }
}

// Botón «Ver»: enseña u oculta la contraseña
document.querySelectorAll('.boton-ver').forEach(boton => {
    boton.addEventListener('click', () => {
        const input = boton.previousElementSibling; // el <input> de al lado
        const mostrar = input.type === 'password';
        input.type = mostrar ? 'text' : 'password';
        boton.setAttribute('aria-pressed', mostrar);
    });
});

// Freya habla al enfocar cualquier campo con data-ayuda
document.querySelectorAll('[data-ayuda]').forEach(input => {
    input.addEventListener('focus', () => state.decirFreya(input.dataset.ayuda, false));
});

// --- Login ---
const formLogin = document.querySelector('#form-login');
if (formLogin) {
    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();
        limpiarErrores(formLogin);

        const email = formLogin.email.value.trim();
        const password = formLogin.password.value;

        if (!esEmailValido(email)) {
            return marcarError(formLogin.email, 'Ese email no parece válido. Ejemplo: nombre@correo.com');
        }
        if (password === '') {
            return marcarError(formLogin.password, 'Te falta la contraseña.');
        }

        state.decirFreya('¡Todo correcto! Entrando…');
        const datos = await enviarApi('login.php', formLogin);

        if (!datos.ok) {
            return state.decirFreya(datos.error);
        }
        window.location.href = datos.tiene_pareja ? 'app.php' : 'vincular.php';
    });
}

// --- Registro ---
const formRegistro = document.querySelector('#form-registro');
if (formRegistro) {
    formRegistro.addEventListener('submit', (e) => {
        e.preventDefault();
        limpiarErrores(formRegistro);

        const nombre = formRegistro.nombre.value.trim();
        const email = formRegistro.email.value.trim();
        const password = formRegistro.password.value;
        const repetir = formRegistro.repetir.value;

        if (nombre === '') {
            return marcarError(formRegistro.nombre, 'Dime cómo te llamas.');
        }
        if (!esEmailValido(email)) {
            return marcarError(formRegistro.email, 'Ese email no parece válido. Ejemplo: nombre@correo.com');
        }
        if (password.length < 4) {
            return marcarError(formRegistro.password, 'La contraseña es muy corta: mínimo 4 caracteres.');
        }
        if (!/[A-Z]/.test(password)) {
            return marcarError(formRegistro.password, 'A la contraseña le falta al menos 1 mayúscula.');
        }
        if (repetir !== password) {
            return marcarError(formRegistro.repetir, 'Las contraseñas no coinciden.');
        }

        state.decirFreya('¡Todo correcto! Creando tu cuenta…');
        // TODO(Sprint 3): POST /api/register.php (cuando el BE confirme los campos)
    });
}

// --- Vincular pareja ---
const formVincular = document.querySelector('#form-vincular');
if (formVincular) {
    formVincular.addEventListener('submit', (e) => {
        e.preventDefault();
        limpiarErrores(formVincular);

        const codigo = formVincular.codigo.value.trim();

        // 3 letras + 4 dígitos (TODO: confirmar con BE si las letras van siempre en mayúscula)
        if (!/^[A-Za-z]{3}[0-9]{4}$/.test(codigo)) {
            return marcarError(formVincular.codigo, 'El código son 3 letras y 4 números, por ejemplo ABC1234.');
        }

        state.decirFreya('¡Código correcto! Vinculando…');
        // TODO(Sprint 3): POST /api/pareja_vincular.php (cuando tengamos API.md)
    });
}