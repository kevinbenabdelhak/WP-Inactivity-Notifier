<?php 
if (!defined('ABSPATH')) {
    exit;
}


function win_dashboard_widget_content() {
    $selected_post_types = get_option('win_inactivity_post_types', ['post']);

    $last_post = get_posts([
        'post_type'      => $selected_post_types,
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish'
    ]);

    echo '<div id="win-inactivity-widget-content" class="widget-inactivity-content">';
    if (!empty($last_post)) {
        $last_post_timestamp    = get_post_time('U', true, $last_post[0]);
        $time_inactive_seconds  = time() - $last_post_timestamp;

        if ($time_inactive_seconds < 0) $time_inactive_seconds = 0;

        $inactivity_days_alert    = get_option('win_inactivity_days_before_alert', 7);
        $inactivity_hours_alert   = get_option('win_inactivity_hours_before_alert', 0);
        $inactivity_minutes_alert = get_option('win_inactivity_minutes_before_alert', 0);

        $inactivity_threshold_seconds =
            ($inactivity_days_alert * DAY_IN_SECONDS) +
            ($inactivity_hours_alert * HOUR_IN_SECONDS) +
            ($inactivity_minutes_alert * MINUTE_IN_SECONDS);

        $time_inactive_display = win_format_time_inactive($time_inactive_seconds);

        if ($time_inactive_seconds > $inactivity_threshold_seconds) {
            echo '<h3>⚠️ Aucune publication récente</h3>';
            echo '<p>La dernière publication date d\'il y a ' . $time_inactive_display . '. Il est temps d\'ajouter du nouveau contenu !</p>';
        } else {
            echo '<h3>✅ Publication récente</h3>';
            echo '<p>La dernière publication date d\'il y a ' . $time_inactive_display . '. Votre site est actif !</p>';
        }
    } else {
        echo '<h3>Aucune publication trouvée</h3>';
        echo '<p>Il n\'y a aucun article publié pour les types de contenu sélectionnés sur votre site. Publiez votre premier article !</p>';
    }
    echo '</div>';
}