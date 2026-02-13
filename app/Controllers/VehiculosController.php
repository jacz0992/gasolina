<?php
namespace Controllers;

use Core\Controller;
use Models\Vehiculo;
use Models\Repostaje;

class VehiculosController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?c=Auth');
            return;
        }

        $userId = $_SESSION['user_id'];
        $vehiculoModel = new Vehiculo();
        $repostajeModel = new Repostaje();

        // 1. Obtener todos los vehículos
        $vehiculos = $vehiculoModel->getAllByUser($userId);
        
        // 2. Filtrar por Búsqueda (si hay)
        $search = $_GET['q'] ?? '';
        if ($search) {
            $vehiculos = array_filter($vehiculos, function($v) use ($search) {
                return stripos($v['modelo'], $search) !== false || stripos($v['placa'], $search) !== false;
            });
        }

        // 3. Procesar Datos para la Tabla (Rendimiento, Tendencia, Última Tanqueada)
        foreach ($vehiculos as &$v) {
            $logs = $repostajeModel->getByVehicle($v['id']);
            
            // A) Última Tanqueada
            $v['ultima_fecha'] = !empty($logs) ? date('d M', strtotime($logs[0]['fecha'])) : '-';
            
            // B) Rendimiento Promedio General
            $rendimientos = [];
            $logsAsc = array_reverse($logs);
            $prevOdo = 0;
            
            // Separar logs por mes para tendencia (Mes Actual vs Anterior)
            $mesActual = date('Y-m');
            $mesAnterior = date('Y-m', strtotime('-1 month'));
            $rendEsteMes = [];
            $rendMesAnt = [];

            foreach ($logsAsc as $log) {
                if ($prevOdo > 0 && $log['odometro'] > $prevOdo) {
                    $dist = $log['odometro'] - $prevOdo;
                    $val = $dist / ($log['galones'] > 0 ? $log['galones'] : 1);
                    
                    if ($val > 0.5 && $val < 200) { // Filtro de coherencia
                        $rendimientos[] = $val;
                        
                        // Clasificar por mes
                        if (strpos($log['fecha'], $mesActual) === 0) $rendEsteMes[] = $val;
                        if (strpos($log['fecha'], $mesAnterior) === 0) $rendMesAnt[] = $val;
                    }
                }
                $prevOdo = $log['odometro'];
            }

            // Promedio General
            $v['rendimiento_promedio'] = count($rendimientos) > 0 ? array_sum($rendimientos) / count($rendimientos) : 0;
            
            // C) Tendencia vs Mes Anterior
            $promEsteMes = count($rendEsteMes) > 0 ? array_sum($rendEsteMes) / count($rendEsteMes) : 0;
            $promMesAnt = count($rendMesAnt) > 0 ? array_sum($rendMesAnt) / count($rendMesAnt) : 0;
            
            $v['tendencia'] = 0;
            if ($promMesAnt > 0) {
                $v['tendencia'] = (($promEsteMes - $promMesAnt) / $promMesAnt) * 100;
            }

            // D) Rango Estimado
            $v['rango_estimado'] = $v['rendimiento_promedio'] * $v['capacidad_tanque'];
            
            // E) Odómetro Actual
            $v['odometro'] = !empty($logs) ? $logs[0]['odometro'] : 0;
        }

        // 4. Paginación
        $itemsPerPage = 5; // Pocos ítems para que se vea bonito como en el mockup
        $totalItems = count($vehiculos);
        $totalPages = max(1, ceil($totalItems / $itemsPerPage));
        $page = max(1, min($_GET['page'] ?? 1, $totalPages));
        
        $vehiculosPaginados = array_slice($vehiculos, ($page - 1) * $itemsPerPage, $itemsPerPage);

        // 5. Renderizar
        $this->view('vehiculos/index', [
            'vehiculos' => $vehiculosPaginados,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'total_items' => $totalItems,
                'q' => $search
            ],
            'user_name' => $_SESSION['user_name']
        ]);
    }
}
