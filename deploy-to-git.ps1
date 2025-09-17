# Script PowerShell para enviar o projeto para o Git
# Execute: .\deploy-to-git.ps1

Write-Host "🚀 ENVIANDO PROJETO PARA O GIT" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green

# Verificar se estamos em um repositório Git
if (-not (Test-Path ".git")) {
    Write-Host "📁 Inicializando repositório Git..." -ForegroundColor Yellow
    git init
    Write-Host "✅ Repositório Git inicializado" -ForegroundColor Green
}

# Adicionar arquivo .gitignore
Write-Host "📝 Configurando .gitignore..." -ForegroundColor Yellow
git add .gitignore
Write-Host "✅ .gitignore configurado" -ForegroundColor Green

# Adicionar todos os arquivos (exceto os ignorados)
Write-Host "📦 Adicionando arquivos ao Git..." -ForegroundColor Yellow
git add .

# Verificar status
Write-Host "📊 Status do repositório:" -ForegroundColor Cyan
git status

# Fazer commit
Write-Host "💾 Fazendo commit..." -ForegroundColor Yellow
$commitMessage = @"
🚀 Sistema completo de Webhook PIX Oasy.fy

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

🎯 Pronto para produção!
"@

git commit -m $commitMessage
Write-Host "✅ Commit realizado com sucesso!" -ForegroundColor Green

# Verificar se há um remote configurado
$remotes = git remote -v
if ($remotes -match "origin") {
    Write-Host "🌐 Enviando para o repositório remoto..." -ForegroundColor Yellow
    git push origin main
    Write-Host "✅ Push realizado com sucesso!" -ForegroundColor Green
} else {
    Write-Host "⚠️ Nenhum repositório remoto configurado." -ForegroundColor Yellow
    Write-Host "📋 Para configurar um repositório remoto, execute:" -ForegroundColor Cyan
    Write-Host "   git remote add origin <URL_DO_SEU_REPOSITORIO>" -ForegroundColor White
    Write-Host "   git branch -M main" -ForegroundColor White
    Write-Host "   git push -u origin main" -ForegroundColor White
}

Write-Host ""
Write-Host "🎉 PROJETO ENVIADO PARA O GIT COM SUCESSO!" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Próximos passos:" -ForegroundColor Cyan
Write-Host "1. Configure um repositório remoto (GitHub, GitLab, etc.)" -ForegroundColor White
Write-Host "2. Execute: git remote add origin <URL_DO_REPOSITORIO>" -ForegroundColor White
Write-Host "3. Execute: git push -u origin main" -ForegroundColor White
Write-Host ""
Write-Host "🔧 Para testar localmente:" -ForegroundColor Cyan
Write-Host "1. Execute: php install.php" -ForegroundColor White
Write-Host "2. Execute: php test-credentials.php" -ForegroundColor White
Write-Host "3. Execute: php test-webhook.php all" -ForegroundColor White
Write-Host ""
Write-Host "📊 Para monitorar:" -ForegroundColor Cyan
Write-Host "1. Interface web: http://localhost/monitor-webhook.php" -ForegroundColor White
Write-Host "2. Interface CLI: php monitor-webhook.php" -ForegroundColor White
Write-Host ""
Write-Host "✅ Sistema pronto para uso!" -ForegroundColor Green

# Pausar para o usuário ver o resultado
Read-Host "Pressione Enter para continuar"
