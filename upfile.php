<?php
// upfile.php

// get save options : font, size, color, height of CE, background
$Opts = file("options.dat");
$Opts = array_map('trim', $Opts);
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>iDoc Image File Uploader</title>
  <script type="text/javascript" src="js/myJS-1.1.min.js"></script>
  <style>
  body {
    margin: 20px;
    background: <?php echo $Opts[5]?>;
    font-family: 'sans-serif';
    font-size: 12pt;
    margin-left: 25px;
  }

  </style>
</head>
<body>
<h1>iDoc Image Upload</h1>
<form enctype="multipart/form-data" action="uphand.php" method="POST">
	<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
	<input type="hidden" name="cdir" value="images" />
	Select file: <input name="userfile" type="file" /><br><br>
	<center><input type="submit" value=" Upload File " /></center>
</form>
<br><br>
<center>
<button onclick="window.close()">Close Window</button>
</center>
</body>
</html>
