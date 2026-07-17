<?php
declare(strict_types=1);

/**
 * Clase CryptoHelper
 * Proporciona cifrado simétrico robusto para datos sensibles (ej. cédula)
 * utilizando OpenSSL con AES-256-CBC e IV determinista por dato para permitir búsquedas exactas (porCedula).
 */
class CryptoHelper {
    // Clave secreta estática para la aplicación SGBV (en producción se obtendría de variables de entorno)
    private const APP_KEY = 'SGBV_Secret_Key_2026_Secure_256bit_Auth#*!';
    private const CIPHER = 'AES-256-CBC';

    /**
     * Encripta una cadena de texto en formato Base64 de forma determinista para indexación y búsqueda.
     * Si la cadena ya está encriptada (o vacía), se maneja adecuadamente.
     *
     * @param string $data
     * @return string
     */
    public static function encrypt(string $data): string {
        $data = trim($data);
        if ($data === '') {
            return '';
        }

        // Si ya parece estar cifrado por nosotros con prefijo SGBVENC:, retornamos como está
        if (str_starts_with($data, 'SGBVENC:')) {
            return $data;
        }

        // Generar IV determinista de 16 bytes a partir de la propia data y clave para permitir búsquedas exactas
        $iv = substr(hash('sha256', self::APP_KEY . $data, true), 0, 16);
        $key = hash('sha256', self::APP_KEY, true);

        $encrypted = openssl_encrypt($data, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            return $data; // Fallback seguro
        }

        return 'SGBVENC:' . base64_encode($iv . $encrypted);
    }

    /**
     * Desencripta una cadena de texto previamente cifrada con `encrypt()`.
     * Si no está cifrada con nuestro formato (ej. datos existentes en texto plano), retorna la cadena original intacta.
     *
     * @param string $encryptedData
     * @return string
     */
    public static function decrypt(string $encryptedData): string {
        $encryptedData = trim($encryptedData);
        if ($encryptedData === '' || !str_starts_with($encryptedData, 'SGBVENC:')) {
            // No está encriptado con nuestro formato o es texto plano anterior
            return $encryptedData;
        }

        $rawBase64 = substr($encryptedData, 8); // Remover prefijo SGBVENC:
        $rawBytes = base64_decode($rawBase64, true);
        if ($rawBytes === false || strlen($rawBytes) <= 16) {
            return $encryptedData;
        }

        $iv = substr($rawBytes, 0, 16);
        $ciphertext = substr($rawBytes, 16);
        $key = hash('sha256', self::APP_KEY, true);

        $decrypted = openssl_decrypt($ciphertext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            return $encryptedData;
        }

        return $decrypted;
    }
}
