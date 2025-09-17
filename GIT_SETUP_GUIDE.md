# 🚀 Guia de Configuração Git - Webhook PIX Oasy.fy

## 📋 Pré-requisitos

- Git instalado no seu sistema
- Conta no GitHub, GitLab ou outro serviço Git
- PHP instalado para testes locais

## 🔧 Configuração Inicial

### 1. Inicializar Repositório Git

```bash
# Inicializar repositório Git
git init

# Configurar usuário (se ainda não configurado)
git config --global user.name "Seu Nome"
git config --global user.email "seu.email@exemplo.com"
```

### 2. Executar Script de Deploy

#### **Windows:**
```cmd
deploy-to-git.bat
```

#### **Linux/Mac:**
```bash
chmod +x deploy-to-git.sh
./deploy-to-git.sh
```

### 3. Configurar Repositório Remoto

```bash
# Adicionar repositório remoto (substitua pela URL do seu repositório)
git remote add origin https://github.com/seu-usuario/webhook-pix-oasyfy.git

# Renomear branch para main
git branch -M main

# Fazer push inicial
git push -u origin main
```

## 📁 Estrutura do Projeto

```
webhook-pix-oasyfy/
├── .gitignore                 # Arquivos ignorados pelo Git
├── .htaccess                  # Configurações de segurança
├── README.md                  # Documentação principal
├── GIT_SETUP_GUIDE.md         # Este guia
├── ENHANCED_WEBHOOK_GUIDE.md  # Guia avançado
├── WEBHOOK_ERROR_HANDLING_GUIDE.md # Tratamento de erros
├── PIX_Webhook_Guide.md       # Guia original PIX
├── webhook-example.php        # Webhook principal
├── config.php                 # Configurações (não versionado)
├── install.php                # Script de instalação
├── test-credentials.php       # Teste de credenciais
├── test-webhook.php           # Testes do webhook
├── monitor-webhook.php        # Monitor em tempo real
├── deploy-to-git.sh           # Script de deploy (Linux/Mac)
├── deploy-to-git.bat          # Script de deploy (Windows)
├── js/                        # JavaScript
│   ├── config.js              # Configurações JS
│   ├── pix-integration.js     # Integração PIX
│   └── ...
├── css/                       # Estilos
├── images/                    # Imagens
├── fonts/                     # Fontes
└── media/                     # Mídia
```

## 🔒 Arquivos Ignorados pelo Git

O arquivo `.gitignore` está configurado para ignorar:

- **Logs**: `logs/*.log`, `logs/*.txt`
- **Dados sensíveis**: `data/*.json`, `data/*.txt`
- **Configurações**: `config.php` (contém credenciais)
- **Arquivos temporários**: `*.tmp`, `*.temp`, `*.bak`
- **Arquivos do sistema**: `.DS_Store`, `Thumbs.db`
- **Cache**: `cache/`, `tmp/`
- **Uploads**: `uploads/`

## 🚀 Comandos Git Úteis

### Comandos Básicos
```bash
# Ver status
git status

# Adicionar arquivos
git add .
git add arquivo.php

# Fazer commit
git commit -m "Mensagem do commit"

# Ver histórico
git log --oneline

# Ver diferenças
git diff
```

### Comandos de Branch
```bash
# Criar nova branch
git checkout -b nova-funcionalidade

# Trocar de branch
git checkout main

# Listar branches
git branch

# Fazer merge
git merge nova-funcionalidade
```

### Comandos de Remote
```bash
# Ver remotes
git remote -v

# Adicionar remote
git remote add origin https://github.com/usuario/repo.git

# Fazer push
git push origin main

# Fazer pull
git pull origin main
```

## 🔧 Configuração de Produção

### 1. Clonar Repositório
```bash
git clone https://github.com/seu-usuario/webhook-pix-oasyfy.git
cd webhook-pix-oasyfy
```

### 2. Instalar Dependências
```bash
# Executar instalação automática
php install.php
```

### 3. Configurar Webhook
```bash
# Testar credenciais
php test-credentials.php

# Testar webhook
php test-webhook.php all
```

### 4. Configurar na Oasy.fy
```
URL do Webhook: https://seusite.com/webhook-example.php
```

## 📊 Monitoramento

### Interface Web
```
https://seusite.com/monitor-webhook.php
```

### Interface CLI
```bash
php monitor-webhook.php
```

## 🔄 Workflow de Desenvolvimento

### 1. Fazer Mudanças
```bash
# Editar arquivos
# Testar localmente
php test-webhook.php all
```

### 2. Commit e Push
```bash
# Adicionar mudanças
git add .

# Fazer commit
git commit -m "Descrição das mudanças"

# Fazer push
git push origin main
```

### 3. Deploy em Produção
```bash
# No servidor de produção
git pull origin main
php install.php
```

## 🛠️ Solução de Problemas

### Problema: Arquivo não está sendo ignorado
```bash
# Remover arquivo do cache do Git
git rm --cached arquivo.php

# Adicionar ao .gitignore
echo "arquivo.php" >> .gitignore

# Fazer commit
git add .gitignore
git commit -m "Adicionar arquivo ao .gitignore"
```

### Problema: Credenciais expostas
```bash
# Remover arquivo do histórico
git filter-branch --force --index-filter \
'git rm --cached --ignore-unmatch config.php' \
--prune-empty --tag-name-filter cat -- --all

# Fazer push forçado
git push origin --force --all
```

### Problema: Conflitos de merge
```bash
# Ver conflitos
git status

# Resolver conflitos manualmente
# Adicionar arquivos resolvidos
git add .

# Fazer commit
git commit -m "Resolver conflitos"
```

## 📚 Recursos Adicionais

### Documentação
- [Git Documentation](https://git-scm.com/doc)
- [GitHub Docs](https://docs.github.com/)
- [GitLab Docs](https://docs.gitlab.com/)

### Tutoriais
- [Git Tutorial](https://www.atlassian.com/git/tutorials)
- [GitHub Tutorial](https://guides.github.com/)

## 🎯 Checklist de Deploy

- [ ] Repositório Git inicializado
- [ ] Arquivo `.gitignore` configurado
- [ ] Script de deploy executado
- [ ] Repositório remoto configurado
- [ ] Push inicial realizado
- [ ] Testes locais passando
- [ ] Webhook configurado na Oasy.fy
- [ ] Monitoramento funcionando

## 🚀 Próximos Passos

1. **Configurar CI/CD** (GitHub Actions, GitLab CI)
2. **Configurar monitoramento** (Uptime, Logs)
3. **Configurar backup** automático
4. **Configurar alertas** por email/SMS
5. **Configurar SSL** para produção

---

**Sistema pronto para deploy! 🎉**
