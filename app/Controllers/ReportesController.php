<?php
namespace Controllers;

use Core\Controller;
use Models\Vehiculo;
use Models\Repostaje;
use Config\Database;

class ReportesController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?c=Auth');
            return;
        }

        $userId = (int)$_SESSION['user_id'];

        $vehiculoModel = new Vehiculo();

        // Filtros
        $vehiculoId = isset($_GET['vehiculo_id']) && $_GET['vehiculo_id'] !== '' ? (int)$_GET['vehiculo_id'] : null;
        $from = $_GET['from'] ?? '';
        $to = $_GET['to'] ?? '';
        $station = $_GET['station'] ?? '';

        // Defaults de fechas (últimos 30 días)
        if ($from === '') $from = date('Y-m-d', strtotime('-30 days'));
        if ($to === '') $to = date('Y-m-d');

        // Paginación
        $itemsPerPage = 10;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $itemsPerPage;

        // Data para filtros (vehículos del usuario)
        $vehiculos = $vehiculoModel->getAllByUser($userId);

        // Estaciones (distinct) para dropdown
        $pdo = Database::connect();
        $sqlStations = "SELECT DISTINCT COALESCE(NULLIF(nombre_estacion,''), 'Estación Desconocida') AS estacion
                        FROM repostajes
                        WHERE user_id = ?
                        ORDER BY estacion ASC";
        $st = $pdo->prepare($sqlStations);
        $st->execute([$userId]);
        $stations = $st->fetchAll(\PDO::FETCH_COLUMN);

        // Query principal con joins (repostajes + vehiculos)
        $where = "r.user_id = :user_id AND r.fecha BETWEEN :from AND :to";
        $params = [
            ':user_id' => $userId,
            ':from' => $from,
            ':to' => $to
        ];

        if ($vehiculoId) {
            $where .= " AND r.vehiculo_id = :vehiculo_id";
            $params[':vehiculo_id'] = $vehiculoId;
        }

        if ($station !== '' && $station !== 'ALL') {
            // Normalizamos '' a "Estación Desconocida" igual que en la consulta DISTINCT
            if ($station === 'Estación Desconocida') {
                $where .= " AND (r.nombre_estacion IS NULL OR r.nombre_estacion = '' OR r.nombre_estacion = 'Estación Desconocida')";
            } else {
                $where .= " AND r.nombre_estacion = :station";
                $params[':station'] = $station;
            }
        }

        // Total para paginación
        $sqlCount = "SELECT COUNT(*) 
                     FROM repostajes r
                     INNER JOIN vehiculos v ON v.id = r.vehiculo_id
                     WHERE $where";
        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $totalItems = (int)$stmtCount->fetchColumn();
        $totalPages = max(1, (int)ceil($totalItems / $itemsPerPage));

        // Data paginada
        $sql = "SELECT 
                    r.id,
                    r.fecha,
                    r.odometro,
                    r.galones,
                    r.precio_total,
                    COALESCE(NULLIF(r.nombre_estacion,''), 'Estación Desconocida') AS nombre_estacion,
                    v.placa,
                    v.modelo
                FROM repostajes r
                INNER JOIN vehiculos v ON v.id = r.vehiculo_id
                WHERE $where
                ORDER BY r.fecha DESC, r.id DESC
                LIMIT $itemsPerPage OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('reportes/index', [
            'user_name' => $_SESSION['user_name'] ?? 'Usuario',
            'vehiculos' => $vehiculos,
            'stations' => $stations,
            'rows' => $rows,
            'filters' => [
                'vehiculo_id' => $vehiculoId,
                'from' => $from,
                'to' => $to,
                'station' => $station
            ],
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'total_items' => $totalItems
            ]
        ]);
    }

    public function download()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?c=Auth');
            return;
        }

        $userId = (int)$_SESSION['user_id'];

        $vehiculoId = isset($_GET['vehiculo_id']) && $_GET['vehiculo_id'] !== '' ? (int)$_GET['vehiculo_id'] : null;
        $from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $to = $_GET['to'] ?? date('Y-m-d');
        $station = $_GET['station'] ?? '';

        $pdo = Database::connect();

        $where = "r.user_id = :user_id AND r.fecha BETWEEN :from AND :to";
        $params = [
            ':user_id' => $userId,
            ':from' => $from,
            ':to' => $to
        ];

        if ($vehiculoId) {
            $where .= " AND r.vehiculo_id = :vehiculo_id";
            $params[':vehiculo_id'] = $vehiculoId;
        }

        if ($station !== '' && $station !== 'ALL') {
            if ($station === 'Estación Desconocida') {
                $where .= " AND (r.nombre_estacion IS NULL OR r.nombre_estacion = '' OR r.nombre_estacion = 'Estación Desconocida')";
            } else {
                $where .= " AND r.nombre_estacion = :station";
                $params[':station'] = $station;
            }
        }

        $sql = "SELECT 
                    COALESCE(NULLIF(r.nombre_estacion,''), 'Estación Desconocida') AS estacion,
                    r.fecha,
                    v.placa,
                    v.modelo,
                    r.odometro,
                    r.galones,
                    r.precio_total
                FROM repostajes r
                INNER JOIN vehiculos v ON v.id = r.vehiculo_id
                WHERE $where
                ORDER BY r.fecha DESC, r.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_repostajes_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');

        // Encabezados CSV
        fputcsv($out, ['ESTACION', 'FECHA', 'PLACA', 'VEHICULO', 'VALOR_POR_GALON', 'ODOMETRO', 'TOTAL', 'GALONES']);

        foreach ($data as $r) {
            $precioGalon = ((float)$r['galones'] > 0) ? ((float)$r['precio_total'] / (float)$r['galones']) : 0;

            fputcsv($out, [
                $r['estacion'],
                $r['fecha'],
                $r['placa'],
                $r['modelo'],
                round($precioGalon, 2),
                $r['odometro'],
                $r['precio_total'],
                $r['galones'],
            ]);
        }

        fclose($out);
        exit;
    }
}
