<?php
/**
 * درخواست‌های AJAX کمپینگ (نسخه کامل با nonce یکپارچه و wp_die)
 * @version 2.6.0
 */
class EzLens_Auth_Campaign_Ajax {

    public static function init() {
        add_action('wp_ajax_ezlens_campaign_load_tab', [__CLASS__, 'load_tab']);
        add_action('wp_ajax_ezlens_campaign_get_audience', [__CLASS__, 'get_audience']);
        add_action('wp_ajax_ezlens_campaign_get_contacts', [__CLASS__, 'get_contacts']);
        add_action('wp_ajax_ezlens_campaign_add_contact', [__CLASS__, 'add_contact']);
        add_action('wp_ajax_ezlens_campaign_delete_contact', [__CLASS__, 'delete_contact']);
        add_action('wp_ajax_ezlens_campaign_get_categories', [__CLASS__, 'get_categories']);
        add_action('wp_ajax_ezlens_campaign_create_group', [__CLASS__, 'create_group']);
        add_action('wp_ajax_ezlens_campaign_get_groups', [__CLASS__, 'get_groups']);
        add_action('wp_ajax_ezlens_campaign_delete_group', [__CLASS__, 'delete_group']);
        add_action('wp_ajax_ezlens_campaign_update_group', [__CLASS__, 'update_group']);
        add_action('wp_ajax_ezlens_campaign_get_group_recipients', [__CLASS__, 'get_group_recipients']);
        add_action('wp_ajax_ezlens_campaign_create', [__CLASS__, 'create']);
        add_action('wp_ajax_ezlens_campaign_send', [__CLASS__, 'send']);
        add_action('wp_ajax_ezlens_campaign_get_list', [__CLASS__, 'get_list']);
        add_action('wp_ajax_ezlens_campaign_get_stats', [__CLASS__, 'get_stats']);
        add_action('wp_ajax_ezlens_campaign_delete', [__CLASS__, 'delete']);
        add_action('wp_ajax_ezlens_campaign_get_track_stats', [__CLASS__, 'get_track_stats']);
        add_action('wp_ajax_ezlens_campaign_get', [__CLASS__, 'get_campaign']);
        add_action('wp_ajax_ezlens_campaign_update', [__CLASS__, 'update_campaign']);
        add_action('wp_ajax_ezlens_campaign_duplicate', [__CLASS__, 'duplicate']);
        add_action('wp_ajax_ezlens_campaign_pause', [__CLASS__, 'pause']);
        add_action('wp_ajax_ezlens_campaign_resume', [__CLASS__, 'resume']);
        add_action('wp_ajax_ezlens_campaign_retry_failed', [__CLASS__, 'retry_failed']);
        add_action('wp_ajax_ezlens_campaign_report', [__CLASS__, 'report']);
    }

