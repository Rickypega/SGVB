<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/**
 * Clase Categoria
 * Gestión de categorías literarias para el catálogo (Tarea 13)
 */
class Categoria {
    public int $id;
    public string $nombre;

    public function __construct(int $id = 0, string $nombre = '') {
        $this->id = $id;
        $this->nombre = $nombre;
    }

    /**
     * Obtiene todas las categorías ordenadas alfabéticamente
     *
     * @return array<int, array<string, mixed>>
     */
    public static function obtenerTodas(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM categorias ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Crea una nueva categoría en la base de datos
     *
     * @param string $nombre
     * @return bool
     */
    public static function crear(string $nombre): bool {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return false;
        }

        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (:nombre)");
            return $stmt->execute(['nombre' => $nombre]);
        } catch (PDOException $e) {
            error_log("Error al crear categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una categoría si no tiene recursos asociados
     *
     * @param int $id
     * @return bool
     */
    public static function eliminar(int $id): bool {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("No se pudo eliminar la categoría (recursos vinculados): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tarea 5: Actualiza el nombre de una categoría existente
     *
     * @param int $id
     * @param string $nombre
     * @return bool
     */
    public static function actualizar(int $id, string $nombre): bool {
        $nombre = trim($nombre);
        if ($id <= 0 || $nombre === '') {
            return false;
        }

        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("UPDATE categorias SET nombre = :nombre WHERE id = :id");
            return $stmt->execute(['nombre' => $nombre, 'id' => $id]);
        } catch (PDOException $e) {
            error_log("Error al actualizar categoría: " . $e->getMessage());
            return false;
        }
    }
}
