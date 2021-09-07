<?php

abstract class BaseTeamMeetingAssignedUsers extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('team_meeting_assigned_users');
		$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
		$this->hasColumn('patient', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('meeting', 'int', 11, array('type' => 'bigint', 'length' => 11));
		$this->hasColumn('row', 'int', 11, array('type' => 'bigint', 'length' => 11));
		$this->hasColumn('user', 'int', 11, array('type' => 'bigint', 'length' => 11));
		
		$this->hasColumn('user_type', 'enum', null, array(
				'type' => 'enum',
				'values' => array('user', 'group', 'pseudogroup'),
				'default' => 'user',
				'notnull' => true,
		));
		
		$this->hasColumn('isdelete', 'int', 1, array('type' => 'int', 'length' => 1));
	}
	
	
	
}

?>