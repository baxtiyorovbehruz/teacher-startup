<?php
$topThree = $contest['top_three'] ?? [];
$ideaCount = count($ideasList);
$communityCount = count($featuredPeople);
$activeFilterCount = count(array_filter([
    $filters['q'] ?? '',
    $filters['sector'] ?? '',
    $filters['stage'] ?? '',
]));
?>
<section class="hero hero--home">
    <div class="hero__content">
        <p class="eyebrow">O'zbek tadbirkorlar hamjamiyati</p>
        <h1>G'oyani ishonchli ko'rsating, hamkor toping va investorga tayyor chiqing.</h1>
        <p class="hero__lead">
            Tadbirkor.uz yoshlar biznes g'oyalarini tartibli ko'rinishda joylash, validatsiya yig'ish,
            mentorlar bilan ishlash va investor e'tiboriga chiqish uchun yaratilgan.
        </p>

        <div class="hero__actions">
            <a class="btn btn--primary" href="/?page=create">G'oya joylash</a>
            <a class="btn btn--ghost" href="/?page=home&sort=trend">Trend lentani ochish</a>
        </div>

        <div class="hero__trust">
            <span class="trust-pill">Trend reytingi</span>
            <span class="trust-pill">Mentor va investor e'tibori</span>
            <span class="trust-pill">Sust internetga mos PWA</span>
        </div>
    </div>

    <div class="hero__panel">
        <div class="metric-grid">
            <article class="metric-card">
                <span class="metric-card__label">Nashr etilgan g'oyalar</span>
                <strong><?= (int) ($stats['total_ideas'] ?? 0) ?></strong>
                <small class="metric-card__hint">Platformadagi faol g'oyalar bazasi</small>
            </article>
            <article class="metric-card">
                <span class="metric-card__label">Hamjamiyat</span>
                <strong><?= (int) ($stats['total_users'] ?? 0) ?></strong>
                <small class="metric-card__hint">Tadbirkor, mentor va investorlar</small>
            </article>
            <article class="metric-card">
                <span class="metric-card__label">Investorlar</span>
                <strong><?= (int) ($stats['investors'] ?? 0) ?></strong>
                <small class="metric-card__hint">Sarmoya qidirayotganlar uchun</small>
            </article>
            <article class="metric-card">
                <span class="metric-card__label">Mentorlar</span>
                <strong><?= (int) ($stats['mentors'] ?? 0) ?></strong>
                <small class="metric-card__hint">Fikr va o'sish yordami</small>
            </article>
        </div>

        <article class="contest-card">
            <div class="section-copy">
                <p class="eyebrow">Oy reytingi</p>
                <h2><?= e($contest['month_title'] ?? date('F Y')) ?> lideri</h2>
            </div>
            <?php if (!empty($contest['leader'])): ?>
                <strong><?= e($contest['leader']['title']) ?></strong>
                <p><?= e($contest['leader']['author_name']) ?> | <?= e($contest['leader']['sector']) ?></p>
                <p>
                    Ball <?= (int) $contest['leader']['score'] ?>
                    | Izoh <?= (int) $contest['leader']['comment_count'] ?>
                    | Validatsiya <?= (int) $contest['leader']['validation_count'] ?>
                </p>
            <?php else: ?>
                <p>Bu oy uchun hali lider shakllanmadi. Birinchi bo'lib kuchli g'oya joylang.</p>
            <?php endif; ?>

            <div class="contest-card__footer">
                <small>Mukofot: <?= e($config['contest']['monthly_prize']) ?></small>
                <span class="score-badge"><?= count($topThree) ?> ta finalist</span>
            </div>
        </article>
    </div>
</section>

