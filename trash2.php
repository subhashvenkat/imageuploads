<?php
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "photo_album";

$conn = mysqli_connect($hostname, $username, $password, $dbname);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

    $album_id = $_POST['album_id'];
    $filename = basename($_FILES['file']['name']);
    $target_path = $upload_dir . time() . "_" . $filename;
    $file_type = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

    if (!in_array($file_type, $allowed_types)) {
        die(json_encode(["status" => "fail", "error" => "Only images allowed"]));
    }

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
        $query = "INSERT INTO photos (album_id, path, filename) VALUES ('$album_id', '$target_path', '$filename')";
        mysqli_query($conn, $query);
        die(json_encode(["status" => "success", "path" => $target_path]));
    } else {
        die(json_encode(["status" => "fail", "error" => "File upload failed"]));
    }
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
    <title>File Upload</title>
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .box { width:300px; height:250px; border:1px solid #ccc; text-align:center; margin:10px; float: left; }
        .box img{ max-width:100%; max-height:200px;  }
        .box div{ height:40px; background-color:#f0f0f0;border:1px solid darkred;  }
        .pbar { width: 250px; height: 20px; background-color: palegreen; }
        .pbar div { height: 20px; background-color: #f00; width: 0%; }
        .album { margin: 10px; padding: 10px; border: 1px solid #ccc; display:inline-block;}
        .photo { max-width: 150px; max-height: 150px; margin: 5px; }
    </style>
</head>
<body>
<div id="app" class="container">
    <h2>File Upload</h2>
    
    <input type="file" multiple id="file1" @change="validate_file_select">
    <div style="width: 300px;"><h6>select album:</h6><select  v-model="selected_album" class="form-select">
        <option value="" disabled>Select an album</option>
        <option v-for="album in albums" :value="album.id">{{ album.name }}</option>
    </select></div>
    <button @click="uploadFile" class="btn btn-primary btn-sm mt-2">Upload</button>

    <div v-for="file in file_queue" class="mt-3">
        <div>{{ file.name }}</div>
        <img v-if="file.data" :src="file.data" class="photo">
        <div v-if="file.status == 'uploading'" class="pbar">
            <div :style="'width:' + file.percent + '%'">{{ file.percent }}%</div>
        </div>
        <div v-if="file.status == 'uploaded'" class="text-success">Uploaded Successfully</div>
    </div>

    <!-- Display Albums & Photos -->
    <div v-for="album in albums" class="mt-4">
        <h4 style="text-align: center;">{{ album.name }}</h4>
        <div class="box" v-for="photo in getPhotos(album.id)">
            <img :src="photo.path" class="photo">
            <div style="overflow-x: scroll;">{{photo.filename}}</div>

        </div>
    </div>
</div>

<script>
const app = Vue.createApp({
    data() {
        return {
            albums: <?= json_encode($albums) ?>,
            photos: <?= json_encode($photos) ?>,
            selected_album: null, // Set to null for proper validation
            file_queue: [],
        };
    },
    methods: {
        validate_file_select(event) {
            for (let file of event.target.files) {
                if (!file.name.match(/\.(jpeg|jpg|gif|png|svg)$/i)) {
                    alert("Please select image files only");
                    event.target.value = "";
                    return;
                }
                this.file_queue.push({ file, name: file.name, status: "pending", data: URL.createObjectURL(file), percent: 0 });
            }
            setTimeout(this.uploadFile, 100);
        },
        uploadFile() {
            if (!this.selected_album) {
                alert("Please select an album before uploading.");
                return;
            }

            for (let i = 0; i < this.file_queue.length; i++) {
                if (this.file_queue[i].status === "pending") {
                    this.file_queue[i].status = "uploading";
                    this.upload_file_now(i);
                }
            }
        },
        upload_file_now(index) {
            let formData = new FormData();
            formData.append("file", this.file_queue[index].file);
            formData.append("album_id", this.selected_album);

            axios.post(window.location.href, formData, { // Fix API endpoint
                onUploadProgress: event => {
                    let percent = Math.round((event.loaded / event.total) * 100);
                    this.file_queue[index].percent = percent;
                }
            }).then(response => {
                if (response.data.status === "success") {
                    this.file_queue[index].status = "uploaded";

                    // Add the uploaded image to the photos list
                    this.photos.push({
                        path: response.data.path,
                        filename: this.file_queue[index].name,
                        album_id: this.selected_album
                    });
                } else {
                    alert(response.data.error);
                }
                setTimeout(this.uploadFile, 1000);
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
