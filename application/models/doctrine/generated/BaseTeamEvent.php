<?php

	abstract class BaseTeamEvent extends Doctrine_Record {
		
//  works just like team meeting

		function setTableDefinition()
		{
			$this->setTableName('team_event');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('event_name', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('event_type', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('from_time', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('till_time', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('completed', 'int', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('organizational', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('first_time', 'int', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('voluntary_event', 'int', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>