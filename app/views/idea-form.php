<?php
$isEdit = isset($idea);
$editId = $isEdit ? (int) $idea['id'] : 0;

// Edit rejimida mavjud qiymatlardan foydalanamiz, aks holda old() dan
function fval(string $key, $idea, string $default = ''): string {
    $fromOld = old($key);
    if ($fromOld !== '') return $fromOld;
    if ($idea !== null && isset($idea[$key])) return (string) $idea[$key];
    return $default;
}
function fcheck(string $key, $idea): bool {
    $fromOld = old($key);
    if ($fromOld !== '') return (bool) $fromOld;
    return !empty($idea[$key]);
}
?>
<section class="hero hero--editor">
    <div class="hero__content">
        <p class="eyebrow"><?= $isEdit ? 'G\'oyani tahrirlash' : 'Yangi post' ?></p>
        <h1><?= $isEdit ? e($idea['title']) . ' — tahrirlash' : 'Biznes g\'oyangizni ishonchli ko\'rinishda chiqaring.' ?></h1>
        <p class="hero__lead">
            Kuchli sarlavha, aniq muammo, monetizatsiya va hamkor talabi bilan yozilgan post ko'proq
            izoh, validatsiya va real aloqalarni olib keladi.
        </p>
        <div class="hero__trust">
            <?php if (!$isEdit): ?>
                <span class="trust-pill">Draft avtomatik saqlanadi</span>
            <?php endif; ?>
            <span class="trust-pill">Investor uchun ko'rinarli format</span>
            <span class="trust-pill">Mobilga mos editor</span>
        </div>
    </div>

    <div class="hero__panel">
        <article class="panel panel--gradient">
            <p class="eyebrow">Tekshiruv ro'yxati</p>
            <h2>Kuchli post nimadan iborat?</h2>
            <ul class="feature-list feature-list--tight">
                <li>Muammo va yechimni 1-2 jumlada aniq ayting</li>
                <li>Qaysi foydalanuvchi uchun ekanini yozing</li>
                <li>Monetizatsiya va budjetni iloji boricha real ko'rsating</li>
                <li>Kimlarni hamkor sifatida izlayotganingizni yozing</li>
            </ul>
        </article>
    </div>
</section>

