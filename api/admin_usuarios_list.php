<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
$stmt = $pdo->query("SELECT id, nombre, email, codigo, rol, saldo_inicial, activo FROM usuarios");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>