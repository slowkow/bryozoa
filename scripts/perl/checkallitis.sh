cd ~/Dropbox/fieldmuseum/bryozoa/scripts/perl
./checkitis.pl -o f -r ../mysql/output/scratchpads.tab > output/scratchpads_fullhierarchy.txt
./checkitis.pl -o p ../mysql/output/scratchpads.tab > output/scratchpads_uniquepaths.txt

./checkitis.pl -o f -r ../php/output/bryan_itis.tab > output/bryan_itis_fullhierarchy.txt
./checkitis.pl -o p ../php/output/bryan_itis.tab > output/bryan_itis_uniquepaths.txt

./checkitis.pl -o f -r ../php/output/bryozone_itis.tab > output/bryozone_itis_fullhierarchy.txt
./checkitis.pl -o p ../php/output/bryozone_itis.tab > output/bryozone_itis_uniquepaths.txt
