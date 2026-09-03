<?php
if (!defined('ABSPATH')) exit;

class EzLens_Product_Options_MetaBox {
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
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post_product', [$this, 'save_meta_box'], 10, 2);
    }

    /**
     * افزودن متاباکس به صفحه ویرایش محصول
     */
    public function add_meta_box() {
        add_meta_box(
            'ezlens_product_options',
            '🧩 ویژگی‌های اختصاصی محصول',
            [$this, 'render_meta_box'],
            'product',
            'normal',
            'default'
        );
    }

    /**
     * رندر متاباکس
     */
    public function render_meta_box($post) {
        wp_nonce_field('ezlens_product_options_meta', 'ezlens_product_options_nonce');

        $current_template_id = get_post_meta($post->ID, '_ezlens_option_template_id', true);
        $templates = $this->manager->get_list([
            'status' => 'active',
            'limit' => 999,
            'offset' => 0
        ]);

        // استایل‌های مینیمال و مدرن
        ?>
        <style>
            .ezlens-meta-box {
                padding: 4px 0;
            }
            .ezlens-meta-box select {
                width: 100%;
                max-width: 400px;
                padding: 8px 12px;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                font-size: 14px;
                background: #f8fafc;
                transition: all 0.2s;
            }
            .ezlens-meta-box select:focus {
                border-color: #2b6cb0;
                box-shadow: 0 0 0 3px rgba(43,108,176,0.1);
                outline: none;
                background: #fff;
            }
            .ezlens-meta-box .field-preview {
                margin-top: 12px;
                padding: 12px 16px;
                background: #f8fafc;
                border-radius: 8px;
                border: 1px solid #e2e8f0;
                display: none;
            }
            .ezlens-meta-box .field-preview.visible {
                display: block;
            }
            .ezlens-meta-box .field-preview .preview-field {
                padding: 4px 0;
                border-bottom: 1px solid #e2e8f0;
                display: flex;
                justify-content: space-between;
                font-size: 13px;
            }
            .ezlens-meta-box .field-preview .preview-field:last-child {
                border-bottom: none;
            }
            .ezlens-meta-box .preview-label {
                font-weight: 500;
                color: #334155;
            }
            .ezlens-meta-box .preview-type {
                color: #94a3b8;
                font-size: 11px;
            }
            .ezlens-meta-box .no-template {
                color: #94a3b8;
                font-size: 13px;
                padding: 8px 0;
            }
            .ezlens-meta-box .template-badge {
                display: inline-block;
                padding: 2px 10px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 600;
                background: #dbeafe;
                color: #1e40af;
            }
            .ezlens-meta-box .btn-manage {
                margin-left: 10px;
                font-size: 12px;
                padding: 4px 12px;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
                background: #fff;
                color: #2b6cb0;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.2s;
            }
            .ezlens-meta-box .btn-manage:hover {
                background: #f1f5f9;
                border-color: #2b6cb0;
            }
        </style>

        <div class="ezlens-meta-box">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                <label for="ezlens_template_id" style="font-weight:600;font-size:14px;color:#0f172a;">
                    📋 انتخاب پالت ویژگی‌ها:
                </label>
                <a href="<?php echo admin_url('admin.php?page=ezlens-product-options'); ?>" class="btn-manage" target="_blank">
                    مدیریت پالت‌ها
                </a>
            </div>

            <select id="ezlens_template_id" name="ezlens_template_id">
                <option value="0">— هیچ پالتی انتخاب نشده —</option>
                <?php if (!empty($templates['items'])): ?>
                    <?php foreach ($templates['items'] as $template): ?>
                        <option value="<?php echo esc_attr($template['id']); ?>" 
                            <?php selected($current_template_id, $template['id']); ?>>
                            <?php echo esc_html($template['title']); ?> 
                            (<?php echo count($template['fields'] ?? []); ?> فیلد)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <div class="field-preview <?php echo $current_template_id ? 'visible' : ''; ?>" id="template-preview">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-weight:600;font-size:13px;color:#0f172a;">پیش‌نمایش فیلدها:</span>
                    <span class="template-badge" id="template-status">فعال</span>
                </div>
                <div id="template-fields-preview">
                    <?php if ($current_template_id): 
                        $template = $this->manager->get($current_template_id);
                        if ($template && !empty($template['fields'])): ?>
                            <?php foreach ($template['fields'] as $key => $field): ?>
                                <?php if (strpos($key, '_code_') === 0) continue; ?>
                                <div class="preview-field">
                                    <span class="preview-label">
                                        <?php echo esc_html($field['label'] ?? 'فیلد'); ?>
                                        <?php if ($field['required'] ?? false): ?>
                                            <span style="color:#dc2626;">*</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="preview-type"><?php echo esc_html($field['type'] ?? 'text'); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="no-template">این پالت فیلدی ندارد.</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="no-template">هیچ پالتی انتخاب نشده است.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#ezlens_template_id').on('change', function() {
                var selected = $(this).val();
                var $preview = $('#template-preview');
                var $fieldsPreview = $('#template-fields-preview');
                
                if (selected == 0) {
                    $preview.removeClass('visible');
                    $fieldsPreview.html('<span class="no-template">هیچ پالتی انتخاب نشده است.</span>');
                    return;
                }

                // بارگذاری پیش‌نمایش با AJAX
                $.post(ajaxurl, {
                    action: 'ezlens_get_template_preview',
                    nonce: '<?php echo wp_create_nonce("ezlens_template_preview_nonce"); ?>',
                    template_id: selected
                }, function(response) {
                    if (response.success) {
                        $preview.addClass('visible');
                        var html = '';
                        var fields = response.data.fields || [];
                        if (fields.length === 0) {
                            html = '<span class="no-template">این پالت فیلدی ندارد.</span>';
                        } else {
                            fields.forEach(function(field) {
                                html += '<div class="preview-field">';
                                html += '<span class="preview-label">' + escHtml(field.label || 'فیلد') + (field.required ? ' <span style="color:#dc2626;">*</span>' : '') + '</span>';
                                html += '<span class="preview-type">' + escHtml(field.type || 'text') + '</span>';
                                html += '</div>';
                            });
                        }
                        $fieldsPreview.html(html);
                    }
                });
            });

            function escHtml(str) {
                if (!str) return '';
                var div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }
        });
        </script>
        <?php
    }

    /**
     * ذخیره متاباکس
     */
    public function save_meta_box($post_id, $post) {
        if (!isset($_POST['ezlens_product_options_nonce']) || 
            !wp_verify_nonce($_POST['ezlens_product_options_nonce'], 'ezlens_product_options_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $template_id = isset($_POST['ezlens_template_id']) ? (int) $_POST['ezlens_template_id'] : 0;

        if ($template_id > 0) {
            // بررسی وجود پالت
            $template = $this->manager->get($template_id);
            if (!$template) {
                return;
            }
            update_post_meta($post_id, '_ezlens_option_template_id', $template_id);
        } else {
            delete_post_meta($post_id, '_ezlens_option_template_id');
        }
    }
}

EzLens_Product_Options_MetaBox::get_instance();