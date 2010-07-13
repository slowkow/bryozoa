#!/usr/bin/perl -w
# Author      : Kamil Slowikowski <kslowikowski@gmail.com>
# Date        : July 13, 2010
# Description : Read list of paths with terms delimited by '.'.
#               Display a Tk Tree with expandable nodes.
# Notes       : Tk methods from http://www.perlmonks.org/?node_id=712482
use strict;
use warnings;
use Tk;
use Tk::Tree;
use Getopt::Long;

$ARGV[0] or die("Display a Tk Tree.\nUsage: tktree.pl <file>\n");

my $main = MainWindow->new(-title => "Bryozoa");
my $tree = $main->ScrlTree(
    -itemtype   => 'text',
    -separator  => '.',
    -scrollbars => "se",
    -selectmode => 'single',
);

$tree->pack(-fill => 'both', -expand => 1);

open(my $file, "<", $ARGV[0]) or die $!;
while (<$file>) {
  # remove newline
  chomp;
  # grab last item in list delimited by periods
  /(?:.+\.)?(.+)$/;
  #print($_ . "\t" . $1 . "\n");
  $tree->add($_, -text => $1, -state => 'normal');
  # hide species
  #~ if ($1 =~ /species/i) {
    #~ $tree->setmode($_, 'close');
    #~ $tree->hide('entry' => $_);
  #~ }
  #~ else {
    #~ $tree->setmode($_, 'open');
  #~ }
}
close($file);

closeTree ($tree);
#openTree ($tree);

MainLoop;

sub openTree {
    my $tree = shift;
    my ( $entryPath, $openChildren ) = @_;
    my @children = $tree->info( children => $entryPath );

    return if !@children;

    for (@children) {
        openTree( $tree, $_, 1 );
        $tree->show( 'entry' => $_ ) if $openChildren;
    }
    $tree->setmode( $entryPath, 'close' ) if $entryPath and length $entryPath;
}

sub closeTree {
    my $tree = shift;
    my ( $entryPath, $hideChildren ) = @_;
    my @children = $tree->info( children => $entryPath );

    return if !@children;

    for (@children) {
        closeTree( $tree, $_, 1 );
        $tree->hide( 'entry' => $_ ) if $hideChildren;
    }
    $tree->setmode( $entryPath, 'open' ) if $entryPath and length $entryPath;
}