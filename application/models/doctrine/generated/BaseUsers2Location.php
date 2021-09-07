<?php

	abstract class BaseUsers2Location extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('users2location');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('location', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('leader', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>