<div>
    <h3>ایجاد تیکت جدید برای کاربر</h3>
    <form id="support-create-ticket-form" enctype="multipart/form-data">
        <div style="margin-bottom:12px;">
            <label style="display:block;font-weight:600;margin-bottom:4px;">انتخاب کاربر از لیست</label>
            <select id="support-user-select" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;">
                <option value="">— انتخاب کاربر —</option>
                <?php
                $users = get_users([
                    'number' => 50,
                    'orderby' => 'registered',
                    'order' => 'DESC',
                    'fields' => ['ID', 'display_name', 'user_login', 'user_email']
                ]);
                foreach ($users as $user) {
                    $name = $user->display_name ?: $user->user_login;
                    echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($name) . ' (' . esc_html($user->user_email) . ')</option>';
                }
                ?>
            </select>
            <span style="font-size:12px;color:#94a3b8;">یا در صورت نیاز از جستجو استفاده کنید.</span>
        </div>
        <div style="margin-bottom:12px;">
            <label style="display:block;font-weight:600;margin-bottom:4px;">جستجوی کاربر</label>
            <input type="text" id="support-user-search" placeholder="نام کاربری، ایمیل یا نام نمایشی..." style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;">
            <div id="support-user-results" style="margin-top:4px;"></div>
        </div>
        <div style="margin-bottom:12px;">
            <label style="display:block;font-weight:600;margin-bottom:4px;">موضوع (اختیاری)</label>
            <input type="text" id="support-subject" placeholder="موضوع..." style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;">
        </div>
        <div style="margin-bottom:12px;">
            <label style="display:block;font-weight:600;margin-bottom:4px;">متن پیام</label>
            <textarea id="support-message" rows="4" placeholder="متن پیام..." style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-family:inherit;"></textarea>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
            <div class="file-wrap" style="position:relative;display:inline-block;">
                <span class="button button-secondary">📎 ضمیمه</span>
                <input type="file" name="attachment" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;">
            </div>
            <button type="submit" class="button button-primary">✅ ایجاد تیکت</button>
        </div>
        <div id="create-ticket-status" style="margin-top:8px;font-size:13px;"></div>
    </form>
</div>