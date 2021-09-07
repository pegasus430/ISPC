<?php

	abstract class BaseReportsColumns extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('reports_columns');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('report_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('column_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('column_order', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			
		}

	}

?>