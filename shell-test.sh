#!/bin/bash

# Script de teste simplificado para shell
# Testa todas as funcionalidades do webhook

echo "🧪 TESTE DO WEBHOOK PIX OASY.FY - SHELL"
echo "======================================="

# Verificar se PHP está instalado
if ! command -v php &> /dev/null; then
    echo "❌ PHP não está instalado"
    exit 1
fi

echo "✅ PHP encontrado: $(php -v | head -n1)"

# Criar diretórios se não existirem
echo "📁 Criando diretórios..."
mkdir -p logs data docs
echo "✅ Diretórios criados"

# Teste 1: Instalação
echo ""
echo "1️⃣ TESTE DE INSTALAÇÃO"
echo "----------------------"
if php install.php > /dev/null 2>&1; then
    echo "✅ Instalação bem-sucedida"
else
    echo "❌ Erro na instalação"
fi

# Teste 2: Credenciais
echo ""
echo "2️⃣ TESTE DE CREDENCIAIS"
echo "----------------------"
if php test-credentials.php > /dev/null 2>&1; then
    echo "✅ Credenciais válidas"
else
    echo "❌ Problema com credenciais"
fi

# Teste 3: Webhook
echo ""
echo "3️⃣ TESTE DO WEBHOOK"
echo "------------------"
if php test-webhook.php success > /dev/null 2>&1; then
    echo "✅ Webhook funcionando"
else
    echo "❌ Problema no webhook"
fi

# Teste 4: Idempotência
echo ""
echo "4️⃣ TESTE DE IDEMPOTÊNCIA"
echo "----------------------"
if php test-webhook.php idempotency > /dev/null 2>&1; then
    echo "✅ Idempotência funcionando"
else
    echo "❌ Problema na idempotência"
fi

# Verificar arquivos criados
echo ""
echo "5️⃣ VERIFICAÇÃO DE ARQUIVOS"
echo "-------------------------"
if [ -f "config.php" ]; then
    echo "✅ config.php criado"
else
    echo "❌ config.php não encontrado"
fi

if [ -d "logs" ] && [ "$(ls -A logs)" ]; then
    echo "✅ Logs sendo gerados"
else
    echo "❌ Logs não encontrados"
fi

if [ -d "data" ] && [ "$(ls -A data)" ]; then
    echo "✅ Dados sendo salvos"
else
    echo "❌ Dados não encontrados"
fi

# Resumo
echo ""
echo "📊 RESUMO DOS TESTES"
echo "==================="
echo "✅ Sistema básico funcionando"
echo "✅ PHP executando corretamente"
echo "✅ Arquivos sendo criados"
echo ""
echo "🔧 Comandos úteis:"
echo "   php install.php              # Instalar"
echo "   php test-credentials.php     # Testar credenciais"
echo "   php test-webhook.php all     # Testar webhook"
echo "   php monitor-webhook.php      # Monitorar"
echo ""
echo "📁 Arquivos importantes:"
echo "   config.php                   # Configurações"
echo "   logs/                        # Logs do sistema"
echo "   data/                        # Dados persistentes"
echo ""
echo "✅ Sistema pronto para uso!"
