<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/**
 * Clase Recurso
 * Mapeo y lógica de acceso a datos para la tabla 'recursos' (libros, audiolibros, artículos)
 */
class Recurso {
    public int $id;
    public string $titulo;
    public string $autor;
    public string $isbn;
    public int $categoria_id;
    public int $anio_publicacion;
    public string $tipo; // 'libro', 'audiolibro', 'articulo'
    public int $disponibilidad;
    public float $precio_renta;
    public string $portada;
    public ?string $archivo_pdf;
    public string $descripcion;
    public string $fecha_creacion;
    public string $categoria_nombre;

    public function __construct(
        int $id = 0,
        string $titulo = '',
        string $autor = '',
        string $isbn = '',
        int $categoria_id = 0,
        int $anio_publicacion = 2024,
        string $tipo = 'libro',
        int $disponibilidad = 1,
        float $precio_renta = 0.00,
        string $portada = 'default_cover.jpg',
        ?string $archivo_pdf = null,
        string $descripcion = '',
        string $fecha_creacion = '',
        string $categoria_nombre = ''
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->autor = $autor;
        $this->isbn = $isbn;
        $this->categoria_id = $categoria_id;
        $this->anio_publicacion = $anio_publicacion;
        $this->tipo = $tipo;
        $this->disponibilidad = $disponibilidad;
        $this->precio_renta = $precio_renta;
        $this->portada = $portada;
        $this->archivo_pdf = $archivo_pdf;
        $this->descripcion = $descripcion;
        $this->fecha_creacion = $fecha_creacion;
        $this->categoria_nombre = $categoria_nombre;
    }

    /**
     * Obtiene todo el catálogo de recursos con el nombre de su categoría
     *
     * @return array<int, Recurso>
     */
    public static function obtenerTodos(): array {
        $pdo = Database::getConnection();
        $sql = "SELECT r.*, c.nombre AS categoria_nombre 
                FROM recursos r 
                INNER JOIN categorias c ON r.categoria_id = c.id 
                ORDER BY r.id ASC";
        $stmt = $pdo->query($sql);

        $recursos = [];
        while ($row = $stmt->fetch()) {
            $recursos[] = self::crearDesdeArray($row);
        }
        return $recursos;
    }

    /**
     * Obtiene todos los recursos del catálogo organizados por orden alfabético de título
     *
     * @return array<int, Recurso>
     */
    public static function obtenerCatalogo(): array {
        $pdo = Database::getConnection();
        $sql = "SELECT r.*, c.nombre AS categoria_nombre 
                FROM recursos r 
                INNER JOIN categorias c ON r.categoria_id = c.id 
                ORDER BY r.titulo ASC";
        $stmt = $pdo->query($sql);

        $recursos = [];
        while ($row = $stmt->fetch()) {
            $recursos[] = self::crearDesdeArray($row);
        }
        return $recursos;
    }

