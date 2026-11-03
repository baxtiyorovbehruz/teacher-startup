<section class="auth-shell">
    <article class="auth-side">
        <p class="eyebrow">Ro'yxatdan o'tish</p>
        <h1>Bir necha daqiqada profilingizni yarating va g'oyalarni ishga tushiring.</h1>
        <p>
            Platformaga qo'shilgach, siz o'z rolingiz bilan ko'rinasiz, tarmoq imkoniyatlari ochiladi va
            g'oyalaringizni tartibli lentaga joylay olasiz.
        </p>

        <div class="hero__trust">
            <span class="trust-pill">Tadbirkor, mentor va investor rollari</span>
            <span class="trust-pill">Mobilga mos tajriba</span>
            <span class="trust-pill">Qoralamani oflayn saqlash</span>
        </div>

        <ul class="feature-list">
            <li>O'zbek tilida g'oya validatsiyasi</li>
            <li>Mobilga mos lenta va webview ko'rinishi</li>
            <li>Sust internet uchun PWA qobig'i va mahalliy qoralama saqlash</li>
        </ul>
    </article>

    <article class="auth-card">
        <div class="section-copy">
            <p class="eyebrow">Yangi akkaunt</p>
            <h2>Asosiy ma'lumotlarni to'ldiring</h2>
        </div>

        <form method="post" class="stack-form">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="form_action" value="register">
            <input type="hidden" name="redirect_to" value="/?page=register">

            <label>
                <span>Ism</span>
                <input type="text" name="name" value="<?= e(old('name')) ?>" placeholder="Ism familiya" required>
            </label>

            <label>
                <span>Elektron pochta</span>
                <input type="email" name="email" value="<?= e(old('email')) ?>" placeholder="siz@example.com" required>
            </label>

            <label>
                <span>Roli</span>
                <select name="role">
                    <option value="founder" <?= old('role', 'founder') === 'founder' ? 'selected' : '' ?>>Tadbirkor</option>
                    <option value="investor" <?= old('role') === 'investor' ? 'selected' : '' ?>>Investor</option>
                    <option value="mentor" <?= old('role') === 'mentor' ? 'selected' : '' ?>>Mentor</option>
                </select>
            </label>

            <label>
                <span>Parol</span>
                <input type="password" name="password" placeholder="Kamida 6 ta belgi" required>
            </label>

            <button class="btn btn--primary" type="submit">Akkaunt yaratish</button>
        </form>

        <p class="auth-note">Akkauntingiz bormi? <a href="/?page=login">Tizimga kiring</a></p>
    </article>
</section>
