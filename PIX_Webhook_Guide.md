# Guia Simplificado: PIX + Webhook - Oasy.fy

## Objetivo
Este guia mostra como implementar apenas duas funcionalidades essenciais:
1. **Gerar cobrança PIX**
2. **Receber notificação via webhook quando o pagamento for confirmado**

---

## 1. Configuração Inicial

### Credenciais
Você precisa de duas chaves da Oasy.fy:
- `x-public-key`: Sua chave pública
- `x-secret-key`: Sua chave privada

**Como obter:**
1. Acesse o painel da Oasy.fy
2. Vá em Integrações > API
3. Clique em "Gerar credenciais"
4. Copie e guarde as chaves (não serão mostradas novamente)

### URL Base
```
https://app.oasyfy.com/api/v1
```

### Teste de Conectividade (Opcional)
Antes de gerar PIX, você pode testar se a API está funcionando:

```javascript
// Teste rápido de conectividade
const response = await fetch('https://app.oasyfy.com/api/v1/ping', {
  method: 'GET',
  headers: {
    'x-public-key': 'SUA_CHAVE_PUBLICA',
    'x-secret-key': 'SUA_CHAVE_PRIVADA'
  }
});

const data = await response.json();
console.log(data.message); // Deve retornar "pong"
```

**Resposta esperada:**
```json
{
  "message": "pong"
}
```

### Códigos de Status HTTP
A API utiliza padrões de resposta HTTP para indicar o sucesso ou falha de operações:

- **200 OK**: A requisição foi bem-sucedida
- **201 Created**: A requisição foi bem-sucedida e um novo recurso foi criado
- **400 Bad Request**: A requisição não foi bem-sucedida devido a erros de validação ou dados inválidos
- **401 Unauthorized**: A requisição não foi bem-sucedida devido a falta de autenticação ou token inválido
- **404 Not Found**: O recurso solicitado não foi encontrado
- **500 Internal Server Error**: Ocorreu um erro interno no servidor

### Boas Práticas de Segurança
- **Segurança de Chaves**: Nunca compartilhe suas chaves privadas. Armazene-as de maneira segura e trate-as como senhas
- **Conexão Segura**: Utilize sempre HTTPS para garantir que suas requisições sejam criptografadas
- **Monitoramento**: Monitore regularmente o acesso à sua API para detectar qualquer atividade suspeita

---

## 2. Gerar Cobrança PIX

### Endpoint
```
POST /gateway/pix/receive
```

### Headers Obrigatórios
```javascript
{
  'x-public-key': 'SUA_CHAVE_PUBLICA',
  'x-secret-key': 'SUA_CHAVE_PRIVADA',
  'Content-Type': 'application/json'
}
```

### Exemplo de Requisição
```javascript
const response = await fetch('https://app.oasyfy.com/api/v1/gateway/pix/receive', {
  method: 'POST',
  headers: {
    'x-public-key': 'SUA_CHAVE_PUBLICA',
    'x-secret-key': 'SUA_CHAVE_PRIVADA',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    "identifier": "pedido-123", // Seu ID único do pedido
    "amount": 100.50, // Valor em reais
    "shippingFee": 10.00, // Frete (opcional)
    "extraFee": 5.00, // Taxas extras (opcional)
    "discount": 15.00, // Desconto (opcional)
    "client": {
      "name": "João da Silva",
      "email": "joao@gmail.com",
      "phone": "(11) 99999-9999",
      "document": "123.456.789-00"
    },
    "products": [ // Lista de produtos (opcional)
      {
        "id": "produto-1",
        "name": "Produto Exemplo",
        "quantity": 1,
        "price": 100.50
      }
    ],
    "splits": [ // Divisão de pagamento (opcional)
      {
        "producerId": "cm1234",
        "amount": 30.00
      }
    ],
    "dueDate": "2025-12-31", // Data de vencimento (opcional, formato YYYY-MM-DD)
    "metadata": { // Metadados personalizados (opcional)
      "campo1": "valor1",
      "campo2": "valor2"
    },
    "callbackUrl": "https://seusite.com/webhook/pix" // URL para receber notificações
  })
});

const data = await response.json();
console.log(data);
```

