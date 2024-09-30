<?php // fmHand.php
/*
  1. delete file
  2. rename file
  3. upload file
*/
extract($_POST);

$sd = "images/";
$inx = 0;
$items = $_POST['items'];
$actions = $_POST['actions'];

for ($x=0; $x < count($actions); $x++) {
  if (strtolower($actions[$x]) == "del") {
    unlink($sd . $items[$x]);
  } else {
    if ($actions[$x] != "") {
      rename($sd . $items[$x], $sd . $actions[$x]);
    }
  }
}

header("Location: fm.php");
?>