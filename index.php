<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$page = (string) ($_GET['page'] ?? 'home');
$user = current_user();
$publicPages = ['login', 'register', 'forgot-password', 'reset-password'];
$pageTitle = 'Tadbirkor.uz';
$pageClass = 'page-' . preg_replace('/[^a-z0-9\-]+/i', '-', $page);

if (is_post()) {
    ensure_csrf($_POST['_csrf'] ?? null);
    $action = (string) ($_POST['form_action'] ?? '');
    $redirectTo = (string) ($_POST['redirect_to'] ?? current_url(['page' => $page]));

    if ($dbError !== null) {
        flash('error', 'Avval MySQL ulanishini sozlab, schema import qiling.');
        redirect($redirectTo);
    }

    if ($action === 'register') {
        store_old($_POST);
        $result = $auth->register($_POST);
        flash($result['success'] ? 'success' : 'error', $result['message']);
        if ($result['success']) {
            clear_old();
            redirect('/?page=home');
        }
        redirect($redirectTo);
    }

    if ($action === 'login') {
        store_old($_POST);
        $result = $auth->login((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));
        flash($result['success'] ? 'success' : 'error', $result['message']);
        if ($result['success']) {
            clear_old();
            redirect('/?page=home');
        }
        redirect($redirectTo);
    }

    if ($action === 'forgot_password') {
        $result = $auth->forgotPassword((string) ($_POST['email'] ?? ''));
        flash($result['success'] ? 'success' : 'error', $result['message']);
        redirect('/?page=forgot-password');
    }

    if ($action === 'reset_password') {
        $token = (string) ($_POST['token'] ?? '');
        $result = $auth->resetPassword($token, (string) ($_POST['password'] ?? ''));
        flash($result['success'] ? 'success' : 'error', $result['message']);
        if ($result['success']) {
            redirect('/?page=login');
        }
        redirect('/?page=reset-password&token=' . urlencode($token));
    }

    if ($action === 'create_idea') {
        if ($user === null) {
            flash('error', 'G\'oya joylash uchun avval tizimga kiring.');
            redirect('/?page=login');
        }

        store_old($_POST);
        $coverFile = $_FILES['cover_image'] ?? null;
        $result = $ideas->createIdea((int) $user['id'], $_POST, $coverFile);
        flash($result['success'] ? 'success' : 'error', $result['message']);
        if ($result['success']) {
            clear_old();
            redirect('/?page=home');
        }
        redirect($redirectTo);
    }

    if ($action === 'update_profile') {
        if ($user === null) {
            flash('error', 'Profilni yangilash uchun tizimga kiring.');
            redirect('/?page=login');
        }

        $avatarFile = $_FILES['avatar'] ?? null;
        $result = $auth->updateProfile((int) $user['id'], $_POST, $avatarFile);
        flash($result['success'] ? 'success' : 'error', $result['message']);
        redirect($redirectTo);
    }

    if ($action === 'update_idea') {
        if ($user === null) {
            flash('error', 'G\'oyani tahrirlash uchun tizimga kiring.');
            redirect('/?page=login');
        }

        $ideaId = (int) ($_POST['idea_id'] ?? 0);
        store_old($_POST);
        $coverFile = $_FILES['cover_image'] ?? null;
        $result = $ideas->updateIdea($ideaId, (int) $user['id'], $auth->isAdmin(), $_POST, $coverFile);
        flash($result['success'] ? 'success' : 'error', $result['message']);
        if ($result['success']) {
            clear_old();
            redirect('/?page=idea&id=' . $ideaId);
        }
        redirect($redirectTo);
    }

    if ($action === 'delete_idea') {
        if ($user === null) {
            flash('error', 'G\'oyani o\'chirish uchun tizimga kiring.');
            redirect('/?page=login');
        }

        $ideaId = (int) ($_POST['idea_id'] ?? 0);
        $result = $ideas->deleteIdea($ideaId, (int) $user['id'], $auth->isAdmin());
        flash($result['success'] ? 'success' : 'error', $result['message']);
        redirect($result['success'] ? '/?page=home' : $redirectTo);
    }

    if ($action === 'admin_idea_status') {
        if ($user === null || !$auth->isAdmin()) {
            flash('error', 'Bu amal faqat admin uchun.');
            redirect('/?page=home');
        }

        $result = $ideas->updateIdeaStatus((int) ($_POST['idea_id'] ?? 0), (string) ($_POST['status'] ?? ''));
        flash($result['success'] ? 'success' : 'error', $result['message']);
        redirect($redirectTo);
    }

    if ($action === 'admin_user_role') {
        if ($user === null || !$auth->isAdmin()) {
            flash('error', 'Bu amal faqat admin uchun.');
            redirect('/?page=home');
        }

        $result = $ideas->updateUserRole((int) ($_POST['user_id'] ?? 0), (string) ($_POST['role'] ?? ''));
        flash($result['success'] ? 'success' : 'error', $result['message']);
        redirect($redirectTo);
    }
}

