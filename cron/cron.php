<?php 
if (!defined('ABSPATH')) {
    exit;
}




function win_activate() {
    if (!wp_next_scheduled('win_check_inactivity_hook'))
        wp_schedule_event(time(), 'hourly', 'win_check_inactivity_hook');
}
register_activation_hook(__FILE__, 'win_activate');


function win_deactivate() {
    wp_clear_scheduled_hook('win_check_inactivity_hook');
    delete_option('win_last_alert_sent_date');
}
register_deactivation_hook(__FILE__, 'win_deactivate');

add_action('win_check_inactivity_hook', 'win_check_inactivity');


function win_trigger_wp_cron() {
    static $ran = false;
    if ($ran) return;
    $ran = true;

    if (!defined('DOING_CRON') && get_transient('doing_cron') === false) {
        if (!wp_next_scheduled('win_check_inactivity_hook')) {
            wp_schedule_event(time(), 'hourly', 'win_check_inactivity_hook');
        }
        spawn_cron();
    }
}
add_action('init', 'win_trigger_wp_cron');


function win_check_inactivity_init_trigger() {
    static $ran = false;
    if ($ran) return;
    $ran = true;
    win_check_inactivity();
}
add_action('init', 'win_check_inactivity_init_trigger');
