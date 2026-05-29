<main>
    <h1>Profile</h1>

    <?php if (!empty($success ?? null)): ?>
        <p><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if (!empty($error ?? null)): ?>
        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <p>Username: <?php echo htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Email: <?php echo htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Member since: <?php echo htmlspecialchars($currentUser['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
</main>
