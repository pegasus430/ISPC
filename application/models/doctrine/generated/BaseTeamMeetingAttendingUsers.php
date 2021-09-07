<?php

	abstract class BaseTeamMeetingAttendingUsers extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('team_meeting_attending_users');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('meeting', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('user', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('isdelete', 'int', 1, array('type' => 'int', 'length' => 1));
		}
		
		function setUp()
		{
		    $this->hasOne('User', array(
		        'local' => 'user',
		        'foreign' => 'id'
		    ));
		    
		    $this->hasMany('TeamMeeting ', array(
		        'local' => 'meeting',
		        'foreign' => 'id'
		    ));
		}
	}

?>