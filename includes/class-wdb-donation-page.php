<?php
/**
 * Classe responsável pela página pública de doações
 * 
 * @package WP_Donate_Brasil
 * @author Dante Testa <https://dantetesta.com.br>
 * @since 1.0.0
 * @created 23/12/2025 09:21
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDB_Donation_Page {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('wp_donate_brasil_page', array($this, 'render_donation_page'));
        add_shortcode('wp_donate_brasil_gallery', array($this, 'render_donors_gallery'));
        add_shortcode('wp_donate_brasil_fullpage', array($this, 'render_fullpage'));
        add_shortcode('wdb_donors_list', array($this, 'render_donors_list_page'));
        
        // Template para página isolada
        add_filter('template_include', array($this, 'load_fullpage_template'));
    }
    
    // Carrega template isolado para página de doações
    public function load_fullpage_template($template) {
        $page_id = get_option('wdb_donation_page_id');
        if (is_page($page_id) && isset($_GET['fullpage'])) {
            return WDB_PLUGIN_DIR . 'public/templates/fullpage-donation.php';
        }
        return $template;
    }
    
    public function render_donation_page($atts) {
        $settings = get_option('wdb_page_settings', array());
        $methods = get_option('wdb_donation_methods', array());
        
        // Configurações de cores
        $primary_color = esc_attr($settings['primary_color'] ?? '#3B82F6');
        $secondary_color = esc_attr($settings['secondary_color'] ?? '#10B981');
        $bg_color = esc_attr($settings['bg_color'] ?? '#0F172A');
        
        ob_start();
        ?>
        <link rel="stylesheet" href="<?php echo esc_url(WDB_PLUGIN_URL . 'assets/css/fontawesome.min.css'); ?>">
        
        <style>
            :root {
                --wdb-primary: <?php echo $primary_color; ?>;
                --wdb-secondary: <?php echo $secondary_color; ?>;
                --wdb-bg: <?php echo $bg_color; ?>;
            }
            
            #wdb-donation-wrapper {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                min-height: 100vh;
                background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
                position: relative;
                overflow: hidden;
            }
            
            #wdb-donation-wrapper * { box-sizing: border-box; }
            
            /* Efeito de bolhas decorativas */
            .wdb-stars {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                background-image: 
                    radial-gradient(circle at 10% 20%, <?php echo $this->hex_to_rgba($primary_color, 0.05); ?> 0%, transparent 50%),
                    radial-gradient(circle at 90% 80%, <?php echo $this->hex_to_rgba($secondary_color, 0.05); ?> 0%, transparent 50%);
            }
            
            /* Glow orbs - versão light */
            .wdb-glow-1 {
                position: absolute;
                top: -200px;
                right: -200px;
                width: 600px;
                height: 600px;
                background: radial-gradient(circle, <?php echo $this->hex_to_rgba($primary_color, 0.1); ?> 0%, transparent 70%);
                border-radius: 50%;
                filter: blur(60px);
                animation: wdb-float 8s ease-in-out infinite;
            }
            
            .wdb-glow-2 {
                position: absolute;
                bottom: -150px;
                left: -150px;
                width: 500px;
                height: 500px;
                background: radial-gradient(circle, <?php echo $this->hex_to_rgba($secondary_color, 0.1); ?> 0%, transparent 70%);
                border-radius: 50%;
                filter: blur(50px);
                animation: wdb-float 10s ease-in-out infinite reverse;
            }
            
            @keyframes wdb-float {
                0%, 100% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(30px, -30px) scale(1.1); }
            }
            
            .wdb-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 60px 20px;
                position: relative;
                z-index: 10;
            }
            
            /* Hero Section */
            .wdb-hero {
                text-align: center;
                padding: 60px 0 80px;
            }
            
            .wdb-hero-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 20px;
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 50px;
                color: #475569;
                font-size: 0.875rem;
                font-weight: 600;
                margin-bottom: 30px;
                animation: wdb-fadeInUp 0.6s ease;
                box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            }
            
            .wdb-hero-badge i {
                color: <?php echo $secondary_color; ?>;
            }
            
            .wdb-hero-title {
                font-size: clamp(2.5rem, 6vw, 4.5rem);
                font-weight: 900;
                background: linear-gradient(135deg, #1e293b 0%, <?php echo $primary_color; ?> 50%, <?php echo $secondary_color; ?> 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                margin: 0 0 20px 0;
                line-height: 1.1;
                animation: wdb-fadeInUp 0.6s ease 0.1s both;
            }
            
            .wdb-hero-subtitle {
                font-size: clamp(1.1rem, 2.5vw, 1.5rem);
                color: #475569;
                font-weight: 500;
                margin: 0 0 15px 0;
                animation: wdb-fadeInUp 0.6s ease 0.2s both;
            }
            
            .wdb-hero-desc {
                font-size: 1rem;
                color: #64748b;
                max-width: 600px;
                margin: 0 auto 40px;
                line-height: 1.7;
                animation: wdb-fadeInUp 0.6s ease 0.3s both;
            }
            
            .wdb-hero-stats {
                display: flex;
                justify-content: center;
                gap: 50px;
                flex-wrap: wrap;
                animation: wdb-fadeInUp 0.6s ease 0.4s both;
            }
            
            .wdb-stat {
                text-align: center;
                background: white;
                padding: 25px 35px;
                border-radius: 20px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            }
            
            .wdb-stat-value {
                font-size: 2.5rem;
                font-weight: 800;
                background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            
            .wdb-stat-label {
                font-size: 0.875rem;
                color: #64748b;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            @keyframes wdb-fadeInUp {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            /* Section Title */
            .wdb-section-title {
                text-align: center;
                margin-bottom: 50px;
            }
            
            .wdb-section-title h2 {
                font-size: 2rem;
                font-weight: 800;
                color: #1e293b;
                margin: 0 0 10px;
            }
            
            .wdb-section-title p {
                color: #64748b;
                font-size: 1rem;
                margin: 0;
            }
            
            /* Cards Grid */
            .wdb-methods-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 24px;
                margin-bottom: 80px;
            }
            
            .wdb-method-card {
                position: relative;
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 24px;
                padding: 40px 30px;
                text-align: center;
                cursor: pointer;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            }
            
            .wdb-method-card::before {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, transparent 0%, <?php echo $this->hex_to_rgba($primary_color, 0.03); ?> 100%);
                opacity: 0;
                transition: opacity 0.4s ease;
            }
            
            .wdb-method-card:hover {
                transform: translateY(-8px) scale(1.02);
                border-color: <?php echo $this->hex_to_rgba($primary_color, 0.3); ?>;
                box-shadow: 0 20px 50px rgba(0,0,0,0.12);
            }
            
            .wdb-method-card:hover::before {
                opacity: 1;
            }
            
            .wdb-method-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 25px;
                border-radius: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 32px;
                color: white;
                position: relative;
                transition: transform 0.4s ease;
            }
            
            .wdb-method-card:hover .wdb-method-icon {
                transform: scale(1.1) rotate(5deg);
            }
            
            .wdb-method-icon.pix { background: linear-gradient(135deg, #00D4AA, #00B894); box-shadow: 0 10px 40px rgba(0,212,170,0.3); }
            .wdb-method-icon.bank { background: linear-gradient(135deg, #3B82F6, #1D4ED8); box-shadow: 0 10px 40px rgba(59,130,246,0.3); }
            .wdb-method-icon.bitcoin { background: linear-gradient(135deg, #F7931A, #E67E22); box-shadow: 0 10px 40px rgba(247,147,26,0.3); }
            .wdb-method-icon.paypal { background: linear-gradient(135deg, #003087, #009CDE); box-shadow: 0 10px 40px rgba(0,156,222,0.3); }
            .wdb-method-icon.link { background: linear-gradient(135deg, #8B5CF6, #6D28D9); box-shadow: 0 10px 40px rgba(139,92,246,0.3); }
            
            .wdb-method-title {
                font-size: 1.5rem;
                font-weight: 700;
                color: #1e293b;
                margin: 0 0 10px;
            }
            
            .wdb-method-desc {
                font-size: 0.9rem;
                color: #64748b;
                margin: 0 0 25px;
                line-height: 1.6;
            }
            
            .wdb-method-btn {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 14px 28px;
                background: #f1f5f9;
                border: none;
                border-radius: 50px;
                color: #475569;
                font-size: 0.9rem;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            
            .wdb-method-card:hover .wdb-method-btn {
                background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);
                color: white;
                box-shadow: 0 10px 30px <?php echo $this->hex_to_rgba($primary_color, 0.3); ?>;
            }
            
            /* Modal */
            .wdb-modal {
                display: none;
                position: fixed;
                inset: 0;
                z-index: 9999;
                align-items: center;
                justify-content: center;
                padding: 20px;
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(5px);
            }
            
            .wdb-modal.active {
                display: flex;
            }
            
            .wdb-modal-content {
                background: white;
                border-radius: 24px;
                max-width: 500px;
                width: 100%;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 25px 60px rgba(0,0,0,0.2);
            }
            
            .wdb-modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 20px 25px;
                border-bottom: 1px solid #e2e8f0;
            }
            
            .wdb-modal-header h3 {
                font-size: 1.25rem;
                font-weight: 700;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 12px;
            }
            
            .wdb-modal-close {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                border: none;
                background: #f3f4f6;
                cursor: pointer;
                font-size: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.3s ease;
            }
            
            .wdb-modal-close:hover {
                background: #fee2e2;
                color: #dc2626;
            }
            
            .wdb-modal-body {
                padding: 25px;
            }
            
            .wdb-modal-footer {
                padding: 20px 25px;
                border-top: 1px solid #e5e7eb;
                background: #f9fafb;
                border-radius: 0 0 20px 20px;
            }
            
            .wdb-btn-primary {
                width: 100%;
                padding: 14px 24px;
                border: none;
                border-radius: 12px;
                background: linear-gradient(135deg, var(--wdb-primary), var(--wdb-secondary));
                color: white;
                font-size: 1rem;
                font-weight: 700;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                transition: opacity 0.3s ease;
            }
            
            .wdb-btn-primary:hover {
                opacity: 0.9;
            }
            
            /* Form */
            .wdb-form-section {
                background: white;
                border-radius: 20px;
                padding: 30px;
                margin-bottom: 50px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            }
            
            .wdb-form-title {
                font-size: 1.5rem;
                font-weight: 700;
                color: #1f2937;
                margin: 0 0 20px 0;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .wdb-form-group {
                margin-bottom: 20px;
            }
            
            .wdb-form-group label {
                display: block;
                font-weight: 600;
                color: #374151;
                margin-bottom: 6px;
            }
            
            .wdb-form-group input,
            .wdb-form-group textarea {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e5e7eb;
                border-radius: 10px;
                font-size: 1rem;
                transition: border-color 0.3s ease;
            }
            
            .wdb-form-group input:focus,
            .wdb-form-group textarea:focus {
                outline: none;
                border-color: var(--wdb-primary);
            }
            
            .wdb-checkbox-group {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
            }
            
            .wdb-checkbox-group label {
                display: flex;
                align-items: center;
                gap: 8px;
                cursor: pointer;
            }
            
            @media (max-width: 768px) {
                .wdb-hero { padding: 40px 0 60px; }
                .wdb-hero-stats { gap: 30px; }
                .wdb-methods-grid { grid-template-columns: 1fr; }
            }
        </style>
        
        <div id="wdb-donation-wrapper">
            <!-- Efeitos de fundo -->
            <div class="wdb-stars"></div>
            <div class="wdb-glow-1"></div>
            <div class="wdb-glow-2"></div>
            
            <div class="wdb-container">
            
                <!-- Hero Section -->
                <header class="wdb-hero">
                    <div class="wdb-hero-badge">
                        <i class="fas fa-heart"></i>
                        <?php echo esc_html($settings['page_highlight'] ?? __('Transforme vidas com sua doação', 'wp-donate-brasil')); ?>
                    </div>
                    
                    <h1 class="wdb-hero-title"><?php echo esc_html($settings['page_title'] ?? __('Faça uma Doação', 'wp-donate-brasil')); ?></h1>
                    
                    <?php if (!empty($settings['page_subtitle'])): ?>
                    <p class="wdb-hero-subtitle"><?php echo esc_html($settings['page_subtitle']); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['page_description'])): ?>
                    <p class="wdb-hero-desc"><?php echo esc_html($settings['page_description']); ?></p>
                    <?php endif; ?>
                </header>
                
                <!-- Métodos de Pagamento -->
                <section>
                    <div class="wdb-section-title">
                        <h2><i class="fas fa-hand-holding-usd"></i> <?php _e('Escolha como doar', 'wp-donate-brasil'); ?></h2>
                        <p><?php _e('Selecione o método de pagamento que preferir', 'wp-donate-brasil'); ?></p>
                    </div>
                    
                    <div class="wdb-methods-grid">
                        <?php 
                        $icons = array(
                            'pix' => 'fas fa-qrcode',
                            'bank_transfer' => 'fas fa-university',
                            'bitcoin' => 'fab fa-bitcoin',
                            'paypal' => 'fab fa-paypal',
                            'payment_link' => 'fas fa-link'
                        );
                        $descs = array(
                            'pix' => __('Transferência instantânea via QR Code', 'wp-donate-brasil'),
                            'bank_transfer' => __('Depósito ou transferência bancária', 'wp-donate-brasil'),
                            'bitcoin' => __('Doação em criptomoeda', 'wp-donate-brasil'),
                            'paypal' => __('Pagamento internacional seguro', 'wp-donate-brasil'),
                            'payment_link' => __('Link direto para pagamento', 'wp-donate-brasil')
                        );
                        foreach ($methods as $method): 
                            if (empty($method['enabled'])) continue;
                            $icon_class = $method['id'];
                            if ($method['id'] === 'bank_transfer') $icon_class = 'bank';
                            if ($method['id'] === 'payment_link') $icon_class = 'link';
                        ?>
                        <div class="wdb-method-card" onclick="wdbOpenModal('<?php echo esc_attr($method['id']); ?>')">
                            <div class="wdb-method-icon <?php echo esc_attr($icon_class); ?>">
                                <i class="<?php echo $icons[$method['id']] ?? 'fas fa-hand-holding-heart'; ?>"></i>
                            </div>
                            <h3 class="wdb-method-title"><?php echo esc_html($method['name']); ?></h3>
                            <p class="wdb-method-desc"><?php echo $descs[$method['id']] ?? esc_html($method['instructions'] ?? ''); ?></p>
                            <span class="wdb-method-btn">
                                <?php _e('Doar agora', 'wp-donate-brasil'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            
                <!-- Modal de Detalhes -->
                <div id="wdb-method-modal" class="wdb-modal">
                    <div class="wdb-modal-content">
                        <div class="wdb-modal-header">
                            <h3 id="wdb-modal-title">
                                <span id="wdb-modal-icon-container"></span>
                                <span id="wdb-modal-title-text"><?php _e('Método', 'wp-donate-brasil'); ?></span>
                            </h3>
                            <button type="button" id="wdb-modal-close" class="wdb-modal-close">&times;</button>
                        </div>
                        <div id="wdb-modal-content" class="wdb-modal-body"></div>
                        <div class="wdb-modal-footer">
                            <button type="button" id="wdb-modal-send-receipt" class="wdb-btn-primary">
                                <i class="fas fa-upload"></i>
                                <?php _e('Enviar Comprovante', 'wp-donate-brasil'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Dados dos métodos para JavaScript -->
                <script type="application/json" id="wdb-methods-data">
                <?php 
                $methods_json = array();
                foreach ($methods as $method) {
                    if (!empty($method['enabled'])) {
                        $methods_json[$method['id']] = $method;
                    }
                }
                echo json_encode($methods_json);
                ?>
                </script>
                
                <!-- JavaScript inline para modal -->
                <script>
                var wdbMethodsData = <?php echo json_encode($methods_json); ?>;
                
                function wdbOpenModal(methodId) {
                    var method = wdbMethodsData[methodId];
                    if (!method) return;
                    
                    var modal = document.getElementById('wdb-method-modal');
                    var titleText = document.getElementById('wdb-modal-title-text');
                    var content = document.getElementById('wdb-modal-content');
                    var sendBtn = document.getElementById('wdb-modal-send-receipt');
                    
                    titleText.textContent = method.name;
                    sendBtn.setAttribute('data-method', methodId);
                    
                    // Renderiza conteúdo baseado no método
                    var html = '';
                    if (method.instructions) {
                        html += '<p style="color:#4b5563;margin-bottom:15px;">' + method.instructions + '</p>';
                    }
                    
                    switch(methodId) {
                        case 'pix':
                            html += wdbRenderPix(method);
                            break;
                        case 'bank_transfer':
                            html += wdbRenderBank(method);
                            break;
                        case 'bitcoin':
                            html += wdbRenderBitcoin(method);
                            break;
                        case 'paypal':
                            html += wdbRenderPaypal(method);
                            break;
                        case 'payment_link':
                            html += wdbRenderPaymentLink(method);
                            break;
                        case 'wise':
                            html += wdbRenderWise(method);
                            break;
                    }
                    
                    content.innerHTML = html;
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    
                    // Carrega QR Code PIX via AJAX se for PIX
                    if (methodId === 'pix' && method.pix_key) {
                        wdbLoadPixQR(method);
                    }
                }
                
                function wdbCloseModal() {
                    var modal = document.getElementById('wdb-method-modal');
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
                
                function wdbRenderPix(method) {
                    if (!method.pix_key) return '<p style="color:red;">Configure a chave PIX.</p>';
                    var html = '<div style="text-align:center;">';
                    html += '<div id="wdb-pix-qr" style="display:inline-block;padding:15px;background:#fff;border-radius:12px;border:2px solid #10b981;margin-bottom:15px;">';
                    html += '<div id="wdb-pix-loading" style="width:180px;height:180px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:15px;">';
                    html += '<div style="width:50px;height:50px;border:4px solid #e5e7eb;border-top-color:#10b981;border-radius:50%;animation:wdb-spin 1s linear infinite;"></div>';
                    html += '<span style="font-size:13px;color:#6b7280;">Gerando QR Code...</span>';
                    html += '</div>';
                    html += '<style>@keyframes wdb-spin { to { transform: rotate(360deg); } }</style>';
                    html += '</div></div>';
                    html += '<div style="margin-top:15px;">';
                    html += '<label style="display:block;font-weight:600;margin-bottom:5px;"><i class="fas fa-key"></i> Chave PIX</label>';
                    html += '<div style="display:flex;gap:8px;">';
                    html += '<input type="text" readonly value="' + method.pix_key + '" style="flex:1;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-family:monospace;">';
                    html += '<button type="button" onclick="wdbCopy(\'' + method.pix_key + '\', this)" style="padding:10px 15px;background:#10b981;color:white;border:none;border-radius:8px;cursor:pointer;transition:all 0.3s;"><i class="fas fa-copy"></i></button>';
                    html += '</div></div>';
                    if (method.pix_name) {
                        html += '<p style="margin-top:10px;font-size:14px;"><strong>Titular:</strong> ' + method.pix_name + '</p>';
                    }
                    return html;
                }
                
                function wdbRenderBank(method) {
                    var html = '<div style="background:#f9fafb;padding:15px;border-radius:10px;">';
                    if (method.bank_name) html += '<p><strong>Banco:</strong> ' + method.bank_name + '</p>';
                    if (method.bank_agency) html += '<p><strong>Agência:</strong> ' + method.bank_agency + '</p>';
                    if (method.bank_account) html += '<p><strong>Conta:</strong> ' + method.bank_account + '</p>';
                    if (method.bank_holder) html += '<p><strong>Titular:</strong> ' + method.bank_holder + '</p>';
                    if (method.bank_cpf_cnpj) html += '<p><strong>CPF/CNPJ:</strong> ' + method.bank_cpf_cnpj + '</p>';
                    html += '</div>';
                    return html;
                }
                
                function wdbRenderBitcoin(method) {
                    if (!method.btc_address) return '<p style="color:red;">Configure o endereço Bitcoin.</p>';
                    var network = method.btc_network || 'Bitcoin (BTC)';
                    var html = '<div style="text-align:center;margin-bottom:20px;">';
                    html += '<i class="fab fa-bitcoin" style="font-size:80px;color:#f7931a;"></i>';
                    html += '</div>';
                    html += '<div style="margin-bottom:15px;">';
                    html += '<label style="display:block;font-weight:600;margin-bottom:5px;"><i class="fa-solid fa-network-wired"></i> Rede</label>';
                    html += '<div style="padding:10px;background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;color:#92400e;font-weight:600;text-align:center;">';
                    html += '<i class="fa-solid fa-exclamation-triangle" style="margin-right:5px;"></i>' + network;
                    html += '</div></div>';
                    html += '<div>';
                    html += '<label style="display:block;font-weight:600;margin-bottom:5px;"><i class="fa-solid fa-wallet"></i> Endereço Bitcoin</label>';
                    html += '<div style="display:flex;gap:8px;">';
                    html += '<input type="text" readonly value="' + method.btc_address + '" style="flex:1;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-family:monospace;font-size:11px;">';
                    html += '<button type="button" onclick="wdbCopy(\'' + method.btc_address + '\', this)" style="padding:10px 15px;background:#f7931a;color:white;border:none;border-radius:8px;cursor:pointer;transition:all 0.3s;"><i class="fas fa-copy"></i></button>';
                    html += '</div></div>';
                    return html;
                }
                
                function wdbRenderPaypal(method) {
                    if (!method.paypal_email) return '<p style="color:red;">Configure o email PayPal.</p>';
                    var html = '<div style="text-align:center;margin-bottom:15px;">';
                    html += '<i class="fab fa-paypal" style="font-size:60px;color:#003087;"></i>';
                    html += '</div>';
                    html += '<div style="display:flex;gap:8px;">';
                    html += '<input type="text" readonly value="' + method.paypal_email + '" style="flex:1;padding:10px;border:1px solid #e5e7eb;border-radius:8px;">';
                    html += '<button type="button" onclick="wdbCopy(\'' + method.paypal_email + '\', this)" style="padding:10px 15px;background:#003087;color:white;border:none;border-radius:8px;cursor:pointer;transition:all 0.3s;"><i class="fas fa-copy"></i></button>';
                    html += '</div>';
                    return html;
                }
                
                function wdbRenderPaymentLink(method) {
                    if (!method.gateway_url) return '<p style="color:red;">Configure o link de pagamento.</p>';
                    var gatewayName = method.gateway_name || 'Gateway de Pagamento';
                    var html = '<div style="text-align:center;">';
                    if (method.gateway_logo) {
                        html += '<div style="margin-bottom:25px; display:flex; justify-content:center;"><img src="' + method.gateway_logo + '" alt="' + gatewayName + '" style="height:120px; max-width:200px; object-fit:contain;"></div>';
                    }
                    html += '<a href="' + method.gateway_url + '" target="_blank" style="display:inline-flex;align-items:center;gap:10px;padding:15px 30px;background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:white;border-radius:30px;text-decoration:none;font-weight:600;">';
                    html += '<i class="fas fa-external-link-alt"></i> Doar via ' + gatewayName + '</a>';
                    html += '<p style="font-size:12px;color:#666;margin-top:12px;"><i class="fas fa-shield-alt"></i> Você será redirecionado para o site seguro</p>';
                    html += '</div>';
                    return html;
                }
                
                function wdbRenderWise(method) {
                    if (!method.wise_tag) return '<p style="color:red;">Configure o WiseTag.</p>';
                    var wiseUrl = 'https://wise.com/pay/me/' + method.wise_tag;
                    var wiseLogoUrl = '<?php echo WDB_PLUGIN_URL; ?>assets/images/wiselogo.png';
                    var html = '<div style="text-align:center;margin-bottom:25px;">';
                    html += '<img src="' + wiseLogoUrl + '" alt="Wise" style="height:40px;margin-bottom:15px;">';
                    html += '<div id="wdb-wise-qr" style="display:flex;justify-content:center;margin-bottom:15px;"></div>';
                    html += '<div style="display:inline-flex;align-items:center;gap:10px;background:#9fe870;padding:12px 20px;border-radius:12px;">';
                    html += '<span style="font-size:22px;font-weight:800;color:#163300;">@' + method.wise_tag + '</span>';
                    html += '<button type="button" onclick="wdbCopy(\'@' + method.wise_tag + '\', this)" style="padding:8px 12px;background:#163300;color:#9fe870;border:none;border-radius:8px;cursor:pointer;transition:all 0.3s;font-weight:600;"><i class="fas fa-copy"></i></button>';
                    html += '</div>';
                    html += '</div>';
                    html += '<div style="text-align:center;">';
                    html += '<a href="' + wiseUrl + '" target="_blank" style="display:inline-flex;align-items:center;gap:10px;padding:14px 30px;background:#163300;color:#9fe870;border-radius:30px;text-decoration:none;font-weight:600;font-size:16px;">';
                    html += '<i class="fas fa-external-link-alt"></i> Abrir no Wise</a>';
                    html += '</div>';
                    setTimeout(function() { wdbLoadWiseQR(method); }, 100);
                    return html;
                }
                
                function wdbLoadWiseQR(method) {
                    var container = document.getElementById('wdb-wise-qr');
                    if (!container || !method.wise_tag) return;
                    var wiseUrl = 'https://wise.com/pay/me/' + method.wise_tag;
                    container.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(wiseUrl) + '" alt="QR Code Wise" style="width:180px;height:180px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.1);">';
                }
                
                function wdbLoadPixQR(method) {
                    var container = document.getElementById('wdb-pix-qr');
                    if (!container) return;
                    
                    // Mostra preloader
                    container.innerHTML = '<div style="width:180px;height:180px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:15px;">' +
                        '<div style="width:50px;height:50px;border:4px solid #e5e7eb;border-top-color:#10b981;border-radius:50%;animation:wdb-spin 1s linear infinite;"></div>' +
                        '<span style="font-size:13px;color:#6b7280;">Gerando QR Code...</span>' +
                        '</div>';
                    
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success && response.data.qr_url) {
                                    // Preload da imagem antes de exibir
                                    var img = new Image();
                                    img.onload = function() {
                                        container.innerHTML = '<img src="' + response.data.qr_url + '" alt="QR Code PIX" style="width:180px;height:180px;">';
                                    };
                                    img.onerror = function() {
                                        container.innerHTML = '<p style="color:red;padding:20px;">Erro ao carregar QR Code</p>';
                                    };
                                    img.src = response.data.qr_url;
                                } else {
                                    container.innerHTML = '<p style="color:red;padding:20px;">Erro ao gerar QR Code</p>';
                                }
                            } catch(e) {
                                console.error('Erro ao carregar QR PIX', e);
                                container.innerHTML = '<p style="color:red;padding:20px;">Erro ao carregar</p>';
                            }
                        }
                    };
                    xhr.onerror = function() {
                        container.innerHTML = '<p style="color:red;padding:20px;">Erro de conexão</p>';
                    };
                    xhr.send('action=wdb_get_pix_payload&nonce=<?php echo wp_create_nonce('wdb_nonce_action'); ?>&pix_key=' + encodeURIComponent(method.pix_key) + '&pix_name=' + encodeURIComponent(method.pix_name) + '&pix_city=' + encodeURIComponent(method.pix_city || 'SAO PAULO'));
                }
                
                function wdbCopy(text, btn) {
                    // Tenta usar clipboard API moderna
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(function() {
                            wdbShowCopied(btn);
                        }).catch(function() {
                            wdbFallbackCopy(text, btn);
                        });
                    } else {
                        wdbFallbackCopy(text, btn);
                    }
                }
                
                // Fallback para copiar em browsers antigos
                function wdbFallbackCopy(text, btn) {
                    var textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-9999px';
                    textArea.style.top = '0';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        wdbShowCopied(btn);
                    } catch (err) {
                        prompt('Copie manualmente:', text);
                    }
                    document.body.removeChild(textArea);
                }
                
                // Mostra animação de copiado no botão
                function wdbShowCopied(btn) {
                    if (!btn) return;
                    var originalHTML = btn.innerHTML;
                    var originalBg = btn.style.background;
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    btn.style.background = '#10b981';
                    btn.style.transform = 'scale(1.1)';
                    setTimeout(function() {
                        btn.innerHTML = originalHTML;
                        btn.style.background = originalBg;
                        btn.style.transform = 'scale(1)';
                    }, 1500);
                }
                
                // Fecha modal ao clicar fora ou no X
                document.getElementById('wdb-modal-close').onclick = wdbCloseModal;
                document.getElementById('wdb-method-modal').onclick = function(e) {
                    if (e.target === this) wdbCloseModal();
                };
                
                // Botão enviar comprovante - abre offcanvas
                document.getElementById('wdb-modal-send-receipt').onclick = function() {
                    var method = this.getAttribute('data-method');
                    wdbCloseModal();
                    document.getElementById('wdb-donation-method').value = method;
                    wdbOpenReceiptForm();
                };
                
                // Abre offcanvas do formulário
                function wdbOpenReceiptForm() {
                    document.getElementById('wdb-receipt-overlay').classList.add('active');
                    document.getElementById('wdb-receipt-offcanvas').classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
                
                // Fecha offcanvas do formulário
                function wdbCloseReceiptForm() {
                    document.getElementById('wdb-receipt-overlay').classList.remove('active');
                    document.getElementById('wdb-receipt-offcanvas').classList.remove('active');
                    document.body.style.overflow = '';
                }
                
                </script>
                
                <!-- Overlay do Offcanvas -->
                <div id="wdb-receipt-overlay" class="wdb-receipt-overlay" onclick="wdbCloseReceiptForm()"></div>
                
                <!-- Offcanvas Formulário de Comprovante -->
                <div id="wdb-receipt-offcanvas" class="wdb-receipt-offcanvas">
                    <div class="wdb-offcanvas-header" style="background: linear-gradient(135deg, var(--wdb-primary), var(--wdb-secondary));">
                        <h2>
                            <i class="fas fa-upload"></i>
                            <?php _e('Enviar Comprovante', 'wp-donate-brasil'); ?>
                        </h2>
                        <button type="button" class="wdb-offcanvas-close" onclick="wdbCloseReceiptForm()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="wdb-offcanvas-body">
                    
                    <form id="wdb-receipt-form" enctype="multipart/form-data">
                        <input type="hidden" name="donation_method" id="wdb-donation-method" value="">
                        <input type="hidden" name="anonymous" id="wdb-anonymous" value="0">
                        
                        <!-- Switch Doação Anônima -->
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding: 15px; background: #f8fafc; border-radius: 10px;">
                            <label class="wdb-switch">
                                <input type="checkbox" id="wdb-anonymous-switch" onchange="wdbToggleAnonymous(this.checked)">
                                <span class="wdb-switch-slider"></span>
                            </label>
                            <div>
                                <span style="font-weight: 600; color: #374151;"><?php _e('Doação Anônima', 'wp-donate-brasil'); ?></span>
                                <p style="font-size: 12px; color: #6b7280; margin: 0;"><?php _e('Seus dados não serão exibidos publicamente', 'wp-donate-brasil'); ?></p>
                            </div>
                        </div>
                        
                        <!-- Campos de identificação (ocultos quando anônimo) -->
                        <div id="wdb-donor-fields">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                <div class="wdb-form-group">
                                    <label><?php _e('Seu Nome', 'wp-donate-brasil'); ?> *</label>
                                    <input type="text" id="wdb-donor-name" name="donor_name" required placeholder="<?php esc_attr_e('Nome completo', 'wp-donate-brasil'); ?>">
                                </div>
                                <div class="wdb-form-group">
                                    <label><?php _e('Seu E-mail', 'wp-donate-brasil'); ?> *</label>
                                    <input type="email" id="wdb-donor-email" name="donor_email" required placeholder="<?php esc_attr_e('seu@email.com', 'wp-donate-brasil'); ?>">
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                <div class="wdb-form-group">
                                    <label><?php _e('Telefone (opcional)', 'wp-donate-brasil'); ?></label>
                                    <input type="tel" id="wdb-donor-phone" name="donor_phone" placeholder="<?php esc_attr_e('(00) 00000-0000', 'wp-donate-brasil'); ?>">
                                </div>
                                <div class="wdb-form-group">
                                    <label><?php _e('Valor em Reais - R$ (opcional)', 'wp-donate-brasil'); ?></label>
                                    <div style="position: relative;">
                                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600;">R$</span>
                                        <input type="text" id="wdb-donation-amount" name="donation_amount" class="wdb-money-input" placeholder="0,00" style="padding-left: 40px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Valor quando anônimo -->
                        <div id="wdb-anonymous-value-field" style="display: none; margin-bottom: 15px;">
                            <div class="wdb-form-group">
                                <label><?php _e('Valor em Reais - R$ (opcional)', 'wp-donate-brasil'); ?></label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600;">R$</span>
                                    <input type="text" id="wdb-donation-amount-anon" class="wdb-money-input" placeholder="0,00" style="padding-left: 40px;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="wdb-form-group">
                            <label><?php _e('Comprovante', 'wp-donate-brasil'); ?> *</label>
                            <input type="file" id="wdb-receipt-file" name="receipt_file" required accept=".jpg,.jpeg,.png,.gif,.pdf">
                            <small style="color: #6b7280;"><?php _e('JPG, PNG, GIF ou PDF. Máx 5MB.', 'wp-donate-brasil'); ?></small>
                        </div>
                        
                        <div class="wdb-form-group">
                            <label><?php _e('Mensagem (opcional)', 'wp-donate-brasil'); ?></label>
                            <textarea id="wdb-message" name="message" rows="3" placeholder="<?php esc_attr_e('Deixe uma mensagem...', 'wp-donate-brasil'); ?>"></textarea>
                        </div>
                        
                        <div id="wdb-gallery-option" class="wdb-checkbox-group" style="margin-bottom: 20px;">
                            <label><input type="checkbox" name="show_in_gallery" id="wdb-show-gallery" checked> <?php _e('Exibir na galeria de doadores', 'wp-donate-brasil'); ?></label>
                        </div>
                        
                        <button type="submit" id="wdb-submit-btn" class="wdb-btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            <?php _e('Enviar Comprovante', 'wp-donate-brasil'); ?>
                        </button>
                    </form>
                    
                    <div id="wdb-form-message" style="margin-top: 15px; display: none;"></div>
                    </div>
                </div>
                
                <!-- CSS do Offcanvas e Switch -->
                <style>
                    /* Offcanvas */
                    .wdb-receipt-overlay {
                        position: fixed;
                        inset: 0;
                        background: rgba(0,0,0,0.5);
                        z-index: 99998;
                        opacity: 0;
                        visibility: hidden;
                        transition: all 0.3s ease;
                    }
                    .wdb-receipt-overlay.active { opacity: 1; visibility: visible; }
                    
                    .wdb-receipt-offcanvas {
                        position: fixed;
                        right: -50%;
                        top: 0;
                        bottom: 0;
                        width: 50%;
                        max-width: 600px;
                        background: #fff;
                        z-index: 99999;
                        box-shadow: -5px 0 30px rgba(0,0,0,0.15);
                        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                        display: flex;
                        flex-direction: column;
                    }
                    .wdb-receipt-offcanvas.active { right: 0; }
                    
                    .wdb-offcanvas-header {
                        padding: 20px 25px;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        flex-shrink: 0;
                    }
                    .wdb-offcanvas-header h2 {
                        margin: 0;
                        font-size: 1.25rem;
                        font-weight: 700;
                        color: white;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .wdb-offcanvas-close {
                        background: rgba(255,255,255,0.2);
                        border: none;
                        color: white;
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        cursor: pointer;
                        font-size: 18px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: all 0.2s;
                    }
                    .wdb-offcanvas-close:hover { background: rgba(255,255,255,0.3); }
                    
                    .wdb-offcanvas-body {
                        padding: 25px;
                        overflow-y: auto;
                        flex: 1;
                    }
                    
                    /* Mobile responsivo */
                    @media (max-width: 768px) {
                        .wdb-receipt-offcanvas {
                            width: 100%;
                            max-width: 100%;
                            right: -100%;
                        }
                    }
                    
                    /* Switch */
                    .wdb-switch { position: relative; display: inline-block; width: 50px; height: 26px; }
                    .wdb-switch input { opacity: 0; width: 0; height: 0; }
                    .wdb-switch-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; border-radius: 26px; transition: 0.3s; }
                    .wdb-switch-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
                    .wdb-switch input:checked + .wdb-switch-slider { background: linear-gradient(135deg, var(--wdb-primary), var(--wdb-secondary)); }
                    .wdb-switch input:checked + .wdb-switch-slider:before { transform: translateX(24px); }
                </style>
                
                <!-- Máscara de Dinheiro Brasileiro -->
                <script>
                (function() {
                    function formatMoney(value) {
                        value = value.replace(/\D/g, '');
                        if (value === '') return '';
                        var num = parseInt(value, 10);
                        var formatted = (num / 100).toFixed(2);
                        var parts = formatted.split('.');
                        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        return parts.join(',');
                    }
                    
                    function applyMoneyMask(input) {
                        input.addEventListener('input', function(e) {
                            var cursorPos = e.target.selectionStart;
                            var oldLen = e.target.value.length;
                            e.target.value = formatMoney(e.target.value);
                            var newLen = e.target.value.length;
                            var newPos = cursorPos + (newLen - oldLen);
                            if (newPos < 0) newPos = 0;
                            e.target.setSelectionRange(newPos, newPos);
                        });
                        input.addEventListener('blur', function(e) {
                            if (e.target.value === '0,00') e.target.value = '';
                        });
                    }
                    
                    document.querySelectorAll('.wdb-money-input').forEach(applyMoneyMask);
                })();
                </script>
                
                <!-- JS do Switch Anônimo -->
                <script>
                function wdbToggleAnonymous(isAnonymous) {
                    var donorFields = document.getElementById('wdb-donor-fields');
                    var anonValueField = document.getElementById('wdb-anonymous-value-field');
                    var galleryOption = document.getElementById('wdb-gallery-option');
                    var showGallery = document.getElementById('wdb-show-gallery');
                    var anonymousInput = document.getElementById('wdb-anonymous');
                    var nameInput = document.getElementById('wdb-donor-name');
                    var emailInput = document.getElementById('wdb-donor-email');
                    var phoneInput = document.getElementById('wdb-donor-phone');
                    var amountInput = document.getElementById('wdb-donation-amount');
                    var amountAnonInput = document.getElementById('wdb-donation-amount-anon');
                    
                    if (isAnonymous) {
                        // Oculta campos de identificação
                        donorFields.style.display = 'none';
                        anonValueField.style.display = 'block';
                        galleryOption.style.display = 'none';
                        
                        // Preenche valores padrão para anônimo
                        nameInput.value = 'Anônimo';
                        nameInput.removeAttribute('required');
                        emailInput.value = 'anonimo@anonimo.com';
                        emailInput.removeAttribute('required');
                        phoneInput.value = '000';
                        anonymousInput.value = '1';
                        showGallery.checked = true; // Anônimos aparecem na lista, não no carrossel
                    } else {
                        // Mostra campos de identificação
                        donorFields.style.display = 'block';
                        anonValueField.style.display = 'none';
                        galleryOption.style.display = 'block';
                        
                        // Limpa campos
                        nameInput.value = '';
                        nameInput.setAttribute('required', 'required');
                        emailInput.value = '';
                        emailInput.setAttribute('required', 'required');
                        phoneInput.value = '';
                        anonymousInput.value = '0';
                    }
                }
                
                // Sincroniza valor entre campos
                document.getElementById('wdb-donation-amount-anon').addEventListener('input', function() {
                    document.getElementById('wdb-donation-amount').value = this.value;
                });
                </script>
                
                <?php if (!empty($settings['show_gallery'])): ?>
                    <?php echo $this->render_donors_gallery(array()); ?>
                <?php endif; ?>
                
                <?php if ($settings['show_credits'] ?? true): ?>
                <!-- Crédito do desenvolvedor -->
                <div class="text-center mt-16 pb-6">
                    <a href="https://dantetesta.com.br" target="_blank" rel="noopener" style="font-size: 11px; color: #9ca3af; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#6b7280'" onmouseout="this.style.color='#9ca3af'">
                        Desenvolvido por Dante Testa
                    </a>
                </div>
                <?php endif; ?>
            
            </div><!-- .wdb-container -->
        </div><!-- #wdb-donation-wrapper -->
        <?php
        return ob_get_clean();
    }
    
    // Card simples de método de pagamento
    private function render_method_card_simple($method) {
        $method_id = esc_attr($method['id']);
        $method_name = esc_html($method['name']);
        
        // Ícones por método
        $icons = array(
            'pix' => 'fas fa-qrcode',
            'bank_transfer' => 'fas fa-university',
            'bitcoin' => 'fab fa-bitcoin',
            'payment_link' => 'fas fa-link',
            'paypal' => 'fab fa-paypal',
        );
        $icon = $icons[$method['id']] ?? 'fas fa-hand-holding-heart';
        
        // Classe de cor do ícone
        $icon_class = $method['id'];
        if ($method['id'] === 'bank_transfer') $icon_class = 'bank';
        if ($method['id'] === 'payment_link') $icon_class = 'link';
        ?>
        <div class="wdb-card" data-method="<?php echo $method_id; ?>" onclick="wdbOpenModal('<?php echo $method_id; ?>')">
            <div class="wdb-card-icon <?php echo $icon_class; ?>">
                <i class="<?php echo $icon; ?>"></i>
            </div>
            <h3 class="wdb-card-title"><?php echo $method_name; ?></h3>
            <p class="wdb-card-desc"><?php _e('Clique para ver detalhes', 'wp-donate-brasil'); ?></p>
            <span class="wdb-card-btn">
                <?php _e('Ver opções', 'wp-donate-brasil'); ?>
                <i class="fas fa-chevron-right"></i>
            </span>
        </div>
        <?php
    }
    
    private function render_method_card($method) {
        $this->render_method_card_simple($method);
    }
    
    private function render_method_details($method) {
        $this->render_method_details_public($method);
    }
    
    // Método público para renderizar detalhes (usado pelo template fullpage)
    public function render_method_details_public($method) {
        switch ($method['id']) {
            case 'pix':
                $this->render_pix_details($method);
                break;
            case 'bank_transfer':
                $this->render_bank_details($method);
                break;
            case 'bitcoin':
                $this->render_bitcoin_details($method);
                break;
            case 'payment_link':
                $this->render_payment_link_details($method);
                break;
            case 'paypal':
                $this->render_paypal_details($method);
                break;
        }
    }
    
    private function render_pix_details($method) {
        if (empty($method['pix_key']) || empty($method['pix_name'])) return;
        
        $pix_city = !empty($method['pix_city']) ? $method['pix_city'] : 'SAO PAULO';
        $pix_description = $method['pix_description'] ?? '';
        ?>
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-200">
            <!-- QR Code PIX -->
            <?php echo WDB_Pix_QRCode::render_pix_qrcode(
                $method['pix_key'],
                $method['pix_name'],
                $pix_city,
                '',
                $pix_description
            ); ?>
            
            <!-- Informações adicionais -->
            <div class="mt-4 pt-4 border-t border-green-200 space-y-2 text-sm text-left">
                <?php if (!empty($method['pix_name'])): ?>
                <p><span class="font-medium text-gray-600"><i class="fa-solid fa-user mr-1"></i><?php _e('Titular:', 'wp-donate-brasil'); ?></span> <?php echo esc_html($method['pix_name']); ?></p>
                <?php endif; ?>
                <?php if (!empty($method['pix_bank'])): ?>
                <p><span class="font-medium text-gray-600"><i class="fa-solid fa-building-columns mr-1"></i><?php _e('Banco:', 'wp-donate-brasil'); ?></span> <?php echo esc_html($method['pix_bank']); ?></p>
                <?php endif; ?>
                
                <!-- Chave PIX com cópia -->
                <div class="mt-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        <i class="fa-solid fa-key mr-1"></i><?php _e('Chave PIX:', 'wp-donate-brasil'); ?>
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="<?php echo esc_attr($method['pix_key']); ?>"
                               class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm font-mono">
                        <button type="button" class="wdb-copy-btn px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all flex items-center gap-2"
                                data-copy="<?php echo esc_attr($method['pix_key']); ?>">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    // Renderiza detalhes do Bitcoin
    private function render_bitcoin_details($method) {
        if (empty($method['btc_address'])) return;
        $network = $method['btc_network'] ?? 'Bitcoin';
        ?>
        <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl p-4 border border-orange-200">
            <div class="text-center mb-4">
                <div class="inline-block p-4 bg-white rounded-xl shadow-md">
                    <i class="fa-brands fa-bitcoin text-7xl text-orange-500"></i>
                </div>
                <p class="text-xs text-orange-600 mt-2 font-medium">
                    <i class="fa-brands fa-bitcoin mr-1"></i>
                    <?php echo esc_html($network); ?>
                </p>
            </div>
            
            <div class="text-left">
                <label class="block text-xs font-medium text-gray-500 mb-1">
                    <i class="fa-solid fa-wallet mr-1"></i><?php _e('Endereço:', 'wp-donate-brasil'); ?>
                </label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="<?php echo esc_attr($method['btc_address']); ?>"
                           class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs font-mono truncate">
                    <button type="button" class="wdb-copy-btn px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-all"
                            data-copy="<?php echo esc_attr($method['btc_address']); ?>">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    // Renderiza Link de Pagamento
    private function render_payment_link_details($method) {
        if (empty($method['gateway_url'])) return;
        $gateway_name = $method['gateway_name'] ?? __('Gateway de Pagamento', 'wp-donate-brasil');
        $gateway_logo = $method['gateway_logo'] ?? '';
        ?>
        <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-4 border border-purple-200 text-center">
            <?php if (!empty($gateway_logo)): ?>
            <div class="mb-4">
                <img src="<?php echo esc_url($gateway_logo); ?>" alt="<?php echo esc_attr($gateway_name); ?>" 
                     class="h-12 mx-auto object-contain" loading="lazy">
            </div>
            <?php endif; ?>
            
            <a href="<?php echo esc_url($method['gateway_url']); ?>" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-3 w-full py-4 px-6 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                <i class="fa-solid fa-external-link-alt text-lg"></i>
                <span><?php printf(__('Doar via %s', 'wp-donate-brasil'), esc_html($gateway_name)); ?></span>
            </a>
            
            <p class="text-xs text-gray-500 mt-3">
                <i class="fa-solid fa-shield-alt mr-1"></i>
                <?php _e('Você será redirecionado para o site seguro do gateway', 'wp-donate-brasil'); ?>
            </p>
        </div>
        <?php
    }
    
    private function render_bank_details($method) {
        if (empty($method['bank_name'])) return;
        ?>
        <div class="bg-gray-50 rounded-lg p-4 text-left">
            <div class="space-y-2 text-sm">
                <p><span class="font-medium text-gray-600"><?php _e('Banco:', 'wp-donate-brasil'); ?></span> <?php echo esc_html($method['bank_name']); ?></p>
                <?php if (!empty($method['bank_agency'])): ?>
                <p><span class="font-medium text-gray-600"><?php _e('Agência:', 'wp-donate-brasil'); ?></span> <?php echo esc_html($method['bank_agency']); ?></p>
                <?php endif; ?>
                <?php if (!empty($method['bank_account'])): ?>
                <p><span class="font-medium text-gray-600"><?php _e('Conta:', 'wp-donate-brasil'); ?></span> <?php echo esc_html($method['bank_account']); ?></p>
                <?php endif; ?>
                <?php if (!empty($method['bank_holder'])): ?>
                <p><span class="font-medium text-gray-600"><?php _e('Titular:', 'wp-donate-brasil'); ?></span> <?php echo esc_html($method['bank_holder']); ?></p>
                <?php endif; ?>
                <?php if (!empty($method['bank_cpf_cnpj'])): ?>
                <p><span class="font-medium text-gray-600"><?php _e('CPF/CNPJ:', 'wp-donate-brasil'); ?></span> <?php echo esc_html($method['bank_cpf_cnpj']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    private function render_paypal_details($method) {
        if (empty($method['paypal_email'])) return;
        ?>
        <div class="bg-gray-50 rounded-lg p-4 text-left">
            <div class="text-sm">
                <p><span class="font-medium text-gray-600"><?php _e('E-mail PayPal:', 'wp-donate-brasil'); ?></span></p>
                <div class="flex items-center gap-2 mt-2">
                    <input type="text" readonly value="<?php echo esc_attr($method['paypal_email']); ?>"
                           class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded text-sm">
                    <button type="button" class="wdb-copy-btn px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors"
                            data-copy="<?php echo esc_attr($method['paypal_email']); ?>" title="<?php esc_attr_e('Copiar', 'wp-donate-brasil'); ?>">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function render_donors_gallery($atts) {
        $settings = get_option('wdb_page_settings', array());
        $primary_color = esc_attr($settings['primary_color'] ?? '#3B82F6');
        $secondary_color = esc_attr($settings['secondary_color'] ?? '#10B981');
        
        // Usa configuração do admin
        $limit = intval($settings['items_per_page'] ?? 10);
        $donors = WDB_Main::get_approved_donors($limit);
        $total_donors = WDB_Main::count_approved_donors();
        
        if (empty($donors)) {
            return '';
        }
        
        ob_start();
        ?>
        <style>
            .wdb-gallery-section {
                padding: 60px 0 80px;
            }
            
            .wdb-gallery-header {
                text-align: center;
                margin-bottom: 40px;
            }
            
            .wdb-gallery-title {
                font-size: 2rem;
                font-weight: 800;
                color: #1e293b;
                margin: 0 0 10px;
            }
            
            .wdb-gallery-subtitle {
                color: #64748b;
                font-size: 1rem;
                margin: 0;
            }
            
            .wdb-donors-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 20px;
                margin-bottom: 40px;
                max-width: 900px;
                margin-left: auto;
                margin-right: auto;
            }
            
            .wdb-donor-item {
                background: white;
                border-radius: 16px;
                padding: 24px 16px;
                text-align: center;
                box-shadow: 0 4px 15px rgba(0,0,0,0.06);
                border: 1px solid #e2e8f0;
                transition: all 0.3s ease;
                position: relative;
            }
            
            .wdb-donor-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 35px rgba(0,0,0,0.1);
                border-color: <?php echo $this->hex_to_rgba($primary_color, 0.3); ?>;
            }
            
            .wdb-donor-badge {
                position: absolute;
                top: 12px;
                right: 12px;
                padding: 3px 8px;
                background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);
                color: white;
                font-size: 0.65rem;
                font-weight: 700;
                border-radius: 12px;
                display: flex;
                align-items: center;
                gap: 3px;
            }
            
            .wdb-donor-avatar {
                width: 70px;
                height: 70px;
                border-radius: 50%;
                overflow: hidden;
                margin: 0 auto 15px;
                border: 3px solid #f1f5f9;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            }
            
            .wdb-donor-avatar img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .wdb-donor-avatar-anon {
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #94a3b8, #64748b);
            }
            
            .wdb-donor-avatar-anon i {
                font-size: 28px;
                color: white;
            }
            
            .wdb-donor-name {
                font-size: 0.95rem;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 6px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .wdb-donor-date {
                font-size: 0.75rem;
                color: #94a3b8;
            }
            
            .wdb-gallery-cta {
                text-align: center;
            }
            
            .wdb-view-all-btn {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 14px 32px;
                background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);
                color: white;
                font-weight: 600;
                font-size: 0.95rem;
                border-radius: 50px;
                border: none;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.3s ease;
                box-shadow: 0 8px 25px <?php echo $this->hex_to_rgba($primary_color, 0.25); ?>;
            }
            
            .wdb-view-all-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 30px <?php echo $this->hex_to_rgba($primary_color, 0.35); ?>;
                color: white;
            }
            
            @media (max-width: 1024px) {
                .wdb-donors-grid { grid-template-columns: repeat(4, 1fr); }
            }
            
            @media (max-width: 768px) {
                .wdb-donors-grid { grid-template-columns: repeat(3, 1fr); gap: 15px; }
                .wdb-donor-item { padding: 20px 12px; }
                .wdb-donor-avatar { width: 60px; height: 60px; }
            }
            
            @media (max-width: 480px) {
                .wdb-donors-grid { grid-template-columns: repeat(2, 1fr); }
            }
        </style>
        
        <section class="wdb-gallery-section">
            <header class="wdb-gallery-header">
                <h2 class="wdb-gallery-title"><?php echo esc_html($settings['gallery_title'] ?? __('Nossos Doadores', 'wp-donate-brasil')); ?></h2>
                <p class="wdb-gallery-subtitle"><?php _e('Pessoas incríveis que fazem a diferença', 'wp-donate-brasil'); ?></p>
            </header>
            
            <div class="wdb-donors-grid">
                <?php foreach ($donors as $donor): 
                    $gravatar_url = WDB_Main::get_gravatar_url($donor->donor_email, 150);
                    $donation_count = intval($donor->donation_count);
                    $is_anonymous = !empty($donor->anonymous);
                ?>
                <article class="wdb-donor-item">
                    <?php if ($donation_count > 1): ?>
                    <span class="wdb-donor-badge">
                        <i class="fas fa-star"></i> <?php echo $donation_count; ?>x
                    </span>
                    <?php endif; ?>
                    
                    <div class="wdb-donor-avatar">
                        <?php if ($is_anonymous): ?>
                        <div class="wdb-donor-avatar-anon"><i class="fas fa-user-secret"></i></div>
                        <?php else: ?>
                        <img src="<?php echo esc_url($gravatar_url); ?>" alt="<?php echo esc_attr($donor->donor_name); ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="wdb-donor-name"><?php echo $is_anonymous ? __('Anônimo', 'wp-donate-brasil') : esc_html($donor->donor_name); ?></h3>
                    <div class="wdb-donor-date"><?php echo date_i18n('d M Y', strtotime($donor->created_at)); ?></div>
                </article>
                <?php endforeach; ?>
            </div>
            
            <?php 
            $donors_page_slug = $settings['donors_page_slug'] ?? 'doadores';
            $donors_page_url = home_url('/' . $donors_page_slug . '/');
            if ($total_donors > 10): 
            ?>
            <div class="wdb-gallery-cta">
                <a href="<?php echo esc_url($donors_page_url); ?>" class="wdb-view-all-btn">
                    <i class="fas fa-users"></i>
                    <?php printf(__('Ver todos os %d doadores', 'wp-donate-brasil'), $total_donors); ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endif; ?>
        </section>
        <?php
        return ob_get_clean();
    }
    
    // Funções helper para manipulação de cores
    private function hex_to_rgba($hex, $alpha = 1) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return "rgba($r, $g, $b, $alpha)";
    }
    
    private function adjust_brightness($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = min(255, max(0, $r + ($r * $percent / 100)));
        $g = min(255, max(0, $g + ($g * $percent / 100)));
        $b = min(255, max(0, $b + ($b * $percent / 100)));
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    private function blend_colors($hex1, $hex2, $ratio) {
        $hex1 = str_replace('#', '', $hex1);
        $hex2 = str_replace('#', '', $hex2);
        
        $r1 = hexdec(substr($hex1, 0, 2));
        $g1 = hexdec(substr($hex1, 2, 2));
        $b1 = hexdec(substr($hex1, 4, 2));
        
        $r2 = hexdec(substr($hex2, 0, 2));
        $g2 = hexdec(substr($hex2, 2, 2));
        $b2 = hexdec(substr($hex2, 4, 2));
        
        $r = round($r1 * (1 - $ratio) + $r2 * $ratio);
        $g = round($g1 * (1 - $ratio) + $g2 * $ratio);
        $b = round($b1 * (1 - $ratio) + $b2 * $ratio);
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    // Página completa de lista de doadores com paginação - Design moderno com cards
    public function render_donors_list_page($atts) {
        $settings = get_option('wdb_page_settings', array());
        $primary_color = esc_attr($settings['primary_color'] ?? '#3B82F6');
        $secondary_color = esc_attr($settings['secondary_color'] ?? '#10B981');
        
        // Campos visíveis
        $show_photo = $settings['list_show_photo'] ?? true;
        $show_name = $settings['list_show_name'] ?? true;
        $show_email = $settings['list_show_email'] ?? false;
        $show_phone = $settings['list_show_phone'] ?? false;
        $show_value = $settings['list_show_value'] ?? false;
        $show_count = $settings['list_show_count'] ?? true;
        $show_date = $settings['list_show_date'] ?? true;
        $show_method = $settings['list_show_method'] ?? false;
        $show_message = $settings['list_show_message'] ?? true;
        
        // Filtros habilitados
        $filter_search_enabled = $settings['filter_search'] ?? true;
        $filter_method_enabled = $settings['filter_method'] ?? true;
        $filter_month_enabled = $settings['filter_month'] ?? true;
        $filter_order_enabled = $settings['filter_order'] ?? true;
        
        // Valores dos filtros
        $filter_search = sanitize_text_field($_GET['busca'] ?? '');
        $filter_method = sanitize_text_field($_GET['metodo'] ?? '');
        $filter_month = intval($_GET['mes'] ?? 0);
        $filter_year = intval($_GET['ano'] ?? 0);
        $filter_order = sanitize_text_field($_GET['ordem'] ?? 'recent');
        
        // Paginação
        $per_page = 40;
        $current_page = max(1, intval($_GET['pg'] ?? 1));
        $offset = ($current_page - 1) * $per_page;
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdb_receipts';
        
        // Constrói condições WHERE para filtros
        $where_base = "status = 'approved' AND show_in_gallery = 1";
        $where_named = $where_base . " AND donor_email != 'anonimo@anonimo.com'";
        $where_anon = $where_base . " AND donor_email = 'anonimo@anonimo.com'";
        
        if ($filter_search) {
            $search_like = '%' . $wpdb->esc_like($filter_search) . '%';
            $where_named .= $wpdb->prepare(" AND (donor_name LIKE %s OR donor_email LIKE %s OR donor_phone LIKE %s)", $search_like, $search_like, $search_like);
        }
        if ($filter_method) {
            $where_named .= $wpdb->prepare(" AND donation_method = %s", $filter_method);
            $where_anon .= $wpdb->prepare(" AND donation_method = %s", $filter_method);
        }
        if ($filter_month > 0) {
            $where_named .= $wpdb->prepare(" AND MONTH(created_at) = %d", $filter_month);
            $where_anon .= $wpdb->prepare(" AND MONTH(created_at) = %d", $filter_month);
        }
        if ($filter_year > 0) {
            $where_named .= $wpdb->prepare(" AND YEAR(created_at) = %d", $filter_year);
            $where_anon .= $wpdb->prepare(" AND YEAR(created_at) = %d", $filter_year);
        }
        
        // Conta total
        $total = $wpdb->get_var(
            "SELECT COUNT(*) FROM (
                SELECT donor_email FROM $table_name WHERE $where_named GROUP BY donor_email
                UNION ALL
                SELECT CONCAT('anon_', donation_method) FROM $table_name WHERE $where_anon GROUP BY donation_method
            ) as combined"
        );
        $total_pages = ceil($total / $per_page);
        
        // Doadores não-anônimos
        $donors_named = $wpdb->get_results(
            "SELECT 
                donor_email,
                MAX(donor_name) as donor_name,
                MAX(donor_phone) as donor_phone,
                MAX(message) as message,
                0 as anonymous,
                MAX(donation_method) as donation_method,
                COUNT(*) as donation_count,
                MAX(created_at) as created_at,
                SUM(donation_amount) as total_amount
            FROM $table_name 
            WHERE $where_named
            GROUP BY donor_email"
        );
        
        // Doadores anônimos agrupados por método
        $donors_anon = $wpdb->get_results(
            "SELECT 
                CONCAT('anon_', donation_method) as donor_email,
                'Doador Anônimo' as donor_name,
                '' as donor_phone,
                '' as message,
                1 as anonymous,
                donation_method,
                COUNT(*) as donation_count,
                MAX(created_at) as created_at,
                SUM(donation_amount) as total_amount
            FROM $table_name 
            WHERE $where_anon
            GROUP BY donation_method"
        );
        
        // Combina e ordena
        $all_donors = array_merge($donors_named, $donors_anon);
        
        // Ordenação
        switch ($filter_order) {
            case 'name':
                usort($all_donors, function($a, $b) { return strcmp($a->donor_name, $b->donor_name); });
                break;
            case 'value':
                usort($all_donors, function($a, $b) { return $b->total_amount - $a->total_amount; });
                break;
            case 'count':
                usort($all_donors, function($a, $b) { return $b->donation_count - $a->donation_count; });
                break;
            case 'oldest':
                usort($all_donors, function($a, $b) { return strtotime($a->created_at) - strtotime($b->created_at); });
                break;
            default: // recent
                usort($all_donors, function($a, $b) { return strtotime($b->created_at) - strtotime($a->created_at); });
        }
        
        // Aplica paginação
        $donors = array_slice($all_donors, $offset, $per_page);
        
        // Anos disponíveis para filtro
        $available_years = $wpdb->get_col("SELECT DISTINCT YEAR(created_at) FROM $table_name WHERE status = 'approved' ORDER BY YEAR(created_at) DESC");
        
        $method_labels = array(
            'pix' => array('label' => 'PIX', 'icon' => 'fas fa-qrcode', 'color' => '#10b981'),
            'bank_transfer' => array('label' => 'Transferência', 'icon' => 'fas fa-university', 'color' => '#3b82f6'),
            'bitcoin' => array('label' => 'Bitcoin', 'icon' => 'fab fa-bitcoin', 'color' => '#f7931a'),
            'paypal' => array('label' => 'PayPal', 'icon' => 'fab fa-paypal', 'color' => '#003087'),
            'payment_link' => array('label' => 'Link', 'icon' => 'fas fa-link', 'color' => '#8b5cf6')
        );
        
        ob_start();
        ?>
        <link rel="stylesheet" href="<?php echo esc_url(WDB_PLUGIN_URL . 'assets/css/tailwind.min.css'); ?>">
        <link rel="stylesheet" href="<?php echo esc_url(WDB_PLUGIN_URL . 'assets/css/fontawesome.min.css'); ?>">
        
        <style>
            .wdb-donors-page { font-family: 'Inter', sans-serif; }
            .wdb-donor-card { transition: all 0.3s ease; }
            .wdb-donor-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
            .wdb-gradient-text { background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
            .wdb-badge { background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>); }
        </style>
        
        <div class="wdb-donors-page min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 py-12 px-4">
            <div class="max-w-7xl mx-auto">
                
                <!-- Header elegante -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full wdb-badge mb-6 shadow-lg">
                        <i class="fas fa-heart text-3xl text-white"></i>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-3">
                        <?php echo esc_html($settings['gallery_title'] ?? __('Nossos Doadores', 'wp-donate-brasil')); ?>
                    </h1>
                    <p class="text-lg text-gray-500 max-w-2xl mx-auto">
                        <?php printf(__('Agradecemos a cada um dos nossos %d apoiadores que tornaram isso possível', 'wp-donate-brasil'), $total); ?>
                    </p>
                </div>
                
                <?php if ($filter_search_enabled || $filter_method_enabled || $filter_month_enabled || $filter_order_enabled): 
                    $has_active_filters = $filter_search || $filter_method || $filter_month || $filter_year || $filter_order !== 'recent';
                ?>
                <!-- Botão Flutuante de Filtros -->
                <style>
                    .wdb-filter-tab {
                        position: fixed;
                        left: 0;
                        top: 100px;
                        z-index: 9999;
                        padding: 12px 14px;
                        background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);
                        color: #fff;
                        border: none;
                        border-radius: 0 10px 10px 0;
                        cursor: pointer;
                        font-size: 16px;
                        box-shadow: 3px 3px 15px rgba(0,0,0,0.15);
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        animation: wdb-pulse 2s infinite;
                    }
                    @keyframes wdb-pulse {
                        0%, 100% { box-shadow: 3px 3px 15px rgba(0,0,0,0.15); }
                        50% { box-shadow: 3px 3px 25px <?php echo $primary_color; ?>60; }
                    }
                    .wdb-filter-tab:hover { 
                        padding-right: 18px;
                        animation: none;
                        box-shadow: 4px 4px 20px <?php echo $primary_color; ?>50;
                    }
                    .wdb-filter-tab:hover::after {
                        content: 'Filtros';
                        position: absolute;
                        left: 100%;
                        top: 50%;
                        transform: translateY(-50%);
                        background: #1f2937;
                        color: #fff;
                        padding: 6px 12px;
                        border-radius: 6px;
                        font-size: 12px;
                        font-weight: 500;
                        white-space: nowrap;
                        margin-left: 8px;
                    }
                    .wdb-filter-tab .wdb-filter-badge {
                        background: #fff;
                        color: <?php echo $primary_color; ?>;
                        border-radius: 50%;
                        width: 18px;
                        height: 18px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 10px;
                        font-weight: 700;
                    }
                    .wdb-filter-overlay {
                        position: fixed;
                        inset: 0;
                        background: rgba(0,0,0,0.5);
                        z-index: 10000;
                        opacity: 0;
                        visibility: hidden;
                        transition: all 0.3s ease;
                    }
                    .wdb-filter-overlay.active { opacity: 1; visibility: visible; }
                    .wdb-filter-sidebar {
                        position: fixed;
                        left: -320px;
                        top: 0;
                        bottom: 0;
                        width: 320px;
                        background: #fff;
                        z-index: 10001;
                        box-shadow: 5px 0 25px rgba(0,0,0,0.15);
                        transition: all 0.3s ease;
                        overflow-y: auto;
                    }
                    .wdb-filter-sidebar.active { left: 0; }
                    .wdb-filter-header {
                        padding: 20px;
                        background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);
                        color: white;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                    }
                    .wdb-filter-header h3 { margin: 0; font-size: 18px; font-weight: 700; }
                    .wdb-filter-close {
                        background: rgba(255,255,255,0.2);
                        border: none;
                        color: white;
                        width: 36px;
                        height: 36px;
                        border-radius: 50%;
                        cursor: pointer;
                        transition: all 0.2s;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 16px;
                        line-height: 1;
                    }
                    .wdb-filter-close:hover { background: rgba(255,255,255,0.3); }
                    .wdb-filter-body { padding: 20px; }
                    .wdb-filter-group { margin-bottom: 20px; }
                    .wdb-filter-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; }
                    .wdb-filter-input, 
                    .wdb-filter-sidebar input[type="text"], 
                    .wdb-filter-sidebar select { 
                        width: 100%; 
                        padding: 12px 16px; 
                        border: 1px solid #e5e7eb !important; 
                        border-radius: 12px; 
                        font-size: 14px; 
                        background: #fff !important; 
                        transition: all 0.2s; 
                        outline: none;
                        -webkit-appearance: none;
                        appearance: none;
                        color: #374151;
                    }
                    .wdb-filter-input:focus, 
                    .wdb-filter-sidebar input[type="text"]:focus, 
                    .wdb-filter-sidebar select:focus { 
                        border-color: <?php echo $primary_color; ?> !important; 
                        box-shadow: 0 0 0 3px <?php echo $primary_color; ?>20; 
                    }
                    .wdb-filter-sidebar select {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
                        background-repeat: no-repeat;
                        background-position: right 12px center;
                        background-size: 20px;
                        padding-right: 40px;
                        cursor: pointer;
                    }
                    .wdb-filter-row { display: flex; gap: 10px; }
                    .wdb-filter-row .wdb-filter-input { flex: 1; }
                    .wdb-filter-actions { display: flex; gap: 10px; margin-top: 25px; }
                    .wdb-filter-btn {
                        flex: 1;
                        padding: 14px;
                        border: none;
                        border-radius: 12px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.2s;
                    }
                    .wdb-filter-btn-primary {
                        background: linear-gradient(135deg, <?php echo $primary_color; ?>, <?php echo $secondary_color; ?>);
                        color: white;
                    }
                    .wdb-filter-btn-primary:hover { opacity: 0.9; transform: translateY(-2px); }
                    .wdb-filter-btn-secondary { background: #f3f4f6; color: #6b7280; }
                    .wdb-filter-btn-secondary:hover { background: #e5e7eb; }
                    .wdb-active-filters {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 8px;
                        margin-bottom: 8px;
                    }
                    .wdb-active-tag {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 6px 12px;
                        background: <?php echo $primary_color; ?>15;
                        color: <?php echo $primary_color; ?>;
                        border-radius: 20px;
                        font-size: 12px;
                        font-weight: 500;
                    }
                </style>
                
                <!-- Aba/Botão Flutuante -->
                <button type="button" class="wdb-filter-tab" onclick="wdbOpenFilters()" title="<?php esc_attr_e('Filtros', 'wp-donate-brasil'); ?>">
                    <i class="fas fa-sliders-h"></i>
                    <?php if ($has_active_filters): ?>
                    <span class="wdb-filter-badge"><?php 
                        $count = 0;
                        if ($filter_search) $count++;
                        if ($filter_method) $count++;
                        if ($filter_month || $filter_year) $count++;
                        if ($filter_order !== 'recent') $count++;
                        echo $count;
                    ?></span>
                    <?php endif; ?>
                </button>
                
                <!-- Overlay -->
                <div class="wdb-filter-overlay" id="wdbFilterOverlay" onclick="wdbCloseFilters()"></div>
                
                <!-- Sidebar de Filtros -->
                <div class="wdb-filter-sidebar" id="wdbFilterSidebar">
                    <div class="wdb-filter-header">
                        <h3><i class="fas fa-filter mr-2"></i><?php _e('Filtrar Doadores', 'wp-donate-brasil'); ?></h3>
                        <button type="button" class="wdb-filter-close" onclick="wdbCloseFilters()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="wdb-filter-body">
                        <form method="get">
                            <?php if ($filter_search_enabled): ?>
                            <div class="wdb-filter-group">
                                <label class="wdb-filter-label"><i class="fas fa-search mr-1"></i> <?php _e('Buscar', 'wp-donate-brasil'); ?></label>
                                <input type="text" name="busca" value="<?php echo esc_attr($filter_search); ?>" 
                                    placeholder="<?php _e('Nome, e-mail ou telefone...', 'wp-donate-brasil'); ?>" class="wdb-filter-input">
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($filter_method_enabled): ?>
                            <div class="wdb-filter-group">
                                <label class="wdb-filter-label"><i class="fas fa-wallet mr-1"></i> <?php _e('Método de Pagamento', 'wp-donate-brasil'); ?></label>
                                <select name="metodo" class="wdb-filter-input">
                                    <option value=""><?php _e('Todos os métodos', 'wp-donate-brasil'); ?></option>
                                    <option value="pix" <?php selected($filter_method, 'pix'); ?>>PIX</option>
                                    <option value="bank_transfer" <?php selected($filter_method, 'bank_transfer'); ?>><?php _e('Transferência Bancária', 'wp-donate-brasil'); ?></option>
                                    <option value="bitcoin" <?php selected($filter_method, 'bitcoin'); ?>>Bitcoin</option>
                                    <option value="paypal" <?php selected($filter_method, 'paypal'); ?>>PayPal</option>
                                    <option value="payment_link" <?php selected($filter_method, 'payment_link'); ?>><?php _e('Link de Pagamento', 'wp-donate-brasil'); ?></option>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($filter_month_enabled): ?>
                            <div class="wdb-filter-group">
                                <label class="wdb-filter-label"><i class="fas fa-calendar mr-1"></i> <?php _e('Período', 'wp-donate-brasil'); ?></label>
                                <div class="wdb-filter-row">
                                    <select name="mes" class="wdb-filter-input">
                                        <option value="0"><?php _e('Mês', 'wp-donate-brasil'); ?></option>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo $m; ?>" <?php selected($filter_month, $m); ?>><?php echo date_i18n('F', mktime(0, 0, 0, $m, 1)); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select name="ano" class="wdb-filter-input">
                                        <option value="0"><?php _e('Ano', 'wp-donate-brasil'); ?></option>
                                        <?php foreach ($available_years as $year): ?>
                                            <option value="<?php echo $year; ?>" <?php selected($filter_year, $year); ?>><?php echo $year; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($filter_order_enabled): ?>
                            <div class="wdb-filter-group">
                                <label class="wdb-filter-label"><i class="fas fa-sort mr-1"></i> <?php _e('Ordenar por', 'wp-donate-brasil'); ?></label>
                                <select name="ordem" class="wdb-filter-input">
                                    <option value="recent" <?php selected($filter_order, 'recent'); ?>><?php _e('Mais recentes', 'wp-donate-brasil'); ?></option>
                                    <option value="oldest" <?php selected($filter_order, 'oldest'); ?>><?php _e('Mais antigos', 'wp-donate-brasil'); ?></option>
                                    <option value="name" <?php selected($filter_order, 'name'); ?>><?php _e('Nome A-Z', 'wp-donate-brasil'); ?></option>
                                    <option value="value" <?php selected($filter_order, 'value'); ?>><?php _e('Maior valor doado', 'wp-donate-brasil'); ?></option>
                                    <option value="count" <?php selected($filter_order, 'count'); ?>><?php _e('Mais doações', 'wp-donate-brasil'); ?></option>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <div class="wdb-filter-actions">
                                <button type="submit" class="wdb-filter-btn wdb-filter-btn-primary">
                                    <i class="fas fa-check mr-1"></i> <?php _e('Aplicar', 'wp-donate-brasil'); ?>
                                </button>
                                <?php if ($has_active_filters): ?>
                                <a href="<?php echo esc_url(strtok($_SERVER['REQUEST_URI'], '?')); ?>" class="wdb-filter-btn wdb-filter-btn-secondary">
                                    <i class="fas fa-times mr-1"></i> <?php _e('Limpar', 'wp-donate-brasil'); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </form>
                        
                        <?php if ($has_active_filters): ?>
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                            <p style="font-size: 12px; color: #6b7280; margin-bottom: 10px;"><?php _e('Filtros ativos:', 'wp-donate-brasil'); ?></p>
                            <div class="wdb-active-filters">
                                <?php if ($filter_search): ?>
                                    <span class="wdb-active-tag"><i class="fas fa-search"></i> "<?php echo esc_html($filter_search); ?>"</span>
                                <?php endif; ?>
                                <?php if ($filter_method): ?>
                                    <span class="wdb-active-tag"><i class="fas fa-wallet"></i> <?php echo esc_html($method_labels[$filter_method]['label'] ?? $filter_method); ?></span>
                                <?php endif; ?>
                                <?php if ($filter_month || $filter_year): ?>
                                    <span class="wdb-active-tag"><i class="fas fa-calendar"></i> <?php 
                                        if ($filter_month) echo date_i18n('M', mktime(0, 0, 0, $filter_month, 1));
                                        if ($filter_month && $filter_year) echo '/';
                                        if ($filter_year) echo $filter_year;
                                    ?></span>
                                <?php endif; ?>
                            </div>
                            <p style="font-size: 12px; color: #9ca3af;"><?php printf(__('%d resultados encontrados', 'wp-donate-brasil'), $total); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <script>
                function wdbOpenFilters() {
                    document.getElementById('wdbFilterOverlay').classList.add('active');
                    document.getElementById('wdbFilterSidebar').classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
                function wdbCloseFilters() {
                    document.getElementById('wdbFilterOverlay').classList.remove('active');
                    document.getElementById('wdbFilterSidebar').classList.remove('active');
                    document.body.style.overflow = '';
                }
                </script>
                <?php endif; ?>
                
                <?php if (empty($donors)): ?>
                    <div class="text-center py-20">
                        <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-heart-crack text-4xl text-gray-300"></i>
                        </div>
                        <p class="text-gray-500 text-lg"><?php _e('Nenhum doador encontrado ainda.', 'wp-donate-brasil'); ?></p>
                    </div>
                <?php else: ?>
                
                    <!-- Grid de Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-10">
                        <?php foreach ($donors as $donor): 
                            $is_anonymous = !empty($donor->anonymous);
                            $gravatar_url = WDB_Main::get_gravatar_url($donor->donor_email, 150);
                            $method_info = $method_labels[$donor->donation_method] ?? array('label' => $donor->donation_method, 'icon' => 'fas fa-hand-holding-heart', 'color' => '#6b7280');
                        ?>
                        <div class="wdb-donor-card bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
                            <!-- Header do card com foto -->
                            <div class="relative pt-8 pb-4 px-6 text-center">
                                <?php if ($show_count && intval($donor->donation_count) > 1): ?>
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold text-white wdb-badge shadow">
                                        <i class="fas fa-star text-yellow-300"></i>
                                        <?php echo intval($donor->donation_count); ?>x
                                    </span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($show_photo): ?>
                                <div class="relative inline-block">
                                    <div class="w-20 h-20 mx-auto rounded-full overflow-hidden ring-4 ring-white shadow-lg">
                                        <?php if ($is_anonymous): ?>
                                            <div class="w-full h-full bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center">
                                                <i class="fas fa-user-secret text-2xl text-white"></i>
                                            </div>
                                        <?php else: ?>
                                            <img src="<?php echo esc_url($gravatar_url); ?>" alt="" class="w-full h-full object-cover">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($show_name): ?>
                                <h3 class="mt-4 text-lg font-bold text-gray-900">
                                    <?php 
                                    if ($is_anonymous) {
                                        $anon_method = $method_labels[$donor->donation_method] ?? array('label' => $donor->donation_method);
                                        echo sprintf(__('Anônimo (%s)', 'wp-donate-brasil'), $anon_method['label']);
                                    } else {
                                        echo esc_html($donor->donor_name);
                                    }
                                    ?>
                                </h3>
                                <?php endif; ?>
                                
                                <?php if ($show_date): ?>
                                <p class="text-sm text-gray-400 mt-1">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    <?php echo date_i18n('d M Y', strtotime($donor->created_at)); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Info do card -->
                            <div class="px-6 pb-6 space-y-3">
                                <?php if ($show_message && !empty($donor->message) && !$is_anonymous): 
                                    $full_message = esc_html($donor->message);
                                    $short_message = mb_strlen($full_message) > 50 ? mb_substr($full_message, 0, 50) . '...' : $full_message;
                                    $has_more = mb_strlen($full_message) > 50;
                                ?>
                                <div class="bg-gray-50 rounded-xl p-3 <?php echo $has_more ? 'cursor-pointer hover:bg-gray-100 transition-colors' : ''; ?>" 
                                     <?php if ($has_more): ?>onclick="this.querySelector('.wdb-msg-short').classList.toggle('hidden'); this.querySelector('.wdb-msg-full').classList.toggle('hidden');"<?php endif; ?>>
                                    <p class="text-sm text-gray-600 italic wdb-msg-short">"<?php echo $short_message; ?>"</p>
                                    <?php if ($has_more): ?>
                                    <p class="text-sm text-gray-600 italic wdb-msg-full hidden">"<?php echo $full_message; ?>"</p>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex flex-wrap gap-2">
                                    <?php if ($show_method): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white" style="background-color: <?php echo $method_info['color']; ?>">
                                        <i class="<?php echo $method_info['icon']; ?>"></i>
                                        <?php echo $method_info['label']; ?>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($show_value && floatval($donor->total_amount) > 0): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-100 text-emerald-700">
                                        <i class="fas fa-coins"></i>
                                        R$ <?php echo number_format(floatval($donor->total_amount), 2, ',', '.'); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($show_email && !$is_anonymous): ?>
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <i class="fas fa-envelope w-4 text-gray-400"></i>
                                    <span><?php echo esc_html(WDB_Main::obfuscate_email($donor->donor_email)); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($show_phone && !$is_anonymous && !empty($donor->donor_phone)): ?>
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <i class="fas fa-phone w-4 text-gray-400"></i>
                                    <span><?php echo esc_html(WDB_Main::obfuscate_phone($donor->donor_phone)); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Paginação moderna -->
                    <?php if ($total_pages > 1): 
                        // Constrói query string preservando filtros
                        $filter_params = array();
                        if ($filter_search) $filter_params['busca'] = $filter_search;
                        if ($filter_method) $filter_params['metodo'] = $filter_method;
                        if ($filter_month) $filter_params['mes'] = $filter_month;
                        if ($filter_year) $filter_params['ano'] = $filter_year;
                        if ($filter_order !== 'recent') $filter_params['ordem'] = $filter_order;
                        
                        $build_url = function($page) use ($filter_params) {
                            $params = array_merge($filter_params, array('pg' => $page));
                            return '?' . http_build_query($params);
                        };
                    ?>
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-2xl shadow-md p-6">
                        <p class="text-sm text-gray-500">
                            <?php printf(__('Mostrando <span class="font-semibold text-gray-700">%d-%d</span> de <span class="font-semibold text-gray-700">%d</span> doadores', 'wp-donate-brasil'), $offset + 1, min($offset + $per_page, $total), $total); ?>
                        </p>
                        <nav class="flex items-center gap-2">
                            <?php if ($current_page > 1): ?>
                            <a href="<?php echo esc_url($build_url($current_page - 1)); ?>" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                                <i class="fas fa-arrow-left"></i>
                                <span class="hidden sm:inline"><?php _e('Anterior', 'wp-donate-brasil'); ?></span>
                            </a>
                            <?php endif; ?>
                            
                            <div class="hidden sm:flex items-center gap-1">
                                <?php
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);
                                
                                if ($start_page > 1): ?>
                                    <a href="<?php echo esc_url($build_url(1)); ?>" class="w-10 h-10 flex items-center justify-center text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span class="px-1 text-gray-400">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $current_page): ?>
                                        <span class="w-10 h-10 flex items-center justify-center text-sm font-bold text-white wdb-badge rounded-xl shadow"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url($build_url($i)); ?>" class="w-10 h-10 flex items-center justify-center text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <span class="px-1 text-gray-400">...</span>
                                    <?php endif; ?>
                                    <a href="<?php echo esc_url($build_url($total_pages)); ?>" class="w-10 h-10 flex items-center justify-center text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200"><?php echo $total_pages; ?></a>
                                <?php endif; ?>
                            </div>
                            
                            <span class="sm:hidden text-sm text-gray-500"><?php echo $current_page; ?> / <?php echo $total_pages; ?></span>
                            
                            <?php if ($current_page < $total_pages): ?>
                            <a href="<?php echo esc_url($build_url($current_page + 1)); ?>" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white wdb-badge rounded-xl hover:opacity-90 transition-opacity shadow">
                                <span class="hidden sm:inline"><?php _e('Próximo', 'wp-donate-brasil'); ?></span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                    <?php endif; ?>
                    
                <?php endif; ?>
                
                <!-- Link voltar -->
                <div class="text-center mt-10">
                    <a href="<?php echo home_url('/doacoes/'); ?>" class="inline-flex items-center gap-2 px-6 py-3 text-gray-600 bg-white rounded-xl shadow-md hover:shadow-lg transition-all">
                        <i class="fas fa-arrow-left"></i>
                        <?php _e('Voltar para página de doações', 'wp-donate-brasil'); ?>
                    </a>
                </div>
                
                <?php if ($settings['show_credits'] ?? true): ?>
                <!-- Crédito do desenvolvedor -->
                <div class="text-center mt-16 pb-6">
                    <a href="https://dantetesta.com.br" target="_blank" rel="noopener" class="text-xs text-gray-400 hover:text-gray-500 transition-colors">
                        Desenvolvido por Dante Testa
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
