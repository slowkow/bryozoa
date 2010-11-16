SCRIPTSDIR=`pwd`

################################################################################
# prepare the MySQL tables

# put Phil Bock's data into MySQL tables, clean it, parse it
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

# put Bryan and Bryozone data into MySQL tables
cd "$SCRIPTSDIR/mysql"
echo "Creating MySQL tables and importing Bryan Quach's data..."
mysql < bryan_import.sql
echo "Creating MySQL tables and importing Bryozone data..."
mysql < bryozone_import.sql

echo "Creating MySQL scratchpads table..."
mysql < create_scratchpads.sql

################################################################################
# use the prepared MySQL tables

cd "$SCRIPTSDIR/php"
echo "Importing Bryan Quach's higher taxonomy into scratchpads table..."
php scratchpads_bryan.php

# insert higher taxa synonyms
cd "$SCRIPTSDIR/php"
echo "Inserting higher taxa synonyms from bryan_invalid table into scratchpads table..."
php scratchpads_higher_synonyms.php

# make the Bryan tab-delimited file
cd "$SCRIPTSDIR/mysql"
echo "Exporting scratchpads table into tab-delimited file..."
./output_scratchpads.sh
mv output/scratchpads.tab output/bryan.tab
# translate Bryan tab-delimited file into a full hierarchy and unique paths
cd "$SCRIPTSDIR/perl"
echo "Translating scratchpads file into full hierarchy file for Bryan's taxonomy..."
./itis2.pl -o f ../mysql/output/bryan.tab > output/bryan_fullhierarchy.txt
echo "Translating scratchpads file into unique paths file for Bryan's taxonomy..."
./itis2.pl -o p ../mysql/output/bryan.tab > output/bryan_uniquepaths.txt

# make the Bryozone tab-delimited file
#~ cd "$SCRIPTSDIR/php"
#~ echo "Creating tab-delimited file for Bryozone taxonomy..."
#~ php bryozone2itis.php > output/bryozone.tab
#~ # translate Bryozone tab-delimited file into a full hierarchy and unique paths
#~ cd "$SCRIPTSDIR/perl"
#~ echo "Translating Bryozone file into full hierarchy..."
#~ ./itis2.pl -o f ../php/output/bryozone.tab > output/bryozone_fullhierarchy.txt
#~ echo "Translating Bryozone file into unique paths..."
#~ ./itis2.pl -o p ../php/output/bryozone.tab > output/bryozone_uniquepaths.txt

cd "$SCRIPTSDIR/php"
echo "Importing Phil Bock's species into scratchpads table..."
php scratchpads_species.php

# delete 'Bock 1999' before querying GNI for authors
cd "$SCRIPTSDIR/mysql"
echo "Replacing 'Bock 1999' with NULL in scratchpads table..."
mysql < delete_bock1999.sql

cd "$SCRIPTSDIR/php"
# find authors via GNI
echo "Querying GNI for entries in scratchpads table with missing authors..."
php getgniauthors.php
echo "Inserting dummy taxa for unplaced taxa into scratchpads table..."
php scratchpads_dummies.php
echo "Inserting GNI authors into scratchpads table..."
php scratchpads_gniauthors.php

# make the final tab-delimited file
cd "$SCRIPTSDIR/mysql"
echo "Exporting scratchpads table into tab-delimited file..."
./output_scratchpads.sh
# translate the final tab-delimited file into various formats
cd "$SCRIPTSDIR/perl"
echo "Checking scratchpads file for proper child-parent linkage..."
./itis2.pl ../mysql/output/scratchpads.tab
echo "Translating scratchpads file into full hierarchy..."
./itis2.pl -o f ../mysql/output/scratchpads.tab > output/scratchpads_fullhierarchy.txt
echo "Translating scratchpads file into unique paths file..."
./itis2.pl -o p ../mysql/output/scratchpads.tab > output/scratchpads_uniquepaths.txt
echo "Translating scratchpads file into newick..."
./itis2.pl -o n ../mysql/output/scratchpads.tab > output/scratchpads.newick
