<?php
if (!function_exists("write_log")) {
    function write_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }
}

function activate_sync_chative()
{
    try {
        create_logs_table();
        schedule_sync_chative();
        write_log("Plugin activated successfully");
    } catch (Exception $e) {
        write_log("Activation error: " . $e->getMessage());
    }
}

function deactivate_sync_chative()
{
    wp_clear_scheduled_hook("sync_chative_contacts");
    write_log("Plugin deactivated");
}

function my_email_plugin_page()
{
    if (
        isset($_POST["manual_sync"]) &&
        check_admin_referer("manual_sync_action")
    ) {
        sync_chative_contacts();
        echo '<div class="notice notice-success"><p>Manual sync completed!</p></div>';
    } ?>
    <div class="wrap">
        <h1>Sync Chative Contacts</h1>
        
        <?php if (!defined('FLUENTCRM')): ?>
            <div class="notice notice-error">
                <p><strong>Important:</strong> This plugin requires FluentCRM to be installed and activated. Please install FluentCRM to use this plugin.</p>
            </div>
        <?php endif; ?>

        <p>Welcome to Sync Chative plugin. This plugin requires FluentCRM to function properly. Please use the settings page to configure the plugin.</p>

        <div class="manual-sync-section">
            <h2>Manual Sync</h2>
            <p>Click the button below to sync immediately:</p>

            <form method="post" action="">
                <?php wp_nonce_field("manual_sync_action"); ?>
                <input type="submit"
                       name="manual_sync"
                       class="button button-primary"
                       value="Sync Now"
                       style="margin-top: 10px;"
                />
            </form>
        </div>

        <div class="auto-sync-info">
            <h2>Auto Sync Information</h2>
            <?php
            $cron_status = is_cron_running();
            if ($cron_status["running"]): ?>
                <p>✅ Auto sync is active</p>
            <?php else: ?>
                <p>❌ Auto sync is not active</p>
                <p>Please check your settings in the Settings page</p>
            <?php endif;?>
        </div>
    </div>
    <?php
}

function my_email_plugin_menu()
{
    add_menu_page(
        "Sync Chative",
        "Sync Chative",
        "manage_options",
        "sync-chative",
        "my_email_plugin_page",
        "dashicons-email-alt",
        6
    );

    add_submenu_page(
        "sync-chative",
        "Sync Chative",
        "Sync Contacts",
        "manage_options",
        "sync-chative",
        "my_email_plugin_page"
    );

    add_submenu_page(
        "sync-chative",
        "Settings",
        "Settings",
        "manage_options",
        "sync-chative-settings",
        "render_settings_page"
    );

    add_submenu_page(
        "sync-chative",
        "Logs",
        "Logs",
        "manage_options",
        "sync-chative-logs",
        "render_logs_page"
    );
}

function register_sync_chative_settings()
{
    register_setting("sync_chative_settings", "chative_api_key");
    register_setting("sync_chative_settings", "chative_webhook_url");
    register_setting("sync_chative_settings", "chative_webhook_enabled");
    register_setting("sync_chative_settings", "chative_sync_interval", [
        "sanitize_callback" => "handle_sync_interval_change",
    ]);
}

function handle_sync_interval_change($new_value)
{
    $old_value = get_option("chative_sync_interval");
    if ($new_value !== $old_value) {
        wp_clear_scheduled_hook("sync_chative_contacts");
        if (!wp_next_scheduled("sync_chative_contacts")) {
            wp_schedule_event(
                time(),
                "custom_interval",
                "sync_chative_contacts"
            );
        }
    }
    return $new_value;
}

function is_cron_running()
{
    $next_scheduled = wp_next_scheduled("sync_chative_contacts");
    if ($next_scheduled) {
        $datetime = new DateTime();
        $datetime->setTimestamp($next_scheduled);
        $datetime->setTimezone(new DateTimeZone(wp_timezone_string()));

        return [
            "running" => true,
            "next_run" => $datetime->format("Y-m-d H:i:s"),
        ];
    }
    return ["running" => false];
}

function add_cron_interval($schedules)
{
    $sync_interval = get_option("chative_sync_interval", 5);
    write_log("Setting up cron interval: " . $sync_interval . " minutes");

    $schedules["custom_interval"] = [
        "interval" => absint($sync_interval) * 60,
        "display" => sprintf("Every %d minutes", $sync_interval),
    ];

    write_log("Available schedules: " . print_r($schedules, true));
    return $schedules;
}

