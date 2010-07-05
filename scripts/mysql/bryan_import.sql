USE `bock`;

DROP TABLE IF EXISTS `bryan_valid`;
DROP TABLE IF EXISTS `bryan_invalid`;
DROP TABLE IF EXISTS `bryan_rank`;

/* we want to see a lot of errors if they exist */
SET max_error_count=1000;

/*
old-primary-id	old-reference-id	taxon	taxon-rank	authority-date	new-primary-id	new-reference-id	
Skip 1 line
*/
CREATE TABLE `bryan_valid` (
  `oldid` INT,
  `oldrefid` INT,
  `name` VARCHAR(512),
  `rankcode` INT,
  `year` VARCHAR(128),
  `newid` INT,
  `newrefid` INT,
  `comments` VARCHAR(2000),
  KEY (`oldid`),
  KEY (`oldrefid`),
  KEY (`newid`),
  KEY (`newrefid`),
  KEY (`name`)
);

SELECT "Loading bryan_valid" AS "Action";

LOAD DATA LOCAL INFILE '../../bryan/sheets/mysql/valid.tab'
INTO TABLE `bryan_valid` IGNORE 1 LINES;
SHOW WARNINGS;

/*
taxonid	parentid	taxonname	rankcode	seniorid	year	author	date	comments
Skip 1 line
*/
CREATE TABLE `bryan_invalid` (
  `taxonid` INT,
  `parentid` INT,
  `taxonname` VARCHAR(512),
  `rankcode` INT,
  `seniorid` INT,
  `year` INT,
  `author` VARCHAR(512),
  `date` DATE,
  `comments` VARCHAR(2000),
  KEY (`taxonid`),
  KEY (`parentid`),
  KEY (`taxonname`),
  KEY (`rankcode`),
  KEY (`seniorid`)
);

SELECT "Loading bryan_invalid" AS "Action";

LOAD DATA LOCAL INFILE '../../bryan/sheets/mysql/invalid.tab'
INTO TABLE `bryan_invalid` IGNORE 1 LINES;
SHOW WARNINGS;
