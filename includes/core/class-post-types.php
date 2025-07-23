<?php
/**
 * Post Types class
 */

namespace Institute_Management\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Post Types class
 */
class Post_Types {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_filter('manage_student_posts_columns', array($this, 'student_columns'));
        add_action('manage_student_posts_custom_column', array($this, 'student_column_content'), 10, 2);
        add_filter('manage_staff_posts_columns', array($this, 'staff_columns'));
        add_action('manage_staff_posts_custom_column', array($this, 'staff_column_content'), 10, 2);
        add_filter('manage_edit-student_sortable_columns', array($this, 'student_sortable_columns'));
        add_filter('manage_edit-staff_sortable_columns', array($this, 'staff_sortable_columns'));
        add_action('pre_get_posts', array($this, 'posts_orderby'));
    }
    
    /**
     * Register custom post types
     */
    public function register_post_types() {
        $this->register_student_post_type();
        $this->register_staff_post_type();
    }
    
    /**
     * Register student post type
     */
    private function register_student_post_type() {
        $labels = array(
            'name'               => __('Students', 'institute-management'),
            'singular_name'      => __('Student', 'institute-management'),
            'menu_name'          => __('Students', 'institute-management'),
            'add_new'            => __('Add New Student', 'institute-management'),
            'add_new_item'       => __('Add New Student', 'institute-management'),
            'new_item'           => __('New Student', 'institute-management'),
            'edit_item'          => __('Edit Student', 'institute-management'),
            'update_item'        => __('Update Student', 'institute-management'),
            'view_item'          => __('View Student', 'institute-management'),
            'view_items'         => __('View Students', 'institute-management'),
            'search_items'       => __('Search Students', 'institute-management'),
            'not_found'          => __('No students found', 'institute-management'),
            'not_found_in_trash' => __('No students found in trash', 'institute-management'),
            'featured_image'     => __('Student Photo', 'institute-management'),
            'set_featured_image' => __('Set student photo', 'institute-management'),
            'remove_featured_image' => __('Remove student photo', 'institute-management'),
            'use_featured_image' => __('Use as student photo', 'institute-management'),
            'insert_into_item'   => __('Insert into student', 'institute-management'),
            'uploaded_to_this_item' => __('Uploaded to this student', 'institute-management'),
            'items_list'         => __('Students list', 'institute-management'),
            'items_list_navigation' => __('Students list navigation', 'institute-management'),
            'filter_items_list'  => __('Filter students list', 'institute-management'),
        );
        
        $args = array(
            'label'              => __('Student', 'institute-management'),
            'description'        => __('Student profiles and information', 'institute-management'),
            'labels'             => $labels,
            'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields', 'revisions'),
            'taxonomies'         => array('student_class'),
            'hierarchical'       => false,
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-groups',
            'show_in_admin_bar'  => true,
            'show_in_nav_menus'  => true,
            'can_export'         => true,
            'has_archive'        => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'rewrite'            => array(
                'slug'       => 'students',
                'with_front' => false,
            ),
            'capability_type'    => 'post',
            'show_in_rest'       => true,
            'rest_base'          => 'students',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
        );
        
        register_post_type('student', $args);
    }
    
    /**
     * Register staff post type
     */
    private function register_staff_post_type() {
        $labels = array(
            'name'               => __('Staff', 'institute-management'),
            'singular_name'      => __('Staff Member', 'institute-management'),
            'menu_name'          => __('Staff', 'institute-management'),
            'add_new'            => __('Add New Staff', 'institute-management'),
            'add_new_item'       => __('Add New Staff Member', 'institute-management'),
            'new_item'           => __('New Staff Member', 'institute-management'),
            'edit_item'          => __('Edit Staff Member', 'institute-management'),
            'update_item'        => __('Update Staff Member', 'institute-management'),
            'view_item'          => __('View Staff Member', 'institute-management'),
            'view_items'         => __('View Staff', 'institute-management'),
            'search_items'       => __('Search Staff', 'institute-management'),
            'not_found'          => __('No staff found', 'institute-management'),
            'not_found_in_trash' => __('No staff found in trash', 'institute-management'),
            'featured_image'     => __('Staff Photo', 'institute-management'),
            'set_featured_image' => __('Set staff photo', 'institute-management'),
            'remove_featured_image' => __('Remove staff photo', 'institute-management'),
            'use_featured_image' => __('Use as staff photo', 'institute-management'),
            'insert_into_item'   => __('Insert into staff', 'institute-management'),
            'uploaded_to_this_item' => __('Uploaded to this staff', 'institute-management'),
            'items_list'         => __('Staff list', 'institute-management'),
            'items_list_navigation' => __('Staff list navigation', 'institute-management'),
            'filter_items_list'  => __('Filter staff list', 'institute-management'),
        );
        
        $args = array(
            'label'              => __('Staff Member', 'institute-management'),
            'description'        => __('Staff profiles and information', 'institute-management'),
            'labels'             => $labels,
            'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields', 'revisions'),
            'taxonomies'         => array('staff_department', 'staff_role'),
            'hierarchical'       => false,
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_position'      => 26,
            'menu_icon'          => 'dashicons-businessperson',
            'show_in_admin_bar'  => true,
            'show_in_nav_menus'  => true,
            'can_export'         => true,
            'has_archive'        => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'rewrite'            => array(
                'slug'       => 'staff',
                'with_front' => false,
            ),
            'capability_type'    => 'post',
            'show_in_rest'       => true,
            'rest_base'          => 'staff',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
        );
        
        register_post_type('staff', $args);
    }
    
    /**
     * Add custom columns for students
     */
    public function student_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['thumbnail'] = __('Photo', 'institute-management');
        $new_columns['title'] = $columns['title'];
        $new_columns['student_class'] = __('Class', 'institute-management');
        $new_columns['student_role'] = __('Role', 'institute-management');
        $new_columns['student_session'] = __('Session', 'institute-management');
        $new_columns['student_branch'] = __('Branch', 'institute-management');
        $new_columns['student_id'] = __('Student ID', 'institute-management');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    /**
     * Student column content
     */
    public function student_column_content($column, $post_id) {
        switch ($column) {
            case 'thumbnail':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, array(50, 50));
                } else {
                    echo '<span class="dashicons dashicons-admin-users" style="font-size: 50px; color: #ccc;"></span>';
                }
                break;
                
            case 'student_class':
                $terms = get_the_terms($post_id, 'student_class');
                if ($terms && !is_wp_error($terms)) {
                    $class_names = wp_list_pluck($terms, 'name');
                    echo esc_html(implode(', ', $class_names));
                } else {
                    echo '—';
                }
                break;
                
            case 'student_role':
                $role = get_post_meta($post_id, '_student_role', true);
                echo $role ? esc_html($role) : '—';
                break;
                
            case 'student_session':
                $session = get_post_meta($post_id, '_student_session', true);
                echo $session ? esc_html($session) : '—';
                break;
                
            case 'student_branch':
                $branch = get_post_meta($post_id, '_student_branch', true);
                echo $branch ? esc_html($branch) : '—';
                break;
                
            case 'student_id':
                $student_id = get_post_meta($post_id, '_student_id', true);
                echo $student_id ? esc_html($student_id) : '—';
                break;
        }
    }
    
    /**
     * Add custom columns for staff
     */
    public function staff_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['thumbnail'] = __('Photo', 'institute-management');
        $new_columns['title'] = $columns['title'];
        $new_columns['staff_department'] = __('Department', 'institute-management');
        $new_columns['staff_position'] = __('Position', 'institute-management');
        $new_columns['staff_phone'] = __('Phone', 'institute-management');
        $new_columns['staff_email'] = __('Email', 'institute-management');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    /**
     * Staff column content
     */
    public function staff_column_content($column, $post_id) {
        switch ($column) {
            case 'thumbnail':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, array(50, 50));
                } else {
                    echo '<span class="dashicons dashicons-businessperson" style="font-size: 50px; color: #ccc;"></span>';
                }
                break;
                
            case 'staff_department':
                $terms = get_the_terms($post_id, 'staff_department');
                if ($terms && !is_wp_error($terms)) {
                    $dept_names = wp_list_pluck($terms, 'name');
                    echo esc_html(implode(', ', $dept_names));
                } else {
                    echo '—';
                }
                break;
                
            case 'staff_position':
                $position = get_post_meta($post_id, '_staff_position', true);
                echo $position ? esc_html($position) : '—';
                break;
                
            case 'staff_phone':
                $phone = get_post_meta($post_id, '_staff_phone', true);
                echo $phone ? esc_html($phone) : '—';
                break;
                
            case 'staff_email':
                $email = get_post_meta($post_id, '_staff_email', true);
                if ($email) {
                    echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
                } else {
                    echo '—';
                }
                break;
        }
    }
    
    /**
     * Make student columns sortable
     */
    public function student_sortable_columns($columns) {
        $columns['student_role'] = 'student_role';
        $columns['student_session'] = 'student_session';
        $columns['student_branch'] = 'student_branch';
        $columns['student_id'] = 'student_id';
        return $columns;
    }
    
    /**
     * Make staff columns sortable
     */
    public function staff_sortable_columns($columns) {
        $columns['staff_position'] = 'staff_position';
        $columns['staff_phone'] = 'staff_phone';
        $columns['staff_email'] = 'staff_email';
        return $columns;
    }
    
    /**
     * Handle custom column sorting
     */
    public function posts_orderby($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        $orderby = $query->get('orderby');
        
        if (in_array($orderby, array('student_role', 'student_session', 'student_branch', 'student_id', 'staff_position', 'staff_phone', 'staff_email'))) {
            $query->set('meta_key', '_' . $orderby);
            $query->set('orderby', 'meta_value');
        }
    }
} 