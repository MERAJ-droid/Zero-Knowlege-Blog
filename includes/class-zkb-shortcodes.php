<?php
/**
 * Shortcode functionality for Zero-Knowledge Blog
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZKB_Shortcodes {
    
    public function __construct() {
        add_shortcode('zkb_encrypted_content', array($this, 'render_encrypted_content'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        
        // Debug: Log when shortcode is registered
        error_log('ZKB: Shortcode class initialized');
    }
    
    public function enqueue_frontend_scripts() {
        // Only enqueue on posts that have encrypted content
        if (is_singular() && $this->post_has_encrypted_content()) {
            // Enqueue our crypto library
            wp_enqueue_script(
                'zkb-crypto',
                ZKB_PLUGIN_URL . 'assets/js/zkb-crypto.js',
                array(),
                ZKB_VERSION,
                true
            );
            
            // Enqueue frontend script
            wp_enqueue_script(
                'zkb-frontend',
                ZKB_PLUGIN_URL . 'assets/js/zkb-frontend.js',
                array('jquery', 'zkb-crypto'),
                ZKB_VERSION,
                true
            );
            
            // Enqueue frontend styles
            wp_enqueue_style(
                'zkb-frontend',
                ZKB_PLUGIN_URL . 'assets/css/zkb-frontend.css',
                array(),
                ZKB_VERSION
            );
        }
    }
    
    private function post_has_encrypted_content() {
        global $post;
        return $post && strpos($post->post_content, '[zkb_encrypted_content]') !== false;
    }
    
    public function render_encrypted_content($atts, $content = '') {
        // Debug logging
        error_log('ZKB: render_encrypted_content called with content length: ' . strlen($content));
        
        if (empty($content)) {
            return '<div style="background: #ffebee; padding: 20px; border: 1px solid #f44336; color: #c62828;">No encrypted content found.</div>';
        }
        
        // Generate unique ID for this encrypted block
        $block_id = 'zkb-block-' . wp_generate_password(8, false);
        
        ob_start();
        ?>
        <div class="zkb-encrypted-block" id="<?php echo esc_attr($block_id); ?>">
            <div class="zkb-decrypt-form">
                <div class="zkb-lock-icon">🔒</div>
                <h3>This content is encrypted</h3>
                <p>Enter the password to decrypt and view this content:</p>
                
                <div class="zkb-form-group">
                    <input 
                        type="password" 
                        class="zkb-password-input" 
                        placeholder="Enter password..."
                        data-encrypted="<?php echo esc_attr($content); ?>"
                        data-block-id="<?php echo esc_attr($block_id); ?>"
                    >
                    <button type="button" class="zkb-decrypt-btn">
                        🔓 Decrypt Content
                    </button>
                </div>
                
                <div class="zkb-messages"></div>
                <p><small><strong>Debug:</strong> Content length: <?php echo strlen($content); ?> characters</small></p>
            </div>
            
            <div class="zkb-decrypted-content" style="display: none;">
                <!-- Decrypted content will appear here -->
            </div>
        </div>
        
        <style>
        .zkb-encrypted-block {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
            text-align: center;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .zkb-lock-icon { font-size: 48px; margin-bottom: 15px; }
        .zkb-encrypted-block h3 { color: #495057; margin-bottom: 10px; font-size: 24px; }
        .zkb-encrypted-block p { color: #6c757d; margin-bottom: 20px; }
        .zkb-form-group { display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap; margin-bottom: 20px; }
        .zkb-password-input { padding: 12px 16px; border: 2px solid #ced4da; border-radius: 6px; font-size: 16px; min-width: 200px; flex: 1; max-width: 300px; }
        .zkb-password-input:focus { outline: none; border-color: #007cba; box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2); }
        .zkb-decrypt-btn { background: #007cba; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-size: 16px; cursor: pointer; transition: background-color 0.2s; white-space: nowrap; }
        .zkb-decrypt-btn:hover { background: #005a87; }
        .zkb-decrypt-btn:disabled { background: #6c757d; cursor: not-allowed; }
        .zkb-decrypted-content { text-align: left; background: white; padding: 20px; border-radius: 6px; border: 1px solid #e9ecef; }
        .zkb-messages { margin-top: 15px; }
        .zkb-alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 10px; }
        .zkb-alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .zkb-alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .zkb-alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        @media (max-width: 600px) {
            .zkb-encrypted-block { padding: 20px; margin: 10px; }
            .zkb-form-group { flex-direction: column; }
            .zkb-password-input { min-width: auto; width: 100%; max-width: none; }
            .zkb-decrypt-btn { width: 100%; }
        }
        </style>
        <?php
        return ob_get_clean();
    }
}
