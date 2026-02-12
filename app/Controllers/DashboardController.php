<?php

namespace Controllers;

use Core\Controller;
use Models\Vehiculo;
use Models\Repostaje;

class DashboardController extends Controller
{
    // --- PÁGINA PRINCIPAL (DASHBOARD) ---
    public function index()
    {
        // 1. Seguridad
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?c=Auth');
            return;
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
            if (!$vehiculoActual) $vehiculoActual = $misVehiculos[0];
        }

        // 4. Inicializar Datos
        $logsForTable = [];
        $pagination = [];
        
        $stats = [
            'promedio_rend' => 0,
            'rango_estimado' => 0,
            'gasto_mes' => 0,
            'mes_nombre' => date('F Y'),
            'tendencia_rend' => 0,
            'tendencia_gasto' => 0
        ];

        $charts = [
            'fechas' => [],
            'rendimiento' => [],
            'gasto_mensual' => [],
            'mapa' => []
        ];

        // 5. LÓGICA PRINCIPAL
        if ($vehiculoActual) {
            // Obtener TODOS los logs sin filtrar primero para cálculos globales (tendencias históricas)
            $rawLogs = $repostajeModel->getByVehicle($vehiculoActual['id']);

            // --- FILTRO DE FECHAS (Nuevo) ---
            $from = $_GET['from'] ?? null;
            $to   = $_GET['to'] ?? null;
            
            // Logs Filtrados (para la tabla y gráficas específicas)
            $filteredLogs = $rawLogs; 

            if ($from && $to) {
                $filteredLogs = array_filter($rawLogs, function($log) use ($from, $to) {
                    $fechaLog = date('Y-m-d', strtotime($log['fecha']));
                    return $fechaLog >= $from && $fechaLog <= $to;
                });
                // Re-indexar array después de filtrar
                $filteredLogs = array_values($filteredLogs);
            }

            // --- A. PAGINACIÓN (Usamos logs filtrados) ---
            $itemsPerPage = 10;
            $totalItems = count($filteredLogs);
            $totalPages = max(1, ceil($totalItems / $itemsPerPage));
            
            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $currentPage = max(1, min($currentPage, $totalPages));
            
            $offset = ($currentPage - 1) * $itemsPerPage;
            $logsForTable = array_slice($filteredLogs, $offset, $itemsPerPage);
            
            $pagination = [
                'current' => $currentPage,
                'total' => $totalPages,
                'v_id' => $vehiculoActual['id'],
                'from' => $from, // Pasar filtros a la vista para mantenerlos en links
                'to' => $to
            ];

            // --- B. ESTADÍSTICAS GLOBALES (Usamos todos los logs para tendencias generales) ---
            $mesActual = date('Y-m');
            $mesPasado = date('Y-m', strtotime('-1 month'));
            
            $logsEsteMes = [];
            $logsMesPasado = [];
            
            foreach ($rawLogs as $log) {
                if (strpos($log['fecha'], $mesActual) === 0) $logsEsteMes[] = $log;
                if (strpos($log['fecha'], $mesPasado) === 0) $logsMesPasado[] = $log;
            }

            // Gasto y Tendencia
            $gastoEsteMes = array_sum(array_column($logsEsteMes, 'precio_total'));
            $gastoMesPasado = array_sum(array_column($logsMesPasado, 'precio_total'));
            
            if ($gastoMesPasado > 0) {
                $stats['tendencia_gasto'] = (($gastoEsteMes - $gastoMesPasado) / $gastoMesPasado) * 100;
            }
            $stats['gasto_mes'] = $gastoEsteMes;

            // Rendimiento y Tendencia
            $rendEsteMes = $this->calcularPromedioRendimiento($logsEsteMes);
            $rendMesPasado = $this->calcularPromedioRendimiento($logsMesPasado);

            if ($rendMesPasado > 0) {
                $stats['tendencia_rend'] = (($rendEsteMes - $rendMesPasado) / $rendMesPasado) * 100;
            }

            // --- C. PROCESAMIENTO HISTÓRICO (Usamos logs filtrados para gráficas coherentes con la tabla) ---
            // Ordenamos cronológicamente (antiguo -> nuevo) para procesar
            $logsAsc = array_reverse($filteredLogs); 
            $prevOdo = 0;
            $totalRendGral = 0;
            $countRendGral = 0;
            $gastosPorMesGrafica = [];

            foreach ($logsAsc as $log) {
                // Gráfica Gastos
                $mesAnio = date('M Y', strtotime($log['fecha']));
                if (!isset($gastosPorMesGrafica[$mesAnio])) $gastosPorMesGrafica[$mesAnio] = 0;
                $gastosPorMesGrafica[$mesAnio] += $log['precio_total'];

                // Rendimiento
                $currentRend = null;
                if ($prevOdo > 0 && $log['odometro'] > $prevOdo && $log['galones'] > 0) {
                    $dist = $log['odometro'] - $prevOdo;
                    $val = $dist / $log['galones'];

                    if ($val > 0.5 && $val < 200) {
                        $totalRendGral += $val;
                        $countRendGral++;
                        $currentRend = round($val, 1);
                    }
                }

                // Datos Gráficas
                if ($currentRend !== null) {
                    $charts['fechas'][] = date('d/m', strtotime($log['fecha']));
                    $charts['rendimiento'][] = $currentRend;
                }

                // Mapa
                if ($log['latitud']) {
                    $charts['mapa'][] = [
                        'lat' => $log['latitud'],
                        'lng' => $log['longitud'],
                        'name' => $log['nombre_estacion']
                    ];
                }

                $prevOdo = $log['odometro'];
            }

            // Promedios Finales
            $stats['promedio_rend'] = $countRendGral > 0 ? ($totalRendGral / $countRendGral) : 0;
            $stats['rango_estimado'] = $stats['promedio_rend'] * $vehiculoActual['capacidad_tanque'];
            
            $charts['gasto_mensual'] = $gastosPorMesGrafica;
        }

