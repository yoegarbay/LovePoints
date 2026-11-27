<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
$id = $_GET['id'] ?? null;
if (!$id) { http_response_code(400); echo json_encode(["error" => "Falta el ID"]); exit; }

$stmt = $pdo->prepare("SELECT id, nombre, email, codigo, rol, saldo_inicial, activo FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user) echo json_encode($user); else { http_response_code(404); echo json_encode(["error" => "No encontrado"]); }
?>