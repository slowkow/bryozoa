/*
let's use a database called bock
*/
USE bock;

/*
DELETE FROM bryozoans WHERE currentname > 99990;
*/

/*
Add the currentnamestring column
*/
/*
ALTER TABLE bryozoans ADD currentnamestring VARCHAR(512);
*/


/*
Somehow, the PHP script performs this task much more quickly than pure MySQL.
Also, PHP gives more control over exactly what happens.
*/
SELECT
  /*bryozoans.name, bryozoans.id, bryozoans.currentname,
  bryozoansref.id, bryozoansref.name*/
  bryozoansref.name
FROM
  bryozoans,
  (SELECT bryozoans.id, bryozoans.name FROM bryozoans) AS bryozoansref
WHERE (
  bryozoans.currentname IS NOT NULL
  AND bryozoans.currentname < 99990
  AND bryozoans.currentname = bryozoansref.id
);


/*
This is a function for adding a column to a table iff it doesn't exist.
*/
/*
delimiter '//'

CREATE PROCEDURE addcol() BEGIN
IF NOT EXISTS(
	SELECT * FROM information_schema.COLUMNS
	WHERE COLUMN_NAME='new_column' AND TABLE_NAME='the_table' AND TABLE_SCHEMA='the_schema'
	)
	THEN
		ALTER TABLE `the_schema`.`the_table`
		ADD COLUMN `new_column` bigint(20) unsigned NOT NULL default 1;

END IF;
END;
//

delimiter ';'

CALL addcol();

DROP PROCEDURE addcol;
*/