<?php
if (!defined('ABSPATH')) exit;

/**
 * Server-side pricing engine for Product Options.
 * Conditional logic is evaluated here as well as in the browser, so hidden
 * fields can never contribute a price merely because a client posts their value.
 */
class EzLens_Product_Options_Pricing_Engine {
    private static $instance = null;
    private $manager;
    private const ALLOWED_CONDITION_OPERATORS = ['equals','not_equals','contains','not_contains','greater_than','less_than','greater_or_equal','less_or_equal','empty','not_empty'];

    public static function get_instance() {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->manager = EzLens_Product_Options_Template_Manager::get_instance();
        add_filter('woocommerce_add_cart_item_data', [$this,'capture_base_price'], 20, 3);
        add_action('woocommerce_before_calculate_totals', [$this,'apply_cart_prices'], 20);
    }

    public function capture_base_price($cart_item_data, $product_id, $variation_id) {
        $product = wc_get_product($variation_id ?: $product_id);
        if (!$product) return $cart_item_data;
        $options = isset($cart_item_data['ezlens_options']) && is_array($cart_item_data['ezlens_options']) ? $cart_item_data['ezlens_options'] : [];
        if (empty($options) && empty($cart_item_data['ezlens_product_id'])) return $cart_item_data;
        $cart_item_data['ezlens_price_base'] = $this->format_price($product->get_price());
        $template_product_id = !empty($cart_item_data['ezlens_product_id']) ? absint($cart_item_data['ezlens_product_id']) : absint($product_id);
        if ($template_product_id) $cart_item_data['ezlens_price_extra'] = $this->calculate_extra_price($options, $template_product_id);
        return $cart_item_data;
    }

    public function apply_cart_prices($cart) {
        if (is_admin() && !wp_doing_ajax()) return;
        if (!$cart || !is_a($cart,'WC_Cart')) return;
        foreach ($cart->get_cart() as $cart_item_key=>$cart_item) {
            if (empty($cart_item['ezlens_options']) || !is_array($cart_item['ezlens_options'])) continue;
            $product_id = !empty($cart_item['ezlens_product_id']) ? absint($cart_item['ezlens_product_id']) : absint($cart_item['product_id'] ?? 0);
            if (!$product_id || empty($cart_item['data']) || !is_object($cart_item['data'])) continue;
            $extra=$this->calculate_extra_price($cart_item['ezlens_options'],$product_id);
            $cart->cart_contents[$cart_item_key]['ezlens_price_extra']=$extra;
            $base=isset($cart_item['ezlens_price_base']) ? $cart_item['ezlens_price_base'] : $cart_item['data']->get_price();
            $cart_item['data']->set_price($this->format_price($this->format_price($base)+$extra));
        }
    }

    public function calculate_extra_price($options,$product_id) {
        if (!is_array($options) || !$product_id) return 0;
        $template=$this->manager->get_template_for_product($product_id);
        if (!$template || !is_array($template['fields'] ?? null)) return 0;
        return $this->calculate_fields_price($template['fields'],$options);
    }

    private function calculate_fields_price($fields,$options) {
        $total=0;
        foreach ($fields as $key=>$field) {
            if (!is_array($field) || strpos((string)$key,'_code_')===0) continue;
            if (!$this->conditions_match($field['conditions'] ?? $field['conditional_logic'] ?? [],$options)) continue;
            $value=$options[$key] ?? null;
            if ($this->is_empty_value($value)) continue;
            $type=sanitize_key($field['type'] ?? 'text');
            $total += isset($field['price']) ? $this->positive_price($field['price']) : 0;
            if (in_array($type,['select','radio','checkbox','image_select'],true)) $total += $this->calculate_option_price($field['options'] ?? [],$value,$type);
            if (!empty($field['children']) && is_array($field['children'])) $total += $this->calculate_fields_price($field['children'],$options);
        }
        return $this->format_price($total);
    }

    private function conditions_match($conditions,$options) {
        if (empty($conditions)) return true;
        if (!is_array($conditions)) return true;
        $rules=isset($conditions['rules']) && is_array($conditions['rules']) ? $conditions['rules'] : $conditions;
        if (empty($rules)) return true;
        $logic=isset($conditions['logic']) && $conditions['logic']==='any' ? 'any' : 'all';
        $results=[];
        foreach ($rules as $rule) {
            if (!is_array($rule)) continue;
            $field=sanitize_key($rule['field'] ?? $rule['field_key'] ?? '');
            $operator=sanitize_key($rule['operator'] ?? 'equals');
            if (!$field || !in_array($operator,self::ALLOWED_CONDITION_OPERATORS,true)) continue;
            $actual=$options[$field] ?? '';
            $expected=$rule['value'] ?? '';
            $results[]=$this->compare_condition_values($actual,$expected,$operator);
        }
        if (empty($results)) return true;
        return $logic==='any' ? in_array(true,$results,true) : !in_array(false,$results,true);
    }

    private function compare_condition_values($actual,$expected,$operator) {
        if (is_array($actual)) {
            $actual=array_map('strval',$actual);
            if ($operator==='contains' || $operator==='equals') return in_array((string)$expected,$actual,true);
            if ($operator==='not_contains') return !in_array((string)$expected,$actual,true);
            $actual=implode(',',$actual);
        }
        $actual=(string)$actual; $expected=is_array($expected)?implode(',',$expected):(string)$expected;
        switch ($operator) {
            case 'not_equals': return $actual!==$expected;
            case 'contains': return strpos($actual,$expected)!==false;
            case 'not_contains': return strpos($actual,$expected)===false;
            case 'greater_than': return is_numeric($actual)&&is_numeric($expected)&&(float)$actual>(float)$expected;
            case 'less_than': return is_numeric($actual)&&is_numeric($expected)&&(float)$actual<(float)$expected;
            case 'greater_or_equal': return is_numeric($actual)&&is_numeric($expected)&&(float)$actual>=(float)$expected;
            case 'less_or_equal': return is_numeric($actual)&&is_numeric($expected)&&(float)$actual<=(float)$expected;
            case 'empty': return $actual==='';
            case 'not_empty': return $actual!=='';
            case 'equals': default: return $actual===$expected;
        }
    }

    private function calculate_option_price($options,$value,$type) {
        if (!is_array($options)) return 0;
        $selected=$type==='checkbox' ? (is_array($value)?$value:[$value]) : [$value]; $total=0;
        foreach ($selected as $selected_value) foreach ($options as $option) {
            if (!is_array($option)) continue;
            $option_value=isset($option['value'])?(string)$option['value']:'';
            if ($option_value!=='' && (string)$selected_value===$option_value) { $total+=isset($option['price'])?$this->positive_price($option['price']):0; break; }
        }
        return $total;
    }

    private function positive_price($value) { $price=is_numeric($value)?(float)$value:0; return $price>0?$price:0; }
    private function format_price($value) { return (float)wc_format_decimal(max(0,(float)$value),wc_get_price_decimals()); }
    private function is_empty_value($value) { return is_array($value)?empty($value):($value===null||$value===''); }
}

EzLens_Product_Options_Pricing_Engine::get_instance();
