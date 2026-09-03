<?php
if (!defined('ABSPATH')) exit;
/** Passwordless mobile authentication for trusted EzLens apps. */
class EzLens_Auth_App_Auth {
    private static $instance = null; 
    private $table;
    private $option_name = 'ezlens_tables_app_tokens_created';
    
    public static function get_instance(){
        return self::$instance ?: self::$instance = new self();
    }
    
    private function __construct(){
        global $wpdb;
        $this->table = $wpdb->prefix . 'ezlens_app_tokens';
        add_action('init', [$this, 'create_table']);
    }
    
    public function create_table(){
        // اگر قبلاً ایجاد شده، دیگر کاری نکن
        if (get_option($this->option_name, false)) {
            return;
        }
        
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            token_hash char(64) NOT NULL,
            device_name varchar(190) DEFAULT '',
            platform varchar(40) DEFAULT '',
            expires_at datetime NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_used_at datetime DEFAULT NULL,
            revoked_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY token_hash (token_hash),
            KEY user_id (user_id),
            KEY expires_at (expires_at)
        ) {$charset}";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // بعد از ایجاد، آپشن را ثبت کن تا دیگر اجرا نشود
        update_option($this->option_name, true);
    }
    
    public function issue_token($user_id,$device='',$platform=''){
        global $wpdb;
        $plain = 'ezat_' . bin2hex(random_bytes(32));
        $wpdb->insert($this->table,[
            'user_id'=>(int)$user_id,
            'token_hash'=>hash('sha256', $plain),
            'device_name'=>sanitize_text_field($device),
            'platform'=>sanitize_text_field($platform),
            'expires_at'=>gmdate('Y-m-d H:i:s', time() + max(1, min(365, (int)EzLens_Auth_Settings::get('app_token_days') ?: 30)) * DAY_IN_SECONDS),
            'created_at'=>current_time('mysql')
        ]);
        return $plain;
    }
    
    public function authenticate($token){
        global $wpdb;
        $hash = hash('sha256', (string)$token);
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE token_hash=%s AND revoked_at IS NULL AND expires_at>%s LIMIT 1", $hash, current_time('mysql')));
        if(!$row) return 0;
        $wpdb->update($this->table, ['last_used_at'=>current_time('mysql')], ['id'=>$row->id]);
        return (int)$row->user_id;
    }
    
    public function revoke($token){
        global $wpdb;
        return (bool)$wpdb->update($this->table, ['revoked_at'=>current_time('mysql')], ['token_hash'=>hash('sha256', (string)$token)]);
    }
    
    public function revoke_user($user_id){
        global $wpdb;
        return $wpdb->update($this->table, ['revoked_at'=>current_time('mysql')], ['user_id'=>(int)$user_id, 'revoked_at'=>null]);
    }
}