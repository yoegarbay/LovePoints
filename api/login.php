<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["ok" => false, "error" => "Método no permitido"]); exit; }

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT id, password_hash FROM usuarios WHERE email = :email AND activo = 1");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['usuario_id'] = $user['id'];
    
    // Comprobar si tiene pareja para la respuesta
    $stmt2 = $pdo->prepare("SELECT id FROM parejas WHERE usuario_1_id = :id OR usuario_2_id = :id");
    $stmt2->execute(['id' => $user['id']]);
    $tiene_pareja = $stmt2->fetch() ? true : false;

    echo json_encode(["ok" => true, "tiene_pareja" => $tiene_pareja]);
} else {
    http_response_code(401); 
    echo json_encode(["ok" => false, "error" => "Credenciales incorrectas"]);
}
?>