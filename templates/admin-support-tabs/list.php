<div id="tickets-list-container">
    <?php $support_phone = '02144385667'; ?>
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px;align-items:center;">
        <select id="support-filter-status" style="padding:6px 12px;border:1px solid #e2e8f0;border-radius:6px;">
            <option value="all">همه</option>
            <option value="open">باز</option>
            <option value="replied">پاسخ داده شده</option>
            <option value="closed">بسته</option>
        </select>
        <input type="text" id="support-filter-search" placeholder="جستجوی کاربر..." style="padding:6px 12px;border:1px solid #e2e8f0;border-radius:6px;min-width:150px;">
        <button id="support-filter-search-btn" class="button button-primary">جستجو</button>
        <button id="support-filter-reset" class="button button-secondary">بازنشانی</button>
        <span style="font-size:12px;color:#94a3b8;margin-right:auto;">📞 پشتیبانی: <?php echo $support_phone; ?></span>
    </div>
    <div id="tickets-list"><p style="color:#94a3b8;">در حال بارگذاری...</p></div>
</div>