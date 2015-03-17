

# Introduction #
This document describes two of Phil Bock's tables called `bryozoans` and
`currentspecies`. Both tables have many thousands of species names and share
some records. The records in `currentspecies` should be more recent.

See:
  * `scripts/mysql/bock_import.sql`
  * `scripts/mysql/bock_clean.sql`


---


# Clean table `bryozoans` #
All deleted records will be put in a new table called `bryozoans_delete`.

Phil Bock marked some records for deletion. He put 'delete' in the `comments`
field, or set `currentname` or `id` to some high value like 99999, or set
`name` to something like 'ignore'.

Count: 829 records

Phil Bock marked some records for extra work. He put something like 'check' in
the `comments` field.

Count: 372 records

There are many records that might be valid, because `currentname` = `id`.
However, they are marked with 0 in the `valid` field. So, this is a
contradiction. I'm not sure which indicator to trust, so I delete the records.

Count: 3961 records

Here's another contradiction. Some records are marked with 1 in the `valid`
field, but they have no value for `currentname`. The value in `currentname`
should be equal to the value in `id`. Again, should I trust the value in
`valid` or should I trust that `currentname` should equal `id`?

Count: 1194 records

Phil Bock marked some records with nonalpha characters. There are specific
rules to follow for each different kind of character. See PhilBock for details.

Count: 1988 records

Some records have `valid` set to 1 and have the word 'synonym' in the field
`status`.

Count: 9 records

The field `currentname` should point to some other record's `id`. Some records
point to non-existant records. I did not delete these records, because doing so
will introduce more bad records with the same problem. That is, there are other
records that will point to the ones that I delete. So, I just leave them for
now.

Count: 633 records


---


# Clean `currentspecies` #
All deleted records will be put in a new table called `currentspecies_delete`.

Some records have `OK` set to 1 and `status` not equal to 'Current'.

Count: 5 records

Some records have non-alpha characters. See PhilBock for details.

Count: 1478 records


---


# Shared Records #
`bryozoans` contains 42202 records.
`currentspecies` contains 21039 records.

## Shared `name` ##
There are 18732 records where the species name exists in both tables.
```
SELECT COUNT(*) FROM bryozoans AS t1, currentspecies AS t2 WHERE t1.name = t2.name;
```

Of those 18732 records, the following are the number of records that have equal
values for the other fields.

_Null values are not compared to other null or non-null values._

| |author|details|comments|valid|date\_created|date\_modified|status|currentnamestring|familyname|
|:|:-----|:------|:-------|:----|:------------|:-------------|:-----|:----------------|:---------|
|= |16446|0 |3 |383|125|57|10|364|0 |
|!=|2282|0 |867|70|643|17778|0 |66|0 |

For example, 383 records share the species name and the validity assignment
that is not null. 70 records have conflicting validity values. Like this:
```
SELECT COUNT(*) FROM bryozoans AS t1, currentspecies AS t2 WHERE t1.name = t2.name AND t1.valid = t2.valid;
```

## Which Table Takes Priority? ##
`currentspecies` is assumed to be more recent. However, sometimes the
`date_modified` in `bryozoans` is more recent than in `currentspecies`. If the
`currentspecies` record replaces the `bryozoans` record, sometimes a value will
be replaced by a NULL. So, information might be lost.


---


# Steps to Combine the Files #

### Combine `bryozoans` with `currentspecies` ###
The two tables do not have identical columns, and `ID` values do not correspond
to the same names in both tables.

The combination can be achieved in a few steps, as shown below.

#### Step 0: Correct or delete troublesome records ####
Follow the instructions in CleanBryozoans and CleanCURRENTSPECIES.

#### Step 1: Replace reference to name with actual name, delete `ID` column ####
  * In `bryozoans`, `Current_name` is an integer that points to the record that contains the current name.
    1. Add a new column for the actual string of the current name called `currentnamestring`.
    1. Fill `currentnamestring` by referencing the values in `Current_name`.

  * In `currentspecies`, `Current_name` contains " was Foo Bar=123" when the record is a synonym.
    1. Add a new column for the actual string of the current name called `currentnamestring`.
    1. Fill `OK` and `currentnamestring` by parsing the "123" out of `Current_name`. Reformat all values from " was Foo Bar=123" to "Foo Bar" where 123 refers to an existing record.
    1. Rename the `Current_Name` column to `Name`.

#### Step 2: Delete unshared columns ####
  1. Delete unnecessary columns from `bryozoans`:
    * `ID`, `Current_name`, `Age`, `Original`, `Newcode`, `Bryozoans2::Name`, `Delete`
  1. Delete unnecessary columns from CURENTSPECIES:
    * `SpeciesID`, `Recent`, `first_name`, `html_page`, `FAMCODE`

#### Step 3: Fix remaining columns ####
  * Now, `bryozoans` has the following columns:
    * `Name`, `currentnamestring`, `Author`, `Details`, `Comments`, `Valid`,` Date_created`, `Date_modified`, `Status`
  * Now, `currentspecies` has the following columns:
    * `Name`, `currentnamestring`, `Author`, `Remarks`, `Date_created`, `Date_modified`, `Status`, `OK`, `FAMILIES::NAME`

  * In `bryozoans`:
    1. Add column `Family`.

  * In `currentspecies`:
    1. Rename `OK` to `Valid`.
    1. Rename `FAMILIES::NAME` to `Family`.
    1. Rename `Remarks` to `Comments`.
    1. Add column `Details`.

#### Step 4: Insert and Replace `currentspecies` into `bryozoans` ####
  * Now, `bryozoans` has the following columns:
    * `Name`, `currentnamestring`, `Author`, `Details`, `Comments`, `Valid`, `Date_created`, `Date_modified`, `Status`, `Family`
  * Now, `currentspecies` has the following columns:
    * `Name`, `currentnamestring`, `Author`, `Details`, `Comments`, `Valid`, `Date_created`, `Date_modified`, `Status`, `Family`

  * For each record in `currentspecies`, insert it into `bryozoans` and replace if the record already exists.

### Problems ###

In `currentspecies`:

  * These records are in the format **" was Foo=123"** and **123** is equal to the `SpeciesID`: 7626 10858 14732 18152 17833
    * **Problem:** The record points to itself, but it should point to a different record.
    * **Solution:** Treat the same way as the other correct records.

  * These records are in the format **" was Foo=123"** and have `OK` set to 1: 1585 1578 13369 2519 589 13133 6214 5427 17809 14223 18152 6726 6727 5898 13121 11618 14186 17141 13933 18728 18721 1203 6042 5344 6049 3920
    * **Problem:** The meaning of `OK` is not clear. Perhaps `OK`=1 names are not synonyms?
    * **Solution:** Treat the same way as the other correct records.