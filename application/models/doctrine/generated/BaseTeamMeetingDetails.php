<?php

	abstract class BaseTeamMeetingDetails extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('team_meeting_details');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('meeting', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('patient', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('row', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('send_todo', 'int', 1, array('type' => 'int', 'length' => 1));
			$this->hasColumn('verlauf', 'int', 1, array('type' => 'int', 'length' => 1));
			$this->hasColumn('problem', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('targets', 'string', 255, array('type' => 'string', 'length' => 255)); //ISPC-2556 Andrei 27.05.2020
			
			$this->hasColumn('current_situation', 'string', 255, array('type' => 'string', 'length' => 255)); //ISPC-2896 Lore 19.04.2021
			$this->hasColumn('hypothesis_problem', 'string', 255, array('type' => 'string', 'length' => 255)); //ISPC-2896 Lore 19.04.2021
			$this->hasColumn('measures_problem', 'string', 255, array('type' => 'string', 'length' => 255)); //ISPC-2896 Lore 19.04.2021
			
			$this->hasColumn('todo', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('status', 'int', 1, array('type' => 'int', 'length' => 1));
			$this->hasColumn('isdelete', 'int', 1, array('type' => 'int', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>