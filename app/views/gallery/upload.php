<section class="upload-layout">
    <div class="upload-intro card accent-card">
        <p class="eyebrow">Upload</p>
        <h1>Add a photo</h1>
        <p class="lead">Choose an image from your device and store it in your gallery.</p>
        <p class="helper-text">Supported formats: JPEG, PNG, GIF, WEBP.</p>
    </div>

    <form class="card upload-form" action="/gallery/upload" method="post" enctype="multipart/form-data">
        <?php if (!empty($success ?? null)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($error ?? null)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="file-field">
            <label for="image">Choose image</label>
            <input id="image" type="file" name="image" required>
            <p id="selected-file">No file selected.</p>
        </div>

        <button class="button button-primary" type="submit">Upload</button>
    </form>
</section>

<p class="helper-text">Webcam capture support will be added in a later step.</p>

<script>
    var imageInput = document.getElementById('image');
    var selectedFile = document.getElementById('selected-file');

    if (imageInput && selectedFile) {
        imageInput.onchange = function () {
            if (imageInput.files && imageInput.files.length > 0) {
                selectedFile.textContent = 'Selected: ' + imageInput.files[0].name;
            } else {
                selectedFile.textContent = 'No file selected.';
            }
        };
    }
</script>
