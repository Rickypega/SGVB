<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';

/**
 * Controlador para Configuración de Cuenta de Usuario (Tarea 12)
 */
class UsuarioController {

    /**
     * Muestra la vista de configuración del usuario
     */
    public function configuracion(): void {
        $usuario = $this->requerirAutenticacion();
        $usuarioRefrescado = Usuario::porId($usuario->id);
        if ($usuarioRefrescado) {
            $usuario = $usuarioRefrescado;
            $_SESSION['usuario'] = $usuario;
        }

        require_once __DIR__ . '/../views/usuario/configuracion.php';
    }

    /**
     * Actualiza nombre, correo y fecha de nacimiento verificando contraseña de confirmación
     */
    public function actualizarDatos(): void {
        $usuario = $this->requerirAutenticacion();
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $fechaNacimiento = trim($_POST['fecha_nacimiento'] ?? '');
        $passwordConfirmar = $_POST['password_confirmar_datos'] ?? '';

        $resultado = Usuario::actualizarPerfil($usuario->id, $nombre, $correo, $fechaNacimiento, $passwordConfirmar);

        if ($resultado['exito']) {
            $_SESSION['exito'] = $resultado['mensaje'];
            $usuarioActual = Usuario::porId($usuario->id);
            if ($usuarioActual) {
                $_SESSION['usuario'] = $usuarioActual;
            }
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        header('Location: ' . BASE_URL . 'usuario/configuracion');
        exit;
    }

    /**
     * Cambia la contraseña del usuario
     */
    public function cambiarPassword(): void {
        $usuario = $this->requerirAutenticacion();
        $passwordAnterior = $_POST['password_anterior'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordConfirmar = $_POST['password_confirmar'] ?? '';

        if ($passwordNueva !== $passwordConfirmar) {
            $_SESSION['error'] = 'La nueva contraseña y su confirmación no coinciden.';
            header('Location: ' . BASE_URL . 'usuario/configuracion');
            exit;
        }

        $resultado = Usuario::cambiarPassword($usuario->id, $passwordAnterior, $passwordNueva);

        if ($resultado['exito']) {
            $_SESSION['exito'] = $resultado['mensaje'];
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        header('Location: ' . BASE_URL . 'usuario/configuracion');
        exit;
    }

    /**
     * Elimina la cuenta (si el usuario es estándar rol_id == 2)
     */
    public function eliminarCuenta(): void {
        $usuario = $this->requerirAutenticacion();

        if ($usuario->rol_id === 1) {
            $_SESSION['error'] = 'El Administrador General no puede eliminar su cuenta del sistema.';
            header('Location: ' . BASE_URL . 'usuario/configuracion');
            exit;
        }

        $confirmacion = trim($_POST['confirmacion_eliminar'] ?? '');
        if (strtolower($confirmacion) !== 'eliminar') {
            $_SESSION['error'] = 'Debes escribir la palabra exacta "ELIMINAR" para confirmar la desactivación y borrado de tu cuenta.';
            header('Location: ' . BASE_URL . 'usuario/configuracion');
            exit;
        }

        if (Usuario::eliminarCuenta($usuario->id)) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['exito'] = 'Tu cuenta de usuario, préstamos en transcurso y suscripciones han sido eliminados de SGBV. ¡Gracias por habernos visitado!';
            header('Location: ' . BASE_URL . 'home');
            exit;
        } else {
            $_SESSION['error'] = 'Ocurrió un error al intentar eliminar tu cuenta.';
            header('Location: ' . BASE_URL . 'usuario/configuracion');
            exit;
        }
    }

    private function requerirAutenticacion(): Usuario {
        if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
            $_SESSION['error'] = 'Debes iniciar sesión para acceder a la configuración de tu cuenta.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        return $_SESSION['usuario'];
    }
}
