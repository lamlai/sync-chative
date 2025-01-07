<div class="wrap">
    <?php if (isset($_GET["settings-updated"]) && $_GET["settings-updated"]) {
        echo '<div class="notice notice-success is-dismissible">';
        echo "<p>Settings saved. Cron schedule has been updated.</p>";
        echo "</div>";
    } ?>

    <h1>Sync Chative Settings</h1>

    <?php if (!defined('FLUENTCRM')): ?>
        <div class="notice notice-error">
            <p><strong>FluentCRM is required!</strong> Please install and activate FluentCRM to use this plugin.</p>
        </div>
    <?php endif; ?>

    <?php if ($cron_status["running"]): ?>
        <div class="notice notice-success">
            <p>Auto sync is active.</p>
        </div>
    <?php else: ?>
        <div class="notice notice-warning">
            <p>Auto sync is not active. Please save settings to activate.</p>
        </div>
    <?php endif; ?>

    <form method="post" action="options.php">
        <?php
        settings_fields("sync_chative_settings");
        do_settings_sections("sync_chative_settings");
        wp_nonce_field(
            "sync_chative_settings_action",
            "sync_chative_settings_nonce"
        );
        ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">API Key</th>
                <td>
                    <input type="text"
                           name="chative_api_key"
                           value="<?php echo esc_attr(
                               get_option("chative_api_key")
                           ); ?>"
                           style="width: 400px;"
                    />
                    <p class="description">Enter your Chative API key</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Sync Method</th>
                <td>
                    <select name="chative_webhook_enabled" id="webhook_enabled">
                        <option value="yes" <?php selected(
                            get_option("chative_webhook_enabled", "yes"),
                            "yes"
                        ); ?>>Use Webhook</option>
                        <option value="no" <?php selected(
                            get_option("chative_webhook_enabled"),
                            "no"
                        ); ?>>Direct to FluentCRM</option>
                    </select>
                    <p class="description">Choose how to sync contacts</p>
                </td>
            </tr>
            <tr valign="top" id="webhook_url_row">
                <th scope="row">Webhook URL</th>
                <td>
                    <input type="text"
                           name="chative_webhook_url"
                           value="<?php echo esc_attr(
                               get_option("chative_webhook_url")
                           ); ?>"
                           style="width: 400px;"
                    />
                    <p class="description">Enter your webhook URL (required if using webhook)</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Auto Sync Interval (minutes)</th>
                <td>
                    <input type="number"
                           name="chative_sync_interval"
                           value="<?php echo esc_attr(
                               get_option("chative_sync_interval", 5)
                           ); ?>"
                           min="1"
                           style="width: 100px;"
                    />
                    <p class="description">Enter the interval for automatic synchronization (default is 5 minutes)</p>
                </td>
            </tr>
        </table>
        <p class="submit">
            <?php submit_button("Save Changes", "primary", "submit", false); ?>
            <button type="button"
                    class="button button-secondary"
                    onclick="resetCron()"
                    style="margin-left: 10px;">
                Reset Cron Schedule
            </button>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    function toggleWebhookUrl() {
        if ($('#webhook_enabled').val() === 'yes') {
            $('#webhook_url_row').show();
        } else {
            $('#webhook_url_row').hide();
        }
    }

    $('#webhook_enabled').on('change', toggleWebhookUrl);
    toggleWebhookUrl();
});

function resetCron() {
    if (confirm('Are you sure you want to reset the cron schedule?')) {
        jQuery.post(ajaxurl, {
            action: 'reset_chative_cron',
            nonce: '<?php echo wp_create_nonce("reset_cron_nonce"); ?>'
        }, function(response) {
            if (response.success) {
                alert('Cron schedule has been reset successfully!');
                location.reload();
            } else {
                alert('Failed to reset cron schedule.');
            }
        });
    }
}
</script>
