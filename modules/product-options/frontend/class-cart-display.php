<?php
/**
 * Professional cart / mini-cart options display.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Cart_Display
 */
class EzLens_PO_Cart_Display {

    /** @var self|null */
    private static $instance = null;

    /** @var int */
    private static $uid = 0;

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
        add_filter('woocommerce_get_item_data', array($this, 'filter_item_data'), 20, 2);
        add_filter('woocommerce_cart_item_name', array($this, 'append_ui'), 20, 3);
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
    }

    public function enqueue() {
        if (!function_exists('is_cart') || (!is_cart() && !is_checkout())) {
            return;
        }
        $css = EZLAUTH_MODULES_DIR . 'product-options/frontend/assets/cart-display.css';
        $js  = EZLAUTH_MODULES_DIR . 'product-options/frontend/assets/cart-display.js';
        if (is_readable($css)) {
            wp_enqueue_style(
                'ezlens-po-cart',
                EZLAUTH_MODULES_URL . 'product-options/frontend/assets/cart-display.css',
                array(),
                (string) filemtime($css)
            );
        }
        if (is_readable($js)) {
            wp_enqueue_script(
                'ezlens-po-cart',
                EZLAUTH_MODULES_URL . 'product-options/frontend/assets/cart-display.js',
                array(),
                (string) filemtime($js),
                true
            );
        }
    }

    /**
     * Strip flat option rows from default WC item data (we render our own UI).
     * Keep extra price if setting says so and mode needs it inline — still shown in our UI.
     *
     * @param array $item_data
     * @param array $cart_item
     * @return array
     */
    public function filter_item_data($item_data, $cart_item) {
        if (empty($cart_item['ezlens_options']) || !is_array($cart_item['ezlens_options'])) {
            return $item_data;
        }
        // Remove any name/value pairs that came from our previous flat renderer.
        // Other plugins' data stays.
        // We can't perfectly detect; FieldRenderer runs at 10 and adds pairs — we re-filter.
        $filtered = array();
        foreach ($item_data as $row) {
            if (!is_array($row)) {
                continue;
            }
            // Keep non-option rows; drop rows we marked.
            if (!empty($row['ezlens_po_skip'])) {
                continue;
            }
            $filtered[] = $row;
        }
        return $filtered;
    }

    /**
     * @param string $name
     * @param array  $cart_item
     * @param string $cart_item_key
     * @return string
     */
    public function append_ui($name, $cart_item, $cart_item_key) {
        if (empty($cart_item['ezlens_options']) || !is_array($cart_item['ezlens_options'])) {
            return $name;
        }
        // Avoid double-render in some themes that call filter twice.
        if (!empty($cart_item['ezlens_po_ui_done'])) {
            return $name;
        }

        $html = $this->build_ui($cart_item, $cart_item_key);
        if ($html === '') {
            return $name;
        }
        return $name . $html;
    }

    /**
     * Group options by template id from keys "tid:field".
     *
     * @param array $options
     * @param int   $product_id
     * @return array[] [ [title=>, rows=>[label=>value]], ... ]
     */
    private function group_options(array $options, $product_id) {
        $manager = class_exists('EzLens_Product_Options_Template_Manager')
            ? EzLens_Product_Options_Template_Manager::get_instance()
            : null;

        $groups = array();
        $flat   = array();

        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $value = implode('، ', array_map('strval', $value));
            }
            $value = (string) $value;
            if ($value === '') {
                continue;
            }

            $tid = 0;
            $fk  = (string) $key;
            if (strpos($fk, ':') !== false) {
                list($tid_s, $fk) = explode(':', $fk, 2);
                $tid = absint($tid_s);
            }

            $label = $fk;
            $title = '';
            if ($manager) {
                if ($tid > 0) {
                    $tpl = $manager->get($tid);
                    if ($tpl) {
                        $title = $tpl['title'] ?? '';
                        foreach (($tpl['fields'] ?? array()) as $field_key => $field) {
                            if (!is_array($field)) {
                                continue;
                            }
                            $name = isset($field['name']) ? $field['name'] : $field_key;
                            if ((string) $name === (string) $fk || (string) $field_key === (string) $fk) {
                                $label = $field['label'] ?? $fk;
                                break;
                            }
                        }
                    }
                } else {
                    $tpl = $manager->get_template_for_product($product_id);
                    if ($tpl) {
                        $title = $tpl['title'] ?? '';
                        foreach (($tpl['fields'] ?? array()) as $field_key => $field) {
                            if (!is_array($field)) {
                                continue;
                            }
                            $name = isset($field['name']) ? $field['name'] : $field_key;
                            if ((string) $name === (string) $fk || (string) $field_key === (string) $fk) {
                                $label = $field['label'] ?? $fk;
                                break;
                            }
                        }
                    }
                }
            }

            $gkey = $tid > 0 ? (string) $tid : '_default';
            if (!isset($groups[ $gkey ])) {
                $groups[ $gkey ] = array(
                    'title' => $title !== '' ? $title : 'ویژگی‌های محصول',
                    'rows'  => array(),
                );
            }
            $groups[ $gkey ]['rows'][] = array(
                'label' => $label,
                'value' => $value,
            );
        }

        return array_values($groups);
    }

    /**
     * @param array  $cart_item
     * @param string $cart_item_key
     * @return string
     */
    private function build_ui(array $cart_item, $cart_item_key) {
        $product_id = absint($cart_item['ezlens_product_id'] ?? $cart_item['product_id'] ?? 0);
        $groups     = $this->group_options($cart_item['ezlens_options'], $product_id);
        if (empty($groups)) {
            return '';
        }

        $mode = 'button';
        if (class_exists('EzLens_PO_Settings')) {
            $mode = EzLens_PO_Settings::get('cart_display_mode', 'button');
        }
        $show_extra = !class_exists('EzLens_PO_Settings') || EzLens_PO_Settings::get('cart_show_extra_price', '1') === '1';
        $fixed_label = class_exists('EzLens_PO_Settings') ? (string) EzLens_PO_Settings::get('cart_button_label', '') : '';

        $extra_html = '';
        if ($show_extra && !empty($cart_item['ezlens_price_extra'])) {
            $extra = (float) $cart_item['ezlens_price_extra'];
            if ($extra > 0) {
                $price_txt = class_exists('EzLens_Product_Options_Helpers')
                    ? EzLens_Product_Options_Helpers::format_price_fa($extra)
                    : wp_strip_all_tags(wc_price($extra));
                $extra_html = '<div class="ezlens-cart-extra"><span>هزینه اضافی</span><strong>' . esc_html($price_txt) . '</strong></div>';
            }
        }

        self::$uid++;
        $uid = 'ezcart-' . self::$uid . '-' . substr(md5($cart_item_key), 0, 6);

        // Button label: fixed or first group title or "ویژگی‌ها"
        $btn_label = $fixed_label !== '' ? $fixed_label : ($groups[0]['title'] ?? 'ویژگی‌ها');
        if (count($groups) > 1 && $fixed_label === '') {
            $btn_label = 'ویژگی‌ها (' . count($groups) . ')';
        }

        ob_start();
        echo '<div class="ezlens-cart-opts ezlens-cart-mode-' . esc_attr($mode) . '" data-uid="' . esc_attr($uid) . '">';

        if ($mode === 'inline') {
            foreach ($groups as $g) {
                echo '<div class="ezlens-cart-group">';
                echo '<div class="ezlens-cart-group-title">' . esc_html($g['title']) . '</div>';
                echo $this->rows_html($g['rows']); // phpcs:ignore
                echo '</div>';
            }
            echo $extra_html; // phpcs:ignore
        } elseif ($mode === 'accordion') {
            foreach ($groups as $i => $g) {
                $id = $uid . '-acc-' . $i;
                echo '<div class="ezlens-cart-acc">';
                echo '<button type="button" class="ezlens-cart-acc-toggle" aria-expanded="false" data-target="' . esc_attr($id) . '">';
                echo '<span>' . esc_html($g['title']) . '</span>';
                echo '<span class="ezlens-cart-acc-chevron" aria-hidden="true"></span>';
                echo '</button>';
                echo '<div class="ezlens-cart-acc-body" id="' . esc_attr($id) . '" hidden>';
                echo $this->rows_html($g['rows']); // phpcs:ignore
                echo '</div></div>';
            }
            echo $extra_html; // phpcs:ignore
        } else {
            // button → popover/panel
            echo '<button type="button" class="ezlens-cart-btn" data-panel="' . esc_attr($uid) . '-panel">';
            echo '<span class="ezlens-cart-btn-icon" aria-hidden="true"></span>';
            echo '<span>' . esc_html($btn_label) . '</span>';
            echo '</button>';
            echo '<div class="ezlens-cart-panel" id="' . esc_attr($uid) . '-panel" hidden>';
            echo '<div class="ezlens-cart-panel-head"><strong>' . esc_html($btn_label) . '</strong>';
            echo '<button type="button" class="ezlens-cart-panel-close" aria-label="بستن">×</button></div>';
            echo '<div class="ezlens-cart-panel-body">';
            foreach ($groups as $g) {
                if (count($groups) > 1) {
                    echo '<div class="ezlens-cart-group-title">' . esc_html($g['title']) . '</div>';
                }
                echo $this->rows_html($g['rows']); // phpcs:ignore
            }
            echo $extra_html; // phpcs:ignore
            echo '</div></div>';
        }

        echo '</div>';
        return (string) ob_get_clean();
    }

    /**
     * @param array $rows
     * @return string
     */
    private function rows_html(array $rows) {
        $html  = '<div class="ezlens-cart-table-wrap">';
        $html .= '<table class="ezlens-cart-table"><tbody>';
        foreach ($rows as $row) {
            $label = $row['label'];
            $value = $row['value'];
            $html .= '<tr>';
            $html .= '<th scope="row">' . esc_html($label) . '</th>';
            $html .= '<td>' . $this->format_value_html($value) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        return $html;
    }

    /**
     * Value cell: image thumb if URL looks like image upload.
     *
     * @param string $value
     * @return string HTML
     */
    private function format_value_html($value) {
        $value = (string) $value;
        if ($value === '') {
            return '<span class="ezlens-cart-empty">—</span>';
        }

        // Image / file URL
        if (preg_match('#^https?://#i', $value) || strpos($value, '/wp-content/uploads/') !== false) {
            $url  = esc_url($value);
            $path = wp_parse_url($value, PHP_URL_PATH);
            $ext  = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
            $img_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            if (in_array($ext, $img_exts, true)) {
                return '<a class="ezlens-cart-thumb-link" href="' . $url . '" target="_blank" rel="noopener">'
                    . '<img class="ezlens-cart-thumb" src="' . $url . '" alt="" loading="lazy">'
                    . '<span class="ezlens-cart-thumb-name">' . esc_html(basename((string) $path)) . '</span>'
                    . '</a>';
            }
            // Non-image file
            $name = $path ? basename($path) : $value;
            return '<a class="ezlens-cart-file" href="' . $url . '" target="_blank" rel="noopener">'
                . '<span class="ezlens-cart-file-icon">' . esc_html(strtoupper($ext ?: 'FILE')) . '</span> '
                . esc_html($name) . '</a>';
        }

        return '<span class="ezlens-cart-val">' . esc_html($value) . '</span>';
    }
}

EzLens_PO_Cart_Display::get_instance();
