<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Recurso.php';

/**
 * Clase Prestamo
 * Modelo transaccional y de consultas para la tabla 'prestamos'
 */
class Prestamo {
    public int $id;
    public int $usuario_id;
    public int $recurso_id;
    public string $fecha_prestamo;
    public string $fecha_devolucion_limite;
    public ?string $fecha_devolucion_real;
    public float $monto_pagado;
    public int $ha_leido;
    public string $estado; // 'reservado', 'activo', 'devuelto'

    // Datos relacionales unidos (Join)
    public string $recurso_titulo;
    public string $recurso_autor;
    public string $recurso_portada;
    public string $recurso_tipo;
    public string $usuario_nombre;
    public string $usuario_correo;

    public function __construct(
        int $id = 0,
        int $usuario_id = 0,
        int $recurso_id = 0,
        string $fecha_prestamo = '',
        string $fecha_devolucion_limite = '',
        ?string $fecha_devolucion_real = null,
        float $monto_pagado = 0.00,
        int $ha_leido = 0,
        string $estado = 'activo',
        string $recurso_titulo = '',
        string $recurso_autor = '',
        string $recurso_portada = '',
        string $recurso_tipo = '',
        string $usuario_nombre = '',
        string $usuario_correo = ''
    ) {
        $this->id = $id;
        $this->usuario_id = $usuario_id;
        $this->recurso_id = $recurso_id;
        $this->fecha_prestamo = $fecha_prestamo;
        $this->fecha_devolucion_limite = $fecha_devolucion_limite;
        $this->fecha_devolucion_real = $fecha_devolucion_real;
        $this->monto_pagado = $monto_pagado;
        $this->ha_leido = $ha_leido;
        $this->estado = $estado;
        $this->recurso_titulo = $recurso_titulo;
        $this->recurso_autor = $recurso_autor;
        $this->recurso_portada = $recurso_portada;
        $this->recurso_tipo = $recurso_tipo;
        $this->usuario_nombre = $usuario_nombre;
        $this->usuario_correo = $usuario_correo;
    }