        // 6. Renderizar Vista
        $this->view('dashboard/index', [
            'user_name' => $_SESSION['user_name'] ?? 'Usuario',
            'mis_vehiculos' => $misVehiculos,
            'vehiculo_actual' => $vehiculoActual,
            'logs' => $logsForTable, // Aquí van los logs ya paginados y filtrados
            'stats' => $stats,
            'charts' => $charts,
            'pagination' => $pagination
        ]);
    }

    private function calcularPromedioRendimiento($logs) {
        $total = 0; $count = 0;
        foreach ($logs as $log) {
            if (isset($log['rendimiento']) && $log['rendimiento'] > 0) {
                $total += $log['rendimiento'];
                $count++;
            }
        }
        return $count > 0 ? $total / $count : 0;
    }

    // --- ACCIONES CRUD (Se mantienen igual) ---
    public function saveVehicle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $data = $_POST;
            if(empty($data['capacidad_tanque'])) $data['capacidad_tanque'] = 12;

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . "." . $ext;
                if(move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $filename)) {
                    $data['foto'] = $filename;
                }
            }

            $model = new Vehiculo();
            $id = $model->save($data, $_SESSION['user_id']);
            $this->redirect("?c=Dashboard&v=$id");
        }
    }

    public function editVehicle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $data = $_POST;
            $vehicleId = $data['id'];
            
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . "." . $ext;
                if(move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $filename)) {
                    $data['foto'] = $filename;
                }
            }

            $model = new Vehiculo();
            $model->update($vehicleId, $data, $_SESSION['user_id']);
            $this->redirect("?c=Dashboard&v=$vehicleId");
        }
    }

    public function saveLog() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $model = new Repostaje();
            $vid = $_POST['vehicle_id'];
            $data = $_POST;
            $data['user_id'] = $_SESSION['user_id'];
            
            if (isset($data['full']) && $data['full'] == 1) {
                $lastLog = $model->getLastByVehicle($vid);
                if ($lastLog && $data['odometro'] > $lastLog['odometro']) {
                     $dist = $data['odometro'] - $lastLog['odometro'];
                     $data['rendimiento'] = $dist / ($data['galones'] > 0 ? $data['galones'] : 1);
                }
            }

            $model->save($data);
            $this->redirect("?c=Dashboard&v=$vid");
        }
    }

    public function deleteLog() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Repostaje();
            if(isset($_POST['id']) && isset($_POST['vehicle_id'])) {
                $model->delete($_POST['id'], $_POST['vehicle_id']);
                $this->redirect("?c=Dashboard&v=" . $_POST['vehicle_id']);
            } else {
                $this->redirect("?c=Dashboard");
            }
        }
    }

    public function deleteVehicle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $id = $_POST['id'] ?? null;
            if ($id) {
                $model = new Vehiculo();
                $model->delete($id, $_SESSION['user_id']);
            }
            $this->redirect("?c=Dashboard");
        }
    }
}
