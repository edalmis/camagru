<main>
    <h1>Login</h1>

    <?php if (!empty($success ?? null)): ?>
        <p><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if (!empty($errors ?? [])): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="/login" method="post">
        <label>
            Email
            <input type="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </label>

        <label>
            Password
            <input type="password" name="password" required>
        </label>

        <button type="submit">Login</button>
    </form>
</main>
