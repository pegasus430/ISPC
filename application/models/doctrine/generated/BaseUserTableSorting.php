<?php

	Doctrine_Manager::getInstance()->bindComponent('UserTableSorting', 'SYSDAT');

	abstract class BaseUserTableSorting extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user_table_sorting');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('user', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('page', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('value', 'string', 255, array('type' => 'string', 'length' => 255));
		}
		function setUp()
		{
		    $this->actAs(new Timestamp());
		}
	}

?>