<?php

	abstract class BaseReportsnew extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('reportsnew');
			$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => false));
			$this->hasColumn('report', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('functionname', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('description', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('columns', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('select_date_for', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('date_type', 'varchar', 30, array('type' => 'varchar', 'length' => 30));
			$this->hasColumn('show_extrafields', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nowactiv_timefilter', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('show_timefilter', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('include_active', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('include_dead', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('include_standby', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			
		}

	}

?>