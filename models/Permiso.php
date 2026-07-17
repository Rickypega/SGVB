<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/**
 * Clase Permiso
 * Modelo para la tabla 'permisos' y su relación 'rol_permiso'
 */
class Permiso {
    public int $id;
    public string $nombre;
    public string $descripcion;

    public function __construct(int $id = 0, string $nombre = '', string $descripcion = '') {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }

    /**
     * Obtiene todos los permisos del sistema
     *
     * @return array<int, Permiso>
     */
    public static function obtenerTodos(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, nombre, descripcion FROM permisos ORDER BY id ASC");
        
        $permisos = [];
        while ($row = $stmt->fetch()) {
            $permisos[] = new self((int)$row['id'], $row['nombre'], $row['descripcion']);
        }
        return $permisos;
    }

    /**
     * Obtiene los permisos asociados a un ID de rol
     *
     * @param int $rolId
     * @return array<int, Permiso>
     */
    public static function porRol(int $rolId): array {
        $pdo = Database::getConnection();
        $sql = "SELECT p.id, p.nombre, p.descripcion 
                FROM permisos p 
                INNER JOIN rol_permiso rp ON p.id = rp.permiso_id 
                WHERE rp.rol_id = :rol_id ORDER BY p.id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['rol_id' => $rolId]);
        
        $permisos = [];
        while ($row = $stmt->fetch()) {
            $permisos[] = new self((int)$row['id'], $row['nombre'], $row['descripcion']);
        }
        return $permisos;
    }

    /**
     * Verifica si un rol tiene asignado un permiso específico (por nombre)
     *
     * @param int $rolId
     * @param string $nombrePermiso
     * @return bool
     */
    public static function rolTienePermiso(int $rolId, string $nombrePermiso): bool {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) FROM rol_permiso rp 
                INNER JOIN permisos p ON rp.permiso_id = p.id 
                WHERE rp.rol_id = :rol_id AND p.nombre = :nombre LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['rol_id' => $rolId, 'nombre' => $nombrePermiso]);
        
        return ((int)$stmt->fetchColumn()) > 0;
    }
}
