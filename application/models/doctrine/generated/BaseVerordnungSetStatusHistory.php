<?php

/**
 * ISPC-2539, elena, 23.10.2020
 *
 * Class BaseVerordnungStatusHistory
 */
class BaseVerordnungSetStatusHistory extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->setTableName('verordnung_set_status_history');
        $this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('sapv_verordnung_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
        $this->hasColumn('datum', 'date');
        $this->hasColumn('set_value', 'string', 64, array('type' => 'string','length' => 64)); //primary_set or secondary_set
        $this->hasColumn('value', 'integer', 4, array('type' => 'integer','length' => 4));
        $this->hasColumn('old_value', 'integer', 4, array('type' => 'integer','length' => 4));
        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1, 'default' => 0));
        $this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
        $this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
    }

    function setUp()
    {
        $this->actAs(new Createtimestamp());
    }

}