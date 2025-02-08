<?php
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "photo_album";

$conn = mysqli_connect($hostname, $username, $password, $dbname);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cropped_image'])) {
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

    $album_id = $_POST['album_id'];
    $image_data = $_POST['cropped_image'];
    $filename = "cropped_" . time() . ".png";
    $target_path = $upload_dir . $filename;

    // Decode and save the cropped image
    $image_data = str_replace('data:image/png;base64,', '', $image_data);
    $image_data = base64_decode($image_data);
    file_put_contents($target_path, $image_data);

    $query = "INSERT INTO photos (album_id, path, filename) VALUES ('$album_id', '$target_path', '$filename')";
    mysqli_query($conn, $query);
    die(json_encode(["status" => "success", "path" => $target_path]));
}

$albums_query = "SELECT * FROM albums";
$albums_result = mysqli_query($conn, $albums_query);
$albums = mysqli_fetch_all($albums_result, MYSQLI_ASSOC);

$photos_query = "SELECT * FROM photos";
$photos_result = mysqli_query($conn, $photos_query);
$photos = mysqli_fetch_all($photos_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload with Cropper.js</title>
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.min.css">
    <style>
    	.box { width:300px; height:250px; border:1px solid #ccc; text-align:center; margin:10px; float: left; }
        .box div{ height:40px; background-color:#f0f0f0;border:1px solid darkred;  }
        .pbar { width: 250px; height: 20px; background-color: palegreen; }
        .pbar div { height: 20px; background-color: #f00; width: 0%; }
        .album { margin: 10px; padding: 10px; border: 1px solid #ccc; display:inline-block; }
        .photo { max-width: 150px; max-height: 150px; margin: 5px; }
    </style>
</head>
<body>
<div id="app" class="container">
    <h2>File Upload with Cropping</h2>
    <input type="file" id="file1" @change="handleFileSelect">
    <div>
        <h6>Select Album:</h6>
        <select v-model="selected_album" class="form-select">
            <option value="" disabled>Select an album</option>
            <option v-for="album in albums" :value="album.id">{{ album.name }}</option>
        </select>
    </div>
    
    <div style="width:500px;" v-if="selected_image">
        <img id="image" :src="selected_image" style="max-width: 100%; display: block;">
    </div>
    <button @click="cropImage" class="btn btn-warning mt-2" v-if="selected_image">Crop</button>
    
    <h2>Cropped Image:</h2>
    <div><img id="croppedImage" :src="cropped_image" v-if="cropped_image" style="width:200px;"></div>
    <button @click="uploadFilenow" class="btn btn-primary mt-2" v-if="cropped_image">Upload Cropped Image</button>

    <div v-for="album in albums" class="mt-4">
        <h4>{{ album.name }}</h4>
        <div v-for="photo in getPhotos(album.id)" class="box">
            <img  :src="photo.path" class="photo">
            <div style="overflow-x:scroll;">{{photo.filename}}</div>
        </div>
    </div>
</div>

<script>
const app = Vue.createApp({
    data() {
        return {
            albums: <?= json_encode($albums) ?>,
            photos: <?= json_encode($photos) ?>,
            selected_album: null,
            selected_image: null,
            cropped_image: null,
            cropper: null,
        };
    },
    methods: {
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file || !file.name.match(/\.(jpeg|jpg|gif|png|svg)$/i)) {
                alert("Please select an image file");
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                this.selected_image = e.target.result;
                this.$nextTick(() => {
                    const img = document.getElementById("image");
                    if (this.cropper) this.cropper.destroy();
                    this.cropper = new Cropper(img, { aspectRatio: 1, viewMode: 1 });
                });
            };
            reader.readAsDataURL(file);
        },
        cropImage() {
            if (this.cropper) {
                const canvas = this.cropper.getCroppedCanvas();
                this.cropped_image = canvas.toDataURL();
            }
        },
        uploadFilenow() {
            if (!this.selected_album) {
                alert("Please select an album before uploading.");
                return;
            }
            if (!this.cropped_image) {
                alert("Please crop an image before uploading.");
                return;
            }
            let formData = new FormData();
            formData.append("cropped_image", this.cropped_image);
            formData.append("album_id", this.selected_album);
            axios.post(window.location.href, formData).then(response => {
                if (response.data.status === "success") {
                    this.photos.push({
                        path: response.data.path,
                        album_id: this.selected_album
                    });
                    alert("Upload successful!");
                } else {
                    alert(response.data.error);
                }
            }).catch(() => alert("Upload failed"));
        },
        getPhotos(albumId) {
            return this.photos.filter(photo => photo.album_id == albumId);
        }
    }
}).mount("#app");
</script>
</body>
</html>
