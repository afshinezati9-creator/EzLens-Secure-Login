<?php
/**
 * درخواست‌های AJAX احراز هویت (ورود، ثبت‌نام، OTP، فراموشی)
 * این کلاس فقط اکشن‌های NOPRIV را ثبت می‌کند
 */
class EzLens_Auth_Ajax_Auth {

    public static function init() {
        // OTP
        add_action('wp_ajax_nopriv_ezlens_otp_send', [EzLens_Auth_Shortcodes_Auth::class, 'ajax_otp_send']);
        add_action('wp_ajax_nopriv_ezlens_otp_verify', [EzLens_Auth_Shortcodes_Auth::class, 'ajax_otp_verify']);

        // ورود و ثبت‌نام
        add_action('wp_ajax_nopriv_min_auth_login', [EzLens_Auth_Shortcodes_Auth::class, 'ajax_login']);
        add_action('wp_ajax_nopriv_min_auth_register', [EzLens_Auth_Shortcodes_Auth::class, 'ajax_register']);
        add_action('wp_ajax_nopriv_ezlens_verify_register', [EzLens_Auth_Shortcodes_Auth::class, 'ajax_verify_register']);

        // فراموشی رمز مشتری
        add_action('wp_ajax_nopriv_ezlens_forgot_send_sms', [EzLens_Auth_Shortcodes_Forgot::class, 'ajax_forgot_send_sms']);
        add_action('wp_ajax_nopriv_ezlens_forgot_verify_sms', [EzLens_Auth_Shortcodes_Forgot::class, 'ajax_forgot_verify_sms']);
        add_action('wp_ajax_nopriv_ezlens_forgot_send_email', [EzLens_Auth_Shortcodes_Forgot::class, 'ajax_forgot_send_email']);
        add_action('wp_ajax_nopriv_ezlens_forgot_verify_email', [EzLens_Auth_Shortcodes_Forgot::class, 'ajax_forgot_verify_email']);

        // فراموشی رمز مدیر
        add_action('wp_ajax_nopriv_ezlens_admin_forgot_send', [EzLens_Auth_Shortcodes_Forgot::class, 'ajax_admin_forgot_send']);
        add_action('wp_ajax_nopriv_ezlens_admin_forgot_verify', [EzLens_Auth_Shortcodes_Forgot::class, 'ajax_admin_forgot_verify']);
    }
}

EzLens_Auth_Ajax_Auth::init();