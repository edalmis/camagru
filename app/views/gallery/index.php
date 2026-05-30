<main>
    <h1>My Gallery</h1>

    <?php if (!empty($success ?? null)): ?>
        <p><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if (!empty($error ?? null)): ?>
        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if (empty($images)): ?>
        <p>No images uploaded yet.</p>
    <?php else: ?>
        <section>
            <?php foreach ($images as $image): ?>
                <article>
                    <img src="/<?php echo htmlspecialchars($image['file_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($image['original_name'], ENT_QUOTES, 'UTF-8'); ?>" style="max-width: 240px; height: auto;">
                    <p><?php echo htmlspecialchars($image['original_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <small><?php echo htmlspecialchars($image['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <p>Webcam capture support will be added in a later step.</p>
</main>
