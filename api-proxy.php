<?php
/**
 * Proxy para API Oasy.fy - Resolve problema de CORS
 * Este arquivo faz as requisições para a API da Oasy.fy do lado do servidor
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, x-public-key, x-secret-key');

// Configurações da API Oasy.fy
$OASYFY_PUBLIC_KEY = 'kevinmatheus986_a1k8td90862zf2d3';
$OASYFY_SECRET_KEY = 'h7gchnycerdys7ty517bspdh2o0inye1cbf97erk8i9421m101zekt389tn83fak';
$OASYFY_API_URL = 'https://app.oasyfy.com/api/v1';

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit();
}

// Obter dados da requisição
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit();
}

// Determinar endpoint baseado na ação
$action = $data['action'] ?? 'generate_pix';
$endpoint = '';

switch ($action) {
    case 'generate_pix':
        $endpoint = '/gateway/pix/receive';
        break;
    case 'check_status':
        $transactionId = $data['transactionId'] ?? '';
        $endpoint = "/gateway/transactions?id={$transactionId}";
        break;
    case 'test_connection':
        $endpoint = '/ping';
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação não suportada']);
        exit();
}

// Preparar requisição para a API Oasy.fy
$url = $OASYFY_API_URL . $endpoint;
$headers = [
    'x-public-key: ' . $OASYFY_PUBLIC_KEY,
    'x-secret-key: ' . $OASYFY_SECRET_KEY,
    'Content-Type: application/json'
];

// Configurar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

// Para requisições POST, enviar dados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'check_status' && $action !== 'test_connection') {
    curl_setopt($ch, CURLOPT_POST, true);
    
    // Corrigir estrutura dos dados
    $postData = $data['paymentData'] ?? $data;
    
    // Log dos dados para debug
    error_log('Dados enviados para API: ' . json_encode($postData, JSON_PRETTY_PRINT));
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
}

// Executar requisição
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Log da resposta para debug
error_log("Resposta da API Oasy.fy - Status: $httpCode");
error_log("Resposta da API Oasy.fy - Body: " . $response);

// Verificar erros
if ($error) {
    error_log("Erro cURL: " . $error);
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro de conexão: ' . $error,
        'statusCode' => 500,
        'errorCode' => 'CONNECTION_ERROR'
    ]);
    exit();
}

// Verificar se a resposta é válida
if ($response === false) {
    error_log("Resposta vazia da API");
    http_response_code(500);
    echo json_encode([
        'error' => 'Resposta vazia da API',
        'statusCode' => 500,
        'errorCode' => 'EMPTY_RESPONSE'
    ]);
    exit();
}

// Retornar resposta da API
http_response_code($httpCode);
echo $response;
?>
