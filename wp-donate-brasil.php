<?php
/**
 * Plugin Name: WP Donate Brasil
 * Plugin URI: https://dantetesta.com.br/plugins
 * Description: Sistema de doações com página pública configurável, múltiplos meios de pagamento e galeria de doadores.
 * Version: 2.0.3
 * Author: Dante Testa
 * Author URI: https://dantetesta.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-donate-brasil
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Update URI: false
 * 
 * @package WP_Donate_Brasil
 * @author Dante Testa <https://dantetesta.com.br>
 * @since 1.0.0
 * @created 23/12/2025 09:21
 */

if (!defined('ABSPATH')) {
    exit;
}

// Constantes do plugin
define('WDB_VERSION', '2.0.3');
define('WDB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WDB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WDB_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WDB_PLUGIN_SLUG', 'wp-donate-brasil');

// Carrega arquivos do plugin
require_once WDB_PLUGIN_DIR . 'includes/class-wdb-main.php';
require_once WDB_PLUGIN_DIR . 'includes/class-wdb-donation-page.php';
require_once WDB_PLUGIN_DIR . 'includes/class-wdb-pix-qrcode.php';
require_once WDB_PLUGIN_DIR . 'includes/class-wdb-emails.php';
require_once WDB_PLUGIN_DIR . 'admin/class-wdb-admin.php';
require_once WDB_PLUGIN_DIR . 'public/class-wdb-frontend.php';

// Inicializa o plugin
function wdb_init() {
    WDB_Main::get_instance();
    
    // Verifica e adiciona novos métodos de doação se não existirem
    wdb_maybe_add_new_methods();
}
add_action('plugins_loaded', 'wdb_init');

// Adiciona novos métodos de doação que podem estar faltando
function wdb_maybe_add_new_methods() {
    $methods = get_option('wdb_donation_methods', array());
    
    // Verifica se os novos métodos já existem
    $existing_ids = array_column($methods, 'id');
    $new_methods_added = false;
    
    // Bitcoin
    if (!in_array('bitcoin', $existing_ids)) {
        $methods[] = array(
            'id' => 'bitcoin',
            'name' => 'Bitcoin',
            'enabled' => false,
            'icon' => 'fa-brands fa-bitcoin',
            'btc_address' => '',
            'btc_network' => 'Bitcoin',
            'instructions' => 'Envie sua doação para o endereço Bitcoin abaixo.'
        );
        $new_methods_added = true;
    }
    
    // Link de Pagamento
    if (!in_array('payment_link', $existing_ids)) {
        $methods[] = array(
            'id' => 'payment_link',
            'name' => 'Link de Pagamento',
            'enabled' => false,
            'icon' => 'fa-solid fa-link',
            'gateway_name' => '',
            'gateway_url' => '',
            'gateway_logo' => '',
            'instructions' => 'Clique no botão abaixo para doar via gateway de pagamento.'
        );
        $new_methods_added = true;
    }
    
    if ($new_methods_added) {
        update_option('wdb_donation_methods', $methods);
        wp_cache_flush();
    }
}

// Bloqueia atualizações do WordPress.org para este plugin
function wdb_block_external_updates($transient) {
    if (isset($transient->response[WDB_PLUGIN_BASENAME])) {
        unset($transient->response[WDB_PLUGIN_BASENAME]);
    }
    return $transient;
}
add_filter('pre_set_site_transient_update_plugins', 'wdb_block_external_updates');
add_filter('site_transient_update_plugins', 'wdb_block_external_updates');

// Remove verificação de atualização para este plugin específico
function wdb_disable_plugin_update_check($r, $url) {
    if (strpos($url, 'api.wordpress.org/plugins/update-check') !== false) {
        $plugins = json_decode($r['body']['plugins'], true);
        if (isset($plugins['plugins'][WDB_PLUGIN_BASENAME])) {
            unset($plugins['plugins'][WDB_PLUGIN_BASENAME]);
            $r['body']['plugins'] = json_encode($plugins);
        }
    }
    return $r;
}
add_filter('http_request_args', 'wdb_disable_plugin_update_check', 10, 2);

// Adiciona link de configurações na lista de plugins
function wdb_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=wdb_donation_settings') . '">' . __('Configurações', 'wp-donate-brasil') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . WDB_PLUGIN_BASENAME, 'wdb_plugin_action_links');

// Adiciona informações na linha do plugin
function wdb_plugin_row_meta($links, $file) {
    if ($file === WDB_PLUGIN_BASENAME) {
        $links[] = '<span style="color: #10B981; font-weight: bold;">✓ ' . __('Atualizações gerenciadas localmente', 'wp-donate-brasil') . '</span>';
    }
    return $links;
}
add_filter('plugin_row_meta', 'wdb_plugin_row_meta', 10, 2);

