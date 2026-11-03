<?php $metrics = $dashboard['metrics'] ?? []; ?>
<section class="hero hero--compact">
    <div class="hero__content">
        <p class="eyebrow">Boshqaruv paneli</p>
        <h1>Moderatsiya, o'sish va platforma signalini bir joyda kuzating.</h1>
        <p class="hero__lead">
            Bu panel yangi foydalanuvchilar, nashr etilgan g'oyalar va oy reytingi bo'yicha
            tez qaror qabul qilish uchun yig'ilgan.
        </p>
    </div>

    <div class="hero__panel">
        <article class="contest-card">
            <p class="eyebrow">Joriy oy</p>
            <h2>Top g'oya holati</h2>
            <?php if (!empty($contest['leader'])): ?>
                <strong><?= e($contest['leader']['title']) ?></strong>
                <p><?= e($contest['leader']['author_name']) ?> | <?= e($contest['leader']['sector']) ?></p>
                <p>
                    Ball <?= (int) $contest['leader']['score'] ?>
                    | Izoh <?= (int) $contest['leader']['comment_count'] ?>
                    | Validatsiya <?= (int) $contest['leader']['validation_count'] ?>
                </p>
            <?php else: ?>
                <p>Bu oy uchun hali lider yo'q.</p>
            <?php endif; ?>
        </article>
    </div>
</section>

<section class="metric-grid metric-grid--dashboard">
    <article class="metric-card">
        <span class="metric-card__label">Foydalanuvchilar</span>
        <strong><?= (int) ($metrics['total_users'] ?? 0) ?></strong>
    </article>
    <article class="metric-card">
        <span class="metric-card__label">Investorlar</span>
        <strong><?= (int) ($metrics['investors'] ?? 0) ?></strong>
    </article>
    <article class="metric-card">
        <span class="metric-card__label">Mentorlar</span>
        <strong><?= (int) ($metrics['mentors'] ?? 0) ?></strong>
    </article>
    <article class="metric-card">
        <span class="metric-card__label">Nashr etilgan g'oyalar</span>
        <strong><?= (int) ($metrics['published_ideas'] ?? 0) ?></strong>
    </article>
    <article class="metric-card">
        <span class="metric-card__label">Arxivlangan g'oyalar</span>
        <strong><?= (int) ($metrics['archived_ideas'] ?? 0) ?></strong>
    </article>
</section>

<section class="detail-grid">
    <article class="panel">
        <div class="section-head">
            <div>
                <p class="eyebrow">Yangi foydalanuvchilar</p>
                <h2>Rol boshqaruvi</h2>
            </div>
        </div>

        <div class="admin-list">
            <?php foreach ($dashboard['recent_users'] as $member): ?>
                <form method="post" class="admin-row">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="form_action" value="admin_user_role">
                    <input type="hidden" name="redirect_to" value="/?page=admin">
                    <input type="hidden" name="user_id" value="<?= (int) $member['id'] ?>">

                    <div class="section-copy">
                        <strong><?= e($member['name']) ?></strong>
                        <small><?= e($member['email']) ?> | <?= e(time_ago($member['created_at'])) ?></small>
                    </div>

                    <div class="admin-row__actions">
                        <select name="role">
                            <?php foreach (['founder', 'investor', 'mentor', 'admin'] as $role): ?>
                                <option value="<?= e($role) ?>" <?= $member['role'] === $role ? 'selected' : '' ?>>
                                    <?= e(role_label($role)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn--ghost" type="submit">Saqlash</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel">
        <div class="section-head">
            <div>
                <p class="eyebrow">Moderatsiya</p>
                <h2>So'nggi g'oyalar</h2>
            </div>
        </div>

        <div class="admin-list">
            <?php foreach ($dashboard['recent_ideas'] as $idea): ?>
                <form method="post" class="admin-row">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="form_action" value="admin_idea_status">
                    <input type="hidden" name="redirect_to" value="/?page=admin">
                    <input type="hidden" name="idea_id" value="<?= (int) $idea['id'] ?>">

                    <div class="section-copy">
                        <strong><?= e($idea['title']) ?></strong>
                        <small><?= e($idea['author_name']) ?> | <?= e($idea['sector']) ?> | Ball <?= (int) $idea['score'] ?></small>
                    </div>

                    <div class="admin-row__actions">
                        <select name="status">
                            <option value="published" <?= $idea['status'] === 'published' ? 'selected' : '' ?>>Nashr etilgan</option>
                            <option value="archived" <?= $idea['status'] === 'archived' ? 'selected' : '' ?>>Arxivlangan</option>
                        </select>
                        <button class="btn btn--ghost" type="submit">Yangilash</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </article>
</section>
