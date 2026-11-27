/**=======================================================
 * app.js
 * -------------------------------------------------------
 * Propósito : Estado global (LoveState) y lógica de todas las
 *             páginas: login, registro, vincular, app y admin.
 * Sprint    : 1 (stubs) -> 2 (flujo) -> 3, 4 y 5 (lógica)
 * Depende de: nada. charts.js (gráficas) se carga después.
 * Se carga  : en todas las páginas, con defer. Cada página
 *             dice quién es con <body data-pagina="...">.
 * Contrato  : nombres de endpoints y campos en docs/CONTRATO-API.md
 *             (acordado con el BE). Datos: POST como formulario
 *             ($_POST) o GET con parámetros; respuestas en JSON.
 * =======================================================*/


/**=======================================================
 * FLUJO DE ESTADO
 * -------------------------------------------------------
 * 1. Al cargar cualquier página: GET check_login.php
 *      - Sin sesión            -> redirigir a login.php
 *                                 (en login y registro no: ya estás ahí)
 *      - Sesión sin pareja     -> redirigir a vincular.php
 *      - Sesión con pareja     -> continuar en app.php
 *      - Superadmin            -> admin.php
 * 2. state.cargarDatos() rellena el estado:
 *      pareja, saldos, predefinidos, recargas y eventos.
 * 3. Cualquier acción del usuario (filtrar, crear, anular...)
 *      pide los datos al BE, actualiza el estado y repinta
 *      la parte afectada de la interfaz.
 *      - Filtros: los aplica el BE (eventos_list.php recibe
 *        usuario, efecto, desde, hasta, nombre y anulado).
 *        El filtro «mes» se convierte en desde/hasta.
 *      - Ordenación (fecha, coste, persona): la hace el FE.
 * 4. Los saldos y estadísticas NUNCA se calculan en el
 *      front: se piden siempre al BE (saldos.php, stats.php).
 * 5. Freya (asistente): todo mensaje al usuario (errores de
 *      validación, avisos, confirmaciones) sale por ella.
 *      Mensajes recientes: sessionStorage. Nunca datos de
 *      eventos o importes.
 *      - En formularios habla sola al enfocar un campo.
 *      - En el resto de la app, solo si el usuario pulsa «?».
 * =======================================================*/


// ===== LLAMADAS AL BACK-END =====

/** Mensaje si el servidor no responde o no devuelve JSON. */
const ERROR_CONEXION = 'No puedo conectar con el servidor. Inténtalo en un rato.';

/**
 * Pide datos al BE (GET) y devuelve su respuesta JSON.
 * @param {string} endpoint Archivo de /api, p. ej. 'saldos.php'.
 * @param {Object} [parametros] Se añaden a la URL (?clave=valor).
 * @returns {Promise<Object>}
 */
async function pedirApi(endpoint, parametros = {}) {
    try {
        const url = 'api/' + endpoint + '?' + new URLSearchParams(parametros);
        const respuesta = await fetch(url);
        return await respuesta.json();
    } catch (e) {
        return { ok: false, error: ERROR_CONEXION };
    }
}

/**
 * Envía datos al BE (POST, como un formulario normal: llegan en $_POST).
 * @param {string} endpoint Archivo de /api, p. ej. 'login.php'.
 * @param {HTMLFormElement|Object} datos Un formulario (se usan sus name) o un objeto.
 * @returns {Promise<Object>}
 */
async function enviarApi(endpoint, datos = {}) {
    let cuerpo;
    if (datos instanceof HTMLFormElement) {
        cuerpo = new FormData(datos);
    } else {
        cuerpo = new FormData();
        for (const clave in datos) cuerpo.append(clave, datos[clave]);
    }

    try {
        const respuesta = await fetch('api/' + endpoint, { method: 'POST', body: cuerpo });
        return await respuesta.json();
    } catch (e) {
        return { ok: false, error: ERROR_CONEXION };
    }
}


// ===== UTILIDADES =====

/**
 * Evita que un texto del BE se interprete como HTML.
 * @param {*} texto
 * @returns {string}
 */
function escaparHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}

/**
 * Formatea puntos al estilo español (1.234,5) con «pts».
 * Con efecto, añade el signo: + suma, − resta.
 * @param {number|string} numero
 * @param {'suma'|'resta'} [efecto]
 * @returns {string}
 */
function formatearPuntos(numero, efecto) {
    const n = Number(numero) || 0;
    const texto = new Intl.NumberFormat('es-ES', { maximumFractionDigits: 2 }).format(Math.abs(n)) + ' pts';
    if (efecto === 'suma') return '+ ' + texto;
    if (efecto === 'resta') return '− ' + texto;
    return (n < 0 ? '− ' : '') + texto;
}

/**
 * Convierte '2026-11-27' en '27/11/2026'.
 * @param {string} fecha
 * @returns {string}
 */
function formatearFecha(fecha) {
    if (!fecha) return '';
    const [a, m, d] = String(fecha).slice(0, 10).split('-');
    return d + '/' + m + '/' + a;
}

/**
 * Fecha de hoy en formato 'AAAA-MM-DD' (el que usan los <input type="date">).
 * @returns {string}
 */
function hoy() {
    const f = new Date();
    return f.getFullYear() + '-' + String(f.getMonth() + 1).padStart(2, '0') + '-' + String(f.getDate()).padStart(2, '0');
}

/**
 * Comprueba que una fecha tiene formato 'AAAA-MM-DD'.
 * @param {string} fecha
 * @returns {boolean}
 */
