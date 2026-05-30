<?php

declare(strict_types=1);

final class Image
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getDBConnection();
    }

    public function create(int $userId, string $filePath, string $originalName, string $mimeType): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO images (user_id, file_path, original_name, mime_type) VALUES (:user_id, :file_path, :original_name, :mime_type)'
        );

        $statement->execute([
            'user_id' => $userId,
            'file_path' => $filePath,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByUserId(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, user_id, file_path, original_name, mime_type, created_at FROM images WHERE user_id = :user_id ORDER BY created_at DESC, id DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }
}
