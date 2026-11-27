<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["error" => "Método no permitido"]); exit; }

$query = "SELECT * FROM eventos WHERE 1=1";
$params = [];
if (isset($_GET['usuario'])) { $query .= " AND usuario_id = :usuario"; $params['usuario'] = $_GET['usuario']; }
if (isset($_GET['efecto'])) { $query .= " AND efecto = :efecto"; $params['efecto'] = $_GET['efecto']; }
if (isset($_GET['desde'])) { $query .= " AND fecha >= :desde"; $params['desde'] = $_GET['desde']; }
if (isset($_GET['hasta'])) { $query .= " AND fecha <= :hasta"; $params['hasta'] = $_GET['hasta']; }
if (isset($_GET['anulado'])) { $query .= " AND anulado = :anulado"; $params['anulado'] = $_GET['anulado']; }
if (isset($_GET['nombre'])) { $query .= " AND nombre LIKE :nombre"; $params['nombre'] = '%' . $_GET['nombre'] . '%'; }

$stmt = $pdo->prepare($query);
$stmt->execute($params);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>