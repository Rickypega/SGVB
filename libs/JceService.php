<?php
/**
 * Servicio de Validación de Cédula (Mock de JCE)
 */

class JceService {
    
    /**
     * Valida la estructura y algoritmo de la cédula y consulta el endpoint de la JCE
     * 
     * @param string $cedula Formato 000-0000000-0 o 00000000000
     * @param string $nombre (Opcional) para validación estricta
     * @param string $fechaNacimiento (Opcional) para validación estricta
     * @return array ['valida' => bool, 'mensaje' => string, 'datos' => array|null]
     */
    public static function validarCedula(string $cedula, string $nombre = '', string $fechaNacimiento = ''): array {
        // Limpiar guiones y espacios
        $cedulaLimpia = preg_replace('/[^0-9]/', '', $cedula);
        
        if (strlen($cedulaLimpia) !== 11) {
            return ['valida' => false, 'mensaje' => 'La cédula debe tener exactamente 11 dígitos.'];
        }

        // Validación de Algoritmo de Luhn (Módulo 10)
        if (!self::luhnCheck($cedulaLimpia)) {
            return ['valida' => false, 'mensaje' => 'La cédula ingresada no es válida (falló verificación de algoritmo).'];
        }

        // TODO: Reemplazar esta sección con la llamada cURL real a la API de la JCE
        // $urlOficial = "https://api.jce.gob.do/v1/ciudadanos/cedula/" . $cedulaLimpia;
        // $response = cURL...
        
        // --- INICIO MOCK (Simulación) ---
        // Para pruebas, consideramos que cualquier cédula que pase Luhn es válida.
        // Simulamos un retraso de red
        usleep(500000); // 0.5 segundos

        // Datos simulados (Mock)
        $datosSimulados = [
            'cedula' => $cedula,
            'nombres' => $nombre ? strtoupper($nombre) : 'CIUDADANO SIMULADO',
            'apellidos' => 'MOCK',
            'fecha_nacimiento' => $fechaNacimiento ? $fechaNacimiento : '1990-01-01',
            'estado' => 'ACTIVO'
        ];
        
        return [
            'valida' => true,
            'mensaje' => 'Cédula verificada correctamente (Modo Mock).',
            'datos' => $datosSimulados
        ];
        // --- FIN MOCK ---
    }

    /**
     * Valida un número mediante el algoritmo de Luhn (Módulo 10)
     */
    private static function luhnCheck(string $number): bool {
        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;
        
        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $number[$i];
            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }
        
        return ($sum % 10 == 0);
    }
}
