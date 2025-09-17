# Guia Avançado: Webhook PIX Oasy.fy com Logs e Idempotência Aprimorados

## 🚀 **Funcionalidades Implementadas**

### ✅ **Sistema de Logs Avançado**

#### **1. Logs Estruturados com Níveis**
```php
Logger::debug('Mensagem de debug', ['contexto' => 'dados']);
Logger::info('Mensagem informativa', ['contexto' => 'dados']);
Logger::warning('Aviso importante', ['contexto' => 'dados']);
Logger::error('Erro recuperável', ['contexto' => 'dados']);
Logger::critical('Erro crítico', ['contexto' => 'dados']);
```

#### **2. Múltiplos Tipos de Log**
- **Webhook Logs**: Logs gerais de todas as operações
- **Error Logs**: Logs específicos de erros e falhas
- **Performance Logs**: Métricas de performance e tempo de execução
- **Audit Logs**: Logs de auditoria para eventos importantes

#### **3. Rotação Automática de Logs**
- Logs organizados por mês (ex: `webhook_2024-01.log`)
- Limpeza automática de logs antigos (30 dias)
- Controle de tamanho e performance

#### **4. Métricas Detalhadas**
```php
Logger::logMetrics([
    'event' => 'TRANSACTION_PAID',
    'processing_time_ms' => 150.5,
    'memory_usage' => '2.5 MB',
    'cache_stats' => ['size' => 100]
]);
```

### ✅ **Sistema de Idempotência Avançado**

#### **1. Cache em Memória**
- Cache de alta performance para verificações rápidas
- Persistência em arquivo para recuperação
- Carregamento automático na inicialização

#### **2. TTL (Time To Live)**
- Transações expiram automaticamente após 24 horas
- Configurável via constante `IDEMPOTENCY_TTL_HOURS`
- Limpeza automática de entradas expiradas

#### **3. Limpeza Automática**
- Limpeza a cada hora para evitar overhead
- Remoção de transações expiradas
- Otimização de performance

#### **4. Informações Detalhadas**
```php
$transactionInfo = IdempotencyManager::getTransactionInfo($transactionId);
// Retorna: event, processed_at, expires_at, ip, user_agent, request_id
```

## 📁 **Estrutura de Arquivos**

```
webhook/
├── webhook-example.php              # Webhook principal aprimorado
├── test-webhook.php                 # Script de testes avançados
├── monitor-webhook.php              # Monitor em tempo real
├── logs/                           # Diretório de logs
│   ├── webhook_2024-01.log         # Logs gerais
│   ├── errors_2024-01.log          # Logs de erro
│   ├── performance_2024-01.log     # Logs de performance
│   └── audit_2024-01.log           # Logs de auditoria
├── data/                           # Dados persistentes
│   ├── processed_transactions.json # Transações processadas
│   ├── idempotency_cache.json      # Cache de idempotência
│   ├── retry_queue.json            # Fila de retry
│   └── confirmed_payments.json     # Backup de pagamentos
└── docs/                           # Documentação
    ├── WEBHOOK_ERROR_HANDLING_GUIDE.md
    └── ENHANCED_WEBHOOK_GUIDE.md
```

## 🔧 **Configurações Avançadas**

### **Constantes de Configuração**
```php
define('IDEMPOTENCY_TTL_HOURS', 24);        // TTL para idempotência
define('LOG_ROTATION_DAYS', 30);            // Rotação de logs
define('CACHE_CLEANUP_INTERVAL', 3600);     // Limpeza de cache (1 hora)
define('MAX_RETRY_ATTEMPTS', 3);            // Máximo de tentativas
define('RETRY_DELAY_SECONDS', 5);           // Delay entre tentativas
```

### **Níveis de Log Configuráveis**
```php
Logger::setLogLevel('debug');   // 0 - Todos os logs
Logger::setLogLevel('info');    // 1 - Info, Warning, Error, Critical
Logger::setLogLevel('warning'); // 2 - Warning, Error, Critical
Logger::setLogLevel('error');   // 3 - Error, Critical
Logger::setLogLevel('critical'); // 4 - Apenas Critical
```

## 📊 **Monitoramento em Tempo Real**

### **1. Interface Web**
```bash
# Acesse no navegador
http://localhost/monitor-webhook.php
```

**Funcionalidades:**
- Estatísticas de logs em tempo real
- Métricas de performance
- Resumo de erros
- Logs recentes
- Auto-refresh a cada 30 segundos

### **2. Interface de Linha de Comando**
```bash
# Monitor em tempo real
php monitor-webhook.php
```

**Funcionalidades:**
- Atualização automática a cada 5 segundos
- Estatísticas detalhadas
- Logs recentes
- Informações do sistema

## 🧪 **Testes Avançados**

### **1. Teste de Idempotência**
```bash
php test-webhook.php idempotency
```
- Testa processamento duplicado
- Verifica cache em memória
- Valida TTL e expiração

### **2. Teste de Performance**
```bash
php test-webhook.php performance
```
- Executa 5 requisições sequenciais
- Mede tempos de resposta
- Calcula estatísticas (média, min, max)

### **3. Teste de Logs**
```bash
php test-webhook.php logs
```
- Verifica geração de logs
- Valida Request ID
- Confirma métricas de performance

### **4. Todos os Testes**
```bash
php test-webhook.php all
```

## 📈 **Métricas e Performance**

