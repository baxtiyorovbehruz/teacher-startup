<section class="auth-shell">
    <article class="auth-side">
        <p class="eyebrow">Parolni tiklash</p>
        <h1>Kirishda qiyinchilik bo'ldimi? Emailingizga tiklash havolasini yuboramiz.</h1>
        <p>
            Email manzilingizni kiriting. Agar u tizimda ro'yxatdan o'tgan bo'lsa,
            1 soat amal qiladigan tiklash havolasi yuboriladi.
        </p>
        <p class="auth-note"><a href="/?page=login">&larr; Kirishga qaytish</a></p>
    </article>

    <article class="auth-card">
        <div class="section-copy">
            <p class="eyebrow">Parolni unutdingizmi?</p>
            <h2>Email manzilini kiriting</h2>
        </div>

        <form method="post" class="stack-form">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="form_action" value="forgot_password">

            <label>
                <span>Elektron pochta</span>
                <input type="email" name="email" placeholder="siz@example.com" required autofocus>
            </label>

            <button class="btn btn--primary" type="submit">Tiklash havolasini yuborish</button>
        </form>

        <p class="auth-note">Parolingizni eslading? <a href="/?page=login">Tizimga kiring</a></p>
    </article>
</section>