function esFechaValida(fecha) {
    return /^\d{4}-\d{2}-\d{2}$/.test(fecha);
}

/**
 * Comprueba si un email tiene formato válido (texto@texto.texto).
 * @param {string} email
 * @returns {boolean}
 */
function esEmailValido(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Comprueba la contraseña según el contrato: mínimo 4 caracteres y 1 mayúscula.
 * @param {string} password
 * @returns {string} Mensaje de error, o '' si está bien.
 */
function errorPassword(password) {
    if (password.length < 4) return 'La contraseña es muy corta: mínimo 4 caracteres.';
    if (!/[A-Z]/.test(password)) return 'A la contraseña le falta al menos 1 mayúscula.';
    return '';
}

/**
 * Cambia de página.
 * @param {string} pagina p. ej. 'app.php'
 */
function irA(pagina) {
    window.location.href = pagina;
}


// ===== CLASE LOVESTATE =====

/**
 * Estado global de LovePoints.
 * Guarda los datos que la interfaz necesita y que vienen del BE.
 * Hay una única instancia: `state`.
 */
class LoveState {

    constructor() {
        /** @type {Object|null} Usuario autenticado (id, nombre, email, codigo, rol). */
        this.usuario = null;

        /** @type {Object|null} La otra persona de la pareja (id, nombre, codigo), o null. */
        this.pareja = null;

        /** @type {Array<Object>} Saldo de cada persona: {usuario_id, nombre, saldo}. Lo calcula el BE. */
        this.saldos = [];

        /** @type {Array<Object>} Eventos que devuelve eventos_list.php con los filtros activos. */
        this.eventos = [];

        /** @type {Array<Object>} Eventos predefinidos del usuario. */
        this.predefinidos = [];

        /** @type {Array<Object>} Recargas de la pareja. */
        this.recargas = [];

        /**
         * Filtros activos de la tabla de eventos (se envían a eventos_list.php).
         * persona: se envía como «usuario».
         * efecto: '' | 'resta' | 'suma'   (valores del contrato con el BE)
         * mes: 'AAAA-MM', se convierte en desde/hasta antes de enviarlo.
         * anulados: true = mostrar también los anulados.
         */
        this.filtros = { persona: '', efecto: '', desde: '', hasta: '', mes: '', nombre: '', anulados: false };

        /** Ordenación de la tabla (la hace el FE): campo 'fecha' | 'coste' | 'persona'. */
        this.orden = { campo: 'fecha', ascendente: false };

        /** @type {'mes'|'trimestre'|'año'} Granularidad de las gráficas. */
        this.granularidad = 'mes';

        /**
         * Últimos mensajes de Freya (máximo 5).
         * Se espejan en sessionStorage para que sobrevivan al cambio de página.
         * @type {Array<string>}
         */
        this.mensajesFreya = [];
        try {
            const guardados = sessionStorage.getItem('mensajesFreya');
            if (guardados) this.mensajesFreya = JSON.parse(guardados);
        } catch (e) { }
    }

    // ----- Consultas -----

    /** @returns {boolean} true si el usuario tiene pareja vinculada. */
    tienePareja() { return this.pareja !== null; }

    /** @returns {Array<Object>} Las dos personas de la pareja: primero el usuario. */
    personas() {
        return [this.usuario, this.pareja].filter(p => p);
    }

    /**
     * Nombre de una persona de la pareja a partir de su id.
     * @param {number|string} id
     * @returns {string}
     */
    nombrePersona(id) {
        const persona = this.personas().find(p => String(p.id) === String(id));
        return persona ? persona.nombre : '';
    }

    /** @returns {Array<Object>} Copia de los eventos, ordenada según this.orden. */
    eventosOrdenados() {
        const { campo, ascendente } = this.orden;
        const lista = [...this.eventos];
        lista.sort((a, b) => {
            let resultado;
            if (campo === 'coste') {
                resultado = Number(a.coste) - Number(b.coste);
            } else if (campo === 'persona') {
                resultado = (a.usuario_nombre || '').localeCompare(b.usuario_nombre || '');
            } else {
                resultado = String(a.fecha).localeCompare(String(b.fecha));
            }
            return ascendente ? resultado : -resultado;
        });
        return lista;
    }

    // ----- Carga de datos (siempre del BE) -----

    /** Carga todo lo que necesita app.php y lo pinta. */
    async cargarDatos() {
        const datos = await pedirApi('pareja_estado.php');
        if (datos.ok === false) return this.decirFreya(datos.error);
        this.pareja = datos.pareja || null;

        rellenarSelectsPersonas();
        await Promise.all([
            this.actualizarSaldos(),
            this.filtrarEventos(),
            this.cargarPredefinidos(),
            this.cargarRecargas(),
            this.renderizarGraficas()
        ]);
    }

    /** Pide los saldos al BE (GET saldos.php) y los pinta. */
    async actualizarSaldos() {
        const datos = await pedirApi('saldos.php');
        if (datos.ok === false) return this.decirFreya(datos.error);
        this.saldos = datos.saldos || [];
        pintarSaldos();
    }

    /** Pide al BE los eventos que cumplen this.filtros (GET eventos_list.php) y los pinta. */
    async filtrarEventos() {
        const f = this.filtros;
        const parametros = {};
        if (f.persona) parametros.usuario = f.persona;
        if (f.efecto) parametros.efecto = f.efecto;
        if (f.desde) parametros.desde = f.desde;
        if (f.hasta) parametros.hasta = f.hasta;
        if (f.mes) {
            // 'AAAA-MM' -> del día 1 al último día del mes
            const [a, m] = f.mes.split('-').map(Number);
            const ultimoDia = new Date(a, m, 0).getDate();
            parametros.desde = f.mes + '-01';
            parametros.hasta = f.mes + '-' + String(ultimoDia).padStart(2, '0');
        }
        if (f.nombre) parametros.nombre = f.nombre;
        if (f.anulados) parametros.anulado = 1;

        const datos = await pedirApi('eventos_list.php', parametros);
        if (datos.ok === false) return this.decirFreya(datos.error);
        this.eventos = datos.eventos || [];
        pintarEventos();
    }

    /** Pide los predefinidos (GET predefinidos_list.php) y los pinta. */
    async cargarPredefinidos() {
        const datos = await pedirApi('predefinidos_list.php');
        if (datos.ok === false) return this.decirFreya(datos.error);
        this.predefinidos = datos.predefinidos || [];
        pintarPredefinidos();
    }

    /** Pide las recargas (GET recargas_list.php) y las pinta. */
    async cargarRecargas() {
        const datos = await pedirApi('recargas_list.php');
        if (datos.ok === false) return this.decirFreya(datos.error);
        this.recargas = datos.recargas || [];
        pintarRecargas();
    }

    /** Pide las estadísticas (GET stats.php) y dibuja la gráfica con charts.js. */
    async renderizarGraficas() {
        const datos = await pedirApi('stats.php', { granularidad: this.granularidad, desde: '', hasta: '' });
        if (datos.ok === false) return this.decirFreya(datos.error);
        if (typeof dibujarGrafica === 'function') dibujarGrafica(datos);
    }

    // ----- Freya -----

    /**
     * Freya dice un mensaje y lo muestra en la burbuja.
     * @param {string} texto Mensaje para el usuario.
     * @param {boolean} [guardar=true] false para ayudas de campos (no se guardan).
     */
    decirFreya(texto, guardar = true) {
        if (!texto) return;
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

    /**
     * Guarda un mensaje para que Freya lo diga en la página siguiente
     * (por ejemplo, después de vincular la pareja).
     * @param {string} texto
     */
    decirFreyaLuego(texto) {
        try {
            sessionStorage.setItem('freyaPendiente', texto);
        } catch (e) { }
    }
}


// ===== INSTANCIA GLOBAL =====
const state = new LoveState();

/** Página actual, según <body data-pagina="..."> */
const pagina = document.body.dataset.pagina;


// ===== FORMULARIOS: AYUDAS DE VALIDACIÓN =====

/**
 * Marca un campo con error y Freya explica qué pasa.
 * @param {HTMLElement} input Campo con el error (input, select o un radio).
 * @param {string} mensaje Explicación para el usuario.
 */
function marcarError(input, mensaje) {
    const caja = input.closest('.campo, .opciones');
    if (caja) caja.classList.add('error');
    input.focus();               // primero el foco (dispara la ayuda)...
    state.decirFreya(mensaje);   // ...y después el error, que la sustituye
}

/**
 * Quita las marcas de error de un formulario.
 * @param {HTMLFormElement} form
 */
function limpiarErrores(form) {
    form.querySelectorAll('.error').forEach(c => c.classList.remove('error'));
}

/**
 * Devuelve el valor del radio marcado de un grupo, o ''.
 * @param {HTMLFormElement} form
 * @param {string} nombre name del grupo
 * @returns {string}
 */
function valorRadio(form, nombre) {
    const marcado = form.querySelector('input[name="' + nombre + '"]:checked');
    return marcado ? marcado.value : '';
}

/**
 * Marca el radio de un grupo que tiene ese valor.
 * @param {HTMLFormElement} form
 * @param {string} nombre
 * @param {string} valor
 */
function marcarRadio(form, nombre, valor) {
    form.querySelectorAll('input[name="' + nombre + '"]').forEach(r => {
        r.checked = (r.value === valor);
    });
}

// Freya habla al enfocar cualquier campo con data-ayuda
document.querySelectorAll('[data-ayuda]').forEach(input => {
    input.addEventListener('focus', () => state.decirFreya(input.dataset.ayuda, false));
});

// Botón «Ver»: enseña u oculta la contraseña
document.querySelectorAll('.boton-ver').forEach(boton => {
    boton.addEventListener('click', () => {
        const input = boton.previousElementSibling; // el <input> de al lado
        const mostrar = input.type === 'password';
        input.type = mostrar ? 'text' : 'password';
        boton.setAttribute('aria-pressed', mostrar);
    });
});

// Botón «?» de Freya: ayuda de la página
const AYUDAS = {
    app: 'Arriba ves los puntos de cada uno. En «Eventos» puedes añadir, filtrar y ordenar (pulsa Fecha, Persona o Puntos). Los predefinidos rellenan el evento por ti.',
    admin: 'Aquí puedes dar de alta, editar o dar de baja usuarios, y crear o corregir parejas.'
};
const botonAyuda = document.querySelector('.freya-ayuda');
if (botonAyuda) {
    botonAyuda.addEventListener('click', () => {
        const burbuja = document.querySelector('.freya-burbuja');
        const ayuda = AYUDAS[pagina] || '¿En qué te ayudo?';
        // Si ya está diciendo la ayuda, el «?» la calla; si no, la dice
        if (!burbuja.classList.contains('oculto') && burbuja.textContent === ayuda) {
            burbuja.classList.add('oculto');
        } else {
            state.decirFreya(ayuda, false);
        }
    });
}

// Botón «Salir» (cabecera)
document.querySelectorAll('[data-salir]').forEach(boton => {
    boton.addEventListener('click', async () => {
        await enviarApi('logout.php');
        try { sessionStorage.clear(); } catch (e) { }
        irA('login.php');
    });
});


// ===== SESIÓN Y REDIRECCIÓN =====

/**
 * Página a la que debe ir un usuario con sesión.
 * @param {Object} sesion Respuesta de check_login.php
 * @returns {string}
 */
function destinoSegunSesion(sesion) {
    if (sesion.usuario && sesion.usuario.rol === 'superadmin') return 'admin.php';
    return sesion.tiene_pareja ? 'app.php' : 'vincular.php';
}

/**
 * Comprueba la sesión (GET check_login.php) y redirige según la página.
 * @returns {Promise<boolean>} true si el usuario puede quedarse en esta página.
 */
async function comprobarSesion() {
    const sesion = await pedirApi('check_login.php');

    // Si el BE no responde, no se redirige (evita bucles); Freya avisa.
    if (sesion.ok === false && sesion.sesion === undefined) {
        state.decirFreya(sesion.error);
        return false;
    }

    state.usuario = sesion.usuario || null;
    const destino = destinoSegunSesion(sesion);

    if (pagina === 'login' || pagina === 'registro') {
        if (sesion.sesion) irA(destino);
        return !sesion.sesion;
    }
    if (!sesion.sesion) {
        irA('login.php');
        return false;
    }
    if (pagina === 'admin') {
        if (destino !== 'admin.php') { irA(destino); return false; }
        return true;
    }
    if (destino !== pagina + '.php') {
        irA(destino);
        return false;
    }
    return true;
}


// ===== LOGIN =====

const formLogin = document.querySelector('#form-login');
if (formLogin) {
    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault(); // no recargar la página
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

        // check_login dice adónde ir (app, vincular o admin)
        const sesion = await pedirApi('check_login.php');
        irA(destinoSegunSesion(sesion));
    });
}


