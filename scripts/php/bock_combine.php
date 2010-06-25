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

/**
 * Step 1: Replace reference to name with actual name, delete ID column
 *   Bryozoans
 */
// add the currentnamestring column
/*
mysql_query("ALTER TABLE `bryozoans`"
  . " ADD COLUMN `currentnamestring` VARCHAR(512)"
);
*/

// select id, name, and currentname for each row, if currentname is set
/*
$result = mysql_query(
  "SELECT `id`, `name`, `currentname`"
  . " FROM `bryozoans`"
  . " WHERE (`currentname` IS NOT NULL AND `currentname` < 99990)");
*/

// loop through results
/*
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $currentnamestring = NULL;
  // if the record points to another one, grab the other name
  if ($row['id'] != $row['currentname']) {
    // find the name of this currentname
    $query = sprintf("SELECT `name` FROM `bryozoans` WHERE `id`='%s'",
      mysql_real_escape_string($row['currentname'])
    );
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
    $querystring = "UPDATE `bryozoans`"
      . " SET `currentnamestring`='%s'"
      . " WHERE `name`='%s'";
    $query = sprintf($querystring,
      mysql_real_escape_string($currentnamestring),
      mysql_real_escape_string($row['name'])
    );
    mysql_query($query);
  }
}
mysql_free_result($result);
*/

// drop unused and unshared columns
/*
mysql_query("ALTER TABLE `bryozoans`"
  . " DROP COLUMN `id`,"
  . " DROP COLUMN `currentname`,"
  . " DROP COLUMN `age`,"
  . " DROP COLUMN `original`,"
  . " DROP COLUMN `newcode`,"
  . " DROP COLUMN `othername`,"
  . " DROP COLUMN `delete`"
);
*/

/**
 * Step 1: Replace reference to name with actual name, delete ID column
 *   CURRENTSPECIES
 */
// add the currentnamestring column
mysql_query("ALTER TABLE `currentspecies`"
  . " ADD COLUMN `currentnamestring` VARCHAR(512),"
  . " ADD COLUMN `Valid` INT"
);

// select synonym records with the id number of the valid name after the =
$result = mysql_query(
  "SELECT `speciesid`, `name`, `first_name`"
  . " FROM `currentspecies`"
  . " WHERE `name` REGEXP '^.*was.+=([^0-9]*[0-9]+)$'");

// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $validname = NULL;
  preg_match('/^.*was.+=[^0-9]*([0-9]+)$/', $row['name'], $matches);
  $validid = $matches[1];
  // if the record points to another one, grab the other name
  if ($validid && $validid != $row['speciesid']) {
    // find this valid name
    $query = sprintf("SELECT `name` FROM `currentspecies` WHERE `speciesid`='%s'",
      mysql_real_escape_string($validid)
    );
    // do the query and grab the result
    $row2 = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
    $validname = $row2['name'];
  }
  else {
    continue;
  }
  // we got a name
  if ($validname) {
    $querystring = "UPDATE `currentspecies`"
      . " SET `currentnamestring`='%s'"
      . " WHERE `name`='%s'";
    $query = sprintf($querystring,
      mysql_real_escape_string($validname),
      mysql_real_escape_string($row['name'])
    );
    mysql_query($query);
  }
}
mysql_free_result($result);

// select synonym records with the valid name after the =
// SELECT speciesid, name, first_name FROM currentspecies WHERE name REGEXP '^.*was.+=[^0-9]*$';

mysql_close($link);