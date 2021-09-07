<?php

	abstract class BaseDailyPlanningUsers extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('daily_planning_users');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//ispc-1533
			$this->hasColumn('view_mode', 'enum', null,
					array('values' => array('order', 'timed'))
					);
			$this->hasColumn('userid_type', 'enum', NULL, 
					array('type' => 'enum', 'values'=>array('grups', 'user', 'pseudogrups' )));
				
			
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