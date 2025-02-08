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
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

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
    <title>Photo Album</title>
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .album { margin: 10px; padding: 10px; border: 1px solid #ccc; display:inline-block;}
        .photo { max-width: 150px; max-height: 150px; margin: 5px; }
    </style>
</head>
<body>

<div id="app">
    <h2>Photo Album</h2>
    <input type="file" @change="selectFile">
    <select v-model="selected_album">
        <option value="" disabled selected>Select an album</option> 
        <option v-for="album in albums" :value="album['id']">{{ album.name }}</option>
    </select>
    <button @click="uploadFile" class="btn btn-primary btn-sm">Upload</button>

    <h3>Albums</h3>
    <div v-for="album in albums"  class="album">
        <h4>{{ album['name'] }}</h4>
        <div>
            <img v-for="photo in getPhotos(album['id'])" :src="photo['path']" class="photo">
        </div>
    </div>
</div>

<script>
const app = Vue.createApp({
    data() {
        return {
            albums: <?= json_encode($albums) ?>, 
            photos: <?= json_encode($photos) ?>,
            selected_album: "", 
            selected_file: "", 
        };
    },
    methods: {
        selectFile(event) {
            this.selected_file = event.target.files[0];
        },

        uploadFile() {
            if (!this.selected_file || !this.selected_album) {
                alert("Please select a file and an album");
                return;
            }

            const formData = new FormData();
            formData.append("file", this.selected_file);
            formData.append("album_id", this.selected_album);
            axios.post("photo_album.php", formData)
                .then(response => {
                    if (response.data.status === "success") {
                        this.photos.push({ album_id: this.selected_album, path: response.data.path }); 
                    } else {
                        alert(response.data.error);
                    }
                })
                .catch(error => console.error(error));
        },

        getPhotos(albumId) {
            return this.photos.filter(photo => photo.album_id == albumId);
        }
    }
}).mount("#app");
</script>

</body>
</html>
