<?php
/**
 * Insert dummy "TAXONNAME Unplaced RANKNAME" entries into table `scratchpads`
 * wherever appropriate. Relink entries to new parents.
 * 
 * For example, Bryozoa has some Families in it. That's not cool. The solution
 * is to make an additional Class called "Bryozoa Unplaced Fam." and then put
 * the Families in there.
 */
require 'include/connect.php';
require 'include/scratchpads.php';

$result = mysql_query(
  "SELECT `full_name`, `taxon_author`"
  . " FROM `scratchpads`"
  . " WHERE `rank_name` NOT LIKE 'subspecies'"
  . " AND `rank_name` NOT LIKE 'species'"
  . " AND `rank_name` NOT LIKE 'genus'"
);

// loop through results
while ($row = mysql_fetch_assoc($result)) {
  $children_parent_name = trim($row['full_name'] . ' ' . $row['taxon_author']);
  $children_ranks = getChildrenRanks($children_parent_name);
  $children       = getChildren($children_parent_name);
  
  // handle the case when children are not all of the same rank
  if (count($children_ranks) <= 1) {
    continue;
  }
  
  // the dummies will be of the same rank as the child with the highest rank
  $dummy_rank_name   = $children_ranks[0];
  $dummy_parent_name = trim($row['full_name'] . ' ' . $row['taxon_author']);
  
/*
  print($row['full_name'] . "\n");
  var_dump($children_ranks);
*/
  // the children in the highest rank don't need a dummy
  // the children in the next highest ranks need dummies
  foreach (array_slice($children_ranks, 1) as $rank) {
    $dummy_full_name = 'x_' . $row['full_name'] . '_' . abbreviation($rank);
    // insert dummy
    insertIntoScratchpads(
      array(
        'full_name'     => $dummy_full_name,
        'rank_name'     => $dummy_rank_name,
        'usage'         => 'invalid',
        'unit_name1'    => $dummy_full_name,
        'parent_name'   => $dummy_parent_name,
        'accepted_name' => $row['full_name'],
        'comments'      => 'dummy taxon for unplaced ' . plural($rank),
      )
    );
    // update children to point to this dummy as their new parent
    foreach ($children as $child) {
      if ($child['rank_name'] == $rank) {
        // set dummy as the new parent
        insertIntoScratchpads(
          array(
            'full_name'   => $child['full_name'],
            'parent_name' => $dummy_full_name,
          )
        );
      }
    }
  }
}
