<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Fleet Manager</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Custom CSS -->
  <link href="public/css/orvion.css" rel="stylesheet" />

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

  <style>
    .hover-bg-light:hover { background-color: #f8f9fa; transition: background-color 0.2s ease; }
    .hover-opacity-100:hover { opacity: 1 !important; }
    .table td { vertical-align: middle; }

    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
  </style>
</head>

<body class="bg-light">
<div class="layout-wrapper">

  <!-- 1) SIDEBAR -->
  <aside class="sidebar bg-white shadow-sm" id="sidebar">
    <div class="sidebar-header border-bottom d-flex align-items-center gap-2 p-3">
      <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-dark fs-5 text-decoration-none" href="?c=Dashboard">
        <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px">
          <i class="bi bi-speedometer2"></i>
        </div>
        <div>Fleet Manager</div>
      </a>
      <button class="btn btn-light d-lg-none ms-auto border-0" onclick="toggleSidebar()">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <nav class="sidebar-menu p-2">
      <a href="?c=Dashboard" class="nav-link-custom active">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
      </a>
      <a href="?c=Vehiculos" class="nav-link-custom">
        <i class="bi bi-car-front-fill"></i> Vehículos
      </a>
      <a href="?c=Reportes" class="nav-link-custom">
        <i class="bi bi-file-earmark-bar-graph"></i> Reportes
      </a>
      <a href="#" class="nav-link-custom">
        <i class="bi bi-geo-alt"></i> Estaciones
      </a>
      <a href="#" class="nav-link-custom">
        <i class="bi bi-gear"></i> Configuración
      </a>
    </nav>

    <!-- Perfil -->
    <div class="p-3 mt-auto border-top bg-light">
      <div class="d-flex align-items-center gap-3">
        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center text-secondary border shadow-sm"
             style="width:45px;height:45px">
          <i class="bi bi-person-fill fs-4"></i>
        </div>

        <div class="overflow-hidden">
          <p class="mb-0 small fw-bold text-truncate" style="max-width: 140px;">
            <?= htmlspecialchars($user_name ?? 'Usuario') ?>
          </p>
          <div class="d-flex gap-2 align-items-center">
            <a href="?c=Auth&a=profile" class="text-muted text-decoration-none fw-medium" style="font-size:0.75rem">Mi Perfil</a>
            <span class="text-muted opacity-50" style="font-size:0.75rem">•</span>
            <a href="?c=Auth&a=logout" class="text-danger text-decoration-none fw-bold" style="font-size:0.75rem">Salir</a>
          </div>
        </div>
      </div>
    </div>
  </aside>

  <!-- 2) MAIN -->
  <main class="main-content">

    <!-- TOPBAR -->
    <header class="topbar bg-white shadow-sm sticky-top" style="height: 70px; z-index: 1020;">
      <div class="d-flex justify-content-between align-items-center h-100 px-3 px-md-4">

        <!-- Izquierda -->
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-light d-lg-none border-0" onclick="toggleSidebar()">
            <i class="bi bi-list fs-4"></i>
          </button>

          <?php if (!empty($vehiculo_actual)): ?>
            <div class="d-flex align-items-center gap-2">
              <div>
                <h5 class="fw-bold m-0 text-dark"><?= htmlspecialchars($vehiculo_actual['modelo'] ?? '') ?></h5>
                <small class="text-muted d-block" style="line-height:1; font-size:0.75rem">
                  <?= htmlspecialchars($vehiculo_actual['placa'] ?? '') ?>
                </small>
              </div>

              <div class="dropdown">
                <button class="btn btn-link text-secondary p-1 border-0 opacity-50 hover-opacity-100" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" title="Configurar vehículo">
                  <i class="bi bi-gear-fill" style="font-size: 1.1rem"></i>
                </button>
                <ul class="dropdown-menu shadow-lg border-0 rounded-4 p-2">
                  <li><h6 class="dropdown-header x-small text-uppercase fw-bold">Acciones</h6></li>
                  <li>
                    <a class="dropdown-item small rounded-2 py-2" href="#"
                       data-bs-toggle="modal" data-bs-target="#modalEditarVehiculo">
                      <i class="bi bi-pencil me-2 text-primary"></i> Editar Información
                    </a>
                  </li>
                  <li>
                    <form action="?c=Dashboard&a=deleteVehicle" method="POST"
                          onsubmit="return confirm('¿Estás SEGURO de eliminar este vehículo? Se borrarán todos sus datos.')">
                      <input type="hidden" name="id" value="<?= htmlspecialchars($vehiculo_actual['id'] ?? '') ?>">
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

        <!-- Derecha -->
        <div class="d-flex align-items-center gap-3">
          <?php if (!empty($vehiculo_actual)): ?>

            <!-- Selector vehículo -->
            <div class="dropdown">
              <button class="btn btn-light bg-light border-0 rounded-pill px-3 py-2 d-flex align-items-center gap-2"
                      type="button" data-bs-toggle="dropdown" title="Cambiar Vehículo">
                <i class="bi bi-car-front-fill text-secondary"></i>
                <span class="d-none d-lg-inline small fw-bold text-secondary">Cambiar</span>
                <i class="bi bi-chevron-down ms-1 small text-muted" style="font-size: 0.7rem"></i>
              </button>

              <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2" style="min-width: 220px;">
                <li class="px-2 py-1">
                  <small class="text-muted fw-bold text-uppercase x-small">Mis Vehículos</small>
                </li>

                <?php foreach (($mis_vehiculos ?? []) as $v): ?>
                  <?php $vid = $v['id'] ?? null; ?>
                  <li>
                    <a class="dropdown-item py-2 rounded-2 small <?= (!empty($vehiculo_actual['id']) && $vehiculo_actual['id'] == $vid) ? 'active bg-primary text-white fw-bold' : '' ?>"
                       href="?c=Dashboard&v=<?= urlencode((string)$vid) ?>">
                      <?= htmlspecialchars($v['modelo'] ?? '') ?>
                    </a>
                  </li>
                <?php endforeach; ?>

                <li><hr class="dropdown-divider"></li>

                <li>
                  <a class="dropdown-item py-2 small text-primary fw-bold rounded-2" href="#"
                     data-bs-toggle="modal" data-bs-target="#modalVehiculo">
                    <i class="bi bi-plus-lg me-2"></i> Nuevo Vehículo
                  </a>
                </li>
              </ul>
            </div>

            <!-- Registrar -->
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm"
                    style="background-color:#2563EB;border:none"
                    data-bs-toggle="modal" data-bs-target="#modalLog">
              <i class="bi bi-plus-lg"></i>
              <span class="d-none d-sm-inline">Registrar</span>
            </button>

          <?php endif; ?>
        </div>

      </div>
    </header>

    <!-- BODY -->
    <div class="p-3 p-md-4 fade-in" style="padding-bottom: 4rem !important;">

      <?php if (!empty($vehiculo_actual)): ?>

        <!-- Info móvil -->
        <div class="d-md-none text-center mb-4 mt-2">
          <div class="position-relative d-inline-block mb-2">
            <?php if (!empty($vehiculo_actual['foto'])): ?>
              <img src="uploads/<?= htmlspecialchars($vehiculo_actual['foto']) ?>"
                   class="rounded-circle shadow-sm object-fit-cover"
                   style="width:80px;height:80px;border:3px solid white" alt="Vehículo">
            <?php else: ?>
              <div class="bg-white rounded-circle d-flex align-items-center justify-content-center text-secondary border shadow-sm mx-auto"
                   style="width:80px;height:80px">
                <i class="bi bi-car-front fs-2"></i>
              </div>
            <?php endif; ?>

            <button class="btn btn-light btn-sm rounded-circle shadow-sm position-absolute bottom-0 end-0 border"
                    style="width:32px;height:32px"
                    data-bs-toggle="modal" data-bs-target="#modalEditarVehiculo">
              <i class="bi bi-pencil-fill small text-secondary"></i>
            </button>
          </div>

          <div class="d-flex justify-content-center gap-2 align-items-center mt-2">
            <span class="badge bg-light text-dark border px-3 py-1 rounded-pill small font-monospace">
              <?= htmlspecialchars($vehiculo_actual['placa'] ?? '') ?>
            </span>
          </div>
        </div>

        <!-- 1) KPI -->
        <div class="row g-3 mb-4">

          <!-- Rendimiento (2 métodos + tooltip) -->
          <div class="col-6 col-xl-3">
            <div class="card-widget p-3 h-100 d-flex flex-column justify-content-between shadow-sm border-0">
              <div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <div class="icon-box blue bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                         style="width:36px;height:36px;font-size:1rem">
                      <i class="bi bi-fuel-pump"></i>
                    </div>
                    <span class="text-muted x-small fw-bold text-uppercase">Rendimiento</span>
                  </div>

                  <button type="button"
                          class="btn btn-sm btn-light border-0 p-0 text-muted"
                          data-bs-toggle="tooltip"
                          data-bs-placement="top"
                          data-bs-html="true"
                          title="<div style='max-width:230px'>
                                   <b>Mes calendario</b>: solo tramos (llenado→llenado) dentro del mes.<br>
                                   <b>Full‑tank continuo</b>: incluye el último llenado anterior.
                                 </div>">
                    <i class="bi bi-info-circle"></i>
                  </button>
                </div>

                <h3 class="fw-bold text-dark mb-1 fs-4">
                  <?= number_format(($stats['rend_cal_mes'] ?? 0), 1) ?>
                </h3>
                <div class="d-flex justify-content-between align-items-center small text-muted">
                  <small>Mes calendario</small>
                  <?php if (!empty($stats['tend_rend_cal'])): ?>
                    <small class="<?= ($stats['tend_rend_cal'] >= 0) ? 'text-success' : 'text-danger' ?> fw-bold x-small">
                      <i class="bi bi-arrow-<?= ($stats['tend_rend_cal'] >= 0) ? 'up' : 'down' ?>"></i>
                      <?= abs(round($stats['tend_rend_cal'])) ?>%
                    </small>
                  <?php endif; ?>
                </div>

                <hr class="my-2">

                <h3 class="fw-bold text-dark mb-1 fs-4">
                  <?= number_format(($stats['rend_full_mes'] ?? 0), 1) ?>
                </h3>
                <div class="d-flex justify-content-between align-items-center small text-muted">
                  <small>Full‑tank continuo</small>
                  <?php if (!empty($stats['tend_rend_full'])): ?>
                    <small class="<?= ($stats['tend_rend_full'] >= 0) ? 'text-success' : 'text-danger' ?> fw-bold x-small">
                      <i class="bi bi-arrow-<?= ($stats['tend_rend_full'] >= 0) ? 'up' : 'down' ?>"></i>
                      <?= abs(round($stats['tend_rend_full'])) ?>%
                    </small>
                  <?php endif; ?>
                </div>

                <small class="text-muted d-block mt-2 x-small">
                  Unidad: <?= htmlspecialchars($vehiculo_actual['unidad_consumo'] ?? '') ?>
                </small>
              </div>
            </div>
          </div>

          <!-- Rango (2 métodos + tooltip) -->
          <div class="col-6 col-xl-3">
            <div class="card-widget p-3 h-100 d-flex flex-column justify-content-between shadow-sm border-0">
              <div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <div class="icon-box green bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                         style="width:36px;height:36px;font-size:1rem">
                      <i class="bi bi-speedometer2"></i>
                    </div>
                    <span class="text-muted x-small fw-bold text-uppercase">Rango</span>
                  </div>

                  <button type="button"
                          class="btn btn-sm btn-light border-0 p-0 text-muted"
                          data-bs-toggle="tooltip"
                          data-bs-placement="top"
                          data-bs-html="true"
                          title="<div style='max-width:220px'>
                                   <b>Mes calendario</b>: rango estimado con rendimiento del mes.<br>
                                   <b>Full‑tank continuo</b>: incluye el último llenado anterior.
                                 </div>">
                    <i class="bi bi-info-circle"></i>
                  </button>
                </div>

                <h3 class="fw-bold text-dark mb-1 fs-4">
                  <?= number_format(($stats['rango_cal_mes'] ?? 0), 0) ?>
                </h3>
                <div class="d-flex justify-content-between align-items-center small text-muted">
                  <small>Mes calendario</small><small>km est.</small>
                </div>

                <hr class="my-2">

                <h3 class="fw-bold text-dark mb-1 fs-4">
                  <?= number_format(($stats['rango_full_mes'] ?? 0), 0) ?>
                </h3>
                <div class="d-flex justify-content-between align-items-center small text-muted">
                  <small>Full‑tank continuo</small><small>km est.</small>
                </div>
              </div>
            </div>
          </div>

          <!-- Gasto mes -->
          <div class="col-6 col-xl-3">
            <div class="card-widget p-3 h-100 d-flex flex-column justify-content-between shadow-sm border-0">
              <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div class="icon-box orange bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                       style="width:36px;height:36px;font-size:1rem">
                    <i class="bi bi-wallet2"></i>
                  </div>
                  <span class="text-muted x-small fw-bold text-uppercase">Gasto mes</span>
                </div>

                <h3 class="fw-bold text-dark mb-0 fs-5">
                  $<?= number_format(($stats['gasto_mes'] ?? 0), 0, ',', '.') ?>
                </h3>

                <?php if (!empty($stats['tendencia_gasto'])): ?>
                  <small class="<?= ($stats['tendencia_gasto'] > 0) ? 'text-danger' : 'text-success' ?> fw-bold d-block x-small mt-1">
                    <i class="bi bi-arrow-<?= ($stats['tendencia_gasto'] > 0) ? 'up' : 'down' ?>"></i>
                    <?= abs(round($stats['tendencia_gasto'])) ?>% vs mes ant.
                  </small>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Última -->
          <div class="col-6 col-xl-3">
            <div class="card-widget p-3 h-100 d-flex flex-column justify-content-between shadow-sm border-0">
              <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div class="icon-box red bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                       style="width:36px;height:36px;font-size:1rem">
                    <i class="bi bi-geo-alt"></i>
                  </div>
                  <span class="text-muted x-small fw-bold text-uppercase">Última</span>
                </div>

                <div class="overflow-hidden">
                  <h6 class="fw-bold text-dark mb-0 text-truncate small">
                    <?= !empty($logs) ? htmlspecialchars($logs[0]['nombre_estacion'] ?? '-') : '-' ?>
                  </h6>
                  <small class="text-muted x-small">
                    <?= !empty($logs) ? date('d M', strtotime($logs[0]['fecha'])) : '-' ?>
                  </small>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- 2) GRÁFICOS + MAPA -->
        <div class="row g-4 mb-4">
          <div class="col-lg-4">
            <div class="card-widget border-0 shadow-sm h-100">
              <h6 class="fw-bold mb-3 small text-uppercase text-muted ls-1">Rendimiento histórico</h6>
              <div style="position: relative; height: 200px;">
                <canvas id="rendimientoChart"></canvas>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card-widget border-0 shadow-sm h-100">
              <h6 class="fw-bold mb-3 small text-uppercase text-muted ls-1">Gastos mensuales</h6>
              <div style="position: relative; height: 200px;">
                <canvas id="gastosChart"></canvas>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
        <div class="card-widget border-0 shadow-sm p-0 overflow-hidden position-relative">
            <div id="map" style="height:240px; width:100%;"></div>
        </div>
        </div>
        </div>

        <!-- 3) HISTORIAL -->
        <div class="card-widget p-0 overflow-hidden border-0 shadow-sm mb-4 h-auto">

          <div class="d-flex flex-wrap justify-content-between align-items-center px-4 py-3 border-bottom bg-white gap-3">
            <h6 class="fw-bold mb-0 text-dark small text-uppercase ls-1">HISTORIAL</h6>

            <form method="GET" class="d-flex align-items-center gap-2">
              <input type="hidden" name="c" value="Dashboard">
              <input type="hidden" name="v" value="<?= htmlspecialchars($vehiculo_actual['id'] ?? '') ?>">

              <div class="input-group input-group-sm" style="width:auto;">
                <span class="input-group-text bg-light border-0 text-muted">
                  <i class="bi bi-calendar-event"></i>
                </span>
                <input type="date" name="from" class="form-control border-0 bg-light text-muted small fw-bold"
                       style="max-width: 130px"
                       value="<?= htmlspecialchars($_GET['from'] ?? date('Y-m-01')) ?>">
              </div>

              <span class="text-muted small">→</span>

              <div class="input-group input-group-sm" style="width:auto;">
                <input type="date" name="to" class="form-control border-0 bg-light text-muted small fw-bold"
                       style="max-width: 130px"
                       value="<?= htmlspecialchars($_GET['to'] ?? date('Y-m-d')) ?>">
              </div>

              <button type="submit" class="btn btn-sm btn-light border fw-bold text-primary">Filtrar</button>

              <?php if (isset($_GET['from']) && isset($_GET['to'])): ?>
                <a href="?c=Dashboard&v=<?= urlencode((string)($vehiculo_actual['id'] ?? '')) ?>"
                   class="btn btn-sm text-muted" title="Limpiar">
                  <i class="bi bi-x-lg"></i>
                </a>
              <?php endif; ?>
            </form>
          </div>

          <div class="table-responsive">
            <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
              <thead class="bg-light">
              <tr>
                <th class="ps-4 py-3 border-0 text-muted x-small fw-bold text-uppercase" style="width:45%;">Estación</th>
                <th class="py-3 border-0 text-muted x-small fw-bold text-uppercase text-center d-none d-md-table-cell">Fecha</th>
                <th class="pe-4 py-3 border-0 text-muted x-small fw-bold text-uppercase text-end">Costo Total</th>
                <th class="py-3 border-0 text-muted x-small fw-bold text-uppercase text-center" style="width:50px;"></th>
              </tr>
              </thead>

              <tbody>
              <?php $logsLimitados = $logs ?? []; ?>
              <?php foreach ($logsLimitados as $i => $row): ?>
                <tr class="hover-bg-light">
                  <td class="ps-4 py-3 border-bottom border-light">
                    <div class="d-flex align-items-center gap-3">
                      <div class="bg-light rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center text-secondary"
                           style="width:42px;height:42px">
                        <i class="bi bi-fuel-pump fs-5 text-muted"></i>
                      </div>
                      <div class="overflow-hidden">
                        <span class="fw-bold text-dark d-block text-truncate" style="font-size:0.95rem">
                          <?= htmlspecialchars($row['nombre_estacion'] ?? '-') ?>
                        </span>
                        <small class="text-muted d-md-none">
                          <?= date('d M Y', strtotime($row['fecha'])) ?>
                        </small>
                      </div>
                    </div>
                  </td>

                  <td class="py-3 border-bottom border-light text-center d-none d-md-table-cell">
                    <span class="text-muted fw-normal small">
                      <?= date('d M Y', strtotime($row['fecha'])) ?>
                    </span>
                  </td>

                  <td class="pe-4 py-3 border-bottom border-light text-end">
                    <div class="fw-bold text-dark" style="font-size:1rem">
                      $<?= number_format((float)($row['precio_total'] ?? 0), 0, ',', '.') ?>
                    </div>
                    <div class="d-flex align-items-center justify-content-end gap-2 mt-1 small text-muted x-small">
                      <?= number_format((float)($row['galones'] ?? 0), 1) ?> gal
                    </div>
                  </td>

                  <td class="py-3 border-bottom border-light text-center">
                    <form action="?c=Dashboard&a=deleteLog" method="POST" onsubmit="return confirm('¿Eliminar registro?')">
                      <input type="hidden" name="id" value="<?= htmlspecialchars($row['id'] ?? '') ?>">
                      <input type="hidden" name="vehicle_id" value="<?= htmlspecialchars($vehiculo_actual['id'] ?? '') ?>">
                      <button type="submit" class="btn btn-link p-0 text-danger opacity-50 hover-opacity-100" title="Eliminar">
                        <i class="bi bi-trash3 fs-6"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>

              <?php if (empty($logsLimitados)): ?>
                <tr>
                  <td colspan="4" class="text-center py-5 text-muted small">
                    No hay registros en este rango de fechas.
                  </td>
                </tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <?php if (!empty($pagination) && (($pagination['total'] ?? 1) > 1)): ?>
            <div class="d-flex justify-content-end p-3 bg-white border-top">
              <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">

                  <li class="page-item <?= (($pagination['current'] ?? 1) <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-3 border fw-bold text-secondary px-3"
                       href="?c=Dashboard&v=<?= urlencode((string)($pagination['v_id'] ?? '')) ?>
                       &page=<?= max(1, (int)($pagination['current'] ?? 1) - 1) ?>
                       &from=<?= urlencode((string)($pagination['from'] ?? '')) ?>
                       &to=<?= urlencode((string)($pagination['to'] ?? '')) ?>">
                      Anterior
                    </a>
                  </li>

                  <li class="page-item disabled">
                    <span class="page-link border-0 bg-transparent text-muted">
                      <?= (int)($pagination['current'] ?? 1) ?> / <?= (int)($pagination['total'] ?? 1) ?>
                    </span>
                  </li>

                  <li class="page-item <?= ((int)($pagination['current'] ?? 1) >= (int)($pagination['total'] ?? 1)) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-3 border fw-bold text-secondary px-3"
                       href="?c=Dashboard&v=<?= urlencode((string)($pagination['v_id'] ?? '')) ?>
                       &page=<?= min((int)($pagination['total'] ?? 1), (int)($pagination['current'] ?? 1) + 1) ?>
                       &from=<?= urlencode((string)($pagination['from'] ?? '')) ?>
                       &to=<?= urlencode((string)($pagination['to'] ?? '')) ?>">
                      Siguiente
                    </a>
                  </li>

                </ul>
              </nav>
            </div>
          <?php endif; ?>

        </div>

      <?php else: ?>

        <!-- Estado vacío -->
        <div class="d-flex flex-column align-items-center justify-content-center text-center p-5" style="min-height: 60vh;">
          <div class="bg-white p-5 rounded-circle shadow-sm mb-4">
            <i class="bi bi-car-front fs-1 text-secondary opacity-25" style="font-size:4rem !important"></i>
          </div>
          <h3 class="fw-bold text-dark">Bienvenido a Fleet Manager</h3>
          <p class="text-muted mb-4 col-md-6 mx-auto">
            Comienza registrando tu primer vehículo.
          </p>
          <button class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm"
                  data-bs-toggle="modal" data-bs-target="#modalVehiculo">
            <i class="bi bi-plus-lg me-2"></i> Registrar Vehículo
          </button>
        </div>

      <?php endif; ?>

    </div><!-- /body -->

  </main>
