<?php
/**
 * Webhook PIX Oasy.fy - Versão com Tratamento de Erros Robusto
 * 
 * Funcionalidades implementadas:
 * 1. ✅ Validação de segurança com token
 * 2. ✅ Sistema de logs estruturado
 * 3. ✅ Tratamento de erros robusto
 * 4. ✅ Idempotência para evitar processamento duplicado
 * 5. ✅ Retry logic para falhas temporárias
 * 6. ✅ Classes de exceção personalizadas
 * 7. ✅ Verificação de arquivo de configuração
 */

// Verificar se arquivo de configuração existe
if (!file_exists(__DIR__ . '/config.php')) {
    // Tentar carregar config.example.php em modo demonstração
    if (file_exists(__DIR__ . '/config.example.php')) {
        // Verificar se está em ambiente de demonstração
        $isDemo = isset($_GET['demo']) || isset($_SERVER['HTTP_X_DEMO_MODE']) || 
                  (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'demo') !== false);
        
        if ($isDemo) {
            // Carregar config.example.php em modo somente leitura
            require_once 'config.example.php';
        } else {
            // Retornar erro 500 com instruções
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Config file not found',
                'hint' => 'Copie config.example.php para config.php',
                'instructions' => [
                    '1. Copie o arquivo config.example.php para config.php',
                    '2. Configure suas credenciais no arquivo config.php',
                    '3. Para modo demonstração, adicione ?demo=1 à URL'
                ]
            ]);
            exit;
        }
    } else {
        // Nenhum arquivo de configuração encontrado
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'No configuration files found',
            'hint' => 'Verifique se config.example.php existe no diretório'
        ]);
        exit;
    }
} else {
    // Carregar configurações normalmente
    require_once 'config.php';
}

// Configurações de segurança
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Headers de segurança
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Configurações do sistema (usando config.php)
$logConfig = getOasyfyConfig('log');
$idempotencyConfig = getOasyfyConfig('idempotency');
$systemConfig = getOasyfyConfig('system');

define('WEBHOOK_LOG_FILE', $logConfig['log_dir'] . 'webhook_' . date('Y-m') . '.log');
define('ERROR_LOG_FILE', $logConfig['log_dir'] . 'errors_' . date('Y-m') . '.log');
define('PERFORMANCE_LOG_FILE', $logConfig['log_dir'] . 'performance_' . date('Y-m') . '.log');
define('AUDIT_LOG_FILE', $logConfig['log_dir'] . 'audit_' . date('Y-m') . '.log');
define('PROCESSED_TRANSACTIONS_FILE', 'data/processed_transactions.json');
define('IDEMPOTENCY_CACHE_FILE', 'data/idempotency_cache.json');
define('MAX_RETRY_ATTEMPTS', $systemConfig['retry_attempts']);
define('RETRY_DELAY_SECONDS', $systemConfig['retry_delay']);
define('IDEMPOTENCY_TTL_HOURS', $idempotencyConfig['ttl_hours']);
define('LOG_ROTATION_DAYS', $logConfig['log_rotation_days']);
define('CACHE_CLEANUP_INTERVAL', $idempotencyConfig['cache_cleanup_interval']);

// Criar diretórios se não existirem
if (!file_exists('logs')) mkdir('logs', 0755, true);
if (!file_exists('data')) mkdir('data', 0755, true);

/**
 * Classes de Exceção Personalizadas
 */
class WebhookException extends Exception {
    protected $errorCode;
    protected $httpStatusCode;
    
    public function __construct($message, $errorCode = 'WEBHOOK_ERROR', $httpStatusCode = 500, $previous = null) {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
        $this->httpStatusCode = $httpStatusCode;
    }
    
    public function getErrorCode() { return $this->errorCode; }
    public function getHttpStatusCode() { return $this->httpStatusCode; }
}

class ValidationException extends WebhookException {
    public function __construct($message, $previous = null) {
        parent::__construct($message, 'VALIDATION_ERROR', 400, $previous);
    }
}

class SecurityException extends WebhookException {
    public function __construct($message, $previous = null) {
        parent::__construct($message, 'SECURITY_ERROR', 401, $previous);
    }
}

class ProcessingException extends WebhookException {
    public function __construct($message, $previous = null) {
        parent::__construct($message, 'PROCESSING_ERROR', 500, $previous);
    }
}

/**
 * Sistema de Logs Avançado com Rotação e Métricas
 */
