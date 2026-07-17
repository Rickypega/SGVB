<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/CryptoHelper.php';

/**
 * Clase Usuario
 * Mapeo estricto y lógica de negocio para la tabla 'usuarios'
 */
class Usuario {
    public int $id;
    public string $nombre;
    public string $correo;
    public string $password;
    public string $cedula;
    public string $fecha_nacimiento;
    public int $rol_id;
    public bool $cedula_verificada;
    public bool $correo_verificado;
    public float $saldo;
    public string $fecha_registro;

    public function __construct(
        int $id = 0,
        string $nombre = '',
        string $correo = '',
        string $password = '',
        string $cedula = '',
        string $fecha_nacimiento = '',
        int $rol_id = 2,
        bool $cedula_verificada = false,
        bool $correo_verificado = false,
        float $saldo = 0.00,
        string $fecha_registro = ''
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->correo = $correo;
        $this->password = $password;
        $this->cedula = $cedula;
        $this->fecha_nacimiento = $fecha_nacimiento;
        $this->rol_id = $rol_id;
        $this->cedula_verificada = $cedula_verificada;
        $this->correo_verificado = $correo_verificado;
        $this->saldo = $saldo;
        $this->fecha_registro = $fecha_registro;
    }

    /**
     * Verifica si la contraseña proporcionada coincide con el hash almacenado
     *
     * @param string $inputPassword Contraseña en texto plano
     * @return bool
     */
    public function verificarPassword(string $inputPassword): bool {
        return password_verify($inputPassword, $this->password);
    }

