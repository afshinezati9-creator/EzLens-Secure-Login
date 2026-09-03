<?php
if (!defined('ABSPATH')) exit;

use EzLens\ProductOptions\Repositories\TemplateRepository;
use EzLens\ProductOptions\Services\TemplateService;
use EzLens\ProductOptions\Services\FieldSchemaValidator;

/**
 * Backward-compatible facade for the Product Options template API.
 * New code should depend on TemplateService directly.
 */
class EzLens_Product_Options_Template_Manager {
    private static $instance = null;
    private $service;

    public const SCHEMA_VERSION = TemplateService::SCHEMA_VERSION;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->service = new TemplateService(
            new TemplateRepository(),
            new FieldSchemaValidator()
        );
    }

    public function build_schema($fields = [], $settings = [], $layout = []) {
        return $this->service->build_schema($fields, $settings, $layout);
    }

    public function normalize_schema($stored_fields) {
        return $this->service->normalize_schema($stored_fields);
    }

    public function create($data) {
        return $this->service->create($data);
    }

    public function update($id, $data) {
        return $this->service->update($id, $data);
    }

    public function get($id) {
        return $this->service->get($id);
    }

    public function get_schema($id) {
        return $this->service->get_schema($id);
    }

    public function get_templates($status = 'all', $search = '', $limit = 20, $offset = 0) {
        return $this->service->get_templates($status, $search, $limit, $offset);
    }

    public function count_templates($status = 'all', $search = '') {
        return $this->service->count_templates($status, $search);
    }

    public function get_list($args = []) {
        return $this->service->get_list($args);
    }

    public function get_connected_products_count($template_id) {
        return $this->service->get_connected_products_count($template_id);
    }

    public function delete($id) {
        return $this->service->delete($id);
    }

    public function duplicate($id) {
        return $this->service->duplicate($id);
    }

    public function get_template_for_product($product_id) {
        return $this->service->get_template_for_product($product_id);
    }

    public function attach_to_product($product_id, $template_id) {
        return $this->service->attach_to_product($product_id, $template_id);
    }

    /**
     * Expose the service for new integrations without breaking legacy callers.
     */
    public function get_service() {
        return $this->service;
    }
}

EzLens_Product_Options_Template_Manager::get_instance();
