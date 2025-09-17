#!/bin/bash

# Script super rápido para shell
# Executa tudo em sequência

echo "⚡ QUICK START - SHELL"
echo "====================="

# Verificar PHP
if ! command -v php &> /dev/null; then
    echo "❌ Instale o PHP primeiro: sudo apt install php-cli"
    exit 1
fi

echo "✅ PHP OK"

# Tornar scripts executáveis
chmod +x shell-deploy.sh shell-test.sh 2>/dev/null

# Deploy para Git
echo "🚀 Deploy para Git..."
./shell-deploy.sh

echo ""
echo "📋 Próximos passos:"
echo "1. Configure repositório remoto:"
echo "   git remote add origin <URL>"
echo "   git push -u origin main"
echo ""
echo "2. Instale o sistema:"
echo "   php install.php"
echo ""
echo "3. Teste:"
echo "   ./shell-test.sh"
echo ""
echo "✅ Pronto!"
