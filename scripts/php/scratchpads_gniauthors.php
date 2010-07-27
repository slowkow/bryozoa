<?php
/**
 * Set authors for entries in table `scratchpads` that were missing authors
 * using data from `gni_scratchpads`.
 */

require 'include/connect.php';
require 'include/scratchpads.php';

/**
 * Get author from table `gni_scratchpads`.
 * 
 * @param name
 *   The name to query `gni_scratchpads`.
 * @return
 *   The author with longest strlen.
 */
function getGNIAuthor($name) {
  $query = sprintf("SELECT `author` FROM `gni_scratchpads`"
    . " WHERE `name` = '%s'",
    mysql_real_escape_string($name)
  );
  $result = mysql_query($query);
  $author = '';
  while ($row = mysql_fetch_assoc($result)) {
    if (strlen($row['author']) > strlen($author)) {
      $author = $row['author'];
    }
  }
  return $author;
}

// find entries in `scratchpads` without authors
$result = mysql_query(
  "SELECT `full_name`"
  . " FROM `scratchpads`"
  . " WHERE"
  . " `taxon_author` IS NULL"
  . " OR `taxon_author` NOT REGEXP '[0-9]'"
);

while ($row = mysql_fetch_assoc($result)) {
  // get the gni author
  $taxon_author = getGNIAuthor($row['full_name']);
  if (!$taxon_author) {
    continue;
  }
  // update the taxon with the new author
  insertIntoScratchpads(
    array(
      'full_name'    => $row['full_name'],
      'taxon_author' => $taxon_author,
    )
  );
  // update all of the taxon's children with the new author
  $children = getChildren($row['full_name']);
  foreach ($children as $child) {
    insertIntoScratchpads(
      array(
        'full_name'   => $child['full_name'],
        'parent_name' => trim($child['parent_name'] . ' ' . $taxon_author),
      )
    );
  }
}