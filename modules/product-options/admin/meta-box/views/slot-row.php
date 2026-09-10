<?php
/**
 * Single slot row partial (also used as JS template when $is_template = true).
 *
 * Expected vars:
 * - $index (int)
 * - $slot (array) template_id, label, placement, display
 * - $templates (array) list of active templates
 * - $placements (array) value => label
 * - $displays (array) value => label
 * - $is_template (bool) if true, names use __INDEX__ placeholder for JS clone
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

$index       = isset($index) ? (int) $index : 0;
$slot        = is_array($slot ?? null) ? $slot : array();
$templates   = is_array($templates ?? null) ? $templates : array();
$placements  = is_array($placements ?? null) ? $placements : array();
$displays    = is_array($displays ?? null) ? $displays : array();
$is_template = !empty($is_template);

$name_prefix = $is_template ? 'ezlens_slots[__INDEX__]' : 'ezlens_slots[' . $index . ']';

$template_id = absint($slot['template_id'] ?? 0);
$label       = (string) ($slot['label'] ?? '');
$placement   = (string) ($slot['placement'] ?? 'below_price');
$display     = (string) ($slot['display'] ?? 'inline');

$badge = class_exists('EzLens_Product_Options_Helpers')
    ? EzLens_Product_Options_Helpers::to_persian_digits($index + 1)
    : (string) ($index + 1);

$row_class = 'ezlens-slot-row' . ($template_id ? '' : ' is-empty-template');
?>
<div class="<?php echo esc_attr($row_class); ?>" data-index="<?php echo esc_attr((string) $index); ?>">
    <div class="ezlens-slot-top">
        <span class="ezlens-slot-badge"><?php echo esc_html($badge); ?></span>
        <span class="ezlens-slot-title">اسلات پالت</span>
        <div class="ezlens-slot-actions">
            <button type="button" class="ezlens-slot-move-up" title="بالا" aria-label="جابه‌جایی به بالا">↑</button>
            <button type="button" class="ezlens-slot-move-down" title="پایین" aria-label="جابه‌جایی به پایین">↓</button>
            <button type="button" class="ezlens-slot-remove" title="حذف" aria-label="حذف اسلات">×</button>
        </div>
    </div>

    <div class="ezlens-slot-grid">
        <div class="ezlens-slot-field">
            <label>پالت (قالب)</label>
            <select class="ezlens-slot-template" name="<?php echo esc_attr($name_prefix); ?>[template_id]">
                <option value="0">— انتخاب پالت —</option>
                <?php foreach ($templates as $tpl) :
                    if (!is_array($tpl)) {
                        continue;
                    }
                    $tid = absint($tpl['id'] ?? 0);
                    if ($tid <= 0) {
                        continue;
                    }
                    $ttitle = (string) ($tpl['title'] ?? ('#' . $tid));
                    $fc = 0;
                    if (!empty($tpl['fields']) && is_array($tpl['fields'])) {
                        foreach ($tpl['fields'] as $fk => $ff) {
                            if (is_string($fk) && strpos($fk, '_code_') === 0) {
                                continue;
                            }
                            if (is_array($ff)) {
                                $fc++;
                            }
                        }
                    }
                    ?>
                    <option value="<?php echo esc_attr((string) $tid); ?>" <?php selected($template_id, $tid); ?>>
                        <?php
                        echo esc_html($ttitle);
                        if ($fc > 0) {
                            echo ' (' . esc_html(
                                class_exists('EzLens_Product_Options_Helpers')
                                    ? EzLens_Product_Options_Helpers::to_persian_digits($fc) . ' فیلد'
                                    : $fc . ' فیلد'
                            ) . ')';
                        }
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ezlens-slot-field">
            <label>برچسب نمایشی (اختیاری)</label>
            <input type="text" class="ezlens-slot-label" name="<?php echo esc_attr($name_prefix); ?>[label]" value="<?php echo esc_attr($label); ?>" placeholder="مثلاً نسخه لنز" />
        </div>

        <div class="ezlens-slot-field">
            <label>مکان نمایش</label>
            <select class="ezlens-slot-placement" name="<?php echo esc_attr($name_prefix); ?>[placement]">
                <?php foreach ($placements as $val => $text) : ?>
                    <option value="<?php echo esc_attr($val); ?>" <?php selected($placement, $val); ?>><?php echo esc_html($text); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ezlens-slot-field">
            <label>حالت نمایش</label>
            <select class="ezlens-slot-display" name="<?php echo esc_attr($name_prefix); ?>[display]">
                <?php foreach ($displays as $val => $text) : ?>
                    <option value="<?php echo esc_attr($val); ?>" <?php selected($display, $val); ?>><?php echo esc_html($text); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="ezlens-slot-preview<?php echo $template_id ? ' is-open' : ''; ?>">
        <div class="ezlens-slot-preview-title">پیش‌نمایش فیلدهای پالت</div>
        <div class="ezlens-preview-fields">
            <span class="ezlens-preview-empty"><?php echo $template_id ? 'در حال بارگذاری…' : 'پالتی انتخاب نشده است.'; ?></span>
        </div>
    </div>
</div>
