<?php
/**
 * Classe responsável pelo painel administrativo
 * 
 * @package WP_Donate_Brasil
 * @author Dante Testa <https://dantetesta.com.br>
 * @since 1.0.0
 * @created 23/12/2025 09:21
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDB_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_wdb_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_wdb_save_methods', array($this, 'ajax_save_methods'));
        add_action('wp_ajax_wdb_clear_cache', array($this, 'ajax_clear_cache'));
        add_action('wp_ajax_wdb_clear_transients', array($this, 'ajax_clear_transients'));
        add_action('wp_ajax_wdb_delete_all_donations', array($this, 'ajax_delete_all_donations'));
    }
    
    public function add_admin_menu() {
        // Menu principal abre Dashboard
        add_menu_page(
            __('WP Donate Brasil', 'wp-donate-brasil'),
            __('Doações', 'wp-donate-brasil'),
            'manage_options',
            'wdb_dashboard',
            array($this, 'render_dashboard_page'),
            'dashicons-heart',
            30
        );
        
        // Submenus na ordem: Dashboard, Comprovantes, Métodos, Configurações
        add_submenu_page(
            'wdb_dashboard',
            __('Dashboard', 'wp-donate-brasil'),
            __('Dashboard', 'wp-donate-brasil'),
            'manage_options',
            'wdb_dashboard',
            array($this, 'render_dashboard_page')
        );
        
        add_submenu_page(
            'wdb_dashboard',
            __('Comprovantes', 'wp-donate-brasil'),
            __('Comprovantes', 'wp-donate-brasil'),
            'manage_options',
            'wdb_receipts',
            array($this, 'render_receipts_page')
        );
        
        add_submenu_page(
            'wdb_dashboard',
            __('Métodos de Doação', 'wp-donate-brasil'),
            __('Métodos', 'wp-donate-brasil'),
            'manage_options',
            'wdb_donation_methods',
            array($this, 'render_methods_page')
        );
        
        add_submenu_page(
            'wdb_dashboard',
            __('Configurações', 'wp-donate-brasil'),
            __('Configurações', 'wp-donate-brasil'),
            'manage_options',
            'wdb_donation_settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('wdb_settings_group', 'wdb_page_settings');
        register_setting('wdb_settings_group', 'wdb_donation_methods');
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Sem permissão.', 'wp-donate-brasil'));
        }
        
        $settings = get_option('wdb_page_settings', array());
        $page_id = get_option('wdb_donation_page_id');
        $page_url = $page_id ? get_permalink($page_id) : '';
        $primary_color = esc_attr($settings['primary_color'] ?? '#3B82F6');
        $secondary_color = esc_attr($settings['secondary_color'] ?? '#10B981');
        ?>
        <style>
            /* Switch Toggle Moderno */
            .wdb-switch {
                position: relative;
                display: inline-flex;
                align-items: center;
                cursor: pointer;
            }
            .wdb-switch input {
                opacity: 0;
                width: 0;
                height: 0;
                position: absolute;
            }
            .wdb-switch-slider {
                position: relative;
                width: 44px;
                height: 24px;
                background: #cbd5e1;
                border-radius: 24px;
                transition: all 0.3s ease;
            }
            .wdb-switch-slider::before {
                content: '';
                position: absolute;
                width: 18px;
                height: 18px;
                background: white;
                border-radius: 50%;
                top: 3px;
                left: 3px;
                transition: all 0.3s ease;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }
            .wdb-switch input:checked + .wdb-switch-slider {
                background: linear-gradient(135deg, #3B82F6, #10B981);
            }
            .wdb-switch input:checked + .wdb-switch-slider::before {
                transform: translateX(20px);
            }
            .wdb-switch input:focus + .wdb-switch-slider {
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            }
            .wdb-switch-label {
                margin-left: 10px;
                font-size: 0.875rem;
                color: #374151;
                user-select: none;
            }
        </style>
        <div class="wrap">
            <div class="max-w-4xl mx-auto py-8">
                <!-- Header Colorido -->
                <div class="rounded-2xl p-6 mb-8 shadow-lg" style="background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                                <i class="fa-solid fa-cog"></i>
                                <?php _e('Configurações', 'wp-donate-brasil'); ?>
                            </h1>
                            <p class="text-white/80 mt-1"><?php _e('Personalize a página de doações do seu site', 'wp-donate-brasil'); ?></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-lg px-4 py-2 text-white font-semibold" style="background: rgba(0,0,0,0.25);">
                                v<?php echo WDB_VERSION; ?>
                            </span>
                            <?php if ($page_url): ?>
                            <a href="<?php echo esc_url($page_url); ?>" target="_blank" 
                               class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg flex items-center gap-2 transition-colors font-medium">
                                <i class="fa-solid fa-external-link"></i>
                                <?php _e('Ver Página', 'wp-donate-brasil'); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs Navigation -->
                <div class="bg-white rounded-xl shadow-md mb-6 border border-gray-100">
                    <div class="flex flex-wrap border-b border-gray-200">
                        <button type="button" class="wdb-tab-btn active px-6 py-3 text-sm font-medium text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-500 transition-all" data-tab="tab-pagina">
                            <i class="fa-solid fa-file-alt mr-2"></i><?php _e('Página', 'wp-donate-brasil'); ?>
                        </button>
                        <button type="button" class="wdb-tab-btn px-6 py-3 text-sm font-medium text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-500 transition-all" data-tab="tab-galeria">
                            <i class="fa-solid fa-images mr-2"></i><?php _e('Galeria', 'wp-donate-brasil'); ?>
                        </button>
                        <button type="button" class="wdb-tab-btn px-6 py-3 text-sm font-medium text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-500 transition-all" data-tab="tab-emails">
                            <i class="fa-solid fa-envelope mr-2"></i><?php _e('E-mails', 'wp-donate-brasil'); ?>
                        </button>
                        <button type="button" class="wdb-tab-btn px-6 py-3 text-sm font-medium text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-500 transition-all" data-tab="tab-ferramentas">
                            <i class="fa-solid fa-tools mr-2"></i><?php _e('Ferramentas', 'wp-donate-brasil'); ?>
                        </button>
                    </div>
                </div>
                
                <form id="wdb-settings-form" class="space-y-6">
                    <?php wp_nonce_field('wdb_nonce_action', 'wdb_nonce'); ?>
                    
                    <!-- TAB: Página -->
                    <div id="tab-pagina" class="wdb-tab-content space-y-6">
                    
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-file-alt text-blue-500"></i>
                            <?php _e('Configurações da Página', 'wp-donate-brasil'); ?>
                        </h2>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Frase de Destaque', 'wp-donate-brasil'); ?></label>
                            <input type="text" name="page_highlight" value="<?php echo esc_attr($settings['page_highlight'] ?? 'Transforme vidas com sua doação'); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="<?php esc_attr_e('Ex: Transforme vidas com sua doação', 'wp-donate-brasil'); ?>">
                            <p class="text-xs text-gray-500 mt-1"><?php _e('Aparece no topo da página dentro do bloquinho com ícone de coração', 'wp-donate-brasil'); ?></p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Título da Página', 'wp-donate-brasil'); ?></label>
                                <input type="text" name="page_title" value="<?php echo esc_attr($settings['page_title'] ?? ''); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Subtítulo', 'wp-donate-brasil'); ?></label>
                                <input type="text" name="page_subtitle" value="<?php echo esc_attr($settings['page_subtitle'] ?? ''); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Descrição', 'wp-donate-brasil'); ?></label>
                            <textarea name="page_description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo esc_textarea($settings['page_description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-palette text-purple-500"></i>
                            <?php _e('Design Visual', 'wp-donate-brasil'); ?>
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Cor Primária', 'wp-donate-brasil'); ?></label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="primary_color" value="<?php echo esc_attr($settings['primary_color'] ?? '#3B82F6'); ?>"
                                        class="w-12 h-10 rounded cursor-pointer border-0">
                                    <span class="text-xs text-gray-500"><?php _e('Botões e destaques', 'wp-donate-brasil'); ?></span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Cor Secundária', 'wp-donate-brasil'); ?></label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="secondary_color" value="<?php echo esc_attr($settings['secondary_color'] ?? '#10B981'); ?>"
                                        class="w-12 h-10 rounded cursor-pointer border-0">
                                    <span class="text-xs text-gray-500"><?php _e('Gradientes', 'wp-donate-brasil'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    </div><!-- /TAB: Página -->
                    
                    <!-- TAB: Galeria -->
                    <div id="tab-galeria" class="wdb-tab-content space-y-6" style="display:none;">
                    
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-images text-green-500"></i>
                            <?php _e('Galeria de Doadores', 'wp-donate-brasil'); ?>
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="show_gallery" <?php checked(!empty($settings['show_gallery'])); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Exibir galeria de doadores', 'wp-donate-brasil'); ?></span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Título da Galeria', 'wp-donate-brasil'); ?></label>
                                <input type="text" name="gallery_title" value="<?php echo esc_attr($settings['gallery_title'] ?? ''); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Itens no Carrossel', 'wp-donate-brasil'); ?></label>
                                <input type="number" name="items_per_page" value="<?php echo esc_attr($settings['items_per_page'] ?? 12); ?>" min="1" max="50"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Página de Lista Completa (slug)', 'wp-donate-brasil'); ?></label>
                            <input type="text" name="donors_page_slug" value="<?php echo esc_attr($settings['donors_page_slug'] ?? 'doadores'); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="doadores">
                            <p class="text-xs text-gray-500 mt-1"><?php _e('Crie uma página com o shortcode [wdb_donors_list] e insira o slug aqui.', 'wp-donate-brasil'); ?></p>
                        </div>
                        
                        <div class="border-t pt-4 mt-4">
                            <h3 class="font-semibold text-gray-700 mb-3"><?php _e('Campos visíveis na Lista de Doadores:', 'wp-donate-brasil'); ?></h3>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                <label class="wdb-switch">
                                    <input type="checkbox" name="list_show_photo" <?php checked($settings['list_show_photo'] ?? true); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Foto', 'wp-donate-brasil'); ?></span>
                                </label>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="list_show_name" <?php checked($settings['list_show_name'] ?? true); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Nome', 'wp-donate-brasil'); ?></span>
                                </label>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="list_show_email" <?php checked($settings['list_show_email'] ?? false); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('E-mail', 'wp-donate-brasil'); ?></span>
                                </label>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="list_show_phone" <?php checked($settings['list_show_phone'] ?? false); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Telefone', 'wp-donate-brasil'); ?></span>
                                </label>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="list_show_value" <?php checked($settings['list_show_value'] ?? false); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Valor', 'wp-donate-brasil'); ?></span>
                                </label>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="list_show_count" <?php checked($settings['list_show_count'] ?? true); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Qtd. Doações', 'wp-donate-brasil'); ?></span>
                                </label>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="list_show_date" <?php checked($settings['list_show_date'] ?? true); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Data', 'wp-donate-brasil'); ?></span>
                                </label>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="list_show_method" <?php checked($settings['list_show_method'] ?? false); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Método', 'wp-donate-brasil'); ?></span>
                                </label>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="list_show_message" <?php checked($settings['list_show_message'] ?? true); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Mensagem', 'wp-donate-brasil'); ?></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="border-t pt-4 mt-4">
                            <h3 class="font-semibold text-gray-700 mb-3"><?php _e('Filtros na Lista de Doadores:', 'wp-donate-brasil'); ?></h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <label class="wdb-switch">
                                    <input type="checkbox" name="filter_search" <?php checked($settings['filter_search'] ?? true); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Busca', 'wp-donate-brasil'); ?></span>
                                </label>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="filter_method" <?php checked($settings['filter_method'] ?? true); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Método', 'wp-donate-brasil'); ?></span>
                                </label>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="filter_month" <?php checked($settings['filter_month'] ?? true); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Mês/Ano', 'wp-donate-brasil'); ?></span>
                                </label>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="filter_order" <?php checked($settings['filter_order'] ?? true); ?>>
                                    <span class="wdb-switch-slider"></span>
                                    <span class="wdb-switch-label"><?php _e('Ordenação', 'wp-donate-brasil'); ?></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="border-t pt-4 mt-4">
                            <h3 class="font-semibold text-gray-700 mb-3"><?php _e('Outras opções:', 'wp-donate-brasil'); ?></h3>
                            <label class="wdb-switch">
                                <input type="checkbox" name="show_credits" <?php checked($settings['show_credits'] ?? true); ?>>
                                <span class="wdb-switch-slider"></span>
                                <span class="wdb-switch-label"><?php _e('Exibir créditos do desenvolvedor', 'wp-donate-brasil'); ?></span>
                            </label>
                        </div>
                    </div>
                    
                    </div><!-- /TAB: Galeria -->
                    
                    <!-- TAB: E-mails -->
                    <div id="tab-emails" class="wdb-tab-content space-y-6" style="display:none;">
                    
                    <!-- Mensagem de Agradecimento -->
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-hand-holding-heart text-pink-500"></i>
                            <?php _e('Mensagem de Agradecimento', 'wp-donate-brasil'); ?>
                        </h2>
                        <p class="text-sm text-gray-500 mb-4"><?php _e('Exibida após o doador enviar o comprovante, com animação de confetes.', 'wp-donate-brasil'); ?></p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Título da Mensagem', 'wp-donate-brasil'); ?></label>
                                <input type="text" name="thank_you_title" value="<?php echo esc_attr($settings['thank_you_title'] ?? 'Muito Obrigado! 🙏'); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Subtítulo', 'wp-donate-brasil'); ?></label>
                                <input type="text" name="thank_you_subtitle" value="<?php echo esc_attr($settings['thank_you_subtitle'] ?? 'Sua doação faz a diferença!'); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Mensagem Completa', 'wp-donate-brasil'); ?></label>
                            <textarea name="thank_you_message" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo esc_textarea($settings['thank_you_message'] ?? 'Recebemos seu comprovante e em breve confirmaremos sua doação. Que Deus abençoe você e sua família! ❤️'); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Configurações de E-mail -->
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-blue-500"></i>
                            <?php _e('Notificações por E-mail', 'wp-donate-brasil'); ?>
                        </h2>
                        
                        <!-- Configurações de Notificações -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-700 flex items-center gap-2">
                                            <i class="fa-solid fa-user-shield text-yellow-600"></i>
                                            <?php _e('Notificar Administrador', 'wp-donate-brasil'); ?>
                                        </h4>
                                        <p class="text-xs text-gray-500 mt-1"><?php _e('Recebe e-mail de novas doações', 'wp-donate-brasil'); ?></p>
                                    </div>
                                    <label class="wdb-switch">
                                        <input type="checkbox" name="emails_notify_admin" <?php checked($settings['emails_notify_admin'] ?? true); ?>>
                                        <span class="wdb-switch-slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-700 flex items-center gap-2">
                                            <i class="fa-solid fa-user text-green-600"></i>
                                            <?php _e('Notificar Doador', 'wp-donate-brasil'); ?>
                                        </h4>
                                        <p class="text-xs text-gray-500 mt-1"><?php _e('Recebe confirmações e status', 'wp-donate-brasil'); ?></p>
                                    </div>
                                    <label class="wdb-switch">
                                        <input type="checkbox" name="emails_notify_donor" <?php checked($settings['emails_notify_donor'] ?? true); ?>>
                                        <span class="wdb-switch-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-500 mb-4"><?php _e('Configure os e-mails enviados automaticamente. Use macros: {nome}, {email}, {valor}, {metodo}, {data}, {mensagem}', 'wp-donate-brasil'); ?></p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Nome do Remetente', 'wp-donate-brasil'); ?></label>
                                <input type="text" name="email_sender_name" value="<?php echo esc_attr($settings['email_sender_name'] ?? get_bloginfo('name')); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>">
                                <p class="text-xs text-gray-500 mt-1"><?php _e('Aparece no cabeçalho do email e como remetente', 'wp-donate-brasil'); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('E-mail do Administrador', 'wp-donate-brasil'); ?></label>
                                <input type="email" name="admin_email" value="<?php echo esc_attr($settings['admin_email'] ?? get_option('admin_email')); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1"><?php _e('Para receber notificações de novas doações', 'wp-donate-brasil'); ?></p>
                            </div>
                        </div>
                        
                        <!-- E-mail: Nova doação para Admin -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <h3 class="font-semibold text-gray-700 flex items-center gap-2 mb-3">
                                <i class="fa-solid fa-bell text-yellow-500"></i>
                                <?php _e('Nova Doação (para Admin)', 'wp-donate-brasil'); ?>
                            </h3>
                            <div class="grid grid-cols-1 gap-3">
                                <input type="text" name="email_admin_new_subject" 
                                    value="<?php echo esc_attr($settings['email_admin_new_subject'] ?? '🔔 Nova doação recebida de {nome}'); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="<?php _e('Assunto', 'wp-donate-brasil'); ?>">
                                <textarea name="email_admin_new_body" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"><?php echo esc_textarea($settings['email_admin_new_body'] ?? "Nova doação recebida!\n\nDoador: {nome}\nE-mail: {email}\nValor: R$ {valor}\nMétodo: {metodo}\nData: {data}\nMensagem: {mensagem}\n\nAcesse o painel para aprovar."); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- E-mail: Comprovante Recebido (para Doador) -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <h3 class="font-semibold text-gray-700 flex items-center gap-2 mb-3">
                                <i class="fa-solid fa-inbox text-blue-500"></i>
                                <?php _e('Comprovante Recebido (para Doador)', 'wp-donate-brasil'); ?>
                            </h3>
                            <div class="grid grid-cols-1 gap-3">
                                <input type="text" name="email_donor_received_subject" 
                                    value="<?php echo esc_attr($settings['email_donor_received_subject'] ?? '📩 Recebemos sua doação, {nome}!'); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="<?php _e('Assunto', 'wp-donate-brasil'); ?>">
                                <textarea name="email_donor_received_body" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"><?php echo esc_textarea($settings['email_donor_received_body'] ?? "Olá {nome},\n\nRecebemos seu comprovante de doação no valor de R$ {valor}.\n\nSeu comprovante está sendo analisado e em breve você receberá a confirmação.\n\nMuito obrigado pelo seu apoio! ❤️"); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- E-mail: Doação Aprovada (para Doador) -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-700 flex items-center gap-2 mb-3">
                                <i class="fa-solid fa-check-circle text-green-500"></i>
                                <?php _e('Doação Aprovada (para Doador)', 'wp-donate-brasil'); ?>
                            </h3>
                            <div class="grid grid-cols-1 gap-3">
                                <input type="text" name="email_donor_approved_subject" 
                                    value="<?php echo esc_attr($settings['email_donor_approved_subject'] ?? '✅ Sua doação foi confirmada, {nome}!'); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="<?php _e('Assunto', 'wp-donate-brasil'); ?>">
                                <textarea name="email_donor_approved_body" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"><?php echo esc_textarea($settings['email_donor_approved_body'] ?? "Olá {nome},\n\n🎉 Sua doação no valor de R$ {valor} foi confirmada com sucesso!\n\nMuito obrigado por fazer parte desta causa. Sua generosidade faz toda a diferença!\n\nQue Deus abençoe você e sua família! 🙏❤️"); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    </div><!-- /TAB: E-mails -->
                    
                    <!-- TAB: Ferramentas -->
                    <div id="tab-ferramentas" class="wdb-tab-content space-y-6" style="display:none;">
                    
                    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
                        <h3 class="font-bold text-blue-800 mb-2">
                            <i class="fa-solid fa-code mr-2"></i>
                            <?php _e('Shortcodes Disponíveis', 'wp-donate-brasil'); ?>
                        </h3>
                        <div class="space-y-2 text-sm text-blue-700">
                            <p class="flex items-center gap-2">
                                <code class="bg-white px-2 py-1 rounded">[wp_donate_brasil_page]</code>
                                <button type="button" onclick="wdbCopyShortcode('[wp_donate_brasil_page]', this)" class="text-blue-500 hover:text-blue-700" title="Copiar"><i class="fa-solid fa-copy"></i></button>
                                - <?php _e('Página completa de doações', 'wp-donate-brasil'); ?>
                            </p>
                            <p class="flex items-center gap-2">
                                <code class="bg-white px-2 py-1 rounded">[wp_donate_brasil_gallery]</code>
                                <button type="button" onclick="wdbCopyShortcode('[wp_donate_brasil_gallery]', this)" class="text-blue-500 hover:text-blue-700" title="Copiar"><i class="fa-solid fa-copy"></i></button>
                                - <?php _e('Apenas galeria de doadores', 'wp-donate-brasil'); ?>
                            </p>
                            <p class="flex items-center gap-2">
                                <code class="bg-white px-2 py-1 rounded">[wdb_donors_list]</code>
                                <button type="button" onclick="wdbCopyShortcode('[wdb_donors_list]', this)" class="text-blue-500 hover:text-blue-700" title="Copiar"><i class="fa-solid fa-copy"></i></button>
                                - <?php _e('Lista completa de doadores (com paginação)', 'wp-donate-brasil'); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Ferramentas de Manutenção -->
                    <div class="bg-red-50 rounded-xl p-6 border border-red-200">
                        <h3 class="font-bold text-red-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-tools"></i>
                            <?php _e('Ferramentas de Manutenção', 'wp-donate-brasil'); ?>
                        </h3>
                        <div class="flex flex-wrap gap-4">
                            <button type="button" id="wdb-clear-cache-btn" 
                                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-broom"></i>
                                <?php _e('Limpar Cache do Plugin', 'wp-donate-brasil'); ?>
                            </button>
                            <button type="button" id="wdb-clear-transients-btn" 
                                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-database"></i>
                                <?php _e('Limpar Transientes', 'wp-donate-brasil'); ?>
                            </button>
                        </div>
                        <p class="text-xs text-red-600 mt-3">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            <?php _e('Use estas opções se houver problemas de exibição ou dados desatualizados.', 'wp-donate-brasil'); ?>
                        </p>
                        <div id="wdb-cache-message" class="hidden mt-3"></div>
                        
                        <!-- Zona de Perigo -->
                        <div class="mt-6 pt-4 border-t border-red-300">
                            <h4 class="font-bold text-red-700 mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-skull-crossbones"></i>
                                <?php _e('Zona de Perigo', 'wp-donate-brasil'); ?>
                            </h4>
                            <button type="button" id="wdb-delete-all-donations-btn" 
                                    class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-trash-alt"></i>
                                <?php _e('Deletar Todas as Doações', 'wp-donate-brasil'); ?>
                            </button>
                            <p class="text-xs text-red-700 mt-2">
                                <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                                <?php _e('ATENÇÃO: Esta ação é irreversível! Todos os comprovantes e dados serão excluídos permanentemente.', 'wp-donate-brasil'); ?>
                            </p>
                        </div>
                    </div>
                    
                    </div><!-- /TAB: Ferramentas -->
                    
                    <div class="flex justify-end mt-6">
                        <button type="submit" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-save"></i>
                            <?php _e('Salvar Configurações', 'wp-donate-brasil'); ?>
                        </button>
                    </div>
                </form>
                
                <div id="wdb-settings-message" class="hidden mt-4"></div>
            </div>
        </div>
        <?php
    }
    
    public function render_methods_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Sem permissão.', 'wp-donate-brasil'));
        }
        
        $methods = get_option('wdb_donation_methods', array());
        $page_settings = get_option('wdb_page_settings', array());
        $primary_color = esc_attr($page_settings['primary_color'] ?? '#3B82F6');
        $secondary_color = esc_attr($page_settings['secondary_color'] ?? '#10B981');
        ?>
        <style>
            .wdb-switch {
                position: relative;
                display: inline-flex;
                align-items: center;
                cursor: pointer;
            }
            .wdb-switch input {
                opacity: 0;
                width: 0;
                height: 0;
                position: absolute;
            }
            .wdb-switch-slider {
                position: relative;
                width: 44px;
                height: 24px;
                background: #cbd5e1;
                border-radius: 24px;
                transition: all 0.3s ease;
            }
            .wdb-switch-slider::before {
                content: '';
                position: absolute;
                width: 18px;
                height: 18px;
                background: white;
                border-radius: 50%;
                top: 3px;
                left: 3px;
                transition: all 0.3s ease;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }
            .wdb-switch input:checked + .wdb-switch-slider {
                background: linear-gradient(135deg, #10B981, #059669);
            }
            .wdb-switch input:checked + .wdb-switch-slider::before {
                transform: translateX(20px);
            }
            .wdb-switch-label {
                margin-left: 10px;
                font-size: 0.875rem;
                color: #374151;
                font-weight: 500;
            }
        </style>
        <div class="wrap">
            <div class="max-w-4xl mx-auto py-8">
                <!-- Header Colorido -->
                <div class="rounded-2xl p-6 mb-8 shadow-lg" style="background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                                <i class="fa-solid fa-credit-card"></i>
                                <?php _e('Métodos de Doação', 'wp-donate-brasil'); ?>
                            </h1>
                            <p class="text-white/80 mt-1"><?php _e('Configure os métodos de pagamento disponíveis', 'wp-donate-brasil'); ?></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-lg px-4 py-2 text-white" style="background: rgba(0,0,0,0.25);">
                                <i class="fa-solid fa-check-circle mr-1"></i>
                                <?php 
                                $active_count = count(array_filter($methods, function($m) { return !empty($m['enabled']); }));
                                printf(__('%d ativos', 'wp-donate-brasil'), $active_count); 
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <form id="wdb-methods-form">
                    <?php wp_nonce_field('wdb_nonce_action', 'wdb_nonce'); ?>
                    
                    <!-- Grid de Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                        <?php foreach ($methods as $index => $method): 
                            $is_enabled = !empty($method['enabled']);
                            $card_border = $is_enabled ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-white';
                            $icon_color = $is_enabled ? 'text-green-600' : 'text-gray-400';
                        ?>
                        <div class="rounded-xl shadow-md p-6 border-2 <?php echo $card_border; ?> transition-all hover:shadow-lg">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center <?php echo $icon_color; ?> text-2xl">
                                    <i class="<?php echo esc_attr($method['icon']); ?>"></i>
                                </div>
                                <label class="wdb-switch">
                                    <input type="checkbox" name="methods[<?php echo $index; ?>][enabled]" 
                                           <?php checked($is_enabled); ?> 
                                           onchange="wdbToggleMethodCard(this, <?php echo $index; ?>)">
                                    <span class="wdb-switch-slider"></span>
                                </label>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1"><?php echo esc_html($method['name']); ?></h3>
                            <p class="text-sm text-gray-500 mb-3"><?php echo esc_html(mb_strimwidth($method['instructions'] ?? __('Configure este método', 'wp-donate-brasil'), 0, 50, '...')); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium px-2 py-1 rounded-full <?php echo $is_enabled ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-600'; ?>">
                                    <?php echo $is_enabled ? __('Ativo', 'wp-donate-brasil') : __('Inativo', 'wp-donate-brasil'); ?>
                                </span>
                                <button type="button" onclick="wdbOpenMethodModal(<?php echo $index; ?>)" class="text-blue-500 hover:text-blue-700 text-sm font-medium hover:underline cursor-pointer">
                                    <i class="fa-solid fa-cog mr-1"></i><?php _e('Configurar', 'wp-donate-brasil'); ?>
                                </button>
                            </div>
                            
                            <!-- Inputs hidden -->
                            <input type="hidden" name="methods[<?php echo $index; ?>][id]" value="<?php echo esc_attr($method['id']); ?>">
                            <input type="hidden" name="methods[<?php echo $index; ?>][name]" value="<?php echo esc_attr($method['name']); ?>">
                            <input type="hidden" name="methods[<?php echo $index; ?>][icon]" value="<?php echo esc_attr($method['icon']); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-save"></i>
                            <?php _e('Salvar Métodos', 'wp-donate-brasil'); ?>
                        </button>
                    </div>
                    
                    <!-- Modais -->
                    <?php foreach ($methods as $index => $method): ?>
                    <div id="wdb-modal-<?php echo $index; ?>" class="wdb-offcanvas fixed inset-0 z-50" style="visibility: hidden; pointer-events: none;">
                        <div class="wdb-offcanvas-backdrop fixed inset-0 bg-black/50 opacity-0 transition-opacity duration-300" onclick="wdbCloseMethodModal(<?php echo $index; ?>)"></div>
                        <div class="wdb-offcanvas-panel fixed right-0 w-full max-w-lg bg-white shadow-2xl overflow-hidden flex flex-col transition-transform duration-300" style="top: 32px; height: calc(100vh - 32px); transform: translateX(100%);">
                            <!-- Modal Header -->
                            <div class="p-6 border-b border-gray-200 flex items-center justify-between" style="background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-white text-xl">
                                        <i class="<?php echo esc_attr($method['icon']); ?>"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-white"><?php echo esc_html($method['name']); ?></h2>
                                        <p class="text-white/70 text-sm"><?php _e('Configure as opções deste método', 'wp-donate-brasil'); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="wdb-switch" style="--slider-bg: rgba(255,255,255,0.3);">
                                        <input type="checkbox" class="wdb-modal-switch" data-index="<?php echo $index; ?>"
                                               <?php checked(!empty($method['enabled'])); ?>>
                                        <span class="wdb-switch-slider"></span>
                                    </label>
                                    <button type="button" onclick="event.preventDefault(); event.stopPropagation(); wdbCloseMethodModal(<?php echo $index; ?>); return false;" class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition-colors">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Modal Body -->
                            <div class="flex-1 overflow-y-auto p-6">
                                <div class="max-w-2xl mx-auto space-y-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Instruções para o doador', 'wp-donate-brasil'); ?></label>
                                        <textarea name="methods[<?php echo $index; ?>][instructions]" rows="3"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            placeholder="<?php esc_attr_e('Digite as instruções que aparecerão para o doador...', 'wp-donate-brasil'); ?>"><?php echo esc_textarea($method['instructions'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <hr class="border-gray-200">
                                    
                                    <h3 class="font-bold text-gray-800"><?php _e('Configurações específicas', 'wp-donate-brasil'); ?></h3>
                                    
                                    <?php $this->render_method_fields($method, $index); ?>
                                </div>
                            </div>
                            
                            <!-- Modal Footer -->
                            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                                <button type="button" onclick="event.preventDefault(); wdbCloseMethodModal(<?php echo $index; ?>); return false;" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                    <?php _e('Fechar', 'wp-donate-brasil'); ?>
                                </button>
                                <button type="button" onclick="event.preventDefault(); wdbCloseMethodModal(<?php echo $index; ?>); return false;" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors flex items-center gap-2">
                                    <i class="fa-solid fa-check"></i>
                                    <?php _e('Aplicar', 'wp-donate-brasil'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </form>
                
                <div id="wdb-methods-message" class="hidden mt-4"></div>
            </div>
        </div>
        <?php
    }
    
    private function render_method_fields($method, $index) {
        switch ($method['id']) {
            case 'pix':
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Chave PIX', 'wp-donate-brasil'); ?> <span class="text-red-500">*</span></label>
                        <input type="text" name="methods[<?php echo $index; ?>][pix_key]" value="<?php echo esc_attr($method['pix_key'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="<?php esc_attr_e('CPF, CNPJ, E-mail, Telefone ou Chave Aleatória', 'wp-donate-brasil'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Nome do Titular', 'wp-donate-brasil'); ?> <span class="text-red-500">*</span></label>
                        <input type="text" name="methods[<?php echo $index; ?>][pix_name]" value="<?php echo esc_attr($method['pix_name'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="<?php esc_attr_e('Nome que aparece no QR Code', 'wp-donate-brasil'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Cidade', 'wp-donate-brasil'); ?></label>
                        <input type="text" name="methods[<?php echo $index; ?>][pix_city]" value="<?php echo esc_attr($method['pix_city'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="<?php esc_attr_e('Ex: SAO PAULO', 'wp-donate-brasil'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Banco', 'wp-donate-brasil'); ?></label>
                        <input type="text" name="methods[<?php echo $index; ?>][pix_bank]" value="<?php echo esc_attr($method['pix_bank'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="<?php esc_attr_e('Ex: Nubank, Itaú, etc', 'wp-donate-brasil'); ?>">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Descrição (opcional)', 'wp-donate-brasil'); ?></label>
                        <input type="text" name="methods[<?php echo $index; ?>][pix_description]" value="<?php echo esc_attr($method['pix_description'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="<?php esc_attr_e('Descrição curta para o QR Code (máx 25 caracteres)', 'wp-donate-brasil'); ?>" maxlength="25">
                    </div>
                </div>
                <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-700"><i class="fa-solid fa-qrcode mr-2"></i><?php _e('QR Code PIX será gerado automaticamente com os dados informados.', 'wp-donate-brasil'); ?></p>
                </div>
                <?php
                break;
                
            case 'bank_transfer':
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Nome do Banco', 'wp-donate-brasil'); ?></label>
                        <input type="text" name="methods[<?php echo $index; ?>][bank_name]" value="<?php echo esc_attr($method['bank_name'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Agência', 'wp-donate-brasil'); ?></label>
                        <input type="text" name="methods[<?php echo $index; ?>][bank_agency]" value="<?php echo esc_attr($method['bank_agency'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Conta', 'wp-donate-brasil'); ?></label>
                        <input type="text" name="methods[<?php echo $index; ?>][bank_account]" value="<?php echo esc_attr($method['bank_account'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Titular', 'wp-donate-brasil'); ?></label>
                        <input type="text" name="methods[<?php echo $index; ?>][bank_holder]" value="<?php echo esc_attr($method['bank_holder'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('CPF/CNPJ', 'wp-donate-brasil'); ?></label>
                        <input type="text" name="methods[<?php echo $index; ?>][bank_cpf_cnpj]" value="<?php echo esc_attr($method['bank_cpf_cnpj'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <?php
                break;
                
            case 'bitcoin':
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Endereço Bitcoin', 'wp-donate-brasil'); ?> <span class="text-red-500">*</span></label>
                        <input type="text" name="methods[<?php echo $index; ?>][btc_address]" value="<?php echo esc_attr($method['btc_address'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                            placeholder="<?php esc_attr_e('bc1q...', 'wp-donate-brasil'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Rede', 'wp-donate-brasil'); ?></label>
                        <select name="methods[<?php echo $index; ?>][btc_network]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="Bitcoin" <?php selected($method['btc_network'] ?? '', 'Bitcoin'); ?>>Bitcoin (BTC)</option>
                            <option value="Lightning" <?php selected($method['btc_network'] ?? '', 'Lightning'); ?>>Lightning Network</option>
                        </select>
                    </div>
                </div>
                <?php
                break;
                
            case 'payment_link':
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Nome do Gateway', 'wp-donate-brasil'); ?> <span class="text-red-500">*</span></label>
                        <input type="text" name="methods[<?php echo $index; ?>][gateway_name]" value="<?php echo esc_attr($method['gateway_name'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="<?php esc_attr_e('Ex: PagSeguro, Mercado Pago, Stripe...', 'wp-donate-brasil'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('URL do Link de Pagamento', 'wp-donate-brasil'); ?> <span class="text-red-500">*</span></label>
                        <input type="url" name="methods[<?php echo $index; ?>][gateway_url]" value="<?php echo esc_attr($method['gateway_url'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="<?php esc_attr_e('https://...', 'wp-donate-brasil'); ?>">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('URL do Logo (opcional)', 'wp-donate-brasil'); ?></label>
                        <input type="url" name="methods[<?php echo $index; ?>][gateway_logo]" value="<?php echo esc_attr($method['gateway_logo'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="<?php esc_attr_e('https://exemplo.com/logo.png', 'wp-donate-brasil'); ?>">
                    </div>
                </div>
                <?php
                break;
                
            case 'paypal':
                ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('E-mail do PayPal', 'wp-donate-brasil'); ?></label>
                    <input type="email" name="methods[<?php echo $index; ?>][paypal_email]" value="<?php echo esc_attr($method['paypal_email'] ?? ''); ?>"
                        class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="<?php esc_attr_e('seu@email.com', 'wp-donate-brasil'); ?>">
                </div>
                <?php
                break;
        }
    }
    
    public function render_receipts_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Sem permissão.', 'wp-donate-brasil'));
        }
        
        // Filtros
        $status_filter = sanitize_text_field($_GET['status'] ?? '');
        $search = sanitize_text_field($_GET['s'] ?? '');
        $month_filter = sanitize_text_field($_GET['month'] ?? '');
        $year_filter = sanitize_text_field($_GET['year'] ?? '');
        $paged = max(1, intval($_GET['paged'] ?? 1));
        $per_page = 20;
        $offset = ($paged - 1) * $per_page;
        
        // Busca comprovantes com filtros
        $receipts = WDB_Main::get_receipts(array(
            'status' => $status_filter,
            'search' => $search,
            'month' => $month_filter,
            'year' => $year_filter,
            'limit' => $per_page,
            'offset' => $offset
        ));
        
        $total = WDB_Main::count_receipts($status_filter, $search, $month_filter, $year_filter);
        $total_pages = ceil($total / $per_page);
        $available_years = WDB_Main::get_available_years();
        
        // Contagens para badges
        $counts = array(
            'all' => WDB_Main::count_receipts('', $search, $month_filter, $year_filter),
            'pending' => WDB_Main::count_receipts('pending', $search, $month_filter, $year_filter),
            'approved' => WDB_Main::count_receipts('approved', $search, $month_filter, $year_filter),
            'rejected' => WDB_Main::count_receipts('rejected', $search, $month_filter, $year_filter)
        );
        
        // Meses para filtro
        $months = array(
            1 => __('Janeiro', 'wp-donate-brasil'),
            2 => __('Fevereiro', 'wp-donate-brasil'),
            3 => __('Março', 'wp-donate-brasil'),
            4 => __('Abril', 'wp-donate-brasil'),
            5 => __('Maio', 'wp-donate-brasil'),
            6 => __('Junho', 'wp-donate-brasil'),
            7 => __('Julho', 'wp-donate-brasil'),
            8 => __('Agosto', 'wp-donate-brasil'),
            9 => __('Setembro', 'wp-donate-brasil'),
            10 => __('Outubro', 'wp-donate-brasil'),
            11 => __('Novembro', 'wp-donate-brasil'),
            12 => __('Dezembro', 'wp-donate-brasil')
        );
        
        // URL base para filtros
        $base_url = admin_url('admin.php?page=wdb_receipts');
        
        // Cores do admin
        $page_settings = get_option('wdb_page_settings', array());
        $primary_color = esc_attr($page_settings['primary_color'] ?? '#3B82F6');
        $secondary_color = esc_attr($page_settings['secondary_color'] ?? '#10B981');
        ?>
        <div class="wrap wdb-receipts-page">
            <div class="max-w-7xl mx-auto py-6">
                <!-- Header -->
                <div class="rounded-2xl p-6 mb-6 shadow-lg" style="background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-bold flex items-center gap-3 text-white">
                                <i class="fa-solid fa-file-invoice"></i>
                                <?php _e('Comprovantes de Doação', 'wp-donate-brasil'); ?>
                            </h1>
                            <p class="text-white/90 mt-1"><?php _e('Gerencie os comprovantes enviados pelos doadores', 'wp-donate-brasil'); ?></p>
                        </div>
                        <div class="flex items-center gap-4 text-sm">
                            <div class="rounded-lg px-4 py-2 text-white" style="background: rgba(0,0,0,0.25);">
                                <span class="opacity-80"><?php _e('Total:', 'wp-donate-brasil'); ?></span>
                                <span class="font-bold text-xl ml-1"><?php echo $counts['all']; ?></span>
                            </div>
                            <div class="rounded-lg px-4 py-2 text-white" style="background: rgba(0,0,0,0.25);">
                                <span class="opacity-80"><?php _e('Pendentes:', 'wp-donate-brasil'); ?></span>
                                <span class="font-bold text-xl ml-1"><?php echo $counts['pending']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="bg-white rounded-xl shadow-md p-5 mb-6 border border-gray-100">
                    <form method="get" action="<?php echo admin_url('admin.php'); ?>" class="flex flex-col lg:flex-row gap-4">
                        <input type="hidden" name="page" value="wdb_receipts">
                        <?php if ($status_filter): ?>
                        <input type="hidden" name="status" value="<?php echo esc_attr($status_filter); ?>">
                        <?php endif; ?>
                        
                        <!-- Pesquisa -->
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php _e('Pesquisar', 'wp-donate-brasil'); ?></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" style="z-index: 1;">
                                    <i class="fa-solid fa-search"></i>
                                </span>
                                <input type="text" name="s" value="<?php echo esc_attr($search); ?>"
                                       class="w-full py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                       style="padding-left: 38px; padding-right: 16px;"
                                       placeholder="<?php esc_attr_e('Nome, e-mail ou telefone...', 'wp-donate-brasil'); ?>">
                            </div>
                        </div>
                        
                        <!-- Filtro Mês -->
                        <div class="w-full lg:w-40">
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php _e('Mês', 'wp-donate-brasil'); ?></label>
                            <select name="month" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value=""><?php _e('Todos', 'wp-donate-brasil'); ?></option>
                                <?php foreach ($months as $num => $name): ?>
                                <option value="<?php echo $num; ?>" <?php selected($month_filter, $num); ?>><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Filtro Ano -->
                        <div class="w-full lg:w-32">
                            <label class="block text-xs font-medium text-gray-500 mb-1"><?php _e('Ano', 'wp-donate-brasil'); ?></label>
                            <select name="year" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value=""><?php _e('Todos', 'wp-donate-brasil'); ?></option>
                                <?php foreach ($available_years as $y): ?>
                                <option value="<?php echo $y; ?>" <?php selected($year_filter, $y); ?>><?php echo $y; ?></option>
                                <?php endforeach; ?>
                                <?php if (empty($available_years)): ?>
                                <option value="<?php echo date('Y'); ?>"><?php echo date('Y'); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <!-- Botões -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-filter"></i>
                                <?php _e('Filtrar', 'wp-donate-brasil'); ?>
                            </button>
                            <?php if ($search || $month_filter || $year_filter): ?>
                            <a href="<?php echo $base_url . ($status_filter ? '&status=' . $status_filter : ''); ?>" 
                               class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-times"></i>
                                <?php _e('Limpar', 'wp-donate-brasil'); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Tabs de Status -->
                <div class="flex flex-wrap gap-2 mb-6">
                    <?php 
                    $status_tabs = array(
                        '' => array('label' => __('Todos', 'wp-donate-brasil'), 'icon' => 'fa-list', 'count' => $counts['all']),
                        'pending' => array('label' => __('Pendentes', 'wp-donate-brasil'), 'icon' => 'fa-clock', 'count' => $counts['pending']),
                        'approved' => array('label' => __('Aprovados', 'wp-donate-brasil'), 'icon' => 'fa-check-circle', 'count' => $counts['approved']),
                        'rejected' => array('label' => __('Rejeitados', 'wp-donate-brasil'), 'icon' => 'fa-times-circle', 'count' => $counts['rejected'])
                    );
                    foreach ($status_tabs as $status_key => $tab):
                        $is_active = $status_filter === $status_key;
                        $tab_url = add_query_arg(array('status' => $status_key, 's' => $search, 'month' => $month_filter, 'year' => $year_filter), $base_url);
                        if (empty($status_key)) $tab_url = add_query_arg(array('s' => $search, 'month' => $month_filter, 'year' => $year_filter), $base_url);
                        
                        // Estilos padronizados
                        $active_style = 'background: linear-gradient(135deg, ' . $primary_color . ', ' . $secondary_color . '); color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
                        $normal_style = 'background: white; color: #4B5563; border: 1px solid #E5E7EB;';
                    ?>
                    <a href="<?php echo $tab_url; ?>"
                       class="px-4 py-2.5 rounded-xl font-medium transition-all flex items-center gap-2 hover:shadow-md"
                       style="<?php echo $is_active ? $active_style : $normal_style; ?>">
                        <i class="fa-solid <?php echo $tab['icon']; ?>"></i>
                        <?php echo $tab['label']; ?>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="<?php echo $is_active ? 'background: rgba(255,255,255,0.3);' : 'background: #F3F4F6;'; ?>">
                            <?php echo $tab['count']; ?>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($receipts)): ?>
                <!-- Estado Vazio -->
                <div class="bg-white rounded-2xl p-12 text-center shadow-md border border-gray-100">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fa-solid fa-inbox text-4xl text-gray-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2"><?php _e('Nenhum comprovante encontrado', 'wp-donate-brasil'); ?></h3>
                    <p class="text-gray-500"><?php _e('Tente ajustar os filtros ou aguarde novos envios.', 'wp-donate-brasil'); ?></p>
                </div>
                <?php else: ?>
                
                <!-- Tabela de Comprovantes -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"><?php _e('Doador', 'wp-donate-brasil'); ?></th>
                                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"><?php _e('Método', 'wp-donate-brasil'); ?></th>
                                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"><?php _e('Valor', 'wp-donate-brasil'); ?></th>
                                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"><?php _e('Status', 'wp-donate-brasil'); ?></th>
                                    <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"><?php _e('Data', 'wp-donate-brasil'); ?></th>
                                    <th class="px-5 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider"><?php _e('Ações', 'wp-donate-brasil'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($receipts as $receipt): 
                                    $gravatar = WDB_Main::get_gravatar_url($receipt->donor_email, 80);
                                ?>
                                <tr class="hover:bg-orange-50/50 transition-all" data-receipt-id="<?php echo intval($receipt->id); ?>">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="<?php echo esc_url($gravatar); ?>" alt="" class="w-10 h-10 rounded-full object-cover border-2 border-gray-100">
                                            <div>
                                                <p class="font-semibold text-gray-800"><?php echo esc_html($receipt->donor_name); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo esc_html($receipt->donor_email); ?></p>
                                                <?php if ($receipt->donor_phone): ?>
                                                <p class="text-xs text-gray-400"><?php echo esc_html($receipt->donor_phone); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?php 
                                        $method_colors = array(
                                            'pix' => 'bg-green-100 text-green-700',
                                            'bank_transfer' => 'bg-blue-100 text-blue-700',
                                            'bitcoin' => 'bg-orange-100 text-orange-700',
                                            'payment_link' => 'bg-purple-100 text-purple-700',
                                            'paypal' => 'bg-indigo-100 text-indigo-700'
                                        );
                                        $method_color = $method_colors[$receipt->donation_method] ?? 'bg-gray-100 text-gray-700';
                                        ?>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold <?php echo $method_color; ?>">
                                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $receipt->donation_method))); ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?php if ($receipt->donation_amount > 0): ?>
                                        <span class="font-bold text-green-600 text-lg">R$ <?php echo number_format($receipt->donation_amount, 2, ',', '.'); ?></span>
                                        <?php else: ?>
                                        <span class="text-gray-400 text-sm"><?php _e('Não informado', 'wp-donate-brasil'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?php
                                        $status_styles = array(
                                            'pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                            'approved' => 'bg-green-100 text-green-700 border-green-200',
                                            'rejected' => 'bg-red-100 text-red-700 border-red-200'
                                        );
                                        $status_icons = array(
                                            'pending' => 'fa-clock',
                                            'approved' => 'fa-check',
                                            'rejected' => 'fa-times'
                                        );
                                        $status_labels = array(
                                            'pending' => __('Pendente', 'wp-donate-brasil'),
                                            'approved' => __('Aprovado', 'wp-donate-brasil'),
                                            'rejected' => __('Rejeitado', 'wp-donate-brasil')
                                        );
                                        ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border <?php echo $status_styles[$receipt->status] ?? ''; ?>">
                                            <i class="fa-solid <?php echo $status_icons[$receipt->status] ?? ''; ?>"></i>
                                            <?php echo $status_labels[$receipt->status] ?? $receipt->status; ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm">
                                            <p class="font-medium text-gray-700"><?php echo date_i18n('d/m/Y', strtotime($receipt->created_at)); ?></p>
                                            <p class="text-gray-400 text-xs"><?php echo date_i18n('H:i', strtotime($receipt->created_at)); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <!-- Editar -->
                                            <button type="button" 
                                                    class="wdb-edit-btn p-2.5 text-purple-500 hover:bg-purple-100 rounded-lg transition-all" 
                                                    data-id="<?php echo intval($receipt->id); ?>"
                                                    data-name="<?php echo esc_attr($receipt->donor_name); ?>"
                                                    data-email="<?php echo esc_attr($receipt->donor_email); ?>"
                                                    data-phone="<?php echo esc_attr($receipt->donor_phone); ?>"
                                                    data-method="<?php echo esc_attr($receipt->donation_method); ?>"
                                                    data-amount="<?php echo esc_attr($receipt->donation_amount); ?>"
                                                    data-message="<?php echo esc_attr($receipt->message ?? ''); ?>"
                                                    data-anonymous="<?php echo esc_attr($receipt->anonymous ?? 0); ?>"
                                                    data-gallery="<?php echo esc_attr($receipt->show_in_gallery ?? 1); ?>"
                                                    data-status="<?php echo esc_attr($receipt->status); ?>"
                                                    title="<?php esc_attr_e('Editar', 'wp-donate-brasil'); ?>">
                                                <i class="fa-solid fa-pen text-lg"></i>
                                            </button>
                                            
                                            <!-- Ver Comprovante (Lightbox) -->
                                            <button type="button" 
                                                    class="wdb-view-receipt p-2.5 text-blue-500 hover:bg-blue-100 rounded-lg transition-all" 
                                                    data-url="<?php echo esc_url($receipt->receipt_file); ?>"
                                                    data-name="<?php echo esc_attr($receipt->donor_name); ?>"
                                                    title="<?php esc_attr_e('Ver Comprovante', 'wp-donate-brasil'); ?>">
                                                <i class="fa-solid fa-eye text-lg"></i>
                                            </button>
                                            
                                            <?php if ($receipt->status !== 'approved'): ?>
                                            <button type="button" class="wdb-approve-btn p-2.5 text-green-500 hover:bg-green-100 rounded-lg transition-all"
                                                    data-id="<?php echo intval($receipt->id); ?>" title="<?php esc_attr_e('Aprovar', 'wp-donate-brasil'); ?>">
                                                <i class="fa-solid fa-check text-lg"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($receipt->status !== 'rejected'): ?>
                                            <button type="button" class="wdb-reject-btn p-2.5 text-red-500 hover:bg-red-100 rounded-lg transition-all"
                                                    data-id="<?php echo intval($receipt->id); ?>" title="<?php esc_attr_e('Rejeitar', 'wp-donate-brasil'); ?>">
                                                <i class="fa-solid fa-times text-lg"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="wdb-delete-btn p-2.5 text-gray-400 hover:bg-gray-100 hover:text-red-500 rounded-lg transition-all"
                                                    data-id="<?php echo intval($receipt->id); ?>" title="<?php esc_attr_e('Excluir', 'wp-donate-brasil'); ?>">
                                                <i class="fa-solid fa-trash text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <?php if ($total_pages > 1): ?>
                    <div class="bg-gray-50 px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-sm text-gray-500">
                            <?php printf(__('Mostrando %d-%d de %d resultados', 'wp-donate-brasil'), 
                                $offset + 1, 
                                min($offset + $per_page, $total), 
                                $total
                            ); ?>
                        </p>
                        <div class="flex items-center gap-1">
                            <?php if ($paged > 1): ?>
                            <a href="<?php echo add_query_arg('paged', $paged - 1); ?>" 
                               class="p-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php 
                            $start_page = max(1, $paged - 2);
                            $end_page = min($total_pages, $paged + 2);
                            
                            if ($start_page > 1): ?>
                            <a href="<?php echo add_query_arg('paged', 1); ?>" class="px-3 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">1</a>
                            <?php if ($start_page > 2): ?><span class="px-2 text-gray-400">...</span><?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <a href="<?php echo add_query_arg('paged', $i); ?>"
                               class="px-3 py-2 rounded-lg transition-colors <?php echo $paged === $i ? 'bg-orange-500 text-white font-bold' : 'bg-white border border-gray-200 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>
                            
                            <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?><span class="px-2 text-gray-400">...</span><?php endif; ?>
                            <a href="<?php echo add_query_arg('paged', $total_pages); ?>" class="px-3 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors"><?php echo $total_pages; ?></a>
                            <?php endif; ?>
                            
                            <?php if ($paged < $total_pages): ?>
                            <a href="<?php echo add_query_arg('paged', $paged + 1); ?>" 
                               class="p-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Modal Lightbox -->
        <div id="wdb-lightbox-modal" class="fixed inset-0 z-[99999] hidden">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" id="wdb-lightbox-overlay"></div>
            <div class="absolute inset-4 md:inset-10 flex items-center justify-center">
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-full overflow-hidden">
                    <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2" id="wdb-lightbox-title">
                            <i class="fa-solid fa-file-image text-orange-500"></i>
                            <?php _e('Comprovante', 'wp-donate-brasil'); ?>
                        </h3>
                        <div class="flex items-center gap-2">
                            <a href="#" id="wdb-lightbox-download" target="_blank" 
                               class="p-2 text-gray-500 hover:bg-gray-200 rounded-lg transition-colors" title="<?php esc_attr_e('Abrir em nova aba', 'wp-donate-brasil'); ?>">
                                <i class="fa-solid fa-external-link"></i>
                            </a>
                            <button type="button" id="wdb-lightbox-close" class="p-2 text-gray-500 hover:bg-red-100 hover:text-red-500 rounded-lg transition-colors">
                                <i class="fa-solid fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4 overflow-auto" style="max-height: calc(100vh - 200px);">
                        <div id="wdb-lightbox-content" class="flex items-center justify-center min-h-[300px]">
                            <div class="text-center text-gray-400">
                                <i class="fa-solid fa-spinner fa-spin text-4xl mb-4"></i>
                                <p><?php _e('Carregando...', 'wp-donate-brasil'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal de Edição -->
        <div id="wdb-edit-modal" class="fixed inset-0 z-[99999] hidden">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" id="wdb-edit-overlay"></div>
            <div class="absolute inset-4 md:inset-10 flex items-center justify-center">
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-full overflow-hidden">
                    <div class="flex items-center justify-between p-4 border-b border-gray-200" style="background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);">
                        <h3 class="font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-pen"></i>
                            <?php _e('Editar Doação', 'wp-donate-brasil'); ?>
                        </h3>
                        <button type="button" id="wdb-edit-close" class="p-2 text-white/80 hover:text-white hover:bg-white/20 rounded-lg transition-colors">
                            <i class="fa-solid fa-times text-xl"></i>
                        </button>
                    </div>
                    <form id="wdb-edit-form" class="p-5 space-y-4 overflow-auto" style="max-height: calc(100vh - 200px);">
                        <input type="hidden" name="receipt_id" id="edit-receipt-id">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Nome', 'wp-donate-brasil'); ?> *</label>
                                <input type="text" name="donor_name" id="edit-donor-name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('E-mail', 'wp-donate-brasil'); ?> *</label>
                                <input type="email" name="donor_email" id="edit-donor-email" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Telefone', 'wp-donate-brasil'); ?></label>
                                <input type="text" name="donor_phone" id="edit-donor-phone"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Valor em Reais (R$)', 'wp-donate-brasil'); ?></label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600;">R$</span>
                                    <input type="text" name="donation_amount" id="edit-donation-amount" class="wdb-money-mask w-full py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" style="padding-left: 40px;" placeholder="0,00">
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Método de Pagamento', 'wp-donate-brasil'); ?></label>
                            <select name="donation_method" id="edit-donation-method"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="pix">PIX</option>
                                <option value="bank_transfer"><?php _e('Transferência Bancária', 'wp-donate-brasil'); ?></option>
                                <option value="bitcoin">Bitcoin</option>
                                <option value="paypal">PayPal</option>
                                <option value="payment_link"><?php _e('Link de Pagamento', 'wp-donate-brasil'); ?></option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Mensagem', 'wp-donate-brasil'); ?></label>
                            <textarea name="donor_message" id="edit-donor-message" rows="2"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                        
                        <div class="flex items-center gap-3 flex-wrap" style="font-size: 13px;">
                            <label class="wdb-switch" style="padding: 6px 10px; background: #f3f4f6; border-radius: 6px;">
                                <input type="checkbox" name="anonymous" id="edit-anonymous" value="1">
                                <span class="wdb-switch-slider"></span>
                                <span class="wdb-switch-label" style="font-size: 12px;"><?php _e('Anônimo', 'wp-donate-brasil'); ?></span>
                            </label>
                            <label class="wdb-switch" style="padding: 6px 10px; background: #f3f4f6; border-radius: 6px;">
                                <input type="checkbox" name="show_in_gallery" id="edit-gallery" value="1">
                                <span class="wdb-switch-slider"></span>
                                <span class="wdb-switch-label" style="font-size: 12px;"><?php _e('Galeria', 'wp-donate-brasil'); ?></span>
                            </label>
                            <label class="wdb-switch" id="wdb-status-switch-wrapper" style="padding: 6px 10px; background: #FEF3C7; border-radius: 6px; border: 1px solid #F59E0B;">
                                <input type="checkbox" name="status_approved" id="edit-status-approved" value="1" onchange="wdbUpdateStatusLabel(this)">
                                <span class="wdb-switch-slider"></span>
                                <span class="wdb-switch-label" id="wdb-status-label" style="font-size: 12px; color: #B45309;"><?php _e('Pendente', 'wp-donate-brasil'); ?></span>
                            </label>
                            <script>
                            function wdbUpdateStatusLabel(checkbox) {
                                var label = document.getElementById('wdb-status-label');
                                var wrapper = document.getElementById('wdb-status-switch-wrapper');
                                if (checkbox.checked) {
                                    label.textContent = 'Aprovado';
                                    label.style.color = '#047857';
                                    wrapper.style.background = '#D1FAE5';
                                    wrapper.style.borderColor = '#10B981';
                                } else {
                                    label.textContent = 'Pendente';
                                    label.style.color = '#B45309';
                                    wrapper.style.background = '#FEF3C7';
                                    wrapper.style.borderColor = '#F59E0B';
                                }
                            }
                            </script>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                            <button type="button" id="wdb-edit-cancel" class="px-5 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                                <?php _e('Cancelar', 'wp-donate-brasil'); ?>
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-white font-medium rounded-lg transition-colors" style="background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);">
                                <i class="fa-solid fa-save mr-1"></i> <?php _e('Salvar', 'wp-donate-brasil'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <style>
            .wdb-receipts-page .wrap { max-width: 100%; margin: 0; padding: 0; }
            #wdb-lightbox-modal.active, #wdb-edit-modal.active { display: block; animation: fadeIn 0.3s ease; }
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            #wdb-lightbox-content img { max-width: 100%; height: auto; border-radius: 8px; }
            #wdb-lightbox-content iframe { width: 100%; height: 70vh; border: none; border-radius: 8px; }
            
            /* Switches */
            .wdb-switch { position: relative; display: inline-flex; align-items: center; cursor: pointer; }
            .wdb-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
            .wdb-switch-slider { position: relative; width: 44px; height: 24px; background: #cbd5e1; border-radius: 24px; transition: all 0.3s ease; }
            .wdb-switch-slider::before { content: ''; position: absolute; width: 18px; height: 18px; background: white; border-radius: 50%; top: 3px; left: 3px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
            .wdb-switch input:checked + .wdb-switch-slider { background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>); }
            .wdb-switch input:checked + .wdb-switch-slider::before { transform: translateX(20px); }
            .wdb-switch-label { margin-left: 10px; font-size: 0.875rem; color: #374151; font-weight: 500; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Lightbox
            $('.wdb-view-receipt').on('click', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var name = $(this).data('name');
                var ext = url.split('.').pop().toLowerCase();
                var content = '';
                
                $('#wdb-lightbox-title').html('<i class="fa-solid fa-file-image text-orange-500 mr-2"></i>' + name);
                $('#wdb-lightbox-download').attr('href', url);
                
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                    content = '<img src="' + url + '" alt="Comprovante" class="mx-auto">';
                } else if (ext === 'pdf') {
                    content = '<iframe src="' + url + '#view=FitH"></iframe>';
                } else {
                    content = '<div class="text-center py-10"><i class="fa-solid fa-file text-6xl text-gray-300 mb-4"></i><p class="text-gray-500">Visualização não disponível</p><a href="' + url + '" target="_blank" class="mt-4 inline-block px-4 py-2 bg-orange-500 text-white rounded-lg">Baixar Arquivo</a></div>';
                }
                
                $('#wdb-lightbox-content').html(content);
                $('#wdb-lightbox-modal').addClass('active');
            });
            
            $('#wdb-lightbox-close, #wdb-lightbox-overlay').on('click', function() {
                $('#wdb-lightbox-modal').removeClass('active');
            });
            
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $('#wdb-lightbox-modal').removeClass('active');
                    $('#wdb-edit-modal').removeClass('active');
                }
            });
            
            // Máscara de dinheiro brasileiro
            function wdbFormatMoney(value) {
                value = String(value).replace(/\D/g, '');
                if (value === '') return '';
                var num = parseInt(value, 10);
                var formatted = (num / 100).toFixed(2);
                var parts = formatted.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return parts.join(',');
            }
            
            function wdbFloatToMoney(val) {
                if (!val || val == 0) return '';
                var num = parseFloat(val);
                return num.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            
            $('.wdb-money-mask').on('input', function() {
                $(this).val(wdbFormatMoney($(this).val()));
            });
            
            // Editar doação
            $('.wdb-edit-btn').on('click', function() {
                var btn = $(this);
                $('#edit-receipt-id').val(btn.data('id'));
                $('#edit-donor-name').val(btn.data('name'));
                $('#edit-donor-email').val(btn.data('email'));
                $('#edit-donor-phone').val(btn.data('phone'));
                $('#edit-donation-method').val(btn.data('method'));
                $('#edit-donation-amount').val(wdbFloatToMoney(btn.data('amount')));
                $('#edit-donor-message').val(btn.data('message'));
                $('#edit-anonymous').prop('checked', btn.data('anonymous') == 1);
                $('#edit-gallery').prop('checked', btn.data('gallery') == 1);
                
                // Define status do switch
                var isApproved = btn.data('status') === 'approved';
                $('#edit-status-approved').prop('checked', isApproved);
                wdbUpdateStatusLabel(document.getElementById('edit-status-approved'));
                
                $('#wdb-edit-modal').addClass('active');
            });
            
            $('#wdb-edit-close, #wdb-edit-cancel, #wdb-edit-overlay').on('click', function() {
                $('#wdb-edit-modal').removeClass('active');
            });
            
            // Salvar edição
            $('#wdb-edit-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalText = submitBtn.html();
                
                submitBtn.html('<i class="fa-solid fa-spinner fa-spin mr-1"></i> Salvando...').prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wdb_update_receipt',
                        receipt_id: $('#edit-receipt-id').val(),
                        donor_name: $('#edit-donor-name').val(),
                        donor_email: $('#edit-donor-email').val(),
                        donor_phone: $('#edit-donor-phone').val(),
                        donation_method: $('#edit-donation-method').val(),
                        donation_amount: $('#edit-donation-amount').val(),
                        donor_message: $('#edit-donor-message').val(),
                        anonymous: $('#edit-anonymous').is(':checked') ? 1 : 0,
                        show_in_gallery: $('#edit-gallery').is(':checked') ? 1 : 0,
                        status_approved: $('#edit-status-approved').is(':checked') ? 1 : 0,
                        _wpnonce: '<?php echo wp_create_nonce('wdb_update_receipt'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || 'Erro ao salvar');
                            submitBtn.html(originalText).prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert('Erro de conexão');
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public function ajax_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sem permissão.', 'wp-donate-brasil')));
        }
        
        if (!wp_verify_nonce($_POST['wdb_nonce'] ?? '', 'wdb_nonce_action')) {
            wp_send_json_error(array('message' => __('Erro de segurança.', 'wp-donate-brasil')));
        }
        
        $settings = array(
            'page_highlight' => sanitize_text_field($_POST['page_highlight'] ?? 'Transforme vidas com sua doação'),
            'page_title' => sanitize_text_field($_POST['page_title'] ?? ''),
            'page_subtitle' => sanitize_text_field($_POST['page_subtitle'] ?? ''),
            'page_description' => sanitize_textarea_field($_POST['page_description'] ?? ''),
            'primary_color' => sanitize_hex_color($_POST['primary_color'] ?? '#3B82F6'),
            'secondary_color' => sanitize_hex_color($_POST['secondary_color'] ?? '#10B981'),
            'show_gallery' => isset($_POST['show_gallery']),
            'gallery_title' => sanitize_text_field($_POST['gallery_title'] ?? ''),
            'items_per_page' => intval($_POST['items_per_page'] ?? 12),
            'donors_page_slug' => sanitize_text_field($_POST['donors_page_slug'] ?? 'doadores'),
            'list_show_photo' => isset($_POST['list_show_photo']),
            'list_show_name' => isset($_POST['list_show_name']),
            'list_show_email' => isset($_POST['list_show_email']),
            'list_show_phone' => isset($_POST['list_show_phone']),
            'list_show_value' => isset($_POST['list_show_value']),
            'list_show_count' => isset($_POST['list_show_count']),
            'list_show_date' => isset($_POST['list_show_date']),
            'list_show_method' => isset($_POST['list_show_method']),
            'list_show_message' => isset($_POST['list_show_message']),
            // Filtros da lista
            'filter_search' => isset($_POST['filter_search']),
            'filter_method' => isset($_POST['filter_method']),
            'filter_month' => isset($_POST['filter_month']),
            'filter_order' => isset($_POST['filter_order']),
            // Créditos
            'show_credits' => isset($_POST['show_credits']),
            // E-mails
            'emails_notify_admin' => isset($_POST['emails_notify_admin']),
            'emails_notify_donor' => isset($_POST['emails_notify_donor']),
            // Mensagem de agradecimento
            'thank_you_title' => sanitize_text_field($_POST['thank_you_title'] ?? 'Muito Obrigado! 🙏'),
            'thank_you_subtitle' => sanitize_text_field($_POST['thank_you_subtitle'] ?? 'Sua doação faz a diferença!'),
            'thank_you_message' => sanitize_textarea_field($_POST['thank_you_message'] ?? ''),
            // Configurações de email
            'email_sender_name' => sanitize_text_field($_POST['email_sender_name'] ?? get_bloginfo('name')),
            'admin_email' => sanitize_email($_POST['admin_email'] ?? get_option('admin_email')),
            'email_admin_new' => isset($_POST['email_admin_new']),
            'email_admin_new_subject' => sanitize_text_field($_POST['email_admin_new_subject'] ?? ''),
            'email_admin_new_body' => sanitize_textarea_field($_POST['email_admin_new_body'] ?? ''),
            'email_donor_received' => isset($_POST['email_donor_received']),
            'email_donor_received_subject' => sanitize_text_field($_POST['email_donor_received_subject'] ?? ''),
            'email_donor_received_body' => sanitize_textarea_field($_POST['email_donor_received_body'] ?? ''),
            'email_donor_approved' => isset($_POST['email_donor_approved']),
            'email_donor_approved_subject' => sanitize_text_field($_POST['email_donor_approved_subject'] ?? ''),
            'email_donor_approved_body' => sanitize_textarea_field($_POST['email_donor_approved_body'] ?? '')
        );
        
        update_option('wdb_page_settings', $settings);
        wp_cache_flush();
        
        wp_send_json_success(array('message' => __('Configurações salvas!', 'wp-donate-brasil')));
    }
    
    public function ajax_save_methods() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sem permissão.', 'wp-donate-brasil')));
        }
        
        if (!wp_verify_nonce($_POST['wdb_nonce'] ?? '', 'wdb_nonce_action')) {
            wp_send_json_error(array('message' => __('Erro de segurança.', 'wp-donate-brasil')));
        }
        
        $methods = array();
        
        if (!empty($_POST['methods']) && is_array($_POST['methods'])) {
            foreach ($_POST['methods'] as $method) {
                $sanitized = array(
                    'id' => sanitize_key($method['id'] ?? ''),
                    'name' => sanitize_text_field($method['name'] ?? ''),
                    'enabled' => isset($method['enabled']),
                    'icon' => sanitize_text_field($method['icon'] ?? ''),
                    'instructions' => sanitize_textarea_field($method['instructions'] ?? '')
                );
                
                switch ($sanitized['id']) {
                    case 'pix':
                        $sanitized['pix_key'] = sanitize_text_field($method['pix_key'] ?? '');
                        $sanitized['pix_name'] = sanitize_text_field($method['pix_name'] ?? '');
                        $sanitized['pix_city'] = sanitize_text_field($method['pix_city'] ?? '');
                        $sanitized['pix_bank'] = sanitize_text_field($method['pix_bank'] ?? '');
                        $sanitized['pix_description'] = sanitize_text_field($method['pix_description'] ?? '');
                        break;
                    case 'bank_transfer':
                        $sanitized['bank_name'] = sanitize_text_field($method['bank_name'] ?? '');
                        $sanitized['bank_agency'] = sanitize_text_field($method['bank_agency'] ?? '');
                        $sanitized['bank_account'] = sanitize_text_field($method['bank_account'] ?? '');
                        $sanitized['bank_holder'] = sanitize_text_field($method['bank_holder'] ?? '');
                        $sanitized['bank_cpf_cnpj'] = sanitize_text_field($method['bank_cpf_cnpj'] ?? '');
                        break;
                    case 'bitcoin':
                        $sanitized['btc_address'] = sanitize_text_field($method['btc_address'] ?? '');
                        $sanitized['btc_network'] = sanitize_text_field($method['btc_network'] ?? 'Bitcoin');
                        break;
                    case 'payment_link':
                        $sanitized['gateway_name'] = sanitize_text_field($method['gateway_name'] ?? '');
                        $sanitized['gateway_url'] = esc_url_raw($method['gateway_url'] ?? '');
                        $sanitized['gateway_logo'] = esc_url_raw($method['gateway_logo'] ?? '');
                        break;
                    case 'paypal':
                        $sanitized['paypal_email'] = sanitize_email($method['paypal_email'] ?? '');
                        break;
                }
                
                $methods[] = $sanitized;
            }
        }
        
        update_option('wdb_donation_methods', $methods);
        wp_cache_flush();
        
        wp_send_json_success(array('message' => __('Métodos salvos!', 'wp-donate-brasil')));
    }
    
    // Limpa cache do plugin
    public function ajax_clear_cache() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sem permissão.', 'wp-donate-brasil')));
        }
        
        if (!wp_verify_nonce($_POST['wdb_nonce'] ?? '', 'wdb_nonce_action')) {
            wp_send_json_error(array('message' => __('Erro de segurança.', 'wp-donate-brasil')));
        }
        
        $cleared = array();
        
        // Limpa cache do WordPress
        wp_cache_flush();
        $cleared[] = 'WP Object Cache';
        
        // Limpa transientes do plugin
        global $wpdb;
        $transients = $wpdb->get_results(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '%_transient_wdb_%' OR option_name LIKE '%_transient_timeout_wdb_%'"
        );
        
        foreach ($transients as $transient) {
            delete_option($transient->option_name);
        }
        $cleared[] = count($transients) . ' transientes WDB';
        
        // Limpa cache de plugins populares
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache(); // WP Super Cache
            $cleared[] = 'WP Super Cache';
        }
        
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all(); // W3 Total Cache
            $cleared[] = 'W3 Total Cache';
        }
        
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain(); // WP Rocket
            $cleared[] = 'WP Rocket';
        }
        
        if (class_exists('LiteSpeed_Cache_API')) {
            LiteSpeed_Cache_API::purge_all(); // LiteSpeed Cache
            $cleared[] = 'LiteSpeed Cache';
        }
        
        // Limpa cache de rewrite rules
        flush_rewrite_rules();
        $cleared[] = 'Rewrite Rules';
        
        wp_send_json_success(array(
            'message' => sprintf(__('Cache limpo! Itens: %s', 'wp-donate-brasil'), implode(', ', $cleared))
        ));
    }
    
    // Limpa apenas transientes do plugin
    public function ajax_clear_transients() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sem permissão.', 'wp-donate-brasil')));
        }
        
        if (!wp_verify_nonce($_POST['wdb_nonce'] ?? '', 'wdb_nonce_action')) {
            wp_send_json_error(array('message' => __('Erro de segurança.', 'wp-donate-brasil')));
        }
        
        global $wpdb;
        
        // Limpa todos os transientes do plugin
        $count = $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_wdb_%' OR option_name LIKE '%_transient_timeout_wdb_%'"
        );
        
        // Limpa também transientes expirados
        $wpdb->query(
            "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
            WHERE a.option_name LIKE '%_transient_%'
            AND a.option_name NOT LIKE '%_transient_timeout_%'
            AND b.option_name = CONCAT('_transient_timeout_', SUBSTRING(a.option_name, 12))
            AND b.option_value < UNIX_TIMESTAMP()"
        );
        
        wp_cache_flush();
        
        wp_send_json_success(array(
            'message' => sprintf(__('Transientes limpos! %d registros removidos.', 'wp-donate-brasil'), $count)
        ));
    }
    
    // Deleta todas as doações
    public function ajax_delete_all_donations() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sem permissão.', 'wp-donate-brasil')));
        }
        
        if (!wp_verify_nonce($_POST['wdb_nonce'] ?? '', 'wdb_nonce_action')) {
            wp_send_json_error(array('message' => __('Erro de segurança.', 'wp-donate-brasil')));
        }
        
        // Confirmação adicional
        $confirm = sanitize_text_field($_POST['confirm'] ?? '');
        if ($confirm !== 'DELETAR') {
            wp_send_json_error(array('message' => __('Confirmação inválida.', 'wp-donate-brasil')));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        // Conta antes de deletar
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // Busca todos os attachment_ids antes de deletar
        $attachment_ids = $wpdb->get_col("SELECT attachment_id FROM $table_name WHERE attachment_id IS NOT NULL AND attachment_id > 0");
        
        // Deleta todas as mídias da Media Library
        foreach ($attachment_ids as $att_id) {
            wp_delete_attachment(intval($att_id), true);
        }
        
        // Deleta todos os registros
        $result = $wpdb->query("TRUNCATE TABLE $table_name");
        
        if ($result === false) {
            // Fallback se TRUNCATE falhar
            $wpdb->query("DELETE FROM $table_name");
        }
        
        wp_cache_flush();
        
        wp_send_json_success(array(
            'message' => sprintf(__('Todas as %d doações e suas mídias foram deletadas permanentemente!', 'wp-donate-brasil'), $total)
        ));
    }
    
    // Página Dashboard com relatórios e gráficos
    public function render_dashboard_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Sem permissão.', 'wp-donate-brasil'));
        }
        
        $settings = get_option('wdb_page_settings', array());
        $primary_color = esc_attr($settings['primary_color'] ?? '#3B82F6');
        $secondary_color = esc_attr($settings['secondary_color'] ?? '#10B981');
        
        // Filtros
        $month = intval($_GET['month'] ?? date('n'));
        $year = intval($_GET['year'] ?? date('Y'));
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        // Constrói condição WHERE baseada nos filtros
        $where_date = "status = 'approved'";
        $where_date_all = "1=1"; // Para status (inclui todos)
        
        if ($year === 0) {
            // Todo período - sem filtro de data
            $filter_label = __('Todo o Período', 'wp-donate-brasil');
        } elseif ($month === 0) {
            // Todos os meses do ano selecionado
            $where_date = $wpdb->prepare("YEAR(created_at) = %d AND status = 'approved'", $year);
            $where_date_all = $wpdb->prepare("YEAR(created_at) = %d", $year);
            $filter_label = sprintf(__('Ano %d (todos os meses)', 'wp-donate-brasil'), $year);
        } else {
            // Mês e ano específicos
            $where_date = $wpdb->prepare("MONTH(created_at) = %d AND YEAR(created_at) = %d AND status = 'approved'", $month, $year);
            $where_date_all = $wpdb->prepare("MONTH(created_at) = %d AND YEAR(created_at) = %d", $month, $year);
            $filter_label = '';
        }
        
        // Dados do período - por dia/mês dependendo do filtro
        $daily_data = array();
        
        if ($year === 0) {
            // Todo período - agrupa por mês/ano
            $results = $wpdb->get_results(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as count, COALESCE(SUM(donation_amount), 0) as total 
                 FROM $table_name 
                 WHERE status = 'approved'
                 GROUP BY period
                 ORDER BY period ASC"
            );
            foreach ($results as $r) {
                $daily_data[] = array(
                    'day' => date_i18n('M/y', strtotime($r->period . '-01')),
                    'count' => intval($r->count),
                    'total' => floatval($r->total)
                );
            }
        } elseif ($month === 0) {
            // Todos os meses do ano - agrupa por mês
            for ($m = 1; $m <= 12; $m++) {
                $result = $wpdb->get_row($wpdb->prepare(
                    "SELECT COUNT(*) as count, COALESCE(SUM(donation_amount), 0) as total 
                     FROM $table_name 
                     WHERE MONTH(created_at) = %d AND YEAR(created_at) = %d AND status = 'approved'",
                    $m, $year
                ));
                $months_short = array(1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez');
                $daily_data[] = array(
                    'day' => $months_short[$m],
                    'count' => intval($result->count ?? 0),
                    'total' => floatval($result->total ?? 0)
                );
            }
        } else {
            // Mês específico - agrupa por dia
            $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            for ($day = 1; $day <= $days_in_month; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $result = $wpdb->get_row($wpdb->prepare(
                    "SELECT COUNT(*) as count, COALESCE(SUM(donation_amount), 0) as total 
                     FROM $table_name 
                     WHERE DATE(created_at) = %s AND status = 'approved'",
                    $date
                ));
                $daily_data[] = array(
                    'day' => $day,
                    'count' => intval($result->count ?? 0),
                    'total' => floatval($result->total ?? 0)
                );
            }
        }
        
        // Totais do período
        $month_totals = $wpdb->get_row(
            "SELECT COUNT(*) as count, COALESCE(SUM(donation_amount), 0) as total 
             FROM $table_name 
             WHERE $where_date"
        );
        
        // Métodos de pagamento
        $methods_data = $wpdb->get_results(
            "SELECT donation_method, COUNT(*) as count, COALESCE(SUM(donation_amount), 0) as total 
             FROM $table_name 
             WHERE $where_date
             GROUP BY donation_method"
        );
        
        // Status dos comprovantes
        $status_data = $wpdb->get_results(
            "SELECT status, COUNT(*) as count 
             FROM $table_name 
             WHERE $where_date_all
             GROUP BY status"
        );
        
        // Top doadores (por valor total + quantidade de doações) - apenas aprovados
        // No admin mostra todos os doadores (incluindo anônimos) para controle interno
        $top_donors = $wpdb->get_results(
            "SELECT 
                donor_name,
                donor_email,
                COUNT(*) as donation_count,
                COALESCE(SUM(donation_amount), 0) as total_amount,
                MAX(anonymous) as is_anonymous
             FROM $table_name 
             WHERE status = 'approved'
             GROUP BY donor_email
             ORDER BY total_amount DESC, donation_count DESC
             LIMIT 10"
        );
        
        // Busca detalhes das doações de cada top doador para o tooltip
        $top_donors_details = array();
        foreach ($top_donors as $donor) {
            $donor_where = $wpdb->prepare("donor_email = %s AND status = 'approved'", $donor->donor_email);
            if ($year > 0 && $month > 0) {
                $donor_where .= $wpdb->prepare(" AND MONTH(created_at) = %d AND YEAR(created_at) = %d", $month, $year);
            } elseif ($year > 0) {
                $donor_where .= $wpdb->prepare(" AND YEAR(created_at) = %d", $year);
            }
            
            $donations = $wpdb->get_results(
                "SELECT donation_amount, created_at 
                 FROM $table_name 
                 WHERE $donor_where
                 ORDER BY created_at DESC"
            );
            $top_donors_details[$donor->donor_email] = array(
                'name' => $donor->donor_name,
                'total' => floatval($donor->total_amount),
                'count' => intval($donor->donation_count),
                'donations' => array_map(function($d) {
                    return array(
                        'amount' => floatval($d->donation_amount),
                        'date' => date_i18n('d/m/Y', strtotime($d->created_at))
                    );
                }, $donations)
            );
        }
        
        // Meses para o select
        $months = array(
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        );
        
        // Anos disponíveis
        $years_available = $wpdb->get_col("SELECT DISTINCT YEAR(created_at) FROM $table_name ORDER BY YEAR(created_at) DESC");
        if (empty($years_available)) {
            $years_available = array(date('Y'));
        }
        
        // Labels dos métodos
        $method_labels = array(
            'pix' => array('label' => 'PIX', 'color' => '#00D4AA'),
            'bank_transfer' => array('label' => 'Transferência', 'color' => '#3B82F6'),
            'bitcoin' => array('label' => 'Bitcoin', 'color' => '#F7931A'),
            'paypal' => array('label' => 'PayPal', 'color' => '#003087'),
            'payment_link' => array('label' => 'Link', 'color' => '#8B5CF6')
        );
        
        // Status labels
        $status_labels = array(
            'pending' => array('label' => 'Pendentes', 'color' => '#F59E0B'),
            'approved' => array('label' => 'Aprovados', 'color' => '#10B981'),
            'rejected' => array('label' => 'Rejeitados', 'color' => '#EF4444')
        );
        ?>
        <div class="wrap wdb-admin-wrap" style="margin: 0; padding: 20px;">
            
            <!-- Header -->
            <?php 
            $donation_page_id = get_option('wdb_donation_page_id');
            $donation_page_url = $donation_page_id ? get_permalink($donation_page_id) : home_url('/doacoes/');
            $donors_page_slug = $settings['donors_page_slug'] ?? 'doadores';
            $donors_page_url = home_url('/' . $donors_page_slug . '/');
            ?>
            <div class="rounded-2xl p-6 mb-8 shadow-lg" style="background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                            <i class="fa-solid fa-chart-line"></i> Dashboard
                        </h1>
                        <p class="text-white/80 mt-1"><?php _e('Relatórios e estatísticas de doações', 'wp-donate-brasil'); ?></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="<?php echo esc_url($donation_page_url); ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 font-medium rounded-lg transition-all" style="background: rgba(0,0,0,0.3); color: #fff !important;" onmouseover="this.style.background='rgba(0,0,0,0.5)'" onmouseout="this.style.background='rgba(0,0,0,0.3)'">
                            <i class="fa-solid fa-heart" style="color: #fff !important;"></i>
                            <?php _e('Página de Doações', 'wp-donate-brasil'); ?>
                        </a>
                        <a href="<?php echo esc_url($donors_page_url); ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 font-medium rounded-lg transition-all" style="background: rgba(0,0,0,0.3); color: #fff !important;" onmouseover="this.style.background='rgba(0,0,0,0.5)'" onmouseout="this.style.background='rgba(0,0,0,0.3)'">
                            <i class="fa-solid fa-users" style="color: #fff !important;"></i>
                            <?php _e('Lista de Doadores', 'wp-donate-brasil'); ?>
                        </a>
                        <span class="px-4 py-2 rounded-full text-sm font-bold text-white" style="background: rgba(0,0,0,0.2);">
                            v<?php echo WDB_VERSION; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100">
                <form method="get" class="flex flex-wrap items-end gap-4">
                    <input type="hidden" name="page" value="wdb_dashboard">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Mês', 'wp-donate-brasil'); ?></label>
                        <select name="month" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="0" <?php selected($month, 0); ?>><?php _e('Todos os meses', 'wp-donate-brasil'); ?></option>
                            <?php foreach ($months as $m => $name): ?>
                            <option value="<?php echo $m; ?>" <?php selected($month, $m); ?>><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Ano', 'wp-donate-brasil'); ?></label>
                        <select name="year" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="0" <?php selected($year, 0); ?>><?php _e('Todo o período', 'wp-donate-brasil'); ?></option>
                            <?php foreach ($years_available as $y): ?>
                            <option value="<?php echo intval($y); ?>" <?php selected($year, $y); ?>><?php echo intval($y); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="px-6 py-2 text-white font-semibold rounded-lg transition-all" style="background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);">
                        <i class="fa-solid fa-filter mr-2"></i><?php _e('Filtrar', 'wp-donate-brasil'); ?>
                    </button>
                </form>
            </div>
            
            <!-- Cards de Resumo -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, <?php echo $primary_color; ?>20, <?php echo $primary_color; ?>40);">
                            <i class="fa-solid fa-hand-holding-dollar text-2xl" style="color: <?php echo $primary_color; ?>;"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500"><?php _e('Total Arrecadado', 'wp-donate-brasil'); ?></p>
                            <p class="text-2xl font-bold text-gray-800">R$ <?php echo number_format($month_totals->total ?? 0, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, <?php echo $secondary_color; ?>20, <?php echo $secondary_color; ?>40);">
                            <i class="fa-solid fa-receipt text-2xl" style="color: <?php echo $secondary_color; ?>;"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500"><?php _e('Doações Aprovadas', 'wp-donate-brasil'); ?></p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo intval($month_totals->count ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php 
                $pending_count = 0;
                foreach ($status_data as $s) {
                    if ($s->status === 'pending') $pending_count = $s->count;
                }
                ?>
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center bg-yellow-100">
                            <i class="fa-solid fa-clock text-2xl text-yellow-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500"><?php _e('Pendentes', 'wp-donate-brasil'); ?></p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo intval($pending_count); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center bg-purple-100">
                            <i class="fa-solid fa-calculator text-2xl text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500"><?php _e('Ticket Médio', 'wp-donate-brasil'); ?></p>
                            <?php $avg = ($month_totals->count > 0) ? $month_totals->total / $month_totals->count : 0; ?>
                            <p class="text-2xl font-bold text-gray-800">R$ <?php echo number_format($avg, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de Linha - Doações por Dia -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-area text-blue-500"></i>
                    <?php 
                    if ($year === 0) {
                        _e('Doações - Todo o Período', 'wp-donate-brasil');
                    } elseif ($month === 0) {
                        printf(__('Doações em %d (todos os meses)', 'wp-donate-brasil'), $year);
                    } else {
                        printf(__('Doações em %s/%d', 'wp-donate-brasil'), $months[$month], $year);
                    }
                    ?>
                </h2>
                <div style="height: 350px;">
                    <canvas id="wdb-chart-daily"></canvas>
                </div>
            </div>
            
            <!-- Gráficos -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Métodos de Pagamento -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-credit-card text-green-500"></i>
                        <?php _e('Métodos de Pagamento', 'wp-donate-brasil'); ?>
                    </h2>
                    <div style="height: 220px;">
                        <canvas id="wdb-chart-methods"></canvas>
                    </div>
                    <div class="mt-4 space-y-1.5 text-xs">
                        <?php foreach ($methods_data as $m): 
                            $info = $method_labels[$m->donation_method] ?? array('label' => ucfirst($m->donation_method), 'color' => '#6B7280');
                        ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background: <?php echo $info['color']; ?>;"></span>
                                <span class="text-gray-600"><?php echo esc_html($info['label']); ?></span>
                            </div>
                            <span class="font-semibold text-gray-800"><?php echo intval($m->count); ?> (R$ <?php echo number_format($m->total, 2, ',', '.'); ?>)</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Status dos Comprovantes -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-pie-chart text-purple-500"></i>
                        <?php _e('Status dos Comprovantes', 'wp-donate-brasil'); ?>
                    </h2>
                    <div style="height: 220px;">
                        <canvas id="wdb-chart-status"></canvas>
                    </div>
                    <div class="mt-4 space-y-1.5 text-xs">
                        <?php foreach ($status_data as $s): 
                            $info = $status_labels[$s->status] ?? array('label' => ucfirst($s->status), 'color' => '#6B7280');
                        ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background: <?php echo $info['color']; ?>;"></span>
                                <span class="text-gray-600"><?php echo esc_html($info['label']); ?></span>
                            </div>
                            <span class="font-semibold text-gray-800"><?php echo intval($s->count); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Top Doadores -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-trophy text-yellow-500"></i>
                        <?php _e('Top Doadores', 'wp-donate-brasil'); ?>
                    </h2>
                    <div style="height: 220px;">
                        <canvas id="wdb-chart-top-donors"></canvas>
                    </div>
                    <div class="mt-4 space-y-1.5 text-xs">
                        <?php 
                        $rank = 1;
                        foreach ($top_donors as $donor): 
                            $medal = '';
                            if ($rank === 1) $medal = '🥇';
                            elseif ($rank === 2) $medal = '🥈';
                            elseif ($rank === 3) $medal = '🥉';
                        ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-400"><?php echo $medal ?: '#' . $rank; ?></span>
                                <span class="text-gray-600 truncate max-w-[100px]"><?php echo esc_html($donor->donor_name); ?></span>
                                <?php if ($donor->donation_count > 1): ?>
                                <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-bold"><?php echo $donor->donation_count; ?>x</span>
                                <?php endif; ?>
                            </div>
                            <span class="font-semibold text-gray-800">R$ <?php echo number_format($donor->total_amount, 2, ',', '.'); ?></span>
                        </div>
                        <?php 
                        $rank++;
                        endforeach; 
                        if (empty($top_donors)): ?>
                        <p class="text-gray-400 text-center py-4"><?php _e('Sem doações no período', 'wp-donate-brasil'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Chart.js -->
            <script src="<?php echo esc_url(WDB_PLUGIN_URL . 'assets/js/chart.min.js'); ?>"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Dados
                var dailyData = <?php echo json_encode($daily_data); ?>;
                var methodsData = <?php echo json_encode(array_values(array_map(function($m) use ($method_labels) {
                    $info = $method_labels[$m->donation_method] ?? array('label' => ucfirst($m->donation_method), 'color' => '#6B7280');
                    return array('label' => $info['label'], 'count' => intval($m->count), 'color' => $info['color']);
                }, $methods_data))); ?>;
                var statusData = <?php echo json_encode(array_values(array_map(function($s) use ($status_labels) {
                    $info = $status_labels[$s->status] ?? array('label' => ucfirst($s->status), 'color' => '#6B7280');
                    return array('label' => $info['label'], 'count' => intval($s->count), 'color' => $info['color']);
                }, $status_data))); ?>;
                
                // Gráfico de Linha - Doações por Dia
                new Chart(document.getElementById('wdb-chart-daily'), {
                    type: 'line',
                    data: {
                        labels: dailyData.map(function(d) { return d.day; }),
                        datasets: [{
                            label: 'Valor (R$)',
                            data: dailyData.map(function(d) { return d.total; }),
                            borderColor: '<?php echo $primary_color; ?>',
                            backgroundColor: '<?php echo $primary_color; ?>20',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }, {
                            label: 'Quantidade',
                            data: dailyData.map(function(d) { return d.count; }),
                            borderColor: '<?php echo $secondary_color; ?>',
                            backgroundColor: 'transparent',
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Valor (R$)' } },
                            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Quantidade' } }
                        }
                    }
                });
                
                // Gráfico de Pizza - Métodos
                if (methodsData.length > 0) {
                    new Chart(document.getElementById('wdb-chart-methods'), {
                        type: 'doughnut',
                        data: {
                            labels: methodsData.map(function(m) { return m.label; }),
                            datasets: [{
                                data: methodsData.map(function(m) { return m.count; }),
                                backgroundColor: methodsData.map(function(m) { return m.color; }),
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { 
                                legend: { 
                                    position: 'bottom',
                                    labels: { usePointStyle: true, pointStyle: 'circle', padding: 15 }
                                } 
                            }
                        }
                    });
                }
                
                // Gráfico de Pizza - Status
                if (statusData.length > 0) {
                    new Chart(document.getElementById('wdb-chart-status'), {
                        type: 'doughnut',
                        data: {
                            labels: statusData.map(function(s) { return s.label; }),
                            datasets: [{
                                data: statusData.map(function(s) { return s.count; }),
                                backgroundColor: statusData.map(function(s) { return s.color; }),
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { 
                                legend: { 
                                    position: 'bottom',
                                    labels: { usePointStyle: true, pointStyle: 'circle', padding: 15 }
                                } 
                            }
                        }
                    });
                }
                
                // Gráfico de Barras - Top Doadores
                var topDonorsData = <?php echo json_encode(array_values(array_map(function($d) use ($top_donors_details) {
                    $details = $top_donors_details[$d->donor_email] ?? array('donations' => array());
                    return array(
                        'name' => mb_substr($d->donor_name, 0, 15) . (mb_strlen($d->donor_name) > 15 ? '...' : ''),
                        'fullName' => $d->donor_name,
                        'total' => floatval($d->total_amount),
                        'count' => intval($d->donation_count),
                        'donations' => $details['donations']
                    );
                }, $top_donors))); ?>;
                
                if (topDonorsData.length > 0) {
                    new Chart(document.getElementById('wdb-chart-top-donors'), {
                        type: 'bar',
                        data: {
                            labels: topDonorsData.map(function(d) { return d.name; }),
                            datasets: [{
                                label: 'Valor (R$)',
                                data: topDonorsData.map(function(d) { return d.total; }),
                                backgroundColor: topDonorsData.map(function(d, i) {
                                    var colors = ['#FFD700', '#C0C0C0', '#CD7F32', '#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#06B6D4', '#EC4899'];
                                    return colors[i] || '#6B7280';
                                }),
                                borderRadius: 4,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { 
                                legend: { display: false },
                                tooltip: {
                                    displayColors: false,
                                    backgroundColor: 'rgba(0,0,0,0.85)',
                                    padding: 12,
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 12 },
                                    footerFont: { size: 13, weight: 'bold' },
                                    callbacks: {
                                        title: function(ctx) {
                                            var donor = topDonorsData[ctx[0].dataIndex];
                                            return donor.fullName + ' (' + donor.count + 'x)';
                                        },
                                        label: function(ctx) {
                                            return '';
                                        },
                                        afterBody: function(ctx) {
                                            var donor = topDonorsData[ctx[0].dataIndex];
                                            var lines = [];
                                            donor.donations.forEach(function(d) {
                                                lines.push('• R$ ' + d.amount.toFixed(2).replace('.', ',') + ' - ' + d.date);
                                            });
                                            return lines;
                                        },
                                        footer: function(ctx) {
                                            var donor = topDonorsData[ctx[0].dataIndex];
                                            return '─────────────\nTotal: R$ ' + donor.total.toFixed(2).replace('.', ',');
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { beginAtZero: true, title: { display: false } },
                                y: { grid: { display: false } }
                            }
                        }
                    });
                }
            });
            </script>
        </div>
        <?php
    }
}
