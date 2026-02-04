<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card p-4 shadow" style="width: 500px;">
        <h4 class="mb-3">Tu Primer Vehículo (Paso 2/2)</h4>
        <form action="?c=Auth&a=registerStep2" method="POST">
            <div class="row g-2 mb-3">
                <div class="col-6"><input type="text" name="nombre" class="form-control" placeholder="Apodo (ej: La Bestia)" required></div>
                <div class="col-6"><input type="text" name="marca" class="form-control" placeholder="Marca" required></div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6"><input type="text" name="modelo" class="form-control" placeholder="Modelo" required></div>
                <div class="col-6"><input type="number" name="capacidad_tanque" class="form-control" placeholder="Capacidad Tanque" required></div>
            </div>
            <!-- Campos ocultos por defecto para simplificar -->
            <input type="hidden" name="descripcion" value="Mi primer vehículo">
            <input type="hidden" name="tipo_combustible" value="Gasolina">
            <input type="hidden" name="unidad_combustible" value="Galones">
            <input type="hidden" name="unidad_consumo" value="km/gal">
            
            <button class="btn btn-success w-100">Finalizar Registro</button>
        </form>
    </div>
</body>
</html>