### Resposta de Sucesso
```json
{
  "transactionId": "clwuwmn4i0007emp9lgn66u1h",
  "status": "OK",
  "fee": 2.50,
  "order": {
    "id": "cm92389asdaskdjkasjdka",
    "url": "https://api-de-pagamentos.com/order/cm92389asdaskdjkasjdka",
    "receiptUrl": "https://api-de-pagamentos.com/order/cm92389asdaskdjkasjdka/receipt"
  },
  "pix": {
    "code": "00020101021126530014BR.GOV.BCB.PIX...",
    "base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABjElEQVR42mNk",
    "image": "https://api.gateway.com/pix/qr/00020101021126530014BR.GOV.BCB.PIX..."
  },
  "details": "Transação criada com sucesso",
  "errorDescription": null
}
```

### O que fazer com a resposta:
- **`pix.code`**: Código PIX para copiar e colar
- **`pix.base64`**: Imagem QR Code em base64
- **`pix.image`**: URL da imagem QR Code
- **`transactionId`**: Guarde este ID para consultas futuras
- **`fee`**: Valor da taxa cobrada pela transação
- **`order.url`**: URL da página do pedido
- **`order.receiptUrl`**: URL do comprovante
- **`status`**: Status da transação (OK, FAILED, PENDING, REJECTED, CANCELED)

---

## 3. Consultar Status da Transação

### Endpoint para Consulta
```
GET /gateway/transactions
```

### Como Consultar
```javascript
// Consultar por ID da transação
const response = await fetch('https://app.oasyfy.com/api/v1/gateway/transactions?id=clwuwmn4i0007emp9lgn66u1h', {
  method: 'GET',
  headers: {
    'x-public-key': 'SUA_CHAVE_PUBLICA',
    'x-secret-key': 'SUA_CHAVE_PRIVADA'
  }
});

// Ou consultar por identificador do cliente
const response2 = await fetch('https://app.oasyfy.com/api/v1/gateway/transactions?clientIdentifier=pedido-123', {
  method: 'GET',
  headers: {
    'x-public-key': 'SUA_CHAVE_PUBLICA',
    'x-secret-key': 'SUA_CHAVE_PRIVADA'
  }
});

const data = await response.json();
console.log('Status:', data.status); // PENDING, COMPLETED, FAILED, etc.
```

### Resposta da Consulta
```json
{
  "id": "clwuwmn4i0007emp9lgn66u1h",
  "clientIdentifier": "pedido-123",
  "status": "COMPLETED",
  "paymentMethod": "PIX",
  "amount": 100.50,
  "payedAt": "2025-09-15T01:31:27.944Z",
  "createdAt": "2025-09-15T01:30:27.944Z",
  "pixInformation": {
    "qrCode": "00020101065465468949845BR.GOV.BCB.PIX...",
    "endToEndId": "E123456789012345678901234567890123456789012345"
  }
}
```

### Quando Usar
- **Verificação manual**: Quando você quer checar o status sem depender do webhook
- **Backup**: Se o webhook falhar, você pode consultar manualmente
- **Debug**: Para verificar se a transação foi criada corretamente

---

## 4. Receber Webhook

### Configuração do Webhook
O webhook é configurado automaticamente quando você envia o `callbackUrl` na requisição do PIX.

### Endpoint do Seu Servidor
Crie um endpoint no seu servidor para receber as notificações:

```javascript
// Exemplo com Express.js
app.post('/webhook/pix', (req, res) => {
  const webhookData = req.body;
  
  // Verificar se o pagamento foi confirmado
  if (webhookData.event === 'TRANSACTION_PAID') {
    console.log('Pagamento confirmado!');
    console.log('ID da transação:', webhookData.transaction.id);
    console.log('Valor pago:', webhookData.transaction.amount);
    console.log('Cliente:', webhookData.client.name);
    
    // Aqui você processa o pagamento confirmado
    // Ex: liberar produto, enviar email, etc.
  }
  
  // Sempre retorne 200 para confirmar recebimento
  res.status(200).json({ received: true });
});
```

