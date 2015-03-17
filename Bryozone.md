


---


# Summary #

When I say Bryozone, I'm referring to bryozone.net, a site commissioned by
Dr. Scott Lidgard several years ago. The goal was to create a place where
the entire bryozoan taxonomy could be browsed. There were plans to add
additional data linked to each entry, but this was left for the future.

The data used to create the site was given to me in the format of a Microsoft
Excel file. I quickly separated the file into many tab-delimited sheets. Next,
I imported the sheets into MySQL while trying to maintain the proper relations
between them.

In short, I only used the authors from Bryozone and I ignored the rest of the
data.

The taxonomy present in these files has very few species, but it has a lot of
the higher taxa. So, this was a possible source for the higher taxonomy. Other
potential sources for a higher taxonomy include Bryan Quach's file that he
created during the summer of 2009 and [http://www.bryozoa.net/famsys.html Phil
Bock's site bryozoa.net].

In the table called `bryozone_taxa`, there are 15296 total records. There are
9714 entries marked as "Uncertain Species", meaning that their validity is
unknown. They could be valid or not. There are 3358 entries marked as
"Species", but 518 are repeats, have no genus, or are called "Uncertain",
leaving us with 2840 real entries for the rank "Species". Since Phil Bock
maintains a list of some 40k species, I decided to ignore the Bryozone list.


---


# Steps #

  1. Use `bryozone_taxa_authors` to merge `bryozone_taxa` and `bryozone_authors`.
  1. Use this new merged table to insert authors into Bryan Quach's taxonomy.


---


# Fields #
The fields should be self-explanatory.


---


# MySQL Warnings #

These are convenient warnings that take very little of my effort to find. MySQL
will complain when something is out of place without any additional analysis by
me.

These warnings are returned when running the queries in
[`bryozone\_import.sql`](http://code.google.com/p/bryozoa/source/browse/scripts/mysql/bryozone_import.sql).

### `age.tab` ###
No MySQL warnings.


---


### `authors_references.tab` ###
No MySQL warnings.


---


### `authors.tab` ###
**MySQL Warnings**
|Level|Code|Message|
|:----|:---|:------|
|Warning|1262|Row 336 was truncated; it contained more data than there were input columns|
|Warning|1262|Row 3849 was truncated; it contained more data than there were input columns|

**Rows 336 and 3849**
|ID|NAME|
|:-|:---|
|1340|Cipolla|actual taxa authors stop here|
|5000|Lopez de la Cuadra & Garcia-Gomez|actual taxa authors start here again|


---


### `latin.tab` ###
No MySQL warnings.


---


### `rank.tab` ###
No MySQL warnings.


---


### `references_full.tab` ###
No MySQL warnings.


---


### `references.tab` ###
No MySQL warnings.


---


### `taxa_authors.tab` ###
No MySQL warnings.


---


### `taxa_references.tab` ###
No MySQL warnings


---


### `taxa.tab` ###
**MySQL Warnings**
|Level|Code|Message|
|:----|:---|:------|
|Warning|1265|Data truncated for column 'revised' at row 15073|
|Warning|1261|Row 15336 doesn't contain data for all columns|

Ignore Warning 1261.

**Row 15073**
|TAXON-ID|PARENT-TAXON-ID|TAXON-NAME|RANK|SENIOR-SYN|YEAR|EXPERT|REVISED|COMMENTS|
|:-------|:--------------|:---------|:---|:---------|:---|:-----|:------|:-------|
|143613|20462|meridionalis|110|160033|1980|Gordon|   |Now Celleporella; Recent, Antarctica|


---


# Hierarchical Paths #
This is a visual representation of all of the taxon relationships present in
Bryan Quach's and Bryozone's taxonomies.

In Bryan Quach's taxonomy, all `NULL` and `Uncertain` taxons were removed.

Example:
```
Order > NULL > Family
->
Order > Family
```

|**Bryan Quach**|**Bryozone**|
|:--------------|:-----------|
|![http://bryozoa.googlecode.com/hg/png/bryan_unique_paths.png](http://bryozoa.googlecode.com/hg/png/bryan_unique_paths.png)|![http://bryozoa.googlecode.com/hg/png/bryozone_unique_paths.png](http://bryozoa.googlecode.com/hg/png/bryozone_unique_paths.png)|


---


# Erroneous repeats of higher taxa #
Genus names and other higher taxon names should appear once in the database.

There are 45 records of rank <110 (higher than species) that appear more than
once. See below.

```
mysql> select taxonname, count(*) from bryozone_taxa
  where rankcode < 110
  group by taxonname order by count(*) desc limit 46;
+-------------------+----------+
| taxonname         | count(*) |
+-------------------+----------+
| Uncertain         |       40 |
| Celleporella      |        4 |
| Flustrina         |        3 |
| Metrarabdotos     |        3 |
| Caloporella       |        3 |
| Celleporina       |        3 |
| Puellina          |        3 |
| Sclerodomus       |        2 |
| Reptocelleporaria |        2 |
| Escharellina      |        2 |
| Multiporina       |        2 |
| Reussina          |        2 |
| Densiporidae      |        2 |
| Buskia            |        2 |
| Ellipsopora       |        2 |
| Amphiblestrum     |        2 |
| Antropora         |        2 |
| Bathosella        |        2 |
| Dendrobeania      |        2 |
| Bryocryptella     |        2 |
| Cellepora         |        2 |
| Celleporaria      |        2 |
| Chaperiopsis      |        2 |
| Christinella      |        2 |
| Microporelloides  |        2 |
| Urceolipora       |        2 |
| Escharella        |        2 |
| Heteractis        |        2 |
| Himantozoum       |        2 |
| Macroporina       |        2 |
| Oligotresium      |        2 |
| Phylactella       |        2 |
| Poripetraliella   |        2 |
| Poristoma         |        2 |
| Puncturiella      |        2 |
| Radulina          |        2 |
| Scrupocellaria    |        2 |
| Stephanollona     |        2 |
| Corymbopora       |        2 |
| Crisina           |        2 |
| Lichenopora       |        2 |
| Zonatula          |        2 |
| Cribricella       |        2 |
| Gemellaria        |        2 |
| Cellarina         |        2 |
| Schizotrema       |        1 |
+-------------------+----------+
46 rows in set (0.00 sec)
```

Maybe there's a good reason for this kind of repeat? Observe:

```
mysql> select * from bryozone_taxa where taxonname = 'Celleporella';
+---------+----------+--------------+----------+----------+------+---------+------------+----------+
| taxonid | parentid | taxonname    | rankcode | seniorid | year | expert  | revised    | comments |
+---------+----------+--------------+----------+----------+------+---------+------------+----------+
|   20046 |     2074 | Celleporella |       90 |    20046 | 1986 | Lidgard | 2006-12-31 |          |
|   20067 |     2074 | Celleporella |       90 |    20067 | 1986 | Lidgard | 2006-12-31 |          |
|   20169 |     2074 | Celleporella |       90 |    20169 | 1848 | Lidgard | 2006-12-31 |          |
|   20614 |     2074 | Celleporella |       90 |    20614 | 1986 | Lidgard | 2006-12-31 |          |
+---------+----------+--------------+----------+----------+------+---------+------------+----------+
4 rows in set (0.00 sec)
```

So, all four rows are the same except for `taxonid`, `seniorid`, and `year`. The
`taxonid` is equal to the `seniorid` for each row. Perhaps the `year` has some
significance for the children?

Let's look at the children:

```
mysql> select count(*) from bryozone_taxa
  where parentid=20046 or parentid=20067 or parentid=20169;
+----------+
| count(*) |
+----------+
|        0 |
+----------+
1 row in set (0.00 sec)

mysql> select count(*) from bryozone_taxa where parentid=20614;
+----------+
| count(*) |
+----------+
|       26 |
+----------+
1 row in set (0.00 sec)
```

Nope, three of the Celleporella records have no children. Now, I can confidently
say that they are erroneous records.


---


# Many species records have unknown genera #

Species listed in `bryozone_taxa` have unknown genera. Observe the example
below:

```
mysql> select * from bryozone_taxa where taxonname = 'mazatlantica';
+---------+----------+--------------+----------+----------+------+--------+------------+--------------------------------------------------------------------+
| taxonid | parentid | taxonname    | rankcode | seniorid | year | expert | revised    | comments                                                           |
+---------+----------+--------------+----------+----------+------+--------+------------+--------------------------------------------------------------------+
|  135079 |    10090 | mazatlantica |      113 |     NULL | 1856 | Bock   | 2004-07-03 | Etiam Schizoporella fide Jelly (1889);   Recent, Pacific (Mexico); |
|  138392 |    20834 | mazatlantica |      113 |     NULL | 1856 | Bock   | 2004-07-03 | Vide Lepralia mazatlantica;                                        |
|  143053 |    10090 | mazatlantica |      110 |   143053 | 2003 | Bock   | 2004-03-27 | Recent, Pacific east (Mexico, Gulf of California)                  |
+---------+----------+--------------+----------+----------+------+--------+------------+--------------------------------------------------------------------+
3 rows in set (0.00 sec)
```

So, we have a species called _mazatlantica_, but what is its genus? Those
comments look like they have genus names. It is not trivial to collect those
names, because I would have to look at other entries with the name
_mazatlantica_ that are marked as synonyms (`rankcode`=113) and then parse
their comments. In this case, I'll collect Schizoporella and Lepralia, and I
can't trust that _Etiam_ or _Vide_ are used properly or consistently. After all
this work, there is no way to confirm if either one of these names is the
intended genus name for _mazatlantica_.

Let's try the more direct route: look up the `parentid`.

```
mysql> select * from bryozone_taxa where taxonid=10090;
+---------+----------+-----------+----------+----------+------+---------+------------+----------+
| taxonid | parentid | taxonname | rankcode | seniorid | year | expert  | revised    | comments |
+---------+----------+-----------+----------+----------+------+---------+------------+----------+
|   10090 |     1483 | Uncertain |       90 |    10090 | 2007 | Lidgard | 2006-08-01 |          |
+---------+----------+-----------+----------+----------+------+---------+------------+----------+
1 row in set (0.00 sec)
```

Alas, the parent genus (`rankcode`=90) is `Uncertain`. Querying the next
`parentid` links you to another `Uncertain` row, this time a family
(`rankcode`=80) as seen below.

```
mysql> select * from bryozone_taxa where taxonid=1483;
+---------+----------+-----------+----------+----------+------+---------+------------+----------+
| taxonid | parentid | taxonname | rankcode | seniorid | year | expert  | revised    | comments |
+---------+----------+-----------+----------+----------+------+---------+------------+----------+
|    1483 |     1411 | Uncertain |       80 |     1483 | 2007 | Lidgard | 2006-12-31 |          |
+---------+----------+-----------+----------+----------+------+---------+------------+----------+
1 row in set (0.00 sec)
```

You can continue to climb up the ladder until you reach a `taxonname` that is
not 'Uncertain', but this doesn't lead us to a genus for this species.


---


# Some species records are repeated and contradictory #

Let's look at a species called _Alcyonidium erectum_.

```
mysql> select * from bryozone_taxa where taxonname = 'erectum';
+---------+----------+-----------+----------+----------+------+--------+------------+-------------------------------------------------------+
| taxonid | parentid | taxonname | rankcode | seniorid | year | expert | revised    | comments                                              |
+---------+----------+-----------+----------+----------+------+--------+------------+-------------------------------------------------------+
|  140193 |    10004 | erectum   |      110 |   140193 | 1942 | Bock   | 2004-12-03 | non Andersson, 1902; Recent, Pacific (Japan);         |
|  140194 |    10004 | erectum   |      110 |   140228 | 1902 | Bock   | 2004-12-03 | non Silen, 1942; Vide Alcyonidium mamillatum erectum; |
+---------+----------+-----------+----------+----------+------+--------+------------+-------------------------------------------------------+
2 rows in set (0.00 sec)

mysql> select taxonname from bryozone_taxa where taxonid='10004';
+-------------+
| taxonname   |
+-------------+
| Alcyonidium |
+-------------+
1 row in set (0.00 sec)
```

The first entry's `seniorid` is equal to the `taxonid`, indicating that the
entry is a valid name, not a synonym. The second entry's `seniorid` points
to an entry called _mamillatum erectum_.

```
mysql> select * from bryozone_taxa where taxonid='140228';
+---------+----------+--------------------+----------+----------+------+--------+------------+----------------------------------------+
| taxonid | parentid | taxonname          | rankcode | seniorid | year | expert | revised    | comments                               |
+---------+----------+--------------------+----------+----------+------+--------+------------+----------------------------------------+
|  140228 |    10004 | mamillatum erectum |      110 |   140228 | 1902 | Bock   | 2004-12-03 | non Silen, 1942; Recent, circumarctic; |
+---------+----------+--------------------+----------+----------+------+--------+------------+----------------------------------------+
1 row in set (0.00 sec)
```

Since I'm not given extra information, I cannot make a decision regarding the
validity of this species. This kind of situation exists for the following
taxa:

```
Membranipora acuta
Leiosalpinx australis
Buskia
Alcyonidium erectum
Alcyonidium diaphanum
```

This kind of problem inhibits my workflow and leads me to doubt the validity
of the entire taxonomy.


---
