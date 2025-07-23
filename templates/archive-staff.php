<?php
/**
 * Archive template for staff
 */

get_header();

$settings = get_option('institute_management_settings', array());
$institute_name = $settings['institute_name'] ?? get_bloginfo('name');
$style = $settings['default_display_style'] ?? 'table';
$columns = $settings['default_grid_columns'] ?? 3;
$enable_search = $settings['enable_search'] ?? true;
$enable_filters = $settings['enable_filters'] ?? true;
?>

<div class="institute-archive-wrapper staff-archive">
    <div class="institute-archive-header">
        <div class="container">
            <h1 class="institute-archive-title">
                <span class="english-subtitle" style="color: #fff;"><?php _e('শিক্ষক মণ্ডলী ও স্টাফ', 'institute-management'); ?></span>
                
            </h1>
            
            <?php if (have_posts()): ?>
                <p class="institute-archive-description">
                    <?php
                    global $wp_query;
                    $total = $wp_query->found_posts;
                    printf(_n('Showing %s staff member', 'Showing %s staff members', $total, 'institute-management'), number_format($total));
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
                    <button type="button" class="view-toggle active" data-style="table" title="<?php _e('Table View', 'institute-management'); ?>">
                        <span class="dashicons dashicons-list-view"></span>
                    </button>
                    <button type="button" class="view-toggle" data-style="grid" title="<?php _e('Grid View', 'institute-management'); ?>">
                        <span class="dashicons dashicons-grid-view"></span>
                    </button>
                </div>
                
                <!-- Compact Filters -->
                <div class="institute-compact-filters">
                    <div class="institute-filter-compact">
                        <label for="top-department-filter"><?php _e('Department:', 'institute-management'); ?></label>
                        <select id="top-department-filter" class="institute-compact-select">
                            <option value=""><?php _e('All Departments', 'institute-management'); ?></option>
                            <?php
                            $departments = get_terms(array(
                                'taxonomy' => 'staff_department',
                                'hide_empty' => true,
                                'orderby' => 'name',
                                'order' => 'ASC'
                            ));
                            
                            if ($departments && !is_wp_error($departments)):
                                foreach ($departments as $department):
                                    $count = $department->count;
                                    echo '<option value="' . esc_attr($department->slug) . '">';
                                    echo esc_html($department->name) . ' (' . $count . ')';
                                    echo '</option>';
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </div>
                    
                    <div class="institute-filter-compact">
                        <label for="top-status-filter"><?php _e('Status:', 'institute-management'); ?></label>
                        <select id="top-status-filter" class="institute-compact-select">
                            <option value=""><?php _e('All Status', 'institute-management'); ?></option>
                            <option value="active"><?php _e('Active', 'institute-management'); ?></option>
                            <option value="inactive"><?php _e('Inactive', 'institute-management'); ?></option>
                            <option value="on-leave"><?php _e('On Leave', 'institute-management'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="institute-search-controls">
                    <label for="staff-search-compact"><?php _e('Search:', 'institute-management'); ?></label>
                    <input type="text" id="staff-search-compact" class="institute-search-input" placeholder="<?php _e('Search by name, ID, position...', 'institute-management'); ?>" />
                </div>
            </div>
            
            <!-- Results Area -->
            <div id="staff-results" class="institute-staff-results">
                
                <?php if (have_posts()): ?>
                
                <!-- Staff Table View -->
                <div class="institute-staff-table">
                    <div class="institute-table-wrapper">
                        <table class="institute-data-table staff-data-table">
                            <thead>
                                <tr>
                                    <th class="institute-th-serial"><?php _e('S.No', 'institute-management'); ?></th>
                                    <th class="institute-th-photo"><?php _e('Image', 'institute-management'); ?></th>
                                    <th class="institute-th-name"><?php _e('Teacher Name', 'institute-management'); ?></th>
                                    <th class="institute-th-mobile"><?php _e('Mobile Number', 'institute-management'); ?></th>
                                    <th class="institute-th-designation"><?php _e('Designation', 'institute-management'); ?></th>
                                    <th class="institute-th-department"><?php _e('Department', 'institute-management'); ?></th>
                                    <th class="institute-th-status"><?php _e('Status', 'institute-management'); ?></th>
                                    <th class="institute-th-actions"><?php _e('Actions', 'institute-management'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $serial = 1;
                                while (have_posts()): the_post(); 
                                    $staff_id = get_post_meta(get_the_ID(), '_staff_id', true);
                                    $position = get_post_meta(get_the_ID(), '_staff_position', true);
                                    $phone = get_post_meta(get_the_ID(), '_staff_phone', true);
                                    $email = get_post_meta(get_the_ID(), '_staff_email', true);
                                    $status = get_post_meta(get_the_ID(), '_staff_status', true);
                                    $departments = get_the_terms(get_the_ID(), 'staff_department');
                                    
                                    // Format phone number - show as requested format
                                    $formatted_phone = $phone;
                                    if ($phone && !empty($phone)) {
                                        // If phone number exists, keep as is, but mask if needed for privacy
                                        if (strlen($phone) > 6) {
                                            $formatted_phone = substr($phone, 0, 3) . 'XXXXXXXX';
                                        }
                                    }
                                ?>
                                <tr class="institute-staff-row" data-staff-id="<?php echo esc_attr($staff_id); ?>">
                                    
                                    <!-- Serial Number -->
                                    <td class="institute-td-serial"><?php echo $serial++; ?></td>
                                    
                                    <!-- Photo -->
                                    <td class="institute-td-photo">
                                        <?php if (has_post_thumbnail()): ?>
                                            <div class="institute-table-photo staff-photo">
                                                <a href="<?php the_permalink(); ?>">
                                                    <?php the_post_thumbnail('thumbnail', array('class' => 'staff-table-photo')); ?>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="institute-table-avatar staff-avatar">
                                                <span class="dashicons dashicons-businessperson"></span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Name -->
                                    <td class="institute-td-name">
                                        <div class="institute-name-cell">
                                            <a href="<?php the_permalink(); ?>" class="institute-staff-name">
                                                <?php the_title(); ?>
                                            </a>
                                            <?php if ($staff_id): ?>
                                                <span class="institute-staff-id"><?php _e('ID:', 'institute-management'); ?> <?php echo esc_html($staff_id); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Mobile Number -->
                                    <td class="institute-td-mobile">
                                        <?php if ($phone): ?>
                                            <span class="institute-phone-number">
                                                <?php echo esc_html($formatted_phone); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="institute-no-data">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Designation/Position -->
                                    <td class="institute-td-designation">
                                        <?php if ($position): ?>
                                            <span class="institute-position">
                                                <?php echo esc_html($position); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="institute-no-data">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Department -->
                                    <td class="institute-td-department">
                                        <?php if ($departments && !is_wp_error($departments)): ?>
                                            <?php 
                                            $dept_names = wp_list_pluck($departments, 'name');
                                            echo esc_html(implode(', ', $dept_names));
                                            ?>
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
                                            <?php if ($phone && !empty($phone)): ?>
                                                <a href="tel:<?php echo esc_attr($phone); ?>" class="institute-btn institute-btn-sm institute-btn-success" title="<?php _e('Call', 'institute-management'); ?>">
                                                    <span class="dashicons dashicons-phone"></span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($email && !empty($email)): ?>
                                                <a href="mailto:<?php echo esc_attr($email); ?>" class="institute-btn institute-btn-sm institute-btn-info" title="<?php _e('Email', 'institute-management'); ?>">
                                                    <span class="dashicons dashicons-email"></span>
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
                
                <?php rewind_posts(); ?>
                
                <!-- Grid View -->
                <div class="institute-staff-grid institute-<?php echo esc_attr($style); ?> institute-columns-<?php echo esc_attr($columns); ?>" style="display: none;">
                    
                    <?php while (have_posts()): the_post(); ?>
                    <article class="institute-staff-card" data-staff-id="<?php echo esc_attr(get_post_meta(get_the_ID(), '_staff_id', true)); ?>">
                        
                        <!-- Staff Photo -->
                        <div class="institute-card-photo">
                            <?php if (has_post_thumbnail()): ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium', array('class' => 'staff-photo')); ?>
                                </a>
                            <?php else: ?>
                                <div class="institute-default-avatar">
                                    <span class="dashicons dashicons-businessperson"></span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Status Badge -->
                            <?php
                            $status = get_post_meta(get_the_ID(), '_staff_status', true);
                            if ($status):
                            ?>
                            <span class="institute-status-badge institute-status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Staff Info -->
                        <div class="institute-card-content">
                            <h3 class="institute-card-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            
                            <?php
                            $staff_id = get_post_meta(get_the_ID(), '_staff_id', true);
                            $position = get_post_meta(get_the_ID(), '_staff_position', true);
                            $phone = get_post_meta(get_the_ID(), '_staff_phone', true);
                            $email = get_post_meta(get_the_ID(), '_staff_email', true);
                            $departments = get_the_terms(get_the_ID(), 'staff_department');
                            ?>
                            
                            <?php if ($staff_id): ?>
                            <p class="institute-card-id">
                                <strong><?php _e('ID:', 'institute-management'); ?></strong> 
                                <span><?php echo esc_html($staff_id); ?></span>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($position): ?>
                            <p class="institute-card-position">
                                <strong><?php _e('Position:', 'institute-management'); ?></strong>
                                <?php echo esc_html($position); ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($departments && !is_wp_error($departments)): ?>
                            <p class="institute-card-department">
                                <strong><?php _e('Department:', 'institute-management'); ?></strong>
                                <?php 
                                $dept_names = wp_list_pluck($departments, 'name');
                                echo esc_html(implode(', ', $dept_names));
                                ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($phone): ?>
                            <p class="institute-card-phone">
                                <strong><?php _e('Phone:', 'institute-management'); ?></strong>
                                <a href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_html($phone); ?></a>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($email): ?>
                            <p class="institute-card-email">
                                <strong><?php _e('Email:', 'institute-management'); ?></strong>
                                <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
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
                
                <!-- No Staff Found -->
                <div class="institute-no-results">
                    <div class="no-results-icon">
                        <span class="dashicons dashicons-businessperson"></span>
                    </div>
                    <h3><?php _e('No staff found', 'institute-management'); ?></h3>
                    <p><?php _e('There are currently no staff members to display. Please check back later or contact the administration.', 'institute-management'); ?></p>
                    
                    <?php if (current_user_can('edit_posts')): ?>
                    <p>
                        <a href="<?php echo admin_url('post-new.php?post_type=staff'); ?>" class="institute-btn institute-btn-primary">
                            <?php _e('Add First Staff Member', 'institute-management'); ?>
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

<style>
.staff-archive .bengali-title {
    font-size: 2.5em;
    font-weight: bold;
    color: #2c5aa0;
    display: block;
    margin-bottom: 10px;
}

.staff-archive .english-subtitle {
    font-size: 1.2em;
    color: #666;
    display: block;
}

.staff-data-table {
    border-collapse: collapse;
    width: 100%;
    margin: 20px 0;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.staff-data-table th {
    background: linear-gradient(135deg, #2c5aa0 0%, #1e3a6f 100%);
    color: white;
    padding: 15px 10px;
    text-align: center;
    font-weight: 600;
    border: 1px solid #ddd;
}

.staff-data-table td {
    padding: 12px 10px;
    text-align: center;
    border: 1px solid #ddd;
    vertical-align: middle;
}

.staff-data-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.staff-data-table tbody tr:hover {
    background-color: #f5f5f5;
}

.staff-photo {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ddd;
}

.staff-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.staff-avatar .dashicons {
    font-size: 30px;
    color: #999;
}

.institute-staff-name {
    font-weight: 600;
    color: #2c5aa0;
    text-decoration: none;
    font-size: 1.1em;
}

.institute-staff-name:hover {
    color: #1e3a6f;
}

.institute-staff-id {
    display: block;
    font-size: 0.9em;
    color: #666;
    margin-top: 5px;
}

.institute-phone-number {
    font-family: monospace;
    font-weight: 600;
    color: #333;
}

.institute-position {
    font-weight: 600;
    color: #2c5aa0;
}

.institute-table-actions {
    display: flex;
    gap: 5px;
    justify-content: center;
    flex-wrap: wrap;
}

.institute-btn-sm {
    padding: 5px 8px;
    font-size: 12px;
    border-radius: 3px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.institute-btn-primary {
    background: #2c5aa0;
    color: white;
}

.institute-btn-secondary {
    background: #6c757d;
    color: white;
}

.institute-btn-success {
    background: #28a745;
    color: white;
}

.institute-btn-info {
    background: #17a2b8;
    color: white;
}

.institute-btn:hover {
    opacity: 0.8;
}

.institute-status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: uppercase;
}

.institute-status-active {
    background: #d4edda;
    color: #155724;
}

.institute-status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.institute-status-on-leave {
    background: #fff3cd;
    color: #856404;
}

@media (max-width: 768px) {
    .staff-data-table {
        font-size: 0.9em;
    }
    
    .staff-data-table th,
    .staff-data-table td {
        padding: 8px 5px;
    }
    
    .institute-table-actions {
        flex-direction: column;
        gap: 3px;
    }
    
    .staff-photo {
        width: 50px;
        height: 50px;
    }
    
    .staff-avatar {
        width: 50px;
        height: 50px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Initialize filtering and search
    var searchTimeout;
    
    // Initialize with table view as default
    $('.institute-staff-grid').hide();
    $('.institute-staff-table').show();
    
    // Search functionality
    $('#staff-search-compact').on('input', function() {
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
            $('.institute-staff-grid').show();
            $('.institute-staff-table').hide();
        } else if (style === 'table') {
            $('.institute-staff-grid').hide();
            $('.institute-staff-table').show();
        }
    });
    
    // Compact filters functionality - auto-apply on change
    $('.institute-compact-select').on('change', function() {
        performTopFilter();
    });
    
    function performTopFilter() {
        var filters = {
            department: $('#top-department-filter').val(),
            status: $('#top-status-filter').val(),
            search: $('#staff-search-compact').val()
        };
        loadResults('top_filter', filters);
    }
    
    function loadResults(action, data) {
        $('#institute-loading').show();
        
        $.ajax({
            url: institute_templates.ajax_url,
            type: 'POST',
            data: $.extend({
                action: 'institute_staff_' + action,
                nonce: institute_templates.nonce
            }, data),
            success: function(response) {
                $('#institute-loading').hide();
                if (response.success) {
                    $('#staff-results').html(response.data);
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