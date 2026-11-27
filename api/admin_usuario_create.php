<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
$data = json_decode(file_get_contents("php://input"), true);

$codigo = "USR-" . strtoupper(substr(md5(uniqid()), 0, 5));

try {
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, codigo, rol, saldo_inicial, activo) VALUES (:n, :e, :p, :c, :r, :s, 1)");
    $stmt->execute([
        'n' => $data['nombre'], 'e' => $data['email'], 
        'p' => password_hash($data['password'], PASSWORD_DEFAULT),
        'c' => $codigo, 'r' => $data['rol'] ?? 'usuario', 's' => $data['saldo_inicial'] ?? 0
    ]);
    echo json_encode(["success" => true, "message" => "Usuario creado", "id" => $pdo->lastInsertId()]);
} catch (PDOException $e) { http_response_code(500); echo json_encode(["error" => "Email o código duplicado"]); }
?>