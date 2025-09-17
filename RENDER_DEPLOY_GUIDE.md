# 🚀 Guia de Deploy no Render - Sistema PIX Webhook

## ❌ Problema Resolvido

O erro `npm install` estava ocorrendo porque o Render estava configurado para um projeto Node.js, mas seu projeto é **PHP puro**.

## ✅ Solução Implementada

Criei os seguintes arquivos para configurar corretamente o deploy:

### 1. `render.yaml` - Configuração Principal
```yaml
services:
  - type: web
    name: checkout-pix-webhook
    env: php
    buildCommand: |
      echo "Configurando ambiente PHP..."
      php -v
      echo "Verificando arquivos..."
      ls -la
      echo "Criando diretórios necessários..."
      mkdir -p logs data docs
      chmod 755 logs data
      echo "Build concluído!"
    startCommand: php -S 0.0.0.0:$PORT -t .
    envVars:
      - key: PHP_VERSION
        value: 8.1
      - key: WEBHOOK_URL
        value: https://checkout-pix-webhook.onrender.com/webhook-example.php
      - key: ENVIRONMENT
        value: production
    healthCheckPath: /index.html
    plan: free
```

### 2. `composer.json` - Metadados do Projeto
- Define o projeto como PHP
- Especifica versão mínima PHP 7.4
- Inclui scripts de teste

### 3. `.htaccess` - Configurações Apache
- Headers de segurança
- Configurações PHP
- Proteção de arquivos sensíveis

### 4. `Dockerfile` - Alternativa Docker
- Configuração completa para deploy via Docker
- PHP 8.1 com Apache
- Extensões necessárias

## 🔧 Como Fazer o Deploy

### Opção 1: Deploy Automático (Recomendado)
1. Faça commit dos novos arquivos:
```bash
git add .
git commit -m "Configuração para deploy PHP no Render"
git push origin main
```

2. No Render:
   - Vá para seu projeto
   - Clique em "Manual Deploy" → "Deploy latest commit"
   - O Render detectará automaticamente o `render.yaml`

### Opção 2: Configuração Manual
1. No Render Dashboard:
   - **Environment**: PHP
   - **Build Command**: `echo "No build required for PHP"`
   - **Start Command**: `php -S 0.0.0.0:$PORT -t .`
   - **Health Check Path**: `/index.html`

### Opção 3: Deploy via Docker
1. No Render:
   - **Environment**: Docker
   - O Render usará automaticamente o `Dockerfile`

## 🌐 URLs Importantes

Após o deploy, suas URLs serão:
- **Site Principal**: `https://checkout-pix-webhook.onrender.com`
- **Webhook PIX**: `https://checkout-pix-webhook.onrender.com/webhook-example.php`
- **Monitor**: `https://checkout-pix-webhook.onrender.com/monitor-webhook.php`
- **Teste**: `https://checkout-pix-webhook.onrender.com/test-webhook.php`

## ⚙️ Configuração na Oasy.fy

Configure o webhook na Oasy.fy com:
```
https://checkout-pix-webhook.onrender.com/webhook-example.php
```

## 🔍 Verificação Pós-Deploy

1. **Teste de Conectividade**:
```bash
curl https://checkout-pix-webhook.onrender.com/test-credentials.php
```

2. **Teste do Webhook**:
```bash
curl https://checkout-pix-webhook.onrender.com/test-webhook.php
```

3. **Monitoramento**:
Acesse: `https://checkout-pix-webhook.onrender.com/monitor-webhook.php`

## 🚨 Troubleshooting

### Se ainda der erro de npm:
1. Verifique se o `render.yaml` está na raiz do projeto
2. Confirme que o ambiente está configurado como "PHP"
3. Verifique se não há `package.json` no projeto

### Se o PHP não funcionar:
1. Verifique se o `startCommand` está correto
2. Confirme se a porta `$PORT` está sendo usada
3. Verifique os logs do Render

### Se os diretórios não forem criados:
1. O `buildCommand` criará automaticamente `logs/`, `data/` e `docs/`
2. Verifique as permissões nos logs do deploy

## 📊 Monitoramento

Após o deploy, monitore:
- Logs do Render Dashboard
- Logs do webhook em `/logs/`
- Interface de monitoramento em `/monitor-webhook.php`

## 🎉 Próximos Passos

1. ✅ Deploy configurado
2. 🔄 Fazer commit e push
3. 🚀 Deploy no Render
4. ⚙️ Configurar webhook na Oasy.fy
5. 🧪 Testar funcionamento
6. 📊 Monitorar logs

---

**Problema resolvido!** Seu projeto PHP agora está configurado corretamente para o Render. 🎯
