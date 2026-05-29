<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

spl_autoload_register(function (string $className): void {
    $candidates = [
        __DIR__ . '/core/' . $className . '.php',
        __DIR__ . '/controllers/' . $className . '.php',
        __DIR__ . '/models/' . $className . '.php',
    ];

    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) {
            require_once $candidate;
            return;
        }
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function redirectTo(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function setFlash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function getFlash(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $user = (new User())->findById((int) $_SESSION['user_id']);

    if ($user === null) {
        unset($_SESSION['user_id']);
    }

    return $user;
}

function requireAuthentication(): array
{
    $user = currentUser();

    if ($user === null) {
        setFlash('error', 'Please log in to access that page.');
        redirectTo('/login');
    }

    return $user;
}

function loginUser(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_username'] = $user['username'];
}

function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function renderView(string $viewPath, array $data = []): void
{
    $viewFile = __DIR__ . '/views/' . $viewPath . '.php';

    if (!file_exists($viewFile)) {
        http_response_code(500);
        echo 'View not found: ' . htmlspecialchars($viewPath, ENT_QUOTES, 'UTF-8');
        return;
    }

    extract($data, EXTR_SKIP);
    require __DIR__ . '/views/layouts/header.php';
    require $viewFile;
    require __DIR__ . '/views/layouts/footer.php';
}
