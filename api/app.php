<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../models/predefinido.php';
require_once __DIR__ . '/../models/evento.php';
require_once __DIR__ . '/../models/recarga.php';
require_once __DIR__ . '/../models/user.php';

$accion = strtolower(trim($_GET['accion'] ?? ''));
$in = get_input();

switch ($accion) {

    // ─── PREDEFINIDOS ───
    case 'predefinidos_list':
        require_method('GET');
        $yo = require_pareja();
        $m = new Predefinido();
        json_response(['ok'=>true, 'items'=>$m->listByUsuario($yo)]);
        break;

    case 'predefinido_get':
        require_method('GET');
        $yo = require_pareja();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) json_error('ID no válido');
        $m = new Predefinido();
        $p = $m->get($id, $yo);
        if (!$p) json_error('Predefinido no encontrado', 404);
        json_response(['ok'=>true, 'item'=>$p]);
        break;

    case 'predefinido_create':
        require_method('POST');
        $yo = require_pareja();
        $nombre = trim($in['nombre'] ?? '');
        if ($nombre === '') json_error('El nombre es obligatorio');
        $coste = validar_coste($in['coste'] ?? 0);
        $efecto = validar_efecto($in['efecto'] ?? 'resta');
        try {
            $m = new Predefinido();
            $id = $m->create($yo, $nombre, $coste, $efecto);
            json_response(['ok'=>true, 'id'=>$id], 201);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'uq_usuario_nombre'))
                json_error('Ya tienes un predefinido con ese nombre', 409);
            error_log($e->getMessage());
            json_error('Error al crear el predefinido', 500);
        }
        break;

    case 'predefinido_update':
        require_method('POST');
        $yo = require_pareja();
        $id = (int)($in['id'] ?? 0);
        if ($id <= 0) json_error('ID no válido');
        $nombre = trim($in['nombre'] ?? '');
        if ($nombre === '') json_error('El nombre es obligatorio');
        $coste = validar_coste($in['coste'] ?? 0);
        $efecto = validar_efecto($in['efecto'] ?? 'resta');
        $m = new Predefinido();
        if (!$m->get($id, $yo)) json_error('Predefinido no encontrado', 404);
        try {
            $m->update($id, $yo, $nombre, $coste, $efecto);
            json_response(['ok'=>true]);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'uq_usuario_nombre'))
                json_error('Ya tienes un predefinido con ese nombre', 409);
            error_log($e->getMessage());
            json_error('Error al actualizar', 500);
        }
        break;

    case 'predefinido_delete':
        require_method('POST');
        $yo = require_pareja();
        $id = (int)($in['id'] ?? 0);
        if ($id <= 0) json_error('ID no válido');
        $m = new Predefinido();
        if (!$m->get($id, $yo)) json_error('Predefinido no encontrado', 404);
        $m->delete($id, $yo);
        json_response(['ok'=>true]);
        break;

    // ─── EVENTOS ───
    case 'eventos_list':
        require_method('GET');
        $yo = require_pareja();
        $pareja = get_pareja_id($yo);
        $filtros = [
            'pareja'  => [$yo, $pareja],
            'usuario' => (isset($_GET['usuario']) && $_GET['usuario'] !== '') ? (int)$_GET['usuario'] : null,
            'efecto'  => $_GET['efecto'] ?? null,
            'desde'   => $_GET['desde'] ?? null,
            'hasta'   => $_GET['hasta'] ?? null,
            'nombre'  => $_GET['nombre'] ?? null,
            'anulado' => $_GET['anulado'] ?? null,
            'orden'   => $_GET['orden'] ?? 'fecha',
            'dir'     => $_GET['dir'] ?? 'desc',
        ];
        $m = new Evento();
        json_response(['ok'=>true, 'items'=>$m->list($filtros)]);
        break;

    case 'evento_get':
        require_method('GET');
        $yo = require_pareja();
        $pareja = get_pareja_id($yo);
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) json_error('ID no válido');
        $m = new Evento();
        $e = $m->get($id);
        if (!$e) json_error('Evento no encontrado', 404);
        if (!in_array((int)$e['usuario_id'], [$yo, $pareja], true))
            json_error('No autorizado', 403);
        json_response(['ok'=>true, 'item'=>$e]);
        break;

    case 'evento_create':
        require_method('POST');
        $yo = require_pareja();
        $usuarioId = (int)($in['usuario_id'] ?? 0);
        if ($usuarioId <= 0) json_error('Debes indicar el usuario');
        require_miembro_pareja($usuarioId);
        $nombre = trim($in['nombre'] ?? '');
        if ($nombre === '') json_error('El nombre es obligatorio');
        $coste = validar_coste($in['coste'] ?? 0);
        $fecha = validar_fecha((string)($in['fecha'] ?? ''));
        $efecto = validar_efecto($in['efecto'] ?? 'resta');
        $predefinidoId = !empty($in['predefinido_id']) ? (int)$in['predefinido_id'] : null;
        $m = new Evento();
        $id = $m->create($usuarioId, $yo, $predefinidoId, $nombre, $fecha, $coste, $efecto);
        json_response(['ok'=>true, 'id'=>$id], 201);
        break;

    case 'evento_update':
        require_method('POST');
        $yo = require_pareja();
        $pareja = get_pareja_id($yo);
        $id = (int)($in['id'] ?? 0);
        if ($id <= 0) json_error('ID no válido');
        $m = new Evento();
        $e = $m->get($id);
        if (!$e) json_error('Evento no encontrado', 404);
        if (!in_array((int)$e['usuario_id'], [$yo, $pareja], true))
            json_error('No autorizado', 403);
        $usuarioId = (int)($in['usuario_id'] ?? $e['usuario_id']);
        require_miembro_pareja($usuarioId);
        $nombre = trim($in['nombre'] ?? '');
        if ($nombre === '') json_error('El nombre es obligatorio');
        $coste = validar_coste($in['coste'] ?? 0);
        $fecha = validar_fecha((string)($in['fecha'] ?? ''));
        $efecto = validar_efecto($in['efecto'] ?? 'resta');
        $predefinidoId = !empty($in['predefinido_id']) ? (int)$in['predefinido_id'] : null;
        $m->update($id, $usuarioId, $predefinidoId, $nombre, $fecha, $coste, $efecto);
        json_response(['ok'=>true]);
        break;

    case 'evento_anular':
        require_method('POST');
        $yo = require_pareja();
        $pareja = get_pareja_id($yo);
        $id = (int)($in['id'] ?? 0);
        if ($id <= 0) json_error('ID no válido');
        $m = new Evento();
        $e = $m->get($id);
        if (!$e) json_error('Evento no encontrado', 404);
        if (!in_array((int)$e['usuario_id'], [$yo, $pareja], true))
            json_error('No autorizado', 403);
        $m->anular($id, (int)$e['usuario_id']);
        json_response(['ok'=>true]);
        break;

    // ─── RECARGAS ───
    case 'recargas_list':
        require_method('GET');
        $yo = require_pareja();
        $pareja = get_pareja_id($yo);
        $m = new Recarga();
        json_response(['ok'=>true, 'items'=>$m->listByUsuarios([$yo, $pareja])]);
        break;

    case 'recarga_create':
        require_method('POST');
        $yo = require_pareja();
        $usuarioId = (int)($in['usuario_id'] ?? 0);
        if ($usuarioId <= 0) json_error('Debes indicar el usuario');
        require_miembro_pareja($usuarioId);
        $importe = validar_coste($in['importe'] ?? 0);
        $motivo = isset($in['motivo']) ? trim((string)$in['motivo']) : null;
        if ($motivo === '') $motivo = null;
        $m = new Recarga();
        $id = $m->create($usuarioId, $importe, $motivo);
        json_response(['ok'=>true, 'id'=>$id], 201);
        break;

    case 'recarga_delete':
        require_method('POST');
        $yo = require_pareja();
        $pareja = get_pareja_id($yo);
        $id = (int)($in['id'] ?? 0);
        if ($id <= 0) json_error('ID no válido');
        $m = new Recarga();
        if (!$m->delete($id, [$yo, $pareja]))
            json_error('Recarga no encontrada o no autorizada', 404);
        json_response(['ok'=>true]);
        break;

    // ─── SALDOS ───
    case 'saldos':
        require_method('GET');
        $yo = require_pareja();
        $parejaId = get_pareja_id($yo);
        $user = new User();
        $yoUser = $user->getById($yo);
        $parejaUser = $user->getById($parejaId);
        if (!$yoUser || !$parejaUser) json_error('Error al cargar usuarios', 500);
        $evModel = new Evento();
        $eventos = $evModel->list(['pareja'=>[$yo,$parejaId], 'anulado'=>null]);
        $recModel = new Recarga();
        $recargas = $recModel->listByUsuarios([$yo, $parejaId]);
        $calcular = function(int $uid, array $u, array $eventos, array $recargas, ?int $parejaId): float {
            $saldo = $u['saldo_inicial'] !== null ? (float)$u['saldo_inicial'] : 0.0;
            foreach ($recargas as $r) {
                if ((int)$r['usuario_id'] === $uid) $saldo += (float)$r['importe'];
            }
            foreach ($eventos as $e) {
                $saldo += Evento::aplicarEfecto($e, $uid, $parejaId);
            }
            return round($saldo, 2);
        };
        json_response([
            'ok' => true,
            'saldos' => [
                ['usuario_id'=>$yo, 'nombre'=>$yoUser['nombre'],
                 'saldo'=>$calcular($yo, $yoUser, $eventos, $recargas, $parejaId)],
                ['usuario_id'=>$parejaId, 'nombre'=>$parejaUser['nombre'],
                 'saldo'=>$calcular($parejaId, $parejaUser, $eventos, $recargas, $yo)],
            ],
        ]);
        break;

    // ─── STATS ───
    case 'stats':
        require_method('GET');
        $yo = require_pareja();
        $parejaId = get_pareja_id($yo);
        $gran = $_GET['granularidad'] ?? 'mes';
        if (!in_array($gran, ['mes','trimestre','año'], true))
            json_error('Granularidad no válida (mes|trimestre|año)');
        $desde = !empty($_GET['desde']) ? validar_fecha($_GET['desde']) : null;
        $hasta = !empty($_GET['hasta']) ? validar_fecha($_GET['hasta']) : null;
        $filtros = ['pareja'=>[$yo,$parejaId], 'anulado'=>0];
        if ($desde) $filtros['desde'] = $desde;
        if ($hasta) $filtros['hasta'] = $hasta;
        $m = new Evento();
        $eventos = $m->list($filtros);
        $clavePeriodo = function(string $fecha) use ($gran): string {
            $dt = new DateTime($fecha);
            switch ($gran) {
                case 'trimestre':
                    $q = intdiv((int)$dt->format('n') - 1, 3) + 1;
                    return $dt->format('Y').'-Q'.$q;
                case 'año':
                    return $dt->format('Y');
                default:
                    return $dt->format('Y-m');
            }
        };
        $res = [];
        foreach ($eventos as $e) {
            $p = $clavePeriodo($e['fecha']);
            if (!isset($res[$p])) $res[$p] = [$yo=>0.0, $parejaId=>0.0];
            $res[$p][$yo]       += -Evento::aplicarEfecto($e, $yo, $parejaId);
            $res[$p][$parejaId] += -Evento::aplicarEfecto($e, $parejaId, $yo);
        }
        ksort($res);
        $periodos = [];
        foreach ($res as $p => $v) {
            $periodos[] = [
                'periodo' => $p,
                'usuario_1' => ['id'=>$yo, 'gasto'=>round($v[$yo],2)],
                'usuario_2' => ['id'=>$parejaId, 'gasto'=>round($v[$parejaId],2)],
            ];
        }
        json_response(['ok'=>true, 'granularidad'=>$gran, 'periodos'=>$periodos]);
        break;

    default:
        json_error('Acción no válida', 400);
}