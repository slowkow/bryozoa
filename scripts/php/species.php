<?php

/**
 * Query the bryozone_rank table with a rank code and return the rank name.
 * 
 * @param rankcode
 *   A rank code number.
 * @return
 *   The name associated with the rank code number.
 */
function getRankName($rankcode) {
  $query = sprintf("SELECT `rankname` FROM `bryozone_rank` WHERE `rankid`='%s'",
    mysql_real_escape_string($rankcode)
  );
  $row = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
  return $row['rankname'];
}

// connect to localhost
$link = mysql_connect('localhost', 'kamil');
if (!$link) { die('Could not connect: ' . mysql_error()); }
// make bock the current db
$db_selected = mysql_select_db('bock', $link);
if (!$db_selected) { die ('Could not use database: ' . mysql_error()); }

$result = mysql_query(
  "SELECT `name`"
  . " FROM `bryozoans`"
  . " WHERE `name` IS NOT NULL"
);

$bryozone_ranks = array();
$bryan_ranks    = array();
$bryozone_count = 0;
$bryan_count    = 0;
// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  list($genus, $species) = split(" ", $row['name']);
  $bryozoans_genus = trim($genus);
  
  $query = sprintf(
    "SELECT `taxonname`, `rankcode` FROM `bryozone_taxa`"
    . " WHERE `taxonname`='%s'",
    //. " AND (`rankcode`=90 OR `rankcode`=100)", //Genus or Subgenus
    mysql_real_escape_string($bryozoans_genus)
  );
  $match = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
  $bryozone_genus = $match['taxonname'];
  $bryozone_rank  = $match['rankcode'];
  
  if ($bryozoans_genus && $bryozone_genus && $bryozone_rank) {
    //print("$bryozoans_genus $bryozone_genus\n");
    $bryozone_count++;
    $bryozone_ranks[$bryozone_rank] += 1;
  }
  
 $query = sprintf(
    "SELECT `name`, `rankcode` FROM `bryan_valid`"
    . " WHERE `name`='%s'",
    //. " AND (`rankcode`=90 OR `rankcode`=100)", //Genus or Subgenus
    mysql_real_escape_string($bryozoans_genus)
  );
  $match = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
  $bryan_genus = $match['name'];
  $bryan_rank  = $match['rankcode'];
  
  if ($bryozoans_genus && $bryan_genus) {
    //print("$bryozoans_genus $bryan_genus\n");
    $bryan_count++;
    $bryan_ranks[$bryan_rank] += 1;
  }
}
print("$bryozone_count genus names from `bryozoans` matched a record in `bryozone_taxa`\n");
foreach ($bryozone_ranks as $rankcode => $count) {
  print("$count matches in `" . getRankName($rankcode) . "` in `bryozone_taxa`\n");
}
print("\n");
print("$bryan_count genus names from `bryozoans` matched a record in `bryan_valid`\n");
foreach ($bryan_ranks as $rankcode => $count) {
  print("$count matches in `" . getRankName($rankcode) . "` in `bryan_valid`\n");
}
