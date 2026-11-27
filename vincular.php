<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vincular pareja · LovePoints</title>
    <link rel="icon" href="assets/img/freya-cara.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body data-pagina="vincular">
    <header class="cabecera">
        <a href="index.php" class="logo">
            <img src="assets/img/logo-nombre.svg" alt="LovePoints">
        </a>
        <button type="button" class="boton boton-secundario boton-pequeno" data-salir>Salir</button>
    </header>

    <main id="vincular" class="contenedor">
        <div class="tarjeta tarjeta-acceso">
            <h1>Vincula a tu pareja</h1>

            <p class="texto-suave">Tu código es:</p>
            <p class="codigo" id="mi-codigo">···</p>
            <p>Pásaselo a tu pareja, o escribe aquí el suyo:</p>

            <form id="form-vincular" novalidate>
                <div class="campo">
                    <label for="codigo">Código de tu pareja</label>
                    <input type="text" id="codigo" name="codigo" maxlength="7"
                           autocomplete="off" autocapitalize="characters" placeholder="ABC1234"
                           data-ayuda="Son 3 letras y 4 números, por ejemplo ABC1234. Te lo da tu pareja.">
                </div>
                <button type="submit" class="boton boton-principal">Vincular</button>
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
