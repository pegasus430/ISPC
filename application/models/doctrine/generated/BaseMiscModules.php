<?php

	abstract class BaseMiscModules extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('misc_modules');
			$this->hasColumn('id', 'integer', 11, array('type' => 'bigint', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('module', 'integer', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'bigint', 'length' => 11));
		}

		function setUp()
		{
			
		}

	}

?>
