<?php

	abstract class BaseTeamMeetingPatients extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('team_meeting_patients');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('meeting', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('patient', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('extra', 'int', 1, array('type' => 'int', 'length' => 1));
			$this->hasColumn('isdelete', 'int', 1, array('type' => 'int', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>