</div><!-- /layout-wrapper -->


<!-- =================== MODALES =================== -->

<!-- Modal Nuevo Vehículo -->
<div class="modal fade" id="modalVehiculo" tabindex="-1" aria-hidden="true">
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

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">DESCRIPCIÓN</label>
            <input type="text" name="descripcion" class="form-control rounded-3" placeholder="Opcional">
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

<!-- Modal Editar Vehículo -->
<div class="modal fade" id="modalEditarVehiculo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Editar Vehículo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-4">
        <form action="?c=Dashboard&a=editVehicle" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= htmlspecialchars($vehiculo_actual['id'] ?? '') ?>">

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">PLACA</label>
            <input type="text" name="placa" class="form-control rounded-3" required
                   value="<?= htmlspecialchars($vehiculo_actual['placa'] ?? '') ?>" style="text-transform: uppercase;">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">MODELO</label>
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
              <label class="form-label small fw-bold text-muted">CAPACIDAD (Gal)</label>
              <input type="number" step="0.1" name="capacidad_tanque" class="form-control rounded-3" required
                     value="<?= htmlspecialchars($vehiculo_actual['capacidad_tanque'] ?? 12) ?>">
            </div>
            <div class="col-6">
              <label class="form-label small fw-bold text-muted">UNIDAD</label>
              <select name="unidad_consumo" class="form-select rounded-3">
                <?php $u = ($vehiculo_actual['unidad_consumo'] ?? 'km/gal'); ?>
                <option value="km/gal" <?= ($u === 'km/gal') ? 'selected' : '' ?>>km/gal</option>
                <option value="km/l" <?= ($u === 'km/l') ? 'selected' : '' ?>>km/l</option>
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label small fw-bold text-muted">FOTO (Opcional)</label>
            <input type="file" name="foto" class="form-control rounded-3" accept="image/*">
          </div>

          <button type="submit" class="btn btn-dark w-100 rounded-pill py-2 fw-bold">Guardar Cambios</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Registrar Tanqueada -->
