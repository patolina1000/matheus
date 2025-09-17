<?php
/**
 * Sistema de Logs Simples e Organizado
 * Solução rápida para organizar logs bagunçados
 */

class SimpleLogger {
    private static $logDir = 'logs/';
    private static $logFile = '';
    private static $requestId = '';
    
    public static function init() {
        // Criar diretório de logs se não existir
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        
        // Gerar ID único para a requisição
        self::$requestId = uniqid('req_', true);
        
        // Definir arquivo de log do dia
        self::$logFile = self::$logDir . 'app_' . date('Y-m-d') . '.log';
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
        if (empty(self::$requestId)) {
            self::init();
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Formatar dados extras se existirem
        $extraData = '';
        if (!empty($data)) {
            $extraData = ' | ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        
        // Linha de log formatada
        $logLine = "[{$timestamp}] [{$level}] [{$ip}] [{$self::$requestId}] {$message}{$extraData}\n";
        
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
        $files = glob(self::$logDir . 'app_*.log');
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
        $files = glob(self::$logDir . 'app_*.log');
        $totalFiles = count($files);
        $totalSize = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
        
        return [
            'total_files' => $totalFiles,
            'total_size' => self::formatBytes($totalSize),
            'log_directory' => self::$logDir
        ];
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
}

// Inicializar automaticamente
SimpleLogger::init();
?>
