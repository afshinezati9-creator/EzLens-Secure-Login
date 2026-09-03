<?php
/**
 * قالب داشبورد مدیریت – مدرن‌سازی شده (فاز ۸)
 * @version 2.3.3
 * 
 * @var array $stats آمار دریافتی از get_dashboard_stats()
 * @var array $users_with_phone لیست کاربران از get_recent_users_with_phone()
 */

// ===== دریافت آمار از متد بهینه‌شده =====
$total_users = $stats['users'];
$total_admins = $stats['admins'];
$total_logins = $stats['logins'];
$total_logouts = $stats['logouts'];
$recent_logs = $stats['recent'];

// ===== ترکیب لاگ‌ها برای نمایش =====
$all_logs = [];
foreach ($recent_logs as $log) {
    $action_label = ($log->action === 'login') ? 'ورود' : (($log->action === 'failed_login') ? 'تلاش ناموفق' : 'خروج');
    $action_class = ($log->action === 'login') ? 'login' : (($log->action === 'failed_login') ? 'failed' : 'logout');
    $all_logs[] = (object) [
        'username' => $log->username,
        'action' => $log->action,
        'action_label' => $action_label,
        'action_class' => $action_class,
        'timestamp' => $log->timestamp,
        'ip' => $log->ip,
        'user_agent' => $log->user_agent ?? ''
    ];
}
usort($all_logs, function($a, $b) {
    return strtotime($b->timestamp) - strtotime($a->timestamp);
});
$all_logs = array_slice($all_logs, 0, 10);

// ===== SVGهای مینیمال =====
$icon_users = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/users.svg');
if (!$icon_users) {
    $icon_users = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
}
$icon_admin = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/user.svg');
if (!$icon_admin) {
    $icon_admin = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
}
$icon_login = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/arrow-right-end-on-rectangle.svg');
if (!$icon_login) {
    $icon_login = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>';
}
$icon_logout = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/arrow-left-end-on-rectangle.svg');
if (!$icon_logout) {
    $icon_logout = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>';
}
$icon_failed = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/exclamation-circle.svg');
if (!$icon_failed) {
    $icon_failed = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
}
?>

