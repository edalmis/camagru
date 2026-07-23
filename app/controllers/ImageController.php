<?php

declare(strict_types=1);

final class ImageController
{
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

        try {
            $upload = $_FILES['image'] ?? [];
            (new Image())->storeUpload((int) $user['id'], $upload);
            setFlash('success', 'Image uploaded successfully.');
            redirectTo('/gallery');
        } catch (ValidationException $exception) {
            renderView('gallery/upload', [
                'pageTitle' => 'Upload Image',
                'currentUser' => $user,
                'errors' => $exception->getErrors(),
                'csrfToken' => csrfToken(),
            ]);
            return;
        } catch (Throwable $exception) {
            error_log('Image upload failed: ' . $exception->getMessage());
            setFlash('error', 'The image could not be uploaded right now.');
            redirectTo('/gallery/upload');
        }
    }
}
