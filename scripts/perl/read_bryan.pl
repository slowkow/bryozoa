#!/usr/bin/perl
# Author      : Kamil Slowikowski <kslowikowski@gmail.com>
# Version     : 0.1
# Date        : June 15, 2010
# Description : To do...
#

use strict;
use warnings;

#~ use Getopt::Long;
#~ my ($infile, $start, $end, $identity, $length, $download, $upstream);
#~ $upstream = 0;
#~ $identity = 0.95;
#~ $length = 0.5;
#~ GetOptions(
  #~ 'h|help'       => sub { exec('perldoc', $0); exit(0); },
  #~ 'i|infile:s'   => \$infile,
  #~ 's|start:i'    => \$start,
  #~ 'e|end:i'      => \$end,
  #~ 'd|identity:f' => \$identity,
  #~ 'l|length:f'   => \$length,
  #~ 'w|download:i' => \$download,
  #~ 'u|upstream:i' => \$upstream
#~ );

# ranks as an array of hashes
my @ranks = (
    {code => 0, name => "Invalid"},
    {code => 1, name => "Nomen Oblitum"},
    {code => 2, name => "Nomen Nudum"},
    {code => 3, name => "Uncertain Classification"},
    {code => 10, name => "Phylum"},
    {code => 20, name => "Class"},
    {code => 30, name => "Order"},
    {code => 36, name => "Subjective Junior Synonym"},
    {code => 40, name => "Suborder"},
    {code => 50, name => "Infraorder"},
    {code => 60, name => "Grade"},
    {code => 70, name => "Superfamily"},
    {code => 80, name => "Family"},
    {code => 85, name => "Family Synonym"},
    {code => 90, name => "Genus"},
    {code => 95, name => "Genus Synonym"},
    {code => 96, name => "Subjective Junior Synonym"},
    {code => 97, name => "Objective Junior Synonym"},
    {code => 98, name => "Homonym"},
    {code => 100, name => "Subgenus"},
    {code => 110, name => "Species"},
    {code => 113, name => "Uncertain Species"},
    {code => 115, name => "Species Synonym"},
    {code => 116, name => "Subjective Junior Synonym"},
    {code => 117, name => "Objective Junior Synonym"},
    {code => 118, name => "Homonym"},
    {code => 99999, name => "Error"}
  );

# input a rank code
# output the array index of the next highest rank
sub getParentRank {
  my $child = shift;
  for (my $i = 1; $i <= $#ranks; $i++) {
    if ($ranks[$i]{code} == $child) {
      return $i - 1;
    }
  }
  die "No parent found for code $child!\n";
}

# hard-coded paths to files
my $bryan_valid_file = "/home/kamil/Dropbox/fieldmuseum/kamil/bryan/sheets/valid.txt";
my $bryozone_taxa_file = "/home/kamil/Dropbox/fieldmuseum/kamil/bryozone/sheets/taxa.txt";

open(my $bryan_valid, "<", $bryan_valid_file) or die $!;

my @taxa = ();

my $line = 0;
while (<$bryan_valid>) {
  # skip comments and blank lines
  next if /^#/ or /^$/;
  $line++;
  
  # skip header
  next if $line == 1;
  
  # collect fields
  my ($oldid, $oldpid, $name, $rank, $date, $id, $pid) = split(/\t/);
  
  # incomplete record
  unless ($name && $rank && $date && $id && $pid) {
    unless ($rank == 110) {
      print;
    }
  }
  
  push(@taxa, {name => $name, rank => $rank, id => $id, pid => $pid});
}

# input child taxon name
# output parent taxon name
sub getParentTaxon {
  my $child = shift;
  my $parentid;
  my $parentrank;
  foreach my $taxon (@taxa) {
    if ($taxon->{name} eq $child) {
      $parentid = $taxon->{pid};
      $parentrank = getParentRank($taxon->{rank});
      last;
    }
  }
  foreach my $rank (@ranks) {
    next if $rank->{code} > $parentrank;
    
    foreach my $taxon (@taxa) {
      if ($taxon->{rank} == $rank->{code} && $taxon->{pid} == $parentid) {
        print $child . " may be child of " . $rank->{name} . " " . $taxon->{name} . "\n";
      }
    }
  }
}

getParentTaxon("stolella");

#~ open(my $bryozone_taxa, "<", $bryozone_taxa_file) or die $!;
#~ 
#~ my $matches = 0;
#~ $line = 0;
#~ while (<$bryozone_taxa>) {
  #~ # skip comments
  #~ next if /^#/;
  #~ 
  #~ # count non-comment lines
  #~ $line++;
  #~ 
  #~ # skip rows without enough fields
  #~ my @fields = split(/\t/);
  #~ next if $#fields < 3;
  #~ 
  #~ # skip header
  #~ next if $line == 1;
  #~ 
  #~ # taxon name is 3rd column, rank is 4th column
  #~ my $name = $fields[2];
  #~ my $rank = int($fields[3]);
  #~ 
  #~ # skip non-species
  #~ next unless ($rank == 110);
  #~ 
  #~ # count occurrences of this name
  #~ if ($species{$name}) {
    #~ $matches += 1;
    #~ print($name . "\n");
  #~ }
#~ }
#~ 
#~ print(keys(%species)
  #~ . " species in valid.txt, " . $matches . " found in taxa.txt\n");