// Hook de ativação
function wdb_activate() {
    // Cria tabela de comprovantes
    global $wpdb;
    $table_name = $wpdb->prefix . 'wdb_receipts';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        donor_name varchar(255) NOT NULL,
        donor_email varchar(255) NOT NULL,
        donor_phone varchar(50) DEFAULT NULL,
        donation_method varchar(100) NOT NULL,
        donation_amount decimal(10,2) DEFAULT NULL,
        receipt_file varchar(500) NOT NULL,
        attachment_id bigint(20) DEFAULT NULL,
        message text DEFAULT NULL,
        status varchar(20) DEFAULT 'pending',
        show_in_gallery tinyint(1) DEFAULT 1,
        anonymous tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status),
        KEY created_at (created_at),
        KEY attachment_id (attachment_id)
    ) $charset_collate;";
    
    // Adiciona coluna attachment_id se não existir (para atualizações)
    $wpdb->query("ALTER TABLE $table_name ADD COLUMN IF NOT EXISTS attachment_id bigint(20) DEFAULT NULL AFTER receipt_file");
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Opções padrão
    $default_settings = array(
        'page_title' => 'Faça uma Doação',
        'page_subtitle' => 'Sua contribuição faz a diferença!',
        'page_description' => 'Escolha uma das formas de doação abaixo e ajude nossa causa.',
        'primary_color' => '#3B82F6',
        'secondary_color' => '#10B981',
        'show_gallery' => true,
        'gallery_title' => 'Nossos Doadores',
        'items_per_page' => 12
    );
    
    if (!get_option('wdb_page_settings')) {
        add_option('wdb_page_settings', $default_settings);
    }
    
    $default_methods = array(
        array(
            'id' => 'pix',
            'name' => 'PIX',
            'enabled' => true,
            'icon' => 'fa-brands fa-pix',
            'pix_key' => '',
            'pix_name' => '',
            'pix_city' => '',
            'pix_bank' => '',
            'pix_description' => '',
            'instructions' => 'Escaneie o QR Code ou copie a chave PIX e faça a transferência pelo seu banco.'
        ),
        array(
            'id' => 'bank_transfer',
            'name' => 'Transferência Bancária',
            'enabled' => false,
            'icon' => 'fa-solid fa-building-columns',
            'bank_name' => '',
            'bank_agency' => '',
            'bank_account' => '',
            'bank_holder' => '',
            'bank_cpf_cnpj' => '',
            'instructions' => 'Faça a transferência para a conta indicada.'
        ),
        array(
            'id' => 'bitcoin',
            'name' => 'Bitcoin',
            'enabled' => false,
            'icon' => 'fa-brands fa-bitcoin',
            'btc_address' => '',
            'btc_network' => 'Bitcoin',
            'instructions' => 'Envie sua doação para o endereço Bitcoin abaixo.'
        ),
        array(
            'id' => 'payment_link',
            'name' => 'Link de Pagamento',
            'enabled' => false,
            'icon' => 'fa-solid fa-link',
            'gateway_name' => '',
            'gateway_url' => '',
            'gateway_logo' => '',
            'instructions' => 'Clique no botão abaixo para doar via gateway de pagamento.'
        ),
        array(
            'id' => 'paypal',
            'name' => 'PayPal',
            'enabled' => false,
            'icon' => 'fa-brands fa-paypal',
            'paypal_email' => '',
            'instructions' => 'Envie sua doação via PayPal.'
        ),
        array(
            'id' => 'wise',
            'name' => 'Wise',
            'enabled' => false,
            'icon' => 'fa-solid fa-money-bill-transfer',
            'wise_tag' => '',
            'instructions' => 'Escaneie o QR Code ou clique no link para doar via Wise.'
        )
    );
    
    if (!get_option('wdb_donation_methods')) {
        add_option('wdb_donation_methods', $default_methods);
    }
    
    // Criar página de doações
    $page_exists = get_page_by_path('doacoes');
    if (!$page_exists) {
        $page_data = array(
            'post_title'    => 'Doações',
            'post_name'     => 'doacoes',
            'post_content'  => '[wp_donate_brasil_page]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1
        );
        $page_id = wp_insert_post($page_data);
        update_option('wdb_donation_page_id', $page_id);
    }
    
    // Limpa cache
    wp_cache_flush();
    
    // Atualiza versão
    update_option('wdb_version', WDB_VERSION);
}
register_activation_hook(__FILE__, 'wdb_activate');

// Upgrade: adiciona novos métodos aos existentes
function wdb_maybe_upgrade() {
    $current_version = get_option('wdb_version', '1.0.0');
    
    // Upgrade para 2.0.3: adiciona método Wise
    if (version_compare($current_version, '2.0.3', '<')) {
        $methods = get_option('wdb_donation_methods', array());
        
        // Verifica se Wise já existe
        $has_wise = false;
        foreach ($methods as $method) {
            if ($method['id'] === 'wise') {
                $has_wise = true;
                break;
            }
        }
        
        // Adiciona Wise se não existir
        if (!$has_wise) {
            $methods[] = array(
                'id' => 'wise',
                'name' => 'Wise',
                'enabled' => false,
                'icon' => 'fa-solid fa-money-bill-transfer',
                'wise_tag' => '',
                'instructions' => 'Escaneie o QR Code ou clique no link para doar via Wise.'
            );
            update_option('wdb_donation_methods', $methods);
        }
        
        update_option('wdb_version', WDB_VERSION);
        wp_cache_flush();
    }
}
add_action('admin_init', 'wdb_maybe_upgrade');

// Hook de desativação
function wdb_deactivate() {
    wp_cache_flush();
}
register_deactivation_hook(__FILE__, 'wdb_deactivate');

// Hook de desinstalação
function wdb_uninstall() {
    global $wpdb;
    
    // Remove tabela
    $table_name = $wpdb->prefix . 'wdb_receipts';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    
    // Remove opções
    delete_option('wdb_page_settings');
    delete_option('wdb_donation_methods');
    delete_option('wdb_donation_page_id');
    delete_option('wdb_version');
    
    // Remove página de doações
    $page_id = get_option('wdb_donation_page_id');
    if ($page_id) {
        wp_delete_post($page_id, true);
    }
    
    wp_cache_flush();
}
register_uninstall_hook(__FILE__, 'wdb_uninstall');