if ($page === 'logout') {
    if ($auth !== null) {
        $auth->logout();
    }

    flash('success', 'Sessiya yopildi.');
    redirect('/?page=login');
}

if ($user === null && !in_array($page, $publicPages, true)) {
    flash('error', 'Tizimdan foydalanish uchun avval ro\'yxatdan o\'ting yoki akkauntingizga kiring.');
    redirect('/?page=login');
}

if ($user !== null && in_array($page, $publicPages, true)) {
    redirect('/?page=home');
}

if ($page === 'register') {
    $pageTitle = 'Ro\'yxatdan o\'tish';
    render('register', ['page' => $page, 'pageTitle' => $pageTitle, 'pageClass' => $pageClass]);
    clear_old();
    return;
}

if ($page === 'login') {
    $pageTitle = 'Kirish';
    render('login', ['page' => $page, 'pageTitle' => $pageTitle, 'pageClass' => $pageClass]);
    clear_old();
    return;
}

if ($page === 'forgot-password') {
    $pageTitle = 'Parolni tiklash';
    render('forgot-password', ['page' => $page, 'pageTitle' => $pageTitle, 'pageClass' => $pageClass]);
    return;
}

if ($page === 'reset-password') {
    $token = trim((string) ($_GET['token'] ?? ''));
    $pageTitle = 'Yangi parol o\'rnatish';
    render('reset-password', ['page' => $page, 'pageTitle' => $pageTitle, 'pageClass' => $pageClass, 'token' => $token]);
    return;
}

if ($page === 'create') {
    if ($user === null) {
        flash('error', 'G\'oya joylash uchun avval tizimga kiring.');
        redirect('/?page=login');
    }

    $pageTitle = 'Yangi g\'oya qo\'shish';
    render('idea-form', [
        'page' => $page,
        'pageTitle' => $pageTitle,
        'pageClass' => $pageClass,
        'sectors' => $ideas ? $ideas->sectors() : [],
        'stages' => $ideas ? $ideas->stages() : [],
    ]);
    clear_old();
    return;
}

if ($page === 'edit') {
    if ($user === null) {
        flash('error', 'G\'oyani tahrirlash uchun avval tizimga kiring.');
        redirect('/?page=login');
    }

    if ($dbError !== null) {
        flash('error', 'G\'oyani tahrirlash uchun baza ulanishi kerak.');
        redirect('/?page=home');
    }

    $ideaId = (int) ($_GET['id'] ?? 0);
    $idea = $ideas->findIdea($ideaId, (int) $user['id']);

    if ($idea === null) {
        flash('error', 'G\'oya topilmadi.');
        redirect('/?page=home');
    }

    $canEdit = $auth->isAdmin() || (int) $idea['user_id'] === (int) $user['id'];
    if (!$canEdit) {
        flash('error', 'Bu g\'oyani tahrirlash uchun ruxsat yo\'q.');
        redirect('/?page=idea&id=' . $ideaId);
    }

    $pageTitle = 'G\'oyani tahrirlash';
    render('idea-form', [
        'page' => $page,
        'pageTitle' => $pageTitle,
        'pageClass' => $pageClass,
        'idea' => $idea,
        'sectors' => $ideas->sectors(),
        'stages' => $ideas->stages(),
    ]);
    clear_old();
    return;
}

if ($page === 'profile') {
    if ($dbError !== null) {
        flash('error', 'Profilni ko\'rish uchun baza ulanishi kerak.');
        redirect('/?page=home');
    }

    $profileId = (int) ($_GET['id'] ?? ($user['id'] ?? 0));
    $profile = $ideas->profile($profileId);

    if ($profile === null) {
        flash('error', 'Profil topilmadi.');
        redirect('/?page=home');
    }

    $pageTitle = $profile['name'] . ' profili';
    render('profile', [
        'page' => $page,
        'pageTitle' => $pageTitle,
        'pageClass' => $pageClass,
        'profile' => $profile,
        'ideasList' => $ideas->listIdeas(
            ['user_id' => $profileId, 'sort' => 'new'],
            $user['id'] ?? null,
            ($user && ((int) $user['id'] === $profileId || $auth->isAdmin()))
        ),
    ]);
    clear_old();
    return;
}

