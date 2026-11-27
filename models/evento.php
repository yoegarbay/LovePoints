<?php
require_once __DIR__ . '/../config/database.php';

class Evento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function list(array $filtros): array
    {
        $sql = "SELECT e.*, u.nombre AS usuario_nombre
                FROM eventos e
                JOIN usuarios u ON u.id = e.usuario_id
                WHERE 1=1";
        $p = [];

        if (!empty($filtros['pareja'])) {
            $sql .= " AND e.usuario_id IN (:p1, :p2)";
            $p['p1'] = $filtros['pareja'][0];
            $p['p2'] = $filtros['pareja'][1];
        }
        if (!empty($filtros['usuario'])) {
            $sql .= " AND e.usuario_id = :u";
            $p['u'] = $filtros['usuario'];
        }
        if (!empty($filtros['efecto'])) {
            $sql .= " AND e.efecto = :e";
            $p['e'] = $filtros['efecto'];
        }
        if (!empty($filtros['desde'])) {
            $sql .= " AND e.fecha >= :d";
            $p['d'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            $sql .= " AND e.fecha <= :h";
            $p['h'] = $filtros['hasta'];
        }
        if (!empty($filtros['nombre'])) {
            $sql .= " AND e.nombre LIKE :n";
            $p['n'] = '%' . $filtros['nombre'] . '%';
        }
        if (isset($filtros['anulado']) && $filtros['anulado'] !== '') {
            $sql .= " AND e.anulado = :a";
            $p['a'] = (int)$filtros['anulado'];
        }

        $orden = in_array($filtros['orden'] ?? '', ['fecha','coste','usuario'], true)
            ? $filtros['orden'] : 'fecha';
        $dir = (strtolower($filtros['dir'] ?? '') === 'asc') ? 'ASC' : 'DESC';
        $sql .= " ORDER BY " . ($orden === 'usuario' ? 'u.nombre' : "e.$orden") . " $dir";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function get(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM eventos WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function create(int $usuarioId, int $registradoPor, ?int $predefinidoId,
                           string $nombre, string $fecha, float $coste, string $efecto): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO eventos (usuario_id, registrado_por_id, predefinido_id, nombre, fecha, coste, efecto)
             VALUES (:u, :r, :p, :n, :f, :c, :e)"
        );
        $stmt->execute([
            'u'=>$usuarioId, 'r'=>$registradoPor, 'p'=>$predefinidoId,
            'n'=>$nombre, 'f'=>$fecha, 'c'=>$coste, 'e'=>$efecto
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $usuarioId, ?int $predefinidoId,
                           string $nombre, string $fecha, float $coste, string $efecto): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE eventos SET predefinido_id = :p, nombre = :n, fecha = :f,
                                coste = :c, efecto = :e
             WHERE id = :id AND usuario_id = :u"
        );
        return $stmt->execute([
            'id'=>$id, 'u'=>$usuarioId, 'p'=>$predefinidoId,
            'n'=>$nombre, 'f'=>$fecha, 'c'=>$coste, 'e'=>$efecto
        ]);
    }

    public function anular(int $id, int $usuarioId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE eventos SET anulado = TRUE WHERE id = :id AND usuario_id = :u"
        );
        return $stmt->execute(['id'=>$id, 'u'=>$usuarioId]);
    }

    /**
     * REGLA ÚNICA del efecto (briefing regla 3):
     *   resta => -coste sobre el usuario del evento
     *   suma  => +coste sobre su PAREJA
     */
    public static function aplicarEfecto(array $evento, int $usuarioIdConsiderado, ?int $parejaId): float
    {
        if (!empty($evento['anulado'])) return 0.0;
        $coste = (float)$evento['coste'];

        if ((int)$evento['usuario_id'] === $usuarioIdConsiderado) {
            return $evento['efecto'] === 'resta' ? -$coste : 0.0;
        }
        if ($parejaId !== null && (int)$evento['usuario_id'] === $parejaId) {
            return $evento['efecto'] === 'suma' ? $coste : 0.0;
        }
        return 0.0;
    }
}