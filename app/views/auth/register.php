<section class="auth-grid">
    <div class="auth-panel card accent-card">
        <p class="eyebrow">Join Camagru</p>
        <h1>Register</h1>
        <p class="lead">Create an account to upload photos, build your gallery, and keep everything organized. You will need to confirm your email before signing in.</p>
    </div>

    <form class="card auth-form" action="/register" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

        <?php if (!empty($errors ?? [])): ?>
            <div class="field-errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <label>
            Username
            <input type="text" name="username" value="<?php echo htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="yourname" minlength="3" maxlength="30" pattern="[A-Za-z0-9_-]{3,30}" autocomplete="username" autocapitalize="none" spellcheck="false" required>
        </label>

        <label>
            Email
            <input type="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="you@example.com" maxlength="254" autocomplete="email" autocapitalize="none" spellcheck="false" required>
        </label>

        <label>
            Password
            <input type="password" name="password" placeholder="At least 8 characters" minlength="8" maxlength="128" autocomplete="new-password" required>
        </label>

        <label>
            Confirm Password
            <input type="password" name="password_confirmation" placeholder="Repeat your password" minlength="8" maxlength="128" autocomplete="new-password" required>
        </label>

        <button class="button button-primary" type="submit">Create account</button>
    </form>
</section>
