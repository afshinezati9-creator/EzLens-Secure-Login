/**
 * اسکریپت‌های ویجت پشتیبانی (فاز ۹)
 * برای بارگذاری در صفحات /my-account
 */
jQuery(document).ready(function($) {
    // اگر ویجت در صفحه وجود دارد، آن را فعال کنید
    if ($('#ezlensChatWidget').length) {
        console.log('✅ ویجت چت پشتیبانی فعال شد.');
    }
});