    /**
     * Busca un recurso específico por ID
     *
     * @param int $id
     * @return Recurso|null
     */
    public static function porId(int $id): ?Recurso {
        $pdo = Database::getConnection();
        $sql = "SELECT r.*, c.nombre AS categoria_nombre 
                FROM recursos r 
                INNER JOIN categorias c ON r.categoria_id = c.id 
                WHERE r.id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row) {
            return self::crearDesdeArray($row);
        }
        return null;
    }

    /**
     * Motor de búsqueda de recursos por título, autor o categoría
     *
     * @param string $termino
     * @param int $categoriaId
     * @return array<int, Recurso>
     */
    public static function buscar(string $termino = '', int $categoriaId = 0): array {
        $pdo = Database::getConnection();
        
        $sql = "SELECT r.*, c.nombre AS categoria_nombre 
                FROM recursos r 
                INNER JOIN categorias c ON r.categoria_id = c.id WHERE 1=1";
        $params = [];

        if (!empty($termino)) {
            $sql .= " AND (r.titulo LIKE :termino OR r.autor LIKE :termino OR r.isbn LIKE :termino)";
            $params['termino'] = '%' . trim($termino) . '%';
        }

        if ($categoriaId > 0) {
            $sql .= " AND r.categoria_id = :categoria_id";
            $params['categoria_id'] = $categoriaId;
        }

        $sql .= " ORDER BY r.titulo ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $recursos = [];
        while ($row = $stmt->fetch()) {
            $recursos[] = self::crearDesdeArray($row);
        }
        return $recursos;
    }

    /**
     * Crea un nuevo recurso en el sistema
     *
     * @param array<string, mixed> $datos
     * @return Recurso|null
     */
    public static function crear(array $datos): ?Recurso {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO recursos (titulo, autor, isbn, categoria_id, anio_publicacion, tipo, disponibilidad, precio_renta, portada, archivo_pdf, descripcion, fecha_creacion) 
                VALUES (:titulo, :autor, :isbn, :categoria_id, :anio_publicacion, :tipo, :disponibilidad, :precio_renta, :portada, :archivo_pdf, :descripcion, NOW())";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'titulo' => $datos['titulo'] ?? '',
                'autor' => $datos['autor'] ?? '',
                'isbn' => $datos['isbn'] ?? '',
                'categoria_id' => (int)($datos['categoria_id'] ?? 1),
                'anio_publicacion' => (int)($datos['anio_publicacion'] ?? date('Y')),
                'tipo' => $datos['tipo'] ?? 'libro',
                'disponibilidad' => (int)($datos['disponibilidad'] ?? 1),
                'precio_renta' => (float)($datos['precio_renta'] ?? 0.00),
                'portada' => !empty($datos['portada']) ? $datos['portada'] : 'default_cover.jpg',
                'archivo_pdf' => !empty($datos['archivo_pdf']) ? $datos['archivo_pdf'] : null,
                'descripcion' => $datos['descripcion'] ?? ''
            ]);

            $nuevoId = (int)$pdo->lastInsertId();
            return self::porId($nuevoId);
        } catch (PDOException $e) {
            error_log("Error al crear recurso: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza un recurso existente
     *
     * @param int $id
     * @param array<string, mixed> $datos
     * @return bool
     */
    public static function actualizar(int $id, array $datos): bool {
        $pdo = Database::getConnection();
        $sql = "UPDATE recursos SET 
                titulo = :titulo, 
                autor = :autor, 
                isbn = :isbn, 
                categoria_id = :categoria_id, 
                anio_publicacion = :anio_publicacion, 
                tipo = :tipo, 
                disponibilidad = :disponibilidad, 
                precio_renta = :precio_renta, 
                portada = :portada, 
                archivo_pdf = :archivo_pdf, 
                descripcion = :descripcion 
                WHERE id = :id";
        
        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                'titulo' => $datos['titulo'] ?? '',
                'autor' => $datos['autor'] ?? '',
                'isbn' => $datos['isbn'] ?? '',
                'categoria_id' => (int)($datos['categoria_id'] ?? 1),
                'anio_publicacion' => (int)($datos['anio_publicacion'] ?? date('Y')),
                'tipo' => $datos['tipo'] ?? 'libro',
                'disponibilidad' => (int)($datos['disponibilidad'] ?? 1),
                'precio_renta' => (float)($datos['precio_renta'] ?? 0.00),
                'portada' => !empty($datos['portada']) ? $datos['portada'] : 'default_cover.jpg',
                'archivo_pdf' => !empty($datos['archivo_pdf']) ? $datos['archivo_pdf'] : null,
                'descripcion' => $datos['descripcion'] ?? '',
                'id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar recurso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un recurso (si no tiene préstamos activos o dependencias restrictivas)
     *
     * @param int $id
     * @return bool
     */
    public static function eliminar(int $id): bool {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("DELETE FROM recursos WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("No se puede eliminar el recurso (posibles dependencias en préstamos): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza la disponibilidad sumando o restando la cantidad indicada
     *
     * @param int $cambio (+1 para devolución, -1 para renta)
     * @return bool
     */
    public function actualizarDisponibilidad(int $cambio): bool {
        $nuevaDisp = $this->disponibilidad + $cambio;
        if ($nuevaDisp < 0) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE recursos SET disponibilidad = :disp WHERE id = :id");
        $exito = $stmt->execute([
            'disp' => $nuevaDisp,
            'id' => $this->id
        ]);

        if ($exito) {
            $this->disponibilidad = $nuevaDisp;
            return true;
        }
        return false;
    }

    /**
     * Obtiene el número total de recursos registrados en el catálogo
     *
     * @return int
     */
    public static function obtenerTotalRecursos(): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM recursos");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Obtiene estadísticas de distribución por categoría para el dashboard admin
     *
     * @return array<int, array<string, mixed>>
     */
    public static function obtenerEstadisticasPorCategoria(): array {
        $pdo = Database::getConnection();
        $sql = "SELECT c.nombre AS categoria, COUNT(r.id) AS total 
                FROM categorias c 
                LEFT JOIN recursos r ON c.id = r.categoria_id 
                GROUP BY c.id ORDER BY total DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Crea una instancia de Recurso a partir del array asociativo SQL
     *
     * @param array<string, mixed> $row
     * @return Recurso
     */
    private static function crearDesdeArray(array $row): Recurso {
        return new self(
            (int)$row['id'],
            $row['titulo'],
            $row['autor'],
            $row['isbn'],
            (int)$row['categoria_id'],
            (int)$row['anio_publicacion'],
            $row['tipo'],
            (int)$row['disponibilidad'],
            (float)$row['precio_renta'],
            $row['portada'] ?? 'default_cover.jpg',
            $row['archivo_pdf'] ?? null,
            $row['descripcion'] ?? '',
            $row['fecha_creacion'] ?? '',
            $row['categoria_nombre'] ?? ''
        );
    }
}
