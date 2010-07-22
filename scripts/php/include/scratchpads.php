<?php
/**
 * Common functions for querying the `scratchpads` table.
 */
/**
 * Keys present in the `scratchpads` table.
 */
$allowed_keys = array(
    'rank_name','unit_name1','unit_name2','unit_name3','parent_name','usage',
    'taxon_author','full_name','comments','accepted_name',
    'unacceptability_reason',
  );
/**
 * Generic get rows by any field.
 * 
 * @param params
 *   The field used for the query.
 * @param value
 *   The value put in the query.
 * @return
 *   An associative array of the resulting rows.
 */
function getRows($field, $value) {
  global $allowed_keys;
  if (!in_array($field, $allowed_keys)) {
    die("Invalid field '$field'!\n");
  }
  $query = sprintf("SELECT * FROM `scratchpads`"
    . "WHERE `%s` = '%s'",
    $field, mysql_escape_string($value));
  $result = mysql_query($query);
  $rows = array();
  while ($row = mysql_fetch_assoc($result)) {
    $rows[] = $row;
  }
  return $rows;
}
/**
 * Insert into the `scratchpads` table.
 * 
 * @param params
 *   An associative array with fields to set.
 */
function insertIntoScratchpads($params) {
  global $allowed_keys;
  // we can't insert a value unless params contains a value for key 'full_name'
  if (!array_key_exists('full_name', $params)) {
    die("Missing key 'full_name'!\n");
  }
  $full_name = mysql_real_escape_string($params['full_name']);
  
  foreach ($params as $key => $value) {
    if ($key == 'full_name') { continue; }
    // must be an allowed key as specified above
    if (in_array($key, $allowed_keys)) {
      $value     = mysql_real_escape_string($value);
      $query = sprintf("INSERT INTO `scratchpads`"
        . " SET `full_name`='%s', `%s`='%s'"
        . " ON DUPLICATE KEY UPDATE `%s`='%s'",
        $full_name, $key, $value, $key, $value);
      mysql_query($query);
      if (mysql_error()) { die(mysql_error() . "\n"); }
    }
  }
}
/**
 * Get plural form of a singular name.
 */
function plural($singular) {
  switch ($singular) {
    case 'Phylum': return 'Phyla';
    case 'Class': return $singular . 'es';
    case 'Order':
    case 'Suborder':
    case 'Infraorder': return $singular . 's';
    case 'Superfamily':
    case 'Family': return substr($singular, 0, -1) . 'ies';
    case 'Genus': return 'Genera';
    case 'Species':
    case 'Subspecies': return $singular;
  }
  return $singular;
}
/**
 * Custom sort for taxonomic ranks.
 */
function ranksort($a, $b) {
  static $ranks = array(
    'Phylum'      => 0,
    'Class'       => 1,
    'Order'       => 2,
    'Suborder'    => 3,
    'Infraorder'  => 4,
    'Superfamily' => 5,
    'Family'      => 6,
    'Genus'       => 7,
    'Species'     => 8,
    'Subspecies'  => 9,
  );
  return $ranks[$a] - $ranks[$b];
}
/**
 * Get a sorted list of the ranks of the children that have the parent_name.
 * 
 * @param parent_name
 *   The parent_name of the children.
 * @return
 *   List of ranks of all children of the specified taxon.
 */
function getChildrenRanks($parent_name) {
  $query = sprintf("SELECT `rank_name` FROM `scratchpads`"
    . " WHERE `parent_name` = '%s'"
    . " GROUP BY `rank_name`",
    mysql_real_escape_string($parent_name)
  );
  $result = mysql_query($query);
  $children_ranks = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $children_ranks[] = $row['rank_name'];
  }
  usort($children_ranks, "ranksort");
  return $children_ranks;
}
/**
 * Get an associative array of all children with the parent_name.
 * 
 * @param parent_name
 *   The parent_name of the children.
 * @return
 *   Associative array of all children with the parent_name.
 */
function getChildren($parent_name) {
  $query = sprintf("SELECT * FROM `scratchpads`"
    . " WHERE `parent_name` = '%s'",
    mysql_real_escape_string($parent_name)
  );
  $result = mysql_query($query);
  $children = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $children[] = $row;
  }
  return $children;
}
/**
 * Get taxon_author by full_name from the `scratchpads` table.
 * 
 * @param full_name
 *   The full name to use as a query.
 * @return
 *   The taxon_author of the returned entry.
 */
function getTaxonAuthor($full_name) {
  $query = sprintf("SELECT `taxon_author` FROM `scratchpads`"
    . " WHERE `full_name`='%s'",
    mysql_real_escape_string($full_name)
  );
  $result = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
  return $result['taxon_author'];
}