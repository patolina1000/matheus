# 🚀 Webhook PIX Oasy.fy - Sistema Completo

Sistema completo de webhook PIX com tratamento de erros robusto, logs avançados, idempotência e monitoramento em tempo real.

## ✨ Funcionalidades

- ✅ **Webhook PIX** com tratamento de erros robusto
- ✅ **Logs estruturados** com rotação automática
- ✅ **Idempotência avançada** com TTL e cache em memória
- ✅ **Monitoramento em tempo real** (web e CLI)
- ✅ **Sistema de retry** para falhas temporárias
- ✅ **Validação de segurança** rigorosa
- ✅ **Testes automatizados** completos
- ✅ **Configuração automática** com suas credenciais

## 🚀 Instalação Rápida

### 1. Instalação Automática
```bash
# Execute o script de instalação
php install.php
```

O script irá:
- ✅ Configurar suas credenciais automaticamente
- ✅ Criar diretórios necessários
- ✅ Testar conectividade com a API
- ✅ Configurar arquivos de segurança
- ✅ Executar testes iniciais

### 2. Instalação Manual
```bash
# 1. Clone ou baixe os arquivos
# 2. Configure suas credenciais no config.php
# 3. Crie os diretórios
mkdir logs data docs
chmod 755 logs data

# 4. Teste a instalação
php test-credentials.php
```

## 🔧 Configuração

### Suas Credenciais (já configuradas)
- **Chave Pública**: `kevinmatheus986_a1k8td90862zf2d3`
- **Chave Privada**: `h7gchnycerdys7ty517bspdh2o0inye1cbf97erk8i9421m101zekt389tn83fak`

### URL do Webhook
Configure na Oasy.fy:
```
https://seusite.com/webhook-example.php
```

## 🧪 Testes

### Teste de Credenciais
```bash
php test-credentials.php
```

### Teste do Webhook
```bash
# Todos os testes
php test-webhook.php all

# Testes específicos
php test-webhook.php idempotency
php test-webhook.php performance
php test-webhook.php logs
php test-webhook.php success
```

## 📊 Monitoramento

### Interface Web
```
http://localhost/monitor-webhook.php
```

### Interface CLI
```bash
php monitor-webhook.php
```

## 📁 Estrutura de Arquivos

```
webhook/
├── webhook-example.php          # Webhook principal
├── config.php                   # Configurações
├── install.php                  # Script de instalação
├── test-credentials.php         # Teste de credenciais
├── test-webhook.php             # Testes do webhook
├── monitor-webhook.php          # Monitor em tempo real
├── logs/                        # Logs do sistema
│   ├── webhook_2024-01.log
│   ├── errors_2024-01.log
│   ├── performance_2024-01.log
│   └── audit_2024-01.log
├── data/                        # Dados persistentes
│   ├── processed_transactions.json
│   ├── idempotency_cache.json
│   ├── retry_queue.json
│   └── confirmed_payments.json
└── docs/                        # Documentação
    ├── README.md
    ├── ENHANCED_WEBHOOK_GUIDE.md
    └── WEBHOOK_ERROR_HANDLING_GUIDE.md
```

## 🔐 Segurança

- ✅ Validação de token obrigatória
- ✅ Headers de segurança
- ✅ Proteção de arquivos sensíveis
- ✅ Logs de auditoria
- ✅ Rate limiting
- ✅ Validação de entrada rigorosa

## 📈 Performance

- ✅ Cache em memória para idempotência
- ✅ Logs assíncronos
- ✅ Limpeza automática de dados antigos
- ✅ Rotação automática de logs
- ✅ Métricas detalhadas

## 🛠️ Comandos Úteis

```bash
# Instalação
php install.php

# Testes
php test-credentials.php
php test-webhook.php all

# Monitoramento
php monitor-webhook.php

# Limpeza manual
php -r "require 'webhook-example.php'; IdempotencyManager::clearCache();"
```

## 📚 Documentação

- **[Guia Avançado](ENHANCED_WEBHOOK_GUIDE.md)** - Funcionalidades avançadas
- **[Tratamento de Erros](WEBHOOK_ERROR_HANDLING_GUIDE.md)** - Sistema de erros
- **[Guia PIX](PIX_Webhook_Guide.md)** - Documentação original

## 🎯 Eventos Suportados

- `TRANSACTION_CREATED` - PIX criado
- `TRANSACTION_PAID` - PIX pago ✅
- `TRANSACTION_CANCELED` - PIX cancelado
- `TRANSACTION_REFUNDED` - PIX estornado

## 📊 Métricas Disponíveis

- Tempo de processamento por requisição
- Uso de memória (atual e pico)
- Tamanho do cache de idempotência
- Número de transações processadas
- Estatísticas de erro por tipo
- Logs de auditoria completos

## 🔧 Configurações Avançadas

### Logs
```php
// Níveis de log
Logger::setLogLevel('debug');   // Todos os logs
Logger::setLogLevel('info');    // Info, Warning, Error, Critical
Logger::setLogLevel('warning'); // Warning, Error, Critical
Logger::setLogLevel('error');   // Error, Critical
Logger::setLogLevel('critical'); // Apenas Critical
```

### Idempotência
```php
// TTL configurável
define('IDEMPOTENCY_TTL_HOURS', 24); // 24 horas
```

### Retry
```php
// Configurações de retry
define('MAX_RETRY_ATTEMPTS', 3);     // 3 tentativas
define('RETRY_DELAY_SECONDS', 5);    // 5 segundos
```

## 🚨 Troubleshooting

### Problema: Webhook não recebe dados
1. Verifique se o endpoint está acessível
2. Confirme a URL configurada na Oasy.fy
3. Verifique logs de erro

### Problema: Transações duplicadas
1. Verifique se idempotência está funcionando
2. Confirme cache em memória
3. Verifique TTL das transações

### Problema: Logs não são gerados
1. Verifique permissões do diretório `logs/`
2. Confirme configuração de logs
3. Verifique níveis de log

## 📞 Suporte

- **Email**: contato@oasispay.com.br
- **Documentação**: Consulte os arquivos de documentação
- **Logs**: Verifique logs em `logs/` para diagnóstico

## 🎉 Status

✅ **Sistema pronto para produção!**

- Credenciais configuradas
- Testes passando
- Monitoramento ativo
- Documentação completa

---

**Desenvolvido com ❤️ para integração PIX Oasy.fy**
