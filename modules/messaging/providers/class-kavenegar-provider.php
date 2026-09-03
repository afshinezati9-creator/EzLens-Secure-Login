<?php
class EzLens_Kavenegar_Provider implements EzLens_Message_Provider_Interface {
    private $api_key,$template;
    public function __construct($settings){$this->api_key=trim((string)($settings['api_key']??''));$this->template=trim((string)($settings['template']??''));}
    private function request($method,$args){
        if(!$this->api_key)return ['success'=>false,'message'=>'API Key کاوه نگار تنظیم نشده است.','reason_code'=>'missing_api_key'];
        $url='https://api.kavenegar.com/v1/'.rawurlencode($this->api_key).'/'.$method.'.json';$start=microtime(true);
        $r=wp_safe_remote_post($url,['body'=>$args,'timeout'=>15]);$latency=round((microtime(true)-$start)*1000,2);
        if(is_wp_error($r))return ['success'=>false,'message'=>'ارتباط با کاوه نگار برقرار نشد: '.$r->get_error_message(),'reason_code'=>'http_request_failed','wp_error_code'=>$r->get_error_code(),'latency_ms'=>$latency];
        $code=wp_remote_retrieve_response_code($r);$data=json_decode(wp_remote_retrieve_body($r),true);$ok=($code>=200&&$code<300&&isset($data['return']['status'])&&(int)$data['return']['status']===200);
        $base=['http_code'=>$code,'latency_ms'=>$latency,'raw'=>$data];
        if($ok)return array_merge($base,['success'=>true,'data'=>$data['entries']??null]);
        return array_merge($base,['success'=>false,'reason_code'=>'provider_error','message'=>$data['return']['message']??('خطای کاوه نگار (HTTP '.$code.')')]);
    }
    public function send_otp($mobile,$code,$context=[]){$template=trim((string)($context['template']??$this->template));if(!$template)return ['success'=>false,'message'=>'نام قالب OTP کاوه نگار تنظیم نشده است.'];return $this->request('verify/lookup',['receptor'=>EzLens_Auth_Helper::normalize_mobile($mobile),'token'=>(string)$code,'template'=>$template]);}
    public function send_sms($mobile,$message,$context=[]){return $this->request('sms/send',['receptor'=>EzLens_Auth_Helper::normalize_mobile($mobile),'sender'=>$context['sender']??'','message'=>$message]);}
    public function test($mobile,$context=[]){return $this->send_otp($mobile,'123456',$context);}
    public function get_status(){return ['configured'=>(bool)$this->api_key,'provider'=>'kavenegar','template_configured'=>(bool)$this->template];}
}
