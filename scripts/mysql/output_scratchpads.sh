mysql < select_scratchpads.sql > output/scratchpads.tab
sed -i 's/NULL//g' output/scratchpads.tab