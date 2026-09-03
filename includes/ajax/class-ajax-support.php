<?php
/**
 * درخواست‌های AJAX سیستم پشتیبانی (نسخه نهایی با دیباگ)
 * @version 2.6.2
 */
class EzLens_Auth_Ajax_Support {

    public static function init() {
        add_action('wp_ajax_ezlens_support_send_message', [__CLASS__, 'send_message']);
        add_action('wp_ajax_nopriv_ezlens_support_send_message', [__CLASS__, 'send_message']);
        add_action('wp_ajax_ezlens_support_get_user_messages', [__CLASS__, 'get_user_messages']);
        add_action('wp_ajax_ezlens_support_get_user_tickets', [__CLASS__, 'get_user_tickets']);
        add_action('wp_ajax_ezlens_admin_support_get_tickets', [__CLASS__, 'get_tickets']);
        add_action('wp_ajax_ezlens_admin_support_get_messages', [__CLASS__, 'get_messages']);
        add_action('wp_ajax_ezlens_admin_support_reply', [__CLASS__, 'reply']);
        add_action('wp_ajax_ezlens_admin_support_update_status', [__CLASS__, 'update_status']);
        add_action('wp_ajax_ezlens_admin_support_search_users', [__CLASS__, 'search_users']);
        add_action('wp_ajax_ezlens_admin_support_create_ticket', [__CLASS__, 'create_ticket']);
        add_action('wp_ajax_ezlens_support_load_tab', [__CLASS__, 'load_tab']);
    }

    // ============================================================
    // متدهای کاربران (فرانت‌اند)
    // ============================================================

