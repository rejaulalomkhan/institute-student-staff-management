<?php
/**
 * Dashboard Widgets class
 */

namespace Institute_Management\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard Widgets class
 */
class Dashboard_Widgets {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
    }
    
    /**
     * Add dashboard widgets
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'institute_management_stats',
            __('Institute Management Statistics', 'institute-management'),
            array($this, 'stats_widget')
        );
    }
    
    /**
     * Statistics widget
     */
    public function stats_widget() {
        $student_count = wp_count_posts('student')->publish;
        $staff_count = wp_count_posts('staff')->publish;
        ?>
        <div class="institute-dashboard-widget">
            <div class="widget-content">
                <div class="institute-stats-grid">
                    <div class="institute-stat-card">
                        <span class="institute-stat-number"><?php echo $student_count; ?></span>
                        <span class="institute-stat-label"><?php _e('Students', 'institute-management'); ?></span>
                    </div>
                    <div class="institute-stat-card">
                        <span class="institute-stat-number"><?php echo $staff_count; ?></span>
                        <span class="institute-stat-label"><?php _e('Staff', 'institute-management'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
} 