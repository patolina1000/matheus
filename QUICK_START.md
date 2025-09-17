# 🚀 Quick Start - Webhook PIX Oasy.fy

## ⚡ Instalação Rápida

### 1. Enviar para Git
```bash
# Windows (PowerShell)
.\deploy-to-git.ps1

# Windows (CMD)
deploy-to-git.bat

# Linux/Mac
chmod +x deploy-to-git.sh
./deploy-to-git.sh
```

### 2. Configurar Repositório Remoto
```bash
# Adicionar repositório remoto
git remote add origin https://github.com/seu-usuario/webhook-pix-oasyfy.git

# Fazer push
git push -u origin main
```

### 3. Instalar e Testar
```bash
# Instalação automática
php install.php

# Testar credenciais
php test-credentials.php

# Testar webhook
php test-webhook.php all
```

## 🔧 Configuração Rápida

### Suas Credenciais (já configuradas)
- **Chave Pública**: `kevinmatheus986_a1k8td90862zf2d3`
- **Chave Privada**: `h7gchnycerdys7ty517bspdh2o0inye1cbf97erk8i9421m101zekt389tn83fak`

### URL do Webhook
Configure na Oasy.fy:
```
https://seusite.com/webhook-example.php
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

## 🧪 Testes

```bash
# Todos os testes
php test-webhook.php all

# Testes específicos
php test-webhook.php idempotency
php test-webhook.php performance
php test-webhook.php logs
```

## 📁 Arquivos Principais

- `webhook-example.php` - Webhook principal
- `config.php` - Configurações (não versionado)
- `install.php` - Instalação automática
- `test-credentials.php` - Teste de credenciais
- `test-webhook.php` - Testes do webhook
- `monitor-webhook.php` - Monitor em tempo real

## 🎯 Pronto!

Sistema completo com:
- ✅ Logs avançados
- ✅ Idempotência robusta
- ✅ Monitoramento em tempo real
- ✅ Testes automatizados
- ✅ Segurança rigorosa
- ✅ Performance otimizada

**Execute `php install.php` para começar!** 🚀
