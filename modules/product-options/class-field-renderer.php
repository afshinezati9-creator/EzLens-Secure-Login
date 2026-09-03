<?php
if (!defined('ABSPATH')) exit;

class EzLens_Product_Options_FieldRenderer {
    private static $instance = null;
    private $manager;
    private $pricing_engine;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->manager = EzLens_Product_Options_Template_Manager::get_instance();
        $this->pricing_engine = class_exists('EzLens_Product_Options_Pricing_Engine')
            ? EzLens_Product_Options_Pricing_Engine::get_instance()
            : null;

        add_action('woocommerce_before_add_to_cart_button', [$this, 'render_fields'], 15);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('woocommerce_add_cart_item_data', [$this, 'add_cart_item_data'], 10, 3);
        add_filter('woocommerce_get_item_data', [$this, 'display_cart_item_data'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_order_item_meta'], 10, 4);
    }

    public function enqueue_assets() {
        if (!is_product()) return;
        global $post;
        if (!$post) return;

        $template = $this->manager->get_template_for_product($post->ID);
        if (!$template) return;

        $css_path = EZLAUTH_PLUGIN_DIR . 'frontend/assets/css/product-options-frontend.css';
        if (file_exists($css_path)) {
            wp_enqueue_style('ezlens-product-options', EZLAUTH_PLUGIN_URL . 'frontend/assets/css/product-options-frontend.css', [], filemtime($css_path));
        }

        $js_path = EZLAUTH_PLUGIN_DIR . 'frontend/assets/js/product-options.js';
        if (file_exists($js_path)) {
            wp_enqueue_script('ezlens-product-options', EZLAUTH_PLUGIN_URL . 'frontend/assets/js/product-options.js', ['jquery'], filemtime($js_path), true);
            $product = wc_get_product($post->ID);
            wp_localize_script('ezlens-product-options', 'ezlens_po', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ezlens_po_nonce'),
                'product_id' => $post->ID,
                'price' => $product ? $product->get_price() : 0,
                'currency' => get_woocommerce_currency_symbol(),
            ]);
        }
    }

    public function render_fields() {
        global $post;
        if (!$post) return;

        $product_id = $post->ID;
        $product = wc_get_product($product_id);
        if ($product && $product->is_type('variation')) {
            $product_id = $product->get_parent_id();
        }

        $template = $this->manager->get_template_for_product($product_id);
        if (!$template) return;

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
        echo '<div class="ezlens-total-price">';
        echo '<span class="label">قیمت نهایی:</span>';
        echo '<span class="price" id="ezlens-total-price">' . ($product ? wc_price($product->get_price()) : '') . '</span>';
        echo '</div></div>';
    }

