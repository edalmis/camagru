<?php

declare(strict_types=1);

loadEnvironmentFile(dirname(__DIR__) . '/.env');

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

function loadEnvironmentFile(string $filePath): void
{
    if (!is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmedLine = trim($line);

        if ($trimmedLine === '' || str_starts_with($trimmedLine, '#') || str_starts_with($trimmedLine, ';')) {
            continue;
        }

        if (str_starts_with($trimmedLine, 'export ')) {
            $trimmedLine = trim(substr($trimmedLine, 7));
        }

        $separatorPosition = strpos($trimmedLine, '=');

        if ($separatorPosition === false) {
            continue;
        }

        $key = trim(substr($trimmedLine, 0, $separatorPosition));
        $value = trim(substr($trimmedLine, $separatorPosition + 1));

        if ($key === '') {
            continue;
        }

        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        if (getenv($key) !== false) {
            continue;
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
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

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    if (!is_string($token) || !isset($_SESSION['csrf_token'])) {
        return false;
    }

    $submittedToken = trim($token);
    $sessionToken = trim((string) $_SESSION['csrf_token']);

    return $submittedToken !== '' && $sessionToken !== '' && hash_equals($sessionToken, $submittedToken);
}

function sanitizeFileName(string $fileName): string
{
    $baseName = pathinfo($fileName, PATHINFO_FILENAME);
    $baseName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $baseName) ?? 'upload';
    $baseName = trim($baseName, '-');

    return $baseName === '' ? 'upload' : $baseName;
}

function appBaseUrl(): string
{
    $baseUrl = getenv('APP_URL');

    if (!is_string($baseUrl) || trim($baseUrl) === '') {
        return 'http://localhost:8080';
    }

    return rtrim(trim($baseUrl), '/');
}

function mailConfig(string $key, ?string $default = null): ?string
{
    $value = getenv($key);

    if ($value === false || trim($value) === '') {
        return $default;
    }

    return trim($value);
}

function smtpSendMail(string $to, string $subject, string $message): bool
{
    $host = mailConfig('SMTP_HOST');

    if ($host === null) {
        return false;
    }

    $port = (int) mailConfig('SMTP_PORT', '587');
    $timeout = 10;
    $transport = 'tcp://' . $host . ':' . $port;
    $socket = @stream_socket_client($transport, $errorNumber, $errorMessage, $timeout);

    if ($socket === false) {
        error_log('SMTP connection failed: ' . $errorMessage);
        return false;
    }

    stream_set_timeout($socket, $timeout);

    $encryption = strtolower((string) mailConfig('SMTP_ENCRYPTION', 'tls'));
    if ($encryption === 'tls') {
        smtpReadResponse($socket, [220]);
        fwrite($socket, "EHLO localhost\r\n");
        smtpReadResponse($socket, [250]);
        fwrite($socket, "STARTTLS\r\n");
        smtpReadResponse($socket, [220]);
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            error_log('SMTP STARTTLS negotiation failed');
            return false;
        }
        fwrite($socket, "EHLO localhost\r\n");
        smtpReadResponse($socket, [250]);
    } else {
        smtpReadResponse($socket, [220]);
        fwrite($socket, "EHLO localhost\r\n");
        smtpReadResponse($socket, [250]);
    }

    $username = mailConfig('SMTP_USER');
    $password = mailConfig('SMTP_PASS');

    if ($username !== null && $password !== null) {
        fwrite($socket, "AUTH LOGIN\r\n");
        smtpReadResponse($socket, [334]);
        fwrite($socket, base64_encode($username) . "\r\n");
        smtpReadResponse($socket, [334]);
        fwrite($socket, base64_encode($password) . "\r\n");
        smtpReadResponse($socket, [235]);
    }

    $from = mailConfig('MAIL_FROM', 'no-reply@camagru.local');
    $fromName = mailConfig('MAIL_FROM_NAME', 'Camagru');
    $headers = [
        'From: ' . $fromName . ' <' . $from . '>',
        'To: ' . $to,
        'Subject: ' . $subject,
        'Reply-To: ' . $from,
        'Content-Type: text/plain; charset=UTF-8',
        'MIME-Version: 1.0',
    ];

    fwrite($socket, 'MAIL FROM:<' . $from . ">\r\n");
    smtpReadResponse($socket, [250]);
    fwrite($socket, 'RCPT TO:<' . $to . ">\r\n");
    smtpReadResponse($socket, [250, 251]);
    fwrite($socket, "DATA\r\n");
    smtpReadResponse($socket, [354]);
    fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $message . "\r\n.\r\n");
    smtpReadResponse($socket, [250]);
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    return true;
}

function smtpReadResponse($socket, array $expectedCodes): array
{
    $responseLines = [];
    $lastLine = '';

    while (($line = fgets($socket)) !== false) {
        $responseLines[] = rtrim($line, "\r\n");
        $lastLine = $line;
        if (strlen($line) < 4 || $line[3] !== '-') {
            break;
        }
    }

    $statusCode = (int) substr($lastLine, 0, 3);

    if (!in_array($statusCode, $expectedCodes, true)) {
        throw new RuntimeException('SMTP unexpected response: ' . implode(' | ', $responseLines));
    }

    return $responseLines;
}

function sendMailMessage(string $to, string $subject, string $message): bool
{
    $headers = [
        'From: Camagru <no-reply@camagru.local>',
        'Reply-To: no-reply@camagru.local',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $headerString = implode("\r\n", $headers);
    $smtpHost = mailConfig('SMTP_HOST');

    if ($smtpHost === null) {
        error_log('SMTP is not configured; set SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_ENCRYPTION, MAIL_FROM, and MAIL_FROM_NAME in .env to send through Gmail.');
    }

    try {
        if (smtpSendMail($to, $subject, $message)) {
            return true;
        }
    } catch (Throwable $exception) {
        error_log('SMTP send failed: ' . $exception->getMessage());
    }

    $safeRecipient = preg_replace('/[^a-zA-Z0-9@._-]+/', '_', $to) ?? 'recipient';
    $fileName = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '_' . $safeRecipient . '.eml';
    $mailContent = "To: {$to}\nSubject: {$subject}\n{$headerString}\n\n{$message}\n";

    // Local/dev fallback: persist mail content when no SMTP/sendmail transport is available.
    $fallbackDirectories = [
        '/tmp/camagru-mail',
        __DIR__ . '/../storage/mail',
    ];

    foreach ($fallbackDirectories as $mailDirectory) {
        if (!is_dir($mailDirectory) && !@mkdir($mailDirectory, 0775, true) && !is_dir($mailDirectory)) {
            continue;
        }

        if (!is_writable($mailDirectory)) {
            continue;
        }

        $filePath = $mailDirectory . '/' . $fileName;
        $bytesWritten = @file_put_contents($filePath, $mailContent, LOCK_EX);

        if ($bytesWritten !== false) {
            error_log('Mail transport unavailable; wrote message to ' . $filePath);
            return false;
        }
    }

    error_log('Mail fallback failed: unable to persist message for ' . $to);
    return false;
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

function renderErrorPage(int $statusCode, string $viewPath, array $data = []): void
{
    http_response_code($statusCode);
    renderView($viewPath, $data);
}
