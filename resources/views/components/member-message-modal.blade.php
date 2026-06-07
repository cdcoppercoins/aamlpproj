<div class="member-message-modal"
     id="member-message-modal"
     hidden
     aria-hidden="true"
     role="dialog"
     aria-modal="true"
     aria-labelledby="member-message-modal-title">
    <div class="member-message-modal-backdrop" data-member-messages-close></div>
    <div class="member-message-modal-panel">
        <header class="member-message-modal-header">
            <h2 class="member-message-modal-title" id="member-message-modal-title">Member messages</h2>
            <button type="button" class="member-message-modal-close" data-member-messages-close aria-label="Close messages">&times;</button>
        </header>

        <div class="member-message-modal-layout">
            <aside class="member-message-thread-list" aria-label="Conversations">
                <p class="member-message-thread-list-empty" data-thread-list-empty hidden>No conversations yet.</p>
                <ul class="member-message-thread-items" data-thread-list></ul>
            </aside>

            <section class="member-message-conversation" aria-label="Conversation">
                <div class="member-message-conversation-empty" data-conversation-empty>
                    Select a conversation or contact a seller from the marketplace.
                </div>
                <div class="member-message-conversation-active" data-conversation-active hidden>
                    <p class="member-message-conversation-subject" data-conversation-subject></p>
                    <div class="member-message-log" data-message-log role="log" aria-live="polite"></div>
                    <form class="member-message-compose" data-message-form>
                        <label class="member-message-compose-label">
                            <span class="auth-label">Your message</span>
                            <textarea name="body"
                                      rows="4"
                                      maxlength="5000"
                                      required
                                      data-message-input
                                      placeholder="Ask about price, condition, or a trade…"></textarea>
                        </label>
                        <p class="member-message-compose-actions">
                            <button type="submit" class="home-primary-btn">Send</button>
                        </p>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>
