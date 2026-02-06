<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Fleet Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="public/css/orvion.css" rel="stylesheet">
</head>
<body style="background-color: #F2F2F2;">

    <!-- Navbar simplificada (o incluye la misma del dashboard) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm px-3 mb-4">
        <a class="navbar-brand fw-bold" href="?c=Dashboard">
            <i class="bi bi-speedometer2"></i> Fleet Manager
        </a>
        <div class="ms-auto">
            <a href="?c=Dashboard" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </nav>

    <div class="container py-4" style="max-width: 900px;">
        <h2 class="fw-bold mb-4">Mi Perfil</h2>

        <div class="row g-4">
            <!-- COLUMNA IZQUIERDA: FOTO Y DATOS -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <form action="?c=Auth&a=updateProfile" method="POST" enctype="multipart/form-data">
                            
                            <!-- Foto de Perfil -->
                            <div class="position-relative d-inline-block mb-3">
                                <?php if (!empty($user['foto'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($user['foto']) ?>" 
                                         class="rounded-circle object-fit-cover border" 
                                         style="width: 120px; height: 120px;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" 
                                         style="width: 120px; height: 120px; font-size: 3rem; color: #ccc;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <label for="fotoInput" class="position-absolute bottom-0 end-0 bg-dark text-white rounded-circle p-2 cursor-pointer" 
                                       style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                                       title="Cambiar foto">
                                    <i class="bi bi-camera-fill small"></i>
                                </label>
                                <input type="file" name="foto" id="fotoInput" class="d-none" accept="image/*" onchange="this.form.submit()">
                            </div>

                            <h5 class="fw-bold"><?= htmlspecialchars($user['nombre']) ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars($user['email']) ?></p>

                            <hr class="my-4">

                            <div class="text-start">
                                <label class="small text-muted fw-bold mb-1">NOMBRE COMPLETO</label>
                                <input type="text" name="nombre" class="form-control mb-3" value="<?= htmlspecialchars($user['nombre']) ?>">
                                
                                <label class="small text-muted fw-bold mb-1">CORREO ELECTRÓNICO</label>
                                <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" disabled readonly>
                                <div class="form-text small"><i class="bi bi-lock-fill"></i> El correo no se puede editar aquí.</div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill mt-4">Guardar Cambios</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: CAMBIAR CONTRASEÑA -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2"></i>Seguridad</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if (isset($error_pass)): ?>
                            <div class="alert alert-danger small py-2"><?= $error_pass ?></div>
                        <?php endif; ?>

                        <?php if (isset($success_pass)): ?>
                            <div class="alert alert-success small py-2"><?= $success_pass ?></div>
                        <?php endif; ?>

                        <form action="?c=Auth&a=changePassword" method="POST">
                            <div class="mb-3">
                                <label class="small text-muted fw-bold mb-1">CONTRASEÑA ACTUAL</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted fw-bold mb-1">NUEVA CONTRASEÑA</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>

                            <div class="mb-4">
                                <label class="small text-muted fw-bold mb-1">CONFIRMAR NUEVA CONTRASEÑA</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-secondary rounded-pill px-4">Actualizar Contraseña</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
