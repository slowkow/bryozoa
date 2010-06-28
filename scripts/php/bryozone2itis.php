<?php
/*
 * The rank codes and names used in several different files.
 */
$ranks = array(
  0 => 'Invalid',
  1 => 'Nomen Oblitum',
  2 => 'Nomen Nudum',
  3 => 'Uncertain Classification',
  10 => 'Phylum',
  20 => 'Class',
  30 => 'Order',
  36 => 'Subjective Junior Synonym',
  40 => 'Suborder',
  50 => 'Infraorder',
  60 => 'Grade',
  70 => 'Superfamily',
  80 => 'Family',
  85 => 'Family Synonym',
  90 => 'Genus',
  95 => 'Genus Synonym',
  96 => 'Subjective Junior Synonym',
  97 => 'Objective Junior Synonym',
  98 => 'Homonym',
  100 => 'Subgenus',
  110 => 'Species',
  113 => 'Uncertain Species',
  115 => 'Species Synonym',
  116 => 'Subjective Junior Synonym',
  117 => 'Objective Junior Synonym',
  118 => 'Homonym',
  99999 => 'Error',
);

/**
 * Read a Bryozone-type file into an array. Set $header to TRUE if the file
 * has a header line with the fieldnames.
 * 
 * @param filename
 *   The full path to the file
 * @param taxonarray
 *   The array into which the file will be read
 * @param header
 *   Set to true if the file has a header line with fieldnames
 */
function readBryanFile($filename, $taxonarray, $header=TRUE) {
  $file1 = fopen($filename, 'r') or die("Cannot open $filename");
  
  // count number of rows
  $count = 0;
  while (!feof($file1)) {
    // read one line and trim extra whitespace
    $row = trim(fgets($file1));
    
    // skip comments and empty lines
    if ($row[0] == '#' || strlen($row) == 0) {
      continue;
    }
    
    // count each non-comment line
    $count++;
    
    // skip header
    if ($count == 1 && $header) {
      continue;
    }
    
    // TODO
    // experiment with one more field $extra to capture the last column in the
    // species rows
    list($oldid, $oldpid, $name, $rank, $date, $id, $pid) = explode("\t", $row);
    
    if (!$name || !$rank || !$id || !$pid) {
      $badrows++;
      continue;
    }
    
    $taxonarray[$rank][$id] = array(
      'name' => $name,
      'rank' => $rank,
      'id'   => $id,
      'pid'  => $pid,
    );
  }
  print("$badrows rows missing name or rank or id or pid.\n");
  fclose($file1);
}