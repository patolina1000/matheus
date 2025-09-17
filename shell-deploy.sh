#!/bin/bash

# Script simplificado para shell/terminal
# Funciona em qualquer sistema com Git e PHP

echo "🚀 DEPLOY WEBHOOK PIX OASY.FY - SHELL"
echo "======================================"

# Verificar se Git está instalado
if ! command -v git &> /dev/null; then
    echo "❌ Git não está instalado. Instale o Git primeiro."
    exit 1
fi

# Verificar se PHP está instalado
if ! command -v php &> /dev/null; then
    echo "❌ PHP não está instalado. Instale o PHP primeiro."
    exit 1
fi

echo "✅ Git e PHP encontrados"

# Inicializar Git se necessário
if [ ! -d ".git" ]; then
    echo "📁 Inicializando repositório Git..."
    git init
    echo "✅ Repositório Git inicializado"
fi

# Configurar Git se necessário
if [ -z "$(git config user.name)" ]; then
    echo "🔧 Configurando Git..."
    git config user.name "Webhook PIX User"
    git config user.email "webhook@example.com"
    echo "✅ Git configurado"
fi

# Adicionar arquivos
echo "📦 Adicionando arquivos ao Git..."
git add .

# Verificar se há mudanças
if git diff --staged --quiet; then
    echo "ℹ️ Nenhuma mudança para commitar"
else
    # Fazer commit
    echo "💾 Fazendo commit..."
    git commit -m "🚀 Sistema Webhook PIX Oasy.fy

✨ Funcionalidades:
- Webhook PIX com tratamento de erros
- Logs avançados com rotação
- Idempotência com TTL e cache
- Monitoramento em tempo real
- Testes automatizados
- Segurança rigorosa

🔧 Arquivos principais:
- webhook-example.php
- install.php
- test-credentials.php
- test-webhook.php
- monitor-webhook.php

🎯 Pronto para produção!"
    
    echo "✅ Commit realizado"
fi

# Verificar remote
if git remote | grep -q "origin"; then
    echo "🌐 Enviando para repositório remoto..."
    git push origin main 2>/dev/null || git push origin master 2>/dev/null
    echo "✅ Push realizado"
else
    echo "⚠️ Nenhum repositório remoto configurado"
    echo ""
    echo "📋 Para configurar um repositório remoto:"
    echo "   git remote add origin <URL_DO_REPOSITORIO>"
    echo "   git branch -M main"
    echo "   git push -u origin main"
    echo ""
    echo "💡 Exemplo:"
    echo "   git remote add origin https://github.com/usuario/repo.git"
fi

echo ""
echo "🎉 DEPLOY CONCLUÍDO!"
echo "==================="
echo ""
echo "🔧 Para testar o sistema:"
echo "   php install.php"
echo "   php test-credentials.php"
echo "   php test-webhook.php all"
echo ""
echo "📊 Para monitorar:"
echo "   php monitor-webhook.php"
echo ""
echo "✅ Sistema pronto para uso!"
