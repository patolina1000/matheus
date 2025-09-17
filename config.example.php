<?php
/**
 * Arquivo de Configuração de Exemplo - Webhook PIX Oasy.fy
 * 
 * INSTRUÇÕES:
 * 1. Copie este arquivo para config.php
 * 2. Substitua as credenciais de exemplo pelas suas credenciais reais
 * 3. Ajuste as configurações conforme necessário
 */

// Suas credenciais da Oasy.fy
define('OASYFY_PUBLIC_KEY', 'SUA_CHAVE_PUBLICA_AQUI');
define('OASYFY_SECRET_KEY', 'SUA_CHAVE_PRIVADA_AQUI');

// URL base da API
define('OASYFY_API_BASE_URL', 'https://app.oasyfy.com/api/v1');

// URL do seu webhook (ajuste conforme seu domínio)
define('OASYFY_WEBHOOK_URL', 'https://seusite.com/webhook-example.php');

// Configurações do sistema
define('OASYFY_SYSTEM_CONFIG', [
    'timeout' => 30,
    'retry_attempts' => 3,
    'retry_delay' => 5,
    'log_level' => 'info',
    'enable_debug' => false
]);

// Configurações de segurança
define('OASYFY_SECURITY_CONFIG', [
    'valid_tokens' => [
        'tbdeizos8f', // Token de exemplo
        'SUA_CHAVE_PUBLICA_AQUI', // Sua chave pública
    ],
    'max_request_size' => 1024 * 1024, // 1MB
    'rate_limit' => 100, // requisições por minuto
    'allowed_ips' => [], // Deixe vazio para permitir todos os IPs
]);

// Configurações de logs
define('OASYFY_LOG_CONFIG', [
    'log_dir' => 'logs/',
    'log_rotation_days' => 30,
    'log_levels' => ['debug', 'info', 'warning', 'error', 'critical'],
    'enable_performance_logs' => true,
    'enable_audit_logs' => true,
]);

// Configurações de idempotência
define('OASYFY_IDEMPOTENCY_CONFIG', [
    'ttl_hours' => 24,
    'cache_cleanup_interval' => 3600, // 1 hora
    'enable_memory_cache' => true,
    'max_cache_size' => 10000,
]);

// Configurações de banco de dados (opcional)
define('OASYFY_DATABASE_CONFIG', [
    'enabled' => false,
    'host' => 'localhost',
    'database' => 'oasyfy_webhook',
    'username' => 'usuario',
    'password' => 'senha',
    'charset' => 'utf8mb4',
]);

// Configurações de email (opcional)
define('OASYFY_EMAIL_CONFIG', [
    'enabled' => false,
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'seu_email@gmail.com',
    'smtp_password' => 'sua_senha_app',
    'from_email' => 'noreply@seusite.com',
    'from_name' => 'Sistema PIX',
]);

// Configurações de notificação
define('OASYFY_NOTIFICATION_CONFIG', [
    'enable_slack' => false,
    'slack_webhook_url' => '',
    'enable_discord' => false,
    'discord_webhook_url' => '',
    'enable_telegram' => false,
    'telegram_bot_token' => '',
    'telegram_chat_id' => '',
]);

// Validação das credenciais
function validateOasyfyCredentials() {
    if (OASYFY_PUBLIC_KEY === 'SUA_CHAVE_PUBLICA_AQUI' || 
        OASYFY_SECRET_KEY === 'SUA_CHAVE_PRIVADA_AQUI') {
        return false;
    }
    
    if (empty(OASYFY_PUBLIC_KEY) || empty(OASYFY_SECRET_KEY)) {
        return false;
    }
    
    return true;
}

// Função para obter configuração
function getOasyfyConfig($key = null) {
    $configs = [
        'public_key' => OASYFY_PUBLIC_KEY,
        'secret_key' => OASYFY_SECRET_KEY,
        'api_base_url' => OASYFY_API_BASE_URL,
        'webhook_url' => OASYFY_WEBHOOK_URL,
        'system' => OASYFY_SYSTEM_CONFIG,
        'security' => OASYFY_SECURITY_CONFIG,
        'log' => OASYFY_LOG_CONFIG,
        'idempotency' => OASYFY_IDEMPOTENCY_CONFIG,
        'database' => OASYFY_DATABASE_CONFIG,
        'email' => OASYFY_EMAIL_CONFIG,
        'notification' => OASYFY_NOTIFICATION_CONFIG,
    ];
    
    if ($key === null) {
        return $configs;
    }
    
    return $configs[$key] ?? null;
}

// Verificar se as credenciais estão configuradas
if (!validateOasyfyCredentials()) {
    error_log('⚠️ ATENÇÃO: Credenciais da Oasy.fy não configuradas corretamente!');
}

// Log de inicialização
if (OASYFY_SYSTEM_CONFIG['enable_debug']) {
    error_log('🔧 Configurações da Oasy.fy carregadas: ' . date('Y-m-d H:i:s'));
}
?>
