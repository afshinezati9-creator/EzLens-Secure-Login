<?php
/**
 * Persistence boundary for Product Option templates.
 *
 * @package EzLens\ProductOptions\Repositories
 */

namespace EzLens\ProductOptions\Repositories;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Keeps SQL/cache concerns outside the domain/service layer.
 */
final class TemplateRepository {

    /** @var string */
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ezlens_option_templates';
    }

    /**
     * @return string
     */
    public function get_table_name() {
        return $this->table_name;
    }

    /**
     * Case-insensitive table existence check (Windows/WAMP safe).
     *
     * @return bool
     */
    public function table_exists() {
        global $wpdb;
        $found = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND LOWER(TABLE_NAME) = LOWER(%s) LIMIT 1',
                $this->table_name
            )
        );
        if ($found) {
            $this->table_name = $found;
            return true;
        }
        $tables = $wpdb->get_col('SHOW TABLES');
        if (!is_array($tables)) {
            return false;
        }
        foreach ($tables as $t) {
            if (strtolower((string) $t) === strtolower($this->table_name)) {
                $this->table_name = $t;
                return true;
            }
        }
        return false;
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function find($id) {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) {
            return null;
        }

        $cache_key = 'ezlens_template_' . $id;
        $cached    = wp_cache_get($cache_key, 'ezlens');
        if ($cached !== false) {
            return $cached;
        }

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$row) {
            return null;
        }

        wp_cache_set($cache_key, $row, 'ezlens', 300);
        return $row;
    }

    /**
     * @param array $data
     * @return array{success:bool,id?:int,message?:string}
     */
    public function insert(array $data) {
        global $wpdb;
        $result = $wpdb->insert(
            $this->table_name,
            $data,
            array('%s', '%s', '%s', '%s', '%d', '%s', '%s')
        );

        if ($result === false) {
            return array(
                'success' => false,
                'message' => 'خطا در ذخیره قالب: ' . $wpdb->last_error,
            );
        }

        return array(
            'success' => true,
            'id'      => (int) $wpdb->insert_id,
        );
    }

    /**
     * @param int   $id
     * @param array $data
     * @param array $formats
     * @return array{success:bool,updated?:int,message?:string}
     */
    public function update($id, array $data, array $formats) {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) {
            return array('success' => false, 'message' => 'شناسه قالب نامعتبر است.');
        }

        $result = $wpdb->update($this->table_name, $data, array('id' => $id), $formats, array('%d'));
        if ($result === false) {
            return array(
                'success' => false,
                'message' => 'خطا در به‌روزرسانی قالب: ' . $wpdb->last_error,
            );
        }

        wp_cache_delete('ezlens_template_' . $id, 'ezlens');
        return array('success' => true, 'updated' => (int) $result);
    }

    /**
     * @param int $id
     * @return array{success:bool,deleted?:int,message?:string}
     */
    public function delete($id) {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) {
            return array('success' => false, 'message' => 'شناسه قالب نامعتبر است.');
        }

        $result = $wpdb->delete($this->table_name, array('id' => $id), array('%d'));
        if ($result === false) {
            return array(
                'success' => false,
                'message' => 'خطا در حذف قالب: ' . $wpdb->last_error,
            );
        }

        wp_cache_delete('ezlens_template_' . $id, 'ezlens');
        return array('success' => true, 'deleted' => (int) $result);
    }

    /**
     * List templates with structured filters (preferred) or pre-prepared where fragments.
     *
     * @param array $args
     * @return array{items:array,total:int}
     */
    public function list(array $args) {
        global $wpdb;

        $orderby = sanitize_key($args['orderby'] ?? 'created_at');
        $order   = strtoupper(sanitize_key($args['order'] ?? 'DESC'));
        $limit   = min(100, max(1, absint($args['limit'] ?? 20)));
        $offset  = max(0, absint($args['offset'] ?? 0));

        $allowed_orderby = array('id', 'title', 'status', 'created_at', 'updated_at');
        if (!in_array($orderby, $allowed_orderby, true)) {
            $orderby = 'created_at';
        }
        if (!in_array($order, array('ASC', 'DESC'), true)) {
            $order = 'DESC';
        }

        $where_parts = array();
        $params      = array();

        if (isset($args['status'])) {
            $status = sanitize_key($args['status']);
            if ($status !== 'all' && in_array($status, array('active', 'inactive'), true)) {
                $where_parts[] = 'status = %s';
                $params[]      = $status;
            }
        }

        if (!empty($args['search'])) {
            $search        = sanitize_text_field($args['search']);
            $like          = '%' . $wpdb->esc_like($search) . '%';
            $where_parts[] = '(title LIKE %s OR description LIKE %s)';
            $params[]      = $like;
            $params[]      = $like;
        }

        if (!empty($args['where']) && is_array($args['where'])) {
            foreach ($args['where'] as $fragment) {
                if (!is_string($fragment) || $fragment === '') {
                    continue;
                }
                if (preg_match('/;\s*(drop|delete|update|insert|alter|union)\b/i', $fragment)) {
                    continue;
                }
                $where_parts[] = $fragment;
            }
        }

        $where_sql = '';
        if (!empty($where_parts)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_parts);
        }

        $sql = "SELECT * FROM {$this->table_name} {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $params_with_limit = array_merge($params, array($limit, $offset));

        if (!empty($params)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $items = $wpdb->get_results($wpdb->prepare($sql, $params_with_limit), ARRAY_A);
        } else {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $items = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$this->table_name} {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                    $limit,
                    $offset
                ),
                ARRAY_A
            );
        }

        if (!is_array($items)) {
            $items = array();
        }

        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} {$where_sql}";
        if (!empty($params)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $count = (int) $wpdb->get_var($wpdb->prepare($count_sql, $params));
        } else {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $count = (int) $wpdb->get_var($count_sql);
        }

        return array(
            'items' => $items,
            'total' => $count,
        );
    }

    /**
     * @param int $template_id
     * @return int
     */
    public function count_connected_products($template_id) {
        global $wpdb;
        $template_id = absint($template_id);
        if ($template_id <= 0) {
            return 0;
        }

        $like = '%"template_id":' . $template_id . '%';

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta}
                 WHERE (meta_key = %s AND meta_value = %s)
                    OR (meta_key = %s AND meta_value LIKE %s)",
                '_ezlens_option_template_id',
                (string) $template_id,
                '_ezlens_option_slots',
                $like
            )
        );
    }

    /**
     * @param int $product_id
     * @return int
     */
    public function get_product_template_id($product_id) {
        $product_id = absint($product_id);

        if (class_exists('\EzLens\ProductOptions\Services\ProductSlotsService')) {
            $slots = (new \EzLens\ProductOptions\Services\ProductSlotsService($this))->get_slots($product_id);
            if (!empty($slots[0]['template_id'])) {
                return absint($slots[0]['template_id']);
            }
        }

        return absint(get_post_meta($product_id, '_ezlens_option_template_id', true));
    }

    /**
     * @param int $product_id
     * @param int $template_id 0 = detach
     */
    public function set_product_template($product_id, $template_id) {
        $product_id  = absint($product_id);
        $template_id = absint($template_id);

        if (class_exists('\EzLens\ProductOptions\Services\ProductSlotsService')) {
            $slots_service = new \EzLens\ProductOptions\Services\ProductSlotsService($this);
            if ($template_id <= 0) {
                $slots_service->save_slots($product_id, array(), true);
                return;
            }
            $slots_service->save_slots(
                $product_id,
                array(
                    array(
                        'template_id' => $template_id,
                        'placement'   => 'below_price',
                        'display'     => 'inline',
                        'order'       => 1,
                    ),
                ),
                true
            );
            return;
        }

        if ($template_id <= 0) {
            delete_post_meta($product_id, '_ezlens_option_template_id');
            return;
        }

        update_post_meta($product_id, '_ezlens_option_template_id', $template_id);
    }
}
