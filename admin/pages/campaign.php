<?php
/**
 * قالب صفحه اصلی کمپینگ (نسخه کامل با تب‌های جدید)
 * @version 2.5.0
 */
?>
<div class="wrap ezlens-campaign-wrap">
    <h1 class="wp-heading-inline">📢 کمپینگ تبلیغاتی</h1>
    <p class="description">مدیریت ارسال ایمیل و پیامک گروهی به کاربران و مخاطبان سفارشی</p>

    <div class="ezlens-campaign-tabs">
        <button class="campaign-tab active" data-tab="dashboard">📊 داشبورد</button>
        <button class="campaign-tab" data-tab="audience">👥 مخاطبان</button>
        <button class="campaign-tab" data-tab="create">✏️ ایجاد کمپین</button>
        <button class="campaign-tab" data-tab="history">📜 تاریخچه</button>
        <button class="campaign-tab" data-tab="settings">⚙️ تنظیمات</button>
    </div>

    <div class="ezlens-campaign-content" id="campaignContent">
        <div style="text-align:center;padding:40px;color:#94a3b8;">⏳ در حال بارگذاری...</div>
    </div>
</div>

<style>
.ezlens-campaign-wrap .ezlens-campaign-tabs {
    display: flex;
    gap: 4px;
    padding: 4px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin: 16px 0 20px;
    flex-wrap: wrap;
}
.ezlens-campaign-wrap .campaign-tab {
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.ezlens-campaign-wrap .campaign-tab:hover {
    background: rgba(43,108,176,0.06);
    color: #0f172a;
}
.ezlens-campaign-wrap .campaign-tab.active {
    background: #2b6cb0;
    color: #fff;
    box-shadow: 0 2px 12px rgba(43,108,176,0.15);
}
.ezlens-campaign-wrap .campaign-tab-content {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 24px;
    min-height: 300px;
}
.ezlens-campaign-wrap .campaign-status {
    display: inline-block;
    padding: 2px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}
.ezlens-campaign-wrap .status-sent { background: #d4edda; color: #155724; }
.ezlens-campaign-wrap .status-draft { background: #f1f5f9; color: #475569; }
.ezlens-campaign-wrap .status-scheduled { background: #ebf8ff; color: #2a69ac; }
.ezlens-campaign-wrap .status-failed { background: #f8d7da; color: #721c24; }
.ezlens-campaign-wrap .pagination-wrap {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-top: 16px;
    flex-wrap: wrap;
}
.ezlens-campaign-wrap .pagination-wrap .page-btn {
    padding: 4px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #fff;
    color: #0f172a;
    cursor: pointer;
    font-size: 13px;
}
.ezlens-campaign-wrap .pagination-wrap .page-btn.active {
    background: #2b6cb0;
    color: #fff;
    border-color: #2b6cb0;
}
.ezlens-campaign-wrap .pagination-wrap .page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

