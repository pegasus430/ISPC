<?php

	abstract class BaseTeamCustomEvents extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('team_custom_events');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('clientid', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('eventTitle', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('endDate', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('startDate', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('eventType', 'int', 2, array('type' => 'bigint', 'length' => 2));
			$this->hasColumn('allDay', 'int', 1, array('type' => 'bigint', 'length' => 1));
			$this->hasColumn('create_user', 'int', 11, array('type' => 'bigint', 'length' => 11));
			//ispc-1533
			// dayplan_inform will block the hours in rooster/dayplaning, so user cannot have visits at this hours
			$this->hasColumn('dayplan_inform', 'tinyint', 1, array('type' => 'tinyint', 'length' => 1));
			
			//ISPC-2175 added this column
			$this->hasColumn('comments', 'string', null, array(
			    'type' => 'string',
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>