<?php
/**
 * کلاس مدیریت کدهای تأیید یکبارمصرف (OTP)
 * @version 2.1.2
 */
class EzLens_Auth_OTP {

    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ezlens_otp';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            mobile varchar(15) NOT NULL,
            code varchar(6) NOT NULL,
            action varchar(20) NOT NULL DEFAULT 'login',
            expires_at datetime NOT NULL,
            is_used tinyint(1) NOT NULL DEFAULT 0,
            attempts int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY mobile (mobile),
            KEY code (code),
            KEY expires_at (expires_at),
            KEY is_used (is_used)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    private static function normalize_mobile($mobile) {
        return EzLens_Auth_Helper::normalize_mobile($mobile);
    }

    public static function generate_code() {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function store($mobile, $action = 'login') {
        global $wpdb;
        $table = $wpdb->prefix . 'ezlens_otp';

        $mobile = self::normalize_mobile($mobile);
        if (strlen($mobile) !== 11 || substr($mobile, 0, 2) !== '09') {
            return false;
        }

        $code = self::generate_code();
        $expiry_minutes = (int) EzLens_Auth_Settings::get('otp_expiry_minutes') ?: 2;
        $expires_at = current_time('mysql');
        $expires_at = date('Y-m-d H:i:s', strtotime($expires_at . ' + ' . $expiry_minutes . ' minutes'));

        $inserted = $wpdb->insert($table, [
            'mobile'     => $mobile,
            'code'       => $code,
            'action'     => $action,
            'expires_at' => $expires_at,
            'is_used'    => 0,
            'attempts'   => 0,
        ]);

        if (!$inserted) return false;

        // حذف کدهای قبلی (به جز کد جدید)
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table WHERE mobile = %s AND action = %s AND is_used = 0 AND code != %s",
                $mobile, $action, $code
            )
        );

        return $code;
    }

    public static function verify($mobile, $code, $action = 'login') {
        global $wpdb;
        $table = $wpdb->prefix . 'ezlens_otp';

        $mobile = self::normalize_mobile($mobile);

        $record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE mobile = %s AND code = %s AND action = %s AND is_used = 0 AND expires_at > NOW()",
            $mobile, $code, $action
        ));

        if (!$record) {
            return ['success' => false, 'message' => 'کد نامعتبر یا منقضی شده است.'];
        }

        $wpdb->update(
            $table,
            ['attempts' => $record->attempts + 1],
            ['id' => $record->id],
            ['%d'],
            ['%d']
        );

        $max_attempts = (int) EzLens_Auth_Settings::get('otp_max_attempts') ?: 5;
        if ($record->attempts >= $max_attempts) {
            return ['success' => false, 'message' => 'تعداد تلاش‌ها بیش از حد مجاز.'];
        }

        $wpdb->update(
            $table,
            ['is_used' => 1],
            ['id' => $record->id],
            ['%d'],
            ['%d']
        );

        return ['success' => true, 'message' => 'کد تأیید شد.'];
    }

    public static function is_valid($mobile, $code, $action = 'login') {
        global $wpdb;
        $table = $wpdb->prefix . 'ezlens_otp';
        $mobile = self::normalize_mobile($mobile);

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE mobile = %s AND code = %s AND action = %s AND is_used = 0 AND expires_at > NOW()",
            $mobile, $code, $action
        ));
        return $count > 0;
    }

    public static function clean_expired() {
        global $wpdb;
        $table = $wpdb->prefix . 'ezlens_otp';
        $wpdb->query("DELETE FROM $table WHERE expires_at < NOW() OR is_used = 1");
    }

    public static function get_last_code($mobile, $action = 'login') {
        global $wpdb;
        $table = $wpdb->prefix . 'ezlens_otp';
        $mobile = self::normalize_mobile($mobile);

        return $wpdb->get_var($wpdb->prepare(
            "SELECT code FROM $table WHERE mobile = %s AND action = %s AND is_used = 0 ORDER BY id DESC LIMIT 1",
            $mobile, $action
        ));
    }

    public static function can_request($mobile, $action = 'login') {
        global $wpdb;
        $table = $wpdb->prefix . 'ezlens_otp';
        $mobile = self::normalize_mobile($mobile);

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE mobile = %s AND action = %s AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            $mobile, $action
        ));

        $limit = (int) EzLens_Auth_Settings::get('otp_request_limit') ?: 3;
        return $count < $limit;
    }
}