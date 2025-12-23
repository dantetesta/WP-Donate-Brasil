<?php
/**
 * Template de página isolada para doações (sem header/footer do WP)
 * 
 * @package WP_Donate_Brasil
 * @author Dante Testa <https://dantetesta.com.br>
 * @since 1.0.1
 * @created 23/12/2025 09:39
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('wdb_page_settings', array());
$methods = get_option('wdb_donation_methods', array());

$primary_color = esc_attr($settings['primary_color'] ?? '#3B82F6');
$secondary_color = esc_attr($settings['secondary_color'] ?? '#10B981');
$page_title = esc_html($settings['page_title'] ?? __('Faça uma Doação', 'wp-donate-brasil'));
$page_subtitle = esc_html($settings['page_subtitle'] ?? '');
$page_description = esc_html($settings['page_description'] ?? '');
$site_name = get_bloginfo('name');
$site_url = home_url();

// Contar métodos ativos
$active_methods = array_filter($methods, function($m) { return !empty($m['enabled']); });
$methods_count = count($active_methods);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $page_title; ?> - <?php echo $site_name; ?>">
    <meta property="og:description" content="<?php echo $page_description; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo get_permalink(); ?>">
    
    <title><?php echo $page_title; ?> - <?php echo $site_name; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo esc_url(WDB_PLUGIN_URL . 'assets/css/fontawesome.min.css'); ?>">
    
    <!-- Swiper -->
    <link rel="stylesheet" href="<?php echo esc_url(WDB_PLUGIN_URL . 'assets/css/swiper.min.css'); ?>">
    
    <!-- AOS Animations -->
    <link rel="stylesheet" href="<?php echo esc_url(WDB_PLUGIN_URL . 'assets/css/aos.min.css'); ?>">
    
    <style>
        :root {
            --wdb-primary: <?php echo $primary_color; ?>;
            --wdb-secondary: <?php echo $secondary_color; ?>;
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
        }
        
        /* Animated Background */
        .wdb-bg-animated {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .wdb-bg-animated::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, var(--wdb-primary) 0%, transparent 50%);
            opacity: 0.1;
            animation: wdbPulse 15s ease-in-out infinite;
        }
        
        .wdb-bg-animated::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, var(--wdb-secondary) 0%, transparent 50%);
            opacity: 0.1;
            animation: wdbPulse 15s ease-in-out infinite reverse;
        }
        
        @keyframes wdbPulse {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(5%, 5%) scale(1.1); }
        }
        
        /* Floating particles */
        .wdb-particle {
            position: absolute;
            border-radius: 50%;
            background: var(--wdb-primary);
            opacity: 0.3;
            animation: wdbFloat 20s infinite;
        }
        
        @keyframes wdbFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.3; }
            50% { transform: translateY(-100px) rotate(180deg); opacity: 0.1; }
        }
        
        /* Glass morphism cards */
        .wdb-glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .wdb-glass-light {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        /* Gradient text */
        .wdb-gradient-text {
            background: linear-gradient(135deg, var(--wdb-primary), var(--wdb-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Method cards */
        .wdb-method-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
        }
        
        .wdb-method-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        
        .wdb-method-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--wdb-primary), var(--wdb-secondary));
            border-radius: 1rem 1rem 0 0;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .wdb-method-card:hover::before {
            opacity: 1;
        }
        
        /* Glow button */
        .wdb-glow-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .wdb-glow-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .wdb-glow-btn:hover::before {
            width: 300%;
            height: 300%;
        }
        
        .wdb-glow-btn:hover {
            box-shadow: 0 0 30px var(--wdb-primary), 0 0 60px var(--wdb-primary);
        }
        
        /* Copy button feedback */
        .wdb-copy-btn.copied {
            background-color: #10B981 !important;
        }
        
        /* Form message */
        #wdb-form-message {
            transition: all 0.3s;
        }
        
        #wdb-form-message.success {
            background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
            color: #065F46;
            border: 1px solid #34D399;
        }
        
        #wdb-form-message.error {
            background: linear-gradient(135deg, #FEE2E2, #FECACA);
            color: #991B1B;
            border: 1px solid #F87171;
        }
        
        /* Modal */
        #wdb-receipt-form-section {
            animation: wdbSlideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes wdbSlideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Swiper customization */
        .wdb-donors-swiper .swiper-pagination-bullet {
            background: var(--wdb-primary);
            opacity: 0.5;
        }
        
        .wdb-donors-swiper .swiper-pagination-bullet-active {
            opacity: 1;
            background: var(--wdb-secondary);
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--wdb-primary);
            border-radius: 4px;
        }
        
        /* Loading animation */
        .wdb-loading {
            animation: wdbSpin 1s linear infinite;
        }
        
        @keyframes wdbSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .wdb-method-card:hover {
                transform: none;
            }
        }
    </style>
    
    <?php wp_head(); ?>
