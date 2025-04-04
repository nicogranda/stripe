<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
file_put_contents('debug.txt', print_r($_POST, true));

require 'vendor/autoload.php'; // Stripe SDK
$config = require 'config.php'; // Carga la configuración

\Stripe\Stripe::setApiKey($config['stripe']['secret_key']); // Establece la clave secreta


// Verifica que los datos POST existen antes de usarlos
if (!isset($_POST['payment_method'], $_POST['amount'])) {
    die('Faltan parámetros en la solicitud');
}

//$body = 'Operation: '.$_POST['operation'];
$mailerTo = $_POST['email'];
$paymentMethod = $_POST['payment_method'];
$amount = $_POST['amount'];  // Recibido en centavos

$mailerId = 'Ikusa';
$mailerFrom = 'contact@ikusa.net';
$mailerToToo = 'ikusa.ads@gmail.com';
$mailerReplay = $mailerFrom;
$subject = "Pago";

try {
    // Recuperar el PaymentMethod desde Stripe para ver si es v��lido
    $pm = \Stripe\PaymentMethod::retrieve($paymentMethod);
    file_put_contents('debug_log.txt', "PaymentMethod encontrado: " . print_r($pm, true), FILE_APPEND);
} catch (\Stripe\Exception\ApiErrorException $e) {
    file_put_contents('debug_log.txt', "Error al recuperar PaymentMethod: " . $e->getMessage(), FILE_APPEND);
    die('Error al recuperar PaymentMethod: ' . $e->getMessage());
}


try {
    // Crear un PaymentIntent
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'eur',
        'payment_method' => $paymentMethod,
       // 'confirmation_method' => 'manual',
        'confirm' => true,
        'return_url' => 'https://ikusa.net/stripe/success.php', // URL de retorno
    ]);

// Verificar el estado del pago
    if ($paymentIntent->status === 'requires_action' || $paymentIntent->status === 'requires_source_action') {
        // Aqu�� necesitas manejar la autenticaci��n adicional
        echo 'El pago requiere autenticaci��n adicional';
    } elseif ($paymentIntent->status === 'succeeded') {
        echo 'Pago exitoso';
            //Invoice Create
           // require_once '../../app/controllers/admin/InvoicesController.php';

            // Crear una instancia de MailController y enviar el correo
           // $invoicesController = new InvoicesController();
        
            // Llamar a la funci��n create() para enviar el correo
            //$invoicesController->create();
            
            // Incluir configuración de correo
            //require_once '../../app/config/email.php';

            // Generar el contenido del correo            //ob_start();
            //include '../../app/views/admin/sales/invoices/mail.php';

            // Enviar el correo
            //require_once '../../app/libraries/inc_phpmailer.php';

    } else {
        echo 'Error en el pago: ' . $paymentIntent->status;
    }
    
    // Verifica si la confirmación fue exitosa
    if ($paymentIntent->status == 'succeeded') {
      //  echo 'Pago exitoso. ID de transacción: ' . $paymentIntent->id;
    } else {
        echo 'Pago pendiente o fallido. Estado: ' . $paymentIntent->status;
    }
} catch (\Stripe\Exception\CardException $e) {
    echo 'Error en el pago: ' . $e->getMessage();
} catch (Exception $e) {
    echo 'Error inesperado: ' . $e->getMessage();
}
?>