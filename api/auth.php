<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/pareja.php';

$accion = strtolower(trim($_GET['accion'] ?? ''));
$in = get_input();

switch ($accion) {

    case 'register':
        require_method('POST');
        $nombre   = trim($in['nombre'] ?? '');
        $email    = trim($in['email'] ?? '');
        $password = $in['password'] ?? '';
        if ($nombre === '') json_error('El nombre es obligatorio');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('El email no es válido');
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password))
            json_error('La contraseña debe tener al menos 8 caracteres y 1 mayúscula');
        $user = new User();
        try {
            if ($user->getByEmail($email)) json_error('Ya existe una cuenta con ese email', 409);
            $nuevo = $user->create($nombre, $email, $password);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_error('No se pudo crear la cuenta', 500);
        }
        json_response(['ok'=>true, 'id'=>$nuevo['id'], 'codigo'=>$nuevo['codigo']], 201);
        break;

    case 'login':
        require_method('POST');
        $email = trim($in['email'] ?? '');
        $password = $in['password'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Email no válido');
        if ($password === '') json_error('La contraseña es obligatoria');
        $user = new User();
        $u = $user->getByEmail($email);
        if (!$u || !$u['activo'] || !$user->verifyPassword($u, $password))
            json_error('Credenciales incorrectas', 401);
        $_SESSION['usuario_id'] = (int)$u['id'];
        $_SESSION['rol'] = $u['rol'];
        $_SESSION['nombre'] = $u['nombre'];
        json_response([
            'ok' => true,
            'id' => (int)$u['id'],
            'nombre' => $u['nombre'],
            'rol' => $u['rol'],
            'codigo' => $u['codigo'],
            'tiene_pareja' => get_pareja((int)$u['id']) !== null,
        ]);
        break;

    case 'logout':
        require_method('POST');
        $_SESSION = [];
        session_destroy();
        json_response(['ok' => true]);
        break;

    case 'check':
        require_method('GET');
        if (empty($_SESSION['usuario_id'])) {
            json_response(['logueado'=>false, 'usuario'=>null, 'tiene_pareja'=>false]);
        }
        json_response([
            'logueado' => true,
            'usuario' => [
                'id' => (int)$_SESSION['usuario_id'],
                'rol' => $_SESSION['rol'] ?? '',
                'nombre' => $_SESSION['nombre'] ?? '',
            ],
            'tiene_pareja' => get_pareja((int)$_SESSION['usuario_id']) !== null,
        ]);
        break;

    case 'mi_codigo':
        require_method('GET');
        $id = require_login();
        $user = new User();
        $u = $user->getById($id);
        if (!$u) json_error('Usuario no encontrado', 404);
        json_response(['ok'=>true, 'codigo'=>$u['codigo']]);
        break;

    case 'vincular':
        require_method('POST');
        $yo = require_login();
        if (get_pareja($yo)) json_error('Ya tienes pareja vinculada', 409);
        $codigo = validar_codigo(trim($in['codigo'] ?? ''));
        $user = new User();
        $pareja = $user->getByCodigo($codigo);
        if (!$pareja) json_error('El código no corresponde a ningún usuario', 404);
        if ((int)$pareja['id'] === $yo) json_error('No puedes vincularte contigo mismo');
        if (get_pareja((int)$pareja['id'])) json_error('Ese usuario ya tiene pareja', 409);
        $p = new Pareja();
        $p->crear($yo, (int)$pareja['id']);
        json_response([
            'ok' => true,
            'pareja' => ['id'=>(int)$pareja['id'], 'nombre'=>$pareja['nombre']],
        ]);
        break;

    case 'pareja':
        require_method('GET');
        $yo = require_login();
        $pareja = get_pareja($yo);
        if (!$pareja) json_response(['ok'=>true, 'pareja'=>null]);
        $otroId = ((int)$pareja['usuario_1_id'] === $yo)
            ? (int)$pareja['usuario_2_id'] : (int)$pareja['usuario_1_id'];
        $user = new User();
        $u = $user->getById($otroId);
        if (!$u) json_error('Pareja no encontrada', 404);
        json_response([
            'ok' => true,
            'pareja' => [
                'id' => (int)$u['id'],
                'nombre' => $u['nombre'],
                'codigo' => $u['codigo'],
                'saldo_inicial' => $u['saldo_inicial'],
            ],
        ]);
        break;

    default:
        json_error('Acción no válida (register|login|logout|check|mi_codigo|vincular|pareja)', 400);
}