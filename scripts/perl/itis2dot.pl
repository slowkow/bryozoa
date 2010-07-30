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
use Getopt::Long;

# used for comparing rank names
my %ranksort = (
  'kingdom'     =>  1,
  'subkingdom'  =>  2,
  'phylum'      =>  3,
  'subphylum'   =>  4,
  'superclass'  =>  5,
  'class'       =>  6,
  'subclass'    =>  7,
  'infraclass'  =>  8,
  'superorder'  =>  9,
  'order'       => 10,
  'suborder'    => 11,
  'infraorder'  => 12,
  'superfamily' => 13,
  'family'      => 14,
  'subfamily'   => 15,
  'tribe'       => 16,
  'subtribe'    => 17,
  'genus'       => 18,
  'subgenus'    => 19,
  'species'     => 20,
  'subspecies'  => 21
);

# die with some help if the user doesn't provide any arguments
$ARGV[0] or die
"Usage: itis2dot.pl [OPTION]... [FILE]\
Try 'itis2dot.pl --help' for more information.\n";

# get some command-line options
my ($lowestrank, $withtext, $withauthor, $withrank, $output, $funkycolors);
GetOptions(
  'h|help'            => sub { exec('perldoc', $0); exit(0); }
  , 'l|lowestrank:s'  => \$lowestrank
  , 't|withtext'      => \$withtext
  , 'a|withauthor'    => \$withauthor
  , 'r|withrank'      => \$withrank
  , 'o|output:s'      => \$output
  , 'f|funkycolors'   => \$funkycolors
);

# set to subspecies if not specified or not a valid rank
$lowestrank = $lowestrank ? lc $lowestrank : 'subspecies';
die "Invalid rank '$lowestrank'.\n" unless $ranksort{$lowestrank};

# default to dot output
$output ||= 'dot';

# Remove preceding or trailing whitespace.
sub trim {
  my $string = shift;
  $string =~ s/^\s+//;
  $string =~ s/\s+$//;
  return $string;
}

# used for assigning node colors for GraphViz
my %rankcolor = (
  'kingdom'     => 'blue1',
  'subkingdom'  => 'aquamarine4',
  'phylum'      => 'brown',
  'subphylum'   => 'burlywood',
  'superclass'  => 'cadetblue',
  'class'       => 'chartreuse',
  'subclass'    => 'chocolate',
  'infraclass'  => 'darkgoldenrod3',
  'superorder'  => 'cyan3',
  'order'       => 'red',
  'suborder'    => 'indigo',
  'infraorder'  => 'hotpink',
  'superfamily' => 'khaki3',
  'family'      => 'seagreen1',
  'subfamily'   => 'sienna1',
  'tribe'       => 'springgreen',
  'subtribe'    => 'steelblue',
  'genus'       => 'peru',
  'subgenus'    => 'palegreen1',
  'species'     => 'rosybrown3',
  'subspecies'  => 'purple3'
);

# open with latin1 encoding to make Perl stop complaining
open(my $file, '<:encoding(latin1)', $ARGV[0]) or die $!;

# create a GraphViz object with some options
my $g = GraphViz->new(
  layout => 'dot'
  , ratio => 'compress'
  , splines => 'true'
  , overlap_scaling => 150
  , overlap => 'prism1000'
  , rankdir => 1
);

my $line = 0;
my @headers;
while (<$file>) {
  $line++;
  chomp;
  if ($line == 1) {
    warn("Missing header!\n") unless (/unit_name1/ && /unit_name2/
      && /unit_name3/ && /rank_name/ && /parent_name/ && /taxon_author/);
    @headers = split(/\t/);
    next;
  }
  # put the row's values into a hash with headers as keys
  my %values;
  @values{@headers} = split(/\t/);
  
  # concatenate the full name
  my $child  = trim(join(' ', $values{'unit_name1'},
    $values{'unit_name2'} || '', $values{'unit_name3'} || ''));
  # append the author name if the user wants it
  if ($withauthor) {
    $child = trim(join(' ', $child, $values{'taxon_author'} || ''));
  }
  my $parent = trim($values{'parent_name'});
  # remove the author unless the user wants it
  unless ($withauthor) {
    $parent =~ s/^(.+?\b).*/$1/;
  }
  my $rank = lc trim($values{'rank_name'});
  
  # skip the ranks below user-specified lowest rank
  next if $ranksort{$rank} > $ranksort{$lowestrank};
  
  if (length($child) > 1) {
    $g->add_node($child
      , rank  => $rank
      , style => 'filled'
      # make each rank a different color
      , color => $funkycolors ? $rankcolor{$rank} : 'skyblue'
      # put text in each node
      , label => ($withtext ? ($withrank ? $rank . ' ' : '') . $child : '')
    );
    if (length($parent) > 1) {
      $g->add_edge($parent => $child);
    }
  }
}

if ($output =~ /^do?t?$/) {
  print $g->as_debug;
}
elsif ($output =~ /^pn?g?$/) {
  $g->as_png($lowestrank
    . ($withtext    ? '_withtext'    : '')
    . ($withrank    ? '_withrank'    : '')
    . ($funkycolors ? '_funkycolors' : '')
    . '.png');
}
elsif ($output =~ /^jp?e?g?$/) {
  $g->as_jpeg($lowestrank
    . ($withtext    ? '_withtext'    : '')
    . ($withrank    ? '_withrank'    : '')
    . ($funkycolors ? '_funkycolors' : '')
    . '.jpg');
}

__END__

=head1 NAME

itis2dot.pl

Read an ITIS-for-Scratchpads file and output a dot file for GraphViz.

=head1 USAGE

 # print dot code
 itis2dot.pl itisfile.tab

 # print dot code for entries from highest rank to Order, inclusive
 itis2dot.pl -l order itisfile.tab

 # print dot code with text labels on the nodes
 itis2dot.pl -t itisfile.tab

 # print dot code with text labels on the nodes, including author in each name
 itis2dot.pl -t -a itisfile.tab

 # produce a png file with node colors that depend on rank
 itis2dot.pl -o p -f itisfile.tab

 # produce a jpg file with all nodes colored skyblue
 itis2dot.pl -o jpg itisfile.tab
 itis2dot.pl --output jpeg itisfile.tab

=head1 OPTIONS

 -h --help         Show this help.
 -o --output       Choose an output:

    dot    Default. Print a dot representation of the file.
    png    Skip printing dot, just output a png file.
    jpeg   Output a jpeg file.

 -l --lowestrank   Include ranks from highest rank down to this rank.
 -f --funkycolors  The color of each node depends on its rank.
 -a --withauthor   Include author in each taxon's name.
 -t --withtext     Display text labels on each node in the graph.
 -r --withrank     Prepend rank name to each taxon's name.

=head1 DESCRIPTION

Read an ITIS-for-Scratchpads file.

By default, convert the parent-child relation into a dot representation of the
file ready for drawing with GraphViz.

Set --option to png or jpeg to directly produce an image rather than printing
the dot code.

=head1 AUTHOR

Kamil Slowikowski, kslowikowski-at-gmail-dot-com

=cut