<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/**
 * Clase Rol
 * Modelo para la tabla 'roles'
 */
class Rol {
    public int $id;
    public string $nombre;
    public string $descripcion;

    public function __construct(int $id = 0, string $nombre = '', string $descripcion = '') {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }

    /**
     * Obtiene todos los roles disponibles
     *
     * @return array<int, Rol>
     */
    public static function obtenerTodos(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, nombre, descripcion FROM roles ORDER BY id ASC");
        
        $roles = [];
        while ($row = $stmt->fetch()) {
            $roles[] = new self((int)$row['id'], $row['nombre'], $row['descripcion']);
        }
        return $roles;
    }

    /**
     * Busca un rol por su ID
     *
     * @param int $id
     * @return Rol|null
     */
    public static function porId(int $id): ?Rol {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, nombre, descripcion FROM roles WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row) {
            return new self((int)$row['id'], $row['nombre'], $row['descripcion']);
        }
        return null;
    }
}
