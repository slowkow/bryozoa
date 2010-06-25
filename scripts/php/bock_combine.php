<?php

/*
 * Combine Phil Bock's Bryozoans and CURRENTSPECIES
 */

// ALTER TABLE bryozoans ADD currentnamestring VARCHAR(512);

// connect to localhost
$link = mysql_connect('localhost', 'kamil');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

// make bock the current db
$db_selected = mysql_select_db('bock', $link);
if (!$db_selected) {
    die ('Could not use database: ' . mysql_error());
}

// add the currentnamestring column
$result = mysql_query("ALTER TABLE `bryozoans` ADD `currentnamestring` VARCHAR(512)");

// select id, name, and currentname for each row, if currentname is set
$result = mysql_query(
  "SELECT `id`, `name`, `currentname`"
  . " FROM `bryozoans`"
  . " WHERE (`currentname` IS NOT NULL AND `currentname` < 99990)");

// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $currentnamestring = NULL;
  // if the record points to another one, grab the other name
  if ($row['id'] != $row['currentname']) {
    // find the name of this currentname id
    $query = sprintf("SELECT `name` FROM `bryozoans` WHERE `id`='%s'",
      mysql_real_escape_string($row['currentname']));
    // do the query and grab the result
    $row2 = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
    $currentnamestring = $row2['name'];
  }
  // don't do second query if the record points to itself
  else {
    $currentnamestring = $row['name'];
  }
  // we got a name
  if ($currentnamestring) {
    print($currentnamestring . "\n");
  }
}

mysql_free_result($result);

mysql_close($link);