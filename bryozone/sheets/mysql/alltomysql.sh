for file in *; do perl ../../scripts/perl/tomysql.pl $file > ./mysql/$file; done
