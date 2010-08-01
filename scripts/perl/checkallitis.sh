echo "Translating scratchpads to newick..."
./itis2.pl -o n ../mysql/output/scratchpads.tab > output/scratchpads.newick
echo "Translating scratchpads to full hierarchy..."
./itis2.pl -o f -r ../mysql/output/scratchpads.tab > output/scratchpads_fullhierarchy.txt
echo "Translating scratchpads to unique paths..."
./itis2.pl -o p ../mysql/output/scratchpads.tab > output/scratchpads_uniquepaths.txt

echo "Translating bryan to full hierarchy..."
./itis2.pl -o f -r ../php/output/bryan.tab > output/bryan_fullhierarchy.txt
echo "Translating bryan to unique paths..."
./itis2.pl -o p ../php/output/bryan.tab > output/bryan_uniquepaths.txt

echo "Translating bryozone to full hierarchy..."
./itis2.pl -o f -r ../php/output/bryozone.tab > output/bryozone_fullhierarchy.txt
echo "Translating bryozone to unique paths..."
./itis2.pl -o p ../php/output/bryozone.tab > output/bryozone_uniquepaths.txt
