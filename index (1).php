<?php
//https://maximivanov.github.io/php-error-reporting-calculator/

ini_set("error_reporting", 2037);

if( $_POST['action'] == "upload" ){

	foreach( $_FILES['file']['name'] as $i=>$j ){
		$fn = "./files/" . $_FILES['file']['name'][ $i ];
		$st = move_uploaded_file($_FILES['file']['tmp_name'][ $i ], $fn);
		if( !$st ){
			echo "<div>File upload failed</div>";exit;
		}
	}
	header("Location: ?event=Uploaded");
	exit;
}

?>
<html>
<body>
<h2>File Box</h2>
<form method="post" enctype="multipart/form-data">
	<div><input type="file" multiple name="file[]" id="file1" onchange="validate_file_select()" ></div>
	<div><input type="submit" value="GO" ></div>
	<input type="hidden" name="action" value="upload" >
</form>
<script>
	function validate_file_select(){
		var l = document.getElementById("file1").files.length;
		for( var i=0;i<l;i++){
			console.log( document.getElementById("file1").files[i] );
			var n = document.getElementById("file1").files[i].name;
			if( n.match(/\.(jpeg|jpg|gif|png|svg)$/i) == null ){
				alert("Please select image files only");
				document.getElementById("file1").value = "";
				return;
			}
		}
	}
</script>
<style>
	.box{ width:300px; height:300px; border:1px solid #ccc; text-align:center; margin:10px; }
	.box img{ max-width:100%; max-height:250px; }
	.box div{ height:30px; background-color:#f0f0f0; }
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
