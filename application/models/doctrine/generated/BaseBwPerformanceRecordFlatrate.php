<?php

	abstract class BaseBwPerformanceRecordFlatrate extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bw_performance_record_flatrate');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pay_date', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('flatrate_date', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}
	}

?>