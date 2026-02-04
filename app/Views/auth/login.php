<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Fleet Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow border-0 p-4" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
            <div class="mb-3 text-primary"><i class="bi bi-speedometer2 display-4"></i></div>
            <h4 class="fw-bold">Bienvenido de nuevo</h4>
            <p class="text-muted small">Gestiona tu flota de manera inteligente</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger py-2 small"><?= $error ?></div>
        <?php endif; ?>
        <?php if(isset($success)): ?>
            <div class="alert alert-success py-2 small"><?= $success ?></div>
        <?php endif; ?>

        <form action="?c=Auth&a=login" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">CORREO ELECTRÓNICO</label>
                <input type="email" name="email" class="form-control" placeholder="nombre@ejemplo.com" required>
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label class="form-label small fw-bold text-muted">CONTRASEÑA</label>
                    <!-- ENLACE OLVIDÉ CONTRASEÑA -->
                    <a href="?c=Auth&a=forgotPassword" class="small text-decoration-none">¿Olvidaste tu contraseña?</a>
                </div>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Ingresar</button>
        </form>

        <hr class="my-4">

        <div class="text-center">
            <p class="small text-muted mb-2">¿Aún no tienes cuenta?</p>
            <!-- ENLACE REGISTRO -->
            <a href="?c=Auth&a=registerStep1" class="btn btn-outline-secondary w-100 fw-bold">Crear Cuenta Nueva</a>
        </div>
    </div>
</body>
</html>
