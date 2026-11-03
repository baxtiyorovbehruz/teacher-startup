<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'message' => 'Faqat POST so\'rovlari qabul qilinadi.'], 405);
}

$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? null);

if (!verify_csrf($token)) {
    json_response(['success' => false, 'message' => 'Xavfsizlik tokeni yaroqsiz.'], 419);
}

if ($dbError !== null) {
    json_response(['success' => false, 'message' => 'MySQL ulanishi sozlanmagan.'], 500);
}

if ($auth === null || !$auth->check()) {
    json_response(['success' => false, 'message' => 'Bu amal uchun tizimga kirish kerak.'], 401);
}

$payload = json_decode((string) file_get_contents('php://input'), true);

if (!is_array($payload)) {
    $payload = $_POST;
}

$action = (string) ($_GET['action'] ?? $payload['action'] ?? '');
$userId = (int) $auth->id();

if ($action === 'vote') {
    $result = $ideas->voteIdea((int) ($payload['idea_id'] ?? 0), $userId, (int) ($payload['vote'] ?? 0));
    json_response($result, $result['success'] ? 200 : 422);
}

if ($action === 'comment') {
    $result = $ideas->addComment((int) ($payload['idea_id'] ?? 0), $userId, (string) ($payload['content'] ?? ''));
    json_response($result, $result['success'] ? 200 : 422);
}

if ($action === 'validate') {
    $result = $ideas->addValidationVote((int) ($payload['idea_id'] ?? 0), $userId, (string) ($payload['signal'] ?? ''));
    json_response($result, $result['success'] ? 200 : 422);
}

json_response(['success' => false, 'message' => 'Noma\'lum API action.'], 404);
