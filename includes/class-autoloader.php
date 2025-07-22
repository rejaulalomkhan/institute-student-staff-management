<?php
/**
 * Autoloader for Institute Management plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoloader class
 */
class Institute_Management_Autoloader {
    
    /**
     * Register autoloader
     */
    public static function register() {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
    
    /**
     * Autoload classes
     */
    public static function autoload($class) {
        // Check if class belongs to our namespace
        if (strpos($class, 'Institute_Management\\') !== 0) {
            return;
        }
        
        // Remove namespace and convert to file path
        $class = str_replace('Institute_Management\\', '', $class);
        $class = str_replace('\\', '/', $class);
        
        // Convert to lowercase and add class- prefix
        $file_parts = explode('/', $class);
        $file_name = 'class-' . strtolower(str_replace('_', '-', array_pop($file_parts)));
        
        // Build file path
        $file_path = INSTITUTE_MANAGEMENT_PLUGIN_DIR . 'includes/';
        if (!empty($file_parts)) {
            $file_path .= strtolower(implode('/', $file_parts)) . '/';
        }
        $file_path .= $file_name . '.php';
        
        // Load file if it exists
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
}

// Register autoloader
Institute_Management_Autoloader::register(); 