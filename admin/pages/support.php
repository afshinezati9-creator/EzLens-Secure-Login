<?php
/**
 * قالب مدیریت پشتیبانی (ادمین)
 * شامل لیست تیکت‌ها، ایجاد تیکت جدید، و مکالمه
 */
if (!defined('ABSPATH')) exit;

$support_phone = '02144385667'; // یا از تنظیمات بخوانید
?>
<div class="wrap ezlens-support">
    <h1>💬 پشتیبانی (تیکت‌ها)</h1>
    <p class="description">مدیریت پیام‌ها و پاسخ به کاربران</p>
    
    <div class="ezlens-support-tabs">
        <button class="support-tab active" data-tab="list">📋 لیست تیکت‌ها</button>
        <button class="support-tab" data-tab="new-ticket">✏️ تیکت جدید</button>
        <button class="support-tab" data-tab="conversation" id="conversation-tab" style="display:none;">💬 مکالمه</button>
    </div>
    
    <div class="ezlens-support-content" id="supportContent">
        <div style="text-align:center;padding:40px;color:#94a3b8;">⏳ در حال بارگذاری...</div>
    </div>
    
    <div style="margin-top:16px;padding:12px 16px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
        📞 شماره تماس پشتیبانی: <strong><?php echo $support_phone; ?></strong>
    </div>
</div>

