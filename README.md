test_ciblo
==========

A Symfony project created on January 16, 2016, 7:51 pm.

Version symfony 3.0.*

How install : symfony create_project your_directory_projetct
Create new bundle SibloTestBundle: php bin/console generate:bundle
Create metadata files of Database : php bin/console doctrine:mapping:import --force CibloBundle yml
Generate entities : php bin/console doctrine:generate:entities CibloBundle

Command of import data from csv file to insert into database
console command : php bin/console path_of_csv_file --option
To import data of statistic set option to : analytic
To import data of statistic set option to : orders


