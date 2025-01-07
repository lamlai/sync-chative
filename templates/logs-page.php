<div class="wrap">
    <h1>Sync Logs</h1>

    <!-- Delete Logs Form -->
    <div class="delete-logs-section" style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px;">
        <h2>Delete Logs</h2>
        <p>Warning: This action cannot be undone!</p>
        <form method="post" action="" onsubmit="return confirm('Are you sure you want to delete these logs? This action cannot be undone.');">
            <?php wp_nonce_field('delete_logs_action', 'delete_logs_nonce'); ?>
            
            <select name="delete_period" style="margin-right: 10px;">
                <option value="all">All Logs</option>
                <option value="30">Older than 30 days</option>
                <option value="60">Older than 60 days</option>
                <option value="90">Older than 90 days</option>
            </select>
            
            <input type="submit" name="delete_logs" class="button button-secondary" value="Delete Logs">
        </form>
    </div>

    <!-- Success/Error Messages -->
    <?php
    if (isset($_SESSION['log_delete_message'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($_SESSION['log_delete_message']) . '</p></div>';
        unset($_SESSION['log_delete_message']);
    }
    ?>

    <!-- Filters -->
    <div class="tablenav top">
        <form method="get" class="alignleft actions">
            <input type="hidden" name="page" value="sync-chative-logs">
            
            <input type="text" 
                   name="search" 
                   value="<?php echo esc_attr($filters['search']); ?>" 
                   placeholder="Search email..."
                   style="margin-right: 5px;">
            
            <select name="status" style="margin-right: 5px;">
                <option value="">All Status</option>
                <option value="success" <?php selected($filters['status'], 'success'); ?>>Success</option>
                <option value="failed" <?php selected($filters['status'], 'failed'); ?>>Failed</option>
            </select>
            
            <select name="sync_type" style="margin-right: 5px;">
                <option value="">All Types</option>
                <option value="webhook" <?php selected($filters['sync_type'], 'webhook'); ?>>Webhook</option>
                <option value="direct" <?php selected($filters['sync_type'], 'direct'); ?>>Direct</option>
            </select>
            
            <input type="submit" class="button" value="Filter">
        </form>
    </div>

    <!-- Logs Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Email</th>
                <th>Sync Type</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="4">No logs found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo esc_html($log->email); ?></td>
                        <td><?php echo esc_html(ucfirst($log->sync_type)); ?></td>
                        <td>
                            <?php if ($log->status === 'success'): ?>
                                <span class="status-success">✅ Success</span>
                            <?php else: ?>
                                <span class="status-failed">❌ Failed</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(
                            wp_date('Y-m-d H:i:s', strtotime($log->created_at))
                        ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php echo sprintf(
                        _n('%s item', '%s items', $total_items),
                        number_format_i18n($total_items)
                    ); ?>
                </span>
                <span class="pagination-links">
                    <?php
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $total_pages,
                        'current' => $current_page
                    ));
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.status-success {
    color: #46b450;
}
.status-failed {
    color: #dc3232;
}
.tablenav.top {
    margin-bottom: 1em;
}
.wp-list-table {
    margin-top: 1em;
}
.delete-logs-section {
    background: #fff;
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid #ccd0d4;
}
.delete-logs-section h2 {
    margin-top: 0;
    margin-bottom: 10px;
}
.delete-logs-section p {
    color: #dc3232;
    margin-bottom: 15px;
}
</style>