    /**
     * Comprueba si el usuario es mayor o igual a 18 años calculando
     * la diferencia entre fecha_nacimiento y la fecha actual.
     *
     * @return bool
     */
    public function esMayorDeEdad(): bool {
        if (empty($this->fecha_nacimiento)) {
            return false;
        }

        try {
            $fechaNac = new DateTime($this->fecha_nacimiento);
            $hoy = new DateTime();
            $diferencia = $hoy->diff($fechaNac);
            return $diferencia->y >= 18;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Debita el monto indicado del saldo del usuario de forma segura
     * y actualiza el registro en base de datos.
     *
     * @param float $monto
     * @return bool
     */
    public function debitarSaldo(float $monto): bool {
        if ($monto <= 0 || $this->saldo < $monto) {
            return false;
        }

        $nuevoSaldo = round($this->saldo - $monto, 2);
        $pdo = Database::getConnection();
        
        $stmt = $pdo->prepare("UPDATE usuarios SET saldo = :saldo WHERE id = :id");
        $exito = $stmt->execute([
            'saldo' => $nuevoSaldo,
            'id' => $this->id
        ]);

        if ($exito) {
            $this->saldo = $nuevoSaldo;
            return true;
        }

        return false;
    }

    /**
     * Recarga o añade fondos a la billetera virtual (para demostración/cobros)
     *
     * @param float $monto
     * @return bool
     */
    public function recargarSaldo(float $monto): bool {
        if ($monto <= 0) {
            return false;
        }

        $nuevoSaldo = round($this->saldo + $monto, 2);
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("UPDATE usuarios SET saldo = :saldo WHERE id = :id");
        $exito = $stmt->execute([
            'saldo' => $nuevoSaldo,
            'id' => $this->id
        ]);

        if ($exito) {
            $this->saldo = $nuevoSaldo;
            return true;
        }

        return false;
    }

    /**
     * Busca un usuario por su ID de base de datos
     *
     * @param int $id
     * @return Usuario|null
     */
    public static function porId(int $id): ?Usuario {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row) {
            return self::crearDesdeArray($row);
        }
        return null;
    }

    /**
     * Busca un usuario por su correo electrónico
     *
     * @param string $correo
     * @return Usuario|null
     */
    public static function porCorreo(string $correo): ?Usuario {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo LIMIT 1");
        $stmt->execute(['correo' => $correo]);
        $row = $stmt->fetch();

        if ($row) {
            return self::crearDesdeArray($row);
        }
        return null;
    }

    /**
     * Busca un usuario por su número de cédula (soportando encriptado y texto plano legado)
     *
     * @param string $cedula
     * @return Usuario|null
     */
    public static function porCedula(string $cedula): ?Usuario {
        $pdo = Database::getConnection();
        $cedulaEnc = CryptoHelper::encrypt($cedula);

        // Buscamos tanto en formato encriptado como en texto plano anterior por retrocompatibilidad
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cedula = :cedula_enc OR cedula = :cedula_plana LIMIT 1");
        $stmt->execute([
            'cedula_enc' => $cedulaEnc,
            'cedula_plana' => trim($cedula)
        ]);
        $row = $stmt->fetch();

        if ($row) {
            return self::crearDesdeArray($row);
        }
        return null;
    }

    /**
     * Registra un nuevo usuario en la base de datos
     *
     * @param string $nombre
     * @param string $correo
     * @param string $passwordTextoPlano
     * @param string $cedula
     * @param string $fechaNacimiento
     * @param int $rolId
     * @return Usuario|null
     */
    public static function crear(
        string $nombre,
        string $correo,
        string $passwordTextoPlano,
        string $cedula,
        string $fechaNacimiento,
        int $rolId = 2
    ): ?Usuario {
        $pdo = Database::getConnection();
        
        $hashPassword = password_hash($passwordTextoPlano, PASSWORD_DEFAULT);
        $cedulaEnc = CryptoHelper::encrypt($cedula);
        // Al registrar, el lector inicia con cédula verificada en 1 para facilitar demos instantáneas
        $cedulaVerificada = 1;
        $correoVerificado = 1;
        $saldoInicial = 30.00; // Bono inicial de billetera virtual para nuevas cuentas

        $sql = "INSERT INTO usuarios (nombre, correo, password, cedula, fecha_nacimiento, rol_id, cedula_verificada, correo_verificado, saldo, fecha_registro) 
                VALUES (:nombre, :correo, :password, :cedula, :fecha_nacimiento, :rol_id, :cedula_verificada, :correo_verificado, :saldo, NOW())";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'correo' => $correo,
                'password' => $hashPassword,
                'cedula' => $cedulaEnc,
                'fecha_nacimiento' => $fechaNacimiento,
                'rol_id' => $rolId,
                'cedula_verificada' => $cedulaVerificada,
                'correo_verificado' => $correoVerificado,
                'saldo' => $saldoInicial
            ]);

