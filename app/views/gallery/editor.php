<section class="editor-layout">
    <main class="editor-main card">
        <div class="page-header">
            <div>
                <p class="eyebrow">Create</p>
                <h1>Photo editor</h1>
            </div>
            <a class="button button-secondary" href="/gallery/mine">My Gallery</a>
        </div>

        <?php if (!empty($success ?? null)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if (!empty($error ?? null)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="camera-stage">
            <video id="camera-preview" autoplay muted playsinline></video>
            <canvas id="capture-canvas" width="640" height="480"></canvas>
            <img id="overlay-preview" alt="" aria-hidden="true">
            <p id="camera-status" class="helper-text">Camera preview is loading.</p>
        </div>

        <div class="editor-controls">
            <fieldset class="overlay-picker">
                <legend>Superposable image</legend>
                <?php foreach ($overlays as $index => $overlay): ?>
                    <label class="overlay-option">
                        <input type="radio" name="overlay" value="<?php echo htmlspecialchars($overlay['path'], ENT_QUOTES, 'UTF-8'); ?>" data-overlay-key="<?php echo htmlspecialchars($overlay['key'], ENT_QUOTES, 'UTF-8'); ?>">
                        <img src="<?php echo htmlspecialchars($overlay['path'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
                        <span><?php echo htmlspecialchars($overlay['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </label>
                <?php endforeach; ?>
            </fieldset>

            <form id="capture-form" action="/editor/capture" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" id="image-data" name="image_data" value="">
                <input type="hidden" id="overlay-key" name="overlay_key" value="">
                <input type="hidden" id="source-name" name="source_name" value="Edited capture">
                <label class="editor-upload-label">
                    Or upload an image
                    <input id="source-image" type="file" name="source_image" accept="image/jpeg,image/png,image/gif,image/webp">
                </label>
                <button id="capture-button" class="button button-primary" type="submit" disabled>Capture picture</button>
            </form>
        </div>
    </main>

    <aside class="editor-history card">
        <p class="eyebrow">Your captures</p>
        <h2>Previous pictures</h2>
        <?php if (empty($images)): ?>
            <p class="helper-text">Your captured pictures will appear here.</p>
        <?php else: ?>
            <div class="history-grid">
                <?php foreach ($images as $image): ?>
                    <div class="history-item">
                        <a href="/<?php echo htmlspecialchars($image['file_path'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                            <img src="/<?php echo htmlspecialchars($image['file_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($image['original_name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </a>
                        <form action="/editor/delete" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="image_id" value="<?php echo (int) $image['id']; ?>">
                            <button type="submit" class="button button-secondary">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </aside>
</section>

<script>
(function () {
    var video = document.getElementById('camera-preview');
    var canvas = document.getElementById('capture-canvas');
    var overlay = document.getElementById('overlay-preview');
    var button = document.getElementById('capture-button');
    var imageData = document.getElementById('image-data');
    var status = document.getElementById('camera-status');
    var overlayInputs = document.getElementsByName('overlay');
    var overlayKey = document.getElementById('overlay-key');
    var sourceImage = document.getElementById('source-image');
    var sourceName = document.getElementById('source-name');
    var stream = null;
    var camera = navigator.mediaDevices && navigator.mediaDevices.getUserMedia ? navigator.mediaDevices.getUserMedia.bind(navigator.mediaDevices) : null;

    if (!camera && navigator.getUserMedia) {
        camera = function (constraints) {
            return new Promise(function (resolve, reject) {
                navigator.getUserMedia(constraints, resolve, reject);
            });
        };
    }

    if (!camera && navigator.mozGetUserMedia) {
        camera = function (constraints) {
            return new Promise(function (resolve, reject) {
                navigator.mozGetUserMedia(constraints, resolve, reject);
            });
        };
    }

    if (!camera && navigator.webkitGetUserMedia) {
        camera = function (constraints) {
            return new Promise(function (resolve, reject) {
                navigator.webkitGetUserMedia(constraints, resolve, reject);
            });
        };
    }

    function selectedOverlay() {
        var index;
        for (index = 0; index < overlayInputs.length; index += 1) {
            if (overlayInputs[index].checked) {
                return overlayInputs[index].getAttribute('data-overlay-key');
            }
        }
        return '';
    }

    function updateOverlay() {
        var path = selectedOverlay();
        var selectedInput = null;
        var index;
        for (index = 0; index < overlayInputs.length; index += 1) {
            if (overlayInputs[index].checked) {
                selectedInput = overlayInputs[index];
            }
        }
        overlayKey.value = path;
        overlay.src = selectedInput ? selectedInput.value : '';
        overlay.style.display = selectedInput && selectedInput.value ? 'block' : 'none';
        button.disabled = !path;
    }

    function startCamera() {
        if (!camera) {
            status.textContent = 'This browser does not support webcam access. Use the upload page instead.';
            return;
        }

        camera({video: true, audio: false}).then(function (activeStream) {
            stream = activeStream;
            if ('srcObject' in video) {
                video.srcObject = activeStream;
            } else if (video.mozSrcObject !== undefined) {
                video.mozSrcObject = activeStream;
            } else if (window.URL && window.URL.createObjectURL) {
                video.src = window.URL.createObjectURL(activeStream);
            }
            video.play();
            button.disabled = false;
            status.textContent = 'Camera ready.';
        }, function () {
            status.textContent = 'Camera access was denied or unavailable. Use the upload page instead.';
        });
    }

    function capture(event) {
        var context;
        var path;
        var overlayImage;

        event.preventDefault();
        if (!stream && (!sourceImage.files || sourceImage.files.length === 0)) {
            status.textContent = 'Camera is not ready yet.';
            return;
        }

        if (sourceImage.files && sourceImage.files.length > 0) {
            sourceName.value = sourceImage.files[0].name;
            imageData.value = '';
            document.getElementById('capture-form').submit();
            return;
        }

        context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        path = selectedOverlay();
        overlayImage = new Image();
        overlayImage.onload = function () {
            imageData.value = canvas.toDataURL('image/png');
            sourceName.value = 'Webcam capture';
            document.getElementById('capture-form').submit();
        };
        overlayImage.src = selectedInputPath(path);
    }

    function selectedInputPath(key) {
        var index;
        for (index = 0; index < overlayInputs.length; index += 1) {
            if (overlayInputs[index].checked) {
                return overlayInputs[index].value;
            }
        }
        return '';
    }

    var index;
    for (index = 0; index < overlayInputs.length; index += 1) {
        overlayInputs[index].onchange = updateOverlay;
    }
    document.getElementById('capture-form').onsubmit = capture;
    updateOverlay();
    startCamera();
}());
</script>
