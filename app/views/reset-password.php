<section class="auth-shell">
    <article class="auth-side">
        <p class="eyebrow">Yangi parol</p>
        <h1>Xavfsiz yangi parol o'rnating.</h1>
        <p>
            Kamida 6 ta belgi bo'lishi kerak. Kuchli parol uchun harf, raqam va belgilardan foydalaning.
        </p>
        <p class="auth-note"><a href="/?page=login">&larr; Kirishga qaytish</a></p>
    </article>

    <article class="auth-card">
        <div class="section-copy">
            <p class="eyebrow">Parolni yangilash</p>
            <h2>Yangi parolni kiriting</h2>
        </div>

        <?php if (empty($token)): ?>
            <div class="flash flash--error">Tiklash havolasi yaroqsiz yoki muddati tugagan.</div>
            <p class="auth-note"><a href="/?page=forgot-password">Qayta so'rov yuborish</a></p>
        <?php else: ?>
            <form method="post" class="stack-form">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="form_action" value="reset_password">
                <input type="hidden" name="token" value="<?= e($token) ?>">

                <label>
                    <span>Yangi parol</span>
                    <input type="password" name="password" placeholder="Kamida 6 ta belgi" required autofocus minlength="6">
                </label>

                <button class="btn btn--primary" type="submit">Parolni saqlash</button>
            </form>
        <?php endif; ?>
    </article>
</section>
