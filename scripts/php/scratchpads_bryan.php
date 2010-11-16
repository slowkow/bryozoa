<?php
/**
 * Insert Bryan Quach's higher taxonomy into table `scratchpads`.
 * 
 * ORDER OF EXECUTION
 * scratchpads_bryan.php
 * scratchpads_species.php
 * getgniauthors.php (only if you don't have GNI names already)
 * scratchpads_dummies.php
 * scratchpads_gniauthors.php
 */

require 'include/connect.php';
require 'include/scratchpads.php';
/**
 * Get the author and year for the taxon name from table `bryozone_easyauthors`.
 * 
 * @param name
 *   The name to use as a query.
 * @return
 *   A properly formatted string containing author name and year.
 */
function getBryozoneAuthorYear($name) {
  $query = sprintf(
    "SELECT `authorname`, `year` FROM `bryozone_easyauthors`"
    . " WHERE `taxonname`='%s'",
    mysql_real_escape_string($name)
  );
  $result = mysql_query($query);
  $row = mysql_fetch_assoc($result);
  return trim($row['authorname'] . ' ' . $row['year']);
}
/**
 * Query table `bryan_valid` with a rank code and newid and return the
 * associated row.
 * 
 * @param rankcode
 *   A rank code of a taxon.
 * @param newid
 *   An id number of a taxon.
 * @return
 *   An associative array containing the row.
 */
function getRow($rankcode, $newid) {
  $query = sprintf("SELECT * FROM `bryan_valid`"
    . " WHERE `rankcode`='%s' AND `newid`='%s'",
    mysql_real_escape_string($rankcode),
    mysql_real_escape_string($newid)
  );
  return mysql_fetch_assoc(mysql_query($query));
}

/**
 * Return the next parent that is a valid rank and is not called 'NULL' or
 * 'uncertain'.
 * 
 * @param row
 *   An associative array containing a row from table `bryan_valid`.
 * @return
 *   An associative array containing the parent row.
 * 
 * @see getRow()
 * @see nextValidRank()
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
  global $ranknames;
  global $validranks;
  if ($rank_code <= 10) { return 10; }
  do {
    $rank_code--;
  } while (!in_array($ranknames[$rank_code], $validranks));
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

// stats
$count = array();

$count['Phylum'] = 1;

// insert Bryozoa
insertIntoScratchpads(
  array(
    'full_name'  => 'Bryozoa',
    'rank_name'  => 'Phylum',
    'unit_name1' => 'Bryozoa',
    'usage'      => 'valid',
  )
);

// select rows from `bryan_valid` to put in `scratchpads`
// "IS NOT NULL" will return true for rows with name='NULL'
$result = mysql_query(
  "SELECT `name`, `rankcode`, `newid`, `newrefid`"
  . " FROM `bryan_valid`"
  . " WHERE (`name` IS NOT NULL"
  . " AND `name` NOT REGEXP 'null|uncertain'" // no bad names
  . " AND `rankcode` IS NOT NULL"
  . " AND `newid` IS NOT NULL"      // if null, then others can't link to it
  //. " AND `newrefid` IS NOT NULL" // if null, we'll link them to Bryozoa
  . " AND `rankcode` < 99990)"
);

// loop through results
while ($row = mysql_fetch_assoc($result)) {
  // get row's unit names directly
  list($unit_name1, $unit_name2, $unit_name3) =
    explode(" ", $row['name'], 3);
  // format the name properly
  $unit_name1 = trim(ucfirst(strtolower($unit_name1)));
  $unit_name2 = trim(strtolower($unit_name2));
  $unit_name3 = trim(strtolower($unit_name3));
  $rank_name  = $ranknames[$row['rankcode']];
  
  // we still have a name after trimming and rank is valid
  if ($unit_name1 && in_array($rank_name, $validranks)) {
    // find a parent that is named
    // if we don't find anyone, then Bryozoa is the parent
    $parent_row = nextRealParent($row);
    $parent_name = trim(ucfirst(strtolower($parent_row['name'])));
    
    $count[$ranknames[$row['rankcode']]] += 1;
    
    insertIntoScratchpads(
      array(
        'rank_name'    => $rank_name,
        'unit_name1'   => $unit_name1,
        'unit_name2'   => $unit_name2,
        'unit_name3'   => $unit_name3,
        'parent_name'  => $parent_name,
        'usage'        => 'valid',
        'full_name'    => trim($unit_name1 . ' ' . $unit_name2 . ' ' . $unit_name3),
        'taxon_author' => getBryozoneAuthorYear($unit_name1),
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
while ($row = mysql_fetch_assoc($result)) {
  insertIntoScratchpads(
    array(
      'full_name' => $row['full_name'],
      'parent_name' => trim($row['parent_name'] . ' ' . getTaxonAuthor($row['parent_name'])),
    )
  );
}

// print some stats
foreach ($count as $rank => $num) {
  print('Inserted ' . $num . ' valid ' . plural($rank) . ".\n");
}