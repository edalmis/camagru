<section class="page-header">
    <div>
        <p class="eyebrow"><?php echo !empty($isPublic) ? 'Community feed' : 'Your work'; ?></p>
        <h1><?php echo !empty($isPublic) ? 'Public Gallery' : 'My Gallery'; ?></h1>
    </div>

    <?php if (!empty($currentUser ?? null)): ?>
        <a class="button button-primary" href="/gallery/upload">Upload image</a>
    <?php endif; ?>
</section>

<?php if (empty($images)): ?>
    <section class="empty-state card">
        <h2>No images yet</h2>
        <p><?php echo !empty($isPublic) ? 'The public gallery will appear here after the first upload.' : 'Your gallery will appear here after the first upload.'; ?></p>
        <?php if (!empty($currentUser ?? null)): ?>
            <a class="button button-primary" href="/gallery/upload">Choose a photo</a>
        <?php endif; ?>
    </section>
<?php else: ?>
    <section class="gallery-grid">
        <?php foreach ($images as $image): ?>
            <article class="gallery-card card">
                <a href="/<?php echo htmlspecialchars($image['file_path'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                    <img src="/<?php echo htmlspecialchars($image['file_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($image['original_name'], ENT_QUOTES, 'UTF-8'); ?>">
                </a>
                <div class="gallery-card-body">
                    <h2><?php echo htmlspecialchars($image['original_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <?php if (!empty($isPublic)): ?>
                        <p>By <?php echo htmlspecialchars($image['username'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <p>Uploaded <?php echo htmlspecialchars($image['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>

                    <div class="gallery-actions">
                        <?php if (!empty($currentUser ?? null)): ?>
                            <form action="/gallery/like" method="post" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="image_id" value="<?php echo (int) $image['id']; ?>">
                                <button type="submit" class="button button-secondary">
                                    <?php echo !empty($image['liked_by_viewer']) ? 'Unlike' : 'Like'; ?> (<?php echo (int) $image['like_count']; ?>)
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="helper-text">Log in to like or comment.</p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($comments[(int) $image['id']] ?? [])): ?>
                        <div class="comment-list">
                            <?php foreach ($comments[(int) $image['id']] as $comment): ?>
                                <p><strong><?php echo htmlspecialchars($comment['username'], ENT_QUOTES, 'UTF-8'); ?></strong>: <?php echo nl2br(htmlspecialchars($comment['comment_text'], ENT_QUOTES, 'UTF-8')); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($currentUser ?? null)): ?>
                        <form action="/gallery/comment" method="post" class="comment-form">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="image_id" value="<?php echo (int) $image['id']; ?>">
                            <label>
                                Add a comment
                                <textarea name="comment_text" maxlength="1000" rows="2" required></textarea>
                            </label>
                            <button type="submit" class="button button-secondary">Comment</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (($totalPages ?? 1) > 1): ?>
    <nav class="pagination" aria-label="Gallery pages">
        <?php if (($page ?? 1) > 1): ?>
            <a class="button button-secondary" href="<?php echo htmlspecialchars($galleryPath, ENT_QUOTES, 'UTF-8'); ?>?page=<?php echo (int) $page - 1; ?>">Previous</a>
        <?php endif; ?>

        <span>Page <?php echo (int) $page; ?> of <?php echo (int) $totalPages; ?></span>

        <?php if (($page ?? 1) < ($totalPages ?? 1)): ?>
            <a class="button button-secondary" href="<?php echo htmlspecialchars($galleryPath, ENT_QUOTES, 'UTF-8'); ?>?page=<?php echo (int) $page + 1; ?>">Next</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>

<p class="helper-text">Webcam capture support will be added in a later step.</p>
