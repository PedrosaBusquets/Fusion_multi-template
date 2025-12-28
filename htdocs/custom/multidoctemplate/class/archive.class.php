<?php
/* Copyright (C) 2024
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class MultiDocArchive
 * Stores generated or uploaded archives for thirdparties/contacts
 */
class MultiDocArchive extends CommonObject
{
    public $element       = 'multidoctemplate_archive';
    public $table_element = 'multidoctemplate_archive';
    public $picto         = 'file';

    /** @var DoliDB */
    public $db;

    public $id;
    public $ref;
    public $fk_template;      // 0 if direct upload
    public $object_type;      // 'thirdparty' or 'contact'
    public $object_id;        // socid or contact id
    public $filename;
    public $filepath;
    public $filetype;
    public $filesize;
    public $tag_filter;       // folder/tag name
    public $date_generation;
    public $fk_user_creat;
    public $entity;

    public $error  = '';
    public $errors = array();

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
     * Generate a reference for an archive
     *
     * @param string $object_type 'thirdparty' or 'contact'
     * @param int    $object_id
     * @return string
     */
    public static function generateRef($object_type, $object_id)
    {
        $object_type = ($object_type === 'contact') ? 'CNT' : 'TP';
        $object_id = (int) $object_id;
        return $object_type.'-'.$object_id.'-'.date('YmdHis');
    }

    /**
     * Get base directory to store an archive (per object + tag)
     *
     * Structure:
     *  - Thirdparty: DOL_DATA_ROOT/multidoctemplate/thirdparty/<socid>/<tag>/
     *  - Contact:    DOL_DATA_ROOT/multidoctemplate/contact/<contactid>/<tag>/
     *
     * @param string $object_type 'thirdparty' or 'contact'
     * @param int    $object_id   socid or contact id
     * @param string $tag         folder/tag name (will be sanitized; if empty -> 'DEFAULT')
     * @return string             absolute directory path
     */
    public static function getArchiveDir($object_type, $object_id, $tag)
    {
        // Normalize inputs
        $object_type = ($object_type === 'contact') ? 'contact' : 'thirdparty';
        $object_id   = (int) $object_id;
        $tag         = trim($tag);

        if ($tag === '') {
            $tag = 'DEFAULT';
        }

        // Sanitize tag for filesystem
        $tag = dol_sanitizeFileName($tag);

        // Base dir for module
        $basedir = DOL_DATA_ROOT.'/multidoctemplate';

        if ($object_type === 'thirdparty') {
            // Thirdparty archives: multidoctemplate/thirdparty/<socid>/<tag>/
            $dir = $basedir.'/thirdparty/'.$object_id.'/'.$tag;
        } else {
            // Contact archives: multidoctemplate/contact/<contactid>/<tag>/
            $dir = $basedir.'/contact/'.$object_id.'/'.$tag;
        }

        return $dir;
    }

    /**
     * Create archive record in database
     *
     * @param User $user
     * @param int  $notrigger
     * @return int <0 if KO, >0 if OK
     */
    public function create($user, $notrigger = 0)
    {
        global $conf;

        $error = 0;
        $now = dol_now();

        $this->db->begin();

        $sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element." (";
        $sql .= "ref, fk_template, object_type, object_id,";
        $sql .= "filename, filepath, filetype, filesize, tag_filter,";
        $sql .= "date_generation, fk_user_creat, entity";
        $sql .= ") VALUES (";
        $sql .= "'".$this->db->escape($this->ref)."',";
        $sql .= (int) $this->fk_template.",";
        $sql .= "'".$this->db->escape($this->object_type)."',";
        $sql .= (int) $this->object_id.",";
        $sql .= "'".$this->db->escape($this->filename)."',";
        $sql .= "'".$this->db->escape($this->filepath)."',";
        $sql .= "'".$this->db->escape($this->filetype)."',";
        $sql .= (int) $this->filesize.",";
        $sql .= "'".$this->db->escape($this->tag_filter)."',";
        $sql .= "'".$this->db->idate($now)."',";
        $sql .= (int) $user->id.",";
        $sql .= (int) $conf->entity;
        $sql .= ")";

        dol_syslog(__METHOD__, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);
            $this->date_generation = $now;
            $this->fk_user_creat   = $user->id;

            if (!$notrigger) {
                // $result = $this->call_trigger('MULTIDOCTEMPLATE_ARCHIVE_CREATE', $user);
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
     * Fetch archive by id
     *
     * @param int $id
     * @return int <0 if KO, 0 if not found, >0 if OK
     */
    public function fetch($id)
    {
        $sql = "SELECT rowid, ref, fk_template, object_type, object_id,";
        $sql .= " filename, filepath, filetype, filesize, tag_filter,";
        $sql .= " date_generation, fk_user_creat, entity";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE rowid = ".(int) $id;

        dol_syslog(__METHOD__, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($obj = $this->db->fetch_object($resql)) {
                $this->id              = $obj->rowid;
                $this->ref             = $obj->ref;
                $this->fk_template     = $obj->fk_template;
                $this->object_type     = $obj->object_type;
                $this->object_id       = $obj->object_id;
                $this->filename        = $obj->filename;
                $this->filepath        = $obj->filepath;
                $this->filetype        = $obj->filetype;
                $this->filesize        = $obj->filesize;
                $this->tag_filter      = $obj->tag_filter;
                $this->date_generation = $this->db->jdate($obj->date_generation);
                $this->fk_user_creat   = $obj->fk_user_creat;
                $this->entity          = $obj->entity;

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
     * Delete archive (and file) from database
     *
     * @param User $user
     * @param int  $notrigger
     * @return int >0 if OK, <0 if KO
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
            // $result = $this->call_trigger('MULTIDOCTEMPLATE_ARCHIVE_DELETE', $user);
            // if ($result < 0) $error++;
        }

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
     * Fetch all archives for a given object (thirdparty/contact)
     *
     * @param string $object_type 'thirdparty' or 'contact'
     * @param int    $object_id
     * @return array|int
     */
    public function fetchAllByObject($object_type, $object_id)
    {
        $archives = array();

        $object_type = ($object_type === 'contact') ? 'contact' : 'thirdparty';
        $object_id   = (int) $object_id;

        $sql = "SELECT rowid, ref, fk_template, object_type, object_id,";
        $sql .= " filename, filepath, filetype, filesize, tag_filter,";
        $sql .= " date_generation, fk_user_creat, entity";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE object_type = '".$this->db->escape($object_type)."'";
        $sql .= " AND object_id = ".$object_id;
        $sql .= " ORDER BY tag_filter ASC, date_generation DESC";

        dol_syslog(__METHOD__, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $arch = new self($this->db);
                $arch->id              = $obj->rowid;
                $arch->ref             = $obj->ref;
                $arch->fk_template     = $obj->fk_template;
                $arch->object_type     = $obj->object_type;
                $arch->object_id       = $obj->object_id;
                $arch->filename        = $obj->filename;
                $arch->filepath        = $obj->filepath;
                $arch->filetype        = $obj->filetype;
                $arch->filesize        = $obj->filesize;
                $arch->tag_filter      = $obj->tag_filter;
                $arch->date_generation = $this->db->jdate($obj->date_generation);
                $arch->fk_user_creat   = $obj->fk_user_creat;
                $arch->entity          = $obj->entity;

                $archives[$obj->rowid] = $arch;
            }
            $this->db->free($resql);
            return $archives;
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }
    }
}