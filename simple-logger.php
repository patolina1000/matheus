<?php
/**
 * Sistema de Logs Simples e Organizado
 * Solução rápida para organizar logs bagunçados
 */

class SimpleLogger {
    private static $defaultLogDir = 'logs/';
    private static $logDir = '';
    private static $logFile = '';
    private static $requestId = '';

    public static function init() {
        if (!empty(self::$logFile) && !empty(self::$logDir)) {
            return true;
        }

        $candidates = self::getCandidateDirectories();

        foreach ($candidates as $candidate) {
            $normalizedDir = self::normalizeLogDir($candidate);

            if (self::ensureLogDirectory($normalizedDir)) {
                self::$logDir = $normalizedDir;
                break;
            }
        }

        if (empty(self::$logDir)) {
            $fallbackDir = self::normalizeLogDir(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'matheus-logs');

            if (self::ensureLogDirectory($fallbackDir)) {
                self::reportInitIssue('Usando diretório alternativo para logs: ' . $fallbackDir);
                self::$logDir = $fallbackDir;
            } else {
                self::reportInitIssue('Falha ao criar diretório de fallback para logs: ' . $fallbackDir);
                return false;
            }
        }

        // Gerar ID único para a requisição
        self::$requestId = uniqid('req_', true);

        // Definir arquivo de log do dia
        self::$logFile = self::$logDir . 'app_' . date('Y-m-d') . '.log';

        return true;
    }
    
    /**
     * Log de informação geral
     */
    public static function info($message, $data = []) {
        self::log('INFO', $message, $data);
    }
    
    /**
     * Log de erro
     */
    public static function error($message, $data = []) {
        self::log('ERROR', $message, $data);
    }
    
    /**
     * Log de aviso
     */
    public static function warning($message, $data = []) {
        self::log('WARNING', $message, $data);
    }
    
    /**
     * Log de debug
     */
    public static function debug($message, $data = []) {
        self::log('DEBUG', $message, $data);
    }
    
    /**
     * Log de sucesso
     */
    public static function success($message, $data = []) {
        self::log('SUCCESS', $message, $data);
    }
    
    /**
     * Log de requisição HTTP
     */
    public static function request($method, $url, $data = []) {
        $message = "{$method} {$url}";
        self::log('REQUEST', $message, $data);
    }
    
    /**
     * Log de resposta HTTP
     */
    public static function response($status, $message, $data = []) {
        $logMessage = "Status {$status}: {$message}";
        self::log('RESPONSE', $logMessage, $data);
    }
    
    /**
     * Log de pagamento PIX
     */
    public static function pix($action, $message, $data = []) {
        $logMessage = "PIX {$action}: {$message}";
        self::log('PIX', $logMessage, $data);
    }
    
    /**
     * Log de webhook
     */
    public static function webhook($event, $message, $data = []) {
        $logMessage = "WEBHOOK {$event}: {$message}";
        self::log('WEBHOOK', $logMessage, $data);
    }
    
    /**
     * Método principal de log
     */
    private static function log($level, $message, $data = []) {
        $extraData = '';
        if (!empty($data)) {
            $extraData = ' | ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        if (!self::init()) {
            error_log('SIMPLE_LOGGER: Falha ao inicializar diretório de logs. [' . $level . '] ' . $message . $extraData);
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Linha de log formatada (sem interpolação incorreta)
        $logLine = '[' . $timestamp . '] [' . $level . '] [' . $ip . '] [' . self::$requestId . '] ' . $message . $extraData . "\n";

        // Escrever no arquivo
        file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Se for erro crítico, também logar no error_log do PHP
        if ($level === 'ERROR') {
            error_log("SIMPLE_LOGGER: {$message}" . $extraData);
        }
    }
    
    /**
     * Limpar logs antigos (manter apenas últimos 7 dias)
     */
    public static function cleanup() {
        if (!self::init()) {
            return;
        }

        $files = glob(self::$logDir . 'app_*.log') ?: [];
        $cutoff = time() - (7 * 24 * 3600); // 7 dias atrás

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                self::info("Log antigo removido: " . basename($file));
            }
        }
    }

    /**
     * Obter estatísticas dos logs
     */
    public static function getStats() {
        $logDir = self::getLogDir();

        if (!is_dir($logDir)) {
            return [
                'total_files' => 0,
                'total_size' => self::formatBytes(0),
                'log_directory' => $logDir
            ];
        }

        $files = glob($logDir . 'app_*.log') ?: [];
        $totalFiles = count($files);
        $totalSize = 0;

        foreach ($files as $file) {
            $totalSize += filesize($file);
        }

        return [
            'total_files' => $totalFiles,
            'total_size' => self::formatBytes($totalSize),
            'log_directory' => $logDir
        ];
    }

    /**
     * Retorna o diretório atual de logs (já normalizado)
     */
    public static function getLogDir() {
        if (self::init()) {
            return self::$logDir;
        }

        return self::normalizeLogDir(self::$defaultLogDir);
    }
    
    /**
     * Formatar bytes em formato legível
     */
    private static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Gera lista de diretórios candidatos a serem utilizados para logs
     */
    private static function getCandidateDirectories() {
        $candidates = [];

        $envSimpleLoggerDir = getenv('SIMPLE_LOGGER_DIR');
        if ($envSimpleLoggerDir !== false && $envSimpleLoggerDir !== '') {
            $candidates[] = $envSimpleLoggerDir;
        }

        $envOasyfyLogDir = getenv('OASYFY_LOG_DIR');
        if ($envOasyfyLogDir !== false && $envOasyfyLogDir !== '') {
            $candidates[] = $envOasyfyLogDir;
        }

        if (defined('OASYFY_LOG_CONFIG')) {
            $logConfig = OASYFY_LOG_CONFIG;
            if (is_array($logConfig) && !empty($logConfig['log_dir'])) {
                $candidates[] = $logConfig['log_dir'];
            }
        }

        $candidates[] = self::$defaultLogDir;

        $candidates = array_filter($candidates, function ($dir) {
            return is_string($dir) && trim($dir) !== '';
        });

        return array_values(array_unique($candidates));
    }

    /**
     * Normaliza o caminho garantindo separador final
     */
    private static function normalizeLogDir($dir) {
        if (!is_string($dir) || $dir === '') {
            return '';
        }

        $normalized = rtrim($dir, "/\\");

        if ($normalized === '') {
            return DIRECTORY_SEPARATOR;
        }

        return $normalized . DIRECTORY_SEPARATOR;
    }

    /**
     * Garante que o diretório existe e é gravável
     */
    private static function ensureLogDirectory($dir) {
        if ($dir === '') {
            return false;
        }

        if (!is_dir($dir)) {
            if (file_exists($dir) && !is_dir($dir)) {
                self::reportInitIssue('Caminho de logs existe, porém não é diretório: ' . $dir);
                return false;
            }

            if (!@mkdir($dir, 0755, true)) {
                self::reportInitIssue('Não foi possível criar diretório de logs: ' . $dir);
                return false;
            }
        }

        if (!is_writable($dir)) {
            self::reportInitIssue('Diretório de logs não é gravável: ' . $dir);
            return false;
        }

        return true;
    }

    /**
     * Registra mensagens de falha na inicialização
     */
    private static function reportInitIssue($message) {
        error_log('SIMPLE_LOGGER_INIT: ' . $message);
    }
}

// Inicializar automaticamente
SimpleLogger::init();
?>
