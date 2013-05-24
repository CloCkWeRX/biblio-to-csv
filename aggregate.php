<?php
$year = @$_SERVER['argv'][1];

if (empty($year)) {
  die('php aggregate.php [year]' . "\n");
}

$files = array(
  'patdesc', 'patents', 'agents', 'assignees', 
  'citations', 'class', 'inventors', 'examiners',
  'classifications'
);

foreach ($files as $file) {
  print 'cat ipgb' . $year . '*.xml-' . $file . '.csv > ' . $year . '-' . $file . '.csv' . "\n";
}