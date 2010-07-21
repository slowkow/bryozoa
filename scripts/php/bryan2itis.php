<?php
/*
 * ORDER OF EXECUTION
 * bryan2itis.php
 * species.php
 * getgniauthors.php (only if you don't have GNI names already)
 * addunplaced.php
 * bryansetauthors.php
 */

require 'include/connect.php';
require 'include/scratchpads.php';
/**
 * Get the author and year for the taxon name from `bryozone_easyauthors`.
 * 
 * @param name
 *   The name to use as a query.
 * @return
 *   An associative array with 'authorname' and 'year'.
 */
function getAuthorYear($name) {
  $query = sprintf(
    "SELECT `authorname`, `year` FROM `bryozone_easyauthors`"
    . " WHERE `taxonname`='%s'",
    mysql_real_escape_string($name)
  );
  $result = mysql_query($query);
  $row = mysql_fetch_array($result, MYSQL_ASSOC);
  return trim($row['authorname'] . ' ' . $row['year']);
}
/**
 * Query the bryan_valid table with a rank code and newid and return the
 * associated row.
 * 
 * @param rankcode
 *   A rank code of a taxon.
 * @param newid
 *   An id number of a taxon.
 * @return
 *   The MySQL row that matches the rank code and id.
 */
function getRow($rankcode, $newid) {
  $query = sprintf("SELECT * FROM `bryan_valid`"
    . " WHERE `rankcode`='%s' AND `newid`='%s'",
    mysql_real_escape_string($rankcode),
    mysql_real_escape_string($newid)
  );
  return mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
}
/**
 * Query the bryan_rank table with a rank code and return the rank name.
 * 
 * @param rankcode
 *   A rank code number.
 * @return
 *   The name associated with the rank code number.
 */
function getRankName($rankcode) {
  $query = sprintf("SELECT `rankname` FROM `bryan_rank` WHERE `rankcode`='%s'",
    mysql_real_escape_string($rankcode)
  );
  $row = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
  return $row['rankname'];
}
/**
 * Return true or false if the rank code number is not equal to some values.
 * 
 * @param rank_code
 *   A rank code number.
 * @return
 *   Return true if the rank code is not equal to some values.
 */
function isValidRankCode($rank_code) {
  switch ($rank_code) {
    case 10: // Phylum
    case 20: // Class
    case 30: // Order
    case 40: // Suborder
    case 50: // Infraorder
    case 70: // Superfamily
    case 80: // Family
    case 90: // Genus
    case 100: // Subgenus
    case 110: // Species
      return true;
  }
  return false;
}
/**
 * Return the next parent that is a valid rank and is not called 'NULL' or
 * 'uncertain'.
 * 
 * @param row
 *   A row from bryozoa_taxa.
 * @return
 *   Parent row that is a valid rank and is not called 'NULL' or 'uncertain'.
 */
function nextRealParent($row) {
  while ($row['newrefid']) {
    // we linked up to a Class, so return Bryozoa as the next real parent
    if ($row['rankcode'] == 20) {
      break;
    }
    $row = getRow(nextValidRank($row['rankcode']), $row['newrefid']);
    // the name is not 'NULL' or 'uncertain'
    if (!preg_match("/(?:null|uncertain)/i", $row['name'])) {
      return $row;
    }
  }
  // either we broke out of the loop or the row had no newrefid, so link
  // this guy to Bryozoa
  return array('rankcode' => 10, 'name' => 'Bryozoa');
}
/**
 * Return next higher valid rank code.
 * 
 * @param rank_code
 *   A rank code.
 * @return
 *   The next higher valid rank code.
 */
function nextValidRank($rank_code) {
  if ($rank_code <= 10) { return 10; }
  do {
    $rank_code--;
  } while (!isValidRankCode($rank_code));
  return $rank_code;
}

// get author/year information from bryozone
mysql_query("DROP TABLE IF EXISTS `bryozone_easyauthors`");
mysql_query(
  "CREATE TABLE `bryozone_easyauthors`"
  . " AS SELECT `t`.`rankcode`, `t`.`taxonname`, `a`.`authorname`, `t`.`year`"
  . " FROM `bryozone_taxa` `t`, `bryozone_authors` `a`, `bryozone_taxa_authors` `b`"
  . " WHERE `t`.`taxonid`=`b`.`taxonid`"
  . " AND `a`.`authorid`=`b`.`authorid`"
  . " AND `t`.`rankcode` < 110"
);

// insert Bryozoa
insertIntoScratchpads(
  array(
    'full_name'  => 'Bryozoa',
    'rank_name'  => 'Phylum',
    'unit_name1' => 'Bryozoa',
    'usage'      => 'valid',
  )
);

// select some vars to put in row
// "IS NOT NULL" will return true for rows with name='NULL'
$result = mysql_query(
  "SELECT `name`, `rankcode`, `newid`, `newrefid`"
  . " FROM `bryan_valid`"
  . " WHERE (`name` IS NOT NULL"
  . " AND `name` NOT REGEXP 'null|uncertain'" // no bad names
  . " AND `rankcode` IS NOT NULL"
  . " AND `newid` IS NOT NULL"      // if null, then it cannot be linked to
  //. " AND `newrefid` IS NOT NULL" // if null, we'll link them to Bryozoa
  . " AND `rankcode` < 99990)"
);

// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  // get row's unit names directly
  list($unit_name1, $unit_name2, $unit_name3) =
    explode(" ", $row['name'], 3);
  // format the name properly
  $unit_name1 = trim(ucfirst(strtolower($unit_name1)));
  $unit_name2 = trim(strtolower($unit_name2));
  $unit_name3 = trim(strtolower($unit_name3));
  
  // we still have a name after trimming and rank is valid
  if ($unit_name1 && isValidRankCode($row['rankcode'])) {
    // find a parent that is named
    // if we don't find anyone, then Bryozoa is the parent
    $parent_row = nextRealParent($row);
    $parent_name = trim(ucfirst(strtolower($parent_row['name'])));
    
    insertIntoScratchpads(
      array(
        'rank_name'    => getRankName($row['rankcode']),
        'unit_name1'   => $unit_name1,
        'unit_name2'   => $unit_name2,
        'unit_name3'   => $unit_name3,
        'parent_name'  => $parent_name,
        'usage'        => 'valid',
        'full_name'    => trim($unit_name1 . ' ' . $unit_name2 . ' ' . $unit_name3),
        'taxon_author' => getAuthorYear($unit_name1),
      )
    );
  }
}
mysql_free_result($result);

/*******************************************************************************
 * Fix parent_name to include the author and year.
 */
$result = mysql_query("SELECT * FROM `scratchpads`");
// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $parent_name = trim($row['parent_name']
    . ' ' . getTaxonAuthor($row['parent_name']));
  $parent_name = mysql_real_escape_string($parent_name);
  $query = sprintf("INSERT INTO `scratchpads`"
    . " SET"
    . "`full_name`='%s',"
    . "`parent_name`='%s'"
    . " ON DUPLICATE KEY UPDATE"
    . "`full_name`='%s',"
    . "`parent_name`='%s'",
    $row['full_name'], $parent_name,
    $row['full_name'], $parent_name
  );
  mysql_query($query);
  if (mysql_error()) { die(mysql_error() . "\n"); }
}