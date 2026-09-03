<?php
if (!defined('ABSPATH')) exit;

namespace EzLens\ProductOptions\Services;

use EzLens\ProductOptions\Repositories\TemplateRepository;

/**
 * Application service for Product Option templates.
 * Owns validation, schema normalization and business rules; persistence is delegated.
 */
final class TemplateService {
    public const SCHEMA_VERSION = 1;

    private $repository;
    private $field_validator;

    public function __construct(TemplateRepository $repository = null, FieldSchemaValidator $field_validator = null) {
        $this->repository = $repository ?: new TemplateRepository();
        $this->field_validator = $field_validator ?: new FieldSchemaValidator();
    }

    public function build_schema($fields = [], $settings = [], $layout = []) {
        return [
            'version' => self::SCHEMA_VERSION,
            'settings' => is_array($settings) ? $settings : [],
            'layout' => is_array($layout) ? $layout : [],
            'fields' => $this->field_validator->normalize($fields),
        ];
    }

    public function normalize_schema($stored) {
        if (!is_array($stored)) return $this->build_schema();

        if (isset($stored['version'], $stored['fields'])) {
            $version = absint($stored['version']);
            if ($version === self::SCHEMA_VERSION && is_array($stored['fields'])) {
                return $this->build_schema(
                    $stored['fields'],
                    $stored['settings'] ?? [],
                    $stored['layout'] ?? []
                );
            }
        }

        return $this->build_schema($stored);
    }

    public function create($data) {
        $title = sanitize_text_field($data['title'] ?? '');
        if ($title === '') return ['success' => false, 'message' => 'عنوان قالب الزامی است.'];

        $description = sanitize_textarea_field($data['description'] ?? '');
        $schema = $this->build_schema($data['fields'] ?? [], $data['settings'] ?? [], $data['layout'] ?? []);
        $encoded = wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) return ['success' => false, 'message' => 'خطا در ساختار داده‌های قالب.'];

        $status = sanitize_key($data['status'] ?? 'active');
        if (!in_array($status, ['active', 'inactive'], true)) $status = 'active';

        $result = $this->repository->insert([
            'title' => $title,
            'description' => $description,
            'fields' => $encoded,
            'status' => $status,
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);

        if (!$result['success']) return $result;
        return ['success' => true, 'id' => $result['id'], 'message' => 'قالب با موفقیت ایجاد شد.'];
    }

    public function update($id, $data) {
        $id = absint($id);
        if ($id <= 0 || !$this->repository->find($id)) {
            return ['success' => false, 'message' => 'قالب یافت نشد.'];
        }

        $update = [];
        $formats = [];
        if (isset($data['title'])) {
            $title = sanitize_text_field($data['title']);
            if ($title === '') return ['success' => false, 'message' => 'عنوان قالب الزامی است.'];
            $update['title'] = $title;
            $formats[] = '%s';
        }
        if (isset($data['description'])) {
            $update['description'] = sanitize_textarea_field($data['description']);
            $formats[] = '%s';
        }
        if (isset($data['fields'])) {
            $schema = $this->build_schema($data['fields'], $data['settings'] ?? [], $data['layout'] ?? []);
            $encoded = wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($encoded === false) return ['success' => false, 'message' => 'خطا در ساختار داده‌های قالب.'];
            $update['fields'] = $encoded;
            $formats[] = '%s';
        }
        if (isset($data['status'])) {
            $status = sanitize_key($data['status']);
            if (!in_array($status, ['active', 'inactive'], true)) {
                return ['success' => false, 'message' => 'وضعیت قالب نامعتبر است.'];
            }
            $update['status'] = $status;
            $formats[] = '%s';
        }

        if (empty($update)) return ['success' => false, 'message' => 'هیچ داده‌ای برای به‌روزرسانی وجود ندارد.'];
        $update['updated_at'] = current_time('mysql');
        $formats[] = '%s';

        $result = $this->repository->update($id, $update, $formats);
        return $result['success']
            ? ['success' => true, 'message' => 'قالب با موفقیت به‌روزرسانی شد.']
            : $result;
    }

    public function get($id) {
        $row = $this->repository->find($id);
        return $row ? $this->hydrate($row) : null;
    }

