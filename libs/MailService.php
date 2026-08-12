<?php
/**
 * Servicio centralizado para envío de correos mediante PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailService {
    
    /**
     * Configura la instancia de PHPMailer
     */
    private static function getMailer(): PHPMailer {
        $mail = new PHPMailer(true);
        
        try {
            // Configuración del Servidor SMTP (Preparado para Mailtrap o Gmail)
            $mail->isSMTP();
            
            $config = require __DIR__ . '/../config/mail.php';
            
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = $config['smtp_auth'];
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            if ($config['smtp_secure'] === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($config['smtp_secure'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
            $mail->Port       = $config['port'];
            
            $mail->setFrom($config['username'], $config['from_name']); // Forzar a username SMTP para evitar spam
            $mail->addReplyTo($config['username'], $config['from_name'] . ' Soporte');
            $mail->XMailer    = 'SGBV Mailer v1.0';
            $mail->CharSet    = 'UTF-8';
            $mail->isHTML(true);
            
            return $mail;
        } catch (\Exception $e) {
            error_log("Error inicializando PHPMailer: " . $mail->ErrorInfo);
            throw new \Exception("Error inicializando el enviador de correo.");
        }
    }

    /**
     * Envía un correo de activación de cuenta
     */
    public static function enviarCorreoActivacion(string $destinatario, string $nombre, string $token): bool {
        $mail = self::getMailer();
        
        try {
            $mail->addAddress($destinatario, $nombre);
            
            $enlaceActivacion = BASE_URL . "verificar?token=" . urlencode($token);
            
            $mail->Subject = 'Activa tu cuenta en SGBV';
            
            // Plantilla HTML
            $cuerpoHTML = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #0d6efd;'>¡Bienvenido a SGBV, $nombre!</h2>
                <p>Gracias por registrarte en el Sistema de Gestión de Bibliotecas Virtuales. Para poder iniciar sesión y acceder a los recursos, debes activar tu cuenta.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$enlaceActivacion' style='background-color: #0d6efd; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Activar mi cuenta</a>
                </div>
                <p style='color: #666; font-size: 12px;'>Si el botón no funciona, copia y pega este enlace en tu navegador:<br>$enlaceActivacion</p>
                <hr style='border: none; border-top: 1px solid #eee; margin-top: 30px;'>
                <p style='color: #999; font-size: 11px; text-align: center;'>Este es un correo automático, por favor no respondas.</p>
            </div>";
            
            $mail->Body = $cuerpoHTML;
            $mail->AltBody = "Hola $nombre, activa tu cuenta copiando el siguiente enlace: $enlaceActivacion";
            
            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log("No se pudo enviar el correo de activación: " . $mail->ErrorInfo);
            throw new \Exception("Error SMTP: " . $mail->ErrorInfo);
        }
    }
    
    /**
     * Envía un correo de recuperación de contraseña
     */
    public static function enviarCorreoRecuperacion(string $destinatario, string $nombre, string $token): bool {
        $mail = self::getMailer();
        
        try {
            $mail->addAddress($destinatario, $nombre);
            
            $enlaceRecuperacion = BASE_URL . "restablecer-password?token=" . urlencode($token);
            
            $mail->Subject = 'Recuperación de contraseña en SGBV';
            
            $cuerpoHTML = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #dc3545;'>Restablecer Contraseña</h2>
                <p>Hola $nombre, hemos recibido una solicitud para restablecer la contraseña de tu cuenta en SGBV.</p>
                <p>Si no fuiste tú, puedes ignorar este correo sin problemas.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$enlaceRecuperacion' style='background-color: #dc3545; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Restablecer Contraseña</a>
                </div>
                <p style='color: #666; font-size: 12px;'>Si el botón no funciona, copia y pega este enlace en tu navegador:<br>$enlaceRecuperacion</p>
                <hr style='border: none; border-top: 1px solid #eee; margin-top: 30px;'>
                <p style='color: #999; font-size: 11px; text-align: center;'>Este es un correo automático, por favor no respondas.</p>
            </div>";
            
            $mail->Body = $cuerpoHTML;
            $mail->AltBody = "Hola $nombre, para restablecer tu contraseña copia el siguiente enlace: $enlaceRecuperacion";
            
            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log("No se pudo enviar el correo de recuperación: " . $mail->ErrorInfo);
            throw new \Exception("Error SMTP: " . $mail->ErrorInfo);
        }
    }

    /**
     * Envía un correo desde el formulario de contacto (Mesa de Ayuda)
     */
    public static function enviarCorreoContacto(string $remitenteNombre, string $remitenteCorreo, string $asunto, string $mensaje): bool {
        $mail = self::getMailer();
        
        try {
            $config = require __DIR__ . '/../config/mail.php';
            // El destinatario es el correo de soporte
            $mail->addAddress('rpgtmsoporte@gmail.com', 'Soporte SGBV');
            
            // Responder a quien envió el formulario
            $mail->addReplyTo($remitenteCorreo, $remitenteNombre);
            
            $mail->Subject = 'Nuevo Mensaje de Mesa de Ayuda: ' . $asunto;
            
            $cuerpoHTML = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #0d6efd;'>Nuevo Mensaje de Contacto</h2>
                <p><strong>Remitente:</strong> $remitenteNombre ($remitenteCorreo)</p>
                <p><strong>Asunto:</strong> $asunto</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='white-space: pre-wrap;'>$mensaje</p>
                <hr style='border: none; border-top: 1px solid #eee; margin-top: 30px;'>
                <p style='color: #999; font-size: 11px; text-align: center;'>Generado desde el formulario de contacto SGBV.</p>
            </div>";
            
            $mail->Body = $cuerpoHTML;
            $mail->AltBody = "Nuevo mensaje de contacto\nRemitente: $remitenteNombre ($remitenteCorreo)\nAsunto: $asunto\n\nMensaje:\n$mensaje";
            
            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log("No se pudo enviar el correo de contacto: " . $mail->ErrorInfo);
            throw new \Exception("Error SMTP: " . $mail->ErrorInfo);
        }
    }
}
