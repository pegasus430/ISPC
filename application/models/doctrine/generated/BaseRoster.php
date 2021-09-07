<?php

	abstract class BaseRoster extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('duty_roster');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('duty_date', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('user_group', 'char', 11, array('type' => 'char', 'length' => 11));
			$this->hasColumn('shift', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('fullShift', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('row', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('shiftStartTime', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('shiftEndTime', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());

			$this->hasOne('Client', array(
				'local' => 'clientid',
				'foreign' => 'id'
			));
			$this->hasOne('User', array(
				'local' => 'userid',
				'foreign' => 'id'
			));
		}

	}

?>