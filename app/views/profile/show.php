<section class="card profile-card">
    <p class="eyebrow">Account</p>
    <h1>Profile</h1>

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
</section>
