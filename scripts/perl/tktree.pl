#!/usr/bin/perl -w
use strict;
use warnings;
use Tk;
use Tk::Tree;

die("please give me a file\n") unless $ARGV[0];

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
  chomp;
  /.+\.(.+)$/;
  my $text = $1 || $_;
  #print($_ . "\t" . $1 . "\n");
  $tree->add($_, -text => $text, -state => 'normal');
}
close($file);

#closeTree ($tree);
openTree ($tree);

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