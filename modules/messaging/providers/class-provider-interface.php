<?php
interface EzLens_Message_Provider_Interface {
    public function send_sms($mobile,$message,$context=[]);
    public function send_otp($mobile,$code,$context=[]);
    public function test($mobile,$context=[]);
    public function get_status();
}
