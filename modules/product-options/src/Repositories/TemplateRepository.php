<?php

namespace EzLens\ProductOptions\Repositories;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Persistence boundary for Product Option templates.
 * Keeps SQL/cache concerns outside the domain/service layer.
 */
final class TemplateRepository {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ezlens_option_templates';
    }

    public function find($id) {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) return null;

        $cache_key = 'ezlens_template_' . $id;
        $cached = wp_cache_get($cache_key, 'ezlens');
        if ($cached !== false) return $cached;

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$row) return null;
        wp_cache_set($cache_key, $row, 'ezlens', 300);
        return $row;
    }

    public function insert(array $data) {
        global $wpdb;
        $result = $wpdb->insert(
            $this->table_name,
            $data,
            ['%s', '%s', '%s', '%s', '%d', '%s', '%s']
        );

        if ($result === false) {
            return ['success' => false, 'message' => 'خطا در ذخیره قالب: ' . $wpdb->last_error];
        }

        return ['success' => true, 'id' => (int) $wpdb->insert_id];
    }

    public function update($id, array $data, array $formats) {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) return ['success' => false, 'message' => 'شناسه قالب نامعتبر است.'];

        $result = $wpdb->update($this->table_name, $data, ['id' => $id], $formats, ['%d']);
        if ($result === false) {
            return ['success' => false, 'message' => 'خطا در به‌روزرسانی قالب: ' . $wpdb->last_error];
        }

        wp_cache_delete('ezlens_template_' . $id, 'ezlens');
        return ['success' => true, 'updated' => (int) $result];
    }

    public function delete($id) {
        global $wpdb;
        $id = absint($id);
        if ($id <= 0) return ['success' => false, 'message' => 'شناسه قالب نامعتبر است.'];

        $result = $wpdb->delete($this->table_name, ['id' => $id], ['%d']);
        if ($result === false) {
            return ['success' => false, 'message' => 'خطا در حذف قالب: ' . $wpdb->last_error];
        }

        wp_cache_delete('ezlens_template_' . $id, 'ezlens');
        return ['success' => true, 'deleted' => (int) $result];
    }

    public function list(array $args) {
        global $wpdb;

        $where = $args['where'] ?? [];
        $orderby = $args['orderby'] ?? 'created_at';
        $order = $args['order'] ?? 'DESC';
        $limit = min(100, max(1, absint($args['limit'] ?? 20)));
        $offset = max(0, absint($args['offset'] ?? 0));
        $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $allowed_orderby = ['id', 'title', 'status', 'created_at', 'updated_at'];
        $orderby = sanitize_key($orderby);
        if (!in_array($orderby, $allowed_orderby, true)) {
            $orderby = 'created_at';
        }

        $order = strtoupper(sanitize_key($order));
        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        $sql = "SELECT * FROM {$this->table_name} {$where_sql} ORDER BY {$orderby} {$order} " .
            $wpdb->prepare('LIMIT %d OFFSET %d', $limit, $offset);
        $items = $wpdb->get_results($sql, ARRAY_A);
        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} {$where_sql}");

        return ['items' => $items, 'total' => $count];
    }

    public function count_connected_products($template_id) {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ezlens_option_template_id' AND meta_value = %d",
            absint($template_id)
        ));
    }

    public function get_product_template_id($product_id) {
        return absint(get_post_meta(absint($product_id), '_ezlens_option_template_id', true));
    }

    public function set_product_template($product_id, $template_id) {
        $product_id = absint($product_id);
        $template_id = absint($template_id);

        if ($template_id <= 0) {
            delete_post_meta($product_id, '_ezlens_option_template_id');
            return;
        }

        update_post_meta($product_id, '_ezlens_option_template_id', $template_id);
    }
}
