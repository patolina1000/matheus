# Guia de Tratamento de Erros - Webhook PIX Oasy.fy

## Visão Geral

Este guia explica como o webhook PIX foi implementado com tratamento de erros robusto, seguindo as melhores práticas de desenvolvimento seguro e confiável.

## Funcionalidades Implementadas

### ✅ 1. Classes de Exceção Personalizadas

```php
// Exceções específicas para diferentes tipos de erro
- WebhookException: Exceção base
- ValidationException: Erros de validação (HTTP 400)
- SecurityException: Erros de segurança (HTTP 401)
- ProcessingException: Erros de processamento (HTTP 500)
```

### ✅ 2. Sistema de Logs Estruturado

```php
// Logs organizados por nível e contexto
Logger::info('Mensagem informativa', ['contexto' => 'dados']);
Logger::warning('Aviso importante', ['contexto' => 'dados']);
Logger::error('Erro recuperável', ['contexto' => 'dados']);
Logger::critical('Erro crítico', ['contexto' => 'dados']);
```

**Arquivos de Log:**
- `logs/webhook_YYYY-MM.log` - Logs gerais
- `logs/errors_YYYY-MM.log` - Logs de erro específicos

### ✅ 3. Validação de Segurança

```php
// Validações implementadas:
- Token de segurança obrigatório
- Método HTTP (apenas POST)
- Content-Type (application/json)
- Tamanho da requisição (máximo 1MB)
- Headers de segurança
```

### ✅ 4. Validação de Dados

```php
// Campos obrigatórios validados:
- event (TRANSACTION_PAID, TRANSACTION_CREATED, etc.)
- transaction.id (string não vazia)
- transaction.amount (número positivo)
- client.email (email válido)
- client.name (string não vazia)
```

### ✅ 5. Idempotência

```php
// Evita processamento duplicado:
- Verifica se transação já foi processada
- Marca transações como processadas
- Retorna status apropriado para duplicatas
```

### ✅ 6. Sistema de Retry

```php
// Retry automático para falhas temporárias:
- Máximo 3 tentativas
- Delay progressivo (5s, 10s, 15s)
- Fila de retry persistente
- Processamento automático da fila
```

## Estrutura de Arquivos

```
webhook/
├── webhook-example.php          # Webhook principal
├── logs/                        # Diretório de logs
│   ├── webhook_2024-01.log     # Logs gerais
│   └── errors_2024-01.log      # Logs de erro
├── data/                        # Dados persistentes
│   ├── processed_transactions.json  # Transações processadas
│   ├── retry_queue.json        # Fila de retry
│   └── confirmed_payments.json # Backup de pagamentos
└── WEBHOOK_ERROR_HANDLING_GUIDE.md  # Este guia
```

## Fluxo de Processamento

### 1. Recebimento da Requisição
```php
try {
    // 1. Processar fila de retry
    RetryManager::processRetryQueue();
    
    // 2. Validar requisição HTTP
    SecurityValidator::validateRequest();
    
    // 3. Receber e decodificar dados
    $webhookData = json_decode($input, true);
    
    // 4. Log da requisição
    Logger::info('Webhook recebido', $context);
    
    // 5. Validar token de segurança
    SecurityValidator::validateToken($token);
    
    // 6. Validar dados do webhook
    WebhookValidator::validate($webhookData);
    
    // 7. Verificar idempotência
    if (IdempotencyManager::isTransactionProcessed($transactionId)) {
        return 'already_processed';
    }
    
    // 8. Processar evento
    $response = processWebhookEvent($webhookData);
    
    // 9. Marcar como processado
    IdempotencyManager::markTransactionAsProcessed($transactionId, $event);
    
} catch (ValidationException $e) {
    // HTTP 400 - Erro de validação
} catch (SecurityException $e) {
    // HTTP 401 - Erro de segurança
} catch (ProcessingException $e) {
    // HTTP 500 - Erro de processamento + retry
} catch (Exception $e) {
    // HTTP 500 - Erro inesperado
}
```

### 2. Processamento de Eventos

```php
switch ($event) {
    case 'TRANSACTION_PAID':
        return processTransactionPaid($transaction, $client, $webhookData);
    case 'TRANSACTION_CREATED':
        return processTransactionCreated($transaction, $client, $webhookData);
    case 'TRANSACTION_CANCELED':
        return processTransactionCanceled($transaction, $client, $webhookData);
    case 'TRANSACTION_REFUNDED':
        return processTransactionRefunded($transaction, $client, $webhookData);
}
```

### 3. Processamento de Pagamento

```php
function processPaymentConfirmed($transactionId, $amount, $clientName, $clientEmail, $fullData) {
    try {
        // 1. Salvar no banco de dados (CRÍTICO)
        savePaymentToDatabase($transactionId, $amount, $clientName, $clientEmail);
        
        // 2. Enviar email (NÃO CRÍTICO)
        sendConfirmationEmail($clientEmail, $clientName, $amount);
        
        // 3. Liberar acesso (CRÍTICO)
        grantAccessToContent($clientEmail, $fullData);
        
        // 4. Atualizar status (CRÍTICO)
        updateOrderStatus($transactionId, 'paid');
        
        // 5. Notificar sistemas (NÃO CRÍTICO)
        notifyInternalSystems($transactionId, $amount);
        
        // 6. Backup (NÃO CRÍTICO)
        savePaymentBackup($transactionId, $amount, $clientName, $clientEmail, $fullData);
        
    } catch (ProcessingException $e) {
        throw $e; // Re-lançar exceções críticas
    } catch (Exception $e) {
        throw new ProcessingException('Erro inesperado: ' . $e->getMessage(), $e);
    }
}
```

