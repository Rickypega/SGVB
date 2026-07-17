<?php
declare(strict_types=1);

/**
 * Clase Database (Patrón Singleton / Estático para PDO)
 * Maneja la conexión estática a la base de datos MySQL para SGBV.
 */
class Database {
    private static ?PDO $instance = null;

    // Configuración por defecto para XAMPP / Localhost
    // Para InfinityFree, ajusta DB_HOST, DB_NAME, DB_USER y DB_PASS
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'sgbv_db';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    /**
     * Constructor privado para prevenir instanciación directa (Singleton)
     */
    private function __construct() {}

    /**
     * Evita la clonación de la instancia
     */
    private function __clone() {}

    /**
     * Obtiene la conexión PDO estática (Singleton)
     *
     * @return PDO
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                self::DB_HOST,
                self::DB_NAME,
                self::DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
            } catch (PDOException $e) {
                // Registrar o relanzar excepción controlada
                error_log("Error de conexión PDO: " . $e->getMessage());
                throw new RuntimeException("No se pudo conectar a la base de datos SGBV: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
