/*
Just copy and paste into a terminal.
*/
SOURCE db.sql;
TEE /tmp/describe_scratchpads.log;
DESCRIBE `scratchpads`;
SELECT COUNT(*) FROM `scratchpads`;
SELECT COUNT(*),`rank_name` FROM `scratchpads` GROUP BY `rank_name` ORDER BY FIELD(`rank_name`,'Phlylum','Class','Order','Suborder','Infraorder','Superfamily','Family','Genus','Species','Subspecies');
SELECT COUNT(*),`usage` FROM `scratchpads` GROUP BY `usage`;
SELECT COUNT(*),`rank_name`,`usage` FROM `scratchpads` GROUP BY `rank_name`,`usage` ORDER BY FIELD(`rank_name`,'Phlylum','Class','Order','Suborder','Infraorder','Superfamily','Family','Genus','Species','Subspecies'), `usage` desc;
SELECT COUNT(*) FROM `scratchpads` WHERE `usage` = 'invalid' AND LENGTH(`unacceptability_reason`) < 1;
SELECT COUNT(*) FROM `scratchpads` WHERE `usage` = 'invalid' AND LENGTH(`unacceptability_reason`) > 1;
SELECT COUNT(*),`parent_name` FROM `scratchpads` GROUP BY `parent_name` ORDER BY COUNT(*) DESC LIMIT 10;
SELECT `full_name`,`taxon_author` FROM `scratchpads` WHERE `taxon_author` IS NULL OR `taxon_author` NOT REGEXP '[0-9]{4}' ORDER BY `full_name`;
SELECT COUNT(*),`unacceptability_reason` FROM `scratchpads` WHERE LENGTH(`unacceptability_reason`) > 1 GROUP BY `unacceptability_reason`;
QUIT;