</head>
<body class="wdb-fullpage antialiased">
    <!-- Animated Background -->
    <div class="wdb-bg-animated">
        <?php for ($i = 0; $i < 5; $i++): ?>
        <div class="wdb-particle" style="
            width: <?php echo rand(10, 30); ?>px;
            height: <?php echo rand(10, 30); ?>px;
            left: <?php echo rand(0, 100); ?>%;
            top: <?php echo rand(0, 100); ?>%;
            animation-delay: <?php echo $i * 2; ?>s;
            animation-duration: <?php echo rand(15, 25); ?>s;
        "></div>
        <?php endfor; ?>
    </div>
    
    <!-- Header -->
    <header class="py-4 px-6 fixed top-0 left-0 right-0 z-50 wdb-glass">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="<?php echo $site_url; ?>" class="flex items-center gap-3 text-white hover:opacity-80 transition-opacity">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="font-medium"><?php _e('Voltar ao site', 'wp-donate-brasil'); ?></span>
            </a>
            <div class="text-white text-sm">
                <i class="fa-solid fa-heart text-red-400 mr-2 animate-pulse"></i>
                <span class="opacity-70"><?php echo $site_name; ?></span>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="pt-24 pb-16 px-4 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <!-- Hero Section -->
            <section class="text-center mb-16" data-aos="fade-up">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full wdb-glass text-white/70 text-sm mb-6">
                    <i class="fa-solid fa-hand-holding-heart text-red-400"></i>
                    <span><?php _e('Sua contribuição faz a diferença', 'wp-donate-brasil'); ?></span>
                </div>
                
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-6 leading-tight">
                    <?php echo $page_title; ?>
                </h1>
                
                <?php if (!empty($page_subtitle)): ?>
                <p class="text-xl md:text-2xl wdb-gradient-text font-semibold mb-4" data-aos="fade-up" data-aos-delay="100">
                    <?php echo $page_subtitle; ?>
                </p>
                <?php endif; ?>
                
                <?php if (!empty($page_description)): ?>
                <p class="text-white/60 text-lg max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                    <?php echo $page_description; ?>
                </p>
                <?php endif; ?>
                
                <div class="flex items-center justify-center gap-8 mt-8 text-white/50 text-sm" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-check text-green-400"></i>
                        <span><?php _e('100% Seguro', 'wp-donate-brasil'); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-yellow-400"></i>
                        <span><?php _e('Rápido e Fácil', 'wp-donate-brasil'); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-heart text-red-400"></i>
                        <span><?php _e('Transparente', 'wp-donate-brasil'); ?></span>
                    </div>
                </div>
            </section>
            
            <!-- Donation Methods -->
            <section class="mb-16">
                <div class="grid grid-cols-1 md:grid-cols-2 <?php echo $methods_count >= 3 ? 'lg:grid-cols-3' : ''; ?> gap-6">
                    <?php 
                    $delay = 0;
                    foreach ($methods as $method): 
                        if (empty($method['enabled'])) continue;
                        $method_id = esc_attr($method['id']);
                        $method_name = esc_html($method['name']);
                        $method_icon = esc_attr($method['icon'] ?? 'fa-solid fa-hand-holding-heart');
                        $instructions = esc_html($method['instructions'] ?? '');
                    ?>
                    <div class="wdb-method-card wdb-glass-light rounded-2xl p-6 relative" 
                         data-method="<?php echo $method_id; ?>"
                         data-aos="fade-up" 
                         data-aos-delay="<?php echo $delay; ?>">
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-5 rounded-2xl flex items-center justify-center shadow-lg"
                                 style="background: linear-gradient(135deg, var(--wdb-primary), var(--wdb-secondary));">
                                <i class="<?php echo $method_icon; ?> text-3xl text-white"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2"><?php echo $method_name; ?></h3>
                            <p class="text-gray-500 text-sm mb-5"><?php echo $instructions; ?></p>
                            
                            <?php 
                            // Renderiza detalhes do método
                            WDB_Donation_Page::get_instance()->render_method_details_public($method);
                            ?>
                            
                            <button type="button" 
                                    class="wdb-send-receipt-btn wdb-glow-btn mt-5 w-full py-4 px-6 text-white font-bold rounded-xl transition-all flex items-center justify-center gap-3"
                                    style="background: linear-gradient(135deg, var(--wdb-primary), var(--wdb-secondary));"
                                    data-method="<?php echo $method_id; ?>" 
                                    data-method-name="<?php echo $method_name; ?>">
                                <i class="fa-solid fa-upload"></i>
                                <span><?php _e('Enviar Comprovante', 'wp-donate-brasil'); ?></span>
                            </button>
                        </div>
                    </div>
                    <?php 
                        $delay += 100;
                    endforeach; 
                    ?>
                </div>
            </section>
            
            <!-- Receipt Form Modal -->
            <section id="wdb-receipt-form-section" class="mb-16 hidden">
                <div class="max-w-2xl mx-auto wdb-glass-light rounded-3xl p-8 shadow-2xl">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" 
                                     style="background: linear-gradient(135deg, var(--wdb-primary), var(--wdb-secondary));">
                                    <i class="fa-solid fa-upload text-white"></i>
                                </div>
                                <?php _e('Enviar Comprovante', 'wp-donate-brasil'); ?>
                            </h2>
                            <p class="text-gray-500 text-sm mt-1"><?php _e('Preencha os dados abaixo para registrar sua doação', 'wp-donate-brasil'); ?></p>
                        </div>
                        <button type="button" id="wdb-close-form" 
                                class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-700 flex items-center justify-center transition-all">
                            <i class="fa-solid fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form id="wdb-receipt-form" enctype="multipart/form-data" class="space-y-5">
                        <input type="hidden" name="donation_method" id="wdb-donation-method" value="">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="wdb-donor-name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <?php _e('Seu Nome', 'wp-donate-brasil'); ?> <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="wdb-donor-name" name="donor_name" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="<?php esc_attr_e('Digite seu nome completo', 'wp-donate-brasil'); ?>">
                            </div>
                            <div>
                                <label for="wdb-donor-email" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <?php _e('Seu E-mail', 'wp-donate-brasil'); ?> <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="wdb-donor-email" name="donor_email" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="<?php esc_attr_e('seu@email.com', 'wp-donate-brasil'); ?>">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="wdb-donor-phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <?php _e('Telefone', 'wp-donate-brasil'); ?>
                                </label>
                                <input type="tel" id="wdb-donor-phone" name="donor_phone"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="<?php esc_attr_e('(00) 00000-0000', 'wp-donate-brasil'); ?>">
                            </div>
                            <div>
                                <label for="wdb-donation-amount" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <?php _e('Valor da Doação', 'wp-donate-brasil'); ?>
                                </label>
                                <input type="number" id="wdb-donation-amount" name="donation_amount" min="0" step="0.01"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="<?php esc_attr_e('R$ 0,00', 'wp-donate-brasil'); ?>">
                            </div>
                        </div>
                        
                        <div>
                            <label for="wdb-receipt-file" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php _e('Comprovante', 'wp-donate-brasil'); ?> <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="file" id="wdb-receipt-file" name="receipt_file" required
                                    accept=".jpg,.jpeg,.png,.gif,.pdf"
                                    class="w-full px-4 py-4 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
                                <p class="text-xs text-gray-400 mt-2 flex items-center gap-2">
                                    <i class="fa-solid fa-info-circle"></i>
                                    <?php _e('JPG, PNG, GIF ou PDF. Máximo 5MB.', 'wp-donate-brasil'); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div>
                            <label for="wdb-message" class="block text-sm font-semibold text-gray-700 mb-2">
                                <?php _e('Mensagem (opcional)', 'wp-donate-brasil'); ?>
                            </label>
                            <textarea id="wdb-message" name="message" rows="3"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"
                                placeholder="<?php esc_attr_e('Deixe uma mensagem de apoio...', 'wp-donate-brasil'); ?>"></textarea>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-4 py-4 px-5 bg-gray-50 rounded-xl">
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" name="show_in_gallery" checked
                                    class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-3 text-sm text-gray-600 group-hover:text-gray-800"><?php _e('Exibir na galeria de doadores', 'wp-donate-brasil'); ?></span>
                            </label>
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" name="anonymous"
                                    class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-3 text-sm text-gray-600 group-hover:text-gray-800"><?php _e('Doação anônima', 'wp-donate-brasil'); ?></span>
                            </label>
                        </div>
                        
                        <button type="submit" id="wdb-submit-btn"
                            class="wdb-glow-btn w-full py-4 px-6 text-white font-bold text-lg rounded-xl transition-all flex items-center justify-center gap-3"
                            style="background: linear-gradient(135deg, var(--wdb-primary), var(--wdb-secondary));">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span><?php _e('Enviar Comprovante', 'wp-donate-brasil'); ?></span>
                        </button>
                    </form>
                    
                    <div id="wdb-form-message" class="mt-5 p-4 rounded-xl hidden"></div>
                </div>
            </section>
            
            <!-- Donors Gallery -->
            <?php if (!empty($settings['show_gallery'])): 
                $donors = WDB_Main::get_approved_donors($settings['items_per_page'] ?? 12);
                if (!empty($donors)):
            ?>
            <section id="wdb-donors-gallery" class="py-12" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-bold text-center text-white mb-10">
                    <i class="fa-solid fa-heart text-red-400 mr-3"></i>
                    <?php echo esc_html($settings['gallery_title'] ?? __('Nossos Doadores', 'wp-donate-brasil')); ?>
                </h2>
                
                <div class="swiper wdb-donors-swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($donors as $donor): 
                            $gravatar_url = WDB_Main::get_gravatar_url($donor->donor_email, 150);
                            $donation_count = intval($donor->donation_count);
                            $is_anonymous = !empty($donor->anonymous);
                        ?>
                        <div class="swiper-slide">
                            <div class="wdb-glass-light rounded-2xl p-6 text-center h-full relative">
                                <!-- Avatar com Gravatar -->
                                <div class="relative inline-block mb-4">
                                    <div class="w-20 h-20 mx-auto rounded-full overflow-hidden ring-4 ring-white/30 shadow-xl">
                                        <?php if ($is_anonymous): ?>
                                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-400 to-gray-600">
                                                <i class="fa-solid fa-user-secret text-3xl text-white"></i>
                                            </div>
                                        <?php else: ?>
                                            <img src="<?php echo esc_url($gravatar_url); ?>" 
                                                 alt="<?php echo esc_attr($donor->donor_name); ?>"
                                                 class="w-full h-full object-cover"
                                                 loading="lazy"
                                                 onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-green-500\'><i class=\'fa-solid fa-user text-3xl text-white\'></i></div>';">
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Badge contador de doações -->
                                    <?php if ($donation_count > 1): ?>
                                    <div class="absolute -top-1 -right-1 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white shadow-lg"
                                         style="background: linear-gradient(135deg, #F59E0B, #EF4444); animation: pulse 2s infinite;"
                                         title="<?php printf(esc_attr__('%d doações', 'wp-donate-brasil'), $donation_count); ?>">
                                        <?php echo $donation_count; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Nome do doador -->
                                <h4 class="font-bold text-gray-800 text-lg mb-1">
                                    <?php echo $is_anonymous ? __('Doador Anônimo', 'wp-donate-brasil') : esc_html($donor->donor_name); ?>
                                </h4>
                                
                                <!-- Indicador de múltiplas doações -->
                                <?php if ($donation_count > 1): ?>
                                <p class="text-xs font-semibold text-amber-600 mb-2">
                                    <i class="fa-solid fa-fire mr-1"></i>
                                    <?php printf(__('%dx doador', 'wp-donate-brasil'), $donation_count); ?>
                                </p>
                                <?php endif; ?>
                                
                                <!-- Mensagem -->
                                <?php if (!empty($donor->message) && !$is_anonymous): ?>
                                <p class="text-gray-500 text-sm italic mb-2">"<?php echo esc_html(wp_trim_words($donor->message, 10)); ?>"</p>
                                <?php endif; ?>
                                
                                <!-- Data mais recente -->
                                <p class="text-xs text-gray-400 mt-2">
                                    <i class="fa-regular fa-calendar mr-1"></i>
                                    <?php echo date_i18n('d/m/Y', strtotime($donor->created_at)); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination mt-8"></div>
                </div>
            </section>
            <?php endif; endif; ?>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="py-8 px-4 wdb-glass">
        <div class="max-w-7xl mx-auto text-center">
            <p class="text-white/50 text-sm">
                <?php printf(__('© %s %s. Todos os direitos reservados.', 'wp-donate-brasil'), date('Y'), $site_name); ?>
            </p>
            <p class="text-white/30 text-xs mt-2">
                <?php _e('Desenvolvido com', 'wp-donate-brasil'); ?> <i class="fa-solid fa-heart text-red-400"></i> <?php _e('por', 'wp-donate-brasil'); ?> 
                <a href="https://dantetesta.com.br" target="_blank" class="text-white/50 hover:text-white transition-colors">Dante Testa</a>
            </p>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="<?php echo esc_url(includes_url('js/jquery/jquery.min.js')); ?>"></script>
    <script src="<?php echo esc_url(WDB_PLUGIN_URL . 'assets/js/swiper.min.js'); ?>"></script>
    <script src="<?php echo esc_url(WDB_PLUGIN_URL . 'assets/js/aos.min.js'); ?>"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 50
        });
        
        // Initialize Swiper
        const swiper = new Swiper('.wdb-donors-swiper', {
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
                640: { slidesPerView: 2 },
                768: { slidesPerView: 3 },
                1024: { slidesPerView: 4 }
            }
        });
        
        // WP Donate Brasil Frontend
        const wdb_vars = {
            ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('wdb_nonce_action'); ?>',
            strings: {
                copied: '<?php _e('Copiado!', 'wp-donate-brasil'); ?>',
                uploading: '<?php _e('Enviando...', 'wp-donate-brasil'); ?>',
                success: '<?php _e('Comprovante enviado com sucesso!', 'wp-donate-brasil'); ?>',
                error: '<?php _e('Erro ao enviar. Tente novamente.', 'wp-donate-brasil'); ?>',
                file_required: '<?php _e('Por favor, selecione um arquivo.', 'wp-donate-brasil'); ?>',
                invalid_file: '<?php _e('Tipo de arquivo não permitido.', 'wp-donate-brasil'); ?>'
            }
        };
        
        (function($) {
            'use strict';
            
            // Copy to clipboard
            $(document).on('click', '.wdb-copy-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const text = $(this).data('copy');
                const $btn = $(this);
                
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(function() {
                        showCopyFeedback($btn);
                    });
                } else {
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    showCopyFeedback($btn);
                }
            });
            
            function showCopyFeedback($btn) {
                const originalHtml = $btn.html();
                $btn.addClass('copied').html('<i class="fa-solid fa-check"></i>');
                setTimeout(function() {
                    $btn.removeClass('copied').html(originalHtml);
                }, 2000);
            }
            
            // Open form
            $(document).on('click', '.wdb-send-receipt-btn', function(e) {
                e.preventDefault();
                const method = $(this).data('method');
                $('#wdb-donation-method').val(method);
                $('#wdb-receipt-form-section').removeClass('hidden');
                $('#wdb-receipt-form')[0].reset();
                $('#wdb-donation-method').val(method);
                $('#wdb-form-message').addClass('hidden');
                
                $('html, body').animate({
                    scrollTop: $('#wdb-receipt-form-section').offset().top - 100
                }, 500);
            });
            
            // Close form
            $('#wdb-close-form').on('click', function() {
                $('#wdb-receipt-form-section').fadeOut(300, function() {
                    $(this).addClass('hidden').show();
                });
            });
            
            // Close on ESC
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && !$('#wdb-receipt-form-section').hasClass('hidden')) {
                    $('#wdb-close-form').click();
                }
            });
            
            // Submit form
            $('#wdb-receipt-form').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'wdb_submit_receipt');
                formData.append('nonce', wdb_vars.nonce);
                
                const $btn = $('#wdb-submit-btn');
                const originalHtml = $btn.html();
                
                $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner wdb-loading mr-2"></i>' + wdb_vars.strings.uploading);
                
                $.ajax({
                    url: wdb_vars.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.data.message, 'success');
                            $('#wdb-receipt-form')[0].reset();
                            setTimeout(function() {
                                $('#wdb-close-form').click();
                            }, 3000);
                        } else {
                            showMessage(response.data.message || wdb_vars.strings.error, 'error');
                        }
                    },
                    error: function() {
                        showMessage(wdb_vars.strings.error, 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
            
            function showMessage(msg, type) {
                $('#wdb-form-message')
                    .removeClass('hidden success error')
                    .addClass(type)
                    .html('<i class="fa-solid ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + ' mr-2"></i>' + msg);
            }
            
            // Phone mask
            $('#wdb-donor-phone').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length > 11) value = value.substring(0, 11);
                
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
            
        })(jQuery);
    </script>
    
    <?php wp_footer(); ?>
</body>
</html>
