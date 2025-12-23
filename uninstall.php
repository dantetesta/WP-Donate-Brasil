<?php
/**
 * Uninstall WP Donate Brasil
 * 
 * Remove todas as opções e tabelas criadas pelo plugin
 * 
 * @package WP_Donate_Brasil
 * @author Dante Testa <https://dantetesta.com.br>
 * @since 1.0.0
 */

// Se não for chamado pelo WordPress, sair
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Remove opções do plugin
$options_to_delete = array(
    'wdb_donation_methods',
    'wdb_page_settings',
    'wdb_email_settings',
    'wdb_donation_page_id',
    'wdb_db_version'
);

foreach ($options_to_delete as $option) {
    delete_option($option);
}

// Remove tabela de comprovantes
$table_name = $wpdb->prefix . 'wdb_receipts';

// Busca e deleta todas as mídias anexadas antes de remover a tabela
$attachment_ids = $wpdb->get_col("SELECT attachment_id FROM $table_name WHERE attachment_id IS NOT NULL AND attachment_id > 0");
if (!empty($attachment_ids)) {
    foreach ($attachment_ids as $att_id) {
        wp_delete_attachment(intval($att_id), true);
    }
}

// Remove a tabela
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Limpa transients
delete_transient('wdb_donation_stats');
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wdb_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wdb_%'");

// Limpa cache
wp_cache_flush();
