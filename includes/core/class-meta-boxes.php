<?php
/**
 * Meta Boxes class
 */

namespace Institute_Management\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Meta Boxes class
 */
class Meta_Boxes {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_student_meta'));
        add_action('save_post', array($this, 'save_staff_meta'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        // Student meta boxes
        add_meta_box(
            'student_personal_info',
            __('Personal Information', 'institute-management'),
            array($this, 'student_personal_info_meta_box'),
            'student',
            'normal',
            'high'
        );
        
        add_meta_box(
            'student_academic_info',
            __('Academic Information', 'institute-management'),
            array($this, 'student_academic_info_meta_box'),
            'student',
            'normal',
            'high'
        );
        
        add_meta_box(
            'student_contact_info',
            __('Contact Information', 'institute-management'),
            array($this, 'student_contact_info_meta_box'),
            'student',
            'side',
            'default'
        );
        
        // Staff meta boxes
        add_meta_box(
            'staff_personal_info',
            __('Personal Information', 'institute-management'),
            array($this, 'staff_personal_info_meta_box'),
            'staff',
            'normal',
            'high'
        );
        
        add_meta_box(
            'staff_employment_info',
            __('Employment Information', 'institute-management'),
            array($this, 'staff_employment_info_meta_box'),
            'staff',
            'normal',
            'high'
        );
        
        add_meta_box(
            'staff_contact_info',
            __('Contact Information', 'institute-management'),
            array($this, 'staff_contact_info_meta_box'),
            'staff',
            'side',
            'default'
        );
    }
    
    /**
     * Student personal information meta box
     */
    public function student_personal_info_meta_box($post) {
        wp_nonce_field('student_meta_nonce', 'student_meta_nonce');
        
        $student_id = get_post_meta($post->ID, '_student_id', true);
        $date_of_birth = get_post_meta($post->ID, '_date_of_birth', true);
        $gender = get_post_meta($post->ID, '_gender', true);
        $blood_group = get_post_meta($post->ID, '_blood_group', true);
        $religion = get_post_meta($post->ID, '_religion', true);
        $nationality = get_post_meta($post->ID, '_nationality', true);
        $father_name = get_post_meta($post->ID, '_father_name', true);
        $mother_name = get_post_meta($post->ID, '_mother_name', true);
        $guardian_name = get_post_meta($post->ID, '_guardian_name', true);
        ?>
        <table class="form-table institute-meta-table">
            <tr>
                <th><label for="student_id"><?php _e('Student ID', 'institute-management'); ?> <span class="required">*</span></label></th>
                <td>
                    <input type="text" id="student_id" name="student_id" value="<?php echo esc_attr($student_id); ?>" class="regular-text" required />
                    <p class="description"><?php _e('Unique identification number for the student.', 'institute-management'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="date_of_birth"><?php _e('Date of Birth', 'institute-management'); ?></label></th>
                <td>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo esc_attr($date_of_birth); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="gender"><?php _e('Gender', 'institute-management'); ?></label></th>
                <td>
                    <select id="gender" name="gender" class="regular-text">
                        <option value=""><?php _e('Select Gender', 'institute-management'); ?></option>
                        <option value="male" <?php selected($gender, 'male'); ?>><?php _e('Male', 'institute-management'); ?></option>
                        <option value="female" <?php selected($gender, 'female'); ?>><?php _e('Female', 'institute-management'); ?></option>
                        <option value="other" <?php selected($gender, 'other'); ?>><?php _e('Other', 'institute-management'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="blood_group"><?php _e('Blood Group', 'institute-management'); ?></label></th>
                <td>
                    <select id="blood_group" name="blood_group" class="regular-text">
                        <option value=""><?php _e('Select Blood Group', 'institute-management'); ?></option>
                        <?php
                        $blood_groups = array('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-');
                        foreach ($blood_groups as $group) {
                            echo '<option value="' . esc_attr($group) . '" ' . selected($blood_group, $group, false) . '>' . esc_html($group) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="religion"><?php _e('Religion', 'institute-management'); ?></label></th>
                <td><input type="text" id="religion" name="religion" value="<?php echo esc_attr($religion); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="nationality"><?php _e('Nationality', 'institute-management'); ?></label></th>
                <td><input type="text" id="nationality" name="nationality" value="<?php echo esc_attr($nationality); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="father_name"><?php _e('Father\'s Name', 'institute-management'); ?></label></th>
                <td><input type="text" id="father_name" name="father_name" value="<?php echo esc_attr($father_name); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="mother_name"><?php _e('Mother\'s Name', 'institute-management'); ?></label></th>
                <td><input type="text" id="mother_name" name="mother_name" value="<?php echo esc_attr($mother_name); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="guardian_name"><?php _e('Guardian\'s Name', 'institute-management'); ?></label></th>
                <td><input type="text" id="guardian_name" name="guardian_name" value="<?php echo esc_attr($guardian_name); ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Student academic information meta box
     */
    public function student_academic_info_meta_box($post) {
        $role = get_post_meta($post->ID, '_student_role', true);
        $session = get_post_meta($post->ID, '_student_session', true);
        $branch = get_post_meta($post->ID, '_student_branch', true);
        $roll_number = get_post_meta($post->ID, '_roll_number', true);
        $registration_number = get_post_meta($post->ID, '_registration_number', true);
        $admission_date = get_post_meta($post->ID, '_admission_date', true);
        $status = get_post_meta($post->ID, '_student_status', true);
        ?>
        <table class="form-table institute-meta-table">
            <tr>
                <th><label for="student_role"><?php _e('Role/Position', 'institute-management'); ?></label></th>
                <td><input type="text" id="student_role" name="student_role" value="<?php echo esc_attr($role); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="student_session"><?php _e('Academic Session', 'institute-management'); ?></label></th>
                <td><input type="text" id="student_session" name="student_session" value="<?php echo esc_attr($session); ?>" class="regular-text" placeholder="e.g., 2023-2024" /></td>
            </tr>
            <tr>
                <th><label for="student_branch"><?php _e('শাখা (Branch)', 'institute-management'); ?></label></th>
                <td><input type="text" id="student_branch" name="student_branch" value="<?php echo esc_attr($branch); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="roll_number"><?php _e('Roll Number', 'institute-management'); ?></label></th>
                <td><input type="text" id="roll_number" name="roll_number" value="<?php echo esc_attr($roll_number); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="registration_number"><?php _e('Registration Number', 'institute-management'); ?></label></th>
                <td><input type="text" id="registration_number" name="registration_number" value="<?php echo esc_attr($registration_number); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="admission_date"><?php _e('Admission Date', 'institute-management'); ?></label></th>
                <td><input type="date" id="admission_date" name="admission_date" value="<?php echo esc_attr($admission_date); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="student_status"><?php _e('Status', 'institute-management'); ?></label></th>
                <td>
                    <select id="student_status" name="student_status" class="regular-text">
                        <option value="active" <?php selected($status, 'active'); ?>><?php _e('Active', 'institute-management'); ?></option>
                        <option value="inactive" <?php selected($status, 'inactive'); ?>><?php _e('Inactive', 'institute-management'); ?></option>
                        <option value="graduated" <?php selected($status, 'graduated'); ?>><?php _e('Graduated', 'institute-management'); ?></option>
                        <option value="suspended" <?php selected($status, 'suspended'); ?>><?php _e('Suspended', 'institute-management'); ?></option>
                        <option value="dropped" <?php selected($status, 'dropped'); ?>><?php _e('Dropped Out', 'institute-management'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Student contact information meta box
     */
    public function student_contact_info_meta_box($post) {
        $phone = get_post_meta($post->ID, '_phone', true);
        $email = get_post_meta($post->ID, '_email', true);
        $address = get_post_meta($post->ID, '_address', true);
        $emergency_contact = get_post_meta($post->ID, '_emergency_contact', true);
        $emergency_phone = get_post_meta($post->ID, '_emergency_phone', true);
        ?>
        <table class="form-table institute-meta-table">
            <tr>
                <th><label for="phone"><?php _e('Phone Number', 'institute-management'); ?></label></th>
                <td><input type="tel" id="phone" name="phone" value="<?php echo esc_attr($phone); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="email"><?php _e('Email Address', 'institute-management'); ?></label></th>
                <td><input type="email" id="email" name="email" value="<?php echo esc_attr($email); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="address"><?php _e('Address', 'institute-management'); ?></label></th>
                <td><textarea id="address" name="address" rows="3" class="large-text"><?php echo esc_textarea($address); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="emergency_contact"><?php _e('Emergency Contact Name', 'institute-management'); ?></label></th>
                <td><input type="text" id="emergency_contact" name="emergency_contact" value="<?php echo esc_attr($emergency_contact); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="emergency_phone"><?php _e('Emergency Contact Phone', 'institute-management'); ?></label></th>
                <td><input type="tel" id="emergency_phone" name="emergency_phone" value="<?php echo esc_attr($emergency_phone); ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Staff personal information meta box
     */
    public function staff_personal_info_meta_box($post) {
        wp_nonce_field('staff_meta_nonce', 'staff_meta_nonce');
        
        $staff_id = get_post_meta($post->ID, '_staff_id', true);
        $date_of_birth = get_post_meta($post->ID, '_date_of_birth', true);
        $gender = get_post_meta($post->ID, '_gender', true);
        $qualification = get_post_meta($post->ID, '_qualification', true);
        $experience = get_post_meta($post->ID, '_experience', true);
        ?>
        <table class="form-table institute-meta-table">
            <tr>
                <th><label for="staff_id"><?php _e('Staff ID', 'institute-management'); ?> <span class="required">*</span></label></th>
                <td>
                    <input type="text" id="staff_id" name="staff_id" value="<?php echo esc_attr($staff_id); ?>" class="regular-text" required />
                    <p class="description"><?php _e('Unique identification number for the staff member.', 'institute-management'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="date_of_birth"><?php _e('Date of Birth', 'institute-management'); ?></label></th>
                <td><input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo esc_attr($date_of_birth); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="gender"><?php _e('Gender', 'institute-management'); ?></label></th>
                <td>
                    <select id="gender" name="gender" class="regular-text">
                        <option value=""><?php _e('Select Gender', 'institute-management'); ?></option>
                        <option value="male" <?php selected($gender, 'male'); ?>><?php _e('Male', 'institute-management'); ?></option>
                        <option value="female" <?php selected($gender, 'female'); ?>><?php _e('Female', 'institute-management'); ?></option>
                        <option value="other" <?php selected($gender, 'other'); ?>><?php _e('Other', 'institute-management'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="qualification"><?php _e('Qualification', 'institute-management'); ?></label></th>
                <td><textarea id="qualification" name="qualification" rows="3" class="large-text"><?php echo esc_textarea($qualification); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="experience"><?php _e('Experience (Years)', 'institute-management'); ?></label></th>
                <td><input type="number" id="experience" name="experience" value="<?php echo esc_attr($experience); ?>" class="regular-text" min="0" step="0.5" /></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Staff employment information meta box
     */
    public function staff_employment_info_meta_box($post) {
        $position = get_post_meta($post->ID, '_staff_position', true);
        $employment_type = get_post_meta($post->ID, '_employment_type', true);
        $join_date = get_post_meta($post->ID, '_join_date', true);
        $salary = get_post_meta($post->ID, '_salary', true);
        $status = get_post_meta($post->ID, '_staff_status', true);
        ?>
        <table class="form-table institute-meta-table">
            <tr>
                <th><label for="staff_position"><?php _e('পদবি (Position)', 'institute-management'); ?></label></th>
                <td><input type="text" id="staff_position" name="staff_position" value="<?php echo esc_attr($position); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="employment_type"><?php _e('Employment Type', 'institute-management'); ?></label></th>
                <td>
                    <select id="employment_type" name="employment_type" class="regular-text">
                        <option value=""><?php _e('Select Type', 'institute-management'); ?></option>
                        <option value="full-time" <?php selected($employment_type, 'full-time'); ?>><?php _e('Full Time', 'institute-management'); ?></option>
                        <option value="part-time" <?php selected($employment_type, 'part-time'); ?>><?php _e('Part Time', 'institute-management'); ?></option>
                        <option value="contract" <?php selected($employment_type, 'contract'); ?>><?php _e('Contract', 'institute-management'); ?></option>
                        <option value="temporary" <?php selected($employment_type, 'temporary'); ?>><?php _e('Temporary', 'institute-management'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="join_date"><?php _e('Join Date', 'institute-management'); ?></label></th>
                <td><input type="date" id="join_date" name="join_date" value="<?php echo esc_attr($join_date); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="salary"><?php _e('Salary', 'institute-management'); ?></label></th>
                <td><input type="number" id="salary" name="salary" value="<?php echo esc_attr($salary); ?>" class="regular-text" min="0" step="0.01" /></td>
            </tr>
            <tr>
                <th><label for="staff_status"><?php _e('Status', 'institute-management'); ?></label></th>
                <td>
                    <select id="staff_status" name="staff_status" class="regular-text">
                        <option value="active" <?php selected($status, 'active'); ?>><?php _e('Active', 'institute-management'); ?></option>
                        <option value="inactive" <?php selected($status, 'inactive'); ?>><?php _e('Inactive', 'institute-management'); ?></option>
                        <option value="on-leave" <?php selected($status, 'on-leave'); ?>><?php _e('On Leave', 'institute-management'); ?></option>
                        <option value="suspended" <?php selected($status, 'suspended'); ?>><?php _e('Suspended', 'institute-management'); ?></option>
                        <option value="terminated" <?php selected($status, 'terminated'); ?>><?php _e('Terminated', 'institute-management'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Staff contact information meta box
     */
    public function staff_contact_info_meta_box($post) {
        $phone = get_post_meta($post->ID, '_staff_phone', true);
        $email = get_post_meta($post->ID, '_staff_email', true);
        $address = get_post_meta($post->ID, '_address', true);
        $emergency_contact = get_post_meta($post->ID, '_emergency_contact', true);
        $emergency_phone = get_post_meta($post->ID, '_emergency_phone', true);
        ?>
        <table class="form-table institute-meta-table">
            <tr>
                <th><label for="staff_phone"><?php _e('ফোন নাম্বার (Phone)', 'institute-management'); ?></label></th>
                <td><input type="tel" id="staff_phone" name="staff_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="staff_email"><?php _e('Email Address', 'institute-management'); ?></label></th>
                <td><input type="email" id="staff_email" name="staff_email" value="<?php echo esc_attr($email); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="address"><?php _e('Address', 'institute-management'); ?></label></th>
                <td><textarea id="address" name="address" rows="3" class="large-text"><?php echo esc_textarea($address); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="emergency_contact"><?php _e('Emergency Contact Name', 'institute-management'); ?></label></th>
                <td><input type="text" id="emergency_contact" name="emergency_contact" value="<?php echo esc_attr($emergency_contact); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="emergency_phone"><?php _e('Emergency Contact Phone', 'institute-management'); ?></label></th>
                <td><input type="tel" id="emergency_phone" name="emergency_phone" value="<?php echo esc_attr($emergency_phone); ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save student meta data
     */
    public function save_student_meta($post_id) {
        if (!isset($_POST['student_meta_nonce']) || !wp_verify_nonce($_POST['student_meta_nonce'], 'student_meta_nonce')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (get_post_type($post_id) != 'student') {
            return;
        }
        
        // Define the meta fields
        $meta_fields = array(
            'student_id', 'date_of_birth', 'gender', 'blood_group', 'religion', 'nationality',
            'father_name', 'mother_name', 'guardian_name', 'student_role', 'student_session',
            'student_branch', 'roll_number', 'registration_number', 'admission_date', 'student_status',
            'phone', 'email', 'address', 'emergency_contact', 'emergency_phone'
        );
        
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                $value = sanitize_text_field($_POST[$field]);
                if ($field === 'address') {
                    $value = sanitize_textarea_field($_POST[$field]);
                } elseif ($field === 'email' || $field === 'staff_email') {
                    $value = sanitize_email($_POST[$field]);
                }
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
    }
    
    /**
     * Save staff meta data
     */
    public function save_staff_meta($post_id) {
        if (!isset($_POST['staff_meta_nonce']) || !wp_verify_nonce($_POST['staff_meta_nonce'], 'staff_meta_nonce')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (get_post_type($post_id) != 'staff') {
            return;
        }
        
        // Define the meta fields
        $meta_fields = array(
            'staff_id', 'date_of_birth', 'gender', 'qualification', 'experience',
            'staff_position', 'employment_type', 'join_date', 'salary', 'staff_status',
            'staff_phone', 'staff_email', 'address', 'emergency_contact', 'emergency_phone'
        );
        
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                $value = sanitize_text_field($_POST[$field]);
                if ($field === 'address' || $field === 'qualification') {
                    $value = sanitize_textarea_field($_POST[$field]);
                } elseif ($field === 'staff_email') {
                    $value = sanitize_email($_POST[$field]);
                }
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        if (in_array($post_type, array('student', 'staff')) && in_array($hook, array('post.php', 'post-new.php'))) {
            wp_enqueue_style(
                'institute-meta-boxes',
                INSTITUTE_MANAGEMENT_PLUGIN_URL . 'assets/css/admin-meta-boxes.css',
                array(),
                INSTITUTE_MANAGEMENT_VERSION
            );
            
            wp_enqueue_script(
                'institute-meta-boxes',
                INSTITUTE_MANAGEMENT_PLUGIN_URL . 'assets/js/admin-meta-boxes.js',
                array('jquery'),
                INSTITUTE_MANAGEMENT_VERSION,
                true
            );
        }
    }
} 