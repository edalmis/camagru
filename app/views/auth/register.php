<section class="auth-grid">
    <div class="auth-panel card accent-card">
        <p class="eyebrow">Join Camagru</p>
        <h1>Register</h1>
        <p class="lead">Create an account to upload photos, build your gallery, and keep everything organized.</p>
    </div>

    <form class="card auth-form" action="/register" method="post">
        <?php if (!empty($errors ?? [])): ?>
            <div class="field-errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <label>
            Username
            <input type="text" name="username" value="<?php echo htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="yourname" required>
        </label>

        <label>
            Email
            <input type="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="you@example.com" required>
        </label>

        <label>
            Password
            <input type="password" name="password" placeholder="At least 8 characters" required>
        </label>

        <label>
            Confirm Password
            <input type="password" name="password_confirmation" placeholder="Repeat your password" required>
        </label>

        <button class="button button-primary" type="submit">Create account</button>
    </form>
</section>
