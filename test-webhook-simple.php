<?php
/**
 * Teste simples do webhook
 */

// Simular dados de webhook da Oasy.fy
$webhookData = [
    'event' => 'TRANSACTION_PAID',
    'transaction' => [
        'id' => 'txn_test_' . time(),
        'status' => 'COMPLETED',
        'amount' => 99.90,
        'paymentMethod' => 'PIX'
    ],
    'client' => [
        'id' => 'client_123',
        'name' => 'João Silva',
        'email' => 'joao@exemplo.com'
    ],
    'token' => 'tbdeizos8f'
];

// Fazer requisição para o webhook
$url = 'http://localhost:8000/webhook-example.php';
$data = json_encode($webhookData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "=== TESTE DO WEBHOOK ===\n";
echo "URL: $url\n";
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
if ($error) {
    echo "Error: $error\n";
}
echo "========================\n";
?>
