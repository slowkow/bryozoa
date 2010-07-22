<?php
/**
 * In Phil Bock's table `bryozoans`, look at invalid names that do not point
 * to a current name.
 * 
 * Parse the `details` field and try to figure out the current name.
 */

require 'include/connect.php';
require 'include/scratchpads.php';

/**
 * Query the `bryozoans` table with a name.
 * 
 * @param name
 *   Name of a taxon.
 * @return
 *   An associative array of the returned row.
 */
function getRow($name) {
  $query = sprintf("SELECT * FROM `bryozoans`"
    . " WHERE `name`='%s'",
    mysql_real_escape_string($name));
  return mysql_fetch_assoc(mysql_query($query));
}
/**
 * Set currentnamestring for a row.
 * 
 * @param name
 *   Name of a taxon.
 */
function setCurrentNameString($name, $currentnamestring) {
  $name = mysql_real_escape_string($name);
  $currentnamestring = mysql_real_escape_string($currentnamestring);
  $query = sprintf("INSERT INTO `bryozoans`"
    . " SET `name`='%s', `currentnamestring`='%s'"
    . " ON DUPLICATE KEY UPDATE `currentnamestring`='%s'",
    $name, $currentnamestring, $currentnamestring);
  mysql_query($query);
  if (mysql_error()) { die(mysql_error() . "\n"); }
}

$result = mysql_query(
  "SELECT `name`, `details` FROM `bryozoans`"
  . " WHERE `valid` = 0"
  . " AND `name` IS NOT NULL"
  . " AND `author` IS NOT NULL"
  . " AND `currentnamestring` IS NULL"
  . " AND `details` IS NOT NULL"
  . " AND `details` REGEXP '(etiam|vide|nunc) '"
);

$count = 0;
while ($row = mysql_fetch_assoc($result)) {
  // get words in taxon name
  $name       = ucfirst(strtolower($row['name']));
  $name_words = str_word_count($name, 1);
  
  // grab one or more words after 'nunc', case insensitive
  $matches = array();
  if (preg_match('/nunc (\w+)\b(?: (\w+)\b)?(?: (\w+)\b)?/i', $row['details'], $matches)) {
    // words up to 'fide' are assumed to be the accepted name
    $accepted_name = '';
    foreach (array_slice($matches, 1) as $match) {
      // various spellings of the word 'fide'
      static $fide = array('fide', 'by', 'afide', 'fise', 'ab');
      if (in_array($match, $fide)) {
        break;
      }
      $accepted_name .= ' ' . $match;
    }
    // now we have the first part of the accepted name
    $accepted_name = trim($accepted_name);
    // grab the rest of the accepted name from the original name
    $accepted_name = trim($accepted_name . ' '
      . implode(' ', array_slice($name_words, str_word_count($accepted_name))));
    
    // check if this name exists
    if ($accepted_row = getRow($accepted_name)) {
      if ($accepted_row['valid'] == 1) {
        setCurrentNameString($name, $accepted_name);
        $count++;
      }
      else {
        print("the accepted name is also invalid! '$accepted_name'\n");
      }
    }
  }
}
mysql_free_result($result);
print("count: $count\n");