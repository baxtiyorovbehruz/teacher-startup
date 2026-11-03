<article class="idea-card" data-idea-id="<?= (int) $idea['id'] ?>">
    <?php $coverUrl = idea_cover_url($idea['cover_image'] ?? null); ?>
    <?php if ($coverUrl): ?>
        <a href="/?page=idea&id=<?= (int) $idea['id'] ?>">
            <img src="<?= e($coverUrl) ?>" alt="<?= e($idea['title']) ?>"
                 class="idea-card__cover" loading="lazy">
        </a>
    <?php endif; ?>
    <div class="idea-card__topline">
        <div class="chip-row">
            <span class="chip"><?= e($idea['sector']) ?></span>
            <span class="chip"><?= e(stage_label($idea['stage'])) ?></span>
        </div>
        <span class="score-badge"><span data-score-count><?= (int) $idea['score'] ?></span> ball</span>
    </div>

    <div class="idea-card__header">
        <div>
            <h3 class="idea-card__title">
                <a href="/?page=idea&id=<?= (int) $idea['id'] ?>"><?= e($idea['title']) ?></a>
            </h3>
            <p class="idea-card__author">
                <a href="/?page=profile&id=<?= (int) $idea['user_id'] ?>"><?= e($idea['author_name']) ?></a>
                <span class="role-pill"><?= e(role_label($idea['author_role'])) ?></span>
            </p>
        </div>
        <a class="idea-card__link" href="/?page=idea&id=<?= (int) $idea['id'] ?>">Batafsil</a>
    </div>

    <p class="idea-card__summary"><?= e(excerpt($idea['description'])) ?></p>

    <div class="chip-row">
        <?php if (!empty($idea['location'])): ?>
            <span class="chip"><?= e($idea['location']) ?></span>
        <?php endif; ?>
        <span class="chip chip--accent"><?= !empty($idea['investment_needed']) ? 'Investitsiya kerak' : 'O\'z kuchiga tayanib' ?></span>
        <?php if (!empty($idea['collaboration_open'])): ?>
            <span class="chip chip--accent">Hamkor qidirmoqda</span>
        <?php endif; ?>
    </div>

    <?php if (!empty($idea['seeking_roles'])): ?>
        <div class="chip-row">
            <?php foreach (split_csv_tags($idea['seeking_roles']) as $roleNeed): ?>
                <span class="chip"><?= e($roleNeed) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="idea-card__insight">
        <div class="idea-card__insight-head">
            <strong>Mini bozor signali</strong>
            <span data-validation-total><?= (int) $idea['validation_count'] ?></span>
        </div>

        <div class="signal-list">
            <span><?= e(validation_signal_label('use')) ?> <strong data-signal-count="use"><?= (int) $idea['use_count'] ?></strong></span>
            <span><?= e(validation_signal_label('pay')) ?> <strong data-signal-count="pay"><?= (int) $idea['pay_count'] ?></strong></span>
            <span><?= e(validation_signal_label('mentor')) ?> <strong data-signal-count="mentor"><?= (int) $idea['mentor_count'] ?></strong></span>
            <span><?= e(validation_signal_label('invest')) ?> <strong data-signal-count="invest"><?= (int) $idea['invest_count'] ?></strong></span>
        </div>
    </div>

    <div class="idea-card__footer">
        <div class="idea-card__meta">
            <span><?= e(time_ago($idea['created_at'])) ?></span>
            <span><span data-comment-count><?= (int) $idea['comment_count'] ?></span> izoh</span>
        </div>

        <div class="idea-actions">
            <button
                type="button"
                class="action-btn js-vote <?= (int) $idea['user_vote'] === 1 ? 'is-active' : '' ?>"
                data-idea-id="<?= (int) $idea['id'] ?>"
                data-vote="1"
            >
                Yoqdi <span data-like-count><?= (int) $idea['likes'] ?></span>
            </button>

            <button
                type="button"
                class="action-btn action-btn--muted js-vote <?= (int) $idea['user_vote'] === -1 ? 'is-active' : '' ?>"
                data-idea-id="<?= (int) $idea['id'] ?>"
                data-vote="-1"
            >
                Tanqid <span data-dislike-count><?= (int) $idea['dislikes'] ?></span>
            </button>

            <a class="action-btn action-btn--link" href="/?page=idea&id=<?= (int) $idea['id'] ?>#comments">
                Izoh <span data-comment-count><?= (int) $idea['comment_count'] ?></span>
            </a>

            <button
                type="button"
                class="action-btn action-btn--link js-share"
                data-share-title="<?= e($idea['title']) ?>"
                data-share-url="<?= e(base_url('?page=idea&id=' . (int) $idea['id'])) ?>"
            >
                Ulashish
            </button>
        </div>
    </div>
</article>
