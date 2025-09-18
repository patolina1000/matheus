<?php
/**
 * Visualizador de Logs Simples
 * Interface web para visualizar e filtrar logs organizados
 */

require_once 'simple-logger.php';

// Configurações
$logDir = SimpleLogger::getLogDir();
$maxLines = 1000; // Máximo de linhas a exibir
$defaultFilter = $_GET['filter'] ?? 'all';
$defaultDate = $_GET['date'] ?? date('Y-m-d');

// Função para ler logs com filtro
function readLogs($date, $filter = 'all', $maxLines = 1000) {
    global $logDir;
    
    $logFile = $logDir . 'app_' . $date . '.log';
    
    if (!file_exists($logFile)) {
        return ['logs' => [], 'total' => 0];
    }
    
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $totalLines = count($lines);
    
    // Aplicar filtro se não for 'all'
    if ($filter !== 'all') {
        $lines = array_filter($lines, function($line) use ($filter) {
            return strpos($line, "[{$filter}]") !== false;
        });
    }
    
    // Pegar apenas as últimas linhas
    $lines = array_slice($lines, -$maxLines);
    
    return [
        'logs' => $lines,
        'total' => $totalLines,
        'filtered' => count($lines)
    ];
}

// Função para obter datas disponíveis
function getAvailableDates() {
    global $logDir;
    $files = glob($logDir . 'app_*.log') ?: [];
    $dates = [];
    
    foreach ($files as $file) {
        $filename = basename($file);
        if (preg_match('/app_(\d{4}-\d{2}-\d{2})\.log/', $filename, $matches)) {
            $dates[] = $matches[1];
        }
    }
    
    rsort($dates); // Mais recente primeiro
    return $dates;
}

// Processar requisição
$availableDates = getAvailableDates();
$logData = readLogs($defaultDate, $defaultFilter, $maxLines);

