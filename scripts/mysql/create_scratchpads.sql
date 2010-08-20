SOURCE db.sql;

/*
drop the scratchpads table if it exists already
*/
DROP TABLE IF EXISTS `scratchpads`;

/*
create a table with the proper data types for each field
*/
CREATE TABLE `scratchpads` (
  /* these are columns that will be uploaded to Scratchpads */
  `rank_name` VARCHAR(64) NOT NULL,
  `unit_name1` VARCHAR(64) NOT NULL,
  `unit_name2` VARCHAR(64),
  `unit_name3` VARCHAR(64),
  `parent_name` VARCHAR(512),
  `usage` VARCHAR(32) NOT NULL,
  `taxon_author` VARCHAR(512),
  `accepted_name` VARCHAR(512),
  `unacceptability_reason` VARCHAR(512),
  `comments` VARCHAR(6000),
  `details` VARCHAR(6000),
  
  /* 
  this column is only used as a MySQL primary key, not uploaded to Scratchpads
  it is the trimmed concatenation of unit_name1 and unit_name2 and unit_name3
  */
  `full_name` VARCHAR(333),
  
  PRIMARY KEY (`full_name`),
  KEY (`rank_name`),
  KEY (`unit_name1`),
  KEY (`parent_name`),
  KEY (`accepted_name`)
);