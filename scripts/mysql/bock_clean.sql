/*
Delete bad records and contradictory records.
*/

USE `bock`;

/*******************************************************************************
Table `bryozoans`
  Number of records before deletion: 42202
  Number of records after deletion:  34536
  Number of records deleted:          7666
*/

DROP TABLE IF EXISTS `bryozoans_delete`;

/*
829 records
Phil Bock marked these for deletion.
*/
CREATE TABLE `bryozoans_delete`
  AS SELECT * FROM `bryozoans`
    WHERE `comments` LIKE '%delete%' OR `currentname` > 90000
      OR `name` LIKE '%comment%' OR `name` LIKE '%ignore%';

DELETE FROM `bryozoans`
  WHERE `comments` LIKE '%delete%' OR `currentname` > 90000
    OR `name` LIKE '%comment%' OR `name` LIKE '%ignore%';

/*
3961 records
Current_name = id but valid 0
*/
INSERT INTO `bryozoans_delete` SELECT * FROM `bryozoans`
  WHERE `currentname` = `id` AND `valid` = 0;
DELETE FROM `bryozoans` WHERE `currentname` = `id` AND `valid` = 0;

/*
1194 records
Current_name null but valid 1
*/
INSERT INTO `bryozoans_delete` SELECT * FROM `bryozoans`
  WHERE `currentname` IS NULL AND `valid` = 1;
DELETE FROM `bryozoans` WHERE `currentname` IS NULL AND `valid` = 1;

/*
1988 records
Non-alpha characters
*/
INSERT INTO `bryozoans_delete` SELECT * FROM `bryozoans`
  WHERE `name` REGEXP "[^A-Za-z ]";
DELETE FROM `bryozoans` WHERE `name` REGEXP "[^A-Za-z ]";

/*
9 records
Valid 1 but status says synonym
*/
INSERT INTO `bryozoans_delete` SELECT * FROM `bryozoans`
  WHERE `valid` = 1 AND `status` LIKE '%synonym%';
DELETE FROM `bryozoans` WHERE `valid` = 1 AND `status` LIKE '%synonym%';

/*******************************************************************************
Table `currentspecies`
  Number of records before deletion: 21039
  Number of records after deletion:  19558
  Number of records deleted:          1481
*/

DROP TABLE IF EXISTS `currentspecies_delete`;

/*
88 records with OK=1 and status='Current'
 5 records with OK=1 and status!='Current'
Delete the 5 records
*/
CREATE TABLE `currentspecies_delete`
  AS SELECT * FROM `currentspecies`
    WHERE `OK` = 1 AND `status` NOT LIKE 'current';

DELETE FROM `currentspecies`
  WHERE `OK` = 1 AND `status` NOT LIKE 'current';

/*
1478 records
Non-alpha characters
(equals sign and numbers are ok because some records reference others)

PROBLEM:
Some of the "was Foo Bar=123" point to these deleted records.
*/
INSERT INTO `currentspecies_delete` SELECT * FROM `currentspecies`
  WHERE `name` REGEXP "[^A-Za-z =0-9]";
DELETE FROM `currentspecies` WHERE `name` REGEXP "[^A-Za-z =0-9]";
