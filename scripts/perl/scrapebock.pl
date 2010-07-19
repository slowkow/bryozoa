#!/usr/bin/perl
use strict;
use warnings;
use URI;
use lib "lib";
use Web::Scraper;

my $scraper = scraper {
  process "h1,h2,h3,h4,h5,h6,ul", 'headers[]' => scraper {
    process "h1,h2,h3,h4,h5,h6", 'head' => 'TEXT';
    process "li", 'listitems[]' => 'TEXT';
  };
  result 'headers';
};

# scrape bock's website
my $hierarchy = $scraper->scrape(URI->new("http://www.bryozoa.net/famsys.html"));
#~ use YAML;
#~ print Dump $hierarchy;
#~ use Data::Dumper;
#~ print Dumper($hierarchy);

my %tabs = (
  'class'       => "",
  'order'       => "\t",
  'suborder'    => "\t\t",
  'infraorder'  => "\t\t\t",
  'superfamily' => "\t\t\t\t",
  'family'      => "\t\t\t\t\t",
  'genus'       => "\t\t"
);

# dump the hierarchy in a somewhat legible format
foreach my $step (@{$hierarchy}) {
  if ($step->{'head'}) {
    $step->{'head'} =~ /^\s*(\w+)\s*(.*)$/;
    next if not defined $tabs{lc $1};
    print $tabs{lc $1} . $step->{'head'} . "\n";
  }
  elsif ($step->{'listitems'}) {
    foreach my $listitem (@{$step->{'listitems'}}) {
      $listitem =~ /^\s*(\w+)\s*(.*)$/;
      if (not defined $tabs{lc $1}) {
        print $tabs{'genus'} . "GENUS " . $listitem . "\n";
      }
      else {
        print $tabs{lc $1} . $listitem . "\n";
      }
    }
  }
}
