<?php
if (!defined('ABSPATH')) exit;

class EzLens_Product_Options_FieldRenderer {
    private static $instance = null;
    private $manager;
    private $current_product_id = 0;
    private $template_data = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->manager = EzLens_Product_Options_Template_Manager::get_instance();
        add_action('woocommerce_before_add_to_cart_button', [$this, 'render_fields'], 15);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('woocommerce_add_cart_item_data', [$this, 'add_cart_item_data'], 10, 3);
        add_filter('woocommerce_get_item_data', [$this, 'display_cart_item_data'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_order_item_meta'], 10, 4);
        add_action('woocommerce_order_item_meta_end', [$this, 'display_order_item_meta'], 10, 4);
    }

    /**
     * بارگذاری استایل و اسکریپت‌های فرانت‌اند
     */
    public function enqueue_assets() {
        if (!is_product()) return;
        
        global $post;
        $template = $this->manager->get_template_for_product($post->ID);
        if (!$template) return;

        // استایل
        $css_path = EZLAUTH_PLUGIN_DIR . 'frontend/assets/css/product-options-frontend.css';
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'ezlens-product-options',
                EZLAUTH_PLUGIN_URL . 'frontend/assets/css/product-options-frontend.css',
                [],
                filemtime($css_path)
            );
        }

