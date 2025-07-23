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
        add_action('wp_ajax_institute_students_top_filter', array($this, 'handle_students_top_filter'));
        add_action('wp_ajax_nopriv_institute_students_top_filter', array($this, 'handle_students_top_filter'));
        add_action('wp_ajax_institute_staff_top_filter', array($this, 'handle_staff_top_filter'));
        add_action('wp_ajax_nopriv_institute_staff_top_filter', array($this, 'handle_staff_top_filter'));
        add_action('wp_ajax_institute_staff_role_filter', array($this, 'handle_staff_role_filter'));
        add_action('wp_ajax_nopriv_institute_staff_role_filter', array($this, 'handle_staff_role_filter'));
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
        } elseif (is_tax('staff_role') || get_query_var('staff_role')) {
            $custom_template = $this->locate_template('taxonomy-staff_role.php');
        } elseif (is_tax(array('student_class', 'staff_department', 'student_batch'))) {
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
            // Enqueue Dashicons for frontend use (icons)
            wp_enqueue_style('dashicons');
            
            wp_enqueue_style('institute-template-css', INSTITUTE_MANAGEMENT_PLUGIN_URL . 'assets/css/templates.css', array('dashicons'), INSTITUTE_MANAGEMENT_VERSION);
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
        // Don't check nonce for initial load to avoid issues
        // check_ajax_referer('institute_management_nonce', 'nonce');
        
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
        // Don't check nonce for public directory access
        // check_ajax_referer('institute_management_nonce', 'nonce');
        
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
    
    /**
     * Handle students top filter AJAX request
     */
    public function handle_students_top_filter() {
        // Don't check nonce for public access
        // check_ajax_referer('institute_management_nonce', 'nonce');
        
        $class = sanitize_text_field($_POST['class'] ?? '');
        $session = sanitize_text_field($_POST['session'] ?? '');
        $search = sanitize_text_field($_POST['search'] ?? '');
        
        // Build query args
        $args = array(
            'post_type' => 'student',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        // Add search query
        if (!empty($search)) {
            $args['s'] = $search;
            
            // Also search in meta fields
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => '_student_id',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_student_phone',
                    'value' => $search,
                    'compare' => 'LIKE'
                )
            );
        }
        
        $meta_query = array('relation' => 'AND');
        $tax_query = array('relation' => 'AND');
        
        // Filter by class
        if (!empty($class)) {
            $tax_query[] = array(
                'taxonomy' => 'student_class',
                'field' => 'slug',
                'terms' => $class
            );
        }
        
        // Filter by session
        if (!empty($session)) {
            $meta_query[] = array(
                'key' => '_student_session',
                'value' => $session,
                'compare' => '='
            );
        }
        
        // Combine meta queries if we have both search and session
        if (!empty($search) && !empty($session)) {
            $args['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_student_id',
                        'value' => $search,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_student_phone',
                        'value' => $search,
                        'compare' => 'LIKE'
                    )
                ),
                array(
                    'key' => '_student_session',
                    'value' => $session,
                    'compare' => '='
                )
            );
        } elseif (!empty($meta_query) && count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }
        
        if (!empty($tax_query) && count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }
        
        $students = get_posts($args);
        
        ob_start();
        
        if (!empty($students)) {
            // Render grid view (hidden by default)
            echo '<div class="institute-students-grid institute-grid institute-columns-3" style="display: none;">';
            foreach ($students as $student) {
                $this->render_student_card($student);
            }
            echo '</div>';
            
            // Render table view (visible by default)
            echo '<div class="institute-students-table">';
            echo '<div class="institute-table-wrapper">';
            echo '<table class="institute-data-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th class="institute-th-serial">' . __('S.No', 'institute-management') . '</th>';
            echo '<th class="institute-th-photo">' . __('Photo', 'institute-management') . '</th>';
            echo '<th class="institute-th-name">' . __('Name', 'institute-management') . '</th>';
            echo '<th class="institute-th-id">' . __('Student ID', 'institute-management') . '</th>';
            echo '<th class="institute-th-class">' . __('Class', 'institute-management') . '</th>';
            echo '<th class="institute-th-batch">' . __('Batch', 'institute-management') . '</th>';
            echo '<th class="institute-th-session">' . __('Session', 'institute-management') . '</th>';
            echo '<th class="institute-th-mobile">' . __('Mobile', 'institute-management') . '</th>';
            echo '<th class="institute-th-status">' . __('Status', 'institute-management') . '</th>';
            echo '<th class="institute-th-actions">' . __('Actions', 'institute-management') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            $serial = 1;
            foreach ($students as $student) {
                $this->render_student_table_row($student, $serial++);
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="institute-no-results">';
            echo '<div class="no-results-icon"><span class="dashicons dashicons-admin-users"></span></div>';
            echo '<h3>' . __('No students found', 'institute-management') . '</h3>';
            echo '<p>' . __('No students match the selected criteria.', 'institute-management') . '</p>';
            echo '</div>';
        }
        
        $html = ob_get_clean();
        wp_send_json_success($html);
    }
    
    /**
     * Render student card for grid view
     */
    private function render_student_card($student) {
        $student_id = get_post_meta($student->ID, '_student_id', true);
        $status = get_post_meta($student->ID, '_student_status', true);
        $role = get_post_meta($student->ID, '_student_role', true);
        $session = get_post_meta($student->ID, '_student_session', true);
        $branch = get_post_meta($student->ID, '_student_branch', true);
        $classes = get_the_terms($student->ID, 'student_class');
        $batches = get_the_terms($student->ID, 'student_batch');
        
        echo '<article class="institute-student-card" data-student-id="' . esc_attr($student_id) . '">';
        
        // Photo
        echo '<div class="institute-card-photo">';
        if (has_post_thumbnail($student->ID)) {
            echo '<a href="' . get_permalink($student->ID) . '">';
            echo get_the_post_thumbnail($student->ID, 'medium', array('class' => 'student-photo'));
            echo '</a>';
        } else {
            echo '<div class="institute-default-avatar">';
            echo '<span class="dashicons dashicons-admin-users"></span>';
            echo '</div>';
        }
        
        if ($status) {
            echo '<span class="institute-status-badge institute-status-' . esc_attr($status) . '">';
            echo esc_html(ucfirst($status));
            echo '</span>';
        }
        echo '</div>';
        
        // Content
        echo '<div class="institute-card-content">';
        echo '<h3 class="institute-card-title">';
        echo '<a href="' . get_permalink($student->ID) . '">' . esc_html($student->post_title) . '</a>';
        echo '</h3>';
        
        if ($student_id) {
            echo '<p class="institute-card-id"><strong>' . __('ID:', 'institute-management') . '</strong> <span>' . esc_html($student_id) . '</span></p>';
        }
        
        if ($classes && !is_wp_error($classes)) {
            $class_names = wp_list_pluck($classes, 'name');
            echo '<p class="institute-card-class"><strong>' . __('Class:', 'institute-management') . '</strong> ' . esc_html(implode(', ', $class_names)) . '</p>';
        }
        
        echo '<div class="institute-card-actions">';
        echo '<a href="' . get_permalink($student->ID) . '" class="institute-btn institute-btn-primary">' . __('View Profile', 'institute-management') . '</a>';
        echo '</div>';
        echo '</div>';
        
        echo '</article>';
    }
    
    /**
     * Render student table row
     */
    private function render_student_table_row($student, $serial) {
        $student_id = get_post_meta($student->ID, '_student_id', true);
        $role = get_post_meta($student->ID, '_student_role', true);
        $session = get_post_meta($student->ID, '_student_session', true);
        $branch = get_post_meta($student->ID, '_student_branch', true);
        $phone = get_post_meta($student->ID, '_student_phone', true);
        $status = get_post_meta($student->ID, '_student_status', true);
        $classes = get_the_terms($student->ID, 'student_class');
        $batches = get_the_terms($student->ID, 'student_batch');
        
        echo '<tr class="institute-student-row" data-student-id="' . esc_attr($student_id) . '">';
        
        // Serial
        echo '<td class="institute-td-serial">' . $serial . '</td>';
        
        // Photo
        echo '<td class="institute-td-photo">';
        if (has_post_thumbnail($student->ID)) {
            echo '<div class="institute-table-photo">';
            echo '<a href="' . get_permalink($student->ID) . '">';
            echo get_the_post_thumbnail($student->ID, 'thumbnail', array('class' => 'student-table-photo'));
            echo '</a>';
            echo '</div>';
        } else {
            echo '<div class="institute-table-avatar">';
            echo '<span class="dashicons dashicons-admin-users"></span>';
            echo '</div>';
        }
        echo '</td>';
        
        // Name
        echo '<td class="institute-td-name">';
        echo '<div class="institute-name-cell">';
        echo '<a href="' . get_permalink($student->ID) . '" class="institute-student-name">' . esc_html($student->post_title) . '</a>';
        if ($role) {
            echo '<span class="institute-student-role">' . esc_html($role) . '</span>';
        }
        echo '</div>';
        echo '</td>';
        
        // Student ID
        echo '<td class="institute-td-id">';
        echo '<span class="institute-student-id">' . ($student_id ? esc_html($student_id) : '-') . '</span>';
        echo '</td>';
        
        // Class
        echo '<td class="institute-td-class">';
        if ($classes && !is_wp_error($classes)) {
            $class_names = wp_list_pluck($classes, 'name');
            echo esc_html(implode(', ', $class_names));
        } else {
            echo '<span class="institute-no-data">-</span>';
        }
        echo '</td>';
        
        // Batch
        echo '<td class="institute-td-batch">';
        if ($batches && !is_wp_error($batches)) {
            echo esc_html($batches[0]->name);
        } else {
            echo '<span class="institute-no-data">-</span>';
        }
        echo '</td>';
        
        // Session
        echo '<td class="institute-td-session">';
        echo $session ? esc_html($session) : '<span class="institute-no-data">-</span>';
        echo '</td>';
        
        // Mobile
        echo '<td class="institute-td-mobile">';
        if ($phone) {
            echo '<a href="tel:' . esc_attr($phone) . '" class="institute-phone-link">' . esc_html($phone) . '</a>';
        } else {
            echo '<span class="institute-no-data">-</span>';
        }
        echo '</td>';
        
        // Status
        echo '<td class="institute-td-status">';
        if ($status) {
            echo '<span class="institute-status-badge institute-status-' . esc_attr($status) . '">' . esc_html(ucfirst($status)) . '</span>';
        } else {
            echo '<span class="institute-status-badge institute-status-active">' . __('Active', 'institute-management') . '</span>';
        }
        echo '</td>';
        
        // Actions
        echo '<td class="institute-td-actions">';
        echo '<div class="institute-table-actions">';
        echo '<a href="' . get_permalink($student->ID) . '" class="institute-btn institute-btn-sm institute-btn-primary" title="' . __('View Profile', 'institute-management') . '">';
        echo '<span class="dashicons dashicons-visibility"></span>';
        echo '</a>';
        if (current_user_can('edit_posts')) {
            echo '<a href="' . get_edit_post_link($student->ID) . '" class="institute-btn institute-btn-sm institute-btn-secondary" title="' . __('Edit', 'institute-management') . '">';
            echo '<span class="dashicons dashicons-edit"></span>';
            echo '</a>';
        }
        echo '</div>';
        echo '</td>';
        
        echo '</tr>';
    }
    
    /**
     * Handle staff top filter AJAX request
     */
    public function handle_staff_top_filter() {
        // Don't check nonce for public access
        // check_ajax_referer('institute_management_nonce', 'nonce');
        
        $department = sanitize_text_field($_POST['department'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        $search = sanitize_text_field($_POST['search'] ?? '');
        
        // Build query args
        $args = array(
            'post_type' => 'staff',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        // Add search query
        if (!empty($search)) {
            $args['s'] = $search;
            
            // Also search in meta fields
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => '_staff_id',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_staff_phone',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_staff_position',
                    'value' => $search,
                    'compare' => 'LIKE'
                )
            );
        }

        $meta_query = array('relation' => 'AND');
        $tax_query = array('relation' => 'AND');
        
        // Filter by department
        if (!empty($department)) {
            $tax_query[] = array(
                'taxonomy' => 'staff_department',
                'field' => 'slug',
                'terms' => $department
            );
        }
        
        // Filter by status
        if (!empty($status)) {
            $meta_query[] = array(
                'key' => '_staff_status',
                'value' => $status,
                'compare' => '='
            );
        }
        
        // Combine meta queries if we have both search and status
        if (!empty($search) && !empty($status)) {
            $args['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_staff_id',
                        'value' => $search,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_staff_phone',
                        'value' => $search,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_staff_position',
                        'value' => $search,
                        'compare' => 'LIKE'
                    )
                ),
                array(
                    'key' => '_staff_status',
                    'value' => $status,
                    'compare' => '='
                )
            );
        } elseif (!empty($meta_query) && count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        if (!empty($tax_query) && count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }
        
        $staff = get_posts($args);
        
        ob_start();
        
        // Render results in both table and grid format
        echo '<div class="institute-staff-table">';
        echo '<div class="institute-table-wrapper">';
        echo '<table class="institute-data-table staff-data-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="institute-th-serial">' . __('S.No', 'institute-management') . '</th>';
        echo '<th class="institute-th-photo">' . __('Image', 'institute-management') . '</th>';
        echo '<th class="institute-th-name">' . __('Teacher Name', 'institute-management') . '</th>';
        echo '<th class="institute-th-mobile">' . __('Mobile Number', 'institute-management') . '</th>';
        echo '<th class="institute-th-designation">' . __('Designation', 'institute-management') . '</th>';
        echo '<th class="institute-th-department">' . __('Department', 'institute-management') . '</th>';
        echo '<th class="institute-th-status">' . __('Status', 'institute-management') . '</th>';
        echo '<th class="institute-th-actions">' . __('Actions', 'institute-management') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (!empty($staff)) {
            $serial = 1;
            foreach ($staff as $staff_member) {
                $this->render_staff_table_row($staff_member, $serial++);
            }
        } else {
            echo '<tr><td colspan="8" style="text-align: center; padding: 20px;">';
            echo __('No staff members found matching your criteria.', 'institute-management');
            echo '</td></tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
        // Grid view (hidden by default)
        echo '<div class="institute-staff-grid institute-grid institute-columns-3" style="display: none;">';
        
        if (!empty($staff)) {
            foreach ($staff as $staff_member) {
                $this->render_staff_grid_card($staff_member);
            }
        } else {
            echo '<div class="institute-no-results">';
            echo '<p>' . __('No staff members found matching your criteria.', 'institute-management') . '</p>';
            echo '</div>';
        }
        
        echo '</div>';
        
        $html = ob_get_clean();
        
        wp_send_json_success($html);
    }
    
    /**
     * Render staff table row
     */
    private function render_staff_table_row($staff_member, $serial) {
        $staff_id = get_post_meta($staff_member->ID, '_staff_id', true);
        $position = get_post_meta($staff_member->ID, '_staff_position', true);
        $phone = get_post_meta($staff_member->ID, '_staff_phone', true);
        $email = get_post_meta($staff_member->ID, '_staff_email', true);
        $status = get_post_meta($staff_member->ID, '_staff_status', true);
        $departments = get_the_terms($staff_member->ID, 'staff_department');
        
        // Format phone number
        $formatted_phone = $phone;
        if ($phone && !empty($phone)) {
            if (strlen($phone) > 6) {
                $formatted_phone = substr($phone, 0, 3) . 'XXXXXXXX';
            }
        }
        
        echo '<tr class="institute-staff-row" data-staff-id="' . esc_attr($staff_id) . '">';
        
        // Serial Number
        echo '<td class="institute-td-serial">' . $serial . '</td>';
        
        // Photo
        echo '<td class="institute-td-photo">';
        if (has_post_thumbnail($staff_member->ID)) {
            echo '<div class="institute-table-photo staff-photo">';
            echo '<a href="' . get_permalink($staff_member->ID) . '">';
            echo get_the_post_thumbnail($staff_member->ID, 'thumbnail', array('class' => 'staff-table-photo'));
            echo '</a>';
            echo '</div>';
        } else {
            echo '<div class="institute-table-avatar staff-avatar">';
            echo '<span class="dashicons dashicons-businessperson"></span>';
            echo '</div>';
        }
        echo '</td>';
        
        // Name
        echo '<td class="institute-td-name">';
        echo '<div class="institute-name-cell">';
        echo '<a href="' . get_permalink($staff_member->ID) . '" class="institute-staff-name">' . esc_html($staff_member->post_title) . '</a>';
        if ($staff_id) {
            echo '<span class="institute-staff-id">' . __('ID:', 'institute-management') . ' ' . esc_html($staff_id) . '</span>';
        }
        echo '</div>';
        echo '</td>';
        
        // Mobile Number
        echo '<td class="institute-td-mobile">';
        if ($phone) {
            echo '<span class="institute-phone-number">' . esc_html($formatted_phone) . '</span>';
        } else {
            echo '<span class="institute-no-data">-</span>';
        }
        echo '</td>';
        
        // Designation/Position
        echo '<td class="institute-td-designation">';
        if ($position) {
            echo '<span class="institute-position">' . esc_html($position) . '</span>';
        } else {
            echo '<span class="institute-no-data">-</span>';
        }
        echo '</td>';
        
        // Department
        echo '<td class="institute-td-department">';
        if ($departments && !is_wp_error($departments)) {
            $dept_names = wp_list_pluck($departments, 'name');
            echo esc_html(implode(', ', $dept_names));
        } else {
            echo '<span class="institute-no-data">-</span>';
        }
        echo '</td>';
        
        // Status
        echo '<td class="institute-td-status">';
        if ($status) {
            echo '<span class="institute-status-badge institute-status-' . esc_attr($status) . '">';
            echo esc_html(ucfirst($status));
            echo '</span>';
        } else {
            echo '<span class="institute-status-badge institute-status-active">';
            echo __('Active', 'institute-management');
            echo '</span>';
        }
        echo '</td>';
        
        // Actions
        echo '<td class="institute-td-actions">';
        echo '<div class="institute-table-actions">';
        echo '<a href="' . get_permalink($staff_member->ID) . '" class="institute-btn institute-btn-sm institute-btn-primary" title="' . __('View Profile', 'institute-management') . '">';
        echo '<span class="dashicons dashicons-visibility"></span>';
        echo '</a>';
        if (current_user_can('edit_posts')) {
            echo '<a href="' . get_edit_post_link($staff_member->ID) . '" class="institute-btn institute-btn-sm institute-btn-secondary" title="' . __('Edit', 'institute-management') . '">';
            echo '<span class="dashicons dashicons-edit"></span>';
            echo '</a>';
        }
        if ($phone && !empty($phone)) {
            echo '<a href="tel:' . esc_attr($phone) . '" class="institute-btn institute-btn-sm institute-btn-success" title="' . __('Call', 'institute-management') . '">';
            echo '<span class="dashicons dashicons-phone"></span>';
            echo '</a>';
        }
        if ($email && !empty($email)) {
            echo '<a href="mailto:' . esc_attr($email) . '" class="institute-btn institute-btn-sm institute-btn-info" title="' . __('Email', 'institute-management') . '">';
            echo '<span class="dashicons dashicons-email"></span>';
            echo '</a>';
        }
        echo '</div>';
        echo '</td>';
        
        echo '</tr>';
    }
    
    /**
     * Render staff grid card
     */
    private function render_staff_grid_card($staff_member) {
        $staff_id = get_post_meta($staff_member->ID, '_staff_id', true);
        $position = get_post_meta($staff_member->ID, '_staff_position', true);
        $phone = get_post_meta($staff_member->ID, '_staff_phone', true);
        $email = get_post_meta($staff_member->ID, '_staff_email', true);
        $status = get_post_meta($staff_member->ID, '_staff_status', true);
        $departments = get_the_terms($staff_member->ID, 'staff_department');
        
        echo '<article class="institute-staff-card" data-staff-id="' . esc_attr($staff_id) . '">';
        
        // Staff Photo
        echo '<div class="institute-card-photo">';
        if (has_post_thumbnail($staff_member->ID)) {
            echo '<a href="' . get_permalink($staff_member->ID) . '">';
            echo get_the_post_thumbnail($staff_member->ID, 'medium', array('class' => 'staff-photo'));
            echo '</a>';
        } else {
            echo '<div class="institute-default-avatar">';
            echo '<span class="dashicons dashicons-businessperson"></span>';
            echo '</div>';
        }
        
        // Status Badge
        if ($status) {
            echo '<span class="institute-status-badge institute-status-' . esc_attr($status) . '">';
            echo esc_html(ucfirst($status));
            echo '</span>';
        }
        echo '</div>';
        
        // Staff Info
        echo '<div class="institute-card-content">';
        echo '<h3 class="institute-card-title">';
        echo '<a href="' . get_permalink($staff_member->ID) . '">' . esc_html($staff_member->post_title) . '</a>';
        echo '</h3>';
        
        if ($staff_id) {
            echo '<p class="institute-card-id">';
            echo '<strong>' . __('ID:', 'institute-management') . '</strong> ';
            echo '<span>' . esc_html($staff_id) . '</span>';
            echo '</p>';
        }
        
        if ($position) {
            echo '<p class="institute-card-position">';
            echo '<strong>' . __('Position:', 'institute-management') . '</strong> ';
            echo esc_html($position);
            echo '</p>';
        }
        
        if ($departments && !is_wp_error($departments)) {
            echo '<p class="institute-card-department">';
            echo '<strong>' . __('Department:', 'institute-management') . '</strong> ';
            $dept_names = wp_list_pluck($departments, 'name');
            echo esc_html(implode(', ', $dept_names));
            echo '</p>';
        }
        
        if ($phone) {
            echo '<p class="institute-card-phone">';
            echo '<strong>' . __('Phone:', 'institute-management') . '</strong> ';
            echo '<a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a>';
            echo '</p>';
        }
        
        if ($email) {
            echo '<p class="institute-card-email">';
            echo '<strong>' . __('Email:', 'institute-management') . '</strong> ';
            echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
            echo '</p>';
        }
        
        // View Profile Button
        echo '<div class="institute-card-actions">';
        echo '<a href="' . get_permalink($staff_member->ID) . '" class="institute-btn institute-btn-primary">';
        echo __('View Profile', 'institute-management');
        echo '</a>';
        echo '</div>';
        echo '</div>';
        
        echo '</article>';
    }
    
    /**
     * Handle staff role filter AJAX request
     */
    public function handle_staff_role_filter() {
        // Don't check nonce for public access
        // check_ajax_referer('institute_management_nonce', 'nonce');
        
        $role = sanitize_text_field($_POST['role'] ?? '');
        $department = sanitize_text_field($_POST['department'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        $search = sanitize_text_field($_POST['search'] ?? '');
        
        // Build query args
        $args = array(
            'post_type' => 'staff',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        // Add search query
        if (!empty($search)) {
            $args['s'] = $search;
            
            // Also search in meta fields
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => '_staff_id',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_staff_phone',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_staff_position',
                    'value' => $search,
                    'compare' => 'LIKE'
                )
            );
        }

        $meta_query = array('relation' => 'AND');
        $tax_query = array('relation' => 'AND');
        
        // Filter by role (required for this specific filter)
        if (!empty($role)) {
            $tax_query[] = array(
                'taxonomy' => 'staff_role',
                'field' => 'slug',
                'terms' => $role
            );
        }
        
        // Filter by department
        if (!empty($department)) {
            $tax_query[] = array(
                'taxonomy' => 'staff_department',
                'field' => 'slug',
                'terms' => $department
            );
        }
        
        // Filter by status
        if (!empty($status)) {
            $meta_query[] = array(
                'key' => '_staff_status',
                'value' => $status,
                'compare' => '='
            );
        }
        
        // Combine meta queries if we have both search and status
        if (!empty($search) && !empty($status)) {
            $args['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_staff_id',
                        'value' => $search,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_staff_phone',
                        'value' => $search,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_staff_position',
                        'value' => $search,
                        'compare' => 'LIKE'
                    )
                ),
                array(
                    'key' => '_staff_status',
                    'value' => $status,
                    'compare' => '='
                )
            );
        } elseif (!empty($meta_query) && count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        if (!empty($tax_query) && count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        } elseif (!empty($tax_query) && count($tax_query) == 2) {
            $args['tax_query'] = $tax_query;
        }
        
        $staff = get_posts($args);
        
        ob_start();
        
        // Render results in both table and grid format
        echo '<div class="institute-staff-table">';
        echo '<div class="institute-table-wrapper">';
        echo '<table class="institute-data-table staff-data-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="institute-th-serial">' . __('S.No', 'institute-management') . '</th>';
        echo '<th class="institute-th-photo">' . __('Image', 'institute-management') . '</th>';
        echo '<th class="institute-th-name">' . __('Name', 'institute-management') . '</th>';
        echo '<th class="institute-th-mobile">' . __('Mobile Number', 'institute-management') . '</th>';
        echo '<th class="institute-th-designation">' . __('Designation', 'institute-management') . '</th>';
        echo '<th class="institute-th-department">' . __('Department', 'institute-management') . '</th>';
        echo '<th class="institute-th-status">' . __('Status', 'institute-management') . '</th>';
        echo '<th class="institute-th-actions">' . __('Actions', 'institute-management') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        if (!empty($staff)) {
            $serial = 1;
            foreach ($staff as $staff_member) {
                $this->render_staff_table_row($staff_member, $serial++);
            }
        } else {
            echo '<tr><td colspan="8" style="text-align: center; padding: 20px;">';
            echo __('No staff members found matching your criteria.', 'institute-management');
            echo '</td></tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
        // Grid view (hidden by default)
        echo '<div class="institute-staff-grid institute-grid institute-columns-3" style="display: none;">';
        
        if (!empty($staff)) {
            foreach ($staff as $staff_member) {
                $this->render_staff_grid_card($staff_member);
            }
        } else {
            echo '<div class="institute-no-results">';
            echo '<p>' . __('No staff members found matching your criteria.', 'institute-management') . '</p>';
            echo '</div>';
        }
        
        echo '</div>';
        
        $html = ob_get_clean();
        
        wp_send_json_success($html);
    }
} 