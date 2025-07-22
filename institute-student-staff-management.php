<?php
/**
 * Plugin Name: Institute Student and Staff Management
 * Plugin URI: https://github.com/armanaazij/institute-management
 * Description: A comprehensive management system for educational institutes to manage students and staff with modern features including REST API, dashboard analytics, and advanced filtering.
 * Version: 2.0.0
 * Author: Arman Azij
 * Author URI: https://fb.com/armanaazij
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: institute-management
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('INSTITUTE_MANAGEMENT_VERSION', '2.0.0');
define('INSTITUTE_MANAGEMENT_PLUGIN_FILE', __FILE__);
define('INSTITUTE_MANAGEMENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('INSTITUTE_MANAGEMENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('INSTITUTE_MANAGEMENT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Require the autoloader
require_once INSTITUTE_MANAGEMENT_PLUGIN_DIR . 'includes/class-autoloader.php';

/**
 * Main plugin class
 */
final class Institute_Management {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->init_classes();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('Institute_Management', 'uninstall'));
    }
    
    /**
     * Initialize classes
     */
    private function init_classes() {
        // Core functionality
        new Institute_Management\Core\Post_Types();
        new Institute_Management\Core\Taxonomies();
        new Institute_Management\Core\Meta_Boxes();
        
        // Admin functionality
        if (is_admin()) {
            new Institute_Management\Admin\Admin_Menu();
            new Institute_Management\Admin\Dashboard_Widgets();
            new Institute_Management\Admin\List_Tables();
        }
        
        // Public functionality
        new Institute_Management\Public\Shortcodes();
        new Institute_Management\Public\Frontend_Display();
        new Institute_Management\Public\Template_Loader();
        
        // API
        new Institute_Management\API\REST_API();
        
        // Features
        new Institute_Management\Features\Notifications();
        new Institute_Management\Features\Import_Export();
        new Institute_Management\Features\Analytics();
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'institute-management',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize assets
        new Institute_Management\Core\Assets();
        
        // Check requirements
        if (!$this->check_requirements()) {
            return;
        }
    }
    
    /**
     * Check plugin requirements
     */
    private function check_requirements() {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo esc_html__('Institute Management requires PHP 7.4 or higher.', 'institute-management');
                echo '</p></div>';
            });
            return false;
        }
        
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo esc_html__('Institute Management requires WordPress 5.0 or higher.', 'institute-management');
                echo '</p></div>';
            });
            return false;
        }
        
        return true;
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables if needed
        $this->create_tables();
        
        // Create post types and taxonomies
        $post_types = new Institute_Management\Core\Post_Types();
        $post_types->register_post_types();
        
        $taxonomies = new Institute_Management\Core\Taxonomies();
        $taxonomies->register_taxonomies();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default options
        $this->set_default_options();
        
        // Schedule events
        if (!wp_next_scheduled('institute_management_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'institute_management_daily_cleanup');
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('institute_management_daily_cleanup');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Remove options
        delete_option('institute_management_settings');
        delete_option('institute_management_version');
        
        // Remove user meta
        delete_metadata('user', 0, 'institute_management_preferences', '', true);
        
        // Remove posts and meta (if setting enabled)
        $remove_data = get_option('institute_management_remove_data_on_uninstall', false);
        if ($remove_data) {
            self::remove_plugin_data();
        }
    }
    
    /**
     * Create custom tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Analytics table
        $table_name = $wpdb->prefix . 'institute_analytics';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            date date DEFAULT '0000-00-00' NOT NULL,
            post_type varchar(20) DEFAULT '' NOT NULL,
            action varchar(20) DEFAULT '' NOT NULL,
            count mediumint(9) DEFAULT 0 NOT NULL,
            PRIMARY KEY (id),
            KEY date_type_action (date, post_type, action)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Update version
        update_option('institute_management_version', INSTITUTE_MANAGEMENT_VERSION);
    }
    
    /**
     * Set default options
     */
    private function set_default_options() {
        $default_settings = array(
            'enable_notifications' => true,
            'enable_analytics' => true,
            'items_per_page' => 20,
            'enable_public_profiles' => true,
            'default_avatar' => INSTITUTE_MANAGEMENT_PLUGIN_URL . 'assets/images/default-avatar.png',
            'email_notifications' => array(
                'new_student' => true,
                'new_staff' => true,
                'profile_updates' => false
            )
        );
        
        add_option('institute_management_settings', $default_settings);
    }
    
    /**
     * Remove all plugin data
     */
    private static function remove_plugin_data() {
        global $wpdb;
        
        // Remove custom posts
        $post_types = array('student', 'staff');
        foreach ($post_types as $post_type) {
            $posts = get_posts(array(
                'post_type' => $post_type,
                'numberposts' => -1,
                'post_status' => 'any'
            ));
            
            foreach ($posts as $post) {
                wp_delete_post($post->ID, true);
            }
        }
        
        // Remove custom tables
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}institute_analytics");
        
        // Remove terms
        $taxonomies = array('student_class', 'staff_department');
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false
            ));
            
            foreach ($terms as $term) {
                wp_delete_term($term->term_id, $taxonomy);
            }
        }
    }
    
    /**
     * Get plugin settings
     */
    public static function get_settings() {
        return get_option('institute_management_settings', array());
    }
    
    /**
     * Get specific setting
     */
    public static function get_setting($key, $default = null) {
        $settings = self::get_settings();
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
}

// Initialize the plugin
function institute_management() {
    return Institute_Management::get_instance();
}

// Start the plugin
institute_management();