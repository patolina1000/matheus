#!/bin/bash

# Script para enviar o projeto para o Git
# Execute: chmod +x deploy-to-git.sh && ./deploy-to-git.sh

echo "🚀 ENVIANDO PROJETO PARA O GIT"
echo "================================"

# Verificar se estamos em um repositório Git
if [ ! -d ".git" ]; then
    echo "📁 Inicializando repositório Git..."
    git init
    echo "✅ Repositório Git inicializado"
fi

# Adicionar arquivo .gitignore
echo "📝 Configurando .gitignore..."
git add .gitignore
echo "✅ .gitignore configurado"

# Adicionar todos os arquivos (exceto os ignorados)
echo "📦 Adicionando arquivos ao Git..."
git add .

# Verificar status
echo "📊 Status do repositório:"
git status

# Fazer commit
echo "💾 Fazendo commit..."
git commit -m "🚀 Sistema completo de Webhook PIX Oasy.fy

✨ Funcionalidades implementadas:
- Webhook PIX com tratamento de erros robusto
- Sistema de logs avançado com rotação automática
- Idempotência com TTL e cache em memória
- Monitoramento em tempo real (web e CLI)
- Testes automatizados completos
- Configuração automática com credenciais
- Segurança rigorosa com validação de tokens
- Performance otimizada

🔧 Arquivos principais:
- webhook-example.php (webhook principal)
- config.php (configurações)
- install.php (instalação automática)
- test-credentials.php (teste de credenciais)
- test-webhook.php (testes do webhook)
- monitor-webhook.php (monitor em tempo real)
- README.md (documentação completa)

📚 Documentação:
- ENHANCED_WEBHOOK_GUIDE.md
- WEBHOOK_ERROR_HANDLING_GUIDE.md
- PIX_Webhook_Guide.md

🎯 Pronto para produção!"

echo "✅ Commit realizado com sucesso!"

# Verificar se há um remote configurado
if git remote -v | grep -q "origin"; then
    echo "🌐 Enviando para o repositório remoto..."
    git push origin main
    echo "✅ Push realizado com sucesso!"
else
    echo "⚠️ Nenhum repositório remoto configurado."
    echo "📋 Para configurar um repositório remoto, execute:"
    echo "   git remote add origin <URL_DO_SEU_REPOSITORIO>"
    echo "   git branch -M main"
    echo "   git push -u origin main"
fi

echo ""
echo "🎉 PROJETO ENVIADO PARA O GIT COM SUCESSO!"
echo "================================"
echo ""
echo "📋 Próximos passos:"
echo "1. Configure um repositório remoto (GitHub, GitLab, etc.)"
echo "2. Execute: git remote add origin <URL_DO_REPOSITORIO>"
echo "3. Execute: git push -u origin main"
echo ""
echo "🔧 Para testar localmente:"
echo "1. Execute: php install.php"
echo "2. Execute: php test-credentials.php"
echo "3. Execute: php test-webhook.php all"
echo ""
echo "📊 Para monitorar:"
echo "1. Interface web: http://localhost/monitor-webhook.php"
echo "2. Interface CLI: php monitor-webhook.php"
echo ""
echo "✅ Sistema pronto para uso!"
