<?php
$dir = new DirectoryIterator(getcwd());
foreach ($dir as $file) {
  if (substr((string)$file, -4) == '.xml') {
    $full_path = $file->getPath() . "/" . (string)$file;
    print 'php ' . dirname(__FILE__) . "/transform.php " . $full_path . "\n";
  }
}

die ($dir);