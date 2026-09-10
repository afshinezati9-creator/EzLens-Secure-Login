<?php
/**
 * کلاس بارگذاری فایل‌های فعال ماژول فرآیند خرید
 *
 * @package EzLens_Secure_Login
 * @subpackage Purchase_Process
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Purchase_Process_Loader {

	private static $instance = null;
	private $loaded = false;

	/** @var string[] توابعی که در همین درخواست از snippetها لود شده‌اند */
	private $loaded_functions = array();

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_active_files' ), 20 );
	}

	private function ensure_database_ready() {
		if ( ! class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			$install_file = dirname( __FILE__ ) . '/class-install.php';
			if ( file_exists( $install_file ) ) {
				require_once $install_file;
			}
		}

		if ( ! class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			return false;
		}

		if ( EzLens_Purchase_Process_Install::is_ready() ) {
			return true;
		}

		error_log( 'EzLens Purchase Process: دیتابیس آماده نیست، تلاش برای ایجاد جدول...' );
		$result = EzLens_Purchase_Process_Install::force_create_table();

		if ( $result ) {
			error_log( 'EzLens Purchase Process: جدول با موفقیت ایجاد شد (خودترمیمی).' );
			return true;
		}

		error_log( 'EzLens Purchase Process: ایجاد خودکار جدول ناموفق بود.' );
		return false;
	}

	private function get_storage_dir() {
		if ( class_exists( 'EzLens_Purchase_Process_Install' ) ) {
			return EzLens_Purchase_Process_Install::get_storage_dir();
		}
		return trailingslashit( dirname( __FILE__ ) ) . 'storage/';
	}

	/**
	 * استخراج نام توابع از کد (token + regex fallback)
	 * شامل توابع داخل if (!function_exists()) هم می‌شود.
	 */
	private function extract_function_names( $code ) {
		$names = array();

		// Regex سریع و عملی برای توابع سراسری
		if ( preg_match_all( '/\bfunction\s+&?([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $code, $m ) ) {
			$names = $m[1];
		}

		// حذف نام‌های متداول متدهای بی‌ربط اگر لازم شد؛ فعلاً همه را نگه می‌داریم
		$names = array_values( array_unique( array_filter( $names ) ) );
		return $names;
	}

	private function deactivate_item( $table, $id, $reason ) {
		global $wpdb;
		$wpdb->update(
			$table,
			array( 'status' => 0 ),
			array( 'id' => (int) $id ),
			array( '%d' ),
			array( '%d' )
		);
		error_log( 'EzLens Purchase Process: رکورد #' . (int) $id . ' غیرفعال شد. دلیل: ' . $reason );
	}

	public function load_active_files() {
		if ( $this->loaded ) {
			return;
		}
		$this->loaded = true;

		if ( ! $this->ensure_database_ready() ) {
			error_log( 'EzLens Purchase Process: دیتابیس ماژول آماده نیست؛ Loader متوقف شد.' );
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ezlens_purchase_scripts';

		// در ویندوز ممکن است نام جدول lowercase باشد
		$items = $wpdb->get_results(
			"SELECT id, title, filename, status FROM `{$table}` WHERE status = 1 ORDER BY id ASC"
		);

		if ( null === $items && ! empty( $wpdb->last_error ) ) {
			// fallback lowercase table name
			$lower = strtolower( $table );
			$wpdb->last_error = '';
			$items = $wpdb->get_results(
				"SELECT id, title, filename, status FROM `{$lower}` WHERE status = 1 ORDER BY id ASC"
			);
		}

		if ( null === $items && ! empty( $wpdb->last_error ) ) {
			error_log( 'EzLens Purchase Process: خطای دیتابیس Loader - ' . $wpdb->last_error );
			return;
		}

		if ( empty( $items ) ) {
			return;
		}

		$storage_dir = trailingslashit( $this->get_storage_dir() );

		foreach ( $items as $item ) {
			$filename = basename( sanitize_file_name( $item->filename ) );

			if ( ! preg_match( '/^[a-zA-Z0-9._-]+\.php$/', $filename ) ) {
				error_log( 'EzLens Purchase Process: filename نامعتبر برای رکورد #' . $item->id );
				continue;
			}

			$file_path = $storage_dir . $filename;

			if ( ! file_exists( $file_path ) ) {
				$this->deactivate_item( $table, $item->id, 'فایل فیزیکی یافت نشد: ' . $filename );
				continue;
			}

			$real_storage = realpath( $storage_dir );
			$real_file    = realpath( $file_path );

			if ( false === $real_storage || false === $real_file ) {
				error_log( 'EzLens Purchase Process: مسیر فایل نامعتبر است - ' . $filename );
				continue;
			}

			$storage_prefix  = trailingslashit( wp_normalize_path( $real_storage ) );
			$normalized_file = wp_normalize_path( $real_file );

			if ( 0 !== strpos( $normalized_file, $storage_prefix ) ) {
				error_log( 'EzLens Purchase Process: تلاش برای Load فایل خارج از storage - ' . $filename );
				continue;
			}

			if ( ! is_readable( $real_file ) ) {
				error_log( 'EzLens Purchase Process: فایل قابل خواندن نیست - ' . $filename );
				continue;
			}

			$code = @file_get_contents( $real_file );
			if ( false === $code ) {
				error_log( 'EzLens Purchase Process: خواندن فایل ناموفق - ' . $filename );
				continue;
			}

			$fn_names = $this->extract_function_names( $code );
			$conflict = null;

			foreach ( $fn_names as $fn ) {
				if ( function_exists( $fn ) || isset( $this->loaded_functions[ $fn ] ) ) {
					$conflict = $fn;
					break;
				}
			}

			if ( null !== $conflict ) {
				$this->deactivate_item(
					$table,
					$item->id,
					'تداخل تابع "' . $conflict . '" در فایل ' . $filename . ' (از قبل تعریف شده). فایل لود نشد.'
				);
				continue;
			}

			// include در شرایطی که redeclare ممکن است Fatal غیرقابل catch باشد
			// بنابراین فقط بعد از عبور از چک بالا include می‌کنیم
			$before = get_defined_functions();
			$before_user = isset( $before['user'] ) ? $before['user'] : array();

			try {
				include_once $real_file;
			} catch ( Throwable $e ) {
				$this->deactivate_item(
					$table,
					$item->id,
					'خطا در Load فایل "' . $filename . '": ' . $e->getMessage()
				);
				continue;
			}

			$after = get_defined_functions();
			$after_user = isset( $after['user'] ) ? $after['user'] : array();
			$new_fns = array_diff( $after_user, $before_user );
			foreach ( $new_fns as $fn ) {
				$this->loaded_functions[ $fn ] = $filename;
			}
			foreach ( $fn_names as $fn ) {
				$this->loaded_functions[ $fn ] = $filename;
			}
		}
	}
}

EzLens_Purchase_Process_Loader::get_instance();
