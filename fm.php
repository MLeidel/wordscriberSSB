<?php // fm.php
// create file list
$dir = "images";
$x = 0;
if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
						$aryFiles[$x] =$file;
						$x++;
				}
		}
		closedir($handle);
}

usort($aryFiles, 'strcasecmp'); // case insensitive sort

// get save options : font, size, color, height of CE, background
$Opts = file("options.dat");
$Opts = array_map('trim', $Opts);
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset='UTF-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1'>
	<title>File Manager</title>
	<script type="text/javascript" src="../js/myJS-1.1.min.js"></script>
	<style>
  body {
    background: <?php echo $Opts[5]?>;
    margin: 20px;
  }
  input[type=submit], button, input[type=file]::file-selector-button {
    border-radius: 7px;
    padding: 3px 6px;
    cursor: pointer;
    background-color: <?php echo $Opts[5]?>; /* Example background color */
    border: 1px solid #111; /* Example border */
    margin-top: 2px;
    transition: background-color 0.6s;
  }
  input[type=submit]:hover, button:hover, input[type=file]::file-selector-button:hover {
    background-color: white;
  }
  .header {
    background-color: <?php echo $Opts[5]?>;
    border-width: 0;
  }
  .table-container {
      height: 150px; /* Adjust the height as needed to show 6 rows */
      overflow-y: auto; /* Enables vertical scrolling */
      border: 1px solid #ccc; /* Optional: adds a border around the table */
  }

  table {
      width: 100%; /* Ensures the table takes the full width of the container */
      border-collapse: collapse; /* Optional: removes spacing between table cells */
  }

  th, td {
      padding: 1px;
      text-align: left;
  }
  </style>
</head>
<body>
<h2>iDoc</h2>
<h3>Image File Manager</h3><br>

  <form name="f1" action="fmHand.php" method="post">
  <div class="table-container">
  <table>
  <tr>
    <td style="text-align: center;">Image File name</td>
    <td style="text-align: center;">Rename file or 'del' to delete</td>
  </tr>
  <?php
    for ($inx=0; $inx < $x; $inx++) {
      echo "<tr><td><input type='text' size='30' name='items[]'   value='$aryFiles[$inx]'></td>";
      echo "<td><input type='text' size='30' name='actions[]' value=''></td></tr>";
    }
  ?>
  </table>
  </div> <!-- end table-container -->
  <br>
  <input type="submit" name="sub" value=" Submit ">&nbsp;&nbsp;&nbsp;&nbsp;
  <span style="float:right;"><button onclick="window.close()">Close Window</button></span>
  </form>


<br><hr> <!-- I M A G E  U P L O A D E R -->

<h3>iDoc Image Upload</h3>
<form enctype="multipart/form-data" action="uphand.php" method="POST">
	<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
	<input type="hidden" name="cdir" value="images" />
	Select file: <input name="userfile" type="file" /><br><br>
	<center><input type="submit" value=" Upload File " /></center>
</form>

</body>
</html>
