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

require 'include/connect.php';

$look_at_scratchpads = 1;
$look_at_bryan = 0;

/*******************************************************************************
 * look at the entries in `scratchpads` without authors
 */
if ($look_at_scratchpads) {
  mysql_query("DROP TABLE IF EXISTS `gni_scratchpads_taxa_authors`");
  mysql_query(
    "CREATE TABLE `gni_scratchpads_taxa_authors` ("
    . " `name` VARCHAR(64) NOT NULL"
    . ", `author` VARCHAR(256) NOT NULL"
    . ", KEY (`name`) )"
  );

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
}

/*******************************************************************************
 * look at all of bryan's taxa
 */
if ($look_at_bryan) {
  mysql_query("DROP TABLE IF EXISTS `gni_bryan_taxa_authors`");
  mysql_query(
    "CREATE TABLE `gni_bryan_taxa_authors` ("
    . " `name` VARCHAR(64) NOT NULL"
    . ", `author` VARCHAR(256) NOT NULL"
    . ", KEY (`name`) )"
  );

  $result = mysql_query(
    "SELECT `name`, `rankcode`"
    . " FROM `bryan_valid`"
    . " WHERE"
    . " `name` NOT REGEXP 'null|uncertain'" // no bad names
    . " AND `rankcode` < 110" // only taxa higher than Species
  );

  $count = 0;
  $numresults = mysql_num_rows($result);
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $results = array();
    // use "uni:" to tell the service that we want higher taxa
    $cmd = "../perl/querygni.pl -d -l -m -n 100 " . "uni:" . $row['name'];
    exec($cmd, $results);
    print("Progress: " . ++$count . "/" . $numresults . "\t" . $row['name'] . "\n");
    foreach ($results as $value) {
      list($name, $author) = explode("\t", $value);
      $query = sprintf("INSERT INTO `gni_bryan_taxa_authors`"
        . " SET"
        . " `name`='%s',"
        . " `author`='%s'",
        mysql_real_escape_string($name),
        mysql_real_escape_string($author)
      );
      mysql_query($query);
    }
  }
}