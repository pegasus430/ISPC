<?php

abstract class BaseCertifiedDevices extends Pms_Doctrine_Record
{

    public function setTableDefinition()
    {
        $this->setTableName('certified_devices');
        
        $this->hasColumn('id', 'bigint', 20, array(
            'type' => 'bigint',
            'length' => 20,
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('clientid', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11
        ));
        $this->hasColumn('deviceid', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('userid', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11
        ));
        $this->hasColumn('create_date', 'datetime', NULL, array(
            'type' => 'datetime',
            'length' => NULL
        ));
        $this->hasColumn('last_sync', 'datetime', NULL, array(
            'type' => 'datetime',
            'length' => NULL
        ));
        $this->hasColumn('isdelete', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
    }

    public function setUp()
    {
        parent::setUp();
        
        $this->actAs(new Softdelete());
    }
}

?>