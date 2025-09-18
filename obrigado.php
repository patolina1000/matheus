<?php
// Configuração de redirecionamento
$redirect_url = 'https://google.com'; // URL padrão

// Tentar carregar configurações do config.php
if (file_exists('config.php')) {
    require_once 'config.php';
    if (defined('REDIRECT_URL')) {
        $redirect_url = REDIRECT_URL;
    }
} else {
    // Se config.php não existir, tentar carregar variáveis de ambiente diretamente
    $redirect_url = $_ENV['REDIRECT_URL'] ?? 'https://google.com';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obrigado pelo pagamento</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/core.css">
    <style>
        body { 
            background: #f7f9fc; 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .thankyou-card { 
            max-width: 500px; 
            margin: 20vh auto; 
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .logo {
            height: 60px;
            margin-bottom: 30px;
        }
        .title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .message {
            color: #7f8c8d;
            font-size: 16px;
            line-height: 1.5;
        }
        .redirect-info {
            margin-top: 30px;
            padding: 15px;
            background: #e8f5e8;
            border-radius: 8px;
            color: #27ae60;
            font-size: 14px;
        }
        .debug-info {
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
    <link rel="icon" href="/images/logo.png">
    <meta http-equiv="Cache-Control" content="no-store" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
</head>
<body>
    <div class="container">
        <div class="card shadow thankyou-card">
            <div class="card-body text-center p-5">
                <img src="/images/logo.svg" alt="Logo" class="logo"/>
                <h2 class="title">Pagamento confirmado!</h2>
                <p class="message">Obrigado pela sua compra. Seu pagamento via PIX foi aprovado.</p>
                
                <div class="redirect-info">
                    <i class="fas fa-clock"></i> Você será redirecionado automaticamente em <span id="countdown">5</span> segundos...
                </div>
                
                <div class="debug-info">
                    <strong>Debug:</strong> Redirecionando para: <?php echo $redirect_url; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // URL de redirecionamento
        const redirectUrl = '<?php echo $redirect_url; ?>';
        
        // Contador regressivo
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        
        console.log('URL de redirecionamento:', redirectUrl);
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                console.log('Redirecionando para:', redirectUrl);
                window.location.href = redirectUrl;
            }
        }, 1000);
        
        // Permitir redirecionamento imediato ao clicar
        document.addEventListener('click', function() {
            clearInterval(timer);
            console.log('Redirecionamento imediato para:', redirectUrl);
            window.location.href = redirectUrl;
        });
    </script>
</body>
</html>


