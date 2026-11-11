<?php

declare(strict_types=1);

final class ImageController
{
    public function gallery(): void
    {
        $viewer = currentUser();
        $imageModel = new Image();
        $page = $this->currentPage();
        $images = $imageModel->findAll($viewer === null ? null : (int) $viewer['id'], null, $page);
        $comments = $imageModel->findCommentsByImageIds(array_column($images, 'id'));

        renderView('gallery/index', [
            'pageTitle' => 'Public Gallery',
            'currentUser' => $viewer,
            'images' => $images,
            'comments' => $comments,
            'page' => $page,
            'totalPages' => $this->totalPages($imageModel->countAll()),
            'galleryPath' => '/gallery',
            'success' => getFlash('success'),
            'error' => getFlash('error'),
            'isPublic' => true,
        ]);
    }

    public function myGallery(): void
    {
        $user = requireAuthentication();
        $imageModel = new Image();
        $page = $this->currentPage();
        $images = $imageModel->findAll((int) $user['id'], (int) $user['id'], $page);
        $comments = $imageModel->findCommentsByImageIds(array_column($images, 'id'));

        renderView('gallery/index', [
            'pageTitle' => 'My Gallery',
            'currentUser' => $user,
            'images' => $images,
            'comments' => $comments,
            'page' => $page,
            'totalPages' => $this->totalPages($imageModel->countAll((int) $user['id'])),
            'galleryPath' => '/gallery/mine',
            'success' => getFlash('success'),
            'error' => getFlash('error'),
            'isPublic' => false,
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

    public function editor(): void
    {
        $user = requireAuthentication();

        renderView('gallery/editor', [
            'pageTitle' => 'Photo Editor',
            'currentUser' => $user,
            'images' => (new Image())->findByUserId((int) $user['id']),
            'csrfToken' => csrfToken(),
            'success' => getFlash('success'),
            'error' => getFlash('error'),
            'overlays' => [
                ['name' => 'Sunburst', 'key' => 'sunburst', 'path' => '/assets/overlays/sunburst.svg'],
                ['name' => 'Sparkles', 'key' => 'sparkles', 'path' => '/assets/overlays/sparkles.svg'],
            ],
        ]);
    }

    public function capture(): void
    {
        $user = requireAuthentication();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid form token. Please try again.');
            redirectTo('/editor');
        }

        try {
            (new Image())->storeEditedImage(
                (int) $user['id'],
                (string) ($_POST['image_data'] ?? ''),
                $_FILES['source_image'] ?? [],
                (string) ($_POST['overlay_key'] ?? ''),
                (string) ($_POST['source_name'] ?? 'Edited capture')
            );
            setFlash('success', 'Webcam photo saved.');
        } catch (ValidationException $exception) {
            setFlash('error', $exception->getErrors()[0] ?? 'The capture could not be saved.');
        } catch (Throwable $exception) {
            error_log('Webcam capture failed: ' . $exception->getMessage());
            setFlash('error', 'The capture could not be saved right now.');
        }

        redirectTo('/editor');
    }

    public function deleteEditorImage(): void
    {
        $user = requireAuthentication();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid form token. Please try again.');
            redirectTo('/editor');
        }

        try {
            (new Image())->deleteByIdForUser((int) ($_POST['image_id'] ?? 0), (int) $user['id']);
            setFlash('success', 'Image deleted.');
        } catch (ValidationException $exception) {
            setFlash('error', $exception->getErrors()[0] ?? 'Unable to delete image.');
        } catch (Throwable $exception) {
            error_log('Editor image deletion failed: ' . $exception->getMessage());
            setFlash('error', 'Unable to delete image right now.');
        }

        redirectTo('/editor');
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
            redirectTo('/gallery/mine');
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

    public function like(): void
    {
        $user = requireAuthentication();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid form token. Please try again.');
            redirectTo('/gallery');
        }

        try {
            (new Image())->toggleLike((int) ($_POST['image_id'] ?? 0), (int) $user['id']);
        } catch (ValidationException $exception) {
            setFlash('error', $exception->getErrors()[0] ?? 'Unable to update like.');
        } catch (Throwable $exception) {
            error_log('Like update failed: ' . $exception->getMessage());
            setFlash('error', 'Unable to update like right now.');
        }

        redirectTo('/gallery');
    }

    public function comment(): void
    {
        $user = requireAuthentication();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            setFlash('error', 'Invalid form token. Please try again.');
            redirectTo('/gallery');
        }

        try {
            $imageId = (int) ($_POST['image_id'] ?? 0);
            $commentText = trim((string) ($_POST['comment_text'] ?? ''));
            $imageModel = new Image();
            $imageModel->addComment(
                $imageId,
                (int) $user['id'],
                $commentText
            );

            $author = $imageModel->findAuthorForNotification($imageId);
            if ($author !== null && (int) $author['id'] !== (int) $user['id'] && !empty($author['comment_email_notifications'])) {
                $mailSent = sendMailMessage(
                    $author['email'],
                    'New comment on your Camagru image',
                    "Hello {$author['username']},\n\n{$user['username']} commented on your image:\n\"{$commentText}\"\n\nView your gallery: " . appBaseUrl() . "/gallery/mine"
                );

                if (!$mailSent) {
                    error_log('Comment notification email could not be sent for image ' . $imageId);
                }
            }

            setFlash('success', 'Comment added.');
        } catch (ValidationException $exception) {
            setFlash('error', $exception->getErrors()[0] ?? 'Unable to add comment.');
        } catch (Throwable $exception) {
            error_log('Comment creation failed: ' . $exception->getMessage());
            setFlash('error', 'Unable to add comment right now.');
        }

        redirectTo('/gallery');
    }

    private function currentPage(): int
    {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

        return is_int($page) && $page > 0 ? $page : 1;
    }

    private function totalPages(int $totalImages): int
    {
        return max(1, (int) ceil($totalImages / 6));
    }
}
