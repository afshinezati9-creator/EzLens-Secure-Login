<?php
/**
 * Frontend field rendering — multi-slot, placement-aware (Phase 2).
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_Product_Options_FieldRenderer
 */
class EzLens_Product_Options_FieldRenderer {

    /** @var self|null */
    private static $instance = null;

    /** @var EzLens_Product_Options_Template_Manager */
    private $manager;

    /** @var bool */
    private $modal_shell_printed = false;

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

        $placement_file = EZLAUTH_MODULES_DIR . 'product-options/frontend/placements/class-placement-registry.php';
        $display_file   = EZLAUTH_MODULES_DIR . 'product-options/frontend/display-modes/class-display-modes.php';
        $dispatch_file  = EZLAUTH_MODULES_DIR . 'product-options/frontend/renderers/class-field-dispatcher.php';
        if (is_readable($placement_file)) {
            require_once $placement_file;
        }
        if (is_readable($display_file)) {
            require_once $display_file;
        }
        if (is_readable($dispatch_file)) {
            require_once $dispatch_file;
            if (class_exists('EzLens_PO_Field_Dispatcher')) {
                EzLens_PO_Field_Dispatcher::boot();
            }
        }

        // Register one callback per placement hook/priority.
        if (class_exists('EzLens_PO_Placement_Registry')) {
            foreach (EzLens_PO_Placement_Registry::map() as $placement => $pairs) {
                foreach ($pairs as $pair) {
                    $hook = $pair[0];
                    $pri  = isset($pair[1]) ? (int) $pair[1] : 10;
                    add_action(
                        $hook,
                        function () use ($placement) {
                            $this->render_placement($placement);
                        },
                        $pri
                    );
                }
            }
        } else {
            add_action('woocommerce_before_add_to_cart_button', array($this, 'render_legacy'), 15);
        }

