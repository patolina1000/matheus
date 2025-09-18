<?php
/**
 * Monitor de Webhook PIX Oasy.fy
 *
 * Script para monitorar logs, estatísticas e status do webhook
 */

// Configurações
require_once 'simple-logger.php';

$logDir = SimpleLogger::getLogDir();
$dataDir = 'data/';
$refreshInterval = 5; // segundos

// Funções de monitoramento
function getLogStats() {
    global $logDir;
    
    $stats = [
        'webhook_logs' => 0,
        'error_logs' => 0,
        'performance_logs' => 0,
        'audit_logs' => 0,
        'total_size' => 0
    ];
    
    $logFiles = [
        'webhook_logs' => 'webhook_' . date('Y-m') . '.log',
        'error_logs' => 'errors_' . date('Y-m') . '.log',
        'performance_logs' => 'performance_' . date('Y-m') . '.log',
        'audit_logs' => 'audit_' . date('Y-m') . '.log'
    ];
    
    foreach ($logFiles as $type => $filename) {
        $filepath = $logDir . $filename;
        if (file_exists($filepath)) {
            $stats[$type] = count(file($filepath));
            $stats['total_size'] += filesize($filepath);
        }
    }
    
    return $stats;
}

function getRecentLogs($type = 'webhook', $lines = 10) {
    global $logDir;
    
    $filename = $type . '_' . date('Y-m') . '.log';
    $filepath = $logDir . $filename;
    
    if (!file_exists($filepath)) {
        return [];
    }
    
    $logs = file($filepath);
    $recentLogs = array_slice($logs, -$lines);
    
    $parsedLogs = [];
    foreach ($recentLogs as $log) {
        $parsed = json_decode(trim($log), true);
        if ($parsed) {
            $parsedLogs[] = $parsed;
        }
    }
    
    return $parsedLogs;
}

function getCacheStats() {
    global $dataDir;
    
    $stats = [
        'processed_transactions' => 0,
        'cache_size' => 0,
        'retry_queue' => 0,
        'confirmed_payments' => 0
    ];
    
    // Processed transactions
    $file = $dataDir . 'processed_transactions.json';
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?? [];
        $stats['processed_transactions'] = count($data);
    }
    
    // Cache
    $file = $dataDir . 'idempotency_cache.json';
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?? [];
        $stats['cache_size'] = count($data);
    }
    
    // Retry queue
    $file = $dataDir . 'retry_queue.json';
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?? [];
        $stats['retry_queue'] = count($data);
    }
    
    // Confirmed payments
    $file = $dataDir . 'confirmed_payments.json';
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?? [];
        $stats['confirmed_payments'] = count($data);
    }
    
    return $stats;
}

function getSystemInfo() {
    return [
        'php_version' => PHP_VERSION,
        'memory_usage' => memory_get_usage(true),
        'memory_peak' => memory_get_peak_usage(true),
        'server_time' => date('Y-m-d H:i:s'),
        'uptime' => getUptime()
    ];
}

function getUptime() {
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        return implode(', ', $load);
    }
    return 'N/A';
}

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

function getErrorSummary() {
    $errorLogs = getRecentLogs('errors', 100);
    $summary = [
        'total_errors' => count($errorLogs),
        'error_types' => [],
        'recent_errors' => []
    ];
    
    foreach ($errorLogs as $log) {
        $level = $log['level'] ?? 'UNKNOWN';
        $summary['error_types'][$level] = ($summary['error_types'][$level] ?? 0) + 1;
    }
    
    $summary['recent_errors'] = array_slice($errorLogs, -5);
    
    return $summary;
}

function getPerformanceMetrics() {
    $perfLogs = getRecentLogs('performance', 50);
    $metrics = [
        'avg_processing_time' => 0,
        'max_processing_time' => 0,
        'min_processing_time' => PHP_INT_MAX,
        'total_requests' => count($perfLogs)
    ];
    
    $totalTime = 0;
    $validTimes = 0;
    
    foreach ($perfLogs as $log) {
        if (isset($log['context']['processing_time_ms'])) {
            $time = (float)$log['context']['processing_time_ms'];
            $totalTime += $time;
            $validTimes++;
            
            $metrics['max_processing_time'] = max($metrics['max_processing_time'], $time);
            $metrics['min_processing_time'] = min($metrics['min_processing_time'], $time);
        }
    }
    
    if ($validTimes > 0) {
        $metrics['avg_processing_time'] = round($totalTime / $validTimes, 2);
    }
    
    if ($metrics['min_processing_time'] === PHP_INT_MAX) {
        $metrics['min_processing_time'] = 0;
    }
    
    return $metrics;
}

