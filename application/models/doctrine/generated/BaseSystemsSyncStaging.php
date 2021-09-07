<?php
abstract class BaseSystemsSyncStaging extends Doctrine_Record
{

    function setTableDefinition ()
    {
        $this->setTableName('systems_sync_staging');
        $this->hasColumn('id', 'int', NULL, array('type' => 'integer', 'length' => NULL, 'primary' => true, 'autoincrement' => true));

        $this->hasColumn('connection',  'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('clientid',    'integer', 11, array('type' => 'integer', 'length' => 11));

        $this->hasColumn('date_received',    'datetime', NULL, array('type' => 'datetime','length' => NULL));

        $this->hasColumn('first_name',   'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('last_name',    'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('birthd',    'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('ipid_there',  'string', 255, array('type' => 'string', 'length' => 255));


        $this->hasColumn('filepath',    'string', 255, array('type' => 'string', 'length' => 255));

        $this->hasColumn('isdelete',    'integer', 1, array('type' => 'integer', 'length' => 1));
    }

    function setUp ()
    {

    }

}
?>