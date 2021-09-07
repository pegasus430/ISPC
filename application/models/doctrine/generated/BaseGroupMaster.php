<?php

	abstract class BaseGroupMaster extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('group_master');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('groupname', 'string', 128, array('type' => 'string', 'length' => 128));
		}

		function setUp()
		{
			$this->hasOne('Client', array(
				'local' => 'id',
				'foreign' => 'clientid'
			));
		}

	}

?>