<div class="wrap ezlens-dashboard">
    <h1 class="wp-heading-inline">📋 داشبورد مدیریت</h1>
    <p class="description">آمار و فعالیت‌های اخیر سایت</p>

    <?php if (isset($_GET['saved'])): ?>
        <div class="ezlens-notice success" role="alert" aria-live="polite">✅ تنظیمات با موفقیت ذخیره شد.</div>
    <?php endif; ?>

    <!-- ===== کارت‌های آمار ===== -->
    <div class="ezlens-stats-grid">
        <div class="stat-card stat-users">
            <div class="stat-icon"><?php echo $icon_users; ?></div>
            <div class="stat-number"><?php echo number_format($total_users); ?></div>
            <div class="stat-label">کاربران</div>
            <div class="stat-sub">ثبت‌نام شده</div>
        </div>
        <div class="stat-card stat-admins">
            <div class="stat-icon"><?php echo $icon_admin; ?></div>
            <div class="stat-number green"><?php echo number_format($total_admins); ?></div>
            <div class="stat-label">مدیران</div>
            <div class="stat-sub">دسترسی کامل</div>
        </div>
        <div class="stat-card stat-logins">
            <div class="stat-icon"><?php echo $icon_login; ?></div>
            <div class="stat-number blue"><?php echo number_format($total_logins); ?></div>
            <div class="stat-label">ورودها</div>
            <div class="stat-sub">کل ورودها</div>
        </div>
        <div class="stat-card stat-logouts">
            <div class="stat-icon"><?php echo $icon_logout; ?></div>
            <div class="stat-number purple"><?php echo number_format($total_logouts); ?></div>
            <div class="stat-label">خروج‌ها</div>
            <div class="stat-sub">کل خروج‌ها</div>
        </div>
    </div>

    <!-- ===== بخش کاربران اخیر ===== -->
    <div class="ezlens-users-section">
        <div class="section-header">
            <h3>👥 کاربران اخیر</h3>
            <span class="count"><?php echo count($users_with_phone); ?> کاربر</span>
        </div>
        <div class="table-wrap">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>نام کاربری</th>
                        <th>نام نمایشی</th>
                        <th>ایمیل</th>
                        <th>شماره تماس</th>
                        <th>تاریخ ثبت‌نام</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users_with_phone)): ?>
                        <?php foreach ($users_with_phone as $user): ?>
                            <tr>
                                <td><strong><?php echo esc_html($user->username); ?></strong></td>
                                <td><?php echo esc_html($user->display_name); ?></td>
                                <td><?php echo esc_html($user->email); ?></td>
                                <td><code><?php echo esc_html($user->phone); ?></code></td>
                                <td><?php echo esc_html(date_i18n('Y/m/d H:i', strtotime($user->registered))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;color:var(--ezlens-muted);padding:20px 0;">هیچ کاربری ثبت نشده است.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top:8px;text-align:left;">
            <a href="<?php echo admin_url('users.php'); ?>" class="button button-small">مشاهده همه کاربران</a>
        </div>
    </div>

    <!-- ===== بخش لاگ‌های اخیر ===== -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px;">
        <!-- جدول آخرین فعالیت‌ها -->
        <div class="ezlens-logs">
            <div class="logs-header">
                <h3>🕒 آخرین فعالیت‌ها</h3>
                <span class="logs-count"><?php echo count($all_logs); ?> مورد</span>
            </div>
            <div class="table-wrap">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>کاربر</th>
                            <th>عمل</th>
                            <th>تاریخ و ساعت</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($all_logs)): ?>
                            <?php foreach ($all_logs as $log): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($log->username); ?></strong></td>
                                    <td>
                                        <span class="log-<?php echo esc_attr($log->action_class); ?>">
                                            <?php echo $log->action_label; ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($log->timestamp); ?></td>
                                    <td><code><?php echo esc_html($log->ip); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center;color:var(--ezlens-muted);padding:30px 0;">
                                    🕊️ هیچ فعالیتی ثبت نشده است.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== ویجت‌های کناری ===== -->
        <div style="display:flex;flex-direction:column;gap:16px;">
            <!-- آمار سریع -->
            <div class="ezlens-quick-stats">
                <div class="quick-item">
                    <span class="quick-icon">👥</span>
                    <div>
                        <span class="quick-number"><?php echo number_format($total_users); ?></span>
                        <span class="quick-label">کاربران کل</span>
                    </div>
                </div>
                <div class="quick-item">
                    <span class="quick-icon">📅</span>
                    <div>
                        <span class="quick-number"><?php echo date_i18n('Y/m/d'); ?></span>
                        <span class="quick-label">تاریخ امروز</span>
                    </div>
                </div>
                <div class="quick-item">
                    <span class="quick-icon">⏰</span>
                    <div>
                        <span class="quick-number"><?php echo date_i18n('H:i'); ?></span>
                        <span class="quick-label">ساعت کنونی</span>
                    </div>
                </div>
            </div>

            <!-- وضعیت لاگ‌گیری -->
            <div class="ezlens-status-card">
                <div class="status-item <?php echo EzLens_Auth_Settings::get('enable_logging') ? 'active' : 'inactive'; ?>">
                    <span class="status-dot"></span>
                    <span class="status-text">
                        <?php echo EzLens_Auth_Settings::get('enable_logging') ? '✅ لاگ‌گیری فعال است' : '❌ لاگ‌گیری غیرفعال است'; ?>
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-text">📊 تعداد لاگ‌ها: <strong><?php echo number_format($total_logins + $total_logouts); ?></strong></span>
                </div>
                <div class="status-item">
                    <span class="status-text">🗑️ حذف خودکار: <strong><?php echo esc_html(EzLens_Auth_Settings::get('log_retention_days')); ?></strong> روز</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== اطلاعات سیستم ===== -->
    <div class="ezlens-system-info">
        <div class="system-item">
            <span class="system-label">🔧 نسخه پلاگین</span>
            <span class="system-value"><?php echo EZLAUTH_VERSION; ?></span>
        </div>
        <div class="system-item">
            <span class="system-label">📦 نسخه وردپرس</span>
            <span class="system-value"><?php echo get_bloginfo('version'); ?></span>
        </div>
        <div class="system-item">
            <span class="system-label">🛒 ووکامرس</span>
            <span class="system-value"><?php echo class_exists('WooCommerce') ? '✅ فعال' : '❌ غیرفعال'; ?></span>
        </div>
        <div class="system-item">
            <span class="system-label">📁 محیط</span>
            <span class="system-value"><?php echo wp_get_environment_type(); ?></span>
        </div>
        <div class="system-item">
            <span class="system-label">آدرس ورود مدیر</span>
            <span class="system-value"><code><?php echo esc_html(home_url('/' . EzLens_Auth_Settings::get('admin_login_slug'))); ?></code></span>
        </div>
    </div>

    <!-- ===== لینک‌های سریع ===== -->
    <div class="ezlens-quick-links">
        <a href="<?php echo admin_url('admin.php?page=ezlens-auth-settings'); ?>" class="quick-link">
            <span class="ql-icon">⚙️</span>
            <span class="ql-text">تنظیمات عمومی</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=ezlens-auth-editor'); ?>" class="quick-link">
            <span class="ql-icon">✏️</span>
            <span class="ql-text">ویرایش صفحات</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=ezlens-auth-logs'); ?>" class="quick-link">
            <span class="ql-icon">📊</span>
            <span class="ql-text">مشاهده لاگ‌ها</span>
        </a>
        <a href="<?php echo home_url('/' . EzLens_Auth_Settings::get('admin_login_slug')); ?>" target="_blank" class="quick-link">
            <span class="ql-icon">🔐</span>
            <span class="ql-text">صفحه ورود مدیر</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=ezlens-auth-campaign'); ?>" class="quick-link">
            <span class="ql-icon">📢</span>
            <span class="ql-text">کمپینگ</span>
        </a>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    function refreshStats() {
        var data = {
            action: 'ezlens_get_stats',
            nonce: ezlens_auth_ajax.nonce
        };

        $.post(ezlens_auth_ajax.ajax_url, data, function(response) {
            if (response.success) {
                var stats = response.data;
                $('.stat-users .stat-number').text(stats.users);
                $('.stat-admins .stat-number').text(stats.admins);
                $('.stat-logins .stat-number').text(stats.logins);
                $('.stat-logouts .stat-number').text(stats.logouts);
                $('.quick-item .quick-number').first().text(stats.users);
            }
        });
    }

    setInterval(refreshStats, 30000);
    console.log('✅ داشبورد مدیریت EzLens بارگذاری شد.');
});
</script>