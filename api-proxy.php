<?php
/**
 * Proxy para API Oasy.fy - Resolve problema de CORS
 * Este arquivo faz as requisições para a API da Oasy.fy do lado do servidor
 */

// Incluir sistema de logs simples
require_once 'simple-logger.php';

// Garantir que erros PHP não sejam enviados no corpo da resposta JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, x-public-key, x-secret-key');

// Configurações da API Oasy.fy
// Preferir usar as constantes de config.php quando disponíveis, mantendo as chaves atuais como fallback
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

$OASYFY_PUBLIC_KEY = defined('OASYFY_PUBLIC_KEY') && OASYFY_PUBLIC_KEY ? OASYFY_PUBLIC_KEY : 'kevinmatheus986_a1k8td90862zf2d3';
$OASYFY_SECRET_KEY = defined('OASYFY_SECRET_KEY') && OASYFY_SECRET_KEY ? OASYFY_SECRET_KEY : 'h7gchnycerdys7ty517bspdh2o0inye1cbf97erk8i9421m101zekt389tn83fak';
$OASYFY_API_URL    = defined('OASYFY_API_BASE_URL') && OASYFY_API_BASE_URL ? OASYFY_API_BASE_URL : 'https://app.oasyfy.com/api/v1';

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    SimpleLogger::request('OPTIONS', $_SERVER['REQUEST_URI']);
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    SimpleLogger::warning('Método HTTP não permitido', [
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI']
    ]);
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit();
}

// Obter dados da requisição
$input = file_get_contents('php://input');
$data = json_decode($input, true);

SimpleLogger::request('POST', $_SERVER['REQUEST_URI'], [
    'content_length' => strlen($input),
    'action' => $data['action'] ?? 'unknown'
]);

