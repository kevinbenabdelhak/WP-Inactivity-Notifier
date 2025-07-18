<?php 
if (!defined('ABSPATH')) {
    exit;
}

function win_check_inactivity() {
    $admin_emails = get_option('win_inactivity_admin_email', []);
    if (empty($admin_emails)) $admin_emails = [get_bloginfo('admin_email')];
    if (!is_array($admin_emails)) $admin_emails = [$admin_emails];

    $inactivity_days = (int) get_option('win_inactivity_days_before_alert', 0);
    $inactivity_hours = (int) get_option('win_inactivity_hours_before_alert', 0);
    $inactivity_minutes = (int) get_option('win_inactivity_minutes_before_alert', 3);

    $threshold = ($inactivity_days * DAY_IN_SECONDS) + ($inactivity_hours * HOUR_IN_SECONDS) + ($inactivity_minutes * MINUTE_IN_SECONDS);

    $post_types = get_option('win_inactivity_post_types', ['post']);
    $last_alert_sent = (int) get_option('win_last_alert_sent_date', 0);

    $site_name = get_bloginfo('name');
    $site_url  = home_url('/');
    $site_link = '<a href="' . esc_url($site_url) . '">' . esc_html($site_name) . '</a>';

    $subject_default  = 'Aucune publication récente sur ' . $site_name;
    $message_default = "Aucun article n'a été publié sur votre site depuis plus de [temps_inactivite].";

    $last_posts = get_posts([
        'post_type'      => $post_types,
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish'
    ]);

    if (!empty($last_posts)) {
        $last_post_time = get_post_time('U', true, $last_posts[0]);
        $inactive_seconds = time() - $last_post_time;
        if ($inactive_seconds < 0) $inactive_seconds = 0;

        if ($last_post_time > $last_alert_sent) {
            update_option('win_last_alert_sent_date', 0);
            $last_alert_sent = 0;
        }

        // 1ere alerte
        if ($inactive_seconds > $threshold && $last_alert_sent == 0) {
            $subject = str_replace('[temps_inactivite]', win_format_time_inactive($inactive_seconds), $subject_default);
            $message_with_time = str_replace('[temps_inactivite]', win_format_time_inactive($inactive_seconds), $message_default);
            $message_final = str_ireplace('votre site', $site_link, $message_with_time);
            foreach ($admin_emails as $mail) {
                wp_mail(
                    $mail,
                    $subject,
                    $message_final,
                    ['Content-Type: text/html; charset=UTF-8']
                );
            }
            update_option('win_last_alert_sent_date', time());

            $log = get_option('win_email_log', []);
            $log[] = sprintf('[%s] Alerte envoyée, inactivité : %s', date_i18n('d/m/Y H:i:s'), win_format_time_inactive($inactive_seconds));
            if (count($log) > 20) $log = array_slice($log, -20);
            update_option('win_email_log', $log);
        }


        $enable_recurring = get_option('win_enable_recurring_reminder', 0);
        $rec_days    = (int)get_option('win_recurring_reminder_days', 0);
        $rec_hours   = (int)get_option('win_recurring_reminder_hours', 0);
        $rec_minutes = (int)get_option('win_recurring_reminder_minutes', 0);
        $interval = ($rec_days * DAY_IN_SECONDS) + ($rec_hours * HOUR_IN_SECONDS) + ($rec_minutes * MINUTE_IN_SECONDS);

        if (
            $inactive_seconds > $threshold &&
            $enable_recurring && $interval > 0 &&
            $last_alert_sent > 0 &&
            (time() - $last_alert_sent) >= $interval
        ) {
            $subject = '[Rappel] ' . str_replace('[temps_inactivite]', win_format_time_inactive($inactive_seconds), $subject_default);
            $message_with_time = str_replace('[temps_inactivite]', win_format_time_inactive($inactive_seconds), $message_default);
            $message_final = str_ireplace('votre site', $site_link, $message_with_time);
            foreach ($admin_emails as $mail) {
                wp_mail(
                    $mail,
                    $subject,
                    $message_final,
                    ['Content-Type: text/html; charset=UTF-8']
                );
            }
            update_option('win_last_alert_sent_date', time());

            $log = get_option('win_email_log', []);
            $log[] = sprintf('[%s] Rappel envoyé, inactivité : %s', date_i18n('d/m/Y H:i:s'), win_format_time_inactive($inactive_seconds));
            if (count($log) > 20) $log = array_slice($log, -20);
            update_option('win_email_log', $log);
        }

    } else {
        if ($last_alert_sent == 0) {
            $subject = $subject_default;
            $message_no_post = 'Il n\'y a aucun article publié pour les types sélectionnés. Veuillez publier votre premier article.';
            $message_final   = str_ireplace('votre site', $site_link, $message_no_post);

            foreach ($admin_emails as $mail) {
                wp_mail(
                    $mail,
                    $subject,
                    $message_final,
                    ['Content-Type: text/html; charset=UTF-8']
                );
            }
            update_option('win_last_alert_sent_date', time());

            $log = get_option('win_email_log', []);
            $log[] = sprintf('[%s] Alerte envoyée, aucun article publié.', date_i18n('d/m/Y H:i:s'));
            if (count($log) > 20) $log = array_slice($log, -20);
            update_option('win_email_log', $log);
        }
    }
}








/* script format */

function win_format_time_inactive($seconds) {
    if ($seconds < 0) $seconds = 0;

    $days = floor($seconds / DAY_IN_SECONDS);
    $seconds %= DAY_IN_SECONDS;
    $hours = floor($seconds / HOUR_IN_SECONDS);
    $seconds %= HOUR_IN_SECONDS;
    $minutes = floor($seconds / MINUTE_IN_SECONDS);

    $parts = [];
    if ($days > 0) $parts[] = sprintf(_n('%d jour', '%d jours', $days, 'wp-inactivity-notifier'), $days);
    if ($hours > 0) $parts[] = sprintf(_n('%d heure', '%d heures', $hours, 'wp-inactivity-notifier'), $hours);
    if ($minutes > 0) $parts[] = sprintf(_n('%d minute', '%d minutes', $minutes), $minutes);

    if (empty($parts)) return __('moins d\'une minute', 'wp-inactivity-notifier');

    if (count($parts) > 1) {
        $last = array_pop($parts);
        return implode(', ', $parts) . ' et ' . $last;
    }

    return $parts[0];
}