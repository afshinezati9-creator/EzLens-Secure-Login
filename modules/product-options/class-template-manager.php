<?php
/**
 * Backward-compatible facade for Product Option templates.
 *
 * All persistence and business rules live in TemplateService + TemplateRepository.
 * Existing callers (AJAX, FieldRenderer, Pricing, MetaBox) keep using this class.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

use EzLens\ProductOptions\Services\TemplateService;
use EzLens\ProductOptions\Services\ProductSlotsService;
use EzLens\ProductOptions\Repositories\TemplateRepository;

/**
 * Class EzLens_Product_Options_Template_Manager
 */
class EzLens_Product_Options_Template_Manager {

    /** @var self|null */
    private static $instance = null;

    /** @var TemplateService */
    private $service;

    /** @var ProductSlotsService */
    private $slots;

    /** @var TemplateRepository */
    private $repository;

    /** @var string */
    private $table_name;

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
        global $wpdb;
        $this->repository = new TemplateRepository();
        $this->service    = new TemplateService($this->repository);
        $this->slots      = new ProductSlotsService($this->repository);
        $this->table_name = $this->repository->get_table_name();
        // Table creation is owned solely by EzLens_Product_Options_Install.
    }

    /**
     * @return TemplateService
     */
    public function get_service() {
        return $this->service;
    }

    /**
     * @return ProductSlotsService
     */
    public function get_slots_service() {
        return $this->slots;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create($data) {
        return $this->service->create(is_array($data) ? $data : array());
    }

    /**
     * @param int   $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        return $this->service->update(absint($id), is_array($data) ? $data : array());
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function get($id) {
        return $this->service->get(absint($id));
    }

    /**
     * @param array $args
     * @return array
     */
    public function get_list($args = array()) {
        $args = is_array($args) ? $args : array();
        $result = $this->service->get_list($args);
        // Always return {items,total}. Callers that need a flat list can use items.
        if (!is_array($result)) {
            return array('items' => array(), 'total' => 0);
        }
        if (!isset($result['items'])) {
            // Legacy flat array
            return array('items' => array_values($result), 'total' => count($result));
        }
        return $result;
    }

    /**
     * @param string $status
     * @param string $search
     * @param int    $limit
     * @param int    $offset
     * @return array
     */
    public function get_templates($status = 'all', $search = '', $limit = 20, $offset = 0) {
        if (method_exists($this->service, 'get_templates')) {
            return $this->service->get_templates($status, $search, $limit, $offset);
        }
        return $this->service->get_list(
            array(
                'status' => $status,
                'search' => $search,
                'limit'  => $limit,
                'offset' => $offset,
            )
        );
    }

    /**
     * @param string $status
     * @param string $search
     * @return int
     */
    public function count_templates($status = 'all', $search = '') {
        if (method_exists($this->service, 'count_templates')) {
            return (int) $this->service->count_templates($status, $search);
        }
        $result = $this->service->get_list(
            array(
                'status' => $status,
                'search' => $search,
                'limit'  => 1,
                'offset' => 0,
            )
        );
        return (int) ($result['total'] ?? 0);
    }

    /**
     * @param int $template_id
     * @return int
     */
    public function get_connected_products_count($template_id) {
        return (int) $this->service->get_connected_products_count(absint($template_id));
    }

    /**
     * @param int $id
     * @return array
     */
    public function delete($id) {
        return $this->service->delete(absint($id));
    }

    /**
     * @param int $id
     * @return array
     */
    public function duplicate($id) {
        return $this->service->duplicate(absint($id));
    }

    /**
     * Primary template for product (slot[0] or legacy meta).
     *
     * @param int $product_id
     * @return array|null
     */
    public function get_template_for_product($product_id) {
        $primary = $this->slots->get_primary_template(absint($product_id));
        if ($primary) {
            return $primary;
        }
        return $this->service->get_template_for_product(absint($product_id));
    }

    /**
     * All templates for product (multi-slot). Phase 2 renderers should use this.
     *
     * @param int $product_id
     * @return array[]
     */
    public function get_templates_for_product($product_id) {
        return $this->slots->get_templates_for_product(absint($product_id));
    }

    /**
     * @param int $product_id
     * @param int $template_id
     * @return array
     */
    public function attach_to_product($product_id, $template_id) {
        return $this->service->attach_to_product(absint($product_id), absint($template_id));
    }

    /**
     * Presets — delegated to PresetLibrary when available, else empty.
     *
     * @return array
     */
    public function get_presets() {
        if (class_exists('\EzLens\ProductOptions\Services\PresetLibrary')) {
            $lib = new \EzLens\ProductOptions\Services\PresetLibrary();
            if (method_exists($lib, 'all')) {
                return $lib->all();
            }
            if (method_exists($lib, 'get_presets')) {
                return $lib->get_presets();
            }
        }
        return array();
    }

    /**
     * @param string $preset_id
     * @param string $title
     * @return array
     */
    public function create_from_preset($preset_id, $title = '') {
        if (class_exists('\EzLens\ProductOptions\Services\PresetLibrary')) {
            $lib = new \EzLens\ProductOptions\Services\PresetLibrary();
            if (method_exists($lib, 'create_template')) {
                return $lib->create_template($preset_id, $title);
            }
        }
        return array('success' => false, 'message' => 'کتابخانه پیش‌فرض در دسترس نیست.');
    }
}
