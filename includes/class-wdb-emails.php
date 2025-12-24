<?php
/**
 * Classe de gerenciamento de emails do WP Donate Brasil
 * @author Dante Testa <https://dantetesta.com.br>
 * @created 23/12/2025 13:10
 */

if (!defined('ABSPATH')) exit;

class WDB_Emails {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Hook para enviar email quando doação é aprovada
        add_action('wdb_donation_approved', array($this, 'send_approved_email'), 10, 1);
    }
    
    // Substitui macros nas mensagens
    private function replace_macros($text, $donation_data) {
        $macros = array(
            '{nome}' => $donation_data['donor_name'] ?? '',
            '{email}' => $donation_data['donor_email'] ?? '',
            '{valor}' => number_format(floatval($donation_data['donation_amount'] ?? 0), 2, ',', '.'),
            '{metodo}' => $this->get_method_label($donation_data['donation_method'] ?? ''),
            '{data}' => date_i18n('d/m/Y H:i', strtotime($donation_data['created_at'] ?? 'now')),
            '{mensagem}' => $donation_data['message'] ?? ''
        );
        
        return str_replace(array_keys($macros), array_values($macros), $text);
    }
    
    // Retorna label do método de pagamento
    private function get_method_label($method_id) {
        $labels = array(
            'pix' => 'PIX',
            'bank_transfer' => 'Transferência Bancária',
            'bitcoin' => 'Bitcoin',
            'paypal' => 'PayPal',
            'payment_link' => 'Link de Pagamento'
        );
        return $labels[$method_id] ?? $method_id;
    }
    
    // Envia email usando wp_mail (SMTP configurado)
    private function send_email($to, $subject, $message, $headers = array()) {
        $settings = get_option('wdb_page_settings', array());
        
        $sender_name = $settings['email_sender_name'] ?? get_bloginfo('name');
        $admin_email = $settings['admin_email'] ?? get_option('admin_email');
        
        $default_headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $sender_name . ' <' . $admin_email . '>'
        );
        $headers = array_merge($default_headers, $headers);
        
        // Converte quebras de linha em <br> para HTML
        $message = nl2br(esc_html($message));
        
        // Template básico de email
        $html_message = $this->get_email_template($message);
        
        return wp_mail($to, $subject, $html_message, $headers);
    }
    
    // Template HTML do email
    private function get_email_template($content) {
        $settings = get_option('wdb_page_settings', array());
        $primary_color = $settings['primary_color'] ?? '#3B82F6';
        $sender_name = $settings['email_sender_name'] ?? get_bloginfo('name');
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background:linear-gradient(135deg,' . $primary_color . ',#10B981);padding:30px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:24px;">🙏 ' . esc_html($sender_name) . '</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px 30px;">
                            <div style="color:#374151;font-size:16px;line-height:1.8;">
                                ' . $content . '
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f9fafb;padding:20px 30px;text-align:center;border-top:1px solid #e5e7eb;">
                            <p style="color:#9ca3af;font-size:12px;margin:0;">
                                Este é um email automático. Por favor, não responda.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
    
    // Email para admin: nova doação
    public function send_new_donation_admin($donation_data) {
        $settings = get_option('wdb_page_settings', array());
        
        // Verifica se notificação para admin está habilitada
        if (!($settings['emails_notify_admin'] ?? true)) return false;
        
        $admin_email = $settings['admin_email'] ?? get_option('admin_email');
        $subject = $this->replace_macros($settings['email_admin_new_subject'] ?? '🔔 Nova doação recebida de {nome}', $donation_data);
        $body = $this->replace_macros($settings['email_admin_new_body'] ?? "Nova doação recebida!\n\nDoador: {nome}\nE-mail: {email}\nValor: R$ {valor}\nMétodo: {metodo}\nData: {data}", $donation_data);
        
        return $this->send_email($admin_email, $subject, $body);
    }
    
    // Email para doador: comprovante recebido
    public function send_receipt_received_donor($donation_data) {
        $settings = get_option('wdb_page_settings', array());
        
        // Verifica se notificação para doador está habilitada
        if (!($settings['emails_notify_donor'] ?? true)) return false;
        if (empty($donation_data['donor_email']) || $donation_data['donor_email'] === 'anonimo@anonimo.com') return false;
        
        $subject = $this->replace_macros($settings['email_donor_received_subject'] ?? '📩 Recebemos sua doação, {nome}!', $donation_data);
        $body = $this->replace_macros($settings['email_donor_received_body'] ?? "Olá {nome},\n\nRecebemos seu comprovante de doação no valor de R$ {valor}.\n\nMuito obrigado pelo seu apoio! ❤️", $donation_data);
        
        return $this->send_email($donation_data['donor_email'], $subject, $body);
    }
    
    // Email para doador: doação aprovada
    public function send_approved_email($donation_id) {
        $settings = get_option('wdb_page_settings', array());
        
        // Verifica se notificação para doador está habilitada
        if (!($settings['emails_notify_donor'] ?? true)) return false;
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        $donation = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $donation_id), ARRAY_A);
        
        if (!$donation || $donation['donor_email'] === 'anonimo@anonimo.com') return false;
        
        $subject = $this->replace_macros($settings['email_donor_approved_subject'] ?? '✅ Sua doação foi confirmada, {nome}!', $donation);
        $body = $this->replace_macros($settings['email_donor_approved_body'] ?? "Olá {nome},\n\n🎉 Sua doação no valor de R$ {valor} foi confirmada com sucesso!\n\nMuito obrigado! 🙏❤️", $donation);
        
        return $this->send_email($donation['donor_email'], $subject, $body);
    }
}

// Inicializa
WDB_Emails::get_instance();
