<?php
/**
 * تب مخاطبان - طراحی سه ستون با مدیریت گروه‌ها و دسته‌بندی مخاطبان دستی (نسخه کامل)
 * @version 2.6.1
 */
?>
<div class="campaign-audience-wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:16px;">
        <div>
            <h2 style="margin:0;font-size:20px;">👥 مدیریت مخاطبان و دسته‌ها</h2>
            <p style="color:#64748b;font-size:13px;margin:2px 0 0;">انتخاب مخاطبان از کاربران سایت یا مخاطبان دستی و ایجاد دسته‌های سفارشی</p>
        </div>
        <div style="display:flex;gap:8px;">
            <button id="audience-help-btn" class="button button-secondary" style="font-size:12px;">❓ راهنما</button>
            <button id="audience-refresh-all" class="button button-primary" style="font-size:12px;">🔄 بروزرسانی همه</button>
        </div>
    </div>

    <!-- ===== راهنمای سریع ===== -->
    <div id="audience-help-box" style="display:none;padding:12px 16px;background:#ebf8ff;border-radius:8px;border:1px solid #bee3f8;margin-bottom:16px;font-size:13px;">
        <strong>📘 راهنمای سریع:</strong>
        <ol style="margin:4px 0 0 20px;color:#2a69ac;">
            <li>از ستون <strong>کاربران سایت</strong> یا <strong>مخاطبان دستی</strong> مخاطبان مورد نظر را انتخاب کنید.</li>
            <li>با دکمه <strong>افزودن انتخاب‌شده</strong> یا <strong>افزودن همه</strong> آن‌ها را به باکس پایین اضافه کنید.</li>
            <li>در باکس پایین، لیست مخاطبان انتخاب‌شده را مشاهده و در صورت نیاز ویرایش کنید.</li>
            <li>با دکمه <strong>ذخیره به عنوان دسته جدید</strong> دسته‌ی دلخواه خود را بسازید.</li>
            <li>دسته‌های ایجادشده در بخش <strong>دسته‌های ذخیره‌شده</strong> قابل مشاهده، ویرایش و حذف هستند.</li>
        </ol>
        <button id="audience-help-close" style="margin-top:6px;background:none;border:none;color:#2b6cb0;cursor:pointer;font-weight:600;">✕ بستن راهنما</button>
    </div>

    <!-- ===== ستون‌های بالا (دو ستون) ===== -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
        
        <!-- ===== ستون ۱: کاربران سایت ===== -->
        <div class="audience-column" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <h3 style="margin:0;font-size:15px;color:#0f172a;">👥 کاربران سایت</h3>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span id="users-count-badge" style="background:#2b6cb0;color:#fff;padding:2px 12px;border-radius:999px;font-size:12px;font-weight:700;">۰</span>
                    <span style="font-size:11px;color:#94a3b8;">کل کاربران</span>
                </div>
            </div>
            
            <!-- فیلترها -->
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px;padding:8px;background:#f8fafc;border-radius:6px;border:1px solid #e2e8f0;">
                <select id="audience-user-role" style="padding:4px 8px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;flex:1;min-width:80px;">
                    <option value="">نقش</option>
                    <option value="administrator">مدیر</option>
                    <option value="customer">مشتری</option>
                    <option value="subscriber">عضو</option>
                </select>
                <input type="date" id="audience-user-date" style="padding:4px 8px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;flex:1;min-width:100px;" placeholder="تاریخ ثبت‌نام">
                <input type="number" id="audience-user-spent" placeholder="حداقل خرید" style="padding:4px 8px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;flex:1;min-width:80px;">
                <button id="audience-user-filter-btn" class="button button-small button-primary" style="padding:2px 12px;font-size:12px;" title="اعمال فیلتر">🔍</button>
                <button id="audience-user-reset-btn" class="button button-small" style="padding:2px 12px;font-size:12px;" title="بازنشانی فیلترها">↺</button>
            </div>
            
            <!-- جستجو -->
            <div style="margin-bottom:8px;">
                <input type="text" id="audience-user-search" placeholder="جستجو در کاربران (نام، ایمیل، موبایل)..." style="width:100%;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;">
            </div>
            
            <!-- لیست کاربران -->
            <div id="audience-user-list" style="max-height:300px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;background:#fafafa;">
                <p style="padding:20px;text-align:center;color:#94a3b8;margin:0;">⏳ در حال بارگذاری...</p>
            </div>
            
            <!-- صفحه‌بندی کاربران -->
            <div id="audience-user-pagination" style="display:flex;justify-content:center;gap:4px;margin-top:8px;flex-wrap:wrap;"></div>
            
            <!-- دکمه‌های انتقال -->
            <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
                <button id="audience-user-select-all" class="button button-small button-secondary" style="font-size:12px;">✅ انتخاب همه</button>
                <button id="audience-user-add-selected" class="button button-small button-primary" style="font-size:12px;background:#2b6cb0;">⬇️ افزودن انتخاب‌شده</button>
                <button id="audience-user-add-all" class="button button-small button-primary" style="font-size:12px;background:#16a34a;border-color:#16a34a;">⬇️ افزودن همه</button>
            </div>
        </div>

        <!-- ===== ستون ۲: مخاطبان دستی با دسته‌بندی ===== -->
        <div class="audience-column" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <h3 style="margin:0;font-size:15px;color:#0f172a;">📋 مخاطبان دستی</h3>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span id="contacts-count-badge" style="background:#805ad5;color:#fff;padding:2px 12px;border-radius:999px;font-size:12px;font-weight:700;">۰</span>
                    <span style="font-size:11px;color:#94a3b8;">کل مخاطبان</span>
                </div>
            </div>
            
            <!-- فیلتر دسته‌بندی -->
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
                <select id="audience-contact-category" style="flex:1;min-width:100px;padding:4px 8px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;">
                    <option value="">همه دسته‌ها</option>
                    <?php
                    $campaign = EzLens_Auth_Campaign::get_instance();
                    $categories = $campaign->get_contact_categories();
                    foreach ($categories as $cat) {
                        echo '<option value="' . esc_attr($cat) . '">' . esc_html($cat) . '</option>';
                    }
                    ?>
                </select>
                <button id="audience-contact-category-filter" class="button button-small button-secondary" style="font-size:12px;">فیلتر</button>
            </div>

            <!-- فرم اضافه کردن مخاطب دستی با دسته‌بندی (AJAX) -->
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;padding:8px;background:#faf5ff;border-radius:6px;border:1px solid #e9d5ff;">
                <span style="font-size:11px;color:#6b21a5;width:100%;margin-bottom:2px;">➕ افزودن مخاطب جدید (بدون رفرش)</span>
                <input type="text" id="audience-contact-name" placeholder="نام" style="flex:1;min-width:80px;padding:4px 8px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;">
                <input type="email" id="audience-contact-email" placeholder="ایمیل (اختیاری برای SMS)" style="flex:1.5;min-width:120px;padding:4px 8px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;">
                <input type="text" id="audience-contact-phone" placeholder="موبایل" style="flex:1;min-width:80px;padding:4px 8px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;">
                <input type="text" id="audience-contact-category-input" placeholder="دسته (مثلاً: ویژه)" style="flex:1;min-width:80px;padding:4px 8px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;">
                <button id="audience-contact-add-btn" class="button button-primary" style="padding:2px 12px;font-size:12px;background:#805ad5;border-color:#805ad5;">➕ افزودن</button>
            </div>
            <div id="audience-add-status" style="font-size:12px;margin-bottom:6px;height:20px;"></div>
            
            <!-- جستجو -->
            <div style="margin-bottom:8px;">
                <input type="text" id="audience-contact-search" placeholder="جستجو در مخاطبان (نام، ایمیل، موبایل)..." style="width:100%;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;">
            </div>
            
            <!-- لیست مخاطبان دستی -->
            <div id="audience-contact-list" style="max-height:300px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;background:#fafafa;">
                <p style="padding:20px;text-align:center;color:#94a3b8;margin:0;">⏳ در حال بارگذاری...</p>
            </div>
            
            <!-- صفحه‌بندی مخاطبان -->
            <div id="audience-contact-pagination" style="display:flex;justify-content:center;gap:4px;margin-top:8px;flex-wrap:wrap;"></div>
            
            <!-- دکمه‌های انتقال -->
            <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
                <button id="audience-contact-select-all" class="button button-small button-secondary" style="font-size:12px;">✅ انتخاب همه</button>
                <button id="audience-contact-add-selected" class="button button-small button-primary" style="font-size:12px;background:#805ad5;">⬇️ افزودن انتخاب‌شده</button>
                <button id="audience-contact-add-all" class="button button-small button-primary" style="font-size:12px;background:#6b21a5;border-color:#6b21a5;">⬇️ افزودن همه</button>
            </div>
        </div>
    </div>

    <!-- ===== باکس پایین: مخاطبان انتخاب‌شده ===== -->
    <div style="background:linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);border:2px solid #16a34a;border-radius:12px;padding:16px;margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <h3 style="margin:0;font-size:15px;color:#0f172a;">📦 مخاطبان انتخاب‌شده</h3>
                <span id="selected-count-badge" style="background:#16a34a;color:#fff;padding:2px 14px;border-radius:999px;font-size:14px;font-weight:700;">۰</span>
                <span style="font-size:12px;color:#64748b;">نفر</span>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button id="selected-view-list" class="button button-small button-secondary" style="font-size:12px;">👁️ مشاهده لیست</button>
                <button id="selected-clear-all" class="button button-small" style="font-size:12px;color:#dc2626;border-color:#dc2626;">🗑️ پاک کردن همه</button>
                <button id="selected-save-group" class="button button-small button-primary" style="font-size:12px;background:#16a34a;border-color:#16a34a;">💾 ذخیره به عنوان دسته جدید</button>
            </div>
        </div>
        <div id="selected-list-preview" style="margin-top:8px;display:flex;flex-wrap:wrap;gap:4px;max-height:60px;overflow-y:auto;padding:4px 0;">
            <span style="color:#94a3b8;font-size:13px;">هنوز مخاطبی انتخاب نشده است.</span>
        </div>
        <div id="selected-save-status" style="font-size:12px;margin-top:4px;height:20px;"></div>
    </div>

    <!-- ===== ستون چهارم: دسته‌های ذخیره‌شده ===== -->
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
            <div>
                <h3 style="margin:0;font-size:15px;color:#0f172a;">🗂️ دسته‌های ذخیره‌شده</h3>
                <p style="margin:2px 0 0;font-size:12px;color:#64748b;">دسته‌هایی که قبلاً ایجاد کرده‌اید. برای استفاده در کمپین، آن‌ها را انتخاب کنید.</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button id="groups-refresh-btn" class="button button-small button-secondary" style="font-size:12px;">🔄 بروزرسانی</button>
                <button id="groups-create-btn" class="button button-small button-primary" style="font-size:12px;">➕ دسته جدید (خالی)</button>
            </div>
        </div>
        <div id="groups-list-container" style="max-height:250px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;background:#fafafa;">
            <p style="padding:20px;text-align:center;color:#94a3b8;margin:0;">⏳ در حال بارگذاری...</p>
        </div>
    </div>
