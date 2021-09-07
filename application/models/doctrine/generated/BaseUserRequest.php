<?php

Doctrine_Manager::getInstance()->bindComponent('UserRequest', 'IDAT');

/**
 * Class BaseUserRequest
 *
 * ISPC-2913,Elena,11.05.2021
 */

class BaseUserRequest extends Pms_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('userrequest');

        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));

        $this->hasColumn('request_source', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('subject_id', 'integer', 11);
        $this->hasColumn('note', 'text');
        $this->hasColumn('request_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
        $this->hasColumn('is_solved', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));
        $this->hasColumn('is_active', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 1));
        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));

    }

    function setUp()
    {
        $this->actAs(new Timestamp());
    }

}