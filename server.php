<?php
// Incluir el archivo de configuración
include 'config.php';

// Cargar Stripe SDK (esto ya lo hace config.php con el autoload)
$stripe = $stripe;  // Usamos el cliente de Stripe configurado en config.php

// Recibe los datos necesarios de la solicitud, por ejemplo, el total
$amount = $_POST['amount'];  // O puedes obtener este valor desde un campo del formulario

// Crear la sesión de pago de Stripe
$session = $stripe->checkout->sessions->create([
    'payment_method_types' => ['card'],
    'line_items' => [
        [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Total Payment',
                ],
                'unit_amount' => $amount * 100,  // Stripe usa centavos, así que multiplicamos por 100
            ],
            'quantity' => 1,
        ],
    ],
    'mode' => 'payment',
    'success_url' => STRIPE_SUCCESS_URL,
    'cancel_url' => STRIPE_CANCEL_URL,
]);

// Retornar el client secret de la sesión de pago a la frontend
echo json_encode(['clientSecret' => $session->id]);
?>
