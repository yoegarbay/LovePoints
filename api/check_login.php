<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["ok" => false, "error" => "Método no permitido"]); exit; }

if (isset($_SESSION['usuario_id'])) {
    $uid = $_SESSION['usuario_id'];
    
    $stmt = $pdo->prepare("SELECT id FROM parejas WHERE usuario_1_id = :id OR usuario_2_id = :id");
    $stmt->execute(['id' => $uid]);
    $tiene_pareja = $stmt->fetch() ? true : false;
    
    echo json_encode(["ok" => true, "sesion" => true, "tiene_pareja" => $tiene_pareja]);
} else {
    echo json_encode(["ok" => true, "sesion" => false, "tiene_pareja" => false]);
}
?>
