<?php

	abstract class BaseVwWorkdata extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('voluntaryworkers_work_data');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('n', 'b')));
			$this->hasColumn('vw_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('work_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('besuchsdauer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('comments', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fahrtkilometer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fahrtzeit', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('grund', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('nightshift', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('amount', 'integer', 10, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>