<?php

declare(strict_types=1);

final class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getDBConnection();
    }

    public function create(string $username, string $email, string $password): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (username, email, password_hash, email_verified_at) VALUES (:username, :email, :password_hash, NULL)'
        );

        $statement->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function register(string $username, string $email, string $password, string $confirmPassword): array
    {
        $errors = $this->validateRegistration($username, $email, $password, $confirmPassword);

        if ($this->findByUsername($username) !== null) {
            $errors[] = 'Username is already taken.';
        }

        if ($this->findByEmail($email) !== null) {
            $errors[] = 'Email is already registered.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $userId = $this->create($username, $email, $password);
        $user = $this->findById($userId);

        if ($user === null) {
            throw new RuntimeException('Unable to load the newly created user.');
        }

        return $user;
    }

    public function authenticate(string $username, string $password): array
    {
        $username = trim($username);

        if ($username === '' || $password === '') {
            throw new ValidationException(['Username and password are required.']);
        }

        $user = $this->findByUsername($username);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            throw new ValidationException(['Invalid username or password.']);
        }

        if (empty($user['email_verified_at'])) {
            throw new ValidationException(['Please verify your email address before logging in.']);
        }

        unset($user['password_hash']);

        return $user;
    }

    public function updateProfile(int $userId, string $username, string $email, string $currentPassword, string $newPassword, string $confirmPassword): array
    {
        $errors = [];
        $username = trim($username);
        $email = trim($email);
        $currentPassword = (string) $currentPassword;
        $newPassword = (string) $newPassword;
        $confirmPassword = (string) $confirmPassword;
        $passwordChanged = $newPassword !== '' || $confirmPassword !== '';

        if ($username === '' || strlen($username) < 3 || strlen($username) > 30) {
            $errors[] = 'Username must be between 3 and 30 characters.';
        }

        if ($username !== '' && preg_match('/^[A-Za-z0-9_-]{3,30}$/', $username) !== 1) {
            $errors[] = 'Username may only contain letters, numbers, underscores, and hyphens.';
        }

        if ($email === '' || strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Please enter a valid email address.';
        }

        if ($passwordChanged) {
            if ($currentPassword === '') {
                $errors[] = 'Current password is required to change your password.';
            }

            if (strlen($newPassword) < 8) {
                $errors[] = 'New password must be at least 8 characters long.';
            }

            if ($newPassword !== $confirmPassword) {
                $errors[] = 'Password confirmation does not match.';
            }
        }

        $currentUser = $this->findByIdWithPassword($userId);

        if ($currentUser === null) {
            throw new RuntimeException('Unable to load the current user.');
        }

        if ($passwordChanged && ($currentPassword === '' || !password_verify($currentPassword, $currentUser['password_hash']))) {
            $errors[] = 'Current password is incorrect.';
        }

        if ($this->findByUsernameExcludingId($username, $userId) !== null) {
            $errors[] = 'Username is already taken.';
        }

        if ($this->findByEmailExcludingId($email, $userId) !== null) {
            $errors[] = 'Email is already registered.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $updates = [
            'username' => $username,
            'email' => $email,
            'email_verified_at' => $currentUser['email'] === $email ? $currentUser['email_verified_at'] : null,
            'id' => $userId,
        ];

        $sql = 'UPDATE users SET username = :username, email = :email, email_verified_at = :email_verified_at';

        if ($passwordChanged) {
            $sql .= ', password_hash = :password_hash';
            $updates['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($updates);

        $user = $this->findById($userId);

        if ($user === null) {
            throw new RuntimeException('Unable to load the updated user.');
        }

        return [
            'user' => $user,
            'emailChanged' => $currentUser['email'] !== $email,
        ];
    }

    public function issueAuthToken(int $userId, string $tokenType, int $ttlSeconds): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable('now'))->modify('+' . $ttlSeconds . ' seconds')->format('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            'INSERT INTO auth_tokens (user_id, token_type, token_hash, expires_at) VALUES (:user_id, :token_type, :token_hash, :expires_at)'
        );
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':token_type', $tokenType, PDO::PARAM_STR);
        $statement->bindValue(':token_hash', hash('sha256', $plainToken), PDO::PARAM_STR);
        $statement->bindValue(':expires_at', $expiresAt, PDO::PARAM_STR);
        $statement->execute();

        return $plainToken;
    }

    public function updatePasswordById(int $userId, string $password): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        $statement->execute([
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $userId,
        ]);
    }

    public function consumeAuthToken(string $tokenType, string $plainToken): ?array
    {
        $plainToken = trim($plainToken);

        if ($plainToken === '') {
            return null;
        }

        $statement = $this->pdo->prepare(
            'SELECT id, user_id, token_type, token_hash, expires_at, used_at FROM auth_tokens WHERE token_type = :token_type AND token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1'
        );
        $statement->execute([
            'token_type' => $tokenType,
            'token_hash' => hash('sha256', $plainToken),
        ]);
        $token = $statement->fetch();

        if ($token === false) {
            return null;
        }

        $markUsed = $this->pdo->prepare('UPDATE auth_tokens SET used_at = NOW() WHERE id = :id');
        $markUsed->execute(['id' => $token['id']]);

        return $token;
    }

    public function markEmailVerified(int $userId): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $userId]);
    }

    public function requestPasswordReset(string $email): ?string
    {
        $user = $this->findByEmail($email);

        if ($user === null) {
            return null;
        }

        return $this->issueAuthToken((int) $user['id'], 'password_reset', 3600);
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, username, email, email_verified_at, created_at FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findByIdWithPassword(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, username, email, password_hash, email_verified_at, created_at FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, username, email, password_hash, email_verified_at, created_at FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, username, email, password_hash, email_verified_at, created_at FROM users WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findByEmailExcludingId(string $email, int $excludeUserId): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, username, email, created_at FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $statement->execute([
            'email' => $email,
            'id' => $excludeUserId,
        ]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findByUsernameExcludingId(string $username, int $excludeUserId): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, username, email, created_at FROM users WHERE username = :username AND id <> :id LIMIT 1');
        $statement->execute([
            'username' => $username,
            'id' => $excludeUserId,
        ]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    private function validateRegistration(string $username, string $email, string $password, string $confirmPassword): array
    {
        $errors = [];

        $username = trim($username);
        $email = trim($email);

        if ($username === '' || strlen($username) < 3 || strlen($username) > 30) {
            $errors[] = 'Username must be between 3 and 30 characters.';
        }

        if ($username !== '' && preg_match('/^[A-Za-z0-9_-]{3,30}$/', $username) !== 1) {
            $errors[] = 'Username may only contain letters, numbers, underscores, and hyphens.';
        }

        if ($email === '' || strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Password confirmation does not match.';
        }

        return $errors;
    }
}
