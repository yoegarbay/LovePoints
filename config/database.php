<?php
class Database
{
    private static $host = '127.0.0.1';
    private static $dbname = 'db_inter2';
    private static $username = 'root';
    private static $password = '';
    private static $charset = 'utf8mb4';

    public static function connect()
    {
        try {
            $dsn = "mysql:host=" . self::$host .
                   ";dbname=" . self::$dbname .
                   ";charset=" . self::$charset;

            $pdo = new PDO($dsn, self::$username, self::$password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw new Exception("Error al conectar con la base de datos.");
        }
    }
}


$pdo = Database::connect();
?>