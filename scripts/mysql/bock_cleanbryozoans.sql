USE `bock`;

/*
Delete records that Phil Bock marked for deletion.
*/
CREATE TABLE `bryozoans_delete`
AS SELECT * FROM `bryozoans`
WHERE `comments` LIKE '%delete%'
OR `currentname` > 90000
OR `name` LIKE '%comment%'
OR `name` LIKE '%ignore%';

DELETE FROM `bryozoans`
WHERE `comments` LIKE '%delete%'
OR `currentname` > 90000
OR `name` LIKE '%comment%'
OR `name` LIKE '%ignore%';

/* Current_name = id but valid 0
SELECT id, name, comments FROM bryozoans WHERE currentname = id AND valid = 0;
*/

/* Current_name null but valid 1
SELECT id, name, comments FROM bryozoans WHERE currentname IS NULL AND valid = 1;
*/

/* Non-alpha characters
SELECT name FROM bryozoans WHERE name REGEXP ".*[[\\(\\.&0-9'\"].*";

More results with this query
SELECT name FROM bryozoans WHERE name REGEXP "[^A-Za-z ]";
*/

/* Valid 1 but status says synonym
SELECT id, name, author, valid, status FROM bryozoans WHERE valid = 1 AND status LIKE '%synonym%';
*/