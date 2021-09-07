<?php
//ISPC-2697, elena, 09.11.2020

Doctrine_Manager::getInstance()->bindComponent('Machine', 'SYSDAT');

abstract  class BaseMachine extends Pms_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('machine');

        $this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
        $this->hasColumn('machine_type', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('machine_name', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('parameters', 'text');
        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));

    }

    function setUp()
    {
        $this->actAs(new Timestamp());
    }

}