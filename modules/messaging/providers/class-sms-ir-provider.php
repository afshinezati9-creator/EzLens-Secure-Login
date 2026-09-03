<?php
class EzLens_SMS_IR_Provider implements EzLens_Message_Provider_Interface {
    private $api_key,$line,$template;
    public function __construct($settings){$this->api_key=trim((string)($settings['api_key']??''));$this->line=trim((string)($settings['line']??''));$this->template=(int)($settings['template']??0);}
    private function request($body,$path){
        if(!$this->api_key)return ['success'=>false,'message'=>'کلید API تنظیم نشده است.','reason_code'=>'missing_api_key'];
        $start=microtime(true);
        $r=wp_safe_remote_post('https://api.sms.ir/v1/'.$path,['headers'=>['Content-Type'=>'application/json','Accept'=>'application/json','X-API-KEY'=>$this->api_key],'body'=>wp_json_encode($body),'timeout'=>15]);
        $latency=round((microtime(true)-$start)*1000,2);
        if(is_wp_error($r))return ['success'=>false,'message'=>'ارتباط با SMS.ir برقرار نشد: '.$r->get_error_message(),'reason_code'=>'http_request_failed','wp_error_code'=>$r->get_error_code(),'latency_ms'=>$latency];
        $code=wp_remote_retrieve_response_code($r);
        $raw=wp_remote_retrieve_body($r);
        $data=json_decode($raw,true);
        $message=is_array($data)?($data['message']??''):'';
        $result=['http_code'=>$code,'latency_ms'=>$latency,'raw'=>$data?:$raw];
        if($code>=200&&$code<300&&isset($data['status'])&&((int)$data['status']===1)){
            $result['success']=true;$result['data']=$data['data']??null;return $result;
        }
        $result['success']=false;$result['reason_code']='provider_error';$result['message']=$message?:('خطای SMS.ir (HTTP '.$code.')');return $result;
    }
    public function send_otp($mobile,$code,$context=[]){if(!$this->template)return ['success'=>false,'message'=>'شناسه قالب OTP تنظیم نشده است.'];$body=['mobile'=>EzLens_Auth_Helper::normalize_mobile($mobile),'templateId'=>$this->template,'parameters'=>[['name'=>'CODE','value'=>(string)$code]]];if($this->line)$body['lineNumber']=$this->line;return $this->request($body,'send/verify');}
    public function send_sms($mobile,$message,$context=[]){$template=(int)($context['template_id']??0);if(!$template)return ['success'=>false,'message'=>'برای ارسال Campaign در SMS.ir باید Template ID کمپین را تعیین کنید.'];$body=['mobile'=>EzLens_Auth_Helper::normalize_mobile($mobile),'templateId'=>$template,'parameters'=>$context['parameters']??[]];if($this->line)$body['lineNumber']=$this->line;return $this->request($body,'send/verify');}
    public function test($mobile,$context=[]){$code='123456';return $this->send_otp($mobile,$code,$context);}
    public function get_status(){return ['configured'=>(bool)$this->api_key,'provider'=>'sms.ir','template_configured'=>(bool)$this->template];}
}
