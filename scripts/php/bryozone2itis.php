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

// connect to localhost
$link = mysql_connect('localhost', 'kamil');
if (!$link) { die('Could not connect: ' . mysql_error()); }
// make bock the current db
$db_selected = mysql_select_db('bock', $link);
if (!$db_selected) { die ('Could not use database: ' . mysql_error()); }

// select id, name, and currentname for each row, if currentname is set
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
print("unit_name1\tunit_name2\trank_name\tparent_name\tusage\n");

// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  // find the parent's name
  $query = sprintf("SELECT `taxonname` FROM `bryozone_taxa` WHERE `taxonid`='%s'",
    mysql_real_escape_string($row['parentid'])
  );
  $row2 = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
  $parent_name = $row2['taxonname'];
  
  $query = sprintf("SELECT `rankname` FROM `bryozone_rank` WHERE `rankid`='%s'",
    mysql_real_escape_string($row['rankcode'])
  );
  $row3 = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
  $rank_name = $row3['rankname'];
  
  $unit_name1 = $row['taxonname'];
  $usage = 'invalid';
  
  if ($row['seniorid'] && $row['seniorid'] == $row['taxonid']) {
    $usage = 'valid';
  }
  
  $rank_code = $row['rankcode'];
  
  // we got a name
  if ($unit_name1 && $rank_name && $parent_name && $usage) {
    if ($rank_code == 110) {
      //print("$parent_name\t$unit_name1\t$rank_name\t$parent_name\t$usage\n");
    }
    else if (3 < $rank_code && $rank_code < 110 && $rank_code != 60
    && $rank_code != 36 && $rank_code != 85 && $rank_code != 95
    && $rank_code != 96 && $rank_code != 97 && $rank_code != 98) {
     print("$unit_name1\t\t$rank_name\t$parent_name\t$usage\n");
    }
  }
}
mysql_free_result($result);