<div class="modal fade" id="modalLog" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Registrar Tanqueada</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-4">
        <form action="?c=Dashboard&a=saveLog" method="POST">
          <input type="hidden" name="vehicle_id" value="<?= htmlspecialchars($vehiculo_actual['id'] ?? '') ?>">
          <input type="hidden" name="latitud" id="lat">
          <input type="hidden" name="longitud" id="lng">
          <input type="hidden" name="nombre_estacion" id="estacionnombre">

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label small fw-bold text-muted">FECHA</label>
              <input type="datetime-local" name="fecha" class="form-control rounded-3" required
                     value="<?= date('Y-m-d\TH:i') ?>">
            </div>

            <div class="col-6">
              <label class="form-label small fw-bold text-muted">ODÓMETRO (km)</label>
              <input type="number" step="0.1" name="odometro" class="form-control rounded-3 fw-bold" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">UBICACIÓN</label>
            <div id="mapPicker" style="height:250px; width:100%; border-radius:8px; border:1px solid #ddd;"></div>
            <small class="text-muted d-block mt-1">Arrastra el marcador para ajustar</small>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-4">
              <label class="form-label small fw-bold text-muted">GALONES</label>
              <input type="number" step="0.01" name="galones" id="galones" class="form-control rounded-3" required min="0">
            </div>
            <div class="col-4">
              <label class="form-label small fw-bold text-muted">GALÓN</label>
              <input type="number" step="0.01" name="precio_galon" id="preciogalon" class="form-control rounded-3" min="0" placeholder="Opcional">
            </div>
            <div class="col-4">
              <label class="form-label small fw-bold text-muted">TOTAL</label>
              <input type="number" step="1" name="precio_total" id="preciototal" class="form-control rounded-3 fw-bold" required min="0">
              <small class="text-muted x-small d-block mt-1">Se calcula si llenas galón</small>
            </div>

            <div class="col-12 d-flex align-items-center">
              <div class="form-check mt-2">
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


