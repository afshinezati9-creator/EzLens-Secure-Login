<?php
/**
 * کلاس نصب و راه‌اندازی ماژول فرآیند خرید
 *
 * @package EzLens_Secure_Login
 * @subpackage Purchase_Process
 * @version 1.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Purchase_Process_Install {

	const DB_VERSION        = '1.5';
	const DB_VERSION_OPTION = 'ezlens_purchase_process_db_version';

	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_purchase_scripts';
	}

	public static function get_storage_dir() {
		if ( defined( 'EZLAUTH_MODULES_DIR' ) && EZLAUTH_MODULES_DIR ) {
			return trailingslashit( wp_normalize_path( EZLAUTH_MODULES_DIR ) ) . 'purchase-process/storage/';
		}
		return trailingslashit( wp_normalize_path( dirname( __FILE__ ) ) ) . 'storage/';
	}

	public static function install() {
		$table_ready   = self::create_or_update_table();
		$storage_ready = self::create_storage_folder();
		if ( ! $table_ready || ! $storage_ready ) {
			self::log( 'Installation failed. table=' . ( $table_ready ? 'ok' : 'fail' ) . ' storage=' . ( $storage_ready ? 'ok' : 'fail' ) );
			return false;
		}
		update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
		return self::is_ready();
	}

	public static function maybe_install() {
		static $running = false;
		if ( $running ) {
			return self::is_ready();
		}
		$running = true;

		$current_version = get_option( self::DB_VERSION_OPTION, '' );

		if ( ! self::table_exists() ) {
			$result  = self::install();
			$running = false;
			return $result;
		}

		if ( '' === $current_version || version_compare( $current_version, self::DB_VERSION, '<' ) ) {
			$table_ready = self::create_or_update_table();
			if ( ! $table_ready ) {
				self::log( 'Database migration failed.' );
				$running = false;
				return false;
			}
			update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
		}

		$storage_ready = self::create_storage_folder();
		if ( ! $storage_ready ) {
			$running = false;
			return false;
		}

		$result  = self::is_ready();
		$running = false;
		return $result;
	}

	/**
	 * بررسی وجود جدول – مقاوم در برابر case-sensitivity ویندوز/WAMP
	 */
	public static function table_exists() {
		global $wpdb;

		$table_name = self::get_table_name();
		$wpdb->last_error = '';

		// روش ۱: information_schema با مقایسه بدون حساسیت به حروف
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT TABLE_NAME FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = DATABASE()
				 AND LOWER(TABLE_NAME) = LOWER(%s)
				 LIMIT 1",
				$table_name
			)
		);

		if ( ! empty( $found ) ) {
			return true;
		}

		// روش ۲: SHOW TABLES
		$wpdb->last_error = '';
		$like = $wpdb->esc_like( $table_name );
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );
		if ( $found ) {
			return true;
		}

		// روش ۳: مقایسه بدون حساسیت (برای ویندوز)
		$wpdb->last_error = '';
		$all_tables = $wpdb->get_col( 'SHOW TABLES' );
		if ( is_array( $all_tables ) ) {
			$target = strtolower( $table_name );
			foreach ( $all_tables as $t ) {
				if ( strtolower( $t ) === $target ) {
					return true;
				}
			}
		}

		return false;
	}

	private static function create_or_update_table() {
		global $wpdb;
		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$wpdb->last_error = '';

		$sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`title` varchar(255) NOT NULL DEFAULT '',
			`filename` varchar(255) NOT NULL DEFAULT '',
			`description` text NULL,
			`status` tinyint(1) NOT NULL DEFAULT 0,
			`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `filename` (`filename`),
			KEY `status` (`status`)
		) {$charset_collate};";

		$result = $wpdb->query( $sql );

		if ( false === $result && ! empty( $wpdb->last_error ) ) {
			self::log( 'CREATE TABLE failed: ' . $wpdb->last_error );
			$wpdb->last_error = '';
			$sql2 = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`title` varchar(255) NOT NULL DEFAULT '',
				`filename` varchar(255) NOT NULL DEFAULT '',
				`description` text NULL,
				`status` tinyint(1) NOT NULL DEFAULT 0,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `filename` (`filename`),
				KEY `status` (`status`)
			) {$charset_collate};";
			$result = $wpdb->query( $sql2 );
		}

		if ( false === $result && ! empty( $wpdb->last_error ) ) {
			self::log( 'Unable to create database table: ' . $wpdb->last_error );
			return false;
		}

		usleep( 80000 );

		if ( ! self::table_exists() ) {
			self::log( 'Table still not detected after CREATE: ' . $table_name );
			return false;
		}

		self::ensure_columns();
		self::ensure_indexes();

		return true;
	}

	private static function ensure_columns() {
		global $wpdb;
		$table_name = self::get_table_name();

		$wpdb->last_error = '';
		$columns = $wpdb->get_results( "SHOW COLUMNS FROM `{$table_name}`" );

		if ( empty( $columns ) ) {
			$lower = strtolower( $table_name );
			$columns = $wpdb->get_results( "SHOW COLUMNS FROM `{$lower}`" );
		}

		if ( empty( $columns ) ) {
			self::log( 'Unable to inspect columns: ' . $wpdb->last_error );
			return false;
		}

		$existing = array();
		foreach ( $columns as $column ) {
			if ( isset( $column->Field ) ) {
				$existing[] = strtolower( $column->Field );
			}
		}

		$needed = array(
			'title'       => "ALTER TABLE `{$table_name}` ADD COLUMN `title` varchar(255) NOT NULL DEFAULT '' AFTER `id`",
			'filename'    => "ALTER TABLE `{$table_name}` ADD COLUMN `filename` varchar(255) NOT NULL DEFAULT '' AFTER `title`",
			'description' => "ALTER TABLE `{$table_name}` ADD COLUMN `description` text NULL AFTER `filename`",
			'status'      => "ALTER TABLE `{$table_name}` ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 AFTER `description`",
			'created_at'  => "ALTER TABLE `{$table_name}` ADD COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `status`",
			'updated_at'  => "ALTER TABLE `{$table_name}` ADD COLUMN `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_at`",
		);

		foreach ( $needed as $col => $sql ) {
			if ( ! in_array( strtolower( $col ), $existing, true ) ) {
				$wpdb->last_error = '';
				$wpdb->query( $sql );
			}
		}

		return true;
	}

	private static function get_indexes() {
		global $wpdb;
		$table_name = self::get_table_name();

		$wpdb->last_error = '';
		$indexes = $wpdb->get_results( "SHOW INDEX FROM `{$table_name}`" );

		if ( empty( $indexes ) ) {
			$lower = strtolower( $table_name );
			$indexes = $wpdb->get_results( "SHOW INDEX FROM `{$lower}`" );
		}

		$result = array();
		if ( is_array( $indexes ) ) {
			foreach ( $indexes as $index ) {
				if ( isset( $index->Key_name ) && '' !== $index->Key_name ) {
					$result[ strtolower( $index->Key_name ) ] = true;
				}
			}
		}
		return $result;
	}

	private static function ensure_indexes() {
		global $wpdb;
		$table_name = self::get_table_name();
		$indexes    = self::get_indexes();

		if ( ! isset( $indexes['primary'] ) ) {
			$wpdb->last_error = '';
			$wpdb->query( "ALTER TABLE `{$table_name}` ADD PRIMARY KEY (`id`)" );
		}

		$indexes = self::get_indexes();
		if ( ! isset( $indexes['filename'] ) ) {
			$wpdb->last_error = '';
			$wpdb->query( "ALTER TABLE `{$table_name}` ADD UNIQUE KEY `filename` (`filename`)" );
		}

		$indexes = self::get_indexes();
		if ( ! isset( $indexes['status'] ) ) {
			$wpdb->last_error = '';
			$wpdb->query( "ALTER TABLE `{$table_name}` ADD KEY `status` (`status`)" );
		}

		return true;
	}

	public static function create_storage_folder() {
		$storage_dir = self::get_storage_dir();
		if ( ! is_string( $storage_dir ) || '' === $storage_dir ) {
			self::log( 'Storage directory path is empty.' );
			return false;
		}

		if ( ! is_dir( $storage_dir ) ) {
			if ( ! wp_mkdir_p( $storage_dir ) ) {
				self::log( 'Unable to create storage directory: ' . $storage_dir );
				return false;
			}
		}

		if ( ! is_dir( $storage_dir ) ) {
			self::log( 'Storage path is not a directory: ' . $storage_dir );
			return false;
		}

		$index_php = trailingslashit( $storage_dir ) . 'index.php';
		if ( ! file_exists( $index_php ) ) {
			@file_put_contents( $index_php, "<?php\ndefined( 'ABSPATH' ) || exit;\n", LOCK_EX );
		}

		$index_html = trailingslashit( $storage_dir ) . 'index.html';
		if ( ! file_exists( $index_html ) ) {
			@file_put_contents( $index_html, '', LOCK_EX );
		}

		$htaccess_file    = trailingslashit( $storage_dir ) . '.htaccess';
		$htaccess_content = "# EzLens Purchase Process Storage\nOptions -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n";
		if ( ! file_exists( $htaccess_file ) || '' === trim( (string) @file_get_contents( $htaccess_file ) ) ) {
			@file_put_contents( $htaccess_file, $htaccess_content, LOCK_EX );
		}

		if ( ! is_readable( $storage_dir ) || ! is_writable( $storage_dir ) ) {
			self::log( 'Storage directory is not readable/writable: ' . $storage_dir );
			return false;
		}

		return true;
	}

	public static function is_ready() {
		if ( ! self::table_exists() ) {
			return false;
		}
		$storage_dir = self::get_storage_dir();
		if ( ! $storage_dir || ! is_dir( $storage_dir ) || ! is_readable( $storage_dir ) || ! is_writable( $storage_dir ) ) {
			return false;
		}
		return true;
	}

	public static function uninstall() {
		global $wpdb;
		$table_name = self::get_table_name();
		$wpdb->last_error = '';
		$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );
		$wpdb->query( 'DROP TABLE IF EXISTS `' . strtolower( $table_name ) . '`' );
		delete_option( self::DB_VERSION_OPTION );
		return true;
	}

	/**
	 * ایجاد اجباری جدول – نسخه نهایی مقاوم در برابر case
	 */
	public static function force_create_table() {
		global $wpdb;

		$table_name = self::get_table_name();

		// اگر از قبل وجود دارد (حتی با حروف متفاوت)
		if ( self::table_exists() ) {
			self::create_storage_folder();
			self::ensure_columns();
			self::ensure_indexes();
			update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
			self::log( 'Table already exists (detected). Marked as ready.' );
			return true;
		}

		$wpdb->last_error = '';
		$charset_collate  = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`title` varchar(255) NOT NULL DEFAULT '',
			`filename` varchar(255) NOT NULL DEFAULT '',
			`description` text NULL,
			`status` tinyint(1) NOT NULL DEFAULT 0,
			`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `filename` (`filename`),
			KEY `status` (`status`)
		) {$charset_collate};";

		$result = $wpdb->query( $sql );

		if ( false === $result && ! empty( $wpdb->last_error ) ) {
			self::log( 'Direct CREATE failed: ' . $wpdb->last_error );
			$wpdb->last_error = '';

			$lower_name = strtolower( $table_name );
			$sql2 = "CREATE TABLE IF NOT EXISTS `{$lower_name}` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`title` varchar(255) NOT NULL DEFAULT '',
				`filename` varchar(255) NOT NULL DEFAULT '',
				`description` text NULL,
				`status` tinyint(1) NOT NULL DEFAULT 0,
				`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				UNIQUE KEY `filename` (`filename`),
				KEY `status` (`status`)
			) {$charset_collate};";
			$wpdb->query( $sql2 );
		}

		usleep( 120000 );
		$wpdb->last_error = '';

		if ( self::table_exists() ) {
			self::ensure_columns();
			self::ensure_indexes();
			self::create_storage_folder();
			update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
			self::log( 'Table created/detected successfully via force_create_table.' );
			return true;
		}

		self::log( 'force_create_table ultimately failed. Table still missing: ' . $table_name );
		return false;
	}

	private static function log( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'EzLens Purchase Process: ' . $message );
		}
	}
}

add_action(
	'plugins_loaded',
	array( 'EzLens_Purchase_Process_Install', 'maybe_install' ),
	1
);
