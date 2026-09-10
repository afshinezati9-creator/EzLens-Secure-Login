مستندات مرجع فنی و توسعه پلاگین EzLens Secure Login

نسخه مرجع: 5.4.0
نویسنده: افشین عزتی
وب‌سایت: https://ezlens.ir
تاریخ مستند: ۱۴۰۴/۰۶/۱۴

فهرست مطالب

معرفی پلاگین

معماری و ساختار کلیFolder PATH listing
Volume serial number is 5667-D242
C:.
│   .gitignore
│   documentation.txt
│   ezlens-secure-login.php
│   README.md
│
├───admin
│   ├───assets
│   │   ├───css
│   │   │       admin-campaign.css
│   │   │       admin.css
│   │   │
│   │   └───js
│   │           admin-campaign.js
│   │           admin.js
│   │
│   └───pages
│           backup.php
│           campaign.php
│           dashboard.php
│           editor.php
│           logs.php
│           settings.php
│           support.php
│
├───api
│       class-api-v1.php
│       class-api-v2.php
│       class-webhooks.php
│
├───assets
│   ├───css
│   ├───fonts
│   │   └───vazirmatn
│   │           Vazirmatn-Bold.woff2
│   │           Vazirmatn-Medium.woff2
│   │           Vazirmatn-Regular.woff2
│   │           Vazirmatn.css
│   │
│   ├───icons
│   │   │   activity.svg
│   │   │   alert-circle.svg
│   │   │   alert-triangle.svg
│   │   │   align-center.svg
│   │   │   align-justify.svg
│   │   │   align-left.svg
│   │   │   align-right.svg
│   │   │   archive.svg
│   │   │   arrow-down.svg
│   │   │   arrow-left.svg
│   │   │   arrow-right.svg
│   │   │   arrow-up.svg
│   │   │   at-sign.svg
│   │   │   bar-chart-2.svg
│   │   │   bar-chart-3-svgrepo-com.svg
│   │   │   bar-chart.svg
│   │   │   battery-charging.svg
│   │   │   battery.svg
│   │   │   bell-off.svg
│   │   │   bell-slash-svgrepo-com.svg
│   │   │   bell-svgrepo-com.svg
│   │   │   bell.svg
│   │   │   bluetooth.svg
│   │   │   bold.svg
│   │   │   bookmark.svg
│   │   │   box.svg
│   │   │   bug.svg
│   │   │   calendar-plus.svg
│   │   │   calendar.svg
│   │   │   camera.svg
│   │   │   chart.svg
│   │   │   check-circle-svgrepo-com.svg
│   │   │   check-circle.svg
│   │   │   check-square-svgrepo-com.svg
│   │   │   check-square.svg
│   │   │   check-svgrepo-com.svg
│   │   │   check.svg
│   │   │   chevron-down.svg
│   │   │   chevron-left-circle-svgrepo-com.svg
│   │   │   chevron-left-square-svgrepo-com (1).svg
│   │   │   chevron-left-square-svgrepo-com.svg
│   │   │   chevron-left.svg
│   │   │   chevron-right.svg
│   │   │   chevron-up.svg
│   │   │   chevrons-down.svg
│   │   │   chevrons-left.svg
│   │   │   chevrons-right.svg
│   │   │   chevrons-up.svg
│   │   │   circle.svg
│   │   │   clipboard-check.svg
│   │   │   clipboard.svg
│   │   │   clock.svg
│   │   │   cloud-rain.svg
│   │   │   cloud.svg
│   │   │   code.svg
│   │   │   codepen.svg
│   │   │   columns.svg
│   │   │   command.svg
│   │   │   compass.svg
│   │   │   copy.svg
│   │   │   corner-down-left.svg
│   │   │   corner-down-right.svg
│   │   │   corner-up-left.svg
│   │   │   corner-up-right.svg
│   │   │   cpu.svg
│   │   │   credit-card.svg
│   │   │   cut.svg
│   │   │   dashboard.svg
│   │   │   database-svgrepo-com.svg
│   │   │   database.svg
│   │   │   dollar-sign.svg
│   │   │   download-package-svgrepo-com.svg
│   │   │   download-svgrepo-com.svg
│   │   │   download.svg
│   │   │   drag.svg
│   │   │   droplet.svg
│   │   │   edit-2.svg
│   │   │   edit-3-svgrepo-com.svg
│   │   │   edit-3.svg
│   │   │   edit.svg
│   │   │   external-link.svg
│   │   │   eye-off-svgrepo-com (1).svg
│   │   │   eye-off-svgrepo-com.svg
│   │   │   eye-off.svg
│   │   │   eye-svgrepo-com (1).svg
│   │   │   eye-svgrepo-com.svg
│   │   │   eye.svg
│   │   │   fast-forward.svg
│   │   │   file-minus.svg
│   │   │   file-plus.svg
│   │   │   file-text.svg
│   │   │   file.svg
│   │   │   film.svg
│   │   │   filter.svg
│   │   │   flag.svg
│   │   │   folder-minus.svg
│   │   │   folder-plus.svg
│   │   │   folder.svg
│   │   │   frown.svg
│   │   │   gift.svg
│   │   │   globe.svg
│   │   │   grid.svg
│   │   │   hammer.svg
│   │   │   hard-drive.svg
│   │   │   hash.svg
│   │   │   heart.svg
│   │   │   help-circle-svgrepo-com.svg
│   │   │   help-circle.svg
│   │   │   help.svg
│   │   │   hexagon.svg
│   │   │   home-svgrepo-com.svg
│   │   │   home.svg
│   │   │   image.svg
│   │   │   inbox.svg
│   │   │   info.svg
│   │   │   italic.svg
│   │   │   key.svg
│   │   │   laptop.svg
│   │   │   layers.svg
│   │   │   layout.svg
│   │   │   link.svg
│   │   │   list-ordered.svg
│   │   │   list.svg
│   │   │   loader-2.svg
│   │   │   loader.svg
│   │   │   lock-password-unlocked-svgrepo-com.svg
│   │   │   lock.svg
│   │   │   log-in-svgrepo-com.svg
│   │   │   log-in.svg
│   │   │   log-off-svgrepo-com.svg
│   │   │   log-out-off-out-svgrepo-com.svg
│   │   │   log-out.svg
│   │   │   mail-open.svg
│   │   │   mail-reception-svgrepo-com.svg
│   │   │   mail-svgrepo-com.svg
│   │   │   mail.svg
│   │   │   map-pin-svgrepo-com.svg
│   │   │   map-pin.svg
│   │   │   map.svg
│   │   │   maximize.svg
│   │   │   meh.svg
│   │   │   menu-alt.svg
│   │   │   menu.svg
│   │   │   message-circle.svg
│   │   │   message-square.svg
│   │   │   mic-off.svg
│   │   │   mic.svg
│   │   │   minimize.svg
│   │   │   minus-circle.svg
│   │   │   minus-square.svg
│   │   │   minus.svg
│   │   │   monitor.svg
│   │   │   moon.svg
│   │   │   more-horizontal.svg
│   │   │   more-vertical.svg
│   │   │   move.svg
│   │   │   music.svg
│   │   │   navigation.svg
│   │   │   octagon.svg
│   │   │   option.svg
│   │   │   orders.svg
│   │   │   package-box-ui-2-svgrepo-com.svg
│   │   │   package.svg
│   │   │   pause.svg
│   │   │   percent.svg
│   │   │   phone-call.svg
│   │   │   phone-calling-svgrepo-com.svg
│   │   │   phone-off.svg
│   │   │   phone-svgrepo-com.svg
│   │   │   phone.svg
│   │   │   pie-chart.svg
│   │   │   play.svg
│   │   │   plus-circle.svg
│   │   │   plus-square.svg
│   │   │   plus.svg
│   │   │   power-off.svg
│   │   │   power.svg
│   │   │   printer.svg
│   │   │   receipt.svg
│   │   │   refresh-cw-alt-2-svgrepo-com.svg
│   │   │   refresh-cw-svgrepo-com.svg
│   │   │   refresh-cw.svg
│   │   │   refresh.svg
│   │   │   repeat.svg
│   │   │   rewind.svg
│   │   │   rotate-ccw.svg
│   │   │   rotate-cw.svg
│   │   │   rows.svg
│   │   │   save.svg
│   │   │   scissors.svg
│   │   │   search.svg
│   │   │   send-square-svgrepo-com.svg
│   │   │   send-svgrepo-com.svg
│   │   │   send.svg
│   │   │   server.svg
│   │   │   settings-2.svg
│   │   │   settings-svgrepo-com.svg
│   │   │   settings.svg
│   │   │   share-2.svg
│   │   │   share.svg
│   │   │   shield-check.svg
│   │   │   shield-off.svg
│   │   │   shield-slash-alt-1-svgrepo-com.svg
│   │   │   shield.svg
│   │   │   shopping-bag.svg
│   │   │   shopping-cart-1135-svgrepo-com.svg
│   │   │   shopping-cart-svgrepo-com.svg
│   │   │   shopping-cart.svg
│   │   │   sidebar.svg
│   │   │   skip-back.svg
│   │   │   skip-forward.svg
│   │   │   slash.svg
│   │   │   sliders.svg
│   │   │   smartphone.svg
│   │   │   smile.svg
│   │   │   square.svg
│   │   │   star-half.svg
│   │   │   star.svg
│   │   │   stop.svg
│   │   │   sun.svg
│   │   │   support.svg
│   │   │   tablet.svg
│   │   │   tag.svg
│   │   │   tags.svg
│   │   │   telegram.svg
│   │   │   terminal.svg
│   │   │   thumbs-down.svg
│   │   │   thumbs-up.svg
│   │   │   toggle-left.svg
│   │   │   toggle-right.svg
│   │   │   tool.svg
│   │   │   trash-2.svg
│   │   │   trash-bin-trash-svgrepo-com.svg
│   │   │   trash.svg
│   │   │   trending-down.svg
│   │   │   trending-up.svg
│   │   │   triangle.svg
│   │   │   type.svg
│   │   │   underline.svg
│   │   │   unlink.svg
│   │   │   unlock.svg
│   │   │   upload-minimalistic-svgrepo-com.svg
│   │   │   upload-svgrepo-com.svg
│   │   │   upload.svg
│   │   │   user-alt-1-svgrepo-com.svg
│   │   │   user-check.svg
│   │   │   user-minus.svg
│   │   │   user-plus-01-svgrepo-com.svg
│   │   │   user-plus-svgrepo-com (1).svg
│   │   │   user-plus-svgrepo-com.svg
│   │   │   user-plus.svg
│   │   │   user-x.svg
│   │   │   user.svg
│   │   │   users-svgrepo-com.svg
│   │   │   users.svg
│   │   │   video.svg
│   │   │   volume-1.svg
│   │   │   volume-2.svg
│   │   │   volume-x.svg
│   │   │   volume.svg
│   │   │   watch.svg
│   │   │   whatsapp.svg
│   │   │   whatsapp48.svg
│   │   │   wifi-off.svg
│   │   │   wifi.svg
│   │   │   wrench.svg
│   │   │   x-circle.svg
│   │   │   x-square.svg
│   │   │   x.svg
│   │   │   zoom-in.svg
│   │   │   zoom-out.svg
│   │   │
│   │   └───modern
│   │           alert-circle.svg
│   │           bell.svg
│   │           box.svg
│   │           calendar.svg
│   │           camera.svg
│   │           chart.svg
│   │           check.svg
│   │           chevron-down.svg
│   │           chevron-left.svg
│   │           chevron-right.svg
│   │           chevron-up.svg
│   │           clock.svg
│   │           coffee.svg
│   │           copy.svg
│   │           database.svg
│   │           download.svg
│   │           edit.svg
│   │           eye-off.svg
│   │           eye.svg
│   │           file-text.svg
│   │           file.svg
│   │           filter.svg
│   │           flag.svg
│   │           folder-open.svg
│   │           folder.svg
│   │           globe.svg
│   │           grid.svg
│   │           heart.svg
│   │           help.svg
│   │           home.svg
│   │           image.svg
│   │           key.svg
│   │           layers.svg
│   │           link.svg
│   │           lock.svg
│   │           mail.svg
│   │           map-pin.svg
│   │           map.svg
│   │           message-circle.svg
│   │           message-square.svg
│   │           moon.svg
│   │           more-horizontal.svg
│   │           more-vertical.svg
│   │           move.svg
│   │           orders.svg
│   │           package.svg
│   │           phone.svg
│   │           plus-square.svg
│   │           print.svg
│   │           refresh.svg
│   │           search.svg
│   │           send.svg
│   │           settings.svg
│   │           share.svg
│   │           shield.svg
│   │           shopping-cart.svg
│   │           star.svg
│   │           sun.svg
│   │           support.svg
│   │           tag.svg
│   │           telegram.svg
│   │           thumbs-down.svg
│   │           thumbs-up.svg
│   │           trash.svg
│   │           upload.svg
│   │           user-check.svg
│   │           user-plus.svg
│   │           users.svg
│   │           video.svg
│   │           whatsapp.svg
│   │           x.svg
│   │
│   └───js
├───bootstrap
│       autoloader.php
│
├───core
│       class-cron.php
│       class-settings.php
│       class-upgrader.php
│       container.php
│       plugin.php
│
├───frontend
│   ├───assets
│   │   ├───css
│   │   │       frontend-admin.css
│   │   │       frontend-auth.css
│   │   │       frontend-core.css
│   │   │       frontend-panel.css
│   │   │       frontend-responsive.css
│   │   │       product-options-frontend.css
│   │   │       support.css
│   │   │
│   │   └───js
│   │           admin-login.js
│   │           auth.js
│   │           panel.js
│   │           product-options.js
│   │           support.js
│   │
│   ├───pages
│   │       forgot-password.php
│   │       login.php
│   │       support-chat.php
│   │       user-panel.php
│   │
│   └───templates
├───includes
│   ├───ajax
│   │       class-ajax-auth.php
│   │       class-ajax-editor.php
│   │       class-ajax-loader.php
│   │       class-ajax-settings.php
│   │       class-ajax-support.php
│   │       class-ajax-tests.php
│   │       class-campaign-ajax.php
│   │
│   └───shortcodes
│           class-shortcodes-auth.php
│           class-shortcodes-forgot.php
│           class-shortcodes-loader.php
│           class-shortcodes-panel.php
│
├───modules
│   ├───analytics
│   │       class-audit.php
│   │       class-health.php
│   │       class-logger.php
│   │
│   ├───auth
│   │       class-app-auth.php
│   │       class-login.php
│   │       class-otp.php
│   │
│   ├───campaign
│   │       class-campaign-compliance.php
│   │       class-campaign-queue.php
│   │       class-campaign.php
│   │
│   ├───customer
│   │       class-notifications.php
│   │       class-order-meta.php
│   │       class-order-tracking.php
│   │       class-user-customizations.php
│   │
│   ├───messaging
│   │   │   class-messaging.php
│   │   │   class-sms.php
│   │   │
│   │   └───providers
│   │           class-custom-provider.php
│   │           class-kavenegar-provider.php
│   │           class-provider-interface.php
│   │           class-sms-ir-provider.php
│   │
│   ├───product-options
│   │   │   class-ajax-handler.php
│   │   │   class-field-renderer.php
│   │   │   class-install.php
│   │   │   class-order-display.php
│   │   │   class-pricing-engine.php
│   │   │   class-template-manager.php
│   │   │   class-woocommerce-integration.php
│   │   │   readme.txt
│   │   │
│   │   ├───admin
│   │   │   │   class-field-renderer.php
│   │   │   │   class-preset-library-page.php
│   │   │   │   meta-box.php
│   │   │   │
│   │   │   └───pages
│   │   │           template-editor-builder.php
│   │   │           template-editor-code.php
│   │   │           template-editor-preview.php
│   │   │           template-editor-settings.php
│   │   │           template-editor.php
│   │   │           template-export-import.php
│   │   │           template-form.php
│   │   │           template-list.php
│   │   │           template-settings.php
│   │   │
│   │   └───src
│   │       ├───Repositories
│   │       │       TemplateRepository.php
│   │       │
│   │       └───Services
│   │               ConditionEvaluator.php
│   │               FieldSchemaValidator.php
│   │               PresetLibrary.php
│   │               TemplateService.php
│   │
│   └───support
│           class-support.php
│
├───shared
│   ├───helpers
│   │       class-helper.php
│   │
│   └───interfaces
└───templates
│   admin-dashboard.php
│   admin-login.php
│
├───admin-support-tabs
│       conversation.php
│       list.php
│       new-ticket.php
│
├───campaign-tabs
│       audience.php
│       create.php
│       dashboard.php
│       history.php
│       settings.php
│
├───defaults
│       admin-login.css
│       admin-login.html
│       admin-login.js
│       customer-login.css
│       customer-login.html
│       customer-login.js
│       lost-password.css
│       lost-password.html
│       lost-password.js
│       user-panel.css
│       user-panel.html
│       user-panel.js
│
└───settings-tabs
appearance.php
captcha.php
email.php
general.php
integration.php
login-methods.php
messaging.php
otp.php
pages.php
security.php
sms.php
smtp.php
tracking.php

