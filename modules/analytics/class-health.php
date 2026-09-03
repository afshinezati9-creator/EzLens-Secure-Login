<?php
if (!defined('ABSPATH')) exit;
/** Lightweight diagnostics; no external requests are made by default. */
class EzLens_Auth_Health {
    public static function check(){ global $wpdb; $db=$wpdb->get_var('SELECT 1')==='1'; $cron=wp_next_scheduled('ezlens_campaign_queue_tick'); $otp=EzLens_Auth_Settings::get('otp_sms_enabled')==='1'; $campaign=(EzLens_Auth_Settings::get('campaign_sms_enabled')==='1'||EzLens_Auth_Settings::get('campaign_email_enabled')==='1'); $api=EzLens_Auth_Settings::get('enable_api')==='1'; return ['status'=>($db?'ok':'critical'),'php'=>PHP_VERSION,'wordpress'=>get_bloginfo('version'),'db'=>$db,'cron'=>['scheduled'=>(bool)$cron,'next'=>$cron?wp_date('Y-m-d H:i:s',$cron):null],'otp_sms'=>['enabled'=>$otp,'provider'=>EzLens_Auth_Settings::get('otp_sms_provider')],'campaign'=>['enabled'=>$campaign,'sms_provider'=>EzLens_Auth_Settings::get('campaign_sms_provider')],'api'=>['enabled'=>$api,'namespace'=>'ezlens-app/v1'],'ssl'=>is_ssl(),'memory_limit'=>ini_get('memory_limit'),'timestamp'=>current_time('mysql')];}
}
