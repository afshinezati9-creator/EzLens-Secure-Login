<?php
/**
 * تب ایجاد کمپین جدید (نسخه کامل با جزئیات بیشتر)
 * @version 2.6.0
 */
?>
<div class="campaign-tab-content">
    <h2>✏️ ایجاد کمپین جدید</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">انتخاب گروه مخاطبان و ارسال پیام</p>

    <!-- راهنمای سریع -->
    <div style="padding:10px 14px;background:#ebf8ff;border-radius:8px;border:1px solid #bee3f8;margin-bottom:16px;font-size:13px;color:#2a69ac;">
        <strong>📘 راهنما:</strong>
        <ul style="margin:4px 0 0 20px;">
            <li>ابتدا در بخش <strong>مخاطبان</strong> گروه‌های مورد نظر خود را ایجاد کنید.</li>
            <li>در این صفحه، یک یا چند گروه را انتخاب کنید.</li>
            <li>متن پیام را با استفاده از متغیرهای <code>{name}</code>، <code>{email}</code>، <code>{phone}</code> و <code>{site_name}</code> شخصی‌سازی کنید.</li>
            <li>برای ارسال فایل ضمیمه، لینک فایل را وارد کنید.</li>
            <li>با دکمه <strong>ایجاد کمپین</strong> فقط ذخیره می‌شود و با <strong>ایجاد و ارسال</strong> بلافاصله ارسال می‌شود.</li>
        </ul>
    </div>

    <form id="campaign-create-form">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">نام کمپین *</label>
                <input type="text" id="campaign-name" placeholder="مثلاً: تخفیف ویژه نوروز" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;">
                <span style="font-size:11px;color:#94a3b8;">یک نام منحصر‌به‌فرد برای شناسایی کمپین</span>
            </div>
            <div>
                <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">نوع ارسال *</label>
                <select id="campaign-type" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;">
                    <option value="email">📧 ایمیل</option>
                    <option value="sms">📱 پیامک</option>
                </select>
                <span style="font-size:11px;color:#94a3b8;">برای پیامک، شماره موبایل مخاطبان باید ثبت شده باشد.</span>
            </div>
        </div>

        <div style="margin-top:10px;">
            <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">موضوع (فقط ایمیل) *</label>
            <input type="text" id="campaign-subject" placeholder="موضوع ایمیل" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;">
            <span style="font-size:11px;color:#94a3b8;">موضوعی که کاربر در ایمیل مشاهده می‌کند.</span>
        </div>

        <div style="margin-top:10px;">
            <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">متن پیام *</label>
            <textarea id="campaign-message" rows="6" placeholder="متن پیام را وارد کنید. از متغیرهای {name}, {phone}, {email}, {site_name} استفاده کنید." style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-family:inherit;"></textarea>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px;">
                <span style="font-size:12px;color:#64748b;">💡 متغیرهای قابل استفاده:</span>
                <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">{name}</code>
                <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">{email}</code>
                <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">{phone}</code>
                <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">{site_name}</code>
                <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">{first_name}</code>
                <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">{order_count}</code>
                <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">{unsubscribe_url}</code>
                <button type="button" id="campaign-insert-var" class="button button-small" style="font-size:12px;padding:2px 8px;">+ درج متغیر</button>
            </div>
        </div>

        <div style="margin-top:10px;">
            <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">زمان ارسال (اختیاری)</label>
            <input type="datetime-local" id="campaign-scheduled-at" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;">
            <span style="font-size:11px;color:#94a3b8;">اگر خالی باشد کمپین ذخیره می‌شود؛ زمان آینده باعث اجرای خودکار توسط Cron می‌شود.</span>
        </div>

        <div style="margin-top:10px;">
            <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">لینک فایل ضمیمه (اختیاری)</label>
            <input type="url" id="campaign-file-url" placeholder="https://example.com/file.pdf" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;">
            <span style="font-size:12px;color:#94a3b8;">لینک فایل (PDF، تصویر، و...) که در پیام ارسال می‌شود.</span>
        </div>

        <!-- انتخاب گروه‌های مخاطبان -->
        <div style="margin-top:16px;padding:12px 16px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
            <h4 style="margin:0 0 8px;">🎯 انتخاب گروه‌های مخاطبان</h4>
            <p style="font-size:12px;color:#64748b;margin:0 0 10px;">یک یا چند گروه مخاطب را انتخاب کنید. گیرندگان پیام از ترکیب این گروه‌ها محاسبه می‌شوند.</p>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
                <button type="button" id="campaign-refresh-groups" class="button button-small button-secondary">🔄 بارگذاری مجدد</button>
                <button type="button" id="campaign-select-all-groups" class="button button-small button-secondary">✅ انتخاب همه</button>
                <button type="button" id="campaign-deselect-all-groups" class="button button-small button-secondary">❌ لغو همه</button>
            </div>
            <div id="campaign-groups-list" style="max-height:200px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;background:#fff;padding:4px;">
                <p style="color:#94a3b8;padding:10px;">در حال بارگذاری گروه‌ها...</p>
            </div>
            <div style="margin-top:6px;font-size:12px;color:#64748b;">
                <span id="campaign-selected-groups-count">۰</span> گروه انتخاب شده
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap;">
            <button type="button" id="campaign-create-btn" class="button button-primary" style="padding:10px 24px;font-weight:600;">✅ ایجاد کمپین (ذخیره)</button>
            <button type="button" id="campaign-create-and-send" class="button button-primary" style="padding:10px 24px;font-weight:600;background:#16a34a;border-color:#16a34a;">✅ ایجاد و ارسال</button>
            <button type="button" id="campaign-reset-form" class="button button-secondary" style="padding:10px 24px;font-weight:600;">🗑️ پاک کردن فرم</button>
        </div>
        <div id="campaign-create-status" style="margin-top:8px;font-size:13px;height:24px;"></div>
    </form>
</div>
