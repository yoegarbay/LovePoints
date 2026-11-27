<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
if (!isset($_SESSION['usuario_id'])) { http_response_code(401); echo json_encode(["error" => "No autorizado"]); exit; }

$uid = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT p.id as pareja_id, p.usuario_1_id, p.usuario_2_id, u1.nombre as nombre1, u2.nombre as nombre2 
                       FROM parejas p
                       JOIN usuarios u1 ON p.usuario_1_id = u1.id
                       JOIN usuarios u2 ON p.usuario_2_id = u2.id
                       WHERE p.usuario_1_id = :uid OR p.usuario_2_id = :uid");
$stmt->execute(['uid' => $uid]);
$pareja = $stmt->fetch(PDO::FETCH_ASSOC);

if ($pareja) echo json_encode(["tiene_pareja" => true, "datos" => $pareja]);
else echo json_encode(["tiene_pareja" => false, "datos" => null]);
?>