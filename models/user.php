<?php
require_once __DIR__ . '/../config/database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function getByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE codigo = :c AND activo = TRUE LIMIT 1");
        $stmt->execute(['c' => $codigo]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function getById($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password_hash']);
    }

    public function generateCode(): string
    {
        do {
            $letras = '';
            for ($i = 0; $i < 3; $i++) {
                $letras .= chr(random_int(65, 90));
            }
            $numeros = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $codigo = $letras . $numeros;
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE codigo = :codigo");
            $stmt->execute(['codigo' => $codigo]);
        } while ($stmt->fetch());

        return $codigo;
    }

    public function create(string $nombre, string $email, string $password): array
    {
        $codigo = $this->generateCode();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, email, password_hash, codigo, rol)
                VALUES (:nombre, :email, :hash, :codigo, 'usuario')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'email'  => $email,
            'hash'   => $hash,
            'codigo' => $codigo,
        ]);

        return ['id' => (int) $this->db->lastInsertId(), 'codigo' => $codigo];
    }

    public function listAll(): array
    {
        return $this->db->query("SELECT id, nombre, email, codigo, rol, saldo_inicial, activo, created_at
                                 FROM usuarios ORDER BY id")->fetchAll();
    }

    public function update(int $id, array $datos): bool
    {
        $campos = [];
        $params = ['id' => $id];
        foreach (['nombre','email','rol','activo','saldo_inicial'] as $c) {
            if (array_key_exists($c, $datos)) {
                $campos[] = "$c = :$c";
                $params[$c] = $datos[$c];
            }
        }
        if (empty($campos)) return false;

        $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = :id";
        return $this->db->prepare($sql)->execute($params);
    }

    public function bajaLogica(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET activo = FALSE WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}