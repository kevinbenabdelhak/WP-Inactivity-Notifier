<?php 
if (!defined('ABSPATH')) {
    exit;
}

function win_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'win_inactivity_dashboard_widget',
        'Statut des publications',
        'win_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'win_add_dashboard_widget');