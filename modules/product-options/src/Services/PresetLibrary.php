<?php

namespace EzLens\ProductOptions\Services;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Built-in starter templates for Iranian optical and vision-care stores.
 * Presets are immutable definitions; using one creates a normal editable template.
 */
final class PresetLibrary {
    public const VERSION = 1;

    /**
     * Return all built-in presets.
     */
    public function all() {
        return [
            $this->glasses_prescription(),
            $this->contact_lens_prescription(),
            $this->optical_lens(),
            $this->eyeglass_frame(),
            $this->colored_contact_lens(),
            $this->lens_care(),
            $this->accessories(),
            $this->vision_care_product(),
            $this->custom_product(),
        ];
    }

    public function get($slug) {
        $slug = sanitize_key($slug);
        foreach ($this->all() as $preset) {
            if ($preset['slug'] === $slug) return $preset;
        }
        return null;
    }

    private function base($slug, $title, $description, $icon, $category, $fields, $settings = [], $layout = []) {
        return [
            'version' => self::VERSION,
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'icon' => $icon,
            'category' => $category,
            'fields' => $fields,
            'settings' => wp_parse_args($settings, [
                'direction' => 'rtl',
                'locale' => 'fa_IR',
                'currency_display' => 'toman',
            ]),
            'layout' => wp_parse_args($layout, [
                'columns' => 2,
                'gap' => 'medium',
            ]),
        ];
    }

    private function eye_fields($prefix, $label) {
        return [
            $prefix . '_sph' => ['name' => $prefix . '_sph', 'type' => 'number', 'label' => $label . ' — SPH', 'placeholder' => 'مثلاً -2.50', 'step' => '0.25'],
            $prefix . '_cyl' => ['name' => $prefix . '_cyl', 'type' => 'number', 'label' => $label . ' — CYL', 'placeholder' => 'مثلاً -1.25', 'step' => '0.25'],
            $prefix . '_axis' => ['name' => $prefix . '_axis', 'type' => 'number', 'label' => $label . ' — AXIS', 'placeholder' => '۰ تا ۱۸۰', 'min' => 0, 'max' => 180, 'step' => 1],
            $prefix . '_add' => ['name' => $prefix . '_add', 'type' => 'number', 'label' => $label . ' — ADD', 'placeholder' => 'در صورت نیاز', 'step' => '0.25'],
        ];
    }

    private function glasses_prescription() {
        $fields = [
            'prescription_heading' => ['name' => 'prescription_heading', 'type' => 'heading', 'label' => 'مشخصات نسخه عینک'],
        ];
        $fields += $this->eye_fields('od', 'چشم راست (OD)');
        $fields += $this->eye_fields('os', 'چشم چپ (OS)');
        $fields['pd_type'] = [
            'name' => 'pd_type', 'type' => 'select', 'label' => 'نوع PD', 'required' => true,
            'options' => [
                ['value' => 'binocular', 'label' => 'PD دوچشمی'],
                ['value' => 'monocular', 'label' => 'PD تک‌چشمی'],
            ],
        ];
        $fields['pd'] = ['name' => 'pd', 'type' => 'number', 'label' => 'PD', 'placeholder' => 'مثلاً ۶۲', 'min' => 30, 'max' => 90, 'step' => '0.5', 'required' => true];
        $fields['use_type'] = [
            'name' => 'use_type', 'type' => 'select', 'label' => 'کاربرد عینک', 'options' => [
                ['value' => 'distance', 'label' => 'دور'],
                ['value' => 'near', 'label' => 'نزدیک'],
                ['value' => 'computer', 'label' => 'کامپیوتر'],
                ['value' => 'progressive', 'label' => 'تدریجی'],
                ['value' => 'other', 'label' => 'سایر'],
            ],
        ];
        $fields['prescription_note'] = ['name' => 'prescription_note', 'type' => 'textarea', 'label' => 'توضیحات نسخه', 'placeholder' => 'در صورت نیاز توضیحات تکمیلی را وارد کنید.'];
        return $this->base('glasses-prescription', 'نسخه عینک', 'فرم استاندارد ثبت شماره چشم برای سفارش عینک طبی.', '👓', 'نسخه و اندازه‌گیری', $fields);
    }

