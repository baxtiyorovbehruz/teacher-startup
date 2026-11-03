<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class Installer
{
    private string $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = rtrim($rootPath, '/\\');
    }

    public function configPath(): string
    {
        return $this->path('app/config/config.php');
    }

    public function lockPath(): string
    {
        return $this->path('storage/install.lock');
    }

    public function isLocked(): bool
    {
        return is_file($this->lockPath());
    }

    public function lockData(): ?array
    {
        if (!$this->isLocked()) {
            return null;
        }

        $json = file_get_contents($this->lockPath());
        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true);

        return is_array($data) ? $data : null;
    }

    public function defaults(array $currentConfig = [], array $server = []): array
    {
        $host = $server['HTTP_HOST'] ?? 'localhost';
        $cleanHost = preg_replace('/:\d+$/', '', (string) $host) ?: 'localhost';

        return [
            'app_name' => (string) ($currentConfig['app_name'] ?? 'Tadbirkor.uz'),
            'base_url' => (string) ($currentConfig['base_url'] ?? $this->detectBaseUrl($server)),
            'timezone' => (string) ($currentConfig['timezone'] ?? 'Asia/Tashkent'),
            'monthly_prize' => (string) ($currentConfig['contest']['monthly_prize'] ?? 'Ekspert sessiyasi + bosh sahifada alohida joy + mentor fikri'),
            'db_host' => (string) ($currentConfig['db']['host'] ?? '127.0.0.1'),
            'db_port' => (string) ($currentConfig['db']['port'] ?? '3306'),
            'db_name' => (string) ($currentConfig['db']['database'] ?? 'tadbirkor'),
            'db_user' => (string) ($currentConfig['db']['username'] ?? 'root'),
            'db_password' => (string) ($currentConfig['db']['password'] ?? ''),
            'admin_name' => 'Administrator',
            'admin_email' => 'admin@' . $cleanHost,
            'admin_password' => '',
            'install_demo' => '1',
        ];
    }

    public function checks(): array
    {
        $configPath = $this->configPath();
        $configWritable = is_file($configPath)
            ? is_writable($configPath)
            : is_writable(dirname($configPath));
        $storageWritable = is_writable(dirname($this->lockPath()));

        return [
            [
                'label' => 'PHP 8.1 yoki yuqori',
                'ok' => PHP_VERSION_ID >= 80100,
                'detail' => PHP_VERSION,
            ],
            [
                'label' => 'PDO MySQL kengaytmasi',
                'ok' => extension_loaded('pdo_mysql'),
                'detail' => extension_loaded('pdo_mysql') ? 'Faol' : 'Topilmadi',
            ],
            [
                'label' => 'Config faylini yozish',
                'ok' => $configWritable,
                'detail' => $configWritable ? $configPath : 'PHP ushbu faylni yoza olmaydi: ' . $configPath,
            ],
            [
                'label' => 'Storage papkasini yozish',
                'ok' => $storageWritable,
                'detail' => $storageWritable ? dirname($this->lockPath()) : 'Yozish ruxsati kerak: ' . dirname($this->lockPath()),
            ],
        ];
    }

    public function install(array $input): array
    {
        $this->assertEnvironment();
        $data = $this->normalize($input);

        $warnings = [];
        $serverPdo = $this->createServerPdo($data['db_host'], $data['db_port'], $data['db_user'], $data['db_password']);

        try {
            $serverPdo->exec('CREATE DATABASE IF NOT EXISTS ' . $this->quoteIdentifier($data['db_name']) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        } catch (PDOException $exception) {
            $warnings[] = 'Database avtomatik yaratilmagan. Agar hosting panelida allaqachon yaratilgan bo\'lsa, bu xabarni e\'tiborsiz qoldirishingiz mumkin.';
        }

        $databasePdo = $this->createDatabasePdo(
            $data['db_host'],
            $data['db_port'],
            $data['db_name'],
            $data['db_user'],
            $data['db_password']
        );

        $this->runSqlFile($databasePdo, $this->path('database/schema.sql'));

        if ($data['install_demo']) {
            $this->runSqlFile($databasePdo, $this->path('database/seed.sql'));
        }

        $this->upsertAdmin($databasePdo, $data['admin_name'], $data['admin_email'], $data['admin_password']);

        $config = [
            'app_name' => $data['app_name'],
            'base_url' => $data['base_url'],
            'timezone' => $data['timezone'],
            'contest' => [
                'monthly_prize' => $data['monthly_prize'],
            ],
            'db' => [
                'host' => $data['db_host'],
                'port' => $data['db_port'],
                'database' => $data['db_name'],
                'username' => $data['db_user'],
                'password' => $data['db_password'],
                'charset' => 'utf8mb4',
            ],
        ];

        $this->writeFile($this->configPath(), $this->buildConfigPhp($config));
        $this->writeFile($this->lockPath(), json_encode([
            'installed_at' => date('c'),
            'app_name' => $data['app_name'],
            'base_url' => $data['base_url'],
            'database' => $data['db_name'],
            'admin_email' => $data['admin_email'],
            'demo_loaded' => $data['install_demo'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return [
            'app_name' => $data['app_name'],
            'base_url' => $data['base_url'],
            'database' => $data['db_name'],
            'admin_email' => $data['admin_email'],
            'config_path' => $this->configPath(),
            'lock_path' => $this->lockPath(),
            'demo_loaded' => $data['install_demo'],
            'warnings' => $warnings,
        ];
    }

    private function normalize(array $input): array
    {
        $data = [
            'app_name' => trim((string) ($input['app_name'] ?? '')),
            'base_url' => rtrim(trim((string) ($input['base_url'] ?? '')), '/'),
            'timezone' => trim((string) ($input['timezone'] ?? 'Asia/Tashkent')),
            'monthly_prize' => trim((string) ($input['monthly_prize'] ?? '')),
            'db_host' => trim((string) ($input['db_host'] ?? '127.0.0.1')),
            'db_port' => trim((string) ($input['db_port'] ?? '3306')),
            'db_name' => trim((string) ($input['db_name'] ?? '')),
            'db_user' => trim((string) ($input['db_user'] ?? '')),
            'db_password' => (string) ($input['db_password'] ?? ''),
            'admin_name' => trim((string) ($input['admin_name'] ?? 'Administrator')),
            'admin_email' => mb_strtolower(trim((string) ($input['admin_email'] ?? ''))),
            'admin_password' => (string) ($input['admin_password'] ?? ''),
            'install_demo' => !empty($input['install_demo']),
        ];

        if ($data['app_name'] === '') {
            throw new RuntimeException('Loyiha nomini kiriting.');
        }

        if ($data['base_url'] === '' || !filter_var($data['base_url'], FILTER_VALIDATE_URL)) {
            throw new RuntimeException('To\'g\'ri `base_url` kiriting. Masalan: https://tadbirkor.uz');
        }

        $scheme = parse_url($data['base_url'], PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new RuntimeException('`base_url` faqat http yoki https bilan boshlanishi kerak.');
        }

        if (!in_array($data['timezone'], timezone_identifiers_list(), true)) {
            throw new RuntimeException('Timezone noto\'g\'ri ko\'rsatilgan.');
        }

        if ($data['monthly_prize'] === '') {
            throw new RuntimeException('Oy mukofoti matnini kiriting.');
        }

        if ($data['db_host'] === '' || $data['db_name'] === '' || $data['db_user'] === '') {
            throw new RuntimeException('DB host, DB nomi va DB foydalanuvchisi majburiy.');
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $data['db_name'])) {
            throw new RuntimeException('DB nomida faqat harf, raqam, `_` va `-` ishlating.');
        }

        if (!ctype_digit($data['db_port'])) {
            throw new RuntimeException('DB port faqat raqamlardan iborat bo\'lishi kerak.');
        }

        if ($data['admin_name'] === '') {
            throw new RuntimeException('Admin ismini kiriting.');
        }

        if (!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Admin email noto\'g\'ri.');
        }

        if (mb_strlen($data['admin_password']) < 6) {
            throw new RuntimeException('Admin paroli kamida 6 ta belgidan iborat bo\'lishi kerak.');
        }

        return $data;
    }

    private function assertEnvironment(): void
    {
        foreach ($this->checks() as $check) {
            if (!$check['ok']) {
                throw new RuntimeException($check['label'] . ': ' . $check['detail']);
            }
        }
    }

    private function createServerPdo(string $host, string $port, string $user, string $password): PDO
    {
        try {
            return new PDO(
                sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port),
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (Throwable $exception) {
            throw new RuntimeException('MySQL serverga ulanib bo\'lmadi. Host, port, login va parolni tekshiring.');
        }
    }

    private function createDatabasePdo(string $host, string $port, string $database, string $user, string $password): PDO
    {
        try {
            return new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database),
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (Throwable $exception) {
            throw new RuntimeException('Tanlangan bazaga ulanib bo\'lmadi. Database mavjudligini va foydalanuvchi ruxsatlarini tekshiring.');
        }
    }

    private function runSqlFile(PDO $pdo, string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException('SQL fayl topilmadi: ' . $path);
        }

        $statements = $this->parseSqlStatements((string) file_get_contents($path));

        foreach ($statements as $statement) {
            if (preg_match('/^(CREATE\s+DATABASE|USE\s+)/i', ltrim($statement))) {
                continue;
            }

            try {
                $pdo->exec($statement);
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    'SQL import vaqtida xato yuz berdi: '
                    . $this->shortenSql($statement)
                    . ' | '
                    . $exception->getMessage()
                );
            }
        }
    }

    private function parseSqlStatements(string $sql): array
    {
        $lines = preg_split('/\R/', $sql) ?: [];
        $buffer = '';
        $statements = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                continue;
            }

            $buffer .= $line . "\n";

            if (str_ends_with(rtrim($trimmed), ';')) {
                $statement = trim($buffer);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $buffer = '';
            }
        }

        $tail = trim($buffer);
        if ($tail !== '') {
            $statements[] = $tail;
        }

        return $statements;
    }

    private function upsertAdmin(PDO $pdo, string $name, string $email, string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $statement = $pdo->prepare(
            'INSERT INTO users (
                name, email, password_hash, role, bio, location, expertise, contact_link, collaboration_status, created_at, last_seen
             ) VALUES (
                :name, :email, :password_hash, :role, :bio, :location, :expertise, :contact_link, :collaboration_status, NOW(), NOW()
             )
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                password_hash = VALUES(password_hash),
                role = VALUES(role),
                bio = VALUES(bio),
                location = VALUES(location),
                expertise = VALUES(expertise),
                contact_link = VALUES(contact_link),
                collaboration_status = VALUES(collaboration_status),
                last_seen = VALUES(last_seen)'
        );

        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $hash,
            'role' => 'admin',
            'bio' => 'Platforma boshqaruvi uchun yaratilgan administrator akkaunti.',
            'location' => '',
            'expertise' => 'Boshqaruv',
            'contact_link' => '',
            'collaboration_status' => 'available',
        ]);
    }

    private function buildConfigPhp(array $config): string
    {
        return "<?php\n"
            . "declare(strict_types=1);\n\n"
            . 'return ' . var_export($config, true) . ";\n";
    }

    private function writeFile(string $path, string $contents): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            throw new RuntimeException('Papka topilmadi: ' . $directory);
        }

        $isWritable = is_file($path) ? is_writable($path) : is_writable($directory);
        if (!$isWritable) {
            throw new RuntimeException('PHP ushbu faylni yoza olmaydi: ' . $path);
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException('Faylni yozib bo\'lmadi: ' . $path);
        }
    }

    private function quoteIdentifier(string $value): string
    {
        return '`' . str_replace('`', '``', $value) . '`';
    }

    private function shortenSql(string $statement): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $statement) ?? '');

        return mb_strlen($clean) <= 120
            ? $clean
            : mb_substr($clean, 0, 117) . '...';
    }

    private function detectBaseUrl(array $server): string
    {
        $isHttps = (!empty($server['HTTPS']) && $server['HTTPS'] !== 'off')
            || (($server['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (($server['SERVER_PORT'] ?? '') === '443');

        $scheme = $isHttps ? 'https' : 'http';
        $host = $server['HTTP_HOST'] ?? 'localhost';

        return $scheme . '://' . $host;
    }

    private function path(string $relativePath): string
    {
        return $this->rootPath . '/' . str_replace('\\', '/', $relativePath);
    }
}
