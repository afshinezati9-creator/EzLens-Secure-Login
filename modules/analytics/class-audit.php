<?php
if (!defined('ABSPATH')) exit;
/** Centralized security/admin audit trail. */
class EzLens_Auth_Audit {
    private static $instance = null;
    private $table;
    private $option_name = 'ezlens_tables_audit_created';
    
    public static function get_instance(){ 
        return self::$instance ?: self::$instance = new self(); 
    }
    
    private function __construct(){ 
        global $wpdb; 
        $this->table = $wpdb->prefix . 'ezlens_audit_log'; 
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
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            action varchar(100) NOT NULL,
            object_type varchar(50) DEFAULT '',
            object_id bigint(20) unsigned NOT NULL DEFAULT 0,
            details longtext,
            ip varchar(45) DEFAULT '',
            user_agent text,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY created_at (created_at),
            KEY object (object_type, object_id)
        ) {$charset}"; 
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // بعد از ایجاد، آپشن را ثبت کن تا دیگر اجرا نشود
        update_option($this->option_name, true);
    }
    
    public function log($action,$details=[],$object_type='',$object_id=0){ 
        global $wpdb; 
        $wpdb->insert($this->table,[
            'user_id'=>get_current_user_id(),
            'action'=>sanitize_key($action),
            'object_type'=>sanitize_key($object_type),
            'object_id'=>(int)$object_id,
            'details'=>wp_json_encode($details,JSON_UNESCAPED_UNICODE),
            'ip'=>sanitize_text_field($_SERVER['REMOTE_ADDR']??''),
            'user_agent'=>substr(sanitize_text_field($_SERVER['HTTP_USER_AGENT']??''),0,1000),
            'created_at'=>current_time('mysql')
        ]); 
        return (int)$wpdb->insert_id; 
    }
    
    public function recent($limit=50){ 
        global $wpdb; 
        $limit=max(1,min(500,(int)$limit)); 
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT %d",$limit),ARRAY_A); 
    }
    
    public function count(){ 
        global $wpdb; 
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$this->table}"); 
    }
}