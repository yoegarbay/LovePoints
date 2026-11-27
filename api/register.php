<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["ok" => false, "error" => "Método no permitido"]); exit; }

// Recibimos por $_POST
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$saldo_inicial = $_POST['saldo_inicial'] ?? 0;

if (empty($email) || empty($password) || empty($nombre)) { 
    http_response_code(400); 
    echo json_encode(["ok" => false, "error" => "Faltan campos obligatorios"]); 
    exit; 
}

$codigo = "USR-" . strtoupper(substr(md5(uniqid()), 0, 5));

try {
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, codigo, rol, saldo_inicial, activo) VALUES (:n, :e, :p, :c, 'usuario', :s, 1)");
    $stmt->execute([
        'n' => $nombre, 'e' => $email, 
        'p' => password_hash($password, PASSWORD_DEFAULT), 
        'c' => $codigo, 's' => $saldo_inicial
    ]);
    echo json_encode(["ok" => true, "codigo" => $codigo]);
} catch (PDOException $e) { 
    http_response_code(500); 
    echo json_encode(["ok" => false, "error" => "El email ya está registrado"]); 
}
?>