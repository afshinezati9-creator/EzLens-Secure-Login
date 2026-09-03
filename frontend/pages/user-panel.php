<?php
/**
 * قالب پنل کاربری – نسخه کامل با رفع خطاها، آیکون‌های SVG و استایل مدرن
 * @version 3.0.0
 */

if (!defined('ABSPATH')) exit;

// ===== کش کردن آمار کاربر =====
$user_id = get_current_user_id();
$user = get_userdata($user_id);
$cache_key = 'ezu_stats_' . $user_id;
$stats = wp_cache_get($cache_key, 'ezu');

if ($stats === false) {
    $stats = [
        'total_spent' => class_exists('WooCommerce') ? wc_get_customer_total_spent($user_id) : 0,
        'order_count' => class_exists('WooCommerce') ? wc_get_customer_order_count($user_id) : 0,
        'open_orders' => 0,
        'days' => max(1, (int) floor((time() - strtotime($user->user_registered)) / DAY_IN_SECONDS)),
    ];

    if (class_exists('WooCommerce')) {
        $open_orders_count = 0;
        $orders = wc_get_orders([
            'customer_id' => $user_id,
            'limit' => -1,
            'return' => 'ids',
        ]);
        foreach ($orders as $order_id) {
            $order = wc_get_order($order_id);
            if (in_array($order->get_status(), ['processing', 'on-hold', 'pending'], true)) {
                $open_orders_count++;
            }
        }
        $stats['open_orders'] = $open_orders_count;
    }

    wp_cache_set($cache_key, $stats, 'ezu', 300);
}

$total_spent = $stats['total_spent'];
$total_order_count = $stats['order_count'];
$open_orders = $stats['open_orders'];
$days = $stats['days'];

// ===== ادامه کدهای اصلی =====
$page_title = EzLens_Auth_Settings::get_page_title('user-panel');
$page_subtitle = EzLens_Auth_Settings::get_page_subtitle('user-panel');
$primary_color = EzLens_Auth_Settings::get('primary_color') ?: '#031f8a';

// ===== دریافت شماره‌های تماس از تنظیمات =====
$support_phone = EzLens_Auth_Settings::get('support_phone') ?: '02144385667';
$support_email = EzLens_Auth_Settings::get('support_email') ?: 'info@ezlens.ir';
$support_whatsapp = EzLens_Auth_Settings::get('support_whatsapp') ?: '09198421069';
$whatsapp_url = 'https://wa.me/98' . ltrim($support_whatsapp, '0');

// ===== آیکون‌های SVG از پوشه 20/solid =====
$icon_home = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/home.svg');
$icon_cart = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/shopping-cart.svg');
$icon_orders = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/document-text.svg');
$icon_address = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/map-pin.svg');
$icon_profile = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/user.svg');
$icon_prescription = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/beaker.svg');
$icon_security = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/shield-check.svg');
$icon_support = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/phone.svg');
$icon_ticket = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/ticket.svg');
$icon_logout = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/arrow-right-end-on-rectangle.svg');
$icon_shop = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/shopping-bag.svg');
$icon_phone = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/phone.svg');
$icon_envelope = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/envelope.svg');
$icon_chat = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/chat-bubble-left.svg');
$icon_paperclip = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/paper-clip.svg');
$icon_send = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/paper-airplane.svg');
$icon_whatsapp = file_get_contents(EZLAUTH_PLUGIN_DIR . 'assets/icons/20/solid/chat-bubble-left-ellipsis.svg');

