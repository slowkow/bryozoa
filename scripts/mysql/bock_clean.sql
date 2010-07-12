/*
Delete bad records and contradictory records.
*/

USE `bock`;

/*******************************************************************************
Table `bryozoans`
  Number of records before deletion: 42202
  Number of records after deletion:  30694
  Number of records deleted:         11508
*/

DROP TABLE IF EXISTS `bryozoans_delete`;

SELECT "Cleaning `bryozoans`" AS "Action";
SELECT COUNT(*) FROM `bryozoans`;

/*
829 records
Phil Bock marked these for extra work or deletion.
*/
CREATE TABLE `bryozoans_delete`
  AS SELECT * FROM `bryozoans`
    WHERE `comments` LIKE '%delete%' OR `currentname` > 90000 OR `id` > 90000
      OR `name` LIKE '%comment%' OR `name` LIKE '%ignore%';
ALTER TABLE `bryozoans_delete`
  ADD PRIMARY KEY (`name`),
  ADD KEY (`id`),
  ADD KEY (`currentname`);

/*
372
Phil Bock marked these for extra work.
SELECT COUNT(*) FROM `bryozoans` WHERE comments LIKE '%check%' OR comments LIKE '%?%';
*/

/*
3961 records
Current_name = id but valid 0
*/
INSERT IGNORE INTO `bryozoans_delete` SELECT * FROM `bryozoans`
  WHERE `currentname` = `id` AND `valid` = 0;

/*
1194 records
Current_name null but valid 1
*/
INSERT IGNORE INTO `bryozoans_delete` SELECT * FROM `bryozoans`
  WHERE `currentname` IS NULL AND `valid` = 1;

/*
1988 records
Non-alpha characters
*/
INSERT IGNORE INTO `bryozoans_delete` SELECT * FROM `bryozoans`
  WHERE `name` REGEXP "[^A-Za-z ]";

/*
9 records
Valid 1 but status says synonym
*/
INSERT IGNORE INTO `bryozoans_delete` SELECT * FROM `bryozoans`
  WHERE `valid` = 1 AND `status` LIKE '%synonym%';

/*
633 records
Current_name points to a record that doesn't exist

Problem: Other records may point to these deleted records. We can continue
to delete records until no records point to nonexistant records, but I prefer
to leave them.
*/
/*
INSERT IGNORE INTO `bryozoans_delete` (
  SELECT * FROM `bryozoans` AS `t1`
    WHERE `currentname` IS NOT NULL
      AND (
        # the referenced record does not exist in bryozoans 
        NOT EXISTS (
          SELECT * FROM `bryozoans` AS `t2` WHERE `t2`.`id` = `t1`.`currentname`
        )
        # the referenced record has been deleted 
        OR EXISTS (
          SELECT * FROM `bryozoans_delete` AS `t3` WHERE `t3`.`id` = `t1`.`currentname`
        )
      )
);
*/

/* Delete the records */
DELETE `t1` FROM `bryozoans` AS `t1`, `bryozoans_delete` AS `t2`
  WHERE `t1`.`name` = `t2`.`name`;

SELECT "Finished cleaning `bryozoans`" AS "Action";
SELECT COUNT(*) FROM `bryozoans`;
SELECT "Records in `bryozoans_delete`" AS "Action";
SELECT COUNT(*) FROM `bryozoans_delete`;

/*******************************************************************************
Table `currentspecies`
  Number of records before deletion: 21039
  Number of records after deletion:  19558
  Number of records deleted:          1481
*/

DROP TABLE IF EXISTS `currentspecies_delete`;

SELECT "Cleaning `currentspecies`" AS "Action";
SELECT COUNT(*) FROM `currentspecies`;

/*
88 records with OK=1 and status='Current'
 5 records with OK=1 and status!='Current'
Delete the 5 records
*/
CREATE TABLE `currentspecies_delete`
  AS SELECT * FROM `currentspecies`
    WHERE `OK` = 1 AND `status` NOT LIKE 'current';
ALTER TABLE `currentspecies_delete`
  ADD PRIMARY KEY (`name`),
  ADD KEY (`speciesid`),
  ADD KEY (`famcode`);

/*
1478 records
Non-alpha characters
(equals sign and numbers are ok because some records reference others)

PROBLEM:
Some of the "was Foo Bar=123" point to these deleted records.
*/
INSERT IGNORE INTO `currentspecies_delete` SELECT * FROM `currentspecies`
  WHERE `name` REGEXP "[^A-Za-z =0-9]";

/* Delete the records */
DELETE `t1` FROM `currentspecies` AS `t1`, `currentspecies_delete` AS `t2`
  WHERE `t1`.`name` = `t2`.`name`;

SELECT "Finished cleaning `currentspecies`" AS "Action";
SELECT COUNT(*) FROM `currentspecies`;
SELECT "Records in `currentspecies_delete`" AS "Action";
SELECT COUNT(*) FROM `currentspecies_delete`;