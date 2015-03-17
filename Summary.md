

# Introduction #
This is a project completed by Kamil Slowikowski and supervised by Dr. Scott
Lidgard. The data, including a list of some forty thousand species, is provided
by Phil Bock. Bryan Quach produced a higher taxonomy down to the level of genus
that is superior to Dr. Lidgard's previous taxonomy.

Before starting this project, it's necessary to read a lot. I had to learn
about taxonomy and synonymy. I also had to become accustomed with all of the
data files that I use. It's necessary to read some lines from all of the files
to understand the big picture. The headings in the various tab-delimited files
are inconsistent, so it's important to understand the big picture in order to
make correct interpretations.

All of the data files used for this project have some mistakes. Misspellings,
omissions, contradictions, etc. The procedure for cleaning the data is different
for every file.

My strategy involves putting all of the data into MySQL tables, so I can easily
view and extract desired data with just a few simple queries.

# Current Status #
See UploadFile for the current status of the final upload file. It is
`scripts/mysql/output/scratchpads.tab`.

**I have omitted synonyms for higher taxa (genus - phylum) at this stage.**

# Data Sources #
There are three sources of data for this project:

Click to read more about the specific data source.

  1. [Phil Bock](PhilBock.md)
    * Two tables with ~40k species in total.
  1. [Bryan Quach](BryanQuach.md)
    * Complete and clean higher taxonomy of bryozoans (higher than species).
      * No authors for all taxa.
      * (I'm ignoring his synonyms for now.)
  1. [Bryozone](Bryozone.md)
    * Authors for Bryan's higher taxonomy.

Phil Bock's original data is in two tables:
`bryozoans` and `currentspecies`.

`bryozoans` is older than `currentspecies`, but they both contain a list of
species. So, `currentspecies` should be merged with `bryozoans` and it should
take precedence over `bryozoans`.

Bryan Quach's original data is in three tables:
`bryan_valid`, `bryan_invalid`, and `bryan_rank`.

`bryan_valid` contains valid taxa. Records with rank genus or higher are
properly linked, so it can be used to construct a full higher taxonomy. Rows
with species names do not have genus names and have no clues to assign them
to genera, so I ignore them and use only the higher taxa. `bryan_invalid`
contains synonyms, which I've ignored for now but will use in the future.
`bryan_rank` explains the meaning of each rank code.

Bryozone has more than just authors. The data is split into many tables:
`bryozone_age`, `bryozone_authors`, `bryozone_authors_references`,
`bryozone_easyauthors`, `bryozone_latin`, `bryozone_rank`,
`bryozone_references`, `bryozone_references_full`, `bryozone_taxa`,
`bryozone_taxa_authors`, `bryozone_taxa_references`.

Read [Bryozone](Bryozone.md) to see why I don't want to use this for a higher taxonomy. I
prefer to use Bryan's data for the higher taxonomy. However, combining
`bryozone_taxa`, `bryozone_authors`, and `bryozone_taxa_authors` allows me to
fill the missing authors in Bryan's higher taxonomy.

# Scripts #
Almost everything in this project is done automatically with scripts. Only a
handful of manual corrections were done so MySQL would happily take the data
without warnings.

There are lots of scripts in this project. See [Scripts](Scripts.md) for details.

Why Perl and PHP?
PHP because I had ambitions about Drupal and I wanted to learn a new language.
Perl because I already had some experience with it before this project.

My choice of languages deserves some explanation. In short, I didn't choose
wisely. The goal was to end up with some PHP code that might be translated into
a Drupal module at some point in the future. It seems that I wrote all of the
problem-specific code in PHP and the more generic code in Perl; exactly the
opposite of what I should have done.

# Flowchart #
This flowchart displays the gist of what I have done. I looked at several data
sources, tried to figure out how I could combine them, and then did my best
to combine them in such a way to preserve most of the data and avoid messing
with troublesome data.

Each box is a MySQL table. Actions performed by the arrows are done by scripts.

![http://bryozoa.googlecode.com/hg/png/flowchart.png](http://bryozoa.googlecode.com/hg/png/flowchart.png)