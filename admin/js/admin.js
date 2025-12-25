/**
 * WP Donate Brasil - Admin Scripts
 * 
 * @package WP_Donate_Brasil
 * @author Dante Testa <https://dantetesta.com.br>
 * @since 1.0.0
 * @created 23/12/2025 09:21
 */

(function($) {
    'use strict';

    const WDBAdmin = {
        init: function() {
            this.bindEvents();
            this.initColorPickers();
            this.initTabs();
        },
        
        initTabs: function() {
            // Sistema de tabs
            $('.wdb-tab-btn').on('click', function() {
                var tabId = $(this).data('tab');
                
                // Remove active de todos os botões
                $('.wdb-tab-btn').removeClass('active text-blue-600 border-blue-500');
                $('.wdb-tab-btn').addClass('text-gray-600 border-transparent');
                
                // Adiciona active no botão clicado
                $(this).addClass('active text-blue-600 border-blue-500');
                $(this).removeClass('text-gray-600 border-transparent');
                
                // Esconde todos os conteúdos
                $('.wdb-tab-content').hide();
                
                // Mostra o conteúdo da tab selecionada
                $('#' + tabId).show();
            });
            
            // Ativa a primeira tab por padrão
            $('.wdb-tab-btn.active').trigger('click');
        },

        bindEvents: function() {
            const self = this;

            $('#wdb-settings-form').on('submit', function(e) {
                e.preventDefault();
                self.saveSettings($(this));
            });

            $('#wdb-methods-form').on('submit', function(e) {
                e.preventDefault();
                self.saveMethods($(this));
            });

            $(document).on('click', '.wdb-approve-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                if (confirm(wdb_admin_vars.strings.confirm_approve)) {
                    self.updateReceiptStatus(id, 'approved', $(this).closest('tr'));
                }
            });

            $(document).on('click', '.wdb-reject-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                if (confirm(wdb_admin_vars.strings.confirm_reject)) {
                    self.updateReceiptStatus(id, 'rejected', $(this).closest('tr'));
                }
            });

            $(document).on('click', '.wdb-delete-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                if (confirm(wdb_admin_vars.strings.confirm_delete)) {
                    self.deleteReceipt(id, $(this).closest('tr'));
                }
            });

            // Botões de limpar cache
            $('#wdb-clear-cache-btn').on('click', function() {
                self.clearCache('wdb_clear_cache', $(this));
            });

            $('#wdb-clear-transients-btn').on('click', function() {
                self.clearCache('wdb_clear_transients', $(this));
            });
        },

        clearCache: function(action, $btn) {
            const self = this;
            const originalText = $btn.html();

            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Limpando...');

            $.ajax({
                url: wdb_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: action,
                    wdb_nonce: wdb_admin_vars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.showMessage('#wdb-cache-message', response.data.message, 'success');
                        self.showToast(response.data.message, 'success');
                    } else {
                        self.showMessage('#wdb-cache-message', response.data.message, 'error');
                        self.showToast(response.data.message, 'error');
                    }
                },
                error: function() {
                    self.showMessage('#wdb-cache-message', 'Erro ao limpar cache', 'error');
                    self.showToast('Erro ao limpar cache', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        },

        initColorPickers: function() {
            $('input[type="color"]').on('input', function() {
                $(this).next('input[type="text"]').val($(this).val());
            });
        },

        saveSettings: function($form) {
            const self = this;
            const $btn = $form.find('button[type="submit"]');
            const originalText = $btn.html();

            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Salvando...');

            // Debug: Log dos dados serializados
            var formData = $form.serialize();
            console.log('WDB Form Data:', formData);
            console.log('WDB Primary Color:', $form.find('input[name="primary_color"]').val());
            console.log('WDB Secondary Color:', $form.find('input[name="secondary_color"]').val());
            console.log('WDB Show Credits:', $form.find('input[name="show_credits"]').is(':checked'));

            $.ajax({
                url: wdb_admin_vars.ajax_url,
                type: 'POST',
                data: formData + '&action=wdb_save_settings',
                success: function(response) {
                    if (response.success) {
                        self.showMessage('#wdb-settings-message', response.data.message, 'success');
                        self.showToast(response.data.message, 'success');
                    } else {
                        self.showMessage('#wdb-settings-message', response.data.message, 'error');
                        self.showToast(response.data.message, 'error');
                    }
                },
                error: function() {
                    self.showMessage('#wdb-settings-message', wdb_admin_vars.strings.error, 'error');
                    self.showToast(wdb_admin_vars.strings.error, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        },

        saveMethods: function($form) {
            const self = this;
            const $btn = $form.find('button[type="submit"]');
            const originalText = $btn.html();

            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Salvando...');

            $.ajax({
                url: wdb_admin_vars.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=wdb_save_methods',
                success: function(response) {
                    if (response.success) {
                        self.showToast(response.data.message, 'success');
                    } else {
                        self.showToast(response.data.message, 'error');
                    }
                },
                error: function() {
                    self.showToast(wdb_admin_vars.strings.error, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        },

        updateReceiptStatus: function(id, status, $row) {
            const self = this;

            $.ajax({
                url: wdb_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wdb_update_receipt_status',
                    nonce: wdb_admin_vars.nonce,
                    receipt_id: id,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        self.showToast(response.data.message, 'success');
                        
                        const statusClasses = {
                            'pending': 'bg-yellow-100 text-yellow-800',
                            'approved': 'bg-green-100 text-green-800',
                            'rejected': 'bg-red-100 text-red-800'
                        };
                        const statusLabels = {
                            'pending': 'Pendente',
                            'approved': 'Aprovado',
                            'rejected': 'Rejeitado'
                        };
                        
                        const $statusCell = $row.find('td:nth-child(4) span');
                        $statusCell
                            .removeClass('bg-yellow-100 text-yellow-800 bg-green-100 text-green-800 bg-red-100 text-red-800')
                            .addClass(statusClasses[status])
                            .text(statusLabels[status]);
                        
                        const $actionsCell = $row.find('td:last-child .flex');
                        
                        if (status === 'approved') {
                            $actionsCell.find('.wdb-approve-btn').remove();
                        } else if (status === 'rejected') {
                            $actionsCell.find('.wdb-reject-btn').remove();
                        }
                        
                        $row.css('background-color', status === 'approved' ? '#D1FAE5' : '#FEE2E2');
                        setTimeout(function() {
                            $row.css('background-color', '');
                        }, 1000);
                        
                    } else {
                        self.showToast(response.data.message, 'error');
                    }
                },
                error: function() {
                    self.showToast(wdb_admin_vars.strings.error, 'error');
                }
            });
        },

        deleteReceipt: function(id, $row) {
            const self = this;

            $.ajax({
                url: wdb_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wdb_delete_receipt',
                    nonce: wdb_admin_vars.nonce,
                    receipt_id: id
                },
                success: function(response) {
                    if (response.success) {
                        self.showToast(response.data.message, 'success');
                        
                        $row.fadeOut(300, function() {
                            $(this).remove();
                            
                            if ($('table tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        self.showToast(response.data.message, 'error');
                    }
                },
                error: function() {
                    self.showToast(wdb_admin_vars.strings.error, 'error');
                }
            });
        },

        showMessage: function(selector, message, type) {
            const $msg = $(selector);
            $msg.removeClass('hidden success error')
                .addClass(type)
                .html('<i class="fa-solid ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + ' mr-2"></i>' + message)
                .hide()
                .fadeIn(300);

            setTimeout(function() {
                $msg.fadeOut(300, function() {
                    $(this).addClass('hidden');
                });
            }, 5000);
        },

        showToast: function(message, type) {
            $('.wdb-toast').remove();

            const $toast = $('<div class="wdb-toast ' + type + '">' +
                '<i class="fa-solid ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + ' mr-2"></i>' +
                message + '</div>');

            $('body').append($toast);

            setTimeout(function() {
                $toast.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        },
        
        deleteAllDonations: function() {
            const self = this;
            
            // Primeira confirmação
            if (!confirm('⚠️ ATENÇÃO: Você está prestes a DELETAR TODAS AS DOAÇÕES!\n\nEsta ação é IRREVERSÍVEL.\n\nDeseja continuar?')) {
                return;
            }
            
            // Segunda confirmação com input
            const confirmText = prompt('Para confirmar, digite DELETAR (em maiúsculas):');
            if (confirmText !== 'DELETAR') {
                self.showToast('Ação cancelada. Digite DELETAR para confirmar.', 'error');
                return;
            }
            
            const $btn = $('#wdb-delete-all-donations-btn');
            const originalHtml = $btn.html();
            $btn.html('<i class="fa-solid fa-spinner fa-spin mr-2"></i>Deletando...').prop('disabled', true);
            
            $.ajax({
                url: wdb_admin_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'wdb_delete_all_donations',
                    wdb_nonce: wdb_admin_vars.nonce,
                    confirm: 'DELETAR'
                },
                success: function(response) {
                    if (response.success) {
                        self.showToast(response.data.message, 'success');
                        self.showMessage('#wdb-cache-message', response.data.message, 'success');
                    } else {
                        self.showToast(response.data.message, 'error');
                    }
                    $btn.html(originalHtml).prop('disabled', false);
                },
                error: function() {
                    self.showToast('Erro ao deletar doações.', 'error');
                    $btn.html(originalHtml).prop('disabled', false);
                }
            });
        }
    };

    $(document).ready(function() {
        WDBAdmin.init();
        
        // Botão deletar todas as doações
        $('#wdb-delete-all-donations-btn').on('click', function() {
            WDBAdmin.deleteAllDonations();
        });
    });

})(jQuery);

// Funções para offcanvas de métodos de pagamento
function wdbOpenMethodModal(index) {
    var offcanvas = document.getElementById('wdb-modal-' + index);
    var backdrop = offcanvas.querySelector('.wdb-offcanvas-backdrop');
    var panel = offcanvas.querySelector('.wdb-offcanvas-panel');
    
    offcanvas.style.visibility = 'visible';
    offcanvas.style.pointerEvents = 'auto';
    
    // Anima após um frame
    requestAnimationFrame(function() {
        backdrop.style.opacity = '1';
        panel.style.transform = 'translateX(0)';
    });
    
    document.body.style.overflow = 'hidden';
}

function wdbCloseMethodModal(index) {
    var offcanvas = document.getElementById('wdb-modal-' + index);
    var backdrop = offcanvas.querySelector('.wdb-offcanvas-backdrop');
    var panel = offcanvas.querySelector('.wdb-offcanvas-panel');
    
    backdrop.style.opacity = '0';
    panel.style.transform = 'translateX(100%)';
    
    // Esconde após animação
    setTimeout(function() {
        offcanvas.style.visibility = 'hidden';
        offcanvas.style.pointerEvents = 'none';
    }, 300);
    
    document.body.style.overflow = '';
}

function wdbSaveAndCloseModal(index) {
    wdbCloseMethodModal(index);
}

function wdbToggleMethodCard(checkbox, index) {
    var card = checkbox.closest('.rounded-xl');
    var badge = card.querySelector('.rounded-full');
    var icon = card.querySelector('.w-14');
    var modalSwitch = document.querySelector('.wdb-modal-switch[data-index="' + index + '"]');
    
    if (checkbox.checked) {
        card.classList.remove('border-gray-200', 'bg-white');
        card.classList.add('border-green-300', 'bg-green-50');
        badge.classList.remove('bg-gray-200', 'text-gray-600');
        badge.classList.add('bg-green-200', 'text-green-800');
        badge.textContent = 'Ativo';
        icon.classList.remove('text-gray-400');
        icon.classList.add('text-green-600');
    } else {
        card.classList.remove('border-green-300', 'bg-green-50');
        card.classList.add('border-gray-200', 'bg-white');
        badge.classList.remove('bg-green-200', 'text-green-800');
        badge.classList.add('bg-gray-200', 'text-gray-600');
        badge.textContent = 'Inativo';
        icon.classList.remove('text-green-600');
        icon.classList.add('text-gray-400');
    }
    
    // Sincroniza com o switch do modal
    if (modalSwitch) {
        modalSwitch.checked = checkbox.checked;
    }
}

// Sincroniza switch do modal com o card
jQuery(document).ready(function($) {
    $('.wdb-modal-switch').on('change', function() {
        var index = $(this).data('index');
        var cardSwitch = $('input[name="methods[' + index + '][enabled]"]');
        var label = $(this).siblings('.wdb-switch-label');
        
        // Atualiza label do switch
        if (this.checked) {
            label.text('Ativo');
        } else {
            label.text('Inativo');
        }
        
        cardSwitch.prop('checked', this.checked);
        wdbToggleMethodCard(cardSwitch[0], index);
    });
});

// Função global para copiar shortcode
function wdbCopyShortcode(text, btn) {
    // Cria textarea temporário para copiar
    var textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    textarea.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        // Feedback visual
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check" style="color:#10B981;"></i> <span style="color:#10B981;font-weight:bold;">Copiado!</span>';
        setTimeout(function() {
            btn.innerHTML = originalHtml;
        }, 2000);
    } catch (err) {
        alert('Erro ao copiar. Copie manualmente: ' + text);
    }
    
    document.body.removeChild(textarea);
}

// Media Uploader para logo do gateway
function wdbSelectLogo(index) {
    var mediaUploader = wp.media({
        title: 'Selecionar Logo',
        button: { text: 'Usar esta imagem' },
        multiple: false,
        library: { type: 'image' }
    });
    
    mediaUploader.on('select', function() {
        var attachment = mediaUploader.state().get('selection').first().toJSON();
        var input = document.getElementById('gateway_logo_' + index);
        var preview = document.getElementById('gateway_logo_preview_' + index);
        var removeBtn = document.getElementById('gateway_logo_remove_' + index);
        
        input.value = attachment.url;
        preview.innerHTML = '<img src="' + attachment.url + '" alt="Logo" class="max-w-full max-h-full object-contain">';
        removeBtn.classList.remove('hidden');
    });
    
    mediaUploader.open();
}

function wdbRemoveLogo(index) {
    var input = document.getElementById('gateway_logo_' + index);
    var preview = document.getElementById('gateway_logo_preview_' + index);
    var removeBtn = document.getElementById('gateway_logo_remove_' + index);
    
    input.value = '';
    preview.innerHTML = '<i class="fa-solid fa-image text-gray-300 text-2xl"></i>';
    removeBtn.classList.add('hidden');
}

// Cache de bancos da BrasilAPI
var wdbBanksCache = null;
var wdbBankSearchTimeout = null;

// Busca bancos na BrasilAPI
function wdbSearchBank(input, index) {
    var query = input.value.toLowerCase().trim();
    var resultsDiv = document.getElementById('wdb-bank-results-' + index);
    
    if (query.length < 2) {
        resultsDiv.classList.add('hidden');
        return;
    }
    
    clearTimeout(wdbBankSearchTimeout);
    wdbBankSearchTimeout = setTimeout(function() {
        if (wdbBanksCache) {
            wdbFilterBanks(query, index);
        } else {
            resultsDiv.innerHTML = '<div class="p-3 text-center text-gray-500"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Carregando bancos...</div>';
            resultsDiv.classList.remove('hidden');
            
            fetch('https://brasilapi.com.br/api/banks/v1')
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    wdbBanksCache = data.filter(function(b) { return b.code !== null; });
                    wdbFilterBanks(query, index);
                })
                .catch(function() {
                    resultsDiv.innerHTML = '<div class="p-3 text-center text-red-500">Erro ao carregar bancos</div>';
                });
        }
    }, 300);
}

// Filtra bancos pelo nome ou código
function wdbFilterBanks(query, index) {
    var resultsDiv = document.getElementById('wdb-bank-results-' + index);
    var filtered = wdbBanksCache.filter(function(bank) {
        return bank.name.toLowerCase().includes(query) || 
               bank.fullName.toLowerCase().includes(query) ||
               (bank.code && bank.code.toString().includes(query));
    }).slice(0, 10);
    
    if (filtered.length === 0) {
        resultsDiv.innerHTML = '<div class="p-3 text-center text-gray-500">Nenhum banco encontrado</div>';
        resultsDiv.classList.remove('hidden');
        return;
    }
    
    var html = '';
    filtered.forEach(function(bank) {
        html += '<div class="p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0" onclick="wdbSelectBank(' + index + ', \'' + bank.name.replace(/'/g, "\\'") + '\', ' + bank.code + ')">';
        html += '<div class="font-semibold text-gray-800">' + bank.name + '</div>';
        html += '<div class="text-xs text-gray-500">Código: ' + bank.code + '</div>';
        html += '</div>';
    });
    
    resultsDiv.innerHTML = html;
    resultsDiv.classList.remove('hidden');
}

// Seleciona banco
function wdbSelectBank(index, name, code) {
    document.getElementById('wdb-bank-name-' + index).value = name;
    document.getElementById('wdb-bank-code-' + index).value = code;
    document.getElementById('wdb-bank-search-' + index).value = '';
    document.getElementById('wdb-bank-results-' + index).classList.add('hidden');
}

// Fecha resultados ao clicar fora
document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="wdb-bank-search-"]') && !e.target.closest('[id^="wdb-bank-results-"]')) {
        document.querySelectorAll('[id^="wdb-bank-results-"]').forEach(function(el) {
            el.classList.add('hidden');
        });
    }
});

// Máscara dinâmica CPF/CNPJ
function wdbMaskCpfCnpj(input) {
    var value = input.value.replace(/\D/g, '');
    
    if (value.length <= 11) {
        // CPF: 000.000.000-00
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        // CNPJ: 00.000.000/0000-00
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    }
    
    input.value = value;
}
