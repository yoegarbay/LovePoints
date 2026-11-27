<?php
require_once __DIR__ . '/../config/database.php';

class Predefinido
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function listByUsuario(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM predefinidos WHERE usuario_id = :u ORDER BY nombre"
        );
        $stmt->execute(['u' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function get(int $id, int $usuarioId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM predefinidos WHERE id = :id AND usuario_id = :u LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'u' => $usuarioId]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function create(int $usuarioId, string $nombre, float $coste, string $efecto): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO predefinidos (usuario_id, nombre, coste, efecto)
             VALUES (:u, :n, :c, :e)"
        );
        $stmt->execute(['u' => $usuarioId, 'n' => $nombre, 'c' => $coste, 'e' => $efecto]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $usuarioId, string $nombre, float $coste, string $efecto): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE predefinidos SET nombre = :n, coste = :c, efecto = :e
             WHERE id = :id AND usuario_id = :u"
        );
        return $stmt->execute(['id'=>$id, 'u'=>$usuarioId, 'n'=>$nombre, 'c'=>$coste, 'e'=>$efecto]);
    }

    public function delete(int $id, int $usuarioId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM predefinidos WHERE id = :id AND usuario_id = :u"
        );
        return $stmt->execute(['id' => $id, 'u' => $usuarioId]);
    }
}