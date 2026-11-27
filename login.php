<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · LovePoints</title>
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

    <!-- TODO(Sprint 3): formulario de login con validación en cliente -->
    <main id="login">
        <div class="tarjeta tarjeta-acceso">
            <nav class="pestanas" aria-label="Acceso">
                <a href="login.php" class="pestana" aria-current="page">Iniciar sesión</a>
                <a href="registro.php" class="pestana">Crear cuenta</a>
            </nav>
            <form id="form-login" novalidate>
                <div class="campo">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" autocomplete="email"
                        data-ayuda="Escribe el email con el que te registraste.">
                </div>
                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" autocomplete="current-password"
                        data-ayuda="Escribe tu contraseña. Ojo con las mayúsculas.">
                </div>
                <button type="submit" class="boton boton-principal">Entrar</button>
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