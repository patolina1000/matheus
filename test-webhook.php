<?php
/**
 * Script de Teste para Webhook PIX Oasy.fy
 * 
 * Este script simula requisições para o webhook para testar
 * o tratamento de erros e funcionalidades implementadas.
 */

// Configurações
$webhookUrl = 'http://localhost/webhook-example.php';
$testToken = 'kevinmatheus986_a1k8td90862zf2d3'; // Sua chave pública

/**
 * Dados de teste para diferentes cenários
 */
$testCases = [
    'success' => [
        'name' => 'Teste de Sucesso',
        'data' => [
            'event' => 'TRANSACTION_PAID',
            'token' => $testToken,
            'transaction' => [
                'id' => 'test-' . time(),
                'status' => 'COMPLETED',
                'amount' => 100.50,
                'paymentMethod' => 'PIX'
            ],
            'client' => [
                'id' => 'client-123',
                'name' => 'João da Silva',
                'email' => 'joao@teste.com',
                'phone' => '(11) 99999-9999'
            ]
        ]
    ],
    
    'validation_error' => [
        'name' => 'Teste de Erro de Validação',
        'data' => [
            'event' => 'INVALID_EVENT',
            'token' => $testToken,
            'transaction' => [
                'id' => 'test-invalid',
                'status' => 'COMPLETED',
                'amount' => -100, // Valor inválido
                'paymentMethod' => 'PIX'
            ],
            'client' => [
                'id' => 'client-123',
                'name' => 'João da Silva',
                'email' => 'email-invalido', // Email inválido
                'phone' => '(11) 99999-9999'
            ]
        ]
    ],
    
    'security_error' => [
        'name' => 'Teste de Erro de Segurança',
        'data' => [
            'event' => 'TRANSACTION_PAID',
            'token' => 'token-invalido',
            'transaction' => [
                'id' => 'test-security',
                'status' => 'COMPLETED',
                'amount' => 100.50,
                'paymentMethod' => 'PIX'
            ],
            'client' => [
                'id' => 'client-123',
                'name' => 'João da Silva',
                'email' => 'joao@teste.com',
                'phone' => '(11) 99999-9999'
            ]
        ]
    ],
    
    'idempotency_test' => [
        'name' => 'Teste de Idempotência',
        'data' => [
            'event' => 'TRANSACTION_PAID',
            'token' => $testToken,
            'transaction' => [
                'id' => 'test-idempotency', // ID fixo para testar duplicata
                'status' => 'COMPLETED',
                'amount' => 100.50,
                'paymentMethod' => 'PIX'
            ],
            'client' => [
                'id' => 'client-123',
                'name' => 'João da Silva',
                'email' => 'joao@teste.com',
                'phone' => '(11) 99999-9999'
            ]
        ]
    ],
    
    'created_event' => [
        'name' => 'Teste de Evento TRANSACTION_CREATED',
        'data' => [
            'event' => 'TRANSACTION_CREATED',
            'token' => $testToken,
            'transaction' => [
                'id' => 'test-created-' . time(),
                'status' => 'PENDING',
                'amount' => 50.00,
                'paymentMethod' => 'PIX'
            ],
            'client' => [
                'id' => 'client-456',
                'name' => 'Maria Santos',
                'email' => 'maria@teste.com',
                'phone' => '(11) 88888-8888'
            ]
        ]
    ],
    
    'canceled_event' => [
        'name' => 'Teste de Evento TRANSACTION_CANCELED',
        'data' => [
            'event' => 'TRANSACTION_CANCELED',
            'token' => $testToken,
            'transaction' => [
                'id' => 'test-canceled-' . time(),
                'status' => 'CANCELED',
                'amount' => 75.00,
                'paymentMethod' => 'PIX'
            ],
            'client' => [
                'id' => 'client-789',
                'name' => 'Pedro Oliveira',
                'email' => 'pedro@teste.com',
                'phone' => '(11) 77777-7777'
            ]
        ]
    ]
];

/**
 * Função para enviar requisição HTTP
 */
function sendWebhookRequest($url, $data) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: Webhook-Test-Script/1.0'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
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
 * Função para exibir resultado do teste
 */
