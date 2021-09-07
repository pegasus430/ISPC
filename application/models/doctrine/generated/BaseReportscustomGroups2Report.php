<?php

	abstract class BaseReportscustomGroups2Report extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('cr_groups2report');
			$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('report_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('group_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
		    $this->actAs(new Timestamp());
		}

	}

?>