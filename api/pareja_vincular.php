<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["ok" => false, "error" => "Método no permitido"]); exit; }
if (!isset($_SESSION['usuario_id'])) { http_response_code(401); echo json_encode(["ok" => false, "error" => "No autorizado"]); exit; }

// Recibimos por $_POST
$codigo_pareja = $_POST['codigo'] ?? '';
$mi_id = $_SESSION['usuario_id'];

if (empty($codigo_pareja)) { http_response_code(400); echo json_encode(["ok" => false, "error" => "Falta el código"]); exit; }

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE codigo = :codigo");
$stmt->execute(['codigo' => $codigo_pareja]);
$pareja = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pareja) { http_response_code(404); echo json_encode(["ok" => false, "error" => "Código no válido"]); exit; }
if ($pareja['id'] == $mi_id) { http_response_code(400); echo json_encode(["ok" => false, "error" => "No puedes vincularte a ti mismo"]); exit; }

try {
    $del = $pdo->prepare("DELETE FROM parejas WHERE usuario_1_id IN (:u1, :u2) OR usuario_2_id IN (:u1, :u2)");
    $del->execute(['u1' => $mi_id, 'u2' => $pareja['id']]);

    $ins = $pdo->prepare("INSERT INTO parejas (usuario_1_id, usuario_2_id) VALUES (:u1, :u2)");
    $ins->execute(['u1' => $mi_id, 'u2' => $pareja['id']]);
    
    echo json_encode(["ok" => true]);
} catch (Exception $e) {
    http_response_code(500); echo json_encode(["ok" => false, "error" => "Error al vincular"]);
}
?>