<?php
/**
 * Legacy-compatible OTP SMS facade.
 * Campaign SMS must use EzLens_Auth_Messaging directly and never this class.
 */
class EzLens_Auth_SMS {
    public function __construct() {}
    public function send_verify($mobile,$code){
        // Backward compatibility: old settings are migrated into OTP settings on first use.
        if (!EzLens_Auth_Settings::get('otp_sms_api_key') && EzLens_Auth_Settings::get('sms_api_key')) EzLens_Auth_Settings::set('otp_sms_api_key',EzLens_Auth_Settings::get('sms_api_key'));
        if (!EzLens_Auth_Settings::get('otp_sms_line') && EzLens_Auth_Settings::get('sms_line_number')) EzLens_Auth_Settings::set('otp_sms_line',EzLens_Auth_Settings::get('sms_line_number'));
        if (!EzLens_Auth_Settings::get('otp_sms_template') && EzLens_Auth_Settings::get('sms_template_id')) EzLens_Auth_Settings::set('otp_sms_template',EzLens_Auth_Settings::get('sms_template_id'));
        if (EzLens_Auth_Settings::get('otp_sms_enabled') !== '1' && EzLens_Auth_Settings::get('sms_enabled') === '1') EzLens_Auth_Settings::set('otp_sms_enabled','1');
        return EzLens_Auth_Messaging::get_instance()->send_otp($mobile,$code);
    }
    public function get_credit(){return ['success'=>false,'message'=>'اعتبار سرویس‌دهنده انتخابی از طریق API اختصاصی همان سرویس مدیریت می‌شود.'];}
    public static function is_enabled(){return EzLens_Auth_Settings::get('otp_sms_enabled')==='1' || EzLens_Auth_Settings::get('sms_enabled')==='1';}
}
