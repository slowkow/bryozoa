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

# command line options for filtering by min year and removing duplicates
my ($minyear, $duplicates, $lowercase);

# grab commandline options from the user
GetOptions(
  'h|help'        => sub { exec('perldoc', $0); exit(0); }
  ,'n|per_page:i' => \$params{per_page}
  ,'p|page:i'     => \$params{page}
  ,'m|minyear'    => \$minyear
  ,'d|duplicates' => \$duplicates
  ,'l|lowercase' => \$lowercase
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

# remove entries that start with lowercase words
sub filterLowerCaseWords {
  my $array = shift;
  my @in = @{$array};
  # 4, because there is "d'Orbigny", "de Gregorio", "von Hagenow", "van Beneden"
  return grep(!/^[a-z]{4}/, @in);
}

# input a list of Author Year entries
# output a list with only the entries that contain the minimum year
sub filterMinYear {
  my $array = shift;
  my @in = @{$array};
  my @out;
  # find the minimum year
  my $min;
  foreach (@in) {
    /(\d{4})/;
    if    (!$min)     { $min = $1; }
    elsif ($1 < $min) { $min = $1; }
  }
  return grep(/$min/, @in);
}

@authors = filterDuplicates(\@authors) if $duplicates;
@authors = filterLowerCaseWords(\@authors) if $lowercase;
@authors = filterMinYear(\@authors) if $minyear;

foreach my $author (@authors) {
  print $name . "\t" . $author . "\n";
}

__END__

=head1 NAME

querygni.pl

=head1 USAGE

./querygni.pl Homo sapiens
./querygni.pl -l -n 100 -m uni:Chilopora
./querygni.pl -d -l -m uni:Chilopora

=head1 OPTIONS

 -h --help        Show this help.
 
 GNI Options
 -n --per_page    Default 1000. Number of results per page.
 -p --page        Default 1. Page number in multipage results.
 
 Filter Options (applied in this order)
 -d --duplicates  Remove duplicates, prefer names with symbols like , & () [].
 -l --lowercase   Remove authors that start with lowercase names (subspecies).
 -m --minyear     Return entries only with the oldest available year.

=head1 DESCRIPTION

Query GNI (http://gni.globalnames.org) and try to get the best author/year.
See http://wiki.github.com/dimus/gni/api

=head1 AUTHOR

Kamil Slowikowski, kslowikowski-at-gmail-dot-com

=cut