<section class="showcase-grid">
    <article class="panel feature-card">
        <span class="feature-card__icon">01</span>
        <h3>Taqdimot andozasi</h3>
        <p>Muammo, auditoriya, monetizatsiya, budjet va hamkor ehtiyojlari bir joyda tartibli ko'rinadi.</p>
    </article>

    <article class="panel feature-card">
        <span class="feature-card__icon">02</span>
        <h3>Bozor signallari</h3>
        <p>Yoqtirish, tanqid, izoh va validatsiya signallari orqali g'oyaning real qiziqishini o'lchaysiz.</p>
    </article>

    <article class="panel feature-card">
        <span class="feature-card__icon">03</span>
        <h3>Hamkor topish oson</h3>
        <p>Investor, mentor va hamasoschilar bitta katalogda. Aloqa va ekspertiza tez topiladi.</p>
    </article>
</section>

<section class="summary-grid">
    <article class="panel">
        <div class="section-copy">
            <p class="eyebrow">Lenta holati</p>
            <h2>Bugungi ko'rinish</h2>
        </div>
        <div class="metric-grid metric-grid--tight">
            <article class="metric-card">
                <span class="metric-card__label">Ko'rinayotgan g'oyalar</span>
                <strong><?= $ideaCount ?></strong>
            </article>
            <article class="metric-card">
                <span class="metric-card__label">Faol filterlar</span>
                <strong><?= $activeFilterCount ?></strong>
            </article>
        </div>
    </article>

    <article class="panel">
        <div class="section-copy">
            <p class="eyebrow">Faol segmentlar</p>
            <h2>Asosiy yo'nalishlar</h2>
        </div>
        <div class="chip-row">
            <?php foreach (array_slice($sectors, 0, 6) as $sector): ?>
                <span class="chip"><?= e($sector) ?></span>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel">
        <div class="section-copy">
            <p class="eyebrow">Oy top 3</p>
            <h2>Kuchli chiqishlar</h2>
        </div>
        <?php if ($topThree !== []): ?>
            <div class="leaderboard-list">
                <?php foreach ($topThree as $index => $leader): ?>
                    <div class="leaderboard-item">
                        <span class="leaderboard-item__rank"><?= $index + 1 ?></span>
                        <div class="leaderboard-item__meta">
                            <strong><?= e($leader['title']) ?></strong>
                            <small><?= e($leader['author_name']) ?> | <?= e($leader['sector']) ?></small>
                        </div>
                        <span class="leaderboard-item__score"><?= (int) $leader['score'] ?> ball</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Hali finalchilar yo'q. Yangi post bilan bu blokka chiqish mumkin.</p>
        <?php endif; ?>
    </article>
</section>