ساختار فایل‌ها و پوشه‌ها

شرح کامل کلاس‌ها

جداول دیتابیس

شورت‌کدها

هوک‌ها و فیلترها

نکات امنیتی

راهنمای توسعه

عیب‌یابی

استاندارد طراحی رابط کاربری و آیکون‌ها

قواعد توسعه و نگهداری آینده

وضعیت مستندات و موارد نیازمند بررسی

۱. معرفی پلاگین

۱.۱. چیستی پلاگین

EzLens Secure Login یک پلاگین جامع و یکپارچه برای مدیریت احراز هویت، پنل کاربری، سیستم تیکت پشتیبانی، کمپینگ تبلیغاتی، ویژگی‌های محصول و داشبورد مدیریت در سایت‌های وردپرسی است. این پلاگین به‌صورت ویژه برای فروشگاه‌های حوزه سلامت بینایی (عینک، لنز، خدمات چشم‌پزشکی) طراحی شده اما قابلیت استفاده در هر نوع سایت وردپرسی را دارد.

توجه: نسخه‌ی فعلی با ساختار ماژولار و لایه‌ای (Repository-Service-Controller) بازنویسی شده است تا امکان توسعه و نگهداری آسان‌تر فراهم شود.

۱.۲. ویژگی‌های کلیدی

