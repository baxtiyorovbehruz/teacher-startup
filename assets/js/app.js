const csrfToken = document.body?.dataset.csrf || window.APP_CONFIG?.csrfToken || '';

function isOnline() {
    return typeof navigator === 'undefined' ? true : navigator.onLine;
}

function ensureToastStack() {
    let stack = document.getElementById('toast-stack');

    if (stack) {
        return stack;
    }

    stack = document.createElement('div');
    stack.id = 'toast-stack';
    stack.className = 'toast-stack';
    stack.setAttribute('aria-live', 'polite');
    stack.setAttribute('aria-atomic', 'true');
    document.body.appendChild(stack);

    return stack;
}

function showToast(message, type = 'error') {
    if (!message) {
        return;
    }

    const stack = ensureToastStack();
    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.textContent = message;
    stack.appendChild(toast);

    window.setTimeout(() => {
        toast.remove();
    }, 3200);
}

function syncOfflineSensitiveControls(online = isOnline()) {
    document.querySelectorAll('.js-vote, .js-validation, form[method="post"] button[type="submit"]').forEach((node) => {
        node.classList.toggle('is-offline-disabled', !online);
        node.setAttribute('aria-disabled', online ? 'false' : 'true');

        if (online) {
            node.removeAttribute('title');
            return;
        }

        node.setAttribute('title', "Internet yo'q. Amal bajarilishi uchun qayta ulaning.");
    });
}

function syncNetworkState() {
    const online = isOnline();

    document.body.classList.toggle('is-offline', !online);
    document.body.dataset.network = online ? 'online' : 'offline';
    syncOfflineSensitiveControls(online);

    if (!online) {
        restoreFeedSnapshot();
    } else {
        const offlineSection = document.getElementById('offline-snapshot');
        if (offlineSection) {
            offlineSection.hidden = true;
        }
    }
}

async function postJson(url, payload) {
    if (!isOnline()) {
        throw new Error("Internet yo'q. Amalni bajarish uchun qayta ulaning.");
    }

    let response;

    try {
        response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
            },
            body: JSON.stringify(payload),
        });
    } catch (error) {
        throw new Error("Internet aloqasi uzildi. Qayta urinib ko'ring.");
    }

    let data;

    try {
        data = await response.json();
    } catch (error) {
        throw new Error("Server javobini o'qib bo'lmadi.");
    }

    if (!response.ok || !data.success) {
        throw new Error(data.message || "So'rov muvaffaqiyatsiz tugadi.");
    }

    return data;
}

function updateIdeaCards(ideaId, summary) {
    document.querySelectorAll(`[data-idea-id="${ideaId}"]`).forEach((card) => {
        const likes = card.querySelector('[data-like-count]');
        const dislikes = card.querySelector('[data-dislike-count]');
        const score = card.querySelector('[data-score-count]');

        if (likes) likes.textContent = summary.likes;
        if (dislikes) dislikes.textContent = summary.dislikes;
        if (score) score.textContent = summary.score;

        card.querySelectorAll('.js-vote').forEach((button) => {
            button.classList.toggle('is-active', Number(button.dataset.vote) === Number(summary.user_vote));
        });
    });

    cacheFeedSnapshot();
}

function updateValidationBlocks(ideaId, summary) {
    document.querySelectorAll(`[data-idea-id="${ideaId}"]`).forEach((card) => {
        const total = card.querySelector('[data-validation-total]');

        if (total) {
            total.textContent = summary.validation_count;
        }

        ['use', 'pay', 'mentor', 'invest'].forEach((signal) => {
            const signalCount = card.querySelector(`[data-signal-count="${signal}"]`);
            if (signalCount) {
                signalCount.textContent = summary[`${signal}_count`];
            }
        });

        card.querySelectorAll('.js-validation').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.signal === summary.user_signal);
        });
    });

    cacheFeedSnapshot();
}

function escapeHtml(value) {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderComment(comment) {
    const roleMap = {
        founder: 'Tadbirkor',
        investor: 'Investor',
        mentor: 'Mentor',
        admin: 'Boshqaruv',
    };

    return `
        <article class="comment-card">
            <div class="comment-card__head">
                <strong>${escapeHtml(comment.name)}</strong>
                <span>${escapeHtml(roleMap[comment.role] || comment.role)} - Hozirgina</span>
            </div>
            <p>${escapeHtml(comment.content).replace(/\n/g, '<br>')}</p>
        </article>
    `;
}

function setupVotes() {
    document.querySelectorAll('.js-vote').forEach((button) => {
        button.addEventListener('click', async () => {
            const ideaId = button.dataset.ideaId;
            const vote = Number(button.dataset.vote);

            try {
                const data = await postJson('/api.php?action=vote', { idea_id: ideaId, vote });
                updateIdeaCards(ideaId, data.summary);
            } catch (error) {
                showToast(error.message, 'error');
            }
        });
    });
}

function setupComments() {
    document.querySelectorAll('.js-comment-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const textarea = form.querySelector('textarea[name="content"]');
            const ideaId = form.querySelector('input[name="idea_id"]')?.value;

            try {
                const data = await postJson('/api.php?action=comment', {
                    idea_id: ideaId,
                    content: textarea.value,
                });

                const list = document.getElementById('comments-list');
                if (list) {
                    const empty = list.querySelector('.empty-state');
                    if (empty) {
                        empty.remove();
                    }
                    list.insertAdjacentHTML('afterbegin', renderComment(data.comment));
                }

                document.querySelectorAll(`[data-idea-id="${ideaId}"] [data-comment-count]`).forEach((node) => {
                    node.textContent = data.comment_count;
                });

                textarea.value = '';
                cacheFeedSnapshot();
                showToast('Izoh yuborildi.', 'success');
            } catch (error) {
                showToast(error.message, 'error');
            }
        });
    });
}

