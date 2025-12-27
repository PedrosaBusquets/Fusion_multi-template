<?php
/*Copyright (C) 2025     Miquel Pallares    <miquel.pallares@rgpd.barcelona>
*/
/**
 * \file                  htdocs/multidoctemplate/card.php
 * \ingroup               societe
 * \brief                 Third party card page & Management page of object additional files and templates
*/
//Multi
define("JQUERY_MULTISELECT_V4", 1);
require_once DOL_DOCUMENT_ROOT . '/multidoctemplate/class/elb.file.grouping.class.php';
require_once DOL_DOCUMENT_ROOT . '/multidoctemplate/class/elb.file.category.class.php';
require_once DOL_DOCUMENT_ROOT . '/multidoctemplate/class/elb.html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/multidoctemplate/class/utils/elb.file.session.class.php';
require_once DOL_DOCUMENT_ROOT . '/multidoctemplate/class/view/elb.file.view.class.php';
//Equal
require_once '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
//template
require_once DOL_DOCUMENT_ROOT.'/custom/multidoctemplate/core/lib/company.lib.php';
//template - check is requiered??
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
//multi - member needed files
if ($conf->adherent->enabled) {
    require_once DOL_DOCUMENT_ROOT . '/core/lib/member.lib.php';
    require_once DOL_DOCUMENT_ROOT . '/adherents/class/adherent.class.php';
    require_once DOL_DOCUMENT_ROOT . '/adherents/class/adherent_type.class.php';
}
//multi - third party needed files
if ($conf->societe->enabled) {
    require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
    require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
}
//multi- module needed files
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/lib/elbmultiupload.lib.php';
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.file.class.php';
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.file_mapping.class.php';
require_once DOL_DOCUMENT_ROOT . '/elbmultiupload/class/elb.common.manager.class.php';

//template
require_once DOL_DOCUMENT_ROOT.'/custom/multidoctemplate/class/societe.class.php';
//template langs multi deprecated
$langs->loadLangs(array("companies","commercial","bills","banks","users"));
if (! empty($conf->categorie->enabled)) $langs->load("categories");
if (! empty($conf->incoterm->enabled)) $langs->load("incoterm");
if (! empty($conf->notification->enabled)) $langs->load("mails");
$mesg=''; $error=0; $errors=array();



