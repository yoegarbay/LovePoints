<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
if (!isset($_SESSION['usuario_id'])) { http_response_code(401); echo json_encode(["error" => "No autorizado"]); exit; }

$uid = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT usuario_1_id, usuario_2_id FROM parejas WHERE usuario_1_id = :uid OR usuario_2_id = :uid");
$stmt->execute(['uid' => $uid]);
$pareja = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pareja) { echo json_encode(["error" => "No tienes pareja"]); exit; }
$u2 = ($pareja['usuario_1_id'] == $uid) ? $pareja['usuario_2_id'] : $pareja['usuario_1_id'];

$sql = "SELECT u.id, u.nombre,
        (COALESCE(u.saldo_inicial, 0) 
        + COALESCE((SELECT SUM(importe) FROM recargas WHERE usuario_id = u.id), 0)
        - COALESCE((SELECT SUM(coste) FROM eventos WHERE usuario_id = u.id AND efecto = 'resta' AND anulado = 0), 0)
        + COALESCE((SELECT SUM(coste) FROM eventos WHERE usuario_id = u.id AND efecto = 'suma' AND anulado = 0), 0)
        ) as saldo_neto
        FROM usuarios u WHERE u.id IN (:u1, :u2)";

$stmt = $pdo->prepare($sql);
$stmt->execute(['u1' => $uid, 'u2' => $u2]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>