    public static function send_message() {
        check_ajax_referer('ezlens_support_nonce', 'nonce');
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('لطفاً وارد شوید.');
            wp_die();
        }
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        if (empty($message)) {
            wp_send_json_error('متن پیام را وارد کنید.');
            wp_die();
        }
        $support = EzLens_Auth_Support::get_instance();
        $attachment_id = self::handle_upload('attachment');
        $ticket = $support->get_open_ticket($user_id);
        if ($ticket) {
            $support->add_message($ticket->id, $user_id, 'user', $message, $attachment_id);
            $ticket_id = $ticket->id;
        } else {
            $ticket_id = $support->create_ticket($user_id, $message, $attachment_id);
        }
        wp_send_json_success(['ticket_id' => $ticket_id]);
        wp_die();
    }

    public static function get_user_messages() {
        check_ajax_referer('ezlens_support_nonce', 'nonce');
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('لطفاً وارد شوید.');
            wp_die();
        }
        $support = EzLens_Auth_Support::get_instance();
        $ticket = $support->get_open_ticket($user_id);
        $messages = $ticket ? $support->get_messages($ticket->id) : [];
        wp_send_json_success(['messages' => $messages]);
        wp_die();
    }

    public static function get_user_tickets() {
        check_ajax_referer('ezlens_support_nonce', 'nonce');
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('لطفاً وارد شوید.');
            wp_die();
        }
        $support = EzLens_Auth_Support::get_instance();
        $tickets = $support->get_user_tickets($user_id);
        wp_send_json_success(['tickets' => $tickets]);
        wp_die();
    }

    // ============================================================
    // متدهای مدیریت (بک‌اند)
    // ============================================================

    public static function create_ticket() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }

        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        error_log('EzLens Support AJAX: create_ticket - user_id=' . $user_id . ', message=' . $message);

        if (empty($user_id)) {
            wp_send_json_error('لطفاً یک کاربر را انتخاب کنید.');
            wp_die();
        }

        if (empty($message)) {
            wp_send_json_error('لطفاً متن پیام را وارد کنید.');
            wp_die();
        }

        if (!class_exists('EzLens_Auth_Support')) {
            require_once EZLAUTH_PLUGIN_DIR . 'includes/class-support.php';
        }

        $support = EzLens_Auth_Support::get_instance();
        if (!$support) {
            error_log('EzLens Support AJAX: Failed to get EzLens_Auth_Support instance');
            wp_send_json_error('خطا در بارگذاری کلاس پشتیبانی.');
            wp_die();
        }

        $attachment_id = self::handle_upload('attachment');

        $ticket_id = $support->create_ticket($user_id, $message, $attachment_id);

        if ($ticket_id) {
            // ارسال ایمیل به کاربر
            $support->send_notification_email(
                $user_id,
                'تیکت جدید در پشتیبانی',
                "یک تیکت جدید برای شما ایجاد شده است.\n\nمتن پیام: $message\n\nشماره تیکت: #$ticket_id"
            );
            wp_send_json_success([
                'ticket_id' => $ticket_id,
                'message' => 'تیکت با موفقیت ایجاد شد.'
            ]);
        } else {
            error_log('EzLens Support AJAX: create_ticket returned false. Check database or logs.');
            wp_send_json_error('خطا در ایجاد تیکت. لطفاً مجدداً تلاش کنید یا با پشتیبانی تماس بگیرید.');
        }
        wp_die();
    }

    // ============================================================
    // سایر متدها (بدون تغییر)
    // ============================================================

    public static function get_tickets() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $status = sanitize_text_field($_POST['status'] ?? 'all');
        $search = sanitize_text_field($_POST['search'] ?? '');
        $limit = (int) ($_POST['limit'] ?? 20);
        $offset = (int) ($_POST['offset'] ?? 0);

        $support = EzLens_Auth_Support::get_instance();
        $tickets = $support->get_tickets($status, $search, $limit, $offset);
        $total = $support->count_tickets($status, $search);

        wp_send_json_success(['tickets' => $tickets, 'total' => $total]);
        wp_die();
    }

    public static function get_messages() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
        if (!$ticket_id) {
            wp_send_json_error('شناسه تیکت نامعتبر است.');
            wp_die();
        }
        $support = EzLens_Auth_Support::get_instance();
        $ticket = $support->get_ticket($ticket_id);
        $messages = $support->get_messages($ticket_id);

        wp_send_json_success(['ticket' => $ticket, 'messages' => $messages]);
        wp_die();
    }

    public static function reply() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? 'replied');

        if (!$ticket_id || empty($message)) {
            wp_send_json_error('اطلاعات ناقص است.');
            wp_die();
        }

        $support = EzLens_Auth_Support::get_instance();
        $attachment_id = self::handle_upload('attachment');
        $admin_id = get_current_user_id();

        $support->add_message($ticket_id, $admin_id, 'admin', $message, $attachment_id);
        $support->update_status($ticket_id, $status);

        $ticket = $support->get_ticket($ticket_id);
        if ($ticket) {
            $support->send_notification_email(
                $ticket->user_id,
                'پاسخ به تیکت شما #' . $ticket_id,
                "به تیکت شما پاسخ داده شده است.\n\nپاسخ: $message\n\nشماره تیکت: #$ticket_id"
            );
        }

        wp_send_json_success(['status' => $status]);
        wp_die();
    }

    public static function update_status() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        if (!$ticket_id || !in_array($status, ['open', 'replied', 'closed'])) {
            wp_send_json_error('وضعیت نامعتبر است.');
            wp_die();
        }
        $support = EzLens_Auth_Support::get_instance();
        $support->update_status($ticket_id, $status);
        wp_send_json_success(['status' => $status]);
        wp_die();
    }

    public static function search_users() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $search = sanitize_text_field($_POST['search'] ?? '');
        if (strlen($search) < 2) {
            wp_send_json_error('حداقل ۲ کاراکتر وارد کنید.');
            wp_die();
        }
        $users = get_users([
            'search' => '*' . $search . '*',
            'search_columns' => ['user_login', 'display_name', 'user_email'],
            'number' => 10,
            'fields' => ['ID', 'display_name', 'user_login', 'user_email'],
        ]);
        wp_send_json_success(['users' => $users]);
        wp_die();
    }

    public static function load_tab() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $tab = sanitize_key($_POST['tab'] ?? 'list');
        ob_start();
        self::render_tab($tab);
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
        wp_die();
    }

    private static function render_tab($tab) {
        $base = EZLAUTH_PLUGIN_DIR . 'templates/admin-support-tabs/';
        switch ($tab) {
            case 'list': include $base . 'list.php'; break;
            case 'conversation': include $base . 'conversation.php'; break;
            case 'new-ticket': include $base . 'new-ticket.php'; break;
            default: echo '<p>بخش یافت نشد.</p>';
        }
    }

    private static function handle_upload($field) {
        if (empty($_FILES[$field]['name'])) return null;
        $file = $_FILES[$field];
        $max_mb=max(1,(int)EzLens_Auth_Settings::get('support_max_attachment_mb'));
        if ($file['size'] > $max_mb * 1024 * 1024) return null;
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'zip'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) return null;
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $id = media_handle_upload($field, 0);
        return is_wp_error($id) ? null : $id;
    }
}
EzLens_Auth_Ajax_Support::init();