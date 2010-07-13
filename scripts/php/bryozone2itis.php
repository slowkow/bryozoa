<?php
/*
 * This script queries the bryozone_* tables and can output a proper ITIS
 * output for uploading to Scratchpads.
 * 
 * Bryozone
 * 
CREATE TABLE `bryozone_taxa` (
  `taxonid` INT,
  `parentid` INT,
  `taxonname` VARCHAR(512),
  `rankcode` INT,
  `seniorid` INT,
  `year` INT,
  `expert` VARCHAR(512),
  `revised` DATE,
  `comments` VARCHAR(6000),
  PRIMARY KEY (`taxonid`),
  KEY (`parentid`),
  KEY (`taxonname`),
  KEY (`rankcode`),
  KEY (`seniorid`)
);
 * 
 * ITIS
 * 
unit_name1	rank_name	parent_name	usage
bryozoa	Phylum	animalia	valid
 */

/**
 * Query the bryozone_taxa table with a taxonid and return the associated row.
 * 
 * @param taxonid
 *   An id number of a taxon.
 * @return
 *   The MySQL row that matches the taxon id number.
 */
function getRow($taxonid) {
  $query = sprintf("SELECT * FROM `bryozone_taxa` WHERE `taxonid`='%s'",
    mysql_real_escape_string($taxonid)
  );
  return mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
}

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
 * Return true if the row links all the way to Bryozoa via parentid.
 * 
 * @param row
 *   A row from bryozoa_taxa.
 * @param print
 *   If true, then print the path from row to Bryozoa.
 * @return
 *   Return true if the row links all the way to Bryozoa via parentid.
 */
function linksToBryozoa($row, $print) {
  if ($print) { $linkpath = getRankName($row['rankcode']) . " " . $row['taxonname']; }
  while ($row['parentid']) {
    $row = getRow($row['parentid']);
    if ($print) { $linkpath = getRankName($row['rankcode']) . " " . $row['taxonname'] . " > " . $linkpath;}
    if ($row['taxonname'] == 'Bryozoa') {
      if ($print) { print($linkpath . "\n"); }
      return true;
    }
  }
  if ($print) { print("ERROR " . $linkpath . "\n"); }
  return false;
}

/**
 * input a row
 * climb up to next parent until we find one that is not 'Uncertain'
 * ouput the parent row
 * 
 * Return the next parent that is not called 'Uncertain'.
 * 
 * @param row
 *   A row from bryozoa_taxa.
 * @param print
 *   If true, then print the path from row to the parent.
 * @return
 *   Return the next parent that is not called 'Uncertain'.
 */
function nextRealParent($row, $print) {
  if ($print) { $linkpath = getRankName($row['rankcode']) . " " . $row['taxonname']; }
  while ($row['parentid']) {
    $row = getRow($row['parentid']);
    if ($print) { $linkpath = getRankName($row['rankcode']) . " " . $row['taxonname'] . " > " . $linkpath; }
    if ($row['taxonname'] != 'Uncertain' && isValidRankCode($row['rankcode'])) {
      if ($print) { print($linkpath . "\n"); }
      return $row;
    }
  }
  if ($print) { print("ERROR " . $linkpath . "\n"); }
  return NULL;
}

// connect to localhost
$link = mysql_connect('localhost', 'kamil');
if (!$link) { die('Could not connect: ' . mysql_error()); }
// make bock the current db
$db_selected = mysql_select_db('bock', $link);
if (!$db_selected) { die ('Could not use database: ' . mysql_error()); }

// select some vars to put in row
$result = mysql_query(
  "SELECT `taxonid`, `taxonname`, `parentid`, `rankcode`, `seniorid`"
  . " FROM `bryozone_taxa`"
  . " WHERE (`taxonid` IS NOT NULL"
  . " AND `taxonname` IS NOT NULL"
  . " AND `parentid` IS NOT NULL"
  . " AND `rankcode` IS NOT NULL"
  . " AND `rankcode` < 99990)");

/*
 * Print the header
 */
print("rank_name\tunit_name1\tunit_name2\tunit_name3\tparent_name\tusage\n");
print("Phylum\tPhylum Bryozoa\t\t\t\tvalid\n");

// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  // get parent's name by looking at row's parentid
  $parent_row = getRow($row['parentid']);
  $parent_name = $parent_row['taxonname'];
  $parent_rank_code = $parent_row['rankcode'];
  $parent_rank_name = getRankName($parent_rank_code);
  // get row's rank name by looking up row's rankcode
  $rank_name = getRankName($row['rankcode']);
  // get row's unit names directly
  list($unit_name1, $unit_name2, $unit_name3, $unit_name4) =
    explode(" ", $row['taxonname']);
  // get validity by comparing the seniorid and the taxonid
  if ($row['seniorid'] && $row['seniorid'] == $row['taxonid']) {
    $usage = 'valid';
  }
  else {
    $usage = 'invalid';
  }
  
  $rank_code = $row['rankcode'];
  
  // we got a name
  if ($rank_name && $unit_name1 && $parent_name && $usage) {
    if ($unit_name1 == 'Uncertain') {
      continue;
    }
    
    if (!isValidRankCode($rank_code)) {
      continue;
    }
    
/*
    if ($rank_code >= 90) {
      continue;
      //print("$parent_name\t$unit_name1\t$rank_name\t$parent_name\t$usage\n");
    }
*/
    
    // find a parent that is named
    if ($parent_name == 'Uncertain' || !isValidRankCode($parent_rank_code)) {
      $parent_row = nextRealParent($row, FALSE);
      $parent_name = $parent_row['taxonname'];
      $parent_rank_code = $parent_row['rankcode'];
      $parent_rank_name = getRankName($parent_rank_code);
    }

    // check if this child links all the way back to Bryozoa
    // they all link back, so this is not necessary
/*
    if (!linksToBryozoa($row, FALSE))
    {
      continue;
    }
*/
    
    if ($parent_row == NULL) {
      continue;
    }
    
    // if you don't print for "valid" codes, then you will have gaps
    // between the taxon and Bryozoa
    // TODO make nextRealParent and linksToBryozoa return FALSE
    // if they don't find a valid ITIS rank
    
    print("$rank_name\t$rank_name $unit_name1\t$unit_name2\t$unit_name3\t$parent_rank_name $parent_name\t$usage\n");
/*
    // graphviz output
    $name = trim(implode(" ", array($unit_name1, $unit_name2)));
    print("\"$parent_rank_name $parent_name\" -> \"$rank_name $name\";\n");
*/
  }
}
mysql_free_result($result);