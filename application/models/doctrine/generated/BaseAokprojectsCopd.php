<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 

class BaseAokprojectsCopd extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('aokprojects_copd');
        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
        $this->hasColumn('datum_plan', 'date');
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('isc', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('glukokortikosteroide', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('anticholinergikum', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('beta', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('kombi', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));

    }

    function setUp()
    {
        $this->actAs(new Createtimestamp());

    }


}