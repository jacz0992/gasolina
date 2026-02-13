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
    <!-- Custom CSS -->
    <link href="public/css/orvion.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <style>
        .hover-bg-light:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
        .hover-opacity-100:hover { opacity: 1 !important; }
        .table td { vertical-align: middle; }
    </style>
</head>
<body class="bg-light">

<div class="layout-wrapper">
    
    <!-- 1. SIDEBAR -->
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
            <a href="#" class="nav-link-custom active">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <a href="?c=Vehiculos" class="nav-link-custom">
                <i class="bi bi-car-front-fill"></i> Vehículos
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-file-earmark-bar-graph"></i> Reportes
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-geo-alt"></i> Estaciones
            </a>
            <a href="#" class="nav-link-custom">
                <i class="bi bi-gear"></i> Configuración
            </a>
        </nav>

                <!-- PIE DEL SIDEBAR (PERFIL) -->
        <div class="p-3 mt-auto border-top bg-light">
             <div class="d-flex align-items-center gap-3">
                
                <!-- FOTO DE PERFIL -->
                <a href="?c=Auth&a=profile" class="text-decoration-none">
                    <?php 
                    // Verificamos si existe foto en la sesión (o variable pasada) y si el archivo físico existe
                    $fotoPerfil = $_SESSION['user_photo'] ?? null; 
                    $rutaFoto = "public/uploads/" . $fotoPerfil;
                    
                    if ($fotoPerfil && file_exists($rutaFoto)): 
                    ?>
                        <img src="<?= $rutaFoto ?>" 
                             class="rounded-circle border shadow-sm object-fit-cover" 
                             style="width: 45px; height: 45px;" 
                             alt="Perfil">
                    <?php else: ?>
                        <!-- Avatar por defecto si no hay foto -->
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center text-secondary border shadow-sm" style="width: 45px; height: 45px;">
                            <i class="bi bi-person-fill fs-4"></i>
                        </div>
                    <?php endif; ?>
                </a>
                
                <!-- Info Usuario -->
                <div class="overflow-hidden">
                    <a href="?c=Auth&a=profile" class="text-decoration-none text-dark">
                        <p class="mb-0 small fw-bold text-truncate hover-text-primary" style="max-width: 120px;">
                            <?= htmlspecialchars($user_name ?? 'Usuario') ?>
                        </p>
                    </a>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="?c=Auth&a=profile" class="x-small text-muted text-decoration-none fw-medium" style="font-size: 0.75rem;">Mi Perfil</a>
                        <span class="text-muted x-small opacity-50">|</span>
                        <a href="?c=Auth&a=logout" class="x-small text-danger text-decoration-none fw-bold" style="font-size: 0.75rem;">Salir</a>
                    </div>
                </div>
            </div>
        </div>


    </aside>

    <!-- 2. MAIN CONTENT -->
    <main class="main-content">
        
        <!-- TOPBAR -->
        <header class="topbar bg-white shadow-sm sticky-top" style="height: 70px; z-index: 1020;">
                        <!-- Izquierda: Título y Configuración -->
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none border-0" onclick="toggleSidebar()"><i class="bi bi-list fs-4"></i></button>
                
                <?php if ($vehiculo_actual): ?>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Info del Vehículo -->
                        <div>
                            <h5 class="fw-bold m-0 text-dark"><?= htmlspecialchars($vehiculo_actual['modelo']) ?></h5>
                            <small class="text-muted d-block" style="line-height: 1; font-size: 0.75rem;">
                                <?= htmlspecialchars($vehiculo_actual['placa']) ?>
                            </small>
                        </div>

                        <!-- MENÚ DE CONFIGURACIÓN (Engranaje) -->
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-1 border-0 opacity-50 hover-opacity-100" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Configurar Vehículo">
                                <i class="bi bi-gear-fill" style="font-size: 1.1rem;"></i>
                            </button>
                            <ul class="dropdown-menu shadow-lg border-0 rounded-4 p-2">
                                <li><h6 class="dropdown-header x-small text-uppercase fw-bold">Acciones</h6></li>
                                <li>
                                    <a class="dropdown-item small rounded-2 py-2" href="#" data-bs-toggle="modal" data-bs-target="#modalEditarVehiculo">
                                        <i class="bi bi-pencil me-2 text-primary"></i> Editar Información
                                    </a>
                                </li>
                                <li>
                                    <form action="?c=Dashboard&a=deleteVehicle" method="POST" onsubmit="return confirm('¿Estás SEGURO de eliminar este vehículo?\nSe borrarán todos sus datos.');">
                                        <input type="hidden" name="id" value="<?= $vehiculo_actual['id'] ?>">
                                        <button type="submit" class="dropdown-item small rounded-2 py-2 text-danger">
                                            <i class="bi bi-trash me-2"></i> Eliminar Vehículo
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <h5 class="fw-bold m-0 text-dark">Dashboard</h5>
                <?php endif; ?>
            </div>


            <!-- Derecha: Selector y Acciones -->
            <div class="d-flex align-items-center gap-3">
                <?php if ($vehiculo_actual): ?>
                    <!-- Selector de Vehículo -->
                                        <!-- Selector y Opciones de Vehículo -->
                                <!-- Derecha: Selector y Registrar -->
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($vehiculo_actual): ?>
                    <!-- Selector de Vehículo -->
                    <div class="dropdown">
                        <button class="btn btn-light bg-light border-0 rounded-pill px-3 py-2 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" title="Cambiar Vehículo">
                            <i class="bi bi-car-front-fill text-secondary"></i>
                            <span class="d-none d-lg-inline small fw-bold text-secondary">Cambiar</span>
                            <i class="bi bi-chevron-down ms-1 small text-muted" style="font-size: 0.7rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2" style="min-width: 220px;">
                            <li class="px-2 py-1"><small class="text-muted fw-bold text-uppercase x-small">Mis Vehículos</small></li>
                            <?php foreach ($mis_vehiculos as $v): ?>
                            <li>
                                <a class="dropdown-item py-2 rounded-2 small <?= ($v['id'] == $vehiculo_actual['id']) ? 'active bg-primary text-white fw-bold' : '' ?>" 
                                   href="?c=Dashboard&v=<?= $v['id'] ?>">
                                    <?= htmlspecialchars($v['modelo']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 small text-primary fw-bold rounded-2" href="#" data-bs-toggle="modal" data-bs-target="#modalVehiculo">
                                    <i class="bi bi-plus-lg me-2"></i> Nuevo Vehículo
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                <?php endif; ?>
            </div>



                    <!-- Botón Registrar -->
                    <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" 
                            style="background-color: #2563EB; border: none;"
                            data-bs-toggle="modal" data-bs-target="#modalLog">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Registrar</span>
                    </button>
                <?php endif; ?>
            </div>
        </header>

        <!-- DASHBOARD BODY -->
        <div class="p-3 p-md-4 fade-in" style="padding-bottom: 4rem !important;">
            <?php if ($vehiculo_actual): ?>

            <!-- Info Móvil -->
            <div class="d-md-none text-center mb-4 mt-2">
                 <div class="position-relative d-inline-block mb-2">
                    <?php if (!empty($vehiculo_actual['foto'])): ?>
                        <img src="uploads/<?= htmlspecialchars($vehiculo_actual['foto']) ?>" class="rounded-circle shadow-sm object-fit-cover" style="width: 80px; height: 80px; border: 3px solid white;" alt="Vehículo">
                    <?php else: ?>
                         <div class="bg-white rounded-circle d-flex align-items-center justify-content-center text-secondary border shadow-sm mx-auto" style="width: 80px; height: 80px;">
                            <i class="bi bi-car-front fs-2"></i>
                        </div>
                    <?php endif; ?>
                    <button class="btn btn-light btn-sm rounded-circle shadow-sm position-absolute bottom-0 end-0 border" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#modalEditarVehiculo">
                        <i class="bi bi-pencil-fill small text-secondary"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-center gap-2 align-items-center mt-2">
                     <span class="badge bg-light text-dark border px-3 py-1 rounded-pill small font-monospace"><?= htmlspecialchars($vehiculo_actual['placa']) ?></span>
                </div>
            </div>

            <!-- 1. TARJETAS KPI -->
            <div class="row g-3 mb-4">
                <!-- Rendimiento -->
                <div class="col-6 col-xl-3">
                    <div class="card-widget p-3 h-100 d-flex flex-column justify-content-between shadow-sm border-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="icon-box blue bg-opacity-10 rounded-circle" style="width: 36px; height: 36px; font-size: 1rem;"><i class="bi bi-fuel-pump"></i></div>
                            <span class="text-muted x-small fw-bold text-uppercase">Rendimiento</span>
                        </div>
                        <div>
                            <h3 class="fw-bold text-dark mb-0 fs-4"><?= number_format($stats['promedio_rend'], 1) ?></h3>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?= $vehiculo_actual['unidad_consumo'] ?></small>
                                <?php if(!empty($stats['tendencia_rend'])): ?>
                                    <small class="<?= $stats['tendencia_rend'] >= 0 ? 'text-success' : 'text-danger' ?> fw-bold x-small">
                                        <i class="bi bi-arrow-<?= $stats['tendencia_rend'] >= 0 ? 'up' : 'down' ?>"></i> 
                                        <?= abs(round($stats['tendencia_rend'])) ?>%
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Rango -->
                <div class="col-6 col-xl-3">
                    <div class="card-widget p-3 h-100 d-flex flex-column justify-content-between shadow-sm border-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="icon-box green bg-opacity-10 rounded-circle" style="width: 36px; height: 36px; font-size: 1rem;"><i class="bi bi-speedometer2"></i></div>
                            <span class="text-muted x-small fw-bold text-uppercase">Rango</span>
                        </div>
                        <div>
                            <h3 class="fw-bold text-dark mb-0 fs-4"><?= number_format($stats['rango_estimado'], 0) ?></h3>
                            <small class="text-muted">km est.</small>
                        </div>
                    </div>
                </div>
                <!-- Gasto -->
                <div class="col-6 col-xl-3">
                    <div class="card-widget p-3 h-100 d-flex flex-column justify-content-between shadow-sm border-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="icon-box orange bg-opacity-10 rounded-circle" style="width: 36px; height: 36px; font-size: 1rem;"><i class="bi bi-wallet2"></i></div>
                            <span class="text-muted x-small fw-bold text-uppercase">Gasto Mes</span>
                        </div>
                        <div>
                            <h3 class="fw-bold text-dark mb-0 fs-5">$<?= number_format($stats['gasto_mes'], 0, ',', '.') ?></h3>
                            <?php if(!empty($stats['tendencia_gasto'])): ?>
                                <small class="<?= $stats['tendencia_gasto'] <= 0 ? 'text-success' : 'text-danger' ?> fw-bold d-block x-small mt-1">
                                    <i class="bi bi-arrow-<?= $stats['tendencia_gasto'] > 0 ? 'up' : 'down' ?>"></i> 
                                    <?= abs(round($stats['tendencia_gasto'])) ?>% vs mes ant.
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Última -->
                <div class="col-6 col-xl-3">
                    <div class="card-widget p-3 h-100 d-flex flex-column justify-content-between shadow-sm border-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="icon-box red bg-opacity-10 rounded-circle" style="width: 36px; height: 36px; font-size: 1rem;"><i class="bi bi-geo-alt"></i></div>
                            <span class="text-muted x-small fw-bold text-uppercase">Última</span>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 text-truncate small"><?= !empty($logs) ? $logs[0]['nombre_estacion'] : '-' ?></h6>
                            <small class="text-muted x-small"><?= !empty($logs) ? date('d M', strtotime($logs[0]['fecha'])) : '-' ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. GRÁFICOS Y MAPA -->
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="card-widget border-0 shadow-sm h-100">
                        <h6 class="fw-bold mb-3 small text-uppercase text-muted ls-1">Rendimiento Histórico</h6>
                        <div style="position: relative; height: 200px;">
                            <canvas id="rendimientoChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-widget border-0 shadow-sm h-100">
                        <h6 class="fw-bold mb-3 small text-uppercase text-muted ls-1">Gastos Mensuales</h6>
                        <div style="position: relative; height: 200px;">
                            <canvas id="gastosChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-widget border-0 shadow-sm p-0 overflow-hidden h-100 position-relative" style="min-height: 240px;">
                        <div id="map" class="w-100 h-100"></div>
                    </div>
                </div>
            </div>

            <!-- 3. HISTORIAL DE REPOSTAJES -->
            <div class="card-widget p-0 overflow-hidden border-0 shadow-sm mb-4 h-auto">
                
                <!-- ENCABEZADO CON FILTROS FUNCIONALES -->
                <div class="d-flex flex-wrap justify-content-between align-items-center px-4 py-3 border-bottom bg-white gap-3">
                    <h6 class="fw-bold mb-0 text-dark small text-uppercase ls-1">HISTORIAL</h6>
                    
                    <!-- Formulario Filtro Fechas -->
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="c" value="Dashboard">
                        <input type="hidden" name="v" value="<?= $vehiculo_actual['id'] ?>">
                        
                        <div class="input-group input-group-sm" style="width: auto;">
                            <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-calendar-event"></i></span>
                            <input type="date" name="from" class="form-control border-0 bg-light text-muted small fw-bold" 
                                   style="max-width: 130px;" 
                                   value="<?= htmlspecialchars($_GET['from'] ?? date('Y-m-01')) ?>">
                        </div>
                        <span class="text-muted small">-</span>
                        <div class="input-group input-group-sm" style="width: auto;">
                            <input type="date" name="to" class="form-control border-0 bg-light text-muted small fw-bold" 
                                   style="max-width: 130px;" 
                                   value="<?= htmlspecialchars($_GET['to'] ?? date('Y-m-d')) ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-sm btn-light border fw-bold text-primary">Filtrar</button>
                        
                        <?php if(isset($_GET['from'])): ?>
                            <a href="?c=Dashboard&v=<?= $vehiculo_actual['id'] ?>" class="btn btn-sm text-muted" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 border-0 text-muted x-small fw-bold text-uppercase" style="width: 45%;">ESTACIÓN</th>
                                <th class="py-3 border-0 text-muted x-small fw-bold text-uppercase text-center">FECHA</th>
                                <th class="pe-4 py-3 border-0 text-muted x-small fw-bold text-uppercase text-end">COSTO TOTAL</th>
                                <th class="py-3 border-0 text-muted x-small fw-bold text-uppercase text-center" style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Lógica de comparación para flechas (Fila actual vs siguiente)
                            $logsLimitados = $logs; 
                            
                            foreach ($logsLimitados as $i => $row): 
                                $esOptimo = isset($row['rendimiento']) && $row['rendimiento'] > 30; 
                                
                                $flecha = null;
                                if (isset($logsLimitados[$i + 1])) {
                                    $costoAnterior = $logsLimitados[$i + 1]['precio_total'];
                                    if ($row['precio_total'] > $costoAnterior) $flecha = 'up'; // Subió (Rojo)
                                    elseif ($row['precio_total'] < $costoAnterior) $flecha = 'down'; // Bajó (Verde)
                                }
                            ?>
                            <tr class="hover-bg-light">
                                <td class="ps-4 py-3 border-bottom border-light">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center text-secondary" style="width: 42px; height: 42px;">
                                            <i class="bi bi-fuel-pump fs-5 text-muted"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block text-truncate" style="font-size: 0.95rem;">
                                                <?= htmlspecialchars($row['nombre_estacion']) ?>
                                            </span>
                                            <small class="text-muted d-md-none"><?= date('d M Y', strtotime($row['fecha'])) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 border-bottom border-light text-center d-none d-md-table-cell">
                                    <span class="text-muted fw-normal small">
                                        <?= date('d M Y', strtotime($row['fecha'])) ?>
                                    </span>
                                </td>
                                <td class="pe-4 py-3 border-bottom border-light text-end">
                                    <div class="fw-bold text-dark" style="font-size: 1rem;">$ <?= number_format($row['precio_total'], 0, ',', '.') ?></div>
                                    <div class="d-flex align-items-center justify-content-end gap-2 mt-1">
                                        <small class="text-muted x-small"><?= number_format($row['galones'], 1) ?> gal</small>
                                        <?php if($esOptimo): ?> 
                                            <i class="bi bi-check-circle-fill text-success" style="font-size: 0.85rem;" title="Consumo Óptimo"></i>
                                        <?php endif; ?>
                                        <?php if($flecha): ?>
                                            <i class="bi bi-arrow-<?= $flecha ?>-short fw-bold text-<?= $flecha == 'up' ? 'danger' : 'success' ?>" 
                                               style="font-size: 1rem;" 
                                               title="<?= $flecha == 'up' ? 'Subió costo' : 'Bajó costo' ?>">
                                            </i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 border-bottom border-light text-center">
                                    <form action="?c=Dashboard&a=deleteLog" method="POST" onsubmit="return confirm('¿Eliminar registro?');">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="vehicle_id" value="<?= $vehiculo_actual['id'] ?>">
                                        <button type="submit" class="btn btn-link p-0 text-danger opacity-50 hover-opacity-100" title="Eliminar">
                                            <i class="bi bi-trash3 fs-6"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($logs)): ?>
                            <tr><td colspan="4" class="text-center py-5 text-muted small">No hay registros en este rango de fechas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINACIÓN -->
            <?php if (isset($pagination) && $pagination['total'] > 1): ?>
            <div class="d-flex justify-content-end">
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item <?= ($pagination['current'] <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-3 border fw-bold text-secondary px-3" href="?c=Dashboard&v=<?= $pagination['v_id'] ?>&page=<?= $pagination['current'] - 1 ?>&from=<?= $pagination['from'] ?>&to=<?= $pagination['to'] ?>">Anterior</a>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link border-0 bg-transparent text-muted"><?= $pagination['current'] ?> / <?= $pagination['total'] ?></span>
                        </li>
                        <li class="page-item <?= ($pagination['current'] >= $pagination['total']) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-3 border fw-bold text-secondary px-3" href="?c=Dashboard&v=<?= $pagination['v_id'] ?>&page=<?= $pagination['current'] + 1 ?>&from=<?= $pagination['from'] ?>&to=<?= $pagination['to'] ?>">Siguiente</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

            <?php else: ?>
                <!-- Estado Vacío -->
                <div class="d-flex flex-column align-items-center justify-content-center text-center p-5" style="min-height: 60vh;">
                    <div class="bg-white p-5 rounded-circle shadow-sm mb-4">
                        <i class="bi bi-car-front fs-1 text-secondary opacity-25" style="font-size: 4rem !important;"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Bienvenido a Fleet Manager</h3>
                    <p class="text-muted mb-4 col-md-6 mx-auto">Comienza registrando tu primer vehículo.</p>
                    <button class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalVehiculo">
                        <i class="bi bi-plus-lg me-2"></i> Registrar Vehículo
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- ================= MODALES ================= -->

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
                        <label class="form-label small fw-bold text-muted">PLACA</label>
                        <input type="text" name="placa" class="form-control rounded-3" required style="text-transform: uppercase;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">MODELO</label>
                        <input type="text" name="modelo" class="form-control rounded-3" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">CAPACIDAD (Gal)</label>
                            <input type="number" step="0.1" name="capacidad_tanque" class="form-control rounded-3" required value="12">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">UNIDAD</label>
                            <select name="unidad_consumo" class="form-select rounded-3">
                                <option value="km/gal">km/gal</option>
                                <option value="km/l">km/l</option>
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

<!-- MODAL EDITAR VEHÍCULO -->
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
                        <label class="form-label small fw-bold text-muted">PLACA</label>
                        <input type="text" name="placa" class="form-control rounded-3" required value="<?= htmlspecialchars($vehiculo_actual['placa'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">MODELO</label>
                        <input type="text" name="modelo" class="form-control rounded-3" required value="<?= htmlspecialchars($vehiculo_actual['modelo'] ?? '') ?>">
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
                    <input type="hidden" name="latitud" id="lat">
                    <input type="hidden" name="longitud" id="lng">
                    <input type="hidden" name="nombre_estacion" id="estacion_nombre">

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">FECHA</label>
                            <input type="datetime-local" name="fecha" class="form-control rounded-3" required value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">ODÓMETRO (km)</label>
                            <input type="number" step="0.1" name="odometro" class="form-control rounded-3 fw-bold" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">UBICACIÓN</label>
                        <div id="mapPicker" style="height: 250px; width: 100%; border-radius: 8px; border: 1px solid #ddd;"></div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <label class="form-label small fw-bold text-muted">GALONES</label>
                            <input type="number" step="0.01" name="galones" class="form-control rounded-3" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold text-muted">TOTAL ($)</label>
                            <input type="number" step="100" name="precio_total" class="form-control rounded-3" required>
                        </div>
                        <div class="col-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="full" value="1" id="checkFull" checked>
                                <label class="form-check-label small" for="checkFull">Full</label>
                            </div>
                        </div>
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
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    // MAPA PICKER
    let mapPicker;
    let markerPicker;
    document.getElementById('modalLog').addEventListener('shown.bs.modal', function () {
        if (!mapPicker) {
            mapPicker = L.map('mapPicker').setView([4.6097, -74.0817], 13);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(mapPicker);
            markerPicker = L.marker([4.6097, -74.0817], {draggable: true}).addTo(mapPicker);
            markerPicker.on('dragend', function(e) {
                const pos = e.target.getLatLng();
                document.getElementById('lat').value = pos.lat.toFixed(6);
                document.getElementById('lng').value = pos.lng.toFixed(6);
            });
        }
        setTimeout(() => { mapPicker.invalidateSize(); }, 200);
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                document.getElementById('lat').value = lat;
                document.getElementById('lng').value = lng;
                mapPicker.setView([lat, lng], 15);
                markerPicker.setLatLng([lat, lng]);
            });
        }
    });

    // GRÁFICOS
    <?php if ($vehiculo_actual): ?>
        const ctxRend = document.getElementById('rendimientoChart').getContext('2d');
        new Chart(ctxRend, {
            type: 'line',
            data: {
                labels: <?= json_encode($charts['fechas']) ?>,
                datasets: [{
                    label: 'Rendimiento',
                    data: <?= json_encode($charts['rendimiento']) ?>,
                    borderColor: '#111', tension: 0.4, fill: false
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { x: { display: false } } }
        });

        const ctxGastos = document.getElementById('gastosChart').getContext('2d');
        new Chart(ctxGastos, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($charts['gasto_mensual'])) ?>,
                datasets: [{ label: 'Gasto', data: <?= json_encode(array_values($charts['gasto_mensual'])) ?>, backgroundColor: '#111', borderRadius: 4 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { x: { display: false } } }
        });

        // Mapa Dashboard
        const mapData = <?= json_encode($charts['mapa']) ?>;
        if (mapData.length > 0) {
            const lastPoint = mapData[0];
            const map = L.map('map').setView([lastPoint.lat, lastPoint.lng], 12);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(map);
            mapData.forEach(p => L.marker([p.lat, p.lng]).addTo(map).bindPopup(p.name));
        } else {
            const map = L.map('map').setView([4.6097, -74.0817], 11);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(map);
        }
    <?php endif; ?>
</script>
</body>
</html>
