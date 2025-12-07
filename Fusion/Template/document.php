<?php
/* Copyright (C) 2002-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2010      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2013      Cédric Salvador      <csalvador@gpcsolutions.fr>
 * Copyright (C) 2015      Marcos García        <marcosgdf@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/societe/document.php
 *  \brief      Tab for documents linked to third party
 *  \ingroup    societe
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/doctemplate/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/doctemplate/class/actions_doctemplate.class.php';

$langs->loadLangs(array("companies", "other"));

$action=GETPOST('action','aZ09');
$confirm=GETPOST('confirm');
$id=(GETPOST('socid','int') ? GETPOST('socid','int') : GETPOST('id','int'));
$ref = GETPOST('ref', 'alpha');



// Get parameters
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if (empty($page) || $page == -1) { $page = 0; }     // If $page is not defined, or '' or -1
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder) $sortorder="ASC";
if (! $sortfield) $sortfield="position_name";

$object = new ActionsDoctemplate($db);

	$upload_dir = DOL_DATA_ROOT.'/doctemplates/thirdparties/'.$filename;
	//$courrier_dir = $conf->societe->multidir_output[$object->entity] . "/courrier/" . get_exdir($object->id,0,0,0,$object,'thirdparty');





/*
 * Actions
 */






// Submit file/link
if (GETPOST('sendit','alpha') && ! empty($conf->global->MAIN_UPLOAD_DOC))
{
	if (! empty($_FILES))
	{
		if (is_array($_FILES['userfile']['tmp_name'])) $userfiles=$_FILES['userfile']['tmp_name'];
		else $userfiles=array($_FILES['userfile']['tmp_name']);

		foreach($userfiles as $key => $userfile)
		{
			if (empty($_FILES['userfile']['tmp_name'][$key]))
			{
				$error++;
				if ($_FILES['userfile']['error'][$key] == 1 || $_FILES['userfile']['error'][$key] == 2){
					setEventMessages($langs->trans('ErrorFileSizeTooLarge'), null, 'errors');
				}
				else {
					setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("File")), null, 'errors');
				}
			}
		}

		if (! $error)
		{
			// Define if we have to generate thumbs or not
			$generatethumbs = 1;
			if (GETPOST('section_dir')) $generatethumbs=0;

			
			
			if (! empty($upload_dirold) && ! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))
			{
				$result = dol_add_file_process($upload_dirold, 0, 0, 'userfile', GETPOST('savingdocmask', 'alpha'), null, '', $generatethumbs);
			}
			
			
			elseif (! empty($upload_dir))
			{
				$result = dol_add_file_process($upload_dir, 0, 0, 'userfile', GETPOST('savingdocmask', 'alpha'), null, '', $generatethumbs);
			}
		}
	}
}
elseif (GETPOST('linkit','none') && ! empty($conf->global->MAIN_UPLOAD_DOC))
{
    $link = GETPOST('link', 'alpha');
    if ($link)
    {
        if (substr($link, 0, 7) != 'http://' && substr($link, 0, 8) != 'https://' && substr($link, 0, 7) != 'file://') {
            $link = 'http://' . $link;
        }
        dol_add_file_process($upload_dir, 0, 1, 'userfile', null, $link, '', 0);
    }
}


