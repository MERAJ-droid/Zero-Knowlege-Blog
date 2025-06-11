<?php
/**
 * Frontend functionality for Zero-Knowledge Blog
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZKB_Frontend {
    
    public function __construct() {
        add_filter('the_content', array($this, 'filter_encrypted_content'));
        add_filter('the_title', array($this, 'filter_encrypted_title'), 10, 2);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_zkb_get_encrypted_data', array($this, 'get_encrypted_data'));
        add_action('wp_ajax_nopriv_zkb_get_encrypted_data', array($this, 'get_encrypted_data'));
        add_shortcode('zkb_encrypted_content', array($this, 'render_encrypted_content_placeholder'));
    }
    
    /**
     * Filter post content to show decryption form for encrypted posts
     */
    public function filter_encrypted_content($content) {
        if (!is_singular() || is_admin()) {
            return $content;
        }
        
        global $post;
        $database = zkb()->get_database();
        
        if ($database->is_post_encrypted($post->ID)) {
            // Replace content with decryption form
            ob_start();
            include ZKB_PLUGIN_DIR . 'templates/decrypt-form.php';
            return ob_get_clean();
        }
        
        return $content;
    }
    
    /**
     * Filter post title for encrypted posts
     */
    public function filter_encrypted_title($title, $post_id = null) {
        if (is_admin() || !$post_id) {
            return $title;
        }
        
        $database = zkb()->get_database();
        $encrypted_data = $database->get_encrypted_post($post_id);
        
        if ($encrypted_data && !empty($encrypted_data['encrypted_title'])) {
            return '<span class="zkb-encrypted-title" data-post-id="' . $post_id . '">[Encrypted Title]</span>';
        }
        
        return $title;
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        if (!is_singular()) {
            return;
        }
        
        global $post;
        $database = zkb()->get_database();
        
        if ($database->is_post_encrypted($post->ID)) {
            wp_enqueue_script(
                'zkb-crypto',
                ZKB_PLUGIN_URL . 'assets/js/zkb-crypto.js',
                array(),
                ZKB_VERSION,
                true
            );
            
            wp_enqueue_script(
                'zkb-frontend',
                ZKB_PLUGIN_URL . 'assets/js/zkb-frontend.js',
                array('jquery', 'zkb-crypto'),
                ZKB_VERSION,
                true
            );
            
            wp_enqueue_style(
                'zkb-frontend',
                ZKB_PLUGIN_URL . 'assets/css/zkb-frontend.css',
                array(),
                ZKB_VERSION
            );
            
            wp_localize_script('zkb-frontend', 'zkb_frontend', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('zkb_frontend_nonce'),
                'post_id' => $post->ID,
                'messages' => array(
                    'decrypting' => 'Decrypting...',
                    'wrong_password' => 'Wrong password. Please try again.',
                    'decryption_failed' => 'Decryption failed. Please check your password.',
                    'hide_content' => 'Hide Content',
                    'show_content' => 'Enter Password'
                )
            ));
        }
    }
    
    /**
     * Handle AJAX request to get encrypted data
     */
    public function get_encrypted_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'zkb_frontend_nonce')) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_POST['post_id']);
        $database = zkb()->get_database();
        $encrypted_data = $database->get_encrypted_post($post_id);
        
        if ($encrypted_data) {
            // Only send necessary data for decryption
            wp_send_json_success(array(
                'encrypted_content' => $encrypted_data['encrypted_content'],
                'encrypted_title' => $encrypted_data['encrypted_title'],
                'salt' => $encrypted_data['salt'],
                'iv' => $encrypted_data['iv']
            ));
        } else {
            wp_send_json_error('No encrypted data found');
        }
    }
    
    /**
     * Render placeholder for encrypted content shortcode
     */
    public function render_encrypted_content_placeholder($atts) {
        return '<div class="zkb-encrypted-placeholder">This content is encrypted.</div>';
    }
}
