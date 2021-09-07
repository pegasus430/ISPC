<?php
abstract class BaseSystemsSyncHospital extends Doctrine_Record
{

    function setTableDefinition ()
    {
        $this->setTableName('systems_sync_hospital');
        $this->hasColumn('id', 'int', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
        $this->hasColumn('epid', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('case_number', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('server_details', 'string', NULL, array('type' => 'string', 'length' => NULL));
        $this->hasColumn('request_details', 'string', NULL, array('type' => 'string', 'length' => NULL));
        $this->hasColumn('request_details', 'string', NULL, array('type' => 'string', 'length' => NULL));
        $this->hasColumn('sapv_start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
    }

    function setUp ()
    {
        $this->actAs(new Timestamp());
    }

}
?>