// Fallback برای آیکون‌ها (در صورت نبود فایل)
if (!$icon_home) $icon_home = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
if (!$icon_cart) $icon_cart = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>';
if (!$icon_orders) $icon_orders = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>';
if (!$icon_address) $icon_address = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>';
if (!$icon_profile) $icon_profile = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
if (!$icon_prescription) $icon_prescription = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>';
if (!$icon_security) $icon_security = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>';
if (!$icon_support) $icon_support = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>';
if (!$icon_ticket) $icon_ticket = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 4H4a2 2 0 00-2 2v4a2 2 0 002 2 2 2 0 002 2v4a2 2 0 002 2h12a2 2 0 002-2v-4a2 2 0 002-2 2 2 0 002-2V6a2 2 0 00-2-2z"/><path d="M10 10h4"/><path d="M12 14v-8"/></svg>';
if (!$icon_logout) $icon_logout = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>';
if (!$icon_shop) $icon_shop = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>';
if (!$icon_phone) $icon_phone = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>';
if (!$icon_envelope) $icon_envelope = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>';
if (!$icon_chat) $icon_chat = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>';
if (!$icon_paperclip) $icon_paperclip = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>';
if (!$icon_send) $icon_send = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>';
if (!$icon_whatsapp) $icon_whatsapp = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>';