// Delete file/link
if ($action == 'confirm_deletefile' && $confirm == 'yes')
{
        $urlfile = GETPOST('urlfile', 'alpha', 0, null, null, 1);				// Do not use urldecode here ($_GET and $_REQUEST are already decoded by PHP).
        if (GETPOST('section', 'alpha')) 	// For a delete from the ECM module, upload_dir is ECM root dir and urlfile contains relative path from upload_dir
        {
        	$file = $upload_dir . (preg_match('/\/$/', $upload_dir) ? '' : '/') . $urlfile;
        }
        else								// For a delete from the file manager into another module, or from documents pages, upload_dir contains already path to file from module dir, so we clean path into urlfile.
		{
       		$urlfile=basename($urlfile);
       		$file = $upload_dir . (preg_match('/\/$/', $upload_dir) ? '' : '/') . $urlfile;
			if (! empty($upload_dirold)) $fileold = $upload_dirold . "/" . $urlfile;
		}
        $linkid = GETPOST('linkid', 'int');

        if ($urlfile)		// delete of a file
        {
	        $dir = dirname($file).'/';		// Chemin du dossier contenant l'image d'origine
	        $dirthumb = $dir.'/thumbs/';	// Chemin du dossier contenant la vignette (if file is an image)

	        $ret = dol_delete_file($file, 0, 0, 0, (is_object($object)?$object:null));
            if (! empty($fileold)) dol_delete_file($fileold, 0, 0, 0, (is_object($object)?$object:null));     // Delete file using old path

	        // Si elle existe, on efface la vignette
	        if (preg_match('/(\.jpg|\.jpeg|\.bmp|\.gif|\.png|\.tiff)$/i',$file,$regs))
	        {
		        $photo_vignette=basename(preg_replace('/'.$regs[0].'/i','',$file).'_small'.$regs[0]);
		        if (file_exists(dol_osencode($dirthumb.$photo_vignette)))
		        {
			        dol_delete_file($dirthumb.$photo_vignette);
		        }

		        $photo_vignette=basename(preg_replace('/'.$regs[0].'/i','',$file).'_mini'.$regs[0]);
		        if (file_exists(dol_osencode($dirthumb.$photo_vignette)))
		        {
			        dol_delete_file($dirthumb.$photo_vignette);
		        }
	        }

            if ($ret) setEventMessages($langs->trans("FileWasRemoved", $urlfile), null, 'mesgs');
            else setEventMessages($langs->trans("ErrorFailToDeleteFile", $urlfile), null, 'errors');
        }
        elseif ($linkid)	// delete of external link
        {
            require_once DOL_DOCUMENT_ROOT . '/core/class/link.class.php';
            $link = new Link($db);
            $link->id = $linkid;
            $link->fetch();
            $res = $link->delete($user);

            $langs->load('link');
            if ($res > 0) {
                setEventMessages($langs->trans("LinkRemoved", $link->label), null, 'mesgs');
            } else {
                if (count($link->errors)) {
                    setEventMessages('', $link->errors, 'errors');
                } else {
                    setEventMessages($langs->trans("ErrorFailedToDeleteLink", $link->label), null, 'errors');
                }
            }
        }

        if (is_object($object) && $object->id > 0)
        {
        	if ($backtopage)
        	{
        		header('Location: ' . $backtopage);
        		exit;
        	}
        	else
        	{
        		header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id.(GETPOST('section_dir','alpha')?'&section_dir='.urlencode(GETPOST('section_dir','alpha')):'').(!empty($withproject)?'&withproject=1':''));
        		exit;
        	}
        }
}
elseif ($action == 'confirm_updateline' && GETPOST('save','alpha') && GETPOST('link', 'alpha'))
{
    require_once DOL_DOCUMENT_ROOT . '/core/class/link.class.php';
    $langs->load('link');
    $link = new Link($db);
    $link->id = GETPOST('linkid', 'int');
    $f = $link->fetch();
    if ($f)
    {
        $link->url = GETPOST('link', 'alpha');
        if (substr($link->url, 0, 7) != 'http://' && substr($link->url, 0, 8) != 'https://' && substr($link->url, 0, 7) != 'file://')
        {
            $link->url = 'http://' . $link->url;
        }
        $link->label = GETPOST('label', 'alpha');
        $res = $link->update($user);
        if (!$res)
        {
            setEventMessages($langs->trans("ErrorFailedToUpdateLink", $link->label), null, 'mesgs');
        }
    }
    else
    {
        //error fetching
    }
}
elseif ($action == 'renamefile' && GETPOST('renamefilesave','alpha'))
{
	// For documents pages, upload_dir contains already path to file from module dir, so we clean path into urlfile.
	if (! empty($upload_dir))
	{
		$filenamefrom=dol_sanitizeFileName(GETPOST('renamefilefrom','alpha'), '_', 0);	// Do not remove accents
		$filenameto=dol_sanitizeFileName(GETPOST('renamefileto','alpha'), '_', 0);		// Do not remove accents

        if ($filenamefrom != $filenameto)
        {
	        // Security:
	        // Disallow file with some extensions. We rename them.
	        // Because if we put the documents directory into a directory inside web root (very bad), this allows to execute on demand arbitrary code.
	        if (preg_match('/\.htm|\.html|\.php|\.pl|\.cgi$/i',$filenameto) && empty($conf->global->MAIN_DOCUMENT_IS_OUTSIDE_WEBROOT_SO_NOEXE_NOT_REQUIRED))
	        {
	            $filenameto.= '.noexe';
	        }

	        if ($filenamefrom && $filenameto)
	        {
	            $srcpath = $upload_dir.'/'.$filenamefrom;
	            $destpath = $upload_dir.'/'.$filenameto;

	            $reshook=$hookmanager->initHooks(array('actionlinkedfiles'));
	            $parameters=array('filenamefrom' => $filenamefrom, 'filenameto' => $filenameto, 'upload_dir' => $upload_dir);
	            $reshook=$hookmanager->executeHooks('renameUploadedFile', $parameters, $object);

	            if (empty($reshook))
	            {
	            	if (! file_exists($destpath))
	            	{
	            		$result = dol_move($srcpath, $destpath);
			            if ($result)
			            {
			            	// Define if we have to generate thumbs or not
			            	$generatethumbs = 1;
			            	// When we rename a file from the file manager in ecm, we must not regenerate thumbs (not a problem, we do pass here)
			            	// When we rename a file from the website module, we must not regenerate thumbs (module = medias in such a case)
			            	// but when we rename from a tab "Documents", we must regenerate thumbs
			            	if (GETPOST('modulepart') == 'medias') $generatethumbs=0;

			            	if ($generatethumbs)
			            	{
			            		if ($object->id)
				            	{
				                	$object->addThumbs($destpath);
				            	}

				                // TODO Add revert function of addThumbs to remove thumbs with old name
				                //$object->delThumbs($srcpath);
			            	}

			                setEventMessages($langs->trans("FileRenamed"), null);
			            }
			            else
			            {
			                $langs->load("errors"); // key must be loaded because we can't rely on loading during output, we need var substitution to be done now.
			                setEventMessages($langs->trans("ErrorFailToRenameFile", $filenamefrom, $filenameto), null, 'errors');
			            }
	            	}
	            	else
	            	{
	            		$langs->load("errors"); // key must be loaded because we can't rely on loading during output, we need var substitution to be done now.
	            		setEventMessages($langs->trans("ErrorDestinationAlreadyExists", $filenameto), null, 'errors');
	            	}
	            }
	        }
        }
    }

    // Update properties in ECM table
    if (GETPOST('ecmfileid', 'int') > 0)
    {
    	$shareenabled = GETPOST('shareenabled', 'alpha');

    	include_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';
	    $ecmfile=new EcmFiles($db);
	    $result = $ecmfile->fetch(GETPOST('ecmfileid', 'int'));
	    if ($result > 0)
	    {
	    	if ($shareenabled)
		    {
		    	if (empty($ecmfile->share))
		    	{
		    		require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
		    		$ecmfile->share = getRandomPassword(true);
		    	}
		    }
		    else
		    {
		    	$ecmfile->share = '';
		    }
		    $result = $ecmfile->update($user);
		    if ($result < 0)
		    {
		    	setEventMessages($ecmfile->error, $ecmfile->errors, 'warnings');
		    }
	    }
    }
}