function schedule_sync_chative()
{
    write_log("Attempting to schedule sync...");

    wp_clear_scheduled_hook("sync_chative_contacts");
    write_log("Cleared existing schedule");

    $scheduled = wp_schedule_event(
        time(),
        "custom_interval",
        "sync_chative_contacts"
    );

    if ($scheduled === false) {
        write_log("Failed to schedule sync");
    } else {
        write_log("Successfully scheduled sync");
        write_log(
            "Next run scheduled for: " .
                date("Y-m-d H:i:s", wp_next_scheduled("sync_chative_contacts"))
        );
    }
}

function sync_chative_contacts()
{
    try {
        $api_key = get_option("chative_api_key");
        $webhook_enabled = get_option("chative_webhook_enabled", "yes");
        $webhook_url = get_option("chative_webhook_url");

        if (empty($api_key)) {
            write_log("Missing API key");
            return;
        }

        if ($webhook_enabled === "yes" && empty($webhook_url)) {
            write_log("Webhook is enabled but URL is missing");
            return;
        }

        if (!defined('FLUENTCRM')) {
            write_log("FluentCRM is not installed or activated");
            return;
        }

        $unique_contacts = get_unique_emails();
        
        if ($webhook_enabled === "yes") {
            foreach ($unique_contacts as $contact) {
                if (send_to_webhook($contact)) {
                    add_sync_log($contact['email'], 'webhook', 'success');
                } else {
                    add_sync_log($contact['email'], 'webhook', 'failed');
                }
            }
        } else {
            foreach ($unique_contacts as $contact) {
                if (add_to_fluentcrm($contact['email'])) {
                    add_sync_log($contact['email'], 'direct', 'success');
                } else {
                    add_sync_log($contact['email'], 'direct', 'failed');
                }
            }
        }

        write_log("Chative sync executed at " . date("Y-m-d H:i:s"));
    } catch (Exception $e) {
        write_log("Sync error: " . $e->getMessage());
    }
}

function add_to_fluentcrm($email) {
    if (!defined('FLUENTCRM')) {
        return false;
    }

    try {
        $contact = FluentCrm\App\Models\Subscriber::create([
            'email' => $email,
            'status' => 'subscribed'
        ]);
        
        return $contact ? true : false;
    } catch (Exception $e) {
        write_log("FluentCRM sync error: " . $e->getMessage());
        return false;
    }
}

function fetch_emails_from_api($page = 0)
{
    $api_key = get_option("chative_api_key");
    if (empty($api_key)) {
        return null;
    }

    $url = "https://api.chative.io/v1.0/contacts?page=" . $page . "&limit=50";
    $response = wp_remote_get($url, [
        "headers" => ["Authorization" => "Bearer " . $api_key],
    ]);

    if (is_wp_error($response)) {
        write_log("API error: " . $response->get_error_message());
        return null;
    }

    return json_decode(wp_remote_retrieve_body($response), true);
}

function get_all_contacts_from_api()
{
    $page = 0;
    $all_contacts = [];

    while (true) {
        $data = fetch_emails_from_api($page);
        if (!$data || empty($data["list"])) {
            break;
        }

        foreach ($data["list"] as $contact) {
            if (!empty($contact["email"])) {
                $all_contacts[] = [
                    "email" => $contact["email"],
                    "firstName" => $contact["firstName"] ?? "",
                    "lastName" => $contact["lastName"] ?? "",
                    "phone" => $contact["phone"] ?? "",
                ];
            }
        }
        $page++;
    }

    return $all_contacts;
}

function get_unique_emails()
{
    $api_contacts = get_all_contacts_from_api();
    $existing_emails = get_existing_emails();

    return array_filter($api_contacts, function ($contact) use (
        $existing_emails
    ) {
        return !in_array($contact["email"], $existing_emails);
    });
}

function get_existing_emails()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "fc_subscribers";
    return $wpdb->get_col("SELECT email FROM $table_name");
}

