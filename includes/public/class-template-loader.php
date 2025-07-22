<?php
/**
 * Template Loader class
 */

namespace Institute_Management\Public;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Loader class
 */
class Template_Loader {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_filter('template_include', array($this, 'template_loader'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_template_assets'));
        add_action('wp_ajax_institute_search', array($this, 'handle_ajax_search'));
        add_action('wp_ajax_nopriv_institute_search', array($this, 'handle_ajax_search'));
        add_action('wp_ajax_institute_filter', array($this, 'handle_ajax_filter'));
        add_action('wp_ajax_nopriv_institute_filter', array($this, 'handle_ajax_filter'));
        add_action('wp_ajax_institute_create_archive_pages', array($this, 'handle_create_archive_pages'));
        add_action('wp_ajax_institute_directory_load', array($this, 'handle_directory_load'));
        add_action('wp_ajax_nopriv_institute_directory_load', array($this, 'handle_directory_load'));
        add_action('wp_ajax_institute_directory_filter', array($this, 'handle_directory_filter'));
        add_action('wp_ajax_nopriv_institute_directory_filter', array($this, 'handle_directory_filter'));
    }
    
    /**
     * Load custom templates
     */
    public function template_loader($template) {
        $settings = get_option('institute_management_settings', array());
        
        // Only override if setting is enabled
        if (!($settings['override_theme_templates'] ?? true)) {
            return $template;
        }
        
        $custom_template = '';
        
        // Check for Institute Directory page
        if (is_page() && $this->is_institute_directory_page()) {
            $custom_template = $this->locate_template('page-institute-directory.php');
        } elseif (is_singular('student')) {
            $custom_template = $this->locate_template('single-student.php');
        } elseif (is_singular('staff')) {
            $custom_template = $this->locate_template('single-staff.php');
        } elseif (is_post_type_archive('student')) {
            $custom_template = $this->locate_template('archive-students.php');
        } elseif (is_post_type_archive('staff')) {
            $custom_template = $this->locate_template('archive-staff.php');
        } elseif (is_tax(array('student_class', 'staff_department', 'student_batch', 'staff_role'))) {
            $custom_template = $this->locate_template('taxonomy-institute.php');
        }
        
        return $custom_template ? $custom_template : $template;
    }
    
    /**
     * Locate template file
     */
    private function locate_template($template_name) {
        // Check if theme has override
        $theme_template = locate_template(array(
            'institute-management/' . $template_name,
            $template_name
        ));
        
        if ($theme_template) {
            return $theme_template;
        }
        
        // Use plugin template
        $plugin_template = INSTITUTE_MANAGEMENT_PLUGIN_DIR . 'templates/' . $template_name;
        
        return file_exists($plugin_template) ? $plugin_template : '';
    }
    
    /**
     * Enqueue template assets
     */
    public function enqueue_template_assets() {
        if ($this->is_institute_page()) {
            wp_enqueue_style('institute-template-css', INSTITUTE_MANAGEMENT_PLUGIN_URL . 'assets/css/templates.css', array(), INSTITUTE_MANAGEMENT_VERSION);
            wp_enqueue_script('institute-template-js', INSTITUTE_MANAGEMENT_PLUGIN_URL . 'assets/js/templates.js', array('jquery'), INSTITUTE_MANAGEMENT_VERSION, true);
            
            wp_localize_script('institute-template-js', 'institute_templates', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('institute_templates_nonce'),
                'strings' => array(
                    'loading' => __('Loading...', 'institute-management'),
                    'no_results' => __('No results found.', 'institute-management'),
                    'error' => __('An error occurred. Please try again.', 'institute-management'),
                )
            ));
        }
    }
    
    /**
     * Check if current page is institute-related
     */
    private function is_institute_page() {
        return is_singular(array('student', 'staff')) ||
               is_post_type_archive(array('student', 'staff')) ||
               is_tax(array('student_class', 'staff_department', 'student_batch', 'staff_role')) ||
               $this->is_institute_directory_page();
    }
    
    /**
     * Check if current page is institute directory
     */
    private function is_institute_directory_page() {
        global $post;
        return $post && has_shortcode($post->post_content, 'institute_directory');
    }
    
    /**
     * Handle AJAX search
     */
    public function handle_ajax_search() {
        check_ajax_referer('institute_management_nonce', 'nonce');
        
        $query = sanitize_text_field($_POST['query'] ?? '');
        
        if (empty($query)) {
            wp_send_json_error('Empty search query');
        }
        
        $results = $this->search_institute_members($query);
        
        ob_start();
        $this->render_search_results($results);
        $html = ob_get_clean();
        
        wp_send_json_success($html);
    }
    
