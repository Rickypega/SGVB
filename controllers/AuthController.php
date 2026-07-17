<?php
declare(strict_types=1);

/**
 * Controlador de Autenticación (Login, Registro y Logout)
 */
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

            if (strlen($cedula) < 5 || !preg_match('/^[a-zA-Z0-9\-\.\s]+$/', $cedula)) {
                $_SESSION['error'] = 'El formato de cédula/DNI no es válido (mín. 5 caracteres, alfanumérico).';
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

            // Verificar si correo o cédula ya existen
            if (Usuario::porCorreo($correo) !== null) {
                $_SESSION['error'] = 'El correo electrónico ingresado ya se encuentra registrado.';
                require_once __DIR__ . '/../views/registro.php';
                return;
            }

            if (Usuario::porCedula($cedula) !== null) {
                $_SESSION['error'] = 'El número de cédula ingresado ya se encuentra vinculado a otro usuario.';
                require_once __DIR__ . '/../views/registro.php';
                return;
            }

            // Crear el usuario lector por defecto (rol_id = 2)
            $nuevoUsuario = Usuario::crear($nombre, $correo, $password, $cedula, $fechaNacimiento, 2);

            if ($nuevoUsuario) {
                $_SESSION['usuario'] = $nuevoUsuario;
                $_SESSION['exito'] = '¡Cuenta creada con éxito! Tu saldo de demostración ($30.00) y verificación de cédula se han activado.';
                header('Location: ' . BASE_URL . 'estandar/panel');
                exit;
            } else {
                $_SESSION['error'] = 'Hubo un problema al registrar la cuenta. Es posible que el número de cédula ya esté vinculado a otro usuario.';
            }
        }

        require_once __DIR__ . '/../views/registro.php';
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
