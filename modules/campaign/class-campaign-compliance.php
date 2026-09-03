<?php
if (!defined('ABSPATH')) exit;
/** Campaign consent/unsubscribe service. */
class EzLens_Auth_Campaign_Compliance {
    private static $instance = null; 
    private $table;
    private $option_name = 'ezlens_tables_campaign_unsubscribes_created';
    
    public static function get_instance(){
        return self::$instance ?: self::$instance = new self();
    }
    
    private function __construct(){
        global $wpdb;
        $this->table = $wpdb->prefix . 'ezlens_campaign_unsubscribes';
        add_action('init', [$this, 'create_table']);
        add_action('init', [$this, 'handle_request']);
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
            channel varchar(20) NOT NULL,
            email varchar(190) DEFAULT '',
            phone varchar(30) DEFAULT '',
            token_hash char(64) DEFAULT '',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY channel (channel),
            KEY email (email),
            KEY phone (phone),
            KEY token_hash (token_hash)
        ) {$charset}";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // بعد از ایجاد، آپشن را ثبت کن تا دیگر اجرا نشود
        update_option($this->option_name, true);
    }
    
    public function token($email){
        $email = strtolower(sanitize_email($email));
        $enc = rtrim(strtr(base64_encode($email), '+/', '-_'), '=');
        return $enc . '.' . hash_hmac('sha256', $email, wp_salt('auth'));
    }
    
    public function is_unsubscribed($email,$phone=''){
        global $wpdb;
        $email = strtolower(sanitize_email($email));
        $phone = sanitize_text_field($phone);
        $q = "SELECT id FROM {$this->table} WHERE " . ($email ? 'email=%s' : 'phone=%s') . " LIMIT 1";
        $v = $email ?: $phone;
        return (bool)$wpdb->get_var($wpdb->prepare($q, $v));
    }
    
    public function unsubscribe($email){
        global $wpdb;
        $email = strtolower(sanitize_email($email));
        if(!$email || !is_email($email)) return false;
        if($this->is_unsubscribed($email)) return true;
        return (bool)$wpdb->insert($this->table,[
            'channel'=>'email',
            'email'=>$email,
            'token_hash'=>hash('sha256', $this->token($email)),
            'created_at'=>current_time('mysql')
        ]);
    }
    
    public function url($email){
        return add_query_arg(['ezlens_unsubscribe'=>'1','token'=>$this->token($email)], home_url('/'));
    }
    
    public function handle_request(){
        if(empty($_GET['ezlens_unsubscribe']) || empty($_GET['token'])) return;
        $token = sanitize_text_field(wp_unslash($_GET['token']));
        $parts = explode('.', $token, 2);
        $email = '';
        if(count($parts) === 2){
            $email = base64_decode(strtr($parts[0], '-_', '+/'));
            $email = is_string($email) ? strtolower(sanitize_email($email)) : '';
            if(!$email || !hash_equals($parts[1], hash_hmac('sha256', $email, wp_salt('auth')))){
                $email = '';
            }
        }
        if($email){
            $this->unsubscribe($email);
        }
        wp_die('<div style="font-family:Vazirmatn,Arial;text-align:center;margin:15vh auto;max-width:520px"><h2>لغو عضویت انجام شد</h2><p>از این پس پیام‌های تبلیغاتی کمپین برای این ایمیل ارسال نمی‌شود.</p></div>', 'EzLens', ['response'=>200]);
    }
}