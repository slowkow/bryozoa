cd '/home/kamil/Dropbox/fieldmuseum/bryozoa/scripts'

################################################################################
# work on Phil Bock's files
cd 'mysql'
echo "Creating MySQL tables and importing Phil Bock's species..."
mysql < bock_import.sql
mysql < bock_clean.sql

cd '../php'
echo "Combining Phil Bock's tables..."
php bock_combine.php
echo "Parsing Phil Bock's nunc, etiam, vide..."
php bryozoans_parsedetails.php

exit
################################################################################
# work on the upload file
cd '../mysql'
echo "Creating MySQL scratchpads table..."
mysql < create_scratchpads.sql

cd '../php'
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

cd '../mysql'
echo "Exporting scratchpads table into tab-delimited file..."
./output_scratchpads.sh

cd '../perl'
echo "Translating scratchpads file into full hierarchy..."
./checkitis.pl -o f ../mysql/output/scratchpads.tab > output/scratchpads_fullhierarchy.txt
echo "Translating scratchpads file into unique paths file..."
./checkitis.pl -o p ../mysql/output/scratchpads.tab > output/scratchpads_uniquepaths.txt
echo "Checking scratchpads file for proper child-parent linkage..."
./checkitis.pl ../mysql/output/scratchpads.tab
