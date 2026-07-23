<?php

declare(strict_types=1);

final class Image
{
    private PDO $pdo;
    private const MAX_UPLOAD_BYTES = 5242880;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

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

    public function storeUpload(int $userId, array $upload): array
    {
        $errors = [];

        if (empty($upload) || !is_array($upload)) {
            $errors[] = 'Please choose an image file to upload.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errorCode = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'The image is too large for the server limit.',
                UPLOAD_ERR_FORM_SIZE => 'The image is too large for the form limit.',
                UPLOAD_ERR_PARTIAL => 'The image upload was interrupted.',
                UPLOAD_ERR_NO_FILE => 'Please choose an image file to upload.',
                UPLOAD_ERR_NO_TMP_DIR => 'The temporary upload directory is unavailable.',
                UPLOAD_ERR_CANT_WRITE => 'The image could not be written to disk.',
                UPLOAD_ERR_EXTENSION => 'The image upload was blocked by a server extension.',
            ];

            throw new ValidationException([$errorMessages[$errorCode] ?? 'The image could not be uploaded.']);
        }

        $temporaryPath = (string) ($upload['tmp_name'] ?? '');
        $originalName = trim((string) ($upload['name'] ?? 'image'));
        $fileSize = (int) ($upload['size'] ?? 0);

        if ($fileSize <= 0) {
            throw new ValidationException(['The selected file appears to be empty.']);
        }

        if ($fileSize > self::MAX_UPLOAD_BYTES) {
            throw new ValidationException(['Images must be 5 MB or smaller.']);
        }

        if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            throw new ValidationException(['Invalid upload data.']);
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($temporaryPath) ?: '';

        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            throw new ValidationException(['Only JPEG, PNG, GIF, and WEBP images are allowed.']);
        }

        $storageDirectory = __DIR__ . '/../../uploads';

        if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0775, true) && !is_dir($storageDirectory)) {
            throw new RuntimeException('The upload directory could not be created.');
        }

        $extension = self::ALLOWED_MIME_TYPES[$mimeType];
        $storedName = sanitizeFileName($originalName) . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $storedPath = $storageDirectory . '/' . $storedName;

        if (!move_uploaded_file($temporaryPath, $storedPath)) {
            throw new RuntimeException('The file could not be stored.');
        }

        $imageId = $this->create($userId, 'uploads/' . $storedName, $originalName, $mimeType);

        return $this->findById($imageId) ?? [
            'id' => $imageId,
            'user_id' => $userId,
            'file_path' => 'uploads/' . $storedName,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
        ];
    }

    public function findByUserId(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, user_id, file_path, original_name, mime_type, created_at FROM images WHERE user_id = :user_id ORDER BY created_at DESC, id DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, user_id, file_path, original_name, mime_type, created_at FROM images WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $image = $statement->fetch();

        return $image ?: null;
    }
}
