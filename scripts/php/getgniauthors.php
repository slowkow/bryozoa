<?php
/**
 * Find entries in table `scratchpads` without authors. Query GNI with the
 * names of these taxa and put the results in `gni_scratchpads`.
 */

require 'include/connect.php';

// get gni authors for `scratchpads` entries without authors
$look_at_scratchpads = 1;
// get gni authors for all `bryan_valid` entries higher than species
$look_at_bryan = 0;

/*******************************************************************************
 * look at the entries in `scratchpads` without authors
 */
if ($look_at_scratchpads) {
  //mysql_query("DROP TABLE IF EXISTS `gni_scratchpads`");
  mysql_query(
    "CREATE TABLE `gni_scratchpads` ("
    . " `name` VARCHAR(64) NOT NULL"
    . ", `author` VARCHAR(256) NOT NULL"
    . ", KEY (`name`) )"
  );

  $result = mysql_query(
    "SELECT `unit_name1`"
    . " FROM `scratchpads`"
    . " WHERE"
    . " (`taxon_author` IS NULL"
    . " OR `taxon_author` NOT REGEXP '[0-9]')"
    . " AND `unit_name1` NOT IN (SELECT `name` FROM `gni_scratchpads`)"
  );

  $count = 0;
  $numresults = mysql_num_rows($result);
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $results = array();
    // use "uni:" to tell the service that we want higher taxa
    $cmd = "../perl/querygni.pl -d -l -m -n 100 " . "uni:" . $row['unit_name1'];
    exec($cmd, $results);
    print("Progress: " . ++$count . "/" . $numresults . "\t"
      . 'Taxon: ' . $row['unit_name1']
      . "\tAuthor(s): " . join("\t", $results) . "\n");
    foreach ($results as $value) {
      list($name, $author) = explode("\t", $value);
      $query = sprintf("INSERT INTO `gni_scratchpads`"
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
 * look at all of bryan's higher taxa
 */
if ($look_at_bryan) {
  mysql_query("DROP TABLE IF EXISTS `gni_bryan`");
  mysql_query(
    "CREATE TABLE `gni_bryan` ("
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
      $query = sprintf("INSERT INTO `gni_bryan`"
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