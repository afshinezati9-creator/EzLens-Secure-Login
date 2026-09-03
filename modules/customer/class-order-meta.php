<?php
/**
 * کلاس مدیریت متاباکس کد رهگیری سفارشات
 * EzLens Auth - Order Meta
 */
class EzLens_Auth_Order_Meta {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // فقط اگر ووکامرس فعال باشد
        if (class_exists('WooCommerce')) {
            add_action('add_meta_boxes', [$this, 'add_tracking_metabox']);
            add_action('save_post_shop_order', [$this, 'save_tracking_meta'], 10, 2);
        }
    }

    /**
     * افزودن متاباکس کد رهگیری در صفحه ویرایش سفارش
     */
    public function add_tracking_metabox() {
        add_meta_box(
            'ezlens_order_tracking',
            '📦 رهگیری سفارش (EzLens)',
            [$this, 'render_tracking_metabox'],
            'shop_order',
            'side',
            'default'
        );
    }

    /**
     * رندر متاباکس کد رهگیری
     */
    public function render_tracking_metabox($post) {
        $order = wc_get_order($post->ID);
        if (!$order) return;

        wp_nonce_field('ezlens_tracking_meta', 'ezlens_tracking_nonce');

        $tracking_code = get_post_meta($post->ID, '_ezlens_tracking_code', true);
        $shipped_date = get_post_meta($post->ID, '_ezlens_shipped_date', true);
        ?>
        <p style="margin:0 0 8px;">
            <label for="ezlens_tracking_code" style="display:block;font-weight:600;font-size:12px;margin-bottom:4px;">کد رهگیری:</label>
            <input type="text" id="ezlens_tracking_code" name="ezlens_tracking_code" 
                   value="<?php echo esc_attr($tracking_code); ?>" 
                   style="width:100%;padding:6px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px;">
        </p>
        <p style="margin:0 0 8px;">
            <label for="ezlens_shipped_date" style="display:block;font-weight:600;font-size:12px;margin-bottom:4px;">تاریخ ارسال:</label>
            <input type="date" id="ezlens_shipped_date" name="ezlens_shipped_date" 
                   value="<?php echo $shipped_date ? date('Y-m-d', strtotime($shipped_date)) : ''; ?>" 
                   style="width:100%;padding:6px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px;">
        </p>
        <p class="description" style="font-size:11px;color:#666;margin:6px 0 0;">پس از ثبت کد رهگیری، مشتری می‌تواند سفارش خود را در پنل کاربری پیگیری کند.</p>
        <?php
    }

    /**
     * ذخیره کد رهگیری و تاریخ ارسال
     */
    public function save_tracking_meta($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['ezlens_tracking_nonce']) || !wp_verify_nonce($_POST['ezlens_tracking_nonce'], 'ezlens_tracking_meta')) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['ezlens_tracking_code'])) {
            update_post_meta($post_id, '_ezlens_tracking_code', sanitize_text_field($_POST['ezlens_tracking_code']));
        }
        if (isset($_POST['ezlens_shipped_date']) && !empty($_POST['ezlens_shipped_date'])) {
            update_post_meta($post_id, '_ezlens_shipped_date', sanitize_text_field($_POST['ezlens_shipped_date']));
        }
    }
}

// مقداردهی اولیه
EzLens_Auth_Order_Meta::get_instance();