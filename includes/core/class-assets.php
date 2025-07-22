<?php
/**
 * Assets class
 */

namespace Institute_Management\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assets class
 */
class Assets {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Check if we're on a relevant page
        if ($this->should_load_frontend_assets()) {
            // Enqueue CSS
            wp_enqueue_style(
                'institute-management-frontend',
                INSTITUTE_MANAGEMENT_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                INSTITUTE_MANAGEMENT_VERSION
            );
            
            // Enqueue JavaScript
            wp_enqueue_script(
                'institute-management-frontend',
                INSTITUTE_MANAGEMENT_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                INSTITUTE_MANAGEMENT_VERSION,
                true
            );
            
            // Localize script
            wp_localize_script('institute-management-frontend', 'institute_management_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('institute_management_nonce'),
                'strings' => array(
                    'loading' => __('Loading...', 'institute-management'),
                    'error' => __('An error occurred. Please try again.', 'institute-management'),
                    'success' => __('Success!', 'institute-management'),
                )
            ));
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        global $post_type, $pagenow;
        
        // Check if we're on a relevant admin page
        if ($this->should_load_admin_assets($hook, $post_type, $pagenow)) {
            // Enqueue CSS
            wp_enqueue_style(
                'institute-management-admin',
                INSTITUTE_MANAGEMENT_PLUGIN_URL . 'assets/css/admin.css',
                array('wp-admin', 'dashicons'),
                INSTITUTE_MANAGEMENT_VERSION
            );
            
            // Enqueue JavaScript
            wp_enqueue_script(
                'institute-management-admin',
                INSTITUTE_MANAGEMENT_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'wp-util'),
                INSTITUTE_MANAGEMENT_VERSION,
                true
            );
            
            // Localize script
            wp_localize_script('institute-management-admin', 'institute_management_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('institute_management_admin_nonce'),
                'strings' => array(
                    'confirm_delete' => __('Are you sure you want to delete this item?', 'institute-management'),
                    'saving' => __('Saving...', 'institute-management'),
                    'saved' => __('Saved!', 'institute-management'),
                    'error' => __('An error occurred. Please try again.', 'institute-management'),
                )
            ));
        }
        
        // Load Chart.js for dashboard widgets
        if ($hook === 'index.php' || strpos($hook, 'institute-management') !== false) {
            wp_enqueue_script(
                'chart-js',
                'https://cdn.jsdelivr.net/npm/chart.js',
                array(),
                '3.9.1',
                true
            );
        }
    }
    
    /**
     * Check if frontend assets should be loaded
     */
    private function should_load_frontend_assets() {
        return (
            is_singular(array('student', 'staff')) ||
            is_post_type_archive(array('student', 'staff')) ||
            is_tax(array('student_class', 'staff_department', 'student_batch', 'staff_role')) ||
            has_shortcode(get_post()->post_content ?? '', 'students_list') ||
            has_shortcode(get_post()->post_content ?? '', 'staff_list') ||
            has_shortcode(get_post()->post_content ?? '', 'institute_directory')
        );
    }
    
    /**
     * Check if admin assets should be loaded
     */
    private function should_load_admin_assets($hook, $post_type, $pagenow) {
        $institute_pages = array(
            'post.php',
            'post-new.php',
            'edit.php',
            'edit-tags.php',
            'term.php'
        );
        
        $institute_post_types = array('student', 'staff');
        $institute_taxonomies = array('student_class', 'staff_department', 'student_batch', 'staff_role');
        
        // Check for institute post types
        if (in_array($pagenow, $institute_pages) && in_array($post_type, $institute_post_types)) {
            return true;
        }
        
        // Check for institute taxonomies
        if (in_array($pagenow, array('edit-tags.php', 'term.php'))) {
            $taxonomy = $_GET['taxonomy'] ?? '';
            if (in_array($taxonomy, $institute_taxonomies)) {
                return true;
            }
        }
        
        // Check for institute management pages
        if (strpos($hook, 'institute-management') !== false) {
            return true;
        }
        
        return false;
    }
} 