### **Logs de Performance**
```json
{
  "timestamp": "2024-01-15T10:30:00Z",
  "level": "INFO",
  "message": "Pagamento processado com sucesso",
  "request_id": "req_65a4b2c8d9e1f",
  "execution_time": 150.5,
  "memory_peak": "2.5 MB",
  "memory_current": "1.8 MB",
  "context": {
    "transaction_id": "tx_123",
    "processing_time_ms": 120.3
  }
}
```

### **Métricas de Sistema**
- Tempo de execução por requisição
- Uso de memória (atual e pico)
- Tamanho do cache de idempotência
- Número de transações processadas
- Estatísticas de erro por tipo

## 🔍 **Análise de Logs**

### **1. Logs Estruturados**
Todos os logs são em formato JSON para fácil análise:
```json
{
  "timestamp": "2024-01-15T10:30:00Z",
  "level": "INFO",
  "message": "Webhook recebido",
  "context": {
    "event": "TRANSACTION_PAID",
    "transaction_id": "tx_123",
    "size": 1024
  },
  "request_id": "req_65a4b2c8d9e1f",
  "execution_time": "15.5ms",
  "memory_usage": "1.8 MB",
  "ip": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "server": {
    "php_version": "8.1.0",
    "server_software": "Apache/2.4.41"
  }
}
```

### **2. Análise com Ferramentas**
```bash
# Contar erros por tipo
grep '"level":"ERROR"' logs/errors_2024-01.log | jq '.context.error' | sort | uniq -c

# Tempo médio de processamento
grep '"type":"metrics"' logs/performance_2024-01.log | jq '.metrics.processing_time_ms' | awk '{sum+=$1; count++} END {print sum/count}'

# Transações por hora
grep '"event":"TRANSACTION_PAID"' logs/webhook_2024-01.log | jq -r '.timestamp' | cut -d'T' -f2 | cut -d':' -f1 | sort | uniq -c
```

## 🛡️ **Segurança Aprimorada**

### **1. Logs de Auditoria**
- Registro de todas as tentativas de acesso
- Rastreamento de IPs e User Agents
- Logs de validação de token
- Registro de transações processadas

### **2. Headers de Segurança**
```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
```

### **3. Validação Rigorosa**
- Validação de token obrigatória
- Verificação de método HTTP
- Validação de Content-Type
- Limite de tamanho da requisição

## ⚡ **Otimizações de Performance**

### **1. Cache em Memória**
- Verificações de idempotência em O(1)
- Redução de I/O de arquivo
- Persistência automática

### **2. Limpeza Automática**
- Limpeza de logs antigos
- Remoção de transações expiradas
- Otimização de arquivos de dados

### **3. Logs Assíncronos**
- Escrita não-bloqueante
- Rotação automática
- Controle de tamanho

## 🔧 **Manutenção e Monitoramento**

### **1. Verificação de Saúde**
```bash
# Verificar status do webhook
curl -X POST http://localhost/webhook-example.php \
  -H "Content-Type: application/json" \
  -d '{"event":"TRANSACTION_PAID","token":"tbdeizos8f","transaction":{"id":"health-check","status":"COMPLETED","amount":1.00},"client":{"id":"health","name":"Health Check","email":"health@test.com"}}'
```

### **2. Limpeza Manual**
```bash
# Limpar cache de idempotência
php -r "require 'webhook-example.php'; IdempotencyManager::clearCache();"

# Verificar estatísticas do cache
php -r "require 'webhook-example.php'; print_r(IdempotencyManager::getCacheStats());"
```

### **3. Backup de Dados**
```bash
# Backup dos dados importantes
tar -czf webhook-backup-$(date +%Y%m%d).tar.gz data/ logs/
```

## 📋 **Checklist de Produção**

### ✅ **Configuração**
- [ ] Configurar tokens de segurança reais
- [ ] Ajustar TTL de idempotência conforme necessário
- [ ] Configurar rotação de logs
- [ ] Definir níveis de log apropriados

### ✅ **Monitoramento**
- [ ] Configurar alertas para erros críticos
- [ ] Monitorar uso de disco (logs)
- [ ] Verificar performance regularmente
- [ ] Analisar logs de auditoria

### ✅ **Manutenção**
- [ ] Backup regular dos dados
- [ ] Limpeza de logs antigos
- [ ] Verificação de integridade
- [ ] Atualização de dependências

### ✅ **Testes**
- [ ] Testes de idempotência
- [ ] Testes de performance
- [ ] Testes de segurança
- [ ] Testes de recuperação

## 🎯 **Benefícios das Melhorias**

### **1. Observabilidade**
- Logs estruturados e detalhados
- Métricas de performance em tempo real
- Rastreamento completo de requisições
- Análise de tendências e padrões

### **2. Confiabilidade**
- Idempotência robusta com TTL
- Cache em memória para performance
- Limpeza automática de dados antigos
- Recuperação automática de falhas

### **3. Manutenibilidade**
- Código organizado e documentado
- Configurações centralizadas
- Ferramentas de monitoramento
- Scripts de teste automatizados

### **4. Performance**
- Cache otimizado para verificações rápidas
- Logs assíncronos
- Limpeza automática de recursos
- Métricas detalhadas para otimização

## 🚀 **Próximos Passos**

1. **Implementar alertas por email/SMS** para erros críticos
2. **Adicionar dashboard web** com gráficos em tempo real
3. **Implementar rate limiting** para proteção contra spam
4. **Adicionar suporte a múltiplos ambientes** (dev, staging, prod)
5. **Implementar backup automático** dos dados críticos

---

**O sistema está agora preparado para produção com logs avançados, idempotência robusta e monitoramento completo!** 🎉