    private function contact_lens_prescription() {
        $fields = [
            'lens_heading' => ['name' => 'lens_heading', 'type' => 'heading', 'label' => 'مشخصات لنز تماسی'],
            'od_power' => ['name' => 'od_power', 'type' => 'number', 'label' => 'چشم راست — Power', 'placeholder' => 'مثلاً -2.50', 'step' => '0.25'],
            'os_power' => ['name' => 'os_power', 'type' => 'number', 'label' => 'چشم چپ — Power', 'placeholder' => 'مثلاً -2.50', 'step' => '0.25'],
            'bc' => ['name' => 'bc', 'type' => 'number', 'label' => 'BC', 'placeholder' => 'مثلاً ۸.۶', 'step' => '0.1', 'required' => true],
            'dia' => ['name' => 'dia', 'type' => 'number', 'label' => 'DIA', 'placeholder' => 'مثلاً ۱۴.۲', 'step' => '0.1', 'required' => true],
            'lens_type' => ['name' => 'lens_type', 'type' => 'select', 'label' => 'نوع لنز', 'options' => [
                ['value' => 'daily', 'label' => 'یک‌بار مصرف روزانه'],
                ['value' => 'monthly', 'label' => 'ماهانه'],
                ['value' => 'toric', 'label' => 'آستیگمات (Toric)'],
                ['value' => 'multifocal', 'label' => 'مولتی‌فوکال'],
            ]],
            'lens_note' => ['name' => 'lens_note', 'type' => 'textarea', 'label' => 'توضیحات نسخه لنز', 'placeholder' => 'اطلاعات تکمیلی نسخه یا توضیحات اپتومتریست.'],
        ];
        return $this->base('contact-lens-prescription', 'نسخه لنز تماسی', 'فرم ثبت مشخصات اصلی لنز تماسی و شماره چشم.', '👁️', 'لنز تماسی', $fields);
    }

    private function optical_lens() {
        $fields = [
            'lens_kind' => ['name' => 'lens_kind', 'type' => 'select', 'label' => 'نوع عدسی', 'required' => true, 'options' => [
                ['value' => 'single_vision', 'label' => 'تک‌دید'], ['value' => 'bifocal', 'label' => 'دو دید'], ['value' => 'progressive', 'label' => 'تدریجی'], ['value' => 'office', 'label' => 'اداری/کامپیوتر'],
            ]],
            'index' => ['name' => 'index', 'type' => 'select', 'label' => 'ضریب شکست', 'options' => [
                ['value' => '1.56', 'label' => '1.56'], ['value' => '1.60', 'label' => '1.60'], ['value' => '1.67', 'label' => '1.67'], ['value' => '1.74', 'label' => '1.74'],
            ]],
            'coating' => ['name' => 'coating', 'type' => 'checkbox', 'label' => 'پوشش‌ها', 'options' => [
                ['value' => 'antireflective', 'label' => 'آنتی‌رفلکس'], ['value' => 'blue_cut', 'label' => 'فیلتر نور آبی'], ['value' => 'uv', 'label' => 'UV'], ['value' => 'scratch', 'label' => 'ضدخش'],
            ]],
            'photochromic' => ['name' => 'photochromic', 'type' => 'radio', 'label' => 'فتوکرومیک', 'options' => [
                ['value' => 'no', 'label' => 'خیر'], ['value' => 'yes', 'label' => 'بله'],
            ]],
        ];
        return $this->base('optical-lens', 'لنز طبی عینک', 'انتخاب نوع عدسی، ضریب شکست و پوشش‌های قابل سفارش.', '🔍', 'عدسی عینک', $fields);
    }

