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

    /**
     * ایجاد پالت جدید
     */
    public function create($data) {
        global $wpdb;

        $title = sanitize_text_field($data['title'] ?? '');
        $description = sanitize_textarea_field($data['description'] ?? '');
        $fields = isset($data['fields']) ? wp_json_encode($data['fields']) : '[]';
        $status = in_array($data['status'] ?? 'active', ['active', 'inactive']) ? $data['status'] : 'active';
        $created_by = get_current_user_id();

        if (empty($title)) {
            return ['success' => false, 'message' => 'عنوان پالت الزامی است.'];
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
            return ['success' => false, 'message' => 'خطا در ذخیره پالت: ' . $wpdb->last_error];
        }

        return ['success' => true, 'id' => $wpdb->insert_id, 'message' => 'پالت با موفقیت ایجاد شد.'];
    }

    /**
     * به‌روزرسانی پالت
     */
    public function update($id, $data) {
        global $wpdb;

        $id = (int) $id;
        $update_data = [];
        $update_format = [];

        if (isset($data['title'])) {
            $update_data['title'] = sanitize_text_field($data['title']);
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
            $status = in_array($data['status'], ['active', 'inactive']) ? $data['status'] : 'active';
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
            return ['success' => false, 'message' => 'خطا در به‌روزرسانی پالت: ' . $wpdb->last_error];
        }

        wp_cache_delete('ezlens_template_' . $id, 'ezlens');

        return ['success' => true, 'message' => 'پالت با موفقیت به‌روزرسانی شد.'];
    }

    /**
     * دریافت یک پالت با شناسه
     */
    public function get($id) {
        global $wpdb;

        $id = (int) $id;
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

    /**
     * دریافت لیست پالت‌ها با فیلتر و صفحه‌بندی (برای استفاده در template-list.php)
     */
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

    /**
     * تعداد کل پالت‌ها (برای صفحه‌بندی)
     */
    public function count_templates($status = 'all', $search = '') {
        global $wpdb;

        $where = [];

        if ($status !== 'all') {
            $where[] = $wpdb->prepare("status = %s", $status);
        }

        if (!empty($search)) {
            $search_like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = $wpdb->prepare("(title LIKE %s OR description LIKE %s)", $search_like, $search_like);
        }

        $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT COUNT(*) FROM {$this->table_name} {$where_sql}";
        
        return (int) $wpdb->get_var($sql);
    }

    /**
     * دریافت لیست پالت‌ها با فیلتر و صفحه‌بندی (نسخه کامل)
     */
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

        if ($args['status'] !== 'all') {
            $where[] = $wpdb->prepare("status = %s", $args['status']);
        }

        if (!empty($args['search'])) {
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = $wpdb->prepare("(title LIKE %s OR description LIKE %s)", $search, $search);
        }

        $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $order_sql = "ORDER BY {$args['orderby']} {$args['order']}";
        $limit_sql = $wpdb->prepare("LIMIT %d OFFSET %d", $args['limit'], $args['offset']);

        $sql = "SELECT * FROM {$this->table_name} {$where_sql} {$order_sql} {$limit_sql}";
        $results = $wpdb->get_results($sql, ARRAY_A);

        foreach ($results as &$row) {
            $row['fields'] = json_decode($row['fields'], true);
            if (!is_array($row['fields'])) {
                $row['fields'] = [];
            }
        }

        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} {$where_sql}";
        $total = (int) $wpdb->get_var($count_sql);

        return [
            'items' => $results,
            'total' => $total,
        ];
    }

    /**
     * تعداد محصولات متصل به یک پالت
     */
    public function get_connected_products_count($template_id) {
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ezlens_option_template_id' AND meta_value = %d",
            (int) $template_id
        ));
        return (int) $count;
    }

    /**
     * حذف پالت (با چک کردن محصولات متصل)
     */
    public function delete($id) {
        global $wpdb;
        $id = (int) $id;
        
        // بررسی وجود محصولات متصل
        $connected = $this->get_connected_products_count($id);
        if ($connected > 0) {
            return ['success' => false, 'message' => 'این پالت به ' . $connected . ' محصول متصل است. ابتدا اتصال را قطع کنید.'];
        }
        
        $result = $wpdb->delete($this->table_name, ['id' => $id], ['%d']);
        if ($result === false) {
            return ['success' => false, 'message' => 'خطا در حذف پالت: ' . $wpdb->last_error];
        }
        
        wp_cache_delete('ezlens_template_' . $id, 'ezlens');
        return ['success' => true, 'message' => 'پالت با موفقیت حذف شد.'];
    }

    /**
     * کپی کردن پالت
     */
    public function duplicate($id) {
        $template = $this->get($id);
        if (!$template) {
            return ['success' => false, 'message' => 'پالت یافت نشد.'];
        }

        $new_data = [
            'title' => $template['title'] . ' (کپی)',
            'description' => $template['description'],
            'fields' => $template['fields'],
            'status' => 'inactive',
        ];

        return $this->create($new_data);
    }

    /**
     * دریافت پالت متصل به یک محصول خاص
     */
    public function get_template_for_product($product_id) {
        $template_id = get_post_meta($product_id, '_ezlens_option_template_id', true);
        if (empty($template_id)) {
            return null;
        }
        return $this->get((int) $template_id);
    }

    /**
     * اتصال پالت به محصول
     */
    public function attach_to_product($product_id, $template_id) {
        $product_id = (int) $product_id;
        $template_id = (int) $template_id;

        if ($template_id <= 0) {
            delete_post_meta($product_id, '_ezlens_option_template_id');
            return ['success' => true, 'message' => 'پالت از محصول جدا شد.'];
        }

        $template = $this->get($template_id);
        if (!$template) {
            return ['success' => false, 'message' => 'پالت یافت نشد.'];
        }

        update_post_meta($product_id, '_ezlens_option_template_id', $template_id);
        return ['success' => true, 'message' => 'پالت به محصول متصل شد.'];
    }
}

// مقداردهی اولیه
EzLens_Product_Options_Template_Manager::get_instance();