<?php

function menu() {
  global $safeFilename, $downloads;
  $uri = $_SERVER['SCRIPT_URL'];
?>
<html>
<head>
<title>Download Menu</title>
</head>
<body>
<form method="GET" action="<?php echo $uri?>">
<select name="filename">
<?php
  $dir = opendir($downloads);
  if (!$dir) {
    die("Bad downloads setting");
  }
  while (($file = readdir($dir)) !== false) {
    // List only files with a safe filename
    if (preg_match($safeFilename, $file)) {
?>
<option value="<?php echo $file?>"><?php echo $file?></option>   
<?php
    }
  }
  closedir($dir);
?>
</select>
<br>
<input type="submit" name="download" value="Download Selected File">
</form>
</body>
</html>
<?php
}

function download() {
  global $filename, $safeFilename, $downloads;
  // MAKE SURE THE FILENAME IS SAFE!
  if (!preg_match($safeFilename, $filename)) {
    error("Bad filename");
  }
  // Now make sure the file actually exists
  if (!file_exists("$downloads/$filename")) {
    error("File does not exist");
  }

  header("Content-disposition: attachment; filename=$filename");
  header("Content-type: application/octet-stream");
  readfile("$downloads/$filename");
  // Exit successfully. We could just let the script exit
  // normally at the bottom of the page, but then blank lines
  // after the close of the script code would potentially cause
  // problems after the file download.
  exit(0);
}

function error($message) {
  // You might want to output a more attractive error
  // message page - hey, you're the web designer
?>
<html>
<head>
<title><?php echo $message?></title>
</head>
<body>
<h1><?php echo $message?></h1>
</body>
</html>
<?php
}

// If the above is not the FIRST line in the file, it WON'T WORK!

// Directory where approved downloadable files live. If we use
// a relative path here (no /) then it is relative to the directory
// where this page is
$downloads = "downloads";

// Regular expression matching a safe filename.

// Filename must contain ONLY letters, digits, and underscores,
// with a single dot in the middle. NO slashes, NO double dots,
// NO pipe characters, nothing potentially dangerous.

// ^ matches the beginning of the string.
// \w matches a "word" character (A-Z, a-z, 0-9, and _ only).
// The "+" sign means "one or more."
// \. matches a single dot.
// And the final $ matches the end of the string.

$safeFilename = '/^\w+\.\w+$/';

// Now get the filename from the user
$filename = $_GET['filename'];

if ($filename == '') {
  # We don't have a filename yet, so generate
  # a file-picking menu page.
  menu();
} else {
  # We have a filename, so download the file.
  download();
}  




?>