#

ویژگی

توضیح

۱

تغییر مسیر ورود

جایگزینی wp-login.php و wp-admin با آدرس‌های سفارشی

۲

سه روش ورود

ورود با OTP (پیامک)، ورود دستی (شماره موبایل + رمز)، ثبت‌نام

۳

پنل کاربری کامل

پیشخوان، سبد خرید، سفارش‌ها، آدرس، پروفایل، نسخه پزشکی، امنیت، پشتیبانی

۴

سیستم تیکت پشتیبانی

چت آنلاین، ارسال فایل، مدیریت تیکت‌ها با صفحه‌بندی و فیلتر

۵

کمپینگ تبلیغاتی

ارسال ایمیل/پیامک گروهی، گروه‌های مخاطبان، Import/Export CSV

۶

داشبورد مدیریت

آمار کاربران، لاگ‌ها، لیست کاربران با شماره تماس

۷

سیستم OTP

کدهای یکبارمصرف ۶ رقمی با انقضا و محدودیت تلاش

۸

اتصال به SMS.ir

ارسال کد تأیید از طریق پیامک

۹

ویرایشگر صفحات

ویرایش HTML/CSS/JS صفحات به‌صورت ایزوله

۱۰

ویژگی‌های محصول (Product Options)

ایجاد پالت‌های سفارشی از فیلدها و اتصال به محصولات ووکامرس

۱۱

بکاپ و API

خروجی/ورودی JSON، کلید API برای اتصال برنامه‌های خارجی

۱.۳. مخاطبان هدف

مدیران فروشگاه‌های آنلاین (ویژه حوزه سلامت بینایی)

توسعه‌دهندگان وردپرس که به دنبال یک راه‌حل جامع احراز هویت هستند

صاحبان کسب‌وکارهای آنلاین که نیاز به سیستم پشتیبانی و کمپینگ دارند

مدیران فروشگاه‌های ووکامرس که نیاز به فیلدهای سفارشی برای محصولات دارند

۲. معماری و ساختار کلی

۲.۱. الگوی طراحی

پلاگین از الگوی Singleton برای کلاس‌های اصلی استفاده می‌کند تا از ایجاد چندین نمونه جلوگیری شود.

private static $instance = null;

public static function get_instance() {
    if (null === self::$instance) {
        self::$instance = new self();
    }
    return self::$instance;
}

۲.۲. ساختار لایه‌ای (Repository-Service-Controller)

ماژول ویژگی‌های محصول از معماری لایه‌ای پیروی می‌کند:

لایه

مسیر

توضیح

Repository

modules/product-options/src/Repositories/

ارتباط با دیتابیس و کوئری‌نویسی

Service

modules/product-options/src/Services/

منطق کسب‌وکار، اعتبارسنجی، کش

Controller

modules/product-options/

مدیریت درخواست‌ها (AJAX، هوک‌ها)

UI (View)

modules/product-options/admin/pages/

رندر صفحات مدیریتی

۲.۳. جریان بارگذاری

ezlens-secure-login.php
    ↓
تعریف Constants و مسیرهای جدید
    ↓
plugins_loaded → ezlens_auth_load_core_classes()
    ↓
بارگذاری کلاس‌های هسته از core/ و modules/
    ↓
is_admin() → بارگذاری admin/pages/dashboard.php
    ↓
init → بارگذاری شورت‌کدها و AJAX
    ↓
wp_enqueue_scripts → بارگذاری Assets (شرطی)

۳. ساختار فایل‌ها و پوشه‌ها (نسخه ماژولار)

