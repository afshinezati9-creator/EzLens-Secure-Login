<?php
/**
 * Plugin Name: صفحه محصول سفارشی
 * Description: نمایش صفحه محصول با طراحی مدرن و مینیمال
 * Version: 2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('custom_product_page', 'custom_product_page_render');

function custom_product_page_render($atts) {
    $product_id = isset($atts['id']) ? intval($atts['id']) : get_the_ID();
    if (!$product_id) {
        return '<p class="product-error">محصولی یافت نشد.</p>';
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        return '<p class="product-error">محصول نامعتبر است.</p>';
    }

    ob_start();

    // دریافت اطلاعات محصول
    $product_name = $product->get_name();
    $product_price = $product->get_price_html();
    $short_desc = $product->get_short_description();
    $full_desc = $product->get_description();
    $sku = $product->get_sku();
    $categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names'));
    $tags = wp_get_post_terms($product_id, 'product_tag', array('fields' => 'names'));
    $rating = $product->get_average_rating();
    $review_count = $product->get_review_count();
    $stock_status = $product->is_in_stock() ? 'موجود' : 'ناموجود';
    $stock_quantity = $product->get_stock_quantity();
    $type = $product->get_type();

    $image_id = $product->get_image_id();
    $main_image = $image_id ? wp_get_attachment_image_url($image_id, 'large') : wc_placeholder_img_src('large');

    $gallery_ids = $product->get_gallery_image_ids();
    $gallery_images = array();
    if ($image_id) {
        $gallery_images[] = $image_id;
    }
    if (!empty($gallery_ids) && is_array($gallery_ids)) {
        foreach ($gallery_ids as $gid) {
            $gallery_images[] = $gid;
        }
    }
    $gallery_images = array_unique($gallery_images);

    $brand = get_post_meta($product_id, '_brand', true);
    $weight = get_post_meta($product_id, '_weight', true);
    $dimensions = get_post_meta($product_id, '_dimensions', true);

    // فیلدهای سفارشی EZLens
    $preset_id = get_post_meta($product_id, '_ezlens_option_template_id', true);
    $custom_fields = array();
    if ($preset_id && class_exists('EzLens_Product_Options_Template_Manager')) {
        $template = EzLens_Product_Options_Template_Manager::get_instance()->get($preset_id);
        if ($template && !empty($template['fields'])) {
            foreach ($template['fields'] as $key => $field) {
                if (strpos($key, '_code_') === 0) {
                    continue;
                }
                $custom_fields[] = array(
                    'key'         => $key,
                    'label'       => isset($field['label']) ? $field['label'] : 'فیلد',
                    'type'        => isset($field['type']) ? $field['type'] : 'text',
                    'placeholder' => isset($field['placeholder']) ? $field['placeholder'] : '',
                    'required'    => !empty($field['required']),
                    'options'     => isset($field['options']) ? $field['options'] : array(),
                );
            }
        }
    }

    $related_ids = wc_get_related_products($product_id, 4);
    $related_products = array();
    foreach ($related_ids as $rel_id) {
        $rel_product = wc_get_product($rel_id);
        if ($rel_product) {
            $related_products[] = $rel_product;
        }
    }

    $comments = get_comments(array(
        'post_id' => $product_id,
        'status'  => 'approve',
        'type'    => 'review',
    ));

    ?>
    <style>
    .custom-product-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
        font-family: 'IRANYekan', 'Vazirmatn', Tahoma, Arial, sans-serif;
        direction: rtl;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.05);
        border: 1px solid #f0f2f5;
        line-height: 1.6;
        color: #1e293b;
    }
    .product-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-bottom: 40px;
        align-items: stretch;
    }
    @media (max-width: 768px) {
        .product-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }
    }
    .product-gallery {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .product-gallery .main-image {
        background: #f8fafc;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #eef2f6;
    }
    .product-gallery .main-image img {
        width: 100%;
        height: auto;
        display: block;
        transition: transform 0.3s ease;
    }
    .product-gallery .main-image img:hover {
        transform: scale(1.02);
    }
    .product-gallery .thumbnails {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
        gap: 8px;
    }
    .product-gallery .thumbnails img {
        width: 100%;
        height: 70px;
        object-fit: cover;
        border-radius: 10px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.2s;
        background: #f8fafc;
    }
    .product-gallery .thumbnails img.active,
    .product-gallery .thumbnails img:hover {
        border-color: #1e293b;
    }
    .product-highlights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 10px;
        margin-top: 8px;
    }
    .highlight-item {
        background: #f8fafc;
        padding: 10px 14px;
        border-radius: 10px;
        text-align: center;
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
        border: 1px solid #eef2f6;
        transition: all 0.2s;
    }
    .highlight-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .share-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        margin-top: 4px;
    }
    .share-buttons .share-label {
        font-weight: 600;
        font-size: 14px;
        color: #1e293b;
        margin-left: 6px;
    }
    .share-buttons a {
        display: inline-block;
        padding: 5px 14px;
        border-radius: 30px;
        background: #f1f5f9;
        color: #1e293b;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.2s, border-color 0.2s;
        border: 1px solid transparent;
    }
    .share-buttons a:hover {
        background: #e2e8f0;
        border-color: #cbd5e1;
    }
    .product-summary {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .product-title {
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        line-height: 1.3;
    }
    .product-price {
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .product-price ins {
        text-decoration: none;
        background: #e2e8f0;
        padding: 2px 12px;
        border-radius: 6px;
        color: #0f172a;
    }
    .product-price del {
        color: #94a3b8;
        font-size: 20px;
        margin-left: 10px;
    }
    .stock-status {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
    }
    .stock-status.in-stock {
        background: #dcfce7;
        color: #166534;
    }
    .stock-status.out-of-stock {
        background: #fee2e2;
        color: #991b1b;
    }
    .product-short-desc {
        background: #f8fafc;
        padding: 16px 20px;
        border-radius: 12px;
        border-right: 4px solid #1e293b;
        font-size: 15px;
        color: #334155;
        line-height: 1.8;
    }
    .product-short-desc p { margin: 0; }
    .cart-form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        margin: 4px 0;
    }
    .cart-form .quantity {
        display: flex;
        align-items: center;
        gap: 4px;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .cart-form .quantity .btn {
        width: 44px;
        height: 48px;
        background: transparent;
        border: none;
        font-size: 22px;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1e293b;
        font-weight: 300;
    }
    .cart-form .quantity .btn:hover {
        background: #eef2f6;
    }
    .cart-form .quantity input.qty {
        width: 60px;
        height: 48px;
        text-align: center;
        border: none;
        background: transparent;
        font-size: 18px;
        font-weight: 600;
        color: #0f172a;
        padding: 0;
        -moz-appearance: textfield;
    }
    .cart-form .quantity input.qty::-webkit-outer-spin-button,
    .cart-form .quantity input.qty::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .cart-form .btn-add,
    .cart-form .btn-buy {
        padding: 12px 32px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .cart-form .btn-add {
        background: #0f172a;
        color: #fff;
        box-shadow: 0 4px 12px rgba(15,23,42,0.15);
    }
    .cart-form .btn-add:hover {
        background: #1e293b;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15,23,42,0.2);
    }
    .cart-form .btn-buy {
        background: #dc2626;
        color: #fff;
        box-shadow: 0 4px 12px rgba(220,38,38,0.15);
    }
    .cart-form .btn-buy:hover {
        background: #b91c1c;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220,38,38,0.25);
    }
    .product-options {
        background: #f8fafc;
        padding: 20px 24px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        margin: 4px 0;
    }
    .product-options .options-title {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 14px;
        letter-spacing: 0.5px;
    }
    .product-options .field-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px 24px;
    }
    @media (max-width: 600px) {
        .product-options .field-group { grid-template-columns: 1fr; }
    }
    .product-options .field-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .product-options .field-item label {
        font-weight: 600;
        font-size: 13px;
        color: #1e293b;
    }
    .product-options .field-item label .required {
        color: #dc2626;
        margin-right: 2px;
    }
    .product-options .field-item input,
    .product-options .field-item select,
    .product-options .field-item textarea {
        padding: 8px 12px;
        border: 1px solid #d1d9e6;
        border-radius: 8px;
        font-size: 14px;
        background: #fff;
        width: 100%;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: inherit;
    }
    .product-options .field-item input:focus,
    .product-options .field-item select:focus,
    .product-options .field-item textarea:focus {
        border-color: #0f172a;
        box-shadow: 0 0 0 3px rgba(15,23,42,0.08);
        outline: none;
    }
    .product-options .total-price {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        font-weight: 700;
        font-size: 18px;
        color: #0f172a;
    }
    .product-tabs {
        margin-top: 40px;
        border-top: 1px solid #e9edf4;
        padding-top: 30px;
    }
    .tabs-nav {
        display: flex;
        gap: 6px;
        border-bottom: 2px solid #e9edf4;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .tabs-nav .tab-btn {
        padding: 10px 22px;
        border: none;
        background: transparent;
        font-weight: 600;
        font-size: 15px;
        color: #64748b;
        cursor: pointer;
        border-radius: 8px 8px 0 0;
        transition: all 0.2s;
        font-family: inherit;
    }
    .tabs-nav .tab-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .tabs-nav .tab-btn.active {
        background: #0f172a;
        color: #fff;
    }
    .tab-panel {
        display: none;
        padding: 8px 0;
        animation: fadeIn 0.3s ease;
    }
    .tab-panel.active { display: block; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .spec-table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
    }
    .spec-table th {
        background: #f1f5f9;
        color: #0f172a;
        padding: 12px 18px;
        text-align: right;
        font-weight: 700;
        font-size: 14px;
    }
    .spec-table td {
        padding: 10px 18px;
        border-bottom: 1px solid #eef2f6;
        font-size: 14px;
    }
    .spec-table tr:last-child td { border-bottom: none; }
    .spec-table tr:nth-child(even) { background: #fafcff; }
    .faq-item {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 10px;
        overflow: hidden;
        transition: border-color 0.2s;
    }
    .faq-item:hover { border-color: #cbd5e1; }
    .faq-item .faq-question {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 20px;
        background: #fafcff;
        cursor: pointer;
        font-weight: 600;
        font-size: 15px;
        user-select: none;
        transition: background 0.2s;
        color: #0f172a;
    }
    .faq-item .faq-question:hover { background: #f1f5f9; }
    .faq-item .faq-question .icon {
        transition: transform 0.3s;
        font-size: 14px;
        color: #64748b;
    }
    .faq-item.open .faq-question .icon { transform: rotate(180deg); }
    .faq-item .faq-answer {
        max-height: 0;
        overflow: hidden;
        padding: 0 20px;
        transition: max-height 0.4s ease, padding 0.3s ease;
    }
    .faq-item.open .faq-answer {
        max-height: 400px;
        padding: 0 20px 18px;
    }
    .faq-item .faq-answer p {
        margin: 0;
        font-size: 14px;
        color: #475569;
        line-height: 1.8;
    }
    .reviews-list {
        list-style: none;
        padding: 0;
        margin: 0 0 24px;
    }
    .reviews-list li {
        padding: 16px 0;
        border-bottom: 1px solid #eef2f6;
    }
    .reviews-list li:last-child { border-bottom: none; }
    .reviews-list .review-author {
        font-weight: 600;
        color: #0f172a;
    }
    .reviews-list .review-rating {
        color: #f59e0b;
        letter-spacing: 1px;
        font-size: 14px;
    }
    .reviews-list .review-content {
        margin: 4px 0 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.7;
    }
    .review-form-wrapper {
        background: #f8fafc;
        padding: 20px 24px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        margin-top: 20px;
    }
    .review-form-wrapper .form-group {
        margin-bottom: 14px;
    }
    .review-form-wrapper label {
        display: block;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 4px;
        color: #1e293b;
    }
    .review-form-wrapper input,
    .review-form-wrapper textarea,
    .review-form-wrapper select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d9e6;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        background: #fff;
        transition: border-color 0.2s;
    }
    .review-form-wrapper input:focus,
    .review-form-wrapper textarea:focus {
        border-color: #0f172a;
        outline: none;
        box-shadow: 0 0 0 3px rgba(15,23,42,0.06);
    }
    .review-form-wrapper .submit-btn {
        background: #0f172a;
        color: #fff;
        border: none;
        padding: 10px 28px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .review-form-wrapper .submit-btn:hover {
        background: #1e293b;
    }
    .related-products {
        margin-top: 48px;
        padding-top: 30px;
        border-top: 1px solid #e9edf4;
    }
    .related-products h3 {
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 20px;
    }
    .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 20px;
    }
    .related-item {
        background: #fafcff;
        border-radius: 14px;
        padding: 16px;
        text-align: center;
        border: 1px solid #eef2f6;
        transition: all 0.25s ease;
    }
    .related-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.04);
        border-color: #cbd5e1;
    }
    .related-item a {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .related-item img {
        max-width: 100%;
        border-radius: 8px;
        margin-bottom: 10px;
        background: #f8fafc;
    }
    .related-item .related-title {
        font-weight: 600;
        font-size: 14px;
        color: #0f172a;
        margin-bottom: 4px;
    }
    .related-item .related-price {
        font-weight: 700;
        color: #0f172a;
        font-size: 16px;
    }
    .product-error {
        color: #dc2626;
        background: #fee2e2;
        padding: 12px 20px;
        border-radius: 10px;
        text-align: center;
        font-weight: 600;
    }
    @media (max-width: 768px) {
        .product-title { font-size: 24px; }
        .product-price { font-size: 24px; }
        .cart-form { flex-direction: column; align-items: stretch; }
        .cart-form .quantity { width: 100%; justify-content: center; }
        .cart-form .btn-add,
        .cart-form .btn-buy { width: 100%; }
        .product-options .field-group { grid-template-columns: 1fr; }
        .related-grid { grid-template-columns: repeat(2, 1fr); }
        .tabs-nav .tab-btn { padding: 8px 14px; font-size: 13px; }
        .product-highlights {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 480px) {
        .custom-product-wrapper { padding: 16px; }
        .product-title { font-size: 20px; }
        .product-price { font-size: 20px; }
        .related-grid { grid-template-columns: 1fr; }
        .product-gallery .thumbnails { grid-template-columns: repeat(3, 1fr); }
        .product-gallery .thumbnails img { height: 60px; }
        .product-highlights {
            grid-template-columns: 1fr;
        }
        .share-buttons .share-label {
            width: 100%;
            margin-bottom: 4px;
        }
    }
    </style>

    <div class="custom-product-wrapper" data-product-id="<?php echo esc_attr($product_id); ?>">
        <div class="product-grid">
            <div class="product-gallery">
                <div class="main-image">
                    <img id="product-main-image" src="<?php echo esc_url($main_image); ?>" alt="<?php echo esc_attr($product_name); ?>" loading="lazy">
                </div>
                <?php if (count($gallery_images) > 1) : ?>
                <div class="thumbnails">
                    <?php foreach ($gallery_images as $img_id) :
                        $thumb_url = wp_get_attachment_image_url($img_id, 'thumbnail');
                        $full_url  = wp_get_attachment_image_url($img_id, 'large');
                        if (!$thumb_url) continue;
                        $is_active = ($img_id == $image_id) ? 'active' : '';
                    ?>
                    <img src="<?php echo esc_url($thumb_url); ?>" data-full="<?php echo esc_url($full_url); ?>" alt="<?php echo esc_attr($product_name); ?>" class="<?php echo esc_attr($is_active); ?>" loading="lazy">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="product-highlights">
                    <div class="highlight-item">ارسال رایگان برای سفارش‌های بالای ۵۰۰ هزار تومان</div>
                    <div class="highlight-item">ضمانت بازگشت وجه تا ۷ روز</div>
                    <div class="highlight-item">پشتیبانی ۲۴ ساعته</div>
                    <div class="highlight-item">محصول اصل و اورجینال</div>
                </div>
                <div class="share-buttons">
                    <span class="share-label">اشتراک‌گذاری:</span>
                    <a href="https://wa.me/?text=<?php echo urlencode($product_name . ' - ' . $product->get_permalink()); ?>" target="_blank" rel="noopener">واتساپ</a>
                    <a href="https://telegram.me/share/url?url=<?php echo urlencode($product->get_permalink()); ?>&text=<?php echo urlencode($product_name); ?>" target="_blank" rel="noopener">تلگرام</a>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($product_name); ?>&url=<?php echo urlencode($product->get_permalink()); ?>" target="_blank" rel="noopener">توییتر</a>
                </div>
            </div>
            <div class="product-summary">
                <h1 class="product-title"><?php echo esc_html($product_name); ?></h1>
                <div class="product-price">
                    <?php echo $product_price; ?>
                    <span class="stock-status <?php echo $product->is_in_stock() ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo $stock_status; ?>
                    </span>
                </div>
                <?php if ($short_desc) : ?>
                <div class="product-short-desc">
                    <?php echo wp_kses_post($short_desc); ?>
                </div>
                <?php endif; ?>
                <form class="cart-form cart" action="" method="post" enctype="multipart/form-data">
                    <?php do_action('woocommerce_before_add_to_cart_button'); ?>
                    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>" />
                    <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />
                    <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
                    <div class="quantity">
                        <button class="btn minus" type="button">-</button>
                        <input type="number" class="qty" name="quantity" value="1" min="1" step="1" aria-label="تعداد">
                        <button class="btn plus" type="button">+</button>
                    </div>
                    <button type="submit" class="btn-add">افزودن به سبد خرید</button>
                    <button type="submit" name="wd-add-to-cart" value="<?php echo esc_attr($product_id); ?>" class="btn-buy">خرید سریع</button>
                    <?php do_action('woocommerce_after_add_to_cart_button'); ?>
                </form>
                <?php if (!empty($custom_fields)) : ?>
                <div class="product-options">
                    <div class="options-title">مشخصات سفارشی</div>
                    <div class="field-group">
                        <?php foreach ($custom_fields as $field) : ?>
                        <div class="field-item">
                            <label>
                                <?php echo esc_html($field['label']); ?>
                                <?php if ($field['required']) : ?>
                                <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            <?php
                            $field_name = 'ezlens_options[' . $field['key'] . ']';
                            if ($field['type'] === 'select') :
                            ?>
                            <select name="<?php echo esc_attr($field_name); ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
                                <option value="">انتخاب کنید...</option>
                                <?php foreach ($field['options'] as $opt) :
                                    $opt_val = isset($opt['value']) ? $opt['value'] : (isset($opt['label']) ? $opt['label'] : '');
                                    $opt_label = isset($opt['label']) ? $opt['label'] : (isset($opt['value']) ? $opt['value'] : '');
                                ?>
                                <option value="<?php echo esc_attr($opt_val); ?>">
                                    <?php echo esc_html($opt_label); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php elseif ($field['type'] === 'textarea') : ?>
                            <textarea name="<?php echo esc_attr($field_name); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" <?php echo $field['required'] ? 'required' : ''; ?>></textarea>
                            <?php else : ?>
                            <input type="<?php echo esc_attr($field['type'] === 'email' ? 'email' : ($field['type'] === 'phone' ? 'tel' : 'text')); ?>" name="<?php echo esc_attr($field_name); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="total-price">
                        <span>قیمت نهایی</span>
                        <span class="price"><?php echo $product_price; ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="product-tabs">
            <div class="tabs-nav">
                <button class="tab-btn active" data-tab="desc">توضیحات</button>
                <button class="tab-btn" data-tab="specs">مشخصات فنی</button>
                <button class="tab-btn" data-tab="faq">پرسش و پاسخ</button>
                <button class="tab-btn" data-tab="reviews">نظرات (<?php echo $review_count; ?>)</button>
            </div>
            <div class="tab-panel active" id="tab-desc">
                <div class="product-description">
                    <?php echo wp_kses_post($full_desc ?: '<p>توضیحاتی برای این محصول ثبت نشده است.</p>'); ?>
                </div>
            </div>
            <div class="tab-panel" id="tab-specs">
                <table class="spec-table">
                    <thead><tr><th>ویژگی</th><th>مقدار</th></tr></thead>
                    <tbody>
                        <tr><td>شناسه محصول</td><td>#<?php echo esc_html($product_id); ?></td></tr>
                        <?php if ($sku) : ?>
                        <tr><td>کد SKU</td><td><?php echo esc_html($sku); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($categories)) : ?>
                        <tr><td>دسته‌بندی</td><td><?php echo esc_html(implode('، ', $categories)); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($tags)) : ?>
                        <tr><td>برچسب‌ها</td><td><?php echo esc_html(implode('، ', $tags)); ?></td></tr>
                        <?php endif; ?>
                        <tr><td>امتیاز</td><td><?php echo number_format($rating, 1); ?> از 5 (<?php echo $review_count; ?> نظر)</td></tr>
                        <tr><td>وضعیت موجودی</td><td><?php echo $stock_status; ?></td></tr>
                        <?php if ($stock_quantity !== null && $product->is_in_stock()) : ?>
                        <tr><td>تعداد موجود</td><td><?php echo $stock_quantity; ?></td></tr>
                        <?php endif; ?>
                        <tr><td>نوع محصول</td><td><?php echo $type; ?></td></tr>
                        <?php if ($brand) : ?>
                        <tr><td>برند</td><td><?php echo esc_html($brand); ?></td></tr>
                        <?php endif; ?>
                        <?php if ($weight) : ?>
                        <tr><td>وزن</td><td><?php echo esc_html($weight); ?> گرم</td></tr>
                        <?php endif; ?>
                        <?php if ($dimensions) : ?>
                        <tr><td>ابعاد</td><td><?php echo esc_html($dimensions); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="tab-panel" id="tab-faq">
                <div class="faq-item open">
                    <div class="faq-question">
                        <span>آیا این محصول گارانتی دارد؟</span>
                        <span class="icon">▾</span>
                    </div>
                    <div class="faq-answer">
                        <p>بله، تمام محصولات ایزی‌لنز دارای گارانتی اصالت و سلامت کالا هستند. برای اطلاعات بیشتر به صفحه <a href="#">گارانتی محصولات</a> مراجعه کنید.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span>آیا امکان مرجوعی کالا وجود دارد؟</span>
                        <span class="icon">▾</span>
                    </div>
                    <div class="faq-answer">
                        <p>بله، طبق قوانین ایزی‌لنز، امکان مرجوعی کالا تا ۷ روز پس از دریافت وجود دارد. برای اطلاعات بیشتر به صفحه <a href="#">بازگرداندن کالا</a> مراجعه کنید.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span>چطور می‌توانم محصول را پیگیری کنم؟</span>
                        <span class="icon">▾</span>
                    </div>
                    <div class="faq-answer">
                        <p>پس از ثبت سفارش، کد رهگیری از طریق پیامک و ایمیل برای شما ارسال می‌شود. همچنین می‌توانید در بخش <a href="#">پیگیری سفارش</a> وضعیت آن را مشاهده کنید.</p>
                    </div>
                </div>
            </div>
            <div class="tab-panel" id="tab-reviews">
                <?php if ($comments) : ?>
                <ul class="reviews-list">
                    <?php foreach ($comments as $comment) : ?>
                    <li>
                        <div class="review-author"><?php echo esc_html($comment->comment_author); ?></div>
                        <div class="review-rating">
                            <?php
                            $rating_val = get_comment_meta($comment->comment_ID, 'rating', true);
                            if ($rating_val) {
                                echo str_repeat('★', intval($rating_val)) . str_repeat('☆', 5 - intval($rating_val));
                            }
                            ?>
                        </div>
                        <div class="review-content"><?php echo esc_html($comment->comment_content); ?></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else : ?>
                <div style="background:#f8fafc; padding:20px; border-radius:12px; text-align:center; border:1px dashed #d1d9e6; color:#64748b;">
                    <p style="margin:0;">هیچ نظری ثبت نشده است.</p>
                    <p style="font-size:13px;">اولین نفری باشید که نظر می‌دهید.</p>
                </div>
                <?php endif; ?>
                <?php if (comments_open($product_id)) : ?>
                <div class="review-form-wrapper">
                    <h4 style="margin-top:0; margin-bottom:16px; font-size:18px;">ارسال نظر</h4>
                    <form method="post" action="<?php echo esc_url(get_permalink($product_id)); ?>#commentform">
                        <?php wp_nonce_field('comment_form', '_wpnonce_comment'); ?>
                        <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr($product_id); ?>" id="comment_post_ID">
                        <input type="hidden" name="comment_type" value="review">
                        <div class="form-group">
                            <label for="author">نام</label>
                            <input type="text" name="author" id="author" required>
                        </div>
                        <div class="form-group">
                            <label for="email">ایمیل</label>
                            <input type="email" name="email" id="email" required>
                        </div>
                        <div class="form-group">
                            <label for="comment">نظر شما</label>
                            <textarea name="comment" id="comment" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="rating">امتیاز</label>
                            <select name="rating" id="rating">
                                <option value="5">۵ ستاره</option>
                                <option value="4">۴ ستاره</option>
                                <option value="3">۳ ستاره</option>
                                <option value="2">۲ ستاره</option>
                                <option value="1">۱ ستاره</option>
                            </select>
                        </div>
                        <button type="submit" class="submit-btn">ارسال نظر</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($related_products)) : ?>
        <div class="related-products">
            <h3>محصولات مرتبط</h3>
            <div class="related-grid">
                <?php foreach ($related_products as $rel) :
                    $rel_img = $rel->get_image_id() ? wp_get_attachment_image_url($rel->get_image_id(), 'thumbnail') : wc_placeholder_img_src('thumbnail');
                ?>
                <div class="related-item">
                    <a href="<?php echo esc_url($rel->get_permalink()); ?>">
                        <img src="<?php echo esc_url($rel_img); ?>" alt="<?php echo esc_attr($rel->get_name()); ?>" loading="lazy">
                        <div class="related-title"><?php echo esc_html($rel->get_name()); ?></div>
                        <div class="related-price"><?php echo $rel->get_price_html(); ?></div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    (function() {
        document.querySelectorAll('.cart-form .quantity .btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var input = this.closest('.quantity').querySelector('.qty');
                var val = parseInt(input.value) || 1;
                if (this.classList.contains('minus')) {
                    if (val > 1) input.value = val - 1;
                } else {
                    input.value = val + 1;
                }
                input.dispatchEvent(new Event('change'));
            });
        });

        var thumbnails = document.querySelectorAll('.product-gallery .thumbnails img');
        var mainImage = document.getElementById('product-main-image');
        if (thumbnails.length && mainImage) {
            thumbnails.forEach(function(img) {
                img.addEventListener('click', function() {
                    var full = this.dataset.full;
                    if (full) {
                        mainImage.src = full;
                    }
                    thumbnails.forEach(function(t) { t.classList.remove('active'); });
                    this.classList.add('active');
                });
            });
        }

        document.querySelectorAll('.tabs-nav .tab-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var tabId = this.dataset.tab;
                document.querySelectorAll('.tabs-nav .tab-btn').forEach(function(b) {
                    b.classList.remove('active');
                });
                document.querySelectorAll('.tab-panel').forEach(function(p) {
                    p.classList.remove('active');
                });
                this.classList.add('active');
                var panel = document.getElementById('tab-' + tabId);
                if (panel) panel.classList.add('active');
            });
        });

        document.querySelectorAll('.faq-item .faq-question').forEach(function(q) {
            q.addEventListener('click', function() {
                var item = this.closest('.faq-item');
                if (item) {
                    item.classList.toggle('open');
                }
            });
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}