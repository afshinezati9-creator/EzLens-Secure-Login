<?php
/**
 * Product Options settings (WP options + optional EzLens_Auth_Settings bridge).
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Settings
 */
class EzLens_PO_Settings {

    const OPTION_KEY = 'ezlens_po_settings';

    /**
     * Defaults.
     *
     * @return array
     */
    public static function defaults() {
        return array(
            'enabled'                 => '1',
            'max_upload_size'         => 5,
            'allowed_extensions'      => 'jpg,jpeg,png,gif,webp,pdf,doc,docx',
            'show_title'              => '1',
            'show_price_in_cart'      => '1',
            'required_fields'         => '1',
            'default_layout'          => '1',
            // Cart / checkout display
            'cart_display_mode'       => 'button', // button | accordion | inline
            'cart_button_label'       => '',       // empty = auto from template/slot title
            'cart_group_by_template'  => '1',
            'cart_show_extra_price'   => '1',
            'checkout_same_as_cart'   => '1',
            'order_display_mode'      => 'inline', // inline | accordion
        );
    }

    /**
     * @return array
     */
    public static function all() {
        $stored = get_option(self::OPTION_KEY, array());
        if (!is_array($stored)) {
            $stored = array();
        }
        $out = array_merge(self::defaults(), $stored);

        // Bridge legacy Auth settings if present.
        if (class_exists('EzLens_Auth_Settings')) {
            $map = array(
                'product_options_enabled'            => 'enabled',
                'product_options_max_upload_size'    => 'max_upload_size',
                'product_options_allowed_extensions' => 'allowed_extensions',
                'product_options_show_title'         => 'show_title',
                'product_options_show_price_in_cart' => 'show_price_in_cart',
                'product_options_required_fields'    => 'required_fields',
                'product_options_default_layout'     => 'default_layout',
            );
            foreach ($map as $auth_key => $local) {
                $v = EzLens_Auth_Settings::get($auth_key);
                if ($v !== null && $v !== '') {
                    $out[ $local ] = $v;
                }
            }
        }
        return $out;
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        $all = self::all();
        if (array_key_exists($key, $all)) {
            return $all[ $key ];
        }
        return $default;
    }

    /**
     * @param array $input
     * @return array sanitized saved
     */
    public static function save(array $input) {
        $defaults = self::defaults();
        $clean    = array();

        $clean['enabled']            = !empty($input['enabled']) ? '1' : '0';
        $clean['max_upload_size']    = max(1, min(100, absint($input['max_upload_size'] ?? 5)));
        $clean['allowed_extensions'] = sanitize_text_field($input['allowed_extensions'] ?? $defaults['allowed_extensions']);
        $clean['show_title']         = !empty($input['show_title']) ? '1' : '0';
        $clean['show_price_in_cart'] = !empty($input['show_price_in_cart']) ? '1' : '0';
        $clean['required_fields']    = !empty($input['required_fields']) ? '1' : '0';
        $clean['default_layout']     = sanitize_text_field((string) ($input['default_layout'] ?? '1'));

        $mode = sanitize_key($input['cart_display_mode'] ?? 'button');
        $clean['cart_display_mode'] = in_array($mode, array('button', 'accordion', 'inline'), true) ? $mode : 'button';
        $clean['cart_button_label'] = sanitize_text_field($input['cart_button_label'] ?? '');
        $clean['cart_group_by_template'] = !empty($input['cart_group_by_template']) ? '1' : '0';
        $clean['cart_show_extra_price']  = !empty($input['cart_show_extra_price']) ? '1' : '0';
        $clean['checkout_same_as_cart']  = !empty($input['checkout_same_as_cart']) ? '1' : '0';
        $om = sanitize_key($input['order_display_mode'] ?? 'inline');
        $clean['order_display_mode'] = in_array($om, array('inline', 'accordion'), true) ? $om : 'inline';

        update_option(self::OPTION_KEY, $clean, false);

        // Mirror to Auth settings when available.
        if (class_exists('EzLens_Auth_Settings') && method_exists('EzLens_Auth_Settings', 'set')) {
            EzLens_Auth_Settings::set('product_options_enabled', $clean['enabled']);
            EzLens_Auth_Settings::set('product_options_max_upload_size', (string) $clean['max_upload_size']);
            EzLens_Auth_Settings::set('product_options_allowed_extensions', $clean['allowed_extensions']);
            EzLens_Auth_Settings::set('product_options_show_title', $clean['show_title']);
            EzLens_Auth_Settings::set('product_options_show_price_in_cart', $clean['show_price_in_cart']);
            EzLens_Auth_Settings::set('product_options_required_fields', $clean['required_fields']);
            EzLens_Auth_Settings::set('product_options_default_layout', $clean['default_layout']);
        }

        return $clean;
    }
}
