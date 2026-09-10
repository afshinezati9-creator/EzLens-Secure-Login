<?php
/**
 * Maps slot placement keys to WooCommerce / theme hooks.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Placement_Registry
 */
class EzLens_PO_Placement_Registry {

    /**
     * placement => [ [hook, priority], ... ]
     *
     * @return array
     */
    public static function map() {
        $map = array(
            'gallery_side'  => array(
                array('woocommerce_before_single_product_summary', 35),
            ),
            'below_price'   => array(
                array('woocommerce_single_product_summary', 15),
            ),
            'below_summary' => array(
                array('woocommerce_single_product_summary', 45),
            ),
            'full_width'    => array(
                array('woocommerce_after_single_product_summary', 8),
            ),
        );

        /**
         * Filter placement → hook map (themes like Woodmart can adjust).
         *
         * @param array $map
         */
        return apply_filters('ezlens_po_placement_hooks', $map);
    }

    /**
     * @param string $placement
     * @return array[] list of [hook, priority]
     */
    public static function hooks_for($placement) {
        $placement = sanitize_key($placement);
        $map       = self::map();
        if (isset($map[$placement]) && is_array($map[$placement])) {
            return $map[$placement];
        }
        return $map['below_price'];
    }

    /**
     * All unique hooks that need a callback registered.
     *
     * @return array hook => max priority used (we register once per hook)
     */
    public static function all_hooks() {
        $hooks = array();
        foreach (self::map() as $placement => $list) {
            foreach ($list as $pair) {
                $hook = $pair[0];
                $pri  = isset($pair[1]) ? (int) $pair[1] : 10;
                if (!isset($hooks[$hook]) || $pri < $hooks[$hook]) {
                    // Register at lowest priority among placements sharing hook? 
                    // Better: register multiple callbacks with their priorities via dedicated methods.
                }
                $hooks[$hook][] = $pri;
            }
        }
        return $hooks;
    }
}
