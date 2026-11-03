<section class="auth-shell">
    <article class="auth-card">
        <div class="section-copy">
            <p class="eyebrow">Akkauntga kirish</p>
            <h2>Davom etish uchun ma'lumotlaringizni kiriting</h2>
        </div>

        <form method="post" class="stack-form">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="form_action" value="login">
            <input type="hidden" name="redirect_to" value="/?page=login">

            <label>
                <span>Elektron pochta</span>
                <input type="email" name="email" value="<?= e(old('email')) ?>" placeholder="siz@example.com" required>
            </label>

            <label>
                <span>Parol</span>
                <input type="password" name="password" placeholder="Kamida 6 ta belgi" required>
            </label>

            <button class="btn btn--primary" type="submit">Kirish</button>
        </form>

        <p class="auth-note">Akkaunt yo'qmi? <a href="/?page=register">Ro'yxatdan o'ting</a></p>
        <p class="auth-note"><a href="/?page=forgot-password">Parolni unutdingizmi?</a></p>
    </article>
</section>
