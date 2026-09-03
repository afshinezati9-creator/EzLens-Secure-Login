<?php
if (!defined('ABSPATH')) exit;

class EzLens_Product_Options_Ajax {
    private static $instance = null;
    private $manager;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->manager = EzLens_Product_Options_Template_Manager::get_instance();
        
        // رجیستر کردن اکشن‌های AJAX
        add_action('wp_ajax_ezlens_save_template', [$this, 'save_template']);
        add_action('wp_ajax_ezlens_duplicate_template', [$this, 'duplicate_template']);
        add_action('wp_ajax_ezlens_get_template_preview', [$this, 'get_template_preview']);
        add_action('wp_ajax_ezlens_upload_file', [$this, 'upload_file']);
        add_action('wp_ajax_nopriv_ezlens_upload_file', [$this, 'upload_file']);
        add_action('wp_ajax_ezlens_delete_template', [$this, 'delete_template']); // ← جدید
    }

    /**
     * ذخیره پالت (افزودن یا ویرایش)
     */
    public function save_template() {
        check_ajax_referer('ezlens_template_editor_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز']);
            return;
        }

        $id = isset($_POST['template_id']) ? (int) $_POST['template_id'] : 0;
        $title = sanitize_text_field($_POST['title'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $status = in_array($_POST['status'] ?? 'active', ['active', 'inactive']) ? $_POST['status'] : 'active';

        // دریافت فیلدها (با چک کردن نوع)
        $fields_raw = $_POST['fields'] ?? '{}';
        
        if (is_string($fields_raw)) {
            $fields_raw = stripslashes($fields_raw);
            $fields = json_decode($fields_raw, true);
        } else {
            $fields = $fields_raw;
        }
        
        if (!is_array($fields)) {
            $fields = [];
        }

        if (empty($title)) {
            wp_send_json_error(['message' => 'عنوان پالت الزامی است.']);
            return;
        }

        $data = [
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'fields' => $fields
        ];

        if ($id > 0) {
            $result = $this->manager->update($id, $data);
            if ($result['success']) {
                wp_send_json_success(['message' => 'پالت با موفقیت به‌روزرسانی شد.', 'id' => $id]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }
        } else {
            $result = $this->manager->create($data);
            if ($result['success']) {
                wp_send_json_success(['message' => 'پالت با موفقیت ایجاد شد.', 'id' => $result['id']]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }
        }
    }

    /**
     * کپی کردن پالت
     */
    public function duplicate_template() {
        check_ajax_referer('ezlens_template_editor_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز']);
            return;
        }

        $id = isset($_POST['template_id']) ? (int) $_POST['template_id'] : 0;
        if (!$id) {
            wp_send_json_error(['message' => 'شناسه پالت نامعتبر است.']);
            return;
        }

        $result = $this->manager->duplicate($id);
        if ($result['success']) {
            wp_send_json_success(['message' => 'پالت کپی شد.', 'id' => $result['id']]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }

    /**
     * پیش‌نمایش فیلدها (برای متاباکس محصول)
     */
    public function get_template_preview() {
        check_ajax_referer('ezlens_template_preview_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز']);
            return;
        }

        $template_id = isset($_POST['template_id']) ? (int) $_POST['template_id'] : 0;
        if (!$template_id) {
            wp_send_json_error(['message' => 'شناسه پالت نامعتبر است.']);
            return;
        }

        $template = $this->manager->get($template_id);
        if (!$template) {
            wp_send_json_error(['message' => 'پالت یافت نشد.']);
            return;
        }

        // فیلدها را برای نمایش آماده کن
        $fields = [];
        foreach ($template['fields'] as $key => $field) {
            if (strpos($key, '_code_') === 0) continue;
            $fields[] = [
                'label' => $field['label'] ?? 'فیلد',
                'type' => $field['type'] ?? 'text',
                'required' => $field['required'] ?? false,
            ];
        }

        wp_send_json_success([
            'fields' => $fields,
            'title' => $template['title'],
            'status' => $template['status']
        ]);
    }

    /**
     * آپلود فایل با نوار پیشرفت
     */
    public function upload_file() {
        check_ajax_referer('ezlens_po_nonce', 'nonce');
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => 'خطا در آپلود فایل.']);
            return;
        }

        $file = $_FILES['file'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_extensions)) {
            wp_send_json_error(['message' => 'نوع فایل مجاز نیست.']);
            return;
        }

        if ($file['size'] > $max_size) {
            wp_send_json_error(['message' => 'حجم فایل بیشتر از ۵ مگابایت است.']);
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload('file', 0);
        if (is_wp_error($attachment_id)) {
            wp_send_json_error(['message' => 'خطا در ذخیره فایل: ' . $attachment_id->get_error_message()]);
            return;
        }

        $url = wp_get_attachment_url($attachment_id);
        wp_send_json_success([
            'url' => $url,
            'filename' => basename($file['name'])
        ]);
    }

    /**
     * حذف پالت (با چک کردن محصولات متصل)
     */
    public function delete_template() {
        check_ajax_referer('ezlens_template_delete_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز']);
            return;
        }

        $id = isset($_POST['template_id']) ? (int) $_POST['template_id'] : 0;
        if (!$id) {
            wp_send_json_error(['message' => 'شناسه پالت نامعتبر است.']);
            return;
        }

        $result = $this->manager->delete($id);
        if ($result['success']) {
            wp_send_json_success(['message' => $result['message']]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
}

// مقداردهی اولیه
EzLens_Product_Options_Ajax::get_instance();