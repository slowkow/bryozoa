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
  "SELECT `name`, `familyname`"
  . " FROM `bryozoans`"
  . " WHERE `name` IS NOT NULL"
  . " AND `valid`=1"
);

$bryozone_ranks  = array();
$bryozone_count  = 0;

$bryan_ranks     = array();
$bryan_unmatched = array();
$bryan_count     = 0;

// print the ITIS header
print("rank_name\tunit_name1\tunit_name2\tparent_name\tusage\n");

// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  list($genus, $species) = split(" ", $row['name']);
  $bryozoans_family  = trim(ucfirst(strtolower(preg_replace("/[^a-zA-Z]/", "", $row['familyname']))));
  $bryozoans_genus   = trim(ucfirst(strtolower(preg_replace("/[^a-zA-Z]/", "", $genus))));
  $bryozoans_species = trim(strtolower(preg_replace("/[^a-zA-Z]/", "", $species)));
  
  if (!$bryozoans_genus) {
    continue;
  }
  
/*
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
*/
  
  $query = sprintf(
    "SELECT `name`, `rankcode` FROM `bryan_valid`"
    . " WHERE `name`='%s'",
    //. " AND (`rankcode`=90 OR `rankcode`=100)", //Genus or Subgenus
    mysql_real_escape_string($bryozoans_genus)
  );
  $match = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
  $bryan_genus = $match['name'];
  $bryan_rank  = $match['rankcode'];
  
  if ($bryan_genus) {
    $bryan_count++;
    $bryan_ranks[$bryan_rank] += 1;
    if ($bryan_rank < 90 && $bryan_ranks[$bryan_rank] == 1) {
      //print(getRankName($bryan_rank) . " $bryan_genus\n");
    }
    print("Species\t$bryozoans_genus\t$bryozoans_species\t$bryozoans_genus\tvalid\n");
  }
  else {
    if ($bryozoans_family
    && preg_match('/(unassigned|unplaced)/i', $bryozoans_family) == 0) {
      // TODO check if the family name exists in Bryan's taxonomy
/*
      print("Genus\t$bryozoans_genus\t\t$bryozoans_family\tvalid\n");
      print("Species\t$bryozoans_genus\t$bryozoans_species\t$bryozoans_genus\tvalid\n");
*/
    }
    else {
      $bryan_unmatched[$bryozoans_genus] += 1;
    }
  }
}
/**
 * Print a summary of the matches from bryozoans to bryozone_taxa and bryan_valid
 */
/*
print("$bryozone_count genus names from `bryozoans` matched a record in `bryozone_taxa`\n");
foreach ($bryozone_ranks as $rankcode => $count) {
  print("$count matches in `" . getRankName($rankcode) . "` in `bryozone_taxa`\n");
}

print("\n");

print("$bryan_count genus names from `bryozoans` matched a record in `bryan_valid`\n");
foreach ($bryan_ranks as $rankcode => $count) {
  print("$count matches in `" . getRankName($rankcode) . "` in `bryan_valid`\n");
}

$bryan_unmatched_total = 0;
foreach ($bryan_unmatched as $key => $value) {
  print("$key, $value\n");
  $bryan_unmatched_total += $value;
}
print("$bryan_unmatched_total records from `bryozoans` without match in `bryan_valid` and without `familyname`\n");
*/
