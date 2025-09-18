<?php
/**
 * API para fornecer logs de webhooks
 * Integra com o sistema de logs existente
 */

// Incluir sistema de logs simples
require_once 'simple-logger.php';

if (!defined('SIMPLE_LOGGER_LOG_DIR')) {
    define('SIMPLE_LOGGER_LOG_DIR', SimpleLogger::getLogDir());
}

// Headers para CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit();
}

// Parâmetros da requisição
$date = $_GET['date'] ?? date('Y-m-d');
$filter = $_GET['filter'] ?? 'all';
$limit = intval($_GET['limit'] ?? 100);

try {
    $logs = getWebhookLogs($date, $filter, $limit);
    $stats = getWebhookStats($date);
    
    // Debug: adicionar informações de debug se solicitado
    $debug = isset($_GET['debug']) && $_GET['debug'] === '1';
    $response = [
        'success' => true,
        'logs' => $logs,
        'stats' => $stats,
        'date' => $date,
        'filter' => $filter,
        'total' => count($logs)
    ];
    
    if ($debug) {
        $response['debug'] = [
            'log_dir' => SIMPLE_LOGGER_LOG_DIR,
            'log_files_found' => glob(SIMPLE_LOGGER_LOG_DIR . '*.log'),
            'date_requested' => $date,
            'filter_requested' => $filter
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Obtém logs de webhooks
 */
function getWebhookLogs($date, $filter, $limit) {
    $logDir = SIMPLE_LOGGER_LOG_DIR;

    // Criar diretório se não existir
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $webhookLogs = [];
    
    // Lista de arquivos de log para verificar
    $logFiles = [
        'app_' . $date . '.log',           // Logs principais do SimpleLogger
        'webhook_' . date('Y-m') . '.log', // Logs específicos de webhook
        'errors_' . date('Y-m') . '.log',  // Logs de erro
        'performance_' . date('Y-m') . '.log', // Logs de performance
        'audit_' . date('Y-m') . '.log'    // Logs de auditoria
    ];
    
    // Se não encontrar logs para a data específica, buscar em todos os arquivos de log disponíveis
    if (empty(array_filter($logFiles, function($file) use ($logDir) { return file_exists($logDir . $file); }))) {
        $allLogFiles = glob($logDir . '*.log') ?: [];
        $logFiles = array_map('basename', $allLogFiles);
    }
    
    foreach ($logFiles as $logFile) {
        $filePath = $logDir . $logFile;
        
        if (file_exists($filePath)) {
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Tentar diferentes padrões de log
                $logEntry = parseLogLine($line, $logFile);
                
                if ($logEntry && isWebhookRelated($logEntry)) {
                    // Aplicar filtro
                    if (shouldIncludeLog($logEntry, $filter)) {
                        $webhookLogs[] = $logEntry;
                    }
                }
            }
        }
    }
    
    // Ordenar por timestamp (mais recente primeiro)
    usort($webhookLogs, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    // Limitar resultados
    return array_slice($webhookLogs, 0, $limit);
}

/**
 * Analisa uma linha de log e extrai informações
 */
function parseLogLine($line, $sourceFile) {
    // Limpar a linha
    $line = trim($line);
    if (empty($line)) {
        return null;
    }
    
    // Padrão 1: JSON logs (webhook, performance, audit) - PRIORIDADE ALTA
    if (strpos($line, '{') === 0) {
        $jsonData = json_decode($line, true);
        if ($jsonData && isset($jsonData['timestamp'])) {
            return [
                'timestamp' => $jsonData['timestamp'],
                'level' => strtolower($jsonData['level'] ?? 'info'),
                'ip' => $jsonData['ip'] ?? 'unknown',
                'requestId' => $jsonData['request_id'] ?? 'unknown',
                'message' => $jsonData['message'] ?? '',
                'source' => $sourceFile,
                'type' => determineLogType($jsonData['message'] ?? '', $jsonData['level'] ?? 'info'),
                'data' => $jsonData['context'] ?? $jsonData
            ];
        }
    }
    
    // Padrão 2: SimpleLogger format [timestamp] [level] [ip] [requestId] message
    if (preg_match('/^\[([^\]]+)\] \[([^\]]+)\] \[([^\]]+)\] \[([^\]]+)\] (.+)$/', $line, $matches)) {
        return [
            'timestamp' => $matches[1],
            'level' => strtolower($matches[2]),
            'ip' => $matches[3],
            'requestId' => $matches[4],
            'message' => $matches[5],
            'source' => $sourceFile,
            'type' => determineLogType($matches[5], $matches[2]),
            'data' => extractLogData($matches[5])
        ];
    }
    
    // Padrão 3: Logs simples (fallback)
    if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) (.+)$/', $line, $matches)) {
        return [
            'timestamp' => $matches[1],
            'level' => 'info',
            'ip' => 'unknown',
            'requestId' => 'unknown',
            'message' => $matches[2],
            'source' => $sourceFile,
            'type' => determineLogType($matches[2], 'info'),
            'data' => extractLogData($matches[2])
        ];
    }
    
    // Padrão 4: Logs sem formato específico - aceitar qualquer linha não vazia
    if (!empty($line)) {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => 'info',
            'ip' => 'unknown',
            'requestId' => 'unknown',
            'message' => $line,
            'source' => $sourceFile,
            'type' => determineLogType($line, 'info'),
            'data' => extractLogData($line)
        ];
    }
    
    return null;
}

