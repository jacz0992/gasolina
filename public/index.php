<?php
session_start();

// 1. Autocarga de clases (PSR-4 básico)
// Convierte "Models\Vehiculo" -> "../app/Models/Vehiculo.php"
spl_autoload_register(function ($class) {
    // Reemplazar barra invertida por barra normal (Windows/Linux compatibilidad)
    $class = str_replace('\\', '/', $class);
    
    // Mapeo básico: Namespace raíz 'Config' o 'Core' o 'Models' o 'Controllers' -> carpeta app/
    $file = __DIR__ . '/../app/' . $class . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});


// 2. Enrutamiento Simple
// URL esperada: index.php?c=Dashboard&a=index
$controllerParam = $_GET['c'] ?? 'Auth'; // Por defecto ir al Login (Auth)
$actionParam     = $_GET['a'] ?? 'index';

// Formatear nombre: 'dashboard' -> 'Controllers\DashboardController'
$controllerName = 'Controllers\\' . ucfirst($controllerParam) . 'Controller';

// 3. Despachar petición
if (class_exists($controllerName)) {
    $controller = new $controllerName();
    
    if (method_exists($controller, $actionParam)) {
        // Ejecutar la acción
        $controller->$actionParam();
    } else {
        die("Error 404: El método '$actionParam' no existe en '$controllerName'.");
    }
} else {
    die("Error Fatal: No se pudo cargar la clase '$controllerName'. <br> Verifique que el archivo exista en app/Controllers/ y tenga el namespace correcto.");
}

?>