function setupValidation() {
    document.querySelectorAll('.js-validation').forEach((button) => {
        button.addEventListener('click', async () => {
            const ideaId = button.dataset.ideaId;
            const signal = button.dataset.signal;

            try {
                const data = await postJson('/api.php?action=validate', { idea_id: ideaId, signal });
                updateValidationBlocks(ideaId, data.summary);
                showToast('Validatsiya signali yangilandi.', 'success');
            } catch (error) {
                showToast(error.message, 'error');
            }
        });
    });
}

function setupShare() {
    document.querySelectorAll('.js-share').forEach((button) => {
        button.addEventListener('click', async () => {
            const title = button.dataset.shareTitle;
            const url = button.dataset.shareUrl;

            try {
                if (navigator.share) {
                    await navigator.share({ title, url });
                    return;
                }

                if (navigator.clipboard) {
                    await navigator.clipboard.writeText(url);
                    showToast('Link nusxalandi.', 'success');
                    return;
                }

                prompt('Linkni nusxalang:', url);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    showToast('Ulashish amalga oshmadi.', 'error');
                }
            }
        });
    });
}

function setupPostForms() {
    document.querySelectorAll('form[method="post"]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (isOnline()) {
                return;
            }

            event.preventDefault();

            if (form.hasAttribute('data-draft-key')) {
                showToast("Internet yo'q. Draft saqlanadi, yuborish esa onlayn holatda ishlaydi.", 'error');
                return;
            }

            showToast("Internet yo'q. Ushbu amalni bajarish uchun qayta ulaning.", 'error');
        });
    });
}

function setupDraftForms() {
    document.querySelectorAll('[data-draft-key]').forEach((form) => {
        const key = `tadbirkor:${form.dataset.draftKey}`;
        const fields = form.querySelectorAll('input:not([type="hidden"]), textarea, select');

        try {
            const saved = JSON.parse(localStorage.getItem(key) || '{}');
            fields.forEach((field) => {
                if (field.type === 'checkbox') {
                    field.checked = Boolean(saved[field.name]) || field.checked;
                } else if (!field.value && saved[field.name]) {
                    field.value = saved[field.name];
                }
            });
        } catch (error) {
            console.warn('Draft restore xatosi', error);
        }

        fields.forEach((field) => {
            field.addEventListener('input', () => {
                const snapshot = {};
                fields.forEach((item) => {
                    snapshot[item.name] = item.type === 'checkbox' ? item.checked : item.value;
                });
                localStorage.setItem(key, JSON.stringify(snapshot));
            });
        });

        form.addEventListener('submit', () => {
            localStorage.removeItem(key);
        });
    });
}

function cacheFeedSnapshot() {
    const feed = document.querySelector('[data-feed-snapshot]');
    if (feed) {
        localStorage.setItem('tadbirkor:feed-snapshot', feed.innerHTML);
        localStorage.setItem('tadbirkor:feed-snapshot-time', new Date().toISOString());
    }
}

function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
}

function restoreFeedSnapshot() {
    if (isOnline()) {
        return;
    }

    const snapshot = localStorage.getItem('tadbirkor:feed-snapshot');
    if (!snapshot) {
        return;
    }

    const feed = document.querySelector('[data-feed-snapshot]');
    const hasLiveCards = Boolean(feed?.querySelector('[data-idea-id]'));

    if (feed && !hasLiveCards) {
        feed.innerHTML = snapshot;
    }

    const offlineFeed = document.querySelector('[data-offline-feed]');
    const offlineSection = document.getElementById('offline-snapshot');
    const timeNode = document.querySelector('[data-offline-time]');
    const savedAt = localStorage.getItem('tadbirkor:feed-snapshot-time');

    if (!offlineFeed || !offlineSection || hasLiveCards) {
        return;
    }

    offlineFeed.innerHTML = snapshot;
    offlineSection.hidden = false;

    if (timeNode && savedAt) {
        const date = new Date(savedAt);
        timeNode.textContent = `Saqlangan: ${date.toLocaleString('uz-UZ')}`;
    }
}

window.addEventListener('online', syncNetworkState);
window.addEventListener('offline', syncNetworkState);

document.addEventListener('DOMContentLoaded', () => {
    syncNetworkState();
    setupVotes();
    setupComments();
    setupValidation();
    setupShare();
    setupPostForms();
    setupDraftForms();
    cacheFeedSnapshot();
    restoreFeedSnapshot();
    registerServiceWorker();
});