if (!$data) {
    SimpleLogger::error('Dados JSON inválidos recebidos', [
        'input_length' => strlen($input),
        'json_error' => json_last_error_msg()
    ]);
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
    case 'test_credentials':
        $endpoint = '/ping';
        break;
    case 'test_proxy':
        // Teste simples do proxy
        http_response_code(200);
        echo json_encode([
            'message' => 'Proxy funcionando corretamente',
            'timestamp' => date('c'),
            'status' => 'OK'
        ]);
        exit();
    case 'test_callback_url':
        // Teste de callback URL
        $callbackUrl = $data['callbackUrl'] ?? '';
        
        if (empty($callbackUrl)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Callback URL não fornecida'
            ]);
            exit();
        }
        
        // Verificar se a URL é válida
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'URL inválida'
            ]);
            exit();
        }
        
        // Verificar se é HTTPS
        if (!str_starts_with($callbackUrl, 'https://')) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'URL deve usar HTTPS'
            ]);
            exit();
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Callback URL válida',
            'url' => $callbackUrl,
            'timestamp' => date('c')
        ]);
        exit();
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
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Para requisições POST, enviar dados
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && !in_array($action, ['check_status', 'test_connection', 'test_credentials'], true)
) {
    curl_setopt($ch, CURLOPT_POST, true);

    // Corrigir estrutura dos dados
    $postData = $data['paymentData'] ?? $data;

    // Garantir callbackUrl válido e apontando para webhook-example.php
    if (isset($postData['callbackUrl'])) {
        $callbackUrl = $postData['callbackUrl'];
        // Preferir valor de config.php se definido
        if (defined('OASYFY_WEBHOOK_URL') && OASYFY_WEBHOOK_URL) {
            $callbackUrl = OASYFY_WEBHOOK_URL;
        }
        // Normalização simples: exigir HTTPS e arquivo webhook-example.php
        if (!is_string($callbackUrl) || stripos($callbackUrl, 'https://') !== 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Callback URL deve usar HTTPS',
                'field' => 'callbackUrl'
            ]);
            exit();
        }
        if (strpos($callbackUrl, 'webhook-example.php') === false) {
            // Forçar caminho correto se o host estiver ok
            $parsed = parse_url($callbackUrl);
            if (!empty($parsed['scheme']) && !empty($parsed['host'])) {
                $callbackUrl = sprintf('%s://%s/webhook-example.php', $parsed['scheme'], $parsed['host']);
            }
        }
        $postData['callbackUrl'] = $callbackUrl;
    } elseif ($action === 'generate_pix') {
        // Se gerar PIX sem callback, tentar preencher da config
        if (defined('OASYFY_WEBHOOK_URL') && OASYFY_WEBHOOK_URL) {
            $postData['callbackUrl'] = OASYFY_WEBHOOK_URL;
        }
    }

    // Log dos dados para debug
    SimpleLogger::pix('REQUEST', 'Dados enviados para API Oasy.fy', [
        'endpoint' => $endpoint,
        'data_size' => strlen(json_encode($postData)),
        'has_callback' => !empty($postData['callbackUrl'])
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
} elseif (in_array($action, ['check_status', 'test_connection', 'test_credentials'], true)) {
    // Para verificação de status e teste de conectividade, usar GET
    curl_setopt($ch, CURLOPT_HTTPGET, true);
}

// Executar requisição
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Log da resposta para debug
SimpleLogger::response($httpCode, 'Resposta da API Oasy.fy', [
    'response_size' => strlen($response),
    'action' => $action
]);

// Verificar erros
if ($error) {
    SimpleLogger::error('Erro cURL na requisição', [
        'error' => $error,
        'action' => $action,
        'url' => $url
    ]);
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
    SimpleLogger::error('Resposta vazia da API', [
        'action' => $action,
        'http_code' => $httpCode
    ]);
    http_response_code(500);
    echo json_encode([
        'error' => 'Resposta vazia da API',
        'statusCode' => 500,
        'errorCode' => 'EMPTY_RESPONSE'
    ]);
    exit();
}

if ($action === 'test_credentials') {
    $decodedResponse = json_decode($response, true);
    $isJson = json_last_error() === JSON_ERROR_NONE;
    $success = in_array($httpCode, [200, 201], true);

    $details = ['statusCode' => $httpCode];
    if ($isJson) {
        $details['response'] = $decodedResponse;
    } elseif (strlen(trim((string) $response)) > 0) {
        $details['rawResponse'] = $response;
    }

    $extractMessage = static function ($data) {
        if (!is_array($data)) {
            return null;
        }

        foreach (['message', 'status', 'detail', 'error'] as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && trim($data[$key]) !== '') {
                return trim($data[$key]);
            }
        }

        return null;
    };

    if ($success) {
        $message = $extractMessage($isJson ? $decodedResponse : null)
            ?? 'Credenciais válidas e comunicação com a API confirmada.';
        http_response_code($httpCode ?: 200);
    } else {
        $errorMessages = [
            400 => 'Requisição inválida ao verificar credenciais.',
            401 => 'Credenciais inválidas. Verifique suas chaves de acesso.',
            403 => 'Acesso negado. Verifique as permissões da conta.',
            404 => 'Endpoint de verificação não encontrado.',
            429 => 'Muitas tentativas de verificação. Aguarde e tente novamente.',
            500 => 'Erro interno na API da Oasy.fy ao verificar credenciais.'
        ];

        $message = $errorMessages[$httpCode] ?? 'Erro ao validar credenciais.';

        if ($isJson) {
            $apiMessage = $extractMessage($decodedResponse);
            if ($apiMessage && stripos($message, $apiMessage) === false) {
                $message .= ' Detalhes: ' . $apiMessage;
            }
        }

        $normalizedCode = $httpCode > 0 ? $httpCode : 500;
        http_response_code($normalizedCode);
        $details['statusCode'] = $normalizedCode;
    }

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'details' => $details
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

// Retornar resposta da API
http_response_code($httpCode);
echo $response;
?>
