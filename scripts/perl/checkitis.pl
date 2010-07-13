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

sub trim {
  my $string = shift;
  $string =~ s/^\s+//;
  $string =~ s/\s+$//;
  return $string;
}

open(my $file, "<", $ARGV[0]) or die $!;

my %unit_name1;
my %parent_name;
my %full_names;
my @headers;
my $line = 0;
my %parents;
while (<$file>) {
  $line++;
  chomp;
  if ($line == 1) {
    die("Missing header!\n") unless (/unit_name1/ && /unit_name2/
      && /unit_name3/ && /parent_name/ && /rank_name/);
    @headers = split(/\t/);
    next;
  }
  my %values;
  @values{@headers} = split(/\t/);
  my $full_name = trim(join(' ', $values{'unit_name1'}, $values{'unit_name2'}, $values{'unit_name3'}));
  $parents{$full_name} = \%values;
  # count number of times this name appears
  $full_names{$full_name} += 1;
  # record line numbers with this name
  $unit_name1{$values{'unit_name1'}} .= "$line ";
  $parent_name{$values{'parent_name'}} .= "$line ";
}
# check if parent_name is subset of full_names
while ((my $key, my $value) = each(%parent_name)) {
  if (!$full_names{$key}) {
    print("($key) not found in full_names on lines:\n$value\n");
  }
}
# check how many times a full name appears "unit_name1+unit_name2+unit_name3"
while ((my $key, my $value) = each(%full_names)) {
  if ($value > 1) {
    print("($key) appears $value times\n");
  }
}

################################################################################
# print the full hierarchy for every entry
#~ my @full_hierarchy;
#~ foreach my $child (keys %parents) {
  #~ my $path = $parents{$child}->{'rank_name'} . " " . $child;
  #~ while ($parents{$child}->{'parent_name'}) {
    #~ $path = $parents{$parents{$child}->{'parent_name'}}->{'rank_name'} . " " . $parents{$child}->{'parent_name'} . "." . $path;
    #~ $child = $parents{$child}->{'parent_name'};
  #~ }
  #~ #print($path . "\n");
  #~ push(@full_hierarchy, $path);
#~ }
#~ 
#~ @full_hierarchy = sort {length($a) <=> length($b)} @full_hierarchy;
#~ foreach my $path (@full_hierarchy) {
  #~ print($path . "\n");
#~ }

################################################################################
# print the unique paths from Kingdom to Subspecies
my %unique_paths;
foreach my $child (keys %parents) {
  my @path;
  while ($parents{$child}->{'parent_name'}) {
    push(@path, $parents{$child}->{'rank_name'});
    $child = $parents{$child}->{'parent_name'};
  }
  push(@path, $parents{$child}->{'rank_name'});
  $unique_paths{join('.', reverse @path)} += 1;
}
my @sortedkeys = sort {length($a) <=> length($b)} keys %unique_paths;
foreach my $path (@sortedkeys) {
  #print($unique_paths{$path} . "\t" . $path . "\n");
  print($path . "\n");
}
