<?php
/**
 * Classe principal do plugin WP Donate Brasil
 * 
 * @package WP_Donate_Brasil
 * @author Dante Testa <https://dantetesta.com.br>
 * @since 1.0.0
 * @created 23/12/2025 09:21
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDB_Main {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_textdomain();
        $this->init_components();
        $this->register_hooks();
    }
    
    private function load_textdomain() {
        load_plugin_textdomain('wp-donate-brasil', false, dirname(WDB_PLUGIN_BASENAME) . '/languages');
    }
    
    private function init_components() {
        WDB_Admin::get_instance();
        WDB_Frontend::get_instance();
        WDB_Donation_Page::get_instance();
    }
    
    private function register_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_wdb_submit_receipt', array($this, 'ajax_submit_receipt'));
        add_action('wp_ajax_nopriv_wdb_submit_receipt', array($this, 'ajax_submit_receipt'));
        add_action('wp_ajax_wdb_update_receipt_status', array($this, 'ajax_update_receipt_status'));
        add_action('wp_ajax_wdb_delete_receipt', array($this, 'ajax_delete_receipt'));
        add_action('wp_ajax_wdb_update_receipt', array($this, 'ajax_update_receipt'));
        add_action('wp_ajax_wdb_get_pix_payload', array($this, 'ajax_get_pix_payload'));
        add_action('wp_ajax_nopriv_wdb_get_pix_payload', array($this, 'ajax_get_pix_payload'));
    }
    
    // Retorna payload PIX gerado no servidor
    public function ajax_get_pix_payload() {
        // Verificação de nonce para segurança
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'wdb_nonce_action')) {
            wp_send_json_error(array('message' => __('Erro de segurança.', 'wp-donate-brasil')));
        }
        
        $method_id = sanitize_text_field($_POST['method_id'] ?? 'pix');
        $methods = get_option('wdb_donation_methods', array());
        
        $method = null;
        foreach ($methods as $m) {
            if ($m['id'] === $method_id) {
                $method = $m;
                break;
            }
        }
        
        if (!$method || empty($method['pix_key']) || empty($method['pix_name'])) {
            wp_send_json_error(array('message' => 'Dados PIX não configurados'));
        }
        
        $pix_city = !empty($method['pix_city']) ? $method['pix_city'] : 'SAO PAULO';
        $payload = WDB_Pix_QRCode::generate_payload(
            $method['pix_key'],
            $method['pix_name'],
            $pix_city
        );
        
        wp_send_json_success(array(
            'payload' => $payload,
            'qr_url' => WDB_Pix_QRCode::get_qrcode_url($payload, 200)
        ));
    }
    
    public function enqueue_frontend_assets() {
        // Font Awesome local
        wp_enqueue_style('font-awesome-wdb', WDB_PLUGIN_URL . 'assets/css/fontawesome.min.css', array(), '6.5.1');
        
        if (!$this->is_donation_page()) {
            return;
        }
        
        wp_enqueue_style('wdb-frontend', WDB_PLUGIN_URL . 'public/css/frontend.css', array('font-awesome-wdb'), WDB_VERSION);
        wp_enqueue_script('wdb-frontend', WDB_PLUGIN_URL . 'public/js/frontend.js', array('jquery'), WDB_VERSION, true);
        wp_enqueue_style('swiper', WDB_PLUGIN_URL . 'assets/css/swiper.min.css', array(), '11.0.0');
        wp_enqueue_script('swiper', WDB_PLUGIN_URL . 'assets/js/swiper.min.js', array(), '11.0.0', true);
        
        $settings = get_option('wdb_page_settings', array());
        wp_localize_script('wdb-frontend', 'wdb_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wdb_nonce_action'),
            'primary_color' => $settings['primary_color'] ?? '#3B82F6',
            'secondary_color' => $settings['secondary_color'] ?? '#10B981',
            'strings' => array(
                'copied' => __('Copiado!', 'wp-donate-brasil'),
                'copy_error' => __('Erro ao copiar', 'wp-donate-brasil'),
                'uploading' => __('Enviando...', 'wp-donate-brasil'),
                'success' => __('Comprovante enviado com sucesso!', 'wp-donate-brasil'),
                'error' => __('Erro ao enviar. Tente novamente.', 'wp-donate-brasil'),
                'file_required' => __('Por favor, selecione um arquivo.', 'wp-donate-brasil'),
                'invalid_file' => __('Tipo de arquivo não permitido.', 'wp-donate-brasil')
            )
        ));
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wdb_donation') === false && strpos($hook, 'wdb-') === false && strpos($hook, 'wdb_') === false) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_style('tailwindcss', WDB_PLUGIN_URL . 'assets/css/tailwind.min.css', array(), '3.4.17');
        wp_enqueue_style('font-awesome', WDB_PLUGIN_URL . 'assets/css/fontawesome.min.css', array('tailwindcss'), '6.5.1');
        wp_enqueue_style('wdb-admin', WDB_PLUGIN_URL . 'admin/css/admin.css', array(), WDB_VERSION);
        wp_enqueue_script('wdb-admin', WDB_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), WDB_VERSION, true);
        
        wp_localize_script('wdb-admin', 'wdb_admin_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wdb_nonce_action'),
            'strings' => array(
                'confirm_approve' => __('Aprovar este comprovante?', 'wp-donate-brasil'),
                'confirm_reject' => __('Rejeitar este comprovante?', 'wp-donate-brasil'),
                'confirm_delete' => __('Excluir permanentemente?', 'wp-donate-brasil'),
                'saved' => __('Salvo com sucesso!', 'wp-donate-brasil'),
                'error' => __('Erro ao salvar.', 'wp-donate-brasil')
            )
        ));
    }
    
    private function is_donation_page() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        $page_id = get_option('wdb_donation_page_id');
        if ($post->ID == $page_id) {
            return true;
        }
        
        if (has_shortcode($post->post_content, 'wp_donate_brasil_page')) {
            return true;
        }
        
        return false;
    }
    
    // Converte valor formatado em brasileiro (1.234,56) para float (1234.56)
    public static function parse_brazilian_money($value) {
        if (empty($value)) return 0;
        $value = preg_replace('/[^\d,.]/', '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return floatval($value);
    }
    
    // Captura IP do visitante
    public static function get_visitor_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field(trim($ip));
    }
    
    // Geolocalização via ip-api.com (gratuita, sem chave, 45 req/min)
    public static function get_geolocation_by_ip() {
        $default = array('ip' => 'Anônimo', 'country' => 'Anônimo', 'state' => 'Anônimo', 'city' => 'Anônimo');
        
        $ip = self::get_visitor_ip();
        if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
            $default['ip'] = $ip ?: 'localhost';
            return $default;
        }
        
        // Consulta API ip-api.com
        $response = wp_remote_get("http://ip-api.com/json/{$ip}?fields=status,country,regionName,city", array(
            'timeout' => 5,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            $default['ip'] = $ip;
            return $default;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data) || $data['status'] !== 'success') {
            $default['ip'] = $ip;
            return $default;
        }
        
        return array(
            'ip' => $ip,
            'country' => !empty($data['country']) ? sanitize_text_field($data['country']) : 'Anônimo',
            'state' => !empty($data['regionName']) ? sanitize_text_field($data['regionName']) : 'Anônimo',
            'city' => !empty($data['city']) ? sanitize_text_field($data['city']) : 'Anônimo'
        );
    }
    
    public function ajax_submit_receipt() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'wdb_nonce_action')) {
            wp_send_json_error(array('message' => __('Erro de segurança. Recarregue a página.', 'wp-donate-brasil')));
        }
        
        $donor_name = sanitize_text_field($_POST['donor_name'] ?? '');
        $donor_email = sanitize_email($_POST['donor_email'] ?? '');
        $donor_phone = sanitize_text_field($_POST['donor_phone'] ?? '');
        $donation_method = sanitize_text_field($_POST['donation_method'] ?? '');
        $donation_amount = self::parse_brazilian_money($_POST['donation_amount'] ?? 0);
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        $anonymous = isset($_POST['anonymous']) ? 1 : 0;
        $show_in_gallery = isset($_POST['show_in_gallery']) ? 1 : 0;
        
        if (empty($donor_name) || empty($donor_email) || empty($donation_method)) {
            wp_send_json_error(array('message' => __('Preencha todos os campos obrigatórios.', 'wp-donate-brasil')));
        }
        
        if (!is_email($donor_email)) {
            wp_send_json_error(array('message' => __('E-mail inválido.', 'wp-donate-brasil')));
        }
        
        if (empty($_FILES['receipt_file'])) {
            wp_send_json_error(array('message' => __('Selecione o comprovante.', 'wp-donate-brasil')));
        }
        
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf');
        $file = $_FILES['receipt_file'];
        
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(array('message' => __('Tipo de arquivo não permitido. Use JPG, PNG, GIF ou PDF.', 'wp-donate-brasil')));
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            wp_send_json_error(array('message' => __('Arquivo muito grande. Máximo 5MB.', 'wp-donate-brasil')));
        }
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        // Upload e registro na Media Library
        $upload = wp_handle_upload($file, array('test_form' => false));
        
        if (isset($upload['error'])) {
            wp_send_json_error(array('message' => $upload['error']));
        }
        
        // Registra como attachment na Media Library
        $attachment_id = 0;
        $attachment = array(
            'post_mime_type' => $file['type'],
            'post_title' => sanitize_file_name(pathinfo($file['name'], PATHINFO_FILENAME)),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $upload['file']);
        
        if (!is_wp_error($attachment_id)) {
            $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            wp_update_attachment_metadata($attachment_id, $attach_data);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        // Captura IP e geolocalização
        $geo_data = self::get_geolocation_by_ip();
        
        $result = $wpdb->insert($table_name, array(
            'donor_name' => $donor_name,
            'donor_email' => $donor_email,
            'donor_phone' => $donor_phone,
            'donation_method' => $donation_method,
            'donation_amount' => $donation_amount,
            'receipt_file' => $upload['url'],
            'attachment_id' => $attachment_id,
            'message' => $message,
            'status' => 'pending',
            'show_in_gallery' => $show_in_gallery,
            'anonymous' => $anonymous,
            'donor_ip' => $geo_data['ip'],
            'donor_country' => $geo_data['country'],
            'donor_state' => $geo_data['state'],
            'donor_city' => $geo_data['city'],
            'created_at' => current_time('mysql')
        ), array('%s', '%s', '%s', '%s', '%f', '%s', '%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s'));
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Erro ao salvar. Tente novamente.', 'wp-donate-brasil')));
        }
        
        // Envia emails de notificação
        $donation_data = array(
            'donor_name' => $donor_name,
            'donor_email' => $donor_email,
            'donation_amount' => $donation_amount,
            'donation_method' => $donation_method,
            'message' => $message,
            'created_at' => current_time('mysql')
        );
        
        $emails = WDB_Emails::get_instance();
        $emails->send_new_donation_admin($donation_data);
        $emails->send_receipt_received_donor($donation_data);
        
        // Retorna dados para exibir mensagem de agradecimento
        $settings = get_option('wdb_page_settings', array());
        
        wp_send_json_success(array(
            'message' => __('Comprovante enviado com sucesso! Aguarde a validação.', 'wp-donate-brasil'),
            'show_thank_you' => true,
            'thank_you_title' => $settings['thank_you_title'] ?? 'Muito Obrigado! 🙏',
            'thank_you_subtitle' => $settings['thank_you_subtitle'] ?? 'Sua doação faz a diferença!',
            'thank_you_message' => $settings['thank_you_message'] ?? 'Recebemos seu comprovante e em breve confirmaremos sua doação. Que Deus abençoe você e sua família! ❤️'
        ));
    }
    
    public function ajax_update_receipt_status() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sem permissão.', 'wp-donate-brasil')));
        }
        
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'wdb_nonce_action')) {
            wp_send_json_error(array('message' => __('Erro de segurança.', 'wp-donate-brasil')));
        }
        
        $receipt_id = intval($_POST['receipt_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        
        if (!$receipt_id || !in_array($status, array('pending', 'approved', 'rejected'))) {
            wp_send_json_error(array('message' => __('Dados inválidos.', 'wp-donate-brasil')));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        $result = $wpdb->update(
            $table_name,
            array('status' => $status, 'updated_at' => current_time('mysql')),
            array('id' => $receipt_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Erro ao atualizar.', 'wp-donate-brasil')));
        }
        
        if ($status === 'approved') {
            // Dispara hook para enviar email de aprovação
            do_action('wdb_donation_approved', $receipt_id);
        }
        
        wp_send_json_success(array('message' => __('Status atualizado!', 'wp-donate-brasil')));
    }
    
    public function ajax_delete_receipt() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sem permissão.', 'wp-donate-brasil')));
        }
        
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'wdb_nonce_action')) {
            wp_send_json_error(array('message' => __('Erro de segurança.', 'wp-donate-brasil')));
        }
        
        $receipt_id = intval($_POST['receipt_id'] ?? 0);
        
        if (!$receipt_id) {
            wp_send_json_error(array('message' => __('ID inválido.', 'wp-donate-brasil')));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        // Busca attachment_id antes de deletar
        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT attachment_id FROM $table_name WHERE id = %d",
            $receipt_id
        ));
        
        // Deleta a mídia da Media Library se existir
        if ($attachment_id && $attachment_id > 0) {
            wp_delete_attachment($attachment_id, true);
        }
        
        $result = $wpdb->delete($table_name, array('id' => $receipt_id), array('%d'));
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Erro ao excluir.', 'wp-donate-brasil')));
        }
        
        wp_send_json_success(array('message' => __('Excluído com sucesso!', 'wp-donate-brasil')));
    }
    
    // Atualiza dados de uma doação
    public function ajax_update_receipt() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sem permissão.', 'wp-donate-brasil')));
        }
        
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'wdb_update_receipt')) {
            wp_send_json_error(array('message' => __('Erro de segurança.', 'wp-donate-brasil')));
        }
        
        $receipt_id = intval($_POST['receipt_id'] ?? 0);
        if (!$receipt_id) {
            wp_send_json_error(array('message' => __('ID inválido.', 'wp-donate-brasil')));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        $data = array(
            'donor_name' => sanitize_text_field($_POST['donor_name'] ?? ''),
            'donor_email' => sanitize_email($_POST['donor_email'] ?? ''),
            'donor_phone' => sanitize_text_field($_POST['donor_phone'] ?? ''),
            'donation_method' => sanitize_text_field($_POST['donation_method'] ?? 'pix'),
            'donation_amount' => self::parse_brazilian_money($_POST['donation_amount'] ?? 0),
            'message' => sanitize_textarea_field($_POST['donor_message'] ?? ''),
            'anonymous' => intval($_POST['anonymous'] ?? 0),
            'show_in_gallery' => intval($_POST['show_in_gallery'] ?? 1)
        );
        
        // Define status baseado no switch
        if (isset($_POST['status_approved'])) {
            $data['status'] = intval($_POST['status_approved']) === 1 ? 'approved' : 'pending';
        }
        
        $formats = array('%s', '%s', '%s', '%s', '%f', '%s', '%d', '%d', '%s');
        
        $result = $wpdb->update($table_name, $data, array('id' => $receipt_id), $formats, array('%d'));
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Erro ao atualizar.', 'wp-donate-brasil')));
        }
        
        wp_send_json_success(array('message' => __('Atualizado com sucesso!', 'wp-donate-brasil')));
    }
    
    private function notify_admin_new_receipt($receipt_id, $donor_name, $donor_email, $method) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf(__('[%s] Novo comprovante de doação', 'wp-donate-brasil'), $site_name);
        
        $message = sprintf(
            __("Um novo comprovante de doação foi enviado:\n\nDoador: %s\nE-mail: %s\nMétodo: %s\n\nAcesse o painel para revisar: %s", 'wp-donate-brasil'),
            $donor_name,
            $donor_email,
            $method,
            admin_url('admin.php?page=wdb_receipts')
        );
        
        wp_mail($admin_email, $subject, $message);
    }
    
    private function notify_donor_approved($receipt) {
        $site_name = get_bloginfo('name');
        
        $subject = sprintf(__('[%s] Sua doação foi aprovada!', 'wp-donate-brasil'), $site_name);
        
        $message = sprintf(
            __("Olá %s,\n\nSua doação foi aprovada! Muito obrigado pela sua generosidade.\n\nAtenciosamente,\nEquipe %s", 'wp-donate-brasil'),
            $receipt->donor_name,
            $site_name
        );
        
        wp_mail($receipt->donor_email, $subject, $message);
    }
    
    public static function get_receipts($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        $defaults = array(
            'status' => '',
            'search' => '',
            'month' => '',
            'year' => '',
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = '1=1';
        
        if (!empty($args['status'])) {
            $where .= $wpdb->prepare(' AND status = %s', $args['status']);
        }
        
        // Filtro de pesquisa (nome, email, telefone)
        if (!empty($args['search'])) {
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $where .= $wpdb->prepare(' AND (donor_name LIKE %s OR donor_email LIKE %s OR donor_phone LIKE %s)', $search, $search, $search);
        }
        
        // Filtro por mês/ano
        if (!empty($args['month']) && !empty($args['year'])) {
            $where .= $wpdb->prepare(' AND MONTH(created_at) = %d AND YEAR(created_at) = %d', intval($args['month']), intval($args['year']));
        } elseif (!empty($args['year'])) {
            $where .= $wpdb->prepare(' AND YEAR(created_at) = %d', intval($args['year']));
        }
        
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        if (!$orderby) {
            $orderby = 'created_at DESC';
        }
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE $where ORDER BY $orderby LIMIT %d OFFSET %d",
            $args['limit'],
            $args['offset']
        );
        
        return $wpdb->get_results($sql);
    }
    
    public static function count_receipts($status = '', $search = '', $month = '', $year = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        $where = '1=1';
        
        if (!empty($status)) {
            $where .= $wpdb->prepare(' AND status = %s', $status);
        }
        
        if (!empty($search)) {
            $search_like = '%' . $wpdb->esc_like($search) . '%';
            $where .= $wpdb->prepare(' AND (donor_name LIKE %s OR donor_email LIKE %s OR donor_phone LIKE %s)', $search_like, $search_like, $search_like);
        }
        
        if (!empty($month) && !empty($year)) {
            $where .= $wpdb->prepare(' AND MONTH(created_at) = %d AND YEAR(created_at) = %d', intval($month), intval($year));
        } elseif (!empty($year)) {
            $where .= $wpdb->prepare(' AND YEAR(created_at) = %d', intval($year));
        }
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");
    }
    
    // Retorna anos disponíveis para filtro
    public static function get_available_years() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        return $wpdb->get_col("SELECT DISTINCT YEAR(created_at) as year FROM $table_name ORDER BY year DESC");
    }
    
    // Doadores aleatórios para carrossel (exclui anônimos)
    public static function get_approved_donors($limit = 12) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        // Agrupa por e-mail, conta doações e pega aleatoriamente (sem anônimos no carrossel)
        return $wpdb->get_results($wpdb->prepare(
            "SELECT 
                donor_email,
                MAX(donor_name) as donor_name,
                MAX(donor_phone) as donor_phone,
                MAX(message) as message,
                MAX(anonymous) as anonymous,
                MAX(donation_method) as donation_method,
                COUNT(*) as donation_count,
                MAX(created_at) as created_at,
                SUM(donation_amount) as total_amount
            FROM $table_name 
            WHERE status = 'approved' AND show_in_gallery = 1 AND anonymous = 0
            GROUP BY donor_email 
            ORDER BY RAND() 
            LIMIT %d",
            $limit
        ));
    }
    
    // Todos os doadores para lista completa
    public static function get_all_approved_donors() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        return $wpdb->get_results(
            "SELECT 
                donor_email,
                MAX(donor_name) as donor_name,
                MAX(donor_phone) as donor_phone,
                MAX(message) as message,
                MAX(anonymous) as anonymous,
                MAX(donation_method) as donation_method,
                COUNT(*) as donation_count,
                MAX(created_at) as created_at,
                SUM(donation_amount) as total_amount
            FROM $table_name 
            WHERE status = 'approved' AND show_in_gallery = 1 
            GROUP BY donor_email 
            ORDER BY MAX(created_at) DESC"
        );
    }
    
    // Conta total de doadores únicos (mesma lógica da lista completa)
    public static function count_approved_donors() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        // Conta doadores identificados (por email único) + anônimos agrupados por método
        return $wpdb->get_var(
            "SELECT COUNT(*) FROM (
                SELECT donor_email FROM $table_name 
                WHERE status = 'approved' AND show_in_gallery = 1 AND donor_email != 'anonimo@anonimo.com' 
                GROUP BY donor_email
                UNION ALL
                SELECT CONCAT('anon_', donation_method) FROM $table_name 
                WHERE status = 'approved' AND show_in_gallery = 1 AND donor_email = 'anonimo@anonimo.com' 
                GROUP BY donation_method
            ) as combined"
        );
    }
    
    // Ofusca e-mail (ex: d***@gmail.com)
    public static function obfuscate_email($email) {
        if (empty($email)) return '';
        $parts = explode('@', $email);
        if (count($parts) !== 2) return '***@***.***';
        $name = $parts[0];
        $domain = $parts[1];
        $obfuscated = substr($name, 0, 1) . str_repeat('*', min(strlen($name) - 1, 3)) . '@' . $domain;
        return $obfuscated;
    }
    
    // Ofusca telefone (ex: (11) ****-1234)
    public static function obfuscate_phone($phone) {
        if (empty($phone)) return '';
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) < 8) return '****-****';
        $last4 = substr($clean, -4);
        if (strlen($clean) >= 10) {
            $ddd = substr($clean, 0, 2);
            return "({$ddd}) ****-{$last4}";
        }
        return "****-{$last4}";
    }
    
    // Gera URL do Gravatar baseado no e-mail
    public static function get_gravatar_url($email, $size = 150) {
        $hash = md5(strtolower(trim($email)));
        $default = urlencode('https://ui-avatars.com/api/' . urlencode(substr($email, 0, 1)) . '/150/3B82F6/ffffff/2/0.5/true');
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d={$default}";
    }
}
