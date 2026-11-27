<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }

session_unset(); session_destroy();
echo json_encode(["success" => true, "message" => "Sesión cerrada"]);
?>