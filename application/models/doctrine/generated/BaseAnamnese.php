<?php
//ISPC-2694, elena, 11.12.2020
Doctrine_Manager::getInstance()->bindComponent('Anamnese', 'MDAT');

/**
 * Class BaseAnamnese
 */
class BaseAnamnese extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('patient_anamnese');
        $this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('contact_form_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'default => 0'));
        //$this->hasColumn('userid', 'integer');
        $this->hasColumn('datum', 'date');
        $this->hasColumn('childhood_diseases', 'text');
        $this->hasColumn('birth_anamnese', 'text');
        $this->hasColumn('development_anamnese', 'text');
        $this->hasColumn('extra_anamnese', 'text');
        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1, 'default' => 0));
        $this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
        $this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
    }

    function setUp()
    {
        $this->actAs(new Timestamp());
    }


}