// Interface de linha de comando
if (php_sapi_name() === 'cli') {
    echo "🔍 MONITOR DO WEBHOOK PIX OASY.FY\n";
    echo str_repeat("=", 50) . "\n";
    
    while (true) {
        // Limpar tela
        system('clear');
        
        echo "🔍 MONITOR DO WEBHOOK PIX OASY.FY\n";
        echo "Atualizado em: " . date('Y-m-d H:i:s') . "\n";
        echo str_repeat("=", 50) . "\n\n";
        
        // Estatísticas de logs
        $logStats = getLogStats();
        echo "📊 ESTATÍSTICAS DE LOGS:\n";
        echo "  Webhook Logs: " . $logStats['webhook_logs'] . " entradas\n";
        echo "  Error Logs: " . $logStats['error_logs'] . " entradas\n";
        echo "  Performance Logs: " . $logStats['performance_logs'] . " entradas\n";
        echo "  Audit Logs: " . $logStats['audit_logs'] . " entradas\n";
        echo "  Tamanho Total: " . formatBytes($logStats['total_size']) . "\n\n";
        
        // Estatísticas de cache
        $cacheStats = getCacheStats();
        echo "💾 ESTATÍSTICAS DE CACHE:\n";
        echo "  Transações Processadas: " . $cacheStats['processed_transactions'] . "\n";
        echo "  Cache em Memória: " . $cacheStats['cache_size'] . " entradas\n";
        echo "  Fila de Retry: " . $cacheStats['retry_queue'] . " itens\n";
        echo "  Pagamentos Confirmados: " . $cacheStats['confirmed_payments'] . "\n\n";
        
        // Métricas de performance
        $perfMetrics = getPerformanceMetrics();
        echo "⚡ MÉTRICAS DE PERFORMANCE:\n";
        echo "  Total de Requisições: " . $perfMetrics['total_requests'] . "\n";
        echo "  Tempo Médio: " . $perfMetrics['avg_processing_time'] . "ms\n";
        echo "  Tempo Máximo: " . $perfMetrics['max_processing_time'] . "ms\n";
        echo "  Tempo Mínimo: " . $perfMetrics['min_processing_time'] . "ms\n\n";
        
        // Resumo de erros
        $errorSummary = getErrorSummary();
        echo "❌ RESUMO DE ERROS:\n";
        echo "  Total de Erros: " . $errorSummary['total_errors'] . "\n";
        foreach ($errorSummary['error_types'] as $type => $count) {
            echo "  $type: $count\n";
        }
        echo "\n";
        
        // Logs recentes
        $recentLogs = getRecentLogs('webhook', 5);
        echo "📝 LOGS RECENTES:\n";
        foreach ($recentLogs as $log) {
            $time = date('H:i:s', strtotime($log['timestamp']));
            $level = $log['level'];
            $message = $log['message'];
            $transactionId = $log['context']['transaction_id'] ?? 'N/A';
            echo "  [$time] [$level] $message (ID: $transactionId)\n";
        }
        echo "\n";
        
        // Informações do sistema
        $systemInfo = getSystemInfo();
        echo "🖥️  INFORMAÇÕES DO SISTEMA:\n";
        echo "  PHP Version: " . $systemInfo['php_version'] . "\n";
        echo "  Memory Usage: " . formatBytes($systemInfo['memory_usage']) . "\n";
        echo "  Memory Peak: " . formatBytes($systemInfo['memory_peak']) . "\n";
        echo "  Server Time: " . $systemInfo['server_time'] . "\n";
        echo "  Load Average: " . $systemInfo['uptime'] . "\n\n";
        
        echo "Pressione Ctrl+C para sair...\n";
        sleep($refreshInterval);
    }
} else {
    // Interface web
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Monitor Webhook PIX Oasy.fy</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { max-width: 1200px; margin: 0 auto; }
            .card { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
            .stat-item { text-align: center; padding: 15px; background: #f8f9fa; border-radius: 5px; }
            .stat-value { font-size: 24px; font-weight: bold; color: #007bff; }
            .stat-label { color: #666; margin-top: 5px; }
            .log-entry { padding: 10px; margin: 5px 0; background: #f8f9fa; border-left: 4px solid #007bff; border-radius: 3px; }
            .error { border-left-color: #dc3545; }
            .warning { border-left-color: #ffc107; }
            .success { border-left-color: #28a745; }
            .refresh-btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
            .refresh-btn:hover { background: #0056b3; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔍 Monitor Webhook PIX Oasy.fy</h1>
            <p>Atualizado em: <?php echo date('Y-m-d H:i:s'); ?></p>
            
            <button class="refresh-btn" onclick="location.reload()">🔄 Atualizar</button>
            
            <div class="card">
                <h2>📊 Estatísticas de Logs</h2>
                <div class="stats">
                    <?php
                    $logStats = getLogStats();
                    $stats = [
                        'Webhook Logs' => $logStats['webhook_logs'],
                        'Error Logs' => $logStats['error_logs'],
                        'Performance Logs' => $logStats['performance_logs'],
                        'Audit Logs' => $logStats['audit_logs']
                    ];
                    
                    foreach ($stats as $label => $value) {
                        echo "<div class='stat-item'>";
                        echo "<div class='stat-value'>$value</div>";
                        echo "<div class='stat-label'>$label</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="card">
                <h2>💾 Estatísticas de Cache</h2>
                <div class="stats">
                    <?php
                    $cacheStats = getCacheStats();
                    $stats = [
                        'Transações Processadas' => $cacheStats['processed_transactions'],
                        'Cache em Memória' => $cacheStats['cache_size'],
                        'Fila de Retry' => $cacheStats['retry_queue'],
                        'Pagamentos Confirmados' => $cacheStats['confirmed_payments']
                    ];
                    
                    foreach ($stats as $label => $value) {
                        echo "<div class='stat-item'>";
                        echo "<div class='stat-value'>$value</div>";
                        echo "<div class='stat-label'>$label</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="card">
                <h2>⚡ Métricas de Performance</h2>
                <div class="stats">
                    <?php
                    $perfMetrics = getPerformanceMetrics();
                    $stats = [
                        'Total Requisições' => $perfMetrics['total_requests'],
                        'Tempo Médio' => $perfMetrics['avg_processing_time'] . 'ms',
                        'Tempo Máximo' => $perfMetrics['max_processing_time'] . 'ms',
                        'Tempo Mínimo' => $perfMetrics['min_processing_time'] . 'ms'
                    ];
                    
                    foreach ($stats as $label => $value) {
                        echo "<div class='stat-item'>";
                        echo "<div class='stat-value'>$value</div>";
                        echo "<div class='stat-label'>$label</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="card">
                <h2>📝 Logs Recentes</h2>
                <?php
                $recentLogs = getRecentLogs('webhook', 10);
                foreach ($recentLogs as $log) {
                    $time = date('H:i:s', strtotime($log['timestamp']));
                    $level = $log['level'];
                    $message = $log['message'];
                    $transactionId = $log['context']['transaction_id'] ?? 'N/A';
                    
                    $class = 'log-entry';
                    if ($level === 'ERROR' || $level === 'CRITICAL') $class .= ' error';
                    elseif ($level === 'WARNING') $class .= ' warning';
                    elseif ($level === 'INFO') $class .= ' success';
                    
                    echo "<div class='$class'>";
                    echo "<strong>[$time] [$level]</strong> $message<br>";
                    echo "<small>Transaction ID: $transactionId</small>";
                    echo "</div>";
                }
                ?>
            </div>
            
            <div class="card">
                <h2>❌ Resumo de Erros</h2>
                <?php
                $errorSummary = getErrorSummary();
                echo "<p><strong>Total de Erros:</strong> " . $errorSummary['total_errors'] . "</p>";
                
                if (!empty($errorSummary['error_types'])) {
                    echo "<h3>Tipos de Erro:</h3>";
                    foreach ($errorSummary['error_types'] as $type => $count) {
                        echo "<p><strong>$type:</strong> $count ocorrências</p>";
                    }
                }
                ?>
            </div>
        </div>
        
        <script>
            // Auto-refresh a cada 30 segundos
            setTimeout(function() {
                location.reload();
            }, 30000);
        </script>
    </body>
    </html>
    <?php
}
?>
