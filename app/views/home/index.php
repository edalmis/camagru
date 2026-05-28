<main>
    <h1><?php echo htmlspecialchars($pageTitle ?? 'Camagru', ENT_QUOTES, 'UTF-8'); ?></h1>
    <p>PHP bootstrap is running.</p>
    <p>Database connection: <?php echo !empty($isDatabaseConnected) ? 'ready' : 'not available'; ?></p>
</main>
