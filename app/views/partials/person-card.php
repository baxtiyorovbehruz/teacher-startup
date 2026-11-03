<article class="person-card">
    <div class="person-card__head">
        <div>
            <p class="eyebrow"><?= e(role_label($person['role'])) ?></p>
            <strong><a href="/?page=profile&id=<?= (int) $person['id'] ?>"><?= e($person['name']) ?></a></strong>
            <p><?= e($person['expertise'] ?: 'Hamkorlik va tarmoq uchun profil ochiq.') ?></p>
        </div>
        <span class="status-pill status-pill--<?= e($person['collaboration_status']) ?>">
            <?= e(collaboration_status_label($person['collaboration_status'])) ?>
        </span>
    </div>

    <p class="person-card__bio"><?= e(excerpt($person['bio'] ?: 'Hamkorlik va tarmoq uchun profil ochiq.', 140)) ?></p>

    <div class="chip-row">
        <?php if (!empty($person['location'])): ?>
            <span class="chip"><?= e($person['location']) ?></span>
        <?php endif; ?>
        <?php if (!empty($person['expertise'])): ?>
            <span class="chip chip--accent"><?= e($person['expertise']) ?></span>
        <?php endif; ?>
        <span class="chip"><?= (int) $person['ideas_count'] ?> g'oya</span>
        <span class="chip"><?= (int) $person['open_ideas_count'] ?> ochiq hamkorlik</span>
    </div>

    <div class="person-card__footer">
        <div class="person-card__metric">
            <strong><?= (int) $person['total_score'] ?></strong>
            <span>jami ball</span>
        </div>

        <div class="idea-actions">
            <a class="action-btn action-btn--link" href="/?page=profile&id=<?= (int) $person['id'] ?>">Profil</a>
            <?php if (!empty($person['contact_link'])): ?>
                <a class="action-btn" href="<?= e(external_link($person['contact_link'])) ?>" target="_blank" rel="noopener noreferrer">Bog'lanish</a>
            <?php endif; ?>
        </div>
    </div>
</article>
