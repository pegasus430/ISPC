<?php

	abstract class BaseTeamMeeting extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('team_meeting');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('meeting_name', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('from_time', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('till_time', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('completed', 'int', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('organizational', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('first_time', 'int', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('patients_location', 'int', 11, array('type' => 'bigint', 'length' => 11));
            $this->hasColumn('casestatus', 'varchar', 255, array('type' => 'varchar', 'length' => 255, 'default' => ''));//Maria:: Migration CISPC to ISPC 22-28.07.2020
		}

		function setUp()
		{
		    $this->hasMany('TeamMeetingAttendingUsers', array(
		        'local' => 'id',
		        'foreign' => 'meeting'
		    ));
			$this->actAs(new Timestamp());
		}

		
		
		
	}

?>