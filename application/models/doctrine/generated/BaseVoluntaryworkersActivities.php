<?php

	abstract class BaseVoluntaryworkersActivities extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('voluntaryworkers_activities');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('vw_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('activity', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('comment', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('team_event', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('team_event_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('team_event_type', 'integer', 11, array('type' => 'integer', 'length' => 11));

			$this->hasColumn('duration', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('driving_time', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>