<?php
$viewer = current_user();
$canEditIdea = $viewer && ($viewer['role'] === 'admin' || (int) $viewer['id'] === (int) $idea['user_id']);
?>
<article class="detail-card" data-idea-id="<?= (int) $idea['id'] ?>">
    <div class="detail-card__masthead">
        <div class="section-copy">
            <p class="eyebrow"><?= e($idea['sector']) ?> | <?= e(stage_label($idea['stage'])) ?></p>
            <h1><?= e($idea['title']) ?></h1>
            <p class="detail-card__author">
                <a href="/?page=profile&id=<?= (int) $idea['user_id'] ?>"><?= e($idea['author_name']) ?></a>
                <span class="role-pill"><?= e(role_label($idea['author_role'])) ?></span>
                <span><?= e(time_ago($idea['created_at'])) ?></span>
                <?php if (!empty($idea['updated_at']) && $idea['updated_at'] !== $idea['created_at']): ?>
                    <span style="opacity:0.6">(yangilangan: <?= e(time_ago($idea['updated_at'])) ?>)</span>
                <?php endif; ?>
            </p>
        </div>
        <?php if ($canEditIdea): ?>
            <div class="chip-row" style="margin-top: 0.5rem;">
                <a href="/?page=edit&id=<?= (int) $idea['id'] ?>" class="btn btn--ghost btn--sm">Tahrirlash</a>
            </div>
        <?php endif; ?>

        <div class="chip-row">
            <span class="chip"><?= !empty($idea['investment_needed']) ? 'Investitsiya kerak' : 'Investitsiya shart emas' ?></span>
            <span class="chip chip--accent"><?= e(format_budget_range($idea['budget_from'] !== null ? (float) $idea['budget_from'] : null, $idea['budget_to'] !== null ? (float) $idea['budget_to'] : null)) ?></span>
            <?php if (!empty($idea['collaboration_open'])): ?>
                <span class="chip chip--accent">Hamkor qidiryapti</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-overview">
        <article class="metric-card">
            <span class="metric-card__label">Ball</span>
            <strong data-score-count><?= (int) $idea['score'] ?></strong>
        </article>
        <article class="metric-card">
            <span class="metric-card__label">Yoqtirish</span>
            <strong data-like-count><?= (int) $idea['likes'] ?></strong>
        </article>
        <article class="metric-card">
            <span class="metric-card__label">Izoh</span>
            <strong data-comment-count><?= (int) $idea['comment_count'] ?></strong>
        </article>
        <article class="metric-card">
            <span class="metric-card__label">Validatsiya</span>
            <strong data-validation-total><?= (int) $idea['validation_count'] ?></strong>
        </article>
    </div>

    <div class="detail-grid">
        <section class="panel">
            <div class="detail-section">
                <div class="detail-section__head">
                    <h2>Tavsif</h2>
                </div>
                <p><?= nl2br(e($idea['description'])) ?></p>
            </div>

            <?php if (!empty($idea['target_audience'])): ?>
                <div class="detail-section">
                    <div class="detail-section__head">
                        <h3>Maqsadli auditoriya</h3>
                    </div>
                    <p><?= e($idea['target_audience']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($idea['market_note'])): ?>
                <div class="detail-section">
                    <div class="detail-section__head">
                        <h3>Mini bozor tahlili</h3>
                    </div>
                    <p><?= nl2br(e($idea['market_note'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($idea['monetization'])): ?>
                <div class="detail-section">
                    <div class="detail-section__head">
                        <h3>Monetizatsiya</h3>
                    </div>
                    <p><?= e($idea['monetization']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($idea['seeking_roles'])): ?>
                <div class="detail-section">
                    <div class="detail-section__head">
                        <h3>Qidirilayotgan rollar</h3>
                    </div>
                    <div class="chip-row">
                        <?php foreach (split_csv_tags($idea['seeking_roles']) as $roleNeed): ?>
                            <span class="chip"><?= e($roleNeed) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <aside class="stack-panel">
            <section class="panel panel--sticky">
                <div class="section-copy">
                    <p class="eyebrow">Reaksiya</p>
                    <h2>Hamjamiyat javobi</h2>
                </div>

                <div class="idea-actions">
                    <button type="button" class="action-btn js-vote <?= (int) $idea['user_vote'] === 1 ? 'is-active' : '' ?>" data-idea-id="<?= (int) $idea['id'] ?>" data-vote="1">
                        Yoqdi <span data-like-count><?= (int) $idea['likes'] ?></span>
                    </button>
                    <button type="button" class="action-btn action-btn--muted js-vote <?= (int) $idea['user_vote'] === -1 ? 'is-active' : '' ?>" data-idea-id="<?= (int) $idea['id'] ?>" data-vote="-1">
                        Tanqid <span data-dislike-count><?= (int) $idea['dislikes'] ?></span>
                    </button>
                    <button type="button" class="action-btn action-btn--link js-share" data-share-title="<?= e($idea['title']) ?>" data-share-url="<?= e(base_url('?page=idea&id=' . (int) $idea['id'])) ?>">
                        Ulashish
                    </button>
                </div>
            </section>

            <section class="panel panel--muted">
                <div class="section-copy">
                    <p class="eyebrow">Validatsiya</p>
                    <h2>Bozor signallari</h2>
                    <p class="panel-note">Signal yig'ish uchun bitta variant tanlanadi.</p>
                </div>

                <div class="signal-grid">
                    <?php foreach (['use', 'pay', 'mentor', 'invest'] as $signal): ?>
                        <button type="button" class="signal-btn js-validation <?= ($idea['user_signal'] ?? '') === $signal ? 'is-active' : '' ?>" data-idea-id="<?= (int) $idea['id'] ?>" data-signal="<?= e($signal) ?>">
                            <span><?= e(validation_signal_label($signal)) ?></span>
                            <strong data-signal-count="<?= e($signal) ?>"><?= (int) ($idea[$signal . '_count'] ?? 0) ?></strong>
                        </button>
                    <?php endforeach; ?>
                </div>
            </section>
        </aside>
    </div>
</article>

<section id="comments" class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">Izohlar</p>
            <h2>Fikrlar va takliflar</h2>
        </div>
    </div>

    <?php if (current_user()): ?>
        <form class="comment-form js-comment-form">
            <textarea name="content" rows="4" placeholder="Kuchli fikr, tanqidiy mulohaza yoki hamkorlik taklifini yozing" required></textarea>
            <input type="hidden" name="idea_id" value="<?= (int) $idea['id'] ?>">
            <button class="btn btn--primary" type="submit">Izoh yuborish</button>
        </form>
    <?php else: ?>
        <div class="empty-state empty-state--compact">
            <p>Izoh yozish uchun <a href="/?page=login">tizimga kiring</a>.</p>
        </div>
    <?php endif; ?>

    <div class="comment-list" id="comments-list">
        <?php if ($comments === []): ?>
            <article class="empty-state empty-state--compact">
                <p>Hali izoh yo'q. Birinchi bo'lib fikr bildiring.</p>
            </article>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <article class="comment-card">
                    <div class="comment-card__head">
                        <strong><?= e($comment['name']) ?></strong>
                        <span><?= e(role_label($comment['role'])) ?> | <?= e(time_ago($comment['created_at'])) ?></span>
                    </div>
                    <p><?= nl2br(e($comment['content'])) ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