### Dados do Webhook
```json
{
  "event": "TRANSACTION_PAID",
  "token": "tbdeizos8f",
  "offerCode": "ABCK181",
  "client": {
    "id": "eep4xpu60s",
    "name": "João da Silva",
    "email": "joao@gmail.com",
    "phone": "(11) 9 8888-7777",
    "cpf": "123.456.789-00",
    "cnpj": null,
    "address": {
      "country": "BR",
      "zipCode": "01304-000",
      "state": "SP",
      "city": "São Paulo",
      "neighborhood": "Consolação",
      "street": "Rua Augusta",
      "number": "6312",
      "complement": "6 andar"
    }
  },
  "transaction": {
    "id": "mdshbx2qli",
    "identifier": "pedido-123",
    "status": "COMPLETED",
    "paymentMethod": "PIX",
    "originalCurrency": "BRL",
    "originalAmount": 100.50,
    "currency": "BRL",
    "exchangeRate": 1,
    "amount": 100.50,
    "createdAt": "2025-09-15T01:30:27.944Z",
    "payedAt": "2025-09-15T01:31:27.944Z",
    "pixInformation": {
      "qrCode": "00020101065465468949845BR.GOV.BCB.PIX...",
      "endToEndId": "E123456789012345678901234567890123456789012345"
    },
    "pixMetadata": {
      "payerDocument": "123.456.789-00",
      "payerName": "João da Silva",
      "payerBankName": "Caixa Economica Federal",
      "payerBankAccount": "12345",
      "payerBankBranch": "12",
      "receiverDocument": "987.654.321-00",
      "receiverName": "Maria da Silva",
      "receiverPixKey": "98765432100",
      "receiverBankName": "Banco do Brasil",
      "receiverBankAccount": "54321",
      "receiverBankBranch": "21"
    }
  },
  "subscription": null,
  "orderItems": [
    {
      "id": "l405zg7e37",
      "price": 100.50,
      "product": {
        "id": "owmlnpmegt",
        "name": "Produto Exemplo",
        "externalId": "produto-1"
      }
    }
  ],
  "trackProps": {
    "utm_source": "facebook",
    "utm_medium": "cpc",
    "utm_campaign": "lancamento",
    "ip": "179.241.195.127",
    "country": "BR",
    "user_agent": "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36"
  }
}
```

### Eventos Possíveis
- **`TRANSACTION_CREATED`**: PIX criado
- **`TRANSACTION_PAID`**: PIX pago ✅
- **`TRANSACTION_CANCELED`**: PIX cancelado
- **`TRANSACTION_REFUNDED`**: PIX estornado

---

## 5. Exemplo Completo

### Frontend (JavaScript)
```javascript
async function gerarPix() {
  try {
    const response = await fetch('https://app.oasyfy.com/api/v1/gateway/pix/receive', {
      method: 'POST',
      headers: {
        'x-public-key': 'SUA_CHAVE_PUBLICA',
        'x-secret-key': 'SUA_CHAVE_PRIVADA',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        "identifier": `pedido-${Date.now()}`,
        "amount": 50.00,
        "client": {
          "name": "Cliente Teste",
          "email": "cliente@teste.com",
          "phone": "(11) 99999-9999",
          "document": "123.456.789-00"
        },
        "callbackUrl": "https://seusite.com/webhook/pix"
      })
    });
    
    const data = await response.json();
    
    if (data.status === 'OK') {
      // Mostrar QR Code para o cliente
      document.getElementById('qr-code').src = data.pix.image;
      document.getElementById('pix-code').value = data.pix.code;
      
      // Salvar transactionId para consultas
      localStorage.setItem('transactionId', data.transactionId);
    }
  } catch (error) {
    console.error('Erro ao gerar PIX:', error);
  }
}
```

