<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Camagru', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
<div class="app-shell">
    <header class="topbar">
        <a class="brand" href="/">
            <span class="brand-mark">C</span>
            <span class="brand-text">Camagru</span>
        </a>

        <nav class="nav-links" aria-label="Primary">
            <a href="/">Home</a>
            <?php if (!empty($currentUser ?? null)): ?>
                <a href="/gallery">Gallery</a>
                <a href="/gallery/upload">Upload</a>
                <a href="/profile">Profile</a>
                <form action="/logout" method="post" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="nav-button">Logout</button>
                </form>
            <?php else: ?>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="page-shell">
        <?php if (!empty($success ?? null)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if (!empty($error ?? null)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
