<?php
declare(strict_types=1);

namespace App\Core;

class IdeaService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function sectors(): array
    {
        return [
            'IT',
            'Qishloq xo\'jaligi',
            'Ta\'lim',
            'Fintech',
            'Logistika',
            'Sog\'liqni saqlash',
            'Turizm',
            'Elektron savdo',
            'Media',
            'Yashil energetika',
        ];
    }

    public function stages(): array
    {
        return ['idea', 'mvp', 'business'];
    }

    public function collaborationStatuses(): array
    {
        return ['available', 'selective', 'busy'];
    }

    public function createIdea(int $userId, array $data, ?array $coverFile = null): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $sector = trim((string) ($data['sector'] ?? ''));
        $stage = trim((string) ($data['stage'] ?? 'idea'));
        $location = trim((string) ($data['location'] ?? ''));
        $targetAudience = trim((string) ($data['target_audience'] ?? ''));
        $marketNote = trim((string) ($data['market_note'] ?? ''));
        $monetization = trim((string) ($data['monetization'] ?? ''));
        $investmentNeeded = !empty($data['investment_needed']) ? 1 : 0;
        $collaborationOpen = !empty($data['collaboration_open']) ? 1 : 0;
        $seekingRoles = trim((string) ($data['seeking_roles'] ?? ''));
        $budgetFrom = is_numeric($data['budget_from'] ?? null) ? (float) $data['budget_from'] : null;
        $budgetTo = is_numeric($data['budget_to'] ?? null) ? (float) $data['budget_to'] : null;

        // Cover rasm yuklash
        $coverImage = null;
        if ($coverFile !== null && ($coverFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $coverImage = upload_image($coverFile, 'ideas');
        }

        if ($title === '' || $description === '' || $sector === '') {
            return ['success' => false, 'message' => 'Sarlavha, tavsif va soha maydonlari majburiy.'];
        }

        if (mb_strlen($title) < 6) {
            return ['success' => false, 'message' => 'Sarlavha kamida 6 ta belgidan iborat bo\'lsin.'];
        }

        if (mb_strlen($description) < 40) {
            return ['success' => false, 'message' => 'Tavsifni biroz batafsilroq yozing. Kamida 40 belgi kerak.'];
        }

        if (!in_array($stage, $this->stages(), true)) {
            $stage = 'idea';
        }

        $this->db->insert(
            'INSERT INTO ideas (
                user_id, title, description, sector, stage, location, investment_needed, budget_from, budget_to,
                target_audience, market_note, monetization, collaboration_open, seeking_roles, cover_image, status, created_at, updated_at
             ) VALUES (
                :user_id, :title, :description, :sector, :stage, :location, :investment_needed, :budget_from, :budget_to,
                :target_audience, :market_note, :monetization, :collaboration_open, :seeking_roles, :cover_image, :status, NOW(), NOW()
             )',
            [
                'user_id' => $userId,
                'title' => mb_substr($title, 0, 140),
                'description' => mb_substr($description, 0, 5000),
                'sector' => mb_substr($sector, 0, 120),
                'stage' => $stage,
                'location' => mb_substr($location, 0, 120),
                'investment_needed' => $investmentNeeded,
                'budget_from' => $budgetFrom,
                'budget_to' => $budgetTo,
                'target_audience' => mb_substr($targetAudience, 0, 255),
                'market_note' => mb_substr($marketNote, 0, 1000),
                'monetization' => mb_substr($monetization, 0, 255),
                'collaboration_open' => $collaborationOpen,
                'seeking_roles' => mb_substr($seekingRoles, 0, 255),
                'cover_image' => $coverImage,
                'status' => 'published',
            ]
        );

        return ['success' => true, 'message' => 'G\'oya lentaga joylandi. Endi yoqtirish, izoh va validatsiya yig\'a olasiz.'];
    }

    public function countIdeas(array $filters = [], bool $includeArchived = false): int
    {
        $where = [];
        $params = [];

        if (!$includeArchived) {
            $where[] = "i.status = 'published'";
        }

        if (!empty($filters['user_id'])) {
            $where[] = 'i.user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(i.title LIKE :search OR i.description LIKE :search OR i.target_audience LIKE :search)';
            $params['search'] = '%' . trim((string) $filters['q']) . '%';
        }

        if (!empty($filters['sector'])) {
            $where[] = 'i.sector = :sector';
            $params['sector'] = $filters['sector'];
        }

        if (!empty($filters['stage'])) {
            $where[] = 'i.stage = :stage';
            $params['stage'] = $filters['stage'];
        }

        $sql = 'SELECT COUNT(*) AS total FROM ideas i';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $row = $this->db->fetchOne($sql, $params);
        return (int) ($row['total'] ?? 0);
    }

    public function listIdeas(array $filters = [], ?int $viewerId = null, bool $includeArchived = false): array
    {
        $where = [];
        $params = [
            'viewer_vote_id' => $viewerId ?? 0,
            'viewer_signal_id' => $viewerId ?? 0,
        ];

        if (!$includeArchived) {
            $where[] = "i.status = 'published'";
        }

        if (!empty($filters['id'])) {
            $where[] = 'i.id = :id';
            $params['id'] = (int) $filters['id'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = 'i.user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(i.title LIKE :search OR i.description LIKE :search OR i.target_audience LIKE :search)';
            $params['search'] = '%' . trim((string) $filters['q']) . '%';
        }

        if (!empty($filters['sector'])) {
            $where[] = 'i.sector = :sector';
            $params['sector'] = $filters['sector'];
        }

        if (!empty($filters['stage'])) {
            $where[] = 'i.stage = :stage';
            $params['stage'] = $filters['stage'];
        }

        $sort = $filters['sort'] ?? 'trend';
        $orderBy = 'trend_score DESC, i.created_at DESC';

        if ($sort === 'new') {
            $orderBy = 'i.created_at DESC';
        } elseif ($sort === 'top') {
            $orderBy = 'score DESC, likes DESC, i.created_at DESC';
        }

        $perPage = 12;
        $currentPage = max(1, (int) ($filters['page'] ?? 1));
        $offset = ($currentPage - 1) * $perPage;

        $sql = '
            SELECT
                i.*,
                u.name AS author_name,
                u.role AS author_role,
                COALESCE(v.likes, 0) AS likes,
                COALESCE(v.dislikes, 0) AS dislikes,
                COALESCE(v.score, 0) AS score,
                COALESCE(c.comment_count, 0) AS comment_count,
                COALESCE(val.validation_count, 0) AS validation_count,
                COALESCE(val.use_count, 0) AS use_count,
                COALESCE(val.pay_count, 0) AS pay_count,
                COALESCE(val.mentor_count, 0) AS mentor_count,
                COALESCE(val.invest_count, 0) AS invest_count,
                COALESCE(uv.vote, 0) AS user_vote,
                vv.`signal` AS user_signal,
                (
                    (COALESCE(v.score, 0) * 4) +
                    (COALESCE(c.comment_count, 0) * 2) +
                    COALESCE(val.validation_count, 0)
                ) AS trend_score
            FROM ideas i
            INNER JOIN users u ON u.id = i.user_id
            LEFT JOIN (
                SELECT
                    idea_id,
                    SUM(CASE WHEN vote = 1 THEN 1 ELSE 0 END) AS likes,
                    SUM(CASE WHEN vote = -1 THEN 1 ELSE 0 END) AS dislikes,
                    SUM(vote) AS score
                FROM idea_votes
                GROUP BY idea_id
            ) v ON v.idea_id = i.id
            LEFT JOIN (
                SELECT idea_id, COUNT(*) AS comment_count
                FROM idea_comments
                GROUP BY idea_id
            ) c ON c.idea_id = i.id
            LEFT JOIN (
                SELECT
                    idea_id,
                    COUNT(*) AS validation_count,
                    SUM(CASE WHEN `signal` = \'use\' THEN 1 ELSE 0 END) AS use_count,
                    SUM(CASE WHEN `signal` = \'pay\' THEN 1 ELSE 0 END) AS pay_count,
                    SUM(CASE WHEN `signal` = \'mentor\' THEN 1 ELSE 0 END) AS mentor_count,
                    SUM(CASE WHEN `signal` = \'invest\' THEN 1 ELSE 0 END) AS invest_count
                FROM validation_votes
                GROUP BY idea_id
            ) val ON val.idea_id = i.id
            LEFT JOIN idea_votes uv
                ON uv.idea_id = i.id AND uv.user_id = :viewer_vote_id
            LEFT JOIN validation_votes vv
                ON vv.idea_id = i.id AND vv.user_id = :viewer_signal_id
        ';

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY ' . $orderBy . ' LIMIT ' . $perPage . ' OFFSET ' . $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function featuredStats(): array
    {
        $users = $this->db->fetchOne(
            'SELECT
                COUNT(*) AS total_users,
                SUM(CASE WHEN role = \'investor\' THEN 1 ELSE 0 END) AS investors,
                SUM(CASE WHEN role = \'mentor\' THEN 1 ELSE 0 END) AS mentors
             FROM users'
        ) ?? [];

        $ideas = $this->db->fetchOne(
            'SELECT COUNT(*) AS total_ideas FROM ideas WHERE status = \'published\''
        ) ?? [];

        return [
            'total_users' => (int) ($users['total_users'] ?? 0),
            'investors' => (int) ($users['investors'] ?? 0),
            'mentors' => (int) ($users['mentors'] ?? 0),
            'total_ideas' => (int) ($ideas['total_ideas'] ?? 0),
        ];
    }

    public function peopleDirectory(array $filters = []): array
    {
        $where = ['u.role <> \'admin\''];
        $params = [];

        if (!empty($filters['role'])) {
            $where[] = 'u.role = :role';
            $params['role'] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'u.collaboration_status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(u.name LIKE :search OR u.bio LIKE :search OR u.expertise LIKE :search OR u.location LIKE :search)';
            $params['search'] = '%' . trim((string) $filters['q']) . '%';
        }

        $sort = $filters['sort'] ?? 'active';
        $limit = max(1, min(36, (int) ($filters['limit'] ?? 18)));
        $orderBy = 'collaboration_rank ASC, COALESCE(open_ideas_count, 0) DESC, COALESCE(total_score, 0) DESC, u.last_seen DESC';

        if ($sort === 'new') {
            $orderBy = 'u.created_at DESC';
        } elseif ($sort === 'top') {
            $orderBy = 'COALESCE(total_score, 0) DESC, COALESCE(ideas_count, 0) DESC, u.last_seen DESC';
        }

        $sql = '
            SELECT
                u.id,
                u.name,
                u.email,
                u.role,
                u.bio,
                u.location,
                u.expertise,
                u.contact_link,
                u.collaboration_status,
                u.created_at,
                u.last_seen,
                COALESCE(i.ideas_count, 0) AS ideas_count,
                COALESCE(i.open_ideas_count, 0) AS open_ideas_count,
                COALESCE(s.total_score, 0) AS total_score,
                CASE
                    WHEN u.collaboration_status = \'available\' THEN 0
                    WHEN u.collaboration_status = \'selective\' THEN 1
                    ELSE 2
                END AS collaboration_rank
            FROM users u
            LEFT JOIN (
                SELECT
                    user_id,
                    COUNT(*) AS ideas_count,
                    SUM(CASE WHEN collaboration_open = 1 THEN 1 ELSE 0 END) AS open_ideas_count
                FROM ideas
                WHERE status = \'published\'
                GROUP BY user_id
            ) i ON i.user_id = u.id
            LEFT JOIN (
                SELECT
                    t.user_id,
                    SUM(t.idea_score) AS total_score
                FROM (
                    SELECT
                        i.user_id,
                        COALESCE(v.score, 0) AS idea_score
                    FROM ideas i
                    LEFT JOIN (
                        SELECT idea_id, SUM(vote) AS score
                        FROM idea_votes
                        GROUP BY idea_id
                    ) v ON v.idea_id = i.id
                    WHERE i.status = \'published\'
                ) t
                GROUP BY t.user_id
            ) s ON s.user_id = u.id
        ';

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY ' . $orderBy . ' LIMIT ' . $limit;

        return $this->db->fetchAll($sql, $params);
    }

    public function featuredPeople(?string $role = null, int $limit = 3): array
    {
        $filters = [
            'sort' => 'active',
            'limit' => $limit,
            'status' => 'available',
        ];

        if ($role !== null) {
            $filters['role'] = $role;
        }

        return $this->peopleDirectory($filters);
    }

    public function monthlyContest(): array
    {
        $leaderboard = $this->db->fetchAll(
            '
            SELECT
                i.id,
                i.title,
                i.sector,
                i.created_at,
                u.name AS author_name,
                COALESCE(v.score, 0) AS score,
                COALESCE(c.comment_count, 0) AS comment_count,
                COALESCE(val.validation_count, 0) AS validation_count
            FROM ideas i
            INNER JOIN users u ON u.id = i.user_id
            LEFT JOIN (
                SELECT idea_id, SUM(vote) AS score
                FROM idea_votes
                GROUP BY idea_id
            ) v ON v.idea_id = i.id
            LEFT JOIN (
                SELECT idea_id, COUNT(*) AS comment_count
                FROM idea_comments
                GROUP BY idea_id
            ) c ON c.idea_id = i.id
            LEFT JOIN (
                SELECT idea_id, COUNT(*) AS validation_count
                FROM validation_votes
                GROUP BY idea_id
            ) val ON val.idea_id = i.id
            WHERE i.status = \'published\'
              AND i.created_at >= DATE_FORMAT(CURDATE(), \'%Y-%m-01\')
              AND i.created_at < DATE_ADD(DATE_FORMAT(CURDATE(), \'%Y-%m-01\'), INTERVAL 1 MONTH)
            ORDER BY ((COALESCE(v.score, 0) * 4) + (COALESCE(c.comment_count, 0) * 2) + COALESCE(val.validation_count, 0)) DESC,
                     i.created_at DESC
            LIMIT 3
            '
        );

        return [
            'month_title' => date('F Y'),
            'leader' => $leaderboard[0] ?? null,
            'top_three' => $leaderboard,
        ];
    }

    public function findIdea(int $ideaId, ?int $viewerId = null): ?array
    {
        $items = $this->listIdeas(['id' => $ideaId], $viewerId, true);

        return $items[0] ?? null;
    }

    public function listComments(int $ideaId): array
    {
        return $this->db->fetchAll(
            '
            SELECT c.id, c.content, c.created_at, u.id AS user_id, u.name, u.role
            FROM idea_comments c
            INNER JOIN users u ON u.id = c.user_id
            WHERE c.idea_id = :idea_id
            ORDER BY c.created_at DESC
            ',
            ['idea_id' => $ideaId]
        );
    }

    public function voteIdea(int $ideaId, int $userId, int $vote): array
    {
        if (!in_array($vote, [-1, 1], true)) {
            return ['success' => false, 'message' => 'Noto\'g\'ri ovoz turi.'];
        }

        $idea = $this->db->fetchOne(
            'SELECT id FROM ideas WHERE id = :id AND status = \'published\' LIMIT 1',
            ['id' => $ideaId]
        );

        if ($idea === null) {
            return ['success' => false, 'message' => 'G\'oya topilmadi.'];
        }

        $this->db->execute(
            '
            INSERT INTO idea_votes (idea_id, user_id, vote, created_at, updated_at)
            VALUES (:idea_id, :user_id, :vote, NOW(), NOW())
            ON DUPLICATE KEY UPDATE vote = VALUES(vote), updated_at = NOW()
            ',
            [
                'idea_id' => $ideaId,
                'user_id' => $userId,
                'vote' => $vote,
            ]
        );

        return ['success' => true, 'summary' => $this->reactionSummary($ideaId, $userId)];
    }

    public function addComment(int $ideaId, int $userId, string $content): array
    {
        $content = trim($content);

        if ($content === '') {
            return ['success' => false, 'message' => 'Izoh matnini kiriting.'];
        }

        if (mb_strlen($content) < 3) {
            return ['success' => false, 'message' => 'Izoh biroz batafsilroq bo\'lsin.'];
        }

        $idea = $this->db->fetchOne(
            'SELECT id FROM ideas WHERE id = :id AND status = \'published\' LIMIT 1',
            ['id' => $ideaId]
        );

        if ($idea === null) {
            return ['success' => false, 'message' => 'Izoh qoldirish uchun g\'oya topilmadi.'];
        }

        $commentId = $this->db->insert(
            '
            INSERT INTO idea_comments (idea_id, user_id, content, created_at)
            VALUES (:idea_id, :user_id, :content, NOW())
            ',
            [
                'idea_id' => $ideaId,
                'user_id' => $userId,
                'content' => mb_substr($content, 0, 1000),
            ]
        );

        $comment = $this->db->fetchOne(
            '
            SELECT c.id, c.content, c.created_at, u.id AS user_id, u.name, u.role
            FROM idea_comments c
            INNER JOIN users u ON u.id = c.user_id
            WHERE c.id = :id
            LIMIT 1
            ',
            ['id' => $commentId]
        );

        $count = $this->db->fetchOne(
            'SELECT COUNT(*) AS total FROM idea_comments WHERE idea_id = :idea_id',
            ['idea_id' => $ideaId]
        );

        return [
            'success' => true,
            'comment' => $comment,
            'comment_count' => (int) ($count['total'] ?? 0),
        ];
    }

    public function addValidationVote(int $ideaId, int $userId, string $signal): array
    {
        $allowed = ['use', 'pay', 'mentor', 'invest'];

        if (!in_array($signal, $allowed, true)) {
            return ['success' => false, 'message' => 'Validatsiya signali noto\'g\'ri.'];
        }

        $idea = $this->db->fetchOne(
            'SELECT id FROM ideas WHERE id = :id AND status = \'published\' LIMIT 1',
            ['id' => $ideaId]
        );

        if ($idea === null) {
            return ['success' => false, 'message' => 'G\'oya topilmadi.'];
        }

        $this->db->execute(
            '
            INSERT INTO validation_votes (idea_id, user_id, `signal`, created_at, updated_at)
            VALUES (:idea_id, :user_id, :signal, NOW(), NOW())
            ON DUPLICATE KEY UPDATE `signal` = VALUES(`signal`), updated_at = NOW()
            ',
            [
                'idea_id' => $ideaId,
                'user_id' => $userId,
                'signal' => $signal,
            ]
        );

        return ['success' => true, 'summary' => $this->validationSummary($ideaId, $userId)];
    }

    public function profile(int $userId): ?array
    {
        return $this->db->fetchOne(
            '
            SELECT
                u.id,
                u.name,
                u.email,
                u.role,
                u.bio,
                u.location,
                u.expertise,
                u.contact_link,
                u.collaboration_status,
                u.created_at,
                u.last_seen,
                COUNT(i.id) AS ideas_count,
                COALESCE(SUM(COALESCE(v.score, 0)), 0) AS total_score,
                SUM(CASE WHEN i.collaboration_open = 1 THEN 1 ELSE 0 END) AS open_ideas_count
            FROM users u
            LEFT JOIN ideas i ON i.user_id = u.id
            LEFT JOIN (
                SELECT idea_id, SUM(vote) AS score
                FROM idea_votes
                GROUP BY idea_id
            ) v ON v.idea_id = i.id
            WHERE u.id = :id
            GROUP BY u.id
            LIMIT 1
            ',
            ['id' => $userId]
        );
    }

    public function adminDashboard(): array
    {
        $metrics = $this->db->fetchOne(
            '
            SELECT
                COUNT(*) AS total_users,
                SUM(CASE WHEN role = \'investor\' THEN 1 ELSE 0 END) AS investors,
                SUM(CASE WHEN role = \'mentor\' THEN 1 ELSE 0 END) AS mentors
            FROM users
            '
        ) ?? [];

        $ideas = $this->db->fetchOne(
            '
            SELECT
                COUNT(*) AS total_ideas,
                SUM(CASE WHEN status = \'published\' THEN 1 ELSE 0 END) AS published_ideas,
                SUM(CASE WHEN status = \'archived\' THEN 1 ELSE 0 END) AS archived_ideas
            FROM ideas
            '
        ) ?? [];

        $recentUsers = $this->db->fetchAll(
            '
            SELECT id, name, email, role, created_at
            FROM users
            ORDER BY created_at DESC
            LIMIT 8
            '
        );

        $recentIdeas = $this->db->fetchAll(
            '
            SELECT
                i.id,
                i.title,
                i.status,
                i.created_at,
                i.sector,
                u.name AS author_name,
                COALESCE(v.score, 0) AS score
            FROM ideas i
            INNER JOIN users u ON u.id = i.user_id
            LEFT JOIN (
                SELECT idea_id, SUM(vote) AS score
                FROM idea_votes
                GROUP BY idea_id
            ) v ON v.idea_id = i.id
            ORDER BY i.created_at DESC
            LIMIT 12
            '
        );

        return [
            'metrics' => array_merge($metrics, $ideas),
            'recent_users' => $recentUsers,
            'recent_ideas' => $recentIdeas,
        ];
    }

    public function updateIdea(int $ideaId, int $userId, bool $isAdmin, array $data, ?array $coverFile = null): array
    {
        $idea = $this->db->fetchOne(
            'SELECT id, user_id, cover_image FROM ideas WHERE id = :id LIMIT 1',
            ['id' => $ideaId]
        );

        if ($idea === null) {
            return ['success' => false, 'message' => 'G\'oya topilmadi.'];
        }

        if (!$isAdmin && (int) $idea['user_id'] !== $userId) {
            return ['success' => false, 'message' => 'Bu g\'oyani tahrirlash uchun ruxsat yo\'q.'];
        }

        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $sector = trim((string) ($data['sector'] ?? ''));
        $stage = trim((string) ($data['stage'] ?? 'idea'));
        $location = trim((string) ($data['location'] ?? ''));
        $targetAudience = trim((string) ($data['target_audience'] ?? ''));
        $marketNote = trim((string) ($data['market_note'] ?? ''));
        $monetization = trim((string) ($data['monetization'] ?? ''));
        $investmentNeeded = !empty($data['investment_needed']) ? 1 : 0;
        $collaborationOpen = !empty($data['collaboration_open']) ? 1 : 0;
        $seekingRoles = trim((string) ($data['seeking_roles'] ?? ''));
        $budgetFrom = is_numeric($data['budget_from'] ?? null) ? (float) $data['budget_from'] : null;
        $budgetTo = is_numeric($data['budget_to'] ?? null) ? (float) $data['budget_to'] : null;

        if ($title === '' || $description === '' || $sector === '') {
            return ['success' => false, 'message' => 'Sarlavha, tavsif va soha maydonlari majburiy.'];
        }

        if (mb_strlen($title) < 6) {
            return ['success' => false, 'message' => 'Sarlavha kamida 6 ta belgidan iborat bo\'lsin.'];
        }

        if (mb_strlen($description) < 40) {
            return ['success' => false, 'message' => 'Tavsifni biroz batafsilroq yozing. Kamida 40 belgi kerak.'];
        }

        if (!in_array($stage, $this->stages(), true)) {
            $stage = 'idea';
        }

        // Cover rasm yangilash
        $coverImage = $idea['cover_image'] ?? null;
        if ($coverFile !== null && ($coverFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $uploaded = upload_image($coverFile, 'ideas');
            if ($uploaded !== null) {
                // Eski rasmni o'chirish
                if (!empty($coverImage)) {
                    $oldPath = __DIR__ . '/../../storage/ideas/' . $coverImage;
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $coverImage = $uploaded;
            }
        }

        $this->db->execute(
            'UPDATE ideas SET
                title = :title, description = :description, sector = :sector, stage = :stage,
                location = :location, investment_needed = :investment_needed,
                budget_from = :budget_from, budget_to = :budget_to,
                target_audience = :target_audience, market_note = :market_note,
                monetization = :monetization, collaboration_open = :collaboration_open,
                seeking_roles = :seeking_roles, cover_image = :cover_image, updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $ideaId,
                'title' => mb_substr($title, 0, 140),
                'description' => mb_substr($description, 0, 5000),
                'sector' => mb_substr($sector, 0, 120),
                'stage' => $stage,
                'location' => mb_substr($location, 0, 120),
                'investment_needed' => $investmentNeeded,
                'budget_from' => $budgetFrom,
                'budget_to' => $budgetTo,
                'target_audience' => mb_substr($targetAudience, 0, 255),
                'market_note' => mb_substr($marketNote, 0, 1000),
                'monetization' => mb_substr($monetization, 0, 255),
                'collaboration_open' => $collaborationOpen,
                'seeking_roles' => mb_substr($seekingRoles, 0, 255),
                'cover_image' => $coverImage,
            ]
        );

        return ['success' => true, 'message' => 'G\'oya muvaffaqiyatli yangilandi.'];
    }

    public function deleteIdea(int $ideaId, int $userId, bool $isAdmin): array
    {
        $idea = $this->db->fetchOne(
            'SELECT id, user_id FROM ideas WHERE id = :id LIMIT 1',
            ['id' => $ideaId]
        );

        if ($idea === null) {
            return ['success' => false, 'message' => 'G\'oya topilmadi.'];
        }

        if (!$isAdmin && (int) $idea['user_id'] !== $userId) {
            return ['success' => false, 'message' => 'Bu g\'oyani o\'chirish uchun ruxsat yo\'q.'];
        }

        // Bog'liq ma'lumotlarni ham o'chirish
        $this->db->execute('DELETE FROM idea_votes WHERE idea_id = :id', ['id' => $ideaId]);
        $this->db->execute('DELETE FROM idea_comments WHERE idea_id = :id', ['id' => $ideaId]);
        $this->db->execute('DELETE FROM validation_votes WHERE idea_id = :id', ['id' => $ideaId]);
        $this->db->execute('DELETE FROM ideas WHERE id = :id', ['id' => $ideaId]);

        return ['success' => true, 'message' => 'G\'oya o\'chirildi.'];
    }

    public function updateIdeaStatus(int $ideaId, string $status): array
    {
        if (!in_array($status, ['published', 'archived'], true)) {
            return ['success' => false, 'message' => 'Noto\'g\'ri holat.'];
        }

        $this->db->execute(
            'UPDATE ideas SET status = :status, updated_at = NOW() WHERE id = :id',
            [
                'id' => $ideaId,
                'status' => $status,
            ]
        );

        return ['success' => true, 'message' => 'G\'oya holati yangilandi.'];
    }

    public function updateUserRole(int $userId, string $role): array
    {
        if (!in_array($role, ['founder', 'investor', 'mentor', 'admin'], true)) {
            return ['success' => false, 'message' => 'Ruxsat etilmagan rol.'];
        }

        $this->db->execute(
            'UPDATE users SET role = :role WHERE id = :id',
            [
                'id' => $userId,
                'role' => $role,
            ]
        );

        return ['success' => true, 'message' => 'Foydalanuvchi roli yangilandi.'];
    }

    private function reactionSummary(int $ideaId, int $userId): array
    {
        return $this->db->fetchOne(
            '
            SELECT
                COALESCE(SUM(CASE WHEN vote = 1 THEN 1 ELSE 0 END), 0) AS likes,
                COALESCE(SUM(CASE WHEN vote = -1 THEN 1 ELSE 0 END), 0) AS dislikes,
                COALESCE(SUM(vote), 0) AS score,
                COALESCE(MAX(CASE WHEN user_id = :user_id THEN vote ELSE 0 END), 0) AS user_vote
            FROM idea_votes
            WHERE idea_id = :idea_id
            ',
            [
                'idea_id' => $ideaId,
                'user_id' => $userId,
            ]
        ) ?? [
            'likes' => 0,
            'dislikes' => 0,
            'score' => 0,
            'user_vote' => 0,
        ];
    }

    private function validationSummary(int $ideaId, int $userId): array
    {
        return $this->db->fetchOne(
            '
            SELECT
                COUNT(*) AS validation_count,
                SUM(CASE WHEN `signal` = \'use\' THEN 1 ELSE 0 END) AS use_count,
                SUM(CASE WHEN `signal` = \'pay\' THEN 1 ELSE 0 END) AS pay_count,
                SUM(CASE WHEN `signal` = \'mentor\' THEN 1 ELSE 0 END) AS mentor_count,
                SUM(CASE WHEN `signal` = \'invest\' THEN 1 ELSE 0 END) AS invest_count,
                MAX(CASE WHEN user_id = :user_id THEN `signal` ELSE NULL END) AS user_signal
            FROM validation_votes
            WHERE idea_id = :idea_id
            ',
            [
                'idea_id' => $ideaId,
                'user_id' => $userId,
            ]
        ) ?? [
            'validation_count' => 0,
            'use_count' => 0,
            'pay_count' => 0,
            'mentor_count' => 0,
            'invest_count' => 0,
            'user_signal' => null,
        ];
    }
}