        // اسکریپت اصلی
        $js_path = EZLAUTH_PLUGIN_DIR . 'frontend/assets/js/product-options.js';
        if (file_exists($js_path)) {
            wp_enqueue_script(
                'ezlens-product-options',
                EZLAUTH_PLUGIN_URL . 'frontend/assets/js/product-options.js',
                ['jquery'],
                filemtime($js_path),
                true
            );
            wp_localize_script('ezlens-product-options', 'ezlens_po', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ezlens_po_nonce'),
                'product_id' => $post->ID,
                'price' => wc_get_product($post->ID)->get_price(),
                'currency' => get_woocommerce_currency_symbol()
            ]);
        }
    }

    /**
     * رندر فیلدها در صفحه محصول
     */
    public function render_fields() {
        global $post;
        $this->current_product_id = $post->ID;
        
        $template = $this->manager->get_template_for_product($post->ID);
        if (!$template) return;
        
        $this->template_data = $template;
        $fields = $template['fields'] ?? [];
        $fields = array_filter($fields, function($key) {
            return strpos($key, '_code_') !== 0;
        }, ARRAY_FILTER_USE_KEY);

        if (empty($fields)) return;

        echo '<div class="ezlens-product-options-wrapper" data-template-id="' . esc_attr($template['id']) . '">';
        echo '<div class="ezlens-options-title">' . esc_html($template['title']) . '</div>';
        echo '<div class="ezlens-options-fields">';

        foreach ($fields as $key => $field) {
            $this->render_field($key, $field);
        }

        echo '</div>';
        echo '<div class="ezlens-total-price" style="display:none;">';
        echo '<span class="label">قیمت نهایی:</span>';
        echo '<span class="price" id="ezlens-total-price">0</span>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * رندر یک فیلد
     */
    private function render_field($key, $field) {
        $type = $field['type'] ?? 'text';
        $label = $field['label'] ?? 'فیلد';
        $name = $field['name'] ?? 'field_' . $key;
        $placeholder = $field['placeholder'] ?? '';
        $required = $field['required'] ?? false;
        $price = $field['price'] ?? 0;
        $width = $field['width'] ?? 'full';
        $settings = $field['settings'] ?? [];
        $options = $field['options'] ?? [];

        $wrapper_class = 'ezlens-field-wrapper ezlens-field-' . $type . ' ezlens-width-' . $width;
        $required_attr = $required ? ' required' : '';
        $required_star = $required ? ' <span class="required">*</span>' : '';
        $data_price = $price > 0 ? ' data-price="' . esc_attr($price) . '"' : '';

        echo '<div class="' . esc_attr($wrapper_class) . '">';
        echo '<label for="ezlens_field_' . esc_attr($key) . '">' . esc_html($label) . $required_star . '</label>';

        switch ($type) {
            case 'text':
            case 'email':
            case 'phone':
                $input_type = $type === 'email' ? 'email' : ($type === 'phone' ? 'tel' : 'text');
                echo '<input type="' . esc_attr($input_type) . '" id="ezlens_field_' . esc_attr($key) . '" ';
                echo 'name="ezlens_options[' . esc_attr($key) . ']" ';
                echo 'class="ezlens-field-input" ';
                echo 'placeholder="' . esc_attr($placeholder) . '" ';
                echo 'data-field-key="' . esc_attr($key) . '"';
                echo $data_price;
                echo $required_attr;
                if (isset($settings['maxlength'])) echo ' maxlength="' . esc_attr($settings['maxlength']) . '"';
                echo '>';
                break;

            case 'textarea':
                echo '<textarea id="ezlens_field_' . esc_attr($key) . '" ';
                echo 'name="ezlens_options[' . esc_attr($key) . ']" ';
                echo 'class="ezlens-field-input" ';
                echo 'placeholder="' . esc_attr($placeholder) . '" ';
                echo 'data-field-key="' . esc_attr($key) . '"';
                echo $data_price;
                echo $required_attr;
                if (isset($settings['rows'])) echo ' rows="' . esc_attr($settings['rows']) . '"';
                echo '></textarea>';
                break;

            case 'number':
                echo '<input type="number" id="ezlens_field_' . esc_attr($key) . '" ';
                echo 'name="ezlens_options[' . esc_attr($key) . ']" ';
                echo 'class="ezlens-field-input" ';
                echo 'placeholder="' . esc_attr($placeholder) . '" ';
                echo 'data-field-key="' . esc_attr($key) . '"';
                echo $data_price;
                echo $required_attr;
                if (isset($settings['min'])) echo ' min="' . esc_attr($settings['min']) . '"';
                if (isset($settings['max'])) echo ' max="' . esc_attr($settings['max']) . '"';
                if (isset($settings['step'])) echo ' step="' . esc_attr($settings['step']) . '"';
                echo '>';
                break;

            case 'select':
                echo '<select id="ezlens_field_' . esc_attr($key) . '" ';
                echo 'name="ezlens_options[' . esc_attr($key) . ']" ';
                echo 'class="ezlens-field-select" ';
                echo 'data-field-key="' . esc_attr($key) . '"';
                echo $data_price;
                echo $required_attr;
                echo '>';
                echo '<option value="">انتخاب کنید...</option>';
                foreach ($options as $opt) {
                    $opt_price = $opt['price'] ?? 0;
                    $opt_label = $opt['label'] ?? $opt['value'] ?? '';
                    $opt_value = $opt['value'] ?? $opt['label'] ?? '';
                    if (empty($opt_label)) continue;
                    echo '<option value="' . esc_attr($opt_value) . '" data-price="' . esc_attr($opt_price) . '">';
                    echo esc_html($opt_label);
                    if ($opt_price > 0) echo ' (+' . wc_price($opt_price) . ')';
                    echo '</option>';
                }
                echo '</select>';
                break;

            case 'radio':
                echo '<div class="ezlens-radio-group">';
                foreach ($options as $opt) {
                    $opt_price = $opt['price'] ?? 0;
                    $opt_label = $opt['label'] ?? $opt['value'] ?? '';
                    $opt_value = $opt['value'] ?? $opt['label'] ?? '';
                    if (empty($opt_label)) continue;
                    echo '<label class="ezlens-radio-label">';
                    echo '<input type="radio" name="ezlens_options[' . esc_attr($key) . ']" ';
                    echo 'value="' . esc_attr($opt_value) . '" ';
                    echo 'data-price="' . esc_attr($opt_price) . '" ';
                    echo 'data-field-key="' . esc_attr($key) . '"';
                    echo $required_attr;
                    echo '>';
                    echo esc_html($opt_label);
                    if ($opt_price > 0) echo ' (+' . wc_price($opt_price) . ')';
                    echo '</label>';
                }
                echo '</div>';
                break;

            case 'checkbox':
                echo '<div class="ezlens-checkbox-group">';
                foreach ($options as $opt) {
                    $opt_price = $opt['price'] ?? 0;
                    $opt_label = $opt['label'] ?? $opt['value'] ?? '';
                    $opt_value = $opt['value'] ?? $opt['label'] ?? '';
                    if (empty($opt_label)) continue;
                    echo '<label class="ezlens-checkbox-label">';
                    echo '<input type="checkbox" name="ezlens_options[' . esc_attr($key) . '][]" ';
                    echo 'value="' . esc_attr($opt_value) . '" ';
                    echo 'data-price="' . esc_attr($opt_price) . '" ';
                    echo 'data-field-key="' . esc_attr($key) . '"';
                    echo '>';
                    echo esc_html($opt_label);
                    if ($opt_price > 0) echo ' (+' . wc_price($opt_price) . ')';
                    echo '</label>';
                }
                echo '</div>';
                break;

            case 'image_select':
                echo '<div class="ezlens-image-select-group">';
                foreach ($options as $opt) {
                    $opt_price = $opt['price'] ?? 0;
                    $opt_label = $opt['label'] ?? $opt['value'] ?? '';
                    $opt_value = $opt['value'] ?? $opt['label'] ?? '';
                    $opt_image = $opt['image'] ?? '';
                    if (empty($opt_label)) continue;
                    echo '<label class="ezlens-image-select-label' . ($opt_image ? ' has-image' : '') . '">';
                    if ($opt_image) {
                        echo '<img src="' . esc_url($opt_image) . '" alt="' . esc_attr($opt_label) . '" class="ezlens-option-image">';
                    }
                    echo '<input type="radio" name="ezlens_options[' . esc_attr($key) . ']" ';
                    echo 'value="' . esc_attr($opt_value) . '" ';
                    echo 'data-price="' . esc_attr($opt_price) . '" ';
                    echo 'data-field-key="' . esc_attr($key) . '"';
                    echo $required_attr;
                    echo '>';
                    echo '<span class="ezlens-option-label">' . esc_html($opt_label) . '</span>';
                    if ($opt_price > 0) echo '<span class="ezlens-option-price">+' . wc_price($opt_price) . '</span>';
                    echo '</label>';
                }
                echo '</div>';
                break;

            case 'color':
                echo '<input type="color" id="ezlens_field_' . esc_attr($key) . '" ';
                echo 'name="ezlens_options[' . esc_attr($key) . ']" ';
                echo 'class="ezlens-field-color" ';
                echo 'data-field-key="' . esc_attr($key) . '"';
                echo $data_price;
                echo $required_attr;
                echo '>';
                break;

            case 'date':
                echo '<input type="date" id="ezlens_field_' . esc_attr($key) . '" ';
                echo 'name="ezlens_options[' . esc_attr($key) . ']" ';
                echo 'class="ezlens-field-date" ';
                echo 'data-field-key="' . esc_attr($key) . '"';
                echo $data_price;
                echo $required_attr;
                echo '>';
                break;

            case 'upload':
                echo '<div class="ezlens-upload-wrapper">';
                echo '<input type="file" id="ezlens_field_' . esc_attr($key) . '" ';
                echo 'name="ezlens_options[' . esc_attr($key) . ']" ';
                echo 'class="ezlens-field-upload" ';
                echo 'data-field-key="' . esc_attr($key) . '"';
                echo $data_price;
                echo $required_attr;
                if (isset($settings['extensions'])) {
                    echo ' accept=".' . implode(',.', $settings['extensions']) . '"';
                }
                echo '>';
                echo '<div class="ezlens-upload-progress" style="display:none;"><div class="progress-bar"></div></div>';
                echo '<div class="ezlens-upload-preview"></div>';
                echo '</div>';
                break;

            case 'heading':
                $level = $settings['level'] ?? 'h3';
                echo '<' . esc_attr($level) . ' class="ezlens-field-heading">' . esc_html($label) . '</' . esc_attr($level) . '>';
                break;

            case 'divider':
                $thickness = $settings['thickness'] ?? 1;
                $color = $settings['color'] ?? '#e2e8f0';
                echo '<hr class="ezlens-field-divider" style="border-top:' . esc_attr($thickness) . 'px solid ' . esc_attr($color) . ';">';
                break;

            case 'spacer':
                $height = $settings['height'] ?? 20;
                echo '<div class="ezlens-field-spacer" style="height:' . esc_attr($height) . 'px;"></div>';
                break;

            case 'html':
                echo '<div class="ezlens-field-html">' . wp_kses_post($settings['code'] ?? '') . '</div>';
                break;

            case 'group':
                $columns = $settings['columns'] ?? 2;
                $gap = $settings['gap'] ?? 'medium';
                $gap_map = ['small' => '10px', 'medium' => '20px', 'large' => '30px'];
                $gap_size = $gap_map[$gap] ?? '20px';
                echo '<div class="ezlens-field-group" style="display:grid;grid-template-columns:repeat(' . esc_attr($columns) . ',1fr);gap:' . esc_attr($gap_size) . ';">';
                if (!empty($field['children'])) {
                    foreach ($field['children'] as $child_key => $child) {
                        $this->render_field($key . '_' . $child_key, $child);
                    }
                }
                echo '</div>';
                break;
        }

        echo '</div>';
    }

    /**
     * ذخیره اطلاعات در سبد خرید
     */
    public function add_cart_item_data($cart_item_data, $product_id, $variation_id) {
        if (isset($_POST['ezlens_options']) && is_array($_POST['ezlens_options'])) {
            $options = $_POST['ezlens_options'];
            $cart_item_data['ezlens_options'] = $this->sanitize_options($options, $product_id);
            $cart_item_data['ezlens_price_extra'] = $this->calculate_extra_price($options, $product_id);
        }
        return $cart_item_data;
    }

    /**
     * نمایش اطلاعات در سبد خرید
     */
    public function display_cart_item_data($item_data, $cart_item) {
        if (isset($cart_item['ezlens_options']) && is_array($cart_item['ezlens_options'])) {
            foreach ($cart_item['ezlens_options'] as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                if (!empty($value)) {
                    $label = $this->get_field_label($key, $cart_item['product_id']);
                    $item_data[] = [
                        'name' => $label ?: $key,
                        'value' => $value
                    ];
                }
            }
        }
        if (isset($cart_item['ezlens_price_extra']) && $cart_item['ezlens_price_extra'] > 0) {
            $item_data[] = [
                'name' => 'هزینه اضافی',
                'value' => wc_price($cart_item['ezlens_price_extra'])
            ];
        }
        return $item_data;
    }

    /**
     * ذخیره در سفارش
     */
    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        if (isset($values['ezlens_options']) && is_array($values['ezlens_options'])) {
            foreach ($values['ezlens_options'] as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                if (!empty($value)) {
                    $label = $this->get_field_label($key, $values['product_id']);
                    $item->add_meta_data($label ?: $key, $value);
                }
            }
        }
        if (isset($values['ezlens_price_extra']) && $values['ezlens_price_extra'] > 0) {
            $item->add_meta_data('هزینه اضافی', wc_price($values['ezlens_price_extra']));
        }
    }

    /**
     * نمایش در سفارش
     */
    public function display_order_item_meta($item_id, $item, $order, $plain_text) {
        // ووکامرس خودش متاها را نمایش می‌دهد
    }

    private function sanitize_options($options, $product_id) {
        $template = $this->manager->get_template_for_product($product_id);
        if (!$template) return [];

        $sanitized = [];
        $fields = $template['fields'] ?? [];
        foreach ($fields as $key => $field) {
            if (strpos($key, '_code_') === 0) continue;
            $type = $field['type'] ?? 'text';
            $value = $options[$key] ?? '';
            if (empty($value)) continue;

            switch ($type) {
                case 'email':
                    $value = sanitize_email($value);
                    break;
                case 'text':
                case 'textarea':
                case 'phone':
                    $value = sanitize_text_field($value);
                    break;
                case 'number':
                    $value = floatval($value);
                    break;
                case 'select':
                case 'radio':
                    $value = sanitize_text_field($value);
                    break;
                case 'checkbox':
                    if (is_array($value)) {
                        $value = array_map('sanitize_text_field', $value);
                    }
                    break;
                default:
                    $value = sanitize_text_field($value);
            }
            if (!empty($value)) {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    private function calculate_extra_price($options, $product_id) {
        $total = 0;
        $template = $this->manager->get_template_for_product($product_id);
        if (!$template) return 0;

        $fields = $template['fields'] ?? [];
        foreach ($fields as $key => $field) {
            if (strpos($key, '_code_') === 0) continue;
            $type = $field['type'] ?? 'text';
            $value = $options[$key] ?? null;
            if (empty($value)) continue;

            // قیمت خود فیلد
            if (isset($field['price']) && $field['price'] > 0) {
                $total += floatval($field['price']);
            }

            // قیمت گزینه‌ها (برای select/radio/checkbox/image_select)
            if (in_array($type, ['select', 'radio', 'checkbox', 'image_select'])) {
                $options_list = $field['options'] ?? [];
                if ($type === 'checkbox' && is_array($value)) {
                    foreach ($value as $v) {
                        foreach ($options_list as $opt) {
                            if (($opt['value'] ?? '') == $v && isset($opt['price'])) {
                                $total += floatval($opt['price']);
                            }
                        }
                    }
                } else {
                    foreach ($options_list as $opt) {
                        if (($opt['value'] ?? '') == $value && isset($opt['price'])) {
                            $total += floatval($opt['price']);
                        }
                    }
                }
            }
        }
        return $total;
    }

    private function get_field_label($key, $product_id) {
        $template = $this->manager->get_template_for_product($product_id);
        if (!$template) return $key;
        $fields = $template['fields'] ?? [];
        foreach ($fields as $field_key => $field) {
            if ($field_key === $key) {
                return $field['label'] ?? $key;
            }
        }
        return $key;
    }
}

EzLens_Product_Options_FieldRenderer::get_instance();