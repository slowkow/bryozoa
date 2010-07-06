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
 * Return true if the row links all the way to Bryozoa via newrefid.
 * 
 * @param row
 *   A row from bryan_valid.
 * @param print
 *   If true, then print the path from row to Bryozoa.
 * @return
 *   Return true if the row links all the way to Bryozoa via newrefid.
 */
function linksToBryozoa($row, $print) {
  if ($print) { $linkpath = getRankName($row['rankcode']) . " " . $row['name']; }
  while ($row['newrefid']) {
    if ($row['rankcode'] == 20) {
      if ($print) { print("Phylum bryozoa > " . $linkpath . "\n"); }
      return true;
    }
    $row = getRow(nextValidRank($row['rankcode']), $row['newrefid']);
    if ($print) { $linkpath = getRankName($row['rankcode']) . " " . $row['name'] . " > " . $linkpath;}
  }
  if ($print) { print("ERROR " . $linkpath . "\n"); }
  return false;
}

/**
 * Return the next parent that is not called 'NULL'.
 * 
 * @param row
 *   A row from bryozoa_taxa.
 * @param print
 *   If true, then print the path from row to the parent.
 * @return
 *   Return the next parent row that is not called 'NULL'.
 */
function nextRealParent($row, $print) {
  if ($print) { $linkpath = getRankName($row['rankcode']) . " " . $row['name']; }
  // if class, just return bryozoa
  while ($row['newrefid']) {
    // we linked up to a Class, so just return Bryozoa as the next real parent
    if ($row['rankcode'] == 20) {
      break;
    }
    
    $row = getRow(nextValidRank($row['rankcode']), $row['newrefid']);
    if ($print) { $linkpath = getRankName($row['rankcode']) . " " . $row['name'] . " > " . $linkpath; }
    
    if ($row['name'] != 'NULL' && $row['name'] != 'uncertain' && isValidRankCode($row['rankcode'])) {
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

// select some vars to put in row
// "IS NOT NULL" will return true for rows with name='NULL'
$result = mysql_query(
  "SELECT `name`, `rankcode`, `newid`, `newrefid`"
  . " FROM `bryan_valid`"
  . " WHERE (`name` IS NOT NULL"
  . " AND `rankcode` IS NOT NULL"
  . " AND `newid` IS NOT NULL"
  //. " AND `newrefid` IS NOT NULL" // if it's NULL, we'll link them to Bryozoa
  . " AND `rankcode` < 99990)");

/*
 * Print the header
 */
print("rank_name\tunit_name1\tunit_name2\tparent_name\tusage\n");
print("Phylum\tPhylum Bryozoa\t\t\tvalid\n");

// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  // get row's unit names directly
  list($unit_name1, $unit_name2, $unit_name3, $unit_name4) =
    explode(" ", $row['name']);
  // get row's rank name by looking up row's rankcode
  $rank_name = getRankName($row['rankcode']);
  $rank_code = $row['rankcode'];
  // assume validity
  $usage = 'valid';
  
  // we got a name
  if ($rank_name && $unit_name1 && $usage) {
    if ($unit_name1 == 'NULL' || $unit_name1 == 'uncertain') {
      continue;
    }
    
    if (!isValidRankCode($rank_code)) {
      continue;
    }
    
    // find a parent that is named
    $parent_row = nextRealParent($row, FALSE);
    $parent_name = $parent_row['name'];
    $parent_rank_code = $parent_row['rankcode'];
    $parent_rank_name = getRankName($parent_rank_code);
    
/*
    // unnecessary, if not links, then we'll just put Bryozoa as the parent
    // check if this child links all the way back to Bryozoa
    if (!linksToBryozoa($row, FALSE))
    {
      continue;
    }
*/
    
    print("$rank_name\t$rank_name $unit_name1\t$unit_name2\t$parent_rank_name $parent_name\t$usage\n");
/*
    // graphviz output
    $name = trim(implode(" ", array($unit_name1, $unit_name2)));
    print("\"$parent_rank_name $parent_name\" -> \"$rank_name $name\";\n");
*/
  }
}
mysql_free_result($result);