// ===== آواتار کاربر =====
$avatar_id = get_user_meta($user_id, 'ezu_avatar_id', true);
$avatar_url = $avatar_id ? wp_get_attachment_image_url($avatar_id, 'thumbnail') : '';
$display_name = $user->display_name ?: $user->user_login;
?>
<div class="ezu" data-ajaxurl="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('ezlens_panel_nonce')); ?>">

    <!-- ===== هدر موبایل ===== -->
    <div class="ezu-m-head">
        <div class="who">
            <?php if ($avatar_url): ?>
                <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>">
            <?php else: ?>
                <svg width="44" height="44" viewBox="0 0 100 100" style="border-radius:50%;background:#e2e8f0;">
                    <circle cx="50" cy="50" r="45" fill="#cbd5e1"/>
                    <circle cx="50" cy="34" r="18" fill="#94a3b8"/>
                    <ellipse cx="50" cy="78" rx="30" ry="18" fill="#94a3b8"/>
                </svg>
            <?php endif; ?>
            <div>
                <div class="nm"><?php echo esc_html($display_name); ?></div>
                <div class="em"><?php echo esc_html($user->user_email); ?></div>
            </div>
        </div>
        <a class="back" href="<?php echo esc_url(home_url()); ?>">← فروشگاه</a>
    </div>

    <div class="ezu-layout">
        <!-- ===== سایدبار ===== -->
        <aside class="ezu-side">
            <div class="ezu-user">
                <?php if ($avatar_url): ?>
                    <img class="ezu-av" src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>">
                <?php else: ?>
                    <svg class="ezu-av" viewBox="0 0 100 100" style="border-radius:50%;background:#e2e8f0;">
                        <circle cx="50" cy="50" r="45" fill="#cbd5e1"/>
                        <circle cx="50" cy="34" r="18" fill="#94a3b8"/>
                        <ellipse cx="50" cy="78" rx="30" ry="18" fill="#94a3b8"/>
                    </svg>
                <?php endif; ?>
                <div class="ezu-name"><?php echo esc_html($display_name); ?></div>
                <div class="ezu-mail"><?php echo esc_html($user->user_email); ?></div>
            </div>

            <div class="ezu-nav-d">
                <button class="active" data-tab="dashboard">
                    <span class="icon"><?php echo $icon_home; ?></span>
                    <span class="label">پیشخوان</span>
                </button>
                <button data-tab="cart">
                    <span class="icon"><?php echo $icon_cart; ?></span>
                    <span class="label">سبد خرید</span>
                </button>
                <button data-tab="orders">
                    <span class="icon"><?php echo $icon_orders; ?></span>
                    <span class="label">سفارش‌ها</span>
                </button>
                <button data-tab="address">
                    <span class="icon"><?php echo $icon_address; ?></span>
                    <span class="label">آدرس</span>
                </button>
                <button data-tab="profile">
                    <span class="icon"><?php echo $icon_profile; ?></span>
                    <span class="label">پروفایل</span>
                </button>
                <button data-tab="prescription">
                    <span class="icon"><?php echo $icon_prescription; ?></span>
                    <span class="label">نسخه پزشکی</span>
                </button>
                <button data-tab="security">
                    <span class="icon"><?php echo $icon_security; ?></span>
                    <span class="label">امنیت</span>
                </button>
                <button data-tab="support">
                    <span class="icon"><?php echo $icon_support; ?></span>
                    <span class="label">پشتیبانی</span>
                </button>
                <button class="logout" onclick="window.location.href='<?php echo esc_url(wp_logout_url(home_url())); ?>'">
                    <span class="icon"><?php echo $icon_logout; ?></span>
                    <span class="label">خروج</span>
                </button>
            </div>

            <a class="shop-btn" href="<?php echo esc_url(home_url()); ?>">
                <span class="icon"><?php echo $icon_shop; ?></span>
                بازگشت به فروشگاه
            </a>
        </aside>

        <!-- ===== محتوای اصلی ===== -->
        <main class="ezu-main ezu-main-pad">

            <!-- ===== تب: پیشخوان ===== -->
            <section class="ezu-tab active" id="tab-dashboard">
                <h2 class="ezu-title">
                    <?php echo esc_html($page_title ?: 'پیشخوان'); ?>
                    <span style="font-size:0.7rem;font-weight:400;color:var(--ezlens-muted);"><?php echo esc_html($page_subtitle); ?></span>
                </h2>

                <div class="ezu-stats">
                    <div class="ezu-stat">
                        <p class="l">مجموع خرید</p>
                        <p class="v"><?php echo number_format($total_spent); ?> تومان</p>
                    </div>
                    <div class="ezu-stat">
                        <p class="l">تعداد سفارش‌ها</p>
                        <p class="v"><?php echo $total_order_count; ?></p>
                    </div>
                    <div class="ezu-stat">
                        <p class="l">سفارش‌های در جریان</p>
                        <p class="v"><?php echo $open_orders; ?></p>
                    </div>
                    <div class="ezu-stat">
                        <p class="l">روز عضویت</p>
                        <p class="v"><?php echo $days; ?> روز</p>
                    </div>
                </div>

                <?php if (class_exists('EzLens_Auth_Notifications')): $unread_notifications = EzLens_Auth_Notifications::get_instance()->unread_count($user_id); $latest_notifications = EzLens_Auth_Notifications::get_instance()->list_for_user($user_id, 3); ?>
                <div class="ezu-notifications-card" style="margin:18px 0;padding:16px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;"><strong>اعلان‌ها</strong><span style="font-size:12px;color:#64748b;"><?php echo (int)$unread_notifications; ?> خوانده‌نشده</span></div>
                    <?php if ($latest_notifications): foreach ($latest_notifications as $notice): ?>
                        <div style="padding:9px 0;border-top:1px solid #f1f5f9;"><strong style="font-size:13px;"><?php echo esc_html($notice['title']); ?></strong><div style="font-size:12px;color:#64748b;margin-top:3px;"><?php echo esc_html(wp_strip_all_tags($notice['message'])); ?></div></div>
                    <?php endforeach; else: ?><div style="font-size:12px;color:#94a3b8;">اعلان جدیدی ندارید.</div><?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="ezu-quick">
                    <a href="#" data-go="orders">
                        <div class="t">📦 مشاهده سفارش‌ها</div>
                        <div class="s">آخرین وضعیت سفارش‌های خود را ببینید</div>
                    </a>
                    <a href="#" data-go="profile">
                        <div class="t">👤 ویرایش پروفایل</div>
                        <div class="s">اطلاعات شخصی خود را به‌روز کنید</div>
                    </a>
                    <a href="#" data-go="address">
                        <div class="t">📍 آدرس</div>
                        <div class="s">مدیریت آدرس‌های تحویل</div>
                    </a>
                    <a href="#" data-go="security">
                        <div class="t">🔒 امنیت</div>
                        <div class="s">تغییر رمز عبور</div>
                    </a>
                </div>
            </section>

            <!-- ===== تب: سبد خرید ===== -->
            <section class="ezu-tab" id="tab-cart">
                <h2 class="ezu-title">🛒 سبد خرید</h2>
                <div class="ezu-cart-wrapper">
                    <?php if (class_exists('WooCommerce')): ?>
                        <?php wc_get_template('cart/cart.php'); ?>
                    <?php else: ?>
                        <p>سبد خرید در حال حاضر خالی است.</p>
                        <a href="<?php echo esc_url(home_url()); ?>" class="ezu-btn">رفتن به فروشگاه</a>
                    <?php endif; ?>
                </div>
            </section>

            <!-- ===== تب: سفارش‌ها ===== -->
            <section class="ezu-tab" id="tab-orders">
                <h2 class="ezu-title">📦 سفارش‌ها</h2>
                <div class="ezu-orders-wrapper">
                    <?php if (class_exists('WooCommerce')): ?>
                        <?php wc_get_template('myaccount/orders.php', ['current_user' => get_user_by('id', $user_id)]); ?>
                    <?php else: ?>
                        <p>هیچ سفارشی ثبت نشده است.</p>
                        <a href="<?php echo esc_url(home_url()); ?>" class="ezu-btn">شروع خرید</a>
                    <?php endif; ?>
                </div>
            </section>

            <!-- ===== تب: آدرس ===== -->
            <section class="ezu-tab" id="tab-address">
                <h2 class="ezu-title">📍 آدرس</h2>
                <div class="ezu-lead">مدیریت آدرس‌های تحویل</div>
                <form id="ezu-address-form">
                    <?php wp_nonce_field('ezlens_save_address', 'address_nonce'); ?>
                    <div class="ezu-form-grid">
                        <div class="ezu-field">
                            <label for="billing_first_name">نام</label>
                            <input type="text" id="billing_first_name" name="billing_first_name" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_first_name', true)); ?>" required>
                        </div>
                        <div class="ezu-field">
                            <label for="billing_last_name">نام خانوادگی</label>
                            <input type="text" id="billing_last_name" name="billing_last_name" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_last_name', true)); ?>" required>
                        </div>
                        <div class="ezu-field full">
                            <label for="billing_address_1">آدرس</label>
                            <input type="text" id="billing_address_1" name="billing_address_1" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_address_1', true)); ?>" required>
                        </div>
                        <div class="ezu-field">
                            <label for="billing_city">شهر</label>
                            <input type="text" id="billing_city" name="billing_city" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_city', true)); ?>" required>
                        </div>
                        <div class="ezu-field">
                            <label for="billing_postcode">کد پستی</label>
                            <input type="text" id="billing_postcode" name="billing_postcode" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_postcode', true)); ?>">
                        </div>
                        <div class="ezu-field">
                            <label for="billing_phone">شماره موبایل</label>
                            <input type="tel" id="billing_phone" name="billing_phone" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_phone', true)); ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="ezu-btn" id="ezu-save-address">💾 ذخیره آدرس</button>
                    <span id="ezu-address-status" style="font-size:13px;margin:8px;color:#38a169;display:none;"></span>
                </form>
            </section>

            <!-- ===== تب: پروفایل ===== -->
            <section class="ezu-tab" id="tab-profile">
                <h2 class="ezu-title">👤 پروفایل</h2>
                <div class="ezu-lead">اطلاعات شخصی خود را مدیریت کنید</div>

                <div class="ezu-avatar-box">
                    <?php if ($avatar_url): ?>
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>" id="ezu-avatar-preview">
                    <?php else: ?>
                        <svg width="72" height="72" viewBox="0 0 100 100" style="border-radius:50%;background:#e2e8f0;">
                            <circle cx="50" cy="50" r="45" fill="#cbd5e1"/>
                            <circle cx="50" cy="34" r="18" fill="#94a3b8"/>
                            <ellipse cx="50" cy="78" rx="30" ry="18" fill="#94a3b8"/>
                        </svg>
                    <?php endif; ?>
                    <div>
                        <div class="ezu-avatar-upload">
                            <div class="file-wrap">
                                <span class="fake-btn">📤 آپلود عکس</span>
                                <input type="file" id="ezu-avatar-upload" accept="image/png,image/jpeg,image/webp">
                            </div>
                            <button class="ezu-btn-danger" id="ezu-avatar-remove">🗑️ حذف</button>
                        </div>
                        <p class="hint">فرمت‌های مجاز: JPG, PNG, WEBP – حداکثر ۲ مگابایت</p>
                    </div>
                </div>

                <form id="ezu-profile-form">
                    <?php wp_nonce_field('ezlens_save_profile', 'profile_nonce'); ?>
                    <div class="ezu-form-grid">
                        <div class="ezu-field">
                            <label for="first_name">نام</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($user->first_name); ?>" required>
                        </div>
                        <div class="ezu-field">
                            <label for="last_name">نام خانوادگی</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($user->last_name); ?>" required>
                        </div>
                        <div class="ezu-field full">
                            <label for="profile_phone">شماره موبایل</label>
                            <input type="tel" id="profile_phone" name="billing_phone" value="<?php echo esc_attr(get_user_meta($user_id, 'billing_phone', true)); ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="ezu-btn" id="ezu-save-profile">💾 ذخیره پروفایل</button>
                    <span id="ezu-profile-status" style="font-size:13px;margin:8px;color:#38a169;display:none;"></span>
                </form>
            </section>

            <!-- ===== تب: نسخه پزشکی ===== -->
            <section class="ezu-tab" id="tab-prescription">
                <h2 class="ezu-title">💊 نسخه پزشکی</h2>
                <div class="ezu-lead">آپلود و مدیریت نسخه‌های پزشکی</div>
                <form id="ezu-prescription-form">
                    <?php wp_nonce_field('ezlens_upload_prescription', 'prescription_nonce'); ?>
                    <div class="file-wrap" style="display:inline-block;margin-bottom:12px;position:relative;">
                        <span class="ezu-btn" style="cursor:pointer;">📤 آپلود نسخه جدید</span>
                        <input type="file" id="ezu-prescription-upload" name="ezu_prescription" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" style="position:absolute;opacity:0;width:100%;height:100%;cursor:pointer;top:0;left:0;">
                    </div>
                    <span id="ezu-prescription-status" style="font-size:13px;color:#38a169;display:none;"></span>
                </form>
                <div id="ezu-prescription-list">
                    <?php
                    $prescriptions = get_user_meta($user_id, 'ezu_prescriptions', true);
                    if (is_array($prescriptions) && !empty($prescriptions)):
                        foreach ($prescriptions as $index => $item):
                    ?>
                        <div class="ezu-prescription-item" style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border:1px solid var(--ezlens-border);border-radius:10px;margin-bottom:8px;">
                            <div>
                                <a href="<?php echo esc_url($item['file']); ?>" target="_blank" style="color:var(--ezlens-primary);font-weight:600;">📄 <?php echo esc_html(basename($item['file'])); ?></a>
                                <span style="font-size:12px;color:var(--ezlens-muted);margin-left:12px;"><?php echo esc_html($item['date']); ?> – <?php echo esc_html($item['size']); ?></span>
                            </div>
                            <button class="ezu-btn-danger" data-index="<?php echo $index; ?>" onclick="deletePrescription(this)">🗑️</button>
                        </div>
                    <?php endforeach; else: ?>
                        <p style="color:var(--ezlens-muted);">هیچ نسخه‌ای آپلود نشده است.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- ===== تب: امنیت ===== -->
            <section class="ezu-tab" id="tab-security">
                <h2 class="ezu-title">🔒 امنیت</h2>
                <div class="ezu-lead">تغییر رمز عبور</div>
                <form id="ezu-password-form">
                    <?php wp_nonce_field('ezlens_change_password', 'password_nonce'); ?>
                    <div class="ezu-form-grid">
                        <div class="ezu-field full">
                            <label for="current_pass">رمز عبور فعلی</label>
                            <input type="password" id="current_pass" name="current_pass" required autocomplete="current-password">
                        </div>
                        <div class="ezu-field">
                            <label for="new_pass">رمز عبور جدید</label>
                            <input type="password" id="new_pass" name="new_pass" required minlength="8" autocomplete="new-password">
                        </div>
                        <div class="ezu-field">
                            <label for="new_pass2">تکرار رمز عبور جدید</label>
                            <input type="password" id="new_pass2" name="new_pass2" required minlength="8" autocomplete="new-password">
                        </div>
                    </div>
                    <button type="submit" class="ezu-btn" id="ezu-change-password">🔑 تغییر رمز عبور</button>
                    <span id="ezu-password-status" style="font-size:13px;margin:8px;color:#38a169;display:none;"></span>
                </form>
            </section>

            <!-- ===== تب: پشتیبانی (مدرن با کارت‌های SVG) ===== -->
            <section class="ezu-tab" id="tab-support">
                <h2 class="ezu-title"><?php echo $icon_support; ?> پشتیبانی</h2>

                <div class="ezu-support-modern">

                    <!-- کارت ۱: تماس مستقیم -->
                    <div class="support-card">
                        <div class="card-icon"><?php echo $icon_phone; ?></div>
                        <h4>تماس مستقیم</h4>
                        <div class="contact-row">
                            <strong>تلفن:</strong>
                            <a href="tel:<?php echo esc_attr($support_phone); ?>"><?php echo esc_html($support_phone); ?></a>
                        </div>
                        <div class="contact-row">
                            <strong>ایمیل:</strong>
                            <a href="mailto:<?php echo esc_attr($support_email); ?>"><?php echo esc_html($support_email); ?></a>
                        </div>
                    </div>

                    <!-- کارت ۲: واتساپ -->
                    <div class="support-card whatsapp-card">
                        <div class="card-icon"><?php echo $icon_whatsapp; ?></div>
                        <h4>واتساپ</h4>
                        <p class="card-desc">ارسال پیام در واتساپ</p>
                        <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" class="btn-wa">
                            <?php echo $icon_send; ?> ارسال پیام (<?php echo esc_html($support_whatsapp); ?>)
                        </a>
                    </div>

                    <!-- کارت ۳: چت پشتیبانی -->
                    <div class="support-card chat-card">
                        <div class="card-icon"><?php echo $icon_chat; ?></div>
                        <h4>ارسال درخواست پشتیبانی</h4>
                        <p class="card-desc">پیام خود را بنویسید، در اسرع وقت پاسخ داده می‌شود.</p>

                        <form id="user-support-chat-form" class="chat-form" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="ezlens_support_send_message">
                            <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('ezlens_support_nonce')); ?>">

                            <textarea name="message" id="user-support-message" rows="2" placeholder="متن درخواست خود را وارد کنید..."></textarea>

                            <div class="chat-actions">
                                <div class="file-wrap">
                                    <span class="fake-btn"><?php echo $icon_paperclip; ?> ضمیمه</span>
                                    <input type="file" name="attachment" id="user-support-attachment">
                                </div>
                                <span id="user-support-file-name" style="font-size:0.8rem;color:#94a3b8;"></span>
                                <button type="submit" class="btn-send" id="user-support-send-btn">
                                    <?php echo $icon_send; ?> ارسال
                                </button>
                            </div>
                            <div id="user-support-status" style="font-size:0.85rem;margin-top:6px;"></div>
                        </form>

                        <div class="chat-messages" id="user-support-messages">
                            <p class="msg-empty">پیام‌های قبلی شما در اینجا نمایش داده می‌شود.</p>
                        </div>
                    </div>
                </div>

                <!-- ⬇️ بخش جدید تیکت‌ها (منتقل شده) -->
                <div style="margin-top: 20px;">
                    <h3 style="font-size: 1rem; color: var(--ezlens-primary); margin-bottom: 10px;">تیکت‌های پشتیبانی شما</h3>
                    <div id="user-tickets-container">
                        <p style="color:#94a3b8;">⏳ در حال بارگذاری...</p>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- ===== منوی پایین موبایل ===== -->
    <nav class="ezu-bottom-nav">
        <div class="ezu-bottom-nav-inner">
            <button class="active" data-tab="dashboard">
                <?php echo $icon_home; ?>
                خانه
            </button>
            <button data-tab="cart">
                <?php echo $icon_cart; ?>
                سبد
            </button>
            <button data-tab="orders">
                <?php echo $icon_orders; ?>
                سفارش
            </button>
            <button data-tab="profile">
                <?php echo $icon_profile; ?>
                پروفایل
            </button>
            <button data-tab="prescription">
                <?php echo $icon_prescription; ?>
                نسخه
            </button>
            <button data-tab="support">
                <?php echo $icon_support; ?>
                پشتیبانی
            </button>
        </div>
    </nav>

