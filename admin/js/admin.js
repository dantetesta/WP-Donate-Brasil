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

            $.ajax({
                url: wdb_admin_vars.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=wdb_save_settings',
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
                        self.showMessage('#wdb-methods-message', response.data.message, 'success');
                        self.showToast(response.data.message, 'success');
                    } else {
                        self.showMessage('#wdb-methods-message', response.data.message, 'error');
                        self.showToast(response.data.message, 'error');
                    }
                },
                error: function() {
                    self.showMessage('#wdb-methods-message', wdb_admin_vars.strings.error, 'error');
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
    var backdrop = offcanvas.querySelector('.bg-black\\/50');
    var panel = offcanvas.querySelector('.max-w-lg');
    
    offcanvas.classList.remove('invisible');
    
    // Anima após um frame
    requestAnimationFrame(function() {
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');
        panel.classList.remove('translate-x-full');
        panel.classList.add('translate-x-0');
    });
    
    document.body.style.overflow = 'hidden';
}

function wdbCloseMethodModal(index) {
    var offcanvas = document.getElementById('wdb-modal-' + index);
    var backdrop = offcanvas.querySelector('.bg-black\\/50');
    var panel = offcanvas.querySelector('.max-w-lg');
    
    backdrop.classList.remove('opacity-100');
    backdrop.classList.add('opacity-0');
    panel.classList.remove('translate-x-0');
    panel.classList.add('translate-x-full');
    
    // Esconde após animação
    setTimeout(function() {
        offcanvas.classList.add('invisible');
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
        cardSwitch.prop('checked', this.checked).trigger('change');
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
