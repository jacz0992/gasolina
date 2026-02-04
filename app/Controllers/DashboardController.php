<?php
namespace Controllers;
use Core\Controller;
use Models\Vehiculo;
use Models\Repostaje;

class DashboardController extends Controller {
    
    // --- PÁGINA PRINCIPAL (DASHBOARD) ---
    public function index() {
        // 1. Seguridad
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?c=Auth');
        }
        
        $userId = $_SESSION['user_id'];
        $vehiculoModel = new Vehiculo();
        $repostajeModel = new Repostaje();

        // 2. Obtener Vehículos
        $misVehiculos = $vehiculoModel->getAllByUser($userId);
        
        // 3. Determinar Vehículo Actual
        $vehiculoActual = null;
        if (!empty($misVehiculos)) {
            $vId = $_GET['v'] ?? $misVehiculos[0]['id'];
            foreach($misVehiculos as $v) {
                if($v['id'] == $vId) { $vehiculoActual = $v; break; }
            }
            // Fallback si el ID de la URL no es válido
            if (!$vehiculoActual) $vehiculoActual = $misVehiculos[0];
        }

        // 4. Inicializar Datos Vacíos
        $logs = [];
        $stats = [
            'promedio_rend' => 0, 
            'rango_estimado' => 0, 
            'gasto_mes' => 0, 
            'mes_nombre' => date('M Y')
        ];
        $charts = [
            'fechas' => [], 
            'rendimiento' => [], 
            'gasto_mensual' => [], 
            'mapa' => []
        ];

        // 5. CÁLCULOS MATEMÁTICOS (Si hay vehículo)
        if ($vehiculoActual) {
            $logs = $repostajeModel->getByVehicle($vehiculoActual['id']);
            
            // Procesar logs en orden cronológico (antiguo a nuevo) para cálculos
            $logsAsc = array_reverse($logs);
            
            $prevOdo = 0;
            $totalRend = 0;
            $countRend = 0;
            $gastosPorMes = [];

            foreach ($logsAsc as $log) {
                // A) Gasto Mensual
                $mesAnio = date('M Y', strtotime($log['fecha']));
                if (!isset($gastosPorMes[$mesAnio])) $gastosPorMes[$mesAnio] = 0;
                $gastosPorMes[$mesAnio] += $log['precio_total'];

                // B) Rendimiento
                $currentRend = null;
                if ($prevOdo > 0 && $log['odometro'] > $prevOdo && $log['galones'] > 0) {
                    $dist = $log['odometro'] - $prevOdo;
                    $val = $dist / $log['galones'];
                    
                    // Filtro de coherencia (evitar picos absurdos)
                    if ($val > 0.5 && $val < 200) {
                        $totalRend += $val;
                        $countRend++;
                        $currentRend = round($val, 1);
                    }
                }
                
                // C) Datos para Gráficos
                if ($currentRend !== null) {
                    $charts['fechas'][] = date('d/m', strtotime($log['fecha']));
                    $charts['rendimiento'][] = $currentRend;
                }

                // D) Mapa
                if ($log['latitud']) {
                    $charts['mapa'][] = [
                        'lat' => $log['latitud'],
                        'lng' => $log['longitud'],
                        'name' => $log['nombre_estacion'] . " (" . date('d/m', strtotime($log['fecha'])) . ")"
                    ];
                }

                $prevOdo = $log['odometro'];
            }

            // 6. Resumen Final de KPIs
            $stats['promedio_rend'] = $countRend > 0 ? ($totalRend / $countRend) : 0;
            $stats['rango_estimado'] = $stats['promedio_rend'] * $vehiculoActual['capacidad_tanque'];
            
            if (!empty($gastosPorMes)) {
                $stats['gasto_mes'] = end($gastosPorMes);
                $stats['mes_nombre'] = key($gastosPorMes);
            }
            
            $charts['gasto_mensual'] = $gastosPorMes;
        }

        // 7. Renderizar Vista
        $this->view('dashboard/index', [
            'user_name' => $_SESSION['user_name'],
            'mis_vehiculos' => $misVehiculos,
            'vehiculo_actual' => $vehiculoActual,
            'logs' => $logs,
            'stats' => $stats,
            'charts' => $charts
        ]);
    }

    // --- ACCIÓN: GUARDAR VEHÍCULO ---
    public function saveVehicle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $data = $_POST;
            
            // Manejo de Foto
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . "." . $ext;
                // Ruta relativa desde public/index.php
                if(move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $filename)) {
                    $data['foto'] = $filename;
                }
            }
            
            $model = new Vehiculo();
            $id = $model->save($data, $_SESSION['user_id']);
            
            $this->redirect("?c=Dashboard&v=$id");
        }
    }

    // --- ACCIÓN: GUARDAR LOG (REPOSTAJE) ---
    public function saveLog() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $model = new Repostaje();
            $vid = $_POST['vehicle_id'];
            
            // Preparar datos (mapear nombres del form a la BD si es necesario)
            // En tu caso los nombres del form coinciden con la BD (fecha, odometro, etc)
            // Pero necesitamos user_id
            $data = $_POST;
            $data['user_id'] = $_SESSION['user_id'];
            
            // IMPORTANTE: Debes asegurarte que tu Modelo Repostaje tenga el método save()
            // Si no lo tiene, agrégalo similar al de Vehiculo.
            $model->save($data);
            
            $this->redirect("?c=Dashboard&v=$vid");
        }
    }

    // --- ACCIÓN: BORRAR LOG ---
    public function deleteLog() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Repostaje();
            $model->delete($_POST['id'], $_POST['vehicle_id']);
            $this->redirect("?c=Dashboard&v=" . $_POST['vehicle_id']);
        }
    }
}
?>
