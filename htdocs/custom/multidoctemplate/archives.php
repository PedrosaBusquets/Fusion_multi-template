<?php
/* MultiDocTemplate - Dolibarr module
 * GPL v3+
 */

/**
 *  \file       htdocs/custom/multidoctemplate/archives.php
 *  \ingroup    multidoctemplate
 *  \brief      Archives tab for Thirdparty/Contact (MultiDocTemplate)
 */

// Load Dolibarr environment
require '../../main.inc.php'; // Adjust if path differs

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';

if (empty($user->rights->multidoctemplate->read)) {
    accessforbidden();
}

$langs->loadLangs(array('multidoctemplate@multidoctemplate', 'companies', 'contacts'));

// Parameters
$id         = GETPOST('id', 'int');          // Thirdparty or Contact ID
$element    = GETPOST('element', 'alpha');   // 'societe' or 'contact' (you can adapt this)
$action     = GETPOST('action', 'alpha');
$token      = GETPOST('token', 'alpha');

$object = null;
$objecttype = '';

// Determine context: thirdparty vs contact
if ($element === 'societe' || empty($element)) {
    $objecttype = 'societe';
    $object = new Societe($db);
    if ($id > 0) {
        $res = $object->fetch($id);
        if ($res <= 0) dol_print_error($db);
    }
} elseif ($element === 'contact') {
    $objecttype = 'contact';
    $object = new Contact($db);
    if ($id > 0) {
        $res = $object->fetch($id);
        if ($res <= 0) dol_print_error($db);
    }
} else {
    accessforbidden('Bad element');
}

// Security on object
if ($objecttype === 'societe') {
    $result = restrictedArea($user, 'societe', $object->id, 'societe', '');
} elseif ($objecttype === 'contact') {
    $result = restrictedArea($user, 'societe', $object->socid, 'societe', 'contact'); // Check linked thirdparty rights
}

// Form
$form = new Form($db);

// --------------------------------------------------------------------
// Actions
// --------------------------------------------------------------------

// Generate new archive from template
if ($action === 'generate' && !empty($user->rights->multidoctemplate->write)) {
    if (!checkToken()) {
        accessforbidden('Bad token');
    }

    $fk_template = GETPOST('fk_template', 'int');
    $archivelabel = trim(GETPOST('archivelabel', 'restricthtml'));

    // TODO: Plug your generator logic here:
    // - Load template
    // - Use your documentgenerator.class.php to generate document for $object
    // - Store archive (file on disk + row in your archives table)

    /*
    require_once DOL_DOCUMENT_ROOT.'/custom/multidoctemplate/class/documentgenerator.class.php';
    $generator = new MultiDocDocumentGenerator($db);
    $result = $generator->generateForObject($object, array(
        'fk_template'  => $fk_template,
        'archivelabel' => $archivelabel,
    ));
    if ($result > 0) {
        setEventMessages($langs->trans('MultiDocTemplateArchiveCreated'), null, 'mesgs');
    } else {
        setEventMessages($generator->error, $generator->errors, 'errors');
    }
    */

    header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id.'&element='.$objecttype);
    exit;
}

// Delete archive
if ($action === 'deletearchive' && !empty($user->rights->multidoctemplate->delete)) {
    if (!checkToken()) {
        accessforbidden('Bad token');
    }

    $archiveid = GETPOST('archiveid', 'int');

    // TODO: Call your Archive class to delete archive row + file
    /*
    require_once DOL_DOCUMENT_ROOT.'/custom/multidoctemplate/class/archive.class.php';
    $archive = new MultiDocArchive($db);
    $res = $archive->fetch($archiveid);
    if ($res > 0) {
        $resdel = $archive->delete($user);
        if ($resdel > 0) {
            setEventMessages($langs->trans('RecordDeleted'), null, 'mesgs');
        } else {
            setEventMessages($archive->error, $archive->errors, 'errors');
        }
    }
    */

    header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id.'&element='.$objecttype);
    exit;
}

// Regenerate archive (optional, if you support it)
if ($action === 'regen' && !empty($user->rights->multidoctemplate->write)) {
    if (!checkToken()) {
        accessforbidden('Bad token');
    }

    $archiveid = GETPOST('archiveid', 'int');

    // TODO: Implement regeneration if needed using your generator + archive info

    header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id.'&element='.$objecttype);
    exit;
}

// --------------------------------------------------------------------
// Load templates available for this object (for the "Generate" form)
// --------------------------------------------------------------------

$availableTemplates = array(); // array(id => label)

/*
require_once DOL_DOCUMENT_ROOT.'/custom/multidoctemplate/class/template.class.php';
$template = new MultiDocTemplateTemplate($db);
$availableTemplates = $template->getForObject($object); // implement this in your class
*/

// --------------------------------------------------------------------
// Load archives list for this object
// --------------------------------------------------------------------

// Expected list item structure:
// (object) array(
//   'id'        => 123,
//   'label'     => 'My generated doc',
//   'template'  => 'Template Label',
//   'filename'  => 'path/to/file.ext',
//   'datec'     => '...',
//   'fk_user'   => '...',
//   'user_name' => '...',
// )

$archives = array();

/*
require_once DOL_DOCUMENT_ROOT.'/custom/multidoctemplate/class/archive.class.php';
$archive = new MultiDocArchive($db);
$archives = $archive->getListForObject($object); // implement this
*/

// --------------------------------------------------------------------
// View
// --------------------------------------------------------------------

$title = $langs->trans('MultiDocTemplateArchives');
llxHeader('', $title);