/*
 * View
 */

$form = new Form($db);


llxHeader('',$title,$help_url);

if (1)
{
	/*
	 * Show tabs
	 */
	
	

	$form=new Form($db);

	
	
	 print load_fiche_titre('Templates', $linktocreatetime, 'title_generic.png');
	



	// Build file list
	$filearray=dol_dir_list($upload_dir,"files",0,'','(\.meta|_preview.*\.png)$',$sortfield,(strtolower($sortorder)=='desc'?SORT_DESC:SORT_ASC),1);
	$totalsize=0;
	foreach($filearray as $key => $file)
	{
		$totalsize+=$file['size'];
	}
	
	


  //  dol_banner_tab($object, 'socid', $linkback, ($user->societe_id?0:1), 'rowid', 'nom');


 

	$modulepart = 'societe';
	$permission = $user->rights->societe->creer;
	$permtoedit = $user->rights->societe->creer;
	//$param = '&id=' . $object->id;
	
	
	
	
	
	
	
	// Protection to avoid direct call of template
if (empty($langs) || ! is_object($langs))
{
	print "Error, template page can't be called as URL";
	exit;
}


$langs->load("link");
if (empty($relativepathwithnofile)) $relativepathwithnofile='';
if (empty($permtoedit)) $permtoedit=-1;

// Drag and drop for up and down allowed on product, thirdparty, ...
// The drag and drop call the page core/ajax/row.php
// If you enable the move up/down of files here, check that page that include template set its sortorder on 'position_name' instead of 'name'
// Also the object->fk_element must be defined.
$disablemove=1;
if (in_array($modulepart, array('product', 'produit', 'societe', 'user', 'ticket', 'holiday', 'expensereport'))) $disablemove=0;



/*
 * Confirm form to delete
 */

if ($action == 'delete')
{
	$langs->load("companies");	// Need for string DeleteFile+ConfirmDeleteFiles
	print $form->formconfirm(
			$_SERVER["PHP_SELF"] . '?id=' . $object->id . '&urlfile=' . urlencode(GETPOST("urlfile")) . '&linkid=' . GETPOST('linkid', 'int') . (empty($param)?'':$param),
			$langs->trans('DeleteFile'),
			$langs->trans('ConfirmDeleteFile'),
			'confirm_deletefile',
			'',
			0,
			1
	);
}

$formfile=new FormFile($db);

// We define var to enable the feature to add prefix of uploaded files
$savingdocmask='';
if (empty($conf->global->MAIN_DISABLE_SUGGEST_REF_AS_PREFIX))
{
	//var_dump($modulepart);
	if (in_array($modulepart,array('facture_fournisseur','commande_fournisseur','facture','commande','propal','supplier_proposal','ficheinter','contract','expedition','project','project_task','expensereport','tax', 'produit', 'product_batch')))
	{
		$savingdocmask=dol_sanitizeFileName($object->ref).'-__file__';
	}
	/*if (in_array($modulepart,array('member')))
	{
		$savingdocmask=$object->login.'___file__';
	}*/
}

// Show upload form (document and links)
$formfile->form_attach_new_file(
    $_SERVER["PHP_SELF"].'?id='.$object->id.(empty($withproject)?'':'&withproject=1'),
    '',
    0,
    0,
    $permission,
    $conf->browser->layout == 'phone' ? 40 : 60,
    $object,
	'',
	1,
	$savingdocmask,
	0
);

// List of document
$formfile->list_of_documents(
    $filearray,
    $object,
    $modulepart,
    $param,
    0,
    $relativepathwithnofile,		// relative path with no file. For example "0/1"
    $permission,
    0,
    '',
    0,
    '',
    '',
    0,
    $permtoedit,
    $upload_dir,
    $sortfield,
    $sortorder,
    $disablemove
);



	
	
	
	
}


// End of page
llxFooter();
$db->close();
