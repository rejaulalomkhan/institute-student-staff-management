<?php
/**
 * Admin Menu class
 */

namespace Institute_Management\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Menu class
 */
class Admin_Menu {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Institute Management', 'institute-management'),
            __('Institute', 'institute-management'),
            'manage_options',
            'institute-management',
            array($this, 'dashboard_page'),
            'dashicons-groups',
            30
        );
        
        add_submenu_page(
            'institute-management',
            __('Dashboard', 'institute-management'),
            __('Dashboard', 'institute-management'),
            'manage_options',
            'institute-management',
            array($this, 'dashboard_page')
        );
        
        add_submenu_page(
            'institute-management',
            __('Settings', 'institute-management'),
            __('Settings', 'institute-management'),
            'manage_options',
            'institute-management-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'institute-management',
            __('Import/Export', 'institute-management'),
            __('Import/Export', 'institute-management'),
            'manage_options',
            'institute-management-import-export',
            array($this, 'import_export_page')
        );
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Institute Management Dashboard', 'institute-management'); ?></h1>
            <div class="institute-dashboard-wrapper">
                <div class="institute-stats-grid">
                    <div class="institute-stat-card">
                        <span class="institute-stat-number"><?php echo wp_count_posts('student')->publish; ?></span>
                        <span class="institute-stat-label"><?php _e('Total Students', 'institute-management'); ?></span>
                    </div>
                    <div class="institute-stat-card">
                        <span class="institute-stat-number"><?php echo wp_count_posts('staff')->publish; ?></span>
                        <span class="institute-stat-label"><?php _e('Total Staff', 'institute-management'); ?></span>
                    </div>
                    <div class="institute-stat-card">
                        <span class="institute-stat-number"><?php echo wp_count_terms('student_class'); ?></span>
                        <span class="institute-stat-label"><?php _e('Total Classes', 'institute-management'); ?></span>
                    </div>
                    <div class="institute-stat-card">
                        <span class="institute-stat-number"><?php echo wp_count_terms('staff_department'); ?></span>
                        <span class="institute-stat-label"><?php _e('Total Departments', 'institute-management'); ?></span>
                    </div>
                </div>
                
                <div class="institute-quick-actions">
                    <h3><?php _e('Quick Actions', 'institute-management'); ?></h3>
                    <p>
                        <a href="<?php echo admin_url('post-new.php?post_type=student'); ?>" class="institute-btn institute-btn-primary">
                            <?php _e('Add New Student', 'institute-management'); ?>
                        </a>
                        <a href="<?php echo admin_url('post-new.php?post_type=staff'); ?>" class="institute-btn institute-btn-primary">
                            <?php _e('Add New Staff Member', 'institute-management'); ?>
                        </a>
                        <a href="<?php echo admin_url('edit-tags.php?taxonomy=student_class&post_type=student'); ?>" class="institute-btn institute-btn-secondary">
                            <?php _e('Manage Classes', 'institute-management'); ?>
                        </a>
                        <a href="<?php echo admin_url('edit-tags.php?taxonomy=staff_department&post_type=staff'); ?>" class="institute-btn institute-btn-secondary">
                            <?php _e('Manage Departments', 'institute-management'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['institute_settings_nonce'], 'institute_settings')) {
            $this->save_settings();
        }
        
        $settings = get_option('institute_management_settings', array());
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        ?>
        <div class="wrap">
            <h1><?php _e('Institute Management Settings', 'institute-management'); ?></h1>
            
            <!-- Settings Navigation -->
            <nav class="institute-settings-nav">
                <ul>
                    <li><a href="?page=institute-management-settings&tab=general" class="<?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('General', 'institute-management'); ?></a></li>
                    <li><a href="?page=institute-management-settings&tab=display" class="<?php echo $active_tab === 'display' ? 'nav-tab-active' : ''; ?>"><?php _e('Display', 'institute-management'); ?></a></li>
                    <li><a href="?page=institute-management-settings&tab=templates" class="<?php echo $active_tab === 'templates' ? 'nav-tab-active' : ''; ?>"><?php _e('Templates', 'institute-management'); ?></a></li>
                </ul>
            </nav>
            
            <form method="post" action="">
                <?php wp_nonce_field('institute_settings', 'institute_settings_nonce'); ?>
                
                <div class="institute-settings-content">
                    <?php
                    switch ($active_tab) {
                        case 'general':
                            $this->render_general_settings($settings);
                            break;
                        case 'display':
                            $this->render_display_settings($settings);
                            break;
                        case 'templates':
                            $this->render_template_settings($settings);
                            break;
                        default:
                            $this->render_general_settings($settings);
                            break;
                    }
                    ?>
                    
                    <?php submit_button(__('Save Settings', 'institute-management'), 'primary', 'submit'); ?>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render general settings
     */
    private function render_general_settings($settings) {
        ?>
        <h2><?php _e('General Settings', 'institute-management'); ?></h2>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Institute Name', 'institute-management'); ?></label>
            <input type="text" name="institute_name" value="<?php echo esc_attr($settings['institute_name'] ?? ''); ?>" class="institute-form-input" />
            <div class="institute-form-description"><?php _e('Name of your educational institute.', 'institute-management'); ?></div>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Institute Address', 'institute-management'); ?></label>
            <textarea name="institute_address" class="institute-form-input" rows="3"><?php echo esc_textarea($settings['institute_address'] ?? ''); ?></textarea>
            <div class="institute-form-description"><?php _e('Complete address of the institute.', 'institute-management'); ?></div>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Contact Phone', 'institute-management'); ?></label>
            <input type="tel" name="institute_phone" value="<?php echo esc_attr($settings['institute_phone'] ?? ''); ?>" class="institute-form-input" />
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Contact Email', 'institute-management'); ?></label>
            <input type="email" name="institute_email" value="<?php echo esc_attr($settings['institute_email'] ?? ''); ?>" class="institute-form-input" />
        </div>
        

        <?php
    }
    
    /**
     * Render display settings
     */
    private function render_display_settings($settings) {
        ?>
        <h2><?php _e('Display Settings', 'institute-management'); ?></h2>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Items Per Page', 'institute-management'); ?></label>
            <input type="number" name="items_per_page" value="<?php echo esc_attr($settings['items_per_page'] ?? 20); ?>" class="institute-form-input" min="1" max="100" />
            <div class="institute-form-description"><?php _e('Number of items to display per page in lists.', 'institute-management'); ?></div>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Default Display Style', 'institute-management'); ?></label>
            <select name="default_display_style" class="institute-form-input">
                <option value="grid" <?php selected($settings['default_display_style'] ?? '', 'grid'); ?>><?php _e('Grid', 'institute-management'); ?></option>
                <option value="list" <?php selected($settings['default_display_style'] ?? '', 'list'); ?>><?php _e('List', 'institute-management'); ?></option>
                <option value="table" <?php selected($settings['default_display_style'] ?? '', 'table'); ?>><?php _e('Table', 'institute-management'); ?></option>
            </select>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="show_photos_by_default" value="1" <?php checked($settings['show_photos_by_default'] ?? true, 1); ?> />
                <?php _e('Show Photos by Default', 'institute-management'); ?>
            </label>
        </div>
        <?php
    }
    

    
    /**
     * Render template settings
     */
    private function render_template_settings($settings) {
        ?>
        <h2><?php _e('Template Settings', 'institute-management'); ?></h2>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="create_archive_pages" value="1" <?php checked($settings['create_archive_pages'] ?? false, 1); ?> />
                <?php _e('Auto-create Archive Pages', 'institute-management'); ?>
            </label>
            <div class="institute-form-description"><?php _e('Automatically create "Students" and "Staff" pages with proper templates.', 'institute-management'); ?></div>
        </div>
        
        <?php if ($settings['create_archive_pages'] ?? false): ?>
        <div class="institute-notice institute-notice-info">
            <p><?php _e('Archive pages will be created at:', 'institute-management'); ?></p>
            <ul>
                <li><strong><?php _e('Students:', 'institute-management'); ?></strong> <?php echo home_url('/students/'); ?></li>
                <li><strong><?php _e('Staff:', 'institute-management'); ?></strong> <?php echo home_url('/staff/'); ?></li>
                <li><strong><?php _e('Institute Directory:', 'institute-management'); ?></strong> <?php echo home_url('/institute-directory/'); ?></li>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="override_theme_templates" value="1" <?php checked($settings['override_theme_templates'] ?? true, 1); ?> />
                <?php _e('Override Theme Templates', 'institute-management'); ?>
            </label>
            <div class="institute-form-description"><?php _e('Use plugin templates instead of theme templates for student/staff pages.', 'institute-management'); ?></div>
        </div>
        <?php
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        // Get existing settings to preserve values from other tabs
        $settings = get_option('institute_management_settings', array());
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        
        // Only update settings for the current tab to avoid overwriting other tabs' settings
        if ($active_tab === 'general') {
            // General settings
            $settings['institute_name'] = sanitize_text_field($_POST['institute_name'] ?? '');
            $settings['institute_address'] = sanitize_textarea_field($_POST['institute_address'] ?? '');
            $settings['institute_phone'] = sanitize_text_field($_POST['institute_phone'] ?? '');
            $settings['institute_email'] = sanitize_email($_POST['institute_email'] ?? '');
        } elseif ($active_tab === 'display') {
            // Display settings
            $settings['items_per_page'] = intval($_POST['items_per_page'] ?? 20);
            $settings['default_display_style'] = sanitize_text_field($_POST['default_display_style'] ?? 'table');
            $settings['show_photos_by_default'] = isset($_POST['show_photos_by_default']) ? 1 : 0;
        } elseif ($active_tab === 'templates') {
            // Template settings
            $settings['create_archive_pages'] = isset($_POST['create_archive_pages']) ? 1 : 0;
            $settings['override_theme_templates'] = isset($_POST['override_theme_templates']) ? 1 : 0;
        }
        
        update_option('institute_management_settings', $settings);
        
        // Create pages if requested (only when on templates tab)
        if ($active_tab === 'templates' && ($settings['create_archive_pages'] ?? false)) {
            $this->create_archive_pages();
        }
        
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'institute-management') . '</p></div>';
    }
    
    /**
     * Create archive pages
     */
    private function create_archive_pages() {
        $pages = array(
            'students' => array(
                'title' => __('Students', 'institute-management'),
                'content' => '[students_list style="grid" columns="3" show_photo="yes" show_class="yes"]'
            ),
            'staff' => array(
                'title' => __('Staff', 'institute-management'),
                'content' => '[staff_list style="grid" columns="3" show_photo="yes" show_position="yes"]'
            ),
            'institute-directory' => array(
                'title' => __('Institute Directory', 'institute-management'),
                'content' => '[institute_directory show_search="yes" show_filters="yes"]'
            )
        );
        
        foreach ($pages as $slug => $page_data) {
            $existing_page = get_page_by_path($slug);
            
            if (!$existing_page) {
                $page_id = wp_insert_post(array(
                    'post_title' => $page_data['title'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $slug
                ));
                
                if ($page_id) {
                    update_post_meta($page_id, '_institute_management_page', true);
                }
            }
        }
    }
    
    /**
     * Import/Export page
     */
    public function import_export_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Import/Export Data', 'institute-management'); ?></h1>
            <div class="institute-import-export">
                <h3><?php _e('Import Data', 'institute-management'); ?></h3>
                <div class="institute-file-upload">
                    <div class="institute-upload-icon">📁</div>
                    <div class="institute-upload-text"><?php _e('Drop CSV file here or click to upload', 'institute-management'); ?></div>
                    <button type="button" class="institute-upload-button"><?php _e('Choose File', 'institute-management'); ?></button>
                </div>
                
                <hr style="margin: 40px 0;">
                
                <h3><?php _e('Export Data', 'institute-management'); ?></h3>
                <p>
                    <a href="#" class="institute-btn institute-btn-primary"><?php _e('Export Students', 'institute-management'); ?></a>
                    <a href="#" class="institute-btn institute-btn-primary"><?php _e('Export Staff', 'institute-management'); ?></a>
                    <a href="#" class="institute-btn institute-btn-secondary"><?php _e('Export All', 'institute-management'); ?></a>
                </p>
            </div>
        </div>
        <?php
    }
} 