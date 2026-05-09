<?php

require __DIR__ . '/BayarGgClient.php';

$apiKey = getenv('BAYAR_GG_API_KEY') ?: 'YOUR_API_KEY_HERE';
$client = new BayarGgClient($apiKey);

try {
    $methods = $client->getPaymentMethods();
    echo "Payment methods:\n";
    echo json_encode($methods, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

    $payment = $client->createPayment([
        'amount' => 10000,
        'description' => 'Test payment from PHP',
        'customer_name' => 'BAYAR GG Customer',
        'customer_email' => 'customer@example.com',
        'customer_phone' => '6281234567890',
        'payment_method' => 'qris',
        'callback_url' => 'https://example.com/webhook/bayar-gg',
        'redirect_url' => 'https://example.com/thank-you',
    ]);

    echo "Created payment:\n";
    echo json_encode($payment, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Error: {$e->getMessage()}\n");
    exit(1);
}
