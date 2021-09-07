<?php

	abstract class BaseDtaLocations extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('dta_locations');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>