### Backend (Node.js + Express)
```javascript
const express = require('express');
const app = express();

app.use(express.json());

// Endpoint para receber webhook
app.post('/webhook/pix', (req, res) => {
  const { event, transaction, client } = req.body;
  
  if (event === 'TRANSACTION_PAID') {
    console.log('🎉 Pagamento confirmado!');
    console.log(`Cliente: ${client.name}`);
    console.log(`Valor: R$ ${transaction.amount}`);
    console.log(`ID: ${transaction.id}`);
    
    // Aqui você processa o pagamento
    // Ex: liberar produto, enviar email, atualizar banco de dados
    processarPagamentoConfirmado(transaction, client);
  }
  
  res.status(200).json({ received: true });
});

function processarPagamentoConfirmado(transaction, client) {
  // Sua lógica aqui
  console.log('Processando pagamento...');
}

app.listen(3000, () => {
  console.log('Servidor rodando na porta 3000');
});
```

---

## 6. Checklist de Implementação

### ✅ Configuração
- [ ] Obter credenciais da Oasy.fy
- [ ] Configurar URL do webhook no seu servidor
- [ ] Testar conectividade com a API

### ✅ Gerar PIX
- [ ] Implementar requisição POST para `/gateway/pix/receive`
- [ ] Incluir headers de autenticação
- [ ] Enviar dados do cliente e valor
- [ ] Configurar `callbackUrl`
- [ ] Exibir QR Code para o cliente

### ✅ Consultar Status
- [ ] Implementar consulta GET para `/gateway/transactions`
- [ ] Usar ID da transação ou identificador do cliente
- [ ] Verificar status da transação

### ✅ Receber Webhook
- [ ] Criar endpoint POST no seu servidor
- [ ] Verificar evento `TRANSACTION_PAID`
- [ ] Processar pagamento confirmado
- [ ] Retornar status 200 para confirmar recebimento

### ✅ Testes
- [ ] Testar geração de PIX
- [ ] Testar consulta de status
- [ ] Testar recebimento de webhook
- [ ] Verificar processamento do pagamento

---

## 7. Informações Importantes

### Cálculo de Valores
A API permite estruturar valores de forma detalhada:
```
Valor Total = (Produtos × Quantidade) + Frete + Taxas Extras - Desconto
```

### Campos Opcionais Úteis
- **`shippingFee`**: Frete da transação
- **`extraFee`**: Taxas extras (ex: parcelamento)
- **`discount`**: Desconto aplicado
- **`products`**: Lista de produtos com preços e quantidades
- **`dueDate`**: Data de vencimento (formato YYYY-MM-DD)
- **`metadata`**: Dados personalizados para rastreamento
- **`splits`**: Divisão de pagamento entre diferentes contas

### Validação de Webhook
O webhook inclui um `token` que pode ser usado para validar a autenticidade da notificação.

### Resposta aos Webhooks
Após processar um webhook, o endpoint do seu sistema deve retornar um status HTTP 2XX para indicar que o webhook foi recebido e processado com sucesso.

### Formato do Objeto de Erro
Quando um erro ocorre, a API retorna um objeto JSON com o seguinte formato:

```json
{
  "statusCode": 400,
  "errorCode": "GATEWAY_INVALID_ARGUMENT",
  "message": "Mensagem detalhada sobre o erro",
  "details": {
    "campo1": "Detalhes sobre o campo 1",
    "campo2": "Detalhes sobre o campo 2"
  }
}
```

### Campos Importantes do Webhook
- **`offerCode`**: Código da oferta (quando é venda no checkout interno)
- **`originalCurrency/originalAmount`**: Moeda e valor originais do cliente
- **`exchangeRate`**: Taxa de câmbio (quando moeda diferente de BRL)
- **`trackProps`**: Dados de rastreamento (UTM, IP, user agent, etc.)
- **`subscription`**: Dados da assinatura (null para pagamentos únicos)

### Status de Transação
- **`COMPLETED`**: Transação concluída
- **`PENDING`**: Transação pendente
- **`FAILED`**: Transação falhou
- **`REFUNDED`**: Transação estornada
- **`CHARGED_BACK`**: Transação com chargeback