ezlens-secure-login/
│
├── ezlens-secure-login.php          # فایل اصلی پلاگین (ورودی)
│
├── core/                            # هسته اصلی (قابل تغییر نیست)
│   ├── class-settings.php           # مدیریت تنظیمات با کش
│   ├── class-upgrader.php           # مدیریت ارتقا و Migration
│   └── class-cron.php               # کرون جاب‌ها
│
├── modules/                         # هر قابلیت یک ماژول مستقل
│   ├── auth/                        # احراز هویت
│   │   ├── class-login.php          # تغییر مسیر ورود و صفحات سفارشی
│   │   ├── class-otp.php            # مدیریت کدهای یکبارمصرف
│   │   └── class-app-auth.php       # احراز هویت اپلیکیشن موبایل
│   │
│   ├── customer/                    # مشتری (داشبورد، پروفایل، سفارش)
│   │   ├── class-notifications.php  # اعلان‌های کاربر
│   │   ├── class-order-meta.php     # متاباکس کد رهگیری سفارش
│   │   ├── class-order-tracking.php # رهگیری سفارشات
│   │   └── class-user-customizations.php # سفارشی‌سازی کاربر
│   │
│   ├── campaign/                    # کمپین‌های تبلیغاتی
│   │   ├── class-campaign.php       # مدیریت کمپین‌ها و گروه‌ها
│   │   ├── class-campaign-queue.php # صف و پردازش کمپین
│   │   └── class-campaign-compliance.php # لغو عضویت (Unsubscribe)
│   │
│   ├── messaging/                   # پیام‌رسانی
│   │   ├── class-messaging.php      # لایه اصلی پیام‌رسانی
│   │   ├── class-sms.php            # مدیریت SMS
│   │   └── providers/               # سرویس‌های پیامکی
│   │       ├── class-provider-interface.php
│   │       ├── class-sms-ir-provider.php
│   │       ├── class-kavenegar-provider.php
│   │       └── class-custom-provider.php
│   │
│   ├── support/                     # پشتیبانی (تیکت‌ها)
│   │   └── class-support.php        # مدیریت تیکت‌ها و پیام‌ها
│   │
│   ├── analytics/                   # آمار و گزارش‌ها
│   │   ├── class-logger.php         # لاگ‌گیری ورود/خروج
│   │   ├── class-audit.php          # لاگ امنیتی (Audit Trail)
│   │   └── class-health.php         # سلامت سیستم
│   │
│   └── product-options/             #  ماژول ویژگی‌های محصول (جدید)
│       ├── class-install.php                 # ایجاد جدول دیتابیس
│       ├── class-template-manager.php        # مدیریت پالت‌ها (CRUD)
│       ├── class-ajax-handler.php            # هندلرهای AJAX
│       ├── class-field-renderer.php          # رندر فیلدها در صفحه محصول
│       ├── class-order-display.php           # نمایش در سفارش‌ها
│       ├── class-pricing-engine.php          # موتور محاسبه قیمت پویا
│       ├── class-woocommerce-integration.php # یکپارچه‌سازی با ووکامرس
│       ├── readme.txt                        # اطلاعات اولیه ماژول
│       │
│       ├── admin/
│       │   ├── class-field-renderer.php      # (منسوخ)
│       │   ├── class-preset-library-page.php # کتابخانه قالب‌های آماده
│       │   ├── meta-box.php                  # متاباکس انتخاب پالت در محصول
│       │   └── pages/
│       │       ├── template-list.php         # لیست پالت‌ها
│       │       ├── template-editor.php       # بارگذار اصلی ویرایشگر
│       │       ├── template-editor-builder.php    # سازنده بصری
│       │       ├── template-editor-code.php       # ویرایشگر کد یکپارچه
│       │       ├── template-editor-preview.php    # پیش‌نمایش زنده
│       │       ├── template-editor-settings.php   # تنظیمات و CSS
│       │       └── template-form.php              # نمونه فرم تست
│       │
│       ├── src/
│       │   ├── Repositories/
│       │   │   └── TemplateRepository.php   # کوئری‌های دیتابیس
│       │   └── Services/
│       │       ├── TemplateService.php       # منطق کسب‌وکار پالت‌ها
│       │       ├── FieldSchemaValidator.php  # اعتبارسنجی JSON فیلدها
│       │       ├── ConditionEvaluator.php    # ارزیابی شرایط شرطی
│       │       └── PresetLibrary.php         # کتابخانه قالب‌های آماده
│       │
│       └── frontend/
│           ├── assets/
│           │   ├── css/
│           │   │   └── product-options-frontend.css
│           │   └── js/
│           │       ├── product-options.js
│           │       └── upload-progress.js
│           └── (هیچ قالب جداگانه‌ای نیاز نیست)
│
├── admin/                           # بخش مدیریتی (ادمین)
│   ├── pages/                       # صفحات ادمین (هر صفحه یک فایل)
│   │   ├── dashboard.php            # داشبورد مدیریت (کلاس EzLens_Auth_Admin)
│   │   ├── settings.php             # تنظیمات عمومی
│   │   ├── editor.php               # ویرایشگر صفحات
│   │   ├── logs.php                 # گزارش‌های لاگ
│   │   ├── backup.php               # بکاپ و API
│   │   ├── campaign.php             # مدیریت کمپینگ
│   │   └── support.php              # مدیریت پشتیبانی
│   │
│   └── assets/                      # فایل‌های مدیریتی
│       ├── css/
│       │   ├── admin.css            # استایل اصلی ادمین
│       │   └── admin-campaign.css   # استایل کمپینگ
│       └── js/
│           ├── admin.js             # اسکریپت اصلی ادمین
│           └── admin-campaign.js    # اسکریپت کمپینگ
│
├── frontend/                        # بخش جلوی سایت
│   ├── pages/                       # صفحات فرانت‌اند
│   │   ├── login.php                # ورود/ثبت‌نام مشتری ([minimal_auth])
│   │   ├── forgot-password.php      # فراموشی رمز
│   │   ├── user-panel.php           # پنل کاربری ([modern_user_panel])
│   │   └── support-chat.php         # ویجت چت پشتیبانی
│   │
│   ├── templates/                   # قالب‌های HTML (در صورت نیاز)
│   │
│   └── assets/                      # فایل‌های فرانت‌اند
│       ├── css/
│       │   ├── frontend-core.css    # استایل‌های پایه
│       │   ├── frontend-auth.css    # استایل صفحات ورود
│       │   ├── frontend-panel.css   # استایل پنل کاربری
│       │   ├── frontend-admin.css   # استایل ورود مدیر
│       │   ├── frontend-responsive.css # استایل ریسپانسیو
│       │   ├── product-options-frontend.css # استایل ویژگی‌های محصول
│       │   └── support.css          # استایل ویجت چت
│       └── js/
│           ├── auth.js              # اسکریپت احراز هویت
│           ├── panel.js             # اسکریپت پنل کاربری
│           ├── admin-login.js       # اسکریپت ورود مدیر
│           ├── product-options.js   # اسکریپت ویژگی‌های محصول
│           └── support.js           # اسکریپت ویجت چت
│
├── api/                             # API و Webhook
│   ├── class-api.php                # REST API نسخه ۱
│   ├── class-api-v2.php             # REST API نسخه ۲ (برای اپلیکیشن)
│   └── class-webhooks.php           # ارسال رویدادها به سرورهای خارجی
│
├── shared/                          # کدهای مشترک
│   └── helpers/
│       └── class-helper.php         # توابع کمکی (نرمال‌سازی، رمزگذاری، ایمیل)
│
├── includes/                        # (در حال انتقال به ماژول‌ها)
│   ├── ajax/                        # هندلرهای AJAX
│   │   ├── class-ajax-loader.php    # بارگذار AJAX
│   │   ├── class-ajax-auth.php
│   │   ├── class-ajax-editor.php
│   │   ├── class-ajax-settings.php
│   │   ├── class-ajax-support.php
│   │   ├── class-ajax-tests.php
│   │   └── class-campaign-ajax.php
│   │
│   └── shortcodes/                  # شورت‌کدها
│       ├── class-shortcodes-loader.php
│       ├── class-shortcodes-auth.php
│       ├── class-shortcodes-forgot.php
│       └── class-shortcodes-panel.php
│
├── templates/                       # قالب‌های قدیمی (برای سازگاری)
│   ├── admin-dashboard.php
│   ├── admin-login.php
│   ├── defaults/                    # قالب‌های پیش‌فرض ویرایشگر
│   ├── admin-support-tabs/
│   ├── campaign-tabs/
│   └── settings-tabs/
│
├── assets/                          # فایل‌های عمومی (فقط ضروری‌ها)
│   └── fonts/
│       └── vazirmatn/               # فونت وزیرمتن (داخلی)
│
├── bootstrap/                       # بارگذار اولیه
│   ├── autoloader.php
│   └── container.php
│
└── .gitignore                       # فایل‌های نادیده‌گرفته شده در گیت

۴. شرح کامل کلاس‌ها

۴.۱. کلاس‌های Core (هسته)

کلاس

مسیر

توضیح

EzLens_Auth_Settings

core/class-settings.php

مدیریت تنظیمات با کش (wp_cache)

EzLens_Auth_Upgrader

core/class-upgrader.php

مدیریت ارتقا و ایجاد/به‌روزرسانی جداول

EzLens_Auth_Cron

core/class-cron.php

کرون جاب برای پاک‌سازی خودکار (لاگ‌ها، OTP)

۴.۲. کلاس‌های Modules (ماژول‌ها)

احراز هویت (Auth)

کلاس

مسیر

توضیح

EzLens_Auth_Login

modules/auth/class-login.php

تغییر مسیر wp-login.php، نمایش صفحات سفارشی و هدایت پس از لاگین

EzLens_Auth_OTP

modules/auth/class-otp.php

مدیریت کدهای یکبارمصرف (ذخیره، تأیید، اعتبارسنجی)

EzLens_Auth_App_Auth

modules/auth/class-app-auth.php

احراز هویت بدون رمز برای اپلیکیشن موبایل (Bearer Token)

مشتری (Customer)

کلاس

مسیر

توضیح

EzLens_Auth_Notifications

modules/customer/class-notifications.php

اعلان‌های کاربر

EzLens_Auth_Order_Meta

modules/customer/class-order-meta.php

متاباکس کد رهگیری سفارش در ادمین

EzLens_Order_Tracking

modules/customer/class-order-tracking.php

رهگیری سفارشات (مراحل و کد رهگیری)

EzLens_Auth_User_Customizations

modules/customer/class-user-customizations.php

سفارشی‌سازی‌های کاربر (پروفایل، لیست کاربران)

کمپینگ (Campaign)

کلاس

مسیر

توضیح

EzLens_Auth_Campaign

modules/campaign/class-campaign.php

مدیریت کمپین‌ها، گروه‌ها و مخاطبان

EzLens_Auth_Campaign_Queue

modules/campaign/class-campaign-queue.php

صف و پردازش کمپین‌ها (با WP-Cron)

EzLens_Auth_Campaign_Compliance

modules/campaign/class-campaign-compliance.php

مدیریت لغو عضویت (Unsubscribe)

پیام‌رسانی (Messaging)

کلاس

مسیر

توضیح

EzLens_Auth_Messaging

modules/messaging/class-messaging.php

لایه اصلی پیام‌رسانی (Email, SMS)

EzLens_Auth_SMS

modules/messaging/class-sms.php

مدیریت SMS و سازگاری با نسخه‌های قدیمی

Providerها

modules/messaging/providers/

پیاده‌سازی سرویس‌های SMS.ir، Kavenegar و Custom HTTP

پشتیبانی (Support)

کلاس

مسیر

توضیح

EzLens_Auth_Support

modules/support/class-support.php

مدیریت تیکت‌ها و پیام‌ها

آمار (Analytics)

کلاس

مسیر

توضیح

EzLens_Auth_Logger

modules/analytics/class-logger.php

لاگ‌گیری ورود/خروج

EzLens_Auth_Audit

modules/analytics/class-audit.php

لاگ امنیتی (Audit Trail)

EzLens_Auth_Health

modules/analytics/class-health.php

بررسی سلامت سیستم

ویژگی‌های محصول (Product Options)

کلاس

مسیر

توضیح

لایه Repository





TemplateRepository

src/Repositories/TemplateRepository.php

انجام عملیات دیتابیسی مربوط به پالت‌ها

لایه Services





TemplateService

src/Services/TemplateService.php

