/**
 * Validação de dados do cliente para PIX
 */

class ClientValidation {
    constructor() {
        this.validationRules = {
            name: {
                required: true,
                minLength: 2,
                maxLength: 100,
                pattern: /^[a-zA-ZÀ-ÿ\s]+$/
            },
            email: {
                required: true,
                pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            },
            phone: {
                required: true,
                pattern: /^\(\d{2}\)\s\d{4,5}-\d{4}$/
            },
            document: {
                required: true,
                pattern: /^\d{3}\.\d{3}\.\d{3}-\d{2}$/
            }
        };
    }

    /**
     * Valida um campo específico
     */
    validateField(fieldName, value) {
        const rules = this.validationRules[fieldName];
        if (!rules) return { valid: true };

        const errors = [];

        // Verifica se é obrigatório
        if (rules.required && (!value || value.trim() === '')) {
            errors.push(`${this.getFieldLabel(fieldName)} é obrigatório`);
        }

        // Verifica comprimento mínimo
        if (rules.minLength && value && value.length < rules.minLength) {
            errors.push(`${this.getFieldLabel(fieldName)} deve ter pelo menos ${rules.minLength} caracteres`);
        }

        // Verifica comprimento máximo
        if (rules.maxLength && value && value.length > rules.maxLength) {
            errors.push(`${this.getFieldLabel(fieldName)} deve ter no máximo ${rules.maxLength} caracteres`);
        }

        // Verifica padrão regex
        if (rules.pattern && value && !rules.pattern.test(value)) {
            errors.push(`${this.getFieldLabel(fieldName)} está em formato inválido`);
        }

        return {
            valid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Valida todos os campos
     */
    validateAllFields(clientData) {
        const results = {};
        let allValid = true;

        for (const field in this.validationRules) {
            const value = clientData[field] || '';
            const validation = this.validateField(field, value);
            results[field] = validation;
            
            if (!validation.valid) {
                allValid = false;
            }
        }

        return {
            valid: allValid,
            fields: results
        };
    }

    /**
     * Retorna o label do campo
     */
    getFieldLabel(fieldName) {
        const labels = {
            name: 'Nome',
            email: 'E-mail',
            phone: 'Telefone',
            document: 'CPF'
        };
        return labels[fieldName] || fieldName;
    }

    /**
     * Formata CPF
     */
    formatCPF(value) {
        // Remove tudo que não é dígito
        const numbers = value.replace(/\D/g, '');
        
        // Aplica a máscara
        if (numbers.length <= 11) {
            return numbers.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        }
        
        return value;
    }

    /**
     * Formata telefone
     */
    formatPhone(value) {
        // Remove tudo que não é dígito
        const numbers = value.replace(/\D/g, '');
        
        // Aplica a máscara
        if (numbers.length <= 11) {
            if (numbers.length <= 10) {
                return numbers.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else {
                return numbers.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            }
        }
        
        return value;
    }

    /**
     * Valida CPF
     */
    validateCPF(cpf) {
        // Remove formatação
        cpf = cpf.replace(/\D/g, '');
        
        // Verifica se tem 11 dígitos
        if (cpf.length !== 11) return false;
        
        // Verifica se todos os dígitos são iguais
        if (/^(\d)\1{10}$/.test(cpf)) return false;
        
        // Validação do algoritmo do CPF
        let sum = 0;
        for (let i = 0; i < 9; i++) {
            sum += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.charAt(9))) return false;
        
        sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += parseInt(cpf.charAt(i)) * (11 - i);
        }
        remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.charAt(10))) return false;
        
        return true;
    }

    /**
     * Mostra modal de coleta de dados do cliente
     */
    showClientDataModal(amount, planName, callback) {
        const modal = this.createClientDataModal(amount, planName, callback);
        document.body.appendChild(modal);
        $(modal).modal('show');
    }

    /**
     * Cria modal para coleta de dados do cliente
     */
    createClientDataModal(amount, planName, callback) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'clientDataModal';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-labelledby', 'clientDataModalLabel');
        modal.setAttribute('aria-hidden', 'true');

        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clientDataModalLabel">Dados para Pagamento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Plano:</strong> ${planName}<br>
                            <strong>Valor:</strong> R$ ${amount.toFixed(2)}
                        </div>
                        
                        <form id="clientDataForm">
                            <div class="mb-3">
                                <label for="clientName" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="clientName" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="clientEmail" class="form-label">E-mail *</label>
                                <input type="email" class="form-control" id="clientEmail" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="clientPhone" class="form-label">Telefone *</label>
                                <input type="text" class="form-control" id="clientPhone" name="phone" placeholder="(11) 99999-9999" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="clientDocument" class="form-label">CPF *</label>
                                <input type="text" class="form-control" id="clientDocument" name="document" placeholder="123.456.789-00" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="confirmPaymentBtn">Confirmar Pagamento</button>
                    </div>
                </div>
            </div>
        `;

        // Adiciona event listeners
        this.addModalEventListeners(modal, callback);

        return modal;
    }

    /**
     * Adiciona event listeners ao modal
     */
    addModalEventListeners(modal, callback) {
        const form = modal.querySelector('#clientDataForm');
        const confirmBtn = modal.querySelector('#confirmPaymentBtn');
        const clientValidation = new ClientValidation();

        // Formatação automática dos campos
        const phoneInput = modal.querySelector('#clientPhone');
        const documentInput = modal.querySelector('#clientDocument');

        phoneInput.addEventListener('input', (e) => {
            e.target.value = clientValidation.formatPhone(e.target.value);
        });

        documentInput.addEventListener('input', (e) => {
            e.target.value = clientValidation.formatCPF(e.target.value);
        });

        // Validação em tempo real
        form.addEventListener('input', (e) => {
            const field = e.target.name;
            const value = e.target.value;
            const validation = clientValidation.validateField(field, value);
            
            if (validation.valid) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else {
                e.target.classList.remove('is-valid');
                e.target.classList.add('is-invalid');
                e.target.nextElementSibling.textContent = validation.errors[0];
            }
        });

        // Confirmação do pagamento
        confirmBtn.addEventListener('click', () => {
            const formData = new FormData(form);
            const clientData = {
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                document: formData.get('document')
            };

            const validation = clientValidation.validateAllFields(clientData);
            
            if (validation.valid) {
                // Valida CPF específico
                if (!clientValidation.validateCPF(clientData.document)) {
                    alert('CPF inválido!');
                    return;
                }

                // Fecha o modal
                $(modal).modal('hide');
                
                // Chama o callback com os dados validados
                callback(clientData);
            } else {
                // Mostra erros
                let errorMessage = 'Por favor, corrija os seguintes erros:\n';
                for (const field in validation.fields) {
                    if (!validation.fields[field].valid) {
                        errorMessage += `• ${validation.fields[field].errors.join(', ')}\n`;
                    }
                }
                alert(errorMessage);
            }
        });
    }
}

// Instância global
const clientValidation = new ClientValidation();

// Exportar para uso global
window.clientValidation = clientValidation;
