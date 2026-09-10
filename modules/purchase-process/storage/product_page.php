<?php
/**
 * عنوان نمایشی: صفحه محصول حرفه‌ای EzLens
 * نام فایل: custom-product-page
 * مسئولیت: نمایش صفحه محصول ووکامرس با طراحی مدرن و سازگار با Woodmart
 * شورت‌کد: [custom_product_page]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| جلوگیری از تعریف مجدد
|--------------------------------------------------------------------------
*/

if ( ! function_exists( 'ezp_custom_product_page_render' ) ) {

	add_shortcode( 'custom_product_page', 'ezp_custom_product_page_render' );

	function ezp_custom_product_page_render( $atts = array() ) {

		/*
		|--------------------------------------------------------------------------
		| بررسی ووکامرس
		|--------------------------------------------------------------------------
		*/

		if ( ! function_exists( 'wc_get_product' ) ) {
			return '<div class="ezp-product-error">ووکامرس در دسترس نیست.</div>';
		}

		/*
		|--------------------------------------------------------------------------
		| پیدا کردن محصول جاری
		|--------------------------------------------------------------------------
		|
		| اولویت:
		| 1. محصولی که در صفحه فعلی است
		| 2. global $product ووکامرس
		| 3. post ID
		|
		*/

		global $product;

		$product_id = 0;

		if ( $product instanceof WC_Product ) {
			$product_id = $product->get_id();
		}

		if ( ! $product_id ) {
			$product_id = get_queried_object_id();
		}

		if ( ! $product_id ) {
			$product_id = get_the_ID();
		}

		if ( ! $product_id ) {
			return '<div class="ezp-product-error">محصولی برای نمایش پیدا نشد.</div>';
		}

		$product = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_visible() ) {
			return '<div class="ezp-product-error">این محصول قابل نمایش نیست.</div>';
		}

		/*
		|--------------------------------------------------------------------------
		| اطلاعات محصول
		|--------------------------------------------------------------------------
		*/

		$product_name = $product->get_name();
		$product_url  = $product->get_permalink();

		$main_image_id = $product->get_image_id();

		$main_image = $main_image_id
			? wp_get_attachment_image_url( $main_image_id, 'large' )
			: wc_placeholder_img_src( 'large' );

		$gallery_ids = $product->get_gallery_image_ids();

		$gallery = array();

		if ( $main_image_id ) {
			$gallery[] = $main_image_id;
		}

		if ( ! empty( $gallery_ids ) ) {
			foreach ( $gallery_ids as $gallery_id ) {
				$gallery[] = $gallery_id;
			}
		}

		$gallery = array_unique( $gallery );

		/*
		|--------------------------------------------------------------------------
		| قیمت
		|--------------------------------------------------------------------------
		*/

		$price_html = $product->get_price_html();

		/*
		|--------------------------------------------------------------------------
		| موجودی
		|--------------------------------------------------------------------------
		*/

		$is_in_stock = $product->is_in_stock();

		$stock_class = $is_in_stock
			? 'is-in-stock'
			: 'is-out-of-stock';

		$stock_text = $is_in_stock
			? 'موجود در انبار'
			: 'ناموجود';

		/*
		|--------------------------------------------------------------------------
		| امتیاز
		|--------------------------------------------------------------------------
		*/

		$rating       = (float) $product->get_average_rating();
		$review_count = (int) $product->get_review_count();

		/*
		|--------------------------------------------------------------------------
		| دسته‌بندی
		|--------------------------------------------------------------------------
		*/

		$categories = wp_get_post_terms(
			$product_id,
			'product_cat',
			array(
				'fields' => 'all',
			)
		);

		/*
		|--------------------------------------------------------------------------
		| SKU
		|--------------------------------------------------------------------------
		*/

		$sku = $product->get_sku();

		/*
		|--------------------------------------------------------------------------
		| توضیحات
		|--------------------------------------------------------------------------
		*/

		$short_description = $product->get_short_description();
		$description       = $product->get_description();

		/*
		|--------------------------------------------------------------------------
		| ویژگی‌های استاندارد ووکامرس
		|--------------------------------------------------------------------------
		*/

		$attributes = $product->get_attributes();

		/*
		|--------------------------------------------------------------------------
		| محصولات مرتبط
		|--------------------------------------------------------------------------
		*/

		$related_ids = wc_get_related_products(
			$product_id,
			4
		);

		/*
		|--------------------------------------------------------------------------
		| قیمت قابل نمایش برای Structured Data
		|--------------------------------------------------------------------------
		*/

		$numeric_price = $product->get_price();

		/*
		|--------------------------------------------------------------------------
		| خروجی
		|--------------------------------------------------------------------------
		*/

		ob_start();

		?>

		<style>

		/* =========================================================
		   EZLENS PRODUCT PAGE
		========================================================= */

		.ezp-product-page {

			--ezp-primary: #111827;
			--ezp-primary-soft: #f3f4f6;
			--ezp-accent: #2563eb;
			--ezp-success: #15803d;
			--ezp-danger: #dc2626;
			--ezp-warning: #d97706;

			--ezp-text: #111827;
			--ezp-muted: #6b7280;

			--ezp-border: #e5e7eb;
			--ezp-bg: #ffffff;
			--ezp-soft: #f8fafc;

			--ezp-radius-sm: 10px;
			--ezp-radius-md: 16px;
			--ezp-radius-lg: 22px;

			--ezp-shadow:
				0 10px 35px rgba(15, 23, 42, .06);

			direction: rtl;

			width: 100%;
			max-width: 1320px;

			margin: 0 auto;

			padding: 28px 20px 70px;

			font-family:
				IRANYekan,
				Vazirmatn,
				Tahoma,
				Arial,
				sans-serif;

			color: var(--ezp-text);

			line-height: 1.8;

			box-sizing: border-box;

		}

		.ezp-product-page *,
		.ezp-product-page *::before,
		.ezp-product-page *::after {

			box-sizing: border-box;

		}


		/* =========================================================
		   BREADCRUMB
		========================================================= */

		.ezp-breadcrumb {

			display: flex;

			align-items: center;

			gap: 8px;

			flex-wrap: wrap;

			margin-bottom: 22px;

			font-size: 12px;

			color: var(--ezp-muted);

		}

		.ezp-breadcrumb a {

			color: var(--ezp-muted);

			text-decoration: none;

			transition: .2s ease;

		}

		.ezp-breadcrumb a:hover {

			color: var(--ezp-accent);

		}

		.ezp-breadcrumb-separator {

			color: #cbd5e1;

		}


		/* =========================================================
		   MAIN GRID
		========================================================= */

		.ezp-product-main {

			display: grid;

			grid-template-columns:
				minmax(0, 1.05fr)
				minmax(360px, .95fr);

			gap: 55px;

			align-items: start;

		}


		/* =========================================================
		   GALLERY
		========================================================= */

		.ezp-product-gallery {

			position: relative;

			min-width: 0;

		}

		.ezp-main-image {

			position: relative;

			display: flex;

			align-items: center;

			justify-content: center;

			width: 100%;

			min-height: 520px;

			background: #f8fafc;

			border: 1px solid var(--ezp-border);

			border-radius: var(--ezp-radius-lg);

			overflow: hidden;

		}

		.ezp-main-image img {

			display: block;

			width: 100%;

			height: 520px;

			object-fit: contain;

			transition:
				transform .45s cubic-bezier(.2,.8,.2,1);

		}

		.ezp-main-image:hover img {

			transform: scale(1.025);

		}


		/* =========================================================
		   SALE BADGE
		========================================================= */

		.ezp-sale-badge {

			position: absolute;

			top: 18px;

			right: 18px;

			z-index: 3;

			display: inline-flex;

			align-items: center;

			justify-content: center;

			padding: 7px 12px;

			border-radius: 999px;

			background: #111827;

			color: #fff;

			font-size: 11px;

			font-weight: 700;

		}


		/* =========================================================
		   THUMBNAILS
		========================================================= */

		.ezp-thumbnails {

			display: grid;

			grid-template-columns:
				repeat(auto-fill, minmax(82px, 1fr));

			gap: 10px;

			margin-top: 12px;

		}

		.ezp-thumbnail {

			position: relative;

			display: block;

			width: 100%;

			height: 82px;

			padding: 0;

			border: 1px solid var(--ezp-border);

			background: #fff;

			border-radius: 12px;

			overflow: hidden;

			cursor: pointer;

			transition:

				border-color .2s ease,

				transform .2s ease,

				box-shadow .2s ease;

		}

		.ezp-thumbnail:hover {

			transform: translateY(-2px);

			border-color: #cbd5e1;

		}

		.ezp-thumbnail.active {

			border-color: var(--ezp-accent);

			box-shadow:
				0 0 0 2px rgba(37,99,235,.08);

		}

		.ezp-thumbnail img {

			width: 100%;

			height: 100%;

			object-fit: cover;

			display: block;

		}


		/* =========================================================
		   PRODUCT SUMMARY
		========================================================= */

		.ezp-product-summary {

			min-width: 0;

			position: relative;

		}

		.ezp-product-summary-inner {

			position: sticky;

			top: 30px;

		}


		/* =========================================================
		   CATEGORY
		========================================================= */

		.ezp-product-category {

			display: flex;

			flex-wrap: wrap;

			gap: 6px;

			margin-bottom: 12px;

		}

		.ezp-product-category a {

			display: inline-flex;

			padding: 5px 10px;

			background: var(--ezp-primary-soft);

			border-radius: 999px;

			color: #4b5563;

			text-decoration: none;

			font-size: 11px;

			font-weight: 600;

		}


		/* =========================================================
		   TITLE
		========================================================= */

		.ezp-product-title {

			margin: 0 0 15px;

			font-size: clamp(24px, 3vw, 38px);

			line-height: 1.35;

			font-weight: 800;

			letter-spacing: -.5px;

			color: var(--ezp-text);

		}


		/* =========================================================
		   RATING
		========================================================= */

		.ezp-product-rating {

			display: flex;

			align-items: center;

			flex-wrap: wrap;

			gap: 10px;

			margin-bottom: 20px;

		}

		.ezp-stars {

			color: #f59e0b;

			font-size: 15px;

			letter-spacing: 1px;

		}

		.ezp-rating-number {

			font-size: 13px;

			font-weight: 700;

		}

		.ezp-review-link {

			color: var(--ezp-muted);

			text-decoration: none;

			font-size: 12px;

		}


		/* =========================================================
		   PRICE BOX
		========================================================= */

		.ezp-price-box {

			display: flex;

			align-items: center;

			justify-content: space-between;

			gap: 15px;

			flex-wrap: wrap;

			padding: 18px 20px;

			margin-bottom: 18px;

			background: var(--ezp-soft);

			border: 1px solid var(--ezp-border);

			border-radius: var(--ezp-radius-md);

		}

		.ezp-product-price {

			font-size: 27px;

			font-weight: 800;

			line-height: 1.3;

		}

		.ezp-product-price ins {

			text-decoration: none;

			color: var(--ezp-accent);

		}

		.ezp-product-price del {

			color: #9ca3af;

			font-size: 16px;

			font-weight: 500;

			margin-left: 7px;

		}


		/* =========================================================
		   STOCK
		========================================================= */

		.ezp-stock {

			display: inline-flex;

			align-items: center;

			gap: 7px;

			padding: 7px 11px;

			border-radius: 999px;

			font-size: 11px;

			font-weight: 700;

			white-space: nowrap;

		}

		.ezp-stock::before {

			content: '';

			width: 7px;

			height: 7px;

			border-radius: 50%;

			background: currentColor;

		}

		.ezp-stock.is-in-stock {

			color: var(--ezp-success);

			background: #ecfdf3;

		}

		.ezp-stock.is-out-of-stock {

			color: var(--ezp-danger);

			background: #fef2f2;

		}


		/* =========================================================
		   SHORT DESCRIPTION
		========================================================= */

		.ezp-short-description {

			padding: 18px 0;

			margin-bottom: 5px;

			color: #4b5563;

			font-size: 14px;

			line-height: 2;

		}

		.ezp-short-description p:last-child {

			margin-bottom: 0;

		}


		/* =========================================================
		   PRODUCT META
		========================================================= */

		.ezp-product-meta {

			display: grid;

			grid-template-columns:
				repeat(2, minmax(0,1fr));

			gap: 10px;

			margin: 15px 0 20px;

		}

		.ezp-meta-item {

			display: flex;

			align-items: center;

			justify-content: space-between;

			gap: 10px;

			padding: 10px 12px;

			background: #fff;

			border: 1px solid var(--ezp-border);

			border-radius: 10px;

			font-size: 11px;

		}

		.ezp-meta-label {

			color: var(--ezp-muted);

		}

		.ezp-meta-value {

			font-weight: 700;

			color: var(--ezp-text);

			text-align: left;

			direction: ltr;

		}


		/* =========================================================
		   WOOCOMMERCE FORM
		========================================================= */

		.ezp-buy-area {

			padding: 20px;

			background: #fff;

			border: 1px solid var(--ezp-border);

			border-radius: var(--ezp-radius-md);

			box-shadow: var(--ezp-shadow);

		}

		.ezp-buy-area form.cart {

			display: flex !important;

			align-items: stretch;

			flex-wrap: wrap;

			gap: 10px;

			margin: 0 !important;

		}


		/* =========================================================
		   WOOCOMMERCE QUANTITY
		========================================================= */

		.ezp-buy-area .quantity {

			display: flex;

			align-items: center;

			margin: 0 !important;

			min-width: 125px;

			border: 1px solid var(--ezp-border);

			border-radius: 12px;

			overflow: hidden;

			background: #f8fafc;

		}

		.ezp-buy-area .quantity input.qty {

			width: 55px !important;

			height: 48px !important;

			margin: 0 !important;

			padding: 0 !important;

			border: 0 !important;

			background: transparent !important;

			box-shadow: none !important;

			text-align: center;

			font-weight: 700;

		}


		/* =========================================================
		   ADD TO CART
		========================================================= */

		.ezp-buy-area .single_add_to_cart_button {

			flex: 1 1 220px;

			min-height: 50px !important;

			margin: 0 !important;

			border: 0 !important;

			border-radius: 12px !important;

			background: var(--ezp-primary) !important;

			color: #fff !important;

			font-family: inherit !important;

			font-size: 14px !important;

			font-weight: 800 !important;

			box-shadow: none !important;

			transition:

				transform .2s ease,

				box-shadow .2s ease,

				background .2s ease;

		}

		.ezp-buy-area .single_add_to_cart_button:hover {

			transform: translateY(-2px);

			background: #000 !important;

			box-shadow:
				0 12px 25px rgba(17,24,39,.15) !important;

		}

		.ezp-buy-area .single_add_to_cart_button:disabled {

			opacity: .55;

			cursor: not-allowed;

			transform: none;

		}


		/* =========================================================
		   WOOCOMMERCE VARIATIONS
		========================================================= */

		.ezp-buy-area form.variations_form {

			display: block !important;

		}

		.ezp-buy-area table.variations {

			width: 100% !important;

			border: 0 !important;

			margin-bottom: 18px !important;

		}

		.ezp-buy-area table.variations tr {

			display: flex;

			flex-direction: column;

			align-items: stretch;

			gap: 7px;

			margin-bottom: 14px;

		}

		.ezp-buy-area table.variations th.label {

			padding: 0 !important;

			text-align: right !important;

		}

		.ezp-buy-area table.variations th.label label {

			font-size: 12px;

			font-weight: 800;

		}

		.ezp-buy-area table.variations td.value {

			padding: 0 !important;

		}

		.ezp-buy-area table.variations select {

			width: 100% !important;

			min-height: 46px;

			border: 1px solid var(--ezp-border) !important;

			border-radius: 11px !important;

			padding: 0 13px !important;

			font-family: inherit !important;

			background: #fff !important;

		}


		/* =========================================================
		   INFORMATION STRIP
		========================================================= */

		.ezp-service-grid {

			display: grid;

			grid-template-columns:
				repeat(4, minmax(0,1fr));

			gap: 10px;

			margin-top: 15px;

		}

		.ezp-service {

			display: flex;

			align-items: center;

			gap: 8px;

			min-height: 55px;

			padding: 10px;

			background: var(--ezp-soft);

			border: 1px solid var(--ezp-border);

			border-radius: 12px;

			font-size: 10px;

			font-weight: 700;

			line-height: 1.5;

		}

		.ezp-service-icon {

			display: inline-flex;

			align-items: center;

			justify-content: center;

			width: 30px;

			height: 30px;

			flex: 0 0 30px;

			border-radius: 9px;

			background: #fff;

			border: 1px solid var(--ezp-border);

			font-size: 14px;

		}


		/* =========================================================
		   TABS
		========================================================= */

		.ezp-product-details {

			margin-top: 60px;

			border-top: 1px solid var(--ezp-border);

			padding-top: 35px;

		}

		.ezp-tabs-nav {

			display: flex;

			align-items: center;

			gap: 5px;

			overflow-x: auto;

			scrollbar-width: none;

			border-bottom: 1px solid var(--ezp-border);

		}

		.ezp-tabs-nav::-webkit-scrollbar {

			display: none;

		}

		.ezp-tab-button {

			flex: 0 0 auto;

			position: relative;

			padding: 14px 18px;

			border: 0;

			background: transparent;

			font-family: inherit;

			color: var(--ezp-muted);

			font-size: 13px;

			font-weight: 700;

			cursor: pointer;

		}

		.ezp-tab-button::after {

			content: '';

			position: absolute;

			bottom: -1px;

			right: 15px;

			left: 15px;

			height: 2px;

			background: var(--ezp-accent);

			transform: scaleX(0);

			transition: .25s ease;

		}

		.ezp-tab-button.active {

			color: var(--ezp-text);

		}

		.ezp-tab-button.active::after {

			transform: scaleX(1);

		}


		.ezp-tab-panel {

			display: none;

			padding: 30px 0;

			animation: ezpFadeIn .3s ease;

		}

		.ezp-tab-panel.active {

			display: block;

		}

		@keyframes ezpFadeIn {

			from {

				opacity: 0;

				transform: translateY(8px);

			}

			to {

				opacity: 1;

				transform: translateY(0);

			}

		}


		/* =========================================================
		   DESCRIPTION
		========================================================= */

		.ezp-description {

			max-width: 900px;

			color: #374151;

			font-size: 14px;

			line-height: 2.1;

		}

		.ezp-description img {

			max-width: 100%;

			height: auto;

		}


		/* =========================================================
		   ATTRIBUTES
		========================================================= */

		.ezp-attributes {

			width: 100%;

			border: 1px solid var(--ezp-border);

			border-radius: 16px;

			overflow: hidden;

		}

		.ezp-attribute-row {

			display: grid;

			grid-template-columns: 230px 1fr;

			border-bottom: 1px solid var(--ezp-border);

		}

		.ezp-attribute-row:last-child {

			border-bottom: 0;

		}

		.ezp-attribute-name {

			padding: 14px 18px;

			background: var(--ezp-soft);

			font-size: 12px;

			font-weight: 800;

		}

		.ezp-attribute-value {

			padding: 14px 18px;

			font-size: 12px;

			color: #4b5563;

		}


		/* =========================================================
		   RELATED PRODUCTS
		========================================================= */

		.ezp-related {

			margin-top: 55px;

		}

		.ezp-related-heading {

			display: flex;

			align-items: center;

			justify-content: space-between;

			margin-bottom: 18px;

		}

		.ezp-related-heading h2 {

			margin: 0;

			font-size: 21px;

			font-weight: 800;

		}

		.ezp-related-grid {

			display: grid;

			grid-template-columns:
				repeat(4, minmax(0,1fr));

			gap: 15px;

		}

		.ezp-related-card {

			display: block;

			background: #fff;

			border: 1px solid var(--ezp-border);

			border-radius: 16px;

			padding: 12px;

			text-decoration: none;

			color: inherit;

			transition:

				transform .25s ease,

				box-shadow .25s ease,

				border-color .25s ease;

		}

		.ezp-related-card:hover {

			transform: translateY(-4px);

			border-color: #d1d5db;

			box-shadow: var(--ezp-shadow);

		}

		.ezp-related-image {

			width: 100%;

			aspect-ratio: 1 / 1;

			border-radius: 12px;

			background: var(--ezp-soft);

			overflow: hidden;

			margin-bottom: 10px;

		}

		.ezp-related-image img {

			width: 100%;

			height: 100%;

			object-fit: contain;

			display: block;

		}

		.ezp-related-title {

			font-size: 12px;

			font-weight: 700;

			line-height: 1.6;

			margin-bottom: 5px;

		}

		.ezp-related-price {

			font-size: 13px;

			font-weight: 800;

		}


		/* =========================================================
		   ERROR
		========================================================= */

		.ezp-product-error {

			max-width: 900px;

			margin: 40px auto;

			padding: 20px;

			border-radius: 14px;

			background: #fef2f2;

			border: 1px solid #fecaca;

			color: #991b1b;

			text-align: center;

			font-family: Tahoma, sans-serif;

		}


		/* =========================================================
		   RESPONSIVE
		========================================================= */

		@media (max-width: 1100px) {

			.ezp-product-main {

				gap: 35px;

				grid-template-columns:
					minmax(0, 1fr)
					minmax(320px, .9fr);

			}

			.ezp-main-image,
			.ezp-main-image img {

				height: 460px;

				min-height: 460px;

			}

			.ezp-service-grid {

				grid-template-columns:
					repeat(2,1fr);

			}

		}


		@media (max-width: 900px) {

			.ezp-product-main {

				grid-template-columns: 1fr;

			}

			.ezp-product-summary-inner {

				position: static;

			}

			.ezp-main-image,
			.ezp-main-image img {

				height: 500px;

				min-height: 500px;

			}

			.ezp-related-grid {

				grid-template-columns:
					repeat(3,1fr);

			}

		}


		@media (max-width: 650px) {

			.ezp-product-page {

				padding:
					15px
					10px
					45px;

			}

			.ezp-product-main {

				gap: 25px;

			}

			.ezp-main-image,
			.ezp-main-image img {

				height: 390px;

				min-height: 390px;

			}

			.ezp-product-title {

				font-size: 24px;

			}

			.ezp-price-box {

				align-items: flex-start;

				flex-direction: column;

			}

			.ezp-product-meta {

				grid-template-columns: 1fr;

			}

			.ezp-service-grid {

				grid-template-columns: 1fr 1fr;

			}

			.ezp-related-grid {

				grid-template-columns:
					repeat(2,1fr);

			}

			.ezp-attribute-row {

				grid-template-columns: 1fr;

			}

			.ezp-attribute-name {

				padding-bottom: 7px;

			}

			.ezp-attribute-value {

				padding-top: 7px;

			}

		}


		@media (max-width: 420px) {

			.ezp-main-image,
			.ezp-main-image img {

				height: 330px;

				min-height: 330px;

			}

			.ezp-thumbnails {

				grid-template-columns:
					repeat(4,1fr);

			}

			.ezp-thumbnail {

				height: 68px;

			}

			.ezp-service-grid {

				grid-template-columns: 1fr;

			}

			.ezp-related-grid {

				grid-template-columns: 1fr 1fr;

				gap: 9px;

			}

			.ezp-related-card {

				padding: 8px;

			}

			.ezp-product-price {

				font-size: 22px;

			}

		}


		/* =========================================================
		   ACCESSIBILITY
		========================================================= */

		.ezp-product-page button:focus-visible,
		.ezp-product-page a:focus-visible,
		.ezp-product-page select:focus-visible,
		.ezp-product-page input:focus-visible {

			outline: 3px solid rgba(37,99,235,.22);

			outline-offset: 2px;

		}


		/* =========================================================
		   REDUCE MOTION
		========================================================= */

		@media (prefers-reduced-motion: reduce) {

			.ezp-product-page *,
			.ezp-product-page *::before,
			.ezp-product-page *::after {

				animation-duration: .01ms !important;

				animation-iteration-count: 1 !important;

				transition-duration: .01ms !important;

				scroll-behavior: auto !important;

			}

		}

		</style>


		<div
			class="ezp-product-page"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
		>


			<!-- =====================================================
			     BREADCRUMB
			===================================================== -->

			<div class="ezp-breadcrumb">

				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					خانه
				</a>

				<span class="ezp-breadcrumb-separator">/</span>

				<?php

				if ( ! empty( $categories ) ) {

					$first_category = reset( $categories );

					if ( $first_category ) {

						$category_link = get_term_link( $first_category );

						if ( ! is_wp_error( $category_link ) ) {
							?>

							<a href="<?php echo esc_url( $category_link ); ?>">
								<?php echo esc_html( $first_category->name ); ?>
							</a>

							<span class="ezp-breadcrumb-separator">
								/
							</span>

							<?php
						}
					}
				}

				?>

				<span>
					<?php echo esc_html( $product_name ); ?>
				</span>

			</div>


			<!-- =====================================================
			     MAIN PRODUCT
			===================================================== -->

			<div class="ezp-product-main">


				<!-- =================================================
				     GALLERY
				================================================= -->

				<div class="ezp-product-gallery">

					<div class="ezp-main-image">

						<?php if ( $product->is_on_sale() ) : ?>

							<span class="ezp-sale-badge">
								پیشنهاد ویژه
							</span>

						<?php endif; ?>


						<img
							id="ezp-main-product-image-<?php echo esc_attr( $product_id ); ?>"
							src="<?php echo esc_url( $main_image ); ?>"
							alt="<?php echo esc_attr( $product_name ); ?>"
							loading="eager"
						>

					</div>


					<?php if ( count( $gallery ) > 1 ) : ?>

						<div class="ezp-thumbnails">

							<?php foreach ( $gallery as $index => $image_id ) :

								$thumb = wp_get_attachment_image_url(
									$image_id,
									'thumbnail'
								);

								$large = wp_get_attachment_image_url(
									$image_id,
									'large'
								);

								if ( ! $thumb || ! $large ) {
									continue;
								}

								?>

								<button
									type="button"
									class="ezp-thumbnail <?php echo 0 === $index ? 'active' : ''; ?>"
									data-image="<?php echo esc_url( $large ); ?>"
									aria-label="نمایش تصویر <?php echo esc_attr( $index + 1 ); ?>"
								>

									<img
										src="<?php echo esc_url( $thumb ); ?>"
										alt="<?php echo esc_attr( $product_name ); ?>"
										loading="lazy"
									>

								</button>

							<?php endforeach; ?>

						</div>

					<?php endif; ?>

				</div>


				<!-- =================================================
				     SUMMARY
				================================================= -->

				<div class="ezp-product-summary">

					<div class="ezp-product-summary-inner">


						<!-- CATEGORY -->

						<?php if ( ! empty( $categories ) ) : ?>

							<div class="ezp-product-category">

								<?php foreach ( $categories as $category ) :

									$link = get_term_link( $category );

									if ( is_wp_error( $link ) ) {
										continue;
									}

									?>

									<a href="<?php echo esc_url( $link ); ?>">

										<?php echo esc_html( $category->name ); ?>

									</a>

								<?php endforeach; ?>

							</div>

						<?php endif; ?>


						<!-- TITLE -->

						<h1 class="ezp-product-title">

							<?php echo esc_html( $product_name ); ?>

						</h1>


						<!-- RATING -->

						<?php if ( $review_count > 0 ) : ?>

							<div class="ezp-product-rating">

								<span class="ezp-stars">

									<?php

									$full_stars = floor( $rating );

									$empty_stars = 5 - $full_stars;

									echo str_repeat(
										'★',
										$full_stars
									);

									echo str_repeat(
										'☆',
										max( 0, $empty_stars )
									);

									?>

								</span>

								<span class="ezp-rating-number">

									<?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?>

								</span>

								<a
									href="#ezp-reviews-<?php echo esc_attr( $product_id ); ?>"
									class="ezp-review-link"
								>

									<?php echo esc_html( number_format_i18n( $review_count ) ); ?>
									نظر

								</a>

							</div>

						<?php endif; ?>


						<!-- PRICE -->

						<div class="ezp-price-box">

							<div class="ezp-product-price">

								<?php echo wp_kses_post( $price_html ); ?>

							</div>

							<span class="ezp-stock <?php echo esc_attr( $stock_class ); ?>">

								<?php echo esc_html( $stock_text ); ?>

							</span>

						</div>


						<!-- SHORT DESCRIPTION -->

						<?php if ( $short_description ) : ?>

							<div class="ezp-short-description">

								<?php
								echo wp_kses_post(
									$short_description
								);
								?>

							</div>

						<?php endif; ?>


						<!-- META -->

						<div class="ezp-product-meta">

							<?php if ( $sku ) : ?>

								<div class="ezp-meta-item">

									<span class="ezp-meta-label">
										کد محصول
									</span>

									<span class="ezp-meta-value">
										<?php echo esc_html( $sku ); ?>
									</span>

								</div>

							<?php endif; ?>


							<div class="ezp-meta-item">

								<span class="ezp-meta-label">
									وضعیت
								</span>

								<span class="ezp-meta-value">

									<?php echo esc_html( $stock_text ); ?>

								</span>

							</div>

						</div>


						<!-- =================================================
						     BUY AREA
						================================================= -->

						<div class="ezp-buy-area">

							<?php

							/*
							|--------------------------------------------------------------------------
							| مهم:
							| اینجا از منطق واقعی ووکامرس استفاده می‌کنیم.
							|
							| این باعث می‌شود:
							|
							| - محصول ساده
							| - محصول متغیر
							| - variation
							| - موجودی
							| - حداقل تعداد
							| - افزونه‌های ووکامرس
							|
							| همچنان طبق منطق خود WooCommerce کار کنند.
							|--------------------------------------------------------------------------
							*/

							if ( $product->is_purchasable() ) {

								woocommerce_template_single_add_to_cart();

							} else {

								?>

								<div
									style="
										padding:15px;
										border-radius:12px;
										background:#fef2f2;
										color:#991b1b;
										text-align:center;
										font-size:13px;
										font-weight:700;
									"
								>

									در حال حاضر امکان خرید این محصول وجود ندارد.

								</div>

								<?php

							}

							?>

						</div>


						<!-- SERVICES -->

						<div class="ezp-service-grid">

							<div class="ezp-service">

								<span class="ezp-service-icon">
									✓
								</span>

								<span>
									تضمین اصالت کالا
								</span>

							</div>


							<div class="ezp-service">

								<span class="ezp-service-icon">
									↻
								</span>

								<span>
									ضمانت سلامت محصول
								</span>

							</div>


							<div class="ezp-service">

								<span class="ezp-service-icon">
									🚚
								</span>

								<span>
									ارسال سریع
								</span>

							</div>


							<div class="ezp-service">

								<span class="ezp-service-icon">
									♧
								</span>

								<span>
									پشتیبانی فروش
								</span>

							</div>

						</div>


					</div>

				</div>

			</div>


			<!-- =====================================================
			     DETAILS
			===================================================== -->

			<div class="ezp-product-details">

				<div class="ezp-tabs-nav">

					<button
						type="button"
						class="ezp-tab-button active"
						data-tab="description-<?php echo esc_attr( $product_id ); ?>"
					>
						توضیحات
					</button>

					<button
						type="button"
						class="ezp-tab-button"
						data-tab="attributes-<?php echo esc_attr( $product_id ); ?>"
					>
						مشخصات
					</button>

					<button
						type="button"
						class="ezp-tab-button"
						data-tab="reviews-<?php echo esc_attr( $product_id ); ?>"
					>
						نظرات
						<?php if ( $review_count ) : ?>
							(<?php echo esc_html( $review_count ); ?>)
						<?php endif; ?>
					</button>

				</div>


				<!-- DESCRIPTION -->

				<div
					id="description-<?php echo esc_attr( $product_id ); ?>"
					class="ezp-tab-panel active"
				>

					<div class="ezp-description">

						<?php

						if ( $description ) {

							echo wp_kses_post(
								$description
							);

						} else {

							echo '<p>توضیحی برای این محصول ثبت نشده است.</p>';

						}

						?>

					</div>

				</div>


				<!-- ATTRIBUTES -->

				<div
					id="attributes-<?php echo esc_attr( $product_id ); ?>"
					class="ezp-tab-panel"
				>

					<?php if ( ! empty( $attributes ) ) : ?>

						<div class="ezp-attributes">

							<?php foreach ( $attributes as $attribute ) :

								if ( ! $attribute->get_visible() ) {
									continue;
								}

								$name = $attribute->get_name();

								$label = wc_attribute_label(
									$name
								);

								$value = $product->get_attribute(
									$name
								);

								if ( ! $value ) {
									continue;
								}

								?>

								<div class="ezp-attribute-row">

									<div class="ezp-attribute-name">

										<?php echo esc_html( $label ); ?>

									</div>

									<div class="ezp-attribute-value">

										<?php echo wp_kses_post( $value ); ?>

									</div>

								</div>

							<?php endforeach; ?>

						</div>

					<?php else : ?>

						<p>
							مشخصات فنی برای این محصول ثبت نشده است.
						</p>

					<?php endif; ?>

				</div>


				<!-- REVIEWS -->

				<div
					id="reviews-<?php echo esc_attr( $product_id ); ?>"
					class="ezp-tab-panel"
				>

					<?php

					/*
					|--------------------------------------------------------------------------
					| سیستم واقعی نظرات ووکامرس
					|--------------------------------------------------------------------------
					*/

					comments_template();

					?>

				</div>

			</div>


			<!-- =====================================================
			     RELATED
			===================================================== -->

			<?php if ( ! empty( $related_ids ) ) : ?>

				<div class="ezp-related">

					<div class="ezp-related-heading">

						<h2>
							محصولات مرتبط
						</h2>

					</div>


					<div class="ezp-related-grid">

						<?php foreach ( $related_ids as $related_id ) :

							$related_product = wc_get_product(
								$related_id
							);

							if ( ! $related_product ) {
								continue;
							}

							$related_image_id =
								$related_product->get_image_id();

							$related_image =
								$related_image_id
									? wp_get_attachment_image_url(
										$related_image_id,
										'medium'
									)
									: wc_placeholder_img_src(
										'medium'
									);

							?>

							<a
								class="ezp-related-card"
								href="<?php echo esc_url( $related_product->get_permalink() ); ?>"
							>

								<div class="ezp-related-image">

									<img
										src="<?php echo esc_url( $related_image ); ?>"
										alt="<?php echo esc_attr( $related_product->get_name() ); ?>"
										loading="lazy"
									>

								</div>

								<div class="ezp-related-title">

									<?php
									echo esc_html(
										$related_product->get_name()
									);
									?>

								</div>

								<div class="ezp-related-price">

									<?php
									echo wp_kses_post(
										$related_product->get_price_html()
									);
									?>

								</div>

							</a>

						<?php endforeach; ?>

					</div>

				</div>

			<?php endif; ?>


		</div>


		<script>

		(function() {

			'use strict';


			/* =====================================================
			   PRODUCT GALLERY
			===================================================== */

			document
				.querySelectorAll('.ezp-product-page')
				.forEach(function(page) {

					var mainImage =
						page.querySelector('.ezp-main-image img');

					var thumbnails =
						page.querySelectorAll('.ezp-thumbnail');


					if (mainImage && thumbnails.length) {

						thumbnails.forEach(function(button) {

							button.addEventListener(
								'click',
								function() {

									var image =
										this.getAttribute(
											'data-image'
										);

									if (!image) {
										return;
									}

									mainImage.style.opacity = '0';

									setTimeout(function() {

										mainImage.src = image;

										mainImage.onload =
											function() {

												mainImage.style.opacity = '1';

											};

									}, 120);


									thumbnails.forEach(
										function(item) {

											item.classList.remove(
												'active'
											);

										}
									);


									this.classList.add(
										'active'
									);

								}
							);

						});

					}


					/* =================================================
					   TABS
					================================================= */

					var tabButtons =
						page.querySelectorAll(
							'.ezp-tab-button'
						);

					var panels =
						page.querySelectorAll(
							'.ezp-tab-panel'
						);


					tabButtons.forEach(function(button) {

						button.addEventListener(
							'click',
							function() {

								var target =
									this.getAttribute(
										'data-tab'
									);

								if (!target) {
									return;
								}


								tabButtons.forEach(
									function(btn) {

										btn.classList.remove(
											'active'
										);

									}
								);


								panels.forEach(
									function(panel) {

										panel.classList.remove(
											'active'
										);

									}
								);


								this.classList.add(
									'active'
								);


								var targetPanel =
									page.querySelector(
										'#' + target
									);


								if (targetPanel) {

									targetPanel.classList.add(
										'active'
									);

								}

							}
						);

					}

				});


			/* =====================================================
			   FIX FOR WOODMART STICKY BUTTON
			===================================================== */

			var style =
				document.createElement('style');

			style.textContent = `

				.ezp-product-page
				+ .wd-sticky-btn-container,

				.ezp-product-page
				~ .wd-sticky-btn-container {

					display: none !important;

				}

			`;

			document.head.appendChild(style);


		})();

		</script>


		<?php

		return ob_get_clean();
	}
}