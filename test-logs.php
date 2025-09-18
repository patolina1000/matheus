<?php
/**
 * Teste simples para verificar logs
 */

// Incluir sistema de logs simples
require_once 'simple-logger.php';

// Headers para CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Testar se os logs estão sendo gerados
SimpleLogger::info('Teste de log - API funcionando');
SimpleLogger::pix('TEST', 'Teste de PIX log');
SimpleLogger::webhook('TEST', 'Teste de webhook log');

// Verificar arquivos de log
$logDir = SimpleLogger::getLogDir();
$logFiles = is_dir($logDir) ? glob($logDir . '*.log') : [];
$logFiles = $logFiles ?: [];

$appLogFile = $logDir . 'app_' . date('Y-m-d') . '.log';

$response = [
    'success' => true,
    'message' => 'Teste de logs executado',
    'log_dir' => $logDir,
    'log_files' => $logFiles,
    'log_files_count' => count($logFiles),
    'current_date' => date('Y-m-d'),
    'app_log_file' => $appLogFile,
    'app_log_exists' => file_exists($appLogFile)
];

// Se o arquivo de log do dia existe, mostrar algumas linhas
if (file_exists($appLogFile)) {
    $lines = file($appLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $response['app_log_lines_count'] = count($lines);
    $response['app_log_last_5_lines'] = array_slice($lines, -5);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
