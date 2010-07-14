cd ~/Dropbox/fieldmuseum/bryozoa/scripts/mysql
mysql < select_scratchpads.sql > output/scratchpads.tab
sed -i 's/NULL//g' output/scratchpads.tab