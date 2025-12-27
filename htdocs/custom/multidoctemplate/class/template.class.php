<?php
/* Copyright (C) 2024
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

/**
 * Class MultiDocTemplate
 * Manages document templates for user groups
 */
class MultiDocTemplate extends CommonObject
{
    public $element       = 'multidoctemplate_template';
    public $table_element = 'multidoctemplate_template';
    public $picto         = 'file';

    /** @var DoliDB */
    public $db;

    public $id;
    public $ref;
    public $label;
    public $description;
    public $tag;
    public $fk_usergroup;
    public $filename;
    public $filepath;
    public $filetype;
    public $filesize;
    public $mime_type;
    public $active;
    public $fk_categorie_customer; // category for Thirdparties (customers)
    public $fk_categorie_contact;  // category for Contacts
    public $date_creation;
    public $date_modification;
    public $fk_user_creat;
    public $fk_user_modif;
    public $entity;

    public $error = '';
    public $errors = array();

    // Allowed file extensions
    public static $allowed_extensions = array(
        'odt',   // Mandatory
        'ods',   // Spreadsheet
        'xls',
        'xlsx',
        'doc',
        'docx',
        'pdf',
        'rtf'
    );

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Create template in database
     *
     * @param User $user User that creates
     * @param int  $notrigger 0=launch triggers, 1=disable triggers
     * @return int <0 if KO, Id of created object if OK
     */
    public function create($user, $notrigger = 0)
    {
        global $conf;

        $error = 0;
        $now = dol_now();

        // Clean parameters
        $this->ref         = dol_sanitizeFileName($this->ref);
        $this->label       = trim($this->label);
        $this->fk_usergroup = (int) $this->fk_usergroup;
        $this->fk_categorie_customer = (int) $this->fk_categorie_customer;
        $this->fk_categorie_contact  = (int) $this->fk_categorie_contact;

        if ($this->fk_categorie_customer <= 0) $this->fk_categorie_customer = null;
        if ($this->fk_categorie_contact  <= 0) $this->fk_categorie_contact  = null;

        // Check parameters
        if (empty($this->ref)) {
            $this->error = 'ErrorRefRequired';
            return -1;
        }
        if (empty($this->fk_usergroup)) {
            $this->error = 'ErrorUserGroupRequired';
            return -2;
        }

        $this->db->begin();

        $sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element." (";
        $sql .= "ref, label, description, tag, fk_usergroup,";
        $sql .= "filename, filepath, filetype, filesize, mime_type,";
        $sql .= "active, fk_categorie_customer, fk_categorie_contact,";
        $sql .= "date_creation, fk_user_creat, entity";
        $sql .= ") VALUES (";
        $sql .= "'".$this->db->escape($this->ref)."',";
        $sql .= "'".$this->db->escape($this->label)."',";
        $sql .= "'".$this->db->escape($this->description)."',";
        $sql .= "'".$this->db->escape($this->tag)."',";
        $sql .= (int) $this->fk_usergroup.",";
        $sql .= "'".$this->db->escape($this->filename)."',";
        $sql .= "'".$this->db->escape($this->filepath)."',";
        $sql .= "'".$this->db->escape($this->filetype)."',";
        $sql .= (int) $this->filesize.",";
        $sql .= "'".$this->db->escape($this->mime_type)."',";
        $sql .= "1,";
        $sql .= (isset($this->fk_categorie_customer) ? (int) $this->fk_categorie_customer : "NULL").",";
        $sql .= (isset($this->fk_categorie_contact)  ? (int) $this->fk_categorie_contact  : "NULL").",";
        $sql .= "'".$this->db->idate($now)."',";
        $sql .= (int) $user->id.",";
        $sql .= (int) $conf->entity;
        $sql .= ")";

        dol_syslog(__METHOD__, LOG_DEBUG);
        $resql = $this->db->query($sql);

        if ($resql) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);
            $this->date_creation = $now;
            $this->fk_user_creat = $user->id;

