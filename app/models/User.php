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
            'INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)'
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

    public function authenticate(string $email, string $password): array
    {
        $email = trim($email);

        if ($email === '' || $password === '') {
            throw new ValidationException(['Email and password are required.']);
        }

        $user = $this->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            throw new ValidationException(['Invalid email or password.']);
        }

        unset($user['password_hash']);

        return $user;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, username, email, created_at FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, username, email, password_hash, created_at FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, username, email, password_hash, created_at FROM users WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);
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

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
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
