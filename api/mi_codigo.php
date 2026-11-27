<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["ok" => false, "error" => "Método no permitido"]); exit; }
if (!isset($_SESSION['usuario_id'])) { http_response_code(401); echo json_encode(["ok" => false, "error" => "No autorizado"]); exit; }

$stmt = $pdo->prepare("SELECT codigo FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(["codigo" => $user['codigo'] ?? null]);
?>