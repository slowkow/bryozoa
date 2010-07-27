SCRIPTSDIR='/home/kamil/Dropbox/fieldmuseum/bryozoa/scripts'
cd "$SCRIPTSDIR"

################################################################################
# Phil Bock's files
cd "$SCRIPTSDIR/mysql"
echo "Creating MySQL tables and importing Phil Bock's species..."
mysql < bock_import.sql
echo "Cleaning Phil Bock's tables..."
mysql < bock_clean.sql

cd "$SCRIPTSDIR/php"
echo "Combining Phil Bock's tables..."
php bock_combine.php
echo "Parsing Phil Bock's nunc, etiam, vide..."
php bryozoans_parsedetails.php

################################################################################
# Bryan Quach's files
cd "$SCRIPTSDIR/mysql"
echo "Creating MySQL tables and importing Bryan Quach's data..."
mysql < bryan_import.sql

################################################################################
# Bryozone files
cd "$SCRIPTSDIR/mysql"
echo "Creating MySQL tables and importing Bryozone data..."
mysql < bryozone_import.sql

################################################################################
# Scratchpads upload file
cd "$SCRIPTSDIR/mysql"
echo "Creating MySQL scratchpads table..."
mysql < create_scratchpads.sql

cd "$SCRIPTSDIR/php"
echo "Importing Bryan Quach's higher taxonomy into scratchpads table..."
php bryan2itis.php
echo "Importing Phil Bock's species into scratchpads table..."
php species.php
echo "Querying GNI for missing authors..."
php getgniauthors.php
echo "Inserting dummy taxa for unplaced taxa..."
php addunplaced.php
echo "Filling missing authors..."
php bryansetauthors.php

cd "$SCRIPTSDIR/mysql"
echo "Exporting scratchpads table into tab-delimited file..."
./output_scratchpads.sh

cd "$SCRIPTSDIR/perl"
echo "Translating scratchpads file into full hierarchy..."
./checkitis.pl -o f ../mysql/output/scratchpads.tab > output/scratchpads_fullhierarchy.txt
echo "Translating scratchpads file into unique paths file..."
./checkitis.pl -o p ../mysql/output/scratchpads.tab > output/scratchpads_uniquepaths.txt
echo "Checking scratchpads file for proper child-parent linkage..."
./checkitis.pl ../mysql/output/scratchpads.tab
