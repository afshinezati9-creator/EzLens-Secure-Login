<?php
if (!defined('ABSPATH')) exit;

class EzLens_Product_Options_Ajax {
    private static $instance = null;
    private $manager;

    private const ALLOWED_FIELD_TYPES = [
        'text', 'email', 'phone', 'textarea', 'number', 'select', 'radio',
        'checkbox', 'image_select', 'color', 'date', 'time', 'upload',
        'heading', 'divider', 'spacer', 'group', 'html'
    ];

    private const ALLOWED_UPLOAD_MIMES = [
        'jpg|jpeg|jpe' => 'image/jpeg',
        'png'          => 'image/png',
        'gif'          => 'image/gif',
        'webp'         => 'image/webp',
        'pdf'          => 'application/pdf',
        'doc'          => 'application/msword',
        'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->manager = EzLens_Product_Options_Template_Manager::get_instance();

        add_action('wp_ajax_ezlens_save_template', [$this, 'save_template']);
        add_action('wp_ajax_ezlens_duplicate_template', [$this, 'duplicate_template']);
        add_action('wp_ajax_ezlens_get_template_preview', [$this, 'get_template_preview']);
        add_action('wp_ajax_ezlens_upload_file', [$this, 'upload_file']);
        add_action('wp_ajax_nopriv_ezlens_upload_file', [$this, 'upload_file']);
        add_action('wp_ajax_ezlens_delete_template', [$this, 'delete_template']);
    }

