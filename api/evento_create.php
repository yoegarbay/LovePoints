<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
$data = json_decode(file_get_contents("php://input"), true);

$registrado_por = $_SESSION['usuario_id'] ?? $data['registrado_por_id']; 

$stmt = $pdo->prepare("INSERT INTO eventos (usuario_id, registrado_por_id, predefinido_id, nombre, fecha, coste, efecto) VALUES (:uid, :rpid, :pid, :nom, :fec, :cos, :efe)");
$stmt->execute([
    'uid' => $data['usuario_id'], 'rpid' => $registrado_por, 
    'pid' => $data['predefinido_id'] ?? null, 'nom' => $data['nombre'],
    'fec' => $data['fecha'], 'cos' => $data['coste'], 'efe' => $data['efecto']
]);
echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
?>