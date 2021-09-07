<?php
/**
 * 
 * Maria:: Migration CISPC to ISPC 22.07.2020
 *
 */
abstract class BasePatientWhitebox extends Doctrine_Record {

    function setTableDefinition()
    {
        $this->setTableName('patient_whitebox');
        $this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('whitebox', 'string', NULL, array('type' => 'string', 'length' => NULL));
        $this->hasColumn('isdeleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
    }

    function setUp()
    {
        $this->actAs(new Createtimestamp());
    }

}

?>