</div>

<!-- ===== اسکریپت‌ها ===== -->
<script>
jQuery(document).ready(function($) {
    'use strict';

    // ============================================================
    // ۱. ویجت چت پشتیبانی در پنل کاربری
    // ============================================================
    var $chatForm = $('#user-support-chat-form');
    var $messageInput = $('#user-support-message');
    var $fileInput = $('#user-support-attachment');
    var $fileName = $('#user-support-file-name');
    var $status = $('#user-support-status');
    var $messagesContainer = $('#user-support-messages');

    // بررسی وجود ezlens_frontend
    if (typeof ezlens_frontend === 'undefined') {
        console.warn('ezlens_frontend not defined. Using fallback.');
        var ezlens_frontend = {
            ajax_url: '<?php echo admin_url("admin-ajax.php"); ?>',
            nonce: '<?php echo wp_create_nonce("ezlens_support_nonce"); ?>'
        };
    }

    // نمایش نام فایل
    $fileInput.on('change', function() {
        if (this.files && this.files[0]) {
            $fileName.text('📎 ' + this.files[0].name);
        } else {
            $fileName.text('');
        }
    });

    // بارگذاری پیام‌های قبلی
    function loadUserMessages() {
        var nonce = $('#user-support-chat-form input[name="nonce"]').val() || ezlens_frontend.nonce;
        $.post(ezlens_frontend.ajax_url, {
            action: 'ezlens_support_get_user_messages',
            nonce: nonce
        }, function(response) {
            if (response.success) {
                var msgs = response.data.messages;
                if (!msgs || !msgs.length) {
                    $messagesContainer.html('<p style="color:#94a3b8;font-size:13px;">هنوز پیامی ارسال نشده است.</p>');
                    return;
                }
                var html = '';
                msgs.forEach(function(msg) {
                    var isAdmin = msg.sender_type === 'admin';
                    var bgColor = isAdmin ? '#2b6cb0' : '#e2e8f0';
                    var textColor = isAdmin ? '#fff' : '#0f172a';
                    var align = isAdmin ? 'text-align:right;' : 'text-align:left;';
                    html += '<div style="padding:6px 10px;margin-bottom:4px;background:' + bgColor + ';color:' + textColor + ';border-radius:8px;' + align + '">';
                    html += '<div>' + escHtml(msg.message) + '</div>';
                    if (msg.file_attachment) {
                        html += '<div><a href="' + escHtml(msg.file_attachment) + '" target="_blank" style="color:' + (isAdmin ? '#fff' : '#2b6cb0') + ';">📎 فایل</a></div>';
                    }
                    html += '<span style="font-size:10px;opacity:0.7;">' + msg.created_at + '</span>';
                    html += '</div>';
                });
                $messagesContainer.html(html);
                $messagesContainer.scrollTop($messagesContainer[0].scrollHeight);
            } else {
                $messagesContainer.html('<p style="color:#dc2626;font-size:13px;">خطا در بارگذاری پیام‌ها</p>');
            }
        }).fail(function() {
            $messagesContainer.html('<p style="color:#dc2626;font-size:13px;">خطا در ارتباط با سرور</p>');
        });
    }

    // ارسال پیام (با AJAX)
    $chatForm.on('submit', function(e) {
        e.preventDefault();

        var message = $messageInput.val().trim();
        if (!message) {
            $status.text('❌ لطفاً متن پیام را وارد کنید.').css('color', '#dc2626');
            return;
        }

        var btn = $('#user-support-send-btn');
        btn.text('⏳...').prop('disabled', true);
        $status.text('');

        var formData = new FormData(this);
        var nonce = $('#user-support-chat-form input[name="nonce"]').val() || ezlens_frontend.nonce;
        formData.append('nonce', nonce);
        formData.append('action', 'ezlens_support_send_message');

        $.ajax({
            url: ezlens_frontend.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                btn.text('📤 ارسال درخواست').prop('disabled', false);
                if (response.success) {
                    $status.text('✅ پیام شما با موفقیت ارسال شد. در اسرع وقت پاسخ داده می‌شود.').css('color', '#16a34a');
                    $messageInput.val('');
                    $fileInput.val('');
                    $fileName.text('');
                    loadUserMessages();
                } else {
                    $status.text('❌ ' + (response.data || 'خطا در ارسال پیام.')).css('color', '#dc2626');
                }
            },
            error: function() {
                btn.text('📤 ارسال درخواست').prop('disabled', false);
                $status.text('❌ خطا در ارتباط با سرور.').css('color', '#dc2626');
            }
        });
    });

    // بارگذاری اولیه پیام‌ها
    loadUserMessages();

    // ============================================================
    // ۲. بارگذاری تیکت‌های کاربر
    // ============================================================
    function loadUserTickets() {
        var $container = $('#user-tickets-container');
        $container.html('<p style="color:#94a3b8;">⏳ در حال بارگذاری...</p>');

        $.post(ezlens_frontend.ajax_url, {
            action: 'ezlens_support_get_user_tickets',
            nonce: ezlens_frontend.nonce
        }, function(response) {
            if (!response.success) {
                $container.html('<p style="color:#dc2626;">❌ خطا در بارگذاری تیکت‌ها</p>');
                return;
            }
            var tickets = response.data.tickets;
            if (!tickets || !tickets.length) {
                $container.html('<p style="color:#94a3b8;">هیچ تیکتی ارسال نشده است.</p>');
                return;
            }
            var html = '';
            tickets.forEach(function(t) {
                var statusLabel = {open:'باز', replied:'پاسخ داده شده', closed:'بسته'}[t.status] || t.status;
                html += '<div class="user-ticket-item">';
                html += '<div class="ticket-header">';
                html += '<div class="ticket-subject">#' + t.id + ' - ' + escHtml(t.subject) + '</div>';
                html += '<span class="ticket-status ' + t.status + '">' + statusLabel + '</span>';
                html += '</div>';
                html += '<div class="ticket-meta">تاریخ: ' + t.created_at + ' | آخرین بروزرسانی: ' + t.updated_at + '</div>';
                html += '</div>';
            });
            $container.html(html);
        }).fail(function() {
            $container.html('<p style="color:#dc2626;">❌ خطا در ارتباط با سرور</p>');
        });
    }

    // بارگذاری تیکت‌ها وقتی تب فعال شود
    $(document).on('click', '[data-tab="support"]', function() {
        loadUserTickets();
    });

    // اگر تب به‌طور پیش‌فرض فعال باشد
    if ($('.ezu-nav-d [data-tab="support"]').hasClass('active') || $('.ezu-bottom-nav [data-tab="support"]').hasClass('active')) {
        loadUserTickets();
    }

    function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
</script>