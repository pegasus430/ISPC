<?php

	abstract class BaseReportscustomColumns extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('cr_columns');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('column_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('c','cc', 'o')));
			$this->hasColumn('allow_average', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('allow_median', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('comments', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('search_type', 'enum', NULL, array('type' => 'enum', 'notnull' => false, 'values' => array('patient', 'invoice', 'user', 'teammeeting','member','voluntary_worker')));
			$this->hasColumn('sortable', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
		    $this->hasOne('ReportscustomColumns2Report', array(
		        'local' => 'id',
		        'foreign' => 'column_id'
		    ));
		    
		    $this->actAs(new Timestamp());
		}

	}

?>