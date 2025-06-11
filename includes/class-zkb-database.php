<?php
/**
 * Database operations for Zero-Knowledge Blog
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZKB_Database {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'zkb_encrypted_posts';
    }
    
    /**
     * Create database table on activation
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            encrypted_content longtext NOT NULL,
            encrypted_title text DEFAULT NULL,
            salt varchar(255) NOT NULL,
            iv varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY post_id (post_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Save encrypted post data
     */
    public function save_encrypted_post($post_id, $encrypted_content, $salt, $iv, $encrypted_title = null) {
        global $wpdb;
        
        return $wpdb->replace(
            $this->table_name,
            array(
                'post_id' => $post_id,
                'encrypted_content' => $encrypted_content,
                'encrypted_title' => $encrypted_title,
                'salt' => $salt,
                'iv' => $iv,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Get encrypted post data
     */
    public function get_encrypted_post($post_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE post_id = %d",
                $post_id
            ),
            ARRAY_A
        );
    }
    
    /**
     * Check if post is encrypted
     */
    public function is_post_encrypted($post_id) {
        global $wpdb;
        
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE post_id = %d",
                $post_id
            )
        );
        
        return $count > 0;
    }
    
    /**
     * Delete encrypted post data
     */
    public function delete_encrypted_post($post_id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('post_id' => $post_id),
            array('%d')
        );
    }
    
    /**
     * Get all encrypted posts
     */
    public function get_encrypted_posts() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC",
            ARRAY_A
        );
    }
}
