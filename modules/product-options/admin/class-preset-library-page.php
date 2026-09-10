<?php
if (!defined('ABSPATH')) exit;

use EzLens\ProductOptions\Services\PresetLibrary;

/**
 * Admin dashboard for built-in Product Options presets.
 */
final class EzLens_Product_Options_Preset_Library_Page {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'register_menu'], 30);
        add_action('admin_post_ezlens_create_from_preset', [$this, 'create_from_preset']);
    }

    public function register_menu() {
        add_submenu_page(
            'ezlens-product-options',
            'قالب‌های آماده',
            'قالب‌های آماده',
            'manage_options',
            'ezlens-product-options-presets',
            [$this, 'render']
        );

    }

    public function render() {
        if (!current_user_can('manage_options')) return;

        $presets = (new PresetLibrary())->all();
        $notice = isset($_GET['ezlens_preset_created']) ? sanitize_key(wp_unslash($_GET['ezlens_preset_created'])) : '';
        ?>
        <div class="wrap ezlens-preset-page" dir="rtl">
            <style>
                .ezlens-preset-page{max-width:1400px}
                .ezlens-preset-hero{margin:22px 0;padding:28px 30px;border:1px solid #e2e8f0;border-radius:18px;background:#fff;box-shadow:0 8px 30px rgba(15,23,42,.05)}
                .ezlens-preset-hero h1{margin:0 0 8px;font-size:28px;color:#0f172a}
                .ezlens-preset-hero p{margin:0;color:#64748b;font-size:14px;max-width:850px;line-height:1.9}
                .ezlens-preset-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px}
                .ezlens-preset-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;display:flex;flex-direction:column;min-height:235px;box-shadow:0 4px 18px rgba(15,23,42,.035);transition:.18s ease}
                .ezlens-preset-card:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(15,23,42,.08);border-color:#cbd5e1}
                .ezlens-preset-icon{font-size:30px;width:54px;height:54px;display:grid;place-items:center;border-radius:14px;background:#f8fafc;margin-bottom:14px}
                .ezlens-preset-card h2{font-size:17px;margin:0 0 7px;color:#0f172a}
                .ezlens-preset-card p{color:#64748b;font-size:13px;line-height:1.8;margin:0 0 14px;flex:1}
                .ezlens-preset-meta{display:flex;gap:8px;align-items:center;margin-bottom:15px;flex-wrap:wrap}
                .ezlens-preset-badge{display:inline-flex;padding:4px 9px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:11px}
                .ezlens-preset-count{color:#94a3b8;font-size:11px}
                .ezlens-preset-actions{display:flex;gap:8px;align-items:center}
                .ezlens-preset-actions .button-primary{border-radius:9px;padding:4px 14px}
                .ezlens-preset-note{margin-top:18px;padding:14px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;color:#475569;font-size:12px;line-height:1.8}
                @media(max-width:782px){.ezlens-preset-grid{grid-template-columns:1fr}.ezlens-preset-hero{padding:20px}}
            </style>

            <div class="ezlens-preset-hero">
                <h1>🧩 قالب‌های آماده EzLens</h1>
                <p>برای شروع سریع، یکی از قالب‌های آماده مخصوص فروشگاه‌های عینک، لنز و محصولات سلامت بینایی را انتخاب کنید. با «ساخت قالب» یک نسخه قابل ویرایش در قالب‌های شما ساخته می‌شود و قالب آماده اصلی تغییر نمی‌کند.</p>
            </div>

            <?php if ($notice === '1'): ?>
                <div class="notice notice-success is-dismissible"><p>قالب با موفقیت ساخته شد. اکنون می‌توانید آن را ویرایش و به محصولات متصل کنید.</p></div>
            <?php elseif ($notice === '0'): ?>
                <div class="notice notice-error is-dismissible"><p>ساخت قالب آماده انجام نشد.</p></div>
            <?php endif; ?>

            <div class="ezlens-preset-grid">
                <?php foreach ($presets as $preset): ?>
                    <article class="ezlens-preset-card">
                        <div class="ezlens-preset-icon" aria-hidden="true"><?php echo esc_html($preset['icon']); ?></div>
                        <h2><?php echo esc_html($preset['title']); ?></h2>
                        <p><?php echo esc_html($preset['description']); ?></p>
                        <div class="ezlens-preset-meta">
                            <span class="ezlens-preset-badge"><?php echo esc_html($preset['category']); ?></span>
                            <span class="ezlens-preset-count"><?php echo esc_html(count($preset['fields'])); ?> فیلد</span>
                        </div>
                        <div class="ezlens-preset-actions">
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="ezlens_create_from_preset">
                                <input type="hidden" name="preset" value="<?php echo esc_attr($preset['slug']); ?>">
                                <?php wp_nonce_field('ezlens_create_from_preset_' . $preset['slug']); ?>
                                <button type="submit" class="button button-primary">ساخت قالب</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="ezlens-preset-note"><strong>نکته:</strong> قالب‌های نسخه عینک و نسخه لنز صرفاً ابزار ثبت اطلاعات سفارش هستند و جایگزین تشخیص یا تجویز متخصص بینایی نیستند.</div>
        </div>
        <?php
    }

    public function create_from_preset() {
        if (!current_user_can('manage_options')) wp_die('دسترسی غیرمجاز.', 403);

        $slug = sanitize_key(wp_unslash($_POST['preset'] ?? ''));
        if ($slug === '') wp_die('قالب آماده نامعتبر است.', 400);

        check_admin_referer('ezlens_create_from_preset_' . $slug);

        $manager = EzLens_Product_Options_Template_Manager::get_instance();
        $result = $manager->create_from_preset($slug);
        $redirect = admin_url('admin.php?page=ezlens-product-options-presets');

        if (!empty($result['success']) && !empty($result['id'])) {
            $redirect = admin_url('admin.php?page=ezlens-product-options&action=edit&id=' . absint($result['id']));
            $redirect = add_query_arg('ezlens_preset_created', '1', $redirect);
        } else {
            $redirect = add_query_arg('ezlens_preset_created', '0', $redirect);
        }

        wp_safe_redirect($redirect);
        exit;
    }
}

EzLens_Product_Options_Preset_Library_Page::get_instance();
