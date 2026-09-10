<?php
/**
 * Product Options meta box — multi-slot UI (Phase 1).
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_Product_Options_MetaBox
 */
if (!class_exists('EzLens_Product_Options_MetaBox', false)) {
class EzLens_Product_Options_MetaBox {

    /** @var self|null */
    private static $instance = null;

    /** @var EzLens_Product_Options_Template_Manager */
    private $manager;

    /**
     * @return self
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->manager = EzLens_Product_Options_Template_Manager::get_instance();
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post_product', array($this, 'save_meta_box'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * @param string $hook
     */
    public function enqueue_assets($hook) {
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->post_type !== 'product') {
            return;
        }

        $base = EZLAUTH_MODULES_DIR . 'product-options/admin/meta-box/assets/';
        $url  = EZLAUTH_MODULES_URL . 'product-options/admin/meta-box/assets/';

        $css = $base . 'meta-box.css';
        $js  = $base . 'meta-box.js';

        if (is_readable($css)) {
            wp_enqueue_style(
                'ezlens-po-meta-box',
                $url . 'meta-box.css',
                array(),
                (string) filemtime($css)
            );
        }
        if (is_readable($js)) {
            wp_enqueue_script(
                'ezlens-po-meta-box',
                $url . 'meta-box.js',
                array('jquery'),
                (string) filemtime($js),
                true
            );
            wp_localize_script(
                'ezlens-po-meta-box',
                'ezlensSlotsMeta',
                array(
                    'previewNonce' => wp_create_nonce('ezlens_template_preview_nonce'),
                    'ajaxUrl'      => admin_url('admin-ajax.php'),
                )
            );
        }
    }

    public function add_meta_box() {
        add_meta_box(
            'ezlens_product_options',
            'ویژگی‌های اختصاصی محصول',
            array($this, 'render_meta_box'),
            'product',
            'normal',
            'default'
        );
    }

    /**
     * @return array
     */
    private function placement_labels() {
        return array(
            'gallery_side'  => 'کنار گالری / عکس محصول',
            'below_price'   => 'زیر قیمت (ستون خلاصه)',
            'below_summary' => 'زیر خلاصه محصول',
            'full_width'    => 'تمام‌عرض زیر بلوک اصلی',
        );
    }

    /**
     * @return array
     */
    private function display_labels() {
        return array(
            'inline'     => 'مستقیم روی صفحه',
            'accordion'  => 'کشویی (آکاردئون)',
            'ajax_modal' => 'باکس / مودال AJAX',
        );
    }

    /**
     * @param WP_Post $post
     */
    public function render_meta_box($post) {
        wp_nonce_field('ezlens_product_options_meta', 'ezlens_product_options_nonce');

        $slots_service = $this->manager->get_slots_service();
        $slots         = $slots_service->get_slots((int) $post->ID);

        $templates = $this->manager->get_list(
            array(
                'status' => 'active',
                'limit'  => 999,
                'offset' => 0,
            )
        );
        if (!is_array($templates)) {
            $templates = array();
        }
        // Normalize list if service returned {items,total}.
        if (isset($templates['items']) && is_array($templates['items'])) {
            $templates = $templates['items'];
        }

        $placements = $this->placement_labels();
        $displays   = $this->display_labels();
        $view       = EZLAUTH_MODULES_DIR . 'product-options/admin/meta-box/views/slot-row.php';

        ?>
        <div class="ezlens-slots-box">
            <div class="ezlens-slots-header">
                <div>
                    <h4>پالت‌های متصل به این محصول</h4>
                    <p>
                        می‌توانید چند پالت جدا (مثلاً رنگ کنار عکس، نسخه زیر قیمت) اضافه کنید.
                        مکان و حالت نمایش هر پالت را جداگانه انتخاب کنید. ذخیرهٔ نهایی روی دکمهٔ «به‌روزرسانی» محصول است.
                    </p>
                </div>
            </div>

            <div id="ezlens-slots-empty" class="ezlens-slots-empty" style="<?php echo empty($slots) ? '' : 'display:none;'; ?>">
                <strong>هنوز پالتی وصل نشده</strong>
                با دکمهٔ زیر اولین اسلات را اضافه کنید.
            </div>

            <div id="ezlens-slots-list" class="ezlens-slots-list">
                <?php
                foreach ($slots as $i => $slot) {
                    $index = (int) $i;
                    if (is_readable($view)) {
                        include $view;
                    }
                }
                ?>
            </div>

            <div class="ezlens-slots-footer">
                <button type="button" class="ezlens-add-slot" id="ezlens-add-slot">+ افزودن پالت</button>
            </div>

            <div class="ezlens-placement-hint">
                <strong>مکان و حالت نمایش روی صفحه محصول اعمال می‌شود:</strong>
                مستقیم (inline)، کشویی (accordion)، یا مودال.
                قیمت نهایی/هزینه اضافی همیشه بیرون مودال و به‌صورت مستقیم نمایش داده می‌شود.
                پس از ذخیره محصول، صفحهٔ محصول را در یک تب ناشناس تست کنید.
            </div>
        </div>

        <script type="text/template" id="ezlens-slot-row-template">
            <?php
            $index       = 0;
            $slot        = array();
            $is_template = true;
            if (is_readable($view)) {
                include $view;
            }
            ?>
        </script>
        <?php
    }

    /**
     * @param int     $post_id
     * @param WP_Post $post
     */
    public function save_meta_box($post_id, $post) {
        if (!isset($_POST['ezlens_product_options_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ezlens_product_options_nonce'])), 'ezlens_product_options_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $raw = isset($_POST['ezlens_slots']) && is_array($_POST['ezlens_slots'])
            ? wp_unslash($_POST['ezlens_slots'])
            : array();

        $slots = array();
        $order = 0;
        foreach ($raw as $row) {
            if (!is_array($row)) {
                continue;
            }
            $tid = absint($row['template_id'] ?? 0);
            if ($tid <= 0) {
                continue;
            }
            // Ensure template exists.
            $tpl = $this->manager->get($tid);
            if (!$tpl) {
                continue;
            }
            $order++;
            $slots[] = array(
                'template_id' => $tid,
                'label'       => sanitize_text_field($row['label'] ?? ''),
                'placement'   => sanitize_key($row['placement'] ?? 'below_price'),
                'display'     => sanitize_key($row['display'] ?? 'inline'),
                'order'       => $order,
            );
        }

        $this->manager->get_slots_service()->save_slots($post_id, $slots, true);
    }
}

EzLens_Product_Options_MetaBox::get_instance();
}
