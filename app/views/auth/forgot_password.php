<section class="auth-grid">
    <div class="auth-panel card accent-card">
        <p class="eyebrow">Password reset</p>
        <h1>Forgot password</h1>
        <p class="lead">Enter the email address linked to your account and we will send a reset link.</p>
    </div>

    <form class="card auth-form" action="/forgot-password" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

        <?php if (!empty($error ?? null)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <label>
            Email
            <input type="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="254" autocomplete="email" autocapitalize="none" spellcheck="false" required>
        </label>

        <button class="button button-primary" type="submit">Send reset link</button>
    </form>
</section>
