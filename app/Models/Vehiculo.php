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
            $sql = "UPDATE vehiculos SET placa=?, nombre=?, marca=?, modelo=?, descripcion=?, tipo_combustible=?, unidad_combustible=?, unidad_consumo=?, capacidad_tanque=?" . ($foto ? ", foto=?" : "") . " WHERE id=? AND user_id=?";
            
            $params = [
                $data['placa'] ?? '',
                $data['nombre'] ?? '', 
                $data['marca'] ?? '', 
                $data['modelo'] ?? '', 
                $data['descripcion'] ?? '', 
                $data['tipo_combustible'] ?? 'Gasolina', 
                $data['unidad_combustible'] ?? 'Galones', 
                $data['unidad_consumo'] ?? 'km/gal', 
                $data['capacidad_tanque'] ?? 12
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
            $sql = "INSERT INTO vehiculos (user_id, placa, nombre, marca, modelo, descripcion, tipo_combustible, unidad_combustible, unidad_consumo, capacidad_tanque, foto) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
            
            $params = [
                $userId,
                $data['placa'] ?? '',
                $data['nombre'] ?? '', 
                $data['marca'] ?? '', 
                $data['modelo'] ?? '', 
                $data['descripcion'] ?? '', 
                $data['tipo_combustible'] ?? 'Gasolina', 
                $data['unidad_combustible'] ?? 'Galones', 
                $data['unidad_consumo'] ?? 'km/gal', 
                $data['capacidad_tanque'] ?? 12, 
                $foto
            ];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        // Retornamos el ID existente (en update) o el nuevo (en insert)
        return $id ? $id : $this->pdo->lastInsertId();
    }

    // --- MÉTODO UPDATE ESPECÍFICO PARA EDICIÓN ---
    public function update($id, $data, $userId)
    {
        // 1. Preparar la consulta base
        $sql = "UPDATE vehiculos 
                SET placa = ?, modelo = ?, descripcion = ?, capacidad_tanque = ?, unidad_consumo = ?";
        
        $params = [
            $data['placa'] ?? '',
            $data['modelo'] ?? '',
            $data['descripcion'] ?? '',
            $data['capacidad_tanque'] ?? 12,
            $data['unidad_consumo'] ?? 'km/gal'
        ];
        
        // 2. Si viene foto nueva, la actualizamos
        if (isset($data['foto'])) {
            $sql .= ", foto = ?";
            $params[] = $data['foto'];
        }
        
        // 3. Condición WHERE (Seguridad: solo editar si pertenece al usuario)
        $sql .= " WHERE id = ? AND user_id = ?";
        $params[] = $id;
        $params[] = $userId;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }


        // --- ELIMINAR VEHÍCULO (Y SUS DATOS RELACIONADOS) ---
    public function delete($id, $userId)
    {
        // Primero borramos los repostajes asociados para mantener integridad
        // (Aunque si tuvieras foreign keys con CASCADE en la BD se haría solo, 
        // pero mejor prevenir por código).
        $stmtLogs = $this->pdo->prepare("DELETE FROM repostajes WHERE vehiculo_id = ? AND user_id = ?");
        $stmtLogs->execute([$id, $userId]);

        // Ahora sí borramos el vehículo
        $stmtVehicle = $this->pdo->prepare("DELETE FROM vehiculos WHERE id = ? AND user_id = ?");
        return $stmtVehicle->execute([$id, $userId]);
    }

}
?>
