

# Summary #

Phil Bock has been maintaining a database of bryozoan taxonomy for a long time
at [his website](http://bryozoa.net/indexes.html). For this project, I only use
two of his lists of bryozoan species available [here](http://bryozoa.net/data).

On his website, there is an older table and a newer table. The old one is
called `Bryozoans.fp7` and the new one is `CURRENTSPECIES.fp7`. I don't like
to use FileMaker Pro, so I exported his data into tab-delimited files.

The field `FAMILIES::NAME` in `currentspecies` was added while I was using
FileMaker Pro. When I opened `CURRENTSPECIES.fp7`, I was prompted to select
a table with family names, and I selected the June 2009 version of
`families.fp7`.

Unfortunately, the June 2009 version of a table called `families.fp7` is not
available on his site. I got it from Bryan Quach's 2009 collection of files.
I also exported `families.fp7` into a tab-delimited file. I didn't use it, but
I now noticed that each family has an author listed, so this could be useful.

The tables `bryozoans` and `currentspecies` contain slightly different fields.
Records in one table may or may not exist in the other table. I merged
`currentspecies` into `bryozoans`. The `currentspecies` table is newer, so it
overrides `bryozoans` when a duplicate occurs.

# Steps #
These are the steps I took to prepare the data for input into the main
`scratchpads` table.

  1. Export from FileMaker to tab-delimited files.
  1. Import to MySQL, record warnings.
  1. Fix warnings by manually modifying tab-delimited files.
  1. Import to MySQL with no warnings.
  1. Merge `currentspecies` into `bryozoans`. See [CombineBock](CombineBock.md) for details.
  1. Link synonyms that are not linked to valid names by parsing latin in `details`.

# Fields #
The following is a description of every field in Phil Bock's tables.

  * `bryozoans`
    * `Age`
      * String. Uncertain meaning. Example: '01, 02'.
    * `Author`
      * String. The author who classified the species and year.
    * `Bryozoans2_Name`
      * String. The actual name referred to by the field `Current_name`.
    * `Comments`
      * String. Phil Bock's personal comments.
    * `Current_name`
      * Integer. A reference to the `ID` of the accepted name for this species.
    * `Date_created`
      * String. Date of record creation.
    * `Date_modified`
      * String. Date of last modification.
    * `Delete`
      * Integer 0 or 1. Whether or not this record should be deleted.
    * `Details`
      * String. Geologic stage, location, latin indicating accepted name.
    * `ID`
      * Integer. Unique identification number.
    * `Name`
      * String. Unique binomial name of the species.
    * `Newcode`
      * String. Year of classification followed by partial species epithet. Example: '1965angust1'.
    * `Original`
      * Integer 0 or 1. Uncertain meaning.
    * `Status`
      * String. Phrase marking the species as current, homonym, synonym, etc.
    * `Valid`
      * Integer 0 or 1. Whether or not this name is valid.

  * `currentspecies`
    * `Author`
      * String. The author who classified the species and year.
    * `Current_Name`
      * String. Unique binomial name of the species. Not necessarily current.
    * `Date_created`
      * String. Date of record creation.
    * `Date_modified`
      * String. Date of last modification.
    * `FAMCODE`
      * Integer. Code of parent family.
    * `FAMILIES::NAME`
      * String. Name of parent family.
    * `first_name`
      * String. Genus epithet.
    * `html_page`
      * String. URL of Phil Bock's bryozoa.net page with this species.
    * `OK`
      * Integer 0 or 1. Whether or not this name is valid.
    * `Recent`
      * Integer 0 or 1 or 9. Uncertain meaning.
    * `Remarks`
      * String. Zoological Record id associated with this species.
    * `SpeciesID`
      * Integer. Unique identification number.
    * `Status`
      * String. Phrase marking the species as current, homonym, synonym, etc.


---


# MySQL Warnings #

Fixing these warnings is the only manual step in this project. Every other
step is performed by a script.

Commits related to manually fixing `bock/Jun2010/mysql/bryozoans.tab`:
> 21, 22, 23, 24, 25

Commits related to manually fixing `bock/Jun2010/mysql/currentspecies.tab`:
> 36

These warnings are returned when running the queries in
[bock\_import.sql](http://code.google.com/p/bryozoa/source/browse/scripts/mysql/bock_import.sql).

|Level|Code|Message|
|:----|:---|:------|
|Warning|1265|Data truncated for column 'valid' at row 1969|
|Warning|1366|Incorrect integer value: 'v' for column 'pid' at row 2118|
|Warning|1366|Incorrect integer value: 'o' for column 'original' at row 3752|
|Warning|1366|Incorrect integer value: '\tSmitt, 1873' for column 'pid' at row 5811|
|Warning|1265|Data truncated for column 'delete' at row 5811|
|Warning|1265|Data truncated for column 'date\_modified' at row 5811|
|Warning|1261|Row 5811 doesn't contain data for all columns|
|Warning|1366|Incorrect integer value: 'Chrysaora pustulosa' for column 'original' at row 7755|
|Warning|1366|Incorrect integer value: '?non Bryozoa fide Hillmer, 1839;   Cretaceous, lower Hauterivian (Germany) fide Hillmer (1971);' for column 'delete' at row 7755|
|Warning|1265|Data truncated for column 'date\_created' at row 7755|
|Warning|1366|Incorrect integer value: 'procer' for column 'valid' at row 15488|
|Warning|1366|Incorrect integer value: 'o' for column 'valid' at row 18431|
|Warning|1265|Data truncated for column 'pid' at row 26842|
|Warning|1265|Data truncated for column 'pid' at row 33045|
|Warning|1366|Incorrect integer value: 'IDMONEA CONTORTILIS' for column 'original' at row 38240|
|Warning|1366|Incorrect integer value: 'Etiam Oncousoecia fide Canu & Bassler (1933);   Etiam Crisisina fide d'Orbigny (1851);   Etiam Filisparsa fide Ulrich & Bassler ' for column 'delete' at row 38240|
|Warning|1265|Data truncated for column 'date\_created' at row 38240|
|Warning|1366|Incorrect integer value: '-' for column 'delete' at row 38565|


---


# Names with non-alpha characters #

There are many names in tables `bryozoans` and `currentspecies` that have
non-alpha characters (match the regular expression `[^A-Za-z ]`). In English,
alpha characters are characters in the alphabet and space. All other
characters are considered non-alpha.

The `name` field in `currentspecies` is more complicated, because there may be
an equals sign '=' followed by one or more numbers, commas, and words. This is
the way that Phil Bock decided to indicate which valid name corresponds to the
synonym.

```
mysql> select name from currentspecies limit 1;
+------------------------------------+
| name                               |
+------------------------------------+
|  was Acanthocladia acuticosta=5780 |
+------------------------------------+
1 row in set (0.00 sec)

mysql> select name from currentspecies where name regexp '=[A-Za-z]' limit 1;
+----------------------------------------------------+
| name                                               |
+----------------------------------------------------+
|  was Conescharellina veronensis=lacrimula perfecta |
+----------------------------------------------------+
1 row in set (0.00 sec)

mysql> select name from currentspecies where name regexp '=.+,' limit 1;
+------------------------------------+
| name                               |
+------------------------------------+
|  was Cyclotrypa bennetti=5462, dup |
+------------------------------------+
1 row in set (0.00 sec)

mysql> select name from currentspecies where name regexp '=.+,.*[0-9]' limit 1;
+--------------------------------------+
| name                                 |
+--------------------------------------+
|  was Eurystrotos compacta=763, 20716 |
+--------------------------------------+
1 row in set (0.00 sec)
```

The format is `was Foo bar=[valid name or names]`. In this project, I only
handle the case when the equals sign is followed by a single id number. I
ignore the cases when it is followed by more than one id number or non-digits.

```
mysql> select count(*) from scratchpads where full_name like '[^A-Za-z ]';
+----------+
| count(*) |
+----------+
|        0 |
+----------+
1 row in set (0.00 sec)
```

In this project, I have ignored all records that have names with non-alpha
characters. None of them are in the final `scratchpads` table.

## Table `bryozoans` ##
This is a space-delimited list of all non-alpha symbols present in the `name`
field in table `bryozoans`:
```
- , ? . ' " ( ) [ ] ® & ﬁ ∆ 0 1 3 8 9
```

The number of names with non-alpha characters:
```
mysql> select count(*) from bryozoans where name regexp '[^A-Za-z ]';
+----------+
| count(*) |
+----------+
|     1988 |
+----------+
1 row in set (0.10 sec)
```

Now let's dive into some specific examples for each non-alpha symbol. I
contacted Phil Bock and he explained their meanings.

```
mysql> select name from bryozoans where name like '%-%' limit 3;
+------------------------------------------------+
| name                                           |
+------------------------------------------------+
| Batostomella columnaris tuberosa-sparsigemmata |
| BIFLUSTRA CRASSO-RAMOSA                        |
| Callotrypa macropora-signata                   |
+------------------------------------------------+
3 rows in set (0.01 sec)

mysql> select count(*) from bryozoans where name like '%-%';
+----------+
| count(*) |
+----------+
|       39 |
+----------+
1 row in set (0.06 sec)
```

Perhaps I should treat '-' as a space? I did not ask Phil Bock about this.

```
mysql> select name from bryozoans where name like '%,%';
+----------------------------------------------+
| name                                         |
+----------------------------------------------+
| Bugula flustroidesjjjjjj,ddfe0               |
| DIPLONOTOS COSTULATUM [sic, read costulatus] |
+----------------------------------------------+
2 rows in set (0.06 sec)

mysql> select count(*) from bryozoans where name like '%,%';
+----------+
| count(*) |
+----------+
|        2 |
+----------+
1 row in set (0.06 sec)
```

These two are the only rows with commas. The first is a typo, the second comma
can be ignored.

```
mysql> select name from bryozoans where name like '%?%' limit 3;
+--------------------------+
| name                     |
+--------------------------+
| ACANTHOCLADIA? PAMPINOSA |
| ACANTHOCLADIA? TENUIS    |
| ACANTHOCLEMA? CAVERNOSA  |
+--------------------------+
3 rows in set (0.00 sec)

mysql> select count(*) from bryozoans where name like '%?%';
+----------+
| count(*) |
+----------+
|      803 |
+----------+
1 row in set (0.06 sec)
```

The genus assignment is doubtful, and it's important to retain this doubt,
perhaps in a comments field.

Phil Bock said:

> The entries with a "?" indicate the assignment to the genus is doubtful, and
> should be retained in some way (For example with a field indicating "genus
> assignment uncertain")

```
mysql> select name, `delete`, comments, currentname from bryozoans
    -> where name like '%.%' limit 8;
+----------------------------------+--------+----------+-------------+
| name                             | delete | comments | currentname |
+----------------------------------+--------+----------+-------------+
| Nicholsonella sp. A & B          |      1 | delete   |       99999 |
| AMPLEXOPORA sp.                  |      1 | delete   |       99999 |
| ASCODICTYON sp.                  |      1 | delete   |       99999 |
| BATOSTOMELLA aff. B. INTERPOROSA |      1 | delete?  |       99999 |
| CHEILOTRYPA? sp.                 |      1 | delete   |       99999 |
| CORYNOTRYPA sp.                  |      1 | delete   |       99999 |
| CYCLOPELTA sp.                   |      1 | delete   |       99999 |
| CYCLOTRYPA aff. TUBULARIA        |      0 | NULL     |       99999 |
+----------------------------------+--------+----------+-------------+
8 rows in set (0.00 sec)

mysql> select count(*) from bryozoans where name like '%.%';
+----------+
| count(*) |
+----------+
|      112 |
+----------+
1 row in set (0.06 sec)
```

These records should be deleted.

Phil Bock said:

> All of the entries with sp., or cf. or indet. or aff. were in the original.
> These (I assume were records in open nomenclature from the original
> reference, and all can be ignored for our purpose. The should be marked with
> a "1" in the "Delete" field (And should also have "delete" as text in the
> Comments field. In my latest version, they also have "99999" in the
> Current\_name field - along with some other entries, such as names which are
> now thought to be non-bryozoan.

```
mysql> select name from bryozoans
    -> where name like "%'%" or name like "%\"%" limit 3;
+---------------------------+
| name                      |
+---------------------------+
| 'BALANTIOSTOMA' IMPEDITUM |
| 'BATOSTOMELLA' LINEATA    |
| 'Batrachopora' peltata    |
+---------------------------+
3 rows in set (0.00 sec)

mysql> select count(*) from bryozoans
    -> where name like "%'%" or name like "%\"%";
+----------+
| count(*) |
+----------+
|       73 |
+----------+
1 row in set (0.07 sec)
```

Single or double quotes are equivalent, and sometimes the first quote is
missing. The quote(s) indicate that the genus assignment is incorrect, so
this case is probably similar to the `?` case.

Phil Bock said:

> Single quotes have varied uses. Generally, it shows a genus to which the
> author assigned it, but recognizing that is was incorrect/inappropriate (yet
> was unwilling to use it to define a new genus).

```
mysql> select name from bryozoans
    -> where name like '%(%' or name like '%)%' limit 3;
+------------------------------------------+
| name                                     |
+------------------------------------------+
| ACANTHODESIA (BIFLUSTRA) MOGADORI        |
| ACANTHOTRYPA (ACANTHOTRYPINA) MENEGHINII |
| ACTINOPORA STELLATA (Delete?)            |
+------------------------------------------+
3 rows in set (0.00 sec)

mysql> select count(*) from bryozoans where name like '%(%' or name like '%)%';
+----------+
| count(*) |
+----------+
|      604 |
+----------+
1 row in set (0.00 sec)

mysql> select name from bryozoans
    -> where (name like '%(%' or name like '%)%')
    -> and name not regexp "\\([A-za-z]+\\)" limit 3;
+---------------------------------------+
| name                                  |
+---------------------------------------+
| ACTINOPORA STELLATA (Delete?)         |
| CELLEPORA (?DERMATOPORA) FAUJASII     |
| CERAMOPORA (LICHENALIA?) CLYPEIFORMIS |
+---------------------------------------+
3 rows in set (0.01 sec)

mysql> select count(*) from bryozoans
    -> where (name like '%(%' or name like '%)%')
    -> and name not regexp "\\([A-za-z]+\\)";
+----------+
| count(*) |
+----------+
|       29 |
+----------+
1 row in set (0.08 sec)
```

When a name is between parentheses, it indicates a potentially incorrect
subgenus assignment. However, `(?)` is equivalent to the `?` case.

Phil Bock said:

> Most of the records with "()" show the use of a subgenus, and should be
> retained. There are a few with "(?)" - which clearly means the same as "?"
> see- above. There a several that I suspect are not the current use of
> subgenus, dating from the 19th century - but leave them as they are. See
> 34823 for example "LEPRALIA (SCHIZOPORELLA) GANDYI" - This is a direct quote,
> but I don't consider that Schizoporella was ever considered as a subgenus of
> Lepralia.

```
mysql> select name from bryozoans
    -> where name like '%[%' or name like '%]%' limit 3;
+------------------------------+
| name                         |
+------------------------------+
| ACAMARCHIS TRIDENTATUS [sic] |
| ADEONE [sic] LAMELLOSA       |
| ADEONE [sic] RETEPORIFORMIS  |
+------------------------------+
3 rows in set (0.00 sec)

mysql> select count(*) from bryozoans where name like '%[%' or name like '%]%';
+----------+
| count(*) |
+----------+
|      421 |
+----------+
1 row in set (0.07 sec)

mysql> select name from bryozoans
    -> where (name like '%[%' or name like '%]%')
    -> and name not like '%[sic]%';
+----------------------------------------------+
| name                                         |
+----------------------------------------------+
| Coelocochlea [Diastopora] pustulosa          |
| DIPLONOTOS COSTULATUM [sic, read costulatus] |
| Mackinneyella tortuosa  [1938]               |
+----------------------------------------------+
3 rows in set (0.12 sec)

mysql> select count(*) from bryozoans
    -> where (name like '%[%' or name like '%]%')
    -> and name not like '%[sic]%';
+----------+
| count(*) |
+----------+
|        3 |
+----------+
1 row in set (0.08 sec)
```

Entries with `[sic]` were probably marked by Horowitz. Retain this marking in
a comments field.

My guess is that `[Diastopora]` should be treated as `(Diastopora)`.

Phil Bock said:

> The entries with "sic" should indicate items Horowitz pointed to as either
> misspellings (such as Steganoporella, which now is recognised to be correctly
> Steginoporella, or Lioclema instead of Leioclema). Several are species names
> with the wrong gender - such as ending in "a" after a neuter genus, which
> should have ended in "um". Leave these at present and mark for attention in
> the new version. As far as I know, the "sic" would NOT have been used in the
> original publication.

```
mysql> select name from bryozoans where name like '%&%';
+----------------------------+
| name                       |
+----------------------------+
| Leptotrypa rara&           |
| Microporelloides areolata& |
| Nicholsonella sp. A & B    |
+----------------------------+
3 rows in set (0.06 sec)

mysql> select count(*) from bryozoans where name like '%&%';
+----------+
| count(*) |
+----------+
|        3 |
+----------+
1 row in set (0.06 sec)
```

Names with trailing `&` are homonyms. As far as I know, Scratchpads cannot
accomodate homonyms.

Phil Bock said:

> The two with a trailing "&" - I used this in my own table ("CURENT SPECIES")
> to allow duplication of names for homonyms - the entries in "Bryozoans" table
> have clearly been copied and pasted, without correction. Delete the
> ampersand. I should mark the entry for 45375 as a homonym.

```
mysql> select name from bryozoans
    -> where name like '%ﬁ%' or name like '%∆%' or name like '%®%';
Empty set (0.11 sec)

mysql> select name from bryozoans where name like 'Ral_na? originalis' or name like 'Macropora _lifera';
+----------------------+
| name                 |
+----------------------+
| Macropora ﬁlifera  |
| Ralﬁna? originalis |
+----------------------+
2 rows in set (0.00 sec)

mysql> select 'ﬁ';
+--------+
| ï¬ |
+--------+
| ï¬ |
+--------+
1 row in set (0.00 sec)

mysql> set character_set_connection=latin1;
Query OK, 0 rows affected (0.00 sec)

mysql> select name from bryozoans where name like 'Macropora ﬁlifera';
Empty set (0.00 sec)

mysql> set character_set_connection=utf8;
Query OK, 0 rows affected (0.00 sec)

mysql> select name from bryozoans where name like 'Macropora ﬁlifera';
+---------------------+
| name                |
+---------------------+
| Macropora ﬁlifera |
+---------------------+
1 row in set (0.00 sec)
```

There are some unicode characters, so make sure to use the right settings. I
could use the query `select ... into` in order to output the characters
properly with `character_set_connection=latin1`, but I had to set it to `utf8`
in order to query the table with the special characters.


---


## Table `currentspecies` ##
All non-alpha symbols in table `currentspecies`: (the character immediately
before `0` is a vertical tab, so it may be invisible)
```
= - , ? . ' ‘ ’ " ( ) & ﬁ  0 1 2 3 4 5 6 7 8 9
```

The number of names with non-alpha characters:
```
mysql> select count(*) from currentspecies where name regexp '[^A-Za-z ]';
+----------+
| count(*) |
+----------+
|     2051 |
+----------+
1 row in set (0.05 sec)
```

See above explanations to interpret the non-alpha characters.


---


# Link synonyms that are not linked to valid names #
This step is performed after merging `currentspecies` into `bryozoans`.

Some records are marked `valid=0` but were not linked to valid names. For
records in table `bryozoans`, that means they did not have a valid id number in
the field `currentname`. In table `currentspecies`, the name did not have a
valid id number after the equals sign.

So, I use the `details` field to link the synonyms to valid names.

```
mysql> select details from bryozoans
    -> where valid=0 and details regexp '(vide|etiam|nunc)'
    -> and length(details) < 180 limit 3;
+---------------------------------------------------------------------------------------------------+
| details                                                                                           |
+---------------------------------------------------------------------------------------------------+
| Vide Amplexopora minor;                                                                           |
| Etiam Chasmatopora fide Lavrentjeva (1985);   Ordovician, Caradocian (Russia: Tolmachev horizon); |
| Vide Dianulites petropolitanus sibiricus;                                                         |
+---------------------------------------------------------------------------------------------------+
3 rows in set (0.00 sec)
```

These are examples of the latin phrases containing _Vide_, _Etiam_, or _Nunc_.

_Vide_ means _see_. _Etiam_ means _also_. _Nunc_ means _now_. Unfortunately,
the latin words are used inconsitently, so their meanings can be ignored.

I use a naive strategy in this project to link 2290/7344 unlinked synonyms. My
strategy is to treat all 3 words the same way and assume that the format is
like this:

```
latin unit_name1 [unit_name2] [unit_name3] [fide] [author][;]
```

The items in square brackets [.md](.md) are optional, so they may be missing. Now I
merge the latin name with the name of the synonym and check if the merged name
is a valid name present in the table. I do this for every occurrence of the
format above. Sometimes there are several occurrences of the latin phrases in
a single `details` field, and I test all of them.


---
