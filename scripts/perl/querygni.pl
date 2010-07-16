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
  search_term => ''   # query
  ,per_page => '1000' # number of results per page
  ,page => 1          # which page to display
);

# grab commandline options from the user
GetOptions(
  'h|help'       => sub { exec('perldoc', $0); exit(0); }
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

# DEBUG use a local file for testing
#~ my $xml = '/home/kamil/Downloads/name_strings.xml';
# die if we can't get the xml
die "Failed to retrieve the result!" unless defined $xml;

# put the xml in a perl data structure
$xml = XMLin(
  $xml
  ,ForceArray => ['name_string']
  ,KeyAttr => []
);

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

# print only the results that seem to have what we're looking for
foreach my $result (@{$allresults}) {
  # skip results without a year
  next unless $result->{name} =~ /\d{4}/;
  
  #~ $result->{name} =~ /^\s*${query}\s+(\(?\s*[A-Z]\w+.+\d{4}\s*\)?).*$/;
  $result->{name} =~
    /^            # start of string
      \s*         # any whitespace
      (           # start capture group, query
        (?i)      #   turn off case sensitivity
        $params{search_term}    #   our query
        (?-i)     #   turn on case sensitivity
      )           # end capture group
      \s+         # at least one whitespace
      (           # start capture group, author and year
        \(?       #   optional start paren
        \s*       #   optional whitespace after start paren
        [A-Z]\w+  #   a word with a capital first letter, maybe first author
        .+        #   anything, maybe additional authors or commas or &'s
        \d{4}     #   year
        \s*       #   optional whitespace before end paren
        \)?       #   optional end paren
      )           # end capture group
      .*          # anything, then the string ends
    $/xo;
  
  # if we got an author and year, print the name and the author and year
  if ($2) { print $1 . "\t" . $2 . "\n"; }
}
