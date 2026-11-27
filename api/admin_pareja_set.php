<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['usuario1_id'], $data['usuario2_id'])) { http_response_code(400); echo json_encode(["error" => "Faltan IDs"]); exit; }

try {
    $del = $pdo->prepare("DELETE FROM parejas WHERE usuario_1_id IN (:u1, :u2) OR usuario_2_id IN (:u1, :u2)");
    $del->execute(['u1' => $data['usuario1_id'], 'u2' => $data['usuario2_id']]);

    $stmt = $pdo->prepare("INSERT INTO parejas (usuario_1_id, usuario_2_id) VALUES (:u1, :u2)");
    $stmt->execute(['u1' => $data['usuario1_id'], 'u2' => $data['usuario2_id']]);
    echo json_encode(["success" => true, "message" => "Pareja asignada"]);
} catch (PDOException $e) {
    http_response_code(500); echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>