<?php

namespace EzLens\ProductOptions\Services;

if (!defined('ABSPATH')) {
    exit;
}

use EzLens\ProductOptions\Repositories\TemplateRepository;

/**
 * Application service for Product Option templates.
 *
 * Owns validation, schema normalization and business rules.
 * Persistence is delegated to TemplateRepository.
 */
final class TemplateService {

    /**
     * Current schema version.
     */
    public const SCHEMA_VERSION = 1;

    /**
     * @var TemplateRepository
     */
    private $repository;

    /**
     * @var FieldSchemaValidator
     */
    private $field_validator;

    /**
     * Constructor.
     *
     * @param TemplateRepository|null $repository
     * @param FieldSchemaValidator|null $field_validator
     */
    public function __construct(
        TemplateRepository $repository = null,
        FieldSchemaValidator $field_validator = null
    ) {
        $this->repository = $repository ?: new TemplateRepository();
        $this->field_validator = $field_validator ?: new FieldSchemaValidator();
    }

    /**
     * Build a normalized template schema.
     *
     * @param array $fields
     * @param array $settings
     * @param array $layout
     * @return array
     */
    public function build_schema(
        $fields = [],
        $settings = [],
        $layout = []
    ) {
        return [
            'version'  => self::SCHEMA_VERSION,
            'settings' => is_array($settings) ? $settings : [],
            'layout'   => is_array($layout) ? $layout : [],
            'fields'   => $this->field_validator->normalize($fields),
        ];
    }

    /**
     * Normalize stored schema and support legacy raw field arrays.
     *
     * @param mixed $stored
     * @return array
     */
    public function normalize_schema($stored) {
        if (!is_array($stored)) {
            return $this->build_schema();
        }

        /*
         * Current schema format:
         *
         * {
         *     "version": 1,
         *     "settings": {},
         *     "layout": {},
         *     "fields": []
         * }
         */
        if (isset($stored['version'], $stored['fields'])) {
            $version = absint($stored['version']);

            if (
                $version === self::SCHEMA_VERSION &&
                is_array($stored['fields'])
            ) {
                return $this->build_schema(
                    $stored['fields'],
                    isset($stored['settings']) && is_array($stored['settings'])
                        ? $stored['settings']
                        : [],
                    isset($stored['layout']) && is_array($stored['layout'])
                        ? $stored['layout']
                        : []
                );
            }
        }

        /*
         * Backward compatibility:
         * old templates may contain only a raw fields array.
         */
        return $this->build_schema($stored);
    }

