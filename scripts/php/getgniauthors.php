<?php
/*
 * This grabs `scratchpads` entries without authors and finds the author from
 * GNI.
 * Puts the data in `gni_scratchpads_taxa_authors`.
 * 
 * TODO
 *   Grab names from GNI for ALL of Bryan's taxa, so we can put missing
 *   parentheses and check what GNI says compared to Bryan.
 */
// connect to localhost
$link = mysql_connect('localhost', 'kamil');
if (!$link) { die('Could not connect: ' . mysql_error()); }
// make bock the current db
$db_selected = mysql_select_db('bock', $link);
if (!$db_selected) { die ('Could not use database: ' . mysql_error()); }

// get author/year information from GNI
mysql_query("DROP TABLE IF EXISTS `gni_scratchpads_taxa_authors`");
mysql_query(
  "CREATE TABLE `gni_scratchpads_taxa_authors` ("
  . " `name` VARCHAR(64) NOT NULL"
  . ", `author` VARCHAR(64) NOT NULL"
  . ", KEY (`name`) )"
);

/*
// look at all of bryan's taxa
$result = mysql_query(
  "SELECT `name`, `rankcode`"
  . " FROM `bryan_valid`"
  . " WHERE"
  . " `name` NOT REGEXP 'null|uncertain'" // no bad names
  . " AND `rankcode` < 110" // only taxa higher than Species
);
*/
// look at the entries in `scratchpads` without authors
$result = mysql_query(
  "SELECT `unit_name1`"
  . " FROM `scratchpads`"
  . " WHERE"
  . " `taxon_author` NOT REGEXP '[0-9]'"
);

$count = 0;
$numresults = mysql_num_rows($result);
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $results = array();
  // use "uni:" to tell the service that we want higher taxa
  $cmd = "../perl/querygni.pl -d -l -m -n 100 " . "uni:" . $row['unit_name1'];
  exec($cmd, $results);
  print("Progress: " . ++$count . "/" . $numresults . "\t" . $row['unit_name1'] . "\n");
  foreach ($results as $value) {
    list($name, $author) = explode("\t", $value);
    $query = sprintf("INSERT INTO `gni_scratchpads_taxa_authors`"
      . " SET"
      . " `name`='%s',"
      . " `author`='%s'",
      mysql_real_escape_string($name),
      mysql_real_escape_string($author)
    );
    mysql_query($query);
    if (mysql_error()) { die(mysql_error() . "\n"); }
  }
}