// ===== REGISTRO =====

const formRegistro = document.querySelector('#form-registro');
if (formRegistro) {
    formRegistro.addEventListener('submit', async (e) => {
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
        const fallo = errorPassword(password);
        if (fallo) {
            return marcarError(formRegistro.password, fallo);
        }
        if (repetir !== password) {
            return marcarError(formRegistro.repetir, 'Las contraseñas no coinciden.');
        }

        state.decirFreya('¡Todo correcto! Creando tu cuenta…');
        const datos = await enviarApi('register.php', formRegistro);
        if (!datos.ok) {
            return state.decirFreya(datos.error);
        }

        // register.php deja la sesión iniciada (acordado con el BE)
        state.decirFreyaLuego('¡Cuenta creada! Ahora vincula a tu pareja con su código.');
        irA('vincular.php');
    });
}


// ===== VINCULAR PAREJA =====

/** Muestra el código del usuario (GET mi_codigo.php). */
async function cargarMiCodigo() {
    const datos = await pedirApi('mi_codigo.php');
    if (datos.ok === false) return state.decirFreya(datos.error);
    document.querySelector('#mi-codigo').textContent = datos.codigo;
}

const formVincular = document.querySelector('#form-vincular');
if (formVincular) {
    formVincular.addEventListener('submit', async (e) => {
        e.preventDefault();
        limpiarErrores(formVincular);

        // El código se envía en mayúsculas (así lo genera el BE)
        const codigo = formVincular.codigo.value.trim().toUpperCase();
        formVincular.codigo.value = codigo;

        if (!/^[A-Z]{3}[0-9]{4}$/.test(codigo)) {
            return marcarError(formVincular.codigo, 'El código son 3 letras y 4 números, por ejemplo ABC1234.');
        }

        state.decirFreya('¡Código correcto! Vinculando…');
        const datos = await enviarApi('pareja_vincular.php', formVincular);
        if (!datos.ok) {
            return state.decirFreya(datos.error);
        }

        state.decirFreyaLuego('¡Ya estáis vinculados! Empieza añadiendo un evento.');
        irA('app.php');
    });
}


