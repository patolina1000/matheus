# 🐚 Guia para Shell/Terminal - Webhook PIX Oasy.fy

## ✅ PHP Funciona no Shell!

O PHP funciona perfeitamente no shell/terminal. Você pode executar todos os scripts PHP diretamente.

## 🚀 Comandos Básicos

### Verificar se PHP está instalado
```bash
php -v
```

### Executar scripts PHP
```bash
php nome-do-arquivo.php
```

## 📦 Deploy para Git (Shell)

### 1. Tornar o script executável
```bash
chmod +x shell-deploy.sh
```

### 2. Executar o deploy
```bash
./shell-deploy.sh
```

### 3. Configurar repositório remoto
```bash
git remote add origin https://github.com/seu-usuario/repo.git
git branch -M main
git push -u origin main
```

## 🧪 Testes no Shell

### 1. Tornar o script executável
```bash
chmod +x shell-test.sh
```

### 2. Executar testes
```bash
./shell-test.sh
```

### 3. Testes individuais
```bash
# Instalação
php install.php

# Testar credenciais
php test-credentials.php

# Testar webhook
php test-webhook.php all

# Monitorar (modo texto)
php monitor-webhook.php
```

## 🔧 Comandos Úteis no Shell

### Verificar arquivos
```bash
ls -la
ls -la logs/
ls -la data/
```

### Ver logs em tempo real
```bash
tail -f logs/webhook_$(date +%Y-%m).log
tail -f logs/errors_$(date +%Y-%m).log
```

### Verificar status do Git
```bash
git status
git log --oneline
```

### Limpar arquivos temporários
```bash
rm -f *.tmp *.log
```

## 📊 Monitoramento no Shell

### Interface CLI do monitor
```bash
php monitor-webhook.php
```

### Verificar estatísticas
```bash
# Contar logs
wc -l logs/*.log

# Ver último log
tail -1 logs/webhook_$(date +%Y-%m).log

# Ver erros recentes
grep "ERROR" logs/errors_$(date +%Y-%m).log | tail -5
```

## 🛠️ Solução de Problemas

### Problema: "Permission denied"
```bash
chmod +x shell-deploy.sh
chmod +x shell-test.sh
```

### Problema: PHP não encontrado
```bash
# Ubuntu/Debian
sudo apt install php-cli

# CentOS/RHEL
sudo yum install php-cli

# macOS
brew install php
```

### Problema: Git não configurado
```bash
git config --global user.name "Seu Nome"
git config --global user.email "seu@email.com"
```

## 📁 Estrutura de Arquivos

```
webhook-pix-oasyfy/
├── shell-deploy.sh          # Deploy para Git
├── shell-test.sh            # Testes no shell
├── SHELL_GUIDE.md           # Este guia
├── webhook-example.php      # Webhook principal
├── install.php              # Instalação
├── test-credentials.php     # Teste credenciais
├── test-webhook.php         # Testes webhook
├── monitor-webhook.php      # Monitor CLI
├── config.php               # Configurações
├── logs/                    # Logs
└── data/                    # Dados
```

## 🎯 Workflow Completo no Shell

### 1. Deploy inicial
```bash
chmod +x shell-deploy.sh
./shell-deploy.sh
```

### 2. Configurar repositório
```bash
git remote add origin https://github.com/usuario/repo.git
git push -u origin main
```

### 3. Instalar sistema
```bash
php install.php
```

### 4. Testar
```bash
chmod +x shell-test.sh
./shell-test.sh
```

### 5. Monitorar
```bash
php monitor-webhook.php
```

## 🔍 Comandos de Debug

### Verificar configurações
```bash
php -r "require 'config.php'; print_r(getOasyfyConfig());"
```

### Testar conectividade
```bash
php test-credentials.php
```

### Ver logs de erro
```bash
tail -f logs/errors_$(date +%Y-%m).log
```

### Verificar cache
```bash
ls -la data/
cat data/idempotency_cache.json
```

## 📋 Checklist Shell

- [ ] PHP instalado (`php -v`)
- [ ] Git instalado (`git --version`)
- [ ] Scripts executáveis (`chmod +x *.sh`)
- [ ] Deploy realizado (`./shell-deploy.sh`)
- [ ] Repositório configurado
- [ ] Sistema instalado (`php install.php`)
- [ ] Testes passando (`./shell-test.sh`)
- [ ] Monitor funcionando (`php monitor-webhook.php`)

## 🚀 Pronto!

O sistema funciona perfeitamente no shell. Todos os scripts PHP são executáveis via terminal.

**Execute `./shell-deploy.sh` para começar!** 🎉
