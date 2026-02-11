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
            // Fallback si el ID de la URL no es válido
            if (!$vehiculoActual) $vehiculoActual = $misVehiculos[0];
        }

        // 4. Inicializar Datos Vacíos
        $allLogs = [];
        $logsForTable = [];
        $pagination = [];

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
            // A. Obtener TODOS los logs para estadísticas
            $allLogs = $repostajeModel->getByVehicle($vehiculoActual['id']);

            // --- B. LOGICA DE PAGINACIÓN ---
            $itemsPerPage = 10;
            $totalItems = count($allLogs);
            $totalPages = max(1, ceil($totalItems / $itemsPerPage));
            
            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            if ($currentPage < 1) $currentPage = 1;
            if ($currentPage > $totalPages) $currentPage = $totalPages;
            
            $offset = ($currentPage - 1) * $itemsPerPage;
            
            // Recortar array para la tabla
            $logsForTable = array_slice($allLogs, $offset, $itemsPerPage);
            
            $pagination = [
                'current' => $currentPage,
                'total' => $totalPages,
                'v_id' => $vehiculoActual['id']
            ];

            // Procesar logs en orden cronológico (antiguo a nuevo) para cálculos
            $logsAsc = array_reverse($allLogs);

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

                    // Filtro de coherencia
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
            'logs' => $logsForTable,
            'stats' => $stats,
            'charts' => $charts,
            'pagination' => $pagination
        ]);
    }

    // --- ACCIÓN: GUARDAR VEHÍCULO (NUEVO) ---
    public function saveVehicle()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $data = $_POST;
            
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

    // --- ACCIÓN: EDITAR VEHÍCULO (MODIFICACIÓN) ---
    public function editVehicle()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $data = $_POST;
            $vehicleId = $data['id'];
            
            // Manejo de Foto (si suben una nueva)
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . "." . $ext;
                
                if(move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $filename)) {
                    $data['foto'] = $filename;
                }
            }

            $model = new Vehiculo();
            // Usamos el método update específico que agregaste al modelo
            $model->update($vehicleId, $data, $_SESSION['user_id']);
            
            $this->redirect("?c=Dashboard&v=$vehicleId");
        }
    }

    // --- ACCIÓN: GUARDAR LOG ---
    public function saveLog()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $model = new Repostaje();
            $vid = $_POST['vehicle_id'];

            $data = $_POST;
            $data['user_id'] = $_SESSION['user_id'];

            $model->save($data);

            $this->redirect("?c=Dashboard&v=$vid");
        }
    }

    // --- ACCIÓN: BORRAR LOG ---
    public function deleteLog()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Repostaje();
            $model->delete($_POST['id'], $_POST['vehicle_id']);
            $this->redirect("?c=Dashboard&v=" . $_POST['vehicle_id']);
        }
    }

        // --- ACCIÓN: BORRAR VEHÍCULO ---
    public function deleteVehicle()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $id = $_POST['id'] ?? null;
            if ($id) {
                $model = new Vehiculo();
                $model->delete($id, $_SESSION['user_id']);
            }
            // Redirigir al dashboard (cargará el siguiente vehículo o la pantalla de bienvenida)
            $this->redirect("?c=Dashboard");
        }
    }

}
