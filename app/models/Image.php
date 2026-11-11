<?php

declare(strict_types=1);

final class Image
{
    private PDO $pdo;
    private const MAX_UPLOAD_BYTES = 5242880;
    private const EDITOR_WIDTH = 640;
    private const EDITOR_HEIGHT = 480;

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

    public function storeCapturedImage(int $userId, string $dataUrl): array
    {
        return $this->storeEditedImage($userId, $dataUrl, [], 'sunburst', 'Webcam capture');
    }

    public function storeEditedImage(int $userId, string $dataUrl, array $upload, string $overlayKey, string $originalName = 'Edited capture'): array
    {
        $overlayKey = trim($overlayKey);

        if (!in_array($overlayKey, ['sunburst', 'sparkles'], true)) {
            throw new ValidationException(['Please select a superposable image.']);
        }

        $imageData = $this->sourceImageData($dataUrl, $upload);
        $sourceImage = @imagecreatefromstring($imageData);

        if ($sourceImage === false) {
            throw new ValidationException(['The source image could not be read.']);
        }

        $finalImage = imagecreatetruecolor(self::EDITOR_WIDTH, self::EDITOR_HEIGHT);
        imagealphablending($finalImage, false);
        imagesavealpha($finalImage, true);
        $transparent = imagecolorallocatealpha($finalImage, 0, 0, 0, 127);
        imagefill($finalImage, 0, 0, $transparent);
        imagealphablending($finalImage, true);
        imagecopyresampled($finalImage, $sourceImage, 0, 0, 0, 0, self::EDITOR_WIDTH, self::EDITOR_HEIGHT, imagesx($sourceImage), imagesy($sourceImage));
        imagedestroy($sourceImage);

        $this->drawOverlay($finalImage, $overlayKey);

        $imageData = null;
        ob_start();
        imagepng($finalImage, null, 6);
        $imageData = ob_get_clean();
        imagedestroy($finalImage);

        if (!is_string($imageData) || $imageData === '') {
            throw new RuntimeException('The edited image could not be encoded.');
        }

        $storageDirectory = __DIR__ . '/../../uploads';
        if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0775, true) && !is_dir($storageDirectory)) {
            throw new RuntimeException('The upload directory could not be created.');
        }

        $storedName = 'edited-' . bin2hex(random_bytes(8)) . '.png';
        $storedPath = $storageDirectory . '/' . $storedName;
        if (file_put_contents($storedPath, $imageData, LOCK_EX) === false) {
            throw new RuntimeException('The edited image could not be stored.');
        }

        $imageId = $this->create($userId, 'uploads/' . $storedName, sanitizeFileName($originalName) . '.png', 'image/png');

        return $this->findById($imageId) ?? [
            'id' => $imageId,
            'user_id' => $userId,
            'file_path' => 'uploads/' . $storedName,
            'original_name' => sanitizeFileName($originalName) . '.png',
            'mime_type' => 'image/png',
        ];
    }

    public function deleteByIdForUser(int $imageId, int $userId): void
    {
        $image = $this->findById($imageId);

        if ($image === null || (int) $image['user_id'] !== $userId) {
            throw new ValidationException(['That image does not belong to your account.']);
        }

        $statement = $this->pdo->prepare('DELETE FROM images WHERE id = :id AND user_id = :user_id');
        $statement->execute([
            'id' => $imageId,
            'user_id' => $userId,
        ]);

        $filePath = __DIR__ . '/../../' . ltrim((string) $image['file_path'], '/');
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }

    private function sourceImageData(string $dataUrl, array $upload): string
    {
        if (!empty($upload)) {
            if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new ValidationException(['The selected image could not be uploaded.']);
            }

            $temporaryPath = (string) ($upload['tmp_name'] ?? '');
            $fileSize = (int) ($upload['size'] ?? 0);
            if ($temporaryPath === '' || !is_uploaded_file($temporaryPath) || $fileSize <= 0 || $fileSize > self::MAX_UPLOAD_BYTES) {
                throw new ValidationException(['Uploaded images must be valid and 5 MB or smaller.']);
            }

            $imageData = file_get_contents($temporaryPath);
        } elseif (preg_match('/^data:image\/png;base64,(.+)$/s', trim($dataUrl), $matches)) {
            $imageData = base64_decode($matches[1], true);
        } else {
            throw new ValidationException(['Please capture or upload an image.']);
        }

        if (!is_string($imageData) || strlen($imageData) === 0 || strlen($imageData) > self::MAX_UPLOAD_BYTES) {
            throw new ValidationException(['The source image is empty, invalid, or too large.']);
        }

        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->buffer($imageData);
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            throw new ValidationException(['Only JPEG, PNG, GIF, and WEBP images are allowed.']);
        }

        return $imageData;
    }

    private function drawOverlay($image, string $overlayKey): void
    {
        imagesavealpha($image, true);
        imagealphablending($image, true);
        $gold = imagecolorallocatealpha($image, 247, 201, 72, 35);
        $white = imagecolorallocatealpha($image, 255, 255, 255, 25);

        if ($overlayKey === 'sunburst') {
            imagesetthickness($image, 8);
            $lines = [[320, 240, 320, 20], [320, 240, 320, 460], [320, 240, 100, 240], [320, 240, 540, 240], [320, 240, 164, 84], [320, 240, 476, 396], [320, 240, 476, 84], [320, 240, 164, 396]];
            foreach ($lines as $line) {
                imageline($image, $line[0], $line[1], $line[2], $line[3], $gold);
            }
            imageellipse($image, 320, 240, 144, 144, $white);
            return;
        }

        $sparkles = [[112, 92, 28], [520, 110, 38], [510, 370, 28], [154, 370, 38]];
        foreach ($sparkles as $sparkle) {
            $points = [];
            for ($index = 0; $index < 8; $index++) {
                $angle = ($index * M_PI / 4) - M_PI / 2;
                $radius = $index % 2 === 0 ? $sparkle[2] : $sparkle[2] / 3;
                $points[] = (int) ($sparkle[0] + cos($angle) * $radius);
                $points[] = (int) ($sparkle[1] + sin($angle) * $radius);
            }
            imagefilledpolygon($image, $points, 8, $white);
        }
    }

    public function findByUserId(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, user_id, file_path, original_name, mime_type, created_at FROM images WHERE user_id = :user_id ORDER BY created_at DESC, id DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function findAll(?int $viewerId = null, ?int $ownerId = null, int $page = 1, int $perPage = 6): array
    {
        $page = max(1, $page);
        $perPage = max(5, $perPage);
        $offset = ($page - 1) * $perPage;
        $whereClause = '';

        if ($ownerId !== null) {
            $whereClause = ' WHERE images.user_id = :owner_id';
        }

        $statement = $this->pdo->prepare(
            'SELECT images.id, images.user_id, images.file_path, images.original_name, images.mime_type, images.created_at, users.username, COUNT(DISTINCT image_likes.id) AS like_count, CASE WHEN SUM(CASE WHEN image_likes.user_id = :viewer_id THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END AS liked_by_viewer FROM images INNER JOIN users ON users.id = images.user_id LEFT JOIN image_likes ON image_likes.image_id = images.id' . $whereClause . ' GROUP BY images.id, images.user_id, images.file_path, images.original_name, images.mime_type, images.created_at, users.username ORDER BY images.created_at DESC, images.id DESC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':viewer_id', $viewerId ?? 0, PDO::PARAM_INT);

        if ($ownerId !== null) {
            $statement->bindValue(':owner_id', $ownerId, PDO::PARAM_INT);
        }

        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function countAll(?int $ownerId = null): int
    {
        $whereClause = '';
        $parameters = [];

        if ($ownerId !== null) {
            $whereClause = ' WHERE user_id = :owner_id';
            $parameters['owner_id'] = $ownerId;
        }

        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM images' . $whereClause);
        $statement->execute($parameters);

        return (int) $statement->fetchColumn();
    }

    public function findCommentsByImageIds(array $imageIds): array
    {
        $imageIds = array_values(array_filter(array_map('intval', $imageIds), static function (int $id): bool {
            return $id > 0;
        }));

        if (empty($imageIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $statement = $this->pdo->prepare(
            'SELECT image_comments.id, image_comments.image_id, image_comments.comment_text, image_comments.created_at, users.username FROM image_comments INNER JOIN users ON users.id = image_comments.user_id WHERE image_comments.image_id IN (' . $placeholders . ') ORDER BY image_comments.created_at ASC, image_comments.id ASC'
        );
        $statement->execute($imageIds);

        $commentsByImage = [];
        foreach ($statement->fetchAll() as $comment) {
            $commentsByImage[(int) $comment['image_id']][] = $comment;
        }

        return $commentsByImage;
    }

    public function toggleLike(int $imageId, int $userId): bool
    {
        if ($this->findById($imageId) === null) {
            throw new ValidationException(['That image does not exist.']);
        }

        $findLike = $this->pdo->prepare('SELECT id FROM image_likes WHERE image_id = :image_id AND user_id = :user_id LIMIT 1');
        $findLike->execute([
            'image_id' => $imageId,
            'user_id' => $userId,
        ]);
        $like = $findLike->fetch();

        if ($like !== false) {
            $deleteLike = $this->pdo->prepare('DELETE FROM image_likes WHERE id = :id');
            $deleteLike->execute(['id' => $like['id']]);
            return false;
        }

        $addLike = $this->pdo->prepare('INSERT INTO image_likes (image_id, user_id) VALUES (:image_id, :user_id)');
        $addLike->execute([
            'image_id' => $imageId,
            'user_id' => $userId,
        ]);

        return true;
    }

    public function addComment(int $imageId, int $userId, string $commentText): void
    {
        $commentText = trim($commentText);

        if ($this->findById($imageId) === null) {
            throw new ValidationException(['That image does not exist.']);
        }

        if ($commentText === '') {
            throw new ValidationException(['Comment cannot be empty.']);
        }

        if (strlen($commentText) > 1000) {
            throw new ValidationException(['Comments must be 1000 characters or fewer.']);
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO image_comments (image_id, user_id, comment_text) VALUES (:image_id, :user_id, :comment_text)'
        );
        $statement->execute([
            'image_id' => $imageId,
            'user_id' => $userId,
            'comment_text' => $commentText,
        ]);
    }

    public function findAuthorForNotification(int $imageId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT users.id, users.username, users.email, users.comment_email_notifications FROM images INNER JOIN users ON users.id = images.user_id WHERE images.id = :image_id LIMIT 1'
        );
        $statement->execute(['image_id' => $imageId]);
        $author = $statement->fetch();

        return $author ?: null;
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
