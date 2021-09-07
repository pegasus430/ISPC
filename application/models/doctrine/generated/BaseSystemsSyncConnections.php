<?php
abstract class BaseSystemsSyncConnections extends Doctrine_Record
{

    function setTableDefinition ()
    {
        $this->setTableName('systems_sync_connections');
        $this->hasColumn('id', 'int', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('connection', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
        $this->hasColumn('shortcuts', 'string', NULL, array('type' => 'string', 'length' => NULL));
        $this->hasColumn('config', 'string', NULL, array('type' => 'string', 'length' => NULL));
    }

    function setUp ()
    {

    }

}
?>