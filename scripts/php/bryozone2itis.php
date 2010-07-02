<?php
/*
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
 * input a taxonid
 * output the contents of the row with the taxonid
 */
function getRow($taxonid) {
  $query = sprintf("SELECT * FROM `bryozone_taxa` WHERE `taxonid`='%s'",
    mysql_real_escape_string($taxonid)
  );
  return mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
}

/**
 * input the rank code
 * output the rank name
 */
function getRankName($rankcode) {
  $query = sprintf("SELECT `rankname` FROM `bryozone_rank` WHERE `rankid`='%s'",
    mysql_real_escape_string($rankcode)
  );
  $row = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
  return $row['rankname'];
}

/**
 * input the rank code
 * output true/false if we should print it
 */
function isValidRankCode($rank_code) {
  return 3 < $rank_code && $rank_code < 110 && $rank_code != 60
    && $rank_code != 36 && $rank_code != 85 && $rank_code != 95
    && $rank_code != 96 && $rank_code != 97 && $rank_code != 98;
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
print("rank_name\tunit_name1\tunit_name2\tparent_name\tusage\n");

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
    
    if ($rank_code == 110) {
      continue;
      //print("$parent_name\t$unit_name1\t$rank_name\t$parent_name\t$usage\n");
    }
    
    // find a parent that is named
    while ($parent_name == 'Uncertain') {
      $parent_row  = getRow($parent_row['parentid']);
      $parent_name = $parent_row['taxonname'];
      $parent_rank_code = $parent_row['rankcode'];
      $parent_rank_name = getRankName($parent_rank_code);
    }
    
    if (isValidRankCode($rank_code) && isValidRankCode($parent_rank_code)) {
      print("$rank_name\t$rank_name $unit_name1\t$unit_name2\t$parent_rank_name $parent_name\t$usage\n");
/*
      // graphviz output
      $name = implode(" ", array($unit_name1, $unit_name2));
      print("\"$parent_rank_name $parent_name\" -> \"$rank_name $name\";\n");
*/
    }
  }
}
mysql_free_result($result);