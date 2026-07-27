<section class="auth-grid">
    <div class="auth-panel card accent-card">
        <p class="eyebrow">Welcome back</p>
        <h1>Login</h1>
        <p class="lead">Pick up where you left off and continue building your gallery.</p>
    </div>

    <form class="card auth-form" action="/login" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

        <?php if (!empty($success ?? null)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

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
            Password
            <input type="password" name="password" placeholder="Your password" minlength="8" maxlength="128" autocomplete="current-password" required>
        </label>

        <button class="button button-primary" type="submit">Login</button>

        <p class="helper-text"><a href="/forgot-password">Forgot your password?</a></p>
    </form>
</section>
