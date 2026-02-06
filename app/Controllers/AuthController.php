<?php

namespace Controllers;

use Core\Controller;
use Core\Mailer;
use Models\User;
use Models\Vehiculo;

class AuthController extends Controller
{
    public function index()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('?c=Dashboard');
            return;
        }
        $this->view('auth/login');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=Auth');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($pass, $user['password'])) {
            $this->view('auth/login', ['error' => 'Credenciales incorrectas']);
            return;
        }

        // Revalidación por seguridad (legacy bloqueados): reenviar activación
        if ((int)$user['is_verified'] === 0) {
            $token = bin2hex(random_bytes(16));
            $userModel->setVerifyToken((int)$user['id'], $token, 24);

            $link = "https://gasolina.loopcraft.com.co/?c=Auth&a=verify&t=" . urlencode($token);

            $msg = "
                <div style='font-family: Arial, sans-serif; color:#111; line-height:1.5'>
                    <h2 style='margin:0 0 12px'>Verifica tu cuenta</h2>
                    <p>Hola " . htmlspecialchars($user['nombre']) . ",</p>
                    <p>Por seguridad necesitamos que verifiques tu correo para activar tu cuenta.</p>
                    <p style='margin:20px 0'>
                        <a href='{$link}' style='background:#111;color:#fff;padding:12px 18px;border-radius:10px;text-decoration:none;display:inline-block'>
                            Verificar cuenta
                        </a>
                    </p>
                    <p style='font-size:12px;color:#666'>Si no funciona el botón, copia este enlace:</p>
                    <p style='font-size:12px;color:#666;word-break:break-all'>{$link}</p>
                </div>
            ";

            $check = $userModel->findById((int)$user['id']);
            var_dump($check['token'], $check['token_expiry']);  // debug temporal

            $sent = Mailer::send($user['email'], 'Activa tu cuenta - Fleet Manager', $msg);

            if ($sent) {
                $this->view('auth/login', [
                    'success' => 'Tu cuenta no está activa. Te enviamos un correo para revalidar/activar tu cuenta.'
                ]);
            } else {
                $this->view('auth/login', [
                    'error' => 'Tu cuenta no está activa y no pudimos enviar el correo. Intenta más tarde.'
                ]);
            }
            return;
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['nombre'];

        $this->redirect('?c=Dashboard');
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        $this->redirect('?c=Auth');
    }

    public function registerStep1()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email  = trim($_POST['email'] ?? '');
            $passRaw = $_POST['password'] ?? '';

            if ($nombre === '' || $email === '' || $passRaw === '') {
                $this->view('auth/register_step1', ['error' => 'Completa todos los campos.']);
                return;
            }

            $passHash = password_hash($passRaw, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(16));

            $userModel = new User();
            $userId = $userModel->createWithToken($nombre, $email, $passHash, $token);

            if ($userId) {
                $_SESSION['temp_id'] = (int)$userId;
                $_SESSION['temp_email'] = $email;
                $_SESSION['temp_token'] = $token;

                $this->redirect('?c=Auth&a=registerStep2');
                return;
            }

            $this->view('auth/register_step1', ['error' => 'El correo electrónico ya está registrado.']);
            return;
        }

        $this->view('auth/register_step1');
    }

    public function registerStep2()
    {
        if (!isset($_SESSION['temp_id'], $_SESSION['temp_email'], $_SESSION['temp_token'])) {
            $this->redirect('?c=Auth&a=registerStep1');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $userId = (int)$_SESSION['temp_id'];
            $email = $_SESSION['temp_email'];
            $token = $_SESSION['temp_token'];

            $vehiculoModel = new Vehiculo();
            $data['foto'] = null;
            $vehiculoModel->save($data, $userId);

            $link = "https://gasolina.loopcraft.com.co/?c=Auth&a=verify&t=" . urlencode($token);

            $msg = "
                <div style='font-family: Arial, sans-serif; color:#111; line-height:1.5'>
                    <h2 style='margin:0 0 12px'>Bienvenido a Fleet Manager</h2>
                    <p>Activa tu cuenta para comenzar:</p>
                    <p style='margin:20px 0'>
                        <a href='{$link}' style='background:#111;color:#fff;padding:12px 18px;border-radius:10px;text-decoration:none;display:inline-block'>
                            Activar cuenta
                        </a>
                    </p>
                    <p style='font-size:12px;color:#666'>Si no funciona el botón, copia este enlace:</p>
                    <p style='font-size:12px;color:#666;word-break:break-all'>{$link}</p>
                </div>
            ";

            $sent = Mailer::send($email, 'Activa tu cuenta - Fleet Manager', $msg);

            if ($sent) {
                unset($_SESSION['temp_id'], $_SESSION['temp_email'], $_SESSION['temp_token']);
                $this->view('auth/register_success');
            } else {
                $this->view('auth/register_step2', ['error' => 'Hubo un error enviando el correo. Intenta de nuevo.']);
            }
            return;
        }

        $this->view('auth/register_step2');
    }

    public function verify()
    {
        $token = trim($_GET['t'] ?? '');
        if ($token === '') {
            $this->view('auth/login', ['error' => 'El enlace de activación es inválido.']);
            return;
        }

        $userModel = new User();
        if ($userModel->activateAccount($token)) {
            $this->view('auth/login', ['success' => 'Cuenta activada con éxito. Ya puedes iniciar sesión.']);
        } else {
            $this->view('auth/login', ['error' => 'El enlace de activación es inválido o expiró.']);
        }
    }

    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(16));
                $userModel->setResetToken($email, $token, 60);

                $link = "https://gasolina.loopcraft.com.co/?c=Auth&a=resetPassword&t=" . urlencode($token);

                $msg = "
                    <div style='font-family: Arial, sans-serif; color:#111; line-height:1.5'>
                        <h2 style='margin:0 0 12px'>Recuperación de contraseña</h2>
                        <p>Haz clic para crear una nueva contraseña:</p>
                        <p style='margin:20px 0'>
                            <a href='{$link}' style='background:#198754;color:#fff;padding:12px 18px;border-radius:10px;text-decoration:none;display:inline-block'>
                                Restablecer contraseña
                            </a>
                        </p>
                        <p style='font-size:12px;color:#666'>Si no funciona, copia este enlace:</p>
                        <p style='font-size:12px;color:#666;word-break:break-all'>{$link}</p>
                    </div>
                ";

                Mailer::send($email, 'Restablecer contraseña - Fleet Manager', $msg);
            }

            $this->view('auth/forgot_password', [
                'success' => 'Si el correo existe, recibirás instrucciones en breve.'
            ]);
            return;
        }

        $this->view('auth/forgot_password');
    }

    public function resetPassword()
    {
        $token = trim($_GET['t'] ?? '');

        $userModel = new User();
        $user = $userModel->findByToken($token); // en User.php nuevo es reset_token

        if (!$user) {
            $this->view('auth/login', ['error' => 'Token de recuperación inválido o expirado.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pass = $_POST['password'] ?? '';
            if (strlen($pass) < 6) {
                $this->view('auth/reset_password', [
                    'token' => $token,
                    'error' => 'La contraseña debe tener al menos 6 caracteres.'
                ]);
                return;
            }

            $passHash = password_hash($pass, PASSWORD_DEFAULT);

            if ($userModel->updatePassword((int)$user['id'], $passHash)) {
                $this->view('auth/login', ['success' => 'Contraseña actualizada correctamente. Inicia sesión.']);
            } else {
                $this->view('auth/reset_password', [
                    'token' => $token,
                    'error' => 'Error al actualizar. Intenta de nuevo.'
                ]);
            }
            return;
        }

        $this->view('auth/reset_password', ['token' => $token]);
    }

        // --- VISTA PERFIL ---
    public function profile()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?c=Auth');
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($_SESSION['user_id']);

        $this->view('auth/profile', ['user' => $user]);
    }

    // --- ACTUALIZAR DATOS (NOMBRE Y FOTO) ---
    public function updateProfile()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=Auth');
            return;
        }

        $userId = $_SESSION['user_id'];
        $nombre = trim($_POST['nombre'] ?? '');
        
        $userModel = new User();

        // 1. Actualizar Nombre
        if ($nombre !== '') {
            $userModel->updateNombre($userId, $nombre);
            $_SESSION['user_name'] = $nombre; // Actualizar sesión
        }

        // 2. Subir Foto (si viene una)
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($ext, $allowed)) {
                $filename = uniqid('user_') . '.' . $ext;
                $uploadPath = 'uploads/' . $filename; // Asegúrate que la carpeta 'public/uploads' exista
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
                    $userModel->updateFoto($userId, $filename);
                }
            }
        }

        $this->redirect('?c=Auth&a=profile');
    }

    // --- CAMBIAR CONTRASEÑA (DESDE PERFIL) ---
    public function changePassword()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?c=Auth');
            return;
        }

        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        $userModel = new User();
        $user = $userModel->findById($_SESSION['user_id']);

        // Validaciones
        if (!password_verify($currentPass, $user['password'])) {
            $this->view('auth/profile', ['user' => $user, 'error_pass' => 'La contraseña actual no es correcta.']);
            return;
        }

        if (strlen($newPass) < 6) {
            $this->view('auth/profile', ['user' => $user, 'error_pass' => 'La nueva contraseña debe tener al menos 6 caracteres.']);
            return;
        }

        if ($newPass !== $confirmPass) {
            $this->view('auth/profile', ['user' => $user, 'error_pass' => 'Las nuevas contraseñas no coinciden.']);
            return;
        }

        // Guardar
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $userModel->updatePassword($userId, $newHash);

        $this->view('auth/profile', ['user' => $user, 'success_pass' => 'Contraseña actualizada correctamente.']);
    }

}