    public function get_schema($id) {
        $template = $this->get($id);
        return $template ? $template['schema'] : null;
    }

    public function get_list($args = []) {
        global $wpdb;
        $defaults = [
            'status' => 'all', 'search' => '', 'limit' => 20, 'offset' => 0,
            'orderby' => 'created_at', 'order' => 'DESC',
        ];
        $args = wp_parse_args($args, $defaults);
        $where = [];

        $status = sanitize_key($args['status']);
        if ($status !== 'all' && in_array($status, ['active', 'inactive'], true)) {
            $where[] = $wpdb->prepare('status = %s', $status);
        }

        $search = sanitize_text_field($args['search']);
        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = $wpdb->prepare('(title LIKE %s OR description LIKE %s)', $like, $like);
        }

        $allowed_orderby = ['id','title','status','created_at','updated_at'];
        $orderby = sanitize_key($args['orderby']);
        if (!in_array($orderby, $allowed_orderby, true)) $orderby = 'created_at';
        $order = strtoupper(sanitize_key($args['order']));
        if (!in_array($order, ['ASC', 'DESC'], true)) $order = 'DESC';

        $result = $this->repository->list([
            'where' => $where,
            'orderby' => $orderby,
            'order' => $order,
            'limit' => $args['limit'],
            'offset' => $args['offset'],
        ]);

        foreach ($result['items'] as &$row) $row = $this->hydrate($row);
        unset($row);
        return $result;
    }

    public function get_templates($status = 'all', $search = '', $limit = 20, $offset = 0) {
        return $this->get_list(compact('status', 'search', 'limit', 'offset'))['items'];
    }

    public function count_templates($status = 'all', $search = '') {
        return $this->get_list(['status' => $status, 'search' => $search, 'limit' => 1, 'offset' => 0])['total'];
    }

    public function get_connected_products_count($template_id) {
        return $this->repository->count_connected_products($template_id);
    }

    public function delete($id) {
        $id = absint($id);
        if ($id <= 0 || !$this->repository->find($id)) return ['success' => false, 'message' => 'قالب یافت نشد.'];

        $connected = $this->repository->count_connected_products($id);
        if ($connected > 0) {
            return ['success' => false, 'message' => 'این قالب به ' . $connected . ' محصول متصل است. ابتدا اتصال را قطع کنید.'];
        }

        $result = $this->repository->delete($id);
        return $result['success']
            ? ['success' => true, 'message' => 'قالب با موفقیت حذف شد.']
            : $result;
    }

    public function duplicate($id) {
        $template = $this->get($id);
        if (!$template) return ['success' => false, 'message' => 'قالب یافت نشد.'];

        return $this->create([
            'title' => $template['title'] . ' (کپی)',
            'description' => $template['description'],
            'fields' => $template['fields'],
            'settings' => $template['schema']['settings'],
            'layout' => $template['schema']['layout'],
            'status' => 'inactive',
        ]);
    }

    public function get_template_for_product($product_id) {
        $template_id = $this->repository->get_product_template_id($product_id);
        return $template_id ? $this->get($template_id) : null;
    }

    public function attach_to_product($product_id, $template_id) {
        $product_id = absint($product_id);
        $template_id = absint($template_id);
        if ($product_id <= 0) return ['success' => false, 'message' => 'شناسه محصول نامعتبر است.'];

        if ($template_id <= 0) {
            $this->repository->set_product_template($product_id, 0);
            return ['success' => true, 'message' => 'قالب از محصول جدا شد.'];
        }

        if (!$this->repository->find($template_id)) {
            return ['success' => false, 'message' => 'قالب یافت نشد.'];
        }

        $this->repository->set_product_template($product_id, $template_id);
        return ['success' => true, 'message' => 'قالب به محصول متصل شد.'];
    }

    private function hydrate(array $row) {
        $decoded = json_decode($row['fields'] ?? '', true);
        $schema = $this->normalize_schema($decoded);
        $row['schema'] = $schema;
        $row['schema_version'] = self::SCHEMA_VERSION;
        $row['fields'] = $schema['fields'];
        return $row;
    }
}
