<main>
    <h1>Register</h1>

    <?php if (!empty($errors ?? [])): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="/register" method="post">
        <label>
            Username
            <input type="text" name="username" value="<?php echo htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </label>

        <label>
            Email
            <input type="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </label>

        <label>
            Password
            <input type="password" name="password" required>
        </label>

        <label>
            Confirm Password
            <input type="password" name="password_confirmation" required>
        </label>

        <button type="submit">Create account</button>
    </form>
</main>
