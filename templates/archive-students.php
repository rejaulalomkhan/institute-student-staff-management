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
    
    <?php if ($enable_search || $enable_filters): ?>
    <div class="institute-filters-section">
        <div class="container">
            <div class="institute-filters-wrapper">
                
                <?php if ($enable_search): ?>
                <div class="institute-search-box">
                    <input type="text" id="student-search" placeholder="<?php _e('Search students by name, ID, or class...', 'institute-management'); ?>" />
                    <button type="button" id="student-search-btn">
                        <span class="dashicons dashicons-search"></span>
                        <?php _e('Search', 'institute-management'); ?>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ($enable_filters): ?>
                <div class="institute-filter-controls">
                    <select id="student-class-filter" class="institute-filter-select">
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
                    
                    <select id="student-batch-filter" class="institute-filter-select">
                        <option value=""><?php _e('All Batches', 'institute-management'); ?></option>
                        <?php
                        $batches = get_terms(array(
                            'taxonomy' => 'student_batch',
                            'hide_empty' => true,
                            'orderby' => 'name',
                            'order' => 'DESC'
                        ));
                        
                        if ($batches && !is_wp_error($batches)):
                            foreach ($batches as $batch):
                                $count = $batch->count;
                                echo '<option value="' . esc_attr($batch->slug) . '">';
                                echo esc_html($batch->name) . ' (' . $count . ')';
                                echo '</option>';
                            endforeach;
                        endif;
                        ?>
                    </select>
                    
                    <select id="student-status-filter" class="institute-filter-select">
                        <option value=""><?php _e('All Status', 'institute-management'); ?></option>
                        <option value="active"><?php _e('Active', 'institute-management'); ?></option>
                        <option value="inactive"><?php _e('Inactive', 'institute-management'); ?></option>
                        <option value="graduated"><?php _e('Graduated', 'institute-management'); ?></option>
                        <option value="suspended"><?php _e('Suspended', 'institute-management'); ?></option>
                    </select>
                    
                    <button type="button" id="clear-filters" class="institute-btn institute-btn-secondary">
                        <?php _e('Clear Filters', 'institute-management'); ?>
                    </button>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="institute-archive-content">
        <div class="container">
            
            <!-- Display Style Toggle -->
            <div class="institute-display-controls">
                <div class="institute-view-toggle">
                    <button type="button" class="view-toggle active" data-style="grid" title="<?php _e('Grid View', 'institute-management'); ?>">
                        <span class="dashicons dashicons-grid-view"></span>
                    </button>
                    <button type="button" class="view-toggle" data-style="list" title="<?php _e('List View', 'institute-management'); ?>">
                        <span class="dashicons dashicons-list-view"></span>
                    </button>
                </div>
                
                <div class="institute-sort-controls">
                    <label for="student-sort"><?php _e('Sort by:', 'institute-management'); ?></label>
                    <select id="student-sort">
                        <option value="title-asc"><?php _e('Name (A-Z)', 'institute-management'); ?></option>
                        <option value="title-desc"><?php _e('Name (Z-A)', 'institute-management'); ?></option>
                        <option value="date-desc"><?php _e('Newest First', 'institute-management'); ?></option>
                        <option value="date-asc"><?php _e('Oldest First', 'institute-management'); ?></option>
                        <option value="id-asc"><?php _e('Student ID', 'institute-management'); ?></option>
                    </select>
                </div>
            </div>
            
            <!-- Results Area -->
            <div id="students-results" class="institute-students-results">
                
                <?php if (have_posts()): ?>
                <div class="institute-students-grid institute-<?php echo esc_attr($style); ?> institute-columns-<?php echo esc_attr($columns); ?>">
                    
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
    
    // Sort functionality
    $('#student-sort').on('change', function() {
        performSort();
    });
    
    // View toggle
    $('.view-toggle').on('click', function() {
        var style = $(this).data('style');
        $('.view-toggle').removeClass('active');
        $(this).addClass('active');
        
        var $grid = $('.institute-students-grid');
        $grid.removeClass('institute-grid institute-list institute-table');
        $grid.addClass('institute-' + style);
    });
    
    // Clear filters
    $('#clear-filters').on('click', function() {
        $('.institute-filter-select').val('');
        $('#student-search').val('');
        performFilter();
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
    
    function performSort() {
        var sort = $('#student-sort').val();
        var parts = sort.split('-');
        loadResults('sort', { orderby: parts[0], order: parts[1] });
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