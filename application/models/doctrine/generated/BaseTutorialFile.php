<?php

/**
 * Class BaseTutorialFile
 *
 *  ISPC-2562, elena, 24.08.2020 (page for videos and files)
 * Maria:: Migration CISPC to ISPC 02.09.2020
 */
class BaseTutorialFile extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('tutorial_file');
        $this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('file_name', 'string', 128, array('type' => 'string', 'length' => 128));
        $this->hasColumn('file_foldername', 'string', 128, array('type' => 'string', 'length' => 128));
        $this->hasColumn('mime_type', 'string', 32, array('type' => 'string', 'length' => 32));
        $this->hasColumn('file_description', 'text');
        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
    }

    function setUp()
    {
        $this->actAs(new Createtimestamp());

    }

}