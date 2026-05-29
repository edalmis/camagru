<main>
    <h1><?php echo htmlspecialchars($pageTitle ?? 'Camagru', ENT_QUOTES, 'UTF-8'); ?></h1>
    <p>PHP bootstrap is running.</p>
    <p>Database connection: <?php echo !empty($isDatabaseConnected) ? 'ready' : 'not available'; ?></p>
    <?php if (!empty($currentUser ?? null)): ?>
        <p>You are logged in as <?php echo htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8'); ?>.</p>
    <?php else: ?>
        <p>Please register or log in to continue.</p>
    <?php endif; ?>
</main>

