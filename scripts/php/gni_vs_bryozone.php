<?php
/**
 * Compare Bryozone's authors to GNI's authors.
 */

require 'include/connect.php';

$result = mysql_query(
  "SELECT `name`, `taxon_author`, `author`"
  . " FROM `scratchpads`, `gni_bryan`"
  . " WHERE `full_name` = `name`"
  . " AND `taxon_author` != `author`"
);

$count = 0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $bryozone_author = $row['taxon_author'];
  $gni_author      = $row['author'];
  // differs only by a comma
  if (str_replace(',', '', $bryozone_author) ==  str_replace(',', '', $gni_author)) {
    continue;
  }
/*
  // compare years
  $matches = array();
  preg_match('/(\d{4})/', $bryozone_author, $matches);
  $bryozone_year = $matches[1];
  
  preg_match('/(\d{4})/', $gni_author, $matches);
  $gni_year = $matches[1];
  if ($bryozone_year == $gni_year) {
    continue;
  }
*/
  print($row['name'] . "\t" . $bryozone_author . "\t" . $gni_author . "\n");
  $count++;
}
print("count: $count\n");
mysql_free_result($result);