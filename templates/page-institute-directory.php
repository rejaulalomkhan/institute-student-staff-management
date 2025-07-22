<?php
/**
 * Template Name: Institute Directory Full Width
 * Custom page template for Institute Directory - Full width without sidebar
 */

// Get custom settings
$settings = get_option('institute_management_settings', array());
$institute_name = $settings['institute_name'] ?? get_bloginfo('name');
$enable_search = $settings['enable_search'] ?? true;
$enable_filters = $settings['enable_filters'] ?? true;

// Add body class for our custom styling
add_filter('body_class', function($classes) {
    $classes[] = 'institute-directory-page';
    return $classes;
});

get_header(); ?>
    
    <style>
        /* Override theme styles for full width */
        .institute-directory-page .site-content,
        .institute-directory-page .content-area,
        .institute-directory-page #main,
        .institute-directory-page #primary,
        .institute-directory-page .container,
        .institute-directory-page .wrap {
            max-width: none !important;
            width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        
        /* Hide sidebars completely */
        .institute-directory-page #secondary,
        .institute-directory-page .sidebar,
        .institute-directory-page aside {
            display: none !important;
        }
        
        /* Remove any theme padding/margins on main content */
        .institute-directory-page .site-main,
        .institute-directory-page .main-content,
        .institute-directory-page .content-wrapper {
            padding: 0 !important;
            margin: 0 !important;
        }
        
        /* Custom full width container */
        .institute-fullwidth-container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Full width hero section */
        .institute-directory-hero {
            background: linear-gradient(135deg, var(--institute-primary, #2563eb), var(--institute-secondary, #7c3aed));
            color: white;
            padding: 4rem 0;
            margin: 0 !important;
            text-align: center;
            position: relative;
            overflow: hidden;
            width: 100vw;
            margin-left: calc(50% - 50vw) !important;
        }
        
        .institute-directory-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .institute-directory-hero-content {
            position: relative;
            z-index: 2;
        }
        
        .institute-hero-title {
            font-size: 3rem;
            font-weight: 800;
            margin: 0 0 1rem 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .institute-hero-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin: 0 0 2rem 0;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .institute-hero-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .institute-stat-item {
            background: rgba(255,255,255,0.1);
            padding: 1.5rem 2rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .institute-stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .institute-stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        /* Hide breadcrumbs and other theme elements */
        .institute-directory-page .breadcrumbs,
        .institute-directory-page .breadcrumb,
        .institute-directory-page .page-header {
            display: none !important;
        }
        
        /* Ensure full width even in themes with containers */
        .institute-directory-page {
            overflow-x: hidden;
        }
        
        /* Directory specific styles */
        .institute-directory-grid {
            display: grid;
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .institute-directory-grid.institute-grid {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
        
        .institute-columns-3.institute-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        
        .institute-directory-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .institute-directory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .institute-card-photo {
            text-align: center;
            margin-bottom: 1rem;
            position: relative;
        }
        
        .institute-card-photo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f1f5f9;
        }
        
        .institute-default-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            color: white;
            font-size: 2.5rem;
        }
        
        .institute-card-content {
            text-align: center;
        }
        
        .institute-card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 1rem 0;
            color: #1f2937;
        }
        
        .institute-card-title a {
            color: inherit;
            text-decoration: none;
        }
        
        .institute-card-title a:hover {
            color: #2563eb;
        }
        
        .institute-card-id {
            background: #f1f5f9;
            padding: 0.5rem;
            border-radius: 6px;
            margin: 0 0 0.75rem 0;
            font-family: monospace;
            font-weight: 600;
            color: #2563eb;
        }
        
        .institute-card-content p {
            margin: 0.5rem 0;
            font-size: 0.875rem;
            color: #6b7280;
            line-height: 1.5;
        }
        
        .institute-card-content strong {
            color: #374151;
        }
        
        .institute-card-actions {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }
        
        .institute-btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }
        
        .institute-btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .institute-btn-primary:hover {
            background: #1d4ed8;
            color: white;
            transform: translateY(-2px);
        }
        
        .institute-no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: #f9fafb;
            border-radius: 12px;
            border: 2px dashed #d1d5db;
        }
        
        .institute-no-results .no-results-icon {
            font-size: 4rem;
            color: #9ca3af;
            margin-bottom: 1rem;
        }
        
        .institute-no-results h3 {
            font-size: 1.5rem;
            color: #374151;
            margin: 0 0 1rem 0;
        }
        
        .institute-no-results p {
            color: #6b7280;
            margin: 0;
        }
        
        @media (max-width: 1024px) {
            .institute-columns-3.institute-grid { 
                grid-template-columns: repeat(2, 1fr); 
            }
        }
        
        @media (max-width: 768px) {
            .institute-columns-3.institute-grid { 
                grid-template-columns: 1fr; 
            }
        }
    </style>

<!-- Full Width Hero Section -->
    <section class="institute-directory-hero">
        <div class="institute-fullwidth-container">
            <div class="institute-directory-hero-content">
                <h1 class="institute-hero-title">
                    <?php 
                    if (have_posts()) {
                        the_post();
                        the_title();
                        rewind_posts();
                    } else {
                        _e('Institute Directory', 'institute-management');
                    }
                    ?>
                </h1>
                
                <?php if ($institute_name): ?>
                <p class="institute-hero-subtitle">
                    <?php printf(__('Complete directory of students and staff at %s', 'institute-management'), esc_html($institute_name)); ?>
                </p>
                <?php endif; ?>
                
                <!-- Statistics -->
                <div class="institute-hero-stats">
                    <?php
                    $student_count = wp_count_posts('student');
                    $staff_count = wp_count_posts('staff');
                    $classes_count = wp_count_terms(array('taxonomy' => 'student_class', 'hide_empty' => false));
                    $departments_count = wp_count_terms(array('taxonomy' => 'staff_department', 'hide_empty' => false));
                    ?>
                    
                    <div class="institute-stat-item">
                        <span class="institute-stat-number"><?php echo number_format($student_count->publish ?? 0); ?></span>
                        <span class="institute-stat-label"><?php _e('Students', 'institute-management'); ?></span>
                    </div>
                    
                    <div class="institute-stat-item">
                        <span class="institute-stat-number"><?php echo number_format($staff_count->publish ?? 0); ?></span>
                        <span class="institute-stat-label"><?php _e('Staff Members', 'institute-management'); ?></span>
                    </div>
                    
                    <div class="institute-stat-item">
                        <span class="institute-stat-number"><?php echo number_format($classes_count); ?></span>
                        <span class="institute-stat-label"><?php _e('Classes', 'institute-management'); ?></span>
                    </div>
                    
                    <div class="institute-stat-item">
                        <span class="institute-stat-number"><?php echo number_format($departments_count); ?></span>
                        <span class="institute-stat-label"><?php _e('Departments', 'institute-management'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Area -->
    <main id="main" class="site-main">
        <div class="institute-fullwidth-container">
            
            <!-- Search and Filter Section -->
            <?php if ($enable_search || $enable_filters): ?>
            <section class="institute-directory-search" style="padding: 3rem 0 2rem;">
                <div style="max-width: 800px; margin: 0 auto; text-align: center;">
                    
                    <?php if ($enable_search): ?>
                    <div class="institute-search-section" style="margin-bottom: 2rem;">
                        <h2 style="margin-bottom: 1rem; color: #374151;"><?php _e('Search Directory', 'institute-management'); ?></h2>
                        <div class="institute-main-search" style="display: flex; gap: 1rem; max-width: 500px; margin: 0 auto;">
                            <input type="text" id="directory-search" placeholder="<?php _e('Search by name, ID, department, or class...', 'institute-management'); ?>" style="flex: 1; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 1rem;" />
                            <button type="button" id="directory-search-btn" style="padding: 1rem 2rem; background: linear-gradient(135deg, var(--institute-primary, #2563eb), var(--institute-secondary, #7c3aed)); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
                                <span class="dashicons dashicons-search"></span>
                                <?php _e('Search', 'institute-management'); ?>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($enable_filters): ?>
                    <div class="institute-filter-section">
                        <h3 style="margin-bottom: 1rem; color: #6b7280;"><?php _e('Filter Options', 'institute-management'); ?></h3>
                        <div class="institute-directory-filters" style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; align-items: center;">
                            
                            <select id="directory-type-filter" style="padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; background: white;">
                                <option value=""><?php _e('All Types', 'institute-management'); ?></option>
                                <option value="student"><?php _e('Students Only', 'institute-management'); ?></option>
                                <option value="staff"><?php _e('Staff Only', 'institute-management'); ?></option>
                            </select>
                            
                            <select id="directory-class-filter" style="padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; background: white;">
                                <option value=""><?php _e('All Classes/Departments', 'institute-management'); ?></option>
                                <optgroup label="<?php _e('Classes', 'institute-management'); ?>">
                                    <?php
                                    $classes = get_terms(array('taxonomy' => 'student_class', 'hide_empty' => false));
                                    if ($classes && !is_wp_error($classes)):
                                        foreach ($classes as $class):
                                            echo '<option value="class-' . esc_attr($class->slug) . '">' . esc_html($class->name) . ' (' . $class->count . ')</option>';
                                        endforeach;
                                    endif;
                                    ?>
                                </optgroup>
                                <optgroup label="<?php _e('Departments', 'institute-management'); ?>">
                                    <?php
                                    $departments = get_terms(array('taxonomy' => 'staff_department', 'hide_empty' => false));
                                    if ($departments && !is_wp_error($departments)):
                                        foreach ($departments as $department):
                                            echo '<option value="department-' . esc_attr($department->slug) . '">' . esc_html($department->name) . ' (' . $department->count . ')</option>';
                                        endforeach;
                                    endif;
                                    ?>
                                </optgroup>
                            </select>
                            
                            <select id="directory-status-filter" style="padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; background: white;">
                                <option value=""><?php _e('All Status', 'institute-management'); ?></option>
                                <option value="active"><?php _e('Active', 'institute-management'); ?></option>
                                <option value="inactive"><?php _e('Inactive', 'institute-management'); ?></option>
                                <option value="graduated"><?php _e('Graduated', 'institute-management'); ?></option>
                            </select>
                            
                            <button type="button" id="clear-directory-filters" style="padding: 0.75rem 1.5rem; background: #6b7280; color: white; border: none; border-radius: 8px; cursor: pointer;">
                                <?php _e('Clear All', 'institute-management'); ?>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                </div>
            </section>
            <?php endif; ?>
            
            <!-- Directory Content -->
            <section class="institute-directory-content" style="padding: 2rem 0 4rem;">
                
                <!-- Display Controls -->
                <div class="institute-directory-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding: 1.5rem; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    
                    <div class="institute-view-options">
                        <label style="font-weight: 600; color: #374151; margin-right: 1rem;"><?php _e('View:', 'institute-management'); ?></label>
                        <button type="button" class="directory-view-toggle active" data-view="grid" style="padding: 0.5rem 1rem; margin-right: 0.5rem; border: 2px solid #e5e7eb; background: var(--institute-primary, #2563eb); color: white; border-radius: 6px; cursor: pointer;">
                            <span class="dashicons dashicons-grid-view"></span> <?php _e('Grid', 'institute-management'); ?>
                        </button>
                        <button type="button" class="directory-view-toggle" data-view="list" style="padding: 0.5rem 1rem; border: 2px solid #e5e7eb; background: white; color: #374151; border-radius: 6px; cursor: pointer;">
                            <span class="dashicons dashicons-list-view"></span> <?php _e('List', 'institute-management'); ?>
                        </button>
                    </div>
                    
                    <div class="institute-sort-options">
                        <label for="directory-sort" style="font-weight: 600; color: #374151; margin-right: 0.5rem;"><?php _e('Sort by:', 'institute-management'); ?></label>
                        <select id="directory-sort" style="padding: 0.5rem; border: 2px solid #e5e7eb; border-radius: 6px; background: white;">
                            <option value="name-asc"><?php _e('Name (A-Z)', 'institute-management'); ?></option>
                            <option value="name-desc"><?php _e('Name (Z-A)', 'institute-management'); ?></option>
                            <option value="type-asc"><?php _e('Type (Student/Staff)', 'institute-management'); ?></option>
                            <option value="date-desc"><?php _e('Recently Added', 'institute-management'); ?></option>
                        </select>
                    </div>
                    
                </div>
                
                <!-- Results Area -->
                <div id="directory-results" class="institute-directory-results">
                    <!-- Fallback content if AJAX fails -->
                    <div class="institute-fallback-content">
                        <?php
                        // Display students
                        $students = get_posts(array(
                            'post_type' => 'student',
                            'posts_per_page' => 10,
                            'post_status' => 'publish'
                        ));
                        
                        if (!empty($students)): ?>
                        <h3><?php _e('Students', 'institute-management'); ?></h3>
                        <div class="institute-directory-grid institute-grid institute-columns-3">
                            <?php foreach ($students as $student): ?>
                            <div class="institute-directory-card institute-student-card">
                                <div class="institute-card-photo">
                                    <?php if (has_post_thumbnail($student->ID)): ?>
                                        <a href="<?php echo get_permalink($student->ID); ?>">
                                            <?php echo get_the_post_thumbnail($student->ID, 'medium'); ?>
                                        </a>
                                    <?php else: ?>
                                        <div class="institute-default-avatar">
                                            <span class="dashicons dashicons-admin-users"></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="institute-card-content">
                                    <h3 class="institute-card-title">
                                        <a href="<?php echo get_permalink($student->ID); ?>"><?php echo esc_html($student->post_title); ?></a>
                                    </h3>
                                    <?php
                                    $student_id = get_post_meta($student->ID, '_student_id', true);
                                    if ($student_id): ?>
                                        <p class="institute-card-id"><strong><?php _e('ID:', 'institute-management'); ?></strong> <?php echo esc_html($student_id); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $class_terms = get_the_terms($student->ID, 'student_class');
                                    if ($class_terms && !is_wp_error($class_terms)): ?>
                                        <p class="institute-card-class"><strong><?php _e('Class:', 'institute-management'); ?></strong> <?php echo esc_html($class_terms[0]->name); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="institute-card-actions">
                                        <a href="<?php echo get_permalink($student->ID); ?>" class="institute-btn institute-btn-primary"><?php _e('View Profile', 'institute-management'); ?></a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php
                        // Display staff
                        $staff = get_posts(array(
                            'post_type' => 'staff',
                            'posts_per_page' => 10,
                            'post_status' => 'publish'
                        ));
                        
                        if (!empty($staff)): ?>
                        <h3><?php _e('Staff', 'institute-management'); ?></h3>
                        <div class="institute-directory-grid institute-grid institute-columns-3">
                            <?php foreach ($staff as $staff_member): ?>
                            <div class="institute-directory-card institute-staff-card">
                                <div class="institute-card-photo">
                                    <?php if (has_post_thumbnail($staff_member->ID)): ?>
                                        <a href="<?php echo get_permalink($staff_member->ID); ?>">
                                            <?php echo get_the_post_thumbnail($staff_member->ID, 'medium'); ?>
                                        </a>
                                    <?php else: ?>
                                        <div class="institute-default-avatar">
                                            <span class="dashicons dashicons-businessperson"></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="institute-card-content">
                                    <h3 class="institute-card-title">
                                        <a href="<?php echo get_permalink($staff_member->ID); ?>"><?php echo esc_html($staff_member->post_title); ?></a>
                                    </h3>
                                    <?php
                                    $staff_id = get_post_meta($staff_member->ID, '_staff_id', true);
                                    if ($staff_id): ?>
                                        <p class="institute-card-id"><strong><?php _e('ID:', 'institute-management'); ?></strong> <?php echo esc_html($staff_id); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $position = get_post_meta($staff_member->ID, '_staff_position', true);
                                    if ($position): ?>
                                        <p class="institute-card-position"><strong><?php _e('Position:', 'institute-management'); ?></strong> <?php echo esc_html($position); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $dept_terms = get_the_terms($staff_member->ID, 'staff_department');
                                    if ($dept_terms && !is_wp_error($dept_terms)): ?>
                                        <p class="institute-card-department"><strong><?php _e('Department:', 'institute-management'); ?></strong> <?php echo esc_html($dept_terms[0]->name); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="institute-card-actions">
                                        <a href="<?php echo get_permalink($staff_member->ID); ?>" class="institute-btn institute-btn-primary"><?php _e('View Profile', 'institute-management'); ?></a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (empty($students) && empty($staff)): ?>
                        <div class="institute-no-results">
                            <div class="no-results-icon">
                                <span class="dashicons dashicons-admin-users"></span>
                            </div>
                            <h3><?php _e('No directory entries found', 'institute-management'); ?></h3>
                            <p><?php _e('Please add some students and staff members to display in the directory.', 'institute-management'); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            </section>
            
            <!-- Page Content (if any) -->
            <?php if (have_posts()): ?>
                <?php while (have_posts()): the_post(); ?>
                    <?php if (get_the_content()): ?>
                    <section class="institute-page-content" style="padding: 2rem 0; background: #f8fafc; margin: 2rem 0; border-radius: 12px;">
                        <div style="margin: 0 auto; padding: 0 2rem;">
                            <div class="entry-content">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php endif; ?>
            
        </div>

<!-- Loading Overlay -->
<div id="institute-directory-loading" class="institute-loading-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255, 255, 255, 0.9); z-index: 9999; display: flex; align-items: center; justify-content: center;">
    <div class="institute-loading-spinner" style="text-align: center;">
        <div class="spinner" style="width: 50px; height: 50px; border: 5px solid #f3f4f6; border-top: 5px solid var(--institute-primary, #2563eb); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;"></div>
        <p style="color: #6b7280;"><?php _e('Loading...', 'institute-management'); ?></p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize directory
    loadDirectoryContent();
    
    // Search functionality
    var searchTimeout;
    $('#directory-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performDirectorySearch();
        }, 500);
    });
    
    $('#directory-search-btn').on('click', function() {
        performDirectorySearch();
    });
    
    // Filter functionality
    $('#directory-type-filter, #directory-class-filter, #directory-status-filter').on('change', function() {
        performDirectoryFilter();
    });
    
    // Sort functionality
    $('#directory-sort').on('change', function() {
        performDirectoryFilter();
    });
    
    // View toggle
    $('.directory-view-toggle').on('click', function() {
        var view = $(this).data('view');
        $('.directory-view-toggle').removeClass('active').css({
            'background': 'white',
            'color': '#374151'
        });
        $(this).addClass('active').css({
            'background': 'var(--institute-primary, #2563eb)',
            'color': 'white'
        });
        
        var $results = $('#directory-results .institute-directory-grid');
        $results.removeClass('institute-grid institute-list');
        $results.addClass('institute-' + view);
    });
    
    // Clear filters
    $('#clear-directory-filters').on('click', function() {
        $('#directory-search').val('');
        $('#directory-type-filter, #directory-class-filter, #directory-status-filter, #directory-sort').val('');
        loadDirectoryContent();
    });
    
    function loadDirectoryContent() {
        // Only try AJAX if we have the required variables
        if (typeof institute_templates === 'undefined') {
            console.log('AJAX variables not available, using fallback content');
            return; // Keep fallback content
        }
        
        $('#institute-directory-loading').show();
        
        $.ajax({
            url: institute_templates.ajax_url,
            type: 'POST',
            data: {
                action: 'institute_directory_load',
                nonce: institute_templates.nonce
            },
            success: function(response) {
                $('#institute-directory-loading').hide();
                if (response.success) {
                    $('#directory-results').html(response.data);
                } else {
                    console.log('AJAX failed, keeping fallback content');
                    // Keep fallback content instead of showing error
                }
            },
            error: function() {
                $('#institute-directory-loading').hide();
                console.log('AJAX error, keeping fallback content');
                // Keep fallback content instead of showing error
            }
        });
    }
    
    function performDirectorySearch() {
        var query = $('#directory-search').val();
        performDirectoryFilter({ search: query });
    }
    
    function performDirectoryFilter(extraData = {}) {
        $('#institute-directory-loading').show();
        
        var filterData = $.extend({
            action: 'institute_directory_filter',
            nonce: institute_templates.nonce,
            type: $('#directory-type-filter').val(),
            class: $('#directory-class-filter').val(),
            status: $('#directory-status-filter').val(),
            sort: $('#directory-sort').val(),
            search: $('#directory-search').val()
        }, extraData);
        
        $.ajax({
            url: institute_templates.ajax_url,
            type: 'POST',
            data: filterData,
            success: function(response) {
                $('#institute-directory-loading').hide();
                if (response.success) {
                    $('#directory-results').html(response.data);
                } else {
                    $('#directory-results').html('<p style="text-align: center; color: #ef4444;">No results found.</p>');
                }
            },
            error: function() {
                $('#institute-directory-loading').hide();
                $('#directory-results').html('<p style="text-align: center; color: #ef4444;">Error loading results.</p>');
            }
        });
    }
});
</script>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive design */
@media (max-width: 768px) {
    .institute-hero-title {
        font-size: 2rem !important;
    }
    
    .institute-hero-stats {
        flex-direction: column;
        align-items: center;
    }
    
    .institute-main-search {
        flex-direction: column !important;
    }
    
    .institute-directory-filters {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    
    .institute-directory-controls {
        flex-direction: column !important;
        gap: 1rem;
        text-align: center;
    }
    
    .institute-view-options,
    .institute-sort-options {
        justify-content: center;
    }
}
</style>

<script>
// Fix AJAX variable availability
if (typeof institute_templates === 'undefined') {
    window.institute_templates = {
        ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('institute_management_nonce'); ?>',
        strings: {
            loading: '<?php _e('Loading...', 'institute-management'); ?>',
            no_results: '<?php _e('No results found.', 'institute-management'); ?>',
            error: '<?php _e('An error occurred. Please try again.', 'institute-management'); ?>'
        }
    };
}
</script>

<?php get_footer(); ?> 