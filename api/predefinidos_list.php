<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
$stmt = $pdo->prepare("SELECT * FROM predefinidos WHERE usuario_id = :uid");
$stmt->execute(['uid' => $_SESSION['usuario_id']]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>