    public static function load_tab() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $tab = sanitize_key($_POST['tab'] ?? 'dashboard');
        ob_start();
        self::render_tab($tab);
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
        wp_die();
    }

    private static function render_tab($tab) {
        $base = EZLAUTH_PLUGIN_DIR . 'templates/campaign-tabs/';
        switch ($tab) {
            case 'dashboard': include $base . 'dashboard.php'; break;
            case 'audience': include $base . 'audience.php'; break;
            case 'create': include $base . 'create.php'; break;
            case 'history': include $base . 'history.php'; break;
            case 'settings': include $base . 'settings.php'; break;
            default: echo '<p>بخش یافت نشد.</p>';
        }
    }

    public static function get_audience() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }

        $limit = max(1, min(100, (int) ($_POST['limit'] ?? 20)));
        $offset = max(0, (int) ($_POST['offset'] ?? 0));
        $search = sanitize_text_field($_POST['search'] ?? '');
        $role = sanitize_text_field($_POST['role'] ?? '');
        $date = sanitize_text_field($_POST['registered_after'] ?? '');
        $spent = isset($_POST['min_spent']) ? (float) $_POST['min_spent'] : 0;

        $args = [
            'number' => $limit,
            'offset' => $offset,
            'fields' => ['ID', 'user_login', 'user_email', 'display_name', 'user_registered'],
        ];
        if (!empty($role)) $args['role'] = $role;
        if (!empty($date)) $args['date_query'] = [['after' => $date, 'inclusive' => true]];
        if (!empty($search)) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = ['user_login', 'display_name', 'user_email'];
        }

        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();
        $total_users = $user_query->get_total();
        if (!empty($users)) {
            update_meta_cache('user', wp_list_pluck($users, 'ID'));
        }

        if (!empty($spent) && class_exists('WooCommerce')) {
            $filtered = [];
            foreach ($users as $user) {
                if (wc_get_customer_total_spent($user->ID) >= $spent) $filtered[] = $user;
            }
            $users = $filtered;
            $total_users = count($filtered);
        }

        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'name' => $user->display_name ?: $user->user_login,
                'email' => $user->user_email,
                'phone' => get_user_meta($user->ID, 'billing_phone', true) ?: get_user_meta($user->ID, 'user_phone', true),
                'registered' => $user->user_registered,
            ];
        }

        wp_send_json_success(['users' => $result, 'count' => $total_users]);
        wp_die();
    }

    public static function get_contacts() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }

        $limit = max(1, min(100, (int) ($_POST['limit'] ?? 20)));
        $offset = max(0, (int) ($_POST['offset'] ?? 0));
        $search = sanitize_text_field($_POST['search'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');

        $campaign = EzLens_Auth_Campaign::get_instance();
        $contacts = $campaign->get_contacts($limit, $offset, $search, $category);
        $total = $campaign->count_contacts($search, $category);

        wp_send_json_success(['contacts' => $contacts, 'total' => $total]);
        wp_die();
    }

    public static function add_contact() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }

        $campaign = EzLens_Auth_Campaign::get_instance();
        $data = [
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'email' => sanitize_email($_POST['email'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'category' => sanitize_text_field($_POST['category'] ?? 'عمومی'),
        ];

        if (empty($data['email']) || !is_email($data['email'])) {
            wp_send_json_error('ایمیل معتبر وارد کنید.');
            wp_die();
        }

        $result = $campaign->add_contact($data);
        if ($result['success']) {
            wp_send_json_success(['id' => $result['id'], 'message' => $result['message']]);
        } else {
            wp_send_json_error($result['message']);
        }
        wp_die();
    }

    public static function get_categories() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $campaign = EzLens_Auth_Campaign::get_instance();
        wp_send_json_success(['categories' => $campaign->get_contact_categories()]);
        wp_die();
    }

    public static function create_group() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $campaign = EzLens_Auth_Campaign::get_instance();
        $contact_ids = isset($_POST['contact_ids']) ? json_decode(stripslashes($_POST['contact_ids']), true) : [];
        $data = [
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'description' => sanitize_textarea_field($_POST['description'] ?? ''),
            'type' => sanitize_text_field($_POST['type'] ?? 'custom'),
            'user_filters' => $_POST['user_filters'] ?? '[]',
            'contact_ids' => wp_json_encode($contact_ids),
        ];
        if (empty($data['name'])) {
            wp_send_json_error('نام گروه را وارد کنید.');
            wp_die();
        }
        $result = $campaign->create_group($data);
        if ($result['success']) {
            wp_send_json_success(['id' => $result['id'], 'message' => $result['message']]);
        } else {
            wp_send_json_error($result['message']);
        }
        wp_die();
    }

    public static function get_groups() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $type = sanitize_text_field($_POST['type'] ?? '');
        $search = sanitize_text_field($_POST['search'] ?? '');
        $campaign = EzLens_Auth_Campaign::get_instance();
        wp_send_json_success(['groups' => $campaign->get_groups($type, $search)]);
        wp_die();
    }

    public static function delete_group() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error('شناسه نامعتبر.');
            wp_die();
        }
        $campaign = EzLens_Auth_Campaign::get_instance();
        if ($campaign->delete_group($id)) {
            wp_send_json_success('گروه حذف شد.');
        } else {
            wp_send_json_error('خطا در حذف گروه.');
        }
        wp_die();
    }

    public static function update_group() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error('شناسه نامعتبر.');
            wp_die();
        }
        $data = [];
        if (isset($_POST['name'])) $data['name'] = sanitize_text_field($_POST['name']);
        if (isset($_POST['description'])) $data['description'] = sanitize_textarea_field($_POST['description']);
        if (isset($_POST['contact_ids'])) $data['contact_ids'] = $_POST['contact_ids'];
        if (empty($data)) {
            wp_send_json_error('هیچ داده‌ای برای به‌روزرسانی وجود ندارد.');
            wp_die();
        }
        $campaign = EzLens_Auth_Campaign::get_instance();
        $result = $campaign->update_group($id, $data);
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
        wp_die();
    }

    public static function get_group_recipients() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error('شناسه نامعتبر.');
            wp_die();
        }
        $campaign = EzLens_Auth_Campaign::get_instance();
        $group = $campaign->get_group($id);
        if (!$group) {
            wp_send_json_error('گروه یافت نشد.');
            wp_die();
        }
        $recipients = $campaign->get_group_recipients($id);
        wp_send_json_success(['recipients' => $recipients, 'count' => count($recipients)]);
        wp_die();
    }

    public static function create() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $campaign = EzLens_Auth_Campaign::get_instance();
        $group_ids = isset($_POST['group_ids']) ? json_decode(stripslashes($_POST['group_ids']), true) : [];
        $group_ids = array_filter(array_map('intval', $group_ids));
        if (empty($group_ids)) {
            wp_send_json_error('حداقل یک گروه مخاطب انتخاب کنید.');
            wp_die();
        }
        $data = [
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'type' => in_array(sanitize_key($_POST['type'] ?? 'email'), ['email','sms'], true) ? sanitize_key($_POST['type'] ?? 'email') : 'email',
            'subject' => sanitize_text_field($_POST['subject'] ?? ''),
            'message' => wp_kses_post($_POST['message'] ?? ''),
            'file_attachment' => esc_url_raw($_POST['file_attachment'] ?? ''),
            'group_ids' => $group_ids,
            'status' => !empty($_POST['scheduled_at']) ? 'scheduled' : 'draft',
            'scheduled_at' => !empty($_POST['scheduled_at']) ? wp_date('Y-m-d H:i:s', (int) strtotime(sanitize_text_field($_POST['scheduled_at']))) : null,
        ];
        if (empty($data['name']) || empty($data['message'])) {
            wp_send_json_error('نام و پیام را وارد کنید.');
            wp_die();
        }
        if ($data['type']==='email' && empty($data['subject'])) {
            wp_send_json_error('برای کمپین ایمیلی موضوع الزامی است.');
            wp_die();
        }
        if ($data['type']==='sms' && EzLens_Auth_Settings::get('campaign_sms_enabled')!=='1') {
            wp_send_json_error('SMS Campaign در تنظیمات پیام‌رسانی فعال نشده است.');
            wp_die();
        }
        if ($data['type']==='email' && EzLens_Auth_Settings::get('campaign_email_enabled')!=='1') {
            wp_send_json_error('Email Campaign در تنظیمات پیام‌رسانی فعال نشده است.');
            wp_die();
        }
        $id = $campaign->create_campaign($data);
        if (!$id) {
            wp_send_json_error(['message' => 'خطا در ایجاد کمپین.']);
            wp_die();
        }
        if (isset($_POST['send_now']) && $_POST['send_now'] === '1') {
            $result = $campaign->send_campaign($id);
            if ($result['success']) {
                wp_send_json_success(['id' => $id, 'sent' => $result['sent'] ?? 0, 'total' => $result['total'] ?? 0, 'queued' => !empty($result['queued']), 'message' => $result['message'] ?? '']);
            } else {
                wp_send_json_error(['message'=>'کمپین ایجاد شد اما ارسال ناموفق بود.','campaign_id'=>$id,'send_error'=>$result['message']??'خطای نامشخص']);
            }
        } else {
            wp_send_json_success(['id' => $id, 'message' => !empty($data['scheduled_at']) ? 'کمپین زمان‌بندی شد.' : 'کمپین ایجاد شد.']);
        }
        wp_die();
    }

    public static function send() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error('شناسه نامعتبر.');
            wp_die();
        }
        $campaign = EzLens_Auth_Campaign::get_instance();
        $result = $campaign->send_campaign($id);
        if ($result['success']) {
            wp_send_json_success(['sent' => $result['sent'], 'failed' => $result['failed'], 'total' => $result['total']]);
        } else {
            wp_send_json_error($result['message']);
        }
        wp_die();
    }

    public static function get_list() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $limit = (int) ($_POST['limit'] ?? 20);
        $offset = (int) ($_POST['offset'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? null);
        $search = sanitize_text_field($_POST['search'] ?? '');
        $campaign = EzLens_Auth_Campaign::get_instance();
        $list = $campaign->get_campaigns($status, $search, $limit, $offset);
        $total = $campaign->count_campaigns($status, $search);
        wp_send_json_success(['data' => $list, 'total' => $total]);
        wp_die();
    }

    public static function get_stats() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $campaign = EzLens_Auth_Campaign::get_instance();
        wp_send_json_success($campaign->get_campaign_stats());
        wp_die();
    }

    public static function delete() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error('شناسه نامعتبر.');
            wp_die();
        }
        $campaign = EzLens_Auth_Campaign::get_instance();
        if ($campaign->delete_campaign($id)) {
            wp_send_json_success('کمپین حذف شد.');
        } else {
            wp_send_json_error('خطا در حذف.');
        }
        wp_die();
    }

    private static function guard(){
        check_ajax_referer('ezlens_auth_nonce','nonce');
        if(!current_user_can('manage_options')) wp_send_json_error('دسترسی غیرمجاز');
    }
    public static function get_campaign(){self::guard();$id=(int)($_POST['id']??0);if(!$id)wp_send_json_error('شناسه نامعتبر.');$c=EzLens_Auth_Campaign::get_instance()->get_campaign($id);if(!$c)wp_send_json_error('کمپین یافت نشد.');wp_send_json_success(['campaign'=>$c,'group_ids'=>EzLens_Auth_Campaign::get_instance()->get_campaign_groups($id)]);}
    public static function update_campaign(){self::guard();$id=(int)($_POST['id']??0);$groups=json_decode(stripslashes($_POST['group_ids']??'[]'),true);$r=EzLens_Auth_Campaign::get_instance()->update_campaign($id,['name'=>$_POST['name']??'','type'=>$_POST['type']??'email','subject'=>$_POST['subject']??'','message'=>$_POST['message']??'','file_attachment'=>$_POST['file_attachment']??'','scheduled_at'=>$_POST['scheduled_at']??'','group_ids'=>is_array($groups)?$groups:[]]);$r['success']?wp_send_json_success($r):wp_send_json_error($r);}
    public static function duplicate(){self::guard();$r=EzLens_Auth_Campaign::get_instance()->duplicate_campaign((int)($_POST['id']??0));$r['success']?wp_send_json_success($r):wp_send_json_error($r);}
    public static function pause(){self::guard();$r=EzLens_Auth_Campaign::get_instance()->pause_campaign((int)($_POST['id']??0));$r['success']?wp_send_json_success($r):wp_send_json_error($r);}
    public static function resume(){self::guard();$r=EzLens_Auth_Campaign::get_instance()->resume_campaign((int)($_POST['id']??0));$r['success']?wp_send_json_success($r):wp_send_json_error($r);}
    public static function retry_failed(){self::guard();$r=EzLens_Auth_Campaign::get_instance()->retry_failed((int)($_POST['id']??0));$r['success']?wp_send_json_success($r):wp_send_json_error($r);}
    public static function report(){self::guard();$id=(int)($_POST['id']??0);$r=EzLens_Auth_Campaign::get_instance()->get_campaign_report($id,(int)($_POST['limit']??100),(int)($_POST['offset']??0));wp_send_json_success($r);}

    public static function get_track_stats() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $campaign_id = (int) ($_POST['campaign_id'] ?? 0);
        if (!$campaign_id) {
            wp_send_json_error('شناسه نامعتبر.');
            wp_die();
        }
        $campaign = EzLens_Auth_Campaign::get_instance();
        wp_send_json_success($campaign->get_track_stats($campaign_id));
        wp_die();
    }

    public static function delete_contact() {
        check_ajax_referer('ezlens_auth_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز');
            wp_die();
        }
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error('شناسه نامعتبر.');
            wp_die();
        }
        $campaign = EzLens_Auth_Campaign::get_instance();
        if ($campaign->delete_contact($id)) {
            wp_send_json_success('مخاطب حذف شد.');
        } else {
            wp_send_json_error('خطا در حذف.');
        }
        wp_die();
    }
}
EzLens_Auth_Campaign_Ajax::init();