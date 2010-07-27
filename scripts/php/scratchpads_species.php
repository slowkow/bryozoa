<?php
/**
 * Insert Phil Bock's species from table `bryozoans` into `scratchpads`.
 */

require 'include/connect.php';
require 'include/scratchpads.php';

/*******************************************************************************
 * handle valid names
 */
$result = mysql_query(
  "SELECT `name`, `author`, `familyname`, `valid`, `details`, `comments`"
  . " FROM `bryozoans`"
  . " WHERE `name` IS NOT NULL"
  //. " AND `author` IS NOT NULL" // we can get the author later from gni
  . " AND `valid` = 1"
);
if (mysql_error()) { die(mysql_error() . "\n"); }

// stats
$count = array(
  'by_genus'   => 0,
  'by_family'  => 0,
  'uninserted' => 0,
);

while ($row = mysql_fetch_assoc($result)) {
  // atomize the name in the `bryozoans` table
  list($genus, $species, $subspecies) = explode(" ", $row['name'], 3);
  
  // fix the case and remove nasty characters and trim
  $bryozoans_family     = trim(ucfirst(strtolower(preg_replace("/[^a-zA-Z]/", "", $row['familyname']))));
  $bryozoans_genus      = trim(ucfirst(strtolower(preg_replace("/[^a-zA-Z]/", "", $genus))));
  $bryozoans_species    = trim(strtolower(preg_replace("/[^a-zA-Z]/", "", $species)));
  $bryozoans_subspecies = trim(strtolower(preg_replace("/[^a-zA-Z]/", "", $subspecies)));
  $bryozoans_author     = trim($row['author']);
  
  // no name left after our trimming and replacing
  if (!$bryozoans_genus) {
    continue;
  }
  
  // check if the parent genus is present
  if (getRows(array('full_name' => $bryozoans_genus, 'rank_name' => 'Genus'))) {
    $count['by_genus'] += 1;
    // insert the species or subspecies
    insertIntoScratchpads(
      array(
        'full_name'    => trim($bryozoans_genus . ' ' . $bryozoans_species . ' ' . $bryozoans_subspecies),
        'rank_name'    => $bryozoans_subspecies ? 'Subspecies' : 'Species',
        'unit_name1'   => $bryozoans_genus,
        'unit_name2'   => $bryozoans_species,
        'unit_name3'   => $bryozoans_subspecies,
        'parent_name'  => trim($bryozoans_genus . ' ' . getTaxonAuthor($bryozoans_genus)),
        'usage'        => 'valid',
        'taxon_author' => $bryozoans_author,
        'comments'     => $row['comments'],
        'details'      => $row['details'],
      )
    );
  }
  // the parent genus isn't present, so check if the parent family is present
  else if (getRows(array('full_name' => $bryozoans_family, 'rank_name' => 'Family'))){
    $count['by_family'] += 1;
    // insert the genus
    insertIntoScratchpads(
      array(
        'full_name'    => $bryozoans_genus,
        'rank_name'    => 'Genus',
        'unit_name1'   => $bryozoans_genus,
        'parent_name'  => trim($bryozoans_family . ' ' . getTaxonAuthor($bryozoans_family)),
        'usage'        => 'valid',
      )
    );
    // insert the species or subspecies
    insertIntoScratchpads(
      array(
        'full_name'    => trim($bryozoans_genus . ' ' . $bryozoans_species . ' ' . $bryozoans_subspecies),
        'rank_name'    => $bryozoans_subspecies ? 'Subspecies' : 'Species',
        'unit_name1'   => $bryozoans_genus,
        'unit_name2'   => $bryozoans_species,
        'unit_name3'   => $bryozoans_subspecies,
        'parent_name'  => trim($bryozoans_genus . ' ' . getTaxonAuthor($bryozoans_genus)),
        'usage'        => 'valid',
        'taxon_author' => $bryozoans_author,
        'comments'     => $row['comments'],
        'details'      => $row['details'],
      )
    );
  }
  else {
    $count['uninserted'] += 1;
  }
}

print('Inserted ' . $count['by_genus']  . " valid species by genus name.\n");
print('Inserted ' . $count['by_family'] . " valid species by family name.\n");
print($count['uninserted'] . " valid species not inserted because parent genus and parent family do not exist.\n");

/*******************************************************************************
 * handle invalid names
 */
$result = mysql_query(
  "SELECT `name`, `author`, `familyname`, `currentnamestring`, `details`, `comments`"
  . " FROM `bryozoans`"
  . " WHERE `name` IS NOT NULL"
  //. " AND `author` IS NOT NULL" // we can get author later from gni
  . " AND `currentnamestring` IS NOT NULL"
  . " AND `valid` = 0"
  // currentnamestring should point to a valid name in table `scratchpads`
  . " AND `currentnamestring` IN (SELECT `full_name` FROM `scratchpads` WHERE `usage` = 'valid')"
);
if (mysql_error()) { die(mysql_error() . "\n"); }

// stats
$count = array(
  'by_genus'   => 0,
  'uninserted' => 0,
);

while ($row = mysql_fetch_assoc($result)) {
  // atomize the name in the `bryozoans` table
  list($genus, $species, $subspecies) = explode(" ", $row['name'], 3);
  
  $bryozoans_family      = trim(ucfirst(strtolower(preg_replace("/[^a-zA-Z]/", "", $row['familyname']))));
  $bryozoans_genus       = trim(ucfirst(strtolower(preg_replace("/[^a-zA-Z]/", "", $genus))));
  $bryozoans_species     = trim(strtolower(preg_replace("/[^a-zA-Z]/", "", $species)));
  $bryozoans_subspecies  = trim(strtolower(preg_replace("/[^a-zA-Z]/", "", $subspecies)));
  $bryozoans_currentname = trim(ucfirst(strtolower($row['currentnamestring'])));
  $bryozoans_author      = trim($row['author']);
  
  $full_name = trim($bryozoans_genus . ' ' . $bryozoans_species . ' ' . $bryozoans_subspecies);
  
  // ensure that we have some data after trimming and replacing
  if (!$bryozoans_genus) {
    continue;
  }
  
  // check if the entry's parent is actually present
  if (getRows(array('full_name' => $bryozoans_genus, 'rank_name' => 'Genus'))) {
    $count['by_genus'] += 1;
    // insert species or subspecies
    insertIntoScratchpads(
      array(
        'full_name'     => trim($bryozoans_genus . ' ' . $bryozoans_species . ' ' . $bryozoans_subspecies),
        'rank_name'     => $bryozoans_subspecies ? 'Subspecies' : 'Species',
        'unit_name1'    => $bryozoans_genus,
        'unit_name2'    => $bryozoans_species,
        'unit_name3'    => $bryozoans_subspecies,
        'parent_name'   => trim($bryozoans_genus . ' ' . getTaxonAuthor($bryozoans_genus)),
        'usage'         => 'invalid',
        'accepted_name' => $bryozoans_currentname,
        'taxon_author'  => $bryozoans_author,
        'comments'      => $row['comments'],
        'details'       => $row['details'],
        'unacceptability_reason' => parseUnacceptabilityReason($row['details']),
      )
    );
  }
  else {
    $count['uninserted'] += 1;
  }
}

print('Inserted ' . $count['by_genus']  . " invalid species by genus name.\n");
print($count['uninserted'] . " invalid species not inserted because parent genus doesn't exist.\n");
