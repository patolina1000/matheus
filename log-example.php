<?php
/**
 * Exemplo de Uso do Sistema de Logs Simples
 * Demonstra como usar o SimpleLogger para organizar logs
 */

require_once 'simple-logger.php';

// Simular algumas operações e gerar logs organizados
echo "<h1>Exemplo de Sistema de Logs Organizado</h1>";
echo "<p>Verifique o arquivo de log em: logs/app_" . date('Y-m-d') . ".log</p>";

// Log de inicialização
SimpleLogger::info('Sistema iniciado', [
    'version' => '1.0.0',
    'environment' => 'development'
]);

// Simular requisição de API
SimpleLogger::request('POST', '/api-proxy.php', [
    'action' => 'generate_pix',
    'amount' => 19.90
]);

// Simular resposta da API
SimpleLogger::response(200, 'PIX gerado com sucesso', [
    'transaction_id' => 'txn_123456789',
    'pix_code' => '00020126580014br.gov.bcb.pix...'
]);

// Simular operação PIX
SimpleLogger::pix('GENERATE', 'PIX gerado para cliente', [
    'client_name' => 'João Silva',
    'client_email' => 'joao@email.com',
    'amount' => 19.90
]);

// Simular webhook recebido
SimpleLogger::webhook('TRANSACTION_PAID', 'Pagamento confirmado', [
    'transaction_id' => 'txn_123456789',
    'amount' => 19.90,
    'status' => 'COMPLETED'
]);

// Simular sucesso
SimpleLogger::success('Processo concluído com sucesso', [
    'total_processed' => 1,
    'execution_time' => '2.5s'
]);

// Simular aviso
SimpleLogger::warning('Tentativa de retry detectada', [
    'transaction_id' => 'txn_123456789',
    'attempt' => 2
]);

// Simular erro (comentado para não gerar erro real)
// SimpleLogger::error('Falha na conexão com banco de dados', [
//     'error_code' => 'DB_CONNECTION_FAILED',
//     'retry_count' => 3
// ]);

// Simular debug
SimpleLogger::debug('Cache atualizado', [
    'cache_size' => 150,
    'memory_usage' => '2.5MB'
]);

echo "<h2>Logs Gerados:</h2>";
echo "<ul>";
echo "<li>✅ Log de inicialização</li>";
echo "<li>✅ Log de requisição HTTP</li>";
echo "<li>✅ Log de resposta HTTP</li>";
echo "<li>✅ Log de operação PIX</li>";
echo "<li>✅ Log de webhook</li>";
echo "<li>✅ Log de sucesso</li>";
echo "<li>✅ Log de aviso</li>";
echo "<li>✅ Log de debug</li>";
echo "</ul>";

echo "<h2>Próximos Passos:</h2>";
echo "<ol>";
echo "<li><a href='log-viewer.php'>Visualizar logs organizados</a></li>";
echo "<li>Testar o sistema PIX para gerar logs reais</li>";
echo "<li>Configurar limpeza automática de logs antigos</li>";
echo "</ol>";

// Mostrar estatísticas
$stats = SimpleLogger::getStats();
echo "<h2>Estatísticas dos Logs:</h2>";
echo "<ul>";
echo "<li>Total de arquivos: " . $stats['total_files'] . "</li>";
echo "<li>Tamanho total: " . $stats['total_size'] . "</li>";
echo "<li>Diretório: " . $stats['log_directory'] . "</li>";
echo "</ul>";
?>
