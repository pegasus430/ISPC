<?php

	abstract class BaseReportscustomGroups extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('cr_groups');
			$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('group_name', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('group_description', 'text', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('system', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
		    $this->actAs(new Timestamp());
		}

	}

?>