<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/app/core/Installer.php';

use App\Core\Installer;

function e(?string $value = null): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$installer = new Installer(__DIR__);
$forceInstall = (($_GET['force'] ?? '') === '1') || (($_POST['force_install'] ?? '') === '1');
$lockData = $installer->lockData();
$currentConfig = [];

try {
    if (is_file($installer->configPath())) {
        $loadedConfig = require $installer->configPath();
        if (is_array($loadedConfig)) {
            $currentConfig = $loadedConfig;
        }
    }
} catch (Throwable $exception) {
    $currentConfig = [];
}

$form = $installer->defaults($currentConfig, $_SERVER);
$errors = [];
$success = null;

if (empty($_SESSION['_install_csrf'])) {
    $_SESSION['_install_csrf'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $form = array_merge($form, $_POST);
    $form['install_demo'] = !empty($_POST['install_demo']) ? '1' : '';

    if (!hash_equals((string) $_SESSION['_install_csrf'], (string) ($_POST['_csrf'] ?? ''))) {
        $errors[] = 'Xavfsizlik tokeni yaroqsiz. Sahifani yangilab qayta urinib ko\'ring.';
    } elseif ($installer->isLocked() && !$forceInstall) {
        $errors[] = 'Installer allaqachon bloklangan. Qayta o\'rnatish uchun `storage/install.lock` ni o\'chiring yoki `?force=1` bilan oching.';
    } else {
        try {
            $success = $installer->install($_POST);
            $lockData = $installer->lockData();
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

$checks = $installer->checks();
?>
<!doctype html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tadbirkor.uz Installer</title>
    <style>
        :root {
            --bg: #f4efe8;
            --panel: #fffaf3;
            --panel-strong: #ffffff;
            --text: #1f2a30;
            --muted: #6d767b;
            --accent: #173445;
            --line: #ddd2c6;
            --success: #e2f1e7;
            --success-text: #24543a;
            --error: #f8e3df;
            --error-text: #8b2f2f;
            --warning: #fff1d8;
            --warning-text: #7b5424;
            --shadow: 0 18px 45px rgba(20, 28, 32, 0.08);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top right, rgba(23, 52, 69, 0.08), transparent 30%),
                linear-gradient(180deg, #f9f5ef 0%, var(--bg) 100%);
        }
        .shell {
            width: min(1100px, calc(100% - 32px));
            margin: 32px auto;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 24px;
        }
        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: var(--shadow);
        }
        .hero, .form-panel, .check-list { padding: 28px; }
        .hero {
            background:
                linear-gradient(135deg, rgba(23, 52, 69, 0.08), rgba(247, 192, 113, 0.10)),
                var(--panel-strong);
        }
        .eyebrow {
            margin: 0 0 10px;
            font-size: 12px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 700;
        }
        h1, h2 { margin: 0 0 12px; line-height: 1.1; }
        p, .meta { color: var(--muted); line-height: 1.6; }
        .hero-grid, .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .hero-grid { margin-top: 24px; }
        .mini-card {
            padding: 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(23, 52, 69, 0.08);
        }
        .stack { display: grid; gap: 20px; }
        .message {
            padding: 14px 16px;
            border-radius: 16px;
            margin-bottom: 14px;
            font-size: 14px;
            line-height: 1.5;
        }
        .message--error { background: var(--error); color: var(--error-text); }
        .message--success { background: var(--success); color: var(--success-text); }
        .message--warning { background: var(--warning); color: var(--warning-text); }
        .field {
            display: grid;
            gap: 8px;
            margin-bottom: 16px;
        }
        .field--full { grid-column: 1 / -1; }
        label span { font-size: 14px; font-weight: 600; }
        input, textarea {
            width: 100%;
            border-radius: 14px;
            border: 1px solid var(--line);
            padding: 12px 14px;
            font: inherit;
            background: #fff;
            color: var(--text);
        }
        textarea { min-height: 96px; resize: vertical; }
        .checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 8px 0 18px;
        }
        .checkbox input {
            width: 18px;
            height: 18px;
            margin-top: 2px;
        }
        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 999px;
            padding: 13px 18px;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
        }
        .button--primary { background: var(--accent); color: #fff; }
        .button--ghost {
            background: transparent;
            color: var(--accent);
            border: 1px solid rgba(23, 52, 69, 0.18);
        }
        .check-list ul, .summary-list {
            list-style: none;
            padding: 0;
            margin: 18px 0 0;
        }
        .check-list li, .summary-list li {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(23, 52, 69, 0.08);
            font-size: 14px;
        }
        .status { font-weight: 700; white-space: nowrap; }
        .status--ok { color: #1f7a4f; }
        .status--bad { color: #a93434; }
        code {
            font-family: Consolas, "Courier New", monospace;
            font-size: 0.95em;
            background: rgba(23, 52, 69, 0.06);
            padding: 2px 6px;
            border-radius: 8px;
        }
        @media (max-width: 920px) {
            .shell { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .grid, .hero-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <section class="panel hero">
            <p class="eyebrow">Installer</p>
            <h1>Tadbirkor.uz ni hostingda ishga tushirish</h1>
            <p>Bu sahifa MySQL ulanishini sozlaydi, jadvallarni yaratadi, admin akkaunt tayyorlaydi va <code>app/config/config.php</code> ni yozadi.</p>

            <div class="hero-grid">
                <article class="mini-card"><strong>1. Ulanish</strong><p>MySQL host, port, baza nomi va login-parolni kiriting.</p></article>
                <article class="mini-card"><strong>2. O'rnatish</strong><p>Schema import qilinadi, ixtiyoriy demo data yuklanadi.</p></article>
                <article class="mini-card"><strong>3. Admin</strong><p>Administrator akkaunti yaratiladi yoki yangilanadi.</p></article>
                <article class="mini-card"><strong>4. Bloklash</strong><p>O'rnatilgach <code>storage/install.lock</code> yaratiladi.</p></article>
            </div>

            <?php if ($success !== null): ?>
                <div class="message message--success" style="margin-top: 24px;">O'rnatish muvaffaqiyatli yakunlandi.</div>
                <ul class="summary-list">
                    <li><span>Sayt</span><strong><?= e($success['base_url']) ?></strong></li>
                    <li><span>Database</span><strong><?= e($success['database']) ?></strong></li>
                    <li><span>Admin email</span><strong><?= e($success['admin_email']) ?></strong></li>
                    <li><span>Config fayli</span><strong><?= e($success['config_path']) ?></strong></li>
                </ul>
                <?php foreach ($success['warnings'] as $warning): ?>
                    <div class="message message--warning" style="margin-top: 16px;"><?= e($warning) ?></div>
                <?php endforeach; ?>
                <div class="actions">
                    <a class="button button--primary" href="<?= e($success['base_url']) ?>/">Saytni ochish</a>
                    <a class="button button--ghost" href="<?= e($success['base_url']) ?>/?page=login">Admin kirishi</a>
                </div>
            <?php elseif ($installer->isLocked() && !$forceInstall): ?>
                <div class="message message--warning" style="margin-top: 24px;">Installer bloklangan. Sayt allaqachon o'rnatilgan bo'lishi mumkin.</div>
                <?php if ($lockData !== null): ?>
                    <ul class="summary-list">
                        <li><span>O'rnatilgan vaqt</span><strong><?= e((string) ($lockData['installed_at'] ?? '')) ?></strong></li>
                        <li><span>Sayt</span><strong><?= e((string) ($lockData['base_url'] ?? '')) ?></strong></li>
                        <li><span>Admin email</span><strong><?= e((string) ($lockData['admin_email'] ?? '')) ?></strong></li>
                    </ul>
                <?php endif; ?>
                <div class="actions">
                    <a class="button button--primary" href="<?= e((string) (($lockData['base_url'] ?? $form['base_url']))) ?>/">Saytni ochish</a>
                    <a class="button button--ghost" href="?force=1">Qayta o'rnatish rejimi</a>
                </div>
            <?php endif; ?>
        </section>

        <div class="stack">
            <section class="panel form-panel">
                <p class="eyebrow">Sozlamalar</p>
                <h2>O'rnatish formasi</h2>
                <p>Agar hostingda baza allaqachon yaratilgan bo'lsa, mavjud DB nomini kiritsangiz kifoya.</p>

                <?php foreach ($errors as $error): ?>
                    <div class="message message--error"><?= e($error) ?></div>
                <?php endforeach; ?>

                <?php if ($installer->isLocked() && !$forceInstall && $success === null): ?>
                    <div class="message message--warning">Qayta o'rnatish kerak bo'lsa, <code>storage/install.lock</code> ni o'chiring yoki <code>?force=1</code> bilan kiring.</div>
                <?php else: ?>
                    <?php if ($forceInstall && $installer->isLocked()): ?>
                        <div class="message message--warning">Siz qayta o'rnatish rejimidasiz. Mavjud baza va konfiguratsiya ustiga yozilishi mumkin.</div>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="_csrf" value="<?= e((string) $_SESSION['_install_csrf']) ?>">
                        <input type="hidden" name="force_install" value="<?= $forceInstall ? '1' : '0' ?>">

                        <div class="grid">
                            <label class="field field--full">
                                <span>Loyiha nomi</span>
                                <input type="text" name="app_name" value="<?= e((string) $form['app_name']) ?>" required>
                            </label>

                            <label class="field field--full">
                                <span>Base URL</span>
                                <input type="url" name="base_url" value="<?= e((string) $form['base_url']) ?>" placeholder="https://tadbirkor.uz" required>
                            </label>

                            <label class="field">
                                <span>Timezone</span>
                                <input type="text" name="timezone" value="<?= e((string) $form['timezone']) ?>" required>
                            </label>

                            <label class="field">
                                <span>Oy mukofoti matni</span>
                                <input type="text" name="monthly_prize" value="<?= e((string) $form['monthly_prize']) ?>" required>
                            </label>

                            <label class="field">
                                <span>DB host</span>
                                <input type="text" name="db_host" value="<?= e((string) $form['db_host']) ?>" required>
                            </label>

                            <label class="field">
                                <span>DB port</span>
                                <input type="text" name="db_port" value="<?= e((string) $form['db_port']) ?>" required>
                            </label>

                            <label class="field">
                                <span>DB nomi</span>
                                <input type="text" name="db_name" value="<?= e((string) $form['db_name']) ?>" required>
                            </label>

                            <label class="field">
                                <span>DB foydalanuvchi</span>
                                <input type="text" name="db_user" value="<?= e((string) $form['db_user']) ?>" required>
                            </label>

                            <label class="field field--full">
                                <span>DB parol</span>
                                <input type="password" name="db_password" value="<?= e((string) $form['db_password']) ?>">
                            </label>

                            <label class="field">
                                <span>Admin ism</span>
                                <input type="text" name="admin_name" value="<?= e((string) $form['admin_name']) ?>" required>
                            </label>

                            <label class="field">
                                <span>Admin email</span>
                                <input type="email" name="admin_email" value="<?= e((string) $form['admin_email']) ?>" required>
                            </label>

                            <label class="field field--full">
                                <span>Admin parol</span>
                                <input type="password" name="admin_password" value="<?= e((string) $form['admin_password']) ?>" minlength="6" required>
                            </label>
                        </div>

                        <label class="checkbox">
                            <input type="checkbox" name="install_demo" value="1" <?= !empty($form['install_demo']) ? 'checked' : '' ?>>
                            <span>Demo ma'lumotlarni ham yuklash. Bu sample foydalanuvchilar va g'oyalarni qo'shadi.</span>
                        </label>

                        <div class="actions">
                            <button class="button button--primary" type="submit">O'rnatishni boshlash</button>
                            <a class="button button--ghost" href="./">Asosiy saytga qaytish</a>
                        </div>
                    </form>
                <?php endif; ?>
            </section>

            <section class="panel check-list">
                <p class="eyebrow">Tekshiruv</p>
                <h2>Server holati</h2>
                <p>Quyidagi bandlar yashil bo'lsa, installer odatda muammosiz ishlaydi.</p>
                <ul>
                    <?php foreach ($checks as $check): ?>
                        <li>
                            <div>
                                <strong><?= e($check['label']) ?></strong>
                                <p class="meta"><?= e($check['detail']) ?></p>
                            </div>
                            <span class="status <?= $check['ok'] ? 'status--ok' : 'status--bad' ?>">
                                <?= $check['ok'] ? 'Tayyor' : 'Muammo bor' ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </div>
    </div>
</body>
</html>
