<?php
/**
 * 
 * Maria:: Migration CISPC to ISPC 22.07.2020
 *
 */
abstract class BaseWeeklyUserprivileges extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('weekly_userprivileges');
        $this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
        $this->hasColumn('userid', 'string', 11, array('type' => 'string','length' => 11));


        $this->hasColumn('k', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('v', 'string', 255, array('type' => 'string','length' => 255));

        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
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

?>

