<?php $viewer = current_user(); $isOwner = $viewer && (int) $viewer['id'] === (int) $profile['id']; ?>
<section class="hero hero--profile">
    <div class="hero__content">
        <div style="display:flex; align-items:center; gap:1rem; margin-bottom:0.75rem;">
            <img src="<?= e(avatar_url($profile['avatar'] ?? null)) ?>" alt="<?= e($profile['name']) ?>"
                 style="width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid var(--brand-soft);">
            <div>
                <p class="eyebrow" style="margin:0"><?= e(role_label($profile['role'])) ?></p>
                <h1 style="margin:0"><?= e($profile['name']) ?></h1>
            </div>
        </div>
        <p class="hero__lead"><?= e($profile['bio'] ?: 'Bu foydalanuvchi hali bio yozmagan.') ?></p>

        <div class="chip-row">
            <?php if (!empty($profile['location'])): ?>
                <span class="chip"><?= e($profile['location']) ?></span>
            <?php endif; ?>
            <?php if (!empty($profile['expertise'])): ?>
                <span class="chip chip--accent"><?= e($profile['expertise']) ?></span>
            <?php endif; ?>
            <span class="chip"><?= e(collaboration_status_label($profile['collaboration_status'] ?? 'available')) ?></span>
        </div>

        <div class="hero__actions">
            <?php if (!empty($profile['contact_link'])): ?>
                <a class="btn btn--primary" href="<?= e(external_link($profile['contact_link'])) ?>" target="_blank" rel="noopener noreferrer">Bog'lanish</a>
            <?php endif; ?>
            <?php if ($isOwner): ?>
                <a class="btn btn--ghost" href="#profile-settings">Profilni tahrirlash</a>
                <a class="btn btn--ghost" href="/?page=create">Yangi g'oya</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero__panel">
        <div class="metric-grid">
            <article class="metric-card">
                <span class="metric-card__label">Jami ball</span>
                <strong><?= (int) $profile['total_score'] ?></strong>
            </article>
            <article class="metric-card">
                <span class="metric-card__label">G'oyalar</span>
                <strong><?= (int) $profile['ideas_count'] ?></strong>
            </article>
            <article class="metric-card">
                <span class="metric-card__label">Ochiq hamkorlik</span>
                <strong><?= (int) ($profile['open_ideas_count'] ?? 0) ?></strong>
            </article>
            <article class="metric-card">
                <span class="metric-card__label">So'nggi faoliyat</span>
                <strong><?= e(time_ago($profile['last_seen'] ?? $profile['created_at'])) ?></strong>
                <small class="metric-card__hint">Qo'shilgan sana: <?= e(date('d.m.Y', strtotime($profile['created_at']))) ?></small>
            </article>
        </div>
    </div>
</section>

<?php if ($isOwner): ?>
    <section class="panel" id="profile-settings">
        <div class="section-head">
            <div>
                <p class="eyebrow">Profil sozlamalari</p>
                <h2>Shaxsiy ma'lumotlar</h2>
            </div>
        </div>

        <form method="post" enctype="multipart/form-data" class="panel-form">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="form_action" value="update_profile">
            <input type="hidden" name="redirect_to" value="/?page=profile&id=<?= (int) $profile['id'] ?>">

            <div class="form-grid">
                <label class="form-span">
                    <span>Profil rasmi (avatar, ixtiyoriy · max 3MB · jpg/png/webp)</span>
                    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:0.5rem;">
                        <img src="<?= e(avatar_url($profile['avatar'] ?? null)) ?>" alt="Avatar"
                             style="width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid var(--border-strong);">
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif">
                    </div>
                </label>
                <label>
                    <span>Ism</span>
                    <input type="text" name="name" value="<?= e($profile['name']) ?>" required>
                </label>
                <label>
                    <span>Hudud</span>
                    <input type="text" name="location" value="<?= e($profile['location']) ?>" placeholder="Shahar yoki remote">
                </label>
                <label class="form-span">
                    <span>Bio</span>
                    <textarea name="bio" rows="4" placeholder="Siz kim siz, nimani qidiryapsiz"><?= e($profile['bio']) ?></textarea>
                </label>
                <label class="form-span">
                    <span>Ekspertiza</span>
                    <input type="text" name="expertise" value="<?= e($profile['expertise']) ?>" placeholder="Fintech, Growth, UX, Supply chain">
                </label>
                <label>
                    <span>Kontakt havola</span>
                    <input type="text" name="contact_link" value="<?= e($profile['contact_link']) ?>" placeholder="https://t.me/username yoki email">
                </label>
                <label>
                    <span>Hamkorlik statusi</span>
                    <select name="collaboration_status">
                        <option value="available" <?= ($profile['collaboration_status'] ?? 'available') === 'available' ? 'selected' : '' ?>>Hamkorlikka ochiq</option>
                        <option value="selective" <?= ($profile['collaboration_status'] ?? '') === 'selective' ? 'selected' : '' ?>>Mos takliflarni ko'raman</option>
                        <option value="busy" <?= ($profile['collaboration_status'] ?? '') === 'busy' ? 'selected' : '' ?>>Hozir bandman</option>
                    </select>
                </label>
            </div>

            <button class="btn btn--primary" type="submit">Profilni saqlash</button>
        </form>
    </section>
<?php endif; ?>

<section class="section-head">
    <div>
        <p class="eyebrow">Portfolio</p>
        <h2><?= e($profile['name']) ?> joylagan g'oyalar</h2>
    </div>
    <?php if ($isOwner): ?>
        <div class="section-head__actions">
            <a class="btn btn--ghost" href="/?page=create">Yangi g'oya qo'shish</a>
        </div>
    <?php endif; ?>
</section>

<section class="ideas-grid">
    <?php if ($ideasList === []): ?>
        <article class="empty-state">
            <h3>Hali g'oya joylanmagan</h3>
            <p>Profilni to'ldirib, birinchi g'oyani joylash bilan boshlash mumkin.</p>
        </article>
    <?php endif; ?>

    <?php foreach ($ideasList as $idea): ?>
        <?php require __DIR__ . '/partials/idea-card.php'; ?>
    <?php endforeach; ?>
</section>