// Prepare head (tabs)
if ($objecttype === 'societe') {
    $head = societe_prepare_head($object);
    dol_fiche_head($head, 'multidoctemplate_archives', $langs->trans("ThirdParty"), 0, 'company');
} elseif ($objecttype === 'contact') {
    $head = contact_prepare_head($object);
    dol_fiche_head($head, 'multidoctemplate_archives', $langs->trans("Contact"), 0, 'contact');
}

// Card header
if ($objecttype === 'societe') {
    print '<table class="border centpercent">';
    print '<tr><td class="titlefield">'.$langs->trans("ThirdPartyName").'</td>';
    print '<td>'.dol_escape_htmltag($object->name).'</td></tr>';
    print '<tr><td>'.$langs->trans("CompanyName").'</td>';
    print '<td>'.dol_escape_htmltag($object->name_alias).'</td></tr>';
    print '</table>';
    print '<br>';
} elseif ($objecttype === 'contact') {
    print '<table class="border centpercent">';
    print '<tr><td class="titlefield">'.$langs->trans("Contact").'</td>';
    print '<td>'.dol_escape_htmltag($object->getFullName($langs)).'</td></tr>';
    if ($object->socid > 0) {
        $thirdparty = new Societe($db);
        if ($thirdparty->fetch($object->socid) > 0) {
            print '<tr><td>'.$langs->trans("ThirdParty").'</td>';
            print '<td>'.$thirdparty->getNomUrl(1).'</td></tr>';
        }
    }
    print '</table>';
    print '<br>';
}

// Title
print load_fiche_titre($langs->trans('MultiDocTemplateArchives'), '', 'file@multidoctemplate');

// --------------------------------------------------------------------
// Generate form
// --------------------------------------------------------------------

if (!empty($user->rights->multidoctemplate->write)) {
    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&element='.$objecttype.'">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="generate">';

    print '<div class="div-table-responsive-no-min">';
    print '<table class="noborder centpercent">';
    print '<tr class="liste_titre">';
    print '<th colspan="4">'.$langs->trans('MultiDocTemplateGenerateNew').'</th>';
    print '</tr>';

    print '<tr class="oddeven">';
    print '<td class="fieldrequired">'.$langs->trans('Template').'</td>';
    print '<td>';
    if (!empty($availableTemplates)) {
        print $form->selectarray('fk_template', $availableTemplates, '', 1);
    } else {
        print $langs->trans('NoTemplates');
    }
    print '</td>';

    print '<td>'.$langs->trans('Label').'</td>';
    print '<td><input type="text" class="flat" name="archivelabel" value=""></td>';
    print '</tr>';

    print '<tr class="oddeven">';
    print '<td colspan="4" class="center">';
    if (!empty($availableTemplates)) {
        print '<input type="submit" class="button" value="'.$langs->trans('Generate').'">';
    } else {
        print '<span class="opacitymedium">'.$langs->trans('NoTemplates').'</span>';
    }
    print '</td>';
    print '</tr>';

    print '</table>';
    print '</div>';

    print '</form>';
    print '<br>';
}

// --------------------------------------------------------------------
// Archives list
// --------------------------------------------------------------------

print '<div class="div-table-responsive">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<th>'.$langs->trans('Date').'</th>';
print '<th>'.$langs->trans('Label').'</th>';
print '<th>'.$langs->trans('Template').'</th>';
print '<th>'.$langs->trans('Author').'</th>';
print '<th>'.$langs->trans('File').'</th>';
print '<th class="right">'.$langs->trans('Actions').'</th>';
print '</tr>';

if (empty($archives)) {
    print '<tr class="oddeven"><td colspan="6" class="center">'.$langs->trans('NoRecords').'</td></tr>';
} else {
    $var = true;
    foreach ($archives as $obj) {
        $var = !$var;
        print '<tr class="'.($var ? 'pair' : 'impair').'">';

        // Date
        print '<td>'.dol_print_date($db->jdate($obj->datec), 'dayhour').'</td>';

        // Label
        print '<td>'.dol_escape_htmltag($obj->label).'</td>';

        // Template
        print '<td>'.dol_escape_htmltag($obj->template).'</td>';

        // Author
        print '<td>'.dol_escape_htmltag($obj->user_name).'</td>';

        // File (download link)
        $relpath = $obj->filename; // relative path into modulepart multidoctemplate
        $fileurl = DOL_URL_ROOT.'/document.php?modulepart=multidoctemplate&file='.urlencode($relpath);
        print '<td><a href="'.$fileurl.'">'.$langs->trans('Download').'</a></td>';

        // Actions
        print '<td class="right nowraponall">';

        if (!empty($user->rights->multidoctemplate->write)) {
            $urlregen = $_SERVER["PHP_SELF"].'?id='.$object->id.'&element='.$objecttype.'&action=regen&archiveid='.$obj->id.'&token='.newToken();
            print '<a class="butActionSmall" href="'.$urlregen.'">'.$langs->trans('Regenerate').'</a> ';
        }

        if (!empty($user->rights->multidoctemplate->delete)) {
            $urldel = $_SERVER["PHP_SELF"].'?id='.$object->id.'&element='.$objecttype.'&action=deletearchive&archiveid='.$obj->id.'&token='.newToken();
            $confirm = dol_escape_js($langs->transnoentities('ConfirmDelete'));
            print '<a class="butActionDelete" href="'.$urldel.'" onclick="return confirm(\''.$confirm.'\');">';
            print $langs->trans('Delete');
            print '</a>';
        }

        print '</td>';

        print '</tr>';
    }
}

print '</table>';
print '</div>';

dol_fiche_end();
llxFooter();
$db->close();