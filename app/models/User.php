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
}
