<?php
/**
 * Plugin Name: Zero-Knowledge Blog
 * Description: Simple zero-knowledge encryption for WordPress posts
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: zero-knowledge-blog
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ZKB_VERSION', '1.0.0');
define('ZKB_PLUGIN_FILE', __FILE__);
define('ZKB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ZKB_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class ZeroKnowledgeBlog {
    
    private static $instance = null;
    private $database;
    private $admin;
    private $frontend;
    private $shortcodes;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
        $this->init_components();
    }
    
    private function init_hooks() {
        register_activation_hook(ZKB_PLUGIN_FILE, array($this, 'activate'));
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    private function load_dependencies() {
        require_once ZKB_PLUGIN_DIR . 'includes/class-zkb-database.php';
        require_once ZKB_PLUGIN_DIR . 'includes/class-zkb-admin.php';
        require_once ZKB_PLUGIN_DIR . 'includes/class-zkb-frontend.php';
        require_once ZKB_PLUGIN_DIR . 'includes/class-zkb-shortcodes.php';
    }
    
    private function init_components() {
        $this->database = new ZKB_Database();
        $this->admin = new ZKB_Admin();
        $this->frontend = new ZKB_Frontend();
        $this->shortcodes = new ZKB_Shortcodes();
    }
    
    public function init() {
        // Plugin initialized
        error_log('ZKB: Plugin initialized');
    }
    
    public function activate() {
        $this->database->create_tables();
        error_log('ZKB: Plugin activated');
    }
    
    public function get_database() {
        return $this->database;
    }
}

// Initialize plugin
ZeroKnowledgeBlog::get_instance();

// Utility function
function zkb() {
    return ZeroKnowledgeBlog::get_instance();
}
