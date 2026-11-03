<?php
declare(strict_types=1);

namespace App\Core;

class Auth
{
    private Database $db;
    private ?array $user = null;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        if ($this->user !== null) {
            return $this->user;
        }

        $this->user = $this->db->fetchOne(
            'SELECT id, name, email, role, bio, location, expertise, contact_link, collaboration_status, created_at, last_seen
             FROM users WHERE id = :id LIMIT 1',
            ['id' => (int) $_SESSION['user_id']]
        );

        return $this->user;
    }

    public function id(): ?int
    {
        $user = $this->user();
        return $user ? (int) $user['id'] : null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function isAdmin(): bool
    {
        $user = $this->user();
        return $user !== null && $user['role'] === 'admin';
    }

    public function login(string $email, string $password): array
    {
        $email = mb_strtolower(trim($email));

        if ($email === '' || $password === '') {
            return ['success' => false, 'message' => 'Email va parolni to\'liq kiriting.'];
        }

        // Rate limiting: IP bo'yicha 5 ta xato urinishdan keyin 15 daqiqa blok
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $rateLimitKey = '_login_attempts_' . md5($ip);
        $attempts = $_SESSION[$rateLimitKey] ?? ['count' => 0, 'first_at' => time()];

        if ($attempts['count'] >= 5) {
            $elapsed = time() - $attempts['first_at'];
            if ($elapsed < 900) { // 15 daqiqa = 900 sekund
                $remaining = (int) ceil((900 - $elapsed) / 60);
                return ['success' => false, 'message' => "Juda ko'p urinish. {$remaining} daqiqadan so'ng qayta urining."];
            }
            // Muddat o'tgan — hisoblagichni tiklash
            unset($_SESSION[$rateLimitKey]);
            $attempts = ['count' => 0, 'first_at' => time()];
        }

        $user = $this->db->fetchOne(
            'SELECT id, password_hash FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        );

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            $attempts['count']++;
            if ($attempts['count'] === 1) {
                $attempts['first_at'] = time();
            }
            $_SESSION[$rateLimitKey] = $attempts;
            $left = 5 - $attempts['count'];
            $hint = $left > 0 ? " ({$left} ta urinish qoldi)" : '';
            return ['success' => false, 'message' => 'Login yoki parol noto\'g\'ri.' . $hint];
        }

        // Muvaffaqiyatli — hisoblagichni tiklash
        unset($_SESSION[$rateLimitKey]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $this->touchLastSeen((int) $user['id']);
        $this->user = null;

        return ['success' => true, 'message' => 'Xush kelibsiz. Profilingizga muvaffaqiyatli kirdingiz.'];
    }

    public function register(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $role = (string) ($data['role'] ?? 'founder');
        $allowedRoles = ['founder', 'investor', 'mentor'];

        if ($name === '' || $email === '' || $password === '') {
            return ['success' => false, 'message' => 'Ro\'yxatdan o\'tish uchun barcha maydonlarni to\'ldiring.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email formati noto\'g\'ri.'];
        }

        if (mb_strlen($password) < 6) {
            return ['success' => false, 'message' => 'Parol kamida 6 ta belgidan iborat bo\'lishi kerak.'];
        }

        if (!in_array($role, $allowedRoles, true)) {
            $role = 'founder';
        }

        $exists = $this->db->fetchOne(
            'SELECT id FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        );

        if ($exists !== null) {
            return ['success' => false, 'message' => 'Bu email bilan foydalanuvchi allaqachon mavjud.'];
        }

        $id = $this->db->insert(
            'INSERT INTO users (name, email, password_hash, role, bio, location, expertise, contact_link, collaboration_status, created_at, last_seen)
             VALUES (:name, :email, :password_hash, :role, :bio, :location, :expertise, :contact_link, :collaboration_status, NOW(), NOW())',
            [
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'bio' => '',
                'location' => '',
                'expertise' => '',
                'contact_link' => '',
                'collaboration_status' => 'available',
            ]
        );

        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        $this->user = null;

        return ['success' => true, 'message' => 'Akkaunt yaratildi. Endi g\'oyangizni joylashingiz mumkin.'];
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
        $this->user = null;
        session_regenerate_id(true);
    }

    public function updateProfile(int $userId, array $data, ?array $avatarFile = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $bio = trim((string) ($data['bio'] ?? ''));
        $location = trim((string) ($data['location'] ?? ''));
        $expertise = trim((string) ($data['expertise'] ?? ''));
        $contactLink = trim((string) ($data['contact_link'] ?? ''));
        $collaborationStatus = trim((string) ($data['collaboration_status'] ?? 'available'));

        if (!in_array($collaborationStatus, ['available', 'selective', 'busy'], true)) {
            $collaborationStatus = 'available';
        }

        if ($name === '') {
            return ['success' => false, 'message' => 'Ism maydoni bo\'sh bo\'lmasligi kerak.'];
        }

        // Avatar yuklash
        $avatarFilename = null;
        if ($avatarFile !== null && ($avatarFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $uploaded = upload_image($avatarFile, 'avatars');
            if ($uploaded === null) {
                return ['success' => false, 'message' => 'Avatar rasmi o\'lcham yoki format talabiga to\'g\'ri kelmadi (max 3MB, jpg/png/webp).'];
            }
            $avatarFilename = $uploaded;

            // Eski avatarni o'chirish
            $old = $this->db->fetchOne('SELECT avatar FROM users WHERE id = :id LIMIT 1', ['id' => $userId]);
            if (!empty($old['avatar'])) {
                $oldPath = __DIR__ . '/../../storage/avatars/' . $old['avatar'];
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        $params = [
            'id' => $userId,
            'name' => $name,
            'bio' => mb_substr($bio, 0, 500),
            'location' => mb_substr($location, 0, 120),
            'expertise' => mb_substr($expertise, 0, 180),
            'contact_link' => mb_substr($contactLink, 0, 255),
            'collaboration_status' => $collaborationStatus,
        ];

        if ($avatarFilename !== null) {
            $params['avatar'] = $avatarFilename;
            $this->db->execute(
                'UPDATE users
                 SET name = :name, bio = :bio, location = :location, expertise = :expertise,
                     contact_link = :contact_link, collaboration_status = :collaboration_status,
                     avatar = :avatar
                 WHERE id = :id',
                $params
            );
        } else {
            $this->db->execute(
                'UPDATE users
                 SET name = :name, bio = :bio, location = :location, expertise = :expertise,
                     contact_link = :contact_link, collaboration_status = :collaboration_status
                 WHERE id = :id',
                $params
            );
        }

        $this->user = null;

        return ['success' => true, 'message' => 'Profil ma\'lumotlari yangilandi.'];
    }

    public function forgotPassword(string $email): array
    {
        $email = mb_strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email formati noto\'g\'ri.'];
        }

        $user = $this->db->fetchOne(
            'SELECT id, name, email FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        );

        // Xavfsizlik uchun: foydalanuvchi yo'q bo'lsa ham muvaffaqiyat xabari beriladi
        if ($user === null) {
            return ['success' => true, 'message' => 'Agar bu email tizimda ro\'yxatdan o\'tgan bo\'lsa, tiklash havolasi yuborildi.'];
        }

        // Eski tokenlarni o'chirish
        $this->db->execute(
            'DELETE FROM password_resets WHERE email = :email',
            ['email' => $email]
        );

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 soat

        $this->db->insert(
            'INSERT INTO password_resets (email, token, expires_at, used, created_at)
             VALUES (:email, :token, :expires_at, 0, NOW())',
            [
                'email' => $email,
                'token' => $token,
                'expires_at' => $expiresAt,
            ]
        );

        // Email yuborish
        global $config;
        $resetUrl = rtrim((string) ($config['base_url'] ?? ''), '/') . '/?page=reset-password&token=' . $token;
        $subject = 'Tadbirkor.uz — parolni tiklash';
        $message = "Salom, {$user['name']}!\n\n"
            . "Parolni tiklash uchun quyidagi havolani bosing (1 soat amal qiladi):\n\n"
            . $resetUrl . "\n\n"
            . "Agar siz so'rov yubormagan bo'lsangiz, ushbu xatni e'tiborsiz qoldiring.\n\n"
            . "— Tadbirkor.uz jamoasi";

        $headers = "From: noreply@tadbirkor.great-site.net\r\nContent-Type: text/plain; charset=UTF-8";

        @mail($user['email'], $subject, $message, $headers);

        return ['success' => true, 'message' => 'Agar bu email tizimda ro\'yxatdan o\'tgan bo\'lsa, tiklash havolasi yuborildi.'];
    }

    public function resetPassword(string $token, string $password): array
    {
        if (mb_strlen($password) < 6) {
            return ['success' => false, 'message' => 'Yangi parol kamida 6 ta belgidan iborat bo\'lishi kerak.'];
        }

        $reset = $this->db->fetchOne(
            'SELECT id, email, expires_at, used FROM password_resets WHERE token = :token LIMIT 1',
            ['token' => $token]
        );

        if ($reset === null || (int) $reset['used'] === 1) {
            return ['success' => false, 'message' => 'Havola yaroqsiz yoki allaqachon ishlatilgan.'];
        }

        if (strtotime($reset['expires_at']) < time()) {
            return ['success' => false, 'message' => 'Tiklash havolasining muddati tugagan. Qayta so\'rov yuboring.'];
        }

        $user = $this->db->fetchOne(
            'SELECT id FROM users WHERE email = :email LIMIT 1',
            ['email' => $reset['email']]
        );

        if ($user === null) {
            return ['success' => false, 'message' => 'Foydalanuvchi topilmadi.'];
        }

        $this->db->execute(
            'UPDATE users SET password_hash = :hash WHERE id = :id',
            [
                'id' => (int) $user['id'],
                'hash' => password_hash($password, PASSWORD_DEFAULT),
            ]
        );

        $this->db->execute(
            'UPDATE password_resets SET used = 1 WHERE token = :token',
            ['token' => $token]
        );

        return ['success' => true, 'message' => 'Parol muvaffaqiyatli yangilandi. Endi tizimga kirishingiz mumkin.'];
    }

    private function touchLastSeen(int $userId): void
    {
        $this->db->execute(
            'UPDATE users SET last_seen = NOW() WHERE id = :id',
            ['id' => $userId]
        );
    }
}
