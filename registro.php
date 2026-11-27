<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta · LovePoints</title>
    <link rel="icon" href="assets/img/freya-cara.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>

<body>
    <header class="cabecera">
        <a href="index.php" class="logo">
            <img src="assets/img/logo-nombre.svg" alt="LovePoints">
        </a>
    </header>

    <!-- TODO(Sprint 3): formulario de registro con validación en cliente -->
    <main id="registro" class="contenedor">
        <div class="tarjeta tarjeta-acceso">
            <nav class="pestanas" aria-label="Acceso">
                <a href="login.php" class="pestana">Iniciar sesión</a>
                <a href="registro.php" class="pestana" aria-current="page">Crear cuenta</a>
            </nav>
            <form id="form-registro" novalidate>
                <div class="campo">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" autocomplete="given-name"
                        data-ayuda="¿Cómo te llamas? Es el nombre que verá tu pareja.">
                </div>
                <div class="campo">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" autocomplete="email"
                        data-ayuda="Tu email. Lo usarás para entrar.">
                </div>
                <div class="campo">
                    <label for="password">Contraseña</label>
                    <div class="campo-con-boton">
                        <input type="password" id="password" name="password" autocomplete="new-password"
                            data-ayuda="Mínimo 4 caracteres y al menos 1 mayúscula.">
                        <button type="button" class="boton-ver" aria-pressed="false">Ver</button>
                    </div>
                </div>
                <div class="campo">
                    <label for="repetir">Repite la contraseña</label>
                    <input type="password" id="repetir" name="repetir" autocomplete="new-password"
                        data-ayuda="Escríbela otra vez para comprobar que no hay errores.">
                </div>
                <div class="campo">
                    <!-- TODO(Sprint 3): confirmar con BE si register.php recibe saldo_inicial -->
                    <label for="saldo">Saldo inicial <span class="texto-suave">(opcional)</span></label>
                    <input type="number" id="saldo" name="saldo" inputmode="decimal" step="0.01"
                        placeholder="Déjalo vacío para empezar en 0"
                        data-ayuda="Si ya llevabais cuentas, pon tu saldo actual. Si no, déjalo vacío.">
                </div>
                <button type="submit" class="boton boton-principal">Crear cuenta</button>
            </form>
        </div>
    </main>

    <!-- Freya (asistente): avisos y ayuda de los formularios -->
    <aside id="freya" aria-live="polite">
        <img class="freya-robot" src="assets/img/freya-cara.svg" alt="">
        <p class="freya-burbuja oculto"></p>
    </aside>
</body>

</html>