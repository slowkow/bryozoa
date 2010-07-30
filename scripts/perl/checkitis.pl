#!/usr/bin/perl

use strict;
use warnings;
use Getopt::Long;
use Data::Dumper;

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

$ARGV[0] or die
"Usage: checkitis.pl [OPTION]... [FILE]\
Try 'checkitis.pl --help' for more information.\n";

my ($output, $withrank, $withauthor, $lowestrank, $graphviz, $withtext);
GetOptions(
  'h|help'            => sub { exec('perldoc', $0); exit(0); }
  , 'o|output:s'      => \$output
  , 'r|withrank'      => \$withrank
  , 'a|withauthor'    => \$withauthor
  , 'l|lowestrank:s'  => \$lowestrank
  , 'g|graphviz:s'    => \$graphviz
  , 't|withtext'      => \$withtext
);

# set to subspecies if not specified or not a valid rank
$lowestrank = $lowestrank ? lc $lowestrank : 'subspecies';
die "Invalid rank '$lowestrank'.\n" unless $ranksort{$lowestrank};

$output ||= 'check';

# Remove preceding and trailing whitespace.
sub trim {
  my $string = shift;
  $string =~ s/^\s+//;
  $string =~ s/\s+$//;
  return $string;
}

# open the user-specified file
open(my $file, "<", $ARGV[0]) or die $!;

