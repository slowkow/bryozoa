SOURCE db.sql;

/* drop the bryozone tables if they already exist */
DROP TABLE IF EXISTS `bryozone_age`;
DROP TABLE IF EXISTS `bryozone_authors_references`;
DROP TABLE IF EXISTS `bryozone_authors`;
DROP TABLE IF EXISTS `bryozone_latin`;
DROP TABLE IF EXISTS `bryozone_rank`;
DROP TABLE IF EXISTS `bryozone_references_full`;
DROP TABLE IF EXISTS `bryozone_references`;
DROP TABLE IF EXISTS `bryozone_taxa_authors`;
DROP TABLE IF EXISTS `bryozone_taxa_references`;
DROP TABLE IF EXISTS `bryozone_taxa`;

/* we want to see a lot of errors if they exist */
SET max_error_count=1000;

/*
TAXON-ID	RANK
Skip 1 line
*/
CREATE TABLE `bryozone_age` (
  `taxonid` INT,
  `age` VARCHAR(512),
  PRIMARY KEY (`taxonid`)
);

SELECT 'Loading into table bryozone_age' AS ' ';

LOAD DATA LOCAL INFILE '../../bryozone/sheets/mysql/age.tab'
INTO TABLE `bryozone_age` IGNORE 1 LINES;
SHOW WARNINGS;

/*
AuthorID	ArticleID
Skip 1 line
*/
CREATE TABLE `bryozone_authors_references` (
  `authorid` INT,
  `articleid` INT,
  PRIMARY KEY (`authorid`),
  KEY (`articleid`)
);

SELECT 'Loading into table bryozone_authors_references' AS ' ';

LOAD DATA LOCAL INFILE '../../bryozone/sheets/mysql/authors_references.tab'
INTO TABLE `bryozone_authors_references` IGNORE 1 LINES;
SHOW WARNINGS;

/*
ID	NAME
Skip 1 line
*/
CREATE TABLE `bryozone_authors` (
  `authorid` INT,
  `authorname` VARCHAR(512),
  PRIMARY KEY (`authorid`)
);

SELECT 'Loading into table bryozone_authors' AS ' ';

LOAD DATA LOCAL INFILE '../../bryozone/sheets/mysql/authors.tab'
INTO TABLE `bryozone_authors` IGNORE 1 LINES;
SHOW WARNINGS;

/*
TAXON-ID	LATIN-COMMENTS
Skip 5 lines
*/
CREATE TABLE `bryozone_latin` (
  `taxonid` INT,
  `latincomments` VARCHAR(6000),
  PRIMARY KEY (`taxonid`)
);

SELECT 'Loading into table bryozone_latin' AS ' ';

LOAD DATA LOCAL INFILE '../../bryozone/sheets/mysql/latin.tab'
INTO TABLE `bryozone_latin` IGNORE 5 LINES;
SHOW WARNINGS;

/*
Rank-ID	Rank-Name
Skip 1 line
*/
CREATE TABLE `bryozone_rank` (
  `rankid` INT,
  `rankname` VARCHAR(512),
  PRIMARY KEY (`rankid`)
);

SELECT 'Loading into table bryozone_rank' AS ' ';

LOAD DATA LOCAL INFILE '../../bryozone/sheets/mysql/rank.tab'
INTO TABLE `bryozone_rank` IGNORE 1 LINES;
SHOW WARNINGS;

/*
ID	AUTHOR	YEAR	TEXT	TITLE	NUMBER	PAGES
Skip 5 lines
*/
CREATE TABLE `bryozone_references_full` (
  `referenceid` INT,
  `author` VARCHAR(512),
  `year` VARCHAR(512),
  `text` VARCHAR(512),
  `title` VARCHAR(512),
  `number` VARCHAR(512),
  `pages` VARCHAR(512),
  PRIMARY KEY (`referenceid`)
);

SELECT 'Loading into table bryozone_references_full' AS ' ';

LOAD DATA LOCAL INFILE '../../bryozone/sheets/mysql/references_full.tab'
INTO TABLE `bryozone_references_full` IGNORE 5 LINES;
SHOW WARNINGS;

/*
ARTICLE-ID	YEAR	ARTICLE-TEXT
Skip 1 line
*/
CREATE TABLE `bryozone_references` (
  `referenceid` INT,
  `year` INT,
  `text` VARCHAR(512),
  PRIMARY KEY (`referenceid`)
);

SELECT 'Loading into table bryozone_references' AS ' ';

LOAD DATA LOCAL INFILE '../../bryozone/sheets/mysql/references.tab'
INTO TABLE `bryozone_references` IGNORE 1 LINES;
SHOW WARNINGS;

/*
TAXON-ID	AUTHOR
Skip 6 lines
*/
CREATE TABLE `bryozone_taxa_authors` (
  `taxonid` INT,
  `authorid` INT,
  PRIMARY KEY (`taxonid`),
  KEY (`authorid`)
);

SELECT 'Loading into table bryozone_taxa_authors' AS ' ';

LOAD DATA LOCAL INFILE '../../bryozone/sheets/mysql/taxa_authors.tab'
INTO TABLE `bryozone_taxa_authors` IGNORE 6 LINES;
SHOW WARNINGS;

/*
TAXON-ID	ARTICLE-ID
Skip 1 line
*/
CREATE TABLE `bryozone_taxa_references` (
  `taxonid` INT,
  `articleid` INT,
  KEY (`taxonid`),
  KEY (`articleid`)
);

SELECT 'Loading into table bryozone_taxa_references' AS ' ';

LOAD DATA LOCAL INFILE '../../bryozone/sheets/mysql/taxa_references.tab'
INTO TABLE `bryozone_taxa_references` IGNORE 1 LINES;
SHOW WARNINGS;

/*
TAXON-ID	PARENT-TAXON-ID	TAXON-NAME	RANK	SENIOR-SYN	YEAR	EXPERT	REVISED	COMMENTS
Skip 22 lines
*/
CREATE TABLE `bryozone_taxa` (
  `taxonid` INT,
  `parentid` INT,
  `taxonname` VARCHAR(512),
  `rankcode` INT,
  `seniorid` INT,
  `year` INT,
  `expert` VARCHAR(512),
  `revised` DATE,
  `comments` VARCHAR(6000),
  PRIMARY KEY (`taxonid`),
  KEY (`parentid`),
  KEY (`taxonname`),
  KEY (`rankcode`),
  KEY (`seniorid`)
);

SELECT 'Loading into table bryozone_taxa' AS ' ';

LOAD DATA LOCAL INFILE '../../bryozone/sheets/mysql/taxa.tab'
INTO TABLE `bryozone_taxa` IGNORE 22 LINES;
SHOW WARNINGS;