            $nuevoId = (int)$pdo->lastInsertId();
            return self::porId($nuevoId);
        } catch (PDOException $e) {
            error_log("Error registrando usuario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todos los usuarios registrados
     *
     * @return array<int, Usuario>
     */
    public static function obtenerTodos(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id ASC");
        
        $usuarios = [];
        while ($row = $stmt->fetch()) {
            $usuarios[] = self::crearDesdeArray($row);
        }
        return $usuarios;
    }

    /**
     * Obtiene el promedio de edad en años de los lectores (rol_id = 2) en SQL
     *
     * @return float
     */
    public static function obtenerPromedioEdadLectores(): float {
        $pdo = Database::getConnection();
        $sql = "SELECT AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) AS promedio_edad 
                FROM usuarios WHERE rol_id = 2";
        $stmt = $pdo->query($sql);
        $promedio = $stmt->fetchColumn();
        return $promedio ? round((float)$promedio, 1) : 0.0;
    }

    /**
     * Crea un objeto Usuario a partir de una fila asociativa de la BD (desencriptando la cédula)
     *
     * @param array<string, mixed> $row
     * @return Usuario
     */
    private static function crearDesdeArray(array $row): Usuario {
        return new self(
            (int)$row['id'],
            $row['nombre'],
            $row['correo'],
            $row['password'],
            CryptoHelper::decrypt($row['cedula'] ?? ''),
            $row['fecha_nacimiento'],
            (int)$row['rol_id'],
            ((int)$row['cedula_verificada']) === 1,
            ((int)$row['correo_verificado']) === 1,
            (float)$row['saldo'],
            $row['fecha_registro'] ?? ''
        );
    }

    /**
     * Tarea 12: Actualiza los datos de perfil del usuario (nombre, correo, fecha de nacimiento) verificando contraseña
     */
    public static function actualizarPerfil(int $id, string $nombre, string $correo, string $fechaNacimiento, string $passwordConfirmar = ''): array {
        $nombre = trim($nombre);
        $correo = trim($correo);
        if ($id <= 0 || empty($nombre) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'Datos inválidos. Verifica que el nombre no esté vacío y el correo tenga un formato válido.'];
        }

        $usuario = self::porId($id);
        if (!$usuario || !$usuario->verificarPassword($passwordConfirmar)) {
            return ['exito' => false, 'mensaje' => 'La contraseña proporcionada es incorrecta. No se guardaron los cambios del perfil.'];
        }

        $pdo = Database::getConnection();
        try {
            // Verificar si el correo ya es usado por otro usuario
            $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :c AND id != :id LIMIT 1");
            $stmtCheck->execute(['c' => $correo, 'id' => $id]);
            if ($stmtCheck->fetch()) {
                return ['exito' => false, 'mensaje' => 'El correo electrónico especificado ya se encuentra registrado por otra cuenta.'];
            }

            $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :n, correo = :c, fecha_nacimiento = :f WHERE id = :id");
            $stmt->execute(['n' => $nombre, 'c' => $correo, 'f' => $fechaNacimiento, 'id' => $id]);
            return ['exito' => true, 'mensaje' => '¡Tu perfil ha sido actualizado correctamente!'];
        } catch (PDOException $e) {
            error_log("Error actualizando perfil: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error al guardar los datos en el servidor.'];
        }
    }

    /**
     * Tarea 12: Cambia la contraseña verificando la contraseña actual
     */
    public static function cambiarPassword(int $id, string $passwordAnterior, string $passwordNueva): array {
        if (strlen($passwordNueva) < 6) {
            return ['exito' => false, 'mensaje' => 'La nueva contraseña debe tener al menos 6 caracteres.'];
        }

        $usuario = self::porId($id);
        if (!$usuario || !$usuario->verificarPassword($passwordAnterior)) {
            return ['exito' => false, 'mensaje' => 'La contraseña actual no es correcta.'];
        }

        $nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET password = :p WHERE id = :id");
            $stmt->execute(['p' => $nuevoHash, 'id' => $id]);
            return ['exito' => true, 'mensaje' => '¡Contraseña actualizada exitosamente!'];
        } catch (PDOException $e) {
            error_log("Error cambiando contraseña: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error interno al actualizar la contraseña.'];
        }
    }

    /**
     * Tarea 12: Eliminar cuenta en caso del usuario estándar
     * Cancela préstamos activos devueltos al inventario y elimina el registro en cascada
     */
    public static function eliminarCuenta(int $id): bool {
        if ($id <= 1) {
            return false; // Nunca permitir eliminar al Administrador General ID 1
        }

        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();
            // Reintegrar stock de préstamos activos
            $stmtActive = $pdo->prepare("SELECT recurso_id FROM prestamos WHERE usuario_id = :uid AND estado = 'activo'");
            $stmtActive->execute(['uid' => $id]);
            $recursos = $stmtActive->fetchAll();

            $stmtInc = $pdo->prepare("UPDATE recursos SET disponibilidad = disponibilidad + 1 WHERE id = :rid");
            foreach ($recursos as $r) {
                $stmtInc->execute(['rid' => $r['recurso_id']]);
            }

            // Eliminar usuario (las tablas dependientes tienen ON DELETE CASCADE en carrito_items, prestamos, suscripciones)
            $stmtDel = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND rol_id = 2");
            $res = $stmtDel->execute(['id' => $id]);

            $pdo->commit();
            return $res;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error al eliminar cuenta: " . $e->getMessage());
            return false;
        }
    }
}
