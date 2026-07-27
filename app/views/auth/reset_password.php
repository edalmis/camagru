<section class="auth-grid">
    <div class="auth-panel card accent-card">
        <p class="eyebrow">Password reset</p>
        <h1>Set new password</h1>
        <p class="lead">Choose a strong new password to regain access to your account.</p>
    </div>

    <form class="card auth-form" action="/reset-password" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

        <?php if (!empty($error ?? null)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <label>
            New password
            <input type="password" name="password" minlength="8" maxlength="128" autocomplete="new-password" required>
        </label>

        <label>
            Confirm new password
            <input type="password" name="password_confirmation" minlength="8" maxlength="128" autocomplete="new-password" required>
        </label>

        <button class="button button-primary" type="submit">Reset password</button>
    </form>
</section>
