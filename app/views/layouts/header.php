<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Camagru', ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body>
<?php if (!empty($success ?? null)): ?>
    <p><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<?php if (!empty($error ?? null)): ?>
    <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<nav>
    <a href="/">Home</a>
    <?php if (!empty($currentUser ?? null)): ?>
        <a href="/gallery">Gallery</a>
        <a href="/gallery/upload">Upload</a>
        <a href="/profile">Profile</a>
        <form action="/logout" method="post" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit">Logout</button>
        </form>
    <?php else: ?>
        <a href="/login">Login</a>
        <a href="/register">Register</a>
    <?php endif; ?>
</nav>
