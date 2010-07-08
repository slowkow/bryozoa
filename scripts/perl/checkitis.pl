#!/usr/bin/perl
# Author       : Kamil Slowikowski <kslowikowski@gmail.com>
# Version      : 0.1
# Date         : July 6, 2010
# Instructions : To use this script, create an ITIS file that can be uploaded
#                to Scratchpads. Run the script on the ITIS file.
# Description  : This script will check if the set of all parent_name values is
#                a subset of all unit_name1 values.
#
# Example      : ./itis2dot.pl bryozone_itis.tab

use strict;
use warnings;

open(my $file, "<", $ARGV[0]) or die $!;

my %unit_name1;
my %parent_name;
my @headers;
my $line = 0;
while (<$file>) {
  $line++;
  chomp;
  if ($line == 1) {
    die("Missing header!\n") unless (/unit_name1/ && /parent_name/);
    @headers = split(/\t/);
    next;
  }
  my %values;
  @values{@headers} = split(/\t/);
  $unit_name1{$values{'unit_name1'}} .= "$line ";
  $parent_name{$values{'parent_name'}} .= "$line ";
}
while ((my $key, my $value) = each(%parent_name)) {
  if (!$unit_name1{$key}) {
    print("(" . $key . ") not found in unit_name1 on $value\n");
  }
}