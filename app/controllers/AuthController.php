<?php
/**
 * AuthController
 * Maneja autenticación y login con Google reCAPTCHA
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';

class AuthController {
    
    /**
     * Mostrar formulario de login
     */
    public function login() {
        // Si ya está autenticado, redirigir al dashboard
        AuthHelper::redirectIfAuthenticated();
        
        // Mostrar vista de login
        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Procesar login
     */
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=auth/login');
            exit();
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

        // Validar campos
        if (empty($username) || empty($password)) {
            SessionHelper::setFlash('danger', 'Por favor complete todos los campos');
            header('Location: /vetalmacen/public/index.php?url=auth/login');
            exit();
        }

        // Validar reCAPTCHA si está habilitado
        if (RECAPTCHA_ENABLED) {
            if (empty($recaptchaResponse)) {
                SessionHelper::setFlash('danger', 'Por favor complete el reCAPTCHA');
                header('Location: /vetalmacen/public/index.php?url=auth/login');
                exit();
            }

            // Verificar reCAPTCHA
            $recaptchaValid = $this->verifyRecaptcha($recaptchaResponse);
            
            if (!$recaptchaValid) {
                SessionHelper::setFlash('danger', 'Verificación reCAPTCHA fallida. Intente nuevamente');
                header('Location: /vetalmacen/public/index.php?url=auth/login');
                exit();
            }
        }

        // Autenticar usuario
        $usuarioModel = new Usuario();
        $user = $usuarioModel->authenticate($username, $password);

        if ($user) {
            // Login exitoso
            SessionHelper::setUser($user);
            SessionHelper::regenerate(); // Regenerar ID de sesión por seguridad
            
            header('Location: /vetalmacen/public/index.php?url=dashboard');
            exit();
        } else {
            // Login fallido
            SessionHelper::setFlash('danger', 'Usuario o contraseña incorrectos');
            header('Location: /vetalmacen/public/index.php?url=auth/login');
            exit();
        }
    }

    /**
     * Logout
     */
    public function logout() {
        SessionHelper::logout();
        header('Location: /vetalmacen/public/index.php?url=auth/login');
        exit();
    }

    /**
     * Verificar reCAPTCHA con Google
     */
    private function verifyRecaptcha($response) {
        $secretKey = RECAPTCHA_SECRET_KEY;
        $verifyUrl = RECAPTCHA_VERIFY_URL;

        // Preparar datos
        $data = [
            'secret' => $secretKey,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        // Hacer petición a Google
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($verifyUrl, false, $context);
        $resultJson = json_decode($result);

        return $resultJson->success ?? false;
    }

    /**
     * Cambiar contraseña del usuario actual
     */
    public function cambiarPassword() {
        AuthHelper::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $passwordActual = $_POST['password_actual'] ?? '';
            $passwordNuevo = $_POST['password_nuevo'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            // Validaciones
            if (empty($passwordActual) || empty($passwordNuevo) || empty($passwordConfirm)) {
                SessionHelper::setFlash('danger', 'Complete todos los campos');
                header('Location: /vetalmacen/public/index.php?url=usuarios/perfil');
                exit();
            }

            if ($passwordNuevo !== $passwordConfirm) {
                SessionHelper::setFlash('danger', 'Las contraseñas nuevas no coinciden');
                header('Location: /vetalmacen/public/index.php?url=usuarios/perfil');
                exit();
            }

            if (strlen($passwordNuevo) < 6) {
                SessionHelper::setFlash('danger', 'La contraseña debe tener al menos 6 caracteres');
                header('Location: /vetalmacen/public/index.php?url=usuarios/perfil');
                exit();
            }

            // Verificar contraseña actual
            $user = SessionHelper::getUser();
            $usuarioModel = new Usuario();
            $userData = $usuarioModel->getById($user['id']);

            if (!AuthHelper::verifyPassword($passwordActual, $userData['Password'])) {
                SessionHelper::setFlash('danger', 'La contraseña actual es incorrecta');
                header('Location: /vetalmacen/public/index.php?url=usuarios/perfil');
                exit();
            }

            // Actualizar contraseña
            $usuarioModel->Id = $user['id'];
            if ($usuarioModel->updatePassword($passwordNuevo)) {
                SessionHelper::setFlash('success', 'Contraseña actualizada exitosamente');
            } else {
                SessionHelper::setFlash('danger', 'Error al actualizar la contraseña');
            }

            header('Location: /vetalmacen/public/index.php?url=usuarios/perfil');
            exit();
        }
    }
}