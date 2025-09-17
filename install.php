<?php
/**
 * Script de Instalação - Webhook PIX Oasy.fy
 * 
 * Este script configura automaticamente o sistema de webhook
 * com suas credenciais e configurações.
 */

echo "🚀 INSTALAÇÃO DO WEBHOOK PIX OASY.FY\n";
echo str_repeat("=", 50) . "\n\n";

// Verificar se as credenciais já estão configuradas
if (file_exists('config.php')) {
    require_once 'config.php';
    
    if (validateOasyfyCredentials()) {
        echo "✅ Configurações já estão definidas!\n";
        echo "   Chave Pública: " . substr(OASYFY_PUBLIC_KEY, 0, 20) . "...\n";
        echo "   Chave Privada: " . substr(OASYFY_SECRET_KEY, 0, 20) . "...\n\n";
        
        echo "🔧 Opções disponíveis:\n";
        echo "1. Testar conectividade\n";
        echo "2. Executar testes do webhook\n";
        echo "3. Iniciar monitoramento\n";
        echo "4. Sair\n\n";
        
        echo "Escolha uma opção (1-4): ";
        $option = trim(fgets(STDIN));
        
        switch ($option) {
            case '1':
                echo "\n🧪 Testando conectividade...\n";
                system('php test-credentials.php');
                break;
                
            case '2':
                echo "\n🧪 Executando testes do webhook...\n";
                system('php test-webhook.php all');
                break;
                
            case '3':
                echo "\n📊 Iniciando monitoramento...\n";
                system('php monitor-webhook.php');
                break;
                
            case '4':
                echo "👋 Até logo!\n";
                exit(0);
                
            default:
                echo "❌ Opção inválida!\n";
                exit(1);
        }
        
        exit(0);
    }
}

// Configurações
$publicKey = 'kevinmatheus986_a1k8td90862zf2d3';
$secretKey = 'h7gchnycerdys7ty517bspdh2o0inye1cbf97erk8i9421m101zekt389tn83fak';

echo "🔐 Configurando credenciais da Oasy.fy...\n";
echo "   Chave Pública: " . substr($publicKey, 0, 20) . "...\n";
echo "   Chave Privada: " . substr($secretKey, 0, 20) . "...\n\n";

// Criar diretórios necessários
echo "📁 Criando diretórios...\n";
$directories = ['logs', 'data', 'docs'];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "   ✅ Diretório '$dir' criado\n";
    } else {
        echo "   ℹ️ Diretório '$dir' já existe\n";
    }
}

// Verificar permissões
echo "\n🔒 Verificando permissões...\n";
$writableDirs = ['logs', 'data'];

foreach ($writableDirs as $dir) {
    if (is_writable($dir)) {
        echo "   ✅ Diretório '$dir' tem permissão de escrita\n";
    } else {
        echo "   ❌ Diretório '$dir' não tem permissão de escrita\n";
        echo "   Execute: chmod 755 $dir\n";
    }
}

// Testar conectividade
echo "\n🌐 Testando conectividade com a API...\n";
$apiUrl = 'https://app.oasyfy.com/api/v1/ping';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'x-public-key: ' . $publicKey,
    'x-secret-key: ' . $secretKey
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "   ❌ Erro de conectividade: $error\n";
} else {
    echo "   HTTP Code: $httpCode\n";
    
    $data = json_decode($response, true);
    if ($data && isset($data['message']) && $data['message'] === 'pong') {
        echo "   ✅ Conectividade com a API confirmada!\n";
    } else {
        echo "   ⚠️ Resposta inesperada da API\n";
    }
}

// Criar arquivo de configuração
echo "\n⚙️ Criando arquivo de configuração...\n";
$configContent = "<?php
/**
 * Configurações da API Oasy.fy
 * 
 * IMPORTANTE: Este arquivo contém suas credenciais reais.
 * Mantenha-o seguro e não compartilhe com terceiros.
 */

// Suas credenciais da Oasy.fy
define('OASYFY_PUBLIC_KEY', '$publicKey');
define('OASYFY_SECRET_KEY', '$secretKey');

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
        '$publicKey', // Sua chave pública
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
function getOasyfyConfig(\$key = null) {
    \$configs = [
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
    
    if (\$key === null) {
        return \$configs;
    }
    
    return \$configs[\$key] ?? null;
}

// Verificar se as credenciais estão configuradas
if (!validateOasyfyCredentials()) {
    error_log('⚠️ ATENÇÃO: Credenciais da Oasy.fy não configuradas corretamente!');
}

// Log de inicialização
if (OASYFY_SYSTEM_CONFIG['enable_debug']) {
    error_log('🔧 Configurações da Oasy.fy carregadas: ' . date('Y-m-d H:i:s'));
}
?>";

if (file_put_contents('config.php', $configContent)) {
    echo "   ✅ Arquivo config.php criado com sucesso\n";
} else {
    echo "   ❌ Erro ao criar arquivo config.php\n";
    exit(1);
}

// Criar arquivo .htaccess para segurança
echo "\n🔒 Criando arquivo .htaccess...\n";
$htaccessContent = "# Proteção do diretório de logs
<Files \"*.log\">
    Order allow,deny
    Deny from all
</Files>

# Proteção do diretório de dados
<Files \"*.json\">
    Order allow,deny
    Deny from all
</Files>

# Proteção do arquivo de configuração
<Files \"config.php\">
    Order allow,deny
    Deny from all
</Files>

# Headers de segurança
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection \"1; mode=block\"
</IfModule>

# Compressão
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>";

if (file_put_contents('.htaccess', $htaccessContent)) {
    echo "   ✅ Arquivo .htaccess criado com sucesso\n";
} else {
    echo "   ⚠️ Não foi possível criar arquivo .htaccess\n";
}

// Executar teste de credenciais
echo "\n🧪 Executando teste de credenciais...\n";
system('php test-credentials.php');

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 INSTALAÇÃO CONCLUÍDA COM SUCESSO!\n";
echo str_repeat("=", 50) . "\n\n";

echo "📋 Próximos passos:\n";
echo "1. Configure a URL do webhook na Oasy.fy:\n";
echo "   https://seusite.com/webhook-example.php\n\n";

echo "2. Teste o webhook:\n";
echo "   php test-webhook.php all\n\n";

echo "3. Monitore em tempo real:\n";
echo "   php monitor-webhook.php\n\n";

echo "4. Acesse o monitor web:\n";
echo "   http://localhost/monitor-webhook.php\n\n";

echo "🔧 Comandos úteis:\n";
echo "   php test-credentials.php    # Testar credenciais\n";
echo "   php test-webhook.php all    # Testar webhook\n";
echo "   php monitor-webhook.php     # Monitor CLI\n";
echo "   php install.php             # Reinstalar\n\n";

echo "📚 Documentação:\n";
echo "   - ENHANCED_WEBHOOK_GUIDE.md\n";
echo "   - WEBHOOK_ERROR_HANDLING_GUIDE.md\n";
echo "   - PIX_Webhook_Guide.md\n\n";

echo "✅ Sistema pronto para uso!\n";
?>
