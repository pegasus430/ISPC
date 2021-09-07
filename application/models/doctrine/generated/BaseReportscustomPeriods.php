<?php

	abstract class BaseReportscustomPeriods extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('cr_reports_periods');
			$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('report_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('type', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('months', 'text', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('quarters', 'text', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('years', 'longtext', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
		    $this->actAs(new Timestamp());
		    
		}

	}

?>