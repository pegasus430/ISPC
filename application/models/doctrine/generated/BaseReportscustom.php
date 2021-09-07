<?php

	abstract class BaseReportscustom extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('cr_reports');
			$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('name', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('description', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('group_operator', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('org', 'andg')));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('system', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('issaved', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
		    $this->actAs(new Timestamp());
		}

	}

?>