#!/usr/bin/perl
# Author       : Kamil Slowikowski <kslowikowski@gmail.com>
# Version      : 0.2
# Date         : June 23, 2010
# Instructions : To use this script, export a tab-delimited file from FileMaker
#                and run this script on the tab-delimited file.
# Description  : This script will
#                  * remove empty lines
#                  * mark empty values as \N
#                  * convert dates from DD/MM/YYYY to YYYY-MM-DD
#

use strict;
use warnings;

open(my $file, "<", $ARGV[0]) or die $!;

while (<$file>) {
  # skip empty lines
  next if /^\s*$/;
  # empty values should be marked \N
  while (s/\t\t/\t\\N\t/g) {};
  # dates should be YYYY-MM-DD
  #while (s@\t(\d{1,2})/(\d{1,2})/(\d{2,4})\t@\t$3-$2-$1\t@g) {};
  while (m@\t(\d{1,2})/(\d{1,2})/(\d{2,4})\t@g) {
    # catch explicit errors
    if ($2 > 12) {
      s@\t(\d{1,2})/(\d{1,2})/(\d{2,4})\t@\t$3-$1-$2\t@;
    }
    else {
      s@\t(\d{1,2})/(\d{1,2})/(\d{2,4})\t@\t$3-$2-$1\t@;
    }
  };
  print;
}
