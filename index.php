<?php
/**=======================================================
 * index.php  ·  Landing de LovePoints
 * -------------------------------------------------------
 * Propósito : Página de presentación. Explica qué es la app
 *             y lleva a crear cuenta o iniciar sesión.
 * Estilos   : css/style.css (base) + css/landing.css (solo landing)
 * Enlaces   : cambia aquí las rutas si tus páginas se llaman distinto.
 * =======================================================*/

$titulo       = 'LovePoints · Llevad juntos la cuenta de vuestros LovePoints';
$descripcion  = 'Registrad los eventos de pareja, mirad los saldos de los dos y descubrid en qué gasta cada uno. Los LovePoints son una unidad interna, no dinero real.';

$url_login    = 'login.php';
$url_registro = 'registro.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titulo) ?></title>
  <meta name="description" content="<?= htmlspecialchars($descripcion) ?>">
  <meta name="theme-color" content="#7254D1">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/landing.css">
</head>
<body>

  <a class="saltar" href="#contenido">Saltar al contenido</a>

  <!-- ===== CABECERA ===== -->
  <header class="cabecera">
    <a class="logo" href="index.php" aria-label="LovePoints, inicio">
      <span class="logo-icono" aria-hidden="true">♥</span> LovePoints
    </a>

    <nav class="nav" aria-label="Principal">
      <a class="nav-enlace" href="#como-funciona">Cómo funciona</a>
      <a class="nav-enlace" href="#preguntas">Preguntas</a>
      <a class="boton boton-secundario" href="<?= $url_login ?>">Iniciar sesión</a>
      <a class="boton boton-principal" href="<?= $url_registro ?>">Crear cuenta</a>
    </nav>
  </header>

  <main id="contenido">

    <!-- ===== HERO ===== -->
    <section class="hero">
      <div class="contenedor hero-grid">

        <div class="hero-texto">
          <p class="sobretitulo">Para dos</p>
          <h1>Llevad juntos la cuenta de vuestros LovePoints</h1>
          <p class="hero-lead">
            Cada cena, festival o regalo cuenta. Registrad los eventos, mirad los saldos
            de los dos y descubrid en qué gasta cada uno. Los LovePoints son una unidad
            interna: no son dinero real.
          </p>

          <div class="botones">
            <a class="boton boton-principal" href="<?= $url_registro ?>">Crear cuenta</a>
            <a class="boton boton-secundario" href="<?= $url_login ?>">Iniciar sesión</a>
          </div>

          <ul class="ventajas texto-suave">
            <li>Marcador siempre al día</li>
            <li>Gratis</li>
            <li>Historial y gráficas por periodo</li>
          </ul>
        </div>

        <!-- Maqueta de la app (ilustración, no son datos reales) -->
        <div class="maqueta tarjeta" role="img"
             aria-label="Ejemplo de la app: saldos de los dos y últimos eventos">
          <div class="saldos" aria-hidden="true">
            <div class="saldo">
              <span class="texto-suave">Tú</span>
              <div class="saldo-numero">120</div>
              <span class="texto-suave">Este mes: −40</span>
            </div>
            <div class="saldo">
              <span class="texto-suave">Tu pareja</span>
              <div class="saldo-numero">−15</div>
              <span class="texto-suave">Este mes: −90</span>
            </div>
          </div>

          <table class="tabla" aria-hidden="true">
            <thead>
              <tr><th>Evento</th><th>Efecto</th><th>Puntos</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>Cena en el italiano</td>
                <td><span class="etiqueta etiqueta-resta">Resta</span></td>
                <td class="importe-resta">−35</td>
              </tr>
              <tr>
                <td>Recarga mensual</td>
                <td><span class="etiqueta etiqueta-suma">Suma</span></td>
                <td class="importe-suma">+60</td>
              </tr>
              <tr>
                <td>Entradas del festival</td>
                <td><span class="etiqueta etiqueta-resta">Resta</span></td>
                <td class="importe-resta">−55</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </section>

    <!-- ===== CÓMO FUNCIONA ===== -->
    <section class="seccion seccion-blanca" id="como-funciona" aria-labelledby="t-como">
      <div class="contenedor">
        <h2 id="t-como" class="titulo-seccion">Cómo funciona</h2>

        <ol class="pasos">
          <li class="tarjeta paso">
            <span class="paso-numero" aria-hidden="true">1</span>
            <h3>Crea tu cuenta</h3>
            <p class="texto-suave">Regístrate y recibe tu código personal, con 3 letras y 4 números.</p>
          </li>
          <li class="tarjeta paso">
            <span class="paso-numero" aria-hidden="true">2</span>
            <h3>Vincula a tu pareja</h3>
            <p class="texto-suave">Introduce el código de tu pareja para empezar a compartir la cuenta.</p>
          </li>
          <li class="tarjeta paso">
            <span class="paso-numero" aria-hidden="true">3</span>
            <h3>Registra y mira</h3>
            <p class="texto-suave">Anota eventos y recargas, y consulta saldos y gráficas.</p>
          </li>
        </ol>
      </div>
    </section>

    <!-- ===== TODO LO QUE PUEDES HACER ===== -->
    <section class="seccion" aria-labelledby="t-todo">
      <div class="contenedor">
        <h2 id="t-todo" class="titulo-seccion">Todo lo que puedes hacer</h2>

        <div class="funciones-grandes">
          <article class="tarjeta">
            <h3>Tabla de eventos con filtros</h3>
            <p class="texto-suave">Filtra por persona, efecto, fechas o nombre, y ordena como quieras.</p>

            <div class="mini" aria-hidden="true">
              <div class="mini-filtros">
                <span class="etiqueta etiqueta-resta">Resta</span>
                <span class="etiqueta etiqueta-suma">Suma</span>
                <span class="mini-buscar">Buscar por nombre</span>
              </div>
              <div class="tabla-scroll">
                <table class="tabla">
                  <tbody>
                    <tr><td>Cena</td><td class="importe-resta">−35</td></tr>
                    <tr><td>Recarga</td><td class="importe-suma">+60</td></tr>
                    <tr><td>Regalo</td><td class="importe-resta">−20</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </article>

          <article class="tarjeta">
            <h3>Gráficas por mes, trimestre y año</h3>
            <p class="texto-suave">Compara el gasto de los dos con barras por persona.</p>

            <div class="mini" aria-hidden="true">
              <div class="grafica">
                <div class="grafica-grupo"><span class="barra barra-a" style="height:70%"></span><span class="barra barra-b" style="height:45%"></span><small>Oct</small></div>
                <div class="grafica-grupo"><span class="barra barra-a" style="height:40%"></span><span class="barra barra-b" style="height:85%"></span><small>Nov</small></div>
                <div class="grafica-grupo"><span class="barra barra-a" style="height:60%"></span><span class="barra barra-b" style="height:55%"></span><small>Dic</small></div>
              </div>
              <p class="grafica-leyenda texto-suave">
                <span class="punto punto-a"></span> Tú
                <span class="punto punto-b"></span> Tu pareja
              </p>
            </div>
          </article>
        </div>

        <div class="funciones-pequenas">
          <article class="tarjeta">
            <h3>Eventos predefinidos</h3>
            <p class="texto-suave">Tus gastos habituales, a un toque.</p>
          </article>
          <article class="tarjeta">
            <h3>Recargas</h3>
            <p class="texto-suave">Suma puntos a quien toque.</p>
          </article>
          <article class="tarjeta">
            <h3>Anular eventos</h3>
            <p class="texto-suave">Quedan en el historial, pero no cuentan.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- ===== PREGUNTAS FRECUENTES ===== -->
    <section class="seccion seccion-blanca" id="preguntas" aria-labelledby="t-faq">
      <div class="contenedor contenedor-estrecho">
        <h2 id="t-faq" class="titulo-seccion">Preguntas frecuentes</h2>

        <details class="pregunta tarjeta" open>
          <summary>¿Los LovePoints son dinero real?</summary>
          <p>No. Son una unidad interna de la aplicación y no se conectan a ningún pago.</p>
        </details>

        <details class="pregunta tarjeta">
          <summary>¿Cómo sé quién gasta qué?</summary>
          <p>Cada evento es de un solo usuario y puede restar de su saldo o sumar al de su pareja.</p>
        </details>

        <details class="pregunta tarjeta">
          <summary>¿Puedo vincularme con más de una persona?</summary>
          <p>No. Cada usuario solo puede tener una pareja.</p>
        </details>

        <details class="pregunta tarjeta">
          <summary>¿Qué pasa si me equivoco en un evento?</summary>
          <p>Puedes anularlo: se queda en el historial pero deja de contar.</p>
        </details>
      </div>
    </section>

    <!-- ===== LLAMADA FINAL ===== -->
    <section class="seccion cta" aria-labelledby="t-cta">
      <div class="contenedor contenedor-estrecho">
        <h2 id="t-cta" class="titulo-seccion">Empieza hoy a llevar la cuenta</h2>
        <p class="hero-lead">
          Crea tu cuenta, vincula a tu pareja y registra el primer evento en un par de minutos.
        </p>
        <div class="botones botones-centro">
          <a class="boton boton-principal" href="<?= $url_registro ?>">Crear cuenta</a>
          <a class="boton boton-secundario" href="<?= $url_login ?>">Iniciar sesión</a>
        </div>
      </div>
    </section>

  </main>

  <!-- ===== PIE ===== -->
  <footer class="pie">
    <div class="contenedor pie-grid">
      <a class="logo" href="index.php">LovePoints</a>
      <span class="texto-suave">Proyecto académico</span>
    </div>
  </footer>

</body>
</html>
