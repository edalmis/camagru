<section class="auth-grid">
    <div class="auth-panel card accent-card">
        <p class="eyebrow">Welcome back</p>
        <h1>Login</h1>
        <p class="lead">Pick up where you left off and continue building your gallery.</p>
    </div>

    <form class="card auth-form" action="/login" method="post">
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
            Email
            <input type="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="you@example.com" required>
        </label>

        <label>
            Password
            <input type="password" name="password" placeholder="Your password" required>
        </label>

        <button class="button button-primary" type="submit">Login</button>
    </form>
</section>
