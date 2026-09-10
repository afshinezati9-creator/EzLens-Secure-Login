<?php
/**
 * Plugin Name: سبد خرید سفارشی EzLens
 * Description: سبد خرید مدرن با AJAX، نمایش متغیرها، کوپن و طراحی حرفه‌ای
 * Version: 3.1.0
 * Shortcode: [custom_cart]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
 * Helpers (خارج از رندر تا redeclare نشود)
 * ============================================================ */
if ( ! function_exists( 'ezp_cart_num' ) ) {
	function ezp_cart_num( $number ) {
		$persian = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
		$english = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
		return str_replace( $english, $persian, (string) $number );
	}
}

if ( ! function_exists( 'ezp_cart_price' ) ) {
	function ezp_cart_price( $price ) {
		$formatted = wp_strip_all_tags( wc_price( $price ) );
		$formatted = preg_replace_callback(
			'/\d+/',
			function ( $m ) {
				return ezp_cart_num( $m[0] );
			},
			$formatted
		);
		return $formatted;
	}
}

/* ============================================================
 * Shortcode
 * ============================================================ */
if ( ! function_exists( 'ezlens_custom_cart_render' ) ) {
	add_shortcode( 'custom_cart', 'ezlens_custom_cart_render' );

	function ezlens_custom_cart_render() {
		if ( ! function_exists( 'WC' ) || is_null( WC()->cart ) ) {
			return '<div class="ezc-error">سبد خرید در دسترس نیست.</div>';
		}

		WC()->cart->calculate_totals();

		$ico_trash = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
		$ico_plus  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>';
		$ico_minus = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>';
		$ico_coupon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v5"/><path d="M4 12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2"/><path d="M9 16v2M15 16v2"/></svg>';
		$ico_cart_empty = '<svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>';
		$ico_check = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
		$ico_arrow = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>';
		$ico_shop  = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>';
		$ico_refresh = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>';
		$ico_close = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
		$ico_success = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';

		$cart_items    = WC()->cart->get_cart();
		$cart_total    = WC()->cart->get_total( 'edit' );
		$cart_subtotal = WC()->cart->get_subtotal();
		$cart_discount = WC()->cart->get_discount_total();
		$cart_shipping = WC()->cart->get_shipping_total();
		$cart_tax      = WC()->cart->get_total_tax();
		$coupons       = WC()->cart->get_applied_coupons();
		$cart_count    = WC()->cart->get_cart_contents_count();
		$shop_url      = get_permalink( wc_get_page_id( 'shop' ) );
		$checkout_url  = wc_get_checkout_url();

		ob_start();
		?>
<style id="ezc-cart-css">
.ezc-wrap{
	--bg:#f4f6f9;--card:#fff;--text:#0f172a;--muted:#64748b;--line:#e8edf3;
	--brand:#031f8a;--brand2:#1e3a8a;--ok:#059669;--danger:#dc2626;
	--radius:16px;--shadow:0 8px 30px rgba(15,23,42,.05);
	max-width:100%;margin:0;padding:20px 14px 48px;background:var(--bg);
	font-family:'Vazirmatn','IRANYekan',Tahoma,Arial,sans-serif;direction:rtl;color:var(--text);line-height:1.6;
	box-sizing:border-box;
}
.ezc-wrap *,.ezc-wrap *::before,.ezc-wrap *::after{box-sizing:border-box}
.ezc-inner{max-width:1180px;margin:0 auto}
.ezc-error{background:#fef2f2;color:#991b1b;padding:14px 18px;border-radius:12px;text-align:center;font-weight:700}

/* Header */
.ezc-header{
	display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;
	padding:18px 22px;background:var(--card);border:1px solid var(--line);border-radius:var(--radius);
	box-shadow:var(--shadow);margin-bottom:20px;
}
.ezc-header-title{display:flex;align-items:center;gap:12px}
.ezc-header-icon{
	width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;
	background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:var(--brand)
}
.ezc-header h1{margin:0;font-size:1.25rem;font-weight:800}
.ezc-header-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.ezc-chip{
	font-size:.78rem;font-weight:700;padding:6px 14px;border-radius:999px;border:1px solid var(--line);
	background:#f8fafc;color:var(--muted)
}
.ezc-chip.total{background:#eef2ff;border-color:#c7d2fe;color:var(--brand)}

/* Layout */
.ezc-layout{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(280px,.85fr);gap:20px;align-items:start}
@media(max-width:960px){.ezc-layout{grid-template-columns:1fr}}

/* Items */
.ezc-items{display:flex;flex-direction:column;gap:12px}
.ezc-item{
	display:grid;grid-template-columns:92px minmax(0,1fr) auto;gap:16px;align-items:center;
	background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:14px 16px;
	box-shadow:var(--shadow);transition:opacity .25s,transform .25s,box-shadow .2s;
}
.ezc-item:hover{box-shadow:0 12px 32px rgba(15,23,42,.07)}
.ezc-item.updating{opacity:.5;pointer-events:none}
.ezc-item.removing{opacity:0;transform:translateX(28px)}

.ezc-thumb{position:relative;width:92px;height:92px;border-radius:14px;overflow:hidden;background:#f1f5f9;border:1px solid var(--line);flex-shrink:0}
.ezc-thumb img{width:100%;height:100%;object-fit:cover;display:block}
.ezc-qty-badge{
	position:absolute;top:-7px;right:-7px;min-width:22px;height:22px;padding:0 5px;border-radius:999px;
	background:var(--brand);color:#fff;font-size:.65rem;font-weight:800;display:flex;align-items:center;justify-content:center;
	box-shadow:0 4px 10px rgba(3,31,138,.25)
}

.ezc-info{min-width:0}
.ezc-name{margin:0 0 4px;font-size:.98rem;font-weight:800;line-height:1.35}
.ezc-name a{color:var(--text);text-decoration:none}
.ezc-name a:hover{color:var(--brand)}
.ezc-sku{font-size:.72rem;color:#94a3b8;margin-bottom:6px;direction:ltr;text-align:right}
.ezc-attrs{display:flex;flex-wrap:wrap;gap:6px;margin-top:4px}
.ezc-attr{
	display:inline-flex;align-items:center;gap:4px;font-size:.72rem;color:#475569;
	background:#f8fafc;border:1px solid var(--line);border-radius:999px;padding:3px 10px
}
.ezc-attr strong{color:var(--text);font-weight:700}
.ezc-unit{margin-top:8px;font-size:.85rem;font-weight:700;color:var(--muted)}
.ezc-unit span{color:var(--text)}

.ezc-side{display:flex;flex-direction:column;align-items:flex-end;gap:12px;min-width:132px}
.ezc-line{font-size:1rem;font-weight:800;color:var(--text);white-space:nowrap}

.ezc-qty{
	display:inline-flex;align-items:center;border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#f8fafc
}
.ezc-qty:focus-within{border-color:var(--brand);box-shadow:0 0 0 3px rgba(3,31,138,.08)}
.ezc-qty button{
	width:36px;height:40px;border:0;background:transparent;cursor:pointer;color:#64748b;
	display:flex;align-items:center;justify-content:center;transition:.15s;padding:0
}
.ezc-qty button:hover{background:#e2e8f0;color:var(--text)}
.ezc-qty input{
	width:46px;height:40px;border:0;background:transparent;text-align:center;font-weight:800;font-size:.95rem;
	font-family:inherit;color:var(--text);-moz-appearance:textfield;padding:0
}
.ezc-qty input::-webkit-outer-spin-button,.ezc-qty input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}

.ezc-remove{
	border:0;background:transparent;color:#94a3b8;cursor:pointer;padding:8px;border-radius:10px;
	display:inline-flex;align-items:center;justify-content:center;transition:.15s
}
.ezc-remove:hover{color:var(--danger);background:#fef2f2}

/* Summary */
.ezc-summary{
	position:sticky;top:18px;background:var(--card);border:1px solid var(--line);border-radius:var(--radius);
	padding:22px;box-shadow:var(--shadow)
}
.ezc-summary h2{margin:0 0 16px;font-size:1.05rem;font-weight:800}
.ezc-rows{display:flex;flex-direction:column;gap:2px;margin-bottom:16px}
.ezc-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:.9rem}
.ezc-row .lbl{color:var(--muted)}
.ezc-row .val{font-weight:700;color:var(--text)}
.ezc-row.discount .val{color:var(--ok)}
.ezc-row.total{border-bottom:0;border-top:2px solid var(--line);margin-top:6px;padding-top:14px;font-size:1.05rem}
.ezc-row.total .lbl,.ezc-row.total .val{font-weight:800;color:var(--text)}
.ezc-row.total .val{color:var(--brand);font-size:1.15rem}

.ezc-coupon{margin:0 0 16px;padding:14px;background:#f8fafc;border:1px solid var(--line);border-radius:14px}
.ezc-coupon-title{display:flex;align-items:center;gap:8px;font-weight:800;font-size:.9rem;margin-bottom:4px;color:var(--text)}
.ezc-coupon-title svg{color:var(--brand)}
.ezc-coupon-desc{font-size:.78rem;color:var(--muted);margin:0 0 10px}
.ezc-coupon-row{display:flex;gap:8px}
.ezc-coupon-row input{
	flex:1;min-width:0;border:1px solid var(--line);border-radius:11px;padding:11px 12px;font-family:inherit;
	font-size:.85rem;background:#fff;outline:none;transition:.15s
}
.ezc-coupon-row input:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(3,31,138,.08)}
.ezc-coupon-row button{
	border:0;border-radius:11px;padding:11px 16px;background:var(--brand);color:#fff;font-weight:800;font-size:.82rem;
	cursor:pointer;font-family:inherit;white-space:nowrap;transition:.2s;box-shadow:0 6px 16px rgba(3,31,138,.18)
}
.ezc-coupon-row button:hover{background:#011663;transform:translateY(-1px)}
.ezc-coupon-row button:disabled{opacity:.6;cursor:not-allowed;transform:none}
.ezc-coupon-msg{display:none;margin-top:8px;font-size:.78rem;padding:7px 10px;border-radius:8px}
.ezc-coupon-msg.show{display:block}
.ezc-coupon-msg.ok{color:#065f46;background:#ecfdf5;border:1px solid #a7f3d0}
.ezc-coupon-msg.err{color:#991b1b;background:#fef2f2;border:1px solid #fecaca}
.ezc-coupons{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px}
.ezc-coupon-chip{
	display:inline-flex;align-items:center;gap:6px;background:#eef2ff;color:var(--brand);border:1px solid #c7d2fe;
	border-radius:999px;padding:4px 10px;font-size:.75rem;font-weight:700
}
.ezc-coupon-chip button{border:0;background:transparent;cursor:pointer;color:#64748b;padding:0;display:inline-flex}
.ezc-coupon-chip button:hover{color:var(--danger)}

.ezc-actions{display:flex;flex-direction:column;gap:10px}
.ezc-btn{
	display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:12px;padding:13px 18px;
	font-weight:800;font-size:.9rem;text-decoration:none;cursor:pointer;font-family:inherit;transition:.2s;border:0
}
.ezc-btn-primary{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;box-shadow:0 8px 22px rgba(15,23,42,.18)}
.ezc-btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(15,23,42,.22);color:#fff}
.ezc-btn-secondary{background:#fff;color:var(--text);border:1px solid var(--line)}
.ezc-btn-secondary:hover{background:#f8fafc;border-color:#cbd5e1}
.ezc-btn-ghost{background:transparent;color:var(--muted);border:0;padding:8px;font-size:.8rem;font-weight:600}
.ezc-btn-ghost:hover{color:var(--text)}
.ezc-note{margin:4px 0 0;text-align:center;font-size:.75rem;color:var(--muted)}

/* Empty */
.ezc-empty{
	text-align:center;padding:72px 24px;background:var(--card);border:1px solid var(--line);
	border-radius:var(--radius);box-shadow:var(--shadow)
}
.ezc-empty .ico{color:#cbd5e1;margin-bottom:14px}
.ezc-empty h3{margin:0 0 8px;font-size:1.25rem}
.ezc-empty p{margin:0 0 22px;color:var(--muted)}

/* Toast */
.ezc-toast{
	position:fixed;bottom:28px;right:28px;z-index:999999;min-width:280px;max-width:420px;
	padding:14px 18px;border-radius:14px;background:#fff;border:1px solid var(--line);
	border-right:4px solid var(--brand);box-shadow:0 16px 40px rgba(0,0,0,.12);
	display:flex;align-items:center;gap:12px;opacity:0;transform:translateY(16px) scale(.97);
	transition:.28s cubic-bezier(.4,0,.2,1);pointer-events:none;font-size:.85rem
}
.ezc-toast.show{opacity:1;transform:translateY(0) scale(1);pointer-events:auto}
.ezc-toast.ok{border-right-color:var(--ok)}
.ezc-toast.err{border-right-color:var(--danger)}
.ezc-toast .msg{flex:1;font-weight:600}
.ezc-toast .x{border:0;background:transparent;cursor:pointer;color:#94a3b8;padding:4px;display:inline-flex}

@media(max-width:640px){
	.ezc-item{grid-template-columns:76px minmax(0,1fr);grid-template-rows:auto auto}
	.ezc-thumb{width:76px;height:76px}
	.ezc-side{grid-column:1/-1;flex-direction:row;justify-content:space-between;align-items:center;min-width:0;width:100%}
	.ezc-header h1{font-size:1.1rem}
	.ezc-toast{left:14px;right:14px;bottom:14px;min-width:0;max-width:none}
}
</style>

<div class="ezc-wrap" id="ez-cart-wrap">
	<div class="ezc-inner">
		<div class="ezc-toast" id="ez-toast">
			<span class="ico" id="ez-toast-icon"><?php echo $ico_success; ?></span>
			<span class="msg" id="ez-toast-msg"></span>
			<button type="button" class="x" id="ez-toast-close" aria-label="بستن"><?php echo $ico_close; ?></button>
		</div>

		<?php if ( WC()->cart->is_empty() ) : ?>
			<div class="ezc-empty">
				<div class="ico"><?php echo $ico_cart_empty; ?></div>
				<h3>سبد خرید شما خالی است</h3>
				<p>هنوز محصولی اضافه نکرده‌اید.</p>
				<a class="ezc-btn ezc-btn-primary" href="<?php echo esc_url( $shop_url ); ?>">
					<?php echo $ico_shop; ?> شروع خرید
				</a>
			</div>
		<?php else : ?>
			<div class="ezc-header">
				<div class="ezc-header-title">
					<span class="ezc-header-icon"><?php echo $ico_shop; ?></span>
					<h1>سبد خرید</h1>
				</div>
				<div class="ezc-header-meta">
					<span class="ezc-chip"><?php echo esc_html( ezp_cart_num( $cart_count ) ); ?> کالا</span>
					<span class="ezc-chip total"><?php echo ezp_cart_price( $cart_total ); ?></span>
				</div>
			</div>

			<div class="ezc-layout">
				<div class="ezc-items" id="ez-cart-body">
					<?php
					foreach ( $cart_items as $cart_key => $item ) :
						$product = isset( $item['data'] ) ? $item['data'] : null;
						if ( ! $product || ! $product->exists() ) {
							continue;
						}
						$product_id        = $product->get_id();
						$product_name      = $product->get_name();
						$product_price     = $product->get_price();
						$product_image     = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ) : wc_placeholder_img_src( 'thumbnail' );
						$product_sku       = $product->get_sku();
						$quantity          = $item['quantity'];
						$subtotal          = $item['line_subtotal'];
						$product_permalink = $product->is_visible() ? $product->get_permalink() : '';
						$meta_data         = wc_get_formatted_cart_item_data( $item, true );
						$max_qty           = $product->get_max_purchase_quantity();
						if ( $max_qty < 1 ) {
							$max_qty = 99;
						}

						$variation_attrs = array();
						if ( ! empty( $item['variation'] ) && is_array( $item['variation'] ) ) {
							foreach ( $item['variation'] as $key => $value ) {
								$taxonomy = str_replace( 'attribute_', '', $key );
								$label    = wc_attribute_label( $taxonomy, $product );
								if ( '' !== (string) $value ) {
									$variation_attrs[] = array(
										'label' => $label,
										'value' => $value,
									);
								}
							}
						}

						$custom_data = array();
						if ( isset( $item['ezlens_options'] ) && is_array( $item['ezlens_options'] ) ) {
							foreach ( $item['ezlens_options'] as $field_key => $field_value ) {
								if ( empty( $field_value ) ) {
									continue;
								}
								$label = is_numeric( $field_key ) ? ( 'گزینه ' . $field_key ) : ( 'فیلد ' . $field_key );
								$value_display = is_array( $field_value ) ? implode( ', ', $field_value ) : $field_value;
								if ( is_string( $field_value ) && preg_match( '/\.(jpg|jpeg|png|gif|pdf|doc|docx|zip)$/i', $field_value ) ) {
									$value_display = '📎 ' . basename( $field_value );
								}
								$custom_data[] = array(
									'label' => $label,
									'value' => $value_display,
								);
							}
						}
						if ( ! empty( $item['ezlens_uploaded_file'] ) ) {
							$custom_data[] = array(
								'label' => 'فایل آپلود شده',
								'value' => '📎 ' . basename( $item['ezlens_uploaded_file'] ),
							);
						}
						$all_attrs = array_merge( $variation_attrs, $custom_data );
						?>
						<div class="ezc-item ez-cart-item" data-key="<?php echo esc_attr( $cart_key ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>">
							<div class="ezc-thumb">
								<?php if ( $product_permalink ) : ?>
									<a href="<?php echo esc_url( $product_permalink ); ?>">
										<img src="<?php echo esc_url( $product_image ); ?>" alt="<?php echo esc_attr( $product_name ); ?>" loading="lazy">
									</a>
								<?php else : ?>
									<img src="<?php echo esc_url( $product_image ); ?>" alt="<?php echo esc_attr( $product_name ); ?>" loading="lazy">
								<?php endif; ?>
								<?php if ( $quantity > 1 ) : ?>
									<span class="ezc-qty-badge"><?php echo esc_html( ezp_cart_num( $quantity ) ); ?></span>
								<?php endif; ?>
							</div>

							<div class="ezc-info">
								<div class="ezc-name">
									<?php if ( $product_permalink ) : ?>
										<a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo esc_html( $product_name ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $product_name ); ?>
									<?php endif; ?>
								</div>
								<?php if ( $product_sku ) : ?>
									<div class="ezc-sku">کد: <?php echo esc_html( $product_sku ); ?></div>
								<?php endif; ?>
								<?php if ( ! empty( $all_attrs ) ) : ?>
									<div class="ezc-attrs">
										<?php foreach ( $all_attrs as $attr ) : ?>
											<span class="ezc-attr"><strong><?php echo esc_html( $attr['label'] ); ?>:</strong> <?php echo esc_html( $attr['value'] ); ?></span>
										<?php endforeach; ?>
									</div>
								<?php elseif ( $meta_data ) : ?>
									<div class="ezc-attrs"><?php echo wp_kses_post( $meta_data ); ?></div>
								<?php endif; ?>
								<div class="ezc-unit">قیمت واحد: <span><?php echo ezp_cart_price( $product_price ); ?></span></div>
							</div>

							<div class="ezc-side">
								<div class="ezc-qty">
									<button type="button" class="qty-minus" data-key="<?php echo esc_attr( $cart_key ); ?>" aria-label="کاهش"><?php echo $ico_minus; ?></button>
									<input type="number" class="qty-input" value="<?php echo esc_attr( $quantity ); ?>" min="1" max="<?php echo esc_attr( $max_qty ); ?>" data-key="<?php echo esc_attr( $cart_key ); ?>">
									<button type="button" class="qty-plus" data-key="<?php echo esc_attr( $cart_key ); ?>" aria-label="افزایش"><?php echo $ico_plus; ?></button>
								</div>
								<div class="ezc-line"><?php echo ezp_cart_price( $subtotal ); ?></div>
								<button type="button" class="ezc-remove ez-cart-remove" data-key="<?php echo esc_attr( $cart_key ); ?>" aria-label="حذف"><?php echo $ico_trash; ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<aside class="ezc-summary">
					<h2>خلاصه سفارش</h2>
					<div class="ezc-rows">
						<div class="ezc-row"><span class="lbl">جمع جزء</span><span class="val"><?php echo ezp_cart_price( $cart_subtotal ); ?></span></div>
						<?php if ( $cart_discount > 0 ) : ?>
						<div class="ezc-row discount"><span class="lbl">تخفیف</span><span class="val">−<?php echo ezp_cart_price( $cart_discount ); ?></span></div>
						<?php endif; ?>
						<?php if ( $cart_shipping > 0 ) : ?>
						<div class="ezc-row"><span class="lbl">هزینه ارسال</span><span class="val"><?php echo ezp_cart_price( $cart_shipping ); ?></span></div>
						<?php endif; ?>
						<?php if ( $cart_tax > 0 ) : ?>
						<div class="ezc-row"><span class="lbl">مالیات</span><span class="val"><?php echo ezp_cart_price( $cart_tax ); ?></span></div>
						<?php endif; ?>
						<div class="ezc-row total"><span class="lbl">مبلغ قابل پرداخت</span><span class="val"><?php echo ezp_cart_price( $cart_total ); ?></span></div>
					</div>

					<div class="ezc-coupon">
						<div class="ezc-coupon-title"><?php echo $ico_coupon; ?> کد تخفیف</div>
						<p class="ezc-coupon-desc">اگر کد دارید اینجا وارد کنید.</p>
						<div class="ezc-coupon-row">
							<input type="text" id="ez-coupon-input" placeholder="کد تخفیف" autocomplete="off">
							<button type="button" id="ez-coupon-apply"><?php echo $ico_coupon; ?> اعمال</button>
						</div>
						<div id="ez-coupon-msg" class="ezc-coupon-msg"></div>
						<?php if ( ! empty( $coupons ) ) : ?>
						<div class="ezc-coupons">
							<?php foreach ( $coupons as $code ) : ?>
							<span class="ezc-coupon-chip">
								<?php echo esc_html( $code ); ?>
								<button type="button" class="ez-coupon-remove-btn" data-code="<?php echo esc_attr( $code ); ?>" aria-label="حذف"><?php echo $ico_close; ?></button>
							</span>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>
					</div>

					<div class="ezc-actions">
						<a href="<?php echo esc_url( $checkout_url ); ?>" class="ezc-btn ezc-btn-primary">
							<?php echo $ico_check; ?> ادامه جهت تسویه حساب
						</a>
						<a href="<?php echo esc_url( $shop_url ); ?>" class="ezc-btn ezc-btn-secondary">
							<?php echo $ico_arrow; ?> ادامه خرید
						</a>
						<button type="button" id="ez-cart-refresh" class="ezc-btn ezc-btn-ghost">
							<?php echo $ico_refresh; ?> بروزرسانی
						</button>
					</div>
					<p class="ezc-note">پرداخت امن · امکان بازگشت تا ۷ روز</p>
				</aside>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
(function($){
	'use strict';
	if (typeof $ === 'undefined') return;

	var ajaxurl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
	var nonce = <?php echo wp_json_encode( wp_create_nonce( 'ez_cart_nonce' ) ); ?>;

	function showToast(message, type){
		var toast = $('#ez-toast');
		var icon = $('#ez-toast-icon');
		var msg = $('#ez-toast-msg');
		toast.removeClass('ok err show');
		if (type === 'success' || type === 'ok') {
			toast.addClass('ok');
			icon.html(<?php echo wp_json_encode( $ico_success ); ?>);
		} else if (type === 'error' || type === 'err') {
			toast.addClass('err');
			icon.html(<?php echo wp_json_encode( $ico_close ); ?>);
		}
		msg.text(message || '');
		toast.addClass('show');
		clearTimeout(toast.data('timer'));
		toast.data('timer', setTimeout(function(){ toast.removeClass('show'); }, 3800));
	}

	$('#ez-toast-close').on('click', function(){ $('#ez-toast').removeClass('show'); });

	function updateQty(key, qty){
		var row = $('.ez-cart-item[data-key="'+key+'"]');
		if (!row.length) return;
		row.addClass('updating');
		$.post(ajaxurl, {
			action: 'ez_cart_update_qty',
			nonce: nonce,
			cart_key: key,
			quantity: qty
		}).done(function(res){
			row.removeClass('updating');
			if (res && res.success) {
				showToast(res.data && res.data.message ? res.data.message : 'سبد به‌روزرسانی شد.', 'ok');
				setTimeout(function(){ location.reload(); }, 500);
			} else {
				showToast((res && res.data && res.data.message) || 'خطا در به‌روزرسانی.', 'err');
			}
		}).fail(function(){
			row.removeClass('updating');
			showToast('خطا در ارتباط با سرور.', 'err');
		});
	}

	function removeItem(key){
		var row = $('.ez-cart-item[data-key="'+key+'"]');
		if (!row.length) return;
		if (!confirm('این کالا از سبد حذف شود؟')) return;
		row.addClass('removing');
		$.post(ajaxurl, {
			action: 'ez_cart_remove_item',
			nonce: nonce,
			cart_key: key
		}).done(function(res){
			if (res && res.success) {
				showToast(res.data && res.data.message ? res.data.message : 'حذف شد.', 'ok');
				setTimeout(function(){ location.reload(); }, 400);
			} else {
				row.removeClass('removing');
				showToast((res && res.data && res.data.message) || 'خطا در حذف.', 'err');
			}
		}).fail(function(){
			row.removeClass('removing');
			showToast('خطا در ارتباط با سرور.', 'err');
		});
	}

	$(document).on('click', '.qty-plus, .qty-minus', function(){
		var btn = $(this);
		var key = btn.data('key');
		var input = $('.qty-input[data-key="'+key+'"]');
		if (!input.length) return;
		var val = parseInt(input.val(), 10) || 1;
		var max = parseInt(input.attr('max'), 10) || 99;
		if (btn.hasClass('qty-plus')) {
			val += 1;
		} else {
			if (val <= 1) return;
			val -= 1;
		}
		if (val > max) val = max;
		input.val(val);
		updateQty(key, val);
	});

	$(document).on('change', '.qty-input', function(){
		var input = $(this);
		var key = input.data('key');
		var val = parseInt(input.val(), 10) || 1;
		var max = parseInt(input.attr('max'), 10) || 99;
		if (val < 1) val = 1;
		if (val > max) val = max;
		input.val(val);
		updateQty(key, val);
	});

	$(document).on('click', '.ez-cart-remove', function(){
		removeItem($(this).data('key'));
	});

	$('#ez-coupon-apply').on('click', function(){
		var btn = $(this);
		var code = ($('#ez-coupon-input').val() || '').trim();
		var msgBox = $('#ez-coupon-msg');
		if (!code) {
			showToast('لطفاً کد تخفیف را وارد کنید.', 'err');
			return;
		}
		btn.prop('disabled', true).text('در حال اعمال...');
		msgBox.removeClass('show ok err');
		$.post(ajaxurl, {
			action: 'ez_cart_apply_coupon',
			nonce: nonce,
			coupon: code
		}).done(function(res){
			btn.prop('disabled', false).html(<?php echo wp_json_encode( $ico_coupon . ' اعمال' ); ?>);
			if (res && res.success) {
				msgBox.text((res.data && res.data.message) || 'اعمال شد.').addClass('show ok');
				showToast((res.data && res.data.message) || 'کد اعمال شد.', 'ok');
				setTimeout(function(){ location.reload(); }, 800);
			} else {
				var m = (res && res.data && res.data.message) || 'کد نامعتبر است.';
				msgBox.text(m).addClass('show err');
				showToast(m, 'err');
			}
		}).fail(function(){
			btn.prop('disabled', false).html(<?php echo wp_json_encode( $ico_coupon . ' اعمال' ); ?>);
			showToast('خطا در ارتباط با سرور.', 'err');
		});
	});

	$(document).on('click', '.ez-coupon-remove-btn', function(){
		var code = $(this).data('code');
		if (!code) return;
		if (!confirm('کد تخفیف «' + code + '» حذف شود؟')) return;
		$.post(ajaxurl, {
			action: 'ez_cart_remove_coupon',
			nonce: nonce,
			coupon: code
		}).done(function(res){
			if (res && res.success) {
				showToast((res.data && res.data.message) || 'کد حذف شد.', 'ok');
				setTimeout(function(){ location.reload(); }, 700);
			} else {
				showToast((res && res.data && res.data.message) || 'حذف ناموفق.', 'err');
			}
		}).fail(function(){ showToast('خطا در ارتباط با سرور.', 'err'); });
	});

	$('#ez-coupon-input').on('keypress', function(e){
		if (e.which === 13) {
			e.preventDefault();
			$('#ez-coupon-apply').trigger('click');
		}
	});

	$('#ez-cart-refresh').on('click', function(){ location.reload(); });
})(jQuery);
</script>
		<?php
		return ob_get_clean();
	}
}

/* ============================================================
 * AJAX Handlers (همان اکشن‌های نسخه قبلی)
 * ============================================================ */
if ( ! function_exists( 'ez_cart_ajax_update_qty' ) ) {
	add_action( 'wp_ajax_ez_cart_update_qty', 'ez_cart_ajax_update_qty' );
	add_action( 'wp_ajax_nopriv_ez_cart_update_qty', 'ez_cart_ajax_update_qty' );

	function ez_cart_ajax_update_qty() {
		check_ajax_referer( 'ez_cart_nonce', 'nonce' );
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => 'سبد در دسترس نیست.' ) );
		}

		$cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_key'] ) ) : '';
		$quantity = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 0;

		if ( '' === $cart_key || $quantity < 1 ) {
			wp_send_json_error( array( 'message' => 'مقادیر نامعتبر.' ) );
		}

		$cart_item = WC()->cart->get_cart_item( $cart_key );
		if ( ! $cart_item ) {
			wp_send_json_error( array( 'message' => 'کالا در سبد یافت نشد.' ) );
		}

		$product = $cart_item['data'];
		$max_qty = $product ? $product->get_max_purchase_quantity() : 0;
		if ( $max_qty > 0 && $quantity > $max_qty ) {
			$quantity = $max_qty;
		}

		WC()->cart->set_quantity( $cart_key, $quantity, true );
		WC()->cart->calculate_totals();

		wp_send_json_success( array(
			'message'     => 'تعداد با موفقیت به‌روزرسانی شد.',
			'total'       => WC()->cart->get_cart_contents_count(),
			'subtotal'    => WC()->cart->get_subtotal(),
			'total_price' => WC()->cart->get_total( 'edit' ),
		) );
	}
}

if ( ! function_exists( 'ez_cart_ajax_remove_item' ) ) {
	add_action( 'wp_ajax_ez_cart_remove_item', 'ez_cart_ajax_remove_item' );
	add_action( 'wp_ajax_nopriv_ez_cart_remove_item', 'ez_cart_ajax_remove_item' );

	function ez_cart_ajax_remove_item() {
		check_ajax_referer( 'ez_cart_nonce', 'nonce' );
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => 'سبد در دسترس نیست.' ) );
		}

		$cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_key'] ) ) : '';
		if ( '' === $cart_key ) {
			wp_send_json_error( array( 'message' => 'شناسه کالا نامعتبر.' ) );
		}

		$removed = WC()->cart->remove_cart_item( $cart_key );
		if ( ! $removed ) {
			wp_send_json_error( array( 'message' => 'خطا در حذف کالا.' ) );
		}

		WC()->cart->calculate_totals();
		wp_send_json_success( array(
			'message' => 'کالا با موفقیت حذف شد.',
			'empty'   => WC()->cart->is_empty(),
			'total'   => WC()->cart->get_cart_contents_count(),
		) );
	}
}

if ( ! function_exists( 'ez_cart_ajax_apply_coupon' ) ) {
	add_action( 'wp_ajax_ez_cart_apply_coupon', 'ez_cart_ajax_apply_coupon' );
	add_action( 'wp_ajax_nopriv_ez_cart_apply_coupon', 'ez_cart_ajax_apply_coupon' );

	function ez_cart_ajax_apply_coupon() {
		check_ajax_referer( 'ez_cart_nonce', 'nonce' );
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => 'سبد در دسترس نیست.' ) );
		}

		$coupon = isset( $_POST['coupon'] ) ? wc_format_coupon_code( wp_unslash( $_POST['coupon'] ) ) : '';
		if ( '' === $coupon ) {
			wp_send_json_error( array( 'message' => 'لطفاً کد تخفیف را وارد کنید.' ) );
		}

		if ( WC()->cart->has_discount( $coupon ) ) {
			wp_send_json_error( array( 'message' => 'این کد قبلاً اعمال شده است.' ) );
		}

		$result = WC()->cart->apply_coupon( $coupon );
		if ( ! $result ) {
			$notices = wc_get_notices( 'error' );
			$msg     = 'کد تخفیف نامعتبر یا منقضی شده است.';
			if ( ! empty( $notices[0]['notice'] ) ) {
				$msg = wp_strip_all_tags( $notices[0]['notice'] );
			}
			wc_clear_notices();
			wp_send_json_error( array( 'message' => $msg ) );
		}

		wc_clear_notices();
		WC()->cart->calculate_totals();
		wp_send_json_success( array( 'message' => 'کد تخفیف با موفقیت اعمال شد.' ) );
	}
}

if ( ! function_exists( 'ez_cart_ajax_remove_coupon' ) ) {
	add_action( 'wp_ajax_ez_cart_remove_coupon', 'ez_cart_ajax_remove_coupon' );
	add_action( 'wp_ajax_nopriv_ez_cart_remove_coupon', 'ez_cart_ajax_remove_coupon' );

	function ez_cart_ajax_remove_coupon() {
		check_ajax_referer( 'ez_cart_nonce', 'nonce' );
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => 'سبد در دسترس نیست.' ) );
		}

		$coupon = isset( $_POST['coupon'] ) ? wc_format_coupon_code( wp_unslash( $_POST['coupon'] ) ) : '';
		if ( '' === $coupon ) {
			wp_send_json_error( array( 'message' => 'کد تخفیف نامعتبر.' ) );
		}

		WC()->cart->remove_coupon( $coupon );
		WC()->cart->calculate_totals();
		wp_send_json_success( array( 'message' => 'کد تخفیف با موفقیت حذف شد.' ) );
	}
}