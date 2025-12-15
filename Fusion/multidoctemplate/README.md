# **MultiDocTemplate**

Multiple templates upload module, replace doli tags and create files with revisioning and category functionality for files, for Dolibarr ERP.



# **Description**

This module is merge Project by Doctemplate module created by Transifex (https://transifex.com/projects/p/dolibarr-module-template) under GPLv3 License and Elbmultiupload (https://github.com/dajevtic/dolibarr-files.git) under Mit License finaly it let Mit License for all future develops from autor "miquelpallares@rgpd.barcelona"



# **Translations**

Translations can be define manually by editing files into directories \*langs\*. 

This module contains also a sample configuration for Transifex, under the hidden directory \[.tx](.tx), so it is possible to manage translation using this service. 

For more informations, see the \[translator's documentation](https://wiki.dolibarr.org/index.php/Translator\_documentation).



# **Install**

From the ZIP file and GUI interface



\- If you get the module in a zip file (like when downloading it from the market place \[Dolistore](https://www.dolistore.com)), go into

menu ```Home - Setup - Modules - Deploy external module``` and upload the zip file.

Note: If this screen tell you there is no custom directory, check your setup is correct: 



\- In your Dolibarr installation directory, edit the ```htdocs/conf/conf.php``` file and check that following lines are not commented:



&nbsp;   ```php

&nbsp;   //$dolibarr\_main\_url\_root\_alt ...

&nbsp;   //$dolibarr\_main\_document\_root\_alt ...

&nbsp;   ```

\- Uncomment them if necessary (delete the leading ```//```) and assign a sensible value according to your Dolibarr installation

&nbsp;   For example :

&nbsp;   - UNIX:

&nbsp;       ```php

&nbsp;       $dolibarr\_main\_url\_root\_alt = '/custom';

&nbsp;       $dolibarr\_main\_document\_root\_alt = '/var/www/Dolibarr/htdocs/custom';

&nbsp;       ```

&nbsp;   - Windows:

&nbsp;       ```php

&nbsp;       $dolibarr\_main\_url\_root\_alt = '/custom';

&nbsp;       $dolibarr\_main\_document\_root\_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';

&nbsp;       ```

&nbsp; 

From a GIT repository



\- Clone the repository in ```$dolibarr\_main\_document\_root\_alt/custom/multidoctemplate```



```sh

cd ....../custom

git clone git@github.com:PedrosaBusquets/Fusion\_multi-template.git 

```



\### <a name="final\_steps"></a>Final steps



From your browser:



&nbsp; - Log into Dolibarr as a super-administrator

&nbsp; - Go to "Setup" -> "Modules"

&nbsp; - You should now be able to find and enable the module



# **Licenses**

Main code

Mit Licensce

See file COPYING for more information.



# **Documentation**

All texts and readmes.

# **Possible Errors**

Be carefull merged app can have several errors from not merged correct! 