منطق کسب‌وکار پالت‌ها (CRUD، اعتبارسنجی، کش، اتصال به محصول)

FieldSchemaValidator

src/Services/FieldSchemaValidator.php

اعتبارسنجی ساختار JSON فیلدها

ConditionEvaluator

src/Services/ConditionEvaluator.php

ارزیابی شرایط شرطی برای نمایش/مخفی کردن فیلدها

PresetLibrary

src/Services/PresetLibrary.php

کتابخانه قالب‌های آماده (لنز، عینک، عینک آفتابی و ...)

لایه Controller





EzLens_Product_Options_Ajax

class-ajax-handler.php

پردازش درخواست‌های AJAX (ذخیره، کپی، حذف، آپلود، پیش‌نمایش)

EzLens_Product_Options_FieldRenderer

class-field-renderer.php

رندر فیلدها در صفحه محصول + قیمت‌گذاری پویا

EzLens_Product_Options_WooCommerce_Integration

class-woocommerce-integration.php

یکپارچه‌سازی با ووکامرس (هوک‌ها، ذخیره‌سازی)

EzLens_Product_Options_OrderDisplay

class-order-display.php

نمایش ویژگی‌ها در پنل کاربری، ادمین و ایمیل‌ها

EzLens_Product_Options_PricingEngine

class-pricing-engine.php

موتور محاسبه قیمت پویا بر اساس انتخاب‌ها

۴.۳. کلاس‌های مدیریت (Admin)

کلاس

مسیر

توضیح

EzLens_Auth_Admin

admin/pages/dashboard.php

مدیریت منوی ادمین، رندر صفحات و ذخیره تنظیمات

۴.۴. کلاس‌های AJAX

کلاس

مسیر

وظیفه

EzLens_Auth_Ajax_Loader

includes/ajax/class-ajax-loader.php

بارگذار درخواست‌های AJAX

EzLens_Auth_Ajax_Auth

includes/ajax/class-ajax-auth.php

OTP، ورود، ثبت‌نام، فراموشی رمز

EzLens_Auth_Ajax_Editor

includes/ajax/class-ajax-editor.php

ذخیره/بازنشانی کدها، پیش‌نمایش، فعال‌سازی صفحات

EzLens_Auth_Ajax_Settings

includes/ajax/class-ajax-settings.php

ذخیره تنظیمات، بکاپ، API Key

EzLens_Auth_Campaign_Ajax

includes/ajax/class-campaign-ajax.php

ایجاد/ارسال/حذف کمپین، گروه‌ها، مخاطبان

EzLens_Auth_Ajax_Support

includes/ajax/class-ajax-support.php

ایجاد تیکت، پاسخ، تغییر وضعیت، جستجوی کاربران

EzLens_Auth_Ajax_Tests

includes/ajax/class-ajax-tests.php

تست SMS و SMTP

۴.۵. کلاس‌های کمکی

کلاس

مسیر

توضیح

EzLens_Auth_Helper

shared/helpers/class-helper.php

توابع کمکی عمومی (نرمال‌سازی موبایل، ایمیل، رمزگذاری، Rate Limiting)

۵. جداول دیتابیس

۵.۱. جدول لاگ‌ها (wp_ezlens_logs)

CREATE TABLE wp_ezlens_logs (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    username varchar(60) NOT NULL,
    action varchar(20) NOT NULL,
    ip varchar(45) NOT NULL,
    user_agent text,
    timestamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY action (action),
    KEY timestamp (timestamp)
);

۵.۲. جدول OTP (wp_ezlens_otp)

CREATE TABLE wp_ezlens_otp (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    mobile varchar(15) NOT NULL,
    code varchar(6) NOT NULL,
    action varchar(20) NOT NULL DEFAULT 'login',
    expires_at datetime NOT NULL,
    is_used tinyint(1) NOT NULL DEFAULT 0,
    attempts int(11) NOT NULL DEFAULT 0,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY mobile (mobile),
    KEY code (code),
    KEY expires_at (expires_at)
);

۵.۳. جداول کمپینگ

wp_ezlens_campaigns

CREATE TABLE wp_ezlens_campaigns (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    type varchar(20) NOT NULL DEFAULT 'email',
    subject varchar(255) DEFAULT '',
    message longtext NOT NULL,
    file_attachment varchar(255) DEFAULT '',
    status varchar(20) NOT NULL DEFAULT 'draft',
    scheduled_at datetime DEFAULT NULL,
    sent_at datetime DEFAULT NULL,
    stats longtext,
    created_by bigint(20) NOT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY status (status),
    KEY created_at (created_at)
);

wp_ezlens_campaign_contacts

CREATE TABLE wp_ezlens_campaign_contacts (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    email varchar(100) NOT NULL,
    phone varchar(20) DEFAULT '',
    category varchar(100) DEFAULT 'عمومی',
    extra_fields longtext,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY email (email),
    KEY category (category)
);

wp_ezlens_campaign_groups

CREATE TABLE wp_ezlens_campaign_groups (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description text,
    type varchar(20) NOT NULL DEFAULT 'custom',
    user_filters longtext,
    contact_ids longtext,
    created_by bigint(20) NOT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY created_by (created_by)
);

wp_ezlens_campaign_group_relations

CREATE TABLE wp_ezlens_campaign_group_relations (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    campaign_id bigint(20) NOT NULL,
    group_id bigint(20) NOT NULL,
    PRIMARY KEY (id),
    KEY campaign_id (campaign_id),
    KEY group_id (group_id)
);

wp_ezlens_campaign_tracks

CREATE TABLE wp_ezlens_campaign_tracks (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    campaign_id bigint(20) NOT NULL,
    recipient_email varchar(100) NOT NULL,
    opened_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip varchar(45) DEFAULT '',
    user_agent text,
    PRIMARY KEY (id),
    KEY campaign_id (campaign_id),
    KEY recipient_email (recipient_email)
);

۵.۴. جداول پشتیبانی

wp_ezlens_support_tickets

CREATE TABLE wp_ezlens_support_tickets (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    status varchar(20) NOT NULL DEFAULT 'open',
    subject varchar(255) DEFAULT '',
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY status (status)
);

wp_ezlens_support_messages

CREATE TABLE wp_ezlens_support_messages (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    ticket_id bigint(20) NOT NULL,
    sender_id bigint(20) NOT NULL,
    sender_type varchar(10) NOT NULL DEFAULT 'user',
    message text NOT NULL,
    file_attachment varchar(255) DEFAULT '',
    is_read tinyint(1) NOT NULL DEFAULT 0,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ticket_id (ticket_id),
    KEY sender_id (sender_id)
);

۵.۵. جداول اضافی (Audit, Notifications, App Tokens, ...)

جداول زیر توسط core/class-upgrader.php ایجاد می‌شوند:

wp_ezlens_audit_log (لاگ امنیتی)

wp_ezlens_notifications (اعلان‌های کاربر)

wp_ezlens_app_tokens (توکن‌های اپلیکیشن)

wp_ezlens_campaign_unsubscribes (لغو عضویت)

wp_ezlens_email_log (لاگ ایمیل‌ها)

wp_ezlens_campaign_recipients (گیرندگان کمپین)

۵.۶.  جدول ویژگی‌های محصول

wp_ezlens_option_templates

CREATE TABLE wp_ezlens_option_templates (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    title varchar(255) NOT NULL,
    description text DEFAULT NULL,
    fields longtext NOT NULL,
    status varchar(20) NOT NULL DEFAULT 'active',
    created_by bigint(20) unsigned NOT NULL DEFAULT 0,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY status (status),
    KEY created_by (created_by),
    KEY created_at (created_at)
);

۶. شورت‌کدها

شورت‌کد

کاربرد

فایل قالب

[minimal_auth]

صفحه ورود/ثبت‌نام مشتری

frontend/pages/login.php

[ezlens_lost_password]

صفحه فراموشی رمز (غیرفعال)

frontend/pages/forgot-password.php

[modern_user_panel]

پنل کاربری کامل

frontend/pages/user-panel.php

[admin_login_page]

صفحه ورود مدیر

templates/admin-login.php

[ezlens_support_chat]

ویجت چت پشتیبانی

frontend/pages/support-chat.php

۷. هوک‌ها و فیلترها

۷.۱. فیلترها (Filters)

فیلتر

توضیح

نمونه

ezlens_auth_login_redirect

تغییر مسیر پس از ورود

add_filter('ezlens_auth_login_redirect', function($url, $user) { return home_url('/custom-page/'); }, 10, 2);

ezlens_auth_register_redirect

تغییر مسیر پس از ثبت‌نام

add_filter('ezlens_auth_register_redirect', function($url, $user_id) { return home_url('/welcome/'); }, 10, 2);

