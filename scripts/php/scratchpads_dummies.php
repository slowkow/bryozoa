<?php
/*
 * Modify table `scratchpads`. Insert dummy "TAXONNAME Unplaced RANKNAME"
 * entries wherever appropriate. Relink entries to new parents.
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
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $children_parent_name = trim($row['full_name'] . ' ' . $row['taxon_author']);
  $children_ranks = getChildrenRanks($children_parent_name);
  $children       = getChildren($children_parent_name);
  // handle case when parent has children of various ranks
  if (count($children_ranks) <= 1) {
    continue;
  }
  
  $dummy_rank_name   = $children_ranks[0];
  $dummy_parent_name = trim($row['full_name'] . ' ' . $row['taxon_author']);
  
/*
  print($row['full_name'] . "\n");
  var_dump($children_ranks);
*/

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
        'accepted_name' => $dummy_parent_name,
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
