<?php

	abstract class BaseVwAvailability extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('voluntaryworkers_availability');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('vw_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('week_day', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('1','2','3','4','5','6','7')));
			$this->hasColumn('start_time', 'time', NULL, array('type' => 'time', 'length' => NULL));
			$this->hasColumn('end_time', 'time', NULL, array('type' => 'time', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
			//ispc 1739 (p.19)
			$this->hasColumn('allday', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			//ISPC-2401 p9
			$this->hasColumn('morning', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('afternoon', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('evening', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('night', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>