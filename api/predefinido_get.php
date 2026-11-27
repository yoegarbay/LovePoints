<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM predefinidos WHERE id = :id AND usuario_id = :uid");
$stmt->execute(['id' => $id, 'uid' => $_SESSION['usuario_id']]);
$pre = $stmt->fetch(PDO::FETCH_ASSOC);
if ($pre) echo json_encode($pre); else { http_response_code(404); echo json_encode(["error" => "No encontrado"]); }
?>