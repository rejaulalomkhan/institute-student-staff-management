<?php
/**
 * Shortcodes class
 */

namespace Institute_Management\Public;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcodes class
 */
class Shortcodes {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_shortcodes'));
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('students_list', array($this, 'students_list_shortcode'));
        add_shortcode('staff_list', array($this, 'staff_list_shortcode'));
        add_shortcode('institute_directory', array($this, 'institute_directory_shortcode'));
        add_shortcode('student_profile', array($this, 'student_profile_shortcode'));
        add_shortcode('staff_profile', array($this, 'staff_profile_shortcode'));
        add_shortcode('institute_stats', array($this, 'institute_stats_shortcode'));
    }
    
    /**
     * Students list shortcode
     */
    public function students_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'class' => '',
            'batch' => '',
            'limit' => 12,
            'columns' => 3,
            'show_photo' => 'yes',
            'show_class' => 'yes',
            'show_session' => 'yes',
            'show_branch' => 'yes',
            'show_contact' => 'no',
            'orderby' => 'title',
            'order' => 'ASC',
            'status' => 'active',
            'style' => 'grid', // grid, list, table
        ), $atts);
        
        $args = array(
            'post_type' => 'student',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => sanitize_text_field($atts['orderby']),
            'order' => sanitize_text_field($atts['order']),
            'meta_query' => array(),
        );
        
        // Add status filter
        if (!empty($atts['status'])) {
            $args['meta_query'][] = array(
                'key' => '_student_status',
                'value' => sanitize_text_field($atts['status']),
                'compare' => '='
            );
        }
        
        // Add taxonomy filters
        $tax_query = array();
        
        if (!empty($atts['class'])) {
            $tax_query[] = array(
                'taxonomy' => 'student_class',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['class']),
            );
        }
        
        if (!empty($atts['batch'])) {
            $tax_query[] = array(
                'taxonomy' => 'student_batch',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['batch']),
            );
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        $query = new \WP_Query($args);
        
        ob_start();
        
        if ($query->have_posts()) {
            $style_class = 'institute-students-' . sanitize_html_class($atts['style']);
            $columns_class = 'institute-columns-' . intval($atts['columns']);
            
            echo '<div class="institute-students-wrapper ' . esc_attr($style_class . ' ' . $columns_class) . '">';
            
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_student_card($atts);
            }
            
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p class="institute-no-results">' . __('No students found.', 'institute-management') . '</p>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Staff list shortcode
     */
    public function staff_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'department' => '',
            'role' => '',
            'limit' => 12,
            'columns' => 3,
            'show_photo' => 'yes',
            'show_position' => 'yes',
            'show_department' => 'yes',
            'show_phone' => 'no',
            'show_email' => 'no',
            'orderby' => 'title',
            'order' => 'ASC',
            'status' => 'active',
            'style' => 'grid', // grid, list, table
        ), $atts);
        
        $args = array(
            'post_type' => 'staff',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => sanitize_text_field($atts['orderby']),
            'order' => sanitize_text_field($atts['order']),
            'meta_query' => array(),
        );
        
        // Add status filter
        if (!empty($atts['status'])) {
            $args['meta_query'][] = array(
                'key' => '_staff_status',
                'value' => sanitize_text_field($atts['status']),
                'compare' => '='
            );
        }
        
        // Add taxonomy filters
        $tax_query = array();
        
        if (!empty($atts['department'])) {
            $tax_query[] = array(
                'taxonomy' => 'staff_department',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['department']),
            );
        }
        
        if (!empty($atts['role'])) {
            $tax_query[] = array(
                'taxonomy' => 'staff_role',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['role']),
            );
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        $query = new \WP_Query($args);
        
        ob_start();
        
        if ($query->have_posts()) {
            $style_class = 'institute-staff-' . sanitize_html_class($atts['style']);
            $columns_class = 'institute-columns-' . intval($atts['columns']);
            
            echo '<div class="institute-staff-wrapper ' . esc_attr($style_class . ' ' . $columns_class) . '">';
            
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_staff_card($atts);
            }
            
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p class="institute-no-results">' . __('No staff members found.', 'institute-management') . '</p>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Institute directory shortcode
     */
    public function institute_directory_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_search' => 'yes',
            'show_filters' => 'yes',
            'show_students' => 'yes',
            'show_staff' => 'yes',
            'style' => 'tabs', // tabs, sections
        ), $atts);
        
        ob_start();
        ?>
        <div class="institute-directory-wrapper">
            <?php if ($atts['show_search'] === 'yes'): ?>
            <div class="institute-directory-search">
                <input type="text" id="institute-search" placeholder="<?php _e('Search by name...', 'institute-management'); ?>" />
                <button type="button" id="institute-search-btn"><?php _e('Search', 'institute-management'); ?></button>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['show_filters'] === 'yes'): ?>
            <div class="institute-directory-filters">
                <select id="institute-type-filter">
                    <option value=""><?php _e('All Types', 'institute-management'); ?></option>
                    <?php if ($atts['show_students'] === 'yes'): ?>
                    <option value="student"><?php _e('Students', 'institute-management'); ?></option>
                    <?php endif; ?>
                    <?php if ($atts['show_staff'] === 'yes'): ?>
                    <option value="staff"><?php _e('Staff', 'institute-management'); ?></option>
                    <?php endif; ?>
                </select>
                
                <select id="institute-class-filter">
                    <option value=""><?php _e('All Classes/Departments', 'institute-management'); ?></option>
                    <?php $this->render_filter_options(); ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="institute-directory-results" id="institute-directory-results">
                <?php $this->render_directory_content($atts); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Institute statistics shortcode
     */
    public function institute_stats_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show' => 'students,staff,classes,departments', // comma separated
            'style' => 'cards', // cards, counters, chart
        ), $atts);
        
        $show_items = array_map('trim', explode(',', $atts['show']));
        
        ob_start();
        ?>
        <div class="institute-stats-wrapper institute-stats-<?php echo esc_attr($atts['style']); ?>">
            <?php if (in_array('students', $show_items)): ?>
            <div class="institute-stat-item">
                <div class="institute-stat-number"><?php echo $this->get_students_count(); ?></div>
                <div class="institute-stat-label"><?php _e('Total Students', 'institute-management'); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if (in_array('staff', $show_items)): ?>
            <div class="institute-stat-item">
                <div class="institute-stat-number"><?php echo $this->get_staff_count(); ?></div>
                <div class="institute-stat-label"><?php _e('Total Staff', 'institute-management'); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if (in_array('classes', $show_items)): ?>
            <div class="institute-stat-item">
                <div class="institute-stat-number"><?php echo $this->get_classes_count(); ?></div>
                <div class="institute-stat-label"><?php _e('Total Classes', 'institute-management'); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if (in_array('departments', $show_items)): ?>
            <div class="institute-stat-item">
                <div class="institute-stat-number"><?php echo $this->get_departments_count(); ?></div>
                <div class="institute-stat-label"><?php _e('Total Departments', 'institute-management'); ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render student card
     */
    private function render_student_card($atts) {
        $student_id = get_post_meta(get_the_ID(), '_student_id', true);
        $role = get_post_meta(get_the_ID(), '_student_role', true);
        $session = get_post_meta(get_the_ID(), '_student_session', true);
        $branch = get_post_meta(get_the_ID(), '_student_branch', true);
        $phone = get_post_meta(get_the_ID(), '_phone', true);
        $email = get_post_meta(get_the_ID(), '_email', true);
        $classes = get_the_terms(get_the_ID(), 'student_class');
        ?>
        <div class="institute-student-card">
            <?php if ($atts['show_photo'] === 'yes'): ?>
            <div class="institute-card-photo">
                <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('medium'); ?>
                <?php else: ?>
                    <div class="institute-default-avatar">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="institute-card-content">
                <h3 class="institute-card-title"><?php the_title(); ?></h3>
                
                <?php if ($student_id): ?>
                <p class="institute-card-id"><strong><?php _e('ID:', 'institute-management'); ?></strong> <?php echo esc_html($student_id); ?></p>
                <?php endif; ?>
                
                <?php if ($atts['show_class'] === 'yes' && $classes): ?>
                <p class="institute-card-class"><strong><?php _e('Class:', 'institute-management'); ?></strong> <?php echo esc_html(wp_list_pluck($classes, 'name')[0]); ?></p>
                <?php endif; ?>
                
                <?php if ($atts['show_session'] === 'yes' && $session): ?>
                <p class="institute-card-session"><strong><?php _e('Session:', 'institute-management'); ?></strong> <?php echo esc_html($session); ?></p>
                <?php endif; ?>
                
                <?php if ($atts['show_branch'] === 'yes' && $branch): ?>
                <p class="institute-card-branch"><strong><?php _e('Branch:', 'institute-management'); ?></strong> <?php echo esc_html($branch); ?></p>
                <?php endif; ?>
                
                <?php if ($atts['show_contact'] === 'yes'): ?>
                <div class="institute-card-contact">
                    <?php if ($phone): ?>
                    <p class="institute-card-phone"><strong><?php _e('Phone:', 'institute-management'); ?></strong> <?php echo esc_html($phone); ?></p>
                    <?php endif; ?>
                    <?php if ($email): ?>
                    <p class="institute-card-email"><strong><?php _e('Email:', 'institute-management'); ?></strong> <?php echo esc_html($email); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render staff card
     */
    private function render_staff_card($atts) {
        $staff_id = get_post_meta(get_the_ID(), '_staff_id', true);
        $position = get_post_meta(get_the_ID(), '_staff_position', true);
        $phone = get_post_meta(get_the_ID(), '_staff_phone', true);
        $email = get_post_meta(get_the_ID(), '_staff_email', true);
        $departments = get_the_terms(get_the_ID(), 'staff_department');
        ?>
        <div class="institute-staff-card">
            <?php if ($atts['show_photo'] === 'yes'): ?>
            <div class="institute-card-photo">
                <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('medium'); ?>
                <?php else: ?>
                    <div class="institute-default-avatar">
                        <span class="dashicons dashicons-businessperson"></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="institute-card-content">
                <h3 class="institute-card-title"><?php the_title(); ?></h3>
                
                <?php if ($atts['show_position'] === 'yes' && $position): ?>
                <p class="institute-card-position"><strong><?php _e('Position:', 'institute-management'); ?></strong> <?php echo esc_html($position); ?></p>
                <?php endif; ?>
                
                <?php if ($atts['show_department'] === 'yes' && $departments): ?>
                <p class="institute-card-department"><strong><?php _e('Department:', 'institute-management'); ?></strong> <?php echo esc_html(wp_list_pluck($departments, 'name')[0]); ?></p>
                <?php endif; ?>
                
                <?php if ($atts['show_phone'] === 'yes' && $phone): ?>
                <p class="institute-card-phone"><strong><?php _e('Phone:', 'institute-management'); ?></strong> <?php echo esc_html($phone); ?></p>
                <?php endif; ?>
                
                <?php if ($atts['show_email'] === 'yes' && $email): ?>
                <p class="institute-card-email"><strong><?php _e('Email:', 'institute-management'); ?></strong> <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get students count
     */
    private function get_students_count() {
        return wp_count_posts('student')->publish;
    }
    
    /**
     * Get staff count
     */
    private function get_staff_count() {
        return wp_count_posts('staff')->publish;
    }
    
    /**
     * Get classes count
     */
    private function get_classes_count() {
        return wp_count_terms('student_class');
    }
    
    /**
     * Get departments count
     */
    private function get_departments_count() {
        return wp_count_terms('staff_department');
    }
    
    /**
     * Render filter options
     */
    private function render_filter_options() {
        // Get classes
        $classes = get_terms(array(
            'taxonomy' => 'student_class',
            'hide_empty' => false,
        ));
        
        if ($classes) {
            foreach ($classes as $class) {
                echo '<option value="class-' . esc_attr($class->slug) . '">' . esc_html($class->name) . ' (Class)</option>';
            }
        }
        
        // Get departments
        $departments = get_terms(array(
            'taxonomy' => 'staff_department',
            'hide_empty' => false,
        ));
        
        if ($departments) {
            foreach ($departments as $department) {
                echo '<option value="department-' . esc_attr($department->slug) . '">' . esc_html($department->name) . ' (Department)</option>';
            }
        }
    }
    
    /**
     * Render directory content
     */
    private function render_directory_content($atts) {
        if ($atts['show_students'] === 'yes') {
            echo '<h3>' . __('Students', 'institute-management') . '</h3>';
            echo do_shortcode('[students_list limit="6" style="grid" columns="3"]');
        }
        
        if ($atts['show_staff'] === 'yes') {
            echo '<h3>' . __('Staff', 'institute-management') . '</h3>';
            echo do_shortcode('[staff_list limit="6" style="grid" columns="3"]');
        }
    }
} 