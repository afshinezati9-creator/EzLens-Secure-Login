<?php
/**
 * Shared helpers for Product Options module.
 *
 * @package EzLens\ProductOptions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_Product_Options_Helpers
 */
class EzLens_Product_Options_Helpers {

    /**
     * Convert Western digits to Persian digits.
     *
     * @param mixed $value Number or string.
     * @return string
     */
    public static function to_persian_digits($value) {
        $str = (string) $value;
        $en  = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $fa  = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        return str_replace($en, $fa, $str);
    }

    /**
     * Convert Persian/Arabic digits to Western digits.
     *
     * @param mixed $value
     * @return string
     */
    public static function to_western_digits($value) {
        $str = (string) $value;
        $fa  = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $ar  = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        $en  = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $str = str_replace($fa, $en, $str);
        return str_replace($ar, $en, $str);
    }

    /**
     * Format a WooCommerce-style price with Persian digits (display only).
     *
     * @param float|int|string $amount
     * @return string
     */
    public static function format_price_fa($amount) {
        if (!function_exists('wc_price')) {
            return self::to_persian_digits(number_format((float) $amount));
        }
        $html = wp_strip_all_tags(wc_price($amount));
        return self::to_persian_digits($html);
    }

    /**
     * Resolve an SVG icon URL from plugin assets/icons (or modern subfolder).
     *
     * @param string $name Icon basename without extension (e.g. "upload", "check").
     * @param string $variant "" or "modern".
     * @return string URL or empty string.
     */
    public static function icon_url($name, $variant = '') {
        $name = sanitize_file_name((string) $name);
        if ($name === '') {
            return '';
        }

        $base_dir = defined('EZLAUTH_PLUGIN_DIR') ? EZLAUTH_PLUGIN_DIR . 'assets/icons/' : '';
        $base_url = defined('EZLAUTH_PLUGIN_URL') ? EZLAUTH_PLUGIN_URL . 'assets/icons/' : '';

        if ($base_dir === '' || $base_url === '') {
            return '';
        }

        $candidates = array();
        if ($variant === 'modern') {
            $candidates[] = array($base_dir . 'modern/' . $name . '.svg', $base_url . 'modern/' . $name . '.svg');
        }
        $candidates[] = array($base_dir . $name . '.svg', $base_url . $name . '.svg');
        $candidates[] = array($base_dir . 'modern/' . $name . '.svg', $base_url . 'modern/' . $name . '.svg');

        foreach ($candidates as $pair) {
            if (is_readable($pair[0])) {
                return $pair[1];
            }
        }

        return '';
    }

    /**
     * Inline SVG markup from icons folder (preferred for minimal UI).
     *
     * @param string $name
     * @param string $variant
     * @param string $class
     * @return string
     */
    public static function icon_svg($name, $variant = '', $class = 'ezlens-icon') {
        $name = sanitize_file_name((string) $name);
        if ($name === '' || !defined('EZLAUTH_PLUGIN_DIR')) {
            return '';
        }

        $base = EZLAUTH_PLUGIN_DIR . 'assets/icons/';
        $paths = array();
        if ($variant === 'modern') {
            $paths[] = $base . 'modern/' . $name . '.svg';
        }
        $paths[] = $base . $name . '.svg';
        $paths[] = $base . 'modern/' . $name . '.svg';

        foreach ($paths as $path) {
            if (!is_readable($path)) {
                continue;
            }
            $svg = file_get_contents($path);
            if ($svg === false || $svg === '') {
                continue;
            }
            // Strip XML declaration / scripts for safety.
            $svg = preg_replace('/<\?xml[^>]*\?>/i', '', $svg);
            $svg = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $svg);
            if ($class !== '') {
                if (preg_match('/<svg\b([^>]*)>/i', $svg, $m)) {
                    $attrs = $m[1];
                    if (stripos($attrs, 'class=') === false) {
                        $svg = preg_replace('/<svg\b/i', '<svg class="' . esc_attr($class) . '"', $svg, 1);
                    }
                }
            }
            return $svg;
        }

        return '';
    }

    /**
     * Whether a field key is an internal code-only field (not rendered / not submitted as user data).
     *
     * Convention: keys starting with "_code_" are reserved for builder/runtime metadata
     * (e.g. injected snippets) and must be skipped in sanitize, validate, price, and UI loops.
     *
     * @param string|int $key
     * @return bool
     */
    public static function is_code_field_key($key) {
        return strpos((string) $key, '_code_') === 0;
    }

    /**
     * Allowed placement values for product option slots (Phase 1+).
     *
     * @return string[]
     */
    public static function allowed_placements() {
        return array('gallery_side', 'below_price', 'below_summary', 'full_width');
    }

    /**
     * Allowed display modes for product option slots (Phase 1+).
     *
     * @return string[]
     */
    public static function allowed_display_modes() {
        return array('inline', 'accordion', 'ajax_modal');
    }
}
