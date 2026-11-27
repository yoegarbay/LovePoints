<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administración · LovePoints</title>
    <link rel="icon" href="assets/img/freya-cara.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body data-pagina="admin">
    <header class="cabecera">
        <a href="admin.php" class="logo">
            <img src="assets/img/logo-nombre.svg" alt="LovePoints">
        </a>
        <div class="cabecera-usuario">
            <span class="texto-suave">Administración</span>
            <button type="button" class="boton boton-secundario boton-pequeno" data-salir>Salir</button>
        </div>
    </header>

    <main id="admin" class="contenedor">

        <!-- ===== USUARIOS ===== -->
        <section class="seccion tarjeta" aria-labelledby="titulo-usuarios">
            <div class="seccion-cabecera">
                <h1 id="titulo-usuarios">Usuarios</h1>
            </div>

            <details class="plegable" id="plegable-usuario">
                <summary id="titulo-form-usuario">Añadir usuario</summary>
                <form id="form-usuario" class="form-columnas" novalidate>
                    <input type="hidden" name="id" id="usuario-id">
                    <div class="campo">
                        <label for="usuario-nombre">Nombre</label>
                        <input type="text" id="usuario-nombre" name="nombre"
                               data-ayuda="Nombre de la persona.">
                    </div>
                    <div class="campo">
                        <label for="usuario-email">Email</label>
                        <input type="email" id="usuario-email" name="email"
                               data-ayuda="Email con el que entrará.">
                    </div>
                    <div class="campo">
                        <label for="usuario-password">Contraseña <span class="texto-suave" id="usuario-password-nota"></span></label>
                        <input type="password" id="usuario-password" name="password" autocomplete="new-password"
                               data-ayuda="Mínimo 4 caracteres y al menos 1 mayúscula. Al editar, déjala vacía para no cambiarla.">
                    </div>
                    <div class="campo">
                        <label for="usuario-rol">Rol</label>
                        <select id="usuario-rol" name="rol" data-ayuda="«Usuario» para las parejas; «Superadmin» solo para administrar.">
                            <option value="usuario">Usuario</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="botones campo-ancho">
                        <button type="submit" class="boton boton-principal">Guardar</button>
                        <button type="button" class="boton boton-secundario oculto" id="usuario-cancelar">Cancelar edición</button>
                    </div>
                </form>
            </details>

            <div class="tabla-scroll">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Email</th>
                            <th scope="col">Código</th>
                            <th scope="col">Rol</th>
                            <th scope="col">Pareja</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="acciones"><span class="solo-lector">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody id="tabla-usuarios">
                        <tr><td colspan="7" class="vacio">Cargando usuarios…</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== PAREJAS ===== -->
        <section class="seccion tarjeta" aria-labelledby="titulo-parejas">
            <h2 id="titulo-parejas">Crear o corregir una pareja</h2>
            <form id="form-pareja" class="form-columnas" novalidate>
                <div class="campo">
                    <label for="pareja-1">Persona 1</label>
                    <select id="pareja-1" name="usuario_1_id" data-usuarios
                            data-ayuda="Elige a las dos personas que forman la pareja.">
                        <option value="">— Elige —</option>
                    </select>
                </div>
                <div class="campo">
                    <label for="pareja-2">Persona 2</label>
                    <select id="pareja-2" name="usuario_2_id" data-usuarios
                            data-ayuda="Tiene que ser una persona distinta de la primera.">
                        <option value="">— Elige —</option>
                    </select>
                </div>
                <div class="botones campo-ancho">
                    <button type="submit" class="boton boton-principal">Guardar pareja</button>
                </div>
            </form>
        </section>
    </main>

    <!-- Freya (asistente) -->
    <aside id="freya" aria-live="polite">
        <button type="button" class="freya-ayuda" aria-label="Ayuda de Freya">
            <img class="freya-robot" src="assets/img/freya-cara.svg" alt="">
        </button>
        <p class="freya-burbuja oculto"></p>
    </aside>
</body>
</html>
