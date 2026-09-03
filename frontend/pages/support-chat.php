<?php
/**
 * ویجت چت پشتیبانی در پنل کاربری (نسخه کامل اصلاح‌شده)
 * @version 2.4.0
 */
if (!is_user_logged_in()) return;

$user_id = get_current_user_id();
$support = EzLens_Auth_Support::get_instance();
$ticket = $support->get_open_ticket($user_id);
$support_phone = '02144385667';
$ajax_url = admin_url('admin-ajax.php');
$nonce = wp_create_nonce('ezlens_support_nonce');
?>
<div class="ezlens-chat-widget" id="ezlensChatWidget">
    <div class="chat-toggle" id="chatToggle">
        <span>💬</span>
        <span>پشتیبانی</span>
    </div>
    <div class="chat-box" id="chatBox" style="display:none;">
        <div class="chat-header">
            <span>💬 پشتیبانی</span>
            <button class="chat-close" id="chatClose">✕</button>
        </div>
        <div class="chat-info">
            📞 <?php echo esc_html($support_phone); ?>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div style="text-align:center;color:#94a3b8;padding:20px;">در حال بارگذاری...</div>
        </div>
        <div class="chat-input-area">
            <form id="chatMessageForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="ezlens_support_send_message">
                <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
                <div style="display:flex;gap:6px;align-items:center;">
                    <textarea name="message" id="chatMessageInput" rows="2" placeholder="پیام خود را بنویسید..." style="flex:1;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-family:inherit;resize:none;"></textarea>
                    <div class="chat-file-wrap" style="position:relative;">
                        <span style="cursor:pointer;font-size:20px;">📎</span>
                        <input type="file" name="attachment" id="chatFileInput" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;">
                    </div>
                    <button type="submit" class="chat-send-btn" style="padding:8px 16px;background:#2b6cb0;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;">ارسال</button>
                </div>
                <div id="chatFilePreview" style="font-size:12px;color:#64748b;margin-top:4px;"></div>
            </form>
        </div>
    </div>
</div>

<style>
.ezlens-chat-widget {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 99999;
    font-family: Vazirmatn, IRANYekan, Tahoma, Arial, sans-serif;
    direction: rtl;
}
.chat-toggle {
    background: #2b6cb0;
    color: #fff;
    padding: 12px 18px;
    border-radius: 50px;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(43,108,176,0.3);
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s;
}
.chat-toggle:hover {
    background: #1a4f8b;
    transform: scale(1.05);
}
.chat-box {
    position: absolute;
    bottom: 70px;
    left: 0;
    width: 360px;
    max-height: 500px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 12px 48px rgba(0,0,0,0.15);
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.chat-header {
    background: #2b6cb0;
    color: #fff;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 700;
}
.chat-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
}
.chat-info {
    padding: 6px 16px;
    background: #f8fafc;
    font-size: 12px;
    color: #475569;
    border-bottom: 1px solid #e2e8f0;
}
.chat-messages {
    flex: 1;
    padding: 12px 16px;
    overflow-y: auto;
    max-height: 300px;
    background: #fafafa;
}
.chat-messages .msg-user {
    background: #e2e8f0;
    border-radius: 12px 12px 12px 4px;
    padding: 8px 14px;
    margin-bottom: 8px;
    max-width: 85%;
    align-self: flex-start;
}
.chat-messages .msg-admin {
    background: #2b6cb0;
    color: #fff;
    border-radius: 12px 12px 4px 12px;
    padding: 8px 14px;
    margin-bottom: 8px;
    max-width: 85%;
    align-self: flex-end;
    margin-right: auto;
}
.chat-messages .msg-time {
    font-size: 9px;
    color: #94a3b8;
    display: block;
    margin-top: 2px;
}
.chat-messages .msg-admin .msg-time {
    color: rgba(255,255,255,0.7);
}
.chat-messages .msg-image {
    max-width: 150px;
    border-radius: 6px;
    margin-top: 4px;
    border: 1px solid #e2e8f0;
}
.chat-messages .msg-file {
    font-size: 12px;
    display: inline-block;
    margin-top: 4px;
}
.chat-messages .msg-file a {
    color: #2b6cb0;
    text-decoration: underline;
}
.chat-messages .msg-admin .msg-file a {
    color: #fff;
}
.chat-input-area {
    padding: 10px 12px;
    border-top: 1px solid #e2e8f0;
    background: #fff;
}
.chat-file-wrap {
    display: inline-block;
    cursor: pointer;
    font-size: 20px;
    padding: 4px 8px;
}
.chat-send-btn:hover {
    background: #1a4f8b;
}
@media (max-width: 480px) {
    .chat-box {
        width: 300px;
        left: -20px;
    }
}
</style>

