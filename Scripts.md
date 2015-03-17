

# scripts #

## go.sh ##
A main script that executes all of the others.

The following input files are required to run this script:

  * `bock/Jun2010/mysql/bryozoans.tab`
  * `bock/Jun2010/mysql/currentspecies.tab`
  * `bryan/sheets/mysql/valid.tab`
  * `bryozone/sheets/mysql/authors.tab`
  * `bryozone/sheets/mysql/taxa.tab`
  * `bryozone/sheets/mysql/taxa_authors.tab`

The following files are output by this script:

  * `scripts/mysql/output/bryan.tab`
  * `scripts/perl/output/bryan_fullhierarchy.txt`
  * `scripts/perl/output/bryan_uniquepaths.txt`

  * `scripts/php/output/bryozone.tab`
  * `scripts/perl/output/bryozone_fullhierarchy.txt`
  * `scripts/perl/output/bryozone_uniquepaths.txt`

  * `scripts/mysql/output/scratchpads.tab`
  * `scripts/perl/output/scratchpads_fullhierarchy.txt`
  * `scripts/perl/output/scratchpads_uniquepaths.txt`
  * `scripts/perl/output/scratchpads.newick`

# scripts/mysql #

## bock\_clean.sql ##
MySQL queries that delete bad entries from Phil Bock's `bryozoans` and
`currentspecies`.

## bock\_import.sql ##
MySQL queries for importing Phil Bock's `bryozoans` and `currentspecies` into
the MySQL db from the tab-delimited files in `bock/Jun2010/mysql`.

## bryan\_import.sql ##
MySQL queries for importing Bryan Quach's `bryan_valid`, `bryan_rank`,
and `bryan_invalid` into the MySQL db from the tab-delimited files in
`bryan/sheets/mysql`.

## bryozone\_import.sql ##
MySQL queries for importing Scott Lidgard's `bryozone_*` sheets into the MySQL
db from the tab-delimited files in `bryozone/sheets/mysql`.

## create\_scratchpads.sql ##
MySQL queries for creating the final `scratchpads` table.

## db.sql ##
MySQL query shared by all other sql files, for connecting to a database.

## describe\_scratchpads.sql ##
MySQL queries used to generate [UploadFile](UploadFile.md). The results of the queries will
give you a good idea of the state of the final `scratchpads` table.

## output\_scratchpads.sh ##
Script for dumping the `scratchpads` table into a tab-delimited file.

## select\_scratchpads.sql ##
Used by `output_scratchpads.sh`. Sorts `scratchpads` first by taxonomic rank
and second alphabetically.


---


# scripts/perl #

## itis2.pl ##
Read an ITIS-for-Scratchpads file. Depending on the passed options:

  * Check for valid linkage between taxa.
  * Translate to full hierarchy.
  * Translate to unique hierarchical paths.
  * Translate to dot for GraphViz.
  * Translate to Newick.

To check for valid linkage, I check if the set of all `parent_name` values is
a subset of all `full_name` values. If this is true, then the taxonomy
tree is fully linked together. If false, then some rows point to parents that
do not exist in the tree.

Read the perldoc for details.

## querygni.pl ##
Use the GNI API to grab author names for taxa with missing authors.

## scrapebock.pl ##
Scrape Phil Bock's site http://bryozoa.net/famsys.html and output the full
taxonomy in ITIS-for-Scratchpads format.

## tktree.pl ##
Read an ITIS-for-Scratchpads file. Show a window with the full taxonomy and
expandable parents, just like on Scratchpads.

## tomysql.pl ##
Read a tab-delimited file and change the formatting to aid import into MySQL.
Delete empty lines, mark empty values as `\N`, convert dates from `DD/MM/YYYY`
to `YYYY-MM-DD`.


---


# scripts/php/include #

## connect.php ##
Shared code for connecting to the database.

## scratchpads.php ##
Shared functions for interacting with the table `scratchpads`.


---


# scripts/php #

## bock\_combine.php ##
Combine `bryozoans` and `currentspecies` tables in MySQL db. See [CombineBock](CombineBock.md).

## bryozoans\_parsedetails.php ##
Use after `bock_combine.php`. Parse field `details` in table `bryozoans` and
link synonyms that are unlinked to valid names.

## bryozone2itis.php ##
Read `bryozone_taxa` table in MySQL db and print in ITIS-for-Scratchpads format.

## getgniauthors.php ##
Call `querygni.pl` for taxa that are missing authors and save the results.

## gni\_vs\_bryozone.php ##
Compare authors taken from GNI to authors taken from [Bryozone](Bryozone.md).

## scratchpads\_bryan.php ##
Insert taxonomy from table `bryan_valid` into table `scratchpads`.

## scratchpads\_dummies.php ##
Insert dummy taxa into table `scratchpads`. For example, if there are families
linked to phylum Bryozoa, then make a dummy class to hold all of those
families.

## scratchpads\_gniauthors.php ##
Update table `scratchpads` with authors returned by `getgniauthors.php`.

## scratchpads\_species.php ##
Insert species from table `bryozoans` into table `scratchpads`.