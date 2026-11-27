<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("INSERT INTO predefinidos (usuario_id, nombre, coste, efecto) VALUES (:uid, :nom, :cos, :efe)");
$stmt->execute([
    'uid' => $_SESSION['usuario_id'], 'nom' => $data['nombre'], 
    'cos' => $data['coste'], 'efe' => $data['efecto']
]);
echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
?>