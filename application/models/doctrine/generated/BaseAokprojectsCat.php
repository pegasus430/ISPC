<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 

class BaseAokprojectsCat extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('aokprojects_cat');
        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('husten', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1', '2', '3', '4', '5'), 'default' => ''));
        $this->hasColumn('verschleimt', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1', '2', '3', '4', '5'), 'default' => ''));
        $this->hasColumn('engegefuehl', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1', '2', '3', '4', '5'), 'default' => ''));
        $this->hasColumn('ausser_atem', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1', '2', '3', '4', '5'), 'default' => ''));
        $this->hasColumn('eingeschraenkt', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1', '2', '3', '4', '5'), 'default' => ''));
        $this->hasColumn('bedenken_haus_verlassen', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1', '2', '3', '4', '5'), 'default' => ''));
        $this->hasColumn('probleme_schlafen', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1', '2', '3', '4', '5'), 'default' => ''));
        $this->hasColumn('keine_energie', 'enum', null, array('type' => 'enum', 'notnull' => true, 'values' => array('', '0', '1', '2', '3', '4', '5'), 'default' => ''));
        $this->hasColumn('points', 'integer', 5, array('type' => 'integer', 'length' => 5, 'default' => 0));
        //$this->hasColumn('auswertung_text', 'text', NULL, array('type' => 'text', 'length' => NULL));

    }

    function setUp()
    {
        $this->actAs(new Createtimestamp());

    }

}