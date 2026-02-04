<?php
namespace Core;

class Controller {
    // Método para cargar vistas y pasarles datos
    protected function view($viewName, $data = []) {
        // Extrae el array asociativo a variables
        // Ej: ['user' => 'Juan'] se convierte en $user = 'Juan'
        extract($data);
        
        $file = "../app/Views/$viewName.php";
        if (file_exists($file)) {
            require_once $file;
        } else {
            die("Vista no encontrada: $viewName");
        }
    }

    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
}
?>
