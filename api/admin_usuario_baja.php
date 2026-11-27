<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id'])) { http_response_code(400); echo json_encode(["error" => "Falta el ID"]); exit; }

$stmt = $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id = :id");
$stmt->execute(['id' => $data['id']]);
echo json_encode(["success" => true, "message" => "Usuario dado de baja"]);
?>