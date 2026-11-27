<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LovePoints</title>
    <link rel="icon" href="assets/img/freya-cara.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Chart.js (cdnjs) antes de app.js; charts.js después -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js" defer></script>
    <script src="assets/js/app.js" defer></script>
    <script src="assets/js/charts.js" defer></script>
</head>
<body data-pagina="app">
    <header class="cabecera">
        <a href="app.php" class="logo">
            <img src="assets/img/logo-nombre.svg" alt="LovePoints">
        </a>
        <div class="cabecera-usuario">
            <span id="nombre-usuario"></span>
            <button type="button" class="boton boton-secundario boton-pequeno" data-salir>Salir</button>
        </div>
    </header>

    <main id="app" class="contenedor">

        <!-- ===== SALDOS (los calcula el BE) ===== -->
        <section class="seccion" aria-labelledby="titulo-saldos">
            <h2 id="titulo-saldos" class="solo-lector">Saldos</h2>
            <div class="saldos" id="saldos">
                <p class="vacio">Cargando saldos…</p>
            </div>
        </section>

        <!-- ===== EVENTOS ===== -->
        <section class="seccion tarjeta" aria-labelledby="titulo-eventos">
            <div class="seccion-cabecera">
                <h2 id="titulo-eventos">Eventos</h2>
            </div>

            <details class="plegable" id="plegable-evento">
                <summary id="titulo-form-evento">Añadir evento</summary>
                <form id="form-evento" class="form-columnas" novalidate>
                    <input type="hidden" name="id" id="evento-id">

                    <div class="campo campo-ancho">
                        <label for="evento-predefinido">Predefinido <span class="texto-suave">(opcional)</span></label>
                        <select id="evento-predefinido" name="predefinido_id"
                                data-ayuda="Elige uno y se rellenan solos el nombre, los puntos y el efecto.">
                            <option value="">— Ninguno —</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="evento-nombre">Nombre</label>
                        <input type="text" id="evento-nombre" name="nombre"
                               data-ayuda="¿Qué ha pasado? Por ejemplo: «Fregar los platos».">
                    </div>
                    <div class="campo">
                        <label for="evento-coste">Puntos</label>
                        <input type="number" id="evento-coste" name="coste" inputmode="decimal" step="0.01" min="0"
                               data-ayuda="Cuántos puntos vale. Tiene que ser mayor que 0.">
                    </div>
                    <div class="campo">
                        <label for="evento-fecha">Fecha</label>
                        <input type="date" id="evento-fecha" name="fecha"
                               data-ayuda="Cuándo pasó. Por defecto, hoy.">
                    </div>
                    <div class="campo">
                        <label for="evento-usuario">Persona</label>
                        <select id="evento-usuario" name="usuario_id" data-persona
                                data-ayuda="¿A quién le suma o le resta los puntos?">
                            <option value="">— Elige —</option>
                        </select>
                    </div>
                    <fieldset class="opciones campo-ancho" id="evento-efecto">
                        <legend class="texto-suave">Efecto</legend>
                        <div class="opciones-lista">
                            <label class="opcion">
                                <input type="radio" name="efecto" value="resta">
                                <span class="opcion-resta">− Resta</span>
                            </label>
                            <label class="opcion">
                                <input type="radio" name="efecto" value="suma">
                                <span class="opcion-suma">+ Suma</span>
                            </label>
                        </div>
                    </fieldset>
                    <div class="botones campo-ancho">
                        <button type="submit" class="boton boton-principal">Guardar</button>
                        <button type="button" class="boton boton-secundario oculto" id="evento-cancelar">Cancelar edición</button>
                    </div>
                </form>
            </details>

            <!-- Filtros: los aplica el BE (eventos_list.php) -->
            <details class="plegable">
                <summary>Filtrar</summary>
                <form id="form-filtros" class="filtros" novalidate>
                    <div class="campo">
                        <label for="filtro-persona">Persona</label>
                        <select id="filtro-persona" name="persona" data-persona>
                            <option value="">Todas</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="filtro-efecto">Efecto</label>
                        <select id="filtro-efecto" name="efecto">
                            <option value="">Todos</option>
                            <option value="resta">− Resta</option>
                            <option value="suma">+ Suma</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="filtro-desde">Desde</label>
                        <input type="date" id="filtro-desde" name="desde">
                    </div>
                    <div class="campo">
                        <label for="filtro-hasta">Hasta</label>
                        <input type="date" id="filtro-hasta" name="hasta">
                    </div>
                    <div class="campo">
                        <label for="filtro-mes">Mes</label>
                        <input type="month" id="filtro-mes" name="mes">
                    </div>
                    <div class="campo">
                        <label for="filtro-nombre">Buscar</label>
                        <input type="search" id="filtro-nombre" name="nombre" placeholder="Nombre del evento">
                    </div>
                    <div class="campo campo-casilla">
                        <label><input type="checkbox" name="anulados"> Ver anulados</label>
                    </div>
                    <div class="campo botones">
                        <button type="reset" class="boton boton-secundario boton-pequeno">Quitar filtros</button>
                    </div>
                </form>
            </details>

            <div class="tabla-scroll">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th scope="col"><button type="button" class="ordenar" data-orden="fecha">Fecha</button></th>
                            <th scope="col">Evento</th>
                            <th scope="col"><button type="button" class="ordenar" data-orden="persona">Persona</button></th>
                            <th scope="col">Efecto</th>
                            <th scope="col" class="numero"><button type="button" class="ordenar" data-orden="coste">Puntos</button></th>
                            <th scope="col" class="acciones"><span class="solo-lector">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody id="tabla-eventos">
                        <tr><td colspan="6" class="vacio">Cargando eventos…</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== GRÁFICAS (datos de stats.php) ===== -->
        <section class="seccion tarjeta" aria-labelledby="titulo-graficas">
            <div class="seccion-cabecera">
                <h2 id="titulo-graficas">Gráficas</h2>
                <label for="granularidad" class="solo-lector">Periodo</label>
                <select id="granularidad" class="boton boton-secundario boton-pequeno">
                    <option value="mes">Por mes</option>
                    <option value="trimestre">Por trimestre</option>
                    <option value="año">Por año</option>
                </select>
            </div>
            <div class="grafica">
                <canvas id="grafica-gasto" aria-label="Gráfica de puntos netos por persona y periodo" role="img"></canvas>
            </div>
            <p class="vacio oculto" id="grafica-vacia">Todavía no hay datos para la gráfica.</p>
        </section>

        <!-- ===== RECARGAS ===== -->
        <section class="seccion tarjeta" aria-labelledby="titulo-recargas">
            <div class="seccion-cabecera">
                <h2 id="titulo-recargas">Recargas</h2>
            </div>

            <details class="plegable">
                <summary>Añadir recarga</summary>
                <form id="form-recarga" class="form-columnas" novalidate>
                    <div class="campo">
                        <label for="recarga-usuario">Persona</label>
                        <select id="recarga-usuario" name="usuario_id" data-persona
                                data-ayuda="¿A quién le recargas puntos?">
                            <option value="">— Elige —</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="recarga-importe">Puntos</label>
                        <input type="number" id="recarga-importe" name="importe" inputmode="decimal" step="0.01" min="0"
                               data-ayuda="Cuántos puntos se suman. Tiene que ser mayor que 0.">
                    </div>
                    <div class="campo">
                        <label for="recarga-fecha">Fecha</label>
                        <input type="date" id="recarga-fecha" name="fecha"
                               data-ayuda="Cuándo se hace la recarga. Por defecto, hoy.">
                    </div>
                    <div class="campo">
                        <label for="recarga-motivo">Motivo <span class="texto-suave">(opcional)</span></label>
                        <input type="text" id="recarga-motivo" name="motivo"
                               data-ayuda="Por qué se recarga. Por ejemplo: «Inicio de mes».">
                    </div>
                    <div class="botones campo-ancho">
                        <button type="submit" class="boton boton-principal">Guardar recarga</button>
                    </div>
                </form>
            </details>

            <div class="tabla-scroll">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Persona</th>
                            <th scope="col">Motivo</th>
                            <th scope="col" class="numero">Puntos</th>
                            <th scope="col" class="acciones"><span class="solo-lector">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody id="tabla-recargas">
                        <tr><td colspan="5" class="vacio">Cargando recargas…</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== PREDEFINIDOS ===== -->
        <section class="seccion tarjeta" aria-labelledby="titulo-predefinidos">
            <div class="seccion-cabecera">
                <h2 id="titulo-predefinidos">Predefinidos</h2>
            </div>

            <details class="plegable" id="plegable-predefinido">
                <summary id="titulo-form-predefinido">Añadir predefinido</summary>
                <form id="form-predefinido" class="form-columnas" novalidate>
                    <input type="hidden" name="id" id="predefinido-id">
                    <div class="campo">
                        <label for="predefinido-nombre">Nombre</label>
                        <input type="text" id="predefinido-nombre" name="nombre"
                               data-ayuda="Un evento que se repite a menudo. Por ejemplo: «Sacar la basura».">
                    </div>
                    <div class="campo">
                        <label for="predefinido-coste">Puntos</label>
                        <input type="number" id="predefinido-coste" name="coste" inputmode="decimal" step="0.01" min="0"
                               data-ayuda="Cuántos puntos vale. Tiene que ser mayor que 0.">
                    </div>
                    <fieldset class="opciones campo-ancho" id="predefinido-efecto">
                        <legend class="texto-suave">Efecto</legend>
                        <div class="opciones-lista">
                            <label class="opcion">
                                <input type="radio" name="efecto" value="resta">
                                <span class="opcion-resta">− Resta</span>
                            </label>
                            <label class="opcion">
                                <input type="radio" name="efecto" value="suma">
                                <span class="opcion-suma">+ Suma</span>
                            </label>
                        </div>
                    </fieldset>
                    <div class="botones campo-ancho">
                        <button type="submit" class="boton boton-principal">Guardar</button>
                        <button type="button" class="boton boton-secundario oculto" id="predefinido-cancelar">Cancelar edición</button>
                    </div>
                </form>
            </details>

            <div class="tabla-scroll">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Efecto</th>
                            <th scope="col" class="numero">Puntos</th>
                            <th scope="col" class="acciones"><span class="solo-lector">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody id="tabla-predefinidos">
                        <tr><td colspan="4" class="vacio">Cargando predefinidos…</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Freya (asistente): en la app solo habla al pulsar «?» (o al enfocar un campo) -->
    <aside id="freya" aria-live="polite">
        <button type="button" class="freya-ayuda" aria-label="Ayuda de Freya">
            <img class="freya-robot" src="assets/img/freya-cara.svg" alt="">
        </button>
        <p class="freya-burbuja oculto"></p>
    </aside>
</body>
</html>
