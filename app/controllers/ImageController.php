<?php

declare(strict_types=1);

final class ImageController
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function gallery(): void
    {
        $user = requireAuthentication();
        $images = (new Image())->findByUserId((int) $user['id']);

        renderView('gallery/index', [
            'pageTitle' => 'My Gallery',
            'currentUser' => $user,
            'images' => $images,
            'success' => getFlash('success'),
            'error' => getFlash('error'),
            'csrfToken' => csrfToken(),
        ]);
    }

    public function showUpload(): void
    {
        $user = requireAuthentication();

        renderView('gallery/upload', [
            'pageTitle' => 'Upload Image',
            'currentUser' => $user,
            'error' => getFlash('error'),
            'success' => getFlash('success'),
            'csrfToken' => csrfToken(),
        ]);
    }

    public function storeUpload(): void
    {
        $user = requireAuthentication();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid form token. Please try again.');
            redirectTo('/gallery/upload');
        }

        if (empty($_FILES['image']) || !is_array($_FILES['image'])) {
            setFlash('error', 'Please choose an image file to upload.');
            redirectTo('/gallery/upload');
        }

        $upload = $_FILES['image'];

        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            setFlash('error', 'The image could not be uploaded.');
            redirectTo('/gallery/upload');
        }

        $temporaryPath = (string) ($upload['tmp_name'] ?? '');
        $originalName = (string) ($upload['name'] ?? 'image');

        if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            setFlash('error', 'Invalid upload data.');
            redirectTo('/gallery/upload');
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($temporaryPath) ?: '';

        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            setFlash('error', 'Only JPEG, PNG, GIF, and WEBP images are allowed.');
            redirectTo('/gallery/upload');
        }

        $extension = self::ALLOWED_MIME_TYPES[$mimeType];
        $storageDirectory = __DIR__ . '/../../uploads';

        if (!is_dir($storageDirectory)) {
            mkdir($storageDirectory, 0775, true);
        }

        $storedName = sanitizeFileName($originalName) . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $storedPath = $storageDirectory . '/' . $storedName;

        if (!move_uploaded_file($temporaryPath, $storedPath)) {
            setFlash('error', 'The file could not be stored.');
            redirectTo('/gallery/upload');
        }

        (new Image())->create((int) $user['id'], 'uploads/' . $storedName, $originalName, $mimeType);

        setFlash('success', 'Image uploaded successfully.');
        redirectTo('/gallery');
    }
}