## Códigos de Resposta HTTP

| Código | Situação | Ação |
|--------|----------|------|
| 200 | Sucesso | Processamento concluído |
| 200 | Já processado | Transação já foi processada |
| 400 | Erro de validação | Dados inválidos |
| 401 | Erro de segurança | Token inválido |
| 500 | Erro de processamento | Retry agendado |
| 500 | Erro interno | Log crítico |

## Configuração de Produção

### 1. Configurar Tokens de Segurança

```php
// Em SecurityValidator::$validTokens
private static $validTokens = [
    'seu_token_producao_1',
    'seu_token_producao_2',
    // Adicione seus tokens reais aqui
];
```

### 2. Configurar Banco de Dados

```php
// Descomente e configure em savePaymentToDatabase()
$pdo = new PDO('mysql:host=localhost;dbname=seu_banco', 'usuario', 'senha', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
```

### 3. Configurar Email

```php
// Descomente e configure em sendConfirmationEmail()
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'seu_email@gmail.com';
$mail->Password = 'sua_senha_app';
```

### 4. Configurar Permissões

```bash
# Criar diretórios com permissões adequadas
mkdir -p logs data
chmod 755 logs data
chmod 644 logs/*.log data/*.json
```

## Monitoramento e Manutenção

### 1. Verificar Logs

```bash
# Ver logs em tempo real
tail -f logs/webhook_$(date +%Y-%m).log

# Ver apenas erros
tail -f logs/errors_$(date +%Y-%m).log
```

### 2. Verificar Fila de Retry

```bash
# Ver transações pendentes de retry
cat data/retry_queue.json | jq '.[] | select(.scheduled_at <= now)'
```

### 3. Verificar Transações Processadas

```bash
# Ver transações já processadas
cat data/processed_transactions.json | jq 'keys | length'
```

### 4. Limpeza de Logs Antigos

```bash
# Remover logs com mais de 30 dias
find logs/ -name "*.log" -mtime +30 -delete
```

## Testes

### 1. Teste de Validação

```bash
# Teste com dados inválidos
curl -X POST http://localhost/webhook-example.php \
  -H "Content-Type: application/json" \
  -d '{"event": "INVALID_EVENT"}'
```

### 2. Teste de Segurança

```bash
# Teste com token inválido
curl -X POST http://localhost/webhook-example.php \
  -H "Content-Type: application/json" \
  -d '{"event": "TRANSACTION_PAID", "token": "invalid_token"}'
```

### 3. Teste de Idempotência

```bash
# Enviar mesma transação duas vezes
curl -X POST http://localhost/webhook-example.php \
  -H "Content-Type: application/json" \
  -d '{"event": "TRANSACTION_PAID", "transaction": {"id": "test-123"}}'
```

## Troubleshooting

### Problema: Webhook não está recebendo dados

**Soluções:**
1. Verificar se o endpoint está acessível
2. Verificar logs de erro
3. Verificar configuração do callbackUrl na Oasy.fy

### Problema: Transações duplicadas

**Soluções:**
1. Verificar se idempotência está funcionando
2. Verificar arquivo `processed_transactions.json`
3. Verificar logs para transações já processadas

### Problema: Retry não está funcionando

**Soluções:**
1. Verificar arquivo `retry_queue.json`
2. Verificar permissões de escrita
3. Verificar logs de erro

### Problema: Logs não estão sendo gerados

**Soluções:**
1. Verificar permissões do diretório `logs/`
2. Verificar se PHP tem permissão de escrita
3. Verificar configuração de error_reporting

## Segurança

### 1. Headers de Segurança

```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
```

### 2. Validação de Entrada

```php
// Sempre validar dados de entrada
WebhookValidator::validate($webhookData);
SecurityValidator::validateToken($token);
```

### 3. Logs de Segurança

```php
// Log tentativas de acesso inválidas
Logger::warning('Token de segurança inválido', [
    'ip' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT']
]);
```

## Performance

### 1. Otimizações Implementadas

- Logs assíncronos
- Validação rápida de dados
- Processamento em memória
- Arquivos de dados otimizados

### 2. Limites de Recursos

- Máximo 1MB por requisição
- Máximo 3 tentativas de retry
- Logs rotativos por mês
- Limpeza automática de dados antigos

## Conclusão

O webhook implementado segue as melhores práticas de:

- ✅ **Segurança**: Validação de token, headers de segurança
- ✅ **Confiabilidade**: Idempotência, retry automático
- ✅ **Observabilidade**: Logs estruturados, monitoramento
- ✅ **Manutenibilidade**: Código organizado, documentação
- ✅ **Performance**: Processamento otimizado, limites de recursos

Este sistema está pronto para produção e pode ser facilmente adaptado para suas necessidades específicas.
