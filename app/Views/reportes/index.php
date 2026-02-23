<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Fleet Manager</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="public/css/orvion.css" rel="stylesheet">

    <style>
        .table-custom th { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; padding: 1rem; }
        .table-custom td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .hover-row:hover { background-color: #f8fafc; }
    </style>
</head>
<body class="bg-light">

<div class="layout-wrapper">

    <!-- SIDEBAR (igual a tus otras vistas) -->
    <aside class="sidebar bg-white shadow-sm" id="sidebar">
        <div class="sidebar-header border-bottom">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark fs-5" href="#">
                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-speedometer2"></i>
                </div>
                Fleet Manager
            </a>
            <button class="btn btn-light d-lg-none ms-auto border-0" onclick="toggleSidebar()"><i class="bi bi-x-lg"></i></button>
        </div>

        <nav class="sidebar-menu">
            <a href="?c=Dashboard" class="nav-link-custom"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            <a href="?c=Vehiculos" class="nav-link-custom"><i class="bi bi-car-front-fill"></i> Vehículos</a>
            <a href="?c=Reportes" class="nav-link-custom active"><i class="bi bi-file-earmark-bar-graph"></i> Reportes</a>
            <a href="#" class="nav-link-custom"><i class="bi bi-geo-alt"></i> Estaciones</a>
            <a href="#" class="nav-link-custom"><i class="bi bi-gear"></i> Configuración</a>
        </nav>

        <!-- PIE PERFIL -->
        <div class="p-3 mt-auto border-top bg-light">
            <div class="d-flex align-items-center gap-3">
                <a href="?c=Auth&a=profile" class="text-decoration-none">
                    <?php
                    $fotoPerfil = $_SESSION['user_photo'] ?? null;
                    $rutaFoto = "public/uploads/" . $fotoPerfil;
                    if ($fotoPerfil && file_exists($rutaFoto)): ?>
                        <img src="<?= $rutaFoto ?>" class="rounded-circle border shadow-sm object-fit-cover" style="width: 45px; height: 45px;" alt="Perfil">
                    <?php else: ?>
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center text-secondary border shadow-sm" style="width: 45px; height: 45px;">
                            <i class="bi bi-person-fill fs-4"></i>
                        </div>
                    <?php endif; ?>
                </a>

                <div class="overflow-hidden">
                    <a href="?c=Auth&a=profile" class="text-decoration-none text-dark">
                        <p class="mb-0 small fw-bold text-truncate" style="max-width: 140px;"><?= htmlspecialchars($user_name ?? 'Usuario') ?></p>
                    </a>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="?c=Auth&a=profile" class="x-small text-muted text-decoration-none" style="font-size: 0.75rem;">Mi Perfil</a>
                        <span class="text-muted x-small opacity-50">|</span>
                        <a href="?c=Auth&a=logout" class="x-small text-danger text-decoration-none fw-bold" style="font-size: 0.75rem;">Salir</a>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="main-content">

        <!-- TOPBAR -->
        <header class="topbar bg-white shadow-sm sticky-top d-flex justify-content-between align-items-center px-4" style="height: 70px;">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none border-0" onclick="toggleSidebar()"><i class="bi bi-list fs-4"></i></button>
                <h5 class="fw-bold m-0 text-dark">Reportes</h5>
            </div>

            <?php
                $dlParams = http_build_query([
                    'c' => 'Reportes',
                    'a' => 'download',
                    'vehiculo_id' => $filters['vehiculo_id'] ?? '',
                    'from' => $filters['from'] ?? '',
                    'to' => $filters['to'] ?? '',
                    'station' => $filters['station'] ?? '',
                ]);
            ?>
            <a class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" href="?<?= $dlParams ?>">
                <i class="bi bi-download me-2"></i> Descargar Reporte
            </a>
        </header>

        <div class="p-4 fade-in">

            <!-- FILTROS -->
            <div class="bg-white p-3 rounded-4 shadow-sm mb-4">
                <form method="GET" class="row g-3 align-items-center">
                    <input type="hidden" name="c" value="Reportes">

                    <div class="col-12 col-lg-3">
                        <select name="vehiculo_id" class="form-select rounded-pill bg-light border-0">
                            <option value="">Seleccionar Vehículo</option>
                            <?php foreach (($vehiculos ?? []) as $v): ?>
                                <option value="<?= $v['id'] ?>" <?= (!empty($filters['vehiculo_id']) && (int)$filters['vehiculo_id'] === (int)$v['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(($v['placa'] ?? '')) ?> - <?= htmlspecialchars(($v['modelo'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-lg-3">
                        <input type="date" name="from" class="form-control rounded-pill bg-light border-0" value="<?= htmlspecialchars($filters['from'] ?? '') ?>">
                    </div>

                    <div class="col-12 col-lg-3">
                        <input type="date" name="to" class="form-control rounded-pill bg-light border-0" value="<?= htmlspecialchars($filters['to'] ?? '') ?>">
                    </div>

                    <div class="col-12 col-lg-2">
                        <select name="station" class="form-select rounded-pill bg-light border-0">
                            <option value="ALL">Todas las estaciones</option>
                            <?php foreach (($stations ?? []) as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>" <?= (($filters['station'] ?? '') === $s) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-lg-1 d-grid">
                        <button class="btn btn-primary rounded-pill fw-bold" type="submit">
                            <i class="bi bi-funnel me-1"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABLA -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="p-3 border-bottom bg-white">
                    <h6 class="m-0 fw-bold">Historial de Repostajes</h6>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                        <tr>
                            <th>Estación</th>
                            <th>Fecha</th>
                            <th>Vehículo</th>
                            <th class="text-end">Valor por Galón</th>
                            <th class="text-end">Odómetro</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Galones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (($rows ?? []) as $r): ?>
                            <?php
                                $precioGalon = ((float)$r['galones'] > 0) ? ((float)$r['precio_total'] / (float)$r['galones']) : 0;
                            ?>
                            <tr class="hover-row">
                                <td class="fw-medium text-dark"><?= htmlspecialchars($r['nombre_estacion']) ?></td>
                                <td><?= date('d M Y', strtotime($r['fecha'])) ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($r['placa']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($r['modelo']) ?></small>
                                </td>
                                <td class="text-end fw-bold">$ <?= number_format($precioGalon, 0, ',', '.') ?></td>
                                <td class="text-end"><?= number_format((float)$r['odometro'], 1, ',', '.') ?> km</td>
                                <td class="text-end fw-bold">$ <?= number_format((float)$r['precio_total'], 0, ',', '.') ?></td>
                                <td class="text-end"><?= number_format((float)$r['galones'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">No hay registros para esos filtros.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINACIÓN -->
                <div class="d-flex justify-content-between align-items-center p-3 border-top bg-white">
                    <small class="text-muted">
                        Mostrando <?= count($rows ?? []) ?> de <?= (int)($pagination['total_items'] ?? 0) ?> registros
                    </small>

                    <?php
                        $base = [
                            'c' => 'Reportes',
                            'vehiculo_id' => $filters['vehiculo_id'] ?? '',
                            'from' => $filters['from'] ?? '',
                            'to' => $filters['to'] ?? '',
                            'station' => $filters['station'] ?? '',
                        ];
                        $current = (int)($pagination['current'] ?? 1);
                        $total = (int)($pagination['total'] ?? 1);
                    ?>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 gap-1">
                            <li class="page-item <?= ($current <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link rounded-2 border-0 bg-light text-dark"
                                   href="?<?= http_build_query($base + ['page' => $current - 1]) ?>">Prev</a>
                            </li>

                            <li class="page-item disabled">
                                <span class="page-link border-0 bg-white"><?= $current ?></span>
                            </li>

                            <li class="page-item <?= ($current >= $total) ? 'disabled' : '' ?>">
                                <a class="page-link rounded-2 border-0 bg-light text-dark"
                                   href="?<?= http_build_query($base + ['page' => $current + 1]) ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar')?.classList.toggle('show');
    }
</script>
</body>
</html>
