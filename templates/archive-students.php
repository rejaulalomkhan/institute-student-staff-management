<?php
/**
 * Archive template for students
 */

get_header();

$settings = get_option('institute_management_settings', array());
$institute_name = $settings['institute_name'] ?? get_bloginfo('name');
$style = $settings['default_display_style'] ?? 'grid';
$columns = $settings['default_grid_columns'] ?? 3;
$enable_search = $settings['enable_search'] ?? true;
$enable_filters = $settings['enable_filters'] ?? true;
?>

<div class="institute-archive-wrapper">
    <div class="institute-archive-header">
        <div class="container">
            <h1 class="institute-archive-title">
                <?php _e('Our Students', 'institute-management'); ?>
                <?php if ($institute_name): ?>
                    <span class="institute-subtitle"><?php echo esc_html($institute_name); ?></span>
                <?php endif; ?>
            </h1>
            
            <?php if (have_posts()): ?>
                <p class="institute-archive-description">
                    <?php
                    global $wp_query;
                    $total = $wp_query->found_posts;
                    printf(_n('Showing %s student', 'Showing %s students', $total, 'institute-management'), number_format($total));
                    ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    


    
    <div class="institute-archive-content">
        <div class="container">
            
            <!-- Display Style Toggle -->
            <div class="institute-display-controls">
                <div class="institute-view-toggle">                    
                    <button type="button" class="view-toggle active" data-style="list" title="<?php _e('List View', 'institute-management'); ?>">
                        <span class="dashicons dashicons-list-view"></span>
                    </button>
                    <button type="button" class="view-toggle" data-style="grid" title="<?php _e('Grid View', 'institute-management'); ?>">
                        <span class="dashicons dashicons-grid-view"></span>
                    </button>
                </div>
                
                <!-- Compact Filters -->
                <div class="institute-compact-filters">
                    <div class="institute-filter-compact">
                        <label for="top-class-filter"><?php _e('Class:', 'institute-management'); ?></label>
                        <select id="top-class-filter" class="institute-compact-select">
                            <option value=""><?php _e('All Classes', 'institute-management'); ?></option>
                            <?php
                            $classes = get_terms(array(
                                'taxonomy' => 'student_class',
                                'hide_empty' => true,
                                'orderby' => 'name',
                                'order' => 'ASC'
                            ));
                            
                            if ($classes && !is_wp_error($classes)):
                                foreach ($classes as $class):
                                    $count = $class->count;
                                    echo '<option value="' . esc_attr($class->slug) . '">';
                                    echo esc_html($class->name) . ' (' . $count . ')';
                                    echo '</option>';
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </div>
                    
                    <div class="institute-filter-compact">
                        <label for="top-session-filter"><?php _e('Session:', 'institute-management'); ?></label>
                        <select id="top-session-filter" class="institute-compact-select">
                            <option value=""><?php _e('All Sessions', 'institute-management'); ?></option>
                            <?php
                            // Get unique sessions from student meta
                            global $wpdb;
                            $sessions = $wpdb->get_col("
                                SELECT DISTINCT meta_value 
                                FROM {$wpdb->postmeta} 
                                WHERE meta_key = '_student_session' 
                                AND meta_value != '' 
                                ORDER BY meta_value DESC
                            ");
                            
                            if ($sessions):
                                foreach ($sessions as $session):
                                    // Count students in this session
                                    $count = $wpdb->get_var($wpdb->prepare("
                                        SELECT COUNT(DISTINCT p.ID)
                                        FROM {$wpdb->posts} p
                                        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                                        WHERE p.post_type = 'student'
                                        AND p.post_status = 'publish'
                                        AND pm.meta_key = '_student_session'
                                        AND pm.meta_value = %s
                                    ", $session));
                                    
                                    echo '<option value="' . esc_attr($session) . '">';
                                    echo esc_html($session) . ' (' . $count . ')';
                                    echo '</option>';
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="institute-search-controls">
                    <label for="student-search-compact"><?php _e('Search:', 'institute-management'); ?></label>
                    <input type="text" id="student-search-compact" class="institute-search-input" placeholder="<?php _e('Search by name, ID, class...', 'institute-management'); ?>" />
                </div>
            </div>
            
            <!-- Results Area -->
            <div id="students-results" class="institute-students-results">
                
                <?php if (have_posts()): ?>
                
                <!-- Grid View -->
                <div class="institute-students-grid institute-<?php echo esc_attr($style); ?> institute-columns-<?php echo esc_attr($columns); ?>" style="display: none;">
                    
                    <?php while (have_posts()): the_post(); ?>
                    <article class="institute-student-card" data-student-id="<?php echo esc_attr(get_post_meta(get_the_ID(), '_student_id', true)); ?>">
                        
                        <!-- Student Photo -->
                        <div class="institute-card-photo">
                            <?php if (has_post_thumbnail()): ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium', array('class' => 'student-photo')); ?>
                                </a>
                            <?php else: ?>
                                <div class="institute-default-avatar">
                                    <span class="dashicons dashicons-admin-users"></span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Status Badge -->
                            <?php
                            $status = get_post_meta(get_the_ID(), '_student_status', true);
                            if ($status):
                            ?>
                            <span class="institute-status-badge institute-status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Student Info -->
                        <div class="institute-card-content">
                            <h3 class="institute-card-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            
                            <?php
                            $student_id = get_post_meta(get_the_ID(), '_student_id', true);
                            $role = get_post_meta(get_the_ID(), '_student_role', true);
                            $session = get_post_meta(get_the_ID(), '_student_session', true);
                            $branch = get_post_meta(get_the_ID(), '_student_branch', true);
                            $phone = get_post_meta(get_the_ID(), '_student_phone', true);
                            $classes = get_the_terms(get_the_ID(), 'student_class');
                            $batches = get_the_terms(get_the_ID(), 'student_batch');
                            ?>
                            
                            <?php if ($student_id): ?>
                            <p class="institute-card-id">
                                <strong><?php _e('ID:', 'institute-management'); ?></strong> 
                                <span><?php echo esc_html($student_id); ?></span>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($classes && !is_wp_error($classes)): ?>
                            <p class="institute-card-class">
                                <strong><?php _e('Class:', 'institute-management'); ?></strong>
                                <?php 
                                $class_names = wp_list_pluck($classes, 'name');
                                echo esc_html(implode(', ', $class_names));
                                ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($batches && !is_wp_error($batches)): ?>
                            <p class="institute-card-batch">
                                <strong><?php _e('Batch:', 'institute-management'); ?></strong>
                                <?php echo esc_html($batches[0]->name); ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($session): ?>
                            <p class="institute-card-session">
                                <strong><?php _e('Session:', 'institute-management'); ?></strong>
                                <?php echo esc_html($session); ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($branch): ?>
                            <p class="institute-card-branch">
                                <strong><?php _e('Branch:', 'institute-management'); ?></strong>
                                <?php echo esc_html($branch); ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($role): ?>
                            <p class="institute-card-role">
                                <strong><?php _e('Role:', 'institute-management'); ?></strong>
                                <?php echo esc_html($role); ?>
                            </p>
                            <?php endif; ?>
                            
                            <!-- Excerpt -->
                            <?php if (has_excerpt()): ?>
                            <div class="institute-card-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- View Profile Button -->
                            <div class="institute-card-actions">
                                <a href="<?php the_permalink(); ?>" class="institute-btn institute-btn-primary">
                                    <?php _e('View Profile', 'institute-management'); ?>
                                </a>
                            </div>
                        </div>
                        
                    </article>
                    <?php endwhile; ?>
                    
                </div>
                
                <?php rewind_posts(); ?>
                
                <!-- Table View -->
                <div class="institute-students-table">
                    <div class="institute-table-wrapper">
                        <table class="institute-data-table">
                            <thead>
                                <tr>
                                    <th class="institute-th-serial"><?php _e('S.No', 'institute-management'); ?></th>
                                    <th class="institute-th-photo"><?php _e('Photo', 'institute-management'); ?></th>
                                    <th class="institute-th-name"><?php _e('Name', 'institute-management'); ?></th>
                                    <th class="institute-th-id"><?php _e('Student ID', 'institute-management'); ?></th>
                                    <th class="institute-th-class"><?php _e('Class', 'institute-management'); ?></th>
                                    <th class="institute-th-batch"><?php _e('Batch', 'institute-management'); ?></th>
                                    <th class="institute-th-session"><?php _e('Session', 'institute-management'); ?></th>
                                    <th class="institute-th-mobile"><?php _e('Mobile', 'institute-management'); ?></th>
                                    <th class="institute-th-status"><?php _e('Status', 'institute-management'); ?></th>
                                    <th class="institute-th-actions"><?php _e('Actions', 'institute-management'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $serial = 1;
                                while (have_posts()): the_post(); 
                                    $student_id = get_post_meta(get_the_ID(), '_student_id', true);
                                    $role = get_post_meta(get_the_ID(), '_student_role', true);
                                    $session = get_post_meta(get_the_ID(), '_student_session', true);
                                    $branch = get_post_meta(get_the_ID(), '_student_branch', true);
                                    $phone = get_post_meta(get_the_ID(), '_student_phone', true);
                                    $status = get_post_meta(get_the_ID(), '_student_status', true);
                                    $classes = get_the_terms(get_the_ID(), 'student_class');
                                    $batches = get_the_terms(get_the_ID(), 'student_batch');
                                ?>
                                <tr class="institute-student-row" data-student-id="<?php echo esc_attr($student_id); ?>">
                                    
                                    <!-- Serial Number -->
                                    <td class="institute-td-serial"><?php echo $serial++; ?></td>
                                    
                                    <!-- Photo -->
                                    <td class="institute-td-photo">
                                        <?php if (has_post_thumbnail()): ?>
                                            <div class="institute-table-photo">
                                                <a href="<?php the_permalink(); ?>">
                                                    <?php the_post_thumbnail('thumbnail', array('class' => 'student-table-photo')); ?>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="institute-table-avatar">
                                                <span class="dashicons dashicons-admin-users"></span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Name -->
                                    <td class="institute-td-name">
                                        <div class="institute-name-cell">
                                            <a href="<?php the_permalink(); ?>" class="institute-student-name">
                                                <?php the_title(); ?>
                                            </a>
                                            <?php if ($role): ?>
                                                <span class="institute-student-role"><?php echo esc_html($role); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Student ID -->
                                    <td class="institute-td-id">
                                        <span class="institute-student-id"><?php echo $student_id ? esc_html($student_id) : '-'; ?></span>
                                    </td>
                                    
                                    <!-- Class -->
                                    <td class="institute-td-class">
                                        <?php if ($classes && !is_wp_error($classes)): ?>
                                            <?php 
                                            $class_names = wp_list_pluck($classes, 'name');
                                            echo esc_html(implode(', ', $class_names));
                                            ?>
                                        <?php else: ?>
                                            <span class="institute-no-data">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Batch -->
                                    <td class="institute-td-batch">
                                        <?php if ($batches && !is_wp_error($batches)): ?>
                                            <?php echo esc_html($batches[0]->name); ?>
                                        <?php else: ?>
                                            <span class="institute-no-data">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Session -->
                                    <td class="institute-td-session">
                                        <?php echo $session ? esc_html($session) : '<span class="institute-no-data">-</span>'; ?>
                                    </td>
                                    
                                    <!-- Mobile -->
                                    <td class="institute-td-mobile">
                                        <?php if ($phone): ?>
                                            <a href="tel:<?php echo esc_attr($phone); ?>" class="institute-phone-link">
                                                <?php echo esc_html($phone); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="institute-no-data">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Status -->
                                    <td class="institute-td-status">
                                        <?php if ($status): ?>
                                            <span class="institute-status-badge institute-status-<?php echo esc_attr($status); ?>">
                                                <?php echo esc_html(ucfirst($status)); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="institute-status-badge institute-status-active">
                                                <?php _e('Active', 'institute-management'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="institute-td-actions">
                                        <div class="institute-table-actions">
                                            <a href="<?php the_permalink(); ?>" class="institute-btn institute-btn-sm institute-btn-primary" title="<?php _e('View Profile', 'institute-management'); ?>">
                                                <span class="dashicons dashicons-visibility"></span>
                                            </a>
                                            <?php if (current_user_can('edit_posts')): ?>
                                                <a href="<?php echo get_edit_post_link(); ?>" class="institute-btn institute-btn-sm institute-btn-secondary" title="<?php _e('Edit', 'institute-management'); ?>">
                                                    <span class="dashicons dashicons-edit"></span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="institute-pagination">
                    <?php
                    the_posts_pagination(array(
                        'prev_text' => __('Previous', 'institute-management'),
                        'next_text' => __('Next', 'institute-management'),
                        'before_page_number' => '<span class="meta-nav screen-reader-text">' . __('Page', 'institute-management') . ' </span>',
                    ));
                    ?>
                </div>
                
                <?php else: ?>
                
                <!-- No Students Found -->
                <div class="institute-no-results">
                    <div class="no-results-icon">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                    <h3><?php _e('No students found', 'institute-management'); ?></h3>
                    <p><?php _e('There are currently no students to display. Please check back later or contact the administration.', 'institute-management'); ?></p>
                    
                    <?php if (current_user_can('edit_posts')): ?>
                    <p>
                        <a href="<?php echo admin_url('post-new.php?post_type=student'); ?>" class="institute-btn institute-btn-primary">
                            <?php _e('Add First Student', 'institute-management'); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                </div>
                
                <?php endif; ?>
                
            </div>
            
        </div>
    </div>
    
</div>

<!-- Loading Overlay -->
<div id="institute-loading" class="institute-loading-overlay" style="display: none;">
    <div class="institute-loading-spinner">
        <div class="spinner"></div>
        <p><?php _e('Loading...', 'institute-management'); ?></p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize filtering and search
    var searchTimeout;
    
    // Initialize with list view as default
    $('.institute-students-grid').hide();
    $('.institute-students-table').show();
    
    // Search functionality
    $('#student-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 500);
    });
    
    $('#student-search-btn').on('click', function() {
        performSearch();
    });
    
    // Filter functionality
    $('.institute-filter-select').on('change', function() {
        performFilter();
    });
    
    // Compact search functionality
    var searchTimeout;
    $('#student-search-compact').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performTopFilter();
        }, 500);
    });
    
    // View toggle
    $('.view-toggle').on('click', function() {
        var style = $(this).data('style');
        $('.view-toggle').removeClass('active');
        $(this).addClass('active');
        
        if (style === 'grid') {
            $('.institute-students-grid').show();
            $('.institute-students-table').hide();
        } else if (style === 'list') {
            $('.institute-students-grid').hide();
            $('.institute-students-table').show();
        }
    });
    
    // Clear filters
    $('#clear-filters').on('click', function() {
        $('.institute-filter-select').val('');
        $('#student-search').val('');
        performFilter();
    });
    
    // Compact filters functionality - auto-apply on change
    $('.institute-compact-select').on('change', function() {
        performTopFilter();
    });
    
    function performSearch() {
        var query = $('#student-search').val();
        loadResults('search', { query: query });
    }
    
    function performFilter() {
        var filters = {
            class: $('#student-class-filter').val(),
            batch: $('#student-batch-filter').val(),
            status: $('#student-status-filter').val(),
            search: $('#student-search').val()
        };
        loadResults('filter', filters);
    }
    
    function performTopFilter() {
        var filters = {
            class: $('#top-class-filter').val(),
            session: $('#top-session-filter').val(),
            search: $('#student-search-compact').val()
        };
        loadResults('top_filter', filters);
    }
    
    function loadResults(action, data) {
        $('#institute-loading').show();
        
        $.ajax({
            url: institute_templates.ajax_url,
            type: 'POST',
            data: $.extend({
                action: 'institute_students_' + action,
                nonce: institute_templates.nonce
            }, data),
            success: function(response) {
                $('#institute-loading').hide();
                if (response.success) {
                    $('#students-results').html(response.data);
                } else {
                    alert(institute_templates.strings.error);
                }
            },
            error: function() {
                $('#institute-loading').hide();
                alert(institute_templates.strings.error);
            }
        });
    }
});
</script>

<?php get_footer(); ?> 