if ($page === 'idea') {
    if ($dbError !== null) {
        flash('error', 'G\'oya sahifasini ko\'rish uchun baza ulanishi kerak.');
        redirect('/?page=home');
    }

    $ideaId = (int) ($_GET['id'] ?? 0);
    $idea = $ideas->findIdea($ideaId, $user['id'] ?? null);
    $canSeeArchived = $user && ($auth->isAdmin() || ((int) $user['id'] === (int) ($idea['user_id'] ?? 0)));

    if ($idea === null || ($idea['status'] === 'archived' && !$canSeeArchived)) {
        flash('error', 'G\'oya topilmadi yoki arxivlangan.');
        redirect('/?page=home');
    }

    $pageTitle = $idea['title'];
    render('idea', [
        'page' => $page,
        'pageTitle' => $pageTitle,
        'pageClass' => $pageClass,
        'idea' => $idea,
        'comments' => $ideas->listComments($ideaId),
    ]);
    clear_old();
    return;
}

if ($page === 'network') {
    if ($dbError !== null) {
        flash('error', 'Tarmoq bo\'limi uchun baza ulanishi kerak.');
        redirect('/?page=home');
    }

    $networkFilters = [
        'q' => trim((string) ($_GET['q'] ?? '')),
        'role' => trim((string) ($_GET['role'] ?? '')),
        'status' => trim((string) ($_GET['status'] ?? '')),
        'sort' => trim((string) ($_GET['sort'] ?? 'active')) ?: 'active',
    ];

    render('network', [
        'page' => $page,
        'pageTitle' => 'Tarmoq',
        'pageClass' => $pageClass,
        'people' => $ideas->peopleDirectory($networkFilters),
        'networkFilters' => $networkFilters,
        'collaborationStatuses' => $ideas->collaborationStatuses(),
    ]);
    clear_old();
    return;
}

if ($page === 'admin') {
    if ($user === null || !$auth->isAdmin()) {
        flash('error', 'Boshqaruv paneli faqat admin uchun ochiladi.');
        redirect('/?page=home');
    }

    $pageTitle = 'Boshqaruv paneli';
    render('admin', [
        'page' => $page,
        'pageTitle' => $pageTitle,
        'pageClass' => $pageClass,
        'dashboard' => $ideas->adminDashboard(),
        'contest' => $ideas->monthlyContest(),
    ]);
    clear_old();
    return;
}

$currentPage = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 12;

$filters = [
    'q' => trim((string) ($_GET['q'] ?? '')),
    'sector' => trim((string) ($_GET['sector'] ?? '')),
    'stage' => trim((string) ($_GET['stage'] ?? '')),
    'sort' => trim((string) ($_GET['sort'] ?? 'trend')) ?: 'trend',
    'page' => $currentPage,
];

$homeStats = [
    'total_users' => 0,
    'investors' => 0,
    'mentors' => 0,
    'total_ideas' => 0,
];
$homeIdeas = [];
$totalIdeas = 0;
$totalPages = 1;
$contest = ['month_title' => date('F Y'), 'leader' => null, 'top_three' => []];
$featuredPeople = [];

if ($dbError === null && $ideas !== null) {
    $homeStats = $ideas->featuredStats();
    $homeIdeas = $ideas->listIdeas($filters, $user['id'] ?? null);
    $totalIdeas = $ideas->countIdeas($filters);
    $totalPages = (int) ceil($totalIdeas / $perPage);
    $contest = $ideas->monthlyContest();
    $featuredPeople = $ideas->featuredPeople(null, 4);
}

render('home', [
    'page' => 'home',
    'pageTitle' => 'Yoshlar biznes g\'oyalari platformasi',
    'pageClass' => $pageClass,
    'stats' => $homeStats,
    'ideasList' => $homeIdeas,
    'filters' => $filters,
    'currentPage' => $currentPage,
    'totalPages' => $totalPages,
    'totalIdeas' => $totalIdeas,
    'contest' => $contest,
    'featuredPeople' => $featuredPeople,
    'collaborationStatuses' => $ideas ? $ideas->collaborationStatuses() : [],
    'sectors' => $ideas ? $ideas->sectors() : [],
    'stages' => $ideas ? $ideas->stages() : [],
]);
clear_old();
