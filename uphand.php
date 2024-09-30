<?php
// uphand

$uploaddir = 'images/';  // eg. 'main/folder/' for iDoc
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

if (!strpos($uploadfile, ".png") & !strpos($uploadfile, ".jpg") & !strpos($uploadfile, ".jpeg")) {
  echo "<body style='background: #abc' ><br><br>";
  echo "<span style='color:#a30303;'>File Chosen Not Allowed: <br>only .png, .jpg, and .jpeg files permitted ☹</span><br><br>";
  echo "\n<a href='fm.php'>Return</a>\n\n";
  exit;
}
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>Image Uploader</title>
</head>
<style type="text/css">
	body {
		margin: 20px;
    background: #abc;
	}
</style>
<body>
<?Php
echo "<pre style='font: normal 12pt monospace;'>";

if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		echo "File is valid, and was successfully uploaded.\n";
} else {
		echo "Possible file upload Failure!\n";
}

print_r($_FILES);

echo "\n<a href='fm.php'>Return</a>\n\n";
echo "<pre style='font: normal 9pt monospace;'>\n";

$aFiles = glob($uploaddir . "*.*");
sort($aFiles);

foreach ($aFiles as $filename) {
		echo "$filename size " . filesize($filename) . "\n";
}

?>

</body>
</html>
