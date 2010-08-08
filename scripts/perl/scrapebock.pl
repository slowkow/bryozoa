#!/usr/bin/perl
use strict;
use warnings;
use URI;
use lib "lib";
use Web::Scraper;

my %ranksort = (
  'Kingdom'     =>  1,
  'Subkingdom'  =>  2,
  'Phylum'      =>  3,
  'Subphylum'   =>  4,
  'Superclass'  =>  5,
  'Class'       =>  6,
  'Subclass'    =>  7,
  'Infraclass'  =>  8,
  'Superorder'  =>  9,
  'Order'       => 10,
  'Suborder'    => 11,
  'Infraorder'  => 12,
  'Superfamily' => 13,
  'Family'      => 14,
  'Subfamily'   => 15,
  'Tribe'       => 16,
  'Subtribe'    => 17,
  'Genus'       => 18,
  'Subgenus'    => 19,
  'Species'     => 20,
  'Subspecies'  => 21
);
my %rranksort = reverse %ranksort;

sub nextRank {
  my $cr = shift;
  return $rranksort{$ranksort{$cr} - 1};
}

my $scraper = scraper {
  process "h1,h2,h3,h4,h5,h6,ul", 'headers[]' => scraper {
    process "h1,h2,h3,h4,h5,h6", 'head' => 'TEXT';
    process "li", 'listitems[]' => 'TEXT';
  };
  result 'headers';
};

my $url = 'http://www.bryozoa.net/famsys.html';
# scrape bock's website
my $scraped = $scraper->scrape(URI->new($url));
#~ use YAML;
#~ print Dump $scraped;
#~ use Data::Dumper;
#~ print Dumper($scraped);

my %hierarchy;
my %last_taxon;
$last_taxon{'Phylum'} = 'Bryozoa';
print "rank_name\tunit_name1\tparent_name\n";
print "Phylum\tBryozoa\t\n";
# dump the scraped elements in a somewhat legible format
foreach my $element (@{$scraped}) {
  if ($element->{'head'}) {
    # when we encounter 'unplaced',
    # the following taxa belong to the parent order
    if ($element->{'head'} =~ /(?:unplaced|uncertain)/i) {
      delete $last_taxon{'Suborder'};
      delete $last_taxon{'Infraorder'};
      delete $last_taxon{'Superfamily'};
    }
    
    $element->{'head'} =~ /^\s*(\w+)\s*(\w+\b)/;
    
    my $rank = ucfirst lc $1;
    my $name = ucfirst lc $2;
    
    next if not defined $ranksort{$rank};
    
    my $parentrank = nextRank($rank);
    while (!$last_taxon{$parentrank}) {
      $parentrank = nextRank($parentrank);
    }
    my $parent = $last_taxon{$parentrank};
    
    #push(@{$hierarchy{$parent}}, $name);
    
    print "$rank\t$name\t$parent\n";
    
    $last_taxon{$rank} = $name;
  }
  elsif ($element->{'listitems'}) {
    foreach my $listitem (@{$element->{'listitems'}}) {
      $listitem =~ /^\s*(\w+)(?:\s*(\w+\b))?/;
      
      my $rank = ucfirst lc $1;
      my $name = ucfirst lc $2;
      
      # we have the genus name, not the rank name
      if (not defined $ranksort{$rank}) {
        $name = $rank;
        $rank = 'Genus';
        
        my $parentrank = nextRank($rank);
        while (!$last_taxon{$parentrank}) {
          $parentrank = nextRank($parentrank);
        }
        my $parent = $last_taxon{$parentrank};
        
        print "Genus\t$name\t$parent\n";
      }
      # we have the rank name
      else {
        my $parentrank = nextRank($rank);
        while (!$last_taxon{$parentrank}) {
          $parentrank = nextRank($parentrank);
        }
        my $parent = $last_taxon{$parentrank};
        
        print "$rank\t$name\t$parent\n";
      }
    }
  }
}

# Phil Bock's ways of marking unplaced taxa:
##################################################
# Cyclostomatids with family placement uncertain
# Unplaced Cryptostomids
# Unplaced Cystoporid genera
# Unplaced Trepostome genera
# Unplaced Fenestrate genera
# Unplaced Ctenostomates
# Unplaced Anascan genera
# Unplaced Ascophoran genera
##################################################