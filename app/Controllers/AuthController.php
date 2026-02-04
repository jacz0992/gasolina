<?php
namespace Controllers;
use Core\Controller;
use Core\Mailer;
use Models\User;
use Models\Vehiculo;

class AuthController extends Controller {
    
    // --- PÁGINA PRINCIPAL (LOGIN) ---
    public function index() {
        // Si ya está logueado, ir al Dashboard directamente
        if (isset($_SESSION['user_id'])) {
            $this->redirect('?c=Dashboard');
        }
        $this->view('auth/login');
    }

    // --- PROCESAR LOGIN ---
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $pass  = $_POST['password'];
            
            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($pass, $user['password'])) {
                // Verificar si confirmó el correo
                if ($user['is_verified'] == 0) {
                    $this->view('auth/login', ['error' => 'Tu cuenta no está activa. Revisa tu correo electrónico para activarla.']);
                    return;
                }

                // Login Exitoso: Guardar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $this->redirect('?c=Dashboard');
            } else {
                $this->view('auth/login', ['error' => 'Credenciales incorrectas']);
            }
        }
    }

    // --- CIERRE DE SESIÓN (LOGOUT) ---
    public function logout() {
        session_unset();
        session_destroy();
        $this->redirect('?c=Auth');
    }

    // --- REGISTRO PASO 1: DATOS USUARIO ---
    public function registerStep1() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'];
            $email = $_POST['email'];
            $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(16)); // Token de activación

            $userModel = new User();
            // Intenta crear usuario pendiente
            $userId = $userModel->createWithToken($nombre, $email, $pass, $token);

            if ($userId) {
                // Guardar datos temporales en sesión para el paso 2
                $_SESSION['temp_id'] = $userId;
                $_SESSION['temp_email'] = $email;
                $_SESSION['temp_token'] = $token;
                
                $this->redirect('?c=Auth&a=registerStep2');
            } else {
                $this->view('auth/register_step1', ['error' => 'El correo electrónico ya está registrado.']);
            }
        } else {
            $this->view('auth/register_step1');
        }
    }

    // --- REGISTRO PASO 2: PRIMER VEHÍCULO ---
    public function registerStep2() {
        // Seguridad: No dejar entrar si no pasó el paso 1
        if (!isset($_SESSION['temp_id'])) {
            $this->redirect('?c=Auth&a=registerStep1');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $userId = $_SESSION['temp_id'];
            
            // Guardar Vehículo
            $vehiculoModel = new Vehiculo();
            // Aseguramos que 'foto' no rompa el modelo aunque venga vacía
            $data['foto'] = null; 
            $vehiculoModel->save($data, $userId);

            // ENVIAR CORREO DE ACTIVACIÓN (SMTP)
            $link = "https://gasolina.loopcraft.com.co/?c=Auth&a=verify&t=" . $_SESSION['temp_token'];
            
            $msg = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <h1 style='color: #0d6efd;'>Bienvenido a Fleet Manager</h1>
                <p>Hola,</p>
                <p>Estás a un paso de comenzar. Por favor, activa tu cuenta haciendo clic en el siguiente botón:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='$link' style='background-color: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Activar Cuenta</a>
                </p>
                <p style='font-size: 12px; color: #999;'>Si no funciona el botón, copia este enlace: $link</p>
            </div>";
            
            // Enviar usando la clase Mailer
            if(Mailer::send($_SESSION['temp_email'], "Activa tu cuenta - Fleet Manager", $msg)) {
                // Limpiar sesión temporal
                unset($_SESSION['temp_id']);
                unset($_SESSION['temp_email']);
                unset($_SESSION['temp_token']);
                
                $this->view('auth/register_success');
            } else {
                // Si falla el envío (raro con SMTP), mostrar error pero no borrar sesión para reintentar
                $this->view('auth/register_step2', ['error' => 'Hubo un error enviando el correo. Intenta de nuevo.']);
            }

        } else {
            $this->view('auth/register_step2');
        }
    }

    // --- VERIFICAR CUENTA (LINK DEL CORREO) ---
    public function verify() {
        $token = $_GET['t'] ?? '';
        $userModel = new User();
        
        if ($userModel->activateAccount($token)) {
            $this->view('auth/login', ['success' => '¡Cuenta activada con éxito! Ya puedes iniciar sesión.']);
        } else {
            $this->view('auth/login', ['error' => 'El enlace de activación es inválido o ya fue utilizado.']);
        }
    }

    // --- OLVIDÉ CONTRASEÑA (VISTA FORMULARIO) ---
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if ($user) {
                // Generar token de recuperación
                $token = bin2hex(random_bytes(16));
                $userModel->setResetToken($email, $token);

                // Enviar correo
                $link = "https://gasolina.loopcraft.com.co/?c=Auth&a=resetPassword&t=" . $token;
                $msg = "<h1>Recuperación de Contraseña</h1><p>Haz clic aquí para cambiar tu clave: <a href='$link'>Restablecer</a></p>";
                Mailer::send($email, "Restablecer Contraseña", $msg);
            }

            // Mensaje genérico por seguridad (para no revelar qué emails existen)
            $this->view('auth/forgot_password', ['success' => 'Si el correo existe, recibirás instrucciones en breve.']);
        } else {
            $this->view('auth/forgot_password');
        }
    }

    // --- RESTABLECER CONTRASEÑA (NUEVA CLAVE) ---
    public function resetPassword() {
        $token = $_GET['t'] ?? '';
        $userModel = new User();
        $user = $userModel->findByToken($token);

        if (!$user) {
            $this->view('auth/login', ['error' => 'Token de recuperación inválido o expirado.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pass = $_POST['password'];
            $passHash = password_hash($pass, PASSWORD_DEFAULT);
            
            if($userModel->updatePassword($user['id'], $passHash)) {
                $this->view('auth/login', ['success' => 'Contraseña actualizada correctamente. Inicia sesión.']);
            } else {
                $this->view('auth/reset_password', ['token' => $token, 'error' => 'Error al actualizar. Intenta de nuevo.']);
            }
        } else {
            $this->view('auth/reset_password', ['token' => $token]);
        }
    }
}
?>
