<?php
abstract class BaseSystemsSyncTables extends Doctrine_Record
{

	function setTableDefinition ()
	{
		$this->setTableName('systems_sync_tables');
		$this->hasColumn('id', 'int', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('connection', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('ipid_here', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('tabname', 'string', 255, array('type' => 'string', 'length' => 255));

        $this->hasColumn('id_here', 'integer', 11, array('type' => 'integer', 'length' => 11));
        $this->hasColumn('id_there', 'integer', 11, array('type' => 'integer', 'length' => 11));

        $this->hasColumn('last_change', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('received_data', 'integer', 1, array('type' => 'integer', 'length' => 1));
        //received_data=1 if dataset was received from foreign system

        $this->hasColumn('packet_id', 'string', 255, array('type' => 'string', 'length' => 255));
	}

	function setUp ()
	{

	}

}
?>