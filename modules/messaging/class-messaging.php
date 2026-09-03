<?php
if (!defined('ABSPATH')) exit;
/**
 * Central messaging facade. OTP and Campaign channels are intentionally isolated.
 * @version 5.3.0
 */
class EzLens_Auth_Messaging {
    private static $instance;
    public static function get_instance(){return self::$instance?:self::$instance=new self();}
    private function __construct(){}

    private function provider($channel='otp'){
        $s=EzLens_Auth_Settings::get_all();
        $prefix=$channel==='campaign'?'campaign_sms_':'otp_sms_';
        $type=sanitize_key($s[$prefix.'provider']??'sms_ir');
        $cfg=['api_key'=>$s[$prefix.'api_key']??'','line'=>$s[$prefix.'line']??'','template'=>$s[$prefix.'template']??'','endpoint'=>$s[$prefix.'endpoint']??'','token'=>$s[$prefix.'token']??''];
        switch($type){
            case 'kavenegar': return new EzLens_Kavenegar_Provider($cfg);
            case 'custom': return new EzLens_Custom_HTTP_Provider($cfg);
            default: return new EzLens_SMS_IR_Provider($cfg);
        }
    }

    public function send_otp($mobile,$code){
        if(EzLens_Auth_Settings::get('otp_sms_enabled')!=='1') return ['success'=>false,'message'=>'ارسال پیامک OTP غیرفعال است.'];
        $start=microtime(true);
        $result=$this->provider('otp')->send_otp($mobile,$code);
        $result['latency_ms']=round((microtime(true)-$start)*1000,2);
        $result['channel']='otp';
        $result['provider']=EzLens_Auth_Settings::get('otp_sms_provider')?:'sms_ir';
        if(EzLens_Auth_Settings::get('enable_logging')==='1') error_log('EzLens OTP: provider='.$result['provider'].' latency_ms='.$result['latency_ms'].' success='.(empty($result['success'])?'0':'1'));
        return $result;
    }

    public function send_campaign_sms($mobile,$message,$context=[]){
        if(EzLens_Auth_Settings::get('campaign_sms_enabled')!=='1') return ['success'=>false,'message'=>'ارسال پیامک Campaign غیرفعال است.'];
        if(!preg_match('/^09\d{9}$/',EzLens_Auth_Helper::normalize_mobile($mobile))) return ['success'=>false,'message'=>'شماره موبایل گیرنده معتبر نیست.'];
        $start=microtime(true);
        $result=$this->provider('campaign')->send_sms($mobile,$message,$context);
        $result['latency_ms']=round((microtime(true)-$start)*1000,2);
        $result['channel']='campaign_sms';
        $result['provider']=EzLens_Auth_Settings::get('campaign_sms_provider')?:'sms_ir';
        return $result;
    }

    public function send_campaign_email($to,$subject,$message,$headers=[],$campaign_id=0){
        return $this->send_email_internal($to,$subject,$message,$headers,$campaign_id,true);
    }

    /**
     * Dedicated email test. It deliberately bypasses the Campaign enabled flag
     * so the administrator can diagnose SMTP/wp_mail even before campaigns are enabled.
     */
    public function test_email($to,$message=''){
        $subject=EzLens_Auth_Settings::get('email_test_subject')?:'تست ایمیل EzLens';
        $body=$message!==''?$message:'این یک ایمیل تست از EzLens Secure Login است. اگر این پیام را دریافت کرده‌اید، مسیر ارسال ایمیل سایت پاسخ داده است.';
        return $this->send_email_internal($to,$subject,$body,[],0,false);
    }

