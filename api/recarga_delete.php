<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("DELETE FROM recargas WHERE id = :id AND usuario_id = :uid");
$stmt->execute(['id' => $data['id'], 'uid' => $_SESSION['usuario_id']]);
echo json_encode(["success" => true, "message" => "Recarga eliminada"]);
?>