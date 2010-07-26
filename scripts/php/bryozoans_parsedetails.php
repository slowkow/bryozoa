<?php
/**
 * In Phil Bock's table `bryozoans`, look at invalid names that do not point
 * to a current name.
 * 
 * Parse the `details` field and try to figure out the current name.
 */

require 'include/connect.php';

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

// 7131 rows, 2290 get set a new currentnamestring
$result = mysql_query(
  "SELECT `name`, `details`, `currentnamestring` FROM `bryozoans`"
  . " WHERE `valid` = 0"
  . " AND `name` IS NOT NULL"
  . " AND `author` IS NOT NULL"
  . " AND `currentnamestring` IS NULL"
  . " AND `details` REGEXP '(etiam|vide|nunc) '"
);

$count = 0;
while ($row = mysql_fetch_assoc($result)) {
  // get words in taxon name
  $name       = ucfirst(strtolower($row['name']));
  $name_words = str_word_count($name, 1);
  
  // various spellings of the word 'fide'
  static $fide = array('fide', 'by', 'afide', 'fise', 'ab');
  static $regexp =
  '/\b(?:vide|etiam|nunc)\b\s+(\w+)\b(?:\s+(\w+)\b)?(?:\s+(\w+)\b)?(?:\s+(\w+)\b)?(?:\s+(\w+)\b)?/i';
  
  $offset = 0;
  $matches = array();
  // match up to 5 words after vide or etiam or nunc
  while (preg_match($regexp, $row['details'], $matches, 0, $offset)) {
    // check the rest of the string on the next iteration
    $offset += strlen($matches[0]);
    
    // words preceding 'fide' are assumed to be part of the accepted name
    $details_name = '';
    foreach (array_slice($matches, 1) as $match) {
      if (in_array($match, $fide)) {
        break;
      }
      $details_name .= ' ' . $match;
    }
    // now we have the first part of the accepted name
    $details_name = trim(ucfirst(strtolower($details_name)));
    // grab the rest of the accepted name from the original name
    $details_name = trim($details_name . ' '
      . implode(' ', array_slice($name_words, str_word_count($details_name))));
    
    // check if this name exists
    if ($accepted_row = getRow($details_name)) {
      // check if this name is valid (not a synonym)
      if ($accepted_row['valid'] == 1
      // the currentnamestring hasn't already been set
      && $row['currentnamestring'] != $details_name) {
        setCurrentNameString($name, $details_name);
        $count++;
      }
    }
  }
}
mysql_free_result($result);
print("count: $count\n");