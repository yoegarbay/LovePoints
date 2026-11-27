<?php
require_once __DIR__ . '/../config/database.php';

class Pareja
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getByUsuario(int $usuarioId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM parejas WHERE usuario_1_id = :a OR usuario_2_id = :b LIMIT 1"
        );
        $stmt->execute(['a' => $usuarioId, 'b' => $usuarioId]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function crear(int $u1, int $u2): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO parejas (usuario_1_id, usuario_2_id) VALUES (:a, :b)"
        );
        return $stmt->execute(['a' => $u1, 'b' => $u2]);
    }

    public function eliminarPorUsuario(int $usuarioId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM parejas WHERE usuario_1_id = :a OR usuario_2_id = :b"
        );
        return $stmt->execute(['a' => $usuarioId, 'b' => $usuarioId]);
    }
}