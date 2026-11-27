<?php
require_once __DIR__ . '/../config/database.php';

class Recarga
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function listByUsuarios(array $usuarios): array
    {
        $in = implode(',', array_map('intval', $usuarios));
        return $this->db->query(
            "SELECT * FROM recargas WHERE usuario_id IN ($in) ORDER BY fecha DESC"
        )->fetchAll();
    }

    public function create(int $usuarioId, float $importe, ?string $motivo): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO recargas (usuario_id, importe, fecha, motivo)
             VALUES (:u, :i, NOW(), :m)"
        );
        $stmt->execute(['u'=>$usuarioId, 'i'=>$importe, 'm'=>$motivo]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id, array $usuariosPermitidos): bool
    {
        $stmt = $this->db->prepare("SELECT usuario_id FROM recargas WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $uid = (int)($stmt->fetchColumn() ?: 0);
        if (!in_array($uid, $usuariosPermitidos, true)) return false;
        return $this->db->prepare("DELETE FROM recargas WHERE id = :id")->execute(['id'=>$id]);
    }
}