            if (!$notrigger) {
                // $result = $this->call_trigger('MULTIDOCTEMPLATE_CREATE', $user);
                // if ($result < 0) $error++;
            }

            if (!$error) {
                $this->db->commit();
                return $this->id;
            } else {
                $this->db->rollback();
                return -1;
            }
        } else {
            $this->error = $this->db->lasterror();
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Update template in database (metadata only, not the file)
     *
     * @param User $user
     * @param int  $notrigger 0=launch triggers, 1=disable triggers
     * @return int >0 if OK, <0 if KO
     */
    public function update($user, $notrigger = 0)
    {
        if (empty($this->id)) {
            $this->error = 'ErrorMissingId';
            return -1;
        }

        $error = 0;
        $now = dol_now();

        // Clean / normalize
        $this->ref         = dol_sanitizeFileName($this->ref);
        $this->label       = trim($this->label);
        $this->fk_usergroup = (int) $this->fk_usergroup;
        $this->fk_categorie_customer = (int) $this->fk_categorie_customer;
        $this->fk_categorie_contact  = (int) $this->fk_categorie_contact;

        if ($this->fk_categorie_customer <= 0) $this->fk_categorie_customer = null;
        if ($this->fk_categorie_contact  <= 0) $this->fk_categorie_contact  = null;

        if (empty($this->ref)) {
            $this->error = 'ErrorRefRequired';
            return -1;
        }
        if (empty($this->fk_usergroup)) {
            $this->error = 'ErrorUserGroupRequired';
            return -2;
        }

        $this->db->begin();

        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET";
        $sql .= " ref = '".$this->db->escape($this->ref)."'";
        $sql .= ", label = '".$this->db->escape($this->label)."'";
        $sql .= ", description = '".$this->db->escape($this->description)."'";
        $sql .= ", tag = '".$this->db->escape($this->tag)."'";
        $sql .= ", fk_usergroup = ".(int) $this->fk_usergroup;
        $sql .= ", fk_categorie_customer = ".(isset($this->fk_categorie_customer) ? (int) $this->fk_categorie_customer : "NULL");
        $sql .= ", fk_categorie_contact = ".(isset($this->fk_categorie_contact) ? (int) $this->fk_categorie_contact : "NULL");
        $sql .= ", date_modification = '".$this->db->idate($now)."'";
        $sql .= ", fk_user_modif = ".(int) $user->id;
        $sql .= " WHERE rowid = ".(int) $this->id;

        dol_syslog(__METHOD__, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql) {
            $this->error = $this->db->lasterror();
            $error++;
        }

        if (!$error && !$notrigger) {
            // $result = $this->call_trigger('MULTIDOCTEMPLATE_MODIFY', $user);
            // if ($result < 0) $error++;
        }

        if (!$error) {
            $this->db->commit();
            return 1;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Load template from database
     *
     * @param int    $id   Id of template to fetch
     * @param string $ref  Ref of template to fetch
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetch($id, $ref = '')
    {
        $sql = "SELECT t.rowid, t.ref, t.label, t.description, t.tag, t.fk_usergroup,";
        $sql .= " t.filename, t.filepath, t.filetype, t.filesize, t.mime_type,";
        $sql .= " t.active, t.fk_categorie_customer, t.fk_categorie_contact,";
        $sql .= " t.date_creation, t.date_modification,";
        $sql .= " t.fk_user_creat, t.fk_user_modif, t.entity";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
        $sql .= " WHERE t.entity IN (".getEntity($this->element).")";
        if ($id) {
            $sql .= " AND t.rowid = ".(int) $id;
        } elseif ($ref) {
            $sql .= " AND t.ref = '".$this->db->escape($ref)."'";
        }

        dol_syslog(__METHOD__, LOG_DEBUG);
        $resql = $this->db->query($sql);

        if ($resql) {
            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                $this->id                    = $obj->rowid;
                $this->ref                   = $obj->ref;
                $this->label                 = $obj->label;
                $this->description           = $obj->description;
                $this->tag                   = $obj->tag;
                $this->fk_usergroup          = $obj->fk_usergroup;
                $this->filename              = $obj->filename;
                $this->filepath              = $obj->filepath;
                $this->filetype              = $obj->filetype;
                $this->filesize              = $obj->filesize;
                $this->mime_type             = $obj->mime_type;
                $this->active                = $obj->active;
                $this->fk_categorie_customer = $obj->fk_categorie_customer;
                $this->fk_categorie_contact  = $obj->fk_categorie_contact;
                $this->date_creation         = $this->db->jdate($obj->date_creation);
                $this->date_modification     = $this->db->jdate($obj->date_modification);
                $this->fk_user_creat         = $obj->fk_user_creat;
                $this->fk_user_modif         = $obj->fk_user_modif;
                $this->entity                = $obj->entity;

                $this->db->free($resql);
                return 1;
            }
            $this->db->free($resql);
            return 0;
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }
    }

    /**
     * Delete template from database
     *
     * @param User $user User that deletes
     * @param int  $notrigger 0=launch triggers, 1=disable triggers
     * @return int <0 if KO, >0 if OK
     */
    public function delete($user, $notrigger = 0)
    {
        $error = 0;

        $this->db->begin();

        // Delete file from disk
        if (!empty($this->filepath) && file_exists($this->filepath)) {
            dol_delete_file($this->filepath);
        }

        if (!$notrigger) {
            // $result = $this->call_trigger('MULTIDOCTEMPLATE_DELETE', $user);
            // if ($result < 0) $error++;
        }

        // First delete all archives that reference this template
        if (!$error) {
            $sql = "DELETE FROM ".MAIN_DB_PREFIX."multidoctemplate_archive";
            $sql .= " WHERE fk_template = ".(int) $this->id;

            dol_syslog(__METHOD__." archives", LOG_DEBUG);
            $resql = $this->db->query($sql);

            if (!$resql) {
                $this->error = $this->db->lasterror();
                $error++;
            }
        }

        // Then delete the template
        if (!$error) {
            $sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
            $sql .= " WHERE rowid = ".(int) $this->id;

            dol_syslog(__METHOD__, LOG_DEBUG);
            $resql = $this->db->query($sql);

            if (!$resql) {
                $this->error = $this->db->lasterror();
                $error++;
            }
        }

        if (!$error) {
            $this->db->commit();
            return 1;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Get list of templates for a user group
     *
     * @param int $fk_usergroup User group ID
     * @param int $active       1=active only, 0=inactive only, -1=all
     * @return array|int        Array of templates (id => object) or -1 on error
     */
    public function fetchAllByUserGroup($fk_usergroup, $active = 1)
    {
        $templates = array();

        $sql = "SELECT t.rowid, t.ref, t.label, t.description, t.tag, t.fk_usergroup,";
        $sql .= " t.filename, t.filepath, t.filetype, t.filesize, t.mime_type,";
        $sql .= " t.active, t.fk_categorie_customer, t.fk_categorie_contact, t.date_creation";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
        $sql .= " WHERE t.entity IN (".getEntity($this->element).")";
        $sql .= " AND t.fk_usergroup = ".(int) $fk_usergroup;
        if ($active >= 0) {
            $sql .= " AND t.active = ".(int) $active;
        }
        $sql .= " ORDER BY t.tag ASC, t.label ASC";

        dol_syslog(__METHOD__, LOG_DEBUG);
        $resql = $this->db->query($sql);

        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $template = new self($this->db);
                $template->id                    = $obj->rowid;
                $template->ref                   = $obj->ref;
                $template->label                 = $obj->label;
                $template->description           = $obj->description;
                $template->tag                   = $obj->tag;
                $template->fk_usergroup          = $obj->fk_usergroup;
                $template->filename              = $obj->filename;
                $template->filepath              = $obj->filepath;
                $template->filetype              = $obj->filetype;
                $template->filesize              = $obj->filesize;
                $template->mime_type             = $obj->mime_type;
                $template->active                = $obj->active;
                $template->fk_categorie_customer = $obj->fk_categorie_customer;
                $template->fk_categorie_contact  = $obj->fk_categorie_contact;
                $template->date_creation         = $this->db->jdate($obj->date_creation);
                $templates[$obj->rowid]          = $template;
            }
            $this->db->free($resql);
            return $templates;
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }
    }

    /**
     * Get all templates accessible to user's groups (for explorer, before category filtering by object)
     *
     * @param User $user User object
     * @return array|int Array of templates (id => object) or -1 on error
     */
    public function fetchAllForUser($user)
    {
        $templates = array();

        // Get user's groups
        $sql_groups = "SELECT fk_usergroup FROM ".MAIN_DB_PREFIX."usergroup_user";
        $sql_groups .= " WHERE fk_user = ".(int) $user->id;
        $sql_groups .= " AND entity IN (".getEntity('usergroup').")";

        dol_syslog(__METHOD__." get groups", LOG_DEBUG);
        $resql_groups = $this->db->query($sql_groups);

        $groupids = array();
        if ($resql_groups) {
            while ($obj_group = $this->db->fetch_object($resql_groups)) {
                $groupids[] = (int) $obj_group->fk_usergroup;
            }
            $this->db->free($resql_groups);
        }

        // If user has no groups, allow all if admin
        if (empty($groupids)) {
            if ($user->admin) {
                $sql_all_groups = "SELECT rowid FROM ".MAIN_DB_PREFIX."usergroup";
                $sql_all_groups .= " WHERE entity IN (".getEntity('usergroup').")";
                $resql_all = $this->db->query($sql_all_groups);
                if ($resql_all) {
                    while ($obj_grp = $this->db->fetch_object($resql_all)) {
                        $groupids[] = (int) $obj_grp->rowid;
                    }
                    $this->db->free($resql_all);
                }
            }
            if (empty($groupids)) {
                return $templates;
            }
        }

        $sql = "SELECT t.rowid, t.ref, t.label, t.description, t.tag, t.fk_usergroup,";
        $sql .= " t.filename, t.filepath, t.filetype, t.filesize, t.mime_type,";
        $sql .= " t.active, t.fk_categorie_customer, t.fk_categorie_contact, t.date_creation,";
        $sql .= " ug.nom as usergroup_name";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."usergroup as ug ON ug.rowid = t.fk_usergroup";
        $sql .= " WHERE t.entity IN (".getEntity($this->element).")";
        $sql .= " AND t.fk_usergroup IN (".implode(',', $groupids).")";
        $sql .= " AND t.active = 1";
        $sql .= " ORDER BY t.tag ASC, ug.nom ASC, t.label ASC";

        dol_syslog(__METHOD__, LOG_DEBUG);
        $resql = $this->db->query($sql);

        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $template = new self($this->db);
                $template->id                    = $obj->rowid;
                $template->ref                   = $obj->ref;
                $template->label                 = $obj->label;
                $template->description           = $obj->description;
                $template->tag                   = $obj->tag;
                $template->fk_usergroup          = $obj->fk_usergroup;
                $template->filename              = $obj->filename;
                $template->filepath              = $obj->filepath;
                $template->filetype              = $obj->filetype;
                $template->filesize              = $obj->filesize;
                $template->mime_type             = $obj->mime_type;
                $template->active                = $obj->active;
                $template->fk_categorie_customer = $obj->fk_categorie_customer;
                $template->fk_categorie_contact  = $obj->fk_categorie_contact;
                $template->date_creation         = $this->db->jdate($obj->date_creation);
                $template->usergroup_name        = $obj->usergroup_name;
                $templates[$obj->rowid]          = $template;
            }
            $this->db->free($resql);
            return $templates;
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }
    }

    /**
     * Get templates visible for a given object (Thirdparty or Contact) according to categories.
     *
     * Rules:
     * - If object is Societe (Thirdparty):
     *     - If fk_categorie_customer IS NULL => template visible (old behaviour).
     *     - If fk_categorie_customer IS NOT NULL => visible only if Thirdparty is in that category.
     * - If object is Contact:
     *     - If fk_categorie_contact IS NULL => template NOT visible.
     *     - If fk_categorie_contact IS NOT NULL => visible only if Contact is in that category.
     *
     * @param CommonObject $object  Societe or Contact
     * @return array                id => stdClass row (DB row)
     */
    public function getForObject($object)
    {
        $result = array();

        if (!is_object($object)) {
            return $result;
        }

        $classname = get_class($object);
        $isSociete = ($classname === 'Societe' || $object->element === 'societe');
        $isContact = ($classname === 'Contact' || $object->element === 'contact');

        if (!$isSociete && !$isContact) {
            return $result;
        }

        // 1. Get categories of the object
        $objectCategoryIds = array();
        $catType = $isSociete ? 'customer' : 'contact';

        $c = new Categorie($this->db);
        $cats = $c->containing($object->id, $catType);
        if (!is_array($cats)) $cats = array();
        foreach ($cats as $cat) {
            $objectCategoryIds[] = (int) $cat->id;
        }

        // 2. Load all active templates
        $sql = "SELECT rowid, ref, label, description, tag, fk_usergroup,";
        $sql .= " filename, filepath, filetype, filesize, mime_type, active,";
        $sql .= " fk_categorie_customer, fk_categorie_contact, date_creation";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE entity IN (".getEntity($this->element).")";
        $sql .= " AND active = 1";

        dol_syslog(__METHOD__, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql) {
            $this->error = $this->db->lasterror();
            return $result;
        }

        while ($obj = $this->db->fetch_object($resql)) {
            $show = false;

            if ($isSociete) {
                // Thirdparty
                if (empty($obj->fk_categorie_customer)) {
                    // No category restriction => visible
                    $show = true;
                } else {
                    if (in_array((int) $obj->fk_categorie_customer, $objectCategoryIds, true)) {
                        $show = true;
                    }
                }
            } elseif ($isContact) {
                // Contact
                if (empty($obj->fk_categorie_contact)) {
                    // No contact category => not visible
                    $show = false;
                } else {
                    if (in_array((int) $obj->fk_categorie_contact, $objectCategoryIds, true)) {
                        $show = true;
                    }
                }
            }

            if ($show) {
                $result[(int) $obj->rowid] = $obj;
            }
        }

        $this->db->free($resql);
        return $result;
    }

    /**
     * Get upload directory path for a user group
     *
     * @param int $fk_usergroup User group ID
     * @return string Directory path
     */
    public static function getUploadDir($fk_usergroup)
    {
        return DOL_DATA_ROOT.'/multidoctemplate/templates/group_'.(int) $fk_usergroup;
    }

    /**
     * Check if file extension is allowed
     *
     * @param string $filename Filename to check
     * @return bool  True if allowed
     */
    public static function isAllowedExtension($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, self::$allowed_extensions);
    }

    /**
     * Check if file is ODT (mandatory format)
     *
     * @param string $filename Filename to check
     * @return bool  True if ODT
     */
    public static function isODT($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return ($ext === 'odt');
    }

    /**
     * Get MIME type for file
     *
     * @param string $filename Filename
     * @return string MIME type
     */
    public static function getMimeType($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimes = array(
            'odt'  => 'application/vnd.oasis.opendocument.text',
            'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf'  => 'application/pdf',
            'rtf'  => 'application/rtf'
        );
        return isset($mimes[$ext]) ? $mimes[$ext] : 'application/octet-stream';
    }
}