/**
 * Verifica se o log está relacionado a webhooks
 */
function isWebhookRelated($logEntry) {
    $message = strtolower($logEntry['message']);
    $source = strtolower($logEntry['source']);
    
    // Verificar por arquivo de origem primeiro (mais confiável)
    if (strpos($source, 'webhook') !== false || 
        strpos($source, 'performance') !== false ||
        strpos($source, 'audit') !== false ||
        strpos($source, 'app_') !== false) {  // Incluir logs do app principal
        return true;
    }
    
    // Verificar por palavras-chave na mensagem
    $webhookKeywords = [
        'webhook', 'transaction', 'pix', 'payment', 'oasyfy',
        'api-proxy', 'generate_pix', 'check_status', 'callback',
        'paid', 'created', 'canceled', 'refunded', 'completed',
        'processando', 'pagamento', 'processado', 'confirmado',
        'liberado', 'acesso', 'email', 'confirmação', 'backup',
        'sistemas internos', 'status do pedido', 'banco de dados',
        'request', 'response', 'curl', 'http', 'api', 'proxy',
        'dados enviados', 'resposta da api', 'pix gerado',
        'cliente gerado', 'teste de conectividade', 'credenciais'
    ];
    
    foreach ($webhookKeywords as $keyword) {
        if (strpos($message, $keyword) !== false) {
            return true;
        }
    }
    
    // Verificar se contém dados de transação
    if (isset($logEntry['data']) && is_array($logEntry['data'])) {
        $data = $logEntry['data'];
        if (isset($data['transaction_id']) || 
            isset($data['event']) || 
            isset($data['amount']) ||
            isset($data['client_email']) ||
            isset($data['client_name']) ||
            isset($data['action']) ||
            isset($data['endpoint'])) {
            return true;
        }
    }
    
    // Se for um log do SimpleLogger (formato [timestamp] [level] [ip] [requestId] message)
    // e contiver qualquer uma das palavras-chave, incluir
    if (strpos($source, 'app_') !== false) {
        foreach ($webhookKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Determina o tipo do log (sent/received)
 */
function determineLogType($message, $level) {
    $message = strtolower($message);
    $level = strtolower($level);
    
    // Primeiro verificar o nível do log
    if ($level === 'error' || $level === 'critical') {
        return 'error';
    }
    
    if ($level === 'warning') {
        return 'warning';
    }
    
    // Depois verificar a mensagem
    if (strpos($message, 'enviado') !== false || 
        strpos($message, 'sent') !== false ||
        strpos($message, 'enviando') !== false ||
        strpos($message, 'dados enviados') !== false ||
        strpos($message, 'request') !== false) {
        return 'sent';
    }
    
    if (strpos($message, 'recebido') !== false || 
        strpos($message, 'received') !== false ||
        strpos($message, 'webhook recebido') !== false ||
        strpos($message, 'webhook init') !== false ||
        strpos($message, 'processando') !== false ||
        strpos($message, 'pagamento') !== false ||
        strpos($message, 'transaction') !== false ||
        strpos($message, 'resposta da api') !== false) {
        return 'received';
    }
    
    if (strpos($message, 'erro') !== false || 
        strpos($message, 'error') !== false ||
        strpos($message, 'falha') !== false ||
        strpos($message, 'failed') !== false) {
        return 'error';
    }
    
    if (strpos($message, 'aviso') !== false || 
        strpos($message, 'warning') !== false) {
        return 'warning';
    }
    
    // Verificar se é relacionado a PIX ou API
    if (strpos($message, 'pix') !== false || 
        strpos($message, 'api') !== false ||
        strpos($message, 'proxy') !== false ||
        strpos($message, 'oasyfy') !== false) {
        return 'api';
    }
    
    return 'info';
}

/**
 * Extrai dados estruturados da mensagem de log
 */
function extractLogData($message) {
    $data = [];
    
    // Tentar extrair JSON da mensagem
    if (preg_match('/\{.*\}/', $message, $matches)) {
        $jsonData = json_decode($matches[0], true);
        if ($jsonData !== null) {
            $data = $jsonData;
        }
    }
    
    // Extrair informações específicas
    if (preg_match('/transaction[_-]?id[:\s]+([a-zA-Z0-9_-]+)/i', $message, $matches)) {
        $data['transaction_id'] = $matches[1];
    }
    
    if (preg_match('/amount[:\s]+([0-9.]+)/i', $message, $matches)) {
        $data['amount'] = floatval($matches[1]);
    }
    
    if (preg_match('/email[:\s]+([a-zA-Z0-9@._-]+)/i', $message, $matches)) {
        $data['email'] = $matches[1];
    }
    
    if (preg_match('/event[:\s]+([A-Z_]+)/i', $message, $matches)) {
        $data['event'] = $matches[1];
    }
    
    // Extrair action se presente
    if (preg_match('/action[:\s]+([a-zA-Z_]+)/i', $message, $matches)) {
        $data['action'] = $matches[1];
    }
    
    // Extrair endpoint se presente
    if (preg_match('/endpoint[:\s]+([a-zA-Z0-9\/_-]+)/i', $message, $matches)) {
        $data['endpoint'] = $matches[1];
    }
    
    // Extrair client_name se presente
    if (preg_match('/client_name[:\s]+([a-zA-Z\s]+)/i', $message, $matches)) {
        $data['client_name'] = trim($matches[1]);
    }
    
    // Extrair client_email se presente
    if (preg_match('/client_email[:\s]+([a-zA-Z0-9@._-]+)/i', $message, $matches)) {
        $data['client_email'] = $matches[1];
    }
    
    return $data;
}

/**
 * Verifica se o log deve ser incluído baseado no filtro
 */
function shouldIncludeLog($logEntry, $filter) {
    if ($filter === 'all') {
        return true;
    }
    
    switch ($filter) {
        case 'sent':
            return $logEntry['type'] === 'sent';
        case 'received':
            return $logEntry['type'] === 'received';
        case 'error':
            return $logEntry['level'] === 'error' || $logEntry['type'] === 'error';
        case 'warning':
            return $logEntry['level'] === 'warning' || $logEntry['type'] === 'warning';
        default:
            return true;
    }
}

/**
 * Obtém estatísticas dos logs
 */
function getWebhookStats($date) {
    $logDir = SIMPLE_LOGGER_LOG_DIR;

    // Criar diretório se não existir
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $stats = [
        'sent' => 0,
        'received' => 0,
        'errors' => 0,
        'warnings' => 0,
        'total' => 0,
        'api_calls' => 0,
        'pix_transactions' => 0,
        'performance_logs' => 0
    ];
    
    // Lista de arquivos de log para verificar
    $logFiles = [
        'app_' . $date . '.log',           // Logs principais do SimpleLogger
        'webhook_' . date('Y-m') . '.log', // Logs específicos de webhook
        'errors_' . date('Y-m') . '.log',  // Logs de erro
        'performance_' . date('Y-m') . '.log', // Logs de performance
        'audit_' . date('Y-m') . '.log'    // Logs de auditoria
    ];
    
    // Se não encontrar logs para a data específica, buscar em todos os arquivos de log disponíveis
    if (empty(array_filter($logFiles, function($file) use ($logDir) { return file_exists($logDir . $file); }))) {
        $allLogFiles = glob($logDir . '*.log') ?: [];
        $logFiles = array_map('basename', $allLogFiles);
    }
    
    foreach ($logFiles as $logFile) {
        $filePath = $logDir . $logFile;
        
        if (file_exists($filePath)) {
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                $logEntry = parseLogLine($line, $logFile);
                
                if ($logEntry && isWebhookRelated($logEntry)) {
                    $stats['total']++;
                    
                    // Contar por tipo
                    switch ($logEntry['type']) {
                        case 'sent':
                            $stats['sent']++;
                            break;
                        case 'received':
                            $stats['received']++;
                            break;
                        case 'error':
                            $stats['errors']++;
                            break;
                        case 'warning':
                            $stats['warnings']++;
                            break;
                    }
                    
                    // Contar por categoria específica
                    $message = strtolower($logEntry['message']);
                    if (strpos($message, 'api') !== false || strpos($message, 'proxy') !== false) {
                        $stats['api_calls']++;
                    }
                    if (strpos($message, 'pix') !== false || strpos($message, 'transaction') !== false) {
                        $stats['pix_transactions']++;
                    }
                    if (strpos($logEntry['source'], 'performance') !== false) {
                        $stats['performance_logs']++;
                    }
                }
            }
        }
    }
    
    return $stats;
}

/**
 * Limpa logs antigos
 */
function cleanupOldLogs() {
    $logDir = SIMPLE_LOGGER_LOG_DIR;
    $cutoff = time() - (7 * 24 * 3600); // 7 dias atrás

    if (is_dir($logDir)) {
        $files = glob($logDir . 'app_*.log') ?: [];
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}

// Limpar logs antigos se solicitado
if (isset($_GET['cleanup']) && $_GET['cleanup'] === '1') {
    cleanupOldLogs();
}
?>