<section class="detail-grid">
    <form method="post" enctype="multipart/form-data" class="panel panel-form" <?= !$isEdit ? 'data-draft-key="idea-form"' : '' ?>>
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="form_action" value="<?= $isEdit ? 'update_idea' : 'create_idea' ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="idea_id" value="<?= $editId ?>">
            <input type="hidden" name="redirect_to" value="/?page=edit&id=<?= $editId ?>">
        <?php else: ?>
            <input type="hidden" name="redirect_to" value="/?page=create">
        <?php endif; ?>

        <div class="section-copy">
            <p class="eyebrow">Taqdimot shakli</p>
            <h2>Asosiy ma'lumotlar</h2>
            <p>Formani to'liqroq to'ldirsangiz, post kartasi va detail sahifasi ancha ishonchli ko'rinadi.</p>
        </div>

        <div class="form-grid">
            <label class="form-span">
                <span>Sarlavha</span>
                <input type="text" name="title" value="<?= e(fval('title', $idea ?? null)) ?>" placeholder="Masalan: AI asosidagi fermer yordamchisi" required>
            </label>

            <label class="form-span">
                <span>Tavsif</span>
                <textarea name="description" rows="7" placeholder="Muammo, yechim va qiymat taklifini qisqa yozing" required><?= e(fval('description', $idea ?? null)) ?></textarea>
            </label>

            <label>
                <span>Soha</span>
                <select name="sector" required>
                    <option value="">Tanlang</option>
                    <?php foreach ($sectors as $sector): ?>
                        <option value="<?= e($sector) ?>" <?= fval('sector', $idea ?? null) === $sector ? 'selected' : '' ?>><?= e($sector) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <span>Bosqich</span>
                <select name="stage" required>
                    <?php foreach ($stages as $stage): ?>
                        <option value="<?= e($stage) ?>" <?= fval('stage', $idea ?? null, 'idea') === $stage ? 'selected' : '' ?>><?= e(stage_label($stage)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <span>Hudud</span>
                <input type="text" name="location" value="<?= e(fval('location', $idea ?? null)) ?>" placeholder="Toshkent, Samarqand, onlayn">
            </label>

            <label>
                <span>Maqsadli auditoriya</span>
                <input type="text" name="target_audience" value="<?= e(fval('target_audience', $idea ?? null)) ?>" placeholder="Kim uchun bu mahsulot kerak?">
            </label>

            <label class="checkbox-field">
                <input type="checkbox" name="investment_needed" value="1" <?= fcheck('investment_needed', $idea ?? null) ? 'checked' : '' ?>>
                <span>Investitsiya kerak</span>
            </label>

            <label class="checkbox-field">
                <input type="checkbox" name="collaboration_open" value="1" <?= fcheck('collaboration_open', $idea ?? null) ? 'checked' : '' ?>>
                <span>Hamkor yoki hamasoschi qidiryapman</span>
            </label>

            <div class="inline-grid form-span">
                <label>
                    <span>Budjetdan ($)</span>
                    <input type="number" name="budget_from" min="0" step="100" value="<?= e(fval('budget_from', $idea ?? null)) ?>" placeholder="5000">
                </label>

                <label>
                    <span>Budjetgacha ($)</span>
                    <input type="number" name="budget_to" min="0" step="100" value="<?= e(fval('budget_to', $idea ?? null)) ?>" placeholder="15000">
                </label>
            </div>

            <label class="form-span">
                <span>Mini bozor tahlili</span>
                <textarea name="market_note" rows="4" placeholder="Bozor hajmi, og'riq nuqtasi, raqobatchilar yoki segmentlar haqida qisqa yozing"><?= e(fval('market_note', $idea ?? null)) ?></textarea>
            </label>

            <label class="form-span">
                <span>Monetizatsiya</span>
                <input type="text" name="monetization" value="<?= e(fval('monetization', $idea ?? null)) ?>" placeholder="Obuna, komissiya, B2B litsenziya va h.k.">
            </label>

            <label class="form-span">
                <span>Qaysi rollar kerak</span>
                <input type="text" name="seeking_roles" value="<?= e(fval('seeking_roles', $idea ?? null)) ?>" placeholder="Masalan: texnik rahbar, marketolog, mentor, investor">
            </label>

            <label class="form-span">
                <span>Cover rasm (ixtiyoriy, max 3MB · jpg/png/webp)</span>
                <?php if ($isEdit && !empty($idea['cover_image'])): ?>
                    <div style="margin-bottom: 0.5rem;">
                        <img src="<?= e(idea_cover_url($idea['cover_image'])) ?>" alt="Mavjud cover" style="max-height:120px; border-radius:8px; object-fit:cover;">
                        <small style="display:block; color:var(--muted)">Yangi rasm yuklasangiz, eskisi o'rnini bosadi.</small>
                    </div>
                <?php endif; ?>
                <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp,image/gif">
            </label>
        </div>

        <div class="form-actions">
            <button class="btn btn--primary" type="submit">
                <?= $isEdit ? 'O\'zgarishlarni saqlash' : 'Lentaga joylash' ?>
            </button>
            <?php if ($isEdit): ?>
                <a href="/?page=idea&id=<?= $editId ?>" class="btn btn--ghost">Bekor qilish</a>
            <?php else: ?>
                <small>Draft brauzerda saqlanadi. Internet sust bo'lsa ham yozganingiz yo'qolmaydi.</small>
            <?php endif; ?>
        </div>

        <?php if ($isEdit): ?>
            <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--color-border);">
            <div class="section-copy">
                <p class="eyebrow" style="color: var(--color-danger, #c0392b)">Xavfli zona</p>
                <h3>G'oyani o'chirish</h3>
                <p>Bu amalni qaytarib bo'lmaydi. Barcha ovozlar, izohlar va validatsiyalar ham o'chadi.</p>
            </div>
            <form method="post" onsubmit="return confirm('G\'oyani o\'chirishni tasdiqlaysizmi? Bu amalni qaytarib bo\'lmaydi.')">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="form_action" value="delete_idea">
                <input type="hidden" name="idea_id" value="<?= $editId ?>">
                <input type="hidden" name="redirect_to" value="/?page=idea&id=<?= $editId ?>">
                <button class="btn btn--danger" type="submit">G'oyani o'chirish</button>
            </form>
        <?php endif; ?>
    </form>

    <aside class="stack-panel">
        <section class="panel panel--sticky">
            <div class="section-copy">
                <p class="eyebrow">Tavsiya</p>
                <h2>Taqdimot sifatini oshiring</h2>
            </div>
            <ul class="info-list">
                <li>Sarlavhada muammo yoki natijani ayting, umumiy gaplardan qoching.</li>
                <li>Maqsadli auditoriya qismi investor va mentor uchun eng muhim signal.</li>
                <li>Budjet va monetizatsiya yozilgan postlar ko'proq ishonch uyg'otadi.</li>
            </ul>
        </section>

        <section class="panel panel--muted">
            <div class="section-copy">
                <p class="eyebrow"><?= $isEdit ? 'Diqqat' : 'Natija' ?></p>
                <h2><?= $isEdit ? 'Tahrirlash haqida' : 'Joylangandan keyin' ?></h2>
            </div>
            <ul class="feature-list feature-list--tight">
                <?php if ($isEdit): ?>
                    <li>O'zgarishlar darhol ko'rinadi</li>
                    <li>Ovozlar va izohlar saqlanib qoladi</li>
                    <li>Tahrirlash sanasi yangilanadi</li>
                <?php else: ?>
                    <li>Post asosiy lentada paydo bo'ladi</li>
                    <li>Foydalanuvchilar yoqtirish, tanqid va izoh qoldiradi</li>
                    <li>Validatsiya signallari orqali qiziqish o'lchanadi</li>
                <?php endif; ?>
            </ul>
        </section>
    </aside>
</section>
