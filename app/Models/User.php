<?php
namespace Models;
use Config\Database;
use PDO;

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    // Buscar usuario por email (para login y verificar duplicados)
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Crear usuario "pendiente" (is_verified = 0)
    public function createWithToken($nombre, $email, $passwordHash, $token) {
        try {
            $sql = "INSERT INTO usuarios (nombre, email, password, token, is_verified) VALUES (?, ?, ?, ?, 0)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nombre, $email, $passwordHash, $token]);
            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            return false; // Error (probablemente email duplicado)
        }
    }

    // Activar cuenta cuando dan clic en el correo
    public function activateAccount($token) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET is_verified = 1, token = NULL WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    // --- NUEVO PARA "OLVIDÉ CONTRASEÑA" ---
    
    // Guardar token de recuperación
    public function setResetToken($email, $token) {
        // Expiración simple: token válido por ahora (sin timestamp complejo para no enredarnos hoy)
        $stmt = $this->pdo->prepare("UPDATE usuarios SET token = ? WHERE email = ?");
        $stmt->execute([$token, $email]);
        return $stmt->rowCount() > 0;
    }

    // Buscar por token (para cambiar contraseña)
    public function findByToken($token) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    // Cambiar contraseña y borrar token
    public function updatePassword($id, $newHash) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET password = ?, token = NULL WHERE id = ?");
        return $stmt->execute([$newHash, $id]);
    }
}
?>