class Logger {
    private static $startTime;
    private static $requestId;
    private static $logLevels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4
    ];
    private static $currentLogLevel = 1; // info por padrão
    
    public static function init() {
        self::$startTime = microtime(true);
        self::$requestId = uniqid('req_', true);
        self::cleanupOldLogs();
    }
    
    public static function setLogLevel($level) {
        if (isset(self::$logLevels[$level])) {
            self::$currentLogLevel = self::$logLevels[$level];
        }
    }
    
    public static function log($level, $message, $context = []) {
        // Verificar se deve logar baseado no nível
        if (self::$logLevels[$level] < self::$currentLogLevel) {
            return;
        }
        
        $logEntry = [
            'timestamp' => date('c'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'request_id' => self::$requestId,
            'execution_time' => round((microtime(true) - self::$startTime) * 1000, 2) . 'ms',
            'memory_usage' => self::formatBytes(memory_get_usage(true)),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'server' => [
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
            ]
        ];
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        
        // Log geral
        file_put_contents(WEBHOOK_LOG_FILE, $logLine, FILE_APPEND | LOCK_EX);
        
        // Log de erro separado
        if (in_array($level, ['error', 'critical'])) {
            file_put_contents(ERROR_LOG_FILE, $logLine, FILE_APPEND | LOCK_EX);
        }
        
        // Log de performance
        if (in_array($level, ['info', 'warning', 'error', 'critical'])) {
            self::logPerformance($level, $message, $context);
        }
        
        // Log de auditoria para eventos importantes
        if (in_array($level, ['info', 'warning', 'error', 'critical'])) {
            self::logAudit($level, $message, $context);
        }
    }
    
    public static function debug($message, $context = []) {
        self::log('debug', $message, $context);
    }
    
    public static function info($message, $context = []) {
        self::log('info', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log('warning', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::log('error', $message, $context);
    }
    
    public static function critical($message, $context = []) {
        self::log('critical', $message, $context);
    }
    
    private static function logPerformance($level, $message, $context) {
        $performanceEntry = [
            'timestamp' => date('c'),
            'level' => strtoupper($level),
            'message' => $message,
            'request_id' => self::$requestId,
            'execution_time' => round((microtime(true) - self::$startTime) * 1000, 2),
            'memory_peak' => self::formatBytes(memory_get_peak_usage(true)),
            'memory_current' => self::formatBytes(memory_get_usage(true)),
            'context' => $context
        ];
        
        $logLine = json_encode($performanceEntry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents(PERFORMANCE_LOG_FILE, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    private static function logAudit($level, $message, $context) {
        $auditEntry = [
            'timestamp' => date('c'),
            'level' => strtoupper($level),
            'message' => $message,
            'request_id' => self::$requestId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'transaction_id' => $context['transaction_id'] ?? null,
            'event' => $context['event'] ?? null,
            'context' => $context
        ];
        
        $logLine = json_encode($auditEntry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents(AUDIT_LOG_FILE, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    private static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    private static function cleanupOldLogs() {
        $logDir = 'logs/';
        $cutoffTime = time() - (LOG_ROTATION_DAYS * 24 * 3600);
        
        if (is_dir($logDir)) {
            $files = glob($logDir . '*.log');
            foreach ($files as $file) {
                if (filemtime($file) < $cutoffTime) {
                    unlink($file);
                    self::info('Log antigo removido', ['file' => $file]);
                }
            }
        }
    }
    
    public static function getRequestId() {
        return self::$requestId;
    }
    
    public static function getExecutionTime() {
        return round((microtime(true) - self::$startTime) * 1000, 2);
    }
    
    public static function logMetrics($metrics) {
        $metricsEntry = [
            'timestamp' => date('c'),
            'request_id' => self::$requestId,
            'type' => 'metrics',
            'metrics' => $metrics,
            'execution_time' => self::getExecutionTime(),
            'memory_usage' => self::formatBytes(memory_get_usage(true))
        ];
        
        $logLine = json_encode($metricsEntry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents(PERFORMANCE_LOG_FILE, $logLine, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Validador de Dados do Webhook
 */
class WebhookValidator {
    private static $requiredFields = [
        'event',
        'transaction' => ['id', 'status', 'amount'],
        'client' => ['id', 'name', 'email']
    ];
    
    public static function validate($data) {
        if (empty($data)) {
            throw new ValidationException('Dados do webhook estão vazios');
        }
        
        // Validar campos obrigatórios
        self::validateRequiredFields($data, self::$requiredFields);
        
        // Validar formato do evento
        if (!in_array($data['event'], ['TRANSACTION_CREATED', 'TRANSACTION_PAID', 'TRANSACTION_CANCELED', 'TRANSACTION_REFUNDED'])) {
            throw new ValidationException('Evento inválido: ' . $data['event']);
        }
        
        // Validar ID da transação
        if (empty($data['transaction']['id']) || !is_string($data['transaction']['id'])) {
            throw new ValidationException('ID da transação inválido');
        }
        
        // Validar valor
        if (!isset($data['transaction']['amount']) || !is_numeric($data['transaction']['amount']) || $data['transaction']['amount'] <= 0) {
            throw new ValidationException('Valor da transação inválido');
        }
        
        // Validar email do cliente
        if (!filter_var($data['client']['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Email do cliente inválido');
        }
        
        return true;
    }
    
    private static function validateRequiredFields($data, $required, $prefix = '') {
        foreach ($required as $key => $value) {
            $fieldPath = $prefix ? "$prefix.$key" : $key;
            
            if (is_array($value)) {
                if (!isset($data[$key]) || !is_array($data[$key])) {
                    throw new ValidationException("Campo obrigatório não encontrado ou inválido: $fieldPath");
                }
                self::validateRequiredFields($data[$key], $value, $fieldPath);
            } else {
                if (!isset($data[$key]) || empty($data[$key])) {
                    throw new ValidationException("Campo obrigatório não encontrado: $fieldPath");
                }
            }
        }
    }
}

/**
 * Validador de Segurança
 */
class SecurityValidator {
    private static $validTokens = null;
    
    private static function getValidTokens() {
        if (self::$validTokens === null) {
            $securityConfig = getOasyfyConfig('security');
            self::$validTokens = $securityConfig['valid_tokens'];
        }
        return self::$validTokens;
    }
    
    public static function validateToken($token) {
        if (empty($token)) {
            throw new SecurityException('Token de segurança não fornecido');
        }
        
        $validTokens = self::getValidTokens();
        if (!in_array($token, $validTokens)) {
            throw new SecurityException('Token de segurança inválido');
        }
        
        return true;
    }
    
    public static function validateRequest() {
        // Validar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new SecurityException('Método HTTP não permitido');
        }
        
        // Validar Content-Type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') === false) {
            throw new SecurityException('Content-Type inválido');
        }
        
        // Validar tamanho da requisição (máximo 1MB)
        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
        if ($contentLength > 1024 * 1024) {
            throw new SecurityException('Requisição muito grande');
        }
        
        return true;
    }
}

/**
 * Gerenciador de Idempotência Avançado com TTL e Cache
 */
class IdempotencyManager {
    private static $memoryCache = [];
    private static $cacheLoaded = false;
    private static $lastCleanup = 0;
    
    public static function init() {
        self::loadMemoryCache();
        self::cleanupExpiredTransactions();
    }
    
    private static function loadMemoryCache() {
        if (self::$cacheLoaded) {
            return;
        }
        
        if (file_exists(IDEMPOTENCY_CACHE_FILE)) {
            $cacheData = json_decode(file_get_contents(IDEMPOTENCY_CACHE_FILE), true) ?? [];
            self::$memoryCache = $cacheData;
        }
        
        self::$cacheLoaded = true;
        Logger::debug('Cache de idempotência carregado', [
            'cache_size' => count(self::$memoryCache)
        ]);
    }
    
    public static function isTransactionProcessed($transactionId) {
        self::init();
        
        // Verificar cache em memória primeiro
        if (isset(self::$memoryCache[$transactionId])) {
            $entry = self::$memoryCache[$transactionId];
            
            // Verificar se não expirou
            if (self::isEntryExpired($entry)) {
                unset(self::$memoryCache[$transactionId]);
                self::saveMemoryCache();
                Logger::debug('Entrada expirada removida do cache', [
                    'transaction_id' => $transactionId,
                    'expired_at' => $entry['expires_at']
                ]);
                return false;
            }
            
            Logger::debug('Transação encontrada no cache', [
                'transaction_id' => $transactionId,
                'event' => $entry['event'],
                'processed_at' => $entry['processed_at']
            ]);
            return true;
        }
        
        // Verificar arquivo persistente
        if (file_exists(PROCESSED_TRANSACTIONS_FILE)) {
            $processed = json_decode(file_get_contents(PROCESSED_TRANSACTIONS_FILE), true) ?? [];
            
            if (isset($processed[$transactionId])) {
                $entry = $processed[$transactionId];
                
                // Verificar se não expirou
                if (self::isEntryExpired($entry)) {
                    unset($processed[$transactionId]);
                    file_put_contents(PROCESSED_TRANSACTIONS_FILE, json_encode($processed, JSON_PRETTY_PRINT), LOCK_EX);
                    Logger::debug('Entrada expirada removida do arquivo', [
                        'transaction_id' => $transactionId
                    ]);
                    return false;
                }
                
                // Adicionar ao cache em memória
                self::$memoryCache[$transactionId] = $entry;
                self::saveMemoryCache();
                
                Logger::debug('Transação encontrada no arquivo e adicionada ao cache', [
                    'transaction_id' => $transactionId,
                    'event' => $entry['event']
                ]);
                return true;
            }
        }
        
        return false;
    }
    
    public static function markTransactionAsProcessed($transactionId, $event, $processedAt = null) {
        self::init();
        
        $now = date('c');
        $expiresAt = date('c', time() + (IDEMPOTENCY_TTL_HOURS * 3600));
        
        $entry = [
            'event' => $event,
            'processed_at' => $processedAt ?? $now,
            'expires_at' => $expiresAt,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_id' => Logger::getRequestId(),
            'ttl_hours' => IDEMPOTENCY_TTL_HOURS
        ];
        
        // Adicionar ao cache em memória
        self::$memoryCache[$transactionId] = $entry;
        self::saveMemoryCache();
        
        // Adicionar ao arquivo persistente
        $processed = [];
        if (file_exists(PROCESSED_TRANSACTIONS_FILE)) {
            $processed = json_decode(file_get_contents(PROCESSED_TRANSACTIONS_FILE), true) ?? [];
        }
        
        $processed[$transactionId] = $entry;
        file_put_contents(PROCESSED_TRANSACTIONS_FILE, json_encode($processed, JSON_PRETTY_PRINT), LOCK_EX);
        
        Logger::info('Transação marcada como processada', [
            'transaction_id' => $transactionId,
            'event' => $event,
            'expires_at' => $expiresAt,
            'cache_size' => count(self::$memoryCache)
        ]);
    }
    
    private static function isEntryExpired($entry) {
        if (!isset($entry['expires_at'])) {
            return true; // Entrada antiga sem TTL
        }
        
        return strtotime($entry['expires_at']) < time();
    }
    
    private static function saveMemoryCache() {
        file_put_contents(IDEMPOTENCY_CACHE_FILE, json_encode(self::$memoryCache, JSON_PRETTY_PRINT), LOCK_EX);
    }
    
    public static function cleanupExpiredTransactions() {
        $now = time();
        
        // Limpeza apenas a cada hora para evitar overhead
        if ($now - self::$lastCleanup < CACHE_CLEANUP_INTERVAL) {
            return;
        }
        
        self::$lastCleanup = $now;
        
        Logger::info('Iniciando limpeza de transações expiradas');
        
        $cleanedCount = 0;
        
        // Limpar cache em memória
        foreach (self::$memoryCache as $transactionId => $entry) {
            if (self::isEntryExpired($entry)) {
                unset(self::$memoryCache[$transactionId]);
                $cleanedCount++;
            }
        }
        
        // Limpar arquivo persistente
        if (file_exists(PROCESSED_TRANSACTIONS_FILE)) {
            $processed = json_decode(file_get_contents(PROCESSED_TRANSACTIONS_FILE), true) ?? [];
            $originalCount = count($processed);
            
            foreach ($processed as $transactionId => $entry) {
                if (self::isEntryExpired($entry)) {
                    unset($processed[$transactionId]);
                    $cleanedCount++;
                }
            }
            
            if ($cleanedCount > 0) {
                file_put_contents(PROCESSED_TRANSACTIONS_FILE, json_encode($processed, JSON_PRETTY_PRINT), LOCK_EX);
            }
        }
        
        // Salvar cache atualizado
        self::saveMemoryCache();
        
        Logger::info('Limpeza de transações expiradas concluída', [
            'cleaned_count' => $cleanedCount,
            'cache_size' => count(self::$memoryCache)
        ]);
    }
    
    public static function getCacheStats() {
        self::init();
        
        $stats = [
            'cache_size' => count(self::$memoryCache),
            'file_exists' => file_exists(PROCESSED_TRANSACTIONS_FILE),
            'last_cleanup' => date('c', self::$lastCleanup),
            'ttl_hours' => IDEMPOTENCY_TTL_HOURS
        ];
        
        if (file_exists(PROCESSED_TRANSACTIONS_FILE)) {
            $processed = json_decode(file_get_contents(PROCESSED_TRANSACTIONS_FILE), true) ?? [];
            $stats['file_size'] = count($processed);
        }
        
        return $stats;
    }
    
    public static function clearCache() {
        self::$memoryCache = [];
        self::$cacheLoaded = false;
        
        if (file_exists(IDEMPOTENCY_CACHE_FILE)) {
            unlink(IDEMPOTENCY_CACHE_FILE);
        }
        
        Logger::warning('Cache de idempotência limpo manualmente');
    }
    
    public static function getTransactionInfo($transactionId) {
        self::init();
        
        if (isset(self::$memoryCache[$transactionId])) {
            return self::$memoryCache[$transactionId];
        }
        
        if (file_exists(PROCESSED_TRANSACTIONS_FILE)) {
            $processed = json_decode(file_get_contents(PROCESSED_TRANSACTIONS_FILE), true) ?? [];
            if (isset($processed[$transactionId])) {
                return $processed[$transactionId];
            }
        }
        
        return null;
    }
}

/**
 * Gerenciador de Retry
 */
class RetryManager {
    private static $retryFile = 'data/retry_queue.json';
    
    public static function scheduleRetry($transactionId, $data, $attempt = 1) {
        if ($attempt > MAX_RETRY_ATTEMPTS) {
            Logger::critical('Máximo de tentativas de retry atingido', [
                'transaction_id' => $transactionId,
                'attempt' => $attempt
            ]);
            return false;
        }
        
        $retryQueue = [];
        if (file_exists(self::$retryFile)) {
            $retryQueue = json_decode(file_get_contents(self::$retryFile), true) ?? [];
        }
        
        $retryQueue[] = [
            'transaction_id' => $transactionId,
            'data' => $data,
            'attempt' => $attempt,
            'scheduled_at' => date('c', time() + (RETRY_DELAY_SECONDS * $attempt)),
            'created_at' => date('c')
        ];
        
        file_put_contents(self::$retryFile, json_encode($retryQueue, JSON_PRETTY_PRINT), LOCK_EX);
        
        Logger::info('Retry agendado', [
            'transaction_id' => $transactionId,
            'attempt' => $attempt,
            'scheduled_at' => date('c', time() + (RETRY_DELAY_SECONDS * $attempt))
        ]);
        
        return true;
    }
    
    public static function processRetryQueue() {
        if (!file_exists(self::$retryFile)) {
            return;
        }
        
        $retryQueue = json_decode(file_get_contents(self::$retryFile), true) ?? [];
        $now = time();
        $processed = [];
        $remaining = [];
        
        foreach ($retryQueue as $retry) {
            if (strtotime($retry['scheduled_at']) <= $now) {
                try {
                    self::processRetry($retry);
                    $processed[] = $retry['transaction_id'];
                } catch (Exception $e) {
                    Logger::error('Erro no retry', [
                        'transaction_id' => $retry['transaction_id'],
                        'error' => $e->getMessage()
                    ]);
                    
                    // Agendar novo retry se ainda não atingiu o limite
                    if ($retry['attempt'] < MAX_RETRY_ATTEMPTS) {
                        $retry['attempt']++;
                        $retry['scheduled_at'] = date('c', time() + (RETRY_DELAY_SECONDS * $retry['attempt']));
                        $remaining[] = $retry;
                    }
                }
            } else {
                $remaining[] = $retry;
            }
        }
        
        // Salvar fila atualizada
        file_put_contents(self::$retryFile, json_encode($remaining, JSON_PRETTY_PRINT), LOCK_EX);
        
        if (!empty($processed)) {
            Logger::info('Retries processados', ['processed' => $processed]);
        }
    }
    
    private static function processRetry($retry) {
        Logger::info('Processando retry', [
            'transaction_id' => $retry['transaction_id'],
            'attempt' => $retry['attempt']
        ]);
        
        // Simular processamento do retry
        // Aqui você implementaria a lógica de processamento
        processPaymentConfirmed(
            $retry['data']['transaction']['id'],
            $retry['data']['transaction']['amount'],
            $retry['data']['client']['name'],
            $retry['data']['client']['email'],
            $retry['data']
        );
    }
}

/**
 * Processamento Principal do Webhook
 */
try {
    // 1. Inicializar sistemas
    Logger::init();
    IdempotencyManager::init();
    
    Logger::info('Webhook iniciado', [
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 0,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
    
    // 2. Processar fila de retry primeiro
    RetryManager::processRetryQueue();
    
    // 3. Validar requisição HTTP
    SecurityValidator::validateRequest();
    
    // 4. Receber e decodificar dados
        $input = file_get_contents('php://input');
    if (empty($input)) {
        throw new ValidationException('Corpo da requisição está vazio');
    }
    
        $webhookData = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new ValidationException('JSON inválido: ' . json_last_error_msg());
    }
    
    // 5. Log da requisição recebida
    Logger::info('Webhook recebido', [
        'event' => $webhookData['event'] ?? 'unknown',
        'transaction_id' => $webhookData['transaction']['id'] ?? 'unknown',
        'size' => strlen($input),
        'json_size' => count($webhookData)
    ]);
    
    // 6. Validar token de segurança
    if (isset($webhookData['token'])) {
        SecurityValidator::validateToken($webhookData['token']);
        Logger::debug('Token de segurança validado');
    } else {
        Logger::warning('Token de segurança não fornecido');
    }
    
    // 7. Validar dados do webhook
    WebhookValidator::validate($webhookData);
    Logger::debug('Dados do webhook validados');
    
    // 8. Verificar idempotência
    $transactionId = $webhookData['transaction']['id'];
    if (IdempotencyManager::isTransactionProcessed($transactionId)) {
        $transactionInfo = IdempotencyManager::getTransactionInfo($transactionId);
        
        Logger::info('Transação já processada', [
            'transaction_id' => $transactionId,
            'original_event' => $transactionInfo['event'] ?? 'unknown',
            'original_processed_at' => $transactionInfo['processed_at'] ?? 'unknown',
            'expires_at' => $transactionInfo['expires_at'] ?? 'unknown'
        ]);
        
            http_response_code(200);
            echo json_encode([
            'status' => 'already_processed',
            'message' => 'Transação já foi processada anteriormente',
            'transaction_id' => $transactionId,
            'original_processed_at' => $transactionInfo['processed_at'] ?? null,
            'request_id' => Logger::getRequestId()
        ]);
        exit;
    }
    
    // 9. Processar evento
    $event = $webhookData['event'];
    $startProcessing = microtime(true);
    
    Logger::info('Iniciando processamento do evento', [
        'event' => $event,
                'transaction_id' => $transactionId
            ]);
            
    $response = processWebhookEvent($webhookData);
    
    $processingTime = round((microtime(true) - $startProcessing) * 1000, 2);
    
    // 10. Marcar como processado se bem-sucedido
    if ($response['success']) {
        IdempotencyManager::markTransactionAsProcessed($transactionId, $event);
    }
    
    // 11. Log de métricas
    Logger::logMetrics([
        'event' => $event,
        'transaction_id' => $transactionId,
        'processing_time_ms' => $processingTime,
        'success' => $response['success'],
        'cache_stats' => IdempotencyManager::getCacheStats()
    ]);
    
    // 12. Resposta de sucesso
            http_response_code(200);
    echo json_encode(array_merge($response, [
        'request_id' => Logger::getRequestId(),
        'processing_time_ms' => $processingTime,
        'execution_time_ms' => Logger::getExecutionTime()
    ]));
    
} catch (ValidationException $e) {
    Logger::warning('Erro de validação', [
        'error' => $e->getMessage(),
        'transaction_id' => $webhookData['transaction']['id'] ?? 'unknown'
    ]);
    
    http_response_code($e->getHttpStatusCode());
    echo json_encode([
        'status' => 'error',
        'error_code' => $e->getErrorCode(),
        'message' => $e->getMessage()
    ]);
    
} catch (SecurityException $e) {
    Logger::warning('Erro de segurança', [
        'error' => $e->getMessage(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    http_response_code($e->getHttpStatusCode());
            echo json_encode([
        'status' => 'error',
        'error_code' => $e->getErrorCode(),
        'message' => 'Erro de segurança'
    ]);
    
} catch (ProcessingException $e) {
    Logger::error('Erro de processamento', [
        'error' => $e->getMessage(),
        'transaction_id' => $webhookData['transaction']['id'] ?? 'unknown'
    ]);
    
    // Tentar agendar retry
    if (isset($webhookData['transaction']['id'])) {
        RetryManager::scheduleRetry($webhookData['transaction']['id'], $webhookData);
    }
    
    http_response_code($e->getHttpStatusCode());
    echo json_encode([
        'status' => 'error',
        'error_code' => $e->getErrorCode(),
        'message' => 'Erro de processamento - retry agendado'
    ]);
    
    } catch (Exception $e) {
    Logger::critical('Erro inesperado', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
        
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
        'error_code' => 'INTERNAL_ERROR',
            'message' => 'Erro interno do servidor'
        ]);
    }

/**
 * Processa eventos do webhook
 */
function processWebhookEvent($webhookData) {
    $event = $webhookData['event'];
    $transaction = $webhookData['transaction'];
    $client = $webhookData['client'];
    
    switch ($event) {
        case 'TRANSACTION_PAID':
            return processTransactionPaid($transaction, $client, $webhookData);
            
        case 'TRANSACTION_CREATED':
            return processTransactionCreated($transaction, $client, $webhookData);
            
        case 'TRANSACTION_CANCELED':
            return processTransactionCanceled($transaction, $client, $webhookData);
            
        case 'TRANSACTION_REFUNDED':
            return processTransactionRefunded($transaction, $client, $webhookData);
            
        default:
            Logger::warning('Evento não reconhecido', ['event' => $event]);
            return [
                'success' => true,
                'status' => 'ignored',
                'message' => 'Evento não processado',
                'event' => $event
            ];
    }
}

/**
 * Processa pagamento confirmado
 */
function processTransactionPaid($transaction, $client, $fullData) {
    try {
        $transactionId = $transaction['id'];
        $amount = $transaction['amount'];
        $clientName = $client['name'];
        $clientEmail = $client['email'];
        
        Logger::info('Processando pagamento confirmado', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'client_name' => $clientName,
            'client_email' => $clientEmail
        ]);
        
        // Processar pagamento confirmado
        processPaymentConfirmed($transactionId, $amount, $clientName, $clientEmail, $fullData);
        
        return [
            'success' => true,
            'status' => 'success',
            'message' => 'Pagamento processado com sucesso',
            'transaction_id' => $transactionId
        ];
        
    } catch (Exception $e) {
        Logger::error('Erro ao processar pagamento confirmado', [
            'transaction_id' => $transaction['id'],
            'error' => $e->getMessage()
        ]);
        
        throw new ProcessingException('Falha ao processar pagamento: ' . $e->getMessage(), $e);
    }
}

/**
 * Processa transação criada
 */
function processTransactionCreated($transaction, $client, $fullData) {
    Logger::info('Transação criada', [
        'transaction_id' => $transaction['id'],
        'amount' => $transaction['amount']
    ]);
    
    return [
        'success' => true,
        'status' => 'created',
        'message' => 'Transação criada',
        'transaction_id' => $transaction['id']
    ];
}

/**
 * Processa transação cancelada
 */
function processTransactionCanceled($transaction, $client, $fullData) {
    Logger::info('Transação cancelada', [
        'transaction_id' => $transaction['id'],
        'amount' => $transaction['amount']
    ]);
    
    // Aqui você pode implementar lógica para cancelar pedidos, etc.
    
    return [
        'success' => true,
        'status' => 'canceled',
        'message' => 'Transação cancelada',
        'transaction_id' => $transaction['id']
    ];
}

/**
 * Processa transação estornada
 */
function processTransactionRefunded($transaction, $client, $fullData) {
    Logger::info('Transação estornada', [
        'transaction_id' => $transaction['id'],
        'amount' => $transaction['amount']
    ]);
    
    // Aqui você pode implementar lógica para estornos, etc.
    
    return [
        'success' => true,
        'status' => 'refunded',
        'message' => 'Transação estornada',
        'transaction_id' => $transaction['id']
    ];
}

/**
 * Processa o pagamento confirmado com tratamento de erros
 */
function processPaymentConfirmed($transactionId, $amount, $clientName, $clientEmail, $fullData) {
    try {
        Logger::info('Iniciando processamento do pagamento', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'client_name' => $clientName,
            'client_email' => $clientEmail
        ]);
    
    // 1. Salvar no banco de dados
        try {
            savePaymentToDatabase($transactionId, $amount, $clientName, $clientEmail);
            Logger::info('Pagamento salvo no banco de dados', ['transaction_id' => $transactionId]);
        } catch (Exception $e) {
            Logger::error('Erro ao salvar no banco de dados', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            throw new ProcessingException('Falha ao salvar no banco de dados', $e);
        }
    
    // 2. Enviar email de confirmação
        try {
            sendConfirmationEmail($clientEmail, $clientName, $amount);
            Logger::info('Email de confirmação enviado', [
                'transaction_id' => $transactionId,
                'client_email' => $clientEmail
            ]);
        } catch (Exception $e) {
            Logger::warning('Erro ao enviar email de confirmação', [
                'transaction_id' => $transactionId,
                'client_email' => $clientEmail,
                'error' => $e->getMessage()
            ]);
            // Não falha o processamento por erro de email
        }
    
    // 3. Liberar acesso ao conteúdo
        try {
            grantAccessToContent($clientEmail, $fullData);
            Logger::info('Acesso liberado para o cliente', [
                'transaction_id' => $transactionId,
                'client_email' => $clientEmail
            ]);
        } catch (Exception $e) {
            Logger::error('Erro ao liberar acesso', [
                'transaction_id' => $transactionId,
                'client_email' => $clientEmail,
                'error' => $e->getMessage()
            ]);
            throw new ProcessingException('Falha ao liberar acesso ao conteúdo', $e);
        }
    
    // 4. Atualizar status do pedido
        try {
            updateOrderStatus($transactionId, 'paid');
            Logger::info('Status do pedido atualizado', ['transaction_id' => $transactionId]);
        } catch (Exception $e) {
            Logger::error('Erro ao atualizar status do pedido', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            throw new ProcessingException('Falha ao atualizar status do pedido', $e);
        }
    
    // 5. Notificar sistemas internos
        try {
            notifyInternalSystems($transactionId, $amount);
            Logger::info('Sistemas internos notificados', ['transaction_id' => $transactionId]);
        } catch (Exception $e) {
            Logger::warning('Erro ao notificar sistemas internos', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            // Não falha o processamento por erro de notificação
        }
        
        // 6. Salvar dados de backup (para demonstração)
        try {
            savePaymentBackup($transactionId, $amount, $clientName, $clientEmail, $fullData);
            Logger::info('Backup do pagamento salvo', ['transaction_id' => $transactionId]);
        } catch (Exception $e) {
            Logger::warning('Erro ao salvar backup', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
        }
        
        Logger::info('Pagamento processado com sucesso', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'client_name' => $clientName
        ]);
        
    } catch (ProcessingException $e) {
        // Re-lançar exceções de processamento
        throw $e;
    } catch (Exception $e) {
        Logger::error('Erro inesperado no processamento do pagamento', [
            'transaction_id' => $transactionId,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        throw new ProcessingException('Erro inesperado no processamento: ' . $e->getMessage(), $e);
    }
}

/**
 * Salva backup do pagamento (para demonstração)
 */
function savePaymentBackup($transactionId, $amount, $clientName, $clientEmail, $fullData) {
    $paymentData = [
        'transaction_id' => $transactionId,
        'amount' => $amount,
        'client_name' => $clientName,
        'client_email' => $clientEmail,
        'confirmed_at' => date('c'),
        'full_data' => $fullData
    ];
    
    $paymentsFile = 'data/confirmed_payments.json';
    $existingPayments = [];
    
    if (file_exists($paymentsFile)) {
        $existingPayments = json_decode(file_get_contents($paymentsFile), true) ?? [];
    }
    
    $existingPayments[] = $paymentData;
    
    if (!file_put_contents($paymentsFile, json_encode($existingPayments, JSON_PRETTY_PRINT), LOCK_EX)) {
        throw new Exception('Falha ao salvar arquivo de backup');
    }
}

/**
 * Salva pagamento no banco de dados com tratamento de erros
 */
function savePaymentToDatabase($transactionId, $amount, $clientName, $clientEmail) {
    try {
        // Exemplo com MySQL - descomente e configure conforme necessário
    /*
        $pdo = new PDO('mysql:host=localhost;dbname=seu_banco', 'usuario', 'senha', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    
    $stmt = $pdo->prepare("
        INSERT INTO payments (transaction_id, amount, client_name, client_email, status, created_at) 
        VALUES (?, ?, ?, ?, 'confirmed', NOW())
    ");
    
    $stmt->execute([$transactionId, $amount, $clientName, $clientEmail]);
    */
        
        // Simulação para demonstração
        Logger::info('Simulando salvamento no banco de dados', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'client_name' => $clientName,
            'client_email' => $clientEmail
        ]);
        
        // Simular possível erro ocasional
        if (rand(1, 100) <= 5) { // 5% de chance de erro
            throw new Exception('Erro simulado de conexão com banco de dados');
        }
        
    } catch (PDOException $e) {
        Logger::error('Erro de banco de dados', [
            'transaction_id' => $transactionId,
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]);
        throw new Exception('Falha na conexão com banco de dados: ' . $e->getMessage());
    } catch (Exception $e) {
        Logger::error('Erro ao salvar no banco de dados', [
            'transaction_id' => $transactionId,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

/**
 * Envia email de confirmação com tratamento de erros
 */
function sendConfirmationEmail($clientEmail, $clientName, $amount) {
    try {
        // Exemplo com PHPMailer - descomente e configure conforme necessário
    /*
    $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'seu_email@gmail.com';
        $mail->Password = 'sua_senha_app';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
    
    $mail->setFrom('noreply@seusite.com', 'Seu Site');
    $mail->addAddress($clientEmail, $clientName);
    $mail->Subject = 'Pagamento Confirmado - Assinatura Privacy';
    $mail->Body = "
        Olá $clientName,
        
        Seu pagamento de R$ $amount foi confirmado com sucesso!
        
        Agora você tem acesso completo ao conteúdo exclusivo.
        
        Obrigado pela sua assinatura!
    ";
    
    $mail->send();
    */
        
        // Simulação para demonstração
        Logger::info('Simulando envio de email', [
            'client_email' => $clientEmail,
            'client_name' => $clientName,
            'amount' => $amount
        ]);
        
        // Simular possível erro ocasional
        if (rand(1, 100) <= 10) { // 10% de chance de erro
            throw new Exception('Erro simulado de envio de email');
        }
        
    } catch (Exception $e) {
        Logger::error('Erro ao enviar email', [
            'client_email' => $clientEmail,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

/**
 * Libera acesso ao conteúdo com tratamento de erros
 */
function grantAccessToContent($clientEmail, $fullData) {
    try {
        // Implementar lógica de liberação de acesso
        // Ex: criar usuário, ativar assinatura, etc.
        
        Logger::info('Simulando liberação de acesso', [
            'client_email' => $clientEmail,
            'transaction_id' => $fullData['transaction']['id'] ?? 'unknown'
        ]);
        
        // Simular possível erro ocasional
        if (rand(1, 100) <= 3) { // 3% de chance de erro
            throw new Exception('Erro simulado na liberação de acesso');
        }
        
    } catch (Exception $e) {
        Logger::error('Erro ao liberar acesso', [
            'client_email' => $clientEmail,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

/**
 * Atualiza status do pedido com tratamento de erros
 */
function updateOrderStatus($transactionId, $status) {
    try {
        // Implementar lógica de atualização de status
        // Ex: atualizar tabela de pedidos
        
        Logger::info('Simulando atualização de status', [
            'transaction_id' => $transactionId,
            'status' => $status
        ]);
        
        // Simular possível erro ocasional
        if (rand(1, 100) <= 2) { // 2% de chance de erro
            throw new Exception('Erro simulado na atualização de status');
        }
        
    } catch (Exception $e) {
        Logger::error('Erro ao atualizar status', [
            'transaction_id' => $transactionId,
            'status' => $status,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

/**
 * Notifica sistemas internos com tratamento de erros
 */
function notifyInternalSystems($transactionId, $amount) {
    try {
        // Implementar notificações para sistemas internos
        // Ex: webhooks internos, APIs, etc.
        
        Logger::info('Simulando notificação de sistemas internos', [
            'transaction_id' => $transactionId,
            'amount' => $amount
        ]);
        
        // Simular possível erro ocasional
        if (rand(1, 100) <= 15) { // 15% de chance de erro
            throw new Exception('Erro simulado na notificação de sistemas internos');
        }
        
    } catch (Exception $e) {
        Logger::error('Erro ao notificar sistemas internos', [
            'transaction_id' => $transactionId,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}
?>
