<?php
/**
 * @version $Id: $
 * 
 * @package Kunena
 *
 * @Copyright (C) 2010 Schultschik Websolution All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 **/
// Dont allow direct linking

defined( '_JEXEC' ) or die();
?>
<!--

Changelog
------------

Legend:

* -> Security Fix
# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Kunena Birthday Modul 1.6.5

29-Sep-2010 Sven
^ moved additional option into advanced group
# white page with when other site is schown than kunena
# KunenaConfig
+ Botname translatedable
+ modulclass_sfx
# daytill == 1
+ language string SW_KBIRTHDAY_FORUMPOST_BOTNAME_DEF

Kunena Birthday Modul 1.6.4

10-Sep-2010 Sven
+ use autodeteced function of Kunena
+ new error language string
# wrong results when timeframe 0 for today only
# wrong leap year calc
# sort after inday
^ move daytill calc into SQL 

08-Sep-2010 Sven
# sorting of results by moving from php -> sql
# getdate calc wrong yeardate
+ second sort name/username
- server time option

07-Sep-2010 Sven
# SW_KBIRTHDAY_TIMEFROM_DESC in english file
# some misspellings in the english file
+ sorting output after daytill

Kunena Birthday Modul 1.6.3

09-Aug-2010 Sven
^ string output from str_replace to sprintf
# using the right time writing on the database without offset
# in leapyear calculation, not showing birthday when it goes over the year end
# make new thread when a new year begins

Kunena Birthday Modul 1.6.2

29-July-2010 Sven
+ reference time option: user
+ dropdown to choose template
+ language strings for new functions
^ moved stringreplace for age from template to helper
^ raiseError to raiseWarning
# check if the cb birthday field exist before reading it

28-July-2010 Sven
+ added language string if no birthdays are set
# fixed invalid argument for foreach when no birthdays
^ moved get params into helper.php
# fixed issue in leap year function when after sub the yday is smaller than today
+ serbian language - Thank you @quila from Kunena Team
+ reference time option: server,website,gmt
^ CB birthday field variable now

Kunena Birthday Modul 1.6.1

27-July-2010 Sven
# right userid in SQL query of CB and kunena
# unset user in array when no birhdate is set
# changed sql query to read the birthdate in same way no matter how it is saved
# fixed issue with leap years, 29 february when we have not currently a leap year

Kunena Birthday Modul 1.6.0
-->