    /**
     * Calcula los días restantes para la devolución basándose en la fecha límite (14 días desde renta).
     * Si ya se devolvió, calcula entre fecha de préstamo y límite, o 0 si está devuelto.
     * Si el préstamo sigue activo o vencido, calcula respecto a hoy. Positivo: días que faltan. Negativo: días de retraso.
     *
     * @return int
     */
    public function calcularDiasRestantes(): int {
        if (empty($this->fecha_devolucion_limite)) {
            return 0;
        }

        try {
            $limite = new DateTime($this->fecha_devolucion_limite);
            
            // Si ya fue devuelto, retornamos 0 o los días que le quedaban al momento exacto
            if ($this->estado === 'devuelto' && $this->fecha_devolucion_real !== null) {
                return 0;
            }

            $hoy = new DateTime();
            
            // Si hoy es mayor al límite, es negativo
            $diff = $hoy->diff($limite);
            $dias = (int)$diff->days;
            
            if ($diff->invert === 1) {
                return -$dias; // Vencido por -X días
            }
            
            return $dias; // Restan X días
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Método transaccional para procesar la renta de un recurso por parte de un usuario.
     * Valida cédula verificada, mayoría de edad, disponibilidad y suficiencia de saldo.
     *
     * @param Usuario $usuario
     * @param Recurso $recurso
     * @return array{exito: bool, mensaje: string}
     */
    public static function procesarRenta(Usuario $usuario, Recurso $recurso): array {
        // 1. Validar si la cédula está verificada
        if (!$usuario->cedula_verificada) {
            return [
                'exito' => false,
                'mensaje' => 'Tu cédula de identidad no está verificada. Por favor, verifica tu documento para poder rentar recursos digitales.'
            ];
        }

        // 2. Control de minoría de edad (valida si es >= 18 años calculados desde fecha_nacimiento)
        if (!$usuario->esMayorDeEdad()) {
            return [
                'exito' => false,
                'mensaje' => 'Control de minoría de edad: El sistema requiere ser mayor de edad (18+ años) según tu fecha de nacimiento (' . $usuario->fecha_nacimiento . ') para autorizar transacciones de renta en este catálogo.'
            ];
        }

        // 3. REGLA DE NEGOCIO: Validar si el usuario ya tiene un préstamo activo de este mismo recurso para evitar dejar stock en 0 o duplicar rentas
        $pdoVal = Database::getConnection();
        $stmtVal = $pdoVal->prepare("SELECT id FROM prestamos WHERE usuario_id = :u AND recurso_id = :r AND estado = 'activo' LIMIT 1");
        $stmtVal->execute(['u' => $usuario->id, 'r' => $recurso->id]);
        if ($stmtVal->fetch()) {
            return [
                'exito' => false,
                'mensaje' => 'Ya rentaste el libro "' . $recurso->titulo . '" y se encuentra activo en tu librería. Para conservarlo más tiempo debes utilizar la opción de extender/renovar el préstamo y así evitar agotar el stock.'
            ];
        }

        // 4. Validar disponibilidad del recurso
        if ($recurso->disponibilidad <= 0) {
            return [
                'exito' => false,
                'mensaje' => 'El recurso "' . $recurso->titulo . '" no se encuentra disponible actualmente (Stock: 0).'
            ];
        }

        // 4. Validar suficiencia de saldo
        if ($usuario->saldo < $recurso->precio_renta) {
            return [
                'exito' => false,
                'mensaje' => 'Saldo insuficiente en tu billetera virtual ($' . number_format($usuario->saldo, 2) . '). El recurso requiere $' . number_format($recurso->precio_renta, 2) . '. Por favor, recarga tu saldo.'
            ];
        }

        // Ejecutar Transacción Atómica PDO
        $pdo = Database::getConnection();
        
        try {
            $pdo->beginTransaction();

            // A. Debitar saldo del usuario en BD
            $nuevoSaldo = round($usuario->saldo - $recurso->precio_renta, 2);
            $stmtUser = $pdo->prepare("UPDATE usuarios SET saldo = :saldo WHERE id = :id");
            $stmtUser->execute(['saldo' => $nuevoSaldo, 'id' => $usuario->id]);

            // B. Reducir disponibilidad en 1 en BD
            $nuevaDisp = $recurso->disponibilidad - 1;
            $stmtRec = $pdo->prepare("UPDATE recursos SET disponibilidad = :disp WHERE id = :id");
            $stmtRec->execute(['disp' => $nuevaDisp, 'id' => $recurso->id]);

            // C. Registrar el préstamo (límite de 14 días)
            $sqlPrestamo = "INSERT INTO prestamos (usuario_id, recurso_id, fecha_prestamo, fecha_devolucion_limite, monto_pagado, ha_leido, estado) 
                            VALUES (:usuario_id, :recurso_id, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), :monto, 0, 'activo')";
            $stmtPrestamo = $pdo->prepare($sqlPrestamo);
            $stmtPrestamo->execute([
                'usuario_id' => $usuario->id,
                'recurso_id' => $recurso->id,
                'monto' => $recurso->precio_renta
            ]);

            $pdo->commit();

            // Actualizar objetos en memoria luego del commit exitoso
            $usuario->saldo = $nuevoSaldo;
            $recurso->disponibilidad = $nuevaDisp;

            return [
                'exito' => true,
                'mensaje' => '¡Renta procesada exitosamente! Has adquirido el préstamo por 14 días del recurso "' . $recurso->titulo . '".'
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en procesarRenta (rollback): " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Ocurrió un error en el servidor al procesar la transacción transaccional. Por favor, intenta de nuevo.'
            ];
        }
    }

    /**
     * Procesa la devolución de un recurso prestado
     *
     * @param int $prestamoId
     * @param int $usuarioId (0 para admin sin restricción de dueño)
     * @return array{exito: bool, mensaje: string}
     */
    public static function devolver(int $prestamoId, int $usuarioId = 0): array {
        $pdo = Database::getConnection();
        
        // Obtener datos del préstamo
        $stmt = $pdo->prepare("SELECT * FROM prestamos WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $prestamoId]);
        $prestamo = $stmt->fetch();

        if (!$prestamo) {
            return ['exito' => false, 'mensaje' => 'El registro del préstamo no fue encontrado.'];
        }

        if ($prestamo['estado'] === 'devuelto') {
            return ['exito' => false, 'mensaje' => 'Este recurso ya fue devuelto previamente.'];
        }

        // Si se provee usuarioId, asegurar que le pertenece (o si es admin se pasa 0)
        if ($usuarioId > 0 && ((int)$prestamo['usuario_id']) !== $usuarioId) {
            return ['exito' => false, 'mensaje' => 'No tienes permiso para devolver un préstamo que no te pertenece.'];
        }

        try {
            $pdo->beginTransaction();

            // Actualizar préstamo
            $stmtUpdate = $pdo->prepare("UPDATE prestamos SET fecha_devolucion_real = NOW(), estado = 'devuelto' WHERE id = :id");
            $stmtUpdate->execute(['id' => $prestamoId]);

            // Devolver stock al recurso
            $stmtRec = $pdo->prepare("UPDATE recursos SET disponibilidad = disponibilidad + 1 WHERE id = :recurso_id");
            $stmtRec->execute(['recurso_id' => $prestamo['recurso_id']]);

            $pdo->commit();

            return ['exito' => true, 'mensaje' => '¡Devolución registrada con éxito! El ejemplar se ha reintegrado a la biblioteca virtual.'];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en devolver préstamo: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'No se pudo procesar la devolución. Error de base de datos.'];
        }
    }

    /**
     * Tarea 10: Devolución automática al cumplirse los 14 días (sin estado vencido)
     * Revisa préstamos activos cuyo límite haya expirado y los marca automáticamente devueltos.
     *
     * @return int Cantidad de préstamos devueltos automáticamente
     */
    public static function devolucionAutomaticaAlVencer(): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, recurso_id FROM prestamos WHERE estado = 'activo' AND fecha_devolucion_limite <= NOW()");
        $expirados = $stmt->fetchAll();

        if (empty($expirados)) {
            return 0;
        }

        try {
            $pdo->beginTransaction();
            $stmtUpd = $pdo->prepare("UPDATE prestamos SET fecha_devolucion_real = NOW(), estado = 'devuelto' WHERE id = :id");
            $stmtRec = $pdo->prepare("UPDATE recursos SET disponibilidad = disponibilidad + 1 WHERE id = :recurso_id");

            foreach ($expirados as $row) {
                $stmtUpd->execute(['id' => $row['id']]);
                $stmtRec->execute(['recurso_id' => $row['recurso_id']]);
            }
            $pdo->commit();
            return count($expirados);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en devolucionAutomaticaAlVencer: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Tarea 14: Devolución anticipada de un artículo solo si el usuario NO lo ha leído (ha_leido == 0)
     * Reintegra los créditos a la billetera virtual del lector y marca el préstamo devuelto/anulado.
     *
     * @param int $prestamoId
     * @param int $usuarioId
     * @return array{exito: bool, mensaje: string}
     */
    public static function devolverAnticipado(int $prestamoId, int $usuarioId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT p.*, r.titulo FROM prestamos p INNER JOIN recursos r ON p.recurso_id = r.id WHERE p.id = :id AND p.usuario_id = :u AND p.estado = 'activo' LIMIT 1");
        $stmt->execute(['id' => $prestamoId, 'u' => $usuarioId]);
        $prestamo = $stmt->fetch();

        if (!$prestamo) {
            return ['exito' => false, 'mensaje' => 'No se encontró un préstamo activo con ese ID para tu cuenta.'];
        }

        if (((int)$prestamo['ha_leido']) === 1) {
            return ['exito' => false, 'mensaje' => 'No es posible devolver o anular la renta del recurso "' . $prestamo['titulo'] . '" porque ya has abierto el visor digital y leído el material.'];
        }

        try {
            $pdo->beginTransaction();
            // 1. Marcar préstamo como devuelto y anotar la devolución
            $stmtUpd = $pdo->prepare("UPDATE prestamos SET fecha_devolucion_real = NOW(), estado = 'devuelto' WHERE id = :id");
            $stmtUpd->execute(['id' => $prestamoId]);

            // 2. Aumentar stock del recurso
            $stmtRec = $pdo->prepare("UPDATE recursos SET disponibilidad = disponibilidad + 1 WHERE id = :recurso_id");
            $stmtRec->execute(['recurso_id' => $prestamo['recurso_id']]);

            // 3. Reintegrar el monto pagado a la billetera del usuario
            $stmtUser = $pdo->prepare("UPDATE usuarios SET saldo = saldo + :monto WHERE id = :uid");
            $stmtUser->execute(['monto' => $prestamo['monto_pagado'], 'uid' => $usuarioId]);

            $pdo->commit();
            return ['exito' => true, 'mensaje' => '¡Renta anulada con éxito! Al no haber sido leído, el recurso "' . $prestamo['titulo'] . '" ha sido devuelto y se han reintegrado ' . number_format((float)$prestamo['monto_pagado'], 2) . ' Créditos a tu billetera.'];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en devolverAnticipado: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error al procesar la devolución anticipada.'];
        }
    }

    /**
     * Tarea 11: Renovación de renta (si faltan 3 días o menos para expirar, extiende 14 días más)
     *
     * @param int $prestamoId
     * @param int $usuarioId
     * @return array{exito: bool, mensaje: string}
     */
    public static function renovarPrestamo(int $prestamoId, int $usuarioId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT p.*, r.titulo, r.precio_renta FROM prestamos p INNER JOIN recursos r ON p.recurso_id = r.id WHERE p.id = :id AND p.usuario_id = :u AND p.estado = 'activo' LIMIT 1");
        $stmt->execute(['id' => $prestamoId, 'u' => $usuarioId]);
        $prestamo = $stmt->fetch();

        if (!$prestamo) {
            return ['exito' => false, 'mensaje' => 'Préstamo no válido para renovación.'];
        }

        // Verificar saldo
        $usuario = Usuario::porId($usuarioId);
        if (!$usuario || $usuario->saldo < $prestamo['precio_renta']) {
            return ['exito' => false, 'mensaje' => 'No tienes suficientes Créditos (' . ($usuario ? number_format($usuario->saldo, 2) : '0.00') . ' Créditos) para renovar el préstamo de "' . $prestamo['titulo'] . '". Precio de renta: ' . number_format((float)$prestamo['precio_renta'], 2) . ' Créditos.'];
        }

        try {
            $pdo->beginTransaction();
            // Debitar saldo
            $stmtUser = $pdo->prepare("UPDATE usuarios SET saldo = saldo - :monto WHERE id = :uid");
            $stmtUser->execute(['monto' => $prestamo['precio_renta'], 'uid' => $usuarioId]);

            // Extender fecha límite +14 días e incrementar monto pagado
            $stmtUpd = $pdo->prepare("UPDATE prestamos SET fecha_devolucion_limite = DATE_ADD(fecha_devolucion_limite, INTERVAL 14 DAY), monto_pagado = monto_pagado + :monto WHERE id = :id");
            $stmtUpd->execute(['monto' => $prestamo['precio_renta'], 'id' => $prestamoId]);

            $pdo->commit();
            return ['exito' => true, 'mensaje' => '¡Préstamo de "' . $prestamo['titulo'] . '" renovado exitosamente por 14 días adicionales!'];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en renovarPrestamo: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error al intentar renovar el préstamo.'];
        }
    }

    /**
     * Tarea 14 & 15: Marca un préstamo como leído (`ha_leido = 1`) al abrir el visor digital
     *
     * @param int $prestamoId
     * @param int $usuarioId
     * @return bool
     */
    public static function marcarLeido(int $prestamoId, int $usuarioId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE prestamos SET ha_leido = 1 WHERE id = :id AND (usuario_id = :u1 OR :u2 = 0)");
        return $stmt->execute(['id' => $prestamoId, 'u1' => $usuarioId, 'u2' => $usuarioId]);
    }

    /**
     * Obtiene el listado de préstamos de un usuario específico
     *
     * @param int $usuarioId
     * @return array<int, Prestamo>
     */
    public static function obtenerPorUsuario(int $usuarioId): array {
        $pdo = Database::getConnection();
        $sql = "SELECT p.*, r.titulo AS recurso_titulo, r.autor AS recurso_autor, r.portada AS recurso_portada, r.tipo AS recurso_tipo 
                FROM prestamos p 
                INNER JOIN recursos r ON p.recurso_id = r.id 
                WHERE p.usuario_id = :usuario_id 
                ORDER BY p.id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);

        $prestamos = [];
        while ($row = $stmt->fetch()) {
            $prestamos[] = self::crearDesdeArray($row);
        }
        return $prestamos;
    }

    /**
     * Obtiene todos los libros alquilados o guardados por el usuario para la biblioteca digital (Mi Librería)
     *
     * @param int $usuarioId
     * @return array<int, array<string, mixed>>
     */
    public static function obtenerMisLibros(int $usuarioId): array {
        $pdo = Database::getConnection();
        $sql = "SELECT p.*, r.titulo AS recurso_titulo, r.autor AS recurso_autor, r.portada AS recurso_portada, 
                       r.tipo AS recurso_tipo, r.archivo_pdf AS recurso_archivo_pdf, r.isbn AS recurso_isbn,
                       r.descripcion AS recurso_descripcion, r.precio_renta AS recurso_precio, 
                       r.disponibilidad AS recurso_disponibilidad, r.anio_publicacion AS recurso_anio,
                       c.nombre AS categoria_nombre
                FROM prestamos p 
                INNER JOIN recursos r ON p.recurso_id = r.id 
                INNER JOIN categorias c ON r.categoria_id = c.id
                WHERE p.usuario_id = :usuario_id 
                ORDER BY p.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene el historial global de todos los préstamos para el administrador
     *
     * @return array<int, Prestamo>
     */
    public static function obtenerTodos(): array {
        $pdo = Database::getConnection();
        $sql = "SELECT p.*, r.titulo AS recurso_titulo, r.autor AS recurso_autor, r.portada AS recurso_portada, r.tipo AS recurso_tipo,
                       u.nombre AS usuario_nombre, u.correo AS usuario_correo 
                FROM prestamos p 
                INNER JOIN recursos r ON p.recurso_id = r.id 
                INNER JOIN usuarios u ON p.usuario_id = u.id 
                ORDER BY p.id ASC";
        $stmt = $pdo->query($sql);

        $prestamos = [];
        while ($row = $stmt->fetch()) {
            $prestamos[] = self::crearDesdeArray($row);
        }
        return $prestamos;
    }

    /**
     * Obtiene las ganancias totales acumuladas por rentas
     *
     * @return float
     */
    public static function obtenerGananciasTotales(): float {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT SUM(monto_pagado) FROM prestamos WHERE estado != 'reservado'");
        $val = $stmt->fetchColumn();
        return $val ? round((float)$val, 2) : 0.00;
    }

    /**
     * Obtiene el género literario (categoría) preferido o más rentado
     *
     * @return array{categoria: string, total: int}|null
     */
    public static function obtenerGeneroPreferido(): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT c.nombre AS categoria, COUNT(p.id) AS total 
                FROM prestamos p 
                INNER JOIN recursos r ON p.recurso_id = r.id 
                INNER JOIN categorias c ON r.categoria_id = c.id 
                GROUP BY c.id 
                ORDER BY total DESC LIMIT 1";
        $stmt = $pdo->query($sql);
        $row = $stmt->fetch();

        if ($row) {
            return [
                'categoria' => $row['categoria'],
                'total' => (int)$row['total']
            ];
        }
        return null;
    }

    /**
     * Obtiene el historial global de préstamos de los últimos 30 días para el gráfico de estado de flujo (Tarea 11)
     *
     * @return array<int, Prestamo>
     */
    public static function obtenerFlujoUltimos30Dias(): array {
        $pdo = Database::getConnection();
        $sql = "SELECT p.*, r.titulo AS recurso_titulo, r.autor AS recurso_autor, r.portada AS recurso_portada, r.tipo AS recurso_tipo,
                       u.nombre AS usuario_nombre, u.correo AS usuario_correo 
                FROM prestamos p 
                INNER JOIN recursos r ON p.recurso_id = r.id 
                INNER JOIN usuarios u ON p.usuario_id = u.id 
                WHERE p.fecha_prestamo >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY p.id ASC";
        $stmt = $pdo->query($sql);

        $prestamos = [];
        while ($row = $stmt->fetch()) {
            $prestamos[] = self::crearDesdeArray($row);
        }
        return $prestamos;
    }

    /**
     * Método transaccional para procesar en lote el Carrito de Préstamos (Tarea 8).
     * Valida cédula, mayoría de edad, stock individual y saldo global antes de procesar el carrito.
     *
     * @param Usuario $usuario
     * @param array<int, array<string, mixed>> $itemsCarrito
     * @return array{exito: bool, mensaje: string, rentados: int}
     */
    public static function procesarCarrito(Usuario $usuario, array $itemsCarrito): array {
        if (empty($itemsCarrito)) {
            return ['exito' => false, 'mensaje' => 'Tu carrito de préstamos está vacío.', 'rentados' => 0];
        }

        if (!$usuario->cedula_verificada) {
            return ['exito' => false, 'mensaje' => 'Tu cédula no está verificada para poder rentar recursos digitales.', 'rentados' => 0];
        }

        if (!$usuario->esMayorDeEdad()) {
            return ['exito' => false, 'mensaje' => 'Control de edad: Requieres ser mayor de edad (18+) para autorizar rentas en el catálogo.', 'rentados' => 0];
        }

        // Calcular costo total y verificar disponibilidad individual en la base de datos en tiempo real
        $pdo = Database::getConnection();
        $costoTotal = 0.0;
        $recursosValidados = [];

        foreach ($itemsCarrito as $item) {
            $recId = (int)($item['id'] ?? 0);
            $recurso = Recurso::porId($recId);
            if (!$recurso || $recurso->disponibilidad <= 0) {
                return [
                    'exito' => false, 
                    'mensaje' => 'El recurso "' . ($recurso ? $recurso->titulo : 'ID #' . $recId) . '" ya no tiene ejemplares disponibles (Disponibles: 0). Por favor elimínalo de tu carrito para continuar.',
                    'rentados' => 0
                ];
            }
            $costoTotal += $recurso->precio_renta;
            $recursosValidados[] = $recurso;
        }

        if ($usuario->saldo < $costoTotal) {
            return [
                'exito' => false,
                'mensaje' => 'Saldo insuficiente en tu billetera virtual ($' . number_format($usuario->saldo, 2) . '). El carrito requiere un total de $' . number_format($costoTotal, 2) . '. Por favor, recarga tus fondos.',
                'rentados' => 0
            ];
        }

        try {
            $pdo->beginTransaction();

            // A. Debitar el saldo total
            $nuevoSaldo = round($usuario->saldo - $costoTotal, 2);
            $stmtUser = $pdo->prepare("UPDATE usuarios SET saldo = :saldo WHERE id = :id");
            $stmtUser->execute(['saldo' => $nuevoSaldo, 'id' => $usuario->id]);

            // B. Para cada recurso, restar 1 y registrar el préstamo
            $stmtRec = $pdo->prepare("UPDATE recursos SET disponibilidad = disponibilidad - 1 WHERE id = :id");
            $stmtPrestamo = $pdo->prepare("INSERT INTO prestamos (usuario_id, recurso_id, fecha_prestamo, fecha_devolucion_limite, monto_pagado, ha_leido, estado) VALUES (:usuario_id, :recurso_id, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), :monto, 0, 'activo')");

            foreach ($recursosValidados as $r) {
                $stmtRec->execute(['id' => $r->id]);
                $stmtPrestamo->execute([
                    'usuario_id' => $usuario->id,
                    'recurso_id' => $r->id,
                    'monto' => $r->precio_renta
                ]);
            }

            // Limpiar carrito en base de datos al procesar con éxito
            $stmtDelCart = $pdo->prepare("DELETE FROM carrito_items WHERE usuario_id = :uid");
            $stmtDelCart->execute(['uid' => $usuario->id]);

            $pdo->commit();

            $usuario->saldo = $nuevoSaldo;
            return [
                'exito' => true,
                'mensaje' => '¡Carrito procesado con éxito! Has adquirido el préstamo de ' . count($recursosValidados) . ' recursos por 14 días (Total: $' . number_format($costoTotal, 2) . ').',
                'rentados' => count($recursosValidados)
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error al procesar carrito: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Ocurrió un error en el servidor al procesar la compra transaccional de tu carrito.', 'rentados' => 0];
        }
    }

    /**
     * Tarea 2: Carrito de Préstamos con Memoria Persistente en BD
     * Obtiene los recursos almacenados en el carrito de la BD para el usuario
     *
     * @param int $usuarioId
     * @return array<int, array<string, mixed>>
     */
    public static function obtenerCarritoBD(int $usuarioId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT r.id, r.titulo, r.autor, r.precio_renta, r.portada, r.disponibilidad, r.tipo, r.categoria_id 
                               FROM carrito_items c 
                               INNER JOIN recursos r ON c.recurso_id = r.id 
                               WHERE c.usuario_id = :uid ORDER BY c.fecha_agregado ASC");
        $stmt->execute(['uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Agrega un recurso al carrito de BD
     */
    public static function agregarAlCarritoBD(int $usuarioId, int $recursoId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT IGNORE INTO carrito_items (usuario_id, recurso_id, fecha_agregado) VALUES (:uid, :rid, NOW())");
        return $stmt->execute(['uid' => $usuarioId, 'rid' => $recursoId]);
    }

    /**
     * Elimina un recurso del carrito de BD
     */
    public static function eliminarDelCarritoBD(int $usuarioId, int $recursoId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM carrito_items WHERE usuario_id = :uid AND recurso_id = :rid");
        return $stmt->execute(['uid' => $usuarioId, 'rid' => $recursoId]);
    }

    /**
     * Instancia un objeto Prestamo desde un array SQL
     *
     * @param array<string, mixed> $row
     * @return Prestamo
     */
    private static function crearDesdeArray(array $row): Prestamo {
        return new self(
            (int)$row['id'],
            (int)$row['usuario_id'],
            (int)$row['recurso_id'],
            $row['fecha_prestamo'] ?? '',
            $row['fecha_devolucion_limite'] ?? '',
            $row['fecha_devolucion_real'] ?? null,
            (float)$row['monto_pagado'],
            (int)($row['ha_leido'] ?? 0),
            $row['estado'] ?? 'activo',
            $row['recurso_titulo'] ?? '',
            $row['recurso_autor'] ?? '',
            $row['recurso_portada'] ?? '',
            $row['recurso_tipo'] ?? '',
            $row['usuario_nombre'] ?? '',
            $row['usuario_correo'] ?? ''
        );
    }
}
