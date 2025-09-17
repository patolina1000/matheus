<?php
/**
 * Teste de Conectividade com API Oasy.fy
 * 
 * Script para testar se suas credenciais estão funcionando corretamente
 */

// Suas credenciais
$publicKey = 'kevinmatheus986_a1k8td90862zf2d3';
$secretKey = 'h7gchnycerdys7ty517bspdh2o0inye1cbf97erk8i9421m101zekt389tn83fak';
$apiBaseUrl = 'https://app.oasyfy.com/api/v1';

echo "🔐 TESTE DE CREDENCIAIS OASY.FY\n";
echo str_repeat("=", 50) . "\n";
echo "Chave Pública: " . substr($publicKey, 0, 20) . "...\n";
echo "Chave Privada: " . substr($secretKey, 0, 20) . "...\n";
echo "URL Base: $apiBaseUrl\n\n";

/**
 * Função para fazer requisição HTTP
 */
function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Oasyfy-Test-Script/1.0');
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

/**
 * Teste 1: Ping da API
 */
echo "1️⃣ TESTE DE PING\n";
echo str_repeat("-", 30) . "\n";

$pingUrl = $apiBaseUrl . '/ping';
$headers = [
    'x-public-key: ' . $publicKey,
    'x-secret-key: ' . $secretKey
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $pingUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "URL: $pingUrl\n";
echo "HTTP Code: $httpCode\n";

if ($error) {
    echo "❌ Erro cURL: $error\n";
} else {
    echo "Resposta: $response\n";
    
    $data = json_decode($response, true);
    if ($data && isset($data['message'])) {
        if ($data['message'] === 'pong') {
            echo "✅ Ping bem-sucedido!\n";
        } else {
            echo "⚠️ Resposta inesperada: " . $data['message'] . "\n";
        }
    } else {
        echo "⚠️ Resposta não é JSON válido\n";
    }
}

echo "\n";

/**
 * Teste 2: Gerar PIX de Teste
 */
echo "2️⃣ TESTE DE GERAÇÃO DE PIX\n";
echo str_repeat("-", 30) . "\n";

$pixData = [
    'identifier' => 'teste-credenciais-' . time(),
    'amount' => 1.00,
    'client' => [
        'name' => 'Teste de Credenciais',
        'email' => 'teste@credenciais.com',
        'phone' => '(11) 99999-9999',
        'document' => '123.456.789-00'
    ],
    'callbackUrl' => 'https://seusite.com/webhook/pix'
];

$pixUrl = $apiBaseUrl . '/gateway/pix/receive';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $pixUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pixData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, [
    'Content-Type: application/json'
]));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "URL: $pixUrl\n";
echo "HTTP Code: $httpCode\n";

if ($error) {
    echo "❌ Erro cURL: $error\n";
} else {
    echo "Resposta: $response\n";
    
    $data = json_decode($response, true);
    if ($data) {
        if (isset($data['status']) && $data['status'] === 'OK') {
            echo "✅ PIX gerado com sucesso!\n";
            echo "   Transaction ID: " . ($data['transactionId'] ?? 'N/A') . "\n";
            echo "   Fee: R$ " . ($data['fee'] ?? 'N/A') . "\n";
            if (isset($data['pix']['image'])) {
                echo "   QR Code: " . $data['pix']['image'] . "\n";
            }
        } else {
            echo "❌ Erro ao gerar PIX:\n";
            echo "   Status: " . ($data['status'] ?? 'N/A') . "\n";
            echo "   Erro: " . ($data['errorDescription'] ?? 'N/A') . "\n";
        }
    } else {
        echo "⚠️ Resposta não é JSON válido\n";
    }
}

echo "\n";

/**
 * Teste 3: Consultar Transações
 */
echo "3️⃣ TESTE DE CONSULTA DE TRANSAÇÕES\n";
echo str_repeat("-", 30) . "\n";

$transactionsUrl = $apiBaseUrl . '/gateway/transactions';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $transactionsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "URL: $transactionsUrl\n";
echo "HTTP Code: $httpCode\n";

if ($error) {
    echo "❌ Erro cURL: $error\n";
} else {
    echo "Resposta: $response\n";
    
    $data = json_decode($response, true);
    if ($data) {
        if (is_array($data)) {
            echo "✅ Consulta de transações bem-sucedida!\n";
            echo "   Total de transações: " . count($data) . "\n";
            
            if (count($data) > 0) {
                $lastTransaction = $data[0];
                echo "   Última transação:\n";
                echo "     ID: " . ($lastTransaction['id'] ?? 'N/A') . "\n";
                echo "     Status: " . ($lastTransaction['status'] ?? 'N/A') . "\n";
                echo "     Valor: R$ " . ($lastTransaction['amount'] ?? 'N/A') . "\n";
            }
        } else {
            echo "⚠️ Resposta não é um array de transações\n";
        }
    } else {
        echo "⚠️ Resposta não é JSON válido\n";
    }
}

echo "\n";

/**
 * Resumo dos Testes
 */
echo "📊 RESUMO DOS TESTES\n";
echo str_repeat("=", 50) . "\n";

if ($httpCode === 200) {
    echo "✅ Suas credenciais estão funcionando corretamente!\n";
    echo "✅ Você pode usar o sistema de webhook PIX.\n";
    echo "\n📋 Próximos passos:\n";
    echo "1. Configure seu webhook URL na Oasy.fy\n";
    echo "2. Teste o webhook com: php test-webhook.php all\n";
    echo "3. Monitore com: php monitor-webhook.php\n";
} else {
    echo "❌ Problemas encontrados com as credenciais.\n";
    echo "❌ Verifique se as chaves estão corretas.\n";
    echo "\n🔧 Possíveis soluções:\n";
    echo "1. Verifique se as chaves foram copiadas corretamente\n";
    echo "2. Confirme se a conta está ativa na Oasy.fy\n";
    echo "3. Verifique se há restrições de IP\n";
    echo "4. Entre em contato com o suporte da Oasy.fy\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Teste concluído em: " . date('Y-m-d H:i:s') . "\n";
?>
