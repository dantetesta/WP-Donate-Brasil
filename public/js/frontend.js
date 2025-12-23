/**
 * WP Donate Brasil - Frontend Scripts
 * 
 * @package WP_Donate_Brasil
 * @author Dante Testa <https://dantetesta.com.br>
 * @since 1.0.0
 * @created 23/12/2025 09:21
 */

(function($) {
    'use strict';

    const WDBFrontend = {
        formSection: $('#wdb-receipt-form-section'),
        form: $('#wdb-receipt-form'),
        submitBtn: $('#wdb-submit-btn'),
        messageDiv: $('#wdb-form-message'),
        methodInput: $('#wdb-donation-method'),

        init: function() {
            this.bindEvents();
            this.initSwiper();
            this.initPhoneMask();
        },

        bindEvents: function() {
            const self = this;

            $(document).on('click', '.wdb-copy-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.copyToClipboard($(this));
            });

            // Abre modal do método ao clicar no card ou botão
            $(document).on('click', '.wdb-method-card, .wdb-open-method-modal', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $card = $(this).closest('.wdb-method-card');
                const method = $card.data('method');
                self.openMethodModal(method);
            });

            // Fecha modal do método
            $('#wdb-modal-close, #wdb-modal-overlay').on('click', function() {
                self.closeMethodModal();
            });

            // Botão enviar comprovante no modal
            $('#wdb-modal-send-receipt').on('click', function() {
                const method = $(this).data('method');
                const methodName = $(this).data('method-name');
                self.closeMethodModal();
                self.openReceiptForm(method, methodName);
            });

            $(document).on('click', '.wdb-send-receipt-btn', function(e) {
                e.preventDefault();
                const method = $(this).data('method');
                const methodName = $(this).data('method-name');
                self.openReceiptForm(method, methodName);
            });

            $('#wdb-close-form').on('click', function() {
                self.closeReceiptForm();
            });

            this.form.on('submit', function(e) {
                e.preventDefault();
                self.submitReceipt();
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    if ($('#wdb-method-modal').is(':visible')) {
                        self.closeMethodModal();
                    } else if (self.formSection.is(':visible')) {
                        self.closeReceiptForm();
                    }
                }
            });

            this.form.find('input[required], textarea[required]').on('blur', function() {
                self.validateField($(this));
            });
        },

        // Abre modal com detalhes do método
        openMethodModal: function(methodId) {
            const methodsData = $('#wdb-methods-data').length ? JSON.parse($('#wdb-methods-data').text()) : {};
            const method = methodsData[methodId];
            
            if (!method) return;

            const $modal = $('#wdb-method-modal');
            const $icon = $('#wdb-modal-icon');
            const $title = $('#wdb-modal-title');
            const $content = $('#wdb-modal-content');
            const $sendBtn = $('#wdb-modal-send-receipt');

            // Cores por método
            const colors = {
                'pix': 'from-green-500 to-emerald-600',
                'bank_transfer': 'from-blue-500 to-blue-600',
                'bitcoin': 'from-orange-500 to-amber-600',
                'payment_link': 'from-purple-500 to-indigo-600',
                'paypal': 'from-blue-600 to-indigo-700'
            };
            const bgColor = colors[methodId] || 'from-gray-500 to-gray-600';

            // Atualiza header
            $icon.attr('class', 'w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br ' + bgColor);
            $icon.html('<i class="' + (method.icon || 'fa-solid fa-heart') + ' text-xl text-white"></i>');
            $title.text(method.name);

            // Atualiza botão enviar
            $sendBtn.data('method', methodId).data('method-name', method.name);

            // Renderiza conteúdo baseado no método
            let content = this.renderMethodContent(method);
            $content.html(content);

            // Mostra modal
            $modal.removeClass('hidden').hide().fadeIn(200);
            $('body').addClass('overflow-hidden');
        },

        closeMethodModal: function() {
            $('#wdb-method-modal').fadeOut(200, function() {
                $(this).addClass('hidden');
            });
            $('body').removeClass('overflow-hidden');
        },

        renderMethodContent: function(method) {
            let html = '';
            
            // Instruções
            if (method.instructions) {
                html += '<p class="text-gray-600 mb-4">' + method.instructions + '</p>';
            }

            switch (method.id) {
                case 'pix':
                    html += this.renderPixContent(method);
                    break;
                case 'bank_transfer':
                    html += this.renderBankContent(method);
                    break;
                case 'bitcoin':
                    html += this.renderBitcoinContent(method);
                    break;
                case 'payment_link':
                    html += this.renderPaymentLinkContent(method);
                    break;
                case 'paypal':
                    html += this.renderPaypalContent(method);
                    break;
            }

            return html;
        },

        renderPixContent: function(method) {
            if (!method.pix_key || !method.pix_name) {
                return '<p class="text-red-500 text-center">Configure a chave PIX nas configurações.</p>';
            }

            // Container com loading inicial - o QR será carregado via AJAX
            let html = '<div class="text-center" id="wdb-pix-qr-container">';
            html += '<div class="inline-block p-3 bg-white rounded-xl shadow-lg border-2 border-green-200 mb-4">';
            html += '<div id="wdb-pix-qr-loading" class="w-[200px] h-[200px] flex items-center justify-center"><i class="fa-solid fa-spinner fa-spin text-3xl text-green-500"></i></div>';
            html += '<img id="wdb-pix-qr-image" src="" alt="QR Code PIX" class="mx-auto hidden" width="200" height="200">';
            html += '<p class="text-xs text-gray-500 mt-2"><i class="fa-solid fa-camera mr-1"></i>Escaneie com seu app</p>';
            html += '</div></div>';

            // Copia e cola - será preenchido com payload via AJAX
            html += '<div class="mt-4">';
            html += '<label class="block text-sm font-medium text-gray-600 mb-2"><i class="fa-solid fa-copy mr-1"></i>PIX Copia e Cola</label>';
            html += '<div class="flex items-center gap-2">';
            html += '<input type="text" readonly id="wdb-pix-payload-input" value="" class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono truncate" placeholder="Carregando...">';
            html += '<button type="button" class="wdb-copy-btn px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all" id="wdb-pix-copy-btn" data-copy=""><i class="fa-solid fa-copy"></i></button>';
            html += '</div></div>';

            // Chave PIX simples
            html += '<div class="mt-4">';
            html += '<label class="block text-sm font-medium text-gray-600 mb-2"><i class="fa-solid fa-key mr-1"></i>Chave PIX</label>';
            html += '<div class="flex items-center gap-2">';
            html += '<input type="text" readonly value="' + method.pix_key + '" class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono truncate">';
            html += '<button type="button" class="wdb-copy-btn px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all" data-copy="' + method.pix_key + '"><i class="fa-solid fa-copy"></i></button>';
            html += '</div></div>';

            // Info adicional
            html += '<div class="mt-4 pt-4 border-t border-gray-200 space-y-2 text-sm">';
            if (method.pix_name) {
                html += '<p><span class="font-medium text-gray-600"><i class="fa-solid fa-user mr-1"></i>Titular:</span> ' + method.pix_name + '</p>';
            }
            if (method.pix_bank) {
                html += '<p><span class="font-medium text-gray-600"><i class="fa-solid fa-building-columns mr-1"></i>Banco:</span> ' + method.pix_bank + '</p>';
            }
            html += '</div>';

            // Carrega QR Code via AJAX após renderizar
            setTimeout(() => this.loadPixQRCode(method.id), 100);

            return html;
        },

        loadPixQRCode: function(methodId) {
            const self = this;
            $.ajax({
                url: wdb_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wdb_get_pix_payload',
                    method_id: methodId
                },
                success: function(response) {
                    if (response.success) {
                        $('#wdb-pix-qr-loading').hide();
                        $('#wdb-pix-qr-image').attr('src', response.data.qr_url).removeClass('hidden');
                        $('#wdb-pix-payload-input').val(response.data.payload);
                        $('#wdb-pix-copy-btn').data('copy', response.data.payload);
                    } else {
                        $('#wdb-pix-qr-loading').html('<i class="fa-solid fa-exclamation-triangle text-3xl text-red-500"></i>');
                    }
                },
                error: function() {
                    $('#wdb-pix-qr-loading').html('<i class="fa-solid fa-exclamation-triangle text-3xl text-red-500"></i>');
                }
            });
        },

        renderBankContent: function(method) {
            let html = '<div class="bg-blue-50 rounded-xl p-4 space-y-3">';
            
            if (method.bank_name) html += '<p class="flex justify-between"><span class="font-medium text-gray-600">Banco:</span><span class="text-gray-800">' + method.bank_name + '</span></p>';
            if (method.bank_agency) html += '<p class="flex justify-between"><span class="font-medium text-gray-600">Agência:</span><span class="text-gray-800">' + method.bank_agency + '</span></p>';
            if (method.bank_account) html += '<p class="flex justify-between"><span class="font-medium text-gray-600">Conta:</span><span class="text-gray-800">' + method.bank_account + '</span></p>';
            if (method.bank_holder) html += '<p class="flex justify-between"><span class="font-medium text-gray-600">Titular:</span><span class="text-gray-800">' + method.bank_holder + '</span></p>';
            if (method.bank_cpf_cnpj) html += '<p class="flex justify-between"><span class="font-medium text-gray-600">CPF/CNPJ:</span><span class="text-gray-800">' + method.bank_cpf_cnpj + '</span></p>';
            
            html += '</div>';
            return html;
        },

        renderBitcoinContent: function(method) {
            if (!method.btc_address) {
                return '<p class="text-red-500 text-center">Configure o endereço Bitcoin nas configurações.</p>';
            }

            const network = method.btc_network || 'Bitcoin';
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(method.btc_address);

            let html = '<div class="text-center">';
            html += '<div class="inline-block p-3 bg-white rounded-xl shadow-lg border-2 border-orange-200 mb-4">';
            html += '<img src="' + qrUrl + '" alt="Bitcoin QR" class="mx-auto" width="180" height="180" loading="lazy">';
            html += '<p class="text-xs text-orange-600 mt-2 font-medium"><i class="fa-brands fa-bitcoin mr-1"></i>' + network + '</p>';
            html += '</div></div>';

            html += '<div class="mt-4">';
            html += '<label class="block text-sm font-medium text-gray-600 mb-2"><i class="fa-solid fa-wallet mr-1"></i>Endereço</label>';
            html += '<div class="flex items-center gap-2">';
            html += '<input type="text" readonly value="' + method.btc_address + '" class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono truncate">';
            html += '<button type="button" class="wdb-copy-btn px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-all" data-copy="' + method.btc_address + '"><i class="fa-solid fa-copy"></i></button>';
            html += '</div></div>';

            return html;
        },

        renderPaymentLinkContent: function(method) {
            if (!method.gateway_url) {
                return '<p class="text-red-500 text-center">Configure o link de pagamento nas configurações.</p>';
            }

            const gatewayName = method.gateway_name || 'Gateway de Pagamento';
            let html = '<div class="text-center">';
            
            if (method.gateway_logo) {
                html += '<div class="mb-4"><img src="' + method.gateway_logo + '" alt="' + gatewayName + '" class="h-16 mx-auto object-contain"></div>';
            }

            html += '<a href="' + method.gateway_url + '" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-3 w-full py-4 px-6 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl transition-all shadow-lg">';
            html += '<i class="fa-solid fa-external-link-alt"></i>';
            html += '<span>Doar via ' + gatewayName + '</span>';
            html += '</a>';

            html += '<p class="text-xs text-gray-500 mt-3"><i class="fa-solid fa-shield-alt mr-1"></i>Site seguro do gateway</p>';
            html += '</div>';

            return html;
        },

        renderPaypalContent: function(method) {
            if (!method.paypal_email) {
                return '<p class="text-red-500 text-center">Configure o e-mail PayPal nas configurações.</p>';
            }

            let html = '<div class="bg-blue-50 rounded-xl p-4">';
            html += '<label class="block text-sm font-medium text-gray-600 mb-2"><i class="fa-brands fa-paypal mr-1"></i>E-mail PayPal</label>';
            html += '<div class="flex items-center gap-2">';
            html += '<input type="text" readonly value="' + method.paypal_email + '" class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm">';
            html += '<button type="button" class="wdb-copy-btn px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all" data-copy="' + method.paypal_email + '"><i class="fa-solid fa-copy"></i></button>';
            html += '</div></div>';

            return html;
        },

        copyToClipboard: function($btn) {
            const text = $btn.data('copy');
            const self = this;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    self.showCopyFeedback($btn, true);
                }).catch(function() {
                    self.fallbackCopy(text, $btn);
                });
            } else {
                this.fallbackCopy(text, $btn);
            }
        },

        fallbackCopy: function(text, $btn) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                this.showCopyFeedback($btn, true);
            } catch (err) {
                this.showCopyFeedback($btn, false);
            }
            
            document.body.removeChild(textarea);
        },

        showCopyFeedback: function($btn, success) {
            const originalHtml = $btn.html();
            
            if (success) {
                $btn.addClass('copied');
                $btn.html('<i class="fa-solid fa-check"></i>');
                
                setTimeout(function() {
                    $btn.removeClass('copied');
                    $btn.html(originalHtml);
                }, 2000);
            } else {
                $btn.html('<i class="fa-solid fa-times"></i>');
                setTimeout(function() {
                    $btn.html(originalHtml);
                }, 2000);
            }
        },

        openReceiptForm: function(method, methodName) {
            this.methodInput.val(method);
            this.formSection.removeClass('hidden').hide().fadeIn(300);
            this.form[0].reset();
            this.methodInput.val(method);
            this.hideMessage();
            
            $('html, body').animate({
                scrollTop: this.formSection.offset().top - 100
            }, 500);
            
            setTimeout(function() {
                $('#wdb-donor-name').focus();
            }, 300);
        },

        closeReceiptForm: function() {
            const self = this;
            this.formSection.fadeOut(300, function() {
                $(this).addClass('hidden');
                self.form[0].reset();
                self.hideMessage();
            });
        },

        submitReceipt: function() {
            const self = this;
            
            if (!this.validateForm()) {
                return;
            }

            const formData = new FormData(this.form[0]);
            formData.append('action', 'wdb_submit_receipt');
            formData.append('nonce', wdb_vars.nonce);

            this.setLoading(true);

            $.ajax({
                url: wdb_vars.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        self.form[0].reset();
                        
                        // Exibe mensagem de agradecimento com confetes
                        if (response.data.show_thank_you) {
                            self.showThankYouModal(response.data);
                        } else {
                            self.showMessage(response.data.message, 'success');
                            setTimeout(function() {
                                self.closeReceiptForm();
                            }, 3000);
                        }
                    } else {
                        self.showMessage(response.data.message || wdb_vars.strings.error, 'error');
                    }
                },
                error: function() {
                    self.showMessage(wdb_vars.strings.error, 'error');
                },
                complete: function() {
                    self.setLoading(false);
                }
            });
        },

        validateForm: function() {
            let isValid = true;
            const self = this;

            this.form.find('input[required], textarea[required]').each(function() {
                if (!self.validateField($(this))) {
                    isValid = false;
                }
            });

            const fileInput = $('#wdb-receipt-file');
            if (fileInput[0].files.length === 0) {
                this.showMessage(wdb_vars.strings.file_required, 'error');
                fileInput.addClass('border-red-500');
                isValid = false;
            } else {
                const file = fileInput[0].files[0];
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                
                if (!allowedTypes.includes(file.type)) {
                    this.showMessage(wdb_vars.strings.invalid_file, 'error');
                    fileInput.addClass('border-red-500');
                    isValid = false;
                } else {
                    fileInput.removeClass('border-red-500');
                }
            }

            return isValid;
        },

        validateField: function($field) {
            const value = $field.val().trim();
            let isValid = true;

            if ($field.prop('required') && !value) {
                $field.addClass('border-red-500');
                isValid = false;
            } else if ($field.attr('type') === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    $field.addClass('border-red-500');
                    isValid = false;
                } else {
                    $field.removeClass('border-red-500');
                }
            } else {
                $field.removeClass('border-red-500');
            }

            return isValid;
        },

        setLoading: function(loading) {
            if (loading) {
                this.submitBtn.addClass('loading').prop('disabled', true);
                this.submitBtn.find('i').removeClass('fa-paper-plane').addClass('fa-spinner');
                this.submitBtn.find('span, text').first().text(wdb_vars.strings.uploading);
            } else {
                this.submitBtn.removeClass('loading').prop('disabled', false);
                this.submitBtn.find('i').removeClass('fa-spinner').addClass('fa-paper-plane');
            }
        },

        showMessage: function(message, type) {
            this.messageDiv
                .removeClass('hidden success error')
                .addClass(type)
                .html('<i class="fa-solid ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + ' mr-2"></i>' + message)
                .hide()
                .fadeIn(300);
        },

        hideMessage: function() {
            this.messageDiv.addClass('hidden').removeClass('success error');
        },

        initSwiper: function() {
            if ($('.wdb-donors-swiper').length) {
                new Swiper('.wdb-donors-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true
                    },
                    autoplay: {
                        delay: 4000,
                        disableOnInteraction: false
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 2
                        },
                        768: {
                            slidesPerView: 3
                        },
                        1024: {
                            slidesPerView: 4
                        }
                    },
                    a11y: {
                        prevSlideMessage: 'Slide anterior',
                        nextSlideMessage: 'Próximo slide',
                        firstSlideMessage: 'Primeiro slide',
                        lastSlideMessage: 'Último slide'
                    }
                });
            }
        },

        initPhoneMask: function() {
            const phoneInput = $('#wdb-donor-phone');
            
            phoneInput.on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                
                if (value.length > 11) {
                    value = value.substring(0, 11);
                }
                
                if (value.length > 0) {
                    if (value.length <= 2) {
                        value = '(' + value;
                    } else if (value.length <= 7) {
                        value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
                    } else {
                        value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 7) + '-' + value.substring(7);
                    }
                }
                
                $(this).val(value);
            });
        },
        
        // Modal de agradecimento com confetes
        showThankYouModal: function(data) {
            const self = this;
            
            // Remove modal existente se houver
            $('#wdb-thank-you-modal').remove();
            
            // Cria o modal
            const modalHtml = `
                <div id="wdb-thank-you-modal" class="wdb-thank-you-modal">
                    <div class="wdb-thank-you-overlay"></div>
                    <div class="wdb-thank-you-content">
                        <div class="wdb-thank-you-icon">🙏</div>
                        <h2 class="wdb-thank-you-title">${data.thank_you_title}</h2>
                        <p class="wdb-thank-you-subtitle">${data.thank_you_subtitle}</p>
                        <p class="wdb-thank-you-message">${data.thank_you_message}</p>
                        <button type="button" class="wdb-thank-you-btn">Fechar</button>
                    </div>
                    <canvas id="wdb-confetti-canvas"></canvas>
                </div>
                <style>
                    .wdb-thank-you-modal {
                        position: fixed;
                        inset: 0;
                        z-index: 99999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 20px;
                        animation: wdbFadeIn 0.3s ease;
                    }
                    @keyframes wdbFadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    .wdb-thank-you-overlay {
                        position: absolute;
                        inset: 0;
                        background: rgba(0, 0, 0, 0.7);
                        backdrop-filter: blur(5px);
                    }
                    .wdb-thank-you-content {
                        position: relative;
                        background: white;
                        border-radius: 24px;
                        padding: 50px 40px;
                        text-align: center;
                        max-width: 450px;
                        width: 100%;
                        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
                        animation: wdbSlideUp 0.5s ease;
                    }
                    @keyframes wdbSlideUp {
                        from { opacity: 0; transform: translateY(30px) scale(0.95); }
                        to { opacity: 1; transform: translateY(0) scale(1); }
                    }
                    .wdb-thank-you-icon {
                        font-size: 80px;
                        margin-bottom: 20px;
                        animation: wdbBounce 1s ease infinite;
                    }
                    @keyframes wdbBounce {
                        0%, 100% { transform: translateY(0); }
                        50% { transform: translateY(-10px); }
                    }
                    .wdb-thank-you-title {
                        font-size: 2rem;
                        font-weight: 800;
                        color: #1e293b;
                        margin: 0 0 10px;
                    }
                    .wdb-thank-you-subtitle {
                        font-size: 1.2rem;
                        color: ${wdb_vars.primary_color || '#3B82F6'};
                        font-weight: 600;
                        margin: 0 0 20px;
                    }
                    .wdb-thank-you-message {
                        font-size: 1rem;
                        color: #64748b;
                        line-height: 1.7;
                        margin: 0 0 30px;
                    }
                    .wdb-thank-you-btn {
                        padding: 14px 40px;
                        background: linear-gradient(135deg, ${wdb_vars.primary_color || '#3B82F6'}, ${wdb_vars.secondary_color || '#10B981'});
                        color: white;
                        font-weight: 700;
                        font-size: 1rem;
                        border: none;
                        border-radius: 50px;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    }
                    .wdb-thank-you-btn:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
                    }
                    #wdb-confetti-canvas {
                        position: fixed;
                        inset: 0;
                        pointer-events: none;
                        z-index: 100000;
                    }
                </style>
            `;
            
            $('body').append(modalHtml);
            
            // Inicia confetes
            this.startConfetti();
            
            // Fecha modal
            $('#wdb-thank-you-modal .wdb-thank-you-btn, #wdb-thank-you-modal .wdb-thank-you-overlay').on('click', function() {
                $('#wdb-thank-you-modal').fadeOut(300, function() {
                    $(this).remove();
                });
                self.closeReceiptForm();
                self.closeMethodModal();
            });
        },
        
        // Animação de confetes
        startConfetti: function() {
            const canvas = document.getElementById('wdb-confetti-canvas');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            
            const confetti = [];
            const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4'];
            
            // Cria confetes
            for (let i = 0; i < 150; i++) {
                confetti.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height - canvas.height,
                    w: Math.random() * 10 + 5,
                    h: Math.random() * 6 + 4,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    speed: Math.random() * 3 + 2,
                    angle: Math.random() * 360,
                    spin: Math.random() * 10 - 5
                });
            }
            
            let animationFrame;
            
            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                let stillFalling = false;
                
                confetti.forEach(c => {
                    c.y += c.speed;
                    c.x += Math.sin(c.angle * Math.PI / 180) * 2;
                    c.angle += c.spin;
                    
                    if (c.y < canvas.height + 20) {
                        stillFalling = true;
                    }
                    
                    ctx.save();
                    ctx.translate(c.x + c.w / 2, c.y + c.h / 2);
                    ctx.rotate(c.angle * Math.PI / 180);
                    ctx.fillStyle = c.color;
                    ctx.fillRect(-c.w / 2, -c.h / 2, c.w, c.h);
                    ctx.restore();
                });
                
                if (stillFalling) {
                    animationFrame = requestAnimationFrame(animate);
                }
            }
            
            animate();
            
            // Para animação após 5 segundos
            setTimeout(() => {
                cancelAnimationFrame(animationFrame);
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }, 5000);
        }
    };

    $(document).ready(function() {
        WDBFrontend.init();
    });

})(jQuery);