    private function render_field($key, $field) {
        $type = $field['type'] ?? 'text';
        $label = $field['label'] ?? 'فیلد';
        $placeholder = $field['placeholder'] ?? '';
        $required = !empty($field['required']);
        $price = isset($field['price']) ? max(0, (float) $field['price']) : 0;
        $width = $field['width'] ?? 'full';
        $settings = $field['settings'] ?? [];
        $options = $field['options'] ?? [];
        $conditions = $field['conditions'] ?? $field['conditional_logic'] ?? [];

        $wrapper_class = 'ezlens-field-wrapper ezlens-field-' . $type . ' ezlens-width-' . sanitize_html_class($width);
        $condition_attrs = $this->build_condition_attributes($conditions);
        $required_attr = $required ? ' required' : '';
        $required_star = $required ? ' <span class="required">*</span>' : '';
        $data_price = $price > 0 ? ' data-price="' . esc_attr($price) . '"' : '';

        echo '<div class="' . esc_attr($wrapper_class) . '"' . $condition_attrs . '>';
        echo '<label for="ezlens_field_' . esc_attr($key) . '">' . esc_html($label) . $required_star . '</label>';

        switch ($type) {
            case 'text': case 'email': case 'phone':
                $input_type = $type === 'email' ? 'email' : ($type === 'phone' ? 'tel' : 'text');
                echo '<input type="' . esc_attr($input_type) . '" id="ezlens_field_' . esc_attr($key) . '" name="ezlens_options[' . esc_attr($key) . ']" class="ezlens-field-input" placeholder="' . esc_attr($placeholder) . '" data-field-key="' . esc_attr($key) . '"' . $data_price . $required_attr . '>';
                break;
            case 'textarea':
                echo '<textarea id="ezlens_field_' . esc_attr($key) . '" name="ezlens_options[' . esc_attr($key) . ']" class="ezlens-field-input" placeholder="' . esc_attr($placeholder) . '" data-field-key="' . esc_attr($key) . '"' . $data_price . $required_attr . '></textarea>';
                break;
            case 'number':
                echo '<input type="number" id="ezlens_field_' . esc_attr($key) . '" name="ezlens_options[' . esc_attr($key) . ']" class="ezlens-field-input" placeholder="' . esc_attr($placeholder) . '" data-field-key="' . esc_attr($key) . '"' . $data_price . $required_attr;
                if (isset($settings['min'])) echo ' min="' . esc_attr($settings['min']) . '"';
                if (isset($settings['max'])) echo ' max="' . esc_attr($settings['max']) . '"';
                if (isset($settings['step'])) echo ' step="' . esc_attr($settings['step']) . '"';
                echo '>';
                break;
            case 'select':
                echo '<select id="ezlens_field_' . esc_attr($key) . '" name="ezlens_options[' . esc_attr($key) . ']" class="ezlens-field-select" data-field-key="' . esc_attr($key) . '"' . $data_price . $required_attr . '><option value="">انتخاب کنید...</option>';
                foreach ($options as $opt) {
                    if (!is_array($opt)) continue;
                    $opt_price = isset($opt['price']) ? max(0, (float) $opt['price']) : 0;
                    $opt_label = $opt['label'] ?? ($opt['value'] ?? '');
                    $opt_value = $opt['value'] ?? ($opt['label'] ?? '');
                    if ($opt_label === '') continue;
                    echo '<option value="' . esc_attr($opt_value) . '" data-price="' . esc_attr($opt_price) . '">' . esc_html($opt_label) . ($opt_price > 0 ? ' (+' . wp_strip_all_tags(wc_price($opt_price)) . ')' : '') . '</option>';
                }
                echo '</select>';
                break;
            case 'radio': case 'image_select':
                echo '<div class="ezlens-radio-group">';
                foreach ($options as $opt) {
                    if (!is_array($opt)) continue;
                    $opt_price = isset($opt['price']) ? max(0, (float) $opt['price']) : 0;
                    $opt_label = $opt['label'] ?? ($opt['value'] ?? '');
                    $opt_value = $opt['value'] ?? ($opt['label'] ?? '');
                    if ($opt_label === '') continue;
                    echo '<label class="ezlens-radio-label">';
                    echo '<input type="radio" name="ezlens_options[' . esc_attr($key) . ']" value="' . esc_attr($opt_value) . '" data-price="' . esc_attr($opt_price) . '" data-field-key="' . esc_attr($key) . '"' . $required_attr . '>';
                    echo esc_html($opt_label);
                    if ($opt_price > 0) echo ' (+' . wp_strip_all_tags(wc_price($opt_price)) . ')';
                    echo '</label>';
                }
                echo '</div>';
                break;
            case 'checkbox':
                echo '<div class="ezlens-checkbox-group">';
                foreach ($options as $opt) {
                    if (!is_array($opt)) continue;
                    $opt_price = isset($opt['price']) ? max(0, (float) $opt['price']) : 0;
                    $opt_label = $opt['label'] ?? ($opt['value'] ?? '');
                    $opt_value = $opt['value'] ?? ($opt['label'] ?? '');
                    if ($opt_label === '') continue;
                    echo '<label class="ezlens-checkbox-label"><input type="checkbox" name="ezlens_options[' . esc_attr($key) . '][]" value="' . esc_attr($opt_value) . '" data-price="' . esc_attr($opt_price) . '" data-field-key="' . esc_attr($key) . '">' . esc_html($opt_label) . ($opt_price > 0 ? ' (+' . wp_strip_all_tags(wc_price($opt_price)) . ')' : '') . '</label>';
                }
                echo '</div>';
                break;
            case 'color': case 'date':
                $input_type = $type === 'color' ? 'color' : 'date';
                echo '<input type="' . $input_type . '" id="ezlens_field_' . esc_attr($key) . '" name="ezlens_options[' . esc_attr($key) . ']" class="ezlens-field-' . esc_attr($type) . '" data-field-key="' . esc_attr($key) . '"' . $data_price . $required_attr . '>';
                break;
            case 'upload':
                echo '<div class="ezlens-upload-wrapper"><input type="file" id="ezlens_field_' . esc_attr($key) . '" name="ezlens_options[' . esc_attr($key) . ']" class="ezlens-field-upload" data-field-key="' . esc_attr($key) . '"' . $data_price . $required_attr . '><div class="ezlens-upload-progress"><div class="progress-bar"></div></div><div class="ezlens-upload-preview"></div></div>';
                break;
            case 'heading':
                $level = in_array(($settings['level'] ?? 'h3'), ['h2','h3','h4','h5','h6'], true) ? $settings['level'] : 'h3';
                echo '<' . $level . ' class="ezlens-field-heading">' . esc_html($label) . '</' . $level . '>';
                break;
            case 'divider':
                $thickness = max(1, (int) ($settings['thickness'] ?? 1));
                $color = sanitize_hex_color($settings['color'] ?? '#e2e8f0') ?: '#e2e8f0';
                echo '<hr class="ezlens-field-divider" style="border-top:' . esc_attr($thickness) . 'px solid ' . esc_attr($color) . ';">';
                break;
            case 'spacer':
                echo '<div class="ezlens-field-spacer" style="height:' . esc_attr(max(0, (int) ($settings['height'] ?? 20))) . 'px;"></div>';
                break;
            case 'html':
                echo '<div class="ezlens-field-html">' . wp_kses_post($settings['code'] ?? '') . '</div>';
                break;
            case 'group':
                $columns = max(1, min(6, (int) ($settings['columns'] ?? 2)));
                echo '<div class="ezlens-field-group" style="display:grid;grid-template-columns:repeat(' . esc_attr($columns) . ',1fr);gap:20px;">';
                foreach (($field['children'] ?? []) as $child_key => $child) {
                    $this->render_field($key . '_' . $child_key, $child);
                }
                echo '</div>';
                break;
        }
        echo '</div>';
    }

