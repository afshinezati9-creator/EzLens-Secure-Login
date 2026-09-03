<?php
/**
 * کلاس مدیریت رهگیری سفارشات
 * EzLens Order Tracking
 * @version 2.1.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class EzLens_Order_Tracking {

    public static function get_steps() {
        return [
            'pending'    => [
                'label'   => 'ثبت سفارش',
                'icon'    => '📋',
                'status'  => 'wc-pending',
                'order'   => 1,
                'desc'    => 'سفارش شما ثبت شد و در انتظار تأیید است.',
            ],
            'processing' => [
                'label'   => 'در حال پردازش',
                'icon'    => '⚙️',
                'status'  => 'wc-processing',
                'order'   => 2,
                'desc'    => 'سفارش شما در حال بررسی و آماده‌سازی است.',
            ],
            'on-hold'    => [
                'label'   => 'در انتظار بررسی',
                'icon'    => '⏳',
                'status'  => 'wc-on-hold',
                'order'   => 3,
                'desc'    => 'سفارش شما نیاز به بررسی بیشتر دارد.',
            ],
            'shipped'    => [
                'label'   => 'ارسال شده',
                'icon'    => '🚚',
                'status'  => 'wc-shipped',
                'order'   => 4,
                'desc'    => 'سفارش شما به پست تحویل داده شده است.',
            ],
            'completed'  => [
                'label'   => 'تحویل داده شده',
                'icon'    => '✅',
                'status'  => 'wc-completed',
                'order'   => 5,
                'desc'    => 'سفارش شما با موفقیت تحویل داده شد.',
            ],
        ];
    }

    public static function register_shipped_status() {
        if (!class_exists('WooCommerce')) return;

        add_action('init', function() {
            register_post_status('wc-shipped', [
                'label'                     => 'ارسال شده',
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop('ارسال شده (%s)', 'ارسال شده (%s)'),
            ]);
        });

        add_filter('wc_order_statuses', function($statuses) {
            $new_statuses = [];
            foreach ($statuses as $key => $label) {
                $new_statuses[$key] = $label;
                if ($key === 'wc-processing') {
                    $new_statuses['wc-shipped'] = 'ارسال شده';
                }
            }
            return $new_statuses;
        });
    }

    public static function get_current_step($order) {
        if (!$order) return null;
        $status = $order->get_status();
        foreach (self::get_steps() as $key => $step) {
            if ($step['status'] === 'wc-' . $status) {
                return $key;
            }
        }
        return 'pending';
    }

    public static function get_current_step_number($order) {
        $step = self::get_current_step($order);
        if (!$step) return 1;
        $steps = self::get_steps();
        return isset($steps[$step]) ? $steps[$step]['order'] : 1;
    }

    public static function get_progress_percent($order) {
        $current = self::get_current_step_number($order);
        $total = count(self::get_steps());
        return round(($current / $total) * 100);
    }

    public static function get_tracking_code($order_id) {
        return get_post_meta($order_id, '_ezlens_tracking_code', true);
    }

    public static function set_tracking_code($order_id, $code) {
        update_post_meta($order_id, '_ezlens_tracking_code', sanitize_text_field($code));
    }

    public static function get_estimated_delivery_date($order) {
        $created = $order->get_date_created();
        if (!$created) return false;
        $timestamp = $created->getTimestamp();
        $days_to_add = self::get_estimated_days($order);
        $delivery_date = $timestamp + ($days_to_add * DAY_IN_SECONDS);
        return date_i18n('Y/m/d', $delivery_date);
    }

    public static function get_estimated_days($order) {
        $step = self::get_current_step($order);
        switch ($step) {
            case 'pending':    return 5;
            case 'processing': return 3;
            case 'on-hold':    return 4;
            case 'shipped':    return 2;
            case 'completed':  return 0;
            default:           return 5;
        }
    }

    public static function get_tracking_link($code) {
        if (empty($code)) return '#';
        $base_url = EzLens_Auth_Settings::get('tracking_base_url') ?: 'https://tracking.post.ir/?id=';
        return $base_url . urlencode($code);
    }

    public static function has_tracking_code($order_id) {
        return !empty(self::get_tracking_code($order_id));
    }

    public static function set_shipped_date($order_id, $date = null) {
        if (!$date) $date = current_time('mysql');
        update_post_meta($order_id, '_ezlens_shipped_date', $date);
    }

    public static function get_shipped_date($order_id) {
        return get_post_meta($order_id, '_ezlens_shipped_date', true);
    }
}

EzLens_Order_Tracking::register_shipped_status();