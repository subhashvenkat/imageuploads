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
        .modal_photo { max-width: 300px; max-height: 200px;}
        .album { margin: 10px; padding: 10px; border: 1px solid #ccc; display:inline-block; }
        .photo { max-width: 150px; max-height: 150px; margin: 5px; }
    </style>
</head>
<body>
<div id="app" class="container">



    <h2>File Upload with Cropping</h2>


    <div style="width:300px;"><input type="file" id="file1" @change="validate_file_select" class="form-control" style="color: blueviolet;"> 
     </div>
     <div style="position: absolute;left: 450px;top: 40px;">
     	<a href='<?php echo $_SERVER['PHP_SELF']; ?>' class="btn btn-warning">
            Refresh
        </a>
     </div>
    <div>
    <h6>Select Album:</h6>
    <div style="width:300px;"><select v-model="selected_album" class="form-select form-select">
        <option value="" disabled selected>Select an album</option>
        <option v-for="album in albums" :value="album.id">{{ album.name }}</option>
    </select></div>
    </div>


    <button @click="uploadFile" class="btn btn-primary btn-sm">Upload</button>

    <div style="width:500px;max-height: 300px;" v-if="selected_image">
      <img id="image" :src="selected_image" style="max-width: 100%; display: block;">
    </div>
    <div style="padding-left: 230px;"><button @click="cropImage" class="btn btn-info mt-2" v-if="selected_image">Crop</button></div>
    

    <div style="position: absolute;right: 500px;top:100px">
    <h2 >Cropped Image:</h2>
    <div ><img id="croppedImage" :src="cropped_image" v-if="cropped_image" style="width:200px;"></div>
    <button @click="uploadFilenow" class="btn btn-primary mt-2" v-if="cropped_image">Upload Cropped Image</button></div>

    <hr style="border:2px solid red;">
    
    <div style="text-align:center;">
    <h3 style="text-align: center;">Albums</h3>
    <div class="album" @click="vacation_selected()">
        <h4>Vacation</h4>
        <div>
            <img src="https://img.freepik.com/free-vector/beach-vacations_24908-53912.jpg" class="modal_photo">
        </div>
    </div>
    <div class="album" @click="family_selected()">
        <h4>Family</h4>
        <div>
            <img src="https://png.pngtree.com/png-clipart/20231001/original/pngtree-happy-family-cartoon-flat-style-in-heart-wreath-png-image_13025279.png" class="modal_photo">
        </div>
    </div>
    <div class="album" @click="nature_selected()">
        <h4>Nature</h4>
        <div>
            <img src="https://t3.ftcdn.net/jpg/06/79/54/40/360_F_679544086_f3W7bO6jPBSJvLFtL9qSFQxHpvpM4Bno.jpg" class="modal_photo">
        </div>
    </div>
</div>

    <hr style="border:2px solid red;">

        <div id="vacation_div" style="display:none;">
    <h3>Vacation Images:</h3>
        <div class="box" v-for="photo in getPhotos('1')" :key="photo.id">
            <img :src="photo.path" style="max-width:300px;max-height:200px;">
            <div style="overflow-x:auto">{{photo.filename}}</div>
        </div>
    </div>

    <div id="family_div" style="display:none;">
    <h3>Family Images:</h3>
        <div class="box" v-for="photo in getPhotos('2')" :key="photo.id">
            <img :src="photo.path" style="max-width:300px;max-height:200px;">
            <div style="overflow-x:auto">{{photo.filename}}</div>
        </div>
    </div>

    <div id="nature_div" style="display:none;">
    <h3>Nature Images:</h3>
        <div class="box" v-for="photo in getPhotos('3')" :key="photo.id">
            <img :src="photo.path" style="max-width:300px;max-height:200px;">
            <div style="overflow-x:auto">{{photo.filename}}</div>
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

    	vacation_selected()
        {
            document.getElementById('vacation_div').style.display="block";
            document.getElementById('family_div').style.display="none";
            document.getElementById('nature_div').style.display="none";
        },
        family_selected()
        {
            document.getElementById('vacation_div').style.display="none";
            document.getElementById('family_div').style.display="block";
            document.getElementById('nature_div').style.display="none";
        },
        nature_selected()
        {
            document.getElementById('vacation_div').style.display="none";
            document.getElementById('family_div').style.display="none";
            document.getElementById('nature_div').style.display="block";
        },


        validate_file_select(event) {
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
