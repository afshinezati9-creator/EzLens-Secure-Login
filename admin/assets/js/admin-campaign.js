/**
 * EzLens Campaign Scripts (نسخه کامل با رفع خطاها)
 * @version 2.6.0
 */
jQuery(document).ready(function($) {
    'use strict';
    if (window.__ezlensCampaignAdminInitialized) return;
    window.__ezlensCampaignAdminInitialized = true;

    // ============================================================
    // ۱. توابع عمومی
    // ============================================================
    function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * تبدیل تاریخ میلادی به شمسی (برای نمایش)
     */
    function toPersianDate(dateStr) {
        if (!dateStr) return '—';
        try {
            var date = new Date(dateStr);
            if (isNaN(date.getTime())) return dateStr;
            var persian = new Intl.DateTimeFormat('fa-IR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
            return persian.format(date);
        } catch(e) {
            return dateStr;
        }
    }

    // ============================================================
    // ۲. مدیریت تب‌ها
    // ============================================================
    var tabs = $('.ezlens-campaign-tabs .campaign-tab');
    var content = $('#campaignContent');

    function loadCampaignTab(tabId) {
        tabs.removeClass('active');
        tabs.filter('[data-tab="' + tabId + '"]').addClass('active');
        content.html('<div style="text-align:center;padding:40px;color:#94a3b8;">⏳ در حال بارگذاری...</div>');

        var data = {
            action: 'ezlens_campaign_load_tab',
            nonce: ezlens_auth_ajax.nonce,
            tab: tabId
        };

        $.post(ezlens_auth_ajax.ajax_url, data, function(response) {
            if (response.success) {
                content.html(response.data.html);
                initTabEvents(tabId);
            } else {
                content.html('<div style="text-align:center;padding:40px;color:#dc2626;">❌ خطا در بارگذاری</div>');
            }
        }).fail(function() {
            content.html('<div style="text-align:center;padding:40px;color:#dc2626;">❌ خطا در ارتباط با سرور</div>');
        });
    }

    var activeTab = tabs.filter('.active').data('tab') || 'dashboard';
    loadCampaignTab(activeTab);

    tabs.on('click', function() {
        loadCampaignTab($(this).data('tab'));
    });

    // ============================================================
    // ۳. توابع هر تب
    // ============================================================
    function initTabEvents(tabId) {
        if (tabId === 'dashboard') {
            initDashboard();
        } else if (tabId === 'audience') {
            initAudience();
        } else if (tabId === 'create') {
            initCreate();
        } else if (tabId === 'history') {
            initHistory();
        } else if (tabId === 'settings') {
            initSettings();
        }
    }

    // ============================================================
    // ۴. داشبورد
    // ============================================================
    function initDashboard() {
        var container = $('#campaign-dashboard-stats');
        container.html('<div style="text-align:center;padding:20px;color:#94a3b8;">⏳ در حال بارگذاری آمار...</div>');

        $.post(ezlens_auth_ajax.ajax_url, {
            action: 'ezlens_campaign_get_stats',
            nonce: ezlens_auth_ajax.nonce
        }, function(response) {
            if (response.success) {
                var stats = response.data;
                var html = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;">';
                html += '<div style="padding:16px;background:#f8fafc;border-radius:10px;text-align:center;"><div style="font-size:28px;font-weight:800;color:#2b6cb0;">' + stats.total + '</div><div style="font-size:13px;color:#64748b;">کل کمپین‌ها</div></div>';
                html += '<div style="padding:16px;background:#f0fdf4;border-radius:10px;text-align:center;"><div style="font-size:28px;font-weight:800;color:#16a34a;">' + stats.sent + '</div><div style="font-size:13px;color:#64748b;">ارسال شده</div></div>';
                html += '<div style="padding:16px;background:#fef3c7;border-radius:10px;text-align:center;"><div style="font-size:28px;font-weight:800;color:#d97706;">' + stats.draft + '</div><div style="font-size:13px;color:#64748b;">پیش‌نویس</div></div>';
                html += '<div style="padding:16px;background:#ebf8ff;border-radius:10px;text-align:center;"><div style="font-size:28px;font-weight:800;color:#3182ce;">' + stats.scheduled + '</div><div style="font-size:13px;color:#64748b;">برنامه‌ریزی شده</div></div>';
                html += '</div>';
                container.html(html);
            } else {
                container.html('<p style="color:#dc2626;">❌ خطا در دریافت آمار</p>');
            }
        }).fail(function() {
            container.html('<p style="color:#dc2626;">❌ خطا در ارتباط با سرور</p>');
        });
    }

    // ============================================================
    // ۵. مخاطبان (نسخه کامل با فرم AJAX)
    // ============================================================
    function initAudience() {
        // متغیرهای عمومی برای تب مخاطبان
        var selectedRecipients = [];
        var userPage = 1, userLimit = 20, userTotal = 0;
        var contactPage = 1, contactLimit = 20, contactTotal = 0;

        // بارگذاری کاربران
        function loadUsers(page) {
            page = page || userPage;
            var container = $('#audience-user-list');
            var pagination = $('#audience-user-pagination');
            var search = $('#audience-user-search').val();
            var role = $('#audience-user-role').val();
            var date = $('#audience-user-date').val();
            var spent = $('#audience-user-spent').val();

            container.html('<p style="padding:20px;text-align:center;color:#94a3b8;margin:0;">⏳ در حال بارگذاری...</p>');

            var offset = (page - 1) * userLimit;
            $.post(ezlens_auth_ajax.ajax_url, {
                action: 'ezlens_campaign_get_audience',
                nonce: ezlens_auth_ajax.nonce,
                role: role,
                registered_after: date,
                min_spent: spent,
                search: search,
                limit: userLimit,
                offset: offset
            }, function(response) {
                if (response.success) {
                    var users = response.data.users;
                    userTotal = response.data.count || 0;
                    $('#users-count-badge').text(userTotal);

                    if (!users || !users.length) {
                        container.html('<p style="padding:20px;text-align:center;color:#94a3b8;margin:0;">هیچ کاربری با فیلترهای انتخاب‌شده یافت نشد.</p>');
                        pagination.html('');
                        return;
                    }

                    var html = '';
                    users.forEach(function(user) {
                        var checked = selectedRecipients.some(r => r.id == user.id && r.source === 'user') ? 'checked' : '';
                        var name = user.name || 'بدون نام';
                        html += '<div class="user-item" data-id="' + user.id + '" data-email="' + escHtml(user.email) + '" data-name="' + escHtml(name) + '" data-phone="' + escHtml(user.phone) + '">';
                        html += '<input type="checkbox" class="user-checkbox" ' + checked + ' value="' + user.id + '">';
                        html += '<span class="item-info">' + escHtml(name) + ' (' + escHtml(user.email) + ')</span>';
                        html += '</div>';
                    });
                    container.html(html);

                    container.find('.user-item').on('click', function(e) {
                        if ($(e.target).is('input[type="checkbox"]')) return;
                        var cb = $(this).find('.user-checkbox');
                        cb.prop('checked', !cb.prop('checked'));
                        cb.trigger('change');
                    });

                    container.find('.user-checkbox').on('change', function() {
                        var item = $(this).closest('.user-item');
                        var id = item.data('id');
                        var email = item.data('email');
                        var name = item.data('name');
                        var phone = item.data('phone');
                        var checked = $(this).prop('checked');
                        if (checked) {
                            addToSelected(id, name, email, phone, 'user');
                        } else {
                            removeFromSelected(id, 'user');
                        }
                        updateSelectedUI();
                    });

                    renderPagination(pagination, userPage, userTotal, userLimit, 'user');

                } else {
                    container.html('<p style="padding:20px;text-align:center;color:#dc2626;margin:0;">❌ خطا در بارگذاری: ' + (response.data || '') + '</p>');
                }
            }).fail(function() {
                container.html('<p style="padding:20px;text-align:center;color:#dc2626;margin:0;">❌ خطا در ارتباط با سرور</p>');
            });
        }

        // بارگذاری مخاطبان دستی
        function loadContacts(page) {
            page = page || contactPage;
            var container = $('#audience-contact-list');
            var pagination = $('#audience-contact-pagination');
            var search = $('#audience-contact-search').val();

            container.html('<p style="padding:20px;text-align:center;color:#94a3b8;margin:0;">⏳ در حال بارگذاری...</p>');

            var offset = (page - 1) * contactLimit;
            $.post(ezlens_auth_ajax.ajax_url, {
                action: 'ezlens_campaign_get_contacts',
                nonce: ezlens_auth_ajax.nonce,
                limit: contactLimit,
                offset: offset,
                search: search
            }, function(response) {
                if (response.success) {
                    var contacts = response.data.contacts;
                    contactTotal = response.data.total || 0;
                    $('#contacts-count-badge').text(contactTotal);

                    if (!contacts || !contacts.length) {
                        container.html('<p style="padding:20px;text-align:center;color:#94a3b8;margin:0;">هیچ مخاطبی یافت نشد.</p>');
                        pagination.html('');
                        return;
                    }

                    var html = '';
                    contacts.forEach(function(c) {
                        var checked = selectedRecipients.some(r => r.id == c.id && r.source === 'custom') ? 'checked' : '';
                        var name = c.name || 'بدون نام';
                        html += '<div class="contact-item" data-id="' + c.id + '" data-email="' + escHtml(c.email) + '" data-name="' + escHtml(name) + '" data-phone="' + escHtml(c.phone) + '">';
                        html += '<input type="checkbox" class="contact-checkbox" ' + checked + ' value="' + c.id + '">';
                        html += '<span class="item-info">' + escHtml(name) + ' (' + escHtml(c.email) + ')</span>';
                        html += '</div>';
                    });
                    container.html(html);

                    container.find('.contact-item').on('click', function(e) {
                        if ($(e.target).is('input[type="checkbox"]')) return;
                        var cb = $(this).find('.contact-checkbox');
                        cb.prop('checked', !cb.prop('checked'));
                        cb.trigger('change');
                    });

                    container.find('.contact-checkbox').on('change', function() {
                        var item = $(this).closest('.contact-item');
                        var id = item.data('id');
                        var email = item.data('email');
                        var name = item.data('name');
                        var phone = item.data('phone');
                        var checked = $(this).prop('checked');
                        if (checked) {
                            addToSelected(id, name, email, phone, 'custom');
                        } else {
                            removeFromSelected(id, 'custom');
                        }
                        updateSelectedUI();
                    });

                    renderPagination(pagination, contactPage, contactTotal, contactLimit, 'contact');

                } else {
                    container.html('<p style="padding:20px;text-align:center;color:#dc2626;margin:0;">❌ خطا در بارگذاری</p>');
                }
            }).fail(function() {
                container.html('<p style="padding:20px;text-align:center;color:#dc2626;margin:0;">❌ خطا در ارتباط با سرور</p>');
            });
        }

        // مدیریت مخاطبان انتخاب‌شده
        function addToSelected(id, name, email, phone, source) {
            if (!selectedRecipients.some(r => r.id == id && r.source === source)) {
                selectedRecipients.push({ id: id, name: name, email: email, phone: phone, source: source });
            }
        }

        function removeFromSelected(id, source) {
            selectedRecipients = selectedRecipients.filter(r => !(r.id == id && r.source === source));
        }

        function updateSelectedUI() {
            var count = selectedRecipients.length;
            $('#selected-count-badge').text(count);

            var preview = $('#selected-list-preview');
            if (count === 0) {
                preview.html('<span style="color:#94a3b8;font-size:13px;">هنوز مخاطبی انتخاب نشده است.</span>');
            } else {
                var html = '';
                var showCount = Math.min(count, 10);
                for (var i = 0; i < showCount; i++) {
                    var r = selectedRecipients[i];
                    var label = r.name || r.email;
                    var sourceLabel = r.source === 'user' ? '👤' : '📋';
                    html += '<span style="background:#e2e8f0;padding:2px 8px;border-radius:4px;font-size:12px;">' + sourceLabel + ' ' + escHtml(label) + '</span>';
                }
                if (count > 10) {
                    html += '<span style="color:#64748b;font-size:12px;">+ ' + (count - 10) + ' نفر دیگر</span>';
                }
                preview.html(html);
            }

            $('.user-checkbox').each(function() {
                var id = parseInt($(this).val());
                var checked = selectedRecipients.some(r => r.id == id && r.source === 'user');
                $(this).prop('checked', checked);
            });
            $('.contact-checkbox').each(function() {
                var id = parseInt($(this).val());
                var checked = selectedRecipients.some(r => r.id == id && r.source === 'custom');
                $(this).prop('checked', checked);
            });
        }

        // صفحه‌بندی
        function renderPagination(container, currentPage, total, limit, type) {
            var totalPages = Math.ceil(total / limit);
            if (totalPages <= 1) {
                container.html('');
                return;
            }
            var html = '<div class="pagination-wrap">';
            html += '<button class="page-btn" data-page="' + (currentPage - 1) + '" data-type="' + type + '" ' + (currentPage <= 1 ? 'disabled' : '') + '>‹</button>';
            for (var p = Math.max(1, currentPage - 2); p <= Math.min(totalPages, currentPage + 2); p++) {
                html += '<button class="page-btn ' + (p === currentPage ? 'active' : '') + '" data-page="' + p + '" data-type="' + type + '">' + p + '</button>';
            }
            html += '<button class="page-btn" data-page="' + (currentPage + 1) + '" data-type="' + type + '" ' + (currentPage >= totalPages ? 'disabled' : '') + '>›</button>';
            html += '<span style="font-size:12px;color:#64748b;margin-right:4px;">' + currentPage + '/' + totalPages + '</span>';
            html += '</div>';
            container.html(html);

            container.find('.page-btn').on('click', function() {
                var page = parseInt($(this).data('page'));
                var type = $(this).data('type');
                if (page && page > 0) {
                    if (type === 'user') {
                        userPage = page;
                        loadUsers(page);
                    } else if (type === 'contact') {
                        contactPage = page;
                        loadContacts(page);
                    }
                }
            });
        }

        // بارگذاری دسته‌ها
        function loadGroups() {
            var container = $('#groups-list-container');
            container.html('<p style="padding:20px;text-align:center;color:#94a3b8;margin:0;">⏳ در حال بارگذاری...</p>');

            $.post(ezlens_auth_ajax.ajax_url, {
                action: 'ezlens_campaign_get_groups',
                nonce: ezlens_auth_ajax.nonce
            }, function(response) {
                if (response.success) {
                    var groups = response.data.groups;
                    if (!groups || !groups.length) {
                        container.html('<p style="padding:20px;text-align:center;color:#94a3b8;margin:0;">هیچ دسته‌ای ایجاد نشده است.</p>');
                        return;
                    }
                    var html = '';
                    groups.forEach(function(g) {
                        var typeLabel = g.type === 'wordpress' ? 'کاربران' : (g.type === 'custom' ? 'دستی' : 'ترکیبی');
                        html += '<div class="group-item" data-id="' + g.id + '">';
                        html += '<div class="group-info">';
                        html += '<strong>' + escHtml(g.name) + '</strong> <span style="font-size:12px;color:#64748b;">(' + typeLabel + ')</span>';
                        html += '<span style="font-size:12px;color:#94a3b8;margin-left:8px;">' + escHtml(g.description) + '</span>';
                        html += '</div>';
                        html += '<div class="group-actions">';
                        html += '<button class="button button-small view-group-recipients" data-id="' + g.id + '" style="font-size:11px;" title="مشاهده مخاطبان">👁️</button>';
                        html += '<button class="button button-small edit-group" data-id="' + g.id + '" data-name="' + escHtml(g.name) + '" data-desc="' + escHtml(g.description) + '" data-type="' + g.type + '" style="font-size:11px;" title="ویرایش">✏️</button>';
                        html += '<button class="button button-small delete-group" data-id="' + g.id + '" style="font-size:11px;color:#dc2626;border-color:#dc2626;" title="حذف">🗑️</button>';
                        html += '</div>';
                        html += '</div>';
                    });
                    container.html(html);

                    container.find('.view-group-recipients').on('click', function() {
                        var id = $(this).data('id');
                        viewGroupRecipients(id);
                    });

                    container.find('.edit-group').on('click', function() {
                        var id = $(this).data('id');
                        var name = $(this).data('name');
                        var desc = $(this).data('desc');
                        var type = $(this).data('type');
                        editGroup(id, name, desc, type);
                    });

                    container.find('.delete-group').on('click', function() {
                        var id = $(this).data('id');
                        var name = $(this).closest('.group-item').find('strong').text();
                        if (!confirm('آیا از حذف دسته "' + name + '" اطمینان دارید؟')) return;
                        $.post(ezlens_auth_ajax.ajax_url, {
                            action: 'ezlens_campaign_delete_group',
                            nonce: ezlens_auth_ajax.nonce,
                            id: id
                        }, function(r) {
                            if (r.success) { loadGroups(); }
                            else { alert('❌ ' + r.data); }
                        });
                    });

                } else {
                    container.html('<p style="padding:20px;text-align:center;color:#dc2626;margin:0;">❌ خطا در بارگذاری</p>');
                }
            }).fail(function() {
                container.html('<p style="padding:20px;text-align:center;color:#dc2626;margin:0;">❌ خطا در ارتباط با سرور</p>');
            });
        }

        // مشاهده مخاطبان یک گروه
        function viewGroupRecipients(id) {
            $.post(ezlens_auth_ajax.ajax_url, {
                action: 'ezlens_campaign_get_group_recipients',
                nonce: ezlens_auth_ajax.nonce,
                id: id
            }, function(response) {
                if (response.success) {
                    var recipients = response.data.recipients;
                    var count = response.data.count;
                    if (!recipients || !recipients.length) {
                        alert('هیچ مخاطبی در این دسته وجود ندارد.');
                        return;
                    }
                    var msg = '📋 لیست مخاطبان دسته (' + count + ' نفر):\n\n';
                    recipients.forEach(function(r, i) {
                        var sourceLabel = r.source === 'user' ? '👤 کاربر' : '📋 دستی';
                        msg += (i+1) + '. ' + (r.name || 'نامشخص') + ' (' + r.email + ') — ' + sourceLabel + '\n';
                    });
                    alert(msg);
                } else {
                    alert('❌ ' + response.data);
                }
            });
        }

        // ویرایش گروه
        function editGroup(id, name, desc, type) {
            var newName = prompt('نام جدید دسته:', name);
            if (newName === null) return;
            var newDesc = prompt('توضیحات جدید:', desc);
            if (newDesc === null) return;

            if (!newName.trim()) { alert('نام گروه نمی‌تواند خالی باشد.'); return; }

            $.post(ezlens_auth_ajax.ajax_url, {
                action: 'ezlens_campaign_update_group',
                nonce: ezlens_auth_ajax.nonce,
                id: id,
                name: newName,
                description: newDesc
            }, function(response) {
                if (response.success) {
                    alert('✅ دسته به‌روز شد.');
                    loadGroups();
                } else {
                    alert('❌ ' + response.data);
                }
            });
        }

        // ============================================================
        // فرم ایجاد دسته جدید (AJAX)
        // ============================================================
        function showGroupForm(data) {
            var isEdit = data && data.id;
            var form = $('#audience-group-form-container');
            var title = $('#audience-group-form-title');
            var nameField = $('#audience-group-name');
            var descField = $('#audience-group-desc');
            var typeField = $('#audience-group-type');
            var editId = $('#audience-group-edit-id');

            if (isEdit) {
                title.text('ویرایش دسته');
                editId.val(data.id);
                nameField.val(data.name);
                descField.val(data.desc);
                typeField.val(data.type);
            } else {
                title.text('ایجاد دسته جدید');
                editId.val(0);
                nameField.val('');
                descField.val('');
                typeField.val('custom');
            }

            form.slideDown();
            // بارگذاری مخاطبان برای انتخاب
            loadContactsForGroup();
        }

        function loadContactsForGroup() {
            var container = $('#audience-contact-select-list');
            container.html('<p style="color:#94a3b8;">⏳ در حال بارگذاری...</p>').show();

            $.post(ezlens_auth_ajax.ajax_url, {
                action: 'ezlens_campaign_get_contacts',
                nonce: ezlens_auth_ajax.nonce,
                limit: 100,
                offset: 0,
                search: ''
            }, function(response) {
                if (response.success) {
                    var contacts = response.data.contacts;
                    if (!contacts || !contacts.length) {
                        container.html('<p style="color:#94a3b8;">هیچ مخاطبی یافت نشد. ابتدا مخاطب اضافه کنید.</p>');
                        return;
                    }
                    var html = '';
                    contacts.forEach(function(c) {
                        var checked = selectedRecipients.some(r => r.id == c.id && r.source === 'custom') ? 'checked' : '';
                        html += '<label style="display:flex;align-items:center;gap:6px;padding:4px 8px;border-bottom:1px solid #f1f5f9;cursor:pointer;">';
                        html += '<input type="checkbox" class="audience-contact-check" value="' + c.id + '" ' + checked + '>';
                        html += '<span>' + escHtml(c.name) + ' (' + escHtml(c.email) + ')</span>';
                        html += '</label>';
                    });
                    html += '<button type="button" id="audience-select-all-contacts" class="button button-small" style="margin-top:4px;">انتخاب همه</button> ';
                    html += '<button type="button" id="audience-deselect-all-contacts" class="button button-small">لغو همه</button>';
                    container.html(html);

                    $('#audience-select-all-contacts').on('click', function() {
                        $('.audience-contact-check').prop('checked', true);
                        updateSelectedCount();
                    });
                    $('#audience-deselect-all-contacts').on('click', function() {
                        $('.audience-contact-check').prop('checked', false);
                        updateSelectedCount();
                    });
                    $('.audience-contact-check').on('change', updateSelectedCount);
                    updateSelectedCount();
                } else {
                    container.html('<p style="color:#dc2626;">❌ خطا در بارگذاری</p>');
                }
            });
        }

        function updateSelectedCount() {
            var count = $('.audience-contact-check:checked').length;
            $('#audience-selected-count').text(count);
        }

        // ذخیره گروه (ایجاد یا ویرایش)
        $('#audience-group-form').on('submit', function(e) {
            e.preventDefault();
            var id = $('#audience-group-edit-id').val();
            var name = $('#audience-group-name').val().trim();
            var type = $('#audience-group-type').val();
            var description = $('#audience-group-desc').val().trim();
            var contactIds = [];
            $('.audience-contact-check:checked').each(function() {
                contactIds.push(parseInt($(this).val()));
            });

            if (!name) {
                $('#audience-group-status').text('❌ نام گروه را وارد کنید.').css('color', '#dc2626');
                return;
            }

            var btn = $(this).find('button[type="submit"]');
            var status = $('#audience-group-status');
            btn.text('⏳...').prop('disabled', true);
            status.text('');

            var action = id ? 'ezlens_campaign_update_group' : 'ezlens_campaign_create_group';
            var data = {
                action: action,
                nonce: ezlens_auth_ajax.nonce,
                name: name,
                type: type,
                description: description,
                contact_ids: JSON.stringify(contactIds)
            };
            if (id) data.id = parseInt(id);

            $.post(ezlens_auth_ajax.ajax_url, data, function(response) {
                btn.html('✅ ذخیره گروه').prop('disabled', false);
                if (response.success) {
                    status.text('✅ ' + response.data).css('color', '#16a34a');
                    $('#audience-group-form-container').slideUp();
                    $('#audience-group-edit-id').val(0);
                    $('#audience-group-name').val('');
                    $('#audience-group-desc').val('');
                    loadGroups();
                } else {
                    status.text('❌ ' + response.data).css('color', '#dc2626');
                }
            }).fail(function() {
                btn.html('✅ ذخیره گروه').prop('disabled', false);
                status.text('❌ خطا در ارتباط با سرور').css('color', '#dc2626');
            });
        });

        // دکمه‌های فرم گروه
        $('#audience-new-group-btn').on('click', function() {
            showGroupForm(null);
        });

        $('#audience-group-form-cancel').on('click', function() {
            $('#audience-group-form-container').slideUp();
        });

        $('#audience-load-contacts-for-group').on('click', function() {
            loadContactsForGroup();
        });

        // ============================================================
        // دکمه‌های باکس پایین
        // ============================================================
        $('#selected-view-list').on('click', function() {
            var count = selectedRecipients.length;
            if (count === 0) {
                alert('هیچ مخاطبی انتخاب نشده است.');
                return;
            }
            var msg = '📋 لیست مخاطبان انتخاب‌شده (' + count + ' نفر):\n\n';
            selectedRecipients.forEach(function(r, i) {
                var sourceLabel = r.source === 'user' ? 'کاربر' : 'دستی';
                msg += (i+1) + '. ' + (r.name || 'نامشخص') + ' (' + r.email + ') — ' + sourceLabel + '\n';
            });
            alert(msg);
        });

        $('#selected-clear-all').on('click', function() {
            if (selectedRecipients.length === 0) return;
            if (!confirm('آیا از پاک کردن همه مخاطبان انتخاب‌شده اطمینان دارید؟')) return;
            selectedRecipients = [];
            updateSelectedUI();
        });

        $('#selected-save-group').on('click', function() {
            var count = selectedRecipients.length;
            if (count === 0) {
                alert('هیچ مخاطبی برای ذخیره وجود ندارد.');
                return;
            }
            var groupName = prompt('نام دسته جدید را وارد کنید:', 'دسته ' + new Date().toLocaleDateString('fa-IR'));
            if (!groupName) return;

            var contactIds = selectedRecipients.filter(r => r.source === 'custom').map(r => r.id);
            var btn = $(this);
            var status = $('#selected-save-status');
            btn.text('⏳...').prop('disabled', true);
            status.text('');

            $.post(ezlens_auth_ajax.ajax_url, {
                action: 'ezlens_campaign_create_group',
                nonce: ezlens_auth_ajax.nonce,
                name: groupName,
                type: 'mixed',
                description: 'دسته شامل ' + count + ' مخاطب',
                contact_ids: JSON.stringify(contactIds)
            }, function(response) {
                btn.html('💾 ذخیره به عنوان دسته جدید').prop('disabled', false);
                if (response.success) {
                    status.text('✅ دسته "' + groupName + '" با موفقیت ایجاد شد.').css('color', '#16a34a');
                    loadGroups();
                    setTimeout(function() { status.text(''); }, 3000);
                } else {
                    status.text('❌ ' + response.data).css('color', '#dc2626');
                }
            }).fail(function() {
                btn.html('💾 ذخیره به عنوان دسته جدید').prop('disabled', false);
                status.text('❌ خطا در ارتباط با سرور').css('color', '#dc2626');
            });
        });

        // ============================================================
        // دکمه‌های ستون کاربران
        // ============================================================
        $('#audience-user-select-all').on('click', function() {
            $('.user-checkbox').prop('checked', true).trigger('change');
        });

        $('#audience-user-add-selected').on('click', function() {
            var added = 0;
            $('.user-checkbox:checked').each(function() {
                var item = $(this).closest('.user-item');
                var id = item.data('id');
                var email = item.data('email');
                var name = item.data('name');
                var phone = item.data('phone');
                if (!selectedRecipients.some(r => r.id == id && r.source === 'user')) {
                    addToSelected(id, name, email, phone, 'user');
                    added++;
                }
            });
            if (added === 0) alert('هیچ کاربر جدیدی برای افزودن وجود ندارد.');
            updateSelectedUI();
        });

        $('#audience-user-add-all').on('click', function() {
            var added = 0;
            $('.user-item').each(function() {
                var id = $(this).data('id');
                var email = $(this).data('email');
                var name = $(this).data('name');
                var phone = $(this).data('phone');
                if (!selectedRecipients.some(r => r.id == id && r.source === 'user')) {
                    addToSelected(id, name, email, phone, 'user');
                    added++;
                }
            });
            if (added === 0) alert('همه کاربران قبلاً اضافه شده‌اند.');
            updateSelectedUI();
        });

        // ============================================================
        // دکمه‌های ستون مخاطبان دستی
        // ============================================================
        $('#audience-contact-select-all').on('click', function() {
            $('.contact-checkbox').prop('checked', true).trigger('change');
        });

        $('#audience-contact-add-selected').on('click', function() {
            var added = 0;
            $('.contact-checkbox:checked').each(function() {
                var item = $(this).closest('.contact-item');
                var id = item.data('id');
                var email = item.data('email');
                var name = item.data('name');
                var phone = item.data('phone');
                if (!selectedRecipients.some(r => r.id == id && r.source === 'custom')) {
                    addToSelected(id, name, email, phone, 'custom');
                    added++;
                }
            });
            if (added === 0) alert('هیچ مخاطب جدیدی برای افزودن وجود ندارد.');
            updateSelectedUI();
        });

        $('#audience-contact-add-all').on('click', function() {
            var added = 0;
            $('.contact-item').each(function() {
                var id = $(this).data('id');
                var email = $(this).data('email');
                var name = $(this).data('name');
                var phone = $(this).data('phone');
                if (!selectedRecipients.some(r => r.id == id && r.source === 'custom')) {
                    addToSelected(id, name, email, phone, 'custom');
                    added++;
                }
            });
            if (added === 0) alert('همه مخاطبان قبلاً اضافه شده‌اند.');
            updateSelectedUI();
        });

        // ============================================================
        // افزودن مخاطب دستی با AJAX
        // ============================================================
        $('#audience-contact-add-btn').on('click', function() {
            var name = $('#audience-contact-name').val().trim();
            var email = $('#audience-contact-email').val().trim();
            var phone = $('#audience-contact-phone').val().trim();
            var status = $('#audience-add-status');

            if (!email) { status.html('❌ ایمیل را وارد کنید.').css('color','#dc2626'); return; }

            var btn = $(this);
            btn.text('⏳...').prop('disabled', true);
            status.text('');

            $.post(ezlens_auth_ajax.ajax_url, {
                action: 'ezlens_campaign_add_contact',
                nonce: ezlens_auth_ajax.nonce,
                name: name,
                email: email,
                phone: phone
            }, function(response) {
                btn.html('➕ افزودن').prop('disabled', false);
                if (response.success) {
                    status.html('✅ ' + response.data.message).css('color', '#16a34a');
                    $('#audience-contact-name').val('');
                    $('#audience-contact-email').val('');
                    $('#audience-contact-phone').val('');
                    loadContacts(contactPage);
                } else {
                    status.html('❌ ' + response.data).css('color', '#dc2626');
                }
            }).fail(function() {
                btn.html('➕ افزودن').prop('disabled', false);
                status.html('❌ خطا در ارتباط با سرور').css('color', '#dc2626');
            });
        });

        // ============================================================
        // رویدادهای جستجو و فیلتر
        // ============================================================
        $('#audience-user-search').on('keyup', function(e) {
            if (e.which === 13) { userPage = 1; loadUsers(); }
        });
        $('#audience-user-filter-btn').on('click', function() { userPage = 1; loadUsers(); });
        $('#audience-user-reset-btn').on('click', function() {
            $('#audience-user-role').val('');
            $('#audience-user-date').val('');
            $('#audience-user-spent').val('');
            $('#audience-user-search').val('');
            userPage = 1;
            loadUsers();
        });

        $('#audience-contact-search').on('keyup', function(e) {
            if (e.which === 13) { contactPage = 1; loadContacts(); }
        });

        $('#groups-refresh-btn').on('click', loadGroups);
        $('#groups-create-btn').on('click', function() {
            showGroupForm(null);
        });

        // ============================================================
        // راهنما
        // ============================================================
        $('#audience-help-btn').on('click', function() {
            $('#audience-help-box').slideToggle();
        });
        $('#audience-help-close').on('click', function() {
            $('#audience-help-box').slideUp();
        });

        // ============================================================
        // بروزرسانی همه
        // ============================================================
        $('#audience-refresh-all').on('click', function() {
            loadUsers(userPage);
            loadContacts(contactPage);
            loadGroups();
        });

        // ============================================================
        // بارگذاری اولیه
        // ============================================================
        loadUsers();
        loadContacts();
        loadGroups();
        updateSelectedUI();

        console.log('✅ تب مخاطبان با طراحی سه ستون بارگذاری شد.');
    }

    // ============================================================
    // ۶. ایجاد کمپین
    // ============================================================
    function initCreate() {
        function loadGroupsForSelect() {
            var container = $('#campaign-groups-list');
            container.html('<p style="color:#94a3b8;padding:10px;">⏳ در حال بارگذاری گروه‌ها...</p>');

            $.post(ezlens_auth_ajax.ajax_url, {
                action: 'ezlens_campaign_get_groups',
                nonce: ezlens_auth_ajax.nonce
            }, function(response) {
                if (response.success) {
                    var groups = response.data.groups;
                    if (!groups || !groups.length) {
                        container.html('<p style="color:#94a3b8;padding:10px;">هیچ گروهی ایجاد نشده است. ابتدا در بخش مخاطبان گروه بسازید.</p>');
                        updateSelectedGroupsCount();
                        return;
                    }
                    var html = '';
                    groups.forEach(function(g) {
                        var typeLabel = g.type === 'wordpress' ? '👥 کاربران' : (g.type === 'custom' ? '📋 دستی' : '🔀 ترکیبی');
                        html += '<label style="display:flex;align-items:center;gap:8px;padding:6px 12px;border-bottom:1px solid #f1f5f9;cursor:pointer;">';
                        html += '<input type="checkbox" class="campaign-group-check" value="' + g.id + '">';
                        html += '<span><strong>' + escHtml(g.name) + '</strong> <span style="font-size:12px;color:#64748b;">' + typeLabel + '</span></span>';
                        html += '<span style="font-size:11px;color:#94a3b8;margin-right:auto;">' + escHtml(g.description) + '</span>';
                        html += '</label>';
                    });
                    container.html(html);

                    container.find('.campaign-group-check').on('change', updateSelectedGroupsCount);
                    updateSelectedGroupsCount();

                } else {
                    container.html('<p style="color:#dc2626;padding:10px;">❌ خطا در بارگذاری گروه‌ها</p>');
                }
            }).fail(function() {
                container.html('<p style="color:#dc2626;padding:10px;">❌ خطا در ارتباط با سرور</p>');
            });
        }

        function updateSelectedGroupsCount() {
            var count = $('.campaign-group-check:checked').length;
            $('#campaign-selected-groups-count').text(count);
        }

        $('#campaign-refresh-groups').on('click', loadGroupsForSelect);
        $('#campaign-select-all-groups').on('click', function() {
            $('.campaign-group-check').prop('checked', true);
            updateSelectedGroupsCount();
        });
        $('#campaign-deselect-all-groups').on('click', function() {
            $('.campaign-group-check').prop('checked', false);
            updateSelectedGroupsCount();
        });

        // درج متغیر
        var currentVar = 0;
        var vars = ['{name}', '{first_name}', '{email}', '{phone}', '{site_name}', '{order_count}', '{unsubscribe_url}'];
        $('#campaign-insert-var').on('click', function() {
            var textarea = $('#campaign-message');
            var cursorPos = textarea.prop('selectionStart');
            var text = textarea.val();
            var varToInsert = vars[currentVar % vars.length];
            textarea.val(text.substring(0, cursorPos) + varToInsert + text.substring(cursorPos));
            currentVar++;
            textarea.focus();
        });

        var campaignCreateBusy = false;

        function createCampaign(sendNow) {
            if (campaignCreateBusy) return;
            var name = $('#campaign-name').val().trim();
            var type = $('#campaign-type').val();
            var subject = $('#campaign-subject').val().trim();
            var message = $('#campaign-message').val().trim();
            var fileUrl = $('#campaign-file-url').val().trim();
            var groupIds = [];
            $('.campaign-group-check:checked').each(function() {
                groupIds.push(parseInt($(this).val()));
            });

            if (!name) { showStatus('❌ لطفاً نام کمپین را وارد کنید.', 'error'); return; }
            if (type === 'email' && !subject) { showStatus('❌ لطفاً موضوع ایمیل را وارد کنید.', 'error'); return; }
            if (!message) { showStatus('❌ لطفاً متن پیام را وارد کنید.', 'error'); return; }
            if (!groupIds.length) { showStatus('❌ لطفاً حداقل یک گروه مخاطب انتخاب کنید.', 'error'); return; }

            campaignCreateBusy = true;
            var btn = sendNow ? $('#campaign-create-and-send') : $('#campaign-create-btn');
            var status = $('#campaign-create-status');
            btn.text('⏳ در حال ایجاد...').prop('disabled', true);
            status.text('');

            var data = {
                action: 'ezlens_campaign_create',
                nonce: ezlens_auth_ajax.nonce,
                name: name,
                type: type,
                subject: subject,
                message: message,
                file_attachment: fileUrl,
                scheduled_at: $('#campaign-scheduled-at').val(),
                group_ids: JSON.stringify(groupIds)
            };
            if (sendNow) data.send_now = '1';

            $.post(ezlens_auth_ajax.ajax_url, data, function(response) {
                btn.html(sendNow ? '✅ ایجاد و ارسال' : '✅ ایجاد کمپین').prop('disabled', false);
                if (response.success) {
                    var msg = response.data.message || 'کمپین با موفقیت ایجاد شد.';
                    if (response.data.sent !== undefined) {
                        msg = '✅ ارسال شد: ' + response.data.sent + ' از ' + response.data.total;
                    }
                    showStatus('✅ ' + msg, 'success');
                    if (!sendNow) {
                        setTimeout(function() {
                            if (confirm('کمپین ذخیره شد. آیا می‌خواهید به تاریخچه بروید؟')) {
                                window.location.hash = 'history';
                                $('.campaign-tab[data-tab="history"]').click();
                            }
                        }, 1000);
                    } else {
                        setTimeout(function() {
                            window.location.hash = 'history';
                            $('.campaign-tab[data-tab="history"]').click();
                        }, 1500);
                    }
                } else {
                    var err = (response.data && response.data.message) ? response.data.message : (typeof response.data === 'string' ? response.data : 'خطا در ایجاد کمپین');
                    if (response.data && response.data.send_error) err += ' — ' + response.data.send_error;
                    showStatus('❌ ' + err, 'error');
                }
            }).fail(function(xhr) {
                btn.html(sendNow ? '✅ ایجاد و ارسال' : '✅ ایجاد کمپین').prop('disabled', false);
                var msg = xhr.status === 403 ? 'نشست یا دسترسی منقضی شده است.' : 'خطا در ارتباط با سرور';
                showStatus('❌ ' + msg, 'error');
            }).always(function() {
                campaignCreateBusy = false;
                $('#campaign-create-btn, #campaign-create-and-send').prop('disabled', false);
            });
        }

        function showStatus(message, type) {
            var status = $('#campaign-create-status');
            status.html(message);
            status.css('color', type === 'success' ? '#16a34a' : '#dc2626');
            setTimeout(function() {
                status.text('');
            }, 5000);
        }

        $('#campaign-create-btn').on('click', function() { createCampaign(false); });
        $('#campaign-create-and-send').on('click', function() { createCampaign(true); });

        $('#campaign-reset-form').on('click', function() {
            if (!confirm('آیا از پاک کردن فرم اطمینان دارید؟')) return;
            $('#campaign-name').val('');
            $('#campaign-subject').val('');
            $('#campaign-message').val('');
            $('#campaign-file-url').val('');
            $('.campaign-group-check').prop('checked', false);
            updateSelectedGroupsCount();
            $('#campaign-create-status').text('');
        });

        loadGroupsForSelect();
        console.log('✅ تب ایجاد کمپین بارگذاری شد.');
    }

    // ============================================================
    // ۷. تاریخچه
    // ============================================================
    function initHistory() {
        var historyPage=1, historyLimit=20, historyTotal=0;
        function post(data){return $.ajax({url:ezlens_auth_ajax.ajax_url,method:'POST',data:data,dataType:'json',timeout:30000});}
        function loadHistory(){
            var container=$('#history-list-container'); container.html('<p style="color:#94a3b8;">⏳ در حال بارگذاری...</p>');
            post({action:'ezlens_campaign_get_list',nonce:ezlens_auth_ajax.nonce,status:$('#history-status-filter').val(),search:$('#history-search').val(),limit:historyLimit,offset:(historyPage-1)*historyLimit})
            .done(function(r){
                if(!r.success){container.html('<p style="color:#dc2626;">❌ '+escHtml(r.data||'خطا')+'</p>');return;}
                var list=r.data.data||[]; historyTotal=parseInt(r.data.total||0); $('#history-total-count').text('تعداد: '+historyTotal);
                if(!list.length){container.html('<p style="color:#94a3b8;padding:20px;">هیچ کمپینی یافت نشد.</p>');return;}
                var html='<table class="wp-list-table widefat fixed striped"><thead><tr><th>#</th><th>نام</th><th>نوع</th><th>موضوع</th><th>وضعیت</th><th>پیشرفت</th><th>باز شدن</th><th>تاریخ</th><th>عملیات</th></tr></thead><tbody>';
                list.forEach(function(item){
                    var st=item.status, label={sent:'✅ ارسال شده',draft:'📝 پیش‌نویس',scheduled:'⏳ برنامه‌ریزی شده',sending:'🚀 در حال ارسال',paused:'⏸ متوقف',partial:'⚠️ ارسال ناقص',failed:'❌ ناموفق'}[st]||st;
                    var editable=['draft','scheduled','failed','partial','paused'].indexOf(st)>=0;
                    html+='<tr><td>#'+item.id+'</td><td><strong>'+escHtml(item.name)+'</strong></td><td>'+(item.type==='email'?'📧 ایمیل':'📱 پیامک')+'</td><td>'+escHtml(item.subject||'—')+'</td><td><span class="campaign-status status-'+escHtml(st)+'">'+label+'</span></td><td class="campaign-progress" data-id="'+item.id+'">⏳</td><td class="track-stats" data-id="'+item.id+'">⏳</td><td>'+toPersianDate(item.created_at)+'</td><td><div class="campaign-actions">';
                    html+='<button class="button button-small campaign-report-btn" data-id="'+item.id+'">📊 گزارش</button>';
                    if(editable) html+='<button class="button button-small campaign-edit-btn" data-id="'+item.id+'">✏️ ویرایش</button>';
                    html+='<button class="button button-small campaign-duplicate-btn" data-id="'+item.id+'">📋 کپی</button>';
                    if(st==='draft') html+='<button class="button button-small campaign-send-btn" data-id="'+item.id+'">📤 ارسال</button>';
                    if(st==='scheduled'||st==='sending') html+='<button class="button button-small campaign-pause-btn" data-id="'+item.id+'">⏸ توقف</button>';
                    if(st==='paused') html+='<button class="button button-small campaign-resume-btn" data-id="'+item.id+'">▶️ ادامه</button>';
                    if(st==='failed'||st==='partial') html+='<button class="button button-small campaign-retry-btn" data-id="'+item.id+'">🔄 Retry</button>';
                    html+='<button class="button button-small campaign-delete-btn" data-id="'+item.id+'" style="color:#dc2626;border-color:#dc2626;">🗑️</button></div></td></tr>';
                }); html+='</tbody></table>'+renderHistoryPagination(Math.ceil(historyTotal/historyLimit)); container.html(html);
                container.find('.track-stats').each(function(){loadTrackStats($(this).data('id'),$(this));});
                container.find('.campaign-progress').each(function(){loadProgress($(this).data('id'),$(this));});
            }).fail(function(xhr){container.html('<p style="color:#dc2626;">❌ خطا در ارتباط با سرور ('+(xhr.status||'')+')</p>');});
        }
        function loadProgress(id,el){post({action:'ezlens_campaign_report',nonce:ezlens_auth_ajax.nonce,id:id,limit:1,offset:0}).done(function(r){if(!r.success)return;var p=r.data.progress||{},pct=parseFloat(p.percent||0);el.html('<div title="'+p.processed+'/'+p.total+'"><div class="campaign-progress-bar"><i style="width:'+pct+'%"></i></div><small>'+pct+'% ('+p.processed+'/'+p.total+')</small></div>');});}
        function loadTrackStats(id,el){post({action:'ezlens_campaign_get_track_stats',nonce:ezlens_auth_ajax.nonce,campaign_id:id}).done(function(r){if(r.success){el.html(r.data.total?('👁️ '+r.data.unique+' نفر / '+r.data.total+' باز'):'—');}else el.html('—');}).fail(function(){el.html('—');});}
        function renderHistoryPagination(totalPages){if(totalPages<=1)return '';var h='<div class="pagination-wrap"><button class="page-btn" data-page="'+(historyPage-1)+'" '+(historyPage<=1?'disabled':'')+'>‹ قبلی</button>';for(var p=Math.max(1,historyPage-2);p<=Math.min(totalPages,historyPage+2);p++)h+='<button class="page-btn '+(p===historyPage?'active':'')+'" data-page="'+p+'">'+p+'</button>';return h+'<button class="page-btn" data-page="'+(historyPage+1)+'" '+(historyPage>=totalPages?'disabled':'')+'>بعدی ›</button></div>';}
        function closeModal(sel){$(sel).hide();}
        function openEditor(id){
            $('#campaign-editor-status').text('⏳ در حال دریافت اطلاعات...').css('color','#64748b'); $('#campaign-editor-modal').show();
            post({action:'ezlens_campaign_get',nonce:ezlens_auth_ajax.nonce,id:id}).done(function(r){
                if(!r.success){$('#campaign-editor-status').text('❌ '+r.data).css('color','#dc2626');return;}
                var c=r.data.campaign||{}; $('#editor-campaign-id').val(c.id);$('#editor-name').val(c.name||'');$('#editor-type').val(c.type||'email');$('#editor-subject').val(c.subject||'');$('#editor-message').val(c.message||'');$('#editor-file').val(c.file_attachment||'');
                if(c.scheduled_at){var d=new Date(c.scheduled_at.replace(' ','T')); if(!isNaN(d.getTime()))$('#editor-scheduled').val(d.toISOString().slice(0,16));}else $('#editor-scheduled').val('');
                loadEditorGroups(r.data.group_ids||[]); $('#campaign-editor-status').text('');
            });
        }
        function loadEditorGroups(selected){
            post({action:'ezlens_campaign_get_groups',nonce:ezlens_auth_ajax.nonce}).done(function(r){var box=$('#editor-groups-list');if(!r.success){box.html('❌ خطا');return;}var html='';(r.data.groups||[]).forEach(function(g){var chk=selected.map(String).indexOf(String(g.id))>=0?'checked':'';html+='<label><input type="checkbox" class="editor-group-check" value="'+g.id+'" '+chk+'> '+escHtml(g.name)+' <small>'+escHtml(g.description||'')+'</small></label>';});box.html(html||'هیچ گروهی وجود ندارد.');});
        }
        function saveEditor(){
            var id=$('#editor-campaign-id').val(), groups=[];$('.editor-group-check:checked').each(function(){groups.push(parseInt($(this).val()));});
            var btn=$('#campaign-editor-save');btn.prop('disabled',true).text('⏳ در حال ذخیره...');
            post({action:'ezlens_campaign_update',nonce:ezlens_auth_ajax.nonce,id:id,name:$('#editor-name').val(),type:$('#editor-type').val(),subject:$('#editor-subject').val(),message:$('#editor-message').val(),file_attachment:$('#editor-file').val(),scheduled_at:$('#editor-scheduled').val(),group_ids:JSON.stringify(groups)})
            .done(function(r){if(r.success){$('#campaign-editor-status').text('✅ ذخیره شد.').css('color','#16a34a');setTimeout(function(){closeModal('#campaign-editor-modal');loadHistory();},500);}else $('#campaign-editor-status').text('❌ '+(r.data.message||r.data||'خطا')).css('color','#dc2626');})
            .fail(function(){ $('#campaign-editor-status').text('❌ ارتباط با سرور ناموفق بود.').css('color','#dc2626');})
            .always(function(){btn.prop('disabled',false).text('💾 ذخیره تغییرات');});
        }
        function showReport(id){
            $('#campaign-report-modal').show();$('#campaign-report-body').html('⏳ در حال دریافت گزارش...');
            post({action:'ezlens_campaign_report',nonce:ezlens_auth_ajax.nonce,id:id,limit:100,offset:0}).done(function(r){if(!r.success){$('#campaign-report-body').html('❌ '+escHtml(r.data||'خطا'));return;}var p=r.data.progress||{},t=r.data.tracking||{};var h='<div class="ezlens-report-grid">';[['کل',p.total],['موفق',p.sent],['ناموفق',p.failed],['لغو عضویت',p.skipped],['باقی‌مانده',p.pending],['پیشرفت',p.percent+'%'],['Unique Open',t.unique],['Open',t.total]].forEach(function(x){h+='<div class="ezlens-report-stat"><b>'+x[1]+'</b><span>'+x[0]+'</span></div>';});h+='</div><h3>گیرندگان</h3><div style="overflow:auto"><table class="widefat striped"><thead><tr><th>نام</th><th>ایمیل</th><th>موبایل</th><th>وضعیت</th><th>Retry</th><th>علت/پاسخ</th></tr></thead><tbody>';(r.data.recipients||[]).forEach(function(x){h+='<tr><td>'+escHtml(x.name||'—')+'</td><td>'+escHtml(x.email||'—')+'</td><td>'+escHtml(x.phone||'—')+'</td><td>'+escHtml(x.status)+'</td><td>'+x.retry_count+'</td><td style="max-width:360px;word-break:break-word">'+escHtml(x.response||'—')+'</td></tr>';});h+='</tbody></table></div>';$('#campaign-report-body').html(h);});
        }
        $(document).off('click.ezlensHistory').on('click.ezlensHistory','.campaign-edit-btn',function(){openEditor($(this).data('id'));}).on('click.ezlensHistory','.campaign-report-btn',function(){showReport($(this).data('id'));}).on('click.ezlensHistory','.campaign-duplicate-btn',function(){var id=$(this).data('id');post({action:'ezlens_campaign_duplicate',nonce:ezlens_auth_ajax.nonce,id:id}).done(function(r){if(r.success){alert('✅ '+r.data.message);loadHistory();}else alert('❌ '+r.data);});}).on('click.ezlensHistory','.campaign-pause-btn',function(){var id=$(this).data('id');post({action:'ezlens_campaign_pause',nonce:ezlens_auth_ajax.nonce,id:id}).done(function(r){if(r.success)loadHistory();else alert('❌ '+r.data);});}).on('click.ezlensHistory','.campaign-resume-btn',function(){var id=$(this).data('id');post({action:'ezlens_campaign_resume',nonce:ezlens_auth_ajax.nonce,id:id}).done(function(r){if(r.success)loadHistory();else alert('❌ '+r.data);});}).on('click.ezlensHistory','.campaign-retry-btn',function(){var id=$(this).data('id');if(!confirm('فقط ارسال‌های ناموفق دوباره تلاش شوند؟'))return;post({action:'ezlens_campaign_retry_failed',nonce:ezlens_auth_ajax.nonce,id:id}).done(function(r){if(r.success)loadHistory();else alert('❌ '+r.data);});}).on('click.ezlensHistory','.campaign-send-btn',function(){var id=$(this).data('id'),b=$(this);if(!confirm('ارسال این کمپین آغاز شود؟'))return;b.prop('disabled',true).text('⏳...');post({action:'ezlens_campaign_send',nonce:ezlens_auth_ajax.nonce,id:id}).done(function(r){if(r.success){alert('✅ '+r.data.message);loadHistory();}else alert('❌ '+(r.data.message||r.data));}).always(function(){b.prop('disabled',false).text('📤 ارسال');});}).on('click.ezlensHistory','.campaign-delete-btn',function(){var id=$(this).data('id');if(!confirm('حذف کمپین و گزارش‌های آن انجام شود؟'))return;post({action:'ezlens_campaign_delete',nonce:ezlens_auth_ajax.nonce,id:id}).done(function(r){if(r.success)loadHistory();else alert('❌ '+r.data);});}).on('click.ezlensHistory','.page-btn',function(){var pg=parseInt($(this).data('page'));if(pg&&pg!==historyPage){historyPage=pg;loadHistory();}});
        $('#history-refresh').off('click.ezlens').on('click.ezlens',function(){historyPage=1;loadHistory();});$('#history-search-btn').off('click.ezlens').on('click.ezlens',function(){historyPage=1;loadHistory();});$('#history-status-filter').off('change.ezlens').on('change.ezlens',function(){historyPage=1;loadHistory();});$('#history-search').off('keypress.ezlens').on('keypress.ezlens',function(e){if(e.which===13){historyPage=1;loadHistory();}});
        $('#campaign-editor-close,#campaign-editor-cancel,.ezlens-campaign-modal-backdrop,.report-close').off('click.ezlens').on('click.ezlens',function(){closeModal($(this).closest('.ezlens-campaign-modal'));});$('#campaign-editor-save').off('click.ezlens').on('click.ezlens',saveEditor);
        var progressTimer=setInterval(function(){
            var active=false;
            $('.campaign-progress[data-id]').each(function(){
                var el=$(this),id=el.data('id');
                post({action:'ezlens_campaign_report',nonce:ezlens_auth_ajax.nonce,id:id,limit:1,offset:0}).done(function(r){
                    if(!r.success)return;
                    var p=r.data.progress||{},pct=parseFloat(p.percent||0);
                    el.html('<div title="'+p.processed+'/'+p.total+'"><div class="campaign-progress-bar"><i style="width:'+pct+'%"></i></div><small>'+pct+'% ('+p.processed+'/'+p.total+')</small></div>');
                    if(p.pending>0 && p.percent<100) active=true;
                });
            });
        },2500);
        loadHistory();
    }

    // ============================================================
    // ۸. تنظیمات
    // ============================================================
    function initSettings() {
        $('#campaign-save-settings').off('click.ezlensCampaignSettings').on('click.ezlensCampaignSettings', function(e) {
            e.preventDefault();
            var btn = $(this);
            var status = $('#campaign-settings-status');
            if (btn.data('busy')) return;
            btn.data('busy', true).text('⏳ در حال ذخیره...').prop('disabled', true);
            status.show().text('در حال ذخیره...').css('color', '#d97706');

            var data = {
                action: 'ezlens_save_settings_ajax',
                nonce: ezlens_auth_ajax.nonce,
                settings_tab: 'campaign'
            };
            $('#campaignContent input, #campaignContent select, #campaignContent textarea').each(function(){
                var el=$(this), name=el.attr('name');
                if(!name) return;
                data[name]=el.is(':checkbox')?(el.is(':checked')?'1':'0'):el.val();
            });

            $.post(ezlens_auth_ajax.ajax_url, data, function(r){
                if(r.success){
                    status.text('✅ ' + (r.data.message||'تنظیمات با موفقیت ذخیره شد.')).css('color','#16a34a');
                } else {
                    var msg=(r.data&&r.data.message)?r.data.message:(typeof r.data==='string'?r.data:'خطا در ذخیره تنظیمات');
                    status.text('❌ '+msg).css('color','#dc2626');
                }
            }).fail(function(xhr){
                status.text('❌ ' + (xhr.status===403?'نشست یا دسترسی منقضی شده است.':'خطا در ارتباط با سرور')).css('color','#dc2626');
            }).always(function(){
                btn.data('busy',false).text('💾 ذخیره تنظیمات').prop('disabled',false);
            });
        });
        console.log('✅ تب تنظیمات کمپینگ بارگذاری شد.');
    }

    console.log('✅ EzLens Campaign Scripts Loaded (نسخه 5.2.0)');
});