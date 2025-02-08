<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cropper.js Example</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.css">
    <style>
        .img-container {
            max-width: 100%;
            max-height: 500px;
        }
    </style>
</head>
<body>

<h1>Crop Your Image</h1>

<!-- File input to select an image -->
<input type="file" id="uploadImage" accept="image/*">

<!-- Image to be cropped -->
<div class="img-container">
    <img id="image" src="" alt="Choose an image" style="max-width: 100%; display: none;">
</div>

<!-- Crop button -->
<button id="cropBtn">Crop</button>

<!-- Cropped image preview -->
<h2>Cropped Image:</h2>
<img id="croppedImage" src="" alt="Cropped image preview">

<script src="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.js"></script>
<script>
    let cropper;

    // Handle image upload
    document.getElementById('uploadImage').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('image');
                img.src = e.target.result;
                img.style.display = 'block';
                
                // Initialize cropper after image is loaded
                img.onload = function() {
                    if (cropper) {
                        cropper.destroy(); // Destroy any existing cropper instance
                    }
                    cropper = new Cropper(img, {
                        aspectRatio: 1, // Set a fixed aspect ratio (1:1) for square cropping
                        viewMode: 1, // Restrict the image to the container
                        responsive: true,
                    });
                };
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle crop button click
    document.getElementById('cropBtn').addEventListener('click', function() {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas();
            const croppedImage = document.getElementById('croppedImage');
            croppedImage.src = canvas.toDataURL(); // Set the cropped image as source for preview
        }
    });
</script>

</body>
</html>