    /**
     * Create a new template.
     *
     * @param array $data
     * @return array
     */
    public function create($data) {
        $data = is_array($data) ? $data : [];

        $title = sanitize_text_field(
            isset($data['title']) ? $data['title'] : ''
        );

        if ($title === '') {
            return [
                'success' => false,
                'message' => 'عنوان قالب الزامی است.',
            ];
        }

        $description = sanitize_textarea_field(
            isset($data['description']) ? $data['description'] : ''
        );

        $schema = $this->build_schema(
            isset($data['fields']) ? $data['fields'] : [],
            isset($data['settings']) ? $data['settings'] : [],
            isset($data['layout']) ? $data['layout'] : []
        );

        $encoded = wp_json_encode(
            $schema,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($encoded === false) {
            return [
                'success' => false,
                'message' => 'خطا در ساختار داده‌های قالب.',
            ];
        }

        $status = sanitize_key(
            isset($data['status']) ? $data['status'] : 'active'
        );

        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = 'active';
        }

        $now = current_time('mysql');

        $result = $this->repository->insert([
            'title'       => $title,
            'description' => $description,
            'fields'      => $encoded,
            'status'      => $status,
            'created_by'  => get_current_user_id(),
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        if (
            !is_array($result) ||
            empty($result['success'])
        ) {
            return is_array($result)
                ? $result
                : [
                    'success' => false,
                    'message' => 'خطا در ذخیره قالب.',
                ];
        }

        return [
            'success' => true,
            'id'      => isset($result['id'])
                ? absint($result['id'])
                : 0,
            'message' => 'قالب با موفقیت ایجاد شد.',
        ];
    }

    /**
     * Update an existing template.
     *
     * @param int   $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        $id = absint($id);
        $data = is_array($data) ? $data : [];

        if (
            $id <= 0 ||
            !$this->repository->find($id)
        ) {
            return [
                'success' => false,
                'message' => 'قالب یافت نشد.',
            ];
        }

        $update  = [];
        $formats = [];

        /*
         * Title.
         */
        if (isset($data['title'])) {
            $title = sanitize_text_field($data['title']);

            if ($title === '') {
                return [
                    'success' => false,
                    'message' => 'عنوان قالب الزامی است.',
                ];
            }

            $update['title'] = $title;
            $formats[] = '%s';
        }

        /*
         * Description.
         */
        if (isset($data['description'])) {
            $update['description'] = sanitize_textarea_field(
                $data['description']
            );

            $formats[] = '%s';
        }

        /*
         * Fields + schema.
         */
        if (isset($data['fields'])) {
            $schema = $this->build_schema(
                $data['fields'],
                isset($data['settings'])
                    ? $data['settings']
                    : [],
                isset($data['layout'])
                    ? $data['layout']
                    : []
            );

            $encoded = wp_json_encode(
                $schema,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            if ($encoded === false) {
                return [
                    'success' => false,
                    'message' => 'خطا در ساختار داده‌های قالب.',
                ];
            }

            $update['fields'] = $encoded;
            $formats[] = '%s';
        }

        /*
         * Status.
         */
        if (isset($data['status'])) {
            $status = sanitize_key($data['status']);

            if (!in_array($status, ['active', 'inactive'], true)) {
                return [
                    'success' => false,
                    'message' => 'وضعیت قالب نامعتبر است.',
                ];
            }

            $update['status'] = $status;
            $formats[] = '%s';
        }

        if (empty($update)) {
            return [
                'success' => false,
                'message' => 'هیچ داده‌ای برای به‌روزرسانی وجود ندارد.',
            ];
        }

        /*
         * Always update timestamp.
         */
        $update['updated_at'] = current_time('mysql');
        $formats[] = '%s';

        $result = $this->repository->update(
            $id,
            $update,
            $formats
        );

        if (
            !is_array($result) ||
            empty($result['success'])
        ) {
            return is_array($result)
                ? $result
                : [
                    'success' => false,
                    'message' => 'خطا در به‌روزرسانی قالب.',
                ];
        }

        return [
            'success' => true,
            'message' => 'قالب با موفقیت به‌روزرسانی شد.',
        ];
    }

    /**
     * Get one template.
     *
     * @param int $id
     * @return array|null
     */
    public function get($id) {
        $row = $this->repository->find($id);

        return $row
            ? $this->hydrate($row)
            : null;
    }

    /**
     * Get normalized schema for a template.
     *
     * @param int $id
     * @return array|null
     */
    public function get_schema($id) {
        $template = $this->get($id);

        return $template
            ? $template['schema']
            : null;
    }

    /**
     * Get template list.
     *
     * @param array $args
     * @return array
     */
    public function get_list($args = []) {
        global $wpdb;

        $defaults = [
            'status'  => 'all',
            'search'  => '',
            'limit'   => 20,
            'offset'  => 0,
            'orderby' => 'created_at',
            'order'   => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);

        /*
         * Normalize pagination.
         */
        $limit = min(
            100,
            max(
                1,
                absint($args['limit'])
            )
        );

        $offset = max(
            0,
            absint($args['offset'])
        );

        /*
         * WHERE clauses.
         */
        $where = [];

        /*
         * Status filter.
         */
        $status = sanitize_key($args['status']);

        if (
            $status !== 'all' &&
            in_array(
                $status,
                ['active', 'inactive'],
                true
            )
        ) {
            $where[] = $wpdb->prepare(
                'status = %s',
                $status
            );
        }

        /*
         * Search filter.
         */
        $search = sanitize_text_field($args['search']);

        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';

            $where[] = $wpdb->prepare(
                '(title LIKE %s OR description LIKE %s)',
                $like,
                $like
            );
        }

        /*
         * ORDER BY whitelist.
         *
         * Never pass arbitrary user input directly into SQL.
         */
        $allowed_orderby = [
            'id',
            'title',
            'status',
            'created_at',
            'updated_at',
        ];

        $orderby = sanitize_key($args['orderby']);

        if (!in_array($orderby, $allowed_orderby, true)) {
            $orderby = 'created_at';
        }

        /*
         * Sort direction whitelist.
         */
        $order = strtoupper(
            sanitize_key($args['order'])
        );

        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        $result = $this->repository->list([
            'where'   => $where,
            'orderby' => $orderby,
            'order'   => $order,
            'limit'   => $limit,
            'offset'  => $offset,
        ]);

        if (
            !is_array($result) ||
            !isset($result['items'])
        ) {
            return [
                'items' => [],
                'total' => 0,
            ];
        }

        foreach ($result['items'] as &$row) {
            if (is_array($row)) {
                $row = $this->hydrate($row);
            }
        }

        unset($row);

        return $result;
    }

    /**
     * Backward-compatible template list.
     *
     * @param string $status
     * @param string $search
     * @param int    $limit
     * @param int    $offset
     * @return array
     */
    public function get_templates(
        $status = 'all',
        $search = '',
        $limit = 20,
        $offset = 0
    ) {
        $result = $this->get_list([
            'status' => $status,
            'search' => $search,
            'limit'  => $limit,
            'offset' => $offset,
        ]);

        return isset($result['items'])
            ? $result['items']
            : [];
    }

    /**
     * Count templates.
     *
     * @param string $status
     * @param string $search
     * @return int
     */
    public function count_templates(
        $status = 'all',
        $search = ''
    ) {
        $result = $this->get_list([
            'status' => $status,
            'search' => $search,
            'limit'  => 1,
            'offset' => 0,
        ]);

        return isset($result['total'])
            ? absint($result['total'])
            : 0;
    }

    /**
     * Count products connected to a template.
     *
     * @param int $template_id
     * @return int
     */
    public function get_connected_products_count($template_id) {
        return absint(
            $this->repository->count_connected_products(
                $template_id
            )
        );
    }

    /**
     * Delete a template.
     *
     * Templates connected to products cannot be deleted.
     *
     * @param int $id
     * @return array
     */
    public function delete($id) {
        $id = absint($id);

        if (
            $id <= 0 ||
            !$this->repository->find($id)
        ) {
            return [
                'success' => false,
                'message' => 'قالب یافت نشد.',
            ];
        }

        $connected = absint(
            $this->repository->count_connected_products($id)
        );

        if ($connected > 0) {
            return [
                'success' => false,
                'message' => 'این قالب به ' .
                    $connected .
                    ' محصول متصل است. ابتدا اتصال را قطع کنید.',
            ];
        }

        $result = $this->repository->delete($id);

        return (
            is_array($result) &&
            !empty($result['success'])
        )
            ? [
                'success' => true,
                'message' => 'قالب با موفقیت حذف شد.',
            ]
            : (
                is_array($result)
                    ? $result
                    : [
                        'success' => false,
                        'message' => 'خطا در حذف قالب.',
                    ]
            );
    }

    /**
     * Duplicate a template.
     *
     * New copy is created as inactive.
     *
     * @param int $id
     * @return array
     */
    public function duplicate($id) {
        $template = $this->get($id);

        if (!$template) {
            return [
                'success' => false,
                'message' => 'قالب یافت نشد.',
            ];
        }

        return $this->create([
            'title'       => $template['title'] . ' (کپی)',
            'description' => isset($template['description'])
                ? $template['description']
                : '',
            'fields'      => isset($template['fields'])
                ? $template['fields']
                : [],
            'settings'    => isset($template['schema']['settings'])
                ? $template['schema']['settings']
                : [],
            'layout'      => isset($template['schema']['layout'])
                ? $template['schema']['layout']
                : [],
            'status'      => 'inactive',
        ]);
    }

    /**
     * Get template attached to a product.
     *
     * @param int $product_id
     * @return array|null
     */
    public function get_template_for_product($product_id) {
        $product_id = absint($product_id);

        if ($product_id <= 0) {
            return null;
        }

        $template_id = $this->repository->get_product_template_id(
            $product_id
        );

        return $template_id
            ? $this->get($template_id)
            : null;
    }

    /**
     * Attach or detach a template from a product.
     *
     * @param int $product_id
     * @param int $template_id
     * @return array
     */
    public function attach_to_product(
        $product_id,
        $template_id
    ) {
        $product_id = absint($product_id);
        $template_id = absint($template_id);

        if ($product_id <= 0) {
            return [
                'success' => false,
                'message' => 'شناسه محصول نامعتبر است.',
            ];
        }

        /*
         * Template ID 0 means detach.
         */
        if ($template_id <= 0) {
            $this->repository->set_product_template(
                $product_id,
                0
            );

            return [
                'success' => true,
                'message' => 'قالب از محصول جدا شد.',
            ];
        }

        /*
         * Make sure the template exists.
         */
        if (!$this->repository->find($template_id)) {
            return [
                'success' => false,
                'message' => 'قالب یافت نشد.',
            ];
        }

        $this->repository->set_product_template(
            $product_id,
            $template_id
        );

        return [
            'success' => true,
            'message' => 'قالب به محصول متصل شد.',
        ];
    }

    /**
     * Convert database row into application template structure.
     *
     * @param array $row
     * @return array
     */
    private function hydrate(array $row) {
        $decoded = json_decode(
            isset($row['fields'])
                ? $row['fields']
                : '',
            true
        );

        $schema = $this->normalize_schema($decoded);

        /*
         * Normalized schema.
         */
        $row['schema'] = $schema;

        /*
         * Explicit schema version for consumers.
         */
        $row['schema_version'] = self::SCHEMA_VERSION;

        /*
         * Backward compatibility:
         * consumers expecting fields directly can continue
         * using $template['fields'].
         */
        $row['fields'] = $schema['fields'];

        return $row;
    }
}