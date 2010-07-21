/*
Just copy and paste into a terminal.
*/
TEE /tmp/describe_scratchpads.log;
USE `bock`;
DESCRIBE `scratchpads`;
SELECT COUNT(*) FROM `scratchpads`;
SELECT COUNT(*),`rank_name` FROM `scratchpads` GROUP BY `rank_name` ORDER BY COUNT(*);
SELECT COUNT(*), `usage` FROM `scratchpads` GROUP BY `usage`;
SELECT COUNT(*),`rank_name`,`usage` FROM `scratchpads` GROUP BY `rank_name`,`usage` ORDER BY `rank_name`, COUNT(*);
SELECT COUNT(*) FROM `scratchpads` WHERE `usage` = 'invalid' AND LENGTH(`unacceptability_reason`) < 1;
SELECT COUNT(*) FROM `scratchpads` WHERE `usage` = 'invalid' AND LENGTH(`unacceptability_reason`) > 1;
SELECT COUNT(*),`parent_name` FROM `scratchpads` GROUP BY `parent_name` ORDER BY COUNT(*) DESC LIMIT 10;
SELECT `full_name`, `taxon_author` FROM `scratchpads` WHERE `taxon_author` NOT REGEXP '[0-9]' ORDER BY `full_name`;
QUIT;
