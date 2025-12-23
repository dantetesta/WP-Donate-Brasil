<?php
/**
 * Classe responsável pela renderização do frontend
 * 
 * @package WP_Donate_Brasil
 * @author Dante Testa <https://dantetesta.com.br>
 * @since 1.0.0
 * @created 23/12/2025 09:21
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDB_Frontend {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_head', array($this, 'add_meta_tags'));
        add_filter('document_title_parts', array($this, 'modify_page_title'));
    }
    
    public function add_meta_tags() {
        if (!$this->is_donation_page()) {
            return;
        }
        
        $settings = get_option('wdb_page_settings', array());
        $page_title = $settings['page_title'] ?? __('Faça uma Doação', 'wp-donate-brasil');
        $page_description = $settings['page_description'] ?? '';
        $site_name = get_bloginfo('name');
        $page_url = get_permalink();
        ?>
        <!-- WP Donate Brasil SEO Meta Tags -->
        <meta name="description" content="<?php echo esc_attr($page_description); ?>">
        <meta name="robots" content="index, follow">
        
        <!-- Open Graph -->
        <meta property="og:title" content="<?php echo esc_attr($page_title . ' - ' . $site_name); ?>">
        <meta property="og:description" content="<?php echo esc_attr($page_description); ?>">
        <meta property="og:type" content="website">
        <meta property="og:url" content="<?php echo esc_url($page_url); ?>">
        <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
        
        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="<?php echo esc_attr($page_title); ?>">
        <meta name="twitter:description" content="<?php echo esc_attr($page_description); ?>">
        
        <!-- Preconnect para performance -->
        <link rel="preconnect" href="https://cdn.tailwindcss.com">
        <link rel="preconnect" href="https://cdnjs.cloudflare.com">
        <link rel="preconnect" href="https://cdn.jsdelivr.net">
        
        <!-- Schema.org para doações -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "DonateAction",
            "name": "<?php echo esc_js($page_title); ?>",
            "description": "<?php echo esc_js($page_description); ?>",
            "url": "<?php echo esc_url($page_url); ?>",
            "recipient": {
                "@type": "Organization",
                "name": "<?php echo esc_js($site_name); ?>"
            }
        }
        </script>
        <?php
    }
    
    public function modify_page_title($title_parts) {
        if (!$this->is_donation_page()) {
            return $title_parts;
        }
        
        $settings = get_option('wdb_page_settings', array());
        if (!empty($settings['page_title'])) {
            $title_parts['title'] = $settings['page_title'];
        }
        
        return $title_parts;
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
}
