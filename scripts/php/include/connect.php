<?php
/**
 * Common code for connecting to the db with required tables.
 */
// connect to localhost
$link = mysql_connect('localhost', 'kamil');
if (!$link) { die('Could not connect: ' . mysql_error()); }
// make bock the current db
$db_selected = mysql_select_db('bryozoa', $link);
if (!$db_selected) { die ('Could not use database: ' . mysql_error()); }