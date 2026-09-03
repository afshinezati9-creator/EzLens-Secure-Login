<?php
/**
 * کلاس مدیریت کمپین‌ها (نسخه نهایی با رفع خطاهای مخاطبان و گروه‌ها)
 * @version 2.6.1
 */
class EzLens_Auth_Campaign {

    private static $instance = null;
    private $campaigns_table;
    private $contacts_table;
    private $groups_table;
    private $campaign_groups_table;
    private $track_table;
    private $recipients_table;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->campaigns_table = $wpdb->prefix . 'ezlens_campaigns';
        $this->contacts_table = $wpdb->prefix . 'ezlens_campaign_contacts';
        $this->groups_table = $wpdb->prefix . 'ezlens_campaign_groups';
        $this->campaign_groups_table = $wpdb->prefix . 'ezlens_campaign_group_relations';
        $this->track_table = $wpdb->prefix . 'ezlens_campaign_tracks';
        $this->recipients_table = $wpdb->prefix . 'ezlens_campaign_recipients';
        add_action('init', [$this, 'create_tables']);
        add_action('template_redirect', [$this, 'handle_track_image']);
    }

    /**
     * ایجاد جداول دیتابیس (با ستون category)
     */
    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // جدول کمپین‌ها
        $campaigns_sql = "CREATE TABLE IF NOT EXISTS {$this->campaigns_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            type varchar(20) NOT NULL DEFAULT 'email',
            subject varchar(255) DEFAULT '',
            message longtext NOT NULL,
            file_attachment varchar(255) DEFAULT '',
            status varchar(20) NOT NULL DEFAULT 'draft',
            scheduled_at datetime DEFAULT NULL,
            sent_at datetime DEFAULT NULL,
            stats longtext,
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        // جدول مخاطبان دستی با ستون category
        $contacts_sql = "CREATE TABLE IF NOT EXISTS {$this->contacts_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) DEFAULT '',
            category varchar(100) DEFAULT 'عمومی',
            extra_fields longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email),
            KEY phone (phone),
            KEY category (category),
            KEY created_at (created_at)
        ) $charset_collate;";

        // جدول گروه‌ها
        $groups_sql = "CREATE TABLE IF NOT EXISTS {$this->groups_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            type varchar(20) NOT NULL DEFAULT 'custom',
            user_filters longtext,
            contact_ids longtext,
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY created_by (created_by)
        ) $charset_collate;";

        // جدول ارتباط کمپین با گروه‌ها
        $relation_sql = "CREATE TABLE IF NOT EXISTS {$this->campaign_groups_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            campaign_id bigint(20) NOT NULL,
            group_id bigint(20) NOT NULL,
            PRIMARY KEY (id),
            KEY campaign_id (campaign_id),
            KEY group_id (group_id)
        ) $charset_collate;";

        // جدول ترک باز شدن ایمیل
        $track_sql = "CREATE TABLE IF NOT EXISTS {$this->track_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            campaign_id bigint(20) NOT NULL,
            recipient_email varchar(100) NOT NULL,
            opened_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ip varchar(45) DEFAULT '',
            user_agent text,
            PRIMARY KEY (id),
            KEY campaign_id (campaign_id),
            KEY recipient_email (recipient_email)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($campaigns_sql);
        dbDelta($contacts_sql);
        dbDelta($groups_sql);
        dbDelta($relation_sql);
        dbDelta($track_sql);

        // ✅ بررسی و اضافه کردن ستون category (برای ارتقا از نسخه‌های قدیمی)
        $this->maybe_add_category_column();
    }

    /**
     * اضافه کردن ستون category در صورت وجود نداشتن (برای ارتقا)
     */
    private function maybe_add_category_column() {
        global $wpdb;
        $row = $wpdb->get_results("SHOW COLUMNS FROM {$this->contacts_table} LIKE 'category'");
        if (empty($row)) {
            $wpdb->query("ALTER TABLE {$this->contacts_table} ADD COLUMN category varchar(100) DEFAULT 'عمومی'");
        }
    }

    /**
     * پردازش تصویر ترک برای تشخیص باز شدن ایمیل
     */
    public function handle_track_image() {
        if (isset($_GET['ezlens_track']) && isset($_GET['campaign_id']) && isset($_GET['email'])) {
            $campaign_id = (int) $_GET['campaign_id'];
            $email = sanitize_email($_GET['email']);
            if ($campaign_id && $email) {
                global $wpdb;
                $wpdb->insert($this->track_table, [
                    'campaign_id' => $campaign_id,
                    'recipient_email' => $email,
                    'opened_at' => current_time('mysql'),
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                ]);
                header('Content-Type: image/gif');
                echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
                exit;
            }
        }
    }

    // ============================================================
    // متدهای کمپین
    // ============================================================

    public function create_campaign($data) {
        global $wpdb;
        $defaults = [
            'name' => '',
            'type' => 'email',
            'subject' => '',
            'message' => '',
            'file_attachment' => '',
            'status' => 'draft',
            'scheduled_at' => null,
            'created_by' => get_current_user_id(),
            'group_ids' => [],
        ];
        $data = wp_parse_args($data, $defaults);

        if (empty($data['name']) || empty($data['message'])) {
            return false;
        }

        $result = $wpdb->insert($this->campaigns_table, [
            'name' => sanitize_text_field($data['name']),
            'type' => sanitize_text_field($data['type']),
            'subject' => sanitize_text_field($data['subject']),
            'message' => wp_kses_post($data['message']),
            'file_attachment' => esc_url_raw($data['file_attachment']),
            'status' => $data['status'],
            'scheduled_at' => $data['scheduled_at'],
            'created_by' => (int) $data['created_by'],
        ]);

        if (!$result) return false;
        $campaign_id = $wpdb->insert_id;

        if (!empty($data['group_ids']) && is_array($data['group_ids'])) {
            foreach ($data['group_ids'] as $group_id) {
                $wpdb->insert($this->campaign_groups_table, [
                    'campaign_id' => $campaign_id,
                    'group_id' => (int) $group_id,
                ]);
            }
        }

        return $campaign_id;
    }

    /**
     * به‌روزرسانی کامل کمپین. فقط وضعیت‌های قابل ویرایش اجازه دارند.
     */
    public function update_campaign($id, $data) {
        global $wpdb;
        $id=(int)$id;
        $campaign=$this->get_campaign($id);
        if(!$campaign) return ['success'=>false,'message'=>'کمپین یافت نشد.'];
        $editable=['draft','scheduled','failed','partial','paused'];
        if(!in_array($campaign->status,$editable,true)) return ['success'=>false,'message'=>'این کمپین در وضعیت «'.$campaign->status.'» قابل ویرایش نیست.'];
        $name=sanitize_text_field($data['name']??'');
        $type=in_array(($data['type']??'email'),['email','sms'],true)?$data['type']:'email';
        $subject=sanitize_text_field($data['subject']??'');
        $message=wp_kses_post($data['message']??'');
        $groups=array_values(array_unique(array_filter(array_map('intval',(array)($data['group_ids']??[])))));
        if($name===''||$message==='') return ['success'=>false,'message'=>'نام و متن پیام الزامی است.'];
        if($type==='email' && $subject==='') return ['success'=>false,'message'=>'موضوع ایمیل الزامی است.'];
        if(!$groups) return ['success'=>false,'message'=>'حداقل یک گروه مخاطب انتخاب کنید.'];
        $scheduled=null;
        if(!empty($data['scheduled_at'])){
            $ts=strtotime(sanitize_text_field($data['scheduled_at']));
            if(!$ts) return ['success'=>false,'message'=>'زمان زمان‌بندی معتبر نیست.'];
            $scheduled=wp_date('Y-m-d H:i:s',$ts);
        }
        $status=$scheduled?'scheduled':'draft';
        $ok=$wpdb->update($this->campaigns_table,[
            'name'=>$name,'type'=>$type,'subject'=>$subject,'message'=>$message,
            'file_attachment'=>esc_url_raw($data['file_attachment']??''),'status'=>$status,
            'scheduled_at'=>$scheduled,'sent_at'=>null,'stats'=>null
        ],['id'=>$id]);
        if($ok===false) return ['success'=>false,'message'=>'خطا در ذخیره کمپین: '.$wpdb->last_error];
        $wpdb->delete($this->campaign_groups_table,['campaign_id'=>$id]);
        foreach($groups as $gid) $wpdb->insert($this->campaign_groups_table,['campaign_id'=>$id,'group_id'=>$gid]);
        // چون محتوا/گروه‌ها عوض شده‌اند، snapshot قبلی حذف می‌شود تا دوباره ساخته شود.
        if($this->table_exists($this->recipients_table)) $wpdb->delete($this->recipients_table,['campaign_id'=>$id]);
        $wpdb->delete($wpdb->prefix.'ezlens_campaign_delivery',['campaign_id'=>$id]);
        return ['success'=>true,'message'=>'کمپین با موفقیت ویرایش شد.','id'=>$id];
    }

    public function duplicate_campaign($id) {
        $campaign=$this->get_campaign((int)$id);
        if(!$campaign) return ['success'=>false,'message'=>'کمپین یافت نشد.'];
        $groups=$this->get_campaign_groups((int)$id);
        $new=$this->create_campaign([
            'name'=>$campaign->name.' — کپی', 'type'=>$campaign->type, 'subject'=>$campaign->subject,
            'message'=>$campaign->message, 'file_attachment'=>$campaign->file_attachment,
            'status'=>'draft','scheduled_at'=>null,'created_by'=>get_current_user_id(),'group_ids'=>$groups
        ]);
        if(!$new) return ['success'=>false,'message'=>'ساخت کپی کمپین ناموفق بود.'];
        return ['success'=>true,'id'=>$new,'message'=>'کمپین به‌صورت پیش‌نویس کپی شد.'];
    }

    private function table_exists($table){
        global $wpdb;
        return (bool)$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$table));
    }

    private function recipient_key($recipient){
        $email=strtolower(sanitize_email($recipient['email']??''));
        $phone=EzLens_Auth_Helper::normalize_mobile($recipient['phone']??'');
        return hash('sha256',(($recipient['source']??'custom').'|'.(int)($recipient['id']??0).'|'.$email.'|'.$phone));
    }

    public function ensure_recipient_snapshot($campaign_id){
        global $wpdb;
        $campaign_id=(int)$campaign_id;
        if(!$this->table_exists($this->recipients_table)) return ['success'=>false,'message'=>'جدول گیرندگان کمپین موجود نیست.'];
        $count=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->recipients_table} WHERE campaign_id=%d",$campaign_id));
        if($count>0) return ['success'=>true,'count'=>$count,'created'=>false];
        $groups=$this->get_campaign_groups($campaign_id);
        if(!$groups) return ['success'=>false,'message'=>'هیچ گروه مخاطبی انتخاب نشده است.'];
        $recipients=[];
        foreach($groups as $gid) $recipients=array_merge($recipients,$this->get_group_recipients((int)$gid));
        $seen=[];$max=(int)EzLens_Auth_Settings::get('campaign_max_recipients');
        foreach($recipients as $r){
            $email=strtolower(sanitize_email($r['email']??'')); $phone=EzLens_Auth_Helper::normalize_mobile($r['phone']??'');
            if($email==='' && $phone==='') continue;
            $dedupe=$email.'|'.$phone; if(isset($seen[$dedupe])) continue; $seen[$dedupe]=1;
            if($max>0 && count($seen)>$max) break;
            $wpdb->insert($this->recipients_table,[
                'campaign_id'=>$campaign_id,'recipient_key'=>$this->recipient_key($r),'source'=>sanitize_key($r['source']??'custom'),
                'source_id'=>(int)($r['id']??0),'name'=>sanitize_text_field($r['name']??''),'email'=>$email,'phone'=>$phone,
                'status'=>'pending','created_at'=>current_time('mysql')
            ]);
        }
        $new=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->recipients_table} WHERE campaign_id=%d",$campaign_id));
        return $new?['success'=>true,'count'=>$new,'created'=>true]:['success'=>false,'message'=>'هیچ گیرنده‌ای برای این کمپین پیدا نشد.'];
    }

    public function get_campaign_progress($campaign_id){
        global $wpdb; $id=(int)$campaign_id;
        $zero=['total'=>0,'pending'=>0,'sending'=>0,'sent'=>0,'failed'=>0,'skipped'=>0,'processed'=>0,'percent'=>0];
        if(!$this->table_exists($this->recipients_table)) return $zero;
        $rows=$wpdb->get_results($wpdb->prepare("SELECT status,COUNT(*) c FROM {$this->recipients_table} WHERE campaign_id=%d GROUP BY status",$id));
        $out=$zero; foreach($rows as $r){$st=(string)$r->status; if(isset($out[$st])) $out[$st]=(int)$r->c; $out['total']+=(int)$r->c;}
        $out['processed']=$out['sent']+$out['failed']+$out['skipped']; $out['percent']=$out['total']?round(($out['processed']/$out['total'])*100,1):0;
        return $out;
    }

    public function get_campaign_report($campaign_id,$limit=100,$offset=0){
        global $wpdb; $id=(int)$campaign_id; $limit=max(1,min(500,(int)$limit)); $offset=max(0,(int)$offset);
        $progress=$this->get_campaign_progress($id); $track=$this->get_track_stats($id);
        $rows=[];
        if($this->table_exists($this->recipients_table)) $rows=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->recipients_table} WHERE campaign_id=%d ORDER BY id ASC LIMIT %d OFFSET %d",$id,$limit,$offset));
        return ['progress'=>$progress,'tracking'=>$track,'recipients'=>$rows];
    }

    public function pause_campaign($id){
        $c=$this->get_campaign((int)$id); if(!$c) return ['success'=>false,'message'=>'کمپین یافت نشد.'];
        if(!in_array($c->status,['sending','scheduled'],true)) return ['success'=>false,'message'=>'این کمپین قابل توقف نیست.'];
        return $this->update_campaign_status((int)$id,'paused')!==false?['success'=>true,'message'=>'کمپین متوقف شد.']:['success'=>false,'message'=>'توقف کمپین ناموفق بود.'];
    }

    public function resume_campaign($id){
        $c=$this->get_campaign((int)$id); if(!$c) return ['success'=>false,'message'=>'کمپین یافت نشد.'];
        if($c->status!=='paused') return ['success'=>false,'message'=>'این کمپین متوقف نیست.'];
        $r=$this->ensure_recipient_snapshot((int)$id); if(!$r['success']) return $r;
        if(!empty($c->scheduled_at) && strtotime($c->scheduled_at)>current_time('timestamp')){
            $this->update_campaign_status((int)$id,'scheduled');
            return ['success'=>true,'message'=>'زمان‌بندی کمپین حفظ شد و کمپین دوباره فعال شد.','scheduled_at'=>$c->scheduled_at];
        }
        $this->update_campaign_status((int)$id,'sending');
        if(class_exists('EzLens_Auth_Campaign_Queue')) EzLens_Auth_Campaign_Queue::schedule_batch((int)$id,1);
        return ['success'=>true,'message'=>'کمپین ادامه پیدا کرد.'];
    }

    public function retry_failed($id){
        global $wpdb; $id=(int)$id; $c=$this->get_campaign($id); if(!$c) return ['success'=>false,'message'=>'کمپین یافت نشد.'];
        if(!$this->table_exists($this->recipients_table)) return ['success'=>false,'message'=>'Recipient Snapshot موجود نیست.'];
        $n=$wpdb->query($wpdb->prepare("UPDATE {$this->recipients_table} SET status='pending', response='', message_id='', retry_count=retry_count+1, updated_at=%s WHERE campaign_id=%d AND status='failed'",current_time('mysql'),$id));
        if($n<1) return ['success'=>false,'message'=>'هیچ ارسال ناموفقی برای Retry وجود ندارد.'];
        $stats=$this->get_campaign_progress($id); $this->update_campaign_status($id,'sending');
        $c2=$this->get_campaign($id); $old=json_decode((string)$c2->stats,true); if(!is_array($old))$old=[];
        $old['retry_started_at']=current_time('mysql'); $old['offset']=0; $old['total']=$stats['total'];
        global $wpdb; $wpdb->update($this->campaigns_table,['stats'=>wp_json_encode($old,JSON_UNESCAPED_UNICODE)],['id'=>$id]);
        if(class_exists('EzLens_Auth_Campaign_Queue')) EzLens_Auth_Campaign_Queue::schedule_batch($id,1);
        return ['success'=>true,'message'=>'ارسال‌های ناموفق دوباره وارد صف شدند.','count'=>$n];
    }

    public function get_campaign($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->campaigns_table} WHERE id = %d", $id));
    }

    public function get_campaigns($status = null, $search = '', $limit = 20, $offset = 0) {
        global $wpdb;
        $sql = "SELECT * FROM {$this->campaigns_table} WHERE 1=1";
        $params = [];
        if ($status && $status !== 'all') {
            $sql .= " AND status = %s";
            $params[] = $status;
        }
        if (!empty($search)) {
            $sql .= " AND name LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }
        $sql .= " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;
        return $wpdb->get_results($wpdb->prepare($sql, ...$params));
    }

    public function count_campaigns($status = null, $search = '') {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$this->campaigns_table} WHERE 1=1";
        $params = [];
        if ($status && $status !== 'all') {
            $sql .= " AND status = %s";
            $params[] = $status;
        }
        if (!empty($search)) {
            $sql .= " AND name LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }
        return (int) ($params ? $wpdb->get_var($wpdb->prepare($sql, ...$params)) : $wpdb->get_var($sql));
    }

    public function update_campaign_status($id, $status) {
        global $wpdb;
        return $wpdb->update($this->campaigns_table, ['status' => $status], ['id' => $id]);
    }

    public function delete_campaign($id) {
        global $wpdb; $id=(int)$id;
        $wpdb->delete($this->campaign_groups_table, ['campaign_id'=>$id]);
        $wpdb->delete($wpdb->prefix.'ezlens_campaign_delivery', ['campaign_id'=>$id]);
        if($this->table_exists($this->recipients_table)) $wpdb->delete($this->recipients_table, ['campaign_id'=>$id]);
        $wpdb->delete($this->track_table, ['campaign_id'=>$id]);
        delete_option('ezlens_campaign_lock_'.$id);
        return $wpdb->delete($this->campaigns_table, ['id'=>$id]);
    }

    public function get_campaign_stats() {
        global $wpdb;
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->campaigns_table}");
        $sent = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->campaigns_table} WHERE status = 'sent'");
        $draft = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->campaigns_table} WHERE status = 'draft'");
        $scheduled = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->campaigns_table} WHERE status = 'scheduled'");
        return ['total' => $total, 'sent' => $sent, 'draft' => $draft, 'scheduled' => $scheduled];
    }

    public function get_track_stats($campaign_id) {
        global $wpdb;
        $total = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->track_table} WHERE campaign_id = %d", $campaign_id));
        $unique = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT recipient_email) FROM {$this->track_table} WHERE campaign_id = %d", $campaign_id));
        return ['total' => $total, 'unique' => $unique];
    }

    public function send_campaign($campaign_id, $force = false) {
        global $wpdb;
        $campaign_id=(int)$campaign_id;
        $lock_key='ezlens_campaign_lock_'.$campaign_id;
        $now=time(); $lock=(int)get_option($lock_key,0);
        if($lock && ($now-$lock)<300) return ['success'=>false,'locked'=>true,'message'=>'این کمپین در حال پردازش است.'];
        if($lock) delete_option($lock_key);
        if(!add_option($lock_key,(string)$now,'','no')) return ['success'=>false,'locked'=>true,'message'=>'این کمپین در حال پردازش است.'];
        try{
            $campaign=$this->get_campaign($campaign_id);
            if(!$campaign) return ['success'=>false,'message'=>'کمپین یافت نشد.'];
            if($campaign->status==='paused') return ['success'=>false,'message'=>'کمپین متوقف است.'];
            if(in_array($campaign->status,['sent'],true)) return ['success'=>false,'message'=>'این کمپین قبلاً تکمیل شده است.'];
            if(!$force && $campaign->scheduled_at && strtotime($campaign->scheduled_at)>current_time('timestamp')){
                $this->update_campaign_status($campaign_id,'scheduled');
                return ['success'=>true,'queued'=>true,'message'=>'کمپین برای زمان تعیین‌شده زمان‌بندی شد.','scheduled_at'=>$campaign->scheduled_at];
            }
            $snap=$this->ensure_recipient_snapshot($campaign_id);
            if(!$snap['success']) return $snap;
            $progress=$this->get_campaign_progress($campaign_id);
            if($progress['total']===0) return ['success'=>false,'message'=>'هیچ گیرنده‌ای وجود ندارد.'];
            $type=in_array($campaign->type,['email','sms'],true)?$campaign->type:'email';
            $batch=max(1,min(200,(int)EzLens_Auth_Settings::get('campaign_batch_size')?:25));
            $rows=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->recipients_table} WHERE campaign_id=%d AND status='pending' ORDER BY id ASC LIMIT %d",$campaign_id,$batch));
            if(!$rows){
                $final=$progress['failed']>0?'partial':'sent';
                $stats=$progress; $stats['provider']=$type==='email'?'wp_mail':(EzLens_Auth_Settings::get('campaign_sms_provider')?:'sms_ir');
                $wpdb->update($this->campaigns_table,['status'=>$final,'sent_at'=>current_time('mysql'),'stats'=>wp_json_encode($stats,JSON_UNESCAPED_UNICODE)],['id'=>$campaign_id]);
                return ['success'=>true]+$stats+['queued'=>false,'message'=>$final==='sent'?'کمپین با موفقیت ارسال شد.':'کمپین تکمیل شد اما بخشی از ارسال‌ها ناموفق بود.'];
            }
            $provider=$type==='email'?'wp_mail':(EzLens_Auth_Settings::get('campaign_sms_provider')?:'sms_ir');
            $compliance=EzLens_Auth_Campaign_Compliance::get_instance();
            foreach($rows as $r){
                if($type==='email' && !is_email($r->email)){
                    $this->set_recipient_result($r,'failed',['success'=>false,'message'=>'ایمیل نامعتبر.','reason_code'=>'invalid_email'],$type); continue;
                }
                if($type==='sms' && !preg_match('/^09\d{9}$/',EzLens_Auth_Helper::normalize_mobile($r->phone))){
                    $this->set_recipient_result($r,'failed',['success'=>false,'message'=>'شماره موبایل نامعتبر.','reason_code'=>'invalid_phone'],$type); continue;
                }
                if($compliance->is_unsubscribed($r->email,$r->phone)){
                    $this->set_recipient_result($r,'skipped',['success'=>false,'message'=>'گیرنده لغو عضویت کرده است.','reason_code'=>'unsubscribed'],$type); continue;
                }
                $name=sanitize_text_field($r->name); $parts=preg_split('/\s+/u',trim($name));
                $user=($r->source==='user' && $r->source_id)?get_userdata((int)$r->source_id):null;
                $order_count=0;$last_order_id='';
                if($user && function_exists('wc_get_orders')){$orders=wc_get_orders(['customer_id'=>$user->ID,'limit'=>1,'orderby'=>'date','order'=>'DESC']);$order_count=function_exists('wc_get_customer_order_count')?(int)wc_get_customer_order_count($user->ID):0;if($orders)$last_order_id=(string)$orders[0]->get_id();}
                $vars=['{name}'=>$name,'{first_name}'=>$parts[0]??'','{last_name}'=>count($parts)>1?implode(' ',array_slice($parts,1)):'','{phone}'=>$r->phone,'{email}'=>$r->email,'{site_name}'=>get_bloginfo('name'),'{date}'=>current_time('Y-m-d'),'{user_id}'=>$user?(string)$user->ID:(string)$r->source_id,'{order_count}'=>(string)$order_count,'{last_order_id}'=>$last_order_id,'{unsubscribe_url}'=>$r->email?$compliance->url($r->email):''];
                $message=strtr((string)$campaign->message,$vars); $result=[];
                if($type==='email'){
                    $full=nl2br(wp_kses_post($message));
                    if(EzLens_Auth_Settings::get('campaign_track_enabled')==='1'){$track=home_url('/?ezlens_track=1&campaign_id='.$campaign_id.'&email='.rawurlencode($r->email));$full.='<img src="'.esc_url($track).'" width="1" height="1" style="display:none" alt="">';}
                    if(EzLens_Auth_Settings::get('campaign_unsubscribe_enabled')==='1' && $r->email)$full.='<br><br><small><a href="'.esc_url($compliance->url($r->email)).'">لغو عضویت</a></small>';
                    if($campaign->file_attachment)$full.='<br><br><a href="'.esc_url($campaign->file_attachment).'">دانلود فایل ضمیمه</a>';
                    $result=EzLens_Auth_Messaging::get_instance()->send_campaign_email($r->email,strtr((string)$campaign->subject,$vars),$full,[], $campaign_id);
                }else{
                    $template=EzLens_Auth_Settings::get('campaign_sms_template');
                    $params=$template?['parameters'=>[['name'=>'NAME','value'=>$name],['name'=>'MESSAGE','value'=>$message]],'template_id'=>$template]:[];
                    $result=EzLens_Auth_Messaging::get_instance()->send_campaign_sms($r->phone,wp_strip_all_tags($message),$params);
                }
                $this->set_recipient_result($r,!empty($result['success'])?'sent':'failed',$result,$type);
                $delay=(int)EzLens_Auth_Settings::get('campaign_delay_ms'); if($delay>0)usleep(min($delay,5000)*1000);
            }
            $progress=$this->get_campaign_progress($campaign_id);
            $complete=$progress['pending']===0;
            $stats=$progress; $stats['provider']=$provider;
            if($complete){
                $final=$progress['failed']>0?'partial':'sent';
                $wpdb->update($this->campaigns_table,['status'=>$final,'sent_at'=>current_time('mysql'),'stats'=>wp_json_encode($stats,JSON_UNESCAPED_UNICODE)],['id'=>$campaign_id]);
                do_action('ezlens_campaign_sent',$campaign_id,$stats);
                return ['success'=>true]+$stats+['queued'=>false,'message'=>$final==='sent'?'کمپین با موفقیت ارسال شد.':'کمپین تکمیل شد اما بخشی از ارسال‌ها ناموفق بود.'];
            }
            $wpdb->update($this->campaigns_table,['status'=>'sending','stats'=>wp_json_encode($stats,JSON_UNESCAPED_UNICODE)],['id'=>$campaign_id]);
            if(class_exists('EzLens_Auth_Campaign_Queue')) EzLens_Auth_Campaign_Queue::schedule_batch($campaign_id,5);
            return ['success'=>true]+$stats+['queued'=>true,'message'=>'Batch ارسال شد و ادامه در صف قرار گرفت.'];
        } finally { delete_option($lock_key); }
    }

    private function set_recipient_result($row,$status,$result,$channel_type='email'){
        global $wpdb;
        $response=wp_json_encode($result,JSON_UNESCAPED_UNICODE);
        $message_id=sanitize_text_field($result['data']['messageId']??$result['data']['messageid']??$result['message_id']??'');
        $sent_at=$status==='sent'?current_time('mysql'):null;
        $wpdb->update($this->recipients_table,['status'=>$status,'response'=>$response,'message_id'=>$message_id,'sent_at'=>$sent_at,'updated_at'=>current_time('mysql')],['id'=>(int)$row->id]);
        $delivery=$wpdb->prefix.'ezlens_campaign_delivery';
        if($this->table_exists($delivery)){
            $wpdb->insert($delivery,['campaign_id'=>(int)$row->campaign_id,'recipient_email'=>$row->email,'recipient_phone'=>$row->phone,'recipient_name'=>$row->name,'channel'=>$channel_type==='email'?'email':'sms','provider'=>sanitize_text_field($result['provider']??''),'status'=>$status,'response'=>$response,'message_id'=>$message_id,'retry_count'=>(int)$row->retry_count,'created_at'=>current_time('mysql'),'sent_at'=>$sent_at]);
        }
    }

    // ============================================================
    // متدهای گروه‌ها (دسته‌ها)
    // ============================================================

    public function create_group($data) {
        global $wpdb;
        $name = sanitize_text_field($data['name'] ?? '');
        if (empty($name)) {
            return ['success' => false, 'message' => 'نام گروه را وارد کنید.'];
        }
        if (strlen($name) < 2) {
            return ['success' => false, 'message' => 'نام گروه حداقل ۲ کاراکتر باشد.'];
        }

        // بررسی تکراری نبودن نام
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->groups_table} WHERE name = %s AND created_by = %d",
            $name,
            get_current_user_id()
        ));
        if ($exists > 0) {
            return ['success' => false, 'message' => 'گروهی با این نام قبلاً ایجاد شده است.'];
        }

        // دریافت contact_ids
        $contact_ids = isset($data['contact_ids']) ? $data['contact_ids'] : '[]';
        if (is_string($contact_ids)) {
            $contact_ids = json_decode($contact_ids, true);
        }
        if (!is_array($contact_ids)) {
            $contact_ids = [];
        }
        $contact_ids = array_filter(array_map('intval', $contact_ids));

        $insert_data = [
            'name' => $name,
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'type' => sanitize_text_field($data['type'] ?? 'custom'),
            'user_filters' => $data['user_filters'] ?? '[]',
            'contact_ids' => wp_json_encode($contact_ids),
            'created_by' => get_current_user_id(),
        ];

        $result = $wpdb->insert($this->groups_table, $insert_data);
        if ($result) {
            return ['success' => true, 'id' => $wpdb->insert_id, 'message' => 'گروه با موفقیت ایجاد شد.'];
        } else {
            return ['success' => false, 'message' => 'خطا در ذخیره گروه: ' . $wpdb->last_error];
        }
    }

    public function get_groups($type = null, $search = '') {
        global $wpdb;
        $sql = "SELECT * FROM {$this->groups_table} WHERE 1=1";
        $params = [];
        if ($type) {
            $sql .= " AND type = %s";
            $params[] = $type;
        }
        if (!empty($search)) {
            $sql .= " AND name LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }
        $sql .= " ORDER BY created_at DESC";
        return $params ? $wpdb->get_results($wpdb->prepare($sql, ...$params)) : $wpdb->get_results($sql);
    }

    public function get_group($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->groups_table} WHERE id = %d", $id));
    }

    public function update_group($id, $data) {
        global $wpdb;
        $update_data = [];
        if (isset($data['name'])) {
            $name = sanitize_text_field($data['name']);
            if (empty($name)) return ['success' => false, 'message' => 'نام گروه نمی‌تواند خالی باشد.'];
            $update_data['name'] = $name;
        }
        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
        }
        if (isset($data['contact_ids'])) {
            $contact_ids = $data['contact_ids'];
            if (is_string($contact_ids)) {
                $contact_ids = json_decode($contact_ids, true);
            }
            if (!is_array($contact_ids)) $contact_ids = [];
            $contact_ids = array_filter(array_map('intval', $contact_ids));
            $update_data['contact_ids'] = wp_json_encode($contact_ids);
        }
        if (empty($update_data)) {
            return ['success' => false, 'message' => 'هیچ داده‌ای برای به‌روزرسانی وجود ندارد.'];
        }
        $result = $wpdb->update($this->groups_table, $update_data, ['id' => $id]);
        if ($result !== false) {
            return ['success' => true, 'message' => 'گروه به‌روز شد.'];
        } else {
            return ['success' => false, 'message' => 'خطا در به‌روزرسانی گروه.'];
        }
    }

    public function delete_group($id) {
        global $wpdb;
        $wpdb->delete($this->campaign_groups_table, ['group_id' => $id]);
        return $wpdb->delete($this->groups_table, ['id' => $id]);
    }

    public function get_campaign_groups($campaign_id) {
        global $wpdb;
        return $wpdb->get_col($wpdb->prepare(
            "SELECT group_id FROM {$this->campaign_groups_table} WHERE campaign_id = %d",
            $campaign_id
        ));
    }

    /**
     * دریافت مخاطبان یک گروه (با پشتیبانی از گروه‌های ترکیبی و سفارشی)
     */
    public function get_group_recipients($group_id) {
        $group = $this->get_group($group_id);
        if (!$group) return [];

        $recipients = [];

        // گروه از نوع کاربران وردپرس
        if ($group->type === 'wordpress') {
            $filters = json_decode($group->user_filters, true);
            $args = ['number' => -1, 'fields' => ['ID', 'user_login', 'user_email', 'display_name']];
            if (!empty($filters['role'])) $args['role'] = $filters['role'];
            if (!empty($filters['registered_after'])) {
                $args['date_query'] = [['after' => $filters['registered_after'], 'inclusive' => true]];
            }
            $users = get_users($args);
            if (!empty($users)) {
                update_meta_cache('user', wp_list_pluck($users, 'ID'));
            }
            if (!empty($filters['min_spent']) && function_exists('wc_get_customer_total_spent')) {
                $min_spent=(float)$filters['min_spent'];
                $users=array_values(array_filter($users,function($u) use ($min_spent){return (float)wc_get_customer_total_spent($u->ID)>=$min_spent;}));
            }
            foreach ($users as $user) {
                $recipients[] = [
                    'name' => $user->display_name ?: $user->user_login,
                    'email' => $user->user_email,
                    'phone' => get_user_meta($user->ID, 'billing_phone', true) ?: get_user_meta($user->ID, 'user_phone', true),
                    'source' => 'user',
                    'id' => $user->ID,
                ];
            }
            return $recipients;
        }

        // گروه از نوع مخاطبان سفارشی یا ترکیبی
        if ($group->type === 'custom' || $group->type === 'mixed') {
            $contact_ids = json_decode($group->contact_ids, true);
            if (!empty($contact_ids) && is_array($contact_ids)) {
                global $wpdb;
                $placeholders = implode(',', array_fill(0, count($contact_ids), '%d'));
                $contacts = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$this->contacts_table} WHERE id IN ($placeholders)",
                    ...$contact_ids
                ));
                foreach ($contacts as $contact) {
                    $recipients[] = [
                        'name' => $contact->name,
                        'email' => $contact->email,
                        'phone' => $contact->phone,
                        'source' => 'custom',
                        'id' => $contact->id,
                    ];
                }
            }
        }

        return $recipients;
    }

    // ============================================================
    // متدهای مخاطبان دستی با دسته‌بندی
    // ============================================================

    public function add_contact($data) {
        global $wpdb;
        $email = sanitize_email($data['email'] ?? '');
        if (empty($email) || !is_email($email)) {
            return ['success' => false, 'message' => 'ایمیل معتبر وارد کنید.'];
        }

        // بررسی تکراری نبودن ایمیل
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->contacts_table} WHERE email = %s",
            $email
        ));
        if ($exists > 0) {
            return ['success' => false, 'message' => 'این ایمیل قبلاً در مخاطبان دستی وجود دارد.'];
        }

        $category = sanitize_text_field($data['category'] ?? 'عمومی');
        $phone = EzLens_Auth_Helper::normalize_mobile($data['phone'] ?? '');
        $result = $wpdb->insert($this->contacts_table, [
            'name' => sanitize_text_field($data['name'] ?? ''),
            'email' => $email,
            'phone' => $phone,
            'category' => $category,
        ]);

        if ($result) {
            return ['success' => true, 'id' => $wpdb->insert_id, 'message' => 'مخاطب با موفقیت اضافه شد.'];
        } else {
            return ['success' => false, 'message' => 'خطا در ذخیره مخاطب: ' . $wpdb->last_error];
        }
    }

    public function get_contacts($limit = 20, $offset = 0, $search = '', $category = '') {
        global $wpdb;
        $sql = "SELECT id,name,email,phone,category,extra_fields,created_at FROM {$this->contacts_table} WHERE 1=1";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (name LIKE %s OR email LIKE %s)";
            $s = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $s;
            $params[] = $s;
        }
        if (!empty($category) && $category !== 'all') {
            $sql .= " AND category = %s";
            $params[] = $category;
        }
        $sql .= " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;
        return $wpdb->get_results($wpdb->prepare($sql, ...$params));
    }

    public function count_contacts($search = '', $category = '') {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$this->contacts_table} WHERE 1=1";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (name LIKE %s OR email LIKE %s)";
            $s = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $s;
            $params[] = $s;
        }
        if (!empty($category) && $category !== 'all') {
            $sql .= " AND category = %s";
            $params[] = $category;
        }
        return (int) ($params ? $wpdb->get_var($wpdb->prepare($sql, ...$params)) : $wpdb->get_var($sql));
    }

    public function delete_contact($id) {
        global $wpdb;
        return $wpdb->delete($this->contacts_table, ['id' => $id]);
    }

    public function get_contact_categories() {
        global $wpdb;
        return $wpdb->get_col("SELECT DISTINCT category FROM {$this->contacts_table} ORDER BY category ASC");
    }
}