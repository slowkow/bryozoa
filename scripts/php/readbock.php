<?php

/**
 * Kamil Slowikowski
 * Summer 2010
 * 
 * This script reads Phil Bock's Bryozoans.tab.
 * 
 */

// Bock's file is large, we need room
ini_set("memory_limit","800M");

/**
 * Read a Bock-type file into an array. Set $header to TRUE if the file
 * has a header line with the fieldnames.
 * 
 * @param filename
 *   The full path to the file
 * @param taxonarray
 *   The array into which the file will be read
 * @param header
 *   Set to true if the file has a header line with fieldnames
 */
function readBockFile($filename, $taxonarray, $header=TRUE) {
  $file1 = fopen($filename, 'r') or die("Cannot open $filename");
  
  // count number of rows
  $count = 0;
  while (!feof($file1)) {
    // read one line and trim extra whitespace
    $row = trim(fgets($file1));
    
    // skip comments and empty lines
    if ($row[0] == '#' || strlen($row) == 0) {
      continue;
    }
    
    // count each non-comment line
    $count++;
    
    // skip header
    if ($count == 1 && $header) {
      continue;
    }
    
    // ID	Name	Current_name	Author	Details	Comments	Age	Original	Valid	Delete	Date_created	Date_modified	Newcode	Status	Bryozoans2_Name
    list($id, $name, $pid, $author, $details, $comments, $age, $original,
      $valid, $delete, $date_created, $date_modified, $newcode, $status,
      $othername) = explode("\t", $row);
    
    //if (!$id || !$name || !$pid || !$author || !$details || !$comments || !$age
    //  || !$original || !$valid || !$delete || !$date_created || !$date_modified
    //  || !$newcode || !$status || !$othername) {
    //  continue;
    //}
    
    $taxonarray[] = array(
      'id' => $id,
      'name' => $name,
      'pid' => $pid,
      'author' => $author,
      'details' => $details,
      'comments' => $comments,
      'age' => $age,
      'original' => $original,
      'valid' => $valid,
      'delete' => $delete,
      'date_created' => $date_created,
      'date_modified' => $date_modified,
      'newcode' => $newcode,
      'status' => $status,
      'othername' => $othername,
    );
  }
  fclose($file1);
}

/**
 * Print the first word of each name (the genus) and the number of times it
 * occurs in the array.
 * 
 * @param taxonarray
 *   An array that holds Bock-type data
 */
function printGenusCount($taxonarray) {
  $genus_count = array();
  foreach ($taxonarray as $key => $record) {
    $genus = strtok($record['name'], " \t");
    $genus_count[$genus] += 1;
  }
  arsort($genus_count);
  foreach ($genus_count as $genus => $count) {
    print("$genus\t$count\n");
  }
}

/**
 * Print names that contain weird characters (not in alphabet and not space)
 * 
 * @param taxonarray
 *   An array that holds Bock-type data
 */
function printWeirdNames($taxonarray) {
  //$names = array();
  //print("name\tvalid\tid\tpid\tdetails\tauthor\tdelete");
  foreach ($taxonarray as $key => $record) {
    $name = $record['name'];
    if (preg_match("/[^A-Za-z ]/", $name)) {
      print($name . "\n");
/*
      $names[] = $name . "\t" . $record['valid']
        . "\t" . $record['id'] . "\t" . $record['pid'] . "\t" . $record['details']
        . "\t" . $record['author'] . "\t" . $record['delete'];
*/
    }
  }
  foreach ($names as $key => $name) {
    print($name . "\n");
  }
}

/**
 * Print maximum length of a specified column.
 * 
 * @param taxonarray
 *   An array that holds Bock-type data
 * @param fieldname
 *   A valid fieldname whose length will be checked
 */
function printMaxLength($taxonarray, $fieldname) {
  $maxlength = 0;
  foreach ($taxonarray as $key => $record) {
    $maxlength = max($maxlength, strlen($record[$fieldname]));
  }
  print($maxlength . "\n");
}

/*
 * Run the functions.
 */

$bock_array = array();
readBockFile('../../bock/Jun2010/Bryozoans.tab', &$bock_array);
//printMaxLength($bock_array, 'comments');
//printWeirdNames($bock_array);
//printGenusCount($bock_array);