// Estatísticas
$stats = SimpleLogger::getStats();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizador de Logs - Sistema PIX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .log-entry {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin-bottom: 2px;
            padding: 4px 8px;
            border-radius: 3px;
        }
        
        .log-info { background-color: #e3f2fd; }
        .log-success { background-color: #e8f5e8; }
        .log-warning { background-color: #fff3cd; }
        .log-error { background-color: #f8d7da; }
        .log-debug { background-color: #f8f9fa; }
        .log-request { background-color: #e1f5fe; }
        .log-response { background-color: #f3e5f5; }
        .log-pix { background-color: #e8f5e8; }
        .log-webhook { background-color: #fff8e1; }
        
        .log-timestamp { color: #666; font-weight: bold; }
        .log-level { font-weight: bold; }
        .log-ip { color: #007bff; }
        .log-request-id { color: #6c757d; }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .filter-badge {
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-badge:hover {
            transform: scale(1.05);
        }
        
        .filter-badge.active {
            background-color: #007bff !important;
        }
        
        .log-container {
            max-height: 70vh;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-file-alt text-primary"></i>
                    Visualizador de Logs - Sistema PIX
                </h1>
            </div>
        </div>
        
        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-alt fa-2x mb-2"></i>
                        <h5><?php echo $stats['total_files']; ?></h5>
                        <small>Arquivos de Log</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-hdd fa-2x mb-2"></i>
                        <h5><?php echo $stats['total_size']; ?></h5>
                        <small>Tamanho Total</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-list fa-2x mb-2"></i>
                        <h5><?php echo $logData['total']; ?></h5>
                        <small>Total de Logs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-filter fa-2x mb-2"></i>
                        <h5><?php echo $logData['filtered']; ?></h5>
                        <small>Logs Filtrados</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-calendar"></i> Data</h6>
                    </div>
                    <div class="card-body">
                        <select class="form-select" onchange="changeDate(this.value)">
                            <?php foreach ($availableDates as $date): ?>
                                <option value="<?php echo $date; ?>" <?php echo $date === $defaultDate ? 'selected' : ''; ?>>
                                    <?php echo date('d/m/Y', strtotime($date)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-filter"></i> Filtro por Tipo</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge filter-badge <?php echo $defaultFilter === 'all' ? 'active bg-primary' : 'bg-secondary'; ?>" 
                                  onclick="changeFilter('all')">Todos</span>
                            <span class="badge filter-badge <?php echo $defaultFilter === 'INFO' ? 'active bg-primary' : 'bg-info'; ?>" 
                                  onclick="changeFilter('INFO')">Info</span>
                            <span class="badge filter-badge <?php echo $defaultFilter === 'SUCCESS' ? 'active bg-primary' : 'bg-success'; ?>" 
                                  onclick="changeFilter('SUCCESS')">Sucesso</span>
                            <span class="badge filter-badge <?php echo $defaultFilter === 'WARNING' ? 'active bg-primary' : 'bg-warning'; ?>" 
                                  onclick="changeFilter('WARNING')">Aviso</span>
                            <span class="badge filter-badge <?php echo $defaultFilter === 'ERROR' ? 'active bg-primary' : 'bg-danger'; ?>" 
                                  onclick="changeFilter('ERROR')">Erro</span>
                            <span class="badge filter-badge <?php echo $defaultFilter === 'PIX' ? 'active bg-primary' : 'bg-success'; ?>" 
                                  onclick="changeFilter('PIX')">PIX</span>
                            <span class="badge filter-badge <?php echo $defaultFilter === 'WEBHOOK' ? 'active bg-primary' : 'bg-warning'; ?>" 
                                  onclick="changeFilter('WEBHOOK')">Webhook</span>
                            <span class="badge filter-badge <?php echo $defaultFilter === 'REQUEST' ? 'active bg-primary' : 'bg-info'; ?>" 
                                  onclick="changeFilter('REQUEST')">Requisição</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Logs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-list"></i> 
                            Logs de <?php echo date('d/m/Y', strtotime($defaultDate)); ?>
                            <?php if ($defaultFilter !== 'all'): ?>
                                - Filtro: <?php echo $defaultFilter; ?>
                            <?php endif; ?>
                        </h6>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshLogs()">
                                <i class="fas fa-sync-alt"></i> Atualizar
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="clearLogs()">
                                <i class="fas fa-trash"></i> Limpar
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="log-container p-3">
                            <?php if (empty($logData['logs'])): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Nenhum log encontrado para esta data/filtro.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($logData['logs'] as $line): ?>
                                    <?php
                                    // Parse da linha de log
                                    if (preg_match('/^\[([^\]]+)\] \[([^\]]+)\] \[([^\]]+)\] \[([^\]]+)\] (.+)$/', $line, $matches)) {
                                        $timestamp = $matches[1];
                                        $level = $matches[2];
                                        $ip = $matches[3];
                                        $requestId = $matches[4];
                                        $message = $matches[5];
                                        
                                        $cssClass = 'log-' . strtolower($level);
                                    } else {
                                        // Fallback para logs mal formatados
                                        $timestamp = date('Y-m-d H:i:s');
                                        $level = 'UNKNOWN';
                                        $ip = 'unknown';
                                        $requestId = 'unknown';
                                        $message = $line;
                                        $cssClass = 'log-debug';
                                    }
                                    ?>
                                    <div class="log-entry <?php echo $cssClass; ?>">
                                        <span class="log-timestamp">[<?php echo $timestamp; ?>]</span>
                                        <span class="log-level">[<?php echo $level; ?>]</span>
                                        <span class="log-ip">[<?php echo $ip; ?>]</span>
                                        <span class="log-request-id">[<?php echo $requestId; ?>]</span>
                                        <span><?php echo htmlspecialchars($message); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeDate(date) {
            const url = new URL(window.location);
            url.searchParams.set('date', date);
            window.location.href = url.toString();
        }
        
        function changeFilter(filter) {
            const url = new URL(window.location);
            url.searchParams.set('filter', filter);
            window.location.href = url.toString();
        }
        
        function refreshLogs() {
            window.location.reload();
        }
        
        function clearLogs() {
            if (confirm('Tem certeza que deseja limpar os logs antigos? Esta ação não pode ser desfeita.')) {
                fetch('log-cleanup.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action: 'cleanup' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Logs antigos foram limpos com sucesso!');
                        window.location.reload();
                    } else {
                        alert('Erro ao limpar logs: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erro ao limpar logs: ' + error.message);
                });
            }
        }
        
        // Auto-refresh a cada 30 segundos
        setInterval(refreshLogs, 30000);
    </script>
</body>
</html>
