<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fleet Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="public/css/orvion.css" rel="stylesheet"> 
     <!-- <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .map-select { height: 300px; width: 100%; border-radius: 8px; margin-bottom: 10px; cursor: crosshair; }
        .vehicle-card { background: white; border: none; border-radius: 12px; transition: 0.3s; }
        .vehicle-img { width: 90px; height: 90px; object-fit: cover; border-radius: 50%; padding: 3px; border: 2px solid #e9ecef; }
        .card-stat { border: none; border-radius: 12px; transition: transform 0.2s; overflow: hidden; }
        .card-stat:hover { transform: translateY(-3px); }
        .stat-icon { position: absolute; right: -10px; bottom: -10px; font-size: 5rem; opacity: 0.1; transform: rotate(-15deg); }
        .chart-container { position: relative; height: 300px; width: 100%; }
        .nav-tabs .nav-link { border: none; color: #6c757d; font-weight: 500; }
        .nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 2px solid #0d6efd; background: transparent; }
        .main-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
        #search-results { position: absolute; z-index: 1000; background: white; width: 100%; border: 1px solid #ccc; max-height: 150px; overflow-y: auto; display: none; }
        .search-item { padding: 8px; cursor: pointer; border-bottom: 1px solid #eee; }
        .search-item:hover { background-color: #f8f9fa; }
    </style> -->
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm px-3">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-speedometer2"></i> Fleet Manager</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item dropdown me-3">
                <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-car-front-fill"></i> <?= $vehiculo_actual ? htmlspecialchars($vehiculo_actual['nombre']) : 'Seleccionar' ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <?php if(!empty($mis_vehiculos)): foreach($mis_vehiculos as $v): ?>
                    <li><a class="dropdown-item" href="?c=Dashboard&v=<?= $v['id'] ?>"><?= htmlspecialchars($v['nombre']) ?></a></li>
                    <?php endforeach; endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-primary" href="#" onclick="openVehicleModal()"><i class="bi bi-plus-circle"></i> Nuevo Vehículo</a></li>
                </ul>
            </li>
            <li class="nav-item"><a href="?c=Auth&a=logout" class="btn btn-outline-secondary btn-sm text-light border-0"><i class="bi bi-power"></i></a></li>
        </ul>
    </div>
</nav>

<div class="container-fluid py-4 px-lg-4">
    <?php if(!$vehiculo_actual): ?>
        <div class="text-center py-5">
            <h2 class="text-muted">🚗 ¡Empieza agregando tu primer vehículo!</h2>
            <button class="btn btn-primary btn-lg mt-3 rounded-pill px-4" onclick="openVehicleModal()">Agregar Vehículo</button>
        </div>
    <?php else: ?>

        <!-- FICHA DEL VEHÍCULO (ESTILO ORVION) -->
    <div class="card mb-4 p-4 border-0 shadow-sm" style="border-radius: 32px;">
        <div class="d-flex align-items-center flex-wrap gap-4">
            
            <!-- 1. FOTO CON BOTÓN DE EDICIÓN FLOTANTE -->
            <div class="vehicle-avatar-container flex-shrink-0">
                <?php if($vehiculo_actual['foto']): ?>
                    <img src="uploads/<?= $vehiculo_actual['foto'] ?>" class="vehicle-avatar" alt="Vehículo">
                <?php else: ?>
                    <div class="vehicle-avatar bg-light d-flex align-items-center justify-content-center text-secondary fs-1">
                        <i class="bi bi-car-front"></i>
                    </div>
                <?php endif; ?>
                
                <!-- Botón flotante para editar (Solo ícono, más limpio) -->
                <a href="#" onclick='openVehicleModal(<?= json_encode($vehiculo_actual) ?>)' class="btn-edit-icon" title="Editar detalles">
                    <i class="bi bi-pencil-fill" style="font-size: 0.8rem;"></i>
                </a>
            </div>

            <!-- 2. INFORMACIÓN PRINCIPAL -->
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <!-- Badge de Marca -->
                    <span class="badge-orvion">
                        <?= htmlspecialchars($vehiculo_actual['marca']) ?>
                    </span>
                    <!-- Badge de Combustible -->
                    <span class="badge-orvion bg-white border">
                        <i class="bi bi-fuel-pump-fill text-muted"></i> 
                        <?= htmlspecialchars($vehiculo_actual['tipo_combustible']) ?>
                    </span>
                </div>
                
                <h1 class="display-title mb-1"><?= htmlspecialchars($vehiculo_actual['nombre']) ?></h1>
                <p class="text-muted mb-0" style="font-size: 0.95rem;">
                    <?= htmlspecialchars($vehiculo_actual['modelo']) ?> • 
                    <span class="fst-italic text-black-50">"<?= htmlspecialchars($vehiculo_actual['descripcion']) ?>"</span>
                </p>
            </div>

            <!-- 3. DATOS TÉCNICOS (A la derecha, limpios) -->
            <div class="d-none d-md-flex align-items-center">
                <div class="text-end">
                    <div class="text-uppercase small fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 1px;">Tanque</div>
                    <div class="fs-4 fw-bold var-text-main">
                        <?= number_format($vehiculo_actual['capacidad_tanque'], 1, ',', '.') ?> 
                        <span class="fs-6 text-muted fw-normal"><?= $vehiculo_actual['unidad_combustible'] ?></span>
                    </div>
                </div>
                
                <div class="vr-soft"></div>
                
                <div class="text-end">
                    <div class="text-uppercase small fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 1px;">Medición</div>
                    <div class="fs-4 fw-bold var-text-main">
                        <?= explode('/', $vehiculo_actual['unidad_consumo'])[0] ?>
                        <span class="fs-6 text-muted fw-normal">/<?= explode('/', $vehiculo_actual['unidad_consumo'])[1] ?? '' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- KPI CARDS RE-ESTILIZADAS -->
    <div class="row g-4 mb-4">
        <!-- Tarjeta Negra (Estilo "Total Revenue") -->
        <div class="col-md-4">
            <div class="card card-stat black-theme h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-uppercase small ls-1 mb-0" style="opacity:0.6">Rendimiento</h6>
                        <div class="bg-white rounded-circle p-1 d-flex justify-content-center align-items-center" style="width:32px;height:32px;opacity:0.2"><i class="bi bi-lightning-fill text-dark"></i></div>
                    </div>
                    <h2 class="display-5 fw-bold mb-0"><?= number_format($stats['promedio_rend'], 1) ?></h2>
                    <p class="small text-white-50 mt-1"><?= $vehiculo_actual['unidad_consumo'] ?> promedio</p>
                </div>
            </div>
        </div>

        <!-- Tarjeta Blanca Clásica -->
        <div class="col-md-4">
            <div class="card card-stat h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-uppercase small text-muted mb-0">Autonomía</h6>
                        <div class="bg-light rounded-circle p-1 d-flex justify-content-center align-items-center" style="width:32px;height:32px"><i class="bi bi-geo-alt text-dark"></i></div>
                    </div>
                    <h2 class="display-5 fw-bold mb-0 text-dark">~<?= number_format($stats['rango_estimado'], 0) ?></h2>
                    <p class="small text-muted mt-1">km con tanque lleno</p>
                </div>
            </div>
        </div>

        <!-- Tarjeta Verde Lima (Estilo "Sales Funnel") -->
        <div class="col-md-4">
            <div class="card card-stat lime-theme h-100" style="background-color: #C7F33C;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-uppercase small mb-0" style="color:#445200">Gasto Mes</h6>
                        <div class="bg-dark rounded-circle p-1 d-flex justify-content-center align-items-center" style="width:32px;height:32px;opacity:0.1"><i class="bi bi-wallet2 text-white"></i></div>
                    </div>
                    <h2 class="display-5 fw-bold mb-0 text-dark">$<?= number_format($stats['gasto_mes'], 0, ',', '.') ?></h2>
                    <p class="small mt-1" style="color:#445200"><?= $stats['mes_nombre'] ?></p>
                </div>
            </div>
        </div>
    </div>


    <!-- MAPA Y GRAFICOS -->
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card main-card h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0"><h6 class="fw-bold"><i class="bi bi-map text-danger"></i> Rutas Frecuentes</h6></div>
                <div class="card-body p-2"><div id="mainMap" style="height: 400px; border-radius: 8px;"></div></div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card main-card h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold"><i class="bi bi-activity text-primary"></i> Métricas</h6>
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-rend" type="button">Eficiencia</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-gasto" type="button">Gastos</button></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content h-100">
                        <div class="tab-pane fade show active" id="tab-rend" role="tabpanel">
                            <div class="chart-container"><canvas id="chartCombined"></canvas></div>
                            <div class="text-center mt-2 small text-muted">Evolución del rendimiento en <?= $vehiculo_actual['unidad_consumo'] ?></div>
                        </div>
                        <div class="tab-pane fade" id="tab-gasto" role="tabpanel">
                            <div class="chart-container"><canvas id="chartMonthly"></canvas></div>
                            <div class="text-center mt-2 small text-muted">Total gastado por mes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA HISTORIAL -->
    <div class="card main-card mt-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold m-0"><i class="bi bi-clock-history"></i> Historial de Cargas</h6>
            <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="openLogModal()"><i class="bi bi-plus-lg"></i> Nuevo Registro</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light small text-uppercase text-muted">
                    <tr><th class="ps-4">Fecha</th><th>Estación</th><th class="text-end">Odómetro</th><th class="text-end">Carga</th><th class="text-end">Total</th><th class="text-center pe-4">Acciones</th></tr>
                </thead>
                <tbody class="border-top-0">
                    <?php foreach ($logs as $row): ?>
                    <tr>
                        <td class="ps-4 fw-bold text-dark"><?= date('d M', strtotime($row['fecha'])) ?> <small class="text-muted fw-normal">'<?= date('y', strtotime($row['fecha'])) ?></small></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="bg-light text-danger rounded-circle p-1 me-2"><i class="bi bi-geo-alt-fill" style="font-size: 0.7rem;"></i></span>
                                <?= htmlspecialchars($row['nombre_estacion']) ?>
                            </div>
                        </td>
                        <td class="text-end font-monospace"><?= number_format($row['odometro'], 1, ',', '.') ?></td>
                        <td class="text-end"><?= number_format($row['galones'], 2, ',', '.') ?></td>
                        <td class="text-end fw-bold text-dark">$ <?= number_format($row['precio_total'], 0, ',', '.') ?></td>
                        <td class="text-center pe-4">
                            <button class="btn btn-sm text-secondary btn-link p-0 me-2" onclick='openLogModal(<?= json_encode($row) ?>)' title="Editar"><i class="bi bi-pencil"></i></button>
                            <!-- Formulario para borrar usando MVC (Action) -->
                            <form action="?c=Dashboard&a=deleteLog" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar registro?');">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="vehicle_id" value="<?= $vehiculo_actual['id'] ?>">
                                <button class="btn btn-sm text-danger btn-link p-0" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL VEHÍCULO -->
<div class="modal fade" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold" id="vehicleModalTitle">Vehículo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="?c=Dashboard&a=saveVehicle" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="vehicle_id" id="v_id">
                <div class="modal-body">
                    <div class="mb-3"><label class="small text-muted text-uppercase fw-bold">Nombre</label><input type="text" name="nombre" id="v_nombre" class="form-control" placeholder="Ej: La Bestia" required></div>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="small text-muted text-uppercase fw-bold">Marca</label><input type="text" name="marca" id="v_marca" class="form-control" required></div>
                        <div class="col-6"><label class="small text-muted text-uppercase fw-bold">Modelo</label><input type="text" name="modelo" id="v_modelo" class="form-control" required></div>
                    </div>
                    <div class="mb-3"><label class="small text-muted text-uppercase fw-bold">Descripción</label><textarea name="descripcion" id="v_desc" class="form-control" rows="2"></textarea></div>
                    
                    <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">Configuración Técnica</h6>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="small text-muted">Combustible</label><select name="tipo_combustible" id="v_tipo" class="form-select"><option>Gasolina</option><option>Diesel</option><option>Eléctrico</option><option>GLP</option></select></div>
                        <div class="col-6"><label class="small text-muted">Unidad Medida</label><select name="unidad_combustible" id="v_unidad" class="form-select"><option>Galones</option><option>Litros</option><option>kWh</option></select></div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="small text-muted">Medición Consumo</label><select name="unidad_consumo" id="v_consumo" class="form-select"><option>km/gal</option><option>km/L</option><option>L/100km</option></select></div>
                        <div class="col-6"><label class="small text-muted">Capacidad Tanque</label><input type="number" step="0.1" name="capacidad_tanque" id="v_capacidad" class="form-control" required></div>
                    </div>
                    <div class="mb-2 p-2 bg-light rounded border border-dashed text-center">
                        <label class="cursor-pointer w-100 py-2">
                            <i class="bi bi-camera mb-1 d-block text-secondary h4"></i>
                            <span class="small text-muted">Click para cambiar foto</span>
                            <input type="file" name="foto" class="d-none" accept="image/*">
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-primary w-100 rounded-pill">Guardar Cambios</button></div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL REPOSTAJE -->
<div class="modal fade" id="logModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold" id="logModalTitle">Registro</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="?c=Dashboard&a=saveLog" method="POST">
                <input type="hidden" name="id" id="log_id">
                <input type="hidden" name="vehicle_id" value="<?= $vehiculo_actual['id'] ?? '' ?>">
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-5 border-end">
                            <div class="mb-3"><label class="small fw-bold">Fecha</label><input type="date" name="fecha" id="log_fecha" class="form-control" required></div>
                            <div class="mb-3"><label class="small fw-bold">Odómetro Actual</label><input type="number" step="any" name="odometro" id="log_odo" class="form-control" required></div>
                            <div class="row g-2 mb-3">
                                <div class="col-6"><label class="small fw-bold" id="lbl_unidad">Cantidad</label><input type="number" step="any" name="galones" id="log_gal" class="form-control" required></div>
                                <div class="col-6"><label class="small fw-bold">Total ($)</label><input type="number" step="any" name="precio_total" id="log_precio" class="form-control" required></div>
                            </div>
                            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="full" id="log_full" checked><label class="form-check-label small">Tanque Lleno</label></div>
                        </div>
                        <div class="col-md-7">
                            <label class="small fw-bold mb-2">Ubicación</label>
                            <div class="position-relative mb-2">
                                <div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span><input type="text" id="addressSearch" class="form-control border-start-0" placeholder="Buscar sitio..."></div>
                                <div id="search-results" class="shadow-sm rounded"></div>
                            </div>
                            <div id="selectMap" class="map-select border rounded"></div>
                            <input type="hidden" name="nombre_estacion" id="log_estacion">
                            <input type="hidden" name="latitud" id="log_lat"><input type="hidden" name="longitud" id="log_lng">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0"><button type="submit" class="btn btn-primary px-4 rounded-pill">Guardar Registro</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // --- LÓGICA DE MAPAS ---
    <?php if($vehiculo_actual): ?>
    if(document.getElementById('mainMap')) {
        const mainMap = L.map('mainMap').setView([4.65, -74.1], 9);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mainMap);
        const markers = <?= json_encode($charts['mapa']) ?>;
        const group = L.featureGroup();
        markers.forEach(m => L.marker([m.lat, m.lng]).addTo(mainMap).bindPopup(m.name).addTo(group));
        if(markers.length > 0) mainMap.fitBounds(group.getBounds().pad(0.1));
    }

    // Configuración Charts
    const commonOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: false } } };

    if(document.getElementById('chartCombined')) {
        new Chart(document.getElementById('chartCombined'), {
            type: 'line',
            data: {
                labels: <?= json_encode($charts['fechas']) ?>,
                datasets: [{ 
                    label: 'Rendimiento', 
                    data: <?= json_encode($charts['rendimiento']) ?>, 
                    borderColor: '#0d6efd', 
                    backgroundColor: 'rgba(13, 110, 253, 0.05)', 
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0d6efd',
                    fill: true, 
                    tension: 0.4 
                }]
            },
            options: commonOptions
        });
        
        new Chart(document.getElementById('chartMonthly'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($charts['gasto_mensual'])) ?>,
                datasets: [{ 
                    label: 'Gasto', 
                    data: <?= json_encode(array_values($charts['gasto_mensual'])) ?>, 
                    backgroundColor: '#198754', 
                    borderRadius: 4 
                }]
            },
            options: commonOptions
        });
    }

    // Modales
    let selectMap, selectMarker;
    const logModal = document.getElementById('logModal');
    logModal.addEventListener('shown.bs.modal', function () {
        if (!selectMap) {
            selectMap = L.map('selectMap').setView([4.65, -74.1], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(selectMap);
            selectMarker = L.marker([4.65, -74.1], {draggable: true}).addTo(selectMap);
            selectMarker.on('dragend', function(e) { updateLoc(e.target.getLatLng()); });
            selectMap.on('click', function(e) { selectMarker.setLatLng(e.latlng); updateLoc(e.latlng); });
        }
        setTimeout(() => { selectMap.invalidateSize(); }, 200);
    });
    
    // Buscador Map
    document.getElementById('addressSearch').addEventListener('input', function(e) {
        const q = e.target.value; const resDiv = document.getElementById('search-results');
        if(q.length < 3) { resDiv.style.display='none'; return; }
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=5`)
            .then(r=>r.json()).then(d=>{
                resDiv.innerHTML='';
                if(d.length>0) {
                    d.forEach(p=>{
                        const div=document.createElement('div'); div.className='search-item small'; 
                        div.innerHTML = `<i class="bi bi-geo-alt text-muted me-2"></i> ${p.display_name}`;
                        div.onclick=()=>{
                            selectMarker.setLatLng([p.lat, p.lon]); selectMap.setView([p.lat, p.lon], 16); updateLoc({lat:p.lat, lng:p.lon});
                            document.getElementById('log_estacion').value = p.display_name.split(',')[0]; 
                            document.getElementById('addressSearch').value = p.display_name.split(',')[0];
                            resDiv.style.display='none';
                        };
                        resDiv.appendChild(div);
                    });
                    resDiv.style.display='block';
                }
            });
    });
    function updateLoc(ll) { document.getElementById('log_lat').value=ll.lat; document.getElementById('log_lng').value=ll.lng || ll.lon; }

    window.openLogModal = function(data=null) {
        document.getElementById('logModalTitle').innerText = data ? 'Editar' : 'Nuevo Registro';
        document.getElementById('log_id').value = data ? data.id : '';
        document.getElementById('log_fecha').value = data ? data.fecha : new Date().toISOString().split('T')[0];
        document.getElementById('log_odo').value = data ? data.odometro : '';
        document.getElementById('log_gal').value = data ? data.galones : '';
        document.getElementById('log_precio').value = data ? data.precio_total : '';
        document.getElementById('log_estacion').value = data ? data.nombre_estacion : '';
        document.getElementById('addressSearch').value = data ? data.nombre_estacion : '';
        document.getElementById('lbl_unidad').innerText = 'Cant. (<?= $vehiculo_actual['unidad_combustible'] ?>)';
        new bootstrap.Modal(logModal).show();
    }
    <?php endif; ?>

    // Modal Vehículo
    const vehModal = new bootstrap.Modal(document.getElementById('vehicleModal'));
    window.openVehicleModal = function(data = null) {
        document.getElementById('vehicleModalTitle').innerText = data ? 'Editar Vehículo' : 'Nuevo Vehículo';
        document.getElementById('v_id').value = data ? data.id : '';
        document.getElementById('v_nombre').value = data ? data.nombre : '';
        document.getElementById('v_marca').value = data ? data.marca : '';
        document.getElementById('v_modelo').value = data ? data.modelo : '';
        document.getElementById('v_desc').value = data ? data.descripcion : '';
        document.getElementById('v_capacidad').value = data ? data.capacidad_tanque : 15;
        if (data) {
            document.getElementById('v_tipo').value = data.tipo_combustible;
            document.getElementById('v_unidad').value = data.unidad_combustible;
            document.getElementById('v_consumo').value = data.unidad_consumo;
        }
        vehModal.show();
    }
</script>
</body>
</html>