<!-- =================== SCRIPTS =================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2Z9VMkNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
  function toggleSidebar() {
    document.getElementById('sidebar')?.classList.toggle('show');
  }

  // Tooltips
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
      new bootstrap.Tooltip(el);
    });
  });

  // Cálculo automático Total/Galón
  (function () {
    const galEl = document.getElementById('galones');
    const pgEl  = document.getElementById('preciogalon');
    const totEl = document.getElementById('preciototal');
    if (!galEl || !pgEl || !totEl) return;

    let last = null;

    function n(v) {
      const x = parseFloat(v);
      return isNaN(x) ? 0 : x;
    }

    function calcFromPrecioGalon() {
      const gal = n(galEl.value);
      const pg  = n(pgEl.value);
      if (gal > 0 && pg > 0) totEl.value = Math.round(gal * pg);
    }

    function calcFromTotal() {
      const gal = n(galEl.value);
      const tot = n(totEl.value);
      if (gal > 0 && tot > 0) pgEl.value = (tot / gal).toFixed(2);
    }

    galEl.addEventListener('input', () => {
      if (last === 'total') calcFromTotal();
      else calcFromPrecioGalon();
    });

    pgEl.addEventListener('input', () => {
      last = 'preciogalon';
      calcFromPrecioGalon();
    });

    totEl.addEventListener('input', () => {
      last = 'total';
      calcFromTotal();
    });
  })();

  // MAPA PICKER en modal
  (function () {
    let mapPicker, markerPicker;
    const modalLog = document.getElementById('modalLog');
    if (!modalLog) return;

    modalLog.addEventListener('shown.bs.modal', function () {
      if (!mapPicker) {
        mapPicker = L.map('mapPicker').setView([4.6097, -74.0817], 13);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 20 }).addTo(mapPicker);
        markerPicker = L.marker([4.6097, -74.0817], { draggable: true }).addTo(mapPicker);

        function setCoords(lat, lng) {
  document.getElementById('lat').value = Number(lat).toFixed(6);
  document.getElementById('lng').value = Number(lng).toFixed(6);
}

markerPicker.on('dragend', function (e) {
  const pos = e.target.getLatLng();
  setCoords(pos.lat, pos.lng);
});

// También al hacer click
mapPicker.on('click', function (e) {
  markerPicker.setLatLng(e.latlng);
  setCoords(e.latlng.lat, e.latlng.lng);
});

// Deja un valor inicial SIEMPRE (aunque el usuario no mueva nada)
setCoords(4.6097, -74.0817);
markerPicker.setLatLng([4.6097, -74.0817]);

setTimeout(() => mapPicker.invalidateSize(), 200);

// Geolocalización (si falla, te quedas con Bogotá)
if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(
    function (position) {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      setCoords(lat, lng);
      mapPicker.setView([lat, lng], 15);
      markerPicker.setLatLng([lat, lng]);
    },
    function () {
      // Si falla (por HTTP/permisos), no pasa nada: ya hay coords por defecto
    },
    { enableHighAccuracy: true, timeout: 8000 }
  );
}

      }

      setTimeout(() => mapPicker.invalidateSize(), 200);

      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          document.getElementById('lat').value = lat.toFixed(6);
          document.getElementById('lng').value = lng.toFixed(6);
          mapPicker.setView([lat, lng], 15);
          markerPicker.setLatLng([lat, lng]);
        });
      }
    });
  })();