// ===== APP: PINTAR DATOS =====

/** Rellena los <select data-persona> con las dos personas de la pareja. */
function rellenarSelectsPersonas() {
    document.querySelectorAll('select[data-persona]').forEach(select => {
        const primera = select.options[0]; // «Todas» o «— Elige —»
        select.innerHTML = '';
        select.append(primera);
        state.personas().forEach(p => {
            const esYo = state.usuario && String(p.id) === String(state.usuario.id);
            select.append(new Option(p.nombre + (esYo ? ' (tú)' : ''), p.id));
        });
    });
}

/** Pinta las tarjetas de saldo (primero el usuario). */
function pintarSaldos() {
    const caja = document.querySelector('#saldos');
    if (!caja) return;

    const lista = [...state.saldos].sort((a, b) =>
        (state.usuario && String(a.usuario_id) === String(state.usuario.id)) ? -1 : 1);

    if (lista.length === 0) {
        caja.innerHTML = '<p class="vacio">Todavía no hay saldos.</p>';
        return;
    }
    caja.innerHTML = lista.map(s => `
        <div class="saldo">
            <p class="texto-suave">${escaparHtml(s.nombre)}</p>
            <p class="saldo-numero">${formatearPuntos(s.saldo)}</p>
        </div>`).join('');
}

