<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow border-0 p-4" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
            <h4 class="fw-bold">Recuperar Contraseña</h4>
            <p class="text-muted small">Ingresa tu correo y te enviaremos un enlace.</p>
        </div>

        <?php if(isset($success)): ?>
            <div class="alert alert-success py-2 small"><?= $success ?></div>
        <?php endif; ?>

        <form action="?c=Auth&a=forgotPassword" method="POST">
            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">CORREO ELECTRÓNICO</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Enviar Enlace</button>
        </form>

        <div class="text-center mt-3">
            <a href="?c=Auth" class="text-decoration-none small text-secondary">Volver al Login</a>
        </div>
    </div>
</body>
</html>
