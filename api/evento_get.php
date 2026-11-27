<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM eventos WHERE id = :id");
$stmt->execute(['id' => $id]);
$ev = $stmt->fetch(PDO::FETCH_ASSOC);
if ($ev) echo json_encode($ev); else { http_response_code(404); echo json_encode(["error" => "No encontrado"]); }
?>