</script>

<?php if (!empty($vehiculo_actual)): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {

    // Rendimiento (línea)
    const ctxRend = document.getElementById('rendimientoChart')?.getContext('2d');
    if (ctxRend) {
      const labelsRend = <?= json_encode($charts['fechas'] ?? []) ?>;
      const dataRend   = <?= json_encode($charts['rendimiento'] ?? []) ?>;

      new Chart(ctxRend, {
        type: 'line',
        data: {
          labels: labelsRend,
          datasets: [{
            label: 'Rendimiento',
            data: dataRend,
            borderColor: '#2563EB',
            tension: 0.4,
            fill: false
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: { x: { display: false }, y: { display: true } }
        }
      });
    }

    // Gastos (barras)
    const ctxGastos = document.getElementById('gastosChart')?.getContext('2d');
    if (ctxGastos) {
      const gastoMensual = <?= json_encode($charts['gasto_mensual'] ?? []) ?>;

      new Chart(ctxGastos, {
        type: 'bar',
        data: {
          labels: Object.keys(gastoMensual),
          datasets: [{
            label: 'Gasto',
            data: Object.values(gastoMensual),
            backgroundColor: '#2563EB',
            borderRadius: 4
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: { x: { display: false }, y: { display: true } }
        }
      });
    }

    // Mapa dashboard
const mapEl = document.getElementById('map');
if (mapEl) {
  const mapData = <?= json_encode($charts['mapa'] ?? []) ?>;

  console.log("mapData:", mapData); // <-- AQUÍ

  // evita doble init
  if (mapEl._leaflet_id) return;

  let map;
  if (Array.isArray(mapData) && mapData.length > 0) {
    const lastPoint = mapData[0];
    map = L.map('map').setView([lastPoint.lat, lastPoint.lng], 12);
  } else {
    map = L.map('map').setView([4.6097, -74.0817], 11);
  }

  L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    maxZoom: 20
  }).addTo(map);

  if (Array.isArray(mapData) && mapData.length > 0) {
    mapData.forEach(p => {
      L.marker([p.lat, p.lng]).addTo(map).bindPopup(p.name || '');
    });
  }

  setTimeout(() => map.invalidateSize(), 200); // recomendado
}


  });
</script>
<?php endif; ?>

</body>
</html>
