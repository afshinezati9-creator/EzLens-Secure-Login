<?php
if (!defined('ABSPATH')) exit;
/** Optional outgoing webhooks for future EzLens Manager/mobile integrations. */
class EzLens_Auth_Webhooks {
    private static $instance=null;
    public static function get_instance(){return self::$instance?:self::$instance=new self();}
    private function __construct(){add_action('ezlens_campaign_sent',[$this,'campaign_sent'],10,2);add_action('ezlens_support_ticket_created',[$this,'support_created'],10,1);}
    public function campaign_sent($campaign_id,$stats=[]){$this->dispatch('campaign.sent',['campaign_id'=>(int)$campaign_id,'stats'=>$stats]);}
    public function support_created($ticket_id){$this->dispatch('support.ticket_created',['ticket_id'=>(int)$ticket_id]);}
    public function dispatch($event,$payload){if(EzLens_Auth_Settings::get('webhook_enabled')!=='1')return false;$url=trim((string)EzLens_Auth_Settings::get('webhook_url'));if(!$url||!wp_http_validate_url($url))return false;$body=['event'=>sanitize_key(str_replace('.','_',$event)),'timestamp'=>time(),'site'=>home_url('/'),'data'=>$payload];$secret=(string)EzLens_Auth_Settings::get('webhook_secret');$json=wp_json_encode($body);$headers=['Content-Type'=>'application/json','X-EzLens-Event'=>$event];if($secret)$headers['X-EzLens-Signature']='sha256='.hash_hmac('sha256',$json,$secret);$r=wp_remote_post($url,['timeout'=>5,'blocking'=>false,'headers'=>$headers,'body'=>$json]);return !is_wp_error($r);}
}
