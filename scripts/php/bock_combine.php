<?php

/*
 * Combine Phil Bock's Bryozoans and CURRENTSPECIES
 */

/**
 * Delete a row that contains a match.
 * 
 * @param table
 *   The name of the table from which to delete the row.
 * @param deletetable
 *   The name of the table where the deleted row will be inserted.
 * @param column
 *   The name of the column whose value will be checked.
 * @param value
 *   The value to match against the column's value.
 */
function deleteRow($table, $deletetable, $column, $value) {
  print("Deleting $value from $table\n");
  
  $querystring = "INSERT INTO `%s`"
    . " SELECT * FROM `%s`"
    . " WHERE `%s`='%s'";
  $query = sprintf($querystring
    , mysql_real_escape_string($deletetable)
    , mysql_real_escape_string($table)
    , mysql_real_escape_string($column)
    , mysql_real_escape_string($row[$column])
  );
  mysql_query($query);
  
  $querystring = "DELETE FROM `%s`"
    . " WHERE `%s`='%s'";
  $query = sprintf($querystring
    , mysql_real_escape_string($table)
    , mysql_real_escape_string($column)
    , mysql_real_escape_string($row['name'])
  );
  mysql_query($query);
}

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
 * Step 1: Replace reference to name with actual name
 *   Bryozoans
 */
// add the currentnamestring column
mysql_query("ALTER TABLE `bryozoans`"
  . " ADD COLUMN `currentnamestring` VARCHAR(512)"
);

// select id, name, and currentname for each row, if currentname is set
$result = mysql_query(
  "SELECT `id`, `name`, `currentname`"
  . " FROM `bryozoans`"
  . " WHERE (`currentname` IS NOT NULL AND `currentname` < 99990)"
);

// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $currentnamestring = NULL;
  // if the record points to another one, grab the other name
  if ($row['id'] != $row['currentname']) {
    // find the name of this currentname
    $query = sprintf("SELECT `name` FROM `bryozoans` WHERE `id`='%s'"
      , mysql_real_escape_string($row['currentname'])
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
    $query = sprintf($querystring
      , mysql_real_escape_string($currentnamestring)
      , mysql_real_escape_string($row['name'])
    );
    mysql_query($query);
  }
}
mysql_free_result($result);

/**
 * Step 1: Replace reference to name with actual name
 *   CURRENTSPECIES
 */

// troublesome records found with the below regexp
// SELECT speciesid, name, first_name FROM currentspecies WHERE name REGEXP '^.*was.+=.+$' AND name NOT REGEXP '^.*was.+=([^0-9]*[0-9]+)$' AND name NOT REGEXP '^.*was.+=[^0-9]+$';

// add the currentnamestring column, change OK to valid
mysql_query("ALTER TABLE `currentspecies`"
  . " ADD COLUMN `currentnamestring` VARCHAR(512),"
  . " CHANGE `OK` `valid` INT"
);

// select synonym records with the id number of the valid name after the =
$result = mysql_query(
  "SELECT `speciesid`, `name`, `first_name`"
  . " FROM `currentspecies`"
  . " WHERE `name` REGEXP '^.*was.+=([^0-9]*[0-9]+)$'");

// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $invalidname = NULL;
  $validname = NULL;
  preg_match('/^.*was(.+)=[^0-9]*([0-9]+)$/', $row['name'], $matches);
  $invalidname = trim($matches[1]);
  $validid = $matches[2];
  
  // if the record points to another one, grab the other name
  if ($validid && $validid != $row['speciesid']) {
    // find this valid name
    $query = sprintf("SELECT `name` FROM `currentspecies` WHERE `speciesid`='%s'"
      , mysql_real_escape_string($validid)
    );
    // do the query and grab the result
    $row2 = mysql_fetch_array(mysql_query($query), MYSQL_ASSOC);
    $validname = $row2['name'];
  }
  // it points to itself
  else {
    $validname = $invalidname;
  }
  
  if (preg_match('/.*=.*/', $validname)) {
    print("ERROR: STILL HAS EQUALS SIGN: $validname\n");
  }
  
  // we got names
  if ($invalidname && $validname) {
    $querystring = "UPDATE `currentspecies`"
      . " SET `name`='%s', `currentnamestring`='%s', `valid`=0"
      . " WHERE `name`='%s'";
    $query = sprintf($querystring
      , mysql_real_escape_string($invalidname)
      , mysql_real_escape_string($validname)
      , mysql_real_escape_string($row['name'])
    );
    mysql_query($query);
  }
  // we failed to get names, delete this troublesome record
  else {
    deleteRow('currentspecies', 'currentspecies_delete', 'name', $row['name']);
  }
}
mysql_free_result($result);

// select synonym records with the valid name after the =
$result = mysql_query(
  "SELECT `speciesid`, `name`, `first_name`"
  . " FROM `currentspecies`"
  . " WHERE `name` REGEXP '^.*was.+=[^0-9]+$'");

// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $invalidname = NULL;
  $validname = NULL;
  preg_match('/^.*was(.+)=([^0-9]+)$/', $row['name'], $matches);
  $invalidname = trim($matches[1]);
  $validname = trim($matches[2]);
  // we got names
  if ($invalidname && $validname) {
    $querystring = "UPDATE `currentspecies`"
      . " SET `name`='%s', `currentnamestring`='%s', `valid`=0"
      . " WHERE `name`='%s'";
    $query = sprintf($querystring
      , mysql_real_escape_string($invalidname)
      , mysql_real_escape_string($validname)
      , mysql_real_escape_string($row['name'])
    );
    mysql_query($query);
  }
  // we failed to get names, delete this troublesome record
  else {
    deleteRow('currentspecies', 'currentspecies_delete', 'name', $row['name']);
  }
}
mysql_free_result($result);

// At this point, `currentspecies` should no longer have name like '%=%'

/**
 * Step 2: Delete unshared and unused columns
 *   Bryozoans
 */
mysql_query("ALTER TABLE `bryozoans`"
  . " DROP COLUMN `id`"
  . ", DROP COLUMN `currentname`"
  . ", DROP COLUMN `age`"
  . ", DROP COLUMN `original`"
  . ", DROP COLUMN `newcode`"
  . ", DROP COLUMN `othername`"
  . ", DROP COLUMN `delete`"
);

/**
 * Step 2: Delete unshared and unused columns
 *   CURRENTSPECIES
 */
mysql_query("ALTER TABLE `currentspecies`"
  . " DROP COLUMN `speciesid`"
  . ", DROP COLUMN `recent`"
  . ", DROP COLUMN `first_name`"
  . ", DROP COLUMN `html_page`"
  . ", DROP COLUMN `famcode`"
);

/**
 * Step 3: Fix remaining columns
 *   Bryozoans
 */
mysql_query("ALTER TABLE `bryozoans`"
  . " ADD COLUMN `familyname` VARCHAR(512)"
);

/**
 * Step 3: Fix remaining columns
 *   CURRENTSPECIES
 */
mysql_query("ALTER TABLE `currentspecies`"
  . " CHANGE `remarks` `comments` VARCHAR(512)"
  . ", ADD COLUMN `details` VARCHAR(6000)"
);

/**
 * Step 4: Insert and Replace CURRENTSPECIES into Bryozoans
 */

mysql_query("INSERT"
  . " INTO `bryozoans` ("
  . "`name`, `currentnamestring`, `author`, `details`, `comments`, `valid`"
  . ", `date_created`, `date_modified`, `status`, `familyname`"
  . ")"
  . " SELECT "
  . "`name`, `currentnamestring`, `author`, `details`, `comments`, `valid`"
  . ", `date_created`, `date_modified`, `status`, `familyname`"
  . " FROM `currentspecies`"
  . " ON DUPLICATE KEY UPDATE "
  . "`bryozoans`.`name` = `currentspecies`.`name`"
  . ", `bryozoans`.`currentnamestring` = `currentspecies`.`currentnamestring`"
  . ", `bryozoans`.`author` = `currentspecies`.`author`"
  . ", `bryozoans`.`details` = `currentspecies`.`details`"
  . ", `bryozoans`.`comments` = `currentspecies`.`comments`"
  . ", `bryozoans`.`valid` = `currentspecies`.`valid`"
  . ", `bryozoans`.`date_created` = `currentspecies`.`date_created`"
  . ", `bryozoans`.`date_modified` = `currentspecies`.`date_modified`"
  . ", `bryozoans`.`status` = `currentspecies`.`status`"
  . ", `bryozoans`.`familyname` = `currentspecies`.`familyname`"
);

/*
// This is the PHP implementation, it allows for some more control

$result = mysql_query("SELECT * FROM `bryozoans`");

// loop through results
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $query = sprintf("SELECT * FROM `currentspecies` WHERE `name`='%s'"
      , mysql_real_escape_string($row['name'])
  );
  $result2 = mysql_query($query);
  if ($match = mysql_fetch_array($result2, MYSQL_ASSOC)) {
    if (
    $match['currentnamestring'] != $row['currentnamestring']
    //|| $match['author'] != $row['author']
    //|| $match['details'] != $row['details']
    //|| $match['comments'] != $row['comments']
    || $match['valid'] != $row['valid']
    //|| $match['date_created'] != $row['date_created']
    //|| $match['date_modified'] != $row['date_modified']
    //|| $match['status'] != $row['status']
    ) {
      print("`currentspecies`"
        . " " . $match['name']
        . " " . $match['currentnamestring']
        . " " . $match['author']
        . " " . $match['valid']
        . "\n");
      print("`bryozoans`"
        . " " . $row['name']
        . " " . $row['name']
        . " " . $row['currentnamestring']
        . " " . $row['author']
        . " " . $row['valid']
        . "\n");
    }
  }
}
mysql_free_result($result);
*/

mysql_close($link);