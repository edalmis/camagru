<section class="hero card">
    <p class="eyebrow">Capture. Curate. Share.</p>
    <h1><?php echo htmlspecialchars($pageTitle ?? 'Camagru', ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="lead">A lightweight photo-sharing workspace with authentication, uploads, and a growing gallery.</p>

    <div class="hero-actions">
        <?php if (!empty($currentUser ?? null)): ?>
            <a class="button button-primary" href="/gallery/upload">Upload a photo</a>
            <a class="button button-secondary" href="/gallery">Open gallery</a>
        <?php else: ?>
            <a class="button button-primary" href="/register">Create account</a>
            <a class="button button-secondary" href="/login">Log in</a>
        <?php endif; ?>
    </div>

    <div class="hero-meta">
        <span>PHP bootstrap: ready</span>
        <span>Database: <?php echo !empty($isDatabaseConnected) ? 'connected' : 'offline'; ?></span>
        <?php if (!empty($currentUser ?? null)): ?>
            <span>Signed in as <?php echo htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
    </div>
</section>

