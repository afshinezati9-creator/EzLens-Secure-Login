<?php
if (!defined('ABSPATH')) exit;

class EzLens_Product_Options_OrderDisplay {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // نمایش در پنل کاربری (سفارش‌های من)
        add_action('woocommerce_order_item_meta_end', [$this, 'display_in_user_panel'], 10, 4);
        
        // نمایش در ادمین (ویرایش سفارش)
        add_action('woocommerce_order_item_meta_end', [$this, 'display_in_admin'], 10, 4);
        
        // نمایش در ایمیل‌های سفارش
        add_action('woocommerce_order_item_meta_end', [$this, 'display_in_emails'], 10, 4);
        
        // اضافه کردن متا باکس در صفحه سفارش ادمین
        add_action('add_meta_boxes', [$this, 'add_order_meta_box']);
    }

    /**
     * نمایش در پنل کاربری (سفارش‌های من)
     */
    public function display_in_user_panel($item_id, $item, $order, $plain_text = false) {
        // فقط در فرانت‌اند و پنل کاربری
        if (is_admin() && !wp_doing_ajax()) return;
        if ($plain_text) return;
        
        $options = $this->get_options_from_item($item);
        if (empty($options)) return;
        
        echo '<div class="ezlens-order-options user-panel" style="margin-top:8px;padding:12px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">';
        echo '<div style="font-weight:600;font-size:13px;color:#0f172a;margin-bottom:6px;">🧩 ویژگی‌های انتخاب‌شده</div>';
        
        foreach ($options as $label => $value) {
            echo '<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">';
            echo '<span style="color:#475569;">' . esc_html($label) . '</span>';
            echo '<span style="color:#0f172a;font-weight:500;">' . esc_html($value) . '</span>';
            echo '</div>';
        }
        
        echo '</div>';
    }

    /**
     * نمایش در ادمین (ویرایش سفارش)
     */
    public function display_in_admin($item_id, $item, $order, $plain_text = false) {
        // فقط در ادمین
        if (!is_admin()) return;
        
        $options = $this->get_options_from_item($item);
        if (empty($options)) return;
        
        // اگر قبلاً نمایش داده شده، جلوگیری از تکرار
        static $displayed_items = [];
        if (in_array($item_id, $displayed_items)) return;
        $displayed_items[] = $item_id;
        
        echo '<div class="ezlens-order-options admin-panel" style="margin:8px 0;padding:12px 16px;background:#f8fafc;border-radius:6px;border:1px solid #e2e8f0;">';
        echo '<div style="font-weight:700;font-size:12px;color:#0f172a;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">🧩 ویژگی‌های سفارش</div>';
        
        foreach ($options as $label => $value) {
            echo '<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">';
            echo '<span style="color:#475569;">' . esc_html($label) . '</span>';
            echo '<span style="color:#0f172a;font-weight:500;">' . esc_html($value) . '</span>';
            echo '</div>';
        }
        
        echo '</div>';
    }

    /**
     * نمایش در ایمیل‌های سفارش
     */
    public function display_in_emails($item_id, $item, $order, $plain_text = true) {
        if (!$plain_text) return;
        
        $options = $this->get_options_from_item($item);
        if (empty($options)) return;
        
        $output = "\n" . '🧩 ویژگی‌های سفارش:' . "\n";
        foreach ($options as $label => $value) {
            $output .= '  ' . $label . ': ' . $value . "\n";
        }
        
        echo $output;
    }

    /**
     * دریافت ویژگی‌ها از آیتم سفارش
     */
    private function get_options_from_item($item) {
        $options = [];
        $meta_data = $item->get_meta_data();
        
        foreach ($meta_data as $meta) {
            $key = $meta->get_data()['key'];
            $value = $meta->get_data()['value'];
            
            // رد کردن متاهای ووکامرس و متاهای غیرمرتبط
            if (strpos($key, '_') === 0) continue;
            if (strpos($key, 'ezlens') !== false) continue;
            if (strpos($key, 'هزینه اضافی') !== false) continue;
            
            // اگر مقدار آرایه است، تبدیل به رشته
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            
            if (!empty($value)) {
                $options[$key] = $value;
            }
        }
        
        return $options;
    }

    /**
     * افزودن متاباکس در صفحه سفارش ادمین
     */
    public function add_order_meta_box() {
        add_meta_box(
            'ezlens_order_options_meta',
            '🧩 ویژگی‌های سفارش',
            [$this, 'render_order_meta_box'],
            'shop_order',
            'side',
            'default'
        );
    }

    /**
     * رندر متاباکس در صفحه سفارش ادمین
     */
    public function render_order_meta_box($post) {
        $order = wc_get_order($post->ID);
        if (!$order) return;
        
        $items = $order->get_items();
        $all_options = [];
        
        foreach ($items as $item) {
            $options = $this->get_options_from_item($item);
            if (!empty($options)) {
                $product_name = $item->get_name();
                $all_options[$product_name] = $options;
            }
        }
        
        if (empty($all_options)) {
            echo '<p style="color:#94a3b8;font-size:13px;">هیچ ویژگی‌ای برای این سفارش ثبت نشده است.</p>';
            return;
        }
        
        echo '<div style="max-height:300px;overflow-y:auto;">';
        foreach ($all_options as $product => $options) {
            echo '<div style="margin-bottom:12px;padding:8px 10px;background:#f8fafc;border-radius:6px;border:1px solid #e2e8f0;">';
            echo '<div style="font-weight:600;font-size:12px;color:#0f172a;margin-bottom:4px;">' . esc_html($product) . '</div>';
            
            foreach ($options as $label => $value) {
                echo '<div style="display:flex;justify-content:space-between;font-size:12px;padding:2px 0;border-bottom:1px solid #f1f5f9;">';
                echo '<span style="color:#64748b;">' . esc_html($label) . '</span>';
                echo '<span style="color:#0f172a;">' . esc_html($value) . '</span>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        echo '</div>';
    }
}

EzLens_Product_Options_OrderDisplay::get_instance();