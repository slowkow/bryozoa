

# ITIS upload format for Scratchpads #
The format required for uploading to Scratchpads is a simple tab-delimited file
with the fields listed below. My descriptions are taken directly from
[ITIS Physical Model](http://www.itis.gov/pdf/phys_mod_all.pdf).

_**The format for Scratchpads is not identical to the accepted ITIS format.**_

I call this special format `ITIS-for-Scratchpads` throughout my code.
Scratchpads calls this the `ITIS Parent/Child` format.

# Instructions for importing an ITIS-for-Scratchpads file #

  1. Go to http://bryozone.myspecies.info/admin/content/taxonomy/add/vocabulary
  1. Give your taxonomy a name and description.
  1. Open the `Settings` menu.
  1. Under `Content type` select `ITIS Name`.
  1. Save.
  1. Go to http://bryozone.myspecies.info/admin/content/taxonomy/import
  1. Click on your taxonomy.
  1. Open `CSV File`, select `ITIS Parent/Child` under `File type`.
  1. Import your file.

# Fields and rules #
A proper ITIS-for-Scratchpads file will have most of these fields. It's not
clear which fields are absolutely required for a successful import.

**unit\_name1**
  * For monomials this will be the only name field entered. For binomials/polynomials, this field will be used for the first part of the name.

**unit\_name2**
  * For binomials, this will be the last field populated for the name. For trinomials and quadrinomials, this will be the second position populated.

**unit\_name3**
  * For trinomials this field will be populated with the last part of the taxonomic name. For quadrinomials and hybrid formulas this field will be populated with the third part of the name.

**unit\_name4**
  * This is the final position populated for quadrinomials or hybrid formulas.

**rank\_name**
  * Accepted values:
    * `Kingdom, Subkingdom, Phylum, Subphylum, Superclass, Class, Subclass, Infraclass, Superorder, Order, Suborder, Infraorder, Superfamily, Family, Subfamily, Tribe, Subtribe, Genus, Subgenus, Species, Subspecies`

**usage**
  * Accepted values:
    * `valid, invalid`

**taxon\_author**
  * The name of the author followed by a comma followed by the year. May be in parentheses: `(Melander, 1913)`

**parent\_name**
  * Full name of the parent taxon, followed by the author's name and date: `Hemeromyia washingtona (Melander, 1913)`

**unacceptability\_reason**
  * Accepted values:
    * `database artifact, misspelling, nomen nudem, incertae sedis, junior homonym, junior synonym, nomen dubium`

**accepted\_name**
  * Leave blank for valid names. Do not include author name and date.