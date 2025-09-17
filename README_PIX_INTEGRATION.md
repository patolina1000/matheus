# Integração PIX com Oasy.fy - Privacy Checkout

Este projeto implementa a integração PIX com a API da Oasy.fy para o sistema de checkout da Privacy, baseado no guia `PIX_Webhook_Guide.md`.

## 🚀 Funcionalidades Implementadas

- ✅ Geração de cobrança PIX via API Oasy.fy
- ✅ Interface para exibição de QR Code e código PIX
- ✅ Geração automática de dados do cliente (nome, email, telefone, CPF)
- ✅ Sistema de webhook para notificações de pagamento
- ✅ Redirecionamento para página de pagamento
- ✅ Tratamento de erros e feedback visual

## 📁 Arquivos Criados/Modificados

### Novos Arquivos:
- `js/config.js` - Configurações da API Oasy.fy
- `js/pix-integration.js` - Classe principal de integração PIX
- `js/client-validation.js` - Validação de dados do cliente
- `webhook-example.php` - Exemplo de webhook para receber notificações
- `README_PIX_INTEGRATION.md` - Este arquivo de documentação

### Arquivos Modificados:
- `index.html` - Integração dos scripts e modificação dos botões de pagamento

## ⚙️ Configuração

### 1. Credenciais da Oasy.fy

1. Acesse o painel da Oasy.fy
2. Vá em **Integrações > API**
3. Clique em **"Gerar credenciais"**
4. Copie suas chaves pública e privada

### 2. Configurar Credenciais

Edite o arquivo `js/config.js` e substitua:

```javascript
const OASYFY_CONFIG = {
    PUBLIC_KEY: 'SUA_CHAVE_PUBLICA_AQUI',  // ← Substitua aqui
    SECRET_KEY: 'SUA_CHAVE_PRIVADA_AQUI',  // ← Substitua aqui
    // ... resto das configurações
};
```

### 3. Configurar Webhook

1. **Para desenvolvimento local:**
   - Use ngrok ou similar para expor sua aplicação
   - Configure a URL do webhook no `js/config.js`

2. **Para produção:**
   - Configure o arquivo `webhook-example.php` no seu servidor
   - Ajuste a URL do webhook para seu domínio

## 🔧 Como Usar

### 1. Teste de Conectividade

```javascript
// Testa se a API está funcionando
pixIntegration.testConnection().then(isConnected => {
    if (isConnected) {
        console.log('✅ API conectada com sucesso!');
    } else {
        console.log('❌ Erro na conexão com a API');
    }
});
```

### 2. Gerar Pagamento PIX

Os botões de pagamento já estão configurados para:
- **1 mês**: R$ 19,90
- **3 meses**: R$ 41,90 (30% off)
- **6 meses**: R$ 59,90 (50% off)

### 3. Fluxo do Usuário

1. Usuário clica em um botão de assinatura
2. Sistema gera automaticamente dados aleatórios do cliente (nome, email, telefone, CPF)
3. PIX é gerado via API Oasy.fy com os dados gerados
4. QR Code e código PIX são exibidos no modal
5. Dados do cliente gerados são mostrados no modal
6. Usuário pode copiar o código ou escanear o QR Code
7. Sistema verifica status do pagamento
8. Webhook notifica quando pagamento é confirmado

## 📱 Interface

### Modal de Pagamento PIX
- **Dados do cliente gerados automaticamente** (nome, email, telefone, CPF)
- QR Code para escaneamento
- Campo de código PIX copiável
- Informações da transação
- Botão para verificar status
- Link para página do pedido

## 🔔 Webhook

O arquivo `webhook-example.php` processa as notificações da Oasy.fy:

### Eventos Suportados:
- `TRANSACTION_PAID` - Pagamento confirmado
- `TRANSACTION_CREATED` - PIX criado
- `TRANSACTION_CANCELED` - PIX cancelado
- `TRANSACTION_REFUNDED` - PIX estornado

### Dados Recebidos:
```json
{
    "event": "TRANSACTION_PAID",
    "transaction": {
        "id": "transaction_id",
        "amount": 19.90,
        "status": "COMPLETED"
    },
    "client": {
        "name": "Nome do Cliente",
        "email": "email@exemplo.com"
    }
}
```

## 🛠️ Personalização

### Modificar Valores dos Planos

Edite no `index.html`:

```javascript
$('#btn-1-mes').on('click', function() {
    generatePixPayment(19.90, '1 mês');  // ← Altere o valor aqui
});
```

### Adicionar Novos Planos

1. Adicione o botão no HTML
2. Configure o event handler
3. Adicione o produto em `js/config.js`

### Personalizar Dados do Cliente

Edite em `js/config.js`:

```javascript
DEFAULT_CLIENT: {
    name: 'Cliente Privacy',           // ← Personalize
    email: 'cliente@privacy.com',      // ← Personalize
    phone: '(11) 99999-9999',         // ← Personalize
    document: '123.456.789-00'        // ← Personalize
}
```

## 🐛 Troubleshooting

### Erro: "Credenciais inválidas"
- Verifique se as chaves estão corretas no `js/config.js`
- Confirme se as credenciais foram geradas no painel da Oasy.fy

### Erro: "Configurações não encontradas"
- Certifique-se de que `js/config.js` está sendo carregado antes de `js/pix-integration.js`

### QR Code não aparece
- Verifique se a API retornou `status: 'OK'`
- Confirme se `pix.image` está presente na resposta

### Webhook não recebe notificações
- Verifique se a URL do webhook está acessível
- Confirme se o arquivo PHP está funcionando
- Teste com ferramentas como ngrok para desenvolvimento local

## 📋 Checklist de Implementação

- [ ] Configurar credenciais da Oasy.fy
- [ ] Testar conectividade com a API
- [ ] Configurar webhook no servidor
- [ ] Testar geração de PIX
- [ ] Testar validação de dados do cliente
- [ ] Testar recebimento de webhook
- [ ] Personalizar valores e textos
- [ ] Testar em ambiente de produção

## 🔒 Segurança

### Recomendações:
- Nunca exponha suas chaves privadas no frontend
- Use HTTPS em produção
- Valide todos os dados recebidos via webhook
- Implemente logs de auditoria
- Monitore tentativas de acesso suspeitas

### Para Produção:
- Mova as credenciais para variáveis de ambiente
- Implemente autenticação no webhook
- Use banco de dados para armazenar transações
- Implemente rate limiting

## 📞 Suporte

- **Email**: contato@oasispay.com.br
- **Documentação**: Consulte `PIX_Webhook_Guide.md`
- **Documentação completa**: `documentacao_oasyfy_organizada.txt`

## 📄 Licença

Este projeto é baseado no guia oficial da Oasy.fy e segue suas diretrizes de implementação.

---

**⚠️ Importante**: Este é um sistema de demonstração. Para uso em produção, implemente todas as medidas de segurança necessárias e teste thoroughly em ambiente controlado.
