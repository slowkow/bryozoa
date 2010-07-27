<?php
/*
 * Found authors for `scratchpads` entries that were missing authors.
 * This fills them in using data from GNI.
 * 79 left that don't get filled in.
 */

require 'include/connect.php';
require 'include/scratchpads.php';

/**
 * Get GNI author.
 * 
 * @param name
 *   The name to query `gni_scratchpads`.
 * @return
 *   The author with longest strlen.
 */
function getAuthor($name) {
  $query = sprintf("SELECT `author` FROM `gni_scratchpads`"
    . " WHERE `name` = '%s'",
    mysql_real_escape_string($name)
  );
  $result = mysql_query($query);
  $author = '';
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    if (strlen($row['author']) > strlen($author)) {
      $author = $row['author'];
    }
  }
  return $author;
}

// look at the entries in `scratchpads` without authors
$result = mysql_query(
  "SELECT `full_name`, `unit_name1`"
  . " FROM `scratchpads`"
  . " WHERE"
  . " `taxon_author` IS NULL"
  . " OR `taxon_author` NOT REGEXP '[0-9]'"
);

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  // update the taxon with the new author
  $taxon_author = getAuthor($row['unit_name1']);
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