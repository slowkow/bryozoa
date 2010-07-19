#!/usr/bin/perl

use strict;
use warnings;
use Getopt::Long;

$ARGV[0] or die
"Usage: checkitis.pl [OPTION]... [FILE]\
Try 'checkitis.pl --help' for more information.\n";

my ($option, $withrank);
GetOptions(
  'h|help'     => sub { exec('perldoc', $0); exit(0); },
  'o|option:s' => \$option,
  'r|withrank' => \$withrank ,
);

# Remove whitespace from left and right sides of string.
sub trim {
  my $string = shift;
  $string =~ s/^\s+//;
  $string =~ s/\s+$//;
  return $string;
}

open(my $file, "<", $ARGV[0]) or die $!;

# slurp the whole file into parents
my %parents;
# record line numbers where parent name is used
my %parent_name;
# record number of times full name is used
my %full_names;
# save headers for use in a hash
my @headers;
my $line = 0;
while (<$file>) {
  $line++;
  chomp;
  if ($line == 1) {
    die("Missing some headers!\n") unless (/unit_name1/ && /unit_name2/
      && /unit_name3/ && /parent_name/ && /rank_name/ && /taxon_author/);
    # save the headers for the hash
    @headers = split(/\t/);
    next;
  }
  # put row values into a hash with headers as keys
  my %values;
  @values{@headers} = split(/\t/);
  # a full name is the concatenation of all unit names
  my $full_name = trim(join(' ', $values{'unit_name1'}, $values{'unit_name2'} || '', $values{'unit_name3'} || '', $values{'taxon_author'} || ''));
  # the whole file is slurped into the parents hash
  $parents{$full_name} = \%values;
  # count number of times this name appears
  $full_names{$full_name} += 1;
  # record line numbers with this name
  $parent_name{$values{'parent_name'}} .= "$line ";
}
################################################################################
# check file for validity
if (!$option || $option =~ /ch?e?c?k?/) {
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
elsif ($option =~ /fu?l?l?h?i?e?r?a?r?c?h?y?/) {
  my @full_hierarchy;
  foreach my $child (keys %parents) {
    my @path;
    
    if ($withrank) {
      push(@path, $parents{$child}->{'rank_name'} . " " . $child);
    }
    else {
      push(@path, $child);
    }
    
    while ($parents{$child}->{'parent_name'}) {
      if ($withrank) {
        push(@path, $parents{$parents{$child}->{'parent_name'}}->{'rank_name'} . " " . $parents{$child}->{'parent_name'});
      }
      else {
        push(@path, $parents{$child}->{'parent_name'});
      }
      $child = $parents{$child}->{'parent_name'};
    }
    
    push(@full_hierarchy, join('.', reverse @path));
  }
  # sort the paths before printing
  @full_hierarchy = sort {length($a) <=> length($b)} @full_hierarchy;
  foreach my $path (@full_hierarchy) {
    print($path . "\n");
  }
}
################################################################################
# print the unique paths from Kingdom to Subspecies
elsif ($option =~ /pa?t?h?s?/) {
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
    # print the number of times that a path is used
    #print($unique_paths{$path} . "\t" . $path . "\n");
    print($path . "\n");
  }
}

__END__

=head1 NAME

checkitis.pl

=head1 USAGE

checkitis.pl itisfile.tab

checkitis.pl -o <check|fullhierarchy|paths> itisfile.tab

checkitis.pl -o p itisfile.tab

checkitis.pl -o fullhierarchy itisfile.tab

checkitis.pl -o f -r itisfile.tab

=head1 OPTIONS

 -h --help      Show this help.
 -o --option    Choose an option:

    [check]        Default. Check if set of all parent_name values is subset of
                   all full names. Check if any full name appears more than
                   once.
    fullhierarchy  Print all parents of every entry.
    paths          Print all unique paths from highest to lowest taxon ranks.

 -r --withrank  When using option fullhierarchy, print each entry's rank name.

=head1 DESCRIPTION

Read an ITIS-for-Scratchpads file.

By default, check if all values of parent_name are a subset of all full names
and check if all full names are listed once. Print results.

Set --option to fullhierarchy to print each entry with all of its parents. Use
--withrank to print the rank name for each item.

Set --option to paths to print all unique paths from the highest to lowest taxon
ranks.

=head1 AUTHOR

Kamil Slowikowski, kslowikowski-at-gmail-dot-com

=cut