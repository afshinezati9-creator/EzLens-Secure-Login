<?php
if (!defined('ABSPATH')) exit;
/** Customer notification center. */
class EzLens_Auth_Notifications {
    private static $instance = null; 
    private $table;
    private $option_name = 'ezlens_tables_notifications_created';
    
    public static function get_instance(){
        return self::$instance ?: self::$instance = new self();
    }
    
    private function __construct(){
        global $wpdb;
        $this->table = $wpdb->prefix . 'ezlens_notifications';
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
            title varchar(255) NOT NULL,
            message text NOT NULL,
            type varchar(40) DEFAULT 'info',
            url varchar(500) DEFAULT '',
            is_read tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY is_read (is_read),
            KEY created_at (created_at)
        ) {$charset}";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // بعد از ایجاد، آپشن را ثبت کن تا دیگر اجرا نشود
        update_option($this->option_name, true);
    }
    
    public function create($user_id,$title,$message,$type='info',$url=''){
        global $wpdb;
        $wpdb->insert($this->table,[
            'user_id'=>(int)$user_id,
            'title'=>sanitize_text_field($title),
            'message'=>wp_kses_post($message),
            'type'=>sanitize_key($type),
            'url'=>esc_url_raw($url),
            'created_at'=>current_time('mysql')
        ]);
        return (int)$wpdb->insert_id;
    }
    
    public function list_for_user($user_id,$limit=30){
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table} WHERE user_id=%d ORDER BY id DESC LIMIT %d",$user_id,max(1,min(100,(int)$limit))),ARRAY_A);
    }
    
    public function unread_count($user_id){
        global $wpdb;
        return (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id=%d AND is_read=0",$user_id));
    }
    
    public function mark_read($user_id,$id=0){
        global $wpdb;
        if($id){
            return (bool)$wpdb->update($this->table,['is_read'=>1],['id'=>(int)$id,'user_id'=>(int)$user_id]);
        }
        return (bool)$wpdb->update($this->table,['is_read'=>1],['user_id'=>(int)$user_id]);
    }
}