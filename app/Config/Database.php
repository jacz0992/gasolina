<?php
namespace Config;
use PDO;
use PDOException;

class Database {
    private static $host = 'localhost';
    private static $db   = 'u500271526_gasolina'; // TU BD
    private static $user = 'u500271526_gasolina';     // TU USUARIO
    private static $pass = 'M4r14&G4rd3l192!';        // TU CLAVE

    public static function connect() {
        try {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=utf8mb4";
            $pdo = new PDO($dsn, self::$user, self::$pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die("<h1>Error de Conexión</h1><p>" . $e->getMessage() . "</p>");
        }
    }
}
?>
