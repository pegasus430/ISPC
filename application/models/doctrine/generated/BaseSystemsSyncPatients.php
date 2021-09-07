<?php
abstract class BaseSystemsSyncPatients extends Doctrine_Record
{

	function setTableDefinition ()
	{
		$this->setTableName('systems_sync_patients');
		$this->hasColumn('id', 'int', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('connection', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('ipid_here', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('ipid_there', 'string', 255, array('type' => 'string', 'length' => 255));

        $this->hasColumn('sync_enable', 'integer', 1, array('type' => 'integer', 'length' => 1));

        $this->hasColumn('last_received', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('last_sent', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
	}

	function setUp ()
	{

	}

}
?>