/** Pinta la tabla de eventos (ya ordenada). */
function pintarEventos() {
    const tbody = document.querySelector('#tabla-eventos');
    if (!tbody) return;

    const lista = state.eventosOrdenados();
    if (lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="vacio">No hay eventos. ¡Añade el primero!</td></tr>';
        return;
    }

    tbody.innerHTML = lista.map(ev => {
        const anulado = Number(ev.anulado) === 1;
        const persona = ev.usuario_nombre || state.nombrePersona(ev.usuario_id);
        const acciones = anulado
            ? '<span class="texto-suave">Anulado</span>'
            : `<button type="button" class="boton boton-secundario boton-pequeno" data-accion="editar" data-id="${ev.id}">Editar</button>
               <button type="button" class="boton boton-peligro boton-pequeno" data-accion="anular" data-id="${ev.id}">Anular</button>`;
        return `
        <tr class="${anulado ? 'anulado' : ''}">
            <td>${formatearFecha(ev.fecha)}</td>
            <td>${escaparHtml(ev.nombre)}</td>
            <td>${escaparHtml(persona)}</td>
            <td><span class="etiqueta etiqueta-${ev.efecto}">${ev.efecto === 'suma' ? '+ Suma' : '− Resta'}</span></td>
            <td class="numero importe-${ev.efecto}">${formatearPuntos(ev.coste, ev.efecto)}</td>
            <td class="acciones">${acciones}</td>
        </tr>`;
    }).join('');
}

/** Pinta la tabla de predefinidos y rellena el selector del formulario de evento. */
function pintarPredefinidos() {
    const tbody = document.querySelector('#tabla-predefinidos');
    if (tbody) {
        if (state.predefinidos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="vacio">Sin predefinidos. Crea los eventos que más se repiten.</td></tr>';
        } else {
            tbody.innerHTML = state.predefinidos.map(p => `
            <tr>
                <td>${escaparHtml(p.nombre)}</td>
                <td><span class="etiqueta etiqueta-${p.efecto}">${p.efecto === 'suma' ? '+ Suma' : '− Resta'}</span></td>
                <td class="numero importe-${p.efecto}">${formatearPuntos(p.coste, p.efecto)}</td>
                <td class="acciones">
                    <button type="button" class="boton boton-secundario boton-pequeno" data-accion="editar" data-id="${p.id}">Editar</button>
                    <button type="button" class="boton boton-peligro boton-pequeno" data-accion="borrar" data-id="${p.id}">Borrar</button>
                </td>
            </tr>`).join('');
        }
    }

    const select = document.querySelector('#evento-predefinido');
    if (select) {
        select.innerHTML = '<option value="">— Ninguno —</option>';
        state.predefinidos.forEach(p => {
            select.append(new Option(p.nombre + ' (' + formatearPuntos(p.coste, p.efecto) + ')', p.id));
        });
    }
}

/** Pinta la tabla de recargas. */
function pintarRecargas() {
    const tbody = document.querySelector('#tabla-recargas');
    if (!tbody) return;

    if (state.recargas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="vacio">No hay recargas.</td></tr>';
        return;
    }
    tbody.innerHTML = state.recargas.map(r => `
        <tr>
            <td>${formatearFecha(r.fecha)}</td>
            <td>${escaparHtml(r.usuario_nombre || state.nombrePersona(r.usuario_id))}</td>
            <td>${escaparHtml(r.motivo)}</td>
            <td class="numero importe-suma">${formatearPuntos(r.importe, 'suma')}</td>
            <td class="acciones">
                <button type="button" class="boton boton-peligro boton-pequeno" data-accion="borrar" data-id="${r.id}">Borrar</button>
            </td>
        </tr>`).join('');
}

/** Marca en la cabecera de la tabla por qué columna se ordena (aria-sort). */
function pintarOrden() {
    document.querySelectorAll('.ordenar').forEach(boton => {
        const th = boton.closest('th');
        const activo = boton.dataset.orden === state.orden.campo;
        th.setAttribute('aria-sort', activo ? (state.orden.ascendente ? 'ascending' : 'descending') : 'none');
        const flecha = activo ? (state.orden.ascendente ? ' ▲' : ' ▼') : '';
        boton.textContent = boton.textContent.replace(/ [▲▼]$/, '') + flecha;
    });
}


// ===== APP: EVENTOS =====

