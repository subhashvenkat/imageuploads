<?php
//https://maximivanov.github.io/php-error-reporting-calculator/

ini_set("error_reporting", 2037);

if( $_POST['action'] == "upload" ){

	sleep(2);

	$fn = "./files/" . time() . $_FILES['file']['name'];
	$st = move_uploaded_file($_FILES['file']['tmp_name'], $fn);
	if( !$st ){
		echo json_encode(['status'=>"fail", "error"=>"Only images are required"]);exit;
	}
	echo json_encode(['status'=>"success"]);exit;


}


?>
<html>
<body>
<style>
	.box { width:300px; height:300px; border:1px solid #ccc; text-align:center; margin:10px; float: left; }
	.box img{ max-width:100%; max-height:250px;  }
	.box div{ height:40px; background-color:#f0f0f0;border:1px solid darkred;  }
	.pbar{ width:250px; height:20px; background-color:palegreen; }
	.pbar div{ height:20px; background-color:#f00; width:0%; }
</style>
  <!-- <script src="/vue.global.prod.js"></script> -->
  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<div id="app" >
	<marquee><h2><u>Files Upload</u></h2></marquee>
	<div style="width:300px"><input class="form-control" style="color: blueviolet;" type="file" multiple id="file1" v-on:change="validate_file_select()" ></div>
	<div style="border: 1px solid #ccc; margin-bottom: 10px;" v-for="v in file_queue">
		<div>{{ v.name }}</div>
		<div v-if="v['data']" ><img v-bind:src="v['data']" style="max-width:200px; max-height: 200px;padding-bottom: 5px;" ></div>
		<div v-if="v['status']=='uploading'" class="pbar" >
			<div v-bind:style="'width:'+v['percent']+'%'" style="text-align: center;" > {{ v.percent }}% <div class="spinner-border text-primary spinner-border-xs" style="width:20px;"></div></div>
		</div>
		<div v-if="v['status']=='pending'" >Pending</div>
		<div v-if="v['status']=='uploaded'" >Successfully Uploaded<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="darkgreen" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/></svg></div>


		<!-- <div v-if="v['status']=='uploaded'" style="position: absolute;left: 160px;top: 290px;" ><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="darkgreen" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/></svg></div> -->



	</div>
	<pre>{{ file_queue }}</pre>
</div>

<script>
var app = Vue.createApp({
	"data": function(){
		return {
			"file_queue": [],
		}
	},
	"methods": {
		validate_file_select: function(){
			for(var i=0;i<document.getElementById("file1").files.length;i++){
				var n = document.getElementById("file1").files[i].name;
				if( n.match(/\.(jpeg|jpg|gif|png|svg)$/i) == null ){
					alert("Please select image files only");
					document.getElementById("file1").value = "";
					return;
				}

				

			    this.file_queue.push({
					"file": document.getElementById("file1").files[i],
					"name": document.getElementById("file1").files[i].name,
					"status": "pending",
					"msg": "",
					"data": URL.createObjectURL(document.getElementById("file1").files[i]),
					"percent": 0,
					//"reader": reader
				});

			}
			setTimeout(this.upload_file,100);
		},
		upload_file(){
			for(var i=0;i<this.file_queue.length;i++){
				if( this.file_queue[i]['status'] == "pending" ){
					this.file_queue[i]['status'] = "uploading";
					this.upload_file_now(i);
					break;
				}
					this.file_queue[i]['status'] = "uploaded";

			}
		},
		upload_file_now: function(vi){

			var vdata = new FormData();
			vdata.append("file", this.file_queue[vi]['file'] );
			vdata.append("action", "upload" );
			var con = new XMLHttpRequest();
			con.open("POST", "?", true);

			//con.setRequestHeader("Content-Type", "multipart/form-data");

			con.upload.addEventListener("progress", (event) => {
				  // console.log( event.loaded ); // Update the progress bar

				  var t =  ((event.loaded / event.total) * 100).toFixed(0);
				  this.file_queue[ vi ].percent = t;
				});
			con.queue_index = vi;
			con.app = this;
			con.onload = function(){
				if( this.status == 200 ){
					this.app.file_queue[this.queue_index]['status'] = "done";
					setTimeout(this.app.upload_file,1000);
				}else{
					alert("Something wrong");
				}
				//console.log( this );
			}
			con.send( vdata );
		}
	}
}).mount("#app");
	var file_queue = [];
	
</script>

<?php
$fp = dir("./files/");
while( $fn = $fp->read() ){
	if( preg_match("/\.(jpeg|jpg|gif|png|svg)$/i", $fn) ){
		echo "<div class=\"box\">
		<img src=\"files/".$fn."\" >
		<div style='overflow-x: auto;'>" . $fn . "</div>
		</div>\n";
	}else{
		echo "<div>" . $fn . "</div>\n";
	}
}
?>
</body>
</html>