    private function send_email_internal($to,$subject,$message,$headers=[],$campaign_id=0,$require_campaign_enabled=true){
        if($require_campaign_enabled && EzLens_Auth_Settings::get('campaign_email_enabled')!=='1') return ['success'=>false,'message'=>'ارسال ایمیل Campaign غیرفعال است.','reason_code'=>'campaign_email_disabled','channel'=>'campaign_email'];
        $to=sanitize_email($to);
        if(!is_email($to)) return ['success'=>false,'message'=>'آدرس ایمیل گیرنده معتبر نیست.','reason_code'=>'invalid_email','channel'=>'campaign_email'];

        $from=sanitize_email(EzLens_Auth_Settings::get('campaign_email_from')) ?: sanitize_email(EzLens_Auth_Settings::get('email_from'));
        $from_name=sanitize_text_field(EzLens_Auth_Settings::get('campaign_email_from_name')) ?: sanitize_text_field(EzLens_Auth_Settings::get('email_from_name'));
        $base=['Content-Type: text/html; charset=UTF-8'];
        if($from) $base[]='From: '.$from_name.' <'.$from.'>';
        $headers=array_merge($base,$headers);

        $mail_error=null;
        $mail_failed=function($error) use (&$mail_error){$mail_error=$error;};
        $smtp_configured=(EzLens_Auth_Settings::get('smtp_enabled')==='1');

        // Configure SMTP only for this request; never remove another plugin's hooks.
        $smtp_hook=null;
        if($smtp_configured){
            $smtp_hook=function($phpmailer){
                $phpmailer->isSMTP();
                $phpmailer->Host=EzLens_Auth_Settings::get('smtp_host');
                $phpmailer->Port=(int)EzLens_Auth_Settings::get('smtp_port');
                $phpmailer->SMTPSecure=EzLens_Auth_Settings::get('smtp_encryption');
                $phpmailer->SMTPAuth=(bool)EzLens_Auth_Settings::get('smtp_auth');
                $phpmailer->Username=EzLens_Auth_Settings::get('smtp_username');
                $phpmailer->Password=EzLens_Auth_Settings::get('smtp_password');
                $from=EzLens_Auth_Settings::get('campaign_email_from') ?: EzLens_Auth_Settings::get('email_from');
                $name=EzLens_Auth_Settings::get('campaign_email_from_name') ?: EzLens_Auth_Settings::get('email_from_name');
                if($from) $phpmailer->setFrom($from,$name,false);
            };
            add_action('phpmailer_init',$smtp_hook,999);
        }

        add_action('wp_mail_failed',$mail_failed,999,1);
        $start=microtime(true);
        $sent=wp_mail($to,$subject,$message,$headers);
        $latency=round((microtime(true)-$start)*1000,2);
        remove_action('wp_mail_failed',$mail_failed,999);
        if($smtp_hook) remove_action('phpmailer_init',$smtp_hook,999);

        $result=[
            'success'=>(bool)$sent,
            'message'=>$sent?'درخواست ارسال توسط WordPress/PHPMailer پذیرفته شد.': 'ارسال ایمیل ناموفق بود.',
            'latency_ms'=>$latency,
            'channel'=>'campaign_email',
            'mail_accepted'=>(bool)$sent,
            'smtp_enabled'=>$smtp_configured,
            'mailer'=>$smtp_configured?'SMTP / PHPMailer':'WordPress default mailer',
        ];

        if($mail_error instanceof WP_Error){
            $result['success']=false;
            $result['message']=$mail_error->get_error_message() ?: $result['message'];
            $result['reason_code']='wp_mail_failed';
            $result['error_code']=$mail_error->get_error_code();
            $data=$mail_error->get_error_data();
            if(is_array($data)){
                $safe=[];
                foreach(['to','subject','headers','error'] as $k){ if(isset($data[$k]) && $k!=='headers') $safe[$k]=$data[$k]; }
                if(isset($data['phpmailer_exception_code'])) $safe['phpmailer_exception_code']=$data['phpmailer_exception_code'];
                if($safe) $result['error_data']=$safe;
            }
        } elseif(!$sent){
            $result['reason_code']='wp_mail_failed';
        }

        if(EzLens_Auth_Settings::get('email_logging')==='1') $this->log_email($to,$subject,$result,$campaign_id);
        return $result;
    }

    private function log_email($to,$subject,$result,$campaign_id=0){
        global $wpdb;
        $table=$wpdb->prefix.'ezlens_email_log';
        if(!$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$table))) return;
        $wpdb->insert($table,[
            'user_id'=>get_current_user_id(), 'campaign_id'=>(int)$campaign_id,
            'to_email'=>$to,'subject'=>sanitize_text_field($subject),
            'status'=>!empty($result['success'])?'sent':'failed',
            'error_message'=>sanitize_textarea_field($result['message']??''),
            'latency_ms'=>(float)($result['latency_ms']??0),'created_at'=>current_time('mysql')
        ]);
    }

    public function test($channel,$mobileOrEmail,$message=''){
        if($channel==='email') {
            $subject=EzLens_Auth_Settings::get('email_test_subject')?:'تست ایمیل Campaign EzLens';
            $body=$message!==''?$message:'این پیام برای تست اتصال ایمیل Campaign EzLens ارسال شده است.';
            return $this->test_email($mobileOrEmail,$body);
        }
        if($channel==='campaign') {
            $message=$message!==''?$message:'این پیام برای تست SMS Campaign ایزی‌لنز است.';
            $template=(int)EzLens_Auth_Settings::get('campaign_sms_template');
            $context=['template_id'=>$template,'parameters'=>[['name'=>'NAME','value'=>'تست'],['name'=>'MESSAGE','value'=>$message]]];
            return $this->send_campaign_sms($mobileOrEmail,$message,$context);
        }
        return $this->provider('otp')->test($mobileOrEmail);
    }
    public function status($channel='otp'){return $this->provider($channel)->get_status();}
    public function get_campaign_settings(){return ['sms_enabled'=>EzLens_Auth_Settings::get('campaign_sms_enabled'),'sms_provider'=>EzLens_Auth_Settings::get('campaign_sms_provider'),'email_enabled'=>EzLens_Auth_Settings::get('campaign_email_enabled')];}
}
EzLens_Auth_Messaging::get_instance();
