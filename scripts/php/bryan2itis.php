<?php
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
  return mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
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

// connect to localhost
$link = mysql_connect('localhost', 'kamil');
if (!$link) { die('Could not connect: ' . mysql_error()); }
// make bock the current db
$db_selected = mysql_select_db('bock', $link);
if (!$db_selected) { die ('Could not use database: ' . mysql_error()); }

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
mysql_query(
  "INSERT INTO `scratchpads`"
  . " (`rank_name`, `unit_name1`, `usage`, `full_name`)"
  . " VALUES ('Phylum', 'Bryozoa', 'valid', 'Bryozoa')"
  . " ON DUPLICATE KEY UPDATE"
  . " `rank_name`='Phylum',"
  . " `unit_name1`='Bryozoa',"
  . " `usage`='valid',"
  . " `full_name`='Bryozoa'"
);
if (mysql_error()) { print(mysql_error() . "\n"); }

// select some vars to put in row
// "IS NOT NULL" will return true for rows with name='NULL'
$result = mysql_query(
  "SELECT `name`, `rankcode`, `newid`, `newrefid`"
  . " FROM `bryan_valid`"
  . " WHERE (`name` IS NOT NULL"
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
  // get row's rank name by looking up row's rankcode
  $rank_name = getRankName($row['rankcode']);
  
  // we still have a name after trimming
  if ($unit_name1 && $rank_name
  // the name is valid
  && !preg_match("/(?:null|uncertain)/i", $unit_name1)
  // the rank is valid
  && isValidRankCode($row['rankcode'])) {
    // find a parent that is named
    // if we don't find anyone, then Bryozoa is the parent
    $parent_row = nextRealParent($row, FALSE);
    $parent_name = trim(ucfirst(strtolower($parent_row['name'])));
    $parent_rank_name = getRankName($parent_row['rankcode']);
    
    $rank_name   = mysql_real_escape_string($rank_name);
    $unit_name1  = mysql_real_escape_string($unit_name1);
    $unit_name2  = mysql_real_escape_string($unit_name2);
    $unit_name3  = mysql_real_escape_string($unit_name3);
    $parent_name = mysql_real_escape_string($parent_name);
    $usage       = 'valid';
    // get author and year
    $match = getAuthorYear($unit_name1);
    $taxon_author = mysql_real_escape_string(trim($match['authorname'] . " " . $match['year']));
    
    $full_name = trim($unit_name1 . " " . $unit_name2 . " " . $unit_name3);
    $full_name   = mysql_real_escape_string($full_name);
    
    $query = sprintf("INSERT INTO `scratchpads`"
      . " SET"
      . " `rank_name`='%s',"
      . "`unit_name1`='%s',"
      . "`unit_name2`='%s',"
      . "`unit_name3`='%s',"
      . "`parent_name`='%s',"
      . "`usage`='%s',"
      . "`full_name`='%s',"
      . "`taxon_author`='%s'"
      . " ON DUPLICATE KEY UPDATE"
      . " `rank_name`='%s',"
      . "`unit_name1`='%s',"
      . "`unit_name2`='%s',"
      . "`unit_name3`='%s',"
      . "`parent_name`='%s',"
      . "`usage`='%s',"
      . "`full_name`='%s',"
      . "`taxon_author`='%s'",
      $rank_name, $unit_name1, $unit_name2, $unit_name3, $parent_name, $usage, $full_name, $taxon_author,
      $rank_name, $unit_name1, $unit_name2, $unit_name3, $parent_name, $usage, $full_name, $taxon_author
    );
    mysql_query($query);
    if (mysql_error()) { die(mysql_error() . "\n"); }
  }
}
mysql_free_result($result);