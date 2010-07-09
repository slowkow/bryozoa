#!/usr/bin/perl
# Author       : Kamil Slowikowski <kslowikowski@gmail.com>
# Version      : 0.1
# Date         : July 6, 2010
# Instructions : To use this script, create an ITIS file that can be uploaded
#                to Scratchpads. Run the script on the ITIS file.
# Description  : This script will check if the set of all parent_name values is
#                a subset of all unit_name1 values.
#                It will also check if any full name appears more than once.
#                A full name is unit_name1 unit_name2
#
# Example      : ./itis2dot.pl bryozone_itis.tab

use strict;
use warnings;

open(my $file, "<", $ARGV[0]) or die $!;

my %unit_name1;
my %parent_name;
my %full_names;
my @headers;
my $line = 0;
while (<$file>) {
  $line++;
  chomp;
  if ($line == 1) {
    die("Missing header!\n") unless (/unit_name1/ && /unit_name2/ && /unit_name3/ && /parent_name/);
    @headers = split(/\t/);
    next;
  }
  my %values;
  @values{@headers} = split(/\t/);
  my $full_name = join(' ', $values{'unit_name1'}, $values{'unit_name2'}, $values{'unit_name3'});
  # count number of times this name appears
  $full_names{$full_name} += 1;
  # record line numbers with this name
  $unit_name1{$values{'unit_name1'}} .= "$line ";
  $parent_name{$values{'parent_name'}} .= "$line ";
}
# check if parent_name is subset of unit_name1
while ((my $key, my $value) = each(%parent_name)) {
  if (!$unit_name1{$key}) {
    print("($key) not found in unit_name1 on lines:\n$value\n");
  }
}
while ((my $key, my $value) = each(%full_names)) {
  if ($value > 1) {
    print("($key) appears $value times\n");
  }
}