# Sistema de Logs de Webhooks

Este sistema permite visualizar e monitorar todos os logs relacionados a webhooks enviados e recebidos no sistema PIX.

## Arquivos Criados

### 1. `webhook-logs.html`
Interface web para visualizar os logs de webhooks com as seguintes funcionalidades:

- **Visualização em tempo real** dos logs de webhooks
- **Filtros** por tipo (enviados, recebidos, erros, avisos)
- **Filtro por data** para visualizar logs de dias específicos
- **Estatísticas** em tempo real (contadores de webhooks enviados/recebidos/erros)
- **Auto-refresh** configurável (atualização automática a cada 10 segundos)
- **Exportação** de logs em formato JSON
- **Limpeza** de logs antigos
- **Interface responsiva** com Bootstrap

### 2. `webhook-logs-api.php`
API backend que fornece os dados dos logs com as seguintes funcionalidades:

- **Leitura de logs** do sistema existente (`simple-logger.php`)
- **Filtragem** por data, tipo e nível de log
- **Extração de dados** estruturados das mensagens de log
- **Estatísticas** agregadas dos logs
- **Limpeza** de logs antigos (mais de 7 dias)
- **Tratamento de erros** robusto

## Como Usar

### 1. Acessar a Interface
Abra o arquivo `webhook-logs.html` em um navegador web ou acesse via servidor web.

### 2. Visualizar Logs
- A interface carrega automaticamente os logs do dia atual
- Use o filtro de data para visualizar logs de dias específicos
- Use os filtros de tipo para focar em webhooks enviados, recebidos, erros ou avisos

### 3. Funcionalidades Disponíveis

#### Filtros
- **Todos**: Mostra todos os logs de webhooks
- **Enviados**: Mostra apenas webhooks enviados pelo sistema
- **Recebidos**: Mostra apenas webhooks recebidos da Oasy.fy
- **Erros**: Mostra apenas logs de erro
- **Avisos**: Mostra apenas logs de aviso

#### Controles
- **Atualizar**: Recarrega os logs manualmente
- **Auto-refresh**: Liga/desliga a atualização automática
- **Limpar**: Remove logs antigos (mais de 7 dias)
- **Exportar**: Baixa os logs em formato JSON

#### Estatísticas
- **Webhooks Enviados**: Contador de webhooks enviados
- **Webhooks Recebidos**: Contador de webhooks recebidos
- **Erros**: Contador de erros
- **Última Atualização**: Timestamp da última atualização

## Estrutura dos Logs

### Tipos de Log
- **sent**: Webhooks enviados pelo sistema
- **received**: Webhooks recebidos da Oasy.fy
- **error**: Logs de erro
- **warning**: Logs de aviso
- **info**: Logs informativos

### Dados Extraídos
O sistema extrai automaticamente informações das mensagens de log:
- **transaction_id**: ID da transação
- **amount**: Valor da transação
- **email**: Email do cliente
- **event**: Tipo de evento (TRANSACTION_PAID, TRANSACTION_CREATED, etc.)

## Integração com Sistema Existente

O sistema se integra com o `simple-logger.php` existente e lê os logs do diretório `logs/` com o padrão:
- `app_YYYY-MM-DD.log`: Logs do dia específico

### Logs Monitorados
O sistema monitora logs que contenham:
- Palavra "WEBHOOK" ou "webhook"
- Palavra "TRANSACTION"
- Palavra "PIX"

## Configuração

### Auto-refresh
- **Intervalo**: 10 segundos
- **Configurável**: Pode ser ligado/desligado
- **Indicador visual**: Mostra status do auto-refresh

### Limpeza de Logs
- **Retenção**: 7 dias
- **Automática**: Executada via API
- **Manual**: Botão na interface

## Exemplo de Uso

```javascript
// Carregar logs via API
fetch('webhook-logs-api.php?date=2024-01-15&filter=sent&limit=50')
    .then(response => response.json())
    .then(data => {
        console.log('Logs:', data.logs);
        console.log('Estatísticas:', data.stats);
    });
```

## Troubleshooting

### Problemas Comuns

1. **Logs não aparecem**
   - Verifique se o diretório `logs/` existe
   - Verifique se há logs para a data selecionada
   - Verifique se os logs contêm palavras-chave monitoradas

2. **Erro na API**
   - Verifique se o `simple-logger.php` está disponível
   - Verifique permissões de leitura no diretório `logs/`
   - Verifique logs de erro do servidor

3. **Auto-refresh não funciona**
   - Verifique se JavaScript está habilitado
   - Verifique console do navegador para erros
   - Tente recarregar a página

## Segurança

- **CORS**: Configurado para permitir acesso de qualquer origem
- **Validação**: Parâmetros de entrada são validados
- **Sanitização**: Dados de saída são sanitizados
- **Limites**: Limite de 1000 logs por requisição

## Performance

- **Cache**: Logs são lidos do arquivo a cada requisição
- **Limite**: Máximo de 1000 logs por requisição
- **Ordenação**: Logs ordenados por timestamp (mais recente primeiro)
- **Filtragem**: Filtros aplicados no servidor para melhor performance
