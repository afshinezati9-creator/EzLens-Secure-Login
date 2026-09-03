<?php
/**
 * کلاس مدیریت لاگ‌های ورود/خروج – بهینه‌سازی شده با پشتیبانی failed_login (فاز ۶)
 * @version 2.3.3
 */
class EzLens_Auth_Logger {

    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ezlens_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            username varchar(60) NOT NULL,
            action varchar(20) NOT NULL,
            ip varchar(45) NOT NULL,
            user_agent text,
            timestamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY timestamp (timestamp)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * ثبت یک رویداد لاگ
     * @param int $user_id شناسه کاربر (0 برای کاربران ناشناس)
     * @param string $action نوع اقدام (login, logout, failed_login)
     */
    public static function log($user_id, $action) {
        global $wpdb;
        $table = $wpdb->prefix . 'ezlens_logs';

        $user = get_userdata($user_id);
        $username = $user ? $user->user_login : 'unknown';
        $ip = self::get_client_ip();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $wpdb->insert(
            $table,
            [
                'user_id'    => $user_id,
                'username'   => $username,
                'action'     => $action,
                'ip'         => $ip,
                'user_agent' => $user_agent,
                'timestamp'  => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s']
        );

        // پاک‌سازی کش آمار
        wp_cache_delete('ezlens_logs_stats_all', 'ezlens');
        wp_cache_delete('ezlens_logs_stats_' . $action, 'ezlens');
    }

    public static function get_recent($action = null, $limit = 5) {
        global $wpdb;
        $table = $wpdb->prefix . 'ezlens_logs';
        $cache_key = 'ezlens_logs_recent_' . ($action ?: 'all') . '_' . $limit;
        $cached = wp_cache_get($cache_key, 'ezlens');
        if ($cached !== false) {
            return $cached;
        }

        $sql = "SELECT * FROM $table";
        $params = [];
        if ($action) {
            $sql .= " WHERE action = %s";
            $params[] = $action;
        }
        $sql .= " ORDER BY timestamp DESC LIMIT %d";
        $params[] = $limit;

        if (empty($params)) {
            $result = $wpdb->get_results($wpdb->prepare($sql, $limit));
        } else {
            $result = $wpdb->get_results($wpdb->prepare($sql, ...$params));
        }

        wp_cache_set($cache_key, $result, 'ezlens', 60);
        return $result;
    }

    public static function get_stats($action = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'ezlens_logs';
        $cache_key = 'ezlens_logs_stats_' . ($action ?: 'all');
        $cached = wp_cache_get($cache_key, 'ezlens');
        if ($cached !== false) {
            return $cached;
        }

        if ($action) {
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE action = %s",
                $action
            ));
        } else {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        }

        wp_cache_set($cache_key, (int)$count, 'ezlens', 300);
        return (int)$count;
    }

    public static function clean_old($days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'ezlens_logs';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        // پاک‌سازی تمام کش‌های لاگ
        wp_cache_flush_group('ezlens');
    }

    private static function get_client_ip() {
        $ip = '';
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field($ip);
    }
}