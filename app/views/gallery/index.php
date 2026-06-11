<section class="page-header">
    <div>
        <p class="eyebrow">Your work</p>
        <h1>My Gallery</h1>
    </div>

    <a class="button button-primary" href="/gallery/upload">Upload image</a>
</section>

<?php if (empty($images)): ?>
    <section class="empty-state card">
        <h2>No images yet</h2>
        <p>Your gallery will appear here after the first upload.</p>
        <a class="button button-primary" href="/gallery/upload">Choose a photo</a>
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
                    <p>Uploaded <?php echo htmlspecialchars($image['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<p class="helper-text">Webcam capture support will be added in a later step.</p>