function displayTestResult($testName, $result) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "TESTE: $testName\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "HTTP Code: " . $result['http_code'] . "\n";
    
    if ($result['error']) {
        echo "Erro cURL: " . $result['error'] . "\n";
    } else {
        echo "Resposta: " . $result['response'] . "\n";
        
        // Tentar decodificar JSON
        $jsonResponse = json_decode($result['response'], true);
        if ($jsonResponse) {
            echo "\nResposta Estruturada:\n";
            echo json_encode($jsonResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
    
    echo str_repeat("-", 60) . "\n";
}

/**
 * Função para verificar status do webhook
 */
function checkWebhookStatus() {
    global $webhookUrl;
    
    echo "Verificando status do webhook...\n";
    echo "URL: $webhookUrl\n";
    
    // Teste simples de conectividade
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhookUrl);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        echo "❌ Erro de conectividade: $error\n";
        return false;
    } else {
        echo "✅ Webhook acessível (HTTP $httpCode)\n";
        return true;
    }
}

/**
 * Função para executar todos os testes
 */
function runAllTests() {
    global $testCases, $webhookUrl;
    
    echo "🧪 INICIANDO TESTES DO WEBHOOK PIX OASY.FY\n";
    echo "URL do Webhook: $webhookUrl\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    
    // Verificar conectividade
    if (!checkWebhookStatus()) {
        echo "\n❌ Webhook não está acessível. Verifique se está rodando.\n";
        return;
    }
    
    // Executar testes
    foreach ($testCases as $key => $testCase) {
        echo "\n🔄 Executando: " . $testCase['name'] . "...\n";
        
        $result = sendWebhookRequest($webhookUrl, $testCase['data']);
        displayTestResult($testCase['name'], $result);
        
        // Pequena pausa entre testes
        sleep(1);
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ TESTES CONCLUÍDOS\n";
    echo "Verifique os logs em: logs/webhook_" . date('Y-m') . ".log\n";
    echo "Verifique os dados em: data/processed_transactions.json\n";
    echo str_repeat("=", 60) . "\n";
}

/**
 * Função para executar teste específico
 */
function runSpecificTest($testKey) {
    global $testCases, $webhookUrl;
    
    if (!isset($testCases[$testKey])) {
        echo "❌ Teste '$testKey' não encontrado.\n";
        echo "Testes disponíveis: " . implode(', ', array_keys($testCases)) . "\n";
        return;
    }
    
    $testCase = $testCases[$testKey];
    echo "🔄 Executando teste específico: " . $testCase['name'] . "\n";
    
    $result = sendWebhookRequest($webhookUrl, $testCase['data']);
    displayTestResult($testCase['name'], $result);
}

/**
 * Função para executar teste de idempotência
 */
function runIdempotencyTest() {
    global $webhookUrl, $testToken;
    
    echo "🔄 Executando teste de idempotência...\n";
    
    $data = [
        'event' => 'TRANSACTION_PAID',
        'token' => $testToken,
        'transaction' => [
            'id' => 'test-idempotency-' . time(),
            'status' => 'COMPLETED',
            'amount' => 100.50,
            'paymentMethod' => 'PIX'
        ],
        'client' => [
            'id' => 'client-idempotency',
            'name' => 'Cliente Idempotência',
            'email' => 'idempotencia@teste.com',
            'phone' => '(11) 99999-9999'
        ]
    ];
    
    echo "1ª requisição:\n";
    $result1 = sendWebhookRequest($webhookUrl, $data);
    displayTestResult('Idempotência - 1ª Requisição', $result1);
    
    echo "\n2ª requisição (mesma transação):\n";
    $result2 = sendWebhookRequest($webhookUrl, $data);
    displayTestResult('Idempotência - 2ª Requisição', $result2);
    
    // Verificar se a segunda requisição retornou "already_processed"
    $jsonResponse = json_decode($result2['response'], true);
    if ($jsonResponse && $jsonResponse['status'] === 'already_processed') {
        echo "✅ Teste de idempotência PASSOU!\n";
        echo "   Request ID: " . ($jsonResponse['request_id'] ?? 'N/A') . "\n";
        echo "   Original Processed At: " . ($jsonResponse['original_processed_at'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Teste de idempotência FALHOU!\n";
    }
}

/**
 * Função para executar teste de performance
 */
function runPerformanceTest() {
    global $webhookUrl, $testToken;
    
    echo "⚡ Executando teste de performance...\n";
    
    $times = [];
    $successCount = 0;
    $totalTests = 5;
    
    for ($i = 1; $i <= $totalTests; $i++) {
        $data = [
            'event' => 'TRANSACTION_PAID',
            'token' => $testToken,
            'transaction' => [
                'id' => 'test-performance-' . time() . '-' . $i,
                'status' => 'COMPLETED',
                'amount' => 100.50,
                'paymentMethod' => 'PIX'
            ],
            'client' => [
                'id' => 'client-performance-' . $i,
                'name' => 'Cliente Performance ' . $i,
                'email' => 'performance' . $i . '@teste.com',
                'phone' => '(11) 99999-999' . $i
            ]
        ];
        
        $startTime = microtime(true);
        $result = sendWebhookRequest($webhookUrl, $data);
        $endTime = microtime(true);
        
        $responseTime = round(($endTime - $startTime) * 1000, 2);
        $times[] = $responseTime;
        
        if ($result['http_code'] === 200) {
            $successCount++;
        }
        
        echo "  Teste $i: {$responseTime}ms (HTTP {$result['http_code']})\n";
        
        // Pequena pausa entre testes
        usleep(100000); // 100ms
    }
    
    $avgTime = round(array_sum($times) / count($times), 2);
    $minTime = min($times);
    $maxTime = max($times);
    
    echo "\n📊 Resultados do Teste de Performance:\n";
    echo "  Sucessos: $successCount/$totalTests\n";
    echo "  Tempo Médio: {$avgTime}ms\n";
    echo "  Tempo Mínimo: {$minTime}ms\n";
    echo "  Tempo Máximo: {$maxTime}ms\n";
    
    if ($successCount === $totalTests) {
        echo "✅ Teste de performance PASSOU!\n";
    } else {
        echo "❌ Teste de performance FALHOU!\n";
    }
}

/**
 * Função para executar teste de logs
 */
function runLogsTest() {
    global $webhookUrl, $testToken;
    
    echo "📝 Executando teste de logs...\n";
    
    $data = [
        'event' => 'TRANSACTION_PAID',
        'token' => $testToken,
        'transaction' => [
            'id' => 'test-logs-' . time(),
            'status' => 'COMPLETED',
            'amount' => 100.50,
            'paymentMethod' => 'PIX'
        ],
        'client' => [
            'id' => 'client-logs',
            'name' => 'Cliente Logs',
            'email' => 'logs@teste.com',
            'phone' => '(11) 99999-9999'
        ]
    ];
    
    $result = sendWebhookRequest($webhookUrl, $data);
    displayTestResult('Teste de Logs', $result);
    
    $jsonResponse = json_decode($result['response'], true);
    if ($jsonResponse && isset($jsonResponse['request_id'])) {
        echo "✅ Teste de logs PASSOU!\n";
        echo "   Request ID: " . $jsonResponse['request_id'] . "\n";
        echo "   Processing Time: " . ($jsonResponse['processing_time_ms'] ?? 'N/A') . "ms\n";
        echo "   Execution Time: " . ($jsonResponse['execution_time_ms'] ?? 'N/A') . "ms\n";
        
        echo "\n📋 Verifique os logs em:\n";
        echo "   - logs/webhook_" . date('Y-m') . ".log\n";
        echo "   - logs/performance_" . date('Y-m') . ".log\n";
        echo "   - logs/audit_" . date('Y-m') . ".log\n";
    } else {
        echo "❌ Teste de logs FALHOU!\n";
    }
}

/**
 * Função para mostrar ajuda
 */
function showHelp() {
    echo "🧪 SCRIPT DE TESTE DO WEBHOOK PIX OASY.FY\n\n";
    echo "Uso:\n";
    echo "  php test-webhook.php [opção]\n\n";
    echo "Opções:\n";
    echo "  all                    Executar todos os testes\n";
    echo "  idempotency           Testar idempotência\n";
    echo "  performance           Teste de performance\n";
    echo "  logs                  Teste de logs\n";
    echo "  success               Teste de sucesso\n";
    echo "  validation_error      Teste de erro de validação\n";
    echo "  security_error        Teste de erro de segurança\n";
    echo "  created_event         Teste de evento TRANSACTION_CREATED\n";
    echo "  canceled_event        Teste de evento TRANSACTION_CANCELED\n";
    echo "  help                  Mostrar esta ajuda\n\n";
    echo "Exemplos:\n";
    echo "  php test-webhook.php all\n";
    echo "  php test-webhook.php idempotency\n";
    echo "  php test-webhook.php performance\n";
    echo "  php test-webhook.php logs\n";
    echo "  php test-webhook.php success\n";
}

// Execução principal
if ($argc < 2) {
    showHelp();
    exit(1);
}

$command = $argv[1];

switch ($command) {
    case 'all':
        runAllTests();
        break;
        
    case 'idempotency':
        runIdempotencyTest();
        break;
        
    case 'performance':
        runPerformanceTest();
        break;
        
    case 'logs':
        runLogsTest();
        break;
        
    case 'help':
        showHelp();
        break;
        
    default:
        if (isset($testCases[$command])) {
            runSpecificTest($command);
        } else {
            echo "❌ Comando inválido: $command\n\n";
            showHelp();
            exit(1);
        }
        break;
}
?>