ezlens_auth_customer_redirect

تغییر مسیر مشتری پس از ورود

add_filter('ezlens_auth_customer_redirect', function($url, $user) { return home_url('/my-account/'); }, 10, 2);

۷.۲. اکشن‌ها (Actions)

اکشن

توضیح

نمونه

ezlens_auth_user_registered

پس از ثبت‌نام کاربر جدید

add_action('ezlens_auth_user_registered', function($user_id, $data) { error_log('User registered: ' . $data['email']); }, 10, 2);

ezlens_support_ticket_created

پس از ایجاد تیکت جدید

add_action('ezlens_support_ticket_created', function($ticket_id) { // ... }, 10, 1);

ezlens_campaign_sent

پس از ارسال کامل کمپین

add_action('ezlens_campaign_sent', function($campaign_id, $stats) { // ... }, 10, 2);

۷.۳. هوک‌های ماژول ویژگی‌های محصول

هوک

کاربرد

متد مرتبط

woocommerce_before_add_to_cart_button

نمایش فیلدها در صفحه محصول

FieldRenderer::render_fields()

woocommerce_add_cart_item_data

افزودن اطلاعات انتخاب‌شده به سبد خرید

WooCommerceIntegration::add_cart_item_data()

woocommerce_get_item_data

نمایش اطلاعات در سبد خرید

WooCommerceIntegration::display_cart_item_data()

woocommerce_checkout_create_order_line_item

ذخیره اطلاعات در سفارش

WooCommerceIntegration::add_order_item_meta()

woocommerce_order_item_meta_end

نمایش در پنل کاربری و ادمین

OrderDisplay::display_in_*()

add_meta_boxes

افزودن متاباکس انتخاب پالت در محصول و سفارش

meta-box.php

woocommerce_cart_item_price

محاسبه قیمت پویا در سبد خرید

PricingEngine::calculate_cart_item_price()

۸. نکات امنیتی

۸.۱. Nonce برای تمام درخواست‌ها

// در JS
'nonce' => wp_create_nonce('ezlens_auth_nonce')

// در PHP
check_ajax_referer('ezlens_auth_nonce', 'nonce');

۸.۲. رمزگذاری کلیدهای حساس

کلیدهای زیر با openssl_encrypt رمزگذاری می‌شوند:

sms_api_key

smtp_password

captcha_secret_key

otp_sms_api_key

campaign_sms_api_key

۸.۳. Sanitization و Validation

تمام ورودی‌ها با توابع استاندارد وردپرس پاک‌سازی می‌شوند:

sanitize_text_field()

sanitize_email()

sanitize_textarea_field()

wp_kses_post()

esc_url_raw()

۸.۴. مسدودسازی wp-admin برای کاربران غیرمدیر

if (is_admin() && !wp_doing_ajax() && !defined('DOING_AJAX')) {
    $current_user = wp_get_current_user();
    if (!is_user_logged_in() || !in_array('administrator', (array) $current_user->roles)) {
        $this->show_404();
        exit;
    }
}

۸.۵. محدودیت تلاش OTP

هر شماره موبایل حداکثر ۵ بار تلاش ناموفق می‌تواند داشته باشد (otp_max_attempts).

۸.۶. Rate Limiting برای ورود

با استفاده از transient، تلاش‌های ناموفق ورود محدود می‌شوند (پیش‌فرض: ۵ تلاش در ۱۵ دقیقه).

۸.۷. امنیت ماژول ویژگی‌های محصول

تمام ورودی‌های فیلدها با sanitize_text_field, wp_kses_post و ... پاک‌سازی می‌شوند.

FieldSchemaValidator از ورود داده‌های مخرب JSON جلوگیری می‌کند.

کوئری‌های دیتابیس در TemplateRepository با $wpdb->prepare پارامتریک شده‌اند.

فایل‌های آپلودشده با media_handle_upload مدیریت می‌شوند.

۹. راهنمای توسعه

۹.۱. افزودن یک ماژول جدید

برای افزودن قابلیت جدید به‌صورت ماژولار:

پوشه‌ی جدید در modules/ ایجاد کنید، مثلاً modules/loyalty/.

کلاس اصلی را با نام class-loyalty.php ایجاد کنید.

از الگوی Singleton استفاده کنید.

هوک‌های موردنیاز را در __construct() ثبت کنید.

اگر نیاز به جدول دیتابیس دارید، متد create_table() اضافه کنید و در core/class-upgrader.php فراخوانی کنید.

برای بارگذاری خودکار کلاس، نام آن را در Autoloader (که در ezlens-secure-login.php تعریف شده) قرار دهید (Autoloader به‌صورت خودکار ماژول‌ها را اسکن می‌کند).

۹.۲. توسعه ماژول ویژگی‌های محصول

۹.۲.۱. افزودن نوع فیلد جدید

در template-editor-builder.php، یک دکمه به نوار ابزار اضافه کنید:

<button class="btn-add-field" data-type="new_type">🆕 نوع جدید</button>

در تابع getFieldHTML، یک case جدید برای type اضافه کنید.

در متد render_field در class-field-renderer.php، یک case جدید برای رندر در فرانت‌اند اضافه کنید.

در FieldSchemaValidator، قانون اعتبارسنجی برای نوع جدید اضافه کنید.

۹.۲.۲. افزودن قابلیت شرطی (Conditional Logic)

از کلاس ConditionEvaluator استفاده کنید.

یک کلید جدید به field اضافه کنید مانند condition: { field: "other_field", operator: "==", value: "something" }.

در product-options.js، هنگام تغییر فیلدها، شرایط را بررسی کنید.

۹.۲.۳. افزودن قالب آماده جدید (Preset)

در PresetLibrary، یک متد جدید اضافه کنید:

public function getPresetNew() {
    return [
        'title' => 'عنوان قالب',
        'fields' => [ /* تعریف فیلدها */ ]
    ];
}

در متد getPresets()، قالب جدید را به لیست اضافه کنید.

۹.۳. افزودن یک شورت‌کد جدید

مرحله ۱: در includes/shortcodes/class-shortcodes-loader.php شورت‌کد را ثبت کنید:

add_shortcode('my_custom_shortcode', [$this, 'render_my_shortcode']);

public function render_my_shortcode($atts) {
    return '<div>محتوای شورت‌کد</div>';
}

مرحله ۲: در ezlens-secure-login.php شورت‌کد را به لیست بررسی‌ها اضافه کنید (در تابع enqueue_frontend_assets):

$shortcodes = array('minimal_auth', 'my_custom_shortcode', ...);

۹.۴. افزودن یک تب جدید به تنظیمات

مرحله ۱: ایجاد فایل templates/settings-tabs/mytab.php

مرحله ۲: در includes/ajax/class-ajax-settings.php کیس جدید اضافه کنید:

case 'mytab':
    include EZLAUTH_PLUGIN_DIR . 'templates/settings-tabs/mytab.php';
    break;

مرحله ۳: در admin/pages/settings.php دکمه تب جدید اضافه کنید.

۹.۵. افزودن یک متد AJAX جدید

مرحله ۱: در کلاس AJAX مناسب، متد جدید اضافه کنید:

public static function my_ajax_action() {
    check_ajax_referer('ezlens_auth_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی غیرمجاز');
        wp_die();
    }
    // منطق شما
    wp_send_json_success(['message' => 'انجام شد.']);
    wp_die();
}

مرحله ۲: در __construct() همان کلاس، اکشن را ثبت کنید:

add_action('wp_ajax_ezlens_my_ajax_action', [__CLASS__, 'my_ajax_action']);

مرحله ۳: در JS درخواست ارسال کنید:

$.post(ezlens_auth_ajax.ajax_url, {
    action: 'ezlens_my_ajax_action',
    nonce: ezlens_auth_ajax.nonce,
    // داده‌ها
}, function(response) {
    // پردازش پاسخ
});

۹.۶. افزودن یک متغیر قابل شخصی‌سازی در ایمیل‌ها

در متد send_campaign یا send_email، متغیر جدید را به جایگزین‌ها اضافه کنید:

$message = str_replace(
    ['{name}', '{email}', '{my_custom_var}'],
    [$recipient['name'], $recipient['email'], $custom_value],
    $campaign->message
);

۱۰. عیب‌یابی

۱۰.۱. مشکلات رایج و راه‌حل‌ها

مشکل

راه‌حل

خطای ۴۰۴ در wp-admin

مطمئن شوید کاربر نقش مدیر دارد. در غیر این صورت به آدرس جدید ورود هدایت می‌شود.

عدم دریافت کد OTP

تنظیمات SMS را بررسی کنید (API Key, Line Number, Template ID). حالت Sandbox را غیرفعال کنید.

خطای ۴۰۳ در AJAX

nonce را بررسی کنید. مطمئن شوید check_ajax_referer با نام صحیح فراخوانی شده است.

خطای ۵۰۰ در AJAX

لاگ‌های PHP را بررسی کنید (wp-content/debug.log).

تیکت ایجاد نمی‌شود

جداول دیتابیس را بررسی کنید. متد create_tables را در class-support.php اجرا کنید.

مخاطب دستی اضافه نمی‌شود

ستون category را در جدول wp_ezlens_campaign_contacts بررسی کنید.

کمپین ارسال نمی‌شود

تنظیمات SMTP و SMS را بررسی کنید. مطمئن شوید گروه‌های مخاطبان انتخاب شده‌اند.

خطای دیتابیس Invalid default value for 'id'

مطمئن شوید core/class-upgrader.php به‌روز است و تابع repair_legacy_id_defaults() اجرا شده است.

استایل‌ها نمایش داده نمی‌شوند

بررسی کنید که فایل‌های CSS در مسیرهای جدید (frontend/assets/css/ و admin/assets/css/) قرار دارند و با Ctrl+F5 رفرش کنید.

** فیلدهای ویژگی محصول در صفحه نمایش داده نمی‌شوند**

۱. مطمئن شوید یک پالت به محصول متصل شده است. ۲. بررسی کنید پالت فعال باشد. ۳. کش صفحه را پاک کنید.

** قیمت پویا محاسبه نمی‌شود**

مطمئن شوید PricingEngine در WooCommerceIntegration به‌درستی فراخوانی شده است. خطاهای JS کنسول را بررسی کنید.

** آپلود فایل کار نمی‌کند**

۱. محدودیت حجم را بررسی کنید. ۲. نوع فایل مجاز باشد. ۳. اطمینان از وجود media_handle_upload در سرور.

** خطای getFieldsData is not defined**

فایل‌های JS را به‌درستی بارگذاری کنید و از window.getFieldsData استفاده کنید.

۱۰.۲. فعال‌سازی حالت دیباگ

برای مشاهده خطاها، در فایل wp-config.php کد زیر را اضافه کنید:

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

سپس خطاها در wp-content/debug.log ذخیره می‌شوند.

۱۰.۳. بازنشانی تنظیمات

برای بازنشانی تمام تنظیمات پلاگین به حالت پیش‌فرض:

به بخش تنظیمات عمومی EzLens بروید.

روی دکمه بازنشانی همه کلیک کنید.

۱۰.۴. بازنشانی تنظیمات ویژگی‌های محصول

جداول دیتابیس را دستکاری نکنید.

برای بازنشانی یک پالت، آن را در صفحه لیست حذف و دوباره ایجاد کنید.

برای بازنشانی همه پالت‌ها، می‌توانید از طریق phpMyAdmin جدول wp_ezlens_option_templates را خالی کنید (توصیه نمی‌شود مگر در مواقع ضروری).

اطلاعات تماس و پشتیبانی

وب‌سایت: https://ezlens.ir

ایمیل: info@ezlens.ir

تلفن: 02144385667

۱۱. استاندارد طراحی رابط کاربری و آیکون‌ها

این بخش از این نسخه به بعد یک قانون رسمی برای تمام توسعه‌های جدید پلاگین است.

۱۱.۱. ممنوعیت آیکون‌های درون‌متنی

در رابط کاربری، متن نباید با Emoji یا آیکون‌های متنی مانند موارد زیر تزئین شود:

Emojiهای عمومی

کاراکترهای تصویری به‌عنوان آیکون

ترکیب‌هایی مانند +, x, ✓, ! برای نمایش عملیات

آیکون‌های متفاوت و ناسازگار در بخش‌های مختلف رابط

اگر یک مفهوم نیاز به آیکون دارد، آیکون باید به‌صورت یک Asset واقعی و قابل کنترل استفاده شود.

۱۱.۲. استفاده از آیکون‌های مینیمال و مدرن

اولویت استفاده از آیکون‌ها به این ترتیب است:

آیکون موجود و مناسب در assets/icons/modern/

آیکون مناسب موجود در assets/icons/

آیکون جدید با همان زبان بصری مجموعه موجود

در صورت نبود آیکون مناسب، قبل از استفاده از جایگزین نامناسب، اعلام شود که آیکون جدید موردنیاز است تا طراحی/ایجاد شود.

۱۱.۳. اصل یکپارچگی

آیکون‌های یک صفحه باید:

از یک سبک بصری واحد پیروی کنند.

ضخامت Stroke و ابعاد منطقی مشابه داشته باشند.

در حالت RTL و LTR رفتار قابل پیش‌بینی داشته باشند.

برای عملیات مشابه در تمام صفحات یک آیکون یکسان داشته باشند.

صرفاً برای تزئین استفاده نشوند و معنای عملیاتی مشخص داشته باشند.

۱۱.۴. انتخاب آیکون

کاربرد

آیکون پیشنهادی

داشبورد

dashboard.svg یا home.svg

تنظیمات

settings.svg

جست‌وجو

search.svg

فیلتر

filter.svg

افزودن

plus-square.svg یا plus.svg

ویرایش

edit.svg

حذف

trash.svg

ذخیره

save.svg در مجموعه عمومی یا معادل موجود در مجموعه مدرن

نمایش

eye.svg

مخفی‌سازی

eye-off.svg

امنیت

shield.svg یا lock.svg

کاربر

user.svg

کاربران

users.svg

سفارش

orders.svg یا shopping-cart.svg

محصول

package.svg

پیام

message-circle.svg

پشتیبانی

support.svg

اعلان

bell.svg

تقویم

calendar.svg

بارگذاری

upload.svg

دانلود

download.svg

تازه‌سازی

refresh.svg

اطلاعات

help.svg یا alert-circle.svg

خطا

alert-circle.svg

موفقیت

check.svg

این جدول راهنمای انتخاب است و نباید باعث استفاده اجباری از آیکونی شود که از نظر معنایی یا بصری مناسب نیست.

۱۱.۵. اگر آیکون موردنیاز وجود نداشت

اگر برای قابلیت جدید آیکون مناسب در پوشه‌های موجود وجود نداشت:

از Emoji استفاده نشود.

از کاراکتر متنی به‌عنوان آیکون استفاده نشود.

از SVG تصادفی با سبک متفاوت استفاده نشود.

موضوع به‌عنوان «آیکون موردنیاز» اعلام شود.

آیکون جدید باید در assets/icons/modern/ قرار گیرد، مگر اینکه دلیل معماری مشخصی برای محل دیگری وجود داشته باشد.

نام فایل باید کوتاه، معنادار و با الگوی نام‌گذاری موجود سازگار باشد.

۱۱.۶. استاندارد استفاده در PHP و HTML

ترجیح بر استفاده از SVG به‌عنوان Asset کنترل‌شده است. در صورت نیاز، کلاس CSS مخصوص آیکون تعریف شود تا اندازه، Alignment و وضعیت Hover/Disabled در یک محل کنترل شود.

نمونه مفهومی:

<img
    class="ezlens-icon"
    src="<?php echo esc_url($icon_url); ?>"
    alt=""
    aria-hidden="true"
/>

برای عملیات مهم، متن دکمه باید همچنان مستقل از آیکون قابل فهم باشد. آیکون جایگزین متن نیست، مگر در کنترل‌های کاملاً شناخته‌شده مانند دکمه بستن یا منوی بیشتر که Tooltip/Accessible Label مناسب دارند.

۱۲. قواعد توسعه و نگهداری آینده

۱۲.۱. اصل مرجعیت

این سند مرجع معماری و ساختار ثبت‌شده پروژه است، اما در زمان توسعه باید وضعیت واقعی کد نیز بررسی شود. اگر بین این سند و کد موجود اختلافی وجود داشت:

اختلاف ثبت شود.

مسیر واقعی کد مشخص شود.

سند پس از تصمیم نهایی به‌روزرسانی شود.

هیچ ساختار جدیدی صرفاً بر اساس حدس به پروژه نسبت داده نشود.

۱۲.۲. حفظ معماری ماژولار

قابلیت جدید باید تا حد امکان داخل ماژول مربوط به خودش قرار گیرد.

از قرار دادن منطق کسب‌وکار جدید در فایل اصلی پلاگین، فایل‌های عمومی یا کلاس‌هایی که مسئولیت دیگری دارند خودداری شود.

۱۲.۳. جداسازی مسئولیت‌ها

Repository: دسترسی به داده و Query

Service: منطق کسب‌وکار و Validation

Controller/AJAX: دریافت و پاسخ‌دهی به درخواست

Renderer/View: نمایش

Integration: اتصال به WordPress/WooCommerce

Helper: عملیات عمومی و بدون وابستگی شدید به یک قابلیت خاص

۱۲.۴. امنیت

هر قابلیت جدید باید قبل از ادغام حداقل این موارد را بررسی کند:

Capability

Nonce

Sanitization

Validation

Authorization

خروجی Escaping

کنترل فایل و MIME Type در Upload

جلوگیری از SQL Injection

Rate Limiting در عملیات حساس

۱۲.۵. سازگاری با ووکامرس

در ماژول Product Options، اطلاعات انتخاب‌شده باید به شکل قابل اعتماد از مرحله محصول تا سبد، Checkout و Order منتقل شود.

هر تغییری که روی قیمت، داده سفارش یا Meta اثر می‌گذارد باید مسیر کامل زیر بررسی شود:

Product
  ↓
Product Options
  ↓
Cart Item
  ↓
Cart Price
  ↓
Checkout
  ↓
Order Item
  ↓
Customer/Admin Display

۱۲.۶. تغییرات دیتابیس

برای جدول جدید یا تغییر Schema:

Migration مشخص ایجاد شود.

نسخه Schema یا Plugin Version در فرآیند Upgrade مدیریت شود.

از تغییر دستی دیتابیس در محیط Production خودداری شود.

داده‌های موجود قبل از Migration در نظر گرفته شوند.

در صورت امکان Migration قابل برگشت یا حداقل قابل بازیابی باشد.

۱۲.۷. Assets

Asset جدید فقط در صورت نیاز واقعی اضافه شود.

CSS باید ماژولار باشد.

JS باید فقط در صفحات موردنیاز Load شود.

از Duplicate Asset جلوگیری شود.

آیکون‌ها طبق استاندارد بخش ۱۱ باشند.

فایل‌های قدیمی قبل از حذف باید از نظر وابستگی بررسی شوند.

۱۲.۸. توسعه Product Options

در توسعه این ماژول:

Schema فیلدها باید معتبر باشد.

منطق شرطی باید از ConditionEvaluator عبور کند.

Validation باید در سمت سرور نیز انجام شود.

قیمت هرگز نباید فقط بر اساس داده قابل اعتماد از JavaScript تعیین شود.

داده سفارش باید مستقل از وضعیت فعلی Template قابل بازیابی باشد.

Upload باید محدود و اعتبارسنجی شود.

UI باید RTL، Responsive و سازگار با سبک مدیریتی EzLens باشد.

۱۲.۹. اصل Backward Compatibility

تا زمانی که نیاز قطعی به تغییر Breaking وجود ندارد:

Hookهای عمومی حذف نشوند.

Shortcodeهای موجود حفظ شوند.

ساختار داده قدیمی بدون Migration مناسب شکسته نشود.

APIهای قبلی با دقت مدیریت شوند.

تغییرات ناسازگار در Changelog ثبت شوند.

۱۲.۱۰. قانون مستندسازی

هر قابلیت مهم جدید باید حداقل در این سند ثبت شود:

نام قابلیت

محل فایل‌ها

کلاس‌های اصلی

جدول‌های دیتابیس در صورت وجود

Hookها

AJAX/API

تنظیمات

امنیت

وضعیت فعلی

وابستگی‌ها

۱۳. وضعیت مستندات و موارد نیازمند بررسی

۱۳.۱. نسخه ثبت‌شده

این سند نسخه مرجع را 5.4.0 ثبت می‌کند.

با توجه به اینکه در Tree ارسالی نام پوشه پروژه EzLens-Secure-Login-5.1.0 دیده شده، شماره پوشه و شماره نسخه داخلی پروژه الزاماً یکسان فرض نمی‌شوند و باید در کد واقعی بررسی شوند.

۱۳.۲. ساختار API

در Tree ارسالی فایل‌های زیر وجود دارند:

api/
├── class-api-v1.php
├── class-api-v2.php
└── class-webhooks.php

بنابراین در این سند class-api-v1.php به‌عنوان API نسخه ۱ ثبت شده است.

۱۳.۳. آیکون‌ها

در پروژه دو مجموعه اصلی مشاهده شده است:

assets/icons/
assets/icons/modern/

مجموعه modern باید اولویت طراحی رابط‌های جدید باشد.

همچنین تعدادی فایل Duplicate یا نام‌گذاری‌شده با پسوندهایی مانند:

-svgrepo-com.svg
(1).svg

در مجموعه عمومی دیده می‌شود. در توسعه‌های جدید نباید بدون بررسی یکپارچگی بصری، از این فایل‌ها استفاده شود. در صورت نیاز، آیکون مناسب از مجموعه مدرن انتخاب یا نسخه استاندارد جدید ایجاد شود.

۱۳.۴. فایل‌های Legacy

پوشه templates/ و بخشی از includes/ در ساختار فعلی نقش Legacy/Compatibility دارند.

قبل از حذف یا انتقال هر فایل باید:

وابستگی‌ها بررسی شود.

Search در کل پروژه انجام شود.

Hookها و Includeها بررسی شوند.

Shortcodeها و مسیرهای قدیمی تست شوند.

در صورت نیاز Migration انجام شود.

۱۳.۵. مواردی که این سند به‌تنهایی تأیید نمی‌کند

این سند ساختار و قابلیت‌های ثبت‌شده را توصیف می‌کند، اما بدون بررسی تک‌تک فایل‌های Source Code نمی‌توان موارد زیر را به‌صورت قطعی تأیید کرد:

تمام متدهای موجود در هر کلاس

تمام Hookهای ثبت‌شده

تمام Endpointهای API

تمام Actionهای AJAX

Schema کامل همه جدول‌های فهرست‌شده

تمام تنظیمات موجود

تمام Capabilityهای استفاده‌شده

پوشش کامل تست‌ها

وضعیت Production هر قابلیت

بنابراین در توسعه واقعی، این موارد باید از Source Code استخراج و در نسخه بعدی مستندات تکمیل شوند.

۱۳.۶. چک‌لیست قبل از Merge

قبل از پذیرش هر تغییر مهم:

[ ] معماری ماژولار رعایت شده است.
[ ] مسئولیت کلاس‌ها تفکیک شده است.
[ ] Nonce بررسی شده است.
[ ] Capability بررسی شده است.
[ ] ورودی‌ها Sanitized و Validated شده‌اند.
[ ] خروجی‌ها Escaped شده‌اند.
[ ] SQL پارامتریک است.
[ ] Upload امن است.
[ ] قیمت سمت سرور قابل اعتماد است.
[ ] WooCommerce Flow بررسی شده است.
[ ] RTL و Responsive بررسی شده است.
[ ] Asset غیرضروری Load نمی‌شود.
[ ] Emoji یا آیکون متنی استفاده نشده است.
[ ] آیکون از مجموعه مدرن انتخاب شده یا نیاز به آیکون جدید اعلام شده است.
[ ] Migration دیتابیس در صورت نیاز اضافه شده است.
[ ] Backward Compatibility بررسی شده است.
[ ] مستندات قابلیت به‌روزرسانی شده است.

پیوست A — مرجع سریع پروژه

مسیرهای اصلی

مسیر

مسئولیت

ezlens-secure-login.php

Bootstrap و ورودی اصلی

bootstrap/

Autoload و Container

core/

هسته و Lifecycle

modules/

قابلیت‌های اصلی

admin/

رابط مدیریت

frontend/

رابط کاربری سایت

includes/

AJAX و Shortcodeهای موجود/در حال انتقال

api/

API و Webhook

shared/

کد مشترک

templates/

قالب‌های قدیمی/سازگاری

assets/

منابع عمومی، فونت و آیکون

ماژول‌های اصلی

auth
customer
campaign
messaging
support
analytics
product-options

هسته Product Options

TemplateRepository
        ↓
TemplateService
        ↓
FieldSchemaValidator
ConditionEvaluator
PresetLibrary
        ↓
AJAX / WooCommerce Integration
        ↓
Field Renderer / Pricing Engine / Order Display

اصل طراحی نهایی

EzLens Secure Login نباید صرفاً مجموعه‌ای از صفحات و قابلیت‌های جدا از هم باشد. هدف معماری، ایجاد یک هسته منظم و قابل توسعه برای احراز هویت، مدیریت مشتری، ارتباط با مشتری، عملیات فروشگاه و قابلیت‌های تخصصی WooCommerce است.

هر قابلیت جدید باید در جای درست خود قرار گیرد، با معماری فعلی سازگار باشد، امنیت آن از ابتدا در نظر گرفته شود و رابط کاربری آن از زبان بصری یکپارچه EzLens پیروی کند.