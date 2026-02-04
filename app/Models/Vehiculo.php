<?php
namespace Models;
use Config\Database;
use PDO;

class Vehiculo {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function getAllByUser($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM vehiculos WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getById($id, $userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM vehiculos WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    public function save($data, $userId) {
        // CORRECCIÓN CRÍTICA:
        // Buscamos 'id' O 'vehicle_id'. Esto arregla el bug de la redirección
        // porque asegura que detectamos el ID al editar, permitiendo al controlador
        // saber a qué vehículo volver después de subir la foto.
        $id = !empty($data['id']) ? $data['id'] : (!empty($data['vehicle_id']) ? $data['vehicle_id'] : null);
        
        $foto = $data['foto'] ?? null;
        
        if ($id) {
            // --- UPDATE (Actualizar vehículo existente) ---
            $sql = "UPDATE vehiculos SET nombre=?, marca=?, modelo=?, descripcion=?, tipo_combustible=?, unidad_combustible=?, unidad_consumo=?, capacidad_tanque=?" . ($foto ? ", foto=?" : "") . " WHERE id=? AND user_id=?";
            
            $params = [
                $data['nombre'], 
                $data['marca'], 
                $data['modelo'], 
                $data['descripcion'], 
                $data['tipo_combustible'], 
                $data['unidad_combustible'], 
                $data['unidad_consumo'], 
                $data['capacidad_tanque']
            ];
            
            // Si hay foto nueva, la agregamos a los parámetros antes del WHERE
            if($foto) {
                $params[] = $foto;
            }
            
            // Agregamos ID y UserID para el WHERE
            $params[] = $id;
            $params[] = $userId;

        } else {
            // --- INSERT (Crear nuevo vehículo) ---
            $sql = "INSERT INTO vehiculos (user_id, nombre, marca, modelo, descripcion, tipo_combustible, unidad_combustible, unidad_consumo, capacidad_tanque, foto) VALUES (?,?,?,?,?,?,?,?,?,?)";
            
            $params = [
                $userId, 
                $data['nombre'], 
                $data['marca'], 
                $data['modelo'], 
                $data['descripcion'], 
                $data['tipo_combustible'], 
                $data['unidad_combustible'], 
                $data['unidad_consumo'], 
                $data['capacidad_tanque'], 
                $foto
            ];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        // Retornamos el ID existente (en update) o el nuevo (en insert)
        return $id ? $id : $this->pdo->lastInsertId();
    }
}
?>
