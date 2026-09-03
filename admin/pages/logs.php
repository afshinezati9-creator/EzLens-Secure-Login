<?php
/**
 * قالب صفحه گزارش‌ها (لاگ‌ها) – مدرن‌سازی شده (فاز ۸)
 * @version 2.3.3
 */

$logs = EzLens_Auth_Logger::get_recent(null, 50);
$actions = ['all' => 'همه', 'login' => 'ورود', 'logout' => 'خروج', 'failed_login' => 'تلاش ناموفق'];
?>
<div class="wrap ezlens-logs-page">
    <h1 class="wp-heading-inline">📊 گزارش‌ها (لاگ‌ها)</h1>
    <p class="description">مشاهده و فیلتر لاگ‌های ورود و خروج کاربران</p>

    <div class="log-filter">
        <select id="log-filter-action">
            <?php foreach ($actions as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" id="log-filter-search" placeholder="جستجوی کاربر..." style="min-width:150px;">
        <button id="log-filter-apply" class="button button-primary">🔍 اعمال</button>
        <button id="log-filter-reset" class="button button-secondary">🔄 بازنشانی</button>
        <span style="font-size:0.75rem;color:var(--ezlens-muted);margin-right:auto;">تعداد: <span id="log-count"><?php echo count($logs); ?></span></span>
    </div>

    <div class="log-table-wrap">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>کاربر</th>
                    <th>عمل</th>
                    <th>IP</th>
                    <th>مرورگر</th>
                    <th>تاریخ و ساعت</th>
                </tr>
            </thead>
            <tbody id="logs-tbody">
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): 
                        $action_label = ($log->action === 'login') ? 'ورود' : (($log->action === 'failed_login') ? 'تلاش ناموفق' : 'خروج');
                        $action_class = ($log->action === 'login') ? 'login' : (($log->action === 'failed_login') ? 'failed' : 'logout');
                    ?>
                        <tr data-action="<?php echo esc_attr($log->action); ?>" data-username="<?php echo esc_attr(strtolower($log->username)); ?>">
                            <td><strong><?php echo esc_html($log->username); ?></strong></td>
                            <td><span class="status-badge <?php echo esc_attr($action_class); ?>"><?php echo esc_html($action_label); ?></span></td>
                            <td><code><?php echo esc_html($log->ip); ?></code></td>
                            <td style="font-size:0.75rem;color:var(--ezlens-muted);"><?php echo esc_html(substr($log->user_agent, 0, 50) . (strlen($log->user_agent) > 50 ? '...' : '')); ?></td>
                            <td><?php echo esc_html($log->timestamp); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--ezlens-muted);padding:30px 0;">هیچ لاگی ثبت نشده است.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    function filterLogs() {
        var action = $('#log-filter-action').val();
        var search = $('#log-filter-search').val().toLowerCase().trim();

        $('#logs-tbody tr').each(function() {
            var $row = $(this);
            var show = true;

            if (action !== 'all' && $row.data('action') !== action) {
                show = false;
            }

            if (search && $row.data('username') && $row.data('username').indexOf(search) === -1) {
                show = false;
            }

            $row.toggle(show);
        });

        var count = $('#logs-tbody tr:visible').length;
        $('#log-count').text(count);
    }

    $('#log-filter-apply').on('click', filterLogs);
    $('#log-filter-reset').on('click', function() {
        $('#log-filter-action').val('all');
        $('#log-filter-search').val('');
        filterLogs();
    });
    $('#log-filter-search').on('keypress', function(e) {
        if (e.which === 13) filterLogs();
    });
    $('#log-filter-action').on('change', filterLogs);

    console.log('✅ صفحه لاگ‌ها EzLens بارگذاری شد.');
});
</script>