/*
let's use a database called bock
*/
CREATE DATABASE IF NOT EXISTS `bock`;
USE `bock`;

/*
drop the bryozoans table if it exists already
*/
DROP TABLE IF EXISTS `bryozoans`;

/*
create a table with the proper data types for each field
*/
CREATE TABLE `bryozoans` (`id` INT,`name` VARCHAR(512),`pid` INT,`author` VARCHAR(512),`details` VARCHAR(6000),`comments` VARCHAR(512),`age` VARCHAR(255),`original` INT,`valid` INT,`delete` INT,`date_created` DATE,`date_modified` DATE,`newcode` VARCHAR(512),`status` VARCHAR(512),`othername` VARCHAR(512) );

/*
we want to see a lot of errors if they exist
*/
SET max_error_count=1000;

/*
load the tab-delimited file into the database, ignore the header line
*/
LOAD DATA LOCAL INFILE '../../bock/Jun2010/Bryozoans_mysql.tab' INTO TABLE `bryozoans` IGNORE 1 LINES;

/*
let's see those warnings
*/
SHOW WARNINGS;
