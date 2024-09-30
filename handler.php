<?php // handler.php
/*

*/
extract($_POST);

if ($act == "read") {
  // get an entry based on date
  // if not date return empty string
  $db = new SQLite3('iDoc.db');
  $sql = "SELECT document FROM documents WHERE category = '$cat' and name = '$nam'";
  $results = $db->query($sql) or die($sql);
  $ar = $results->fetchArray();
  if (!$ar) {
    if ($nam == "") {
      echo("select or create file");
    } else {
      // if ($cat == 'd') {
      //   echo("Default : <strong>$nam</strong> was not found");
      // } else {
      //   echo("Final : <strong>$nam</strong> was not found");
      // }
      echo <<<EOD
Document <strong>$nam</strong> not found in this category.<br>
Select a different document, select a different category,<br>
or create new document.
EOD;
    }
  } else {
      echo $ar[0];
    }
  $db->close();
  exit;
}

if ($action == "save") {
  $db = new SQLite3('iDoc.db');
  $sql = "SELECT document FROM documents WHERE category = '$cat' and name = '$nam'";
  $results = $db->query($sql) or die($sql);
  $ar = $results->fetchArray();
  if (!$ar) {
    $text = SQLite3::escapeString($txt);
    $sql = "INSERT INTO documents VALUES ('$cat', '$nam', '$text')";
    $db->exec($sql) or die($sql);
  } else {
    $text = SQLite3::escapeString($txt);
    $sql = "UPDATE documents SET document = '$text' WHERE category = '$cat' and name = '$nam'";
    $db->exec($sql) or die($sql);
  }
  $db->close();
  exit;
}

if ($action == "delete") {
  $db = new SQLite3('iDoc.db');
  $sql = "DELETE FROM documents WHERE category = '$cat' and name = '$nam'";
  $db->exec($sql) or die($sql);
  $db->close();
  exit;
}

echo "no good!";
