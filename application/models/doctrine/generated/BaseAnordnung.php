<?php
//ISPC-2697, elena, 13.11.2020
Doctrine_Manager::getInstance()->bindComponent('Anordnung', 'MDAT');

class BaseAnordnung extends Pms_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('anordnung');

        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('name', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('machine', 'integer', 11);
        $this->hasColumn('anordnung_type', 'string', 255, array('type' => 'string','length' => 255, 'default' => 'beatmung'));
        $this->hasColumn('parameters', 'text');
        $this->hasColumn('timelinedata', 'text');
        $this->hasColumn('description', 'text');
        $this->hasColumn('color', 'string', 16, array('type' => 'string','length' => 16));
        $this->hasColumn('is_active', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 1));
        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));

    }

    function setUp()
    {
        $this->actAs(new Timestamp());
    }

}