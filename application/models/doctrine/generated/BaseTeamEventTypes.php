<?php
	abstract class BaseTeamEventTypes extends Doctrine_Record {
		
		function setTableDefinition()
		{
			$this->setTableName('team_event_types');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('voluntary', 'int', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('name', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'int', 1, array('type' => 'integer', 'length' => 1));
		}

		
		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>