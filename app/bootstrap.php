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
