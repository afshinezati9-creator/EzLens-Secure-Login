<?php
/**
 * کلاس مدیریت سیستم پشتیبانی و تیکت‌ها (نسخه نهایی با دیباگ)
 * @version 2.6.2
 */
class EzLens_Auth_Support {

    private static $instance = null;
    private $tickets_table;
    private $messages_table;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->tickets_table = $wpdb->prefix . 'ezlens_support_tickets';
        $this->messages_table = $wpdb->prefix . 'ezlens_support_messages';
        add_action('init', [$this, 'create_tables']);
    }

    /**
     * ایجاد جداول دیتابیس در صورت عدم وجود
     */
    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $tickets_sql = "CREATE TABLE IF NOT EXISTS {$this->tickets_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'open',
            subject varchar(255) DEFAULT '',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";

        $messages_sql = "CREATE TABLE IF NOT EXISTS {$this->messages_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) NOT NULL,
            sender_id bigint(20) NOT NULL,
            sender_type varchar(10) NOT NULL DEFAULT 'user',
            message text NOT NULL,
            file_attachment varchar(255) DEFAULT '',
            is_read tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY ticket_id (ticket_id),
            KEY sender_id (sender_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($tickets_sql);
        dbDelta($messages_sql);
    }

    /**
     * ایجاد تیکت جدید (با لاگ‌گیری)
     */
    public function create_ticket($user_id, $message, $attachment_id = null) {
        global $wpdb;
        $subject = mb_substr(strip_tags($message), 0, 50) . '...';

        // لاگ برای دیباگ
        error_log('EzLens Support: create_ticket called with user_id=' . $user_id . ', message=' . $message);

        $result = $wpdb->insert($this->tickets_table, [
            'user_id' => (int) $user_id,
            'status' => 'open',
            'subject' => $subject,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);

        if (!$result) {
            error_log('EzLens Support: Failed to insert ticket. Error: ' . $wpdb->last_error);
            return false;
        }

        $ticket_id = $wpdb->insert_id;
        error_log('EzLens Support: Ticket created with ID: ' . $ticket_id);

        // افزودن پیام اول
        $this->add_message($ticket_id, $user_id, 'user', $message, $attachment_id, false);

        do_action('ezlens_support_ticket_created', (int)$ticket_id);
        if (class_exists('EzLens_Auth_Audit')) EzLens_Auth_Audit::get_instance()->log('support.ticket_created', ['ticket_id'=>(int)$ticket_id], 'ticket', $ticket_id);
        if (class_exists('EzLens_Auth_Notifications')) EzLens_Auth_Notifications::get_instance()->create((int)$user_id, 'تیکت جدید', 'تیکت شما با موفقیت ثبت شد.', 'support');
        return $ticket_id;
    }

    /**
     * افزودن پیام به تیکت
     */
    public function add_message($ticket_id, $sender_id, $sender_type, $message, $attachment_id = null, $notify = true) {
        global $wpdb;
        $file_url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
        $wpdb->insert($this->messages_table, [
            'ticket_id' => $ticket_id,
            'sender_id' => $sender_id,
            'sender_type' => $sender_type,
            'message' => wp_kses_post($message),
            'file_attachment' => $file_url,
            'created_at' => current_time('mysql'),
        ]);
        $wpdb->update($this->tickets_table, ['updated_at' => current_time('mysql')], ['id' => $ticket_id]);
        if ($notify) {
            $ticket = $this->get_ticket($ticket_id);
            if ($sender_type === 'user') {
                if (EzLens_Auth_Settings::get('support_notify_admin_email') === '1') {
                    $admin_email = EzLens_Auth_Settings::get('support_admin_email') ?: get_option('admin_email');
                    if (is_email($admin_email)) wp_mail($admin_email, 'پیام جدید پشتیبانی #' . $ticket_id, wp_strip_all_tags($message));
                }
                $wpdb->update($this->tickets_table, ['status' => 'open'], ['id' => $ticket_id]);
            } elseif ($ticket && !empty($ticket->user_id)) {
                if (class_exists('EzLens_Auth_Notifications')) EzLens_Auth_Notifications::get_instance()->create((int)$ticket->user_id, 'پاسخ جدید پشتیبانی', 'برای تیکت #' . (int)$ticket_id . ' پاسخ جدید ثبت شده است.', 'support');
                if (EzLens_Auth_Settings::get('support_email_notifications') === '1') $this->send_notification_email((int)$ticket->user_id, 'پاسخ جدید پشتیبانی #' . $ticket_id, 'برای تیکت شما پاسخ جدید ثبت شده است.');
            }
        }
    }

    // ============================================================
    // سایر متدها (بدون تغییر)
    // ============================================================

    public function get_tickets($status = null, $search = '', $limit = 20, $offset = 0) {
        global $wpdb;
        $sql = "SELECT t.*, u.display_name, u.user_email, u.user_login 
                FROM {$this->tickets_table} t 
                LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID WHERE 1=1";
        $params = [];
        if ($status && $status !== 'all') {
            $sql .= " AND t.status = %s";
            $params[] = $status;
        }
        if (!empty($search)) {
            $sql .= " AND (u.display_name LIKE %s OR u.user_login LIKE %s OR u.user_email LIKE %s)";
            $s = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }
        $sql .= " ORDER BY t.updated_at DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;
        return $wpdb->get_results($wpdb->prepare($sql, ...$params));
    }

    public function count_tickets($status = null, $search = '') {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$this->tickets_table} t 
                LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID WHERE 1=1";
        $params = [];
        if ($status && $status !== 'all') {
            $sql .= " AND t.status = %s";
            $params[] = $status;
        }
        if (!empty($search)) {
            $sql .= " AND (u.display_name LIKE %s OR u.user_login LIKE %s OR u.user_email LIKE %s)";
            $s = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }
        return (int) ($params ? $wpdb->get_var($wpdb->prepare($sql, ...$params)) : $wpdb->get_var($sql));
    }

    public function get_ticket($ticket_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT t.*, u.display_name, u.user_email, u.user_login 
             FROM {$this->tickets_table} t 
             LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID WHERE t.id = %d",
            $ticket_id
        ));
    }

    public function get_messages($ticket_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->messages_table} WHERE ticket_id = %d ORDER BY created_at ASC",
            $ticket_id
        ));
    }

    public function update_status($ticket_id, $status) {
        global $wpdb;
        return $wpdb->update($this->tickets_table, ['status' => $status, 'updated_at' => current_time('mysql')], ['id' => $ticket_id]);
    }

    public function get_user_tickets($user_id, $limit = 10) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->tickets_table} WHERE user_id = %d ORDER BY updated_at DESC LIMIT %d",
            $user_id,
            $limit
        ));
    }

    public function get_open_ticket($user_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->tickets_table} WHERE user_id = %d AND status = 'open' ORDER BY id DESC LIMIT 1",
            $user_id
        ));
    }

    public function send_notification_email($user_id, $subject, $message) {
        $user = get_userdata($user_id);
        if (!$user) return false;
        $to = $user->user_email;
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        $site_name = get_bloginfo('name');
        $full_message = "سلام {$user->display_name}\n\n{$message}\n\n—\n{$site_name}";
        if (EzLens_Auth_Settings::get('support_email_notifications') !== '1') return false;
        return wp_mail($to, $subject, $full_message, $headers);
    }
}