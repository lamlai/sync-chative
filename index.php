<?php
/*
Plugin Name: Sync Chative
Description: Sync contacts from Chative to FluentCRM. The plugin connects to the Chative API to fetch email lists and compares them with FluentCRM's MySQL database. If an email doesn't exist, it will be sent to your configured webhook or directly added to FluentCRM.
Version: 1.0
Author: Lam Lai
*/

defined("ABSPATH") or die("No script kiddies please!");

require_once plugin_dir_path(__FILE__) . "functions.php";

register_activation_hook(__FILE__, "activate_sync_chative");
register_deactivation_hook(__FILE__, "deactivate_sync_chative");

add_action("admin_menu", "my_email_plugin_menu");
add_action("admin_init", "register_sync_chative_settings");
add_action("admin_head", "add_admin_styles");
add_action("admin_init", "add_ajax_actions");
add_action("sync_chative_contacts", "sync_chative_contacts", 10);
add_filter("cron_schedules", "add_cron_interval");
