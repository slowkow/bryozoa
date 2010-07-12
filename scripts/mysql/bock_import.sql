/*
let's use a database called bock
*/
CREATE DATABASE IF NOT EXISTS `bock`;
USE `bock`;

/*
we want to see a lot of errors if they exist
*/
SET max_error_count=1000;

/*
drop the bryozoans table if it exists already
*/
DROP TABLE IF EXISTS `bryozoans`;

/*
create a table with the proper data types for each field
*/
CREATE TABLE `bryozoans` (
  `id` INT,
  `name` VARCHAR(512),
  `currentname` INT,
  `author` VARCHAR(512),
  `details` VARCHAR(6000),
  `comments` VARCHAR(512),
  `age` VARCHAR(255),
  `original` INT,
  `valid` INT,
  `delete` INT,
  `date_created` DATE,
  `date_modified` DATE,
  `newcode` VARCHAR(512),
  `status` VARCHAR(512),
  `othername` VARCHAR(512),
  PRIMARY KEY (`name`),
  KEY (`id`),
  KEY (`currentname`)
);

SELECT "Loading bryozoans" AS "Action";
/*
load the tab-delimited file into the database, ignore the header line
*/
LOAD DATA LOCAL INFILE '../../bock/Jun2010/Bryozoans_mysql.tab' INTO TABLE `bryozoans` IGNORE 1 LINES;
SELECT COUNT(*) FROM `bryozoans`;

/*
let's see those warnings
*/
SHOW WARNINGS;

/*
drop the currentspecies table if it exists already
*/
DROP TABLE IF EXISTS `currentspecies`;

/*
create a table with the proper data types for each field
*/
CREATE TABLE `currentspecies` (
  `speciesid` INT,
  `name` VARCHAR(512),
  `author` VARCHAR(512),
  `famcode` INT,
  `recent` INT,
  `remarks` VARCHAR(512),
  `date_created` DATE,
  `date_modified` DATE,
  `first_name` VARCHAR(512),
  `html_page` VARCHAR(2048),
  `OK` INT,
  `status` VARCHAR(512),
  `familyname` VARCHAR(512),
  PRIMARY KEY (`name`),
  KEY (`speciesid`),
  KEY (`famcode`)
);


SELECT "Loading currentspecies" AS "Action";
/*
load the tab-delimited file into the database, ignore the header line
*/
LOAD DATA LOCAL INFILE '../../bock/Jun2010/CURRENTSPECIES_mysql.tab' INTO TABLE `currentspecies` IGNORE 1 LINES;
SELECT COUNT(*) FROM `currentspecies`;

/*
let's see those warnings
*/
SHOW WARNINGS;
