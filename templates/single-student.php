<?php
/**
 * Single Student Template
 * Template for displaying individual student profiles
 */

get_header();

$settings = get_option('institute_management_settings', array());
$institute_name = $settings['institute_name'] ?? get_bloginfo('name');
?>

<div class="institute-single-student">
    
    <?php while (have_posts()): the_post(); ?>
    
    <!-- Student Header Section -->
    <div class="institute-student-header">
        <div class="container">
            <div class="institute-student-hero">
                
                <!-- Student Photo -->
                <div class="institute-student-photo-section">
                    <div class="institute-student-main-photo">
                        <?php if (has_post_thumbnail()): ?>
                            <?php the_post_thumbnail('large', array('class' => 'student-profile-photo')); ?>
                        <?php else: ?>
                            <div class="institute-default-photo">
                                <span class="dashicons dashicons-admin-users"></span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Status Badge -->
                        <?php
                        $status = get_post_meta(get_the_ID(), '_student_status', true);
                        if (!$status) $status = 'active';
                        ?>
                        <div class="institute-status-overlay">
                            <span class="institute-status-badge institute-status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Student Basic Info -->
                <div class="institute-student-basic-info">
                    <h1 class="institute-student-name"><?php the_title(); ?></h1>
                    
                    <?php
                    $student_id = get_post_meta(get_the_ID(), '_student_id', true);
                    ?>
                    
                    <?php if ($student_id): ?>
                        <div class="institute-student-id-simple">
                            <span class="institute-student-id-text"><?php echo esc_html($student_id); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Quick Actions -->
                    <div class="institute-student-actions">
                        <?php if (current_user_can('edit_posts')): ?>
                            <a href="<?php echo get_edit_post_link(); ?>" class="institute-btn institute-btn-primary">
                                <span class="dashicons dashicons-edit"></span>
                                <?php _e('Edit Student', 'institute-management'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo get_post_type_archive_link('student'); ?>" class="institute-btn institute-btn-secondary">
                            <span class="dashicons dashicons-arrow-left-alt"></span>
                            <?php _e('Back to Students', 'institute-management'); ?>
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Student Details Content -->
    <div class="institute-student-content">
        <div class="container">
            
            <div class="institute-student-details-grid">
                
                <!-- Personal Information -->
                <div class="institute-details-section">
                    <h3 class="institute-section-title">
                        <span class="dashicons dashicons-admin-users"></span>
                        <?php _e('Personal Information', 'institute-management'); ?>
                    </h3>
                    
                    <div class="institute-details-card">
                        <?php
                        // Get personal information using correct meta field names from backend
                        $phone = get_post_meta(get_the_ID(), '_phone', true);
                        $email = get_post_meta(get_the_ID(), '_email', true);
                        $dob = get_post_meta(get_the_ID(), '_date_of_birth', true);
                        $gender = get_post_meta(get_the_ID(), '_gender', true);
                        $address = get_post_meta(get_the_ID(), '_address', true);
                        $blood_group = get_post_meta(get_the_ID(), '_blood_group', true);
                        $religion = get_post_meta(get_the_ID(), '_religion', true);
                        $nationality = get_post_meta(get_the_ID(), '_nationality', true);
                        $father_name = get_post_meta(get_the_ID(), '_father_name', true);
                        $mother_name = get_post_meta(get_the_ID(), '_mother_name', true);
                        
                        // Get privacy settings
                        $settings = get_option('institute_management_settings', array());
                        $public_fields = $settings['public_fields'] ?? array();
                        $show_privacy_notice = $settings['show_privacy_notice'] ?? true;
                        $is_logged_in = is_user_logged_in();
                        
                        // Helper function to check if field should be shown
                        function should_show_field($field_name, $public_fields, $is_logged_in) {
                            return $is_logged_in || in_array($field_name, $public_fields);
                        }
                        ?>
                        
                        <?php if ($phone && should_show_field('phone', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-phone"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Phone:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value">
                                        <a href="tel:<?php echo esc_attr($phone); ?>" class="institute-phone-link">
                                            <?php echo esc_html($phone); ?>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($email && should_show_field('email', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-email"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Email:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value">
                                        <a href="mailto:<?php echo esc_attr($email); ?>" class="institute-email-link">
                                            <?php echo esc_html($email); ?>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($dob && should_show_field('dob', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-calendar-alt"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Date of Birth:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html(date('F j, Y', strtotime($dob))); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($gender && should_show_field('gender', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-admin-users"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Gender:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html($gender); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($address && should_show_field('address', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-location"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Address:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html($address); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($blood_group && should_show_field('blood_group', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-heart"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Blood Group:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value institute-blood-group"><?php echo esc_html($blood_group); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($religion && should_show_field('religion', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-admin-site"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Religion:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html($religion); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($nationality && should_show_field('nationality', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-admin-site-alt3"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Nationality:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html($nationality); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>                       
                                                
                        <?php 
                        // Check if any personal information is visible
                        $visible_fields = array();
                        $all_fields = array(
                            'phone' => $phone, 'email' => $email, 'dob' => $dob, 'gender' => $gender, 
                            'address' => $address, 'blood_group' => $blood_group, 'religion' => $religion, 
                            'nationality' => $nationality, 'father_name' => $father_name, 'mother_name' => $mother_name
                        );
                        
                        foreach ($all_fields as $field_name => $field_value) {
                            if ($field_value && should_show_field($field_name, $public_fields, $is_logged_in)) {
                                $visible_fields[] = $field_name;
                            }
                        }
                        
                        // Show privacy notice if not logged in and some fields are hidden
                        if (!$is_logged_in && $show_privacy_notice && !empty($all_fields) && count($visible_fields) < count(array_filter($all_fields))): ?>
                            <div class="institute-privacy-notice">
                                <div class="institute-privacy-icon">
                                    <span class="dashicons dashicons-lock"></span>
                                </div>
                                <p><?php _e('Some personal information is only visible to logged-in users for privacy protection.', 'institute-management'); ?></p>
                                <p><a href="<?php echo wp_login_url(get_permalink()); ?>" class="institute-login-link"><?php _e('Login to view complete profile', 'institute-management'); ?></a></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php 
                        // Show a message if no personal information is available or visible
                        if (empty($visible_fields)): ?>
                            <div class="institute-no-personal-info">
                                <div class="institute-no-info-icon">
                                    <span class="dashicons dashicons-info"></span>
                                </div>
                                <?php if (!$is_logged_in && !empty(array_filter($all_fields))): ?>
                                    <p><?php _e('Personal information is protected. Please log in to view details.', 'institute-management'); ?></p>
                                    <p><a href="<?php echo wp_login_url(get_permalink()); ?>" class="institute-login-link"><?php _e('Login to view profile', 'institute-management'); ?></a></p>
                                <?php else: ?>
                                    <p><?php _e('No personal information available for this student.', 'institute-management'); ?></p>
                                    <?php if (current_user_can('edit_posts')): ?>
                                        <p><a href="<?php echo get_edit_post_link(); ?>" class="institute-edit-link"><?php _e('Add personal information', 'institute-management'); ?></a></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Guardian Information -->
                <?php
                $guardian_name = get_post_meta(get_the_ID(), '_guardian_name', true);
                $guardian_phone = get_post_meta(get_the_ID(), '_guardian_phone', true);
                $guardian_email = get_post_meta(get_the_ID(), '_guardian_email', true);
                $guardian_relation = get_post_meta(get_the_ID(), '_guardian_relation', true);
                
                if ($guardian_name || $guardian_phone || $guardian_email):
                ?>
                <div class="institute-details-section">
                    <h3 class="institute-section-title">
                        <span class="dashicons dashicons-admin-home"></span>
                        <?php _e('Guardian Information', 'institute-management'); ?>
                    </h3>
                    
                    <div class="institute-details-card">
                        <?php if ($guardian_name): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-admin-users"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Guardian Name:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html($guardian_name); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($father_name && should_show_field('father_name', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-admin-users"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Father\'s Name:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html($father_name); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($mother_name && should_show_field('mother_name', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-admin-users"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Mother\'s Name:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html($mother_name); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($religion && should_show_field('religion', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-admin-site"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Religion:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html($religion); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($phone && should_show_field('phone', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-phone"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Phone:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value">
                                        <a href="tel:<?php echo esc_attr($phone); ?>" class="institute-phone-link">
                                            <?php echo esc_html($phone); ?>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($address && should_show_field('address', $public_fields, $is_logged_in)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-location"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Address:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html($address); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($guardian_relation): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-heart"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Relation:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html($guardian_relation); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($guardian_phone): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-phone"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Guardian Phone:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value">
                                        <a href="tel:<?php echo esc_attr($guardian_phone); ?>" class="institute-phone-link">
                                            <?php echo esc_html($guardian_phone); ?>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($guardian_email): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-email"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Guardian Email:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value">
                                        <a href="mailto:<?php echo esc_attr($guardian_email); ?>" class="institute-email-link">
                                            <?php echo esc_html($guardian_email); ?>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Academic Information -->
                <div class="institute-details-section">
                    <h3 class="institute-section-title">
                        <span class="dashicons dashicons-welcome-learn-more"></span>
                        <?php _e('Academic Information', 'institute-management'); ?>
                    </h3>
                    
                    <div class="institute-details-card">
                        <?php
                        $classes = get_the_terms(get_the_ID(), 'student_class');
                        $session = get_post_meta(get_the_ID(), '_student_session', true);
                        $branch = get_post_meta(get_the_ID(), '_student_branch', true);
                        $batches = get_the_terms(get_the_ID(), 'student_batch');
                        $admission_date = get_post_meta(get_the_ID(), '_admission_date', true);
                        $roll_number = get_post_meta(get_the_ID(), '_roll_number', true);
                        $registration_number = get_post_meta(get_the_ID(), '_registration_number', true);
                        ?>
                        
                        <?php if ($classes && !is_wp_error($classes)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-book"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Class:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value">
                                        <?php 
                                        foreach ($classes as $class) {
                                            echo '<span class="institute-class-badge">' . esc_html($class->name) . '</span>';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($batches && !is_wp_error($batches)): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-groups"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Batch:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value">
                                        <?php 
                                        foreach ($batches as $batch) {
                                            echo '<span class="institute-batch-badge">' . esc_html($batch->name) . '</span>';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($session): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-calendar"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Session:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value institute-session-badge"><?php echo esc_html($session); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($branch): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-building"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Branch:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html($branch); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($roll_number): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-id"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Roll Number:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value institute-roll-number"><?php echo esc_html($roll_number); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($admission_date): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-calendar-alt"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Admission Date:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value"><?php echo esc_html(date('F j, Y', strtotime($admission_date))); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($registration_number): ?>
                            <div class="institute-detail-item">
                                <span class="institute-detail-icon dashicons dashicons-index-card"></span>
                                <div class="institute-detail-content">
                                    <span class="institute-detail-label"><?php _e('Registration Number:', 'institute-management'); ?></span>
                                    <span class="institute-detail-value institute-registration-number"><?php echo esc_html($registration_number); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Additional Details -->
                <?php if (get_the_content()): ?>
                <div class="institute-details-section institute-full-width">
                    <h3 class="institute-section-title">
                        <span class="dashicons dashicons-media-text"></span>
                        <?php _e('Additional Information', 'institute-management'); ?>
                    </h3>
                    
                    <div class="institute-details-card">
                        <div class="institute-student-content-text">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            
        </div>
    </div>
    
    <?php endwhile; ?>
    
</div>

<?php get_footer(); ?> 