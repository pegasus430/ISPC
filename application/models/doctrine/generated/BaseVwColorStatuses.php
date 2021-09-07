<?php

	abstract class BaseVwColorStatuses extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('voluntaryworkers_color_statuses');
			$this->hasColumn('id', 'bigint', 11, array('type' => 'bigint', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('vw_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('status', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('g', 'y', 'r', 'b')));
			$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>