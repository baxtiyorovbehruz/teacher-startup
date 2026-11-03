<?php
declare(strict_types=1);

function e(?string $value = null): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function store_old(array $payload): void
{
    $_SESSION['_old'] = $payload;
}

function old(string $key, string $default = ''): string
{
    if (!isset($_SESSION['_old'])) {
        return $default;
    }

    return (string) ($_SESSION['_old'][$key] ?? $default);
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function verify_csrf(?string $token = null): bool
{
    return $token !== null
        && isset($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $token);
}

function ensure_csrf(?string $token = null): void
{
    if (!verify_csrf($token)) {
        flash('error', 'Xavfsizlik tokeni eskirgan. Sahifani yangilab qayta urinib ko\'ring.');
        redirect(current_url());
    }
}

function current_user(): ?array
{
    global $auth;

    if ($auth === null) {
        return null;
    }

    return $auth->user();
}

function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}

function current_url(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);

    foreach ($query as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        }
    }

    return $query === [] ? '/' : '/?' . http_build_query($query);
}

function base_url(string $path = ''): string
{
    global $config;

    $base = rtrim((string) ($config['base_url'] ?? ''), '/');
    if ($base === '' && isset($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . $_SERVER['HTTP_HOST'];
    }
    $trimmed = ltrim($path, '/');

    if ($trimmed === '') {
        return $base !== '' ? $base . '/' : '/';
    }

    return ($base !== '' ? $base . '/' : '/') . $trimmed;
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function role_label(string $role): string
{
    $map = [
        'founder' => 'Tadbirkor',
        'investor' => 'Investor',
        'mentor' => 'Mentor',
        'admin' => 'Boshqaruv',
    ];

    return $map[$role] ?? ucfirst($role);
}

function stage_label(string $stage): string
{
    $map = [
        'idea' => 'Faqat g\'oya',
        'mvp' => 'MVP tayyor',
        'business' => 'Tayyor biznes',
    ];

    return $map[$stage] ?? ucfirst($stage);
}

function validation_signal_label(string $signal): string
{
    $map = [
        'use' => 'Sinab ko\'raman',
        'pay' => 'Pul to\'layman',
        'mentor' => 'Mentor bo\'laman',
        'invest' => 'Invest qilaman',
    ];

    return $map[$signal] ?? ucfirst($signal);
}

function collaboration_status_label(string $status): string
{
    $map = [
        'available' => 'Hamkorlikka ochiq',
        'selective' => 'Mos takliflarni ko\'rib chiqadi',
        'busy' => 'Hozir band',
    ];

    return $map[$status] ?? ucfirst($status);
}

function split_csv_tags(?string $value): array
{
    if ($value === null || trim($value) === '') {
        return [];
    }

    $items = array_map('trim', explode(',', $value));
    $items = array_filter($items, static fn ($item): bool => $item !== '');

    return array_values(array_unique($items));
}

function external_link(?string $value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $value)) {
        return $value;
    }

    if (strpos($value, '@') === 0) {
        return 'https://t.me/' . ltrim($value, '@');
    }

    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return 'mailto:' . $value;
    }

    return 'https://' . ltrim($value, '/');
}

function format_money(?float $value = null): string
{
    if ($value === null) {
        return 'Kelishiladi';
    }

    return number_format($value, 0, '.', ' ') . ' $';
}

function format_budget_range(?float $from = null, ?float $to = null): string
{
    if ($from === null && $to === null) {
        return 'Aniqlanmagan';
    }

    if ($from !== null && $to !== null) {
        return format_money($from) . ' - ' . format_money($to);
    }

    return $from !== null
        ? 'Kamida ' . format_money($from)
        : 'Maksimal ' . format_money($to);
}

function excerpt(string $text, int $limit = 220): string
{
    $clean = trim(preg_replace('/\s+/', ' ', $text) ?? '');

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($clean) <= $limit) {
            return $clean;
        }

        return rtrim(mb_substr($clean, 0, max(0, $limit - 3))) . '...';
    }

    if (strlen($clean) <= $limit) {
        return $clean;
    }

    return rtrim(substr($clean, 0, $limit - 3)) . '...';
}

function time_ago(string $dateTime): string
{
    $timestamp = strtotime($dateTime);

    if ($timestamp === false) {
        return $dateTime;
    }

    $seconds = max(1, time() - $timestamp);
    $units = [
        31536000 => 'yil',
        2592000 => 'oy',
        604800 => 'hafta',
        86400 => 'kun',
        3600 => 'soat',
        60 => 'daqiqa',
        1 => 'soniya',
    ];

    foreach ($units as $divider => $label) {
        if ($seconds >= $divider) {
            $value = (int) floor($seconds / $divider);
            return $value . ' ' . $label . ' oldin';
        }
    }

    return 'Hozir';
}

/**
 * Rasm yuklash funksiyasi.
 * $subfolder: 'avatars' yoki 'ideas'
 * Muvaffaqiyatli bo'lsa fayl nomi qaytariladi, xato bo'lsa null.
 */
function upload_image(array $file, string $subfolder): ?string
{
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 3 * 1024 * 1024; // 3 MB

    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if ($file['size'] > $maxSize) {
        return null;
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowedTypes, true)) {
        return null;
    }

    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    $ext = $extMap[$mime] ?? 'jpg';

    $dir = __DIR__ . '/../../storage/' . $subfolder . '/';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = bin2hex(random_bytes(12)) . '.' . $ext;
    $dest = $dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }

    return $filename;
}

function avatar_url(?string $filename = null): string
{
    if (!empty($filename) && is_file(__DIR__ . '/../../storage/avatars/' . $filename)) {
        return '/storage/avatars/' . $filename;
    }

    return '/assets/icons/icon.svg';
}

function idea_cover_url(?string $filename = null): ?string
{
    if (!empty($filename) && is_file(__DIR__ . '/../../storage/ideas/' . $filename)) {
        return '/storage/ideas/' . $filename;
    }

    return null;
}

function render(string $view, array $data = []): void
{
    global $config, $dbError;

    $viewFile = __DIR__ . '/../views/' . $view . '.php';

    if (!is_file($viewFile)) {
        throw new RuntimeException('View topilmadi: ' . $view);
    }

    extract($data, EXTR_SKIP);

    require __DIR__ . '/../views/layout.php';
}
