<?php
declare(strict_types=1);

/**
 * Controlador de Autenticación (Login, Registro y Logout)
 */
require_once __DIR__ . '/../libs/JceService.php';

class AuthController {

    /**
     * Maneja el inicio de sesión
     */
    public function login(): void {
        // Si ya hay sesión activa, redirigir según el rol
        if (isset($_SESSION['usuario']) && $_SESSION['usuario'] instanceof Usuario) {
            $this->redirigirPorRol($_SESSION['usuario']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = trim($_POST['correo'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($correo) || empty($password)) {
                $_SESSION['error'] = 'Por favor, completa todos los campos del formulario.';
                require_once __DIR__ . '/../views/login.php';
                return;
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'El formato de correo electrónico ingresado no es válido.';
                require_once __DIR__ . '/../views/login.php';
                return;
            }

            $usuario = Usuario::porCorreo($correo);

            if ($usuario && $usuario->verificarPassword($password)) {
                if (!$usuario->correo_verificado) {
                    $_SESSION['warning'] = 'Debes activar tu cuenta. Revisa tu correo electrónico para verificar tu enlace.';
                    require_once __DIR__ . '/../views/login.php';
                    return;
                }
                
                // Guardar el objeto Usuario completo en sesión tal como requiere la arquitectura
                $_SESSION['usuario'] = $usuario;
                $_SESSION['exito'] = '¡Bienvenido de nuevo, ' . htmlspecialchars($usuario->nombre) . '!';
                $this->redirigirPorRol($usuario);
                return;
            } else {
                $_SESSION['error'] = 'Correo electrónico o contraseña incorrectos.';
            }
        }

        require_once __DIR__ . '/../views/login.php';
    }

    /**
     * Maneja el registro de nuevos lectores estándares
     */
    public function registro(): void {
        if (isset($_SESSION['usuario']) && $_SESSION['usuario'] instanceof Usuario) {
            $this->redirigirPorRol($_SESSION['usuario']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $password = $_POST['password'] ?? '';
            $cedula = trim($_POST['cedula'] ?? '');
            $fechaNacimiento = trim($_POST['fecha_nacimiento'] ?? '');

            if (empty($nombre) || empty($correo) || empty($password) || empty($cedula) || empty($fechaNacimiento)) {
                $_SESSION['error'] = 'Por favor, completa todos los campos obligatorios para el registro.';
                require_once __DIR__ . '/../views/registro.php';
                return;
            }

            if (strlen($nombre) < 3 || strlen($nombre) > 100) {
                $_SESSION['error'] = 'El nombre completo debe tener entre 3 y 100 caracteres.';
                require_once __DIR__ . '/../views/registro.php';
                return;
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Por favor, ingresa una dirección de correo electrónico válida.';
                require_once __DIR__ . '/../views/registro.php';
                return;
            }

            if (strlen($password) < 6) {
                $_SESSION['error'] = 'La contraseña debe tener un mínimo de 6 caracteres por seguridad.';
                require_once __DIR__ . '/../views/registro.php';
                return;
            }

            $validacionJce = JceService::validarCedula($cedula, $nombre, $fechaNacimiento);
            if (!$validacionJce['valida']) {
                $_SESSION['error'] = 'Validación JCE fallida: ' . $validacionJce['mensaje'];
                require_once __DIR__ . '/../views/registro.php';
                return;
            }

            $fechaNacObj = DateTime::createFromFormat('Y-m-d', $fechaNacimiento);
            if (!$fechaNacObj || $fechaNacObj->format('Y-m-d') !== $fechaNacimiento) {
                $_SESSION['error'] = 'La fecha de nacimiento no tiene un formato válido (AAAA-MM-DD).';
                require_once __DIR__ . '/../views/registro.php';
                return;
            }

            $hoy = new DateTime();
            if ($fechaNacObj > $hoy) {
                $_SESSION['error'] = 'La fecha de nacimiento no puede ser una fecha futura.';
                require_once __DIR__ . '/../views/registro.php';
                return;
            }

            $edad = $hoy->diff($fechaNacObj)->y;
            if ($edad > 120 || $edad < 5) {
                $_SESSION['error'] = 'La edad calculada desde la fecha de nacimiento no se encuentra en un rango permitido para el registro.';
                require_once __DIR__ . '/../views/registro.php';
                return;
            }

            // Crear el usuario
            try {
                $tokenVerificacion = bin2hex(random_bytes(32));
                // rol_id (2) es sobreescrito a 1 si es el primer usuario dentro de crear()
                $nuevoUsuario = Usuario::crear($nombre, $correo, $password, $cedula, $fechaNacimiento, 2, $tokenVerificacion);

                require_once __DIR__ . '/../libs/MailService.php';
                
                // Si es admin (primer usuario), ya está verificado, no enviar correo de activación
                if ($nuevoUsuario->rol_id === 1) {
                    $_SESSION['exito'] = '¡Cuenta de Administrador creada con éxito! Ya puedes iniciar sesión.';
                } else {
                    MailService::enviarCorreoActivacion($correo, $nombre, $tokenVerificacion);
                    $_SESSION['exito'] = '¡Cuenta creada con éxito! Por favor revisa tu correo electrónico para activarla.';
                }
                
                header('Location: ' . BASE_URL . 'usuario/login');
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }

        require_once __DIR__ . '/../views/registro.php';
    }

    /**
     * Muestra y procesa el formulario de recuperación de contraseña
     */
    public function recuperarPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = trim($_POST['correo'] ?? '');
            $cedula = trim($_POST['cedula'] ?? '');
            
            if (empty($correo) || empty($cedula)) {
                $_SESSION['error'] = 'Por favor ingresa tu correo y tu cédula.';
            } else {
                $usuario = Usuario::porCorreo($correo);
                // Validar que el correo existe y la cédula coincida (hash)
                if ($usuario && hash('sha256', $cedula) === $usuario->cedula) {
                    $token = bin2hex(random_bytes(32));
                    $pdo = Database::getConnection();
                    $stmt = $pdo->prepare("UPDATE usuarios SET token_recuperacion = :token, expiracion_token = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = :id");
                    $stmt->execute(['token' => $token, 'id' => $usuario->id]);
                    
                    require_once __DIR__ . '/../libs/MailService.php';
                    
                    try {
                        MailService::enviarCorreoRecuperacion($usuario->correo, $usuario->nombre, $token);
                        $_SESSION['exito'] = 'Si los datos coinciden con un usuario registrado, hemos enviado un enlace de recuperación a tu correo.';
                    } catch (\Exception $e) {
                        $_SESSION['error'] = 'No se pudo enviar el correo de recuperación. Verifique la configuración SMTP.';
                        // error_log($e->getMessage()); // Descomentar para depurar
                    }
                } else {
                    // Mensaje genérico para evitar enumeración de cuentas
                    $_SESSION['exito'] = 'Si los datos coinciden con un usuario registrado, hemos enviado un enlace de recuperación a tu correo.';
                }
            }
        }
        require_once __DIR__ . '/../views/recuperar_password.php';
    }

    /**
     * Muestra y procesa el formulario de restablecimiento de contraseña
     */
    public function restablecerPassword(): void {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        
        if (empty($token)) {
            $_SESSION['error'] = 'Enlace de recuperación inválido o faltante.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE token_recuperacion = :token AND expiracion_token > NOW() LIMIT 1");
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();

        if (!$row) {
            $_SESSION['error'] = 'El enlace de recuperación no es válido o ha expirado.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmar = $_POST['confirmar_password'] ?? '';
            
            if (strlen($password) < 6) {
                $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
            } elseif ($password !== $confirmar) {
                $_SESSION['error'] = 'Las contraseñas no coinciden.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmtUpdate = $pdo->prepare("UPDATE usuarios SET password = :pass, token_recuperacion = NULL, expiracion_token = NULL WHERE id = :id");
                $stmtUpdate->execute(['pass' => $hash, 'id' => $row['id']]);
                
                $_SESSION['exito'] = 'Tu contraseña ha sido restablecida con éxito. Puedes iniciar sesión.';
                header('Location: ' . BASE_URL . 'login?password_updated=1');
                exit;
            }
        }

        require_once __DIR__ . '/../views/restablecer_password.php';
    }

    /**
     * Cierra la sesión activa
     */
    public function logout(): void {
        unset($_SESSION['usuario']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        session_start();
        $_SESSION['exito'] = 'Has cerrado sesión correctamente.';
        header('Location: ' . BASE_URL . 'home');
        exit;
    }

    /**
     * Redirige al usuario según su rol_id
     */
    private function redirigirPorRol(Usuario $usuario): void {
        if ($usuario->rol_id === 1) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
        } else {
            header('Location: ' . BASE_URL . 'estandar/panel');
        }
        exit;
    }
}
