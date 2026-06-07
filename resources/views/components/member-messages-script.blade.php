<script>
(function () {
    var modal = document.getElementById('member-message-modal');
    if (!modal) {
        return;
    }

    var threadsUrl = @json(route('collection.messages.threads'));
    var storeUrl = @json(route('collection.messages.store'));
    var csrfToken = @json(csrf_token());

    var threadListEl = modal.querySelector('[data-thread-list]');
    var threadListEmptyEl = modal.querySelector('[data-thread-list-empty]');
    var conversationEmptyEl = modal.querySelector('[data-conversation-empty]');
    var conversationActiveEl = modal.querySelector('[data-conversation-active]');
    var conversationSubjectEl = modal.querySelector('[data-conversation-subject]');
    var messageLogEl = modal.querySelector('[data-message-log]');
    var messageForm = modal.querySelector('[data-message-form]');
    var messageInput = modal.querySelector('[data-message-input]');

    var activeThreadId = null;
    var pendingOwnedItemId = null;
    var pendingSellerUsername = null;
    var pendingPlateSummary = null;

    function openModal() {
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('member-message-modal-open');
        loadThreads();
    }

    function closeModal() {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('member-message-modal-open');
    }

    function threadShowUrl(threadId) {
        return @json(url('/collection/messages/threads')) + '/' + threadId;
    }

    function renderThreadList(threads) {
        if (!threadListEl) {
            return;
        }

        threadListEl.innerHTML = '';

        if (!threads.length) {
            if (threadListEmptyEl) {
                threadListEmptyEl.hidden = false;
            }
            return;
        }

        if (threadListEmptyEl) {
            threadListEmptyEl.hidden = true;
        }

        threads.forEach(function (thread) {
            var li = document.createElement('li');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'member-message-thread-btn';
            btn.setAttribute('data-thread-id', String(thread.id));
            if (thread.id === activeThreadId) {
                btn.classList.add('is-active');
            }

            var title = document.createElement('span');
            title.className = 'member-message-thread-btn-title';
            title.textContent = thread.other_username || 'Member';

            var preview = document.createElement('span');
            preview.className = 'member-message-thread-btn-preview';
            preview.textContent = thread.plate_summary || thread.listing_label || '';

            btn.appendChild(title);
            btn.appendChild(preview);

            if (thread.unread_count > 0) {
                var badge = document.createElement('span');
                badge.className = 'member-message-thread-unread';
                badge.textContent = String(thread.unread_count);
                btn.appendChild(badge);
            }

            btn.addEventListener('click', function () {
                openThread(thread.id);
            });

            li.appendChild(btn);
            threadListEl.appendChild(li);
        });
    }

    function loadThreads() {
        fetch(threadsUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                renderThreadList(data.threads || []);

                if (pendingOwnedItemId) {
                    startNewConversation();
                } else if (activeThreadId) {
                    openThread(activeThreadId);
                }
            });
    }

    function showConversation(subject) {
        if (conversationEmptyEl) {
            conversationEmptyEl.hidden = true;
        }
        if (conversationActiveEl) {
            conversationActiveEl.hidden = false;
        }
        if (conversationSubjectEl) {
            conversationSubjectEl.textContent = subject || '';
        }
    }

    function renderMessages(messages) {
        if (!messageLogEl) {
            return;
        }

        messageLogEl.innerHTML = '';

        messages.forEach(function (message) {
            var item = document.createElement('div');
            item.className = 'member-message-item' + (message.is_mine ? ' member-message-item--mine' : ' member-message-item--theirs');
            item.textContent = message.body;
            messageLogEl.appendChild(item);
        });

        messageLogEl.scrollTop = messageLogEl.scrollHeight;
    }

    function openThread(threadId) {
        activeThreadId = threadId;
        pendingOwnedItemId = null;

        fetch(threadShowUrl(threadId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                var thread = data.thread || {};
                var subject = (thread.plate_summary || 'Listing') + ' — with ' + (thread.other_username || 'member');
                showConversation(subject);
                renderMessages(data.messages || []);
                loadThreads();
            });
    }

    function startNewConversation() {
        if (!pendingOwnedItemId) {
            return;
        }

        var subject = (pendingPlateSummary || 'Listing') + ' — message ' + (pendingSellerUsername || 'seller');
        showConversation(subject);
        renderMessages([]);

        if (messageInput) {
            messageInput.focus();
        }
    }

    function sendMessage(body) {
        var payload = { body: body };

        if (activeThreadId) {
            payload.thread_id = activeThreadId;
        } else if (pendingOwnedItemId) {
            payload.owned_item_id = pendingOwnedItemId;
        } else {
            return Promise.reject(new Error('No conversation selected.'));
        }

        return fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Send failed.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                activeThreadId = data.thread_id;
                pendingOwnedItemId = null;
                pendingSellerUsername = null;
                pendingPlateSummary = null;
                if (messageInput) {
                    messageInput.value = '';
                }
                openThread(data.thread_id);
            });
    }

    document.querySelectorAll('[data-member-messages-open]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            pendingOwnedItemId = trigger.getAttribute('data-owned-item-id') || null;
            pendingSellerUsername = trigger.getAttribute('data-seller-username') || null;
            pendingPlateSummary = trigger.getAttribute('data-plate-summary') || null;
            activeThreadId = null;
            openModal();
        });
    });

    modal.querySelectorAll('[data-member-messages-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    if (messageForm) {
        messageForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var body = messageInput ? messageInput.value.trim() : '';
            if (!body) {
                return;
            }

            sendMessage(body).catch(function (err) {
                window.alert(err.message || 'Could not send message.');
            });
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
})();
</script>