### Métodos de Pagamento
- **`PIX`**: Pix
- **`BOLETO`**: Boleto
- **`CREDIT_CARD`**: Cartão
- **`SPLIT`**: Divisão de pagamento
- **`TED`**: Transferência
- **`DYNAMIC`**: Dinâmico (outros métodos)

### Tipo de Transação
- **`ONCE`**: Compra única
- **`RECURRING`**: Assinatura

### Moedas Suportadas
A API suporta as seguintes moedas:

**Moedas Fiat:**
- ARS, BRL, CAD, COP, EUR, GBP, JPY, MXN, MZN, USD, CNY, SAR, BDM

**Criptomoedas:**
- ETH, BNB, BTC, USDT, USDC

### Split de Pagamentos
Nas rotas de pagamento, você pode incluir um campo opcional chamado `splits` para realizar a divisão automática do valor da transação entre diferentes contas.

**Exemplo de Split:**
```json
{
  "splits": [
    {
      "producerId": "cm1234",
      "amount": 30.00
    },
    {
      "producerId": "cm9876",
      "amount": 20.00
    }
  ]
}
```

**Importante**: O somatório dos `amount` dentro de `splits` não pode exceder o valor total da transação.

## 8. Códigos de Erro Comuns

| Código | Descrição | Solução |
|--------|-----------|---------|
| `GATEWAY_UNAUTHORIZED` | Credenciais inválidas | Verificar chaves públicas/privadas |
| `GATEWAY_INVALID_ARGUMENT` | Dados inválidos | Verificar formato dos dados enviados |
| `GATEWAY_NO_BODY` | Corpo da requisição vazio | Verificar se está enviando JSON |
| `GATEWAY_TRANSACTION_DENIED` | Transação negada | Verificar dados do cliente |
| `GATEWAY_INVALID_CREDENTIALS` | Credenciais inválidas | Gerar novas credenciais |
| `GATEWAY_NO_CREDENTIALS` | Credenciais não fornecidas | Incluir headers de autenticação |
| `GATEWAY_TRANSACTION_NOT_FOUND` | Transação não encontrada | Verificar ID da transação |
| `GATEWAY_PERMISSION_DENIED` | Sem permissão | Verificar permissões da conta |
| `GATEWAY_UNAVAILABLE` | Serviço indisponível | Tentar novamente mais tarde |
| `GATEWAY_INTERNAL_SERVER_ERROR` | Erro interno do servidor | Tentar novamente ou contatar suporte |
| `GATEWAY_ROUTE_NOT_FOUND` | Endpoint não encontrado | Verificar URL da requisição |
| `GATEWAY_COMPANY_NOT_FOUND` | Empresa não encontrada | Verificar credenciais |
| `GATEWAY_FAILED_PRECONDITION` | Sistema não está no estado necessário | Verificar condições da operação |
| `GATEWAY_ABORTED` | Operação abortada | Tentar novamente |
| `GATEWAY_OUT_OF_RANGE` | Valor fora do intervalo permitido | Verificar valores enviados |
| `GATEWAY_UNIMPLEMENTED` | Operação não implementada | Verificar se o endpoint existe |
| `GATEWAY_INTERNAL` | Erro interno | Tentar novamente ou contatar suporte |
| `GATEWAY_DATA_LOSS` | Dados perdidos | Contatar suporte imediatamente |
| `GATEWAY_UNAUTHENTICATED` | Requer autenticação | Incluir headers de autenticação |
| `GATEWAY_INVALID_DATA` | Dados inválidos | Verificar formato dos dados |
| `GATEWAY_NO_SPLIT_ACCOUNT` | Conta de split não encontrada | Verificar IDs de split |
| `GATEWAY_NOT_OWNER` | Não é proprietário do recurso | Verificar permissões |

---

## 9. Suporte

- **Email**: contato@oasispay.com.br
- **Documentação completa**: Consulte o arquivo `documentacao_oasyfy_organizada.txt`

---

*Este guia foca apenas no essencial: gerar PIX e receber confirmação via webhook. Para funcionalidades avançadas, consulte a documentação completa.*
