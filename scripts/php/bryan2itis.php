<?php
/*
 * This script queries the bryan_* tables and can output a proper ITIS
 * output for uploading to Scratchpads.
 * 
 * bryan
 * 
CREATE TABLE `bryan_valid` (
  `oldid` INT,
  `oldrefid` INT,
  `name` VARCHAR(512),
  `rankcode` INT,
  `year` VARCHAR(128),
  `newid` INT,
  `newrefid` INT,
  `comments` VARCHAR(2000),
  KEY (`oldid`),
  KEY (`oldrefid`),
  KEY (`rankcode`),
  KEY (`newid`),
  KEY (`newrefid`),
  KEY (`name`)
);
 * 
 * ITIS
 * 
unit_name1	rank_name	parent_name	usage
bryozoa	Phylum	animalia	valid
 */

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
 * @param print
 *   If true, then print the path from row to the parent.
 * @return
 *   Parent row that is a valid rank and is not called 'NULL' or 'uncertain'.
 */
function nextRealParent($row, $print) {
  if ($print) { $linkpath = getRankName($row['rankcode']) . " " . $row['name']; }
  while ($row['newrefid']) {
    // we linked up to a Class, so return Bryozoa as the next real parent
    if ($row['rankcode'] == 20) {
      break;
    }
    
    $row = getRow(nextValidRank($row['rankcode']), $row['newrefid']);
    if ($print) { $linkpath = getRankName($row['rankcode']) . " " . $row['name'] . " > " . $linkpath; }
    
    // the name is not 'NULL' or 'uncertain'
    if ($row['name'] != 'NULL' && $row['name'] != 'uncertain') {
      if ($print) { print($linkpath . "\n"); }
      return $row;
    }
  }
  // either we broke out of the loop or the row had no newrefid, so link
  // this guy to Bryozoa
  $row = array('rankcode' => 10, 'name' => 'Bryozoa');
  if ($print) { print(getRankName($row['rankcode']) . " " . $row['name'] . " > " . $linkpath . "\n"); }
  return $row;
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



// enable printing or not
$g_print = FALSE;
// execute sql queries or not
$g_mysql = TRUE;

/*
 * Print the header
 */
if ($g_print) {
  print("rank_name\tunit_name1\tunit_name2\tunit_name3\tparent_name\tusage\ttaxon_author\taccepted_name\tunacceptability_reason\n");
  //print("Phylum\tPhylum Bryozoa\t\t\t\tvalid\n");
  print("Phylum\tBryozoa\t\t\t\tvalid\t\t\t\n");
}
if ($g_mysql) {
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
}

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
  $rank_code = $row['rankcode'];
  $rank_name = getRankName($rank_code);
  // assume validity
  $usage = 'valid';
  
  // we got a name
  if ($rank_name && $unit_name1 && $usage) {
    // the name is not a real entry
    if ($unit_name1 == 'Null' || $unit_name1 == 'Uncertain') {
      continue;
    }
    // we're not printing invalid entries
    if (!isValidRankCode($rank_code)) {
      continue;
    }
    // find a parent that is named, don't print
    // if we don't find anyone, then Bryozoa is the parent
    $parent_row = nextRealParent($row, FALSE);
    $parent_name = trim(ucfirst(strtolower($parent_row['name'])));
    $parent_rank_name = getRankName($parent_row['rankcode']);
    
    $taxon_author = "";
    $accepted_name = "";
    $unacceptability_reason = "";
    
    if ($g_print) {
      print("$rank_name\t$unit_name1\t$unit_name2\t$unit_name3\t$parent_name\t$usage\t$taxon_author\t$accepted_name\t$unacceptability_reason\n");
    }
    if ($g_mysql) {
      $full_name = trim($unit_name1 . " " . $unit_name2 . " " . $unit_name3);
      
      $rank_name   = mysql_real_escape_string($rank_name);
      $unit_name1  = mysql_real_escape_string($unit_name1);
      $unit_name2  = mysql_real_escape_string($unit_name2);
      $unit_name3  = mysql_real_escape_string($unit_name3);
      $parent_name = mysql_real_escape_string($parent_name);
      $usage       = mysql_real_escape_string($usage);
      $full_name   = mysql_real_escape_string($full_name);
      
      $query = sprintf("INSERT INTO `scratchpads`"
        . " (`rank_name`, `unit_name1`, `unit_name2`, `unit_name3`, `parent_name`, `usage`, `full_name`)"
        . " VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s')"
        . " ON DUPLICATE KEY UPDATE"
        . " `rank_name`='%s',"
        . " `unit_name1`='%s',"
        . " `unit_name2`='%s',"
        . " `unit_name3`='%s',"
        . " `parent_name`='%s',"
        . " `usage`='%s',"
        . " `full_name`='%s'",
        $rank_name, $unit_name1, $unit_name2, $unit_name3, $parent_name, $usage, $full_name,
        $rank_name, $unit_name1, $unit_name2, $unit_name3, $parent_name, $usage, $full_name
      );
      mysql_query($query);
      if (mysql_error()) { print(mysql_error() . "\n"); }
    }
  }
}
mysql_free_result($result);