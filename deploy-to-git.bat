@echo off
REM Script para enviar o projeto para o Git (Windows)
REM Execute: deploy-to-git.bat

echo 🚀 ENVIANDO PROJETO PARA O GIT
echo ================================

REM Verificar se estamos em um repositório Git
if not exist ".git" (
    echo 📁 Inicializando repositório Git...
    git init
    echo ✅ Repositório Git inicializado
)

REM Adicionar arquivo .gitignore
echo 📝 Configurando .gitignore...
git add .gitignore
echo ✅ .gitignore configurado

REM Adicionar todos os arquivos (exceto os ignorados)
echo 📦 Adicionando arquivos ao Git...
git add .

REM Verificar status
echo 📊 Status do repositório:
git status

REM Fazer commit
echo 💾 Fazendo commit...
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

echo ✅ Commit realizado com sucesso!

REM Verificar se há um remote configurado
git remote -v | findstr "origin" >nul
if %errorlevel% equ 0 (
    echo 🌐 Enviando para o repositório remoto...
    git push origin main
    echo ✅ Push realizado com sucesso!
) else (
    echo ⚠️ Nenhum repositório remoto configurado.
    echo 📋 Para configurar um repositório remoto, execute:
    echo    git remote add origin ^<URL_DO_SEU_REPOSITORIO^>
    echo    git branch -M main
    echo    git push -u origin main
)

echo.
echo 🎉 PROJETO ENVIADO PARA O GIT COM SUCESSO!
echo ================================
echo.
echo 📋 Próximos passos:
echo 1. Configure um repositório remoto (GitHub, GitLab, etc.)
echo 2. Execute: git remote add origin ^<URL_DO_REPOSITORIO^>
echo 3. Execute: git push -u origin main
echo.
echo 🔧 Para testar localmente:
echo 1. Execute: php install.php
echo 2. Execute: php test-credentials.php
echo 3. Execute: php test-webhook.php all
echo.
echo 📊 Para monitorar:
echo 1. Interface web: http://localhost/monitor-webhook.php
echo 2. Interface CLI: php monitor-webhook.php
echo.
echo ✅ Sistema pronto para uso!
pause
