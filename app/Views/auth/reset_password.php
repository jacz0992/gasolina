<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow border-0 p-4" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
            <h4 class="fw-bold">Nueva Contraseña</h4>
            <p class="text-muted small">Ingresa tu nueva clave segura.</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger py-2 small"><?= $error ?></div>
        <?php endif; ?>

        <!-- El token viaja en la URL (?t=...), lo mantenemos en el action -->
        <form action="?c=Auth&a=resetPassword&t=<?= htmlspecialchars($token) ?>" method="POST">
            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">NUEVA CONTRASEÑA</label>
                <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
            </div>
            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">Guardar Contraseña</button>
        </form>
    </div>
</body>
</html>
