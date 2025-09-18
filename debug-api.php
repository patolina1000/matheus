<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'simple-logger.php';

$logDir = SimpleLogger::getLogDir();
$logFiles = is_dir($logDir) ? glob($logDir . '*.log') : [];
$logFiles = $logFiles ?: [];

$response = [
    'success' => true,
    'message' => 'Debug API funcionando',
    'timestamp' => date('Y-m-d H:i:s'),
    'log_dir' => $logDir,
    'log_files' => $logFiles,
    'log_files_count' => count($logFiles)
];

// Verificar se o diretório existe
$response['log_dir_exists'] = is_dir($logDir);
$response['log_dir_writable'] = is_writable($logDir);

// Verificar arquivo específico
$todayLog = $logDir . 'app_' . date('Y-m-d') . '.log';
$response['today_log_file'] = $todayLog;
$response['today_log_exists'] = file_exists($todayLog);

if (file_exists($todayLog)) {
    $response['today_log_size'] = filesize($todayLog);
    $lines = file($todayLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $response['today_log_lines'] = count($lines);
    $response['today_log_last_3_lines'] = array_slice($lines, -3);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
