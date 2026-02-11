<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Fleet Manager</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS (Orvion Style) -->
    <link href="public/css/orvion.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Leaflet CSS (Mapa) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F5F7FA; }
        .card-kpi { border: none; border-radius: 12px; transition: transform 0.2s; }
        .card-kpi:hover { transform: translateY(-3px); }
        .nav-pills .nav-link.active { background-color: #111; color: #fff; }
        .nav-pills .nav-link { color: #666; font-weight: 500; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        /* Mapa Principal */
        #map { height: 350px; width: 100%; border-radius: 12px; z-index: 1; }
    </style>
</head>
<body class="bg-light">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top border-bottom py-3 px-4 shadow-sm">
        <div class="container-fluid">
            <!-- Logo / Brand -->
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark" href="#">
                <div class="bg-dark text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-speedometer2"></i>
                </div>
                Fleet Manager
            </a>

            <!-- Toggle Mobile -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Content -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto align-items-center gap-3">
                    <!-- Selector de Vehículo (Si tiene varios) -->
                    <?php if (count($mis_vehiculos) > 1): ?>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle border rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-car-front-fill me-1"></i> 
                                <?= htmlspecialchars($vehiculo_actual['modelo']) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                                <?php foreach ($mis_vehiculos as $v): ?>
                                <li>
                                    <a class="dropdown-item py-2 small <?= ($v['id'] == $vehiculo_actual['id']) ? 'active bg-light text-dark fw-bold' : '' ?>" 
                                       href="?c=Dashboard&v=<?= $v['id'] ?>">
                                        <?= htmlspecialchars($v['modelo']) ?> 
                                        <span class="text-muted ms-1">(<?= htmlspecialchars($v['placa'] ?? '') ?>)</span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>

                    <!-- Botón Agregar Vehículo -->
                    <li class="nav-item">
                        <button class="btn btn-dark btn-sm rounded-pill px-3 d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalVehiculo">
                            <i class="bi bi-plus-lg"></i> <span>Nuevo Vehículo</span>
                        </button>
                    </li>
                    
                    <div class="vr h-50 my-auto text-secondary d-none d-lg-block"></div>

                    <!-- Menú de Usuario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center text-secondary border shadow-sm" style="width: 38px; height: 38px;">
                                <i class="bi bi-person-fill fs-5"></i>
                            </div>
                            <span class="d-none d-md-block small fw-bold text-dark">
                                <?= htmlspecialchars($user_name ?? 'Mi Cuenta') ?>
                            </span>
                        </a>
                        
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2" style="min-width: 200px;">
                            <li>
                                <div class="px-3 py-2">
                                    <p class="mb-0 small fw-bold text-dark">Hola, <?= htmlspecialchars($user_name ?? 'Usuario') ?></p>
                                    <p class="mb-0 x-small text-muted" style="font-size: 0.75rem;">Gestiona tu cuenta</p>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item rounded-3 py-2" href="?c=Auth&a=profile">
                                    <i class="bi bi-person-badge me-2 text-primary"></i> Mi Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 py-2 text-danger" href="?c=Auth&a=logout">
                                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container-fluid px-4 py-4">
        
                        <!-- ENCABEZADO: Info del Vehículo -->
        <?php if ($vehiculo_actual): ?>
        <div class="row align-items-center mb-4 g-3">
            <!-- Columna Info (Adaptable) -->
            <div class="col-12 col-md-8">
                <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3 text-center text-md-start">
                    
                    <!-- Foto Vehículo -->
                    <div class="bg-white p-1 rounded-4 shadow-sm border position-relative" style="width: 80px; height: 80px; flex-shrink: 0;">
                        <?php if (!empty($vehiculo_actual['foto'])): ?>
                            <img src="uploads/<?= htmlspecialchars($vehiculo_actual['foto']) ?>" class="w-100 h-100 object-fit-cover rounded-3" alt="Vehículo">
                        <?php else: ?>
                            <div class="w-100 h-100 bg-light rounded-3 d-flex align-items-center justify-content-center text-secondary">
                                <i class="bi bi-car-front fs-1"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Info + Acciones -->
                    <div class="flex-grow-1 w-100">
                        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-2">
                            <h4 class="fw-bold mb-0 text-dark text-break" style="line-height: 1.2;">
                                <?= htmlspecialchars($vehiculo_actual['modelo']) ?>
                            </h4>
                            
                            <!-- ACCIONES (Botones juntos) -->
                            <div class="d-flex align-items-center gap-1 bg-white border rounded-pill px-2 py-1 shadow-sm ms-1">
                                <button class="btn btn-link btn-sm text-secondary p-1 border-0" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEditarVehiculo" 
                                        title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <div class="vr py-2 text-secondary opacity-25"></div>
                                <form action="?c=Dashboard&a=deleteVehicle" method="POST" class="d-inline" 
                                      onsubmit="return confirm('¿Eliminar <?= htmlspecialchars($vehiculo_actual['modelo']) ?>?');">
                                    <input type="hidden" name="id" value="<?= $vehiculo_actual['id'] ?>">
                                    <button type="submit" class="btn btn-link btn-sm text-danger p-1 border-0" title="Eliminar">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                            <span class="badge bg-dark text-white fw-normal px-2 py-1 rounded-2 font-monospace">
                                <?= htmlspecialchars($vehiculo_actual['placa'] ?? 'SIN PLACA') ?>
                            </span>
                            <span class="text-muted small border-start ps-2 text-truncate" style="max-width: 200px;">
                                <?= htmlspecialchars($vehiculo_actual['descripcion']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botón Registrar Tanqueada -->
            <div class="col-12 col-md-4 text-center text-md-end">
                <button class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-bold w-100 w-md-auto d-inline-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#modalLog">
                    <i class="bi bi-fuel-pump-fill"></i>
                    <span>Registrar Tanqueada</span>
                </button>
            </div>
        </div>


        <!-- KPIs (Tarjetas de Resumen) -->
        <div class="row g-3 mb-4">
            <!-- 1. Rendimiento Promedio -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-kpi bg-white shadow-sm h-100 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-speedometer"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small mb-0 fw-bold text-uppercase">Rendimiento</h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <?= number_format($stats['promedio_rend'], 1) ?> 
                                <span class="fs-6 text-secondary fw-normal"><?= $vehiculo_actual['unidad_consumo'] ?></span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Rango Estimado -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-kpi bg-white shadow-sm h-100 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-signpost-split"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small mb-0 fw-bold text-uppercase">Rango Estimado</h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                <?= number_format($stats['rango_estimado'], 0, ',', '.') ?>
                                <span class="fs-6 text-secondary fw-normal">km</span>
                            </h3>
                            <small class="text-muted x-small">con tanque lleno</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Gasto Mes Actual -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-kpi bg-white shadow-sm h-100 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small mb-0 fw-bold text-uppercase">Gasto (<?= $stats['mes_nombre'] ?>)</h6>
                            <h3 class="fw-bold mb-0 text-dark">
                                $ <?= number_format($stats['gasto_mes'], 0, ',', '.') ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Última Estación -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-kpi bg-white shadow-sm h-100 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="text-muted small mb-0 fw-bold text-uppercase">Última Estación</h6>
                            <h5 class="fw-bold mb-0 text-dark text-truncate" title="<?= !empty($logs) ? $logs[0]['nombre_estacion'] : '-' ?>">
                                <?= !empty($logs) ? $logs[0]['nombre_estacion'] : '-' ?>
                            </h5>
                            <small class="text-muted x-small">
                                <?= !empty($logs) ? date('d M Y', strtotime($logs[0]['fecha'])) : '' ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN GRÁFICOS Y MAPA -->
        <div class="row g-4 mb-4">
            <!-- Gráfica de Rendimiento (Línea) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up me-2"></i>Rendimiento</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <canvas id="rendimientoChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Gráfica de Gastos (Barras) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-fill me-2"></i>Gasto Mensual</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <canvas id="gastosChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Mapa de Estaciones -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-map me-2"></i>Ruta</h6>
                    </div>
                    <div class="card-body p-0 position-relative">
                        <div id="map" class="h-100" style="min-height: 250px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE REGISTROS -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-ul me-2"></i>Registros Recientes</h6>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="ps-4 text-muted fw-bold small text-uppercase" style="font-size: 0.75rem;">Fecha</th>
                            <th class="text-muted fw-bold small text-uppercase" style="font-size: 0.75rem;">Estación</th>
                            <th class="text-end text-muted fw-bold small text-uppercase" style="font-size: 0.75rem;">Odómetro</th>
                            <th class="text-end text-muted fw-bold small text-uppercase" style="font-size: 0.75rem;">Carga</th>
                            <th class="text-end text-muted fw-bold small text-uppercase" style="font-size: 0.75rem;">$/Gal</th>
                            <th class="text-end text-muted fw-bold small text-uppercase" style="font-size: 0.75rem;">Total</th>
                            <th class="text-end pe-4 text-muted fw-bold small text-uppercase" style="font-size: 0.75rem;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $row): ?>
                        <tr class="align-middle border-bottom border-light">
                            <!-- Fecha -->
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                    <?= date('d M', strtotime($row['fecha'])) ?>
                                    <span class="text-muted small ms-1">'<?= date('y', strtotime($row['fecha'])) ?></span>
                                </div>
                            </td>

                            <!-- Estación -->
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2 text-danger" 
                                         style="width: 24px; height: 24px;">
                                        <i class="bi bi-geo-alt-fill" style="font-size: 10px;"></i>
                                    </div>
                                    <span class="text-dark" style="font-size: 0.9rem;">
                                        <?= htmlspecialchars($row['nombre_estacion']) ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Odómetro -->
                            <td class="text-end py-3 font-monospace text-dark" style="font-size: 0.95rem;">
                                <?= number_format($row['odometro'], 1, ',', '.') ?>
                            </td>

                            <!-- Carga (Galones) -->
                            <td class="text-end py-3 font-monospace text-dark" style="font-size: 0.95rem;">
                                <?= number_format($row['galones'], 2, ',', '.') ?>
                            </td>
                            
                            <!-- Precio por Galón -->
                            <td class="text-end py-3 font-monospace text-muted fw-bold" style="font-size: 0.85rem;">
                                <?php 
                                    $precioGalon = ($row['galones'] > 0) ? ($row['precio_total'] / $row['galones']) : 0;
                                    echo '$ ' . number_format($precioGalon, 0, ',', '.');
                                ?>
                            </td>

                            <!-- Total -->
                            <td class="text-end py-3 fw-bold text-dark" style="font-size: 0.95rem;">
                                $ <?= number_format($row['precio_total'], 0, ',', '.') ?>
                            </td>

                            <!-- Acciones -->
                            <td class="text-end pe-4 py-3">
                                <form action="?c=Dashboard&a=deleteLog" method="POST" class="d-inline" onsubmit="return confirm('¿Borrar este registro?');">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="vehicle_id" value="<?= $vehiculo_actual['id'] ?>">
                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0 border-0 bg-transparent" title="Eliminar">
                                        <i class="bi bi-trash3" style="font-size: 1rem;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted small">
                                <i class="bi bi-journal-x mb-2 d-block fs-4"></i>
                                No hay registros en esta página
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if (isset($pagination) && $pagination['total'] > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-3 px-4 pb-4 border-top pt-3">
                <div class="small text-muted fw-bold" style="font-size: 0.8rem;">
                    Página <?= $pagination['current'] ?> de <?= $pagination['total'] ?>
                </div>
                
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Botón Anterior -->
                        <li class="page-item <?= ($pagination['current'] <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link border rounded-start-pill px-3 py-1 fw-bold text-secondary" 
                               href="?c=Dashboard&v=<?= $pagination['v_id'] ?>&page=<?= $pagination['current'] - 1 ?>"
                               style="font-size: 0.8rem;">
                               <i class="bi bi-chevron-left me-1"></i> Anterior
                            </a>
                        </li>

                        <!-- Botón Siguiente -->
                        <li class="page-item <?= ($pagination['current'] >= $pagination['total']) ? 'disabled' : '' ?>">
                            <a class="page-link border rounded-end-pill px-3 py-1 fw-bold text-secondary ms-1" 
                               href="?c=Dashboard&v=<?= $pagination['v_id'] ?>&page=<?= $pagination['current'] + 1 ?>"
                               style="font-size: 0.8rem;">
                               Siguiente <i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

        </div>
        
        <?php else: ?>
        <!-- Vista vacía si no hay vehículo -->
        <div class="text-center py-5">
            <h2 class="fw-bold text-secondary">Bienvenido a Fleet Manager</h2>
            <p class="text-muted">Para comenzar, registra tu primer vehículo.</p>
            <button class="btn btn-dark rounded-pill px-4 mt-3" data-bs-toggle="modal" data-bs-target="#modalVehiculo">
                Registrar Vehículo
            </button>
        </div>
        <?php endif; ?>

    </div>

    <!-- MODAL NUEVO VEHÍCULO -->
    <div class="modal fade" id="modalVehiculo" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Nuevo Vehículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="?c=Dashboard&a=saveVehicle" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">PLACA / MATRÍCULA</label>
                            <input type="text" name="placa" class="form-control rounded-3" required placeholder="Ej: ABC-123" style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">MODELO / NOMBRE</label>
                            <input type="text" name="modelo" class="form-control rounded-3" required placeholder="Ej: Mazda 3">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">CAP. TANQUE (Gal)</label>
                                <input type="number" step="0.1" name="capacidad_tanque" class="form-control rounded-3" required value="12">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">UNIDAD</label>
                                <select name="unidad_consumo" class="form-select rounded-3">
                                    <option value="km/gal">km/gal</option>
                                    <option value="km/l">km/l</option>
                                    <option value="mpg">mpg</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">FOTO (Opcional)</label>
                            <input type="file" name="foto" class="form-control rounded-3" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-dark w-100 rounded-pill py-2 fw-bold">Guardar Vehículo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR VEHÍCULO (LIMPIO) -->
    <div class="modal fade" id="modalEditarVehiculo" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Editar Vehículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="?c=Dashboard&a=editVehicle" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $vehiculo_actual['id'] ?? '' ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">PLACA / MATRÍCULA</label>
                            <input type="text" name="placa" class="form-control rounded-3" required 
                                   value="<?= htmlspecialchars($vehiculo_actual['placa'] ?? '') ?>" 
                                   style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">MODELO / NOMBRE</label>
                            <input type="text" name="modelo" class="form-control rounded-3" required 
                                   value="<?= htmlspecialchars($vehiculo_actual['modelo'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">DESCRIPCIÓN</label>
                            <input type="text" name="descripcion" class="form-control rounded-3" 
                                   value="<?= htmlspecialchars($vehiculo_actual['descripcion'] ?? '') ?>">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">CAP. TANQUE</label>
                                <input type="number" step="0.1" name="capacidad_tanque" class="form-control rounded-3" required 
                                       value="<?= $vehiculo_actual['capacidad_tanque'] ?? 12 ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">UNIDAD</label>
                                <select name="unidad_consumo" class="form-select rounded-3">
                                    <option value="km/gal" <?= ($vehiculo_actual['unidad_consumo'] ?? '') == 'km/gal' ? 'selected' : '' ?>>km/gal</option>
                                    <option value="km/l" <?= ($vehiculo_actual['unidad_consumo'] ?? '') == 'km/l' ? 'selected' : '' ?>>km/l</option>
                                    <option value="mpg" <?= ($vehiculo_actual['unidad_consumo'] ?? '') == 'mpg' ? 'selected' : '' ?>>mpg</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">CAMBIAR FOTO (Opcional)</label>
                            <input type="file" name="foto" class="form-control rounded-3" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-dark w-100 rounded-pill py-2 fw-bold">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL REGISTRO TANQUEADA -->
    <div class="modal fade" id="modalLog" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Registrar Tanqueada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="?c=Dashboard&a=saveLog" method="POST">
                        <input type="hidden" name="vehicle_id" value="<?= $vehiculo_actual['id'] ?? '' ?>">
                        
                        <!-- Campos Ocultos para Geo -->
                        <input type="hidden" name="latitud" id="lat">
                        <input type="hidden" name="longitud" id="lng">
                        <input type="hidden" name="nombre_estacion" id="estacion_nombre">

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">FECHA</label>
                                <input type="datetime-local" name="fecha" class="form-control rounded-3" required 
                                       value="<?= date('Y-m-d\TH:i') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">ODÓMETRO (km)</label>
                                <input type="number" step="0.1" name="odometro" class="form-control rounded-3 fw-bold" required placeholder="00000">
                            </div>
                        </div>

                        <!-- MAPA INTERACTIVO -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">UBICACIÓN</label>
                            <div id="mapPicker" style="height: 250px; width: 100%; border-radius: 8px; border: 1px solid #ddd;"></div>
                            <small class="text-muted d-block mt-1">Arrastra el marcador para ajustar la ubicación</small>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <label class="form-label small fw-bold text-muted">GALONES</label>
                                <input type="number" step="0.01" name="galones" class="form-control rounded-3" required placeholder="0.00">
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold text-muted">TOTAL ($)</label>
                                <input type="number" step="100" name="precio_total" class="form-control rounded-3" required placeholder="$ 0">
                            </div>
                            <div class="col-4 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="full" value="1" id="checkFull" checked>
                                    <label class="form-check-label small" for="checkFull">
                                        Tanque Lleno
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alerta Geo -->
                        <div id="geo-status" class="alert alert-light border small py-2 d-flex align-items-center gap-2 text-muted mb-4">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                            Obteniendo ubicación...
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">Guardar Registro</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // 1. GEO-LOCALIZACIÓN AUTOMÁTICA
        let mapPicker;
        let markerPicker;

        document.getElementById('modalLog').addEventListener('shown.bs.modal', function () {
            if (!mapPicker) {
                mapPicker = L.map('mapPicker').setView([4.6097, -74.0817], 13);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(mapPicker);
                markerPicker = L.marker([4.6097, -74.0817], {draggable: true}).addTo(mapPicker);
                markerPicker.on('dragend', function(e) {
                    const pos = e.target.getLatLng();
                    document.getElementById('lat').value = pos.lat.toFixed(6);
                    document.getElementById('lng').value = pos.lng.toFixed(6);
                    reverseGeocode(pos.lat, pos.lng);
                });
            }
            setTimeout(() => { mapPicker.invalidateSize(); }, 200);
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        document.getElementById('lat').value = lat;
                        document.getElementById('lng').value = lng;
                        mapPicker.setView([lat, lng], 15);
                        markerPicker.setLatLng([lat, lng]);
                        reverseGeocode(lat, lng);
                    },
                    (error) => {
                        const status = document.getElementById('geo-status');
                        status.className = "alert alert-warning border-warning small py-2 mb-3";
                        status.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Ubicación no permitida. Arrastra el marcador en el mapa.`;
                    }
                );
            }
        });

        function reverseGeocode(lat, lng) {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`;
            fetch(url).then(r => r.json()).then(data => {
                let name = data.address.amenity || data.address.shop || data.address.road || "Ubicación Actual";
                if(data.address.brand) name = data.address.brand + " " + name;
                document.getElementById('estacion_nombre').value = name;
                const status = document.getElementById('geo-status');
                status.className = "alert alert-success border-success small py-2 mb-3";
                status.innerHTML = `<i class="bi bi-geo-alt-fill"></i> ${name}`;
            }).catch(() => {
                document.getElementById('estacion_nombre').value = "Estación Desconocida";
            });
        }

        // 2. GRÁFICA DE RENDIMIENTO
        const ctxRend = document.getElementById('rendimientoChart').getContext('2d');
        new Chart(ctxRend, {
            type: 'line',
            data: {
                labels: <?= json_encode($charts['fechas']) ?>,
                datasets: [{
                    label: 'Rendimiento (km/gal)',
                    data: <?= json_encode($charts['rendimiento']) ?>,
                    borderColor: '#111', backgroundColor: 'rgba(0,0,0,0.05)', borderWidth: 2, pointBackgroundColor: '#fff', pointBorderColor: '#111', pointRadius: 4, tension: 0.4, fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: false, grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } } }
        });

        // 3. GRÁFICA DE GASTOS
        const ctxGastos = document.getElementById('gastosChart').getContext('2d');
        new Chart(ctxGastos, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($charts['gasto_mensual'])) ?>,
                datasets: [{ label: 'Gasto Mensual ($)', data: <?= json_encode(array_values($charts['gasto_mensual'])) ?>, backgroundColor: '#111', borderRadius: 4 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } } }
        });

        // 4. MAPA
        const mapData = <?= json_encode($charts['mapa']) ?>;
        if (mapData.length > 0) {
            const lastPoint = mapData[0];
            const map = L.map('map').setView([lastPoint.lat, lastPoint.lng], 12);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);
            const blackIcon = L.icon({ iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-black.png', shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png', iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41] });
            mapData.forEach(point => { L.marker([point.lat, point.lng], {icon: blackIcon}).addTo(map).bindPopup(`<b>${point.name}</b>`); });
        } else {
            const map = L.map('map').setView([4.6097, -74.0817], 11);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(map);
        }
    </script>
</body>
</html>
