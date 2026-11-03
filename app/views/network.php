<?php
$totalPeople = count($people);
$openPeople = count(array_filter($people, static fn (array $person): bool => ($person['collaboration_status'] ?? '') === 'available'));
$mentorCount = count(array_filter($people, static fn (array $person): bool => ($person['role'] ?? '') === 'mentor'));
$investorCount = count(array_filter($people, static fn (array $person): bool => ($person['role'] ?? '') === 'investor'));
?>
<section class="hero hero--compact">
    <div class="hero__content">
        <p class="eyebrow">Hamkorlik katalogi</p>
        <h1>Mentor, investor va hamasoschilar bilan ishonchli tarmoq yarating.</h1>
        <p class="hero__lead">
            Bu bo'limda faol profil, ekspertiza va aloqa ko'rinishida kerakli odamlarga tez chiqasiz.
            Saralash yordamida aynan kerakli rol va holatdagi foydalanuvchilarni topish mumkin.
        </p>
        <div class="hero__trust">
            <span class="trust-pill"><?= $totalPeople ?> ta profil</span>
            <span class="trust-pill"><?= $openPeople ?> ta hamkorlikka ochiq</span>
            <span class="trust-pill"><?= $mentorCount ?> ta mentor</span>
        </div>
    </div>

    <div class="hero__panel">
        <div class="metric-grid metric-grid--tight">
            <article class="metric-card">
                <span class="metric-card__label">Umumiy profil</span>
                <strong><?= $totalPeople ?></strong>
            </article>
            <article class="metric-card">
                <span class="metric-card__label">Ochiq hamkorlik</span>
                <strong><?= $openPeople ?></strong>
            </article>
            <article class="metric-card">
                <span class="metric-card__label">Mentorlar</span>
                <strong><?= $mentorCount ?></strong>
            </article>
            <article class="metric-card">
                <span class="metric-card__label">Investorlar</span>
                <strong><?= $investorCount ?></strong>
            </article>
        </div>
    </div>
</section>

<section class="filter-panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">Aniq qidiruv</p>
            <h2>Rol, status va kalit so'z bo'yicha toping</h2>
        </div>
        <div class="section-head__actions">
            <span class="ghost-chip"><?= $totalPeople ?> ta natija</span>
        </div>
    </div>

    <form method="get" class="filter-form">
        <input type="hidden" name="page" value="network">

        <label>
            <span>Qidiruv</span>
            <input type="search" name="q" value="<?= e($networkFilters['q'] ?? '') ?>" placeholder="Ism, mutaxassislik, shahar">
        </label>

        <label>
            <span>Roli</span>
            <select name="role">
                <option value="">Barcha rollar</option>
                <?php foreach (['founder', 'investor', 'mentor'] as $role): ?>
                    <option value="<?= e($role) ?>" <?= ($networkFilters['role'] ?? '') === $role ? 'selected' : '' ?>>
                        <?= e(role_label($role)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Status</span>
            <select name="status">
                <option value="">Barcha statuslar</option>
                <?php foreach ($collaborationStatuses as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($networkFilters['status'] ?? '') === $status ? 'selected' : '' ?>>
                        <?= e(collaboration_status_label($status)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Saralash</span>
            <select name="sort">
                <option value="active" <?= ($networkFilters['sort'] ?? '') === 'active' ? 'selected' : '' ?>>Faol</option>
                <option value="top" <?= ($networkFilters['sort'] ?? '') === 'top' ? 'selected' : '' ?>>Top</option>
                <option value="new" <?= ($networkFilters['sort'] ?? '') === 'new' ? 'selected' : '' ?>>Yangi</option>
            </select>
        </label>

        <button class="btn btn--primary" type="submit">Qidirish</button>
    </form>
</section>

<section class="section-head">
    <div>
        <p class="eyebrow">Katalog</p>
        <h2>Hamkor, mentor va investorlar ro'yxati</h2>
    </div>
    <div class="section-head__actions">
        <span class="ghost-chip"><?= $openPeople ?> ta faol profil</span>
    </div>
</section>

<section class="people-grid">
    <?php if ($people === []): ?>
        <article class="empty-state">
            <h3>Mos profil topilmadi</h3>
            <p>Filterlarni yengillashtirib ko'ring yoki yangi foydalanuvchilar qo'shilishini kuting.</p>
        </article>
    <?php endif; ?>

    <?php foreach ($people as $person): ?>
        <?php require __DIR__ . '/partials/person-card.php'; ?>
    <?php endforeach; ?>
</section>