    /**
     * Handle AJAX filter
     */
    public function handle_ajax_filter() {
        check_ajax_referer('institute_management_nonce', 'nonce');
        
        $type = sanitize_text_field($_POST['type'] ?? '');
        $class_dept = sanitize_text_field($_POST['class'] ?? '');
        
        $results = $this->filter_institute_members($type, $class_dept);
        
        ob_start();
        $this->render_search_results($results);
        $html = ob_get_clean();
        
        wp_send_json_success($html);
    }
    
    /**
     * Search institute members
     */
    private function search_institute_members($query) {
        $results = array();
        
        // Search students
        $student_args = array(
            'post_type' => 'student',
            'posts_per_page' => 20,
            's' => $query,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_student_id',
                    'value' => $query,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_phone',
                    'value' => $query,
                    'compare' => 'LIKE'
                )
            )
        );
        
        $students = get_posts($student_args);
        foreach ($students as $student) {
            $results[] = array(
                'type' => 'student',
                'post' => $student
            );
        }
        
        // Search staff
        $staff_args = array(
            'post_type' => 'staff',
            'posts_per_page' => 20,
            's' => $query,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_staff_id',
                    'value' => $query,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_staff_phone',
                    'value' => $query,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_staff_email',
                    'value' => $query,
                    'compare' => 'LIKE'
                )
            )
        );
        
        $staff = get_posts($staff_args);
        foreach ($staff as $staff_member) {
            $results[] = array(
                'type' => 'staff',
                'post' => $staff_member
            );
        }
        
        return $results;
    }
    
    /**
     * Filter institute members
     */
    private function filter_institute_members($type, $class_dept) {
        $results = array();
        
        if ($type === 'student' || empty($type)) {
            $student_args = array(
                'post_type' => 'student',
                'posts_per_page' => 20
            );
            
            if (!empty($class_dept) && strpos($class_dept, 'class-') === 0) {
                $class_slug = str_replace('class-', '', $class_dept);
                $student_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'student_class',
                        'field' => 'slug',
                        'terms' => $class_slug
                    )
                );
            }
            
            $students = get_posts($student_args);
            foreach ($students as $student) {
                $results[] = array(
                    'type' => 'student',
                    'post' => $student
                );
            }
        }
        
        if ($type === 'staff' || empty($type)) {
            $staff_args = array(
                'post_type' => 'staff',
                'posts_per_page' => 20
            );
            
            if (!empty($class_dept) && strpos($class_dept, 'department-') === 0) {
                $dept_slug = str_replace('department-', '', $class_dept);
                $staff_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'staff_department',
                        'field' => 'slug',
                        'terms' => $dept_slug
                    )
                );
            }
            
            $staff = get_posts($staff_args);
            foreach ($staff as $staff_member) {
                $results[] = array(
                    'type' => 'staff',
                    'post' => $staff_member
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Render search results
     */
    private function render_search_results($results) {
        $settings = get_option('institute_management_settings', array());
        $style = $settings['default_display_style'] ?? 'grid';
        $columns = $settings['default_grid_columns'] ?? 3;
        
        if (empty($results)) {
            echo '<p class="institute-no-results">' . __('No results found.', 'institute-management') . '</p>';
            return;
        }
        
        echo '<div class="institute-search-results institute-' . esc_attr($style) . ' institute-columns-' . esc_attr($columns) . '">';
        
        foreach ($results as $result) {
            $post = $result['post'];
            $type = $result['type'];
            
            echo '<div class="institute-' . esc_attr($type) . '-card">';
            
            // Photo
            if (has_post_thumbnail($post->ID)) {
                echo '<div class="institute-card-photo">';
                echo get_the_post_thumbnail($post->ID, 'medium');
                echo '</div>';
            } else {
                echo '<div class="institute-card-photo">';
                echo '<div class="institute-default-avatar">';
                echo '<span class="dashicons dashicons-' . ($type === 'student' ? 'admin-users' : 'businessperson') . '"></span>';
                echo '</div>';
                echo '</div>';
            }
            
            // Content
            echo '<div class="institute-card-content">';
            echo '<h3 class="institute-card-title">' . esc_html($post->post_title) . '</h3>';
            
            if ($type === 'student') {
                $student_id = get_post_meta($post->ID, '_student_id', true);
                $class_terms = get_the_terms($post->ID, 'student_class');
                
                if ($student_id) {
                    echo '<p class="institute-card-id"><strong>' . __('ID:', 'institute-management') . '</strong> ' . esc_html($student_id) . '</p>';
                }
                
                if ($class_terms && !is_wp_error($class_terms)) {
                    echo '<p class="institute-card-class"><strong>' . __('Class:', 'institute-management') . '</strong> ' . esc_html($class_terms[0]->name) . '</p>';
                }
            } else {
                $staff_id = get_post_meta($post->ID, '_staff_id', true);
                $position = get_post_meta($post->ID, '_staff_position', true);
                $dept_terms = get_the_terms($post->ID, 'staff_department');
                
                if ($staff_id) {
                    echo '<p class="institute-card-id"><strong>' . __('ID:', 'institute-management') . '</strong> ' . esc_html($staff_id) . '</p>';
                }
                
                if ($position) {
                    echo '<p class="institute-card-position"><strong>' . __('Position:', 'institute-management') . '</strong> ' . esc_html($position) . '</p>';
                }
                
                if ($dept_terms && !is_wp_error($dept_terms)) {
                    echo '<p class="institute-card-department"><strong>' . __('Department:', 'institute-management') . '</strong> ' . esc_html($dept_terms[0]->name) . '</p>';
                }
            }
            
            // View profile link
            echo '<p class="institute-card-link">';
            echo '<a href="' . get_permalink($post->ID) . '" class="institute-btn institute-btn-primary">' . __('View Profile', 'institute-management') . '</a>';
            echo '</p>';
            
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Handle create archive pages AJAX
     */
    public function handle_create_archive_pages() {
        check_ajax_referer('institute_create_pages', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $pages_created = 0;
        
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
                    $pages_created++;
                }
            } else {
                // Update existing page
                wp_update_post(array(
                    'ID' => $existing_page->ID,
                    'post_content' => $page_data['content']
                ));
                $pages_created++;
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('%d pages created/updated successfully!', 'institute-management'), $pages_created)
        ));
    }
    
    /**
     * Handle directory load AJAX
     */
    public function handle_directory_load() {
        check_ajax_referer('institute_management_nonce', 'nonce');
        
        $settings = get_option('institute_management_settings', array());
        $style = $settings['default_display_style'] ?? 'grid';
        $columns = $settings['default_grid_columns'] ?? 3;
        
        // Get students and staff
        $students = get_posts(array(
            'post_type' => 'student',
            'posts_per_page' => 20,
            'post_status' => 'publish'
        ));
        
        $staff = get_posts(array(
            'post_type' => 'staff',
            'posts_per_page' => 20,
            'post_status' => 'publish'
        ));
        
        $results = array();
        foreach ($students as $student) {
            $results[] = array('type' => 'student', 'post' => $student);
        }
        foreach ($staff as $staff_member) {
            $results[] = array('type' => 'staff', 'post' => $staff_member);
        }
        
        ob_start();
        $this->render_directory_results($results, $style, $columns);
        $html = ob_get_clean();
        
        wp_send_json_success($html);
    }
    
    /**
     * Handle directory filter AJAX
     */
    public function handle_directory_filter() {
        check_ajax_referer('institute_management_nonce', 'nonce');
        
        $type = sanitize_text_field($_POST['type'] ?? '');
        $class_dept = sanitize_text_field($_POST['class'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        $sort = sanitize_text_field($_POST['sort'] ?? 'name-asc');
        $search = sanitize_text_field($_POST['search'] ?? '');
        
        $settings = get_option('institute_management_settings', array());
        $style = $settings['default_display_style'] ?? 'grid';
        $columns = $settings['default_grid_columns'] ?? 3;
        
        $results = $this->get_filtered_directory_results($type, $class_dept, $status, $sort, $search);
        
        ob_start();
        $this->render_directory_results($results, $style, $columns);
        $html = ob_get_clean();
        
        wp_send_json_success($html);
    }
    
    /**
     * Get filtered directory results
     */
    private function get_filtered_directory_results($type, $class_dept, $status, $sort, $search) {
        $results = array();
        
        // Parse sort
        $sort_parts = explode('-', $sort);
        $orderby = $sort_parts[0] ?? 'title';
        $order = $sort_parts[1] ?? 'ASC';
        
        if ($orderby === 'name') $orderby = 'title';
        
        $base_args = array(
            'posts_per_page' => 50,
            'post_status' => 'publish',
            'orderby' => $orderby,
            'order' => strtoupper($order)
        );
        
        if ($search) {
            $base_args['s'] = $search;
        }
        
        if ($status) {
            $base_args['meta_query'] = array(
                array(
                    'key' => '_student_status',
                    'value' => $status,
                    'compare' => '='
                )
            );
        }
        
        // Get students
        if ($type === '' || $type === 'student') {
            $student_args = array_merge($base_args, array('post_type' => 'student'));
            
            if ($class_dept && strpos($class_dept, 'class-') === 0) {
                $class_slug = str_replace('class-', '', $class_dept);
                $student_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'student_class',
                        'field' => 'slug',
                        'terms' => $class_slug
                    )
                );
            }
            
            $students = get_posts($student_args);
            foreach ($students as $student) {
                $results[] = array('type' => 'student', 'post' => $student);
            }
        }
        
        // Get staff
        if ($type === '' || $type === 'staff') {
            $staff_args = array_merge($base_args, array('post_type' => 'staff'));
            
            if ($status) {
                $staff_args['meta_query'] = array(
                    array(
                        'key' => '_staff_status',
                        'value' => $status,
                        'compare' => '='
                    )
                );
            }
            
            if ($class_dept && strpos($class_dept, 'department-') === 0) {
                $dept_slug = str_replace('department-', '', $class_dept);
                $staff_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'staff_department',
                        'field' => 'slug',
                        'terms' => $dept_slug
                    )
                );
            }
            
            $staff = get_posts($staff_args);
            foreach ($staff as $staff_member) {
                $results[] = array('type' => 'staff', 'post' => $staff_member);
            }
        }
        
        return $results;
    }
    
    /**
     * Render directory results
     */
    private function render_directory_results($results, $style = 'grid', $columns = 3) {
        if (empty($results)) {
            echo '<div class="institute-no-results">';
            echo '<div class="no-results-icon"><span class="dashicons dashicons-search"></span></div>';
            echo '<h3>' . __('No results found', 'institute-management') . '</h3>';
            echo '<p>' . __('Try adjusting your search or filter criteria.', 'institute-management') . '</p>';
            echo '</div>';
            return;
        }
        
        echo '<div class="institute-directory-grid institute-' . esc_attr($style) . ' institute-columns-' . esc_attr($columns) . '">';
        
        foreach ($results as $result) {
            $post = $result['post'];
            $type = $result['type'];
            
            echo '<div class="institute-directory-card institute-' . esc_attr($type) . '-card">';
            
            // Photo
            echo '<div class="institute-card-photo">';
            if (has_post_thumbnail($post->ID)) {
                echo '<a href="' . get_permalink($post->ID) . '">';
                echo get_the_post_thumbnail($post->ID, 'medium');
                echo '</a>';
            } else {
                echo '<div class="institute-default-avatar">';
                echo '<span class="dashicons dashicons-' . ($type === 'student' ? 'admin-users' : 'businessperson') . '"></span>';
                echo '</div>';
            }
            
            // Status badge
            $status = get_post_meta($post->ID, '_' . $type . '_status', true);
            if ($status) {
                echo '<span class="institute-status-badge institute-status-' . esc_attr($status) . '">';
                echo esc_html(ucfirst($status));
                echo '</span>';
            }
            echo '</div>';
            
            // Content
            echo '<div class="institute-card-content">';
            echo '<h3 class="institute-card-title">';
            echo '<a href="' . get_permalink($post->ID) . '">' . esc_html($post->post_title) . '</a>';
            echo '</h3>';
            
            if ($type === 'student') {
                $student_id = get_post_meta($post->ID, '_student_id', true);
                $class_terms = get_the_terms($post->ID, 'student_class');
                
                if ($student_id) {
                    echo '<p class="institute-card-id"><strong>' . __('ID:', 'institute-management') . '</strong> ' . esc_html($student_id) . '</p>';
                }
                
                if ($class_terms && !is_wp_error($class_terms)) {
                    echo '<p class="institute-card-class"><strong>' . __('Class:', 'institute-management') . '</strong> ' . esc_html($class_terms[0]->name) . '</p>';
                }
            } else {
                $staff_id = get_post_meta($post->ID, '_staff_id', true);
                $position = get_post_meta($post->ID, '_staff_position', true);
                $dept_terms = get_the_terms($post->ID, 'staff_department');
                
                if ($staff_id) {
                    echo '<p class="institute-card-id"><strong>' . __('ID:', 'institute-management') . '</strong> ' . esc_html($staff_id) . '</p>';
                }
                
                if ($position) {
                    echo '<p class="institute-card-position"><strong>' . __('Position:', 'institute-management') . '</strong> ' . esc_html($position) . '</p>';
                }
                
                if ($dept_terms && !is_wp_error($dept_terms)) {
                    echo '<p class="institute-card-department"><strong>' . __('Department:', 'institute-management') . '</strong> ' . esc_html($dept_terms[0]->name) . '</p>';
                }
            }
            
            echo '<div class="institute-card-actions">';
            echo '<a href="' . get_permalink($post->ID) . '" class="institute-btn institute-btn-primary">' . __('View Profile', 'institute-management') . '</a>';
            echo '</div>';
            
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
} 