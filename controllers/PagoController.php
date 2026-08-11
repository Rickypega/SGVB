<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Controlador para la integración de la Pasarela de Pagos (Stripe)
 */
class PagoController {

    private string $stripeSecretKey;

    public function __construct() {
        $config = require __DIR__ . '/../config/stripe.php';
        $this->stripeSecretKey = $config['secret_key'] ?? '';
        \Stripe\Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Muestra la vista de Billetera Virtual
     */
    public function index(): void {
        $usuario = $this->requerirAutenticacion();
        $config = require __DIR__ . '/../config/stripe.php';
        $stripePublishableKey = $config['publishable_key'] ?? '';
        require_once __DIR__ . '/../views/estandar/billetera.php';
    }

    /**
     * Inicia una sesión de Checkout en Stripe
     */
    public function checkout(): void {
        $usuario = $this->requerirAutenticacion();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $montoStr = $_POST['monto'] ?? '0';
            $monto = (float)$montoStr;

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || !empty($_POST['ajax']);

            if ($monto < 5.00 || $monto > 500.00) {
                $msg = 'El monto de recarga debe estar entre 5.00 y 500.00 USD por transacción.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['exito' => false, 'error' => $msg]);
                    exit;
                }
                $_SESSION['error'] = $msg;
                header('Location: ' . BASE_URL . 'billetera');
                exit;
            }

            try {
                // Crear sesión de Stripe Checkout
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => 'Recarga de Créditos ⛃ SGBV',
                                'description' => 'Recarga de billetera virtual para ' . $usuario->nombre,
                            ],
                            'unit_amount' => (int)($monto * 100), // Stripe usa centavos
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => BASE_URL . 'pago/success?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => BASE_URL . 'billetera',
                    'client_reference_id' => (string)$usuario->id,
                ]);

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['exito' => true, 'id' => $session->id]);
                    exit;
                }

                // Redirigir a Stripe (Fallback si no hay JS SDK)
                header('Location: ' . $session->url);
                exit;

            } catch (\Stripe\Exception\ApiErrorException $e) {
                error_log("Stripe API Error: " . $e->getMessage());
                $msg = 'Error de la pasarela: ' . $e->getMessage();
                if ($isAjax) {
                    ob_clean(); // Evitar advertencias antes del json
                    header('Content-Type: application/json');
                    echo json_encode(['exito' => false, 'error' => $msg]);
                    exit;
                }
                $_SESSION['error'] = 'Hubo un error al conectar con la pasarela de pagos. Por favor intenta más tarde.';
                header('Location: ' . BASE_URL . 'billetera');
                exit;
            } catch (\Exception $e) {
                error_log("Stripe Checkout Error: " . $e->getMessage());
                $msg = 'Error del servidor: ' . $e->getMessage();
                if ($isAjax) {
                    ob_clean(); // Evitar advertencias antes del json
                    header('Content-Type: application/json');
                    echo json_encode(['exito' => false, 'error' => $msg]);
                    exit;
                }
                $_SESSION['error'] = 'Hubo un error interno. Por favor intenta más tarde.';
                header('Location: ' . BASE_URL . 'billetera');
                exit;
            }
        }
    }

    /**
     * Retorno exitoso desde Stripe
     */
    public function success(): void {
        $usuario = $this->requerirAutenticacion();
        $sessionId = $_GET['session_id'] ?? '';

        if (empty($sessionId)) {
            $_SESSION['error'] = 'Sesión de pago no válida.';
            header('Location: ' . BASE_URL . 'estandar/panel');
            exit;
        }

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            // Verificar si el pago fue exitoso y corresponde a este usuario
            if ($session->payment_status === 'paid' && $session->client_reference_id == $usuario->id) {
                
                // Evitar recargar dos veces si se recarga la página
                $pdo = Database::getConnection();
                $stmtCheck = $pdo->prepare("SELECT id FROM transacciones_dinero_real WHERE referencia = :ref");
                $stmtCheck->execute(['ref' => $session->id]);
                
                if ($stmtCheck->fetch()) {
                    $_SESSION['exito'] = 'Esta recarga ya fue procesada anteriormente.';
                    header('Location: ' . BASE_URL . 'estandar/panel');
                    exit;
                }

                $montoConvertido = $session->amount_total / 100; // Convertir centavos a dólares/créditos
                
                // Guardar transacción real
                $stmtInsert = $pdo->prepare("INSERT INTO transacciones_dinero_real (usuario_id, monto, moneda, referencia, pasarela, fecha) VALUES (:user_id, :monto, 'USD', :ref, 'Stripe', NOW())");
                $stmtInsert->execute([
                    'user_id' => $usuario->id,
                    'monto' => $montoConvertido,
                    'ref' => $session->id
                ]);

                // Recargar el saldo del usuario
                $usuarioActual = Usuario::porId($usuario->id);
                if ($usuarioActual && $usuarioActual->recargarSaldo((float)$montoConvertido)) {
                    $_SESSION['usuario'] = $usuarioActual;
                    $_SESSION['exito'] = '¡Pago procesado con éxito! Has añadido ' . number_format((float)$montoConvertido, 2) . ' Créditos ⛃ a tu saldo.';
                } else {
                    $_SESSION['error'] = 'Pago recibido, pero ocurrió un error al actualizar tu saldo. Contacta a soporte.';
                }
            } else {
                $_SESSION['error'] = 'El pago no se completó o no coincide con tu cuenta.';
            }
        } catch (\Exception $e) {
            error_log("Stripe Success Verification Error: " . $e->getMessage());
            $_SESSION['error'] = 'Error verificando el pago.';
        }

        header('Location: ' . BASE_URL . 'billetera');
        exit;
    }

    private function requerirAutenticacion(): Usuario {
        if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
            $_SESSION['error'] = 'Debes iniciar sesión para realizar pagos.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        return $_SESSION['usuario'];
    }
}
