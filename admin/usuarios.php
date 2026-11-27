<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'superadmin') {
    header('Location: ../login.php');
    exit;
}

$db = Database::connect();
$usuarios = $db->query(
    "SELECT id, nombre, email, codigo, rol, saldo_inicial, activo, created_at
     FROM usuarios ORDER BY id"
)->fetchAll();

$parejas = $db->query(
    "SELECT p.id, p.usuario_1_id, p.usuario_2_id,
            u1.nombre AS nombre1, u2.nombre AS nombre2
     FROM parejas p
     JOIN usuarios u1 ON u1.id = p.usuario_1_id
     JOIN usuarios u2 ON u2.id = p.usuario_2_id"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Usuarios · FamilyCoinsApp</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Panel de administración</h1>
        <nav>
            <a href="../app.php">Volver a la app</a>
            <button id="btn-logout">Cerrar sesión</button>
        </nav>
    </header>

    <main>
        <section>
            <h2>Usuarios (<?= count($usuarios) ?>)</h2>
            <table id="tabla-usuarios">
                <thead>
                    <tr>
                        <th>ID</th><th>Nombre</th><th>Email</th>
                        <th>Código</th><th>Rol</th><th>Saldo inicial</th>
                        <th>Activo</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr data-id="<?= $u['id'] ?>">
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['nombre']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><code><?= htmlspecialchars($u['codigo']) ?></code></td>
                        <td><?= htmlspecialchars($u['rol']) ?></td>
                        <td><?= $u['saldo_inicial'] !== null ? number_format((float)$u['saldo_inicial'], 2) : '—' ?></td>
                        <td><?= $u['activo'] ? 'Sí' : 'No' ?></td>
                        <td>
                            <button class="btn-editar" data-id="<?= $u['id'] ?>">Editar</button>
                            <?php if ($u['activo'] && $u['rol'] !== 'superadmin'): ?>
                                <button class="btn-baja" data-id="<?= $u['id'] ?>">Dar de baja</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section>
            <h2>Parejas (<?= count($parejas) ?>)</h2>
            <table id="tabla-parejas">
                <thead>
                    <tr><th>ID</th><th>Usuario 1</th><th>Usuario 2</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php foreach ($parejas as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['nombre1']) ?></td>
                        <td><?= htmlspecialchars($p['nombre2']) ?></td>
                        <td>
                            <button class="btn-eliminar-pareja"
                                    data-u1="<?= $p['usuario_1_id'] ?>"
                                    data-u2="<?= $p['usuario_2_id'] ?>">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Asignar / corregir pareja</h3>
            <form id="form-pareja">
                <input type="number" name="usuario_1_id" placeholder="ID usuario 1" required>
                <input type="number" name="usuario_2_id" placeholder="ID usuario 2" required>
                <button type="submit">Guardar pareja</button>
            </form>
        </section>
    </main>

    <script src="../assets/js/admin.js"></script>
</body>
</html>