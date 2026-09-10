<?php
/**
 * Dispatches field rendering to type-specific renderers.
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EzLens_PO_Field_Dispatcher
 */
class EzLens_PO_Field_Dispatcher {

    /**
     * Load all renderer classes once.
     */
    public static function boot() {
        static $booted = false;
        if ($booted) {
            return;
        }
        $booted = true;
        $dir    = EZLAUTH_MODULES_DIR . 'product-options/frontend/renderers/';
        $files  = array(
            'class-field-render-helpers.php',
            'class-text-renderer.php',
            'class-choice-renderer.php',
            'class-image-select-renderer.php',
            'class-color-datetime-renderer.php',
            'class-structural-renderer.php',
            'class-group-renderer.php',
            'class-upload-renderer.php',
        );
        foreach ($files as $file) {
            $path = $dir . $file;
            if (is_readable($path)) {
                require_once $path;
            }
        }
    }

    /**
     * @param string $key
     * @param array  $field
     * @param int    $template_id
     */
    public static function render($key, array $field, $template_id) {
        self::boot();

        $type = sanitize_key($field['type'] ?? 'text');

        switch ($type) {
            case 'text':
            case 'email':
            case 'phone':
            case 'number':
            case 'textarea':
                EzLens_PO_Text_Renderer::render($key, $field, $template_id);
                break;
            case 'select':
            case 'radio':
            case 'checkbox':
                EzLens_PO_Choice_Renderer::render($key, $field, $template_id);
                break;
            case 'image_select':
                EzLens_PO_Image_Select_Renderer::render($key, $field, $template_id);
                break;
            case 'color':
            case 'date':
            case 'time':
                EzLens_PO_Color_Datetime_Renderer::render($key, $field, $template_id);
                break;
            case 'upload':
                $meta = EzLens_PO_Field_Render_Helpers::normalize($field);
                $id   = EzLens_PO_Field_Render_Helpers::field_id($key, $template_id);
                $name = EzLens_PO_Field_Render_Helpers::field_name($key, $template_id);
                EzLens_PO_Field_Render_Helpers::open_wrapper($key, $template_id, $meta);
                EzLens_PO_Field_Render_Helpers::label($meta, $id);
                if (class_exists('EzLens_PO_Upload_Renderer')) {
                    EzLens_PO_Upload_Renderer::render($id, $name, $key, $template_id, $meta['required'], $meta['price']);
                }
                EzLens_PO_Field_Render_Helpers::description($meta);
                EzLens_PO_Field_Render_Helpers::close_wrapper();
                break;
            case 'heading':
            case 'divider':
            case 'spacer':
            case 'html':
                EzLens_PO_Structural_Renderer::render($key, $field, $template_id);
                break;
            case 'group':
                EzLens_PO_Group_Renderer::render(
                    $key,
                    $field,
                    $template_id,
                    array(__CLASS__, 'render')
                );
                break;
            default:
                // Fallback to text.
                $field['type'] = 'text';
                EzLens_PO_Text_Renderer::render($key, $field, $template_id);
        }
    }
}
