SOURCE db.sql;

SELECT
  `rank_name`,
  `unit_name1`,
  `unit_name2`,
  `unit_name3`,
  `parent_name`,
  `usage`,
  `taxon_author`,
  `accepted_name`,
  `unacceptability_reason`,
  `comments`,
  `details`
FROM `scratchpads`
ORDER BY FIELD(
  `rank_name`,
  'Phlylum',
  'Class',
  'Order',
  'Suborder',
  'Infraorder',
  'Superfamily',
  'Family',
  'Genus',
  'Species',
  'Subspecies'
);