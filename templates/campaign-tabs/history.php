<?php
/**
 * تب تاریخچه کمپین‌ها با صفحه‌بندی و آمار باز شدن
 * @version 2.6.0
 */
?>
<div class="campaign-tab-content">
    <h2>📜 تاریخچه کمپین‌ها</h2>
    <p style="color:#64748b;font-size:13px;margin:-6px 0 16px;">لیست کمپین‌های ایجادشده و آمار باز شدن</p>

    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px;align-items:center;padding:10px 14px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
        <button id="history-refresh" class="button button-secondary">🔄 بروزرسانی</button>
        <select id="history-status-filter" style="padding:6px 12px;border:1px solid #e2e8f0;border-radius:6px;">
            <option value="all">همه</option>
            <option value="sent">ارسال شده</option>
            <option value="draft">پیش‌نویس</option>
            <option value="scheduled">برنامه‌ریزی شده</option>
            <option value="failed">ناموفق</option>
        </select>
        <input type="text" id="history-search" placeholder="جستجوی نام..." style="padding:6px 12px;border:1px solid #e2e8f0;border-radius:6px;min-width:150px;">
        <button id="history-search-btn" class="button button-primary">🔍 جستجو</button>
        <span id="history-total-count" style="font-size:12px;color:#64748b;margin-right:auto;">تعداد: ۰</span>
    </div>

    <div id="history-list-container">
        <p style="color:#94a3b8;">⏳ در حال بارگذاری...</p>
    </div>
</div>

<div id="campaign-editor-modal" class="ezlens-campaign-modal" style="display:none;">
  <div class="ezlens-campaign-modal-backdrop"></div>
  <div class="ezlens-campaign-modal-card" role="dialog" aria-modal="true" aria-labelledby="campaign-editor-title">
    <div class="ezlens-campaign-modal-head"><strong id="campaign-editor-title">✏️ ویرایش کمپین</strong><button type="button" class="button-link" id="campaign-editor-close">✕</button></div>
    <div class="ezlens-campaign-modal-body">
      <input type="hidden" id="editor-campaign-id">
      <div class="ezlens-editor-grid">
        <label>نام کمپین *<input id="editor-name" type="text"></label>
        <label>نوع ارسال<select id="editor-type"><option value="email">📧 ایمیل</option><option value="sms">📱 پیامک</option></select></label>
      </div>
      <label>موضوع ایمیل<input id="editor-subject" type="text"></label>
      <label>متن پیام *<textarea id="editor-message" rows="8"></textarea></label>
      <label>فایل ضمیمه<input id="editor-file" type="url"></label>
      <label>زمان ارسال (اختیاری)<input id="editor-scheduled" type="datetime-local"></label>
      <div class="ezlens-editor-groups"><strong>🎯 گروه‌های مخاطبان</strong><div id="editor-groups-list">در حال بارگذاری...</div></div>
      <div id="campaign-editor-status"></div>
    </div>
    <div class="ezlens-campaign-modal-foot"><button class="button" id="campaign-editor-cancel">انصراف</button><button class="button button-primary" id="campaign-editor-save">💾 ذخیره تغییرات</button></div>
  </div>
</div>
<div id="campaign-report-modal" class="ezlens-campaign-modal" style="display:none;"><div class="ezlens-campaign-modal-backdrop"></div><div class="ezlens-campaign-modal-card ezlens-report-card"><div class="ezlens-campaign-modal-head"><strong>📊 گزارش کمپین</strong><button type="button" class="button-link report-close">✕</button></div><div class="ezlens-campaign-modal-body" id="campaign-report-body">در حال بارگذاری...</div></div></div>