    private function eyeglass_frame() {
        $fields = [
            'frame_color' => ['name' => 'frame_color', 'type' => 'select', 'label' => 'رنگ فریم', 'options' => [
                ['value' => 'black', 'label' => 'مشکی'], ['value' => 'brown', 'label' => 'قهوه‌ای'], ['value' => 'gold', 'label' => 'طلایی'], ['value' => 'silver', 'label' => 'نقره‌ای'], ['value' => 'transparent', 'label' => 'شفاف'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'frame_size' => ['name' => 'frame_size', 'type' => 'select', 'label' => 'سایز فریم', 'options' => [
                ['value' => 'small', 'label' => 'کوچک'], ['value' => 'medium', 'label' => 'متوسط'], ['value' => 'large', 'label' => 'بزرگ'],
            ]],
            'frame_material' => ['name' => 'frame_material', 'type' => 'select', 'label' => 'جنس فریم', 'options' => [
                ['value' => 'acetate', 'label' => 'استات'], ['value' => 'metal', 'label' => 'فلزی'], ['value' => 'mixed', 'label' => 'ترکیبی'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'frame_shape' => ['name' => 'frame_shape', 'type' => 'select', 'label' => 'فرم فریم', 'options' => [
                ['value' => 'round', 'label' => 'گرد'], ['value' => 'square', 'label' => 'مربعی'], ['value' => 'rectangular', 'label' => 'مستطیلی'], ['value' => 'cat_eye', 'label' => 'کت‌آی'], ['value' => 'aviator', 'label' => 'خلبانی'], ['value' => 'other', 'label' => 'سایر'],
            ]],
        ];
        return $this->base('eyeglass-frame', 'فریم عینک', 'ویژگی‌های قابل انتخاب برای فریم و مدل عینک.', '🕶️', 'فریم و اکسسوری', $fields);
    }

    private function colored_contact_lens() {
        $fields = [
            'color' => ['name' => 'color', 'type' => 'select', 'label' => 'رنگ لنز', 'required' => true, 'options' => [
                ['value' => 'brown', 'label' => 'قهوه‌ای'], ['value' => 'hazel', 'label' => 'عسلی'], ['value' => 'green', 'label' => 'سبز'], ['value' => 'gray', 'label' => 'طوسی'], ['value' => 'blue', 'label' => 'آبی'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'power' => ['name' => 'power', 'type' => 'number', 'label' => 'نمره لنز', 'placeholder' => 'مثلاً -1.50', 'step' => '0.25'],
            'wear_period' => ['name' => 'wear_period', 'type' => 'select', 'label' => 'مدت مصرف', 'options' => [
                ['value' => 'daily', 'label' => 'روزانه'], ['value' => 'monthly', 'label' => 'ماهانه'], ['value' => 'quarterly', 'label' => 'سه‌ماهه'],
            ]],
            'quantity' => ['name' => 'quantity', 'type' => 'number', 'label' => 'تعداد', 'min' => 1, 'step' => 1],
        ];
        return $this->base('colored-contact-lens', 'لنز رنگی', 'قالب ساده و کاربردی برای محصولات لنز رنگی.', '🌈', 'لنز تماسی', $fields);
    }

    private function lens_care() {
        $fields = [
            'volume' => ['name' => 'volume', 'type' => 'select', 'label' => 'حجم', 'options' => [
                ['value' => '60', 'label' => '۶۰ میلی‌لیتر'], ['value' => '120', 'label' => '۱۲۰ میلی‌لیتر'], ['value' => '360', 'label' => '۳۶۰ میلی‌لیتر'], ['value' => '500', 'label' => '۵۰۰ میلی‌لیتر'],
            ]],
            'care_type' => ['name' => 'care_type', 'type' => 'select', 'label' => 'نوع محصول', 'options' => [
                ['value' => 'multipurpose', 'label' => 'محلول چندمنظوره'], ['value' => 'saline', 'label' => 'محلول سالین'], ['value' => 'rewetting', 'label' => 'قطره مرطوب‌کننده'], ['value' => 'case', 'label' => 'جا لنزی'],
            ]],
            'brand' => ['name' => 'brand', 'type' => 'text', 'label' => 'برند', 'placeholder' => 'نام برند'],
        ];
        return $this->base('lens-care', 'محلول و مراقبت لنز', 'برای محلول، قطره، جا لنزی و محصولات مراقبت از لنز.', '🧴', 'مراقبت لنز', $fields);
    }

    private function accessories() {
        $fields = [
            'accessory_type' => ['name' => 'accessory_type', 'type' => 'select', 'label' => 'نوع اکسسوری', 'options' => [
                ['value' => 'case', 'label' => 'جاقابی/قاب عینک'], ['value' => 'cloth', 'label' => 'دستمال عینک'], ['value' => 'chain', 'label' => 'بند عینک'], ['value' => 'tool', 'label' => 'ابزار و لوازم جانبی'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'color' => ['name' => 'color', 'type' => 'select', 'label' => 'رنگ', 'options' => [
                ['value' => 'black', 'label' => 'مشکی'], ['value' => 'white', 'label' => 'سفید'], ['value' => 'brown', 'label' => 'قهوه‌ای'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'model' => ['name' => 'model', 'type' => 'text', 'label' => 'مدل/شناسه', 'placeholder' => 'مدل محصول'],
        ];
        return $this->base('accessories', 'اکسسوری عینک و لنز', 'قالب عمومی برای لوازم جانبی عینک و لنز.', '🧼', 'فریم و اکسسوری', $fields);
    }

    private function vision_care_product() {
        $fields = [
            'product_usage' => ['name' => 'product_usage', 'type' => 'select', 'label' => 'کاربرد محصول', 'options' => [
                ['value' => 'eye_care', 'label' => 'مراقبت از چشم'], ['value' => 'lens_care', 'label' => 'مراقبت از لنز'], ['value' => 'comfort', 'label' => 'راحتی و خشکی چشم'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'usage_note' => ['name' => 'usage_note', 'type' => 'textarea', 'label' => 'راهنمای مصرف/توضیحات', 'placeholder' => 'اطلاعات تکمیلی محصول'],
            'manufacturer' => ['name' => 'manufacturer', 'type' => 'text', 'label' => 'تولیدکننده/برند'],
        ];
        return $this->base('vision-care-product', 'محصولات مرتبط با سلامت بینایی', 'قالب عمومی برای محصولات حوزه مراقبت و سلامت بینایی.', '🩺', 'سلامت بینایی', $fields);
    }

    private function custom_product() {
        $fields = [
            'product_note' => ['name' => 'product_note', 'type' => 'textarea', 'label' => 'توضیحات سفارش', 'placeholder' => 'اطلاعات موردنیاز مشتری برای این محصول را وارد کنید.'],
        ];
        return $this->base('custom-product', 'محصول سفارشی', 'یک نقطه شروع ساده برای محصولاتی که قالب اختصاصی ندارند.', '🛒', 'عمومی', $fields, [], ['columns' => 1]);
    }
}