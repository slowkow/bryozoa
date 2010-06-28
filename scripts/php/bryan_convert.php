<?php

/**
 * Kamil Slowikowski
 * Summer 2010
 * 
 * This script reads Bryan Quach's valid.txt and spits out a full hierarchy.
 * 
 */

/*
 * The rank codes and names used in several different files.
 */
$ranks = array(
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

/*
$filename = $_FILES['file1']['name'];
$file1 = fopen($_FILES['file1']['tmp_name'], "r") or die("Cannot open $filename");
*/

/**
 * Read a Bryan-type file into an array. Set $header to TRUE if the file
 * has a header line with the fieldnames.
 * 
 * @param filename
 *   The full path to the file
 * @param taxonarray
 *   The array into which the file will be read
 * @param header
 *   Set to true if the file has a header line with fieldnames
 */
function readBryanFile($filename, $taxonarray, $header=TRUE) {
  $file1 = fopen($filename, 'r') or die("Cannot open $filename");
  
  // count number of bad rows
  $badrows = 0;
  // count number of rows
  $count = 0;
  while (!feof($file1)) {
    // read one line and trim extra whitespace
    $row = trim(fgets($file1));
    
    // skip comments and empty lines
    if ($row[0] == '#' || strlen($row) == 0) {
      continue;
    }
    
    // count each non-comment line
    $count++;
    
    // skip header
    if ($count == 1 && $header) {
      continue;
    }
    
    // TODO
    // experiment with one more field $extra to capture the last column in the
    // species rows
    list($oldid, $oldpid, $name, $rank, $date, $id, $pid) = explode("\t", $row);
    
    if (!$name || !$rank || !$id || !$pid) {
      $badrows++;
      continue;
    }
    
    $taxonarray[$rank][$id] = array(
      'name' => $name,
      'rank' => $rank,
      'id'   => $id,
      'pid'  => $pid,
    );
  }
  print("$badrows rows missing name or rank or id or pid.\n");
  fclose($file1);
}

/**
 * Return the next highest rank code.
 * 
 * @param rank
 *   The child rank code
 * @param ranks
 *   Sorted array of rank code => rank name
 * @return
 *   The next highest rank code
 */
function getNextRank($rank, $ranks) {
  $keys = array_keys($ranks);
  for ($i = 1; $i < count($keys); $i++) {
    if ($rank == $keys[$i]) {
      return $keys[$i-1];
    }
  }
  print("failed on rank $rank\n");
  return NULL;
}

/**
 * Return the array corresponding to the row that matches the name.
 * 
 * @param name
 *   The name of the taxon
 * @return
 *   The array with the contents of the corresponding row
 */
function getRowByName($name, $ranks, $taxonarray) {
  $count = 0;
  $row   = NULL;
  // loop through ranks, key is rank code, value is rank name
  foreach ($ranks as $rank => $rankname) {
    // skip ranks that have no members
    if (!isset($taxonarray[$rank])) {
      continue;
    }
    // loop through each member in the rank
    foreach ($taxonarray[$rank] as $id => $taxon) {
      if ($taxon['name'] == $name) {
        $count++;
        $row = $taxon;
      }
    }
  }
  if ($count != 1) {
    print("$count row(s) with name '$name'\n");
    return NULL;
  }
  return $row;
}

function getParentByName($cname, $ranks, $taxonarray) {
  if ($cname == NULL) {
    print("cname is '$cname'\n");
    return NULL;
  }
  $ptaxon = NULL;
  $ctaxon = getRowByName($cname, $ranks, $taxonarray);
  if ($ctaxon == NULL) {
    print("cname is '$cname', ctaxon is '$ctaxon'\n");
    return NULL;
  }
  $prank  = $ctaxon['rank'];
  $pid    = $ctaxon['pid'];
  $count  = 0;
  while (($prank = getNextRank($prank, $ranks)) != NULL) {
    if (isset($taxonarray[$prank][$pid])) {
      $count++;
      $ptaxon = $taxonarray[$prank][$pid];
    }
  }
  if ($count != 1) {
    print("$count row(s) with rank $prank and id $pid\n");
    return NULL;
  }
  return $ptaxon;
}

function getParentById($cid, $crank, $ranks, $taxonarray) {
  if ($cid == NULL) {
    return NULL;
  }
  $prank = getNextRank($crank, $ranks);
  if ($prank == NULL) {
    return NULL;
  }
  $pid = $taxonarray[$crank][$cid]['pid'];
  while ($prank != NULL && !$taxonarray[$prank][$pid]) {
    $prank = getNextRank($prank, $ranks);
  }
  return $prank ? $taxonarray[$prank][$pid] : NULL;
}

function getParentAndParentRankById($cid, $crank, $ranks, $taxonarray) {
  if ($cid == NULL) {
    return NULL;
  }
  $prank = getNextRank($crank, $ranks);
  if ($prank == NULL) {
    return NULL;
  }
  $pid = $taxonarray[$crank][$cid]['pid'];
  while ($prank != NULL && !$taxonarray[$prank][$pid]) {
    $prank = getNextRank($prank, $ranks);
  }
  return $prank ? array($taxonarray[$prank][$pid], $prank) : NULL;
}

/*
 * Read the contents of the 'valid' sheet from Bryan Quach's file into
 * the array $bryan_valid.
 */
$bryan_valid = array();
readBryanFile('../../bryan/sheets/valid.tab', &$bryan_valid);

/*
 * Print the number of entries in each rank.
 */

/*
foreach ($ranks as $rank => $rankname) {
  $count = count($bryan_valid[$rank]);
  if ($count) {
    print("$count $rankname\n");
  }
}
*/

/*
 * The code below will print an ITIS style output.
 * 
 * php bryan2full.php > output/bryan_itis.tab
 */
$header = array('unit_name1', 'rank_name', 'parent_name', 'usage');
print(implode("\t", $header) . "\n");

foreach ($ranks as $rank => $rankname) {
  if (count($bryan_valid[$rank])) {
    foreach ($bryan_valid[$rank] as $id => $taxon) {
      if ($taxon['name'] == 'NULL') {
        continue;
      }
      $unit_name1 = $taxon['name'];
      $rank_name = $ranks[$taxon['rank']];
      $parent_name = NULL;
      $usage = 'valid';
      while (($ptaxon = getParentById($taxon['id'], $taxon['rank'], $ranks, $bryan_valid)) != NULL) {
        if ($ptaxon['name'] != 'NULL') {
          $parent_name = $ptaxon['name'];
          break;
        }
        $taxon = $ptaxon;
      }
      print("$unit_name1\t$rank_name\t$parent_name\t$usage\n");
    }
  }
}


exit();

/* 
 * The code below will print a full hierarchy style output.
 */

/* Grab the names of the ranks that have more than 0 members, this will be the 
 * header for the full hierarchy output.
 */
$header = array('Rank ID');
foreach ($ranks as $rank => $rankname) {
  $count = count($bryan_valid[$rank]);
  if ($count) {
    $header[] = $rankname;
  }
}
print(implode("\t", $header) . "\n");

/*
 * For each entry, print all of its parents. This outputs the full hierarchy.
 */
foreach ($ranks as $rank => $rankname) {
  if (count($bryan_valid[$rank])) {
    foreach ($bryan_valid[$rank] as $id => $taxon) {
      $rankid = $taxon['rank'] . ' ' . $taxon['id'];
      $row = $taxon['name'];
      //$row = $ranks[$taxon['rank']];
      while (($ptaxon = getParentById($taxon['id'], $taxon['rank'], $ranks, $bryan_valid)) != NULL) {
        $row = $ptaxon['name'] . "\t" . $row;
        //$row = $ranks[$ptaxon['rank']] . "\t" . $fullname;
        $taxon = $ptaxon;
      }
      print("$rankid\t$row\n");
    }
  }
}

