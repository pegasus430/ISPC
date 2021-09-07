<?php
/**
 * 
 * Maria:: Migration CISPC to ISPC 22.07.2020
 *
 */
abstract class BaseFormLock extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('form_lock');
        $this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint','length' => NULL, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar','length' => 255));
        $this->hasColumn('form', 'varchar', 255, array('type' => 'varchar','length' => 255));
        $this->hasColumn('forminner', 'varchar', 255, array('type' => 'varchar','length' => 255));
        $this->hasColumn('lockdate', 'bigint', NULL, array('type' => 'integer','bigint' => NULL));
        $this->hasColumn('user', 'integer', 11, array('type' => 'integer', 'length' => 11));

    }

    function setUp()
    {
    }

}

?>