</div>

<!-- استایل‌های اختصاصی تب مخاطبان -->
<style>
.campaign-audience-wrap .audience-column .user-item,
.campaign-audience-wrap .audience-column .contact-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 10px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background 0.15s;
    font-size: 13px;
}
.campaign-audience-wrap .audience-column .user-item:hover,
.campaign-audience-wrap .audience-column .contact-item:hover {
    background: #f1f5f9;
}
.campaign-audience-wrap .audience-column .user-item.selected,
.campaign-audience-wrap .audience-column .contact-item.selected {
    background: #dbeafe;
}
.campaign-audience-wrap .audience-column .user-item input[type="checkbox"],
.campaign-audience-wrap .audience-column .contact-item input[type="checkbox"] {
    margin: 0;
    flex-shrink: 0;
}
.campaign-audience-wrap .audience-column .user-item .item-info,
.campaign-audience-wrap .audience-column .contact-item .item-info {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.campaign-audience-wrap .pagination-wrap {
    display: flex;
    justify-content: center;
    gap: 4px;
    flex-wrap: wrap;
}
.campaign-audience-wrap .pagination-wrap .page-btn {
    padding: 2px 8px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    background: #fff;
    color: #0f172a;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
}
.campaign-audience-wrap .pagination-wrap .page-btn.active {
    background: #2b6cb0;
    color: #fff;
    border-color: #2b6cb0;
}
.campaign-audience-wrap .pagination-wrap .page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.campaign-audience-wrap .group-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 12px;
    border-bottom: 1px solid #f1f5f9;
}
.campaign-audience-wrap .group-item:hover {
    background: #f8fafc;
}
.campaign-audience-wrap .group-item .group-info {
    flex: 1;
    overflow: hidden;
}
.campaign-audience-wrap .group-item .group-actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}
</style>
