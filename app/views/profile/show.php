<section class="card profile-card">
    <p class="eyebrow">Account</p>
    <h1>Profile</h1>
    <p class="helper-text">
        <?php if (!empty($currentUser['email_verified_at'] ?? null)): ?>
            Your email is confirmed.
        <?php else: ?>
            Your email is not confirmed yet.
        <?php endif; ?>
    </p>

    <dl class="profile-grid">
        <div>
            <dt>Username</dt>
            <dd><?php echo htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8'); ?></dd>
        </div>
        <div>
            <dt>Email</dt>
            <dd><?php echo htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8'); ?></dd>
        </div>
        <div>
            <dt>Member since</dt>
            <dd><?php echo htmlspecialchars($currentUser['created_at'], ENT_QUOTES, 'UTF-8'); ?></dd>
        </div>
    </dl>

    <form class="auth-form" action="/profile" method="post">
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
            <input type="text" name="username" value="<?php echo htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8'); ?>" minlength="3" maxlength="30" pattern="[A-Za-z0-9_-]{3,30}" autocomplete="username" autocapitalize="none" spellcheck="false" required>
        </label>

        <label>
            Email
            <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="254" autocomplete="email" autocapitalize="none" spellcheck="false" required>
        </label>

        <label>
            Current password
            <input type="password" name="current_password" placeholder="Required only for password changes" autocomplete="current-password">
        </label>

        <label>
            New password
            <input type="password" name="new_password" minlength="8" maxlength="128" autocomplete="new-password" placeholder="Leave blank to keep your current password">
        </label>

        <label>
            Confirm new password
            <input type="password" name="new_password_confirmation" minlength="8" maxlength="128" autocomplete="new-password">
        </label>

        <button class="button button-primary" type="submit">Save changes</button>
    </form>

    <?php if (empty($currentUser['email_verified_at'] ?? null)): ?>
        <form class="inline-form" action="/profile/resend-verification" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <button class="button button-secondary" type="submit">Resend confirmation email</button>
        </form>
    <?php endif; ?>
</section>
