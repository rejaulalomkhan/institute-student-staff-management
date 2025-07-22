<?php
/**
 * Taxonomies class
 */

namespace Institute_Management\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Taxonomies class
 */
class Taxonomies {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_taxonomies'));
        add_action('admin_init', array($this, 'add_custom_fields'));
        add_action('student_class_add_form_fields', array($this, 'add_class_fields'));
        add_action('student_class_edit_form_fields', array($this, 'edit_class_fields'));
        add_action('created_student_class', array($this, 'save_class_fields'));
        add_action('edited_student_class', array($this, 'save_class_fields'));
        add_action('staff_department_add_form_fields', array($this, 'add_department_fields'));
        add_action('staff_department_edit_form_fields', array($this, 'edit_department_fields'));
        add_action('created_staff_department', array($this, 'save_department_fields'));
        add_action('edited_staff_department', array($this, 'save_department_fields'));
    }
    
    /**
     * Register custom taxonomies
     */
    public function register_taxonomies() {
        $this->register_student_class_taxonomy();
        $this->register_staff_department_taxonomy();
        $this->register_student_batch_taxonomy();
        $this->register_staff_role_taxonomy();
    }
    
    /**
     * Register student class taxonomy
     */
    private function register_student_class_taxonomy() {
        $labels = array(
            'name'              => __('Classes', 'institute-management'),
            'singular_name'     => __('Class', 'institute-management'),
            'search_items'      => __('Search Classes', 'institute-management'),
            'all_items'         => __('All Classes', 'institute-management'),
            'parent_item'       => __('Parent Class', 'institute-management'),
            'parent_item_colon' => __('Parent Class:', 'institute-management'),
            'edit_item'         => __('Edit Class', 'institute-management'),
            'update_item'       => __('Update Class', 'institute-management'),
            'add_new_item'      => __('Add New Class', 'institute-management'),
            'new_item_name'     => __('New Class Name', 'institute-management'),
            'menu_name'         => __('Classes', 'institute-management'),
            'back_to_items'     => __('Back to Classes', 'institute-management'),
        );
        
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'public'            => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'rewrite'           => array(
                'slug'         => 'class',
                'with_front'   => false,
                'hierarchical' => true,
            ),
            'show_in_rest'      => true,
            'rest_base'         => 'student-classes',
            'capabilities'      => array(
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts',
            ),
        );
        
        register_taxonomy('student_class', array('student'), $args);
    }
    
    /**
     * Register staff department taxonomy
     */
    private function register_staff_department_taxonomy() {
        $labels = array(
            'name'              => __('Departments', 'institute-management'),
            'singular_name'     => __('Department', 'institute-management'),
            'search_items'      => __('Search Departments', 'institute-management'),
            'all_items'         => __('All Departments', 'institute-management'),
            'parent_item'       => __('Parent Department', 'institute-management'),
            'parent_item_colon' => __('Parent Department:', 'institute-management'),
            'edit_item'         => __('Edit Department', 'institute-management'),
            'update_item'       => __('Update Department', 'institute-management'),
            'add_new_item'      => __('Add New Department', 'institute-management'),
            'new_item_name'     => __('New Department Name', 'institute-management'),
            'menu_name'         => __('Departments', 'institute-management'),
            'back_to_items'     => __('Back to Departments', 'institute-management'),
        );
        
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'public'            => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'rewrite'           => array(
                'slug'         => 'department',
                'with_front'   => false,
                'hierarchical' => true,
            ),
            'show_in_rest'      => true,
            'rest_base'         => 'staff-departments',
            'capabilities'      => array(
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts',
            ),
        );
        
        register_taxonomy('staff_department', array('staff'), $args);
    }
    
    /**
     * Register student batch taxonomy
     */
    private function register_student_batch_taxonomy() {
        $labels = array(
            'name'              => __('Batches', 'institute-management'),
            'singular_name'     => __('Batch', 'institute-management'),
            'search_items'      => __('Search Batches', 'institute-management'),
            'all_items'         => __('All Batches', 'institute-management'),
            'edit_item'         => __('Edit Batch', 'institute-management'),
            'update_item'       => __('Update Batch', 'institute-management'),
            'add_new_item'      => __('Add New Batch', 'institute-management'),
            'new_item_name'     => __('New Batch Name', 'institute-management'),
            'menu_name'         => __('Batches', 'institute-management'),
        );
        
        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'public'            => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
            'rewrite'           => array('slug' => 'batch'),
            'show_in_rest'      => true,
            'rest_base'         => 'student-batches',
        );
        
        register_taxonomy('student_batch', array('student'), $args);
    }
    
    /**
     * Register staff role taxonomy
     */
    private function register_staff_role_taxonomy() {
        $labels = array(
            'name'              => __('Staff Roles', 'institute-management'),
            'singular_name'     => __('Staff Role', 'institute-management'),
            'search_items'      => __('Search Staff Roles', 'institute-management'),
            'all_items'         => __('All Staff Roles', 'institute-management'),
            'edit_item'         => __('Edit Staff Role', 'institute-management'),
            'update_item'       => __('Update Staff Role', 'institute-management'),
            'add_new_item'      => __('Add New Staff Role', 'institute-management'),
            'new_item_name'     => __('New Staff Role Name', 'institute-management'),
            'menu_name'         => __('Staff Roles', 'institute-management'),
        );
        
        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'public'            => true,
            'show_in_nav_menus' => false,
            'show_tagcloud'     => false,
            'rewrite'           => array('slug' => 'staff-role'),
            'show_in_rest'      => true,
            'rest_base'         => 'staff-roles',
        );
        
        register_taxonomy('staff_role', array('staff'), $args);
    }
    
    /**
     * Add custom fields to taxonomies
     */
    public function add_custom_fields() {
        // Initialize custom fields if needed
    }
    
    /**
     * Add class custom fields
     */
    public function add_class_fields($tag) {
        ?>
        <div class="form-field">
            <label for="class_capacity"><?php _e('Class Capacity', 'institute-management'); ?></label>
            <input type="number" name="class_capacity" id="class_capacity" value="" min="1" />
            <p class="description"><?php _e('Maximum number of students in this class.', 'institute-management'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="class_room"><?php _e('Room Number', 'institute-management'); ?></label>
            <input type="text" name="class_room" id="class_room" value="" />
            <p class="description"><?php _e('Room number or location for this class.', 'institute-management'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="class_teacher"><?php _e('Class Teacher', 'institute-management'); ?></label>
            <input type="text" name="class_teacher" id="class_teacher" value="" />
            <p class="description"><?php _e('Name of the class teacher.', 'institute-management'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Edit class custom fields
     */
    public function edit_class_fields($tag) {
        $capacity = get_term_meta($tag->term_id, 'class_capacity', true);
        $room = get_term_meta($tag->term_id, 'class_room', true);
        $teacher = get_term_meta($tag->term_id, 'class_teacher', true);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="class_capacity"><?php _e('Class Capacity', 'institute-management'); ?></label></th>
            <td>
                <input type="number" name="class_capacity" id="class_capacity" value="<?php echo esc_attr($capacity); ?>" min="1" />
                <p class="description"><?php _e('Maximum number of students in this class.', 'institute-management'); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row"><label for="class_room"><?php _e('Room Number', 'institute-management'); ?></label></th>
            <td>
                <input type="text" name="class_room" id="class_room" value="<?php echo esc_attr($room); ?>" />
                <p class="description"><?php _e('Room number or location for this class.', 'institute-management'); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row"><label for="class_teacher"><?php _e('Class Teacher', 'institute-management'); ?></label></th>
            <td>
                <input type="text" name="class_teacher" id="class_teacher" value="<?php echo esc_attr($teacher); ?>" />
                <p class="description"><?php _e('Name of the class teacher.', 'institute-management'); ?></p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save class custom fields
     */
    public function save_class_fields($term_id) {
        if (isset($_POST['class_capacity'])) {
            update_term_meta($term_id, 'class_capacity', sanitize_text_field($_POST['class_capacity']));
        }
        
        if (isset($_POST['class_room'])) {
            update_term_meta($term_id, 'class_room', sanitize_text_field($_POST['class_room']));
        }
        
        if (isset($_POST['class_teacher'])) {
            update_term_meta($term_id, 'class_teacher', sanitize_text_field($_POST['class_teacher']));
        }
    }
    
    /**
     * Add department custom fields
     */
    public function add_department_fields($tag) {
        ?>
        <div class="form-field">
            <label for="department_head"><?php _e('Department Head', 'institute-management'); ?></label>
            <input type="text" name="department_head" id="department_head" value="" />
            <p class="description"><?php _e('Name of the department head.', 'institute-management'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="department_budget"><?php _e('Annual Budget', 'institute-management'); ?></label>
            <input type="number" name="department_budget" id="department_budget" value="" min="0" step="0.01" />
            <p class="description"><?php _e('Annual budget allocation for this department.', 'institute-management'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="department_location"><?php _e('Location', 'institute-management'); ?></label>
            <input type="text" name="department_location" id="department_location" value="" />
            <p class="description"><?php _e('Physical location of the department.', 'institute-management'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Edit department custom fields
     */
    public function edit_department_fields($tag) {
        $head = get_term_meta($tag->term_id, 'department_head', true);
        $budget = get_term_meta($tag->term_id, 'department_budget', true);
        $location = get_term_meta($tag->term_id, 'department_location', true);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="department_head"><?php _e('Department Head', 'institute-management'); ?></label></th>
            <td>
                <input type="text" name="department_head" id="department_head" value="<?php echo esc_attr($head); ?>" />
                <p class="description"><?php _e('Name of the department head.', 'institute-management'); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row"><label for="department_budget"><?php _e('Annual Budget', 'institute-management'); ?></label></th>
            <td>
                <input type="number" name="department_budget" id="department_budget" value="<?php echo esc_attr($budget); ?>" min="0" step="0.01" />
                <p class="description"><?php _e('Annual budget allocation for this department.', 'institute-management'); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row"><label for="department_location"><?php _e('Location', 'institute-management'); ?></label></th>
            <td>
                <input type="text" name="department_location" id="department_location" value="<?php echo esc_attr($location); ?>" />
                <p class="description"><?php _e('Physical location of the department.', 'institute-management'); ?></p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save department custom fields
     */
    public function save_department_fields($term_id) {
        if (isset($_POST['department_head'])) {
            update_term_meta($term_id, 'department_head', sanitize_text_field($_POST['department_head']));
        }
        
        if (isset($_POST['department_budget'])) {
            update_term_meta($term_id, 'department_budget', sanitize_text_field($_POST['department_budget']));
        }
        
        if (isset($_POST['department_location'])) {
            update_term_meta($term_id, 'department_location', sanitize_text_field($_POST['department_location']));
        }
    }
} 