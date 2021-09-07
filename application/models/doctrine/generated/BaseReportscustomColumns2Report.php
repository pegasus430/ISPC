<?php

	abstract class BaseReportscustomColumns2Report extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('cr_columns2report');
			$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('report_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('column_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('show_average', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('show_median', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('order_number', 'integer', 3, array('type' => 'integer', 'length' => 3));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
            $this->hasOne('ReportscustomColumns', array(
		        'local' => 'column_id',
		        'foreign' => 'id'
		    ));
		    
		    
		    $this->actAs(new Timestamp());
		}

	}

?>