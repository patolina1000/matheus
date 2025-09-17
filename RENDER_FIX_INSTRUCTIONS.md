# 🚨 INSTRUÇÕES URGENTES - Corrigir Deploy no Render

## ❌ Problema Atual
O Render ainda está tentando executar `npm install` mesmo com os arquivos de configuração PHP.

## ✅ SOLUÇÃO IMEDIATA

### 1. **ACESSE O RENDER DASHBOARD**
- Vá para: https://dashboard.render.com
- Entre no seu projeto `checkout-pix-webhook`

### 2. **ALTERE AS CONFIGURAÇÕES MANUALMENTE**

No painel do seu serviço, vá em **"Settings"** e configure:

#### **Environment**
- ✅ **Mude para**: `PHP`
- ❌ **NÃO deixe**: `Node` ou `Docker`

#### **Build Command**
```
echo "=== CONFIGURAÇÃO PHP ==="
echo "Verificando ambiente PHP..."
php -v
echo "Verificando arquivos do projeto..."
ls -la
echo "Criando diretórios necessários..."
mkdir -p logs data docs
chmod 755 logs data
echo "=== BUILD CONCLUÍDO ==="
```

#### **Start Command**
```
php -S 0.0.0.0:$PORT -t .
```

#### **Health Check Path**
```
/index.html
```

### 3. **VARIÁVEIS DE AMBIENTE**
Adicione estas variáveis:
- `PHP_VERSION` = `8.1`
- `WEBHOOK_URL` = `https://checkout-pix-webhook.onrender.com/webhook-example.php`
- `ENVIRONMENT` = `production`
- `NODE_ENV` = (deixe vazio)

### 4. **SALVE E FAÇA DEPLOY**
- Clique em **"Save Changes"**
- Clique em **"Manual Deploy"** → **"Deploy latest commit"**

## 🔧 ALTERNATIVA: Deletar e Recriar

Se ainda não funcionar:

### 1. **Delete o serviço atual**
- Vá em Settings → Delete Service

### 2. **Crie um novo serviço**
- Clique em "New" → "Web Service"
- Conecte seu repositório Git
- **IMPORTANTE**: Selecione **"PHP"** como environment
- Configure os comandos acima

## 📋 CHECKLIST DE VERIFICAÇÃO

Antes do deploy, confirme:
- [ ] Environment = PHP
- [ ] Build Command não contém npm
- [ ] Start Command usa php -S
- [ ] Arquivos render.yaml, composer.json, package.json estão no repositório
- [ ] Fez commit e push dos arquivos

## 🚨 SE AINDA DER ERRO

### Opção 1: Deploy via Docker
1. Mude Environment para **"Docker"**
2. O Render usará o `Dockerfile` automaticamente

### Opção 2: Deploy via Static Site
1. Mude Environment para **"Static Site"**
2. Build Command: `echo "Static site"`
3. Publish Directory: `.`

## 📞 SUPORTE RENDER

Se nada funcionar:
1. Acesse: https://render.com/docs
2. Procure por "PHP deployment"
3. Ou contate o suporte do Render

## 🎯 RESULTADO ESPERADO

Após a correção, você deve ver:
```
=== CONFIGURAÇÃO PHP ===
Verificando ambiente PHP...
PHP 8.1.x (cli)...
Verificando arquivos do projeto...
[lista de arquivos]
Criando diretórios necessários...
=== BUILD CONCLUÍDO ===
```

**NÃO deve aparecer**:
- `npm install`
- `node_modules`
- Erros de package.json

---

## ⚡ AÇÃO IMEDIATA NECESSÁRIA

**VÁ AGORA NO RENDER DASHBOARD E ALTERE O ENVIRONMENT PARA PHP!**

O problema está na configuração do serviço, não nos arquivos do projeto.