        // Fallback: slots still rendered near cart if theme removes summary hooks.
        add_action('woocommerce_before_add_to_cart_button', array($this, 'render_unplaced_fallback'), 14);

        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'print_modal_shell'), 5);

        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 3);
        add_filter('woocommerce_get_item_data', array($this, 'display_cart_item_data'), 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_order_item_meta'), 10, 4);

        add_action('wp_ajax_ezlens_po_load_slot', array($this, 'ajax_load_slot'));
        add_action('wp_ajax_nopriv_ezlens_po_load_slot', array($this, 'ajax_load_slot'));
    }

    /**
     * @return int
     */
    private function current_product_id() {
        global $post;
        if (!$post) {
            return 0;
        }
        $product = wc_get_product($post->ID);
        if ($product && $product->is_type('variation')) {
            return (int) $product->get_parent_id();
        }
        return (int) $post->ID;
    }

    /**
     * @param int $product_id
     * @return array[]
     */
    private function slots_for_product($product_id) {
        if ($product_id <= 0) {
            return array();
        }
        return $this->manager->get_slots_service()->get_slots($product_id);
    }

    /**
     * Track which slots already rendered this request.
     *
     * @var array
     */
    private $rendered_slot_keys = array();

    /**
     * @param string $placement
     */
    public function render_placement($placement) {
        if (!is_product()) {
            return;
        }
        $product_id = $this->current_product_id();
        $slots      = $this->slots_for_product($product_id);
        if (empty($slots)) {
            return;
        }

        $placement = sanitize_key($placement);
        $matched   = array();
        foreach ($slots as $slot) {
            $sp = sanitize_key($slot['placement'] ?? 'below_price');
            if ($sp === $placement) {
                $matched[] = $slot;
            }
        }
        if (empty($matched)) {
            return;
        }

        echo '<div class="ezlens-po-root ezlens-po-placement-' . esc_attr($placement) . '" data-product-id="' . esc_attr((string) $product_id) . '">';
        foreach ($matched as $slot) {
            $this->render_slot($slot, $product_id);
        }
        // Price summary always visible (outside accordion/modal content).
        echo '<div class="ezlens-po-price-hint" hidden></div>';
        echo '</div>';
    }

    /**
     * Legacy single hook if registry missing.
     */
    public function render_legacy() {
        $this->render_placement('below_price');
    }

    /**
     * Render slots whose placement hook never fired (e.g. custom template).
     */
    public function render_unplaced_fallback() {
        if (!is_product()) {
            return;
        }
        $product_id = $this->current_product_id();
        $slots      = $this->slots_for_product($product_id);
        $pending    = array();
        foreach ($slots as $slot) {
            $key = absint($slot['template_id'] ?? 0) . ':' . sanitize_key($slot['placement'] ?? '') . ':' . sanitize_key($slot['display'] ?? '');
            if (!isset($this->rendered_slot_keys[$key])) {
                $pending[] = $slot;
            }
        }
        if (empty($pending)) {
            return;
        }
        echo '<div class="ezlens-po-root ezlens-po-placement-fallback" data-product-id="' . esc_attr((string) $product_id) . '">';
        foreach ($pending as $slot) {
            $this->render_slot($slot, $product_id);
        }
        echo '<div class="ezlens-po-price-hint" hidden></div>';
        echo '</div>';
    }

    /**
     * @param array $slot
     * @param int   $product_id
     */
    private function render_slot(array $slot, $product_id) {
        $tid = absint($slot['template_id'] ?? 0);
        if ($tid <= 0) {
            return;
        }
        $template = $this->manager->get($tid);
        if (!$template || ($template['status'] ?? '') === 'inactive') {
            return;
        }

        $key = $tid . ':' . sanitize_key($slot['placement'] ?? '') . ':' . sanitize_key($slot['display'] ?? '');
        $this->rendered_slot_keys[$key] = true;

        if (!class_exists('EzLens_PO_Display_Modes')) {
            echo '<div class="ezlens-po-slot">';
            $this->render_fields_for_template($template, $tid);
            echo '</div>';
            return;
        }

        EzLens_PO_Display_Modes::open($slot, $template);
        $this->render_fields_for_template($template, $tid);
        EzLens_PO_Display_Modes::close($slot);
    }

    /**
     * Field names: ezlens_options[{template_id}][{field_key}]
     *
     * @param array $template
     * @param int   $template_id
     */
    private function render_fields_for_template(array $template, $template_id) {
        $fields = $template['fields'] ?? array();
        if (!is_array($fields) || empty($fields)) {
            return;
        }

        // Schema may store fields as list or map.
        foreach ($fields as $key => $field) {
            if (!is_array($field)) {
                continue;
            }
            // Prefer explicit name from field schema.
            $field_key = isset($field['name']) ? sanitize_key($field['name']) : sanitize_key((string) $key);
            if ($field_key === '' || (class_exists('EzLens_Product_Options_Helpers') && EzLens_Product_Options_Helpers::is_code_field_key($field_key))) {
                continue;
            }
            if (is_string($key) && class_exists('EzLens_Product_Options_Helpers') && EzLens_Product_Options_Helpers::is_code_field_key($key)) {
                continue;
            }
            $this->render_field($field_key, $field, $template_id);
        }
    }

    /**
     * @param string $key
     * @param array  $field
     * @param int    $template_id
     */
    private function render_field($key, array $field, $template_id) {
        $conditions = $field['conditions'] ?? $field['conditional_logic'] ?? array();
        $cond_attr  = '';
        if (!empty($conditions) && is_array($conditions)) {
            $cond_attr = ' data-conditions="' . esc_attr(wp_json_encode($conditions, JSON_UNESCAPED_UNICODE)) . '"';
        }

        if (class_exists('EzLens_PO_Field_Dispatcher')) {
            // Inject conditions onto wrapper via filter output buffer? Simpler: set on field temp.
            if ($cond_attr !== '') {
                ob_start();
                EzLens_PO_Field_Dispatcher::render($key, $field, $template_id);
                $html = ob_get_clean();
                // Add conditions to first wrapper div.
                $html = preg_replace(
                    '/(<div class="ezlens-field-wrapper[^"]*")/',
                    '$1' . $cond_attr,
                    $html,
                    1
                );
                echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                return;
            }
            EzLens_PO_Field_Dispatcher::render($key, $field, $template_id);
            return;
        }

        // Extreme fallback: plain text input
        $name = 'ezlens_options[' . absint($template_id) . '][' . esc_attr($key) . ']';
        echo '<div class="ezlens-field-wrapper"' . $cond_attr . '><input type="text" name="' . esc_attr($name) . '" class="ezlens-field-input"></div>';
    }


    private function render_structural($type, $label, array $field, array $settings) {
        if ($type === 'heading') {
            $level = in_array(($settings['level'] ?? 'h3'), array('h2', 'h3', 'h4', 'h5', 'h6'), true) ? $settings['level'] : 'h3';
            echo '<' . $level . ' class="ezlens-field-heading">' . esc_html($label) . '</' . $level . '>';
            return;
        }
        if ($type === 'divider') {
            $thickness = max(1, (int) ($settings['thickness'] ?? 1));
            $color     = sanitize_hex_color($settings['color'] ?? '#e2e8f0') ?: '#e2e8f0';
            echo '<hr class="ezlens-field-divider" style="border:0;border-top:' . esc_attr((string) $thickness) . 'px solid ' . esc_attr($color) . ';">';
            return;
        }
        if ($type === 'spacer') {
            $h = max(4, (int) ($settings['height'] ?? 16));
            echo '<div class="ezlens-field-spacer" style="height:' . esc_attr((string) $h) . 'px"></div>';
            return;
        }
        if ($type === 'html') {
            $html = $field['content'] ?? $field['html'] ?? '';
            echo '<div class="ezlens-field-html">' . wp_kses_post($html) . '</div>';
        }
    }

    /**
     * @param float $amount
     * @return string
     */
    private function price_label($amount) {
        if (class_exists('EzLens_Product_Options_Helpers')) {
            return EzLens_Product_Options_Helpers::format_price_fa($amount);
        }
        return wp_strip_all_tags(wc_price($amount));
    }

    public function enqueue_assets() {
        if (!function_exists('is_product') || !is_product()) {
            return;
        }
        $product_id = $this->current_product_id();
        $slots      = $this->slots_for_product($product_id);
        if (empty($slots)) {
            return;
        }

        $css_module = EZLAUTH_MODULES_DIR . 'product-options/frontend/assets/product-options-phase2.css';
        $js_module  = EZLAUTH_MODULES_DIR . 'product-options/frontend/assets/product-options-phase2.js';
        $css_legacy = EZLAUTH_PLUGIN_DIR . 'frontend/assets/css/product-options-frontend.css';
        $js_legacy  = EZLAUTH_PLUGIN_DIR . 'frontend/assets/js/product-options.js';

        if (is_readable($css_module)) {
            wp_enqueue_style(
                'ezlens-product-options',
                EZLAUTH_MODULES_URL . 'product-options/frontend/assets/product-options-phase2.css',
                array(),
                (string) filemtime($css_module)
            );
        } elseif (is_readable($css_legacy)) {
            wp_enqueue_style(
                'ezlens-product-options',
                EZLAUTH_PLUGIN_URL . 'frontend/assets/css/product-options-frontend.css',
                array(),
                (string) filemtime($css_legacy)
            );
        }

        $product = wc_get_product($product_id);
        $deps    = array('jquery');
        $js_url  = '';
        $js_ver  = EZLAUTH_VERSION;

        if (is_readable($js_module)) {
            $js_url = EZLAUTH_MODULES_URL . 'product-options/frontend/assets/product-options-phase2.js';
            $js_ver = (string) filemtime($js_module);
        } elseif (is_readable($js_legacy)) {
            $js_url = EZLAUTH_PLUGIN_URL . 'frontend/assets/js/product-options.js';
            $js_ver = (string) filemtime($js_legacy);
        }

        if ($js_url !== '') {
            wp_enqueue_script('ezlens-product-options', $js_url, $deps, $js_ver, true);
            wp_localize_script(
                'ezlens-product-options',
                'ezlens_po',
                array(
                    'ajax_url'         => admin_url('admin-ajax.php'),
                    'nonce'            => wp_create_nonce('ezlens_po_nonce'),
                    'product_id'       => $product_id,
                    'price'            => $product ? (float) $product->get_price() : 0,
                    'currency'         => get_woocommerce_currency_symbol(),
                    'currency_suffix'  => html_entity_decode(get_woocommerce_currency_symbol()),
                    'price_decimals'   => wc_get_price_decimals(),
                    'i18n'             => array(
                        'options'   => 'گزینه‌ها',
                        'close'     => 'بستن',
                        'confirm'   => 'تأیید',
                        'extra'     => 'هزینه اضافی',
                        'total'     => 'جمع',
                    ),
                )
            );
        }
    }

    public function print_modal_shell() {
        if (!function_exists('is_product') || !is_product() || $this->modal_shell_printed) {
            return;
        }
        $slots = $this->slots_for_product($this->current_product_id());
        $need  = false;
        foreach ($slots as $s) {
            if (($s['display'] ?? '') === 'ajax_modal') {
                $need = true;
                break;
            }
        }
        if (!$need) {
            return;
        }
        $this->modal_shell_printed = true;
        ?>
        <div id="ezlens-po-modal" class="ezlens-po-modal" hidden>
            <div class="ezlens-po-modal-backdrop" data-close="1"></div>
            <div class="ezlens-po-modal-dialog" role="dialog" aria-modal="true">
                <div class="ezlens-po-modal-header">
                    <strong class="ezlens-po-modal-title"></strong>
                    <button type="button" class="ezlens-po-modal-close" data-close="1" aria-label="بستن">×</button>
                </div>
                <div class="ezlens-po-modal-body"></div>
                <div class="ezlens-po-modal-footer">
                    <button type="button" class="ezlens-po-modal-done" data-close="1">تأیید</button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Optional AJAX refresh of slot HTML (for future lazy modal).
     */
    public function ajax_load_slot() {
        check_ajax_referer('ezlens_po_nonce', 'nonce');
        $product_id  = absint($_POST['product_id'] ?? 0);
        $template_id = absint($_POST['template_id'] ?? 0);
        if (!$product_id || !$template_id) {
            wp_send_json_error(array('message' => 'پارامتر نامعتبر'), 400);
        }
        $template = $this->manager->get($template_id);
        if (!$template) {
            wp_send_json_error(array('message' => 'پالت یافت نشد'), 404);
        }
        ob_start();
        $this->render_fields_for_template($template, $template_id);
        $html = ob_get_clean();
        wp_send_json_success(array('html' => $html));
    }

    /**
     * Flatten POST ezlens_options[tid][key] → storage array with namespaced keys tid:key
     *
     * @param array $raw
     * @param int   $product_id
     * @return array
     */
    private function collect_options_from_request($raw, $product_id) {
        if (!is_array($raw)) {
            return array();
        }
        $slots = $this->slots_for_product($product_id);
        if (empty($slots) && !empty($raw)) {
            // Legacy flat POST.
            return $this->sanitize_options_flat($raw, $product_id);
        }

        $out = array();
        foreach ($slots as $slot) {
            $tid = absint($slot['template_id'] ?? 0);
            if ($tid <= 0) {
                continue;
            }
            $template = $this->manager->get($tid);
            if (!$template) {
                continue;
            }
            $bucket = isset($raw[$tid]) && is_array($raw[$tid]) ? $raw[$tid] : array();
            // Also accept string keys.
            if (empty($bucket) && isset($raw[(string) $tid]) && is_array($raw[(string) $tid])) {
                $bucket = $raw[(string) $tid];
            }
            $sanitized = $this->sanitize_against_template($bucket, $template);
            foreach ($sanitized as $k => $v) {
                $out[$tid . ':' . $k] = $v;
            }
        }
        return $out;
    }

    /**
     * @param array $options
     * @param array $template
     * @return array
     */
    private function sanitize_against_template(array $options, array $template) {
        $sanitized = array();
        foreach (($template['fields'] ?? array()) as $key => $field) {
            if (!is_array($field)) {
                continue;
            }
            $field_key = isset($field['name']) ? sanitize_key($field['name']) : sanitize_key((string) $key);
            if ($field_key === '' || (is_string($key) && strpos($key, '_code_') === 0)) {
                continue;
            }
            $type  = sanitize_key($field['type'] ?? 'text');
            $value = $options[$field_key] ?? null;
            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                continue;
            }
            switch ($type) {
                case 'email':
                    $value = sanitize_email($value);
                    break;
                case 'number':
                    $value = is_numeric($value) ? (string) (float) $value : '';
                    break;
                case 'checkbox':
                    $value = is_array($value) ? array_values(array_map('sanitize_text_field', $value)) : sanitize_text_field($value);
                    break;
                case 'upload':
                    $value = esc_url_raw($value);
                    break;
                default:
                    $value = is_array($value) ? array_values(array_map('sanitize_text_field', $value)) : sanitize_text_field($value);
            }
            if (in_array($type, array('select', 'radio', 'checkbox', 'image_select'), true) && !empty($field['options']) && is_array($field['options'])) {
                $allowed = array();
                foreach ($field['options'] as $opt) {
                    if (is_array($opt) && isset($opt['value'])) {
                        $allowed[] = (string) $opt['value'];
                    }
                }
                if (is_array($value)) {
                    $value = array_values(array_intersect(array_map('strval', $value), $allowed));
                } elseif (!in_array((string) $value, $allowed, true)) {
                    $value = '';
                }
            }
            if ($value !== '' && $value !== array()) {
                $sanitized[$field_key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * @param array $options
     * @param int   $product_id
     * @return array
     */
    private function sanitize_options_flat(array $options, $product_id) {
        $template = $this->manager->get_template_for_product($product_id);
        if (!$template) {
            return array();
        }
        return $this->sanitize_against_template($options, $template);
    }

    public function add_cart_item_data($cart_item_data, $product_id, $variation_id) {
        $pid = $variation_id ? (int) (wc_get_product($variation_id) ? wc_get_product($variation_id)->get_parent_id() : $product_id) : (int) $product_id;
        $raw = isset($_POST['ezlens_options']) ? wp_unslash($_POST['ezlens_options']) : array();
        if (!is_array($raw) || empty($raw)) {
            return $cart_item_data;
        }
        $options = $this->collect_options_from_request($raw, $pid);
        if (empty($options)) {
            return $cart_item_data;
        }
        $cart_item_data['ezlens_options']     = $options;
        $cart_item_data['ezlens_product_id']  = $pid;
        $cart_item_data['ezlens_options_key'] = md5(wp_json_encode($options));
        return $cart_item_data;
    }

    public function display_cart_item_data($item_data, $cart_item) {
        // Professional cart UI is handled by EzLens_PO_Cart_Display.
        if (class_exists('EzLens_PO_Cart_Display') && !empty($cart_item['ezlens_options'])) {
            return $item_data;
        }
        if (empty($cart_item['ezlens_options']) || !is_array($cart_item['ezlens_options'])) {
            return $item_data;
        }
        $product_id = absint($cart_item['ezlens_product_id'] ?? $cart_item['product_id'] ?? 0);
        foreach ($cart_item['ezlens_options'] as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            if ($value === '') {
                continue;
            }
            $item_data[] = array(
                'name'  => $this->get_field_label((string) $key, $product_id),
                'value' => $value,
            );
        }
        if (!empty($cart_item['ezlens_price_extra'])) {
            $extra = (float) $cart_item['ezlens_price_extra'];
            $item_data[] = array(
                'name'  => 'هزینه اضافی',
                'value' => class_exists('EzLens_Product_Options_Helpers')
                    ? EzLens_Product_Options_Helpers::format_price_fa($extra)
                    : wp_strip_all_tags(wc_price($extra)),
            );
        }
        return $item_data;
    }

    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        if (empty($values['ezlens_options']) || !is_array($values['ezlens_options'])) {
            return;
        }
        $product_id = absint($values['ezlens_product_id'] ?? $values['product_id'] ?? 0);
        foreach ($values['ezlens_options'] as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            if ($value === '') {
                continue;
            }
            $item->add_meta_data($this->get_field_label((string) $key, $product_id), $value);
        }
        if (!empty($values['ezlens_price_extra'])) {
            $item->add_meta_data(
                'هزینه اضافی',
                class_exists('EzLens_Product_Options_Helpers')
                    ? EzLens_Product_Options_Helpers::format_price_fa($values['ezlens_price_extra'])
                    : wp_strip_all_tags(wc_price($values['ezlens_price_extra']))
            );
        }
    }

    /**
     * @param string $key tid:field or plain field
     * @param int    $product_id
     * @return string
     */
    private function get_field_label($key, $product_id) {
        $tid = 0;
        $fk  = $key;
        if (strpos($key, ':') !== false) {
            list($tid_s, $fk) = explode(':', $key, 2);
            $tid = absint($tid_s);
        }
        if ($tid > 0) {
            $template = $this->manager->get($tid);
        } else {
            $template = $this->manager->get_template_for_product($product_id);
        }
        if (!$template) {
            return $fk;
        }
        foreach (($template['fields'] ?? array()) as $field_key => $field) {
            if (!is_array($field)) {
                continue;
            }
            $name = isset($field['name']) ? $field['name'] : $field_key;
            if ((string) $name === (string) $fk || (string) $field_key === (string) $fk) {
                $label = $field['label'] ?? $fk;
                $prefix = ($template['title'] ?? '') !== '' ? $template['title'] . ' — ' : '';
                return $prefix . $label;
            }
        }
        return $fk;
    }
}

EzLens_Product_Options_FieldRenderer::get_instance();
