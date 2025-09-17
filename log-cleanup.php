<?php
/**
 * Limpeza de Logs
 * API para limpar logs antigos
 */

require_once 'simple-logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || $data['action'] !== 'cleanup') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    exit();
}

try {
    // Executar limpeza
    SimpleLogger::cleanup();
    
    // Obter estatísticas após limpeza
    $stats = SimpleLogger::getStats();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logs antigos foram limpos com sucesso',
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao limpar logs: ' . $e->getMessage()
    ]);
}
?>
