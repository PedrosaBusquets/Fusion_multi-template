# Project tips reminder:

##### 

##### -Used vocabulary:

third parties = companies = societe =Thirdparty

Tags = categories

"template" = template file

substitutions see https://wiki.dolibarr.org/index.php/Variable\_substitution\_system

extra fields = extrafields = Complementary atributes



##### -Module name: MultiDocTemplate

##### 

##### -Module rights:

1-Create user rigths for module (read "lire",create "creer",delete "supprimer",view "voir" for part 1"RGPD" and part2 "Templates")



##### 

##### -Module actions:

1-List template files (read rights)

2-Add a template files (créate rights)

3-Delete template files (delete rights)

4-List substitution file (view rights)

5-Generate substitution file (create rights)

6-Delete substitution file (delete rights)

7-List Tags (view rigths)

8-Add Tags (view rigths)

9-Delete Tags (delete rights)



##### -Module description:

###### -Part 1 

1-New TAB in third-party card and in contact off third-party.(view rights requiered)

2- TAB name “RGPD” 

3- third-party and it’s contact (both) card RGPD with Tag/categories filtering for select “template” 

4-Generate button for file creation from selected "template" and save new file with substitutions maded in separate folder for every thirty-party ID and orher folder for each contact ID (create rights requiered)

5-Rename, modify name, delete options requiered. (delete rights requiered)



###### — Part 2

6-New TAB in user/group card (existing user group create rights requiered)

7-TAB name “Templates”

8-Templates card with Tag/categories filtering for select “template” file 

9-Upload button for file for upload template files (.odt, .csv, .xls, .pdf, .doc) it save/store in separate folder for every user group ID

-Common

10-Templates created and stored in separate folder for each user group to be used in Part 1



##### -Module dependencies:

1-TAGS/CATEGORIES module or new solution

2-THIRD PARTIES module

3-USER \& GROUPS module

4-Variable substitution system

5-Aditional extrafields for each module dependencies

6-Translation system \& langs in module (en\_US at last)