const formEvento = document.querySelector('#form-evento');
if (formEvento) {
    const campoIdEvento = document.querySelector('#evento-id'); // (formEvento.id sería el id del <form>)
    const plegable = document.querySelector('#plegable-evento');
    const titulo = document.querySelector('#titulo-form-evento');
    const cancelar = document.querySelector('#evento-cancelar');

    /** Deja el formulario listo para un evento nuevo. */
    const formularioNuevo = () => {
        formEvento.reset();
        limpiarErrores(formEvento);
        campoIdEvento.value = '';
        formEvento.fecha.value = hoy();
        titulo.textContent = 'Añadir evento';
        cancelar.classList.add('oculto');
    };
    formularioNuevo();
    cancelar.addEventListener('click', formularioNuevo);

    // Elegir un predefinido rellena nombre, coste y efecto
    formEvento.predefinido_id.addEventListener('change', () => {
        const p = state.predefinidos.find(x => String(x.id) === formEvento.predefinido_id.value);
        if (!p) return;
        formEvento.nombre.value = p.nombre;
        formEvento.coste.value = p.coste;
        marcarRadio(formEvento, 'efecto', p.efecto);
    });

    formEvento.addEventListener('submit', async (e) => {
        e.preventDefault();
        limpiarErrores(formEvento);

        if (formEvento.nombre.value.trim() === '') {
            return marcarError(formEvento.nombre, 'Ponle un nombre al evento.');
        }
        if (!(Number(formEvento.coste.value) > 0)) {
            return marcarError(formEvento.coste, 'Los puntos tienen que ser un número mayor que 0.');
        }
        if (!esFechaValida(formEvento.fecha.value)) {
            return marcarError(formEvento.fecha, 'Elige una fecha válida.');
        }
        if (formEvento.usuario_id.value === '') {
            return marcarError(formEvento.usuario_id, '¿A quién le aplico los puntos? Elige una persona.');
        }
        if (valorRadio(formEvento, 'efecto') === '') {
            return marcarError(formEvento.querySelector('input[name="efecto"]'), 'Elige si el evento resta o suma.');
        }

        const editando = campoIdEvento.value !== '';
        const datos = await enviarApi(editando ? 'evento_update.php' : 'evento_create.php', formEvento);
        if (!datos.ok) return state.decirFreya(datos.error);

        state.decirFreya(editando ? 'Evento actualizado.' : '¡Evento guardado!');
        formularioNuevo();
        plegable.open = false;
        await Promise.all([state.filtrarEventos(), state.actualizarSaldos(), state.renderizarGraficas()]);
    });

    // Botones de cada fila: editar y anular
    document.querySelector('#tabla-eventos').addEventListener('click', async (e) => {
        const boton = e.target.closest('button[data-accion]');
        if (!boton) return;
        const evento = state.eventos.find(ev => String(ev.id) === boton.dataset.id);
        if (!evento) return;

        if (boton.dataset.accion === 'editar') {
            limpiarErrores(formEvento);
            campoIdEvento.value = evento.id;
            formEvento.predefinido_id.value = evento.predefinido_id || '';
            formEvento.nombre.value = evento.nombre;
            formEvento.coste.value = evento.coste;
            formEvento.fecha.value = String(evento.fecha).slice(0, 10);
            formEvento.usuario_id.value = evento.usuario_id;
            marcarRadio(formEvento, 'efecto', evento.efecto);
            titulo.textContent = 'Editar evento';
            cancelar.classList.remove('oculto');
            plegable.open = true;
            formEvento.nombre.focus();
            return;
        }

        if (boton.dataset.accion === 'anular') {
            if (!confirm('¿Anular «' + evento.nombre + '»? Seguirá en el historial, pero no contará.')) return;
            const datos = await enviarApi('evento_anular.php', { id: evento.id });
            if (!datos.ok) return state.decirFreya(datos.error);
            state.decirFreya('Evento anulado.');
            await Promise.all([state.filtrarEventos(), state.actualizarSaldos(), state.renderizarGraficas()]);
        }
    });
}

// Filtros: cada cambio vuelve a pedir los eventos al BE
const formFiltros = document.querySelector('#form-filtros');
if (formFiltros) {
    const leerFiltros = () => {
        state.filtros = {
            persona: formFiltros.persona.value,
            efecto: formFiltros.efecto.value,
            desde: formFiltros.desde.value,
            hasta: formFiltros.hasta.value,
            mes: formFiltros.mes.value,
            nombre: formFiltros.nombre.value.trim(),
            anulados: formFiltros.anulados.checked
        };
        state.filtrarEventos();
    };

    formFiltros.addEventListener('change', leerFiltros);
    formFiltros.addEventListener('submit', (e) => { e.preventDefault(); leerFiltros(); });

    // Buscar por nombre: espera a que el usuario deje de escribir
    let espera;
    formFiltros.nombre.addEventListener('input', () => {
        clearTimeout(espera);
        espera = setTimeout(leerFiltros, 400);
    });

    // «Quitar filtros»: el reset vacía los campos justo después del evento
    formFiltros.addEventListener('reset', () => setTimeout(leerFiltros, 0));
}

// Ordenar la tabla (lo hace el FE, sin pedir nada al BE)
document.querySelectorAll('.ordenar').forEach(boton => {
    boton.addEventListener('click', () => {
        const campo = boton.dataset.orden;
        if (state.orden.campo === campo) {
            state.orden.ascendente = !state.orden.ascendente;
        } else {
            state.orden = { campo, ascendente: campo === 'persona' };
        }
        pintarOrden();
        pintarEventos();
    });
});


// ===== APP: RECARGAS =====

const formRecarga = document.querySelector('#form-recarga');
if (formRecarga) {
    formRecarga.fecha.value = hoy();

    formRecarga.addEventListener('submit', async (e) => {
        e.preventDefault();
        limpiarErrores(formRecarga);

        if (formRecarga.usuario_id.value === '') {
            return marcarError(formRecarga.usuario_id, '¿A quién le recargo? Elige una persona.');
        }
        if (!(Number(formRecarga.importe.value) > 0)) {
            return marcarError(formRecarga.importe, 'Los puntos tienen que ser un número mayor que 0.');
        }
        if (!esFechaValida(formRecarga.fecha.value)) {
            return marcarError(formRecarga.fecha, 'Elige una fecha válida.');
        }

        const datos = await enviarApi('recarga_create.php', formRecarga);
        if (!datos.ok) return state.decirFreya(datos.error);

        state.decirFreya('¡Recarga guardada!');
        formRecarga.reset();
        formRecarga.fecha.value = hoy();
        formRecarga.closest('details').open = false;
        await Promise.all([state.cargarRecargas(), state.actualizarSaldos(), state.renderizarGraficas()]);
    });

    document.querySelector('#tabla-recargas').addEventListener('click', async (e) => {
        const boton = e.target.closest('button[data-accion="borrar"]');
        if (!boton) return;
        if (!confirm('¿Borrar esta recarga?')) return;

        const datos = await enviarApi('recarga_delete.php', { id: boton.dataset.id });
        if (!datos.ok) return state.decirFreya(datos.error);
        state.decirFreya('Recarga borrada.');
        await Promise.all([state.cargarRecargas(), state.actualizarSaldos(), state.renderizarGraficas()]);
    });
}


