# Tadbirkor.uz MVP

Mobilga mos PHP + MySQL MVP. Platforma yoshlar biznes g'oyalarini joylash, yoqtirish/tanqid, izoh, profil rollari, qidiruv, boshqaruv paneli va mini validatsiya oqimlarini birlashtiradi.

## Asosiy imkoniyatlar

- Login / register / logout
- G'oya joylash va lenta
- Trend / top / new saralash + qidiruv va filter
- Yoqtirish / tanqid / izoh AJAX orqali
- Mentor / investor / foydalanuvchi validatsiya signallari
- Profil sahifasi va rolga qarab ko'rinish
- Investor / mentor / hamkor katalogi
- Boshqaruv paneli: rol yangilash va g'oya moderatsiyasi
- PWA qobig'i + qoralama autosave

## Tavsiya etilgan o'rnatish

1. Fayllarni hostingga yuklang.
2. Brauzerda `/install.php` sahifasini oching.
3. DB ulanishi, sayt URL'i va admin ma'lumotlarini kiriting.
4. Xohlasangiz demo ma'lumotlarni ham yuklang.
5. O'rnatilgach saytni oching va admin akkaunt bilan kiring.

Installer muvaffaqiyatli tugagach `storage/install.lock` yaratiladi. Qayta o'rnatish kerak bo'lsa, shu faylni o'chiring yoki `/install.php?force=1` dan foydalaning.

## Qo'lda o'rnatish

1. MySQL'da `database/schema.sql` ni import qiling.
2. Ixtiyoriy demo ma'lumot uchun `database/seed.sql` ni import qiling.
3. `app/config/config.php` ichida DB sozlamalarini moslang.
4. Saytni domen rootiga yo'naltirib oching.

Agar siz eski skeletdan yangilayotgan bo'lsangiz, yangi maydonlar uchun `database/upgrade_v2.sql` ni ham ishga tushiring.

## Demo admin

- Email: `admin@tadbirkor.uz`
- Parol: `password`
