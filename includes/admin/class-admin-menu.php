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
                    <li><a href="?page=institute-management-settings&tab=notifications" class="<?php echo $active_tab === 'notifications' ? 'nav-tab-active' : ''; ?>"><?php _e('Notifications', 'institute-management'); ?></a></li>
                    <li><a href="?page=institute-management-settings&tab=permissions" class="<?php echo $active_tab === 'permissions' ? 'nav-tab-active' : ''; ?>"><?php _e('Permissions', 'institute-management'); ?></a></li>
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
                        case 'notifications':
                            $this->render_notification_settings($settings);
                            break;
                        case 'permissions':
                            $this->render_permission_settings($settings);
                            break;
                        case 'templates':
                            $this->render_template_settings($settings);
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
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Academic Year Format', 'institute-management'); ?></label>
            <select name="academic_year_format" class="institute-form-input">
                <option value="YYYY-YYYY" <?php selected($settings['academic_year_format'] ?? '', 'YYYY-YYYY'); ?>><?php _e('2023-2024', 'institute-management'); ?></option>
                <option value="YYYY" <?php selected($settings['academic_year_format'] ?? '', 'YYYY'); ?>><?php _e('2024', 'institute-management'); ?></option>
            </select>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Default Student Status', 'institute-management'); ?></label>
            <select name="default_student_status" class="institute-form-input">
                <option value="active" <?php selected($settings['default_student_status'] ?? '', 'active'); ?>><?php _e('Active', 'institute-management'); ?></option>
                <option value="pending" <?php selected($settings['default_student_status'] ?? '', 'pending'); ?>><?php _e('Pending', 'institute-management'); ?></option>
            </select>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="auto_generate_ids" value="1" <?php checked($settings['auto_generate_ids'] ?? false, 1); ?> />
                <?php _e('Auto-generate Student/Staff IDs', 'institute-management'); ?>
            </label>
            <div class="institute-form-description"><?php _e('Automatically generate unique IDs for new students and staff.', 'institute-management'); ?></div>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('ID Format', 'institute-management'); ?></label>
            <input type="text" name="id_format" value="<?php echo esc_attr($settings['id_format'] ?? 'STU-{YYYY}-{###}'); ?>" class="institute-form-input" />
            <div class="institute-form-description"><?php _e('Use {YYYY} for year, {###} for sequential number. Example: STU-{YYYY}-{###}', 'institute-management'); ?></div>
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
            <label class="institute-form-label"><?php _e('Default Grid Columns', 'institute-management'); ?></label>
            <select name="default_grid_columns" class="institute-form-input">
                <option value="2" <?php selected($settings['default_grid_columns'] ?? '', '2'); ?>>2</option>
                <option value="3" <?php selected($settings['default_grid_columns'] ?? '', '3'); ?>>3</option>
                <option value="4" <?php selected($settings['default_grid_columns'] ?? '', '4'); ?>>4</option>
            </select>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="show_photos_by_default" value="1" <?php checked($settings['show_photos_by_default'] ?? true, 1); ?> />
                <?php _e('Show Photos by Default', 'institute-management'); ?>
            </label>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="enable_public_profiles" value="1" <?php checked($settings['enable_public_profiles'] ?? true, 1); ?> />
                <?php _e('Enable Public Profiles', 'institute-management'); ?>
            </label>
            <div class="institute-form-description"><?php _e('Allow public access to individual student/staff profile pages.', 'institute-management'); ?></div>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="enable_search" value="1" <?php checked($settings['enable_search'] ?? true, 1); ?> />
                <?php _e('Enable Search Functionality', 'institute-management'); ?>
            </label>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="enable_filters" value="1" <?php checked($settings['enable_filters'] ?? true, 1); ?> />
                <?php _e('Enable Filter System', 'institute-management'); ?>
            </label>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Date Format', 'institute-management'); ?></label>
            <select name="date_format" class="institute-form-input">
                <option value="Y-m-d" <?php selected($settings['date_format'] ?? '', 'Y-m-d'); ?>>YYYY-MM-DD</option>
                <option value="d/m/Y" <?php selected($settings['date_format'] ?? '', 'd/m/Y'); ?>>DD/MM/YYYY</option>
                <option value="m/d/Y" <?php selected($settings['date_format'] ?? '', 'm/d/Y'); ?>>MM/DD/YYYY</option>
            </select>
        </div>
        <?php
    }
    
    /**
     * Render notification settings
     */
    private function render_notification_settings($settings) {
        ?>
        <h2><?php _e('Notification Settings', 'institute-management'); ?></h2>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="enable_notifications" value="1" <?php checked($settings['enable_notifications'] ?? false, 1); ?> />
                <?php _e('Enable Email Notifications', 'institute-management'); ?>
            </label>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Admin Email for Notifications', 'institute-management'); ?></label>
            <input type="email" name="admin_notification_email" value="<?php echo esc_attr($settings['admin_notification_email'] ?? get_option('admin_email')); ?>" class="institute-form-input" />
        </div>
        
        <h3><?php _e('Email Notification Types', 'institute-management'); ?></h3>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="notify_new_student" value="1" <?php checked($settings['notify_new_student'] ?? true, 1); ?> />
                <?php _e('New Student Registration', 'institute-management'); ?>
            </label>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="notify_new_staff" value="1" <?php checked($settings['notify_new_staff'] ?? true, 1); ?> />
                <?php _e('New Staff Addition', 'institute-management'); ?>
            </label>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="notify_status_change" value="1" <?php checked($settings['notify_status_change'] ?? false, 1); ?> />
                <?php _e('Status Changes', 'institute-management'); ?>
            </label>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="notify_profile_updates" value="1" <?php checked($settings['notify_profile_updates'] ?? false, 1); ?> />
                <?php _e('Profile Updates', 'institute-management'); ?>
            </label>
        </div>
        
        <h3><?php _e('Email Templates', 'institute-management'); ?></h3>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('New Student Email Subject', 'institute-management'); ?></label>
            <input type="text" name="new_student_email_subject" value="<?php echo esc_attr($settings['new_student_email_subject'] ?? 'New Student Registration'); ?>" class="institute-form-input" />
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('New Student Email Body', 'institute-management'); ?></label>
            <textarea name="new_student_email_body" rows="5" class="institute-form-input"><?php echo esc_textarea($settings['new_student_email_body'] ?? 'A new student {student_name} has been registered.'); ?></textarea>
            <div class="institute-form-description"><?php _e('Available placeholders: {student_name}, {student_id}, {class}, {institute_name}', 'institute-management'); ?></div>
        </div>
        <?php
    }
    
    /**
     * Render permission settings
     */
    private function render_permission_settings($settings) {
        ?>
        <h2><?php _e('Permission Settings', 'institute-management'); ?></h2>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Who can manage students?', 'institute-management'); ?></label>
            <select name="student_management_capability" class="institute-form-input">
                <option value="manage_options" <?php selected($settings['student_management_capability'] ?? '', 'manage_options'); ?>><?php _e('Administrators only', 'institute-management'); ?></option>
                <option value="edit_others_posts" <?php selected($settings['student_management_capability'] ?? '', 'edit_others_posts'); ?>><?php _e('Editors and above', 'institute-management'); ?></option>
                <option value="edit_posts" <?php selected($settings['student_management_capability'] ?? '', 'edit_posts'); ?>><?php _e('Authors and above', 'institute-management'); ?></option>
            </select>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Who can manage staff?', 'institute-management'); ?></label>
            <select name="staff_management_capability" class="institute-form-input">
                <option value="manage_options" <?php selected($settings['staff_management_capability'] ?? '', 'manage_options'); ?>><?php _e('Administrators only', 'institute-management'); ?></option>
                <option value="edit_others_posts" <?php selected($settings['staff_management_capability'] ?? '', 'edit_others_posts'); ?>><?php _e('Editors and above', 'institute-management'); ?></option>
            </select>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="allow_frontend_submission" value="1" <?php checked($settings['allow_frontend_submission'] ?? false, 1); ?> />
                <?php _e('Allow Frontend Submissions', 'institute-management'); ?>
            </label>
            <div class="institute-form-description"><?php _e('Allow users to submit student/staff information from frontend forms.', 'institute-management'); ?></div>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="require_approval" value="1" <?php checked($settings['require_approval'] ?? true, 1); ?> />
                <?php _e('Require Admin Approval', 'institute-management'); ?>
            </label>
            <div class="institute-form-description"><?php _e('Frontend submissions require admin approval before publication.', 'institute-management'); ?></div>
        </div>
        
        <h3><?php _e('Personal Information Privacy', 'institute-management'); ?></h3>
        <p class="institute-form-description"><?php _e('Control which personal information fields are visible to public (non-logged-in) users on student profile pages.', 'institute-management'); ?></p>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Personal Information Visible to Public:', 'institute-management'); ?></label>
            <div class="institute-checkbox-group">
                <label class="institute-checkbox-item">
                    <input type="checkbox" name="public_fields[]" value="phone" <?php checked(in_array('phone', $settings['public_fields'] ?? array()), true); ?> />
                    <?php _e('Phone Number', 'institute-management'); ?>
                </label>
                <label class="institute-checkbox-item">
                    <input type="checkbox" name="public_fields[]" value="email" <?php checked(in_array('email', $settings['public_fields'] ?? array()), true); ?> />
                    <?php _e('Email Address', 'institute-management'); ?>
                </label>
                <label class="institute-checkbox-item">
                    <input type="checkbox" name="public_fields[]" value="dob" <?php checked(in_array('dob', $settings['public_fields'] ?? array()), true); ?> />
                    <?php _e('Date of Birth', 'institute-management'); ?>
                </label>
                <label class="institute-checkbox-item">
                    <input type="checkbox" name="public_fields[]" value="gender" <?php checked(in_array('gender', $settings['public_fields'] ?? array()), true); ?> />
                    <?php _e('Gender', 'institute-management'); ?>
                </label>
                <label class="institute-checkbox-item">
                    <input type="checkbox" name="public_fields[]" value="address" <?php checked(in_array('address', $settings['public_fields'] ?? array()), true); ?> />
                    <?php _e('Address', 'institute-management'); ?>
                </label>
                <label class="institute-checkbox-item">
                    <input type="checkbox" name="public_fields[]" value="blood_group" <?php checked(in_array('blood_group', $settings['public_fields'] ?? array()), true); ?> />
                    <?php _e('Blood Group', 'institute-management'); ?>
                </label>
                <label class="institute-checkbox-item">
                    <input type="checkbox" name="public_fields[]" value="religion" <?php checked(in_array('religion', $settings['public_fields'] ?? array()), true); ?> />
                    <?php _e('Religion', 'institute-management'); ?>
                </label>
                <label class="institute-checkbox-item">
                    <input type="checkbox" name="public_fields[]" value="nationality" <?php checked(in_array('nationality', $settings['public_fields'] ?? array()), true); ?> />
                    <?php _e('Nationality', 'institute-management'); ?>
                </label>
                <label class="institute-checkbox-item">
                    <input type="checkbox" name="public_fields[]" value="father_name" <?php checked(in_array('father_name', $settings['public_fields'] ?? array()), true); ?> />
                    <?php _e('Father\'s Name', 'institute-management'); ?>
                </label>
                <label class="institute-checkbox-item">
                    <input type="checkbox" name="public_fields[]" value="mother_name" <?php checked(in_array('mother_name', $settings['public_fields'] ?? array()), true); ?> />
                    <?php _e('Mother\'s Name', 'institute-management'); ?>
                </label>
            </div>
            <div class="institute-form-description"><?php _e('Unchecked fields will only be visible to logged-in users. Guardian information is always private.', 'institute-management'); ?></div>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="show_privacy_notice" value="1" <?php checked($settings['show_privacy_notice'] ?? true, 1); ?> />
                <?php _e('Show Privacy Notice', 'institute-management'); ?>
            </label>
            <div class="institute-form-description"><?php _e('Display a notice when some personal information is hidden for privacy.', 'institute-management'); ?></div>
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
            <label class="institute-form-label"><?php _e('Archive Page Template', 'institute-management'); ?></label>
            <select name="archive_template_style" class="institute-form-input">
                <option value="modern" <?php selected($settings['archive_template_style'] ?? '', 'modern'); ?>><?php _e('Modern Grid Layout', 'institute-management'); ?></option>
                <option value="classic" <?php selected($settings['archive_template_style'] ?? '', 'classic'); ?>><?php _e('Classic List Layout', 'institute-management'); ?></option>
                <option value="cards" <?php selected($settings['archive_template_style'] ?? '', 'cards'); ?>><?php _e('Card Layout', 'institute-management'); ?></option>
            </select>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label">
                <input type="checkbox" name="override_theme_templates" value="1" <?php checked($settings['override_theme_templates'] ?? true, 1); ?> />
                <?php _e('Override Theme Templates', 'institute-management'); ?>
            </label>
            <div class="institute-form-description"><?php _e('Use plugin templates instead of theme templates for student/staff pages.', 'institute-management'); ?></div>
        </div>
        
        <div class="institute-form-group">
            <label class="institute-form-label"><?php _e('Custom CSS', 'institute-management'); ?></label>
            <textarea name="custom_css" rows="10" class="institute-form-input" style="font-family: monospace;"><?php echo esc_textarea($settings['custom_css'] ?? ''); ?></textarea>
            <div class="institute-form-description"><?php _e('Add custom CSS to style the frontend display.', 'institute-management'); ?></div>
        </div>
        
        <h3><?php _e('Template Actions', 'institute-management'); ?></h3>
        
        <p>
            <button type="button" class="institute-btn institute-btn-secondary" onclick="createArchivePages()">
                <?php _e('Create Archive Pages Now', 'institute-management'); ?>
            </button>
            <button type="button" class="institute-btn institute-btn-secondary" onclick="regenerateTemplates()">
                <?php _e('Regenerate Templates', 'institute-management'); ?>
            </button>
        </p>
        
        <script>
        function createArchivePages() {
            if (confirm('<?php _e('This will create/update archive pages. Continue?', 'institute-management'); ?>')) {
                // AJAX call to create pages
                jQuery.post(ajaxurl, {
                    action: 'institute_create_archive_pages',
                    nonce: '<?php echo wp_create_nonce('institute_create_pages'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('<?php _e('Archive pages created successfully!', 'institute-management'); ?>');
                        location.reload();
                    } else {
                        alert('<?php _e('Error creating pages: ', 'institute-management'); ?>' + response.data);
                    }
                });
            }
        }
        
        function regenerateTemplates() {
            if (confirm('<?php _e('This will regenerate all templates. Continue?', 'institute-management'); ?>')) {
                // AJAX call to regenerate templates
                jQuery.post(ajaxurl, {
                    action: 'institute_regenerate_templates',
                    nonce: '<?php echo wp_create_nonce('institute_regenerate_templates'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('<?php _e('Templates regenerated successfully!', 'institute-management'); ?>');
                    } else {
                        alert('<?php _e('Error regenerating templates: ', 'institute-management'); ?>' + response.data);
                    }
                });
            }
        }
        </script>
        <?php
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        $settings = array();
        
        // General settings
        $settings['institute_name'] = sanitize_text_field($_POST['institute_name'] ?? '');
        $settings['institute_address'] = sanitize_textarea_field($_POST['institute_address'] ?? '');
        $settings['institute_phone'] = sanitize_text_field($_POST['institute_phone'] ?? '');
        $settings['institute_email'] = sanitize_email($_POST['institute_email'] ?? '');
        $settings['academic_year_format'] = sanitize_text_field($_POST['academic_year_format'] ?? 'YYYY-YYYY');
        $settings['default_student_status'] = sanitize_text_field($_POST['default_student_status'] ?? 'active');
        $settings['auto_generate_ids'] = isset($_POST['auto_generate_ids']) ? 1 : 0;
        $settings['id_format'] = sanitize_text_field($_POST['id_format'] ?? 'STU-{YYYY}-{###}');
        
        // Display settings
        $settings['items_per_page'] = intval($_POST['items_per_page'] ?? 20);
        $settings['default_display_style'] = sanitize_text_field($_POST['default_display_style'] ?? 'grid');
        $settings['default_grid_columns'] = intval($_POST['default_grid_columns'] ?? 3);
        $settings['show_photos_by_default'] = isset($_POST['show_photos_by_default']) ? 1 : 0;
        $settings['enable_public_profiles'] = isset($_POST['enable_public_profiles']) ? 1 : 0;
        $settings['enable_search'] = isset($_POST['enable_search']) ? 1 : 0;
        $settings['enable_filters'] = isset($_POST['enable_filters']) ? 1 : 0;
        $settings['date_format'] = sanitize_text_field($_POST['date_format'] ?? 'Y-m-d');
        
        // Notification settings
        $settings['enable_notifications'] = isset($_POST['enable_notifications']) ? 1 : 0;
        $settings['admin_notification_email'] = sanitize_email($_POST['admin_notification_email'] ?? '');
        $settings['notify_new_student'] = isset($_POST['notify_new_student']) ? 1 : 0;
        $settings['notify_new_staff'] = isset($_POST['notify_new_staff']) ? 1 : 0;
        $settings['notify_status_change'] = isset($_POST['notify_status_change']) ? 1 : 0;
        $settings['notify_profile_updates'] = isset($_POST['notify_profile_updates']) ? 1 : 0;
        $settings['new_student_email_subject'] = sanitize_text_field($_POST['new_student_email_subject'] ?? '');
        $settings['new_student_email_body'] = sanitize_textarea_field($_POST['new_student_email_body'] ?? '');
        
        // Permission settings
        $settings['student_management_capability'] = sanitize_text_field($_POST['student_management_capability'] ?? 'manage_options');
        $settings['staff_management_capability'] = sanitize_text_field($_POST['staff_management_capability'] ?? 'manage_options');
        $settings['allow_frontend_submission'] = isset($_POST['allow_frontend_submission']) ? 1 : 0;
        $settings['require_approval'] = isset($_POST['require_approval']) ? 1 : 0;
        
        // Privacy settings
        $public_fields = $_POST['public_fields'] ?? array();
        $allowed_fields = array('phone', 'email', 'dob', 'gender', 'address', 'blood_group', 'religion', 'nationality', 'father_name', 'mother_name');
        $settings['public_fields'] = array_intersect($public_fields, $allowed_fields);
        $settings['show_privacy_notice'] = isset($_POST['show_privacy_notice']) ? 1 : 0;
        
        // Template settings
        $settings['create_archive_pages'] = isset($_POST['create_archive_pages']) ? 1 : 0;
        $settings['archive_template_style'] = sanitize_text_field($_POST['archive_template_style'] ?? 'modern');
        $settings['override_theme_templates'] = isset($_POST['override_theme_templates']) ? 1 : 0;
        $settings['custom_css'] = sanitize_textarea_field($_POST['custom_css'] ?? '');
        
        update_option('institute_management_settings', $settings);
        
        // Create pages if requested
        if ($settings['create_archive_pages']) {
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