<main>
    <h1>Upload Image</h1>

    <?php if (!empty($success ?? null)): ?>
        <p><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if (!empty($error ?? null)): ?>
        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form action="/gallery/upload" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

        <label for="image">Choose image</label>
        <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>

        <p id="selected-file">No file selected.</p>

        <button type="submit">Upload</button>
    </form>

    <p>You can add webcam capture here in the next step.</p>
</main>

<script>
    const imageInput = document.getElementById('image');
    const selectedFile = document.getElementById('selected-file');

    imageInput.addEventListener('change', () => {
        selectedFile.textContent = imageInput.files.length > 0 ? imageInput.files[0].name : 'No file selected.';
    });
</script>
