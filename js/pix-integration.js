/**
 * Integração PIX com Oasy.fy
 * Baseado no guia PIX_Webhook_Guide.md
 */

class PixIntegration {
    constructor() {
        // Carrega configurações
        this.loadConfig();
        // Controle de polling
        this.activePolls = {};
    }

    loadConfig() {
        // Verifica se as configurações estão disponíveis
        if (typeof OASYFY_CONFIG === 'undefined') {
            console.error('Configurações da Oasy.fy não encontradas! Carregue o arquivo config.js primeiro.');
            return;
        }

        // Configurações da API Oasy.fy
        this.apiBaseUrl = OASYFY_CONFIG.API_BASE_URL;
        this.publicKey = OASYFY_CONFIG.PUBLIC_KEY;
        this.secretKey = OASYFY_CONFIG.SECRET_KEY;
        this.callbackUrl = OASYFY_CONFIG.WEBHOOK_URL;
        
        // Valida credenciais
        if (!validateCredentials()) {
            console.error('Credenciais inválidas! Configure suas chaves no arquivo config.js');
        }
    }

    /**
     * Testa a conectividade com a API
     */
    async testConnection() {
        try {
            const response = await fetch('/api-proxy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'test_connection'
                })
            });

            const data = await response.json();
            console.log('Teste de conectividade:', data.message);
            return data.message === 'pong';
        } catch (error) {
            console.error('Erro no teste de conectividade:', error);
            return false;
        }
    }

    /**
     * Gera dados aleatórios do cliente
     * @returns {Object} - Dados aleatórios do cliente
     */
    generateRandomClientData() {
        // Lista de nomes aleatórios
        const nomes = [
            'João Silva', 'Maria Santos', 'Pedro Oliveira', 'Ana Costa', 'Carlos Ferreira',
            'Lucia Almeida', 'Roberto Lima', 'Fernanda Rocha', 'Marcos Souza', 'Juliana Pereira',
            'Rafael Barbosa', 'Camila Dias', 'Diego Martins', 'Patricia Nunes', 'Thiago Ribeiro',
            'Amanda Correia', 'Bruno Carvalho', 'Larissa Gomes', 'Felipe Araújo', 'Gabriela Lopes'
        ];

        // Lista de sobrenomes adicionais
        const sobrenomes = [
            'da Silva', 'dos Santos', 'de Oliveira', 'da Costa', 'Ferreira',
            'Almeida', 'Lima', 'Rocha', 'Souza', 'Pereira'
        ];

        // Gera nome aleatório
        const nome = nomes[Math.floor(Math.random() * nomes.length)];
        const sobrenome = sobrenomes[Math.floor(Math.random() * sobrenomes.length)];
        const nomeCompleto = `${nome} ${sobrenome}`;

        // Gera email aleatório
        const dominios = ['gmail.com', 'hotmail.com', 'yahoo.com.br', 'outlook.com', 'uol.com.br'];
        // Normaliza nome removendo acentos e convertendo para minúsculas
        const nomeNormalizado = nome.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase();
        // Remove espaços múltiplos e converte para formato de email
        const nomeEmail = nomeNormalizado.replace(/\s+/g, '');
        const dominio = dominios[Math.floor(Math.random() * dominios.length)];
        const email = `${nomeEmail}${Math.floor(Math.random() * 999)}@${dominio}`;

        // Gera telefone aleatório
        const ddd = ['11', '21', '31', '41', '51', '61', '71', '81', '85', '95'];
        const dddAleatorio = ddd[Math.floor(Math.random() * ddd.length)];
        const numero = Math.floor(Math.random() * 900000000) + 100000000;
        const telefone = `(${dddAleatorio}) ${numero.toString().substring(0, 5)}-${numero.toString().substring(5)}`;

        // Gera CPF aleatório válido
        const cpf = this.generateValidCPF();

        return {
            name: nomeCompleto,
            email: email,
            phone: telefone,
            document: cpf
        };
    }

    /**
     * Gera um CPF válido aleatório
     * @returns {string} - CPF formatado
     */
    generateValidCPF() {
        // Gera os 9 primeiros dígitos
        let cpf = '';
        for (let i = 0; i < 9; i++) {
            cpf += Math.floor(Math.random() * 10);
        }

        // Calcula o primeiro dígito verificador
        let soma = 0;
        for (let i = 0; i < 9; i++) {
            soma += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let resto = soma % 11;
        let primeiroDigito = resto < 2 ? 0 : 11 - resto;
        cpf += primeiroDigito;

        // Calcula o segundo dígito verificador
        soma = 0;
        for (let i = 0; i < 10; i++) {
            soma += parseInt(cpf.charAt(i)) * (11 - i);
        }
        resto = soma % 11;
        let segundoDigito = resto < 2 ? 0 : 11 - resto;
        cpf += segundoDigito;

        // Formata o CPF
        return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }

    /**
     * Valida dados do cliente antes de enviar
     * @param {Object} clientData - Dados do cliente
     * @returns {Object} - Resultado da validação
     */
    validateClientData(clientData) {
        const errors = [];
        let isValid = true;

        // Validar nome (mínimo 2 caracteres)
        if (!clientData.name || clientData.name.length < 2) {
            clientData.name = 'Cliente Teste';
            errors.push('Nome corrigido automaticamente');
        }

        // Validar email com regex robusta
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!clientData.email || !emailRegex.test(clientData.email)) {
            const emailOriginal = clientData.email;
            // Normaliza e-mail: converte para minúsculas e remove espaços múltiplos
            clientData.email = clientData.email ? 
                clientData.email.toLowerCase().replace(/\s+/g, '') : 
                'cliente.teste@gmail.com';
            
            // Se ainda não for válido após normalização, usa email padrão
            if (!emailRegex.test(clientData.email)) {
                clientData.email = 'cliente.teste@gmail.com';
            }
            
            // Log para auditoria
            console.log(`Email ajustado: '${emailOriginal}' -> '${clientData.email}'`);
            errors.push('Email corrigido automaticamente');
        }

        // Validar telefone (formato brasileiro)
        if (!clientData.phone || !clientData.phone.match(/\(\d{2}\)\s\d{4,5}-\d{4}/)) {
            clientData.phone = '(11) 99999-9999';
            errors.push('Telefone corrigido automaticamente');
        }

        // Validar CPF (formato brasileiro)
        if (!clientData.document || !clientData.document.match(/\d{3}\.\d{3}\.\d{3}-\d{2}/)) {
            clientData.document = '123.456.789-00';
            errors.push('CPF corrigido automaticamente');
        }

        return {
            isValid: isValid,
            errors: errors,
            data: clientData
        };
    }

    /**
     * Gera uma cobrança PIX
     * @param {Object} paymentData - Dados do pagamento
     * @returns {Promise<Object>} - Resposta da API
     */
    async generatePixPayment(paymentData) {
        try {
            // Gera dados aleatórios do cliente
            let clientData = this.generateRandomClientData();
            
            // Valida os dados do cliente
            const validationResult = this.validateClientData(clientData);
            clientData = validationResult.data;

            // Estrutura correta para a API Oasy.fy
            const requestData = {
                identifier: `pedido-${Date.now()}`,
                amount: parseFloat(paymentData.amount), // Garantir que é número
                client: {
                    name: clientData.name,
                    email: clientData.email,
                    phone: clientData.phone,
                    document: clientData.document
                },
                // Campos opcionais - só incluir se tiver valor
                ...(paymentData.products && paymentData.products.length > 0 && { products: paymentData.products }),
                ...(paymentData.shippingFee > 0 && { shippingFee: parseFloat(paymentData.shippingFee) }),
                ...(paymentData.extraFee > 0 && { extraFee: parseFloat(paymentData.extraFee) }),
                ...(paymentData.discount > 0 && { discount: parseFloat(paymentData.discount) }),
                ...(paymentData.dueDate && { dueDate: paymentData.dueDate }),
                ...(paymentData.metadata && Object.keys(paymentData.metadata).length > 0 && { metadata: paymentData.metadata }),
                callbackUrl: this.callbackUrl
            };

            // Log dos dados para debug
            console.log('Dados enviados para API:', JSON.stringify(requestData, null, 2));

            const response = await fetch('/api-proxy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'generate_pix',
                    paymentData: requestData
                })
            });

            const data = await response.json();
            
            // Log da resposta para debug
            console.log('Resposta da API:', data);
            
            if (data.status === 'OK') {
                // Salvar registro local como pendente
                try {
                    this.saveLocalPaymentRecord({
                        transactionId: data.transactionId,
                        status: 'PENDING',
                        amount: data.amount ?? paymentData.amount,
                        createdAt: new Date().toISOString(),
                        client: clientData,
                        orderUrl: data.order?.url ?? null
                    });
                } catch (e) {
                    console.warn('Falha ao salvar no localStorage:', e);
                }
                console.log('PIX gerado com sucesso:', data);
                console.log('Dados do cliente gerados:', clientData);
                return {
                    success: true,
                    data: data,
                    clientData: clientData
                };
            } else {
                console.error('Erro ao gerar PIX:', data);
                return {
                    success: false,
                    error: data.errorDescription || data.message || 'Erro desconhecido',
                    details: data.details || null
                };
            }
        } catch (error) {
            console.error('Erro na requisição PIX:', error);
            return {
                success: false,
                error: 'Erro de conexão com a API: ' + error.message
            };
        }
    }

    /**
     * Consulta o status de uma transação
     * @param {string} transactionId - ID da transação
     * @returns {Promise<Object>} - Status da transação
     */
    async checkTransactionStatus(transactionId) {
        try {
            const response = await fetch('/api-proxy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'check_status',
                    transactionId: transactionId
                })
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Erro ao consultar status:', error);
            return null;
        }
    }

    /**
     * Inicia polling automático do status até completar ou expirar
     * @param {string} transactionId
     * @param {{intervalMs?: number, maxAttempts?: number, onUpdate?: Function, onComplete?: Function, onTimeout?: Function}} options
     */
    startPolling(transactionId, options = {}) {
        const intervalMs = typeof options.intervalMs === 'number' ? options.intervalMs : 6000;
        const maxAttempts = typeof options.maxAttempts === 'number' ? options.maxAttempts : 60; // ~6 min
        let attempts = 0;

        // Evitar múltiplos polling para o mesmo ID
        this.stopPolling(transactionId);

        const tick = async () => {
            attempts += 1;
            const status = await this.checkTransactionStatus(transactionId);
            // Atualiza espelho local
            if (status && status.status) {
                this.updateLocalPaymentStatus(transactionId, status.status, status);
            }
            if (typeof options.onUpdate === 'function') {
                try { options.onUpdate(status, attempts); } catch (_) {}
            }

            if (status && (status.status === 'COMPLETED' || status.status === 'CANCELED' || status.status === 'FAILED')) {
                clearInterval(this.activePolls[transactionId]);
                delete this.activePolls[transactionId];
                if (typeof options.onComplete === 'function') {
                    try { options.onComplete(status); } catch (_) {}
                }
                if (status.status === 'COMPLETED') {
                    alert('Pagamento confirmado! Redirecionando...');
                    window.location.href = '/obrigado.html';
                } else if (status.status === 'CANCELED') {
                    alert('Pagamento cancelado.');
                } else if (status.status === 'FAILED') {
                    alert('Pagamento falhou.');
                }
                return;
            }

            if (attempts >= maxAttempts) {
                clearInterval(this.activePolls[transactionId]);
                delete this.activePolls[transactionId];
                if (typeof options.onTimeout === 'function') {
                    try { options.onTimeout(); } catch (_) {}
                }
                alert('Tempo de verificação esgotado. Tente novamente mais tarde.');
            }
        };

        // Executa primeiro tick imediatamente e agenda os próximos
        tick();
        this.activePolls[transactionId] = setInterval(tick, intervalMs);
    }

    /**
     * Interrompe polling para a transação
     * @param {string} transactionId
     */
    stopPolling(transactionId) {
        if (this.activePolls && this.activePolls[transactionId]) {
            clearInterval(this.activePolls[transactionId]);
            delete this.activePolls[transactionId];
        }
    }

    /**
     * Exibe o QR Code e código PIX na interface
     * @param {Object} pixData - Dados do PIX retornados pela API
     * @param {Object} clientData - Dados do cliente gerados
     */
    displayPixData(pixData, clientData = null) {
        // Cria modal para exibir PIX
        const modal = this.createPixModal(pixData, clientData);
        document.body.appendChild(modal);
        
        // Mostra o modal
        $(modal).modal('show');

        // Inicia polling automático assim que exibir o modal
        if (pixData && pixData.transactionId) {
            this.startPolling(pixData.transactionId);
        }
    }

    /**
     * Persiste um registro de pagamento no localStorage (lista 'pix_payments')
     * @param {object} record
     */
    saveLocalPaymentRecord(record) {
        const key = 'pix_payments';
        const current = JSON.parse(localStorage.getItem(key) || '[]');
        const existsIdx = current.findIndex(r => r.transactionId === record.transactionId);
        if (existsIdx >= 0) {
            current[existsIdx] = { ...current[existsIdx], ...record };
        } else {
            current.unshift(record);
        }
        localStorage.setItem(key, JSON.stringify(current));
    }

    /**
     * Atualiza status local da transação
     * @param {string} transactionId
     * @param {string} status
     * @param {object} providerData
     */
    updateLocalPaymentStatus(transactionId, status, providerData = null) {
        const key = 'pix_payments';
        const current = JSON.parse(localStorage.getItem(key) || '[]');
        const idx = current.findIndex(r => r.transactionId === transactionId);
        if (idx >= 0) {
            current[idx].status = status;
            current[idx].updatedAt = new Date().toISOString();
            if (providerData) current[idx].provider = providerData;
            localStorage.setItem(key, JSON.stringify(current));
        } else {
            this.saveLocalPaymentRecord({
                transactionId,
                status,
                updatedAt: new Date().toISOString(),
                provider: providerData || null
            });
        }
    }

    /**
     * Cria modal para exibir dados do PIX
     * @param {Object} pixData - Dados do PIX
     * @param {Object} clientData - Dados do cliente gerados
     * @returns {HTMLElement} - Elemento do modal
     */
    createPixModal(pixData, clientData = null) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'pixModal';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-labelledby', 'pixModalLabel');
        modal.setAttribute('aria-hidden', 'true');

        // Seção de dados do cliente (se disponível)
        const clientInfo = clientData ? `
            <div class="alert alert-light mb-3">
                <h6 class="mb-2"><i class="fas fa-user"></i> Dados do Cliente:</h6>
                <small>
                    <strong>Nome:</strong> ${clientData.name}<br>
                    <strong>Email:</strong> ${clientData.email}<br>
                    <strong>Telefone:</strong> ${clientData.phone}<br>
                    <strong>CPF:</strong> ${clientData.document}
                </small>
            </div>
        ` : '';

        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pixModalLabel">
                            <i class="fas fa-qrcode"></i> Pagamento PIX
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        ${clientInfo}
                        
                        <div class="mb-3">
                            <h6>Escaneie o QR Code com seu app de pagamento:</h6>
                            <img src="${pixData.pix.image}" alt="QR Code PIX" class="img-fluid" style="max-width: 300px; border: 1px solid #ddd; border-radius: 8px;">
                        </div>
                        
                        <div class="mb-3">
                            <h6>Ou copie o código PIX:</h6>
                            <div class="input-group">
                                <input type="text" class="form-control" id="pixCode" value="${pixData.pix.code}" readonly style="font-size: 12px;">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyPixCode()">
                                    <i class="fas fa-copy"></i> Copiar
                                </button>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <small>
                                <strong>Valor:</strong> R$ ${pixData.amount || 'N/A'}<br>
                                <strong>Taxa:</strong> R$ ${pixData.fee || 'N/A'}<br>
                                <strong>ID da Transação:</strong> ${pixData.transactionId}
                            </small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" onclick="checkPaymentStatus('${pixData.transactionId}')">
                                <i class="fas fa-sync-alt"></i> Verificar Pagamento
                            </button>
                            <a href="${pixData.order.url}" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Ver Pedido
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    /**
     * Redireciona para página de pagamento
     * @param {string} orderUrl - URL da página do pedido
     */
    redirectToPayment(orderUrl) {
        if (orderUrl) {
            window.open(orderUrl, '_blank');
        }
    }
}

// Instância global
const pixIntegration = new PixIntegration();

// Funções globais para uso nos botões
function copyPixCode() {
    const pixCodeInput = document.getElementById('pixCode');
    if (pixCodeInput) {
        pixCodeInput.select();
        pixCodeInput.setSelectionRange(0, 99999); // Para mobile
        document.execCommand('copy');
        
        // Feedback visual
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copiado!';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    }
}

function checkPaymentStatus(transactionId) {
    pixIntegration.checkTransactionStatus(transactionId).then(status => {
        if (status) {
            if (status.status === 'COMPLETED') {
                alert('Pagamento confirmado! Redirecionando...');
                // Aqui você pode redirecionar para uma página de sucesso
                window.location.href = '/obrigado.html';
            } else {
                alert(`Status do pagamento: ${status.status}`);
            }
        } else {
            alert('Erro ao verificar status do pagamento');
        }
    });
}

// Exportar para uso global
window.pixIntegration = pixIntegration;
window.copyPixCode = copyPixCode;
window.checkPaymentStatus = checkPaymentStatus;
