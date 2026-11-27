<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("UPDATE usuarios SET nombre=:n, email=:e, rol=:r, activo=:a WHERE id=:id");
$stmt->execute(['n'=>$data['nombre'], 'e'=>$data['email'], 'r'=>$data['rol'], 'a'=>$data['activo'], 'id'=>$data['id']]);
echo json_encode(["success" => true, "message" => "Usuario actualizado"]);
?>