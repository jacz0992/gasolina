<?php
namespace Models;
use Config\Database;
use PDO;
use Exception;

class Repostaje {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function getByVehicle($vehicleId) {
        $stmt = $this->pdo->prepare("SELECT * FROM repostajes WHERE vehiculo_id = ? ORDER BY fecha DESC");
        $stmt->execute([$vehicleId]);
        return $stmt->fetchAll();
    }

    public function delete($id, $vehicleId) {
        $stmt = $this->pdo->prepare("DELETE FROM repostajes WHERE id = ? AND vehiculo_id = ?");
        return $stmt->execute([$id, $vehicleId]);
    }

    public function save($data) {
        try {
            // Depuración rápida: asegurar valores nulos si están vacíos
            $lat = !empty($data['latitud']) ? $data['latitud'] : null;
            $lng = !empty($data['longitud']) ? $data['longitud'] : null;
            $full = isset($data['full']) ? 1 : 0;
            $estacion = !empty($data['nombre_estacion']) ? $data['nombre_estacion'] : 'Estación Desconocida';

            // Verificar ID para saber si es UPDATE o INSERT
            $id = !empty($data['id']) ? $data['id'] : null;

            if ($id) {
                // UPDATE
                $sql = "UPDATE repostajes SET fecha=?, odometro=?, galones=?, precio_total=?, full=?, nombre_estacion=?, latitud=?, longitud=? WHERE id=?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $data['fecha'], 
                    $data['odometro'], 
                    $data['galones'], 
                    $data['precio_total'], 
                    $full, 
                    $estacion, 
                    $lat, 
                    $lng, 
                    $id
                ]);
            } else {
                // INSERT
                // Aseguramos que existan las claves críticas
                if (!isset($data['user_id']) || !isset($data['vehicle_id'])) {
                    throw new Exception("Faltan datos críticos (user_id o vehicle_id)");
                }

                $sql = "INSERT INTO repostajes (user_id, vehiculo_id, fecha, odometro, galones, precio_total, full, nombre_estacion, latitud, longitud) VALUES (?,?,?,?,?,?,?,?,?,?)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $data['user_id'], 
                    $data['vehicle_id'], // Ojo aquí: en el HTML se llama 'vehicle_id', en BD 'vehiculo_id'
                    $data['fecha'], 
                    $data['odometro'], 
                    $data['galones'], 
                    $data['precio_total'], 
                    $full, 
                    $estacion, 
                    $lat, 
                    $lng
                ]);
            }
        } catch (Exception $e) {
            // Esto mostrará el error en pantalla en lugar del "500" genérico
            die("Error SQL en Repostaje::save(): " . $e->getMessage());
        }
    }

        // --- AGREGAR ESTA FUNCIÓN AL FINAL ---
    public function getLastByVehicle($vehicleId) {
        // Busca el último repostaje ordenado por odómetro descendente
        // Nota: uso 'vehiculo_id' porque así lo tienes en tu base de datos según tu código
        $stmt = $this->pdo->prepare("SELECT * FROM repostajes WHERE vehiculo_id = ? ORDER BY odometro DESC LIMIT 1");
        $stmt->execute([$vehicleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
?>
