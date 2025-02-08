<?php
echo "<pre>";
print_r( $_FILES );
print_r( $_POST );
print_r( $_GET );
print_r( $_SERVER );
echo "</pre>";
?>
<html>
<body>
<form method="post" enctype="multipart/form-data">
	<div><input type="file" multiple name="file[]" ></div>
	<div><input type="text" name="name" value="OK" ></div>
	<div><input type="text" name="number" value="1" ></div>
	<div><input type="submit" value="GO" ></div>
</form>
</body>
</html>
