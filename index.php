<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Pago</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Vincula el archivo CSS -->
    <script src="https://js.stripe.com/v3/"></script> <!-- Cargar Stripe.js -->
</head>

<?php 
// Verifica que los parámetros GET estén definidos
$email = isset($_GET["email"]) ? $_GET["email"] : 0;  
$operation = isset($_GET["operation"]) ? $_GET["operation"] : 0;  
$total = isset($_GET["amount"]) ? $_GET["amount"] : 0;  // Valor predeterminado 0 si no se encuentra "amount"
$description = isset($_GET["description"]) ? $_GET["description"] : "Pagando"; // Si no se define, usa "Pagando"

//require '../../app/config/stripe.php';  // Asegúrate de que la ruta sea correcta
$config = require 'config.php';

?>

<body>
    <form id="payment-form">
        <h2>Formulario de Pago</h2>
        <div>
            <label for="amount">Total a pagar:</label>
            <span id="amount"><?php echo number_format($total, 2, ',', '.'); ?> €</span><br>
        </div>
        <div>
            <label for="card-element">Detalles de la tarjeta</label>
            <div id="card-element">
                <!-- Un elemento de tarjeta de Stripe será insertado aquí -->
            </div>
        </div>
        <button id="submit">Pagar</button>
        <div id="card-errors" role="alert"></div>
    </form>

    <script>
        var stripe = Stripe("<?php echo $config['stripe']['public_key']; ?>");

       // var stripe = Stripe("<?php echo $config['stripe']['public_key']; ?>");
        var elements = stripe.elements();

        var card = elements.create('card');
        card.mount('#card-element');

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            stripe.createPaymentMethod({
                type: 'card',
                card: card
            })
            .then(function(result) {
                console.log(result); // Esto nos muestra todo el contenido de result para ver si hay algo raro
            
                if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    var paymentMethod = result.paymentMethod ? result.paymentMethod.id : null;  // Verifica si paymentMethod está presente
            
                    console.log(paymentMethod); // Verifica que paymentMethod está siendo devuelto correctamente
            
                    if (paymentMethod) {
                        var data = new FormData();
                       data.append('payment_method', paymentMethod); // Usamos el paymentMethod
                        data.append('amount', <?php echo json_encode($total * 100); ?>); // Multiplicamos por 100 para obtener centavos
                         data.append('email', <?php echo json_encode($email); ?>); 
                        data.append('operation', <?php echo json_encode($operation); ?>); 
                        
                        fetch('payment.php', {
                            method: 'POST',
                            body: data
                        })
                        .then(response => response.text())
                        .then(data => {
                            alert(data);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Hubo un problema con el pago.');
                        });
                    } else {
                        console.error('No se obtuvo un PaymentMethod válido');
                    }
                }
            });

        });
    </script>
</body>


