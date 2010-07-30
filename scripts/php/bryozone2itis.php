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

require 'include/connect.php';

$ranknames = array(
  0 => 'Invalid',
  1 => 'Nomen Oblitum',
  2 => 'Nomen Nudum',
  3 => 'Uncertain Classification',
  10 => 'Phylum',
  20 => 'Class',
  30 => 'Order',
  36 => 'Subjective Junior Synonym',
  40 => 'Suborder',
  50 => 'Infraorder',
  60 => 'Grade',
  70 => 'Superfamily',
  80 => 'Family',
  85 => 'Family Synonym',
  90 => 'Genus',
  95 => 'Genus Synonym',
  96 => 'Subjective Junior Synonym',
  97 => 'Objective Junior Synonym',
  98 => 'Homonym',
  100 => 'Subgenus',
  110 => 'Species',
  113 => 'Uncertain Species',
  115 => 'Species Synonym',
  116 => 'Subjective Junior Synonym',
  117 => 'Objective Junior Synonym',
  118 => 'Homonym',
  99999 => 'Error',
);

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
 * Query the bryozone_taxa table with a taxonid and return the associated row.
 * 
 * @param taxonid
 *   An id number of a taxon.
 * @return
 *   The MySQL row that matches the taxon id number.
 * 
 * @see nextRealParent()
 */
function getRow($taxonid) {
  $query = sprintf("SELECT * FROM `bryozone_taxa` WHERE `taxonid`='%s'",
    mysql_real_escape_string($taxonid)
  );
  return mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
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
 * @return
 *   Return the next parent that is not called 'Uncertain'.
 * 
 * @see getRow()
 */
function nextRealParent($row) {
  while ($row['parentid']) {
    $row = getRow($row['parentid']);
    if ($row['taxonname'] != 'Uncertain' && isValidRankCode($row['rankcode'])) {
      return $row;
    }
  }
  return NULL;
}

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
while ($row = mysql_fetch_assoc($result)) {
  // we don't care for invalid records at the moment
  if (!isValidRankCode($row['rankcode'])) {
    continue;
  }
  
  // get parent's name by looking at row's parentid
  $parent_row       = nextRealParent($row);
  $parent_name      = $parent_row['taxonname'];
  $parent_rank_code = $parent_row['rankcode'];
  $parent_rank_name = $ranknames[$parent_rank_code];
  
  // get row's rank name by looking up row's rankcode
  $rank_code = $row['rankcode'];
  $rank_name = $ranknames[$rank_code];
  
  // we have a species or subspecies without a genus name, not useful
  if ($rank_code >= 110 && $parent_rank_name != 'Genus') {
    continue;
  }
  
  // it's a species or subspecies, so the genus name is unit_name1
  if ($rank_code >= 110) {
    $unit_name1 = $parent_name;
    list($unit_name2, $unit_name3) =
      explode(" ", $row['taxonname'], 2);
  }
  // it's a genus or higher taxon, so the name is more simple
  else {
    list($unit_name1, $unit_name2, $unit_name3) =
      explode(" ", $row['taxonname'], 3);
  }
  // quit if it's an uncertain row
  if ($unit_name1 == 'Uncertain') {
    continue;
  }
  
  // get validity by comparing the seniorid and the taxonid
  $usage = $row['seniorid'] == $row['taxonid'] ? 'valid' : 'invalid';
  
  // we have what we need
  if ($rank_name && $unit_name1 && $parent_name && $usage) {
    //print("$rank_name\t$rank_name $unit_name1\t$unit_name2\t$unit_name3\t$parent_rank_name $parent_name\t$usage\n");
    print("$rank_name\t$unit_name1\t$unit_name2\t$unit_name3\t$parent_name\t$usage\n");
  }
}
mysql_free_result($result);