#!/usr/bin/perl
# Author      : Kamil Slowikowski <kslowikowski@gmail.com>
# Date        : July 16, 2010
# Description : Query GNI and try to get the best author/year.
# Notes       : Please read http://wiki.github.com/dimus/gni/api

use strict;
use warnings;
use XML::Simple qw(:strict);
use LWP::Simple;
use URI::Escape;
use Getopt::Long;

# set default web service parameters
my %params = (
  search_term => ''     # query
  ,per_page   => '1000' # number of results per page
  ,page       => 1      # which page to display
);

# grab commandline options from the user
GetOptions(
  'h|help'        => sub { exec('perldoc', $0); exit(0); }
  ,'n|per_page:i' => \$params{per_page}
  ,'p|page:i'     => \$params{page}
);

# grab the user's query from the command prompt
# it must be properly capitalized
$params{search_term} = join(' ', @ARGV) || die "Please input a query";

# grab the xml
my $xml = get("http://gni.globalnames.org/name_strings.xml?"
  . "search_term=" . uri_escape($params{search_term})
  . "&per_page=" . $params{per_page}
  . "&page=" . $params{page});

$params{search_term} =~ s/(?:\*|ns:|can:|uni:|gen:|sp:|ssp:|au:|yr:)//g;

# DEBUG use a local file for testing
#~ my $xml = '/home/kamil/Downloads/name_strings.xml';
# die if we can't get the xml
die "Failed to retrieve the result!" unless defined $xml;

# put the xml in a perl data structure
$xml = XMLin($xml, ForceArray => ['name_string'], KeyAttr => []);

# DEBUG display the data structure
#~ use Data::Dumper;
#~ print Dumper($xml);

# grab the array of actual results from the xml
my $allresults = $xml->{name_strings}->{name_string};

# DEBUG print all results with a year
#~ foreach my $result (@{$allresults}) {
  #~ # skip results without a year
  #~ next unless $result->{name} =~ /\d{4}/;
  #~ 
  #~ print $result->{name} . "\n";
#~ }
#~ print "----\n";

# our query
my $name;
# resulting authors
my @authors;
# save only the results that seem to have what we're looking for
foreach my $result (@{$allresults}) {
  # skip results without a year
  next unless $result->{name} =~ /\d{4}/;
  # first word assumed to be name, rest is author
  $result->{name} =~ /^(.+?)\s+(.+)$/;
  # if we have an author, save it
  if ($2) {
    #print $1 . "\t" . $2 . "\n";
    $name ||= $1;
    push(@authors, $2);
  }
}

# input a list of Author Year entries
# output a list with the duplicates removed
# we prefer to keep the entries with symbols like "," or "&"
sub filterDuplicates {
  my $array = shift;
  my @in = @{$array};
  my @out;
  for my $a (@in) {
    my $keep = 1;
    # compare every pair of values
    for my $b (@in) {
      next if $a eq $b;
      # compare the values without non-alpha characters
      my ($aw, $bw) = ($a, $b);
      $aw =~ s/\W//g;
      $bw =~ s/\W//g;
      # if they're the same without non-alpha characters, we want the longer one
      # for example, we prefer "Smitt, 1867" to "Smitt 1867"
      if ($aw eq $bw && length($a) < length($b)) {
        # print "Prefer '$b' over '$a'\n";
        $keep = 0;
      }
    }
    if ($keep) {
      push(@out, $a);
    }
  }
  return @out;
}

# input a list of Author Year entries
# output a list with only the entries that contain the minimum year
sub filterMinYear {
  my $array = shift;
  my @in = @{$array};
  my @out;
  # find the minimum year
  my $minyear;
  foreach (@in) {
    /(\d{4})/;
    if    (!$minyear)     { $minyear = $1; }
    elsif ($1 < $minyear) { $minyear = $1; }
  }
  return grep(/$minyear/, @in);
}

@authors = filterDuplicates(\@authors);
@authors = filterMinYear(\@authors);

foreach my $author (@authors) {
  print $name . "\t" . $author . "\n";
}