<section class="filter-panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">Qidiruv va saralash</p>
            <h2>O'zingizga mos g'oyani toping</h2>
        </div>
        <div class="section-head__actions">
            <span class="ghost-chip"><?= $ideaCount ?> ta natija</span>
        </div>
    </div>

    <form method="get" class="filter-form">
        <input type="hidden" name="page" value="home">

        <label>
            <span>Qidiruv</span>
            <input type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Masalan: agritech, fintech, edtech">
        </label>

        <label>
            <span>Soha</span>
            <select name="sector">
                <option value="">Barcha sohalar</option>
                <?php foreach ($sectors as $sector): ?>
                    <option value="<?= e($sector) ?>" <?= ($filters['sector'] ?? '') === $sector ? 'selected' : '' ?>>
                        <?= e($sector) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Bosqich</span>
            <select name="stage">
                <option value="">Barcha bosqichlar</option>
                <?php foreach ($stages as $stage): ?>
                    <option value="<?= e($stage) ?>" <?= ($filters['stage'] ?? '') === $stage ? 'selected' : '' ?>>
                        <?= e(stage_label($stage)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Saralash</span>
            <select name="sort">
                <option value="trend" <?= ($filters['sort'] ?? '') === 'trend' ? 'selected' : '' ?>>Trend</option>
                <option value="top" <?= ($filters['sort'] ?? '') === 'top' ? 'selected' : '' ?>>Eng mashhur</option>
                <option value="new" <?= ($filters['sort'] ?? '') === 'new' ? 'selected' : '' ?>>Eng yangi</option>
            </select>
        </label>

        <button class="btn btn--primary" type="submit">Filtrlash</button>
    </form>
</section>

<?php if ($dbError !== null): ?>
    <section class="empty-state">
        <h2>Platforma skeleti tayyor</h2>
        <p>Endi `database/schema.sql` va `database/seed.sql` ni import qilib, real ma'lumot bilan ishga tushiring.</p>
    </section>
<?php else: ?>
    <?php if (!empty($featuredPeople)): ?>
        <section class="section-head">
            <div>
                <p class="eyebrow">Tarmoq</p>
                <h2>Investor, mentor va faol hamkorlar</h2>
            </div>
            <div class="section-head__actions">
                <span class="ghost-chip"><?= $communityCount ?> ta tavsiya</span>
                <a class="btn btn--ghost" href="/?page=network">To'liq katalog</a>
            </div>
        </section>

        <section class="people-grid">
            <?php foreach ($featuredPeople as $person): ?>
                <?php require __DIR__ . '/partials/person-card.php'; ?>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <section class="section-head">
        <div>
            <p class="eyebrow">Asosiy lenta</p>
            <h2>Yoshlarning biznes g'oyalari</h2>
        </div>
        <div class="section-head__actions">
            <span class="ghost-chip"><?= $ideaCount ?> ta post</span>
            <?php if (current_user()): ?>
                <a class="btn btn--ghost" href="/?page=create">Yangi post</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="ideas-grid" data-feed-snapshot>
        <?php if ($ideasList === []): ?>
            <article class="empty-state">
                <h3>Hozircha mos g'oya topilmadi</h3>
                <p>Qidiruvni yengillashtiring yoki yangi g'oya qo'shib, birinchi bo'lib trendga chiqing.</p>
            </article>
        <?php endif; ?>

        <?php foreach ($ideasList as $idea): ?>
            <?php require __DIR__ . '/partials/idea-card.php'; ?>
        <?php endforeach; ?>
    </section>

    <?php if (($totalPages ?? 1) > 1): ?>
        <nav class="pagination" aria-label="Sahifalar">
            <?php
            $pBase = array_filter([
                'page' => 'home',
                'q' => $filters['q'] ?: null,
                'sector' => $filters['sector'] ?: null,
                'stage' => $filters['stage'] ?: null,
                'sort' => $filters['sort'] !== 'trend' ? $filters['sort'] : null,
            ]);
            $currentPage = $currentPage ?? 1;
            $totalPages = $totalPages ?? 1;
            ?>
            <?php if ($currentPage > 1): ?>
                <a class="pagination__btn" href="/?<?= http_build_query(array_merge($pBase, ['p' => $currentPage - 1])) ?>">&larr; Oldingi</a>
            <?php endif; ?>

            <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
                <a class="pagination__btn <?= $p === $currentPage ? 'is-active' : '' ?>" href="/?<?= http_build_query(array_merge($pBase, ['p' => $p])) ?>"><?= $p ?></a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a class="pagination__btn" href="/?<?= http_build_query(array_merge($pBase, ['p' => $currentPage + 1])) ?>">Keyingi &rarr;</a>
            <?php endif; ?>

            <span class="pagination__info"><?= $totalIdeas ?? 0 ?> ta g'oyadan <?= $currentPage ?> / <?= $totalPages ?> sahifa</span>
        </nav>
    <?php endif; ?>

    <section class="offline-snapshot" id="offline-snapshot" hidden>
        <div class="section-head">
            <div>
                <p class="eyebrow">Oflayn nusxa</p>
                <h2>Oxirgi saqlangan lenta</h2>
            </div>
            <small data-offline-time></small>
        </div>
        <div class="ideas-grid" data-offline-feed></div>
    </section>
<?php endif; ?>