<style>
.ezlens-support-tabs {
    display: flex; gap: 4px; padding: 4px; background: #f8fafc; border-radius: 12px;
    border: 1px solid #e2e8f0; margin: 16px 0 20px; flex-wrap: wrap;
}
.support-tab {
    padding: 8px 16px; border: none; border-radius: 8px; background: transparent;
    color: #64748b; font-weight: 600; font-size: 13px; cursor: pointer;
    transition: all 0.2s; font-family: inherit;
}
.support-tab:hover { background: rgba(43,108,176,0.06); color: #0f172a; }
.support-tab.active { background: #2b6cb0; color: #fff; box-shadow: 0 2px 12px rgba(43,108,176,0.15); }
.support-tab-content { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; min-height: 300px; }
.ticket-status { display: inline-block; padding: 2px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; }
.status-open { background: #fef3c7; color: #92400e; }
.status-replied { background: #dbeafe; color: #1e40af; }
.status-closed { background: #e2e8f0; color: #475569; }
.message-user { background: #f1f5f9; border-radius: 12px 12px 12px 4px; padding: 10px 14px; margin-bottom: 6px; max-width: 80%; }
.message-admin { background: #2b6cb0; color: #fff; border-radius: 12px 12px 4px 12px; padding: 10px 14px; margin-bottom: 6px; max-width: 80%; margin-right: auto; }
.message-image { max-width: 200px; border-radius: 8px; margin-top: 4px; border: 1px solid #e2e8f0; }
.message-time { font-size: 10px; color: #94a3b8; display: block; margin-top: 2px; }
.message-admin .message-time { color: rgba(255,255,255,0.7); }
.pagination-wrap { display: flex; justify-content: center; gap: 6px; margin-top: 16px; flex-wrap: wrap; }
.pagination-wrap .page-btn { padding: 4px 12px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; color: #0f172a; cursor: pointer; font-size: 13px; }
.pagination-wrap .page-btn.active { background: #2b6cb0; color: #fff; border-color: #2b6cb0; }
.pagination-wrap .page-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* استایل‌های فرم تیکت جدید */
#support-create-ticket-form input[type="text"], 
#support-create-ticket-form textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-family: inherit;
    margin-bottom: 12px;
}
#support-create-ticket-form textarea { min-height: 100px; resize: vertical; }
#support-user-search { margin-bottom: 8px; }
.user-item:hover { background: #f1f5f9; }
#support-user-results { margin-bottom: 12px; }
</style>

<script>
jQuery(document).ready(function($) {
    var tabs = $('.support-tab');
    var content = $('#supportContent');
    var currentPage = 1, limit = 20, totalPages = 1;

    function loadTab(tabId, data) {
        tabs.removeClass('active');
        tabs.filter('[data-tab="'+tabId+'"]').addClass('active');
        content.html('<div style="text-align:center;padding:40px;color:#94a3b8;">⏳ در حال بارگذاری...</div>');
        var postData = { action: 'ezlens_support_load_tab', nonce: ezlens_auth_ajax.nonce, tab: tabId };
        if (data) $.extend(postData, data);
        $.post(ezlens_auth_ajax.ajax_url, postData, function(r) {
            if (r.success) { content.html(r.data.html); initTab(tabId); }
            else content.html('<div style="text-align:center;padding:40px;color:#dc2626;">❌ خطا</div>');
        }).fail(function(){ content.html('<div style="text-align:center;padding:40px;color:#dc2626;">❌ خطا در ارتباط</div>'); });
    }

    function initTab(tabId) {
        if (tabId === 'list') initList();
        else if (tabId === 'conversation') initConversation();
        else if (tabId === 'new-ticket') initNewTicketTab(); // فراخوانی تابع جدید
    }

    function initList() {
        loadTickets();
        $('#support-filter-status, #support-filter-search-btn').on('change click', function(){ currentPage=1; loadTickets(); });
        $('#support-filter-reset').on('click', function(){ $('#support-filter-status').val('all'); $('#support-filter-search').val(''); currentPage=1; loadTickets(); });
    }

    function loadTickets() {
        var container = $('#tickets-list');
        container.html('<p>⏳ در حال بارگذاری...</p>');
        var offset = (currentPage-1)*limit;
        var data = {
            action: 'ezlens_admin_support_get_tickets',
            nonce: ezlens_auth_ajax.nonce,
            status: $('#support-filter-status').val(),
            search: $('#support-filter-search').val(),
            limit: limit, offset: offset
        };
        $.post(ezlens_auth_ajax.ajax_url, data, function(r) {
            if (!r.success) { container.html('<p style="color:#dc2626;">❌ خطا</p>'); return; }
            var tickets = r.data.tickets, total = r.data.total;
            totalPages = Math.ceil(total/limit);
            if (!tickets.length) { container.html('<p style="color:#94a3b8;">هیچ تیکتی یافت نشد.</p>'); return; }
            var html = '<table class="wp-list-table widefat fixed striped"><thead><tr><th>شناسه</th><th>کاربر</th><th>موضوع</th><th>وضعیت</th><th>تاریخ</th><th>عملیات</th></tr></thead><tbody>';
            tickets.forEach(function(t){
                var lbl = {open:'باز',replied:'پاسخ داده شده',closed:'بسته'}[t.status]||t.status;
                html += '<tr><td>#'+t.id+'</td><td><strong>'+escHtml(t.display_name||t.user_login)+'</strong><br><span style="font-size:11px;color:#94a3b8;">'+escHtml(t.user_email)+'</span></td>';
                html += '<td>'+escHtml(t.subject)+'</td><td><span class="ticket-status status-'+t.status+'">'+lbl+'</span></td>';
                html += '<td>'+t.updated_at+'</td><td><button class="button button-small view-ticket" data-id="'+t.id+'">مشاهده</button></td></tr>';
            });
            html += '</tbody></table>';
            html += renderPagination();
            container.html(html);
            container.find('.view-ticket').on('click', function(){ window.loadConversation($(this).data('id')); });
        }).fail(function(){ container.html('<p style="color:#dc2626;">❌ خطا در ارتباط</p>'); });
    }

    function renderPagination() {
        if (totalPages<=1) return '';
        var html = '<div class="pagination-wrap">';
        html += '<button class="page-btn" data-page="'+(currentPage-1)+'" '+(currentPage<=1?'disabled':'')+'>‹ قبلی</button>';
        for (var p=Math.max(1,currentPage-2); p<=Math.min(totalPages,currentPage+2); p++) {
            html += '<button class="page-btn '+(p===currentPage?'active':'')+'" data-page="'+p+'">'+p+'</button>';
        }
        html += '<button class="page-btn" data-page="'+(currentPage+1)+'" '+(currentPage>=totalPages?'disabled':'')+'>بعدی ›</button>';
        html += '<span class="page-info">صفحه '+currentPage+' از '+totalPages+'</span>';
        html += '</div>';
        return html;
    }

    function initConversation() {
        var ticketId = tabs.filter('[data-tab="conversation"]').data('ticket-id') || 0;
        if (!ticketId) { $('#conversation-container').html('<p style="color:#dc2626;">شناسه نامعتبر</p>'); return; }
        $('#conversation-container').html('<p>⏳ در حال بارگذاری...</p>');
        $.post(ezlens_auth_ajax.ajax_url, {
            action: 'ezlens_admin_support_get_messages',
            nonce: ezlens_auth_ajax.nonce,
            ticket_id: ticketId
        }, function(r) {
            if (!r.success) { $('#conversation-container').html('<p style="color:#dc2626;">❌ خطا</p>'); return; }
            var t = r.data.ticket, msgs = r.data.messages;
            var html = '<div style="margin-bottom:16px;padding:12px 16px;background:#f8fafc;border-radius:8px;display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;">';
            html += '<div><strong>کاربر:</strong> '+escHtml(t.display_name||t.user_login)+' ('+escHtml(t.user_email)+')</div>';
            html += '<div><strong>وضعیت:</strong> <span class="ticket-status status-'+t.status+'">'+({open:'باز',replied:'پاسخ داده شده',closed:'بسته'}[t.status]||t.status)+'</span></div>';
            html += '<div><strong>تاریخ:</strong> '+t.created_at+'</div></div>';

            html += '<div style="max-height:400px;overflow-y:auto;margin-bottom:16px;padding:8px;">';
            if (!msgs.length) html += '<p style="color:#94a3b8;">هیچ پیامی نیست.</p>';
            else msgs.forEach(function(m){
                var isAdmin = m.sender_type==='admin';
                var cls = isAdmin?'message-admin':'message-user';
                html += '<div class="'+cls+'">';
                html += '<div><strong>'+(isAdmin?'مدیریت':t.display_name||t.user_login)+'</strong></div>';
                html += '<div>'+nl2br(escHtml(m.message))+'</div>';
                if (m.file_attachment) {
                    var ext = m.file_attachment.split('.').pop().toLowerCase();
                    if (['jpg','jpeg','png','gif','webp'].indexOf(ext)!==-1) {
                        html += '<div><img src="'+escHtml(m.file_attachment)+'" class="message-image" /></div>';
                    } else {
                        html += '<div><a href="'+escHtml(m.file_attachment)+'" target="_blank">📎 دانلود فایل</a></div>';
                    }
                }
                html += '<span class="message-time">'+m.created_at+'</span></div>';
            });
            html += '</div>';

            html += '<form id="support-reply-form" enctype="multipart/form-data">';
            html += '<input type="hidden" name="ticket_id" value="'+ticketId+'">';
            html += '<input type="hidden" name="action" value="ezlens_admin_support_reply">';
            html += '<input type="hidden" name="nonce" value="'+ezlens_auth_ajax.nonce+'">';
            html += '<textarea name="message" rows="3" placeholder="متن پاسخ..." style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-family:inherit;margin-bottom:8px;"></textarea>';
            html += '<div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">';
            html += '<div class="file-wrap" style="position:relative;display:inline-block;"><span class="button button-secondary">📎 ضمیمه</span><input type="file" name="attachment" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;"></div>';
            html += '<select name="status" style="padding:6px 12px;border:1px solid #e2e8f0;border-radius:6px;"><option value="replied">پاسخ داده شده</option><option value="open">باز</option><option value="closed">بسته</option></select>';
            html += '<button type="submit" class="button button-primary">📤 ارسال پاسخ</button></div></form>';
            $('#conversation-container').html(html);

            $('#support-reply-form').on('submit', function(e) {
                e.preventDefault();
                var btn = $(this).find('button[type="submit"]');
                var status = $('#reply-status');
                btn.text('⏳...').prop('disabled', true);
                var fd = new FormData(this);
                $.ajax({
                    url: ezlens_auth_ajax.ajax_url,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(resp) {
                        btn.text('📤 ارسال پاسخ').prop('disabled', false);
                        if (resp.success) { status.text('✅ ارسال شد').css('color','#16a34a'); setTimeout(function(){ window.loadConversation(ticketId); },1000); }
                        else status.text('❌ '+resp.data).css('color','#dc2626');
                    },
                    error: function(){ btn.text('📤 ارسال پاسخ').prop('disabled', false); status.text('❌ خطا').css('color','#dc2626'); }
                });
            });
        });
    }

    // ============================================================
    // تابع اختصاصی: INIT NEW TICKET (از فایل متنی شما)
    // ============================================================
    function initNewTicketTab() {
        var selectedUserId = 0;

        // انتخاب از لیست کشویی
        $('#support-user-select').on('change', function() {
            var id = parseInt($(this).val());
            if (id) {
                selectedUserId = id;
                var name = $(this).find('option:selected').text();
                $('#support-user-search').val(name);
                $('#support-user-results').html('<span style="color:#16a34a;">✅ کاربر انتخاب شد: ' + name + '</span>');
                console.log('selectedUserId (dropdown):', selectedUserId);
            } else {
                selectedUserId = 0;
                $('#support-user-search').val('');
                $('#support-user-results').html('');
            }
        });

        // جستجوی کاربران
        $('#support-user-search').on('keyup', function() {
            var val = $(this).val().trim();
            if (val.length < 2) {
                $('#support-user-results').html('');
                return;
            }
            $.post(ezlens_auth_ajax.ajax_url, {
                action: 'ezlens_admin_support_search_users',
                nonce: ezlens_auth_ajax.nonce,
                search: val
            }, function(r) {
                if (!r.success) {
                    $('#support-user-results').html('<p style="color:#dc2626;">❌ خطا</p>');
                    return;
                }
                var users = r.data.users;
                if (!users.length) {
                    $('#support-user-results').html('<p style="color:#94a3b8;">کاربری یافت نشد.</p>');
                    return;
                }
                var html = '<div style="max-height:150px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;">';
                users.forEach(function(u){
                    html += '<div class="user-item" style="padding:6px 12px;cursor:pointer;border-bottom:1px solid #f1f5f9;" data-id="'+u.ID+'" data-name="'+escHtml(u.display_name||u.user_login)+'">'+escHtml(u.display_name||u.user_login)+' ('+escHtml(u.user_email)+')</div>';
                });
                html += '</div>';
                $('#support-user-results').html(html);
                $('.user-item').on('click', function(){
                    var id = $(this).data('id');
                    var name = $(this).data('name');
                    selectedUserId = id;
                    $('#support-user-search').val(name);
                    $('#support-user-select').val(id);
                    $('#support-user-results').html('<span style="color:#16a34a;">✅ کاربر انتخاب شد: '+name+'</span>');
                    console.log('selectedUserId (search):', selectedUserId);
                });
            });
        });

        // ایجاد تیکت جدید
        $('#support-create-ticket-form').on('submit', function(e) {
            e.preventDefault();
            var msg = $('#support-message').val().trim();
            console.log('selectedUserId before submit:', selectedUserId);
            console.log('message:', msg);

            if (!selectedUserId) {
                alert('لطفاً یک کاربر را انتخاب کنید.');
                return;
            }
            if (!msg) {
                alert('متن پیام را وارد کنید.');
                return;
            }

            var btn = $(this).find('button[type="submit"]');
            var status = $('#create-ticket-status');
            btn.text('⏳...').prop('disabled', true);
            status.text('');

            var formData = new FormData(this);
            formData.append('action', 'ezlens_admin_support_create_ticket');
            formData.append('nonce', ezlens_auth_ajax.nonce);
            formData.append('user_id', selectedUserId);
            formData.append('message', msg);

            $.ajax({
                url: ezlens_auth_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    btn.text('✅ ایجاد تیکت').prop('disabled', false);
                    console.log('Server response:', response);
                    if (response.success) {
                        status.text('✅ ' + (response.data.message || 'تیکت ایجاد شد.')).css('color', '#16a34a');
                        $('#support-message').val('');
                        $('#support-subject').val('');
                        $('#support-user-search').val('');
                        $('#support-user-select').val('');
                        selectedUserId = 0;
                        $('#support-user-results').html('');
                        setTimeout(function(){
                            tabs.filter('[data-tab="list"]').click();
                        }, 1500);
                    } else {
                        status.text('❌ ' + (response.data || 'خطا')).css('color', '#dc2626');
                    }
                },
                error: function(xhr) {
                    btn.text('✅ ایجاد تیکت').prop('disabled', false);
                    console.error('AJAX error:', xhr);
                    status.text('❌ خطا در ارتباط با سرور: ' + xhr.status).css('color', '#dc2626');
                }
            });
        });
    }
    // ============================================================

    window.loadConversation = function(id) {
        var tab = tabs.filter('[data-tab="conversation"]');
        tab.data('ticket-id', id);
        tab.show();
        tab.click();
    };

    function escHtml(s){ if(!s) return ''; var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
    function nl2br(s){ return s.replace(/\n/g,'<br>'); }

    // بارگذاری تب پیش‌فرض
    loadTab('list');
    tabs.on('click', function(){
        var tabId = $(this).data('tab');
        if (tabId==='conversation') {
            var id = $(this).data('ticket-id')||0;
            loadTab(tabId, { ticket_id: id });
        } else loadTab(tabId);
    });
});
</script>