<?php 
if (!defined('ABSPATH')) {
    exit;
}


/* Page d’options */

function win_add_options_page() {
    add_options_page(
        'WP Inactivity Notifier',
        'WP Inactivity Notifier',
        'manage_options',
        'win-inactivity-options',
        'win_render_options_page'
    );
}
add_action('admin_menu', 'win_add_options_page');


function win_register_settings() {
    register_setting('win_inactivity_options_group', 'win_inactivity_admin_email', 'win_sanitize_admin_emails');
    register_setting('win_inactivity_options_group', 'win_inactivity_days_before_alert', 'absint');
    register_setting('win_inactivity_options_group', 'win_inactivity_hours_before_alert', 'absint');
    register_setting('win_inactivity_options_group', 'win_inactivity_minutes_before_alert', 'absint');
    register_setting('win_inactivity_options_group', 'win_inactivity_post_types', 'win_sanitize_post_types');
    register_setting('win_inactivity_options_group', 'win_enable_recurring_reminder', 'absint');
    register_setting('win_inactivity_options_group', 'win_recurring_reminder_days', 'absint');
    register_setting('win_inactivity_options_group', 'win_recurring_reminder_hours', 'absint');
    register_setting('win_inactivity_options_group', 'win_recurring_reminder_minutes', 'absint');

    add_settings_section('win_inactivity_main_section', 'Paramètres des alertes de contenu', 'win_inactivity_section_callback', 'win-inactivity-options');

    add_settings_field('win_inactivity_admin_email_field',      'Adresse(s) e-mail pour l\'alerte',           'win_admin_email_callback',             'win-inactivity-options', 'win_inactivity_main_section');
    add_settings_field('win_inactivity_duration_field',        'Seuil d\'inactivité avant alerte',           'win_inactivity_duration_callback',     'win-inactivity-options', 'win_inactivity_main_section');
    add_settings_field('win_recurring_reminder_enable_field',  'Activer les rappels récurrents',             'win_recurring_reminder_enable_callback','win-inactivity-options','win_inactivity_main_section');
    add_settings_field('win_recurring_reminder_duration_field','Fréquence des rappels récurrents',           'win_recurring_reminder_duration_callback', 'win-inactivity-options','win_inactivity_main_section');
    add_settings_field('win_inactivity_post_types_field',      'Types de contenu à surveiller',               'win_post_types_callback',              'win-inactivity-options', 'win_inactivity_main_section');
    add_settings_field('win_email_summary_field',              'Récapitulatif des e-mails envoyés',           'win_email_summary_callback',           'win-inactivity-options', 'win_inactivity_main_section');
}
add_action('admin_init', 'win_register_settings');






/*champ admin_emails */
function win_sanitize_admin_emails($input) {
    if (is_array($input)) $input = implode("\n", $input);
    $lines = preg_split("/\r\n|\n|\r/", trim($input));
    $clean = array();
    foreach ($lines as $email) {
        $email = trim($email);
        if (is_email($email)) $clean[] = $email;
    }
    return $clean;
}

/** Callbacks pour la page d’options */

function win_inactivity_section_callback() {
    echo '<p>Configurez les paramètres pour les alertes de mise à jour de contenu.</p>';
}

function win_admin_email_callback() {
    $admin_emails = get_option('win_inactivity_admin_email', get_bloginfo('admin_email'));
    if (is_array($admin_emails)) {
        $admin_emails = implode("\n", $admin_emails);
    }
    echo '<textarea name="win_inactivity_admin_email" rows="4" cols="50" class="large-text" placeholder="ex: mail1@exemple.com&#10;mail2@exemple.com">' . esc_textarea($admin_emails) . '</textarea>';
    echo '<p class="description">Un e-mail par ligne. Les notifications seront envoyées à toutes les adresses listées.</p>';
}

function win_inactivity_duration_callback() {
    $days = get_option('win_inactivity_days_before_alert', 7);
    $hours = get_option('win_inactivity_hours_before_alert', 0);
    $minutes = get_option('win_inactivity_minutes_before_alert', 0);

    echo '<input type="number" name="win_inactivity_days_before_alert" value="' . esc_attr($days) . '" min="0" class="small-text"> jours ';
    echo '<input type="number" name="win_inactivity_hours_before_alert" value="' . esc_attr($hours) . '" min="0" max="23" class="small-text"> heures ';
    echo '<input type="number" name="win_inactivity_minutes_before_alert" value="' . esc_attr($minutes) . '" min="0" max="59" class="small-text"> minutes';
    echo '<p class="description">Durée d\'inactivité avant envoi de l\'alerte.</p>';
}

function win_recurring_reminder_enable_callback() {
    $checked = get_option('win_enable_recurring_reminder', 0) ? 'checked="checked"' : '';
    echo '<label><input type="checkbox" name="win_enable_recurring_reminder" id="win-enable-recurring-reminder" value="1" ' . $checked . '> Activer les rappels récurrents en cas d\'inactivité prolongée</label>';
}

function win_recurring_reminder_duration_callback() {
    $days = get_option('win_recurring_reminder_days', 0);
    $hours = get_option('win_recurring_reminder_hours', 0);
    $minutes = get_option('win_recurring_reminder_minutes', 0);


    echo '<div id="win-recurring-reminder-settings" style="margin-left:20px;' . (get_option('win_enable_recurring_reminder', 0) ? '' : 'display:none;') . '">';
    echo '<input type="number" name="win_recurring_reminder_days" value="' . esc_attr($days) . '" min="0" class="small-text"> jours ';
    echo '<input type="number" name="win_recurring_reminder_hours" value="' . esc_attr($hours) . '" min="0" max="23" class="small-text"> heures ';
    echo '<input type="number" name="win_recurring_reminder_minutes" value="' . esc_attr($minutes) . '" min="0" max="59" class="small-text"> minutes';
    echo '<p class="description">Intervalle entre chaque rappel après la première alerte envoyée.</p>';
    echo '</div>';
}

function win_post_types_callback() {
    $selected_post_types = get_option('win_inactivity_post_types', ['post']);
    $post_types = get_post_types(['public' => true], 'objects');

    echo '<fieldset>';
    foreach ($post_types as $post_type_obj) {
        if ($post_type_obj->name === 'attachment') continue;
        $checked = in_array($post_type_obj->name, $selected_post_types) ? 'checked="checked"' : '';
        echo '<label><input type="checkbox" name="win_inactivity_post_types[]" value="' . esc_attr($post_type_obj->name) . '" ' . $checked . '> ' . esc_html($post_type_obj->labels->singular_name) . '</label><br>';
    }
    echo '<p class="description">Types de contenu à surveiller.</p></fieldset>';
}

function win_sanitize_post_types($input) {
    $valid_post_types = [];
    $all_public_post_types = get_post_types(['public' => true], 'names');

    if (is_array($input)) {
        foreach ($input as $post_type) {
            if (in_array($post_type, $all_public_post_types)) {
                $valid_post_types[] = sanitize_key($post_type);
            }
        }
    }
    return empty($valid_post_types) ? ['post'] : $valid_post_types;
}

function win_email_summary_callback() {
    $summary = get_option('win_email_log', []);
    if (empty($summary)) {
        echo '<p>Aucun e-mail envoyé jusqu\'à présent.</p>';
    } else {
        $summary = array_reverse($summary);
        echo '<ul>';
        foreach ($summary as $email_record) {
            echo '<li>' . esc_html($email_record) . '</li>';
        }
        echo '</ul>';
    }
}