function send_to_webhook($contact)
{
    $webhook_url = get_option("chative_webhook_url");
    $response = wp_remote_post($webhook_url, [
        "headers" => ["Content-Type" => "application/json"],
        "body" => json_encode($contact),
        "timeout" => 30,
    ]);
    return !is_wp_error($response);
}

function create_logs_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_chative_logs';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        sync_type varchar(50) NOT NULL,
        status varchar(50) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function add_sync_log($email, $sync_type, $status) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_chative_logs';
    
    return $wpdb->insert(
        $table_name,
        array(
            'email' => $email,
            'sync_type' => $sync_type,
            'status' => $status
        ),
        array('%s', '%s', '%s')
    );
}

function delete_logs($period = 'all') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_chative_logs';
    
    if ($period === 'all') {
        $result = $wpdb->query("TRUNCATE TABLE $table_name");
    } else {
        $days = intval($period);
        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );
    }
    
    return $result !== false;
}

function get_sync_logs($per_page = 20, $page = 1, $filters = array()) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sync_chative_logs';
    
    $offset = ($page - 1) * $per_page;
    
    $where = array();
    $where_values = array();
    
    if (!empty($filters['status'])) {
        $where[] = 'status = %s';
        $where_values[] = $filters['status'];
    }
    
    if (!empty($filters['sync_type'])) {
        $where[] = 'sync_type = %s';
        $where_values[] = $filters['sync_type'];
    }
    
    if (!empty($filters['search'])) {
        $where[] = 'email LIKE %s';
        $where_values[] = '%' . $wpdb->esc_like($filters['search']) . '%';
    }
    
    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = $wpdb->prepare(
        "SELECT * FROM $table_name $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d",
        array_merge($where_values, array($per_page, $offset))
    );
    
    $total_query = "SELECT COUNT(*) FROM $table_name $where_clause";
    if (!empty($where_values)) {
        $total_query = $wpdb->prepare($total_query, $where_values);
    }
    
    return array(
        'logs' => $wpdb->get_results($query),
        'total' => $wpdb->get_var($total_query)
    );
}

function render_logs_page() {
    if (!session_id()) {
        session_start();
    }

    if (
        isset($_POST['delete_logs']) &&
        isset($_POST['delete_logs_nonce']) &&
        wp_verify_nonce($_POST['delete_logs_nonce'], 'delete_logs_action')
    ) {
        $period = isset($_POST['delete_period']) ? sanitize_text_field($_POST['delete_period']) : 'all';
        if (delete_logs($period)) {
            $_SESSION['log_delete_message'] = 'Logs deleted successfully.';
        } else {
            $_SESSION['log_delete_message'] = 'Error deleting logs.';
        }
        wp_redirect(add_query_arg(array('page' => 'sync-chative-logs'), admin_url('admin.php')));
        exit;
    }

    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    
    $filters = array(
        'status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
        'sync_type' => isset($_GET['sync_type']) ? sanitize_text_field($_GET['sync_type']) : '',
        'search' => isset($_GET['search']) ? sanitize_text_field($_GET['search']) : ''
    );
    
    $result = get_sync_logs($per_page, $current_page, $filters);
    $logs = $result['logs'];
    $total_items = $result['total'];
    $total_pages = ceil($total_items / $per_page);
    
    include plugin_dir_path(__FILE__) . 'templates/logs-page.php';
}

function render_settings_page()
{
    $cron_status = is_cron_running();
    include plugin_dir_path(__FILE__) . "templates/settings-page.php";
}

function add_admin_styles()
{
    include plugin_dir_path(__FILE__) . "templates/admin-styles.php";
}

function add_ajax_actions()
{
    add_action("wp_ajax_send_webhook", "handle_send_webhook");
}

function handle_send_webhook()
{
    check_ajax_referer("sync_chative_settings_action", "nonce");

    $contact = isset($_POST["contact"]) ? $_POST["contact"] : null;
    if (!$contact) {
        wp_send_json_error("Invalid contact data");
        return;
    }

    $success = send_to_webhook($contact);
    wp_send_json(["success" => $success]);
}

add_action("wp_ajax_reset_chative_cron", "handle_reset_cron");

function handle_reset_cron()
{
    check_ajax_referer("reset_cron_nonce", "nonce");

    wp_clear_scheduled_hook("sync_chative_contacts");
    schedule_sync_chative();

    wp_send_json_success();
}
