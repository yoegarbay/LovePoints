<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }
if (!isset($_SESSION['usuario_id'])) { http_response_code(401); echo json_encode(["error" => "No autorizado"]); exit; }

$granularidad = $_GET['granularidad'] ?? 'mes';
$desde = $_GET['desde'] ?? '1970-01-01';
$hasta = $_GET['hasta'] ?? date('Y-m-d');
$uid = $_SESSION['usuario_id'];

$formato = ($granularidad === 'mes') ? '%Y-%m' : (($granularidad === 'trimestre') ? 'CONCAT(YEAR(fecha), "-T", QUARTER(fecha))' : '%Y');

$sql = "SELECT DATE_FORMAT(fecha, :formato) as periodo, 
               SUM(CASE WHEN efecto = 'resta' THEN coste ELSE 0 END) as total_gastado,
               SUM(CASE WHEN efecto = 'suma' THEN coste ELSE 0 END) as total_ganado
        FROM eventos 
        WHERE usuario_id = :uid AND fecha BETWEEN :desde AND :hasta AND anulado = 0
        GROUP BY periodo ORDER BY periodo ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['formato' => $formato, 'uid' => $uid, 'desde' => $desde, 'hasta' => $hasta]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>