<?php
class EzLens_Custom_HTTP_Provider implements EzLens_Message_Provider_Interface {
    private $s;
    public function __construct($settings){$this->s=$settings;}
    private function send($mobile,$message,$context=[]){
        $url=trim((string)($this->s['endpoint']??''));if(!$url)return ['success'=>false,'message'=>'Endpoint سرویس سفارشی تنظیم نشده است.','reason_code'=>'missing_endpoint'];
        $payload=['mobile'=>EzLens_Auth_Helper::normalize_mobile($mobile),'message'=>$message,'code'=>$context['code']??'','template_id'=>$context['template_id']??''];$headers=['Content-Type'=>'application/json','Accept'=>'application/json'];$token=trim((string)($this->s['token']??''));if($token)$headers['Authorization']='Bearer '.$token;
        $start=microtime(true);$r=wp_safe_remote_post($url,['headers'=>$headers,'body'=>wp_json_encode($payload),'timeout'=>15]);$latency=round((microtime(true)-$start)*1000,2);
        if(is_wp_error($r))return ['success'=>false,'message'=>'ارتباط با سرویس سفارشی برقرار نشد: '.$r->get_error_message(),'reason_code'=>'http_request_failed','wp_error_code'=>$r->get_error_code(),'latency_ms'=>$latency];
        $code=wp_remote_retrieve_response_code($r);$raw=wp_remote_retrieve_body($r);$data=json_decode($raw,true);$base=['http_code'=>$code,'latency_ms'=>$latency,'raw'=>$data?:$raw];
        if($code>=200&&$code<300)return array_merge($base,['success'=>true]);
        return array_merge($base,['success'=>false,'reason_code'=>'provider_error','message'=>'سرویس سفارشی HTTP '.$code]);
    }
    public function send_sms($mobile,$message,$context=[]){return $this->send($mobile,$message,$context);}
    public function send_otp($mobile,$code,$context=[]){return $this->send($mobile,(string)$code,['code'=>$code,'template_id'=>$context['template_id']??'']);}
    public function test($mobile,$context=[]){return $this->send_otp($mobile,'123456',$context);}
    public function get_status(){return ['configured'=>(bool)trim((string)($this->s['endpoint']??'')),'provider'=>'custom'];}
}
