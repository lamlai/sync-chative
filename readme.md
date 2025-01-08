# Sync Chative

Sync Chative is a WordPress plugin that synchronizes contacts between Chative and FluentCRM. It automatically fetches contacts from Chative's API and either sends them to a webhook or directly adds them to FluentCRM.

## Features

- **Automatic Synchronization**
  - Configurable sync interval (default: 5 minutes)
  - Prevents duplicate entries
  - Background processing via WordPress cron

- **Multiple Sync Methods**
  - Webhook integration
  - Direct FluentCRM integration
  - Manual sync option

- **Contact Management**
  - Syncs email addresses
  - Includes additional contact details (first name, last name, phone)
  - Checks for existing contacts before syncing

- **Detailed Logging**
  - Tracks all sync attempts
  - Records success/failure status
  - Maintains sync method history
  - Log cleanup options (30/60/90 days)

- **User Interface**
  - Clean, intuitive admin interface
  - Real-time sync status indicators
  - Comprehensive settings page
  - Detailed logs viewer with filters

## Requirements

- WordPress 5.0 or higher
- FluentCRM plugin installed and activated
- PHP 7.2 or higher
- Valid Chative API key

## Installation

1. Upload the plugin files to `/wp-content/plugins/sync-chative`
2. Activate the plugin through the WordPress plugins screen
3. Go to "Sync Chative" in your admin menu
4. Configure your Chative API key and sync settings

## Configuration

### Required Settings
- Chative API Key
- Sync Method (Webhook/Direct)
- Webhook URL (if using webhook method)
- Sync Interval

### Optional Settings
- Log retention period
- Manual sync triggers
- Filter and search options for logs

## Usage

1. **Initial Setup**
   - Enter your Chative API key
   - Choose sync method (webhook or direct)
   - Set desired sync interval

2. **Manual Sync**
   - Visit the Sync Contacts page
   - Click "Sync Now" button

3. **View Logs**
   - Access the Logs page
   - Filter by status, type, or search emails
   - Delete old logs as needed

## Support

For support or feature requests, please contact the plugin author.

## Sreenshoot
![Manual Sync](https://media.beehiiv.com/cdn-cgi/image/fit=scale-down,format=auto,onerror=redirect,quality=80/uploads/asset/file/e5642198-fffd-40f9-a044-2b95ac096c9a/Xnapper-2025-01-07-12.37.38.png?t=1736234044)
![Direct Sync](https://media.beehiiv.com/cdn-cgi/image/fit=scale-down,format=auto,onerror=redirect,quality=80/uploads/asset/file/4a9d596d-df50-4a12-b9bd-ffe52adcc707/Xnapper-2025-01-07-14.12.13.png?t=1736234320)
![Webhook Sync](https://media.beehiiv.com/cdn-cgi/image/fit=scale-down,format=auto,onerror=redirect,quality=80/uploads/asset/file/25f6f628-e262-4a40-a540-58b632ae927e/Xnapper-2025-01-07-14.12.39.png?t=1736234369)
![Logs](https://media.beehiiv.com/cdn-cgi/image/fit=scale-down,format=auto,onerror=redirect,quality=80/uploads/asset/file/62ab8fa9-2662-42da-ac7b-734af0fafa7a/Xnapper-2025-01-07-12.38.05.png?t=1736234585)


## Version History

### 1.0
- Initial release
- Basic sync functionality
- Webhook and direct sync support
- Logging system
- Admin interface
- Manual and automatic sync options

## Author

[Lam Lai](https://lam.ee)

## License

This plugin is licensed under the GPL v2 or later.

---

**Note:** This plugin requires FluentCRM to be installed and activated to function properly.
