<?php
if (!defined('ABSPATH')) exit;

class EzLens_Product_Options_Template_Manager {
    private static $instance = null;
    private $table_name;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ezlens_option_templates';
    }

    public function create($data) {
        global $wpdb;

        $title = sanitize_text_field($data['title'] ?? '');
        $description = sanitize_textarea_field($data['description'] ?? '');
        $fields = isset($data['fields']) ? wp_json_encode($data['fields']) : '[]';
        $status = sanitize_key($data['status'] ?? 'active');
        $status = in_array($status, ['active', 'inactive'], true) ? $status : 'active';
        $created_by = get_current_user_id();

        if (empty($title)) {
            return ['success' => false, 'message' => 'عنوان قالب الزامی است.'];
        }

        $result = $wpdb->insert(
            $this->table_name,
            [
                'title' => $title,
                'description' => $description,
                'fields' => $fields,
                'status' => $status,
                'created_by' => $created_by,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s', '%s']
        );

        if ($result === false) {
            return ['success' => false, 'message' => 'خطا در ذخیره قالب: ' . $wpdb->last_error];
        }

        return ['success' => true, 'id' => $wpdb->insert_id, 'message' => 'قالب با موفقیت ایجاد شد.'];
    }

    public function update($id, $data) {
        global $wpdb;

        $id = absint($id);
        if ($id <= 0) {
            return ['success' => false, 'message' => 'شناسه قالب نامعتبر است.'];
        }

        $update_data = [];
        $update_format = [];

        if (isset($data['title'])) {
            $title = sanitize_text_field($data['title']);
            if ($title === '') {
                return ['success' => false, 'message' => 'عنوان قالب الزامی است.'];
            }
            $update_data['title'] = $title;
            $update_format[] = '%s';
        }
        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
            $update_format[] = '%s';
        }
        if (isset($data['fields'])) {
            $update_data['fields'] = wp_json_encode($data['fields']);
            $update_format[] = '%s';
        }
        if (isset($data['status'])) {
            $status = sanitize_key($data['status']);
            if (!in_array($status, ['active', 'inactive'], true)) {
                return ['success' => false, 'message' => 'وضعیت قالب نامعتبر است.'];
            }
            $update_data['status'] = $status;
            $update_format[] = '%s';
        }

        if (empty($update_data)) {
            return ['success' => false, 'message' => 'هیچ داده‌ای برای به‌روزرسانی وجود ندارد.'];
        }

        $update_data['updated_at'] = current_time('mysql');
        $update_format[] = '%s';

        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            ['id' => $id],
            $update_format,
            ['%d']
        );

        if ($result === false) {
            return ['success' => false, 'message' => 'خطا در به‌روزرسانی قالب: ' . $wpdb->last_error];
        }

        wp_cache_delete('ezlens_template_' . $id, 'ezlens');

        return ['success' => true, 'message' => 'قالب با موفقیت به‌روزرسانی شد.'];
    }

    public function get($id) {
        global $wpdb;

        $id = absint($id);
        if ($id <= 0) {
            return null;
        }

        $cache_key = 'ezlens_template_' . $id;
        $template = wp_cache_get($cache_key, 'ezlens');

        if ($template !== false) {
            return $template;
        }

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$row) {
            return null;
        }

        $row['fields'] = json_decode($row['fields'], true);
        if (!is_array($row['fields'])) {
            $row['fields'] = [];
        }

        wp_cache_set($cache_key, $row, 'ezlens', 300);
        return $row;
    }

    public function get_templates($status = 'all', $search = '', $limit = 20, $offset = 0) {
        $args = [
            'status' => $status,
            'search' => $search,
            'limit' => $limit,
            'offset' => $offset,
        ];
        $result = $this->get_list($args);
        return $result['items'];
    }

    public function count_templates($status = 'all', $search = '') {
        global $wpdb;

        $where = [];

        if ($status !== 'all') {
            $status = sanitize_key($status);
            if (in_array($status, ['active', 'inactive'], true)) {
                $where[] = $wpdb->prepare("status = %s", $status);
            }
        }

        if (!empty($search)) {
            $search_like = '%' . $wpdb->esc_like(sanitize_text_field($search)) . '%';
            $where[] = $wpdb->prepare("(title LIKE %s OR description LIKE %s)", $search_like, $search_like);
        }

        $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT COUNT(*) FROM {$this->table_name} {$where_sql}";

        return (int) $wpdb->get_var($sql);
    }

    public function get_list($args = []) {
        global $wpdb;

        $defaults = [
            'status' => 'all',
            'search' => '',
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);
        $where = [];

        $status = sanitize_key($args['status']);
        if ($status !== 'all') {
            if (!in_array($status, ['active', 'inactive'], true)) {
                $status = 'all';
            } else {
                $where[] = $wpdb->prepare("status = %s", $status);
            }
        }

        $search = sanitize_text_field($args['search']);
        if ($search !== '') {
            $search_like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = $wpdb->prepare("(title LIKE %s OR description LIKE %s)", $search_like, $search_like);
        }

        $allowed_orderby = [
            'id' => 'id',
            'title' => 'title',
            'status' => 'status',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
        $requested_orderby = sanitize_key($args['orderby']);
        $orderby = $allowed_orderby[$requested_orderby] ?? 'created_at';

        $order = strtoupper(sanitize_key($args['order']));
        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        $limit = min(100, max(1, absint($args['limit'])));
        $offset = max(0, absint($args['offset']));

        $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $order_sql = "ORDER BY {$orderby} {$order}";
        $limit_sql = $wpdb->prepare("LIMIT %d OFFSET %d", $limit, $offset);

        $sql = "SELECT * FROM {$this->table_name} {$where_sql} {$order_sql} {$limit_sql}";
        $results = $wpdb->get_results($sql, ARRAY_A);

        foreach ($results as &$row) {
            $row['fields'] = json_decode($row['fields'], true);
            if (!is_array($row['fields'])) {
                $row['fields'] = [];
            }
        }
        unset($row);

        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} {$where_sql}";
        $total = (int) $wpdb->get_var($count_sql);

        return [
            'items' => $results,
            'total' => $total,
        ];
    }

    public function get_connected_products_count($template_id) {
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ezlens_option_template_id' AND meta_value = %d",
            absint($template_id)
        ));
        return (int) $count;
    }

    public function delete($id) {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) {
            return ['success' => false, 'message' => 'شناسه قالب نامعتبر است.'];
        }

        $connected = $this->get_connected_products_count($id);
        if ($connected > 0) {
            return ['success' => false, 'message' => 'این قالب به ' . $connected . ' محصول متصل است. ابتدا اتصال را قطع کنید.'];
        }

        $result = $wpdb->delete($this->table_name, ['id' => $id], ['%d']);
        if ($result === false) {
            return ['success' => false, 'message' => 'خطا در حذف قالب: ' . $wpdb->last_error];
        }

        wp_cache_delete('ezlens_template_' . $id, 'ezlens');
        return ['success' => true, 'message' => 'قالب با موفقیت حذف شد.'];
    }

    public function duplicate($id) {
        $id = absint($id);
        $template = $this->get($id);
        if (!$template) {
            return ['success' => false, 'message' => 'قالب یافت نشد.'];
        }

        $new_data = [
            'title' => $template['title'] . ' (کپی)',
            'description' => $template['description'],
            'fields' => $template['fields'],
            'status' => 'inactive',
        ];

        return $this->create($new_data);
    }

    public function get_template_for_product($product_id) {
        $template_id = get_post_meta(absint($product_id), '_ezlens_option_template_id', true);
        if (empty($template_id)) {
            return null;
        }
        return $this->get((int) $template_id);
    }

    public function attach_to_product($product_id, $template_id) {
        $product_id = absint($product_id);
        $template_id = absint($template_id);

        if ($product_id <= 0) {
            return ['success' => false, 'message' => 'شناسه محصول نامعتبر است.'];
        }

        if ($template_id <= 0) {
            delete_post_meta($product_id, '_ezlens_option_template_id');
            return ['success' => true, 'message' => 'قالب از محصول جدا شد.'];
        }

        $template = $this->get($template_id);
        if (!$template) {
            return ['success' => false, 'message' => 'قالب یافت نشد.'];
        }

        update_post_meta($product_id, '_ezlens_option_template_id', $template_id);
        return ['success' => true, 'message' => 'قالب به محصول متصل شد.'];
    }
}

EzLens_Product_Options_Template_Manager::get_instance();