<script>
(function($) {
    'use strict';

    var $widget = $('#ezlensChatWidget');
    var $toggle = $('#chatToggle');
    var $box = $('#chatBox');
    var $close = $('#chatClose');
    var $messages = $('#chatMessages');
    var $form = $('#chatMessageForm');
    var $input = $('#chatMessageInput');
    var $fileInput = $('#chatFileInput');
    var $filePreview = $('#chatFilePreview');

    // بررسی وجود ezlens_frontend
    if (typeof ezlens_frontend === 'undefined') {
        window.ezlens_frontend = {
            ajax_url: '<?php echo admin_url("admin-ajax.php"); ?>',
            nonce: '<?php echo wp_create_nonce("ezlens_support_nonce"); ?>'
        };
    }

    $toggle.on('click', function() {
        if ($box.is(':hidden')) {
            $box.show();
            loadMessages();
        } else {
            $box.hide();
        }
    });

    $close.on('click', function() {
        $box.hide();
    });

    function loadMessages() {
        $messages.html('<div style="text-align:center;color:#94a3b8;padding:20px;">⏳ در حال بارگذاری...</div>');

        var data = {
            action: 'ezlens_support_get_user_messages',
            nonce: '<?php echo esc_attr(wp_create_nonce("ezlens_support_nonce")); ?>'
        };

        $.post(ezlens_frontend.ajax_url, data, function(response) {
            if (response.success) {
                var msgs = response.data.messages;
                if (msgs.length === 0) {
                    $messages.html('<div style="text-align:center;color:#94a3b8;padding:20px;">هیچ پیامی وجود ندارد.<br>اولین پیام را ارسال کنید.</div>');
                    return;
                }
                var html = '';
                msgs.forEach(function(msg) {
                    var isAdmin = msg.sender_type === 'admin';
                    var cls = isAdmin ? 'msg-admin' : 'msg-user';
                    var align = isAdmin ? 'style="text-align:right;"' : '';
                    html += '<div class="' + cls + '" ' + align + '>';
                    html += '<div>' + escHtml(msg.message) + '</div>';
                    if (msg.file_attachment) {
                        var ext = msg.file_attachment.split('.').pop().toLowerCase();
                        var isImage = ['jpg','jpeg','png','gif','webp'].indexOf(ext) !== -1;
                        if (isImage) {
                            html += '<div><img src="' + escHtml(msg.file_attachment) + '" class="msg-image" /></div>';
                        } else {
                            html += '<div class="msg-file"><a href="' + escHtml(msg.file_attachment) + '" target="_blank">📎 دانلود فایل</a></div>';
                        }
                    }
                    html += '<span class="msg-time">' + msg.created_at + '</span>';
                    html += '</div>';
                });
                $messages.html(html);
                $messages.scrollTop($messages[0].scrollHeight);
            } else {
                $messages.html('<div style="text-align:center;color:#dc2626;padding:20px;">خطا در بارگذاری پیام‌ها</div>');
            }
        }).fail(function() {
            $messages.html('<div style="text-align:center;color:#dc2626;padding:20px;">خطا در ارتباط با سرور</div>');
        });
    }

    $fileInput.on('change', function() {
        var file = this.files[0];
        if (file) {
            $filePreview.text('📎 ' + file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)');
        } else {
            $filePreview.text('');
        }
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        var message = $input.val().trim();
        if (!message) {
            alert('لطفاً متن پیام را وارد کنید.');
            return;
        }

        var btn = $(this).find('.chat-send-btn');
        btn.text('⏳...').prop('disabled', true);

        var formData = new FormData(this);
        formData.append('nonce', '<?php echo esc_attr(wp_create_nonce("ezlens_support_nonce")); ?>');

        $.ajax({
            url: ezlens_frontend.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                btn.text('ارسال').prop('disabled', false);
                if (response.success) {
                    $input.val('');
                    $fileInput.val('');
                    $filePreview.text('');
                    loadMessages();
                } else {
                    alert('❌ ' + (response.data || 'خطا در ارسال پیام.'));
                }
            },
            error: function() {
                btn.text('ارسال').prop('disabled', false);
                alert('❌ خطا در ارتباط با سرور.');
            }
        });
    });

    function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // بارگذاری اولیه اگر چت باز باشد
    if ($box.is(':visible')) {
        loadMessages();
    }

})(jQuery);
</script>