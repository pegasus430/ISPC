<?php
//ISPC-2577, elena, 07.09.2020

class BaseShortcutTextBlock extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('shortcut_text_blocks');
        $this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('blockname', 'string', 255, array('type' => 'string', 'length' => 255));

        $this->hasColumn('shortcut', 'string', 2, array('type' => 'string', 'length' => 2));
    }

    function setUp()
    {

    }


}