<?php
if (!defined('ABSPATH')) exit;
/** Scheduled and batched campaign runner. */
class EzLens_Auth_Campaign_Queue {
    private static $instance=null;
    private $hook='ezlens_campaign_queue_tick';
    private $batch_hook='ezlens_campaign_process_batch';
    public static function get_instance(){return self::$instance?:self::$instance=new self();}
    private function __construct(){add_filter('cron_schedules',[$this,'schedules']);add_action($this->hook,[$this,'run']);add_action($this->batch_hook,[$this,'run_batch']);add_action('wp_loaded',[$this,'schedule']);}
    public function schedules($s){$s['ezlens_1min']=['interval'=>60,'display'=>'EzLens every minute'];return $s;}
    public function schedule(){if(!wp_next_scheduled($this->hook))wp_schedule_event(time()+60,'ezlens_1min',$this->hook);}
    public function unschedule(){wp_clear_scheduled_hook($this->hook);wp_clear_scheduled_hook($this->batch_hook);}
    public static function schedule_batch($campaign_id,$delay=5){$hook='ezlens_campaign_process_batch';$args=[(int)$campaign_id];if(!wp_next_scheduled($hook,$args)) wp_schedule_single_event(time()+max(1,(int)$delay),$hook,$args);}
    public function run(){
        global $wpdb;$table=$wpdb->prefix.'ezlens_campaigns';
        $ids=$wpdb->get_col($wpdb->prepare("SELECT id FROM {$table} WHERE status='scheduled' AND scheduled_at IS NOT NULL AND scheduled_at<=%s ORDER BY scheduled_at ASC LIMIT 3",current_time('mysql')));
        foreach($ids as $id){$result=EzLens_Auth_Campaign::get_instance()->send_campaign((int)$id,true);EzLens_Auth_Audit::get_instance()->log('campaign.scheduled_run',['result'=>$result],'campaign',$id);}
        $sending=$wpdb->get_col("SELECT id FROM {$table} WHERE status='sending' ORDER BY created_at ASC LIMIT 3");
        foreach($sending as $id) self::schedule_batch((int)$id,5);
    }
    public function run_batch($campaign_id){$result=EzLens_Auth_Campaign::get_instance()->send_campaign((int)$campaign_id,true);EzLens_Auth_Audit::get_instance()->log('campaign.batch_run',['result'=>$result],'campaign',$campaign_id);}
}
