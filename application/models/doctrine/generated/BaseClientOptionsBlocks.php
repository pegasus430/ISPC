<?php

//ISPC-2698, elena, 22.12.2020

class BaseClientOptionsBlocks extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->setTableName('client_options_blocks');
        $this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'int', 11, array('type' => 'string', 'length' => 11));
        $this->hasColumn('blockname', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('headline', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('options', 'text');
        $this->hasColumn('shortcut', 'string', 2, array('type' => 'string', 'length' => 2, 'default' => ''
        ));
    }

    function setUp()
    {

    }

}