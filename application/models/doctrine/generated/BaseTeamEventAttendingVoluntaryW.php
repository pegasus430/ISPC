<?php

	abstract class BaseTeamEventAttendingVoluntaryW extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('team_event_attending_vw');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('event', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('vw_id', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('isdelete', 'int', 1, array('type' => 'int', 'length' => 1));
		}

	}

?>