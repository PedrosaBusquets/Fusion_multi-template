<?php
/* Copyright ...
 * GPL v3+ ...
 */

/**
 *  \file       htdocs/custom/multidoctemplate/templates.php
 *  \ingroup    multidoctemplate
 *  \brief      Templates management page (MultiDocTemplate)
 */

require '../config.php'; // Adapt path if needed (e.g. ../../main.inc.php)
if (!defined('NOREQUIREDB'))     define('NOREQUIREDB', '0');
if (!defined('NOREQUIREUSER'))   define('NOREQUIREUSER', '0');
if (!defined('NOREQUIRESOC'))    define('NOREQUIRESOC', '0');
if (!defined('NOREQUIRETRAN'))   define('NOREQUIRETRAN', '0');
if (!defined('NOCSRFCHECK'))     define('NOCSRFCHECK', '0');
if (!defined('NOREQUIREMENU'))   define('NOREQUIREMENU', '0');
if (!defined('NOREQUIREHTML'))   define('NOREQUIREHTML', '0');
if (!defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX', '0');

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

// Security check
if (!isset($user->rights->multidoctemplate->read)) {
    accessforbidden();
}
if (empty($user->rights->multidoctemplate->read)) {
    accessforbidden();
}

$langs->loadLangs(array('multidoctemplate@multidoctemplate', 'companies', 'users'));

// Parameters
$action           = GETPOST('action', 'alpha');
$token            = GETPOST('token', 'alpha');
$search_template  = trim(GETPOST('search_template', 'alpha'));
$search_tag       = trim(GETPOST('search_tag', 'alpha'));
$sortfield        = GETPOST('sortfield', 'alpha');
$sortorder        = GETPOST('sortorder', 'alpha');
$page             = max(0, (int) GETPOST('page', 'int'));
$limit            = GETPOST('limit', 'int') > 0 ? GETPOST('limit', 'int') : $conf->liste_limit;
$offset           = $page * $limit;

// Objects
$form = new Form($db);

// --------------------------------------------------------------------
// Actions (create/update/delete) – keep your existing business logic
// --------------------------------------------------------------------

if ($action == 'create' && $user->rights->multidoctemplate->write) {
    if (!checkToken()) {
        accessforbidden('Bad token');
    }

    // TODO: call your existing code to create a new template
    // e.g. $res = createTemplateFromPost($db, $user);

    if (!empty($res) && $res > 0) {
        setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    } else {
        // setEventMessages('Error...', $errors, 'errors');
    }
}

if ($action == 'delete' && $user->rights->multidoctemplate->delete) {
    if (!checkToken()) {
        accessforbidden('Bad token');
    }

    $id = GETPOST('id', 'int');

    // TODO: call your existing delete logic for template $id

    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

// --------------------------------------------------------------------
// Load templates list – use your existing model / db logic
// --------------------------------------------------------------------

// Expected result structure (you adapt this to your real code):
// $templatesByTag = array(
//     'FolderA' => array( (object)array('id'=>1, 'label'=>'...', 'filename'=>'...', 'groupname'=>'...', 'format'=>'ODT', 'datec'=>...), ... ),
//     'FolderB' => array(...),
// );
$templatesByTag = array();

// TODO: replace with your real loading code; the following is just schema:
//
// $sql = "SELECT ... FROM ".MAIN_DB_PREFIX."multidoctemplate_template ...";
// add search conditions on $search_template, $search_tag
// add sort on $sortfield/$sortorder
// $resql = $db->query($sql);
// while ($obj = $db->fetch_object($resql)) {
//     $tag = (string) $obj->tag;
//     if (!isset($templatesByTag[$tag])) $templatesByTag[$tag] = array();
//     $templatesByTag[$tag][] = $obj;
// }

// --------------------------------------------------------------------
// View
// --------------------------------------------------------------------

$title = $langs->trans('MultiDocTemplateTemplates');
llxHeader('', $title);

print load_fiche_titre($title, '', 'multidoctemplate@multidoctemplate');

// Search & top actions
print '<form method="GET" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';

print '<div class="fichecenter">';
print '<div class="fichehalfleft">';

// Search box
print '<table class="noborder">';
print '<tr class="liste_titre">';
print '<th colspan="2">'.$langs->trans('Search').'</th>';
print '</tr>';

print '<tr class="oddeven">';
print '<td class="nowraponall">'.$langs->trans('Template').'</td>';
print '<td>';
print '<input type="text" class="flat" name="search_template" value="'.dol_escape_htmltag($search_template).'">';
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td class="nowraponall">'.$langs->trans('Tag').'</td>';
print '<td>';
print '<input type="text" class="flat" name="search_tag" value="'.dol_escape_htmltag($search_tag).'">';
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td colspan="2" class="center">';
print '<input type="submit" class="button" value="'.$langs->trans('Search').'">';
print '&nbsp;';
print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'">'.$langs->trans('RemoveFilter').'</a>';
print '</td>';
print '</tr>';

print '</table>';

print '</div>'; // fichehalfleft

// New template button
print '<div class="fichehalfright" style="text-align:right;">';
if (!empty($user->rights->multidoctemplate->write)) {
    print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=createform">';
    print $langs->trans('MultiDocTemplateNewTemplate');
    print '</a>';
}
print '</div>'; // fichehalfright
print '</div>'; // fichecenter
print '</form>';

print '<br>';

// Creation form (inline) if action=createform
if ($action == 'createform' && !empty($user->rights->multidoctemplate->write)) {
    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'" enctype="multipart/form-data">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="create">';

    print '<div class="div-table-responsive-no-min">';
    print '<table class="noborder centpercent">';
    print '<tr class="liste_titre">';
    print '<th colspan="2">'.$langs->trans('MultiDocTemplateNewTemplate').'</th>';
    print '</tr>';

    print '<tr class="oddeven">';
    print '<td class="fieldrequired">'.$langs->trans('Label').'</td>';
    print '<td><input type="text" class="flat" name="label" value=""></td>';
    print '</tr>';

    print '<tr class="oddeven">';
    print '<td>'.$langs->trans('Tag').'</td>';
    print '<td><input type="text" class="flat" name="tag" value=""></td>';
    print '</tr>';

    print '<tr class="oddeven">';
    print '<td class="fieldrequired">'.$langs->trans('File').'</td>';
    print '<td><input type="file" name="userfile" class="flat" required></td>';
    print '</tr>';

    // TODO: group selector if templates are group-based
    // $form->select_dolgroups(...)

    print '<tr class="oddeven">';
    print '<td colspan="2" class="center">';
    print '<input type="submit" class="button" value="'.$langs->trans('Create').'">';
    print '&nbsp;';
    print '<a class="button" href="'.$_SERVER["PHP_SELF"].'">'.$langs->trans('Cancel').'</a>';
    print '</td>';
    print '</tr>';

    print '</table>';
    print '</div>';

    print '</form>';
    print '<br>';
}

// --------------------------------------------------------------------
// Tag (folder) groups
// --------------------------------------------------------------------
if (empty($templatesByTag)) {
    print $langs->trans('NoRecords');
} else {
    foreach ($templatesByTag as $tag => $list) {
        $tagLabel = dol_escape_htmltag($tag !== '' ? $tag : $langs->trans('MultiDocTemplateUntagged'));

        print load_fiche_titre($langs->trans('MultiDocTemplateFolder').' : '.$tagLabel, '', 'folder');

        print '<div class="div-table-responsive">';
        print '<table class="noborder centpercent">';
        print '<tr class="liste_titre">';
        print '<th>'.$langs->trans('Label').'</th>';
        print '<th>'.$langs->trans('File').'</th>';
        print '<th>'.$langs->trans('Format').'</th>';
        print '<th>'.$langs->trans('Group').'</th>';
        print '<th>'.$langs->trans('DateCreation').'</th>';
        print '<th class="right">'.$langs->trans('Actions').'</th>';
        print '</tr>';

        $var = true;
        foreach ($list as $obj) {
            $var = !$var;
            print '<tr class="'.($var ? 'pair' : 'impair').'">';

            // Label
            print '<td>'.dol_escape_htmltag($obj->label).'</td>';

            // File
            print '<td>'.dol_escape_htmltag($obj->filename).'</td>';

            // Format
            print '<td>'.dol_escape_htmltag($obj->format).'</td>';

            // Group
            print '<td>'.dol_escape_htmltag($obj->groupname).'</td>';

            // Date
            print '<td>'.dol_print_date($db->jdate($obj->datec), 'dayhour').'</td>';

            // Actions
            print '<td class="right nowraponall">';
            if (!empty($user->rights->multidoctemplate->write)) {
                // Edit
                print '<a class="butActionSmall" href="'.$_SERVER["PHP_SELF"].'?action=edit&id='.$obj->id.'">';
                print $langs->trans('Modify');
                print '</a> ';
            }
            if (!empty($user->rights->multidoctemplate->delete)) {
                // Delete
                $url = $_SERVER["PHP_SELF"].'?action=delete&id='.$obj->id.'&token='.newToken();
                $confirm = dol_escape_js($langs->transnoentities('ConfirmDelete'));
                print '<a class="butActionDelete" href="'.$url.'" onclick="return confirm(\''.$confirm.'\');">';
                print $langs->trans('Delete');
                print '</a>';
            }
            print '</td>';

            print '</tr>';
        }

        print '</table>';
        print '</div>';
        print '<br>';
    }
}

llxFooter();
$db->close();