// ===== APP: PREDEFINIDOS =====

const formPredefinido = document.querySelector('#form-predefinido');
if (formPredefinido) {
    const campoIdPredefinido = document.querySelector('#predefinido-id');
    const plegable = document.querySelector('#plegable-predefinido');
    const titulo = document.querySelector('#titulo-form-predefinido');
    const cancelar = document.querySelector('#predefinido-cancelar');

    const formularioNuevo = () => {
        formPredefinido.reset();
        limpiarErrores(formPredefinido);
        campoIdPredefinido.value = '';
        titulo.textContent = 'Añadir predefinido';
        cancelar.classList.add('oculto');
    };
    cancelar.addEventListener('click', formularioNuevo);

    formPredefinido.addEventListener('submit', async (e) => {
        e.preventDefault();
        limpiarErrores(formPredefinido);

        if (formPredefinido.nombre.value.trim() === '') {
            return marcarError(formPredefinido.nombre, 'Ponle un nombre al predefinido.');
        }
        if (!(Number(formPredefinido.coste.value) > 0)) {
            return marcarError(formPredefinido.coste, 'Los puntos tienen que ser un número mayor que 0.');
        }
        if (valorRadio(formPredefinido, 'efecto') === '') {
            return marcarError(formPredefinido.querySelector('input[name="efecto"]'), 'Elige si resta o suma.');
        }

        const editando = campoIdPredefinido.value !== '';
        const datos = await enviarApi(editando ? 'predefinido_update.php' : 'predefinido_create.php', formPredefinido);
        if (!datos.ok) return state.decirFreya(datos.error);

        state.decirFreya(editando ? 'Predefinido actualizado.' : '¡Predefinido guardado!');
        formularioNuevo();
        plegable.open = false;
        await state.cargarPredefinidos();
    });

    document.querySelector('#tabla-predefinidos').addEventListener('click', async (e) => {
        const boton = e.target.closest('button[data-accion]');
        if (!boton) return;
        const p = state.predefinidos.find(x => String(x.id) === boton.dataset.id);
        if (!p) return;

        if (boton.dataset.accion === 'editar') {
            limpiarErrores(formPredefinido);
            campoIdPredefinido.value = p.id;
            formPredefinido.nombre.value = p.nombre;
            formPredefinido.coste.value = p.coste;
            marcarRadio(formPredefinido, 'efecto', p.efecto);
            titulo.textContent = 'Editar predefinido';
            cancelar.classList.remove('oculto');
            plegable.open = true;
            formPredefinido.nombre.focus();
            return;
        }

        if (boton.dataset.accion === 'borrar') {
            if (!confirm('¿Borrar el predefinido «' + p.nombre + '»?')) return;
            const datos = await enviarApi('predefinido_delete.php', { id: p.id });
            if (!datos.ok) return state.decirFreya(datos.error);
            state.decirFreya('Predefinido borrado.');
            await state.cargarPredefinidos();
        }
    });
}

// Gráficas: cambiar mes / trimestre / año
const selectGranularidad = document.querySelector('#granularidad');
if (selectGranularidad) {
    selectGranularidad.addEventListener('change', () => {
        state.granularidad = selectGranularidad.value;
        state.renderizarGraficas();
    });
}


// ===== ADMIN (solo superadmin) =====

/** @type {Array<Object>} Usuarios de admin_usuarios_list.php */
let usuariosAdmin = [];

/** Pide la lista de usuarios y la pinta. */
async function cargarUsuariosAdmin() {
    const datos = await pedirApi('admin_usuarios_list.php');
    if (datos.ok === false) return state.decirFreya(datos.error);
    usuariosAdmin = datos.usuarios || [];
    pintarUsuariosAdmin();
}

/** Pinta la tabla de usuarios y rellena los selectores de pareja. */
function pintarUsuariosAdmin() {
    const tbody = document.querySelector('#tabla-usuarios');
    if (usuariosAdmin.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="vacio">No hay usuarios.</td></tr>';
    } else {
        tbody.innerHTML = usuariosAdmin.map(u => {
            const activo = Number(u.activo) === 1;
            return `
            <tr class="${activo ? '' : 'anulado'}">
                <td>${escaparHtml(u.nombre)}</td>
                <td>${escaparHtml(u.email)}</td>
                <td>${escaparHtml(u.codigo)}</td>
                <td>${u.rol === 'superadmin' ? 'Superadmin' : 'Usuario'}</td>
                <td>${escaparHtml(u.pareja_nombre) || '<span class="texto-suave">—</span>'}</td>
                <td>${activo ? 'Activo' : 'De baja'}</td>
                <td class="acciones">
                    <button type="button" class="boton boton-secundario boton-pequeno" data-accion="editar" data-id="${u.id}">Editar</button>
                    ${activo ? `<button type="button" class="boton boton-peligro boton-pequeno" data-accion="baja" data-id="${u.id}">Dar de baja</button>` : ''}
                </td>
            </tr>`;
        }).join('');
    }

    // Para las parejas, solo usuarios activos con rol «usuario»
    document.querySelectorAll('select[data-usuarios]').forEach(select => {
        const elegido = select.value;
        select.innerHTML = '<option value="">— Elige —</option>';
        usuariosAdmin
            .filter(u => Number(u.activo) === 1 && u.rol !== 'superadmin')
            .forEach(u => select.append(new Option(u.nombre + ' (' + u.codigo + ')', u.id)));
        select.value = elegido;
    });
}

