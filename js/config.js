/**
 * Configurações da API Oasy.fy
 * IMPORTANTE: Substitua as credenciais abaixo pelas suas credenciais reais
 * 
 * Como obter as credenciais:
 * 1. Acesse o painel da Oasy.fy
 * 2. Vá em Integrações > API
 * 3. Clique em "Gerar credenciais"
 * 4. Copie e guarde as chaves (não serão mostradas novamente)
 */

const OASYFY_CONFIG = {
    // SUAS CREDENCIAIS DA OASY.FY
    PUBLIC_KEY: 'kevinmatheus986_a1k8td90862zf2d3',
    SECRET_KEY: 'h7gchnycerdys7ty517bspdh2o0inye1cbf97erk8i9421m101zekt389tn83fak',
    
    // URL base da API
    API_BASE_URL: 'https://app.oasyfy.com/api/v1',
    
    // URL do webhook (será configurada automaticamente)
    WEBHOOK_URL: window.location.origin + '/webhook-example.php',
    
    // Configurações do sistema
    SYSTEM_CONFIG: {
        // Dados padrão do cliente (você pode personalizar)
        DEFAULT_CLIENT: {
            name: 'Cliente Privacy',
            email: 'cliente@privacy.com',
            phone: '(11) 99999-9999',
            document: '123.456.789-00'
        },
        
        // Produtos disponíveis
        PRODUCTS: {
            '1-mes': {
                id: 'assinatura-1-mes',
                name: 'Assinatura Privacy - 1 mês',
                price: 19.90
            },
            '3-meses': {
                id: 'assinatura-3-meses',
                name: 'Assinatura Privacy - 3 meses (30% off)',
                price: 41.90
            },
            '6-meses': {
                id: 'assinatura-6-meses',
                name: 'Assinatura Privacy - 6 meses (50% off)',
                price: 59.90
            }
        },
        
        // Metadados padrão
        DEFAULT_METADATA: {
            source: 'privacy-checkout',
            platform: 'web'
        }
    }
};

// Validação das credenciais
function validateCredentials() {
    if (OASYFY_CONFIG.PUBLIC_KEY === 'SUA_CHAVE_PUBLICA_AQUI' || 
        OASYFY_CONFIG.SECRET_KEY === 'SUA_CHAVE_PRIVADA_AQUI') {
        console.warn('⚠️ ATENÇÃO: Credenciais da Oasy.fy não configuradas!');
        console.warn('Configure suas credenciais no arquivo js/config.js');
        return false;
    }
    return true;
}

// Exportar configuração
window.OASYFY_CONFIG = OASYFY_CONFIG;
window.validateCredentials = validateCredentials;
