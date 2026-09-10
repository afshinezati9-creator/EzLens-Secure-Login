<?php
/**
 * قالب داشبورد مدیریت – مدرن‌سازی شده (نسخه نهایی با آیکون‌های اصلاح‌شده)
 * @version 2.3.5
 * 
 * @var array $stats آمار دریافتی از get_dashboard_stats()
 * @var array $users_with_phone لیست کاربران از get_recent_users_with_phone()
 * @var array $purchase_stats آمار ماژول فرآیند خرید
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

// ===== توابع کمکی برای آیکون‌ها (با fallback) =====
function ezlens_get_svg_icon($name, $fallback = '') {
    $paths = [
        EZLAUTH_PLUGIN_DIR . 'assets/icons/modern/' . $name . '.svg',
        EZLAUTH_PLUGIN_DIR . 'assets/icons/' . $name . '.svg',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if ($content !== false) {
                return $content;
            }
        }
    }
    return $fallback;
}

// ===== آیکون‌ها =====
$icon_users = ezlens_get_svg_icon('users', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>');
$icon_admin = ezlens_get_svg_icon('user', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>');
$icon_login = ezlens_get_svg_icon('log-in', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>');
$icon_logout = ezlens_get_svg_icon('logout', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>');
$icon_failed = ezlens_get_svg_icon('alert-circle', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>');
$icon_purchase = ezlens_get_svg_icon('shopping-cart', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>');
$icon_file = ezlens_get_svg_icon('file-text', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>');
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

    <!-- ============================================================
    🛒 بخش جدید: فرآیند خرید (جدا از بقیه با یک کارت اختصاصی)
    ============================================================ -->
    <div class="ezlens-purchase-section" style="margin-top:28px;border-top:2px solid var(--ezlens-border);padding-top:24px;">
        <div class="section-header">
            <h3 style="display:flex;align-items:center;gap:10px;font-size:1.1rem;margin:0;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;background:linear-gradient(135deg,#031f8a,#5c8092);border-radius:8px;color:#fff;padding:4px;">
                    <?php echo $icon_purchase; ?>
                </span>
                فرآیند خرید (فایل‌های سفارشی)
            </h3>
            <span class="count">
                <?php echo $purchase_stats['exists'] ? number_format($purchase_stats['total']) . ' فایل' : 'ماژول غیرفعال'; ?>
            </span>
        </div>

        <?php if ($purchase_stats['exists']): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin:12px 0 16px;">
                <div class="stat-card" style="text-align:center;padding:14px;">
                    <div class="stat-number" style="font-size:1.6rem;"><?php echo number_format($purchase_stats['total']); ?></div>
                    <div class="stat-label" style="font-size:0.8rem;">کل فایل‌ها</div>
                </div>
                <div class="stat-card" style="text-align:center;padding:14px;border-right:3px solid #16a34a;">
                    <div class="stat-number" style="font-size:1.6rem;color:#16a34a;"><?php echo number_format($purchase_stats['active']); ?></div>
                    <div class="stat-label" style="font-size:0.8rem;">فایل‌های فعال</div>
                </div>
                <div class="stat-card" style="text-align:center;padding:14px;border-right:3px solid #dc2626;">
                    <div class="stat-number" style="font-size:1.6rem;color:#dc2626;"><?php echo number_format($purchase_stats['inactive']); ?></div>
                    <div class="stat-label" style="font-size:0.8rem;">فایل‌های غیرفعال</div>
                </div>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                <a href="<?php echo admin_url('admin.php?page=ezlens-purchase-process-list'); ?>" class="button button-primary" style="display:inline-flex;align-items:center;gap:6px;">
                    <span class="dashicons dashicons-list-view" style="font-size:18px;width:18px;height:18px;"></span>
                    مدیریت فایل‌ها
                </a>
                <a href="<?php echo admin_url('admin.php?page=ezlens-purchase-process-add'); ?>" class="button button-secondary" style="display:inline-flex;align-items:center;gap:6px;">
                    <span class="dashicons dashicons-plus" style="font-size:18px;width:18px;height:18px;"></span>
                    افزودن فایل جدید
                </a>
                <a href="<?php echo admin_url('admin.php?page=ezlens-auth-settings'); ?>" class="button" style="display:inline-flex;align-items:center;gap:6px;">
                    <span class="dashicons dashicons-admin-generic" style="font-size:18px;width:18px;height:18px;"></span>
                    تنظیمات
                </a>
            </div>
        <?php else: ?>
            <div style="background:var(--ezlens-bg);border:1px dashed var(--ezlens-border);border-radius:var(--ezlens-radius);padding:24px 20px;text-align:center;color:var(--ezlens-muted);">
                <p style="margin:0;font-size:0.95rem;">
                    ⚡ ماژول فرآیند خرید هنوز فعال نشده است. 
                    <a href="<?php echo admin_url('admin.php?page=ezlens-purchase-process-add'); ?>" style="color:var(--ezlens-primary);font-weight:600;">
                        اولین فایل خود را ایجاد کنید
                    </a>
                </p>
                <p style="margin:6px 0 0;font-size:0.8rem;">
                    با ایجاد فایل‌های PHP سفارشی، می‌توانید فرآیند خرید را شخصی‌سازی کنید.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- ============================================================
    پایان بخش فرآیند خرید
    ============================================================ -->

    <!-- ===== اطلاعات سیستم ===== -->
    <div class="ezlens-system-info" style="margin-top:20px;">
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
        <!-- لینک جدید به فرآیند خرید -->
        <a href="<?php echo admin_url('admin.php?page=ezlens-purchase-process-list'); ?>" class="quick-link" style="border-color:var(--ezlens-primary);background:var(--ezlens-primary-light);">
            <span class="ql-icon">🛒</span>
            <span class="ql-text">فرآیند خرید</span>
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