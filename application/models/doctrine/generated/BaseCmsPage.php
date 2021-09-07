<?php

/**
 * Class BaseCmsPage
 * ISPC-2562, elena, 24.08.2020 (page for videos and files)
 * Maria:: Migration CISPC to ISPC 02.09.2020
 */
class BaseCmsPage extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('cms_page');
        $this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('page_name', 'string', 20, array('type' => 'string', 'length' => 64));
        $this->hasColumn('page_content', 'text');
        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
    }

    function setUp()
    {
        $this->actAs(new Createtimestamp());

    }

}