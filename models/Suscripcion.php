<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/**
 * Clase Suscripcion
 * Gestión de notificaciones para recursos agotados (Tarea 12)
 */
class Suscripcion {
    public int $id;
    public int $usuario_id;
    public int $recurso_id;
    public string $fecha_suscripcion;
    public string $estado; // 'pendiente', 'notificado'

    public function __construct(
        int $id = 0,
        int $usuario_id = 0,
        int $recurso_id = 0,
        string $fecha_suscripcion = '',
        string $estado = 'pendiente'
    ) {
        $this->id = $id;
        $this->usuario_id = $usuario_id;
        $this->recurso_id = $recurso_id;
        $this->fecha_suscripcion = $fecha_suscripcion;
        $this->estado = $estado;
    }

    /**
     * Registra una suscripción de un usuario para recibir notificación cuando el recurso esté disponible.
     *
     * @param int $usuarioId
     * @param int $recursoId
     * @return array{exito: bool, mensaje: string}
     */
    public static function suscribir(int $usuarioId, int $recursoId): array {
        $pdo = Database::getConnection();

        // Verificar si ya está suscrito
        $stmtCheck = $pdo->prepare("SELECT id FROM suscripciones_recursos WHERE usuario_id = :uid AND recurso_id = :rid LIMIT 1");
        $stmtCheck->execute(['uid' => $usuarioId, 'rid' => $recursoId]);
        if ($stmtCheck->fetch()) {
            return ['exito' => true, 'mensaje' => 'Ya te encuentras suscrito para recibir una alerta en cuanto este recurso regrese al inventario.'];
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO suscripciones_recursos (usuario_id, recurso_id, fecha_suscripcion, estado) VALUES (:uid, :rid, NOW(), 'pendiente')");
            $stmt->execute(['uid' => $usuarioId, 'rid' => $recursoId]);
            return ['exito' => true, 'mensaje' => '¡Te has suscrito con éxito! Recibirás una notificación prioritaria cuando este ejemplar esté disponible.'];
        } catch (PDOException $e) {
            error_log("Error en suscribir: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'No se pudo registrar la suscripción.'];
        }
    }
}
