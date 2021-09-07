<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 

class BaseAokprojectsHerzinsuffienz extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('aokprojects_herzinsuffienz');
        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
        $this->hasColumn('datum_plan', 'date');
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('betablocker', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('acehemmer', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('antikoagulantien', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('glukokortikosteroide', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('antidepressiva', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('nsar', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));
        $this->hasColumn('diuretika', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1'), 'default' => ''));


    }

    function setUp()
    {
        $this->actAs(new Createtimestamp());

    }


}