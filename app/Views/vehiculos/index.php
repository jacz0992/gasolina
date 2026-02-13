<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehículos - Fleet Manager</title>
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tu CSS -->
    <link href="public/css/orvion.css" rel="stylesheet">
    
    <style>
        .vehicle-img { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; }
        .progress-thin { height: 6px; border-radius: 3px; background-color: #f1f5f9; }
        .progress-bar-custom { background-color: #3b82f6; border-radius: 3px; }
        .table-custom th { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; padding: 1rem; }
        .table-custom td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .btn-action { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; color: #94a3b8; transition: all 0.2s; }
        .btn-action:hover { background-color: #f1f5f9; color: #334155; }
        .hover-row:hover { background-color: #f8fafc; }

        /* --- MODO RESPONSIVO: TARJETAS EN MÓVIL --- */
        @media (max-width: 768px) {
            .table-custom thead { display: none; }
            .table-custom tbody, .table-custom tr, .table-custom td { display: block; width: 100%; }
            .table-custom tr { margin-bottom: 1rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); padding: 1rem; }
            .table-custom td { padding: 0.5rem 0; text-align: left; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
            .table-custom td:last-child { border-bottom: none; padding-top: 1rem; justify-content: center; }
            .table-custom td::before { content: attr(data-label); font-weight: 600; font-size: 0.75rem; color: #64748b; text-transform: uppercase; margin-right: 1rem; }
            .table-custom td:first-child::before, .table-custom td:last-child::before { display: none; }
            .vehicle-img { width: 56px; height: 56px; }
            .progress-thin { height: 8px; width: 100px; }
            .topbar { padding: 0 1rem !important; }
        }
    </style>
</head>
<body class="bg-light">

<!-- Overlay para cerrar menú móvil -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

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
            <a href="?c=Dashboard" class="nav-link-custom">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <a href="?c=Vehiculos" class="nav-link-custom active">
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
                    $fotoPerfil = $_SESSION['user_photo'] ?? null; 
                    $rutaFoto = "public/uploads/" . $fotoPerfil;
                    if ($fotoPerfil && file_exists($rutaFoto)): 
                    ?>
                        <img src="<?= $rutaFoto ?>" class="rounded-circle border shadow-sm object-fit-cover" style="width: 45px; height: 45px;" alt="Perfil">
                    <?php else: ?>
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center text-secondary border shadow-sm" style="width: 45px; height: 45px;"><i class="bi bi-person-fill fs-4"></i></div>
                    <?php endif; ?>
                </a>
                
                <!-- Info Usuario -->
                <div class="overflow-hidden">
                    <a href="?c=Auth&a=profile" class="text-decoration-none text-dark">
                        <p class="mb-0 small fw-bold text-truncate hover-text-primary" style="max-width: 120px;">
                            <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>
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

    <!-- MAIN CONTENT -->
    <main class="main-content">
        
        <!-- TOPBAR -->
        <header class="topbar bg-white shadow-sm sticky-top d-flex justify-content-between align-items-center px-4" style="height: 70px;">
            <div class="d-flex align-items-center gap-3">
                <!-- Botón Hamburguesa Móvil -->
                <button class="btn btn-light d-lg-none border-0" onclick="toggleSidebar()"><i class="bi bi-list fs-4"></i></button>
                <h5 class="fw-bold m-0 text-dark">Vehículos</h5>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-none d-sm-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalNuevoVehiculo">
                    <i class="bi bi-plus-lg"></i> Nuevo Vehículo
                </button>
                <button class="btn btn-primary btn-sm rounded-circle d-sm-none shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoVehiculo" style="width: 36px; height: 36px;">
                    <i class="bi bi-plus-lg"></i>
                </button>
                
                <!-- Foto Perfil Mini Topbar -->
                <div class="border-start ps-3 d-none d-md-block">
                     <?php if($fotoPerfil && file_exists($rutaFoto)): ?>
                        <img src="<?= $rutaFoto ?>" class="rounded-circle border" width="36" height="36">
                    <?php else: ?>
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-secondary border" style="width: 36px; height: 36px;"><i class="bi bi-person-fill"></i></div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="p-4 fade-in">
            
            <!-- BARRA DE BÚSQUEDA -->
            <div class="bg-white p-3 rounded-4 shadow-sm mb-4">
                <form action="" method="GET" class="position-relative">
                    <input type="hidden" name="c" value="Vehiculos">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" name="q" class="form-control border-0 bg-light ps-5 rounded-pill" 
                           placeholder="Buscar por modelo, placa..." value="<?= htmlspecialchars($pagination['q'] ?? '') ?>">
                </form>
            </div>

            <!-- TABLA DE VEHÍCULOS -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-transparent bg-md-white">
                <div class="table-responsive">
                    <table class="table table-custom mb-0 w-100">
                        <thead>
                            <tr>
                                <th class="ps-4">Vehículo</th>
                                <th>Placa</th>
                                <th>Rendimiento</th>
                                <th>Última Tanqueada</th>
                                <th>Rango Estimado</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehiculos as $v): ?>
                            <tr class="hover-row">
                                <!-- Columna Vehículo -->
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if(!empty($v['foto'])): ?>
                                            <img src="uploads/<?= $v['foto'] ?>" class="vehicle-img shadow-sm object-fit-cover">
                                        <?php else: ?>
                                            <div class="vehicle-img bg-light d-flex align-items-center justify-content-center text-secondary border rounded-3">
                                                <i class="bi bi-car-front-fill fs-4"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($v['modelo']) ?></div>
                                            <small class="text-muted d-block"><?= htmlspecialchars($v['marca']) ?> • <?= $v['tipo_combustible'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Placa -->
                                <td data-label="Placa">
                                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace fs-6"><?= htmlspecialchars($v['placa']) ?></span>
                                </td>
                                
                                <!-- Rendimiento -->
                                <td data-label="Rendimiento">
                                    <div class="text-end text-md-start">
                                        <span class="fw-bold text-dark d-block"><?= number_format($v['rendimiento_promedio'], 1) ?> <small class="text-muted fw-normal"><?= $v['unidad_consumo'] ?></small></span>
                                        <?php if($v['tendencia'] != 0): ?>
                                            <small class="<?= $v['tendencia'] > 0 ? 'text-success' : 'text-danger' ?> fw-bold x-small">
                                                <i class="bi bi-caret-<?= $v['tendencia'] > 0 ? 'up' : 'down' ?>-fill"></i> 
                                                <?= abs(round($v['tendencia'], 1)) ?>%
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                
                                <!-- Última Fecha -->
                                <td data-label="Última Carga" class="text-dark fw-medium">
                                    <?= $v['ultima_fecha'] ?>
                                </td>
                                
                                <!-- Rango Estimado -->
                                <td data-label="Rango Est.">
                                    <div class="d-flex align-items-center gap-3 justify-content-end justify-content-md-start">
                                        <div class="progress progress-thin" style="width: 100px;">
                                            <?php $pct = min(100, ($v['rango_estimado'] / 600) * 100); ?>
                                            <div class="progress-bar progress-bar-custom" style="width: <?= $pct ?>%"></div>
                                        </div>
                                        <span class="fw-bold text-dark small"><?= number_format($v['rango_estimado'], 0) ?> km</span>
                                    </div>
                                </td>
                                
                                <!-- Acciones -->
                                <td class="text-center text-md-end pe-4">
                                    <div class="d-flex gap-2 justify-content-center justify-content-md-end w-100">
                                        <a href="?c=Dashboard&v=<?= $v['id'] ?>" class="btn btn-light btn-sm border text-primary fw-bold flex-fill flex-md-grow-0">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <button class="btn btn-light btn-sm border text-secondary fw-bold flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $v['id'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="?c=Dashboard&a=deleteVehicle" method="POST" onsubmit="return confirm('¿Eliminar?');" class="d-inline flex-fill flex-md-grow-0">
                                            <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                            <button class="btn btn-light btn-sm border text-danger fw-bold w-100">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- MODAL EDITAR (Uno por fila) -->
                                    <div class="modal fade" id="modalEditar<?= $v['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold">Editar Vehículo</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <form action="?c=Dashboard&a=editVehicle" method="POST" enctype="multipart/form-data">
                                                        <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                                        <div class="mb-3"><label class="small fw-bold text-muted">Modelo</label><input type="text" name="modelo" class="form-control rounded-3" value="<?= $v['modelo'] ?>"></div>
                                                        <div class="mb-3"><label class="small fw-bold text-muted">Placa</label><input type="text" name="placa" class="form-control rounded-3" value="<?= $v['placa'] ?>"></div>
                                                        <div class="mb-3"><label class="small fw-bold text-muted">Foto</label><input type="file" name="foto" class="form-control rounded-3"></div>
                                                        <button class="btn btn-primary w-100 rounded-pill fw-bold">Guardar Cambios</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Paginación -->
                <div class="d-flex justify-content-between align-items-center p-3 border-top bg-white rounded-bottom-4">
                    <small class="text-muted">Mostrando <?= count($vehiculos) ?> de <?= $pagination['total_items'] ?> vehículos</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 gap-1">
                            <li class="page-item <?= ($pagination['current'] <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link rounded-2 border-0 bg-light text-dark" href="?c=Vehiculos&page=<?= $pagination['current']-1 ?>">Prev</a>
                            </li>
                            <li class="page-item disabled"><span class="page-link border-0 bg-white"><?= $pagination['current'] ?></span></li>
                            <li class="page-item <?= ($pagination['current'] >= $pagination['total']) ? 'disabled' : '' ?>">
                                <a class="page-link rounded-2 border-0 bg-light text-dark" href="?c=Vehiculos&page=<?= $pagination['current']+1 ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- MODAL NUEVO VEHÍCULO -->
<div class="modal fade" id="modalNuevoVehiculo" tabindex="-1">
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
                        <input type="text" name="modelo" class="form-control rounded-3" required placeholder="Ej: BMW F800 GS">
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
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Guardar Vehículo</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        sidebar.classList.toggle('show');
        if (overlay) overlay.classList.toggle('show');
    }
</script>
</body>
</html>
