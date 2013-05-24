<?php
$dir = new DirectoryIterator(getcwd());
foreach ($dir as $file) {
  if (substr((string)$file, -4) == '.zip') {
    $full_path = $file->getPath() . "/" . (string)$file;
    print 'unzip ' . $full_path  . " && rm " . $full_path . "\n";
  }
}

die ($dir);