<?php
/**
 * Admin functionality for Zero-Knowledge Blog
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZKB_Admin {
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('wp_ajax_zkb_encrypt_post', array($this, 'handle_encrypt_post'));
        add_action('wp_ajax_zkb_decrypt_post_admin', array($this, 'handle_decrypt_post'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('delete_post', array($this, 'delete_encrypted_data'));
    }
    
    /**
     * Add meta box to post editor
     */
    public function add_meta_box() {
        add_meta_box(
            'zkb-encryption',
            'Zero-Knowledge Encryption',
            array($this, 'render_meta_box'),
            array('post', 'page'),
            'side',
            'high'
        );
    }
    
    /**
     * Render the encryption meta box
     */
    public function render_meta_box($post) {
        $database = zkb()->get_database();
        $is_encrypted = $database->is_post_encrypted($post->ID);
        
        wp_nonce_field('zkb_encrypt_nonce', 'zkb_nonce');
        
        include ZKB_PLUGIN_DIR . 'templates/admin-meta-box.php';
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }
        
        wp_enqueue_script(
            'zkb-crypto',
            ZKB_PLUGIN_URL . 'assets/js/zkb-crypto.js',
            array(),
            ZKB_VERSION,
            true
        );
        
        wp_enqueue_script(
            'zkb-admin',
            ZKB_PLUGIN_URL . 'assets/js/zkb-admin.js',
            array('jquery', 'zkb-crypto'),
            ZKB_VERSION,
            true
        );
        
        wp_enqueue_style(
            'zkb-admin',
            ZKB_PLUGIN_URL . 'assets/css/zkb-admin.css',
            array(),
            ZKB_VERSION
        );
        
        wp_localize_script('zkb-admin', 'zkb_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('zkb_admin_nonce'),
            'post_id' => get_the_ID()
        ));
    }
    
    /**
     * Handle AJAX encryption request
     */
    public function handle_encrypt_post() {
        if (!wp_verify_nonce($_POST['nonce'], 'zkb_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }
        
        $post_id = intval($_POST['post_id']);
        $encrypted_content = sanitize_textarea_field($_POST['encrypted_content']);
        $encrypted_title = sanitize_text_field($_POST['encrypted_title']);
        $salt = sanitize_text_field($_POST['salt']);
        $iv = sanitize_text_field($_POST['iv']);
        
        $database = zkb()->get_database();
        $result = $database->save_encrypted_post(
            $post_id,
            $encrypted_content,
            $salt,
            $iv,
            $encrypted_title
        );
        
        if ($result !== false) {
            // Update post content to show it's encrypted
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => '[zkb_encrypted_content]'
            ));
            
            wp_send_json_success('Post encrypted successfully');
        } else {
            wp_send_json_error('Failed to encrypt post');
        }
    }
    
    /**
     * Handle AJAX decryption request for admin preview
     */
    public function handle_decrypt_post() {
        if (!wp_verify_nonce($_POST['nonce'], 'zkb_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }
        
        $post_id = intval($_POST['post_id']);
        $database = zkb()->get_database();
        $encrypted_data = $database->get_encrypted_post($post_id);
        
        if ($encrypted_data) {
            wp_send_json_success($encrypted_data);
        } else {
            wp_send_json_error('No encrypted data found');
        }
    }
    
    /**
     * Delete encrypted data when post is deleted
     */
    public function delete_encrypted_data($post_id) {
        $database = zkb()->get_database();
        $database->delete_encrypted_post($post_id);
    }
}
