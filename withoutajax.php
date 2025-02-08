<?php
//https://maximivanov.github.io/php-error-reporting-calculator/

ini_set("error_reporting", 2037);

if( $_POST['action'] == "upload" ){

	$fn = "./files/" . $_FILES['file']['name'];
	$st = move_uploaded_file($_FILES['file']['tmp_name'], $fn);
	if( !$st ){
		echo json_encode(['status'=>"fail", "error"=>"Only images are required"]);exit;
	}
	echo json_encode(['status'=>"success"]);exit;
}

?>
<html>
<body>
<h2>File Box</h2>
<div><input type="file" name="file" id="file1" onchange="validate_file_select()" ></div>
<div id="msg" ></div>
<div id="pbar" ><div id="pbar2"></div></div>
<script>
	function validate_file_select(){
			var n = document.getElementById("file1").files[0].name;
			// if( n.match(/\.(jpeg|jpg|gif|png|svg)$/i) == null ){
			// 	alert("Please select image files only");
			// 	document.getElementById("file1").value = "";
			// 	return;
			// }
		var vdata = new FormData();
		vdata.append("file", document.getElementById("file1").files[0] );
		vdata.append("action", "upload" );
		var con = new XMLHttpRequest();
		con.open("POST", "?", true);
		//con.setRequestHeader("Content-Type", "multipart/form-data");
		con.upload.addEventListener("progress", (event) => {
			  console.log( event.loaded ); // Update the progress bar
			  var t =  ((event.loaded / event.total) * 100).toFixed(2);
			  document.getElementById("msg").innerHTML = `Uploaded: `+t;
			  document.getElementById("pbar2").style.width = t + "px";
			});
		con.onload = function(){
			if( this.status == 200 ){

			}else{
				alert("Something wrong");
			}
			//console.log( this );
		}
		con.send( vdata );
	}
</script>
<style>
	.box{ width:300px; height:300px; border:1px solid #ccc; text-align:center; margin:10px; }
	.box img{ max-width:100%; max-height:250px; }
	.box div{ height:30px; background-color:#f0f0f0; }
	#pbar{ width:500px; height:30px; background-color:#cf0; }
	#pbar div{ height:30px; background-color:#f00; width:0%; }
</style>
<?php
$fp = dir("./files/");
while( $fn = $fp->read() ){
	if( preg_match("/\.(jpeg|jpg|gif|png|svg)$/i", $fn) ){
		echo "<div class=\"box\">
		<img src=\"files/".$fn."\" >
		<div>" . $fn . "</div>
		</div>\n";
	}else{
		echo "<div>" . $fn . "</div>\n";
	}
}
?>

</body>
</html>