    /**
     * ذخیره قالب (افزودن یا ویرایش)
     */
    public function save_template() {
        check_ajax_referer('ezlens_template_editor_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $id = isset($_POST['template_id']) ? absint($_POST['template_id']) : 0;
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $description = sanitize_textarea_field(wp_unslash($_POST['description'] ?? ''));
        $status = sanitize_key(wp_unslash($_POST['status'] ?? 'active'));
        $status = in_array($status, ['active', 'inactive'], true) ? $status : 'active';

        if (empty($title)) {
            wp_send_json_error(['message' => 'عنوان قالب الزامی است.'], 400);
        }

        if ($id > 0 && !$this->manager->get($id)) {
            wp_send_json_error(['message' => 'قالب موردنظر یافت نشد.'], 404);
        }

        $fields_raw = $_POST['fields'] ?? [];
        if (is_string($fields_raw)) {
            $fields_raw = wp_unslash($fields_raw);
            $fields = json_decode($fields_raw, true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                wp_send_json_error(['message' => 'ساختار فیلدهای قالب نامعتبر است.'], 400);
            }
        } else {
            $fields = wp_unslash($fields_raw);
        }

        if (!is_array($fields)) {
            wp_send_json_error(['message' => 'ساختار فیلدهای قالب باید آرایه باشد.'], 400);
        }

        $validation = $this->validate_fields($fields);
        if (!$validation['valid']) {
            wp_send_json_error(['message' => $validation['message']], 400);
        }

        $data = [
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'fields' => $validation['fields'],
        ];

        if ($id > 0) {
            $result = $this->manager->update($id, $data);
            if ($result['success']) {
                wp_send_json_success(['message' => 'قالب با موفقیت به‌روزرسانی شد.', 'id' => $id]);
            }
            wp_send_json_error(['message' => $result['message']], 500);
        }

        $result = $this->manager->create($data);
        if ($result['success']) {
            wp_send_json_success(['message' => 'قالب با موفقیت ایجاد شد.', 'id' => $result['id']]);
        }

        wp_send_json_error(['message' => $result['message']], 500);
    }

    /**
     * اعتبارسنجی و نرمال‌سازی فیلدهای Builder بدون تغییر ساختار فعلی ذخیره‌سازی.
     */
    private function validate_fields(array $fields) {
        $normalized = [];
        $position = 0;

        foreach ($fields as $key => $field) {
            if (strpos((string) $key, '_code_') === 0) {
                continue;
            }

            if (!is_array($field)) {
                return [
                    'valid' => false,
                    'message' => 'ساختار یکی از فیلدهای قالب نامعتبر است.',
                    'fields' => [],
                ];
            }

            $type = sanitize_key($field['type'] ?? '');
            if (!in_array($type, self::ALLOWED_FIELD_TYPES, true)) {
                return [
                    'valid' => false,
                    'message' => 'نوع یکی از فیلدهای قالب پشتیبانی نمی‌شود: ' . $type,
                    'fields' => [],
                ];
            }

            $field_name = sanitize_key($field['name'] ?? $key);
            if ($field_name === '') {
                $field_name = 'field_' . $position;
            }

            $clean = $field;
            $clean['type'] = $type;
            $clean['name'] = $field_name;

            if (isset($field['label'])) {
                $clean['label'] = sanitize_text_field($field['label']);
            }
            if (isset($field['placeholder'])) {
                $clean['placeholder'] = sanitize_text_field($field['placeholder']);
            }
            if (isset($field['description'])) {
                $clean['description'] = sanitize_textarea_field($field['description']);
            }

            if (isset($field['required'])) {
                $clean['required'] = (bool) filter_var($field['required'], FILTER_VALIDATE_BOOLEAN);
            }

            if (isset($field['price'])) {
                if (!is_numeric($field['price']) || (float) $field['price'] < 0) {
                    return [
                        'valid' => false,
                        'message' => 'قیمت یکی از فیلدها نامعتبر است.',
                        'fields' => [],
                    ];
                }
                $clean['price'] = (float) $field['price'];
            }

            if (isset($field['options'])) {
                if (!is_array($field['options'])) {
                    return [
                        'valid' => false,
                        'message' => 'گزینه‌های یکی از فیلدها نامعتبر است.',
                        'fields' => [],
                    ];
                }

                $clean_options = [];
                foreach ($field['options'] as $option_key => $option) {
                    if (is_array($option)) {
                        $option_clean = $option;
                        if (isset($option['label'])) {
                            $option_clean['label'] = sanitize_text_field($option['label']);
                        }
                        if (isset($option['value'])) {
                            $option_clean['value'] = sanitize_text_field($option['value']);
                        }
                        if (isset($option['price'])) {
                            if (!is_numeric($option['price']) || (float) $option['price'] < 0) {
                                return [
                                    'valid' => false,
                                    'message' => 'قیمت یکی از گزینه‌های قالب نامعتبر است.',
                                    'fields' => [],
                                ];
                            }
                            $option_clean['price'] = (float) $option['price'];
                        }
                        if (isset($option['image'])) {
                            $option_clean['image'] = esc_url_raw($option['image']);
                        }
                        $clean_options[$option_key] = $option_clean;
                    } else {
                        $clean_options[$option_key] = sanitize_text_field($option);
                    }
                }
                $clean['options'] = $clean_options;
            }

            if (isset($field['children'])) {
                if (!is_array($field['children'])) {
                    return [
                        'valid' => false,
                        'message' => 'ساختار فیلدهای زیرمجموعه نامعتبر است.',
                        'fields' => [],
                    ];
                }
                $children_validation = $this->validate_fields($field['children']);
                if (!$children_validation['valid']) {
                    return $children_validation;
                }
                $clean['children'] = $children_validation['fields'];
            }

            $normalized[$key] = $clean;
            $position++;
        }

        return [
            'valid' => true,
            'message' => '',
            'fields' => $normalized,
        ];
    }

    /**
     * کپی کردن قالب
     */
    public function duplicate_template() {
        check_ajax_referer('ezlens_template_editor_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $id = isset($_POST['template_id']) ? absint($_POST['template_id']) : 0;
        if (!$id) {
            wp_send_json_error(['message' => 'شناسه قالب نامعتبر است.'], 400);
        }

        $result = $this->manager->duplicate($id);
        if ($result['success']) {
            wp_send_json_success(['message' => 'قالب کپی شد.', 'id' => $result['id']]);
        }
        wp_send_json_error(['message' => $result['message']], 500);
    }

    /**
     * پیش‌نمایش فیلدها (برای متاباکس محصول)
     */
    public function get_template_preview() {
        check_ajax_referer('ezlens_template_preview_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $template_id = isset($_POST['template_id']) ? absint($_POST['template_id']) : 0;
        if (!$template_id) {
            wp_send_json_error(['message' => 'شناسه قالب نامعتبر است.'], 400);
        }

        $template = $this->manager->get($template_id);
        if (!$template) {
            wp_send_json_error(['message' => 'قالب یافت نشد.'], 404);
        }

        $fields = [];
        foreach ((array) $template['fields'] as $key => $field) {
            if (strpos((string) $key, '_code_') === 0 || !is_array($field)) continue;
            $fields[] = [
                'label' => $field['label'] ?? 'فیلد',
                'type' => $field['type'] ?? 'text',
                'required' => !empty($field['required']),
            ];
        }

        wp_send_json_success([
            'fields' => $fields,
            'title' => $template['title'],
            'status' => $template['status'],
        ]);
    }

    /**
     * آپلود فایل با اعتبارسنجی MIME و محدودیت‌های سمت سرور.
     * این endpoint برای فرم‌های مشتری نیز استفاده می‌شود؛ بنابراین nopriv عمداً حفظ شده است.
     */
    public function upload_file() {
        check_ajax_referer('ezlens_po_nonce', 'nonce');

        if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
            wp_send_json_error(['message' => 'فایلی برای آپلود ارسال نشده است.'], 400);
        }

        $file = $_FILES['file'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => 'خطا در آپلود فایل.'], 400);
        }

        $max_size = 5 * 1024 * 1024;
        $size = isset($file['size']) ? (int) $file['size'] : 0;
        if ($size <= 0 || $size > $max_size) {
            wp_send_json_error(['message' => 'حجم فایل باید بیشتر از صفر و حداکثر ۵ مگابایت باشد.'], 400);
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            wp_send_json_error(['message' => 'فایل آپلودشده معتبر نیست.'], 400);
        }

        $filename = sanitize_file_name($file['name'] ?? '');
        if ($filename === '') {
            wp_send_json_error(['message' => 'نام فایل نامعتبر است.'], 400);
        }

        $file['name'] = $filename;
        $filetype = wp_check_filetype_and_ext($file['tmp_name'], $filename, self::ALLOWED_UPLOAD_MIMES);

        if (empty($filetype['ext']) || empty($filetype['type'])) {
            wp_send_json_error(['message' => 'نوع واقعی فایل مجاز نیست.'], 400);
        }

        if (!in_array($filetype['type'], array_values(self::ALLOWED_UPLOAD_MIMES), true)) {
            wp_send_json_error(['message' => 'نوع فایل مجاز نیست.'], 400);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload('file', 0, [], [
            'test_form' => false,
            'mimes' => self::ALLOWED_UPLOAD_MIMES,
        ]);

        if (is_wp_error($attachment_id)) {
            wp_send_json_error(['message' => 'خطا در ذخیره فایل: ' . $attachment_id->get_error_message()], 500);
        }

        $url = wp_get_attachment_url($attachment_id);
        if (!$url) {
            wp_delete_attachment($attachment_id, true);
            wp_send_json_error(['message' => 'آدرس فایل ایجاد نشد.'], 500);
        }

        wp_send_json_success([
            'url' => esc_url_raw($url),
            'filename' => basename($filename),
            'attachment_id' => (int) $attachment_id,
        ]);
    }

    /**
     * حذف قالب (با چک کردن محصولات متصل)
     */
    public function delete_template() {
        check_ajax_referer('ezlens_template_delete_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $id = isset($_POST['template_id']) ? absint($_POST['template_id']) : 0;
        if (!$id) {
            wp_send_json_error(['message' => 'شناسه قالب نامعتبر است.'], 400);
        }

        $result = $this->manager->delete($id);
        if ($result['success']) {
            wp_send_json_success(['message' => $result['message']]);
        }
        wp_send_json_error(['message' => $result['message']], 500);
    }
}

EzLens_Product_Options_Ajax::get_instance();