    private function build_condition_attributes($conditions) {
        if (empty($conditions)) return '';
        if (isset($conditions['rules']) && is_array($conditions['rules'])) {
            $conditions = $conditions['rules'];
        }
        if (!is_array($conditions)) return '';

        $rules = [];
        foreach ($conditions as $rule) {
            if (!is_array($rule)) continue;
            $field = sanitize_key($rule['field'] ?? $rule['field_key'] ?? '');
            $operator = sanitize_key($rule['operator'] ?? 'equals');
            $value = $rule['value'] ?? '';
            if ($field === '') continue;
            if (is_array($value)) $value = array_values(array_map('sanitize_text_field', $value));
            else $value = sanitize_text_field($value);
            $rules[] = ['field' => $field, 'operator' => $operator, 'value' => $value];
        }
        if (empty($rules)) return '';
        $logic = sanitize_key($conditions['logic'] ?? 'all');
        $logic = in_array($logic, ['all','any'], true) ? $logic : 'all';
        return ' data-conditions="' . esc_attr(wp_json_encode(['logic' => $logic, 'rules' => $rules], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '"';
    }

    public function add_cart_item_data($cart_item_data, $product_id, $variation_id) {
        if (!isset($_POST['ezlens_options']) || !is_array($_POST['ezlens_options'])) return $cart_item_data;
        $options = wp_unslash($_POST['ezlens_options']);
        $product = wc_get_product($product_id);
        if ($product && $product->is_type('variation')) $product_id = $product->get_parent_id();

        $sanitized = $this->sanitize_options($options, $product_id);
        $cart_item_data['ezlens_options'] = $sanitized;
        $cart_item_data['ezlens_product_id'] = $product_id;
        if ($this->pricing_engine) {
            $cart_item_data['ezlens_price_extra'] = $this->pricing_engine->calculate_extra_price($sanitized, $product_id);
        }
        return $cart_item_data;
    }

    public function display_cart_item_data($item_data, $cart_item) {
        if (!empty($cart_item['ezlens_options']) && is_array($cart_item['ezlens_options'])) {
            $product_id = $cart_item['ezlens_product_id'] ?? $cart_item['product_id'];
            foreach ($cart_item['ezlens_options'] as $key => $value) {
                if (is_array($value)) $value = implode(', ', $value);
                if ($value !== '') $item_data[] = ['name' => $this->get_field_label($key, $product_id), 'value' => $value];
            }
        }
        if (!empty($cart_item['ezlens_price_extra'])) $item_data[] = ['name' => 'هزینه اضافی', 'value' => wc_price($cart_item['ezlens_price_extra'])];
        return $item_data;
    }

    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        if (!empty($values['ezlens_options']) && is_array($values['ezlens_options'])) {
            $product_id = $values['ezlens_product_id'] ?? $values['product_id'];
            foreach ($values['ezlens_options'] as $key => $value) {
                if (is_array($value)) $value = implode(', ', $value);
                if ($value !== '') $item->add_meta_data($this->get_field_label($key, $product_id), $value);
            }
        }
        if (!empty($values['ezlens_price_extra'])) $item->add_meta_data('هزینه اضافی', wc_price($values['ezlens_price_extra']));
    }

    private function sanitize_options($options, $product_id) {
        $template = $this->manager->get_template_for_product($product_id);
        if (!$template) return [];
        $sanitized = [];
        foreach (($template['fields'] ?? []) as $key => $field) {
            if (strpos((string) $key, '_code_') === 0 || !is_array($field)) continue;
            $type = sanitize_key($field['type'] ?? 'text');
            $value = $options[$key] ?? '';
            if ($value === '' || $value === null || (is_array($value) && empty($value))) continue;
            switch ($type) {
                case 'email': $value = sanitize_email($value); break;
                case 'number': $value = is_numeric($value) ? (float) $value : ''; break;
                case 'checkbox': $value = is_array($value) ? array_values(array_map('sanitize_text_field', $value)) : []; break;
                default: $value = is_array($value) ? array_values(array_map('sanitize_text_field', $value)) : sanitize_text_field($value); break;
            }
            if ($value !== '' && $value !== []) $sanitized[$key] = $value;
        }
        return $sanitized;
    }

    private function get_field_label($key, $product_id) {
        $template = $this->manager->get_template_for_product($product_id);
        if (!$template) return $key;
        foreach (($template['fields'] ?? []) as $field_key => $field) {
            if ($field_key === $key) return $field['label'] ?? $key;
        }
        return $key;
    }
}

EzLens_Product_Options_FieldRenderer::get_instance();
