<?php
/**
 * Product option slots (multi-template per product).
 *
 * Meta key: _ezlens_option_slots (JSON array)
 * Legacy:   _ezlens_option_template_id (single int) — migrated on read.
 *
 * @package EzLens\ProductOptions\Services
 */

namespace EzLens\ProductOptions\Services;

if (!defined('ABSPATH')) {
    exit;
}

use EzLens\ProductOptions\Repositories\TemplateRepository;

/**
 * Manages per-product template slots: placement + display mode + order.
 */
final class ProductSlotsService {

    public const META_SLOTS    = '_ezlens_option_slots';
    public const META_LEGACY   = '_ezlens_option_template_id';
    public const META_VERSION  = '_ezlens_option_slots_version';
    public const SCHEMA_VERSION = 1;

    /** @var TemplateRepository */
    private $repository;

    public function __construct(TemplateRepository $repository = null) {
        $this->repository = $repository ?: new TemplateRepository();
    }

    /**
     * Get normalized slots for a product (always array of slot arrays).
     * Migrates legacy single template meta on the fly.
     *
     * @param int $product_id
     * @return array[]
     */
    public function get_slots($product_id) {
        $product_id = absint($product_id);
        if ($product_id <= 0) {
            return array();
        }

        $raw = get_post_meta($product_id, self::META_SLOTS, true);

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        if (is_array($raw) && !empty($raw)) {
            return $this->normalize_slots($raw);
        }

        // Legacy single template.
        $legacy_id = absint(get_post_meta($product_id, self::META_LEGACY, true));
        if ($legacy_id > 0) {
            $slots = array(
                $this->default_slot($legacy_id, 1),
            );
            // Persist migration so next load is clean (non-destructive to legacy key until Phase 1 UI saves).
            $this->save_slots($product_id, $slots, false);
            return $slots;
        }

        return array();
    }

    /**
     * Persist slots. Optionally keep legacy key in sync with first slot for old readers.
     *
     * @param int   $product_id
     * @param array $slots
     * @param bool  $sync_legacy
     * @return array{success:bool,message?:string,slots?:array}
     */
    public function save_slots($product_id, array $slots, $sync_legacy = true) {
        $product_id = absint($product_id);
        if ($product_id <= 0) {
            return array('success' => false, 'message' => 'شناسه محصول نامعتبر است.');
        }

        $normalized = $this->normalize_slots($slots);

        if (empty($normalized)) {
            delete_post_meta($product_id, self::META_SLOTS);
            if ($sync_legacy) {
                delete_post_meta($product_id, self::META_LEGACY);
            }
            update_post_meta($product_id, self::META_VERSION, self::SCHEMA_VERSION);
            return array('success' => true, 'slots' => array());
        }

        $encoded = wp_json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            return array('success' => false, 'message' => 'خطا در ذخیره اسلات‌ها.');
        }

        update_post_meta($product_id, self::META_SLOTS, $encoded);
        update_post_meta($product_id, self::META_VERSION, self::SCHEMA_VERSION);

        if ($sync_legacy) {
            $first_id = absint($normalized[0]['template_id'] ?? 0);
            if ($first_id > 0) {
                update_post_meta($product_id, self::META_LEGACY, $first_id);
            } else {
                delete_post_meta($product_id, self::META_LEGACY);
            }
        }

        return array('success' => true, 'slots' => $normalized);
    }

    /**
     * All templates attached to product (hydrated), ordered by slot order.
     * Used by Pricing / FieldRenderer until they switch to per-slot rendering (Phase 2).
     *
     * @param int $product_id
     * @return array[] list of template arrays (same shape as TemplateService::get)
     */
    public function get_templates_for_product($product_id) {
        $slots = $this->get_slots($product_id);
        if (empty($slots)) {
            return array();
        }

        $service = new TemplateService($this->repository);
        $out     = array();

        foreach ($slots as $slot) {
            $tid = absint($slot['template_id'] ?? 0);
            if ($tid <= 0) {
                continue;
            }
            $tpl = $service->get($tid);
            if (!$tpl || ($tpl['status'] ?? '') === 'inactive') {
                continue;
            }
            $tpl['_slot'] = $slot;
            $out[]        = $tpl;
        }

        return $out;
    }

    /**
     * First template only — backward compatible with get_template_for_product().
     *
     * @param int $product_id
     * @return array|null
     */
    public function get_primary_template($product_id) {
        $list = $this->get_templates_for_product($product_id);
        return $list[0] ?? null;
    }

    /**
     * @param array $slots
     * @return array[]
     */
    private function normalize_slots(array $slots) {
        $allowed_place   = \EzLens_Product_Options_Helpers::allowed_placements();
        $allowed_display = \EzLens_Product_Options_Helpers::allowed_display_modes();
        $normalized      = array();
        $order           = 0;

        foreach ($slots as $slot) {
            if (!is_array($slot)) {
                continue;
            }
            $tid = absint($slot['template_id'] ?? $slot['id'] ?? 0);
            if ($tid <= 0) {
                continue;
            }

            $order++;
            $placement = sanitize_key($slot['placement'] ?? 'below_price');
            if (!in_array($placement, $allowed_place, true)) {
                $placement = 'below_price';
            }

            $display = sanitize_key($slot['display'] ?? 'inline');
            if (!in_array($display, $allowed_display, true)) {
                $display = 'inline';
            }

            $slot_order = isset($slot['order']) ? absint($slot['order']) : $order;

            $normalized[] = array(
                'template_id' => $tid,
                'label'       => sanitize_text_field($slot['label'] ?? ''),
                'placement'   => $placement,
                'display'     => $display,
                'order'       => $slot_order > 0 ? $slot_order : $order,
            );
        }

        usort(
            $normalized,
            static function ($a, $b) {
                return ($a['order'] <=> $b['order']) ?: ($a['template_id'] <=> $b['template_id']);
            }
        );

        // Re-index order 1..n
        $i = 1;
        foreach ($normalized as &$row) {
            $row['order'] = $i++;
        }
        unset($row);

        return $normalized;
    }

    /**
     * @param int $template_id
     * @param int $order
     * @return array
     */
    private function default_slot($template_id, $order = 1) {
        return array(
            'template_id' => absint($template_id),
            'label'       => '',
            'placement'   => 'below_price',
            'display'     => 'inline',
            'order'       => absint($order),
        );
    }
}