# slurp the whole file into rows
my %rows;
# check - record line numbers where parent name is used
my %parent_name;
# check - record number of times full name is used
my %full_names;
# newick - record the children of every taxon
my %children;
# save headers for use in a hash
my @headers;
my $line = 0;
while (<$file>) {
  $line++;
  chomp;
  if ($line == 1) {
    warn("Missing some headers!\n") unless (/unit_name1/ && /unit_name2/
      && /unit_name3/ && /parent_name/ && /rank_name/ && /taxon_author/);
    # save the headers for the hash
    @headers = split(/\t/);
    next;
  }
  # put the row's values into a hash with headers as keys
  my %values;
  @values{@headers} = split(/\t/);
  
  # a full name is the concatenation of all unit names
  my $full_name = trim(join(' ', $values{'unit_name1'},
    $values{'unit_name2'} || '', $values{'unit_name3'} || ''));
  # append the author if the user wants it
  if ($withauthor) {
    $full_name = trim(join(' ', $full_name, $values{'taxon_author'} || ''));
  }
  # remove author name from parent_name unless the user wants it
  unless ($withauthor) {
    $values{'parent_name'} =~ s/^(.+?\b).*/$1/;
  }
  # the whole file is slurped into the rows hash
  $rows{$full_name} = \%values;
  if ($output =~ /^ch?e?c?k?$/i) {
    # count number of times this full name appears
    $full_names{$full_name} += 1;
    # record line numbers with this name
    $parent_name{$values{'parent_name'}} .= "$line ";
  }
  elsif ($output =~ /^ne?w?i?c?k?$/i) {
    push(@{$children{$values{'parent_name'}}}, $full_name);
  }
}
################################################################################
# check file for validity
if ($output =~ /^ch?e?c?k?$/i) {
  # check if parent_name is subset of full_names
  while ((my $key, my $value) = each(%parent_name)) {
    if (!$full_names{$key}) {
      print("parent_name ($key) not found in full names on lines: $value\n");
    }
  }
  # check how many times a full name appears "unit_name1+unit_name2+unit_name3"
  while ((my $key, my $value) = each(%full_names)) {
    if ($value > 1) {
      print("full name ($key) appears $value times\n");
    }
  }
}
################################################################################
# print the full hierarchy for every entry
elsif ($output =~ /^fu?l?l?h?i?e?r?a?r?c?h?y?$/i) {
  my @full_hierarchy;
  foreach my $child (keys %rows) {
    next if $ranksort{lc $rows{$child}->{'rank_name'}} > $ranksort{$lowestrank};
    
    my @path;
    # get all of the parents for this child
    push(@path, ($withrank ? $rows{$child}->{'rank_name'} . ' ' : '') . $child);
    
    while ($rows{$child}->{'parent_name'}) {
      push(@path,
        ($withrank ?
          $rows{$rows{$child}->{'parent_name'}}->{'rank_name'} . ' '
          : '')
        . $rows{$child}->{'parent_name'});
      $child = $rows{$child}->{'parent_name'};
    }
    # save the full path for this child
    push(@full_hierarchy, join('@', reverse @path));
  }
  # sort the paths before printing by
  #   desc length of child path, alphabetically by child name
  sub mysort {
    my $acount = ($a =~ tr/@//);
    my $bcount = ($b =~ tr/@//);
    if ($acount != $bcount) { return $acount <=> $bcount; }
    my @aa = split(/@/, reverse($a));
    my @bb = split(/@/, reverse($b));
    return reverse($aa[0]) cmp reverse($bb[0]);
  }
  @full_hierarchy = sort mysort @full_hierarchy;
  foreach my $path (@full_hierarchy) {
    print($path . "\n");
  }
}
################################################################################
# print the unique paths from Kingdom to lowest rank specified
elsif ($output =~ /^pa?t?h?s?$/i) {
  my %unique_paths;
  foreach my $child (keys %rows) {
    next if $ranksort{lc $rows{$child}->{'rank_name'}} > $ranksort{$lowestrank};
    
    my @path;
    while ($rows{$child}->{'parent_name'}) {
      push(@path, $rows{$child}->{'rank_name'});
      $child = $rows{$child}->{'parent_name'};
    }
    push(@path, $rows{$child}->{'rank_name'});
    $unique_paths{join('@', reverse @path)} += 1;
  }
  my @sortedkeys = sort { length $a <=> length $b } keys %unique_paths;
  foreach my $path (@sortedkeys) {
    # print the number of times that a path is used
    #print($unique_paths{$path} . "\t" . $path . "\n");
    print($path . "\n");
  }
}
################################################################################
# print newick format
elsif ($output =~ /^ne?w?i?c?k?$/i) {
  use Bio::Taxon;
  use Bio::Tree::Tree;
  use Bio::TreeIO;
  
  # get the root
  my @allchildren = keys %rows;
  my $somechild   = $allchildren[0];
  my $root;
  while ($rows{$somechild}->{'parent_name'}) {
    $root      = $rows{$somechild}->{'parent_name'};
    $somechild = $root;
  }
  
  # create the root
  my $root_taxon = Bio::Taxon->new(  -name => $root
                                   , -id   => $root
                                   , -rank => $rows{$root}->{'rank_name'}
                                  );
  addChildrenTaxa($root_taxon);
  
  # make a tree
  my $tree = Bio::Tree::Tree->new(-root => $root_taxon);
  
  # print the tree
  my $treeio = Bio::TreeIO->new(-format => 'newick');
  $treeio->write_tree($tree);
  
  # recursive subroutine to add all children to the tree
  sub addChildrenTaxa {
    my $parent_taxon = shift;
    my $parent_name  = $parent_taxon->id();
    foreach my $child (@{$children{$parent_name}}) {
      next if $ranksort{lc $rows{$child}->{'rank_name'}} > $ranksort{$lowestrank};
      my $child_taxon = Bio::Taxon->new(  -name => $child
                                        , -id   => $child
                                        , -rank => $rows{$child}->{'rank_name'}
                                       );
      $parent_taxon->add_Descendent($child_taxon);
      addChildrenTaxa($child_taxon);
    }
  }
}
################################################################################
# print dot format for GraphViz
elsif ($output =~ /^gr?a?p?h?v?i?z?$/i) {
  use GraphViz;
  
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
  
  # create a GraphViz object with some options
  my $g = GraphViz->new(
    layout => 'dot'
    , ratio => 'compress'
    , splines => 'true'
    , overlap_scaling => 150
    , overlap => 'prism1000'
    , rankdir => 1
  );
  
  foreach my $child (keys %rows) {
    my $rank   = $rows{$child}->{'rank_name'};
    my $parent = $rows{$child}->{'parent_name'}; 
    # skip the ranks below user-specified lowest rank
    next if $ranksort{lc $rank} > $ranksort{$lowestrank};
    
    if (length($child) > 1) {
      my $label =
        ($withrank ? $rank . ' ' : '')
        . $child
        . ($withauthor ? ' ' . $rows{$child}->{'taxon_author'} : '');
      $g->add_node($child
        , rank  => $rank
        , style => 'filled'
        # make each rank a different color
        , color => $rankcolor{lc $rank}
        # put text in each node
        , label => ($withtext ? $label : '')
      );
      if (length($parent) > 1) {
        $g->add_edge($parent => $child);
      }
    }
  }
  
  if (!$graphviz || $graphviz =~ /^pr?i?n?t?$/i) {
    print $g->as_debug;
  }
  elsif ($graphviz =~ /^pn?g?$/i) {
    $g->as_png($lowestrank
      . ($withtext   ? '_name'   : '')
      . ($withrank   ? '_rank'   : '')
      . ($withauthor ? '_author' : '')
      . '.png');
  }
  elsif ($graphviz =~ /^jp?e?g?$/i) {
    $g->as_jpeg($lowestrank
      . ($withtext   ? '_name'   : '')
      . ($withrank   ? '_rank'   : '')
      . ($withauthor ? '_author' : '')
      . '.jpg');
  }
}

__END__

=head1 NAME

checkitis.pl

Check an ITIS-for-Scratchpads file for proper linkage between children and
parents. More functions available below.

=head1 USAGE

 # check for proper linkage between children and parents, print results
 checkitis.pl itisfile.tab

 # print all unique paths from highest to lowest ranks
 checkitis.pl -o p itisfile.tab

 # print full hierarchy for all taxa from highest rank to Order
 checkitis.pl -o fullhierarchy -l order itisfile.tab

 # print newick format with author appended to each taxon name
 checkitis.pl -o new -a itisfile.tab
 checkitis.pl --output newick --withauthor itisfile.tab

 # print dot format for GraphViz, nodes without text
 checkitis.pl --output graphviz itisfile.tab
 
 # print dot format for GraphViz, nodes with text
 checkitis.pl --output graphviz --withtext itisfile.tab
 
 # produce a jpeg of the GraphViz graph down to Order, nodes with text
 checkitis.pl --lowestrank order -o g --withtext --graphviz jpeg itisfile.tab

=head1 OPTIONS

 -h --help        Show this help.
 
 -o --output      Choose an option:

    check           Default. Check if set of all parent_name values is subset
                    of all full names. Check if any full name appears more than
                    once.
    fullhierarchy   Print all parents of every entry.
    paths           Print all unique paths from highest to lowest taxon ranks.
    newick          Print newick format of the taxonomy.
    graphviz        Print dot format for GraphViz. See --dot below.

 -l --lowestrank  Include ranks from highest rank down to this rank.
 
 -r --withrank    For --output fullhierarchy or graphviz.
                  Prepend rank to taxon name.
                  
 -a --withauthor  For --output fullhierarchy, newick, or graphviz.
                  Append author to taxon name.
 
 Below options are only for --output graphviz.
 
 -g --graphviz    Choose an option:

    print           Default. Print dot format for GraphViz.
    png             Produce a png image of the GraphViz graph.
    jpeg            Produce a jpeg image.

 -t --withtext    For --output graphviz.
                  Put text in GraphViz nodes or leave them blank.

=head1 DESCRIPTION

Read an ITIS-for-Scratchpads file.

By default, check if all parent names are a subset of all full names and
check if all full names are listed once. Print results.

Set --option to fullhierarchy to print each entry with all of its parents. Use
--withrank to print the rank name for each item.

Set --option to paths to print all unique paths from the highest to lowest taxon
ranks.

Set --option to newick to print the taxonomy in newick format.

Set --option to graphviz to print in GraphViz's dot format. You can produce an
image directly by using --dot png or --dot jpeg.

=head1 AUTHOR

Kamil Slowikowski, kslowikowski-at-gmail-dot-com

=cut