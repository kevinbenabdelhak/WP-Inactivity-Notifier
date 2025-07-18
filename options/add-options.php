<?php 
if (!defined('ABSPATH')) {
    exit;
}


function win_render_options_page() {
    ?>
    <div class="wrap">
        <h1>WP Inactivity Notifier</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('win_inactivity_options_group');
            do_settings_sections('win-inactivity-options');
            submit_button();
            ?>
        </form>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var enableReminder = document.getElementById('win-enable-recurring-reminder');
    var settingsDiv = document.getElementById('win-recurring-reminder-settings');
    var frequencyRow = document.getElementById('win_recurring_reminder_duration_field');



  
    function toggleRecurringSettings() {
        if (enableReminder.checked) {
            settingsDiv.style.display = 'block';
            frequencyRow.style.display = 'table-row';
        } else {
            settingsDiv.style.display = 'none';
            frequencyRow.style.display = 'none';
        }
    }

    enableReminder.addEventListener('change', toggleRecurringSettings);
    toggleRecurringSettings(); 
});
</script>
    </div>
    <?php
}