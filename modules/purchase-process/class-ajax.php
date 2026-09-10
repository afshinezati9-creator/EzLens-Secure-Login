<?php
/**
 * مدیریت درخواست‌های AJAX ماژول فرآیند خرید
 *
 * @package EzLens_Secure_Login
 * @subpackage Purchase_Process
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Purchase_Process_Ajax {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_ezpurchase_toggle_status', array( $this, 'ajax_toggle_status' ) );
		add_action( 'wp_ajax_ezpurchase_delete_file', array( $this, 'ajax_delete_file' ) );
		add_action( 'wp_ajax_ezpurchase_get_list', array( $this, 'ajax_get_list' ) );
		add_action( 'wp_ajax_ezpurchase_save_file', array( $this, 'ajax_save_file' ) );
		add_action( 'wp_ajax_ezpurchase_validate_code', array( $this, 'ajax_validate_code' ) );
		// ===== جدید: ایجاد اجباری جدول =====
		add_action( 'wp_ajax_ezpurchase_force_install', array( $this, 'ajax_force_install' ) );
	}

	private function ensure_database() {
		if ( ! class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			$install_file = dirname( __FILE__ ) . '/class-install.php';
			if ( file_exists( $install_file ) ) {
				require_once $install_file;
			}
		}
		if ( ! class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			return false;
		}
		return EzLens_Purchase_Process_Install::is_ready();
	}

	private function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_purchase_scripts';
	}

	public function ajax_toggle_status() {
		$this->verify_request();
		if ( ! $this->ensure_database() ) {
			wp_send_json_error( array( 'message' => 'جدول ماژول فرآیند خرید آماده نیست.' ) );
		}
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$status = isset( $_POST['status'] ) ? absint( $_POST['status'] ) : 0;
		$status = $status ? 1 : 0;

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'شناسه فایل معتبر نیست.' ) );
		}

		global $wpdb;
		$table = $this->get_table_name();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT id, status FROM {$table} WHERE id = %d LIMIT 1", $id ) );
		if ( ! $row ) {
			wp_send_json_error( array( 'message' => 'فایل موردنظر یافت نشد.' ) );
		}

		$updated = $wpdb->update( $table, array( 'status' => $status ), array( 'id' => $id ), array( '%d' ), array( '%d' ) );
		if ( false === $updated ) {
			wp_send_json_error( array( 'message' => 'خطا در بروزرسانی وضعیت فایل.' ) );
		}

		wp_send_json_success( array(
			'message' => $status ? 'فایل با موفقیت فعال شد.' : 'فایل با موفقیت غیرفعال شد.',
			'status' => $status,
			'id' => $id,
		) );
	}

	public function ajax_delete_file() {
		$this->verify_request();
		if ( ! $this->ensure_database() ) {
			wp_send_json_error( array( 'message' => 'جدول ماژول فرآیند خرید آماده نیست.' ) );
		}
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'شناسه فایل معتبر نیست.' ) );
		}

		global $wpdb;
		$table = $this->get_table_name();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT id, filename FROM {$table} WHERE id = %d LIMIT 1", $id ) );
		if ( ! $row ) {
			wp_send_json_error( array( 'message' => 'فایل موردنظر یافت نشد.' ) );
		}

		$file_path = $this->get_file_path( $row->filename );
		if ( false === $file_path ) {
			wp_send_json_error( array( 'message' => 'مسیر فایل نامعتبر است.' ) );
		}

		$deleted = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
		if ( false === $deleted ) {
			wp_send_json_error( array( 'message' => 'خطا در حذف رکورد دیتابیس.' ) );
		}

		if ( file_exists( $file_path ) ) {
			if ( ! is_writable( $file_path ) ) {
				wp_send_json_error( array( 'message' => 'رکورد حذف شد اما فایل فیزیکی قابل حذف نیست.' ) );
			}
			if ( ! @unlink( $file_path ) ) {
				wp_send_json_error( array( 'message' => 'رکورد حذف شد اما حذف فایل فیزیکی ناموفق بود.' ) );
			}
		}

		wp_send_json_success( array( 'message' => 'فایل با موفقیت حذف شد.', 'id' => $id ) );
	}

	public function ajax_get_list() {
		$this->verify_request();
		if ( ! $this->ensure_database() ) {
			wp_send_json_error( array( 'message' => 'جدول ماژول فرآیند خرید آماده نیست.' ) );
		}

		$paged = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;
		$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		$paged = max( 1, $paged );
		$per_page = max( 1, min( 100, $per_page ) );
		$offset = ( $paged - 1 ) * $per_page;

		global $wpdb;
		$table = $this->get_table_name();

		$where = '1=1';
		$where_args = array();
		if ( '' !== $search ) {
			$where .= ' AND title LIKE %s';
			$where_args[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		$total_query = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
		if ( ! empty( $where_args ) ) {
			$total = $wpdb->get_var( $wpdb->prepare( $total_query, $where_args ) );
		} else {
			$total = $wpdb->get_var( $total_query );
		}

		$query = "SELECT id, title, filename, description, status, created_at, updated_at FROM {$table} WHERE {$where} ORDER BY id DESC LIMIT %d OFFSET %d";
		$args = array_merge( $where_args, array( $per_page, $offset ) );
		$items = $wpdb->get_results( $wpdb->prepare( $query, $args ) );

		if ( null === $items ) {
			$items = array();
		}

		$total = (int) $total;
		$total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 0;

		wp_send_json_success( array(
			'items' => $items,
			'total' => $total,
			'paged' => $paged,
			'per_page' => $per_page,
			'total_pages' => $total_pages,
		) );
	}

	public function ajax_save_file() {
		$this->verify_request();
		if ( ! $this->ensure_database() ) {
			wp_send_json_error( array( 'message' => 'جدول ماژول فرآیند خرید آماده نیست.' ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$filename_input = isset( $_POST['filename'] ) ? sanitize_text_field( wp_unslash( $_POST['filename'] ) ) : '';
		$code = isset( $_POST['code'] ) ? wp_unslash( $_POST['code'] ) : '';
		$activate = ! empty( $_POST['activate'] ) ? 1 : 0;

		$title = trim( $title );
		if ( '' === $title ) {
			wp_send_json_error( array( 'message' => 'عنوان نمایشی الزامی است.' ) );
		}

		$code = trim( $code );
		if ( '' === $code ) {
			wp_send_json_error( array( 'message' => 'کد PHP نمی‌تواند خالی باشد.' ) );
		}

		if ( 0 !== strpos( $code, '<?php' ) ) {
			$code = "<?php\n" . $code;
		}

		global $wpdb;
		$table = $this->get_table_name();
		$old_row = null;

		if ( $id > 0 ) {
			$old_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ) );
			if ( ! $old_row ) {
				wp_send_json_error( array( 'message' => 'رکورد موردنظر برای ویرایش یافت نشد.' ) );
			}
		}

		// نام فایل از فیلد جداگانه می‌آید؛ عنوان نمایشی روی آن اثر ندارد
		if ( $old_row && ! empty( $old_row->filename ) ) {
			// در ویرایش، نام فایل قفل است
			$filename = basename( $old_row->filename );
		} else {
			$filename_input = trim( $filename_input );
			$filename_input = preg_replace( '/\.php$/i', '', $filename_input );
			$filename_input = sanitize_file_name( $filename_input );
			$filename_input = preg_replace( '/[^A-Za-z0-9_-]/', '', $filename_input );

			if ( '' === $filename_input ) {
				wp_send_json_error( array( 'message' => 'نام فایل انگلیسی الزامی است و فقط می‌تواند شامل حروف انگلیسی، عدد، - و _ باشد.' ) );
			}

			$filename = $filename_input . '.php';
		}

		$filename = basename( sanitize_file_name( $filename ) );
		if ( ! preg_match( '/\.php$/i', $filename ) ) {
			$filename .= '.php';
		}

		// اگر فایل/رکورد تکراری وجود دارد → خطا بده، rename خودکار نکن
		$conflict = $this->filename_conflict( $filename, $id );
		if ( $conflict ) {
			wp_send_json_error( array( 'message' => $conflict ) );
		}

		$file_path = $this->get_file_path( $filename );
		if ( false === $file_path ) {
			wp_send_json_error( array( 'message' => 'مسیر ذخیره فایل معتبر نیست.' ) );
		}

		$saved = @file_put_contents( $file_path, $code, LOCK_EX );
		if ( false === $saved ) {
			wp_send_json_error( array( 'message' => 'خطا در ذخیره فایل. دسترسی پوشه storage را بررسی کنید.' ) );
		}

		if ( ! file_exists( $file_path ) ) {
			wp_send_json_error( array( 'message' => 'فایل روی دیسک ایجاد نشد.' ) );
		}

		$data = array(
			'title' => $title,
			'filename' => $filename,
			'description' => $description,
			'status' => $activate,
		);
		$formats = array( '%s', '%s', '%s', '%d' );

		if ( $id > 0 ) {
			$updated = $wpdb->update( $table, $data, array( 'id' => $id ), $formats, array( '%d' ) );
			if ( false === $updated ) {
				if ( $old_row && $old_row->filename !== $filename && file_exists( $file_path ) ) {
					@unlink( $file_path );
				}
				wp_send_json_error( array( 'message' => 'خطا در بروزرسانی رکورد دیتابیس: ' . $wpdb->last_error ) );
			}

			if ( $old_row && ! empty( $old_row->filename ) && $old_row->filename !== $filename ) {
				$old_file_path = $this->get_file_path( $old_row->filename );
				if ( false !== $old_file_path && file_exists( $old_file_path ) && $old_file_path !== $file_path ) {
					@unlink( $old_file_path );
				}
			}
			$new_id = $id;
		} else {
			$inserted = $wpdb->insert( $table, $data, $formats );
			if ( false === $inserted ) {
				if ( file_exists( $file_path ) ) {
					@unlink( $file_path );
				}
				wp_send_json_error( array( 'message' => 'خطا در ثبت رکورد دیتابیس: ' . $wpdb->last_error ) );
			}
			$new_id = (int) $wpdb->insert_id;
		}

		wp_send_json_success( array(
			'id' => $new_id,
			'message' => sprintf( 'فایل با موفقیت ذخیره شد. وضعیت: %s', $activate ? 'فعال' : 'غیرفعال' ),
			'filename' => $filename,
			'status' => $activate,
		) );
	}

	public function ajax_validate_code() {
		$this->verify_request();

		$code = isset( $_POST['code'] ) ? wp_unslash( $_POST['code'] ) : '';
		$code = trim( $code );
		if ( '' === $code ) {
			wp_send_json_error( array( 'message' => 'کد خالی است.' ) );
		}

		if ( 0 !== strpos( $code, '<?php' ) ) {
			$code = "<?php\n" . $code;
		}

		if ( ! function_exists( 'exec' ) ) {
			wp_send_json_error( array( 'message' => 'امکان بررسی خودکار PHP در این سرور وجود ندارد؛ تابع exec فعال نیست.' ) );
		}

		$php_binary = $this->find_php_binary();
		if ( ! $php_binary ) {
			wp_send_json_error( array( 'message' => 'PHP CLI پیدا نشد.' ) );
		}

		$temp_file = tempnam( sys_get_temp_dir(), 'ezp_lint_' );
		if ( false === $temp_file ) {
			wp_send_json_error( array( 'message' => 'خطا در ایجاد فایل موقت.' ) );
		}

		$written = @file_put_contents( $temp_file, $code, LOCK_EX );
		if ( false === $written ) {
			@unlink( $temp_file );
			wp_send_json_error( array( 'message' => 'نوشتن فایل موقت ناموفق بود.' ) );
		}

		$output = array();
		$return_var = 0;
		$command = escapeshellarg( $php_binary ) . ' -l ' . escapeshellarg( $temp_file ) . ' 2>&1';
		@exec( $command, $output, $return_var );
		@unlink( $temp_file );

		if ( 0 === $return_var ) {
			wp_send_json_success( array( 'message' => 'کد PHP از نظر نحوی صحیح است.' ) );
		}

		$error_msg = implode( "\n", $output );
		if ( '' === trim( $error_msg ) ) {
			$error_msg = 'کد PHP دارای خطای نحوی است.';
		}
		wp_send_json_error( array( 'message' => $error_msg ) );
	}

	/**
	 * =========================================================
	 * جدید: ایجاد اجباری جدول از طریق AJAX
	 * =========================================================
	 */
	public function ajax_force_install() {
		$this->verify_request();

		if ( ! class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			$install_file = dirname( __FILE__ ) . '/class-install.php';
			if ( file_exists( $install_file ) ) {
				require_once $install_file;
			}
		}

		if ( ! class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			wp_send_json_error( array( 'message' => 'فایل نصب یافت نشد.' ) );
		}

		global $wpdb;
		$wpdb->last_error = '';

		$result = EzLens_Purchase_Process_Install::force_create_table();

		if ( $result && EzLens_Purchase_Process_Install::is_ready() ) {
			wp_send_json_success( array(
				'message' => 'جدول و پوشه storage با موفقیت ایجاد/بررسی شدند.',
				'table'   => EzLens_Purchase_Process_Install::get_table_name(),
			) );
		} else {
			$error_detail = ! empty( $wpdb->last_error ) ? $wpdb->last_error : 'جزئیات در debug.log ثبت شده است.';
			wp_send_json_error( array(
				'message' => 'خطا در ایجاد جدول. ' . $error_detail,
			) );
		}
	}

	private function find_php_binary() {
		$candidates = array();
		if ( defined( 'PHP_BINARY' ) && is_string( PHP_BINARY ) && '' !== PHP_BINARY ) {
			$candidates[] = PHP_BINARY;
		}
		$candidates[] = 'php';
		if ( function_exists( 'ini_get' ) ) {
			$extension_dir = ini_get( 'extension_dir' );
			if ( $extension_dir ) {
				$php_dir = dirname( wp_normalize_path( $extension_dir ) );
				$candidates[] = $php_dir . DIRECTORY_SEPARATOR . 'php.exe';
			}
		}
		foreach ( $candidates as $candidate ) {
			if ( 'php' === $candidate ) {
				return 'php';
			}
			if ( is_string( $candidate ) && '' !== $candidate && file_exists( $candidate ) && is_file( $candidate ) ) {
				return $candidate;
			}
		}
		return false;
	}

	/**
	 * اگر نام فایل در دیتابیس یا روی دیسک تکراری باشد پیام خطا برمی‌گرداند؛ در غیر این صورت null.
	 * دیگر rename خودکار انجام نمی‌شود تا فایل‌های تکراری ساخته نشوند.
	 */
	private function filename_conflict( $filename, $exclude_id = 0 ) {
		global $wpdb;
		$table = $this->get_table_name();

		$filename = basename( sanitize_file_name( $filename ) );
		if ( ! preg_match( '/\.php$/i', $filename ) ) {
			$filename .= '.php';
		}

		$existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE filename = %s AND id != %d LIMIT 1", $filename, $exclude_id ) );
		if ( null !== $existing_id ) {
			return sprintf( 'فایلی با نام «%s» از قبل در دیتابیس ثبت شده است (شناسه: %d). عنوان یا نام را تغییر دهید.', $filename, (int) $existing_id );
		}

		$file_path = $this->get_file_path( $filename );
		if ( false === $file_path ) {
			return 'مسیر ذخیره فایل معتبر نیست.';
		}

		if ( file_exists( $file_path ) ) {
			// در حالت ویرایش، همان فایل متعلق به همین رکورد مجاز است
			if ( $exclude_id > 0 ) {
				$current_owner = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE filename = %s LIMIT 1", $filename ) );
				if ( null === $current_owner || (int) $current_owner === (int) $exclude_id ) {
					return null;
				}
			}
			return sprintf( 'فایل فیزیکی «%s» از قبل در پوشه storage وجود دارد. نام دیگری انتخاب کنید یا فایل قبلی را حذف کنید.', $filename );
		}

		return null;
	}

	private function verify_request() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ezpurchase_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'درخواست نامعتبر یا منقضی شده است.' ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'دسترسی غیرمجاز.' ) );
		}
	}

	private function get_file_path( $filename ) {
		$filename = basename( sanitize_file_name( $filename ) );
		if ( ! preg_match( '/^[a-zA-Z0-9._-]+\.php$/', $filename ) ) {
			return false;
		}

		if ( class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			$storage_dir = EzLens_Purchase_Process_Install::get_storage_dir();
		} else {
			$storage_dir = dirname( __FILE__ ) . '/storage/';
		}

		if ( ! is_string( $storage_dir ) || '' === $storage_dir ) {
			return false;
		}

		if ( ! file_exists( $storage_dir ) ) {
			if ( ! wp_mkdir_p( $storage_dir ) ) {
				return false;
			}
		}

		if ( ! is_dir( $storage_dir ) ) {
			return false;
		}

		$storage_dir = trailingslashit( wp_normalize_path( $storage_dir ) );
		$file_path = $storage_dir . $filename;

		$real_storage = realpath( $storage_dir );
		if ( false === $real_storage ) {
			return false;
		}
		$real_storage = trailingslashit( wp_normalize_path( $real_storage ) );

		if ( file_exists( $file_path ) ) {
			$real_file = realpath( $file_path );
			if ( false === $real_file ) {
				return false;
			}
			$real_file = wp_normalize_path( $real_file );
			if ( 0 !== strpos( $real_file, $real_storage ) ) {
				return false;
			}
		}

		return $file_path;
	}
}

EzLens_Purchase_Process_Ajax::get_instance();