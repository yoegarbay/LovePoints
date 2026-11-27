<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/pareja.php';

$accion = strtolower(trim($_GET['accion'] ?? ''));
$in = get_input();

require_superadmin(); 

switch ($accion) {

    case 'usuarios_list':
        require_method('GET');
        $m = new User();
        json_response(['ok'=>true, 'items'=>$m->listAll()]);
        break;

    case 'usuario_get':
        require_method('GET');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) json_error('ID no válido');
        $m = new User();
        $u = $m->getById($id);
        if (!$u) json_error('Usuario no encontrado', 404);
        unset($u['password_hash']);
        json_response(['ok'=>true, 'item'=>$u]);
        break;

    case 'usuario_create':
        require_method('POST');
        $nombre = trim($in['nombre'] ?? '');
        $email = trim($in['email'] ?? '');
        $password = $in['password'] ?? '';
        if ($nombre === '') json_error('El nombre es obligatorio');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Email no válido');
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password))
            json_error('La contraseña debe tener al menos 8 caracteres y 1 mayúscula');
        $m = new User();
        if ($m->getByEmail($email)) json_error('Ya existe una cuenta con ese email', 409);
        $nuevo = $m->create($nombre, $email, $password);
        $extra = [];
        if (!empty($in['rol'])) $extra['rol'] = $in['rol'];
        if (array_key_exists('saldo_inicial', $in)) $extra['saldo_inicial'] = $in['saldo_inicial'];
        if ($extra) $m->update($nuevo['id'], $extra);
        json_response(['ok'=>true, 'id'=>$nuevo['id'], 'codigo'=>$nuevo['codigo']], 201);
        break;

    case 'usuario_update':
        require_method('POST');
        $id = (int)($in['id'] ?? 0);
        if ($id <= 0) json_error('ID no válido');
        $m = new User();
        if (!$m->getById($id)) json_error('Usuario no encontrado', 404);
        $datos = [];
        foreach (['nombre','email','rol','activo','saldo_inicial'] as $c) {
            if (array_key_exists($c, $in)) $datos[$c] = $in[$c];
        }
        if (isset($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL))
            json_error('Email no válido');
        if (isset($datos['rol']) && !in_array($datos['rol'], ['usuario','superadmin'], true))
            json_error('Rol no válido');
        $m->update($id, $datos);
        json_response(['ok'=>true]);
        break;

    case 'usuario_baja':
        require_method('POST');
        $id = (int)($in['id'] ?? 0);
        if ($id <= 0) json_error('ID no válido');
        $m = new User();
        $u = $m->getById($id);
        if (!$u) json_error('Usuario no encontrado', 404);
        if ($u['rol'] === 'superadmin') json_error('No puedes dar de baja a un superadmin', 403);
        $m->bajaLogica($id);
        json_response(['ok'=>true]);
        break;

    case 'pareja_set':
        require_method('POST');
        $u1 = (int)($in['usuario_1_id'] ?? 0);
        $u2 = (int)($in['usuario_2_id'] ?? 0);
        if ($u1 <= 0 || $u2 <= 0) json_error('Debes indicar dos usuarios');
        if ($u1 === $u2) json_error('No puede ser el mismo usuario');
        $user = new User();
        if (!$user->getById($u1) || !$user->getById($u2))
            json_error('Algún usuario no existe', 404);
        $p = new Pareja();
        $p->eliminarPorUsuario($u1);
        $p->eliminarPorUsuario($u2);
        $p->crear($u1, $u2);
        json_response(['ok'=>true]);
        break;

    default:
        json_error('Acción no válida (usuarios_list|usuario_get|usuario_create|usuario_update|usuario_baja|pareja_set)', 400);
}