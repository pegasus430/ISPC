<?php

	abstract class BaseDailyPlanningVisits extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('daily_planning_visits');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			//ispc-1533
			$this->hasColumn('orderid', 'tinyint', 4, array('type' => 'tinyint', 'length' => 4));
			$this->hasColumn('hour', 'tinyint', 4, array('type' => 'tinyint', 'length' => 4));
			$this->hasColumn('comment', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('is_autoassigned', 'tinyint', 1, array('type' => 'tinyint', 'length' => 1));
			$this->hasColumn('userid_type', 'enum', NULL, array('type' => 'enum', 'values'=>array('grups', 'user', 'pseudogrups' )));
			
			
			$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('custom', 'tinyint', 1, array('type' => 'tinyint', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());

			$this->hasOne('User', array(
				'local' => 'userid',
				'foreign' => 'id'
			));
		}

	}

?>