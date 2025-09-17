<?php
/**
 * Teste Simples de Credenciais Oasy.fy
 * Este script testa apenas a conectividade básica
 */

header('Content-Type: application/json');

// Suas credenciais
$publicKey = 'kevinmatheus986_a1k8td90862zf2d3';
$secretKey = 'h7gchnycerdys7ty517bspdh2o0inye1cbf97erk8i9421m101zekt389tn83fak';

echo "=== TESTE DE CREDENCIAIS OASY.FY ===\n\n";

// Teste 1: Ping
echo "1. Testando conectividade (ping)...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://app.oasyfy.com/api/v1/ping');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'x-public-key: ' . $publicKey,
    'x-secret-key: ' . $secretKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Resposta: $response\n";
if ($error) {
    echo "Erro: $error\n";
}
echo "\n";

// Teste 2: Dados do produtor
echo "2. Testando dados do produtor...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://app.oasyfy.com/api/v1/gateway/producer');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'x-public-key: ' . $publicKey,
    'x-secret-key: ' . $secretKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Resposta: $response\n";
if ($error) {
    echo "Erro: $error\n";
}
echo "\n";

// Teste 3: PIX simples
echo "3. Testando geração de PIX simples...\n";
$pixData = [
    'identifier' => 'teste-' . time(),
    'amount' => 1.00, // Valor mínimo para teste
    'client' => [
        'name' => 'Cliente Teste',
        'email' => 'teste@exemplo.com',
        'phone' => '(11) 99999-9999',
        'document' => '123.456.789-00'
    ],
    'callbackUrl' => 'https://matheus-39wu.onrender.com/webhook-example.php'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://app.oasyfy.com/api/v1/gateway/pix/receive');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pixData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'x-public-key: ' . $publicKey,
    'x-secret-key: ' . $secretKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Resposta: $response\n";
if ($error) {
    echo "Erro: $error\n";
}

echo "\n=== FIM DO TESTE ===\n";
?>
