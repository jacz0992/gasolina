<?php

namespace Models;

use Config\Database;
use PDO;
use PDOException;

class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public function findByEmail(string $email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById(int $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createWithToken(string $nombre, string $email, string $passwordHash, string $token)
    {
        try {
            $sql = "INSERT INTO usuarios (nombre, email, password, token, token_expiry, is_verified)
                    VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), 0)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nombre, $email, $passwordHash, $token]);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function setVerifyToken(int $userId, string $token, int $hoursValid = 24): bool
    {
        $hoursValid = max(1, (int)$hoursValid);
        $sql = "UPDATE usuarios
                SET token = ?, token_expiry = DATE_ADD(NOW(), INTERVAL $hoursValid HOUR)
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$token, $userId]);
    }

    public function activateAccount(string $token): bool
    {
        $sql = "UPDATE usuarios
                SET is_verified = 1, token = NULL, token_expiry = NULL
                WHERE token = ?
                  AND (token_expiry IS NULL OR token_expiry > NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    // Reset password (usa reset_token/reset_expires, no pisa token de verificación)
    public function setResetToken(string $email, string $token, int $minutesValid = 60): bool
    {
        $minutesValid = max(5, (int)$minutesValid);
        $sql = "UPDATE usuarios
                SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL $minutesValid MINUTE)
                WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$token, $email]);
        return $stmt->rowCount() > 0;
    }

    // Compatibilidad con tu AuthController: findByToken() para reset
    public function findByToken(string $token)
    {
        $sql = "SELECT * FROM usuarios
                WHERE reset_token = ?
                  AND (reset_expires IS NULL OR reset_expires > NOW())
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword(int $id, string $newHash): bool
    {
        $sql = "UPDATE usuarios
                SET password = ?, reset_token = NULL, reset_expires = NULL
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$newHash, $id]);
    }

    // --- PERFIL ---

    public function updateNombre(int $id, string $nombre): bool
    {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
        return $stmt->execute([$nombre, $id]);
    }

    public function updateFoto(int $id, string $filename): bool
    {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
        return $stmt->execute([$filename, $id]);
    }
}