const formUsuario = document.querySelector('#form-usuario');
if (formUsuario) {
    const campoIdUsuario = document.querySelector('#usuario-id');
    const plegable = document.querySelector('#plegable-usuario');
    const titulo = document.querySelector('#titulo-form-usuario');
    const cancelar = document.querySelector('#usuario-cancelar');
    const nota = document.querySelector('#usuario-password-nota');

    const formularioNuevo = () => {
        formUsuario.reset();
        limpiarErrores(formUsuario);
        campoIdUsuario.value = '';
        titulo.textContent = 'Añadir usuario';
        nota.textContent = '';
        cancelar.classList.add('oculto');
    };
    cancelar.addEventListener('click', formularioNuevo);

    formUsuario.addEventListener('submit', async (e) => {
        e.preventDefault();
        limpiarErrores(formUsuario);

        const editando = campoIdUsuario.value !== '';
        const password = formUsuario.password.value;

        if (formUsuario.nombre.value.trim() === '') {
            return marcarError(formUsuario.nombre, 'Falta el nombre.');
        }
        if (!esEmailValido(formUsuario.email.value.trim())) {
            return marcarError(formUsuario.email, 'Ese email no parece válido. Ejemplo: nombre@correo.com');
        }
        // Al crear, la contraseña es obligatoria; al editar, solo si se escribe
        if (!editando || password !== '') {
            const fallo = errorPassword(password);
            if (fallo) return marcarError(formUsuario.password, fallo);
        }

        const datos = await enviarApi(editando ? 'admin_usuario_update.php' : 'admin_usuario_create.php', formUsuario);
        if (!datos.ok) return state.decirFreya(datos.error);

        state.decirFreya(editando ? 'Usuario actualizado.' : '¡Usuario creado!');
        formularioNuevo();
        plegable.open = false;
        await cargarUsuariosAdmin();
    });

    document.querySelector('#tabla-usuarios').addEventListener('click', async (e) => {
        const boton = e.target.closest('button[data-accion]');
        if (!boton) return;
        const u = usuariosAdmin.find(x => String(x.id) === boton.dataset.id);
        if (!u) return;

        if (boton.dataset.accion === 'editar') {
            limpiarErrores(formUsuario);
            campoIdUsuario.value = u.id;
            formUsuario.nombre.value = u.nombre;
            formUsuario.email.value = u.email;
            formUsuario.password.value = '';
            formUsuario.rol.value = u.rol;
            nota.textContent = '(déjala vacía para no cambiarla)';
            titulo.textContent = 'Editar usuario';
            cancelar.classList.remove('oculto');
            plegable.open = true;
            formUsuario.nombre.focus();
            return;
        }

        if (boton.dataset.accion === 'baja') {
            if (!confirm('¿Dar de baja a ' + u.nombre + '? No podrá entrar, pero sus datos se guardan.')) return;
            const datos = await enviarApi('admin_usuario_baja.php', { id: u.id });
            if (!datos.ok) return state.decirFreya(datos.error);
            state.decirFreya(u.nombre + ' está de baja.');
            await cargarUsuariosAdmin();
        }
    });
}

const formPareja = document.querySelector('#form-pareja');
if (formPareja) {
    formPareja.addEventListener('submit', async (e) => {
        e.preventDefault();
        limpiarErrores(formPareja);

        const uno = formPareja.usuario_1_id.value;
        const dos = formPareja.usuario_2_id.value;
        if (uno === '') return marcarError(formPareja.usuario_1_id, 'Elige a la primera persona.');
        if (dos === '') return marcarError(formPareja.usuario_2_id, 'Elige a la segunda persona.');
        if (uno === dos) return marcarError(formPareja.usuario_2_id, 'Tienen que ser dos personas distintas.');

        const datos = await enviarApi('admin_pareja_set.php', formPareja);
        if (!datos.ok) return state.decirFreya(datos.error);

        state.decirFreya('¡Pareja guardada!');
        formPareja.reset();
        await cargarUsuariosAdmin();
    });
}


// ===== ARRANQUE DE CADA PÁGINA =====

(async () => {
    // Mensaje que dejó la página anterior (p. ej. «¡Cuenta creada!»)
    try {
        const pendiente = sessionStorage.getItem('freyaPendiente');
        if (pendiente) {
            sessionStorage.removeItem('freyaPendiente');
            state.decirFreya(pendiente);
        }
    } catch (e) { }

    const puedeQuedarse = await comprobarSesion();
    if (!puedeQuedarse) return;

    if (pagina === 'vincular') {
        await cargarMiCodigo();
    }
    if (pagina === 'app') {
        document.querySelector('#nombre-usuario').textContent = state.usuario ? 'Hola, ' + state.usuario.nombre : '';
        pintarOrden();
        await state.cargarDatos();
    }
    if (pagina === 'admin') {
        await cargarUsuariosAdmin();
    }
})();
