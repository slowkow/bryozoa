#!/usr/bin/perl
# Author       : Kamil Slowikowski <kslowikowski@gmail.com>
# Version      : 0.1
# Date         : July 6, 2010
# Instructions : To use this script, create an ITIS file that can be uploaded
#                to Scratchpads. Run the script on the ITIS file.
# Description  : This script will construct a dot hierarchy from the ITIS file.
#
# Example      : ./itis2dot.pl ../php/output/bryozone_itis.tab > bryozone_itis.gv
#                dot -O -Tpng bryozone_itis.gv

use strict;
use warnings;
use GraphViz;

sub trim {
  my $string = shift;
  $string =~ s/^\s+//;
  $string =~ s/\s+$//;
  return $string;
}

open(my $file, "<", $ARGV[0]) or die $!;

my $g = GraphViz->new(
  layout => 'dot'
  , ratio => 'compress'
  , rankdir => 1
);

my $line = 0;
my @headers;
my %ranks;
while (<$file>) {
  $line++;
  chomp;
  if ($line == 1) {
    die("Missing header!\n") unless (/unit_name1/ && /unit_name2/ && /rank_name/ && /parent_name/ && /usage/);
    @headers = split(/\t/);
    next;
  }
  my %values;
  @values{@headers} = split(/\t/);
  
  my $child  = trim(join(" ", $values{'unit_name1'}, $values{'unit_name2'}));
  my $parent = trim($values{'parent_name'});
  my $rank   = trim($values{'rank_name'});
  
  # skip the ranks that have thousands of members
  next if ($rank =~ /(Species|Genus|Family)/);
  $ranks{$rank} += 1;
  
  #~ print("$parent_name -> $child_name\n");
  if (length($child) > 1) {
    $g->add_node($child, rank => $rank);
    if (length($parent) > 1) {
      $g->add_edge($parent => $child);
    }
  }
}

print $g->as_debug;
#~ while ((my $key, my $value) = each(%ranks)) {
  #~ print($key . ", " . $value . "\n");
#~ }