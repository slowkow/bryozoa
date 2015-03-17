# MySQL Queries #

```
mysql> DESCRIBE `scratchpads`;
+------------------------+---------------+------+-----+---------+-------+
| Field                  | Type          | Null | Key | Default | Extra |
+------------------------+---------------+------+-----+---------+-------+
| rank_name              | varchar(64)   | NO   | MUL | NULL    |       |
| unit_name1             | varchar(64)   | NO   | MUL | NULL    |       |
| unit_name2             | varchar(64)   | YES  |     | NULL    |       |
| unit_name3             | varchar(64)   | YES  |     | NULL    |       |
| parent_name            | varchar(512)  | YES  | MUL | NULL    |       |
| usage                  | varchar(32)   | NO   |     | NULL    |       |
| taxon_author           | varchar(512)  | YES  |     | NULL    |       |
| accepted_name          | varchar(512)  | YES  |     | NULL    |       |
| unacceptability_reason | varchar(512)  | YES  |     | NULL    |       |
| comments               | varchar(6000) | YES  |     | NULL    |       |
| details                | varchar(6000) | YES  |     | NULL    |       |
| full_name              | varchar(333)  | NO   | PRI |         |       |
+------------------------+---------------+------+-----+---------+-------+
12 rows in set (0.00 sec)

mysql> SELECT COUNT(*) FROM `scratchpads`;
+----------+
| COUNT(*) |
+----------+
|    30471 |
+----------+
1 row in set (0.00 sec)

mysql> SELECT COUNT(*),`rank_name` FROM `scratchpads` GROUP BY `rank_name`
    ->   ORDER BY
    ->     FIELD(`rank_name`,'Phlylum','Class','Order','Suborder','Infraorder',
    ->       'Superfamily','Family','Genus','Species','Subspecies');
+----------+-------------+
| COUNT(*) | rank_name   |
+----------+-------------+
|        1 | Phylum      |
|        4 | Class       |
|        9 | Order       |
|       30 | Suborder    |
|        4 | Infraorder  |
|       44 | Superfamily |
|      355 | Family      |
|     2229 | Genus       |
|    26334 | Species     |
|     1461 | Subspecies  |
+----------+-------------+
10 rows in set (0.00 sec)

mysql> SELECT COUNT(*),`usage` FROM `scratchpads` GROUP BY `usage`;
+----------+---------+
| COUNT(*) | usage   |
+----------+---------+
|     7774 | invalid |
|    22697 | valid   |
+----------+---------+
2 rows in set (0.00 sec)

mysql> SELECT COUNT(*),`rank_name`,`usage` FROM `scratchpads`
    ->   GROUP BY `rank_name`,`usage`
    ->   ORDER BY
    ->     FIELD(`rank_name`,'Phlylum','Class','Order','Suborder','Infraorder',
    ->       'Superfamily','Family','Genus','Species','Subspecies'),
    ->     `usage` desc;
+----------+-------------+---------+
| COUNT(*) | rank_name   | usage   |
+----------+-------------+---------+
|        1 | Phylum      | valid   |
|        3 | Class       | valid   |
|        1 | Class       | invalid |
|        9 | Order       | valid   |
|       25 | Suborder    | valid   |
|        5 | Suborder    | invalid |
|        2 | Infraorder  | valid   |
|        2 | Infraorder  | invalid |
|       42 | Superfamily | valid   |
|        2 | Superfamily | invalid |
|      342 | Family      | valid   |
|       13 | Family      | invalid |
|     2229 | Genus       | valid   |
|    19212 | Species     | valid   |
|     7122 | Species     | invalid |
|      832 | Subspecies  | valid   |
|      629 | Subspecies  | invalid |
+----------+-------------+---------+
17 rows in set (0.00 sec)

mysql> SELECT COUNT(*) FROM `scratchpads`
    -> WHERE `usage` = 'invalid' AND LENGTH(`unacceptability_reason`) < 1;
+----------+
| COUNT(*) |
+----------+
|     7056 |
+----------+
1 row in set (0.00 sec)

mysql> SELECT COUNT(*) FROM `scratchpads`
    -> WHERE `usage` = 'invalid' AND LENGTH(`unacceptability_reason`) > 1;
+----------+
| COUNT(*) |
+----------+
|      695 |
+----------+
1 row in set (0.00 sec)

mysql> SELECT COUNT(*),`parent_name` FROM `scratchpads`
    -> GROUP BY `parent_name` ORDER BY COUNT(*) DESC LIMIT 10;
+----------+---------------------------------+
| COUNT(*) | parent_name                     |
+----------+---------------------------------+
|     1175 | Fenestella Bolten 1798          |
|      497 | Membranipora de Blainville 1830 |
|      472 | Fistulipora Rafinesque 1831     |
|      465 | Polypora M'Coy 1842             |
|      315 | Schizoporella Hincks 1877       |
|      284 | Cellepora Linnaeus 1767         |
|      261 | Smittina Norman 1903            |
|      255 | Onychocella Jullien 1882        |
|      203 | Callopora Gray 1848             |
|      186 | Leioclema Ulrich 1882           |
+----------+---------------------------------+
10 rows in set (0.00 sec)

mysql> SELECT `full_name`,`taxon_author` FROM `scratchpads`
    ->   WHERE `taxon_author` IS NULL OR `taxon_author` NOT REGEXP '[0-9]{4}'
    ->   ORDER BY `full_name`;
+-------------------------------------+----------------------------+
| full_name                           | taxon_author               |
+-------------------------------------+----------------------------+
| Abakana                             |                            |
| Anomalotoechus                      |                            |
| Arcanoporidae                       |                            |
| Asatkinella                         |                            |
| Asperopora inornatum                | (Bassler, )                |
| Atactoporella                       |                            |
| Aviculofenestella                   |                            |
| Bactrellaria pacifica               |                            |
| Bashkirella                         |                            |
| Batoporoidea                        |                            |
| Beania octaeeras                    |                            |
| Beisselinidae                       |                            |
| Bicellariellidae                    |                            |
| Bowerbankia gracilis bengalensis    | Annandale ms               |
| Chaperia bilamellata                |                            |
| Cliocystiramus                      |                            |
| Columnothecidae                     |                            |
| Crepidacantha crenispina            |                            |
| Cribriporella                       |                            |
| Cryptostyloecia                     | NULL                       |
| Cyphotrypa putunggouensis           | Liu, 199x(undated)         |
| Cystoporella                        |                            |
| Dunayeva                            |                            |
| Dybowskites                         |                            |
| Electra betula                      |                            |
| Eridotrypella gansuensis            | Liu, 199x(undated)         |
| Esthonioporella                     |                            |
| Fenestellata                        |                            |
| Fistulipora asteria                 | Fistulipora asteria        |
| Fistulipora procerula               | Liu, 199x(undated)         |
| Fistulipora putunggouensis          | Liu, 199x(undated)         |
| Fistuliramus                        |                            |
| Fistuliramus tuberculatus           | Liu, 199x(undated)         |
| Flexifenestella reteporinaeformis   | (Schulga-Nesterenko, 19??) |
| Ganiella                            |                            |
| Grammanatosoecia                    |                            |
| Hallopora congesta                  | Liu, 199x(undated)         |
| Hemiulrichostylus                   |                            |
| Hesychoxeniidae                     |                            |
| Hesyxochenia                        | NULL                       |
| Hincksina flustroides crassispinata | Calvet,                    |
| Hunanopora                          |                            |
| Ijimaiellia                         | NULL                       |
| Inferusia                           | NULL                       |
| Klugenotus                          |                            |
| Koneprusiella                       | NULL                       |
| Laxifenestella stschugorensis       | (Schulga-Nesterenko, 19??) |
| Laxifenestella stuckenbergi         | (Nikiforova, 19??)         |
| Leioclema clarum                    | Liu, 199x(undated)         |
| Leioclema claudestinum              | Liu, 199x(undated)         |
| Leioclema inornatum                 | Bassler                    |
| Leioclemella                        | NULL                       |
| Leiosellina                         | NULL                       |
| Marsonniella                        | NULL                       |
| Metelipora                          |                            |
| Nekhoroshoviella                    | NULL                       |
| Nemacanthoclema                     |                            |
| Nematoporellidae                    |                            |
| Orbiramus                           |                            |
| Pakrydictya                         |                            |
| Palescharidae                       |                            |
| Paulella                            | NULL                       |
| Pencilleta                          | NULL                       |
| Permofenestella angustataeformis    | (Schulga-Nesterenko, 19??) |
| Permofenestella pentagonalis        | (Schulga-Nesterenko, 19??) |
| Permoleioclema                      |                            |
| Phosphasipho                        |                            |
| Phosphotesta                        |                            |
| Phylloporinella                     |                            |
| Polipora                            |                            |
| Polypora spininodatiformis          | Nikiforova,                |
| Polyporella spininodatiformis       | Nikiforova,                |
| Qilianopora                         |                            |
| Quadricellara                       | NULL                       |
| Radioporidae                        |                            |
| Reussinella                         | NULL                       |
| Rhombopora dangdouensis             | Liu, 199x(undated)         |
| Robinella                           |                            |
| Schischcatella                      |                            |
| Semifenestella                      |                            |
| Sibiredictya                        |                            |
| Stephanollina                       |                            |
| Stereotoechus zonatus               | Liu, 199x(undated)         |
| Taenioporinidae                     |                            |
| x_Adeonoidea_gen                    | NULL                       |
| x_Ascophorina_fam                   | NULL                       |
| x_Ascophorina_gen                   | NULL                       |
| x_Benedeniporoidea_gen              | NULL                       |
| x_Bryozoa_fam                       | NULL                       |
| x_Buguloidea_gen                    | NULL                       |
| x_Calloporoidea_gen                 | NULL                       |
| x_Celleporoidea_gen                 | NULL                       |
| x_Cheilostomata_gen                 | NULL                       |
| x_Cryptostomida_fam                 | NULL                       |
| x_Ctenostomata_fam                  | NULL                       |
| x_Ctenostomata_gen                  | NULL                       |
| x_Cyclostomata_gen                  | NULL                       |
| x_Didymoselloidea_gen               | NULL                       |
| x_Dysnoetoporoidea_gen              | NULL                       |
| x_Mamilloporoidea_gen               | NULL                       |
| x_Membraniporoidea_gen              | NULL                       |
| x_Microporoidea_gen                 | NULL                       |
| x_Neocheilostomina_fam              | NULL                       |
| x_Neocheilostomina_superfam         | NULL                       |
| x_Schizoporelloidea_gen             | NULL                       |
| x_Smittinoidea_gen                  | NULL                       |
| x_Tubuliporina_gen                  | NULL                       |
| Yunnanopora                         |                            |
+-------------------------------------+----------------------------+
108 rows in set (0.00 sec)

mysql> SELECT COUNT(*),`unacceptability_reason` FROM `scratchpads`
    -> WHERE LENGTH(`unacceptability_reason`) > 1 GROUP BY `unacceptability_reason`;
+----------+------------------------+
| COUNT(*) | unacceptability_reason |
+----------+------------------------+
|      694 | junior synonym         |
|        1 | nomen dubium           |
+----------+------------------------+
2 rows in set (0.00 sec)
```

# Non-alpha characters #
There are some Unicode characters that might not appear correctly in the final
output file `scripts/mysql/output/scratchpads.tab`.

You can find the rows containing those characters with this command:

```
grep --invert-match --extended-regexp "^.+$" scratchpads.tab
```