<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card p-4 shadow" style="width: 400px;">
        <h4 class="mb-3">Registro (Paso 1/2)</h4>
        <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        
        <form action="?c=Auth&a=registerStep1" method="POST">
            <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
            <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="mb-3"><label>Contraseña</label><input type="password" name="password" class="form-control" required></div>
            <button class="btn btn-primary w-100">Siguiente ></button>
        </form>
    </div>
</body>
</html>
