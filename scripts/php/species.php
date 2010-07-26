<?php
// Require eZComponents library
/*
require 'ezc/Base/ezc_bootstrap.php';
$store = new ezcTreeXmlInternalDataStore();
exit();
*/
require 'include/connect.php';
require 'include/scratchpads.php';

/**
 * Get a name and rankcode from the `bryan_valid` table.
 * 
 * @param name
 *   The name to use as a query.
 */
function getBryanNameRankCode($name) {
  $query = sprintf(
    "SELECT `name`, `rankcode` FROM `bryan_valid`"
    . " WHERE `name`='%s'",
    mysql_real_escape_string($name)
  );
  return mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
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

// enable printing or not
$g_print_stats = FALSE;
// execute sql queries or not
$g_mysql = TRUE;


/*******************************************************************************
 * handle valid names
 */
$result = mysql_query(
  "SELECT `name`, `author`, `familyname`, `valid`, `comments`"
  . " FROM `bryozoans`"
  . " WHERE `name` IS NOT NULL"
  . " AND `author` IS NOT NULL"
  . " AND `valid` = 1"
);
if (mysql_error()) { die(mysql_error() . "\n"); }

// stats
$bryan_ranks     = array();
$bryan_unmatched = array();
$bryan_count     = 0;

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
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
  
  // check if Bryan has this genus in his taxonomy
  $match = getBryanNameRankCode($bryozoans_genus);
  // bryozoans genus made a successful hit in bryan's taxonomy
  //   we're linking a Genus name from bryozoans to bryan, so:
  //     matches with rank Species and lower ranks not allowed
  //     matches with invalid rank not allowed
  if ($match['name'] && $match['rankcode'] < 110 && isValidRankCode($match['rankcode'])) {
    $bryan_count++;
    $bryan_ranks[$match['rankcode']] += 1;
    if ($g_mysql) {
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
          'comments'     => $row['comments']
        )
      );
    }
  }
  // bryozoans genus failed to match in Bryan's table
  // so, we can try the bryozoans family if it exists and is valid
  else if (!$bryan_genus && $bryozoans_family
    && !preg_match('/(unassigned|unplaced)/i', $bryozoans_family)) {
    // let's see if bryan has this family somewhere
    $match = getBryanNameRankCode($bryozoans_family);
    # we're linking a Family name from bryozoans to bryan, so:
    # matches with rank Genus and lower ranks not allowed
    # matches with invalid rank not allowed
    if ($match['name'] && $match['rankcode'] < 90 && isValidRankCode($match['rankcode'])) {
      if ($g_mysql) {
        // TODO
        //   This will be a problem, because parent_name should also
        //   have the author & year for the parent, which will be done later
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
          )
        );
      }
    }
    // bryozoans_family failed to match in Bryan's table
    else {
      $bryan_unmatched[$bryozoans_genus] += 1;
    }
  }
}


/*******************************************************************************
 * handle invalid names
 */
$result = mysql_query(
  "SELECT `name`, `author`, `familyname`, `currentnamestring`, `details`, `comments`"
  . " FROM `bryozoans`"
  . " WHERE `name` IS NOT NULL"
  . " AND `author` IS NOT NULL"
  . " AND `currentnamestring` IS NOT NULL"
  . " AND `valid` = 0"
  // currentnamestring should point to a valid name
  . " AND `currentnamestring` IN (SELECT `name` FROM `bryozoans` WHERE `valid` = 1)"
);
if (mysql_error()) { die(mysql_error() . "\n"); }

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
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
  $query = sprintf(
    "SELECT `full_name` FROM `scratchpads`"
    . " WHERE `full_name`='%s' AND `rank_name`='Genus'",
    mysql_real_escape_string($bryozoans_genus)
  );
  // the parent must exist and it must be a Genus
  if (!($match = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC))) {
    continue;
  }
  
  if ($g_mysql) {
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
}


/**
 * Query the `bryan_rank` table with a rank code and return the rank name.
 * 
 * @param rankcode
 *   A rank code number.
 * @return
 *   The name associated with the rank code number.
 */
function getRankName($rankcode) {
  $query = sprintf("SELECT `rankname` FROM `bryan_rank` WHERE `rankid`='%s'",
    mysql_real_escape_string($rankcode)
  );
  $row = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
  return $row['rankname'];
}
/**
 * Print a summary of the matches from bryozoans to bryozone_taxa and bryan_valid
 */
if ($g_print_stats) {
  print("$bryozone_count genus names from `bryozoans` matched a record in `bryozone_taxa`\n");
  foreach ($bryozone_ranks as $rankcode => $count) {
    print("$count matches in `" . getRankName($rankcode) . "` in `bryozone_taxa`\n");
  }

  print("\n");

  print("$bryan_count genus names from `bryozoans` matched a record in `bryan_valid`\n");
  foreach ($bryan_ranks as $rankcode => $count) {
    print("$count matches in `" . getRankName($rankcode) . "` in `bryan_valid`\n");
  }

  $bryan_unmatched_total = 0;
  foreach ($bryan_unmatched as $key => $value) {
    //print("$key, $value\n");
    $bryan_unmatched_total += $value;
  }
  print("$bryan_unmatched_total records from `bryozoans` without match in `bryan_valid` and without `familyname`\n");
}