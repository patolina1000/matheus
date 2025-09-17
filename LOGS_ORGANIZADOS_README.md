# Sistema de Logs Organizados - Solução Simples

## 🎯 Problema Resolvido

Seus logs estavam bagunçados e difíceis de ler. Agora você tem um sistema organizado e fácil de usar!

## 📁 Arquivos Criados

### 1. `simple-logger.php`
- **Sistema principal de logs**
- Logs organizados por tipo e data
- Formato limpo e legível
- Rotação automática de logs

### 2. `log-viewer.php`
- **Interface web para visualizar logs**
- Filtros por tipo de log
- Estatísticas em tempo real
- Auto-refresh a cada 30 segundos

### 3. `log-cleanup.php`
- **API para limpeza de logs antigos**
- Remove logs com mais de 7 dias
- Mantém sistema limpo

### 4. `log-example.php`
- **Exemplo de uso do sistema**
- Demonstra todos os tipos de log
- Teste rápido do sistema

## 🚀 Como Usar

### 1. Visualizar Logs
```
http://seusite.com/log-viewer.php
```

### 2. Testar o Sistema
```
http://seusite.com/log-example.php
```

### 3. Limpar Logs Antigos
- Use o botão "Limpar" no visualizador
- Ou acesse diretamente: `log-cleanup.php`

## 📊 Tipos de Logs

| Tipo | Cor | Descrição |
|------|-----|-----------|
| **INFO** | Azul | Informações gerais |
| **SUCCESS** | Verde | Operações bem-sucedidas |
| **WARNING** | Amarelo | Avisos importantes |
| **ERROR** | Vermelho | Erros do sistema |
| **PIX** | Verde | Operações PIX |
| **WEBHOOK** | Laranja | Webhooks recebidos |
| **REQUEST** | Azul claro | Requisições HTTP |
| **RESPONSE** | Roxo | Respostas HTTP |
| **DEBUG** | Cinza | Informações de debug |

## 🔧 Integração com Seus Arquivos

### No `api-proxy.php`:
```php
require_once 'simple-logger.php';

// Em vez de:
error_log('Dados enviados para API: ' . json_encode($postData));

// Use:
SimpleLogger::pix('REQUEST', 'Dados enviados para API Oasy.fy', [
    'endpoint' => $endpoint,
    'data_size' => strlen(json_encode($postData))
]);
```

### No `webhook-example.php`:
```php
require_once 'simple-logger.php';

// Em vez de logs complexos, use:
SimpleLogger::webhook('RECEIVED', 'Webhook recebido', [
    'event' => $webhookData['event'],
    'transaction_id' => $webhookData['transaction']['id']
]);
```

## 📈 Benefícios

### ✅ Antes (Bagunçado):
```
17 11:57:53 AM 91blm 10.229.169.34:10000 GET /css/fontisto-brands.min.css HTTP/1.1 200 3216 https://matheus-39wu.onrender.com/ Mozilla/5.0...
[php:notice] [pid 20:tid 20] [client 10.229.154.4:60734] Dados enviados para API: {\n "identifier": "pedido-1758121112908",\n "client": {\n "name": "Ana Costa dos Santos"...
```

### ✅ Agora (Organizado):
```
[2025-01-17 11:57:53] [INFO] [10.229.169.34] [req_65a8b2c1d4e5f] Sistema iniciado
[2025-01-17 11:57:54] [PIX] [10.229.169.34] [req_65a8b2c1d4e5f] PIX gerado para cliente
[2025-01-17 11:57:55] [WEBHOOK] [10.229.169.34] [req_65a8b2c1d4e5f] Pagamento confirmado
```

## 🎨 Interface Visual

- **Dashboard com estatísticas**
- **Filtros por tipo de log**
- **Cores diferentes para cada tipo**
- **Auto-refresh automático**
- **Responsivo para mobile**

## 🔄 Rotação Automática

- Logs são organizados por data
- Arquivo diário: `logs/app_2025-01-17.log`
- Limpeza automática após 7 dias
- Mantém sistema sempre limpo

## 🛠️ Personalização

### Alterar diretório de logs:
```php
// Em simple-logger.php
private static $logDir = 'meus-logs/';
```

### Alterar período de retenção:
```php
// Em simple-logger.php, método cleanup()
$cutoff = time() - (30 * 24 * 3600); // 30 dias
```

### Adicionar novos tipos de log:
```php
// Em simple-logger.php
public static function custom($message, $data = []) {
    self::log('CUSTOM', $message, $data);
}
```

## 📱 Acesso Mobile

O visualizador é totalmente responsivo e funciona perfeitamente em:
- 📱 Smartphones
- 📱 Tablets
- 💻 Desktops

## 🔒 Segurança

- Logs não expõem informações sensíveis
- Limpeza automática de dados antigos
- Controle de acesso via web server

## 🎯 Próximos Passos

1. **Teste o sistema**: Acesse `log-example.php`
2. **Visualize os logs**: Acesse `log-viewer.php`
3. **Integre nos seus arquivos**: Substitua `error_log()` por `SimpleLogger::`
4. **Configure limpeza**: Ajuste período de retenção se necessário

## 💡 Dicas

- Use filtros para encontrar logs específicos rapidamente
- Monitore logs de ERRO para identificar problemas
- Use logs PIX para acompanhar transações
- Configure auto-refresh para monitoramento em tempo real

---

**🎉 